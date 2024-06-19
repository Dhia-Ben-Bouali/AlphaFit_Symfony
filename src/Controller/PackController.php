<?php

namespace App\Controller;

use App\Entity\Pack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PackType;
use App\Repository\PackRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PackController extends AbstractController
{
    #[Route('/pack', name: 'app_pack')]
    public function index(): Response
    {
        return $this->render('pack/index.html.twig', [
            'controller_name' => 'PackController',
        ]);
    }
    #[Route("/pack/addpack", name:"add_pack", methods:["GET","POST"])]
    public function addPack(Request $request): Response
    {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pack);
            $entityManager->flush();

            return $this->redirectToRoute('show_all_pack');
        }

        return $this->render('pack/new.html.twig', [
            'pack' => $pack,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/pack/get-pack-details/{id}', name: 'get_pack_details')]
    public function getPackDetails(Request $request, $id): JsonResponse
    {
        // You should implement your logic to fetch user details based on the $id parameter
        // Replace this with your actual logic to retrieve user details from the database or any other source
        $packDetails = $this->getDoctrine()->getRepository(Pack::class)->find($id);

        // Check if user details were found
        if (!$packDetails) {
            return new JsonResponse(['error' => 'Package not found'], 404);
        }

        // Return user details in JSON format
        return new JsonResponse([
            'nom' => $packDetails->getNom(),
            'description' => $packDetails->getDescription(),
            'prix' => $packDetails->getPrix(),
            
        ]);
    }

    #[Route("/pack/pack{id}", name:"show_pack", methods:["GET"])]
    public function showPack($id, PackRepository $packRepository): Response
    {
        $pack = $packRepository->find($id);

        if (!$pack) {
            throw $this->createNotFoundException('Pack not found');
        }

        return $this->render('pack/show.html.twig', [
            'pack' => $pack,
        ]);
    }

    #[Route("/pack/edit/{id}", name:"edit_pack", methods:["GET", "POST"])]
    public function editPack(int $id, Request $request, EntityManagerInterface $entityManager, PackRepository $packRepository): Response
    {
        $pack = $entityManager->getRepository(Pack::class)->find($id);
        $packs = $this->getDoctrine()->getRepository(Pack::class)->findAll();

        if (!$pack) {
            throw $this->createNotFoundException('Package not found');
        }

        // Manually set user fields from the request
        $pack->setNom($request->request->get('pack')['nom']);
        $pack->setDescription($request->request->get('pack')['description']);
        $pack->setPrix($request->request->get('pack')['prix']);

        $entityManager->persist($pack);
        $entityManager->flush();

        $this->addFlash('success', 'Pack updated successfully.');
        return $this->redirectToRoute('show_all_pack');
    }
    
    #[Route("/pack/delete/{id}", name:"delete_pack", methods:["POST"])]
    public function deletePack(int $id, EntityManagerInterface $entityManager): Response
    {
        $pack = $entityManager->getRepository(Pack::class)->find($id);

        if (!$pack) {
            // Handle the case where the user with the given ID is not found
            throw $this->createNotFoundException('Pack not found');
        }

        // Ensure the user is authenticated or add additional checks if needed
        // (e.g., check if the user has the right to delete users)
        $entityManager->remove($pack);
        $entityManager->flush();

        $this->addFlash('success', 'pack deleted successfully.');

        return $this->redirectToRoute('show_all_pack');
    }    

    #[Route("/pack/showall", name:"show_all_pack", methods:["GET"])]
    public function showAllPacks(PackRepository $packRepository, Request $request, MailerInterface $mailer, UserRepository $userRepository): Response
    {
        $packs = $packRepository->findAll();
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pack);
            $entityManager->flush();

            //$reclamationState = $form->get('pack.nom')->getData();
            $users = $userRepository->findAll();

            foreach ($users as $user) {
                $email = (new Email())
                    ->from('legaltechrec@gmail.com')
                    ->to($user->getEmail()) // Send email to each user
                    ->subject('New Package Added')
                    ->html('<head>
                        <style>
                            /* Blue CSS file */
                            body {
                                background-color: #f0f5f9;
                                font-family: Arial, sans-serif;
                                color: #333;
                            }
                            .container {
                                max-width: 600px;
                                margin: 0 auto;
                                padding: 20px;
                                background-color: #fff;
                                border-radius: 8px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }
                            h1 {
                                color: #007bff;
                            }
                            p {
                                line-height: 1.6;
                                font-size: 16px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1>New Package has been added</h1>
                            <p>Dear Client,</p>
                            <p>We wanted to inform you that a <strong>new Package on AlphaFit has been added.</strong></p>
                            <p>Thank you for your attention.</p>
                            <p>Sincerely,<br>AlphaFit</p>
                        </div>
                    </body>
                    </html>');

                $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'yxzc rivk amcc neif');
                $mailer = new Mailer($transport);
                $mailer->send($email);
            }

            return $this->redirectToRoute('show_all_pack');
        }
        return $this->render('pack/show_all.html.twig', [
            'packs' => $packs,
            'pack' => $pack,
            'form' => $form->createView(),
        ]);
    }
    #[Route("/pack/ourpackages", name:"our_packages", methods:["GET"])]
    public function getOurPackages(PackRepository $packRepository): Response
    {
        $packs = $packRepository->findAll();

        return $this->render('pack/our_packages.html.twig', [
            'packs' => $packs,
        ]);
    }
}
