<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Abonnement;
use App\Entity\Suivi;
use App\Entity\User;
use App\Repository\AbonnementRepository;
use App\Repository\SuiviRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;

class ListController extends AbstractController

{

   
    #[Route('/list', name: 'list')]
    public function list(Request $request, AbonnementRepository $abonnementRepo, PaginatorInterface $paginator): Response
    {
        $query = $abonnementRepo->createQueryBuilder('a')
            ->getQuery();
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8 // Nombre d'éléments par page
        );
    
        return $this->render('list/index.html.twig', [
            'abonnements' => $pagination,
        ]);
    }

    #[Route('/indexadmin/{id}', name: 'indexadmin')]

    public function indexadmin(ManagerRegistry $doctrine,int $id)

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

        return $this->render('list/planing.html.twig',[
            'data' => $data,
            'id' => $id,
        ]);
    }

    #[Route('/Admindetails/{id}', name: 'Admindetails')]
    public function Admindetails(int $id,SuiviRepository $suivirepo): Response
    {   $details=$suivirepo->find($id);
        dump($details);
        return $this->render('list/Admindetails.html.twig', [
            'details' => $details,
            'id'=>$id,
        ]);
    }


   

    #[Route('/admin', name: 'admin')]
    public function admin() : Response

    {   
       
        return $this->render('baseback.html.twig');
            
    }


    #[Route('/excel', name: 'excel')]
    public function excel(): Response
    
    {
            // Récupérez vos données Abonnement (remplacez cela par votre propre méthode pour obtenir vos données)
            $abonnements = $this->getDoctrine()->getRepository(Abonnement::class)->findAll();
    
            // Créez une instance de PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // Entête du tableau Excel
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Nom du Pack');
            $sheet->setCellValue('C1', 'Nom du Client');
            $sheet->setCellValue('D1', 'Nom du Coach');
            $sheet->setCellValue('E1', 'Nom du Nutri');
    
            // Remplissez les données
            $row = 2;
            foreach ($abonnements as $abonnement) {
                $sheet->setCellValue('A' . $row, $abonnement->getId());
                $sheet->setCellValue('B' . $row, $abonnement->getNpack()->getNom()); // Assuming "Nom" is the name property of Pack entity
                $sheet->setCellValue('C' . $row, $abonnement->getClient()->getNom()); // Assuming "Nom" is the name property of User entity
                $sheet->setCellValue('D' . $row, $abonnement->getCoach() ? $abonnement->getCoach()->getNom() : ''); // Assuming "Nom" is the name property of User entity
                $sheet->setCellValue('E' . $row, $abonnement->getNutri() ? $abonnement->getNutri()->getNom() : ''); // Assuming "Nom" is the name property of User entity
                $row++;
            }
    
            // Créez un objet Writer pour sauvegarder le fichier
            $writer = new Xlsx($spreadsheet);
    
            // Enregistrez le fichier Excel dans le dossier public
            $filename = 'export.xlsx';
            $path = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
            $writer->save($path);
            
    
            // Retournez une réponse pour le téléchargement du fichier
            return $this->file($path, $filename);
        
    }


    #[Route('/search_admin', name: 'search_admin')]
    public function search_admin(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
        $packName = $request->query->get('packName');
        $page = $request->query->getInt('page', 1); // Page par défaut est 1
        $limit = 8; // Nombre d'éléments par page
    
        $abonnements = $doctrine->getManager()->getRepository(Abonnement::class)->searchByPackName($packName);
    
        $pagination = $paginator->paginate(
            $abonnements, // Requête à paginer
            $page, // Numéro de la page
            $limit // Nombre d'éléments par page
        );
    
        return $this->render('list/index.html.twig', [
            'abonnements' => $pagination,
        ]);
    }

    #[Route('/avis_admin/{id}', name: 'avis_admin')]
    public function avis_admin(int $id): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $avis = $user->getAvis();

        return $this->render('list/avis.html.twig', [
            'user' => $user,
            'avis' => $avis,
        ]);
    }
}

