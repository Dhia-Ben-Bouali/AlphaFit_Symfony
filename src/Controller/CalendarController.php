<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Suivi;
use App\Entity\User;
use App\Form\AvisType;
use App\Form\SuiviType;
use App\Repository\SuiviRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{    
  
    #[Route('/partype/{userId}', name: 'partype')]

    public function partype(SuiviRepository $suiviRepository, int $userId): Response
    {
        $nombreCoursCoach = $suiviRepository->getNombreCoursParTypeCoachPourUtilisateur($userId);
        $nombreCoursNutritionniste = $suiviRepository->getNombreCoursParTypeNutritionnistePourUtilisateur($userId);

        return $this->render('calendar/stat.html.twig', [
            'nombreCoursCoach' => $nombreCoursCoach,
            'nombreCoursNutritionniste' => $nombreCoursNutritionniste,
        ]);
    }



    #[Route('/calendar', name: 'calendar')]
    public function calendar(): Response
    {
        return $this->render('calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }


    #[Route('/details/{id}', name: 'details')]
    public function details(int $id,SuiviRepository $suivirepo): Response
    {   $details=$suivirepo->find($id);
        dump($details);
        return $this->render('calendar/details.html.twig', [
            'details' => $details,
            'id'=>$id,
        ]);
    }



    #[Route('/addevent/{id}', name: 'addevent')]

    public function addevent(Request $request,UserRepository $userrepo, int $id): Response
    {   $user=$userrepo->find($id);
        $event = new Suivi();
        $form = $this->createForm(SuiviType::class, $event);
        $form->add('add',SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $event->setUser($user);
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('index', ['id' => $id]);
        }

        return $this->render('calendar/addevent.html.twig', [
            'calendar' => $event,
            'form' => $form->createView(),
            'id'=>$id
        ]);
    }

    #[Route('/index/{id}', name: 'index')]

    public function index(ManagerRegistry $doctrine,int $id)

    {  dump($id);
        $suivirepo=$doctrine->getRepository(Suivi::class);
        $events=$suivirepo->findBy(['user'=>$id]);
        

        

        $rdvs = [];

        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'backgroundColor' => $event->getBackgroundColar(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor(),
                'isAllDay' => $event->isAllDay(),
            ];
        }

        $data = json_encode($rdvs);

        return $this->render('calendar/index.html.twig',[
            'data' => $data,
            'id' => $id,
        ]);
    }



    #[Route('/updateevent/{id}/{idevent}', name: 'updateevent')]
    public function updateevent(ManagerRegistry $doctrine,int  $id, int $idevent, Request $request): Response
    {    
        $suivirepo=$doctrine->getRepository(Suivi::class);
        $suivi=$suivirepo->find($idevent);
        $form=$this->createForm(SuiviType::class,$suivi);
        $form->add('update',SubmitType::class);
        $form->handleRequest($request);
        $em=$doctrine->getManager();
        if ($form->isSubmitted() and $form->isValid())
        {
           
            $em->flush();
            return $this->redirectToRoute('index', ['id' => $id]);

        }
        
        return $this->render('calendar/updateevent.html.twig', [
            'form' => $form->createView(),
            'id'=>$id,
            'idevent'=>$idevent
        ]);
    }


    #[Route('deleteevent/{id}/{eventid}', name: 'deletevent')]
    public function deleteevent(ManagerRegistry $doctrine, int $id,int $idevent ,Request $request): Response
    {
        $suivirepo=$doctrine->getRepository(Suivi::class);
        $suivi=$suivirepo->find($idevent);
        $form=$this->createForm(SuiviType::class,$suivi);
        $form->add('Delete',SubmitType::class);
        $form->handleRequest($request);
        $em=$doctrine->getManager();

        $em->remove($suivi);
        $em->flush();
        return $this->redirectToRoute('index', ['id' => $id]);



    }
    #[Route('/pdf/{id}', name: 'pdf')]
    public function pdf(ManagerRegistry $doctrine, int $id): Response
    {
        $now = new \DateTime();
        $startOfWeek = $now->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('sunday this week')->setTime(23, 59, 59);

        $suiviRepo = $doctrine->getRepository(Suivi::class);
        $allEvents = $suiviRepo->findBy(['user' => $id]);

        // Filtrer localement les événements pour la semaine actuelle
        $filteredEvents = array_filter($allEvents, function ($event) use ($startOfWeek, $endOfWeek) {
            return $event->getStart() >= $startOfWeek && $event->getEnd() <= $endOfWeek;
        });
        $pdfOptions = new Options();
        $pdfOptions->set("isHtml5ParserEnabled", true);
        $pdfOptions->set("isPhpEnabled", true);

        $dompdf = new Dompdf($pdfOptions);

        // Passer les données à la vue pdf.html.twig
        $html = $this->renderView('calendar/pdf.html.twig', [
            'data' =>  $filteredEvents,
            'id' => $id,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();

        // Enregistrer le PDF dans un fichier avec un nom spécifique
        $pdfPath = 'C:\Users\ikram\Desktop\calendar.pdf';
        file_put_contents($pdfPath, $output);

        // Ou, retourner le PDF en tant que réponse
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="calendar.pdf"');

        return $response;
    }

    #[Route('/deleteavis/{id}', name: 'deleteavis')]
public function deleteavis(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $avis = $entityManager->getRepository(Avis::class)->find($id);
       
       $form=$this->createForm(AvisType::class);
       $form->add('Delete',SubmitType::class);


        $entityManager->remove($avis);
        $entityManager->flush();

        return $this->redirectToRoute('avis', ['id' => $id]); 
    }

    #[Route('/avis/{id}', name: 'avis')]
    public function avis(Request $request, Security $security, int $id): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        // Récupérer l'avis de l'utilisateur (s'il existe)
        $avis = $this->getDoctrine()->getRepository(Avis::class)->findOneBy(['user' => $user]);

        // Créer le formulaire en fonction de l'existence de l'avis
        $form = $this->createForm(AvisType::class, $avis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assurez-vous que l'ID de l'utilisateur est correctement défini
            if (!$avis) {
                // Si l'avis n'existe pas, créez une nouvelle instance
                $avis = new Avis();
                $avis->setUser($user); // Associez l'avis à l'utilisateur
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($avis);
            $entityManager->flush();
        }

        return $this->render('calendar/avis.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'avis' => $avis,
            'id'=>$id,
        ]);
    }

}
