<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Lignecommande;
use App\Entity\Commande;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CheckoutType;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartController extends AbstractController
{
    private $tokenStorage;

    
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(TokenStorageInterface $tokenStorage,EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $cart = $this->getCart();
        $productId = $request->query->get('productId');
    
        if ($productId) {
            $this->addProduct($productId, $request);
        }
    
        // Create the checkout form
        $checkoutForm = $this->createForm(CheckoutType::class);
        $checkoutForm->handleRequest($request);
    
        if ($checkoutForm->isSubmitted() && $checkoutForm->isValid()) {
    
            $entityManager = $this->getDoctrine()->getManager();
            $total = 0;
            // Get the Commande entity from the form data
            $commande = $checkoutForm->getData();
    
            // No need to set properties one by one since they should already be set by the form
            $commande->setIsValid(false);
            $commande->setDate(new \DateTime());
            $commande->setTotale($this->session->get('cartTotal', 0));
    
            $this->entityManager->persist($commande);
    
            foreach ($cart as $item) {
                $article = $item->getArticle();
                // If the article is not managed, find the managed article instance
                if (!$this->entityManager->contains($article)) {
                    $article = $this->entityManager->getRepository(Article::class)->find($article->getId());
                }
                $lineItem = new Lignecommande();
            $lineItem->setArticle($article);
            $lineItem->setQuantite($item->getQuantite());
            $lineItem->setIdCommande($commande);
    
            $total += $article->getPrix() * $item->getQuantite();
    
            $entityManager->persist($lineItem);
        }
    
            $commande->setTotale($total);
            $entityManager->persist($commande);
            $entityManager->flush();
        
            
            $paymentType = $commande->getPaymentType(); // Assuming your Commande entity has a getPaymentType() method

        if ($paymentType === 'delivery') {
            // Handle on delivery payment
            // Send confirmation email
            $user = $this->tokenStorage->getToken()->getUser();
            $email = (new Email())
                ->from('legaltechrec@gmail.com') // Replace with your email
                ->to($user->getUserIdentifier())
                ->subject('Order Confirmation')
                ->html($this->renderView('emails/order_confirmation.html.twig', [
                    'commande' => $commande,
                ]));

            $mailer->send($email);
            $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'yxzc rivk amcc neif');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            // Clear the cart
            $this->session->remove('cart');

            // Redirect to a confirmation page
            //return $this->redirectToRoute('app_order_confirmation');

        } else if ($paymentType === 'online') {
            // Handle online payment
            // Redirect to Stripe payment page
            return $this->redirectToRoute('app_payment', [
                'total' => $total, // Pass the total amount to the payment route
            ]);
        }}
    
        // ... existing code ...
    
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'checkoutForm' => $checkoutForm->createView(),
        ]);
    }



#[Route('/cart/add/{productId}', name: 'app_cart_add')]
public function addProduct($productId, Request $request): Response {
    $cart = $this->getCart(); // Get the cart from the session
    $quantity = (int) $request->request->get('quantity', 1);

    $lineItem = $this->findLineItem($productId, $cart);

    if ($lineItem) {
        $lineItem->setQuantite($lineItem->getQuantite() + $quantity);
    } else {
        $product = $this->getProduct($productId);
        $lineItem = new Lignecommande();
        $lineItem->setArticle($product);
        $lineItem->setQuantite($quantity);
        $cart->append($lineItem);
    }

    $this->updateTotal($cart);
    $this->session->set('cart', $cart);
    return $this->redirectToRoute('app_cart');
}



    #[Route('/cart/remove/{productId}', name: 'app_cart_remove', methods: ['DELETE'])]
    public function removeProduct( $productId, SessionInterface $session): Response
    {


        $cart = $session->get('cart', new \ArrayObject()); // Get the cart from the session or create a new one

        $lineItem = $this->findLineItem($productId, $cart);

        if ($lineItem) {
            $key = $this->getLineItemKey($productId, $cart);

            if ($key !== false) {
                $cart->offsetUnset($key);
                $this->updateTotal($cart);
            }
        }

        $session->set('cart', $cart); // Update the session with the modified cart

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{productId}/{quantity}', name: 'app_cart_update', methods: ['POST'])]
public function updateProductQuantity(Request $request, $productId, $quantity): Response {
    $cart = $this->getCart();
    $found = false;

    foreach ($cart as $lineItem) {
        if ($lineItem->getArticle()->getId() == $productId) {
            $lineItem->setQuantite($quantity);
            $found = true;
            break;
        }
    }

    if ($found) {
        $this->updateTotal($cart);
        $this->session->set('cart', $cart);
        return $this->json(['success' => true, 'newTotal' => $this->session->get('cartTotal')]);
    } else {
        return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
    }
}






private function getLineItemKey($productId, \ArrayObject $cart): bool|int
{
    foreach ($cart as $key => $lineItem) {
        if ($lineItem->getArticle()->getId() == $productId) {
            return $key;
        }
    }

    return false;
}


private function getCart(): \ArrayObject
{
    $cart = $this->session->get('cart');

    if (!$cart) {
        $cart = new \ArrayObject();
        $this->session->set('cart', $cart);
    }

    return $cart;
}

    private function getProduct($productId): Article
    {
        $product = $this->getDoctrine()->getRepository(Article::class)->find($productId);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        return $product;
    }

    private function findLineItem($productId, \ArrayObject $cart): ?Lignecommande
    {
        foreach ($cart as $lineItem) {
            if ($lineItem->getArticle()->getId() == $productId) {
                return $lineItem;
            }
        }

        return null;
    }

    private function updateTotal(\ArrayObject $cart): void
    {
        $total = 0;

        foreach ($cart as $lineItem) {
            $total += $lineItem->getQuantite() * $lineItem->getArticle()->getPrix();
        }

        $this->getSession()->set('cartTotal', $total);
    }

    private function getSession(): SessionInterface
{
    return $this->session;
}




    

}