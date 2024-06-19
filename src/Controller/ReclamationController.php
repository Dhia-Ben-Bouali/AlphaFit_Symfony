<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationFormType;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\ReclamationFormAjoutType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\Mailer;


class ReclamationController extends AbstractController
{
    #[Route('/reclamation_admin', name: 'app_reclamationA')]
    public function reclamation_admin(Request $request, ReclamationRepository $reclamationRepository, PaginatorInterface $paginator): Response
    {
    
    $searchDate = $request->query->get('date');
    $searchEtat = $request->query->get('etat');

    if (($searchDate !== null && $searchDate !== '') || ($searchEtat !== null && $searchEtat !== '') || ($searchEtat == null && $searchEtat !== '') || ($searchEtat !== null && $searchEtat == '')) {    
        $reclamation = $reclamationRepository->findByDateAndEtat($searchDate, $searchEtat);
    } else {
        $reclamation = $reclamationRepository->findAll();
    }

    
    $sortByDate = $request->query->get('sortByDate');
    if ($sortByDate === 'asc') {
        $reclamation = $reclamationRepository->findBy([], ['date' => 'ASC']);
    } elseif ($sortByDate === 'desc') {
        $reclamation = $reclamationRepository->findBy([], ['date' => 'DESC']);
    }

    
    $reclamation = $paginator->paginate(
        $reclamation,
        $request->query->getInt('page', 1),
        8
    );

    return $this->render('reclamation/ReclamationA.html.twig', [
        'rec' => $reclamation,
    ]);
    }
    


    #[Route('/delete/{id}', name: 'app_delete')]
    public function delete(ReclamationRepository $reclamationRepository, $id, MailerInterface $mailer): Response
    {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation non trouvé');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($reclamation);
        $em->flush();
        $this->addFlash('success', 'Reclamation has been deleted successfully.');
        return $this->redirectToRoute('app_reclamationA');
    }


    #[Route('/edit/{id}', name: 'app_edit')]
    public function edit(ReclamationRepository $reclamationRepository, $id, Request $request)
    {
        $author = $reclamationRepository->find($id);
        $form = $this->createForm(ReclamationFormAjoutType::class, $author);
        $form->add('Edit', SubmitType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $reclamationState = $form->get('etat')->getData();
            $email = (new Email())
                ->from('legaltechrec@gmail.com')
                ->to('ikramsegni28@gmail.com')
                ->subject('Reclamation Update')
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
                        <h1>Reclamation Status Update</h1>
                        <p>Dear Client,</p>
                        <p>We wanted to inform you that the status of your reclamation on AlphaFit has been changed.</p>
                        <p>New Status: <strong>'  . $reclamationState . '</strong></p>
                        <p>Thank you for your attention.</p>
                        <p>Sincerely,<br>AlphaFit</p>
                    </div>
                </body>
                </html>');

            $transport = new GmailSmtpTransport('legaltechrec@gmail.com', 'rkprtxlynlapdxdk');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            $this->addFlash('success', 'Reclamation has been edited successfully.');
            return $this->redirectToRoute("app_reclamationA");
        }
    
        return $this->render('reclamation/ReclamationA_Edit.html.twig', [
            'reclamationForm' => $form->createView(),
        ]);
    }

    #[Route('/reclamation_client', name: 'app_reclamation')]
    public function reclamationC(ReclamationRepository $reclamationRepository, Request $request, Security $security): Response
    {
        $user = $security->getUser();
        $reclamationRepository = new Reclamation();
        $reclamationRepository->setRecuser($user);
        $form = $this->CreateForm(ReclamationFormAjoutType::class, $reclamationRepository);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
            $em=$this->getDoctrine()->getManager();
            $em->persist($reclamationRepository);
            $em->flush();
            $this->addFlash('success', 'Reclamation has been added successfully.');
            return $this->redirectToRoute("home");
        }
        return $this->render("reclamation/reclamation_Ajout.html.twig", 
        ["reclamationForm"=> $form->createView(),]);
    }

#[Route('/reclamation', name: 'app_reclamations')] // Removed userId from route
public function userReclamations(Security $security): Response
{
    // Get the currently logged-in user
    $currentUser = $security->getUser();

    // Check if a user is logged in
    if (!$currentUser) {
        throw $this->createAccessDeniedException('You must be logged in to access this resource.');
    }

    $reclamations = $currentUser->getReclamations();

    return $this->render('reclamation/user_reclamations.html.twig', [
        'reclamations' => $reclamations,
    ]);
}


    #[Route('/reclamation_stat', name: 'app_reclamation_stat')]
    public function reclamationGraph(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findAll(); // Assuming findAll() fetches all reclamations

        $data = [];
        foreach ($reclamations as $reclamation) {
            $date = $reclamation->getDate()->format('Y-m-d'); // Assuming your Date is a DateTime object
            if (!isset($data[$date])) {
                $data[$date] = 0;
            }
            $data[$date]++;
        }

        // Prepare data for JavaScript
        $labels = [];
        $values = [];
        foreach ($data as $date => $count) {
            $labels[] = $date;
            $values[] = $count;
        }

        return $this->render('reclamation/graph.html.twig', [
            'labels' => json_encode($labels),
            'values' => json_encode($values),
        ]);
    }
}
