<?php

namespace App\Controller;

use App\Entity\Messagerie;
use App\Form\MessagerieType;
use App\Repository\MessagerieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'app_messagerie')]
    public function messagerie(): Response

    {
        return $this->render('messagerie/index.html.twig', [
            'controller_name' => 'MessagerieController',
        ]);
    }


    #[Route('/sendmessage', name: 'sendmessage')]
    public function sendmessage(Request $request): Response

    { $message=new Messagerie();
        $form=$this->createForm(MessagerieType::class,$message);
        $form->add('send', SubmitType::class);

        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid())
        {

            $message->setSender($this->getUser());
            $em=$this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash("contenu", "Message sent successfully");
            return $this->redirectToRoute("app_messagerie");
        }


        return $this->render('messagerie/sendmessage.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/updatemessage/{id}', name: 'updatemessage')]
    public function updatemessage(ManagerRegistry $doctrine, $id,Request $request): Response
    {
        $messagerepo=$doctrine->getRepository(Messagerie::class);
        $message=$messagerepo->find($id);
        $form=$this->createForm(MessagerieType::class,$message);
        $form->add('update',SubmitType::class);

        $form->handleRequest($request);
        $em=$doctrine->getManager();
        if ($form->isSubmitted() and $form->isValid())
        {

            $em->flush();
            return $this->redirectToRoute('messagessent');
        }

        return $this->render('messagerie/updatemessage.html.twig', [
            'form' => $form->createView(),
            'id'=>$id,
        ]);
    }

    #[Route('/messagesreceived', name: 'messagesreceived')]
    public function messagesreceived(Request $request, PaginatorInterface $paginator): Response
    {
        $messages = $this->getDoctrine()->getRepository(Messagerie::class)->findBy(
            ['recipient' => $this->getUser()] // Changer 'sender' à 'recipient' pour les messages reçus
        );

        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de page actuel, par défaut à 1
            6// Nombre d'éléments par page
        );

        return $this->render('messagerie/messagesreceived.html.twig', ['pagination' => $pagination]);
    }


    #[Route('/read/{id}', name: 'read')]
    public function read(ManagerRegistry $doctrine,$id): Response

    {
        $messagerepo=$doctrine->getRepository(Messagerie::class);
        $message=$messagerepo->find($id);
        $message->setIsRead(true);
        $em=$this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();
        return $this->render('messagerie/read.html.twig',compact("message")
        );
    }

    #[Route('deletemessage/{id}', name: 'deletemessage')]
    public function deletemessage(ManagerRegistry $doctrine, $id,Request $request): Response
    {
        $messagerepo=$doctrine->getRepository(Messagerie::class);
        $message=$messagerepo->find($id);
        $form=$this->createForm(MessagerieType::class,$message);
        $form->add('Delete',SubmitType::class);
        $form->handleRequest($request);
        $em=$doctrine->getManager();

        $em->remove($message);
        $em->flush();
        return $this->redirectToRoute('messagessent');



    }




    #[Route('/messagessent', name: 'messagessent')]
    public function messagessent(Request $request, PaginatorInterface $paginator): Response
    {
        $messages = $this->getDoctrine()->getRepository(Messagerie::class)->findBy(
            ['sender' => $this->getUser()] // En supposant que vous avez une entité nommée "Message" avec une propriété "sender"

        );

        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de page actuel, par défaut à 1
            6// Nombre d'éléments par page
        );

        return $this->render('messagerie/messagessent.html.twig', ['pagination' => $pagination]);
    }


    #[Route('/messagesmarked', name: 'messagesmarked')]
    public function messagesmarked(Request $request, PaginatorInterface $paginator): Response
    {
        $messages = $this->getDoctrine()->getRepository(Messagerie::class)->findBy(
            ['sender' => $this->getUser(),
                'is_favorite' => true,

            ] // En supposant que vous avez une entité nommée "Message" avec une propriété "sender"

        );

        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de page actuel, par défaut à 1
            6// Nombre d'éléments par page
        );

        return $this->render('messagerie/marked.html.twig', ['pagination' => $pagination]);
    }
    #[Route('/search_sent', name: 'search-sent')]
    public function search_sent(Request $request, MessagerieRepository $repo, PaginatorInterface $paginator): Response
    {
        $recipient = $request->query->get('recipient');

        // Utilisez le repository pour effectuer la recherche par destinataire
        $messages = $repo->search($recipient);

        // Paginez les résultats
        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de la page dans laquelle vous êtes
            6 // Limite d'éléments par page
        );

        // Passez les résultats paginés à votre vue
        return $this->render('messagerie/messagessent.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/search_marked', name: 'search_marked')]
    public function search_marked(Request $request, MessagerieRepository $repo, PaginatorInterface $paginator): Response
    {
        $recipient = $request->query->get('recipient');

        // Utilisez le repository pour effectuer la recherche par destinataire
        $messages = $repo->search($recipient);

        // Paginez les résultats
        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de la page dans laquelle vous êtes
            6 // Limite d'éléments par page
        );

        // Passez les résultats paginés à votre vue
        return $this->render('messagerie/marked.html.twig', [
            'pagination' => $pagination,
        ]);
    }


    #[Route('/search_recived', name: 'search_recived')]
    public function search_recived(Request $request, MessagerieRepository $repo, PaginatorInterface $paginator): Response
    {
        $recipient = $request->query->get('sender');

        // Utilisez le repository pour effectuer la recherche par destinataire
        $messages = $repo->searchinbox($recipient);

        // Paginez les résultats
        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1), // Numéro de la page dans laquelle vous êtes
            6 // Limite d'éléments par page
        );

        // Passez les résultats paginés à votre vue
        return $this->render('messagerie/messagesreceived.html.twig', [
            'pagination' => $pagination,
        ]);
    }

}