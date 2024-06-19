<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Entity\User;
use App\Repository\AbonnementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Illuminate\Support\Facades\DB;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Core\Security;

class AbonementController extends AbstractController
{
    #[Route('/abonement', name: 'app_abo')]
    public function index(): Response
    {
        return $this->render('abonement/index.html.twig', [
            'controller_name' => 'AbonementController',
        ]);
    }

    #[Route('/Gestion_abonnement', name: 'app_abonement')]
    public function abonnement(Request $request, AbonnementRepository $abonnementRepository, PaginatorInterface $paginator): Response
    {
        $searchDate = $request->query->get('date');
        
    if ($searchDate !== null) {
        
        $searchDateTime = new \DateTime($searchDate);

        $reclamations = $abonnementRepository->findByDate($searchDateTime);
    } else {
        
        $reclamations = $abonnementRepository->findAll();
    }
    $pagination = $paginator->paginate(
        $reclamations,
        $request->query->getInt('page', 1),
        8
    );

    return $this->render('abonement/AbonnementA.html.twig', [
        'abo' => $pagination,
    ]);
    }

    #[Route('/affec', name: 'app_affectation')]
    public function affecterCoachNutri(AbonnementRepository $abonnementRepository, UserRepository $userRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $utilisateurs = $userRepository->findAll();
        $abonnementsSansCoachOuNutri = $abonnementRepository->findWithoutCoachOrNutri();
    
        foreach ($abonnementsSansCoachOuNutri as $abonnement) {
    
            $coachsDisponibles = [];
            $nutrisDisponibles = [];
    
            // Filtrer les utilisateurs disponibles pour chaque rôle
            foreach ($utilisateurs as $utilisateur) {
                if (in_array('ROLE_COACH', $utilisateur->getRoles())) {
                    $coachsDisponibles[] = $utilisateur;
                }
    
                if (in_array('ROLE_NUTRITIONIST', $utilisateur->getRoles())) {
                    $nutrisDisponibles[] = $utilisateur;
                }
            }
    
            $coach = $coachsDisponibles[array_rand($coachsDisponibles)];
            $nutri = $nutrisDisponibles[array_rand($nutrisDisponibles)];
    

            $abonnement->setCoach($coach);
            $abonnement->setNutri($nutri);
    
            $entityManager->persist($abonnement);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('app_abonement');
    }
    
    #[Route('/deletee/{id}', name: 'app_ab_delete')]
    public function deletee($id, AbonnementRepository $abonnementRepository)
    {
        $abonnement = $abonnementRepository->find($id);

        if (!$abonnement) {
            throw $this->createNotFoundException('Reclamation non trouvé');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($abonnement);
        $em->flush();
        $this->addFlash('success', 'Abonnement has been deleted successfully.');
        return $this->redirectToRoute('app_abonement');
    }

    #[Route('/Exp', name: 'app_ExpDate')]
    public function supprimerAbonnementsExpires( AbonnementRepository $abonnementRepository)
    {


        $abonnementsExpires = $abonnementRepository->findAbonnementsExpires();

        foreach ($abonnementsExpires as $abonnement) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($abonnement);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Expired Abonnement has been deleted successfully.');
        return $this->redirectToRoute('app_abonement');
    }

    #[Route('/abonnementspdf', name: 'app_pdf')]
    public function generatePdf(): Response
    {
        // Fetch subscription data
        $aboData = $this->getDoctrine()->getRepository(Abonnement::class)->findAll();

        // Render Twig template to HTML
        $html = $this->renderView('pdf/abonnement_list.html.twig', [
            'abo' => $aboData,
        ]);
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/user_affec', name: 'user_affec')]
    public function dashboard(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('home');
        }


        $roles = $user->getRoles();
        if (!in_array('ROLE_COACH', $roles) && !in_array('ROLE_NUTRITIONIST', $roles)) {
            return $this->redirectToRoute('home');
        }


        $entityManager = $this->getDoctrine()->getManager();
        $abonnements = $entityManager->getRepository(Abonnement::class)->findBy(['coach' => $user->getId()]);

        // Render the dashboard template with the fetched users
        return $this->render('abonement/user_affec.html.twig', [
            'abonnements' => $abonnements,
        ]);
    }
}
