<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CategorieController extends AbstractController
{
    
    #[Route('/categorie', name: 'list_categorie')]
    public function index(Request $request, CategorieRepository $catrepo): Response
    {
        // Récupère le paramètre de tri de la requête
        $sort = $request->query->get('sort', 'id'); 
        
        // Vérifier si le paramètre de tri est valide
        $validSorts = ['id','libelle']; // Seul le tri par libellé est valide
        if (!in_array($sort, $validSorts)) {
            throw $this->createNotFoundException('Invalid sorting parameter');
        }
    
        // Récupérer les catégories triées depuis le repository
        $categories = $catrepo->findBy([], [$sort => 'ASC']);
    
        // Passer les catégories triées au template Twig
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'currentSort' => $sort,
        ]);
    }

 
    #[Route('/adcategorie/add', name: 'add_categorie')]
    public function addCategorie(EntityManagerInterface $em,Request $request): Response
    {   $categorie=new Categorie;
        //formulaire
        $form=$this->createForm(CategorieType::class,$categorie);
        $form->add('Ajouter', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success'],
        ]);

        //louwil issm lformulaire w theni lvar li bch nhot feha les donnes li jeyin mil formulaire
        $form->handleRequest($request);
        //ki naml lformulaire bch yitadew donnes fi wisst request
        if ($form->isSubmitted()&& $form->isValid()){//ken ki namr formulaire titada lil persist
            $em->persist($categorie);
            $em->flush();//pour confirmer ajout
            return $this->redirectToRoute('list_categorie');//nhot name mtaa route

        }
        return $this->render('categorie/addcategorie.html.twig', [
            'formA'=>$form->createView(),
        ]);  
      }
      
      #[Route('/updatecategorie/update/{id}', name: 'up_categorie')]
      public function updateCategorie($id,EntityManagerInterface $em,Request $request,CategorieRepository $repo): Response
      {  
          //formulaire
          $categorie=$repo->find($id);
          $form=$this->createForm(CategorieType::class,$categorie);
          $form->add('Modifier', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success'],
        ]);
          //louwil issm lformulaire w theni lvar li bch nhot feha les donnes li jeyin mil formulaire
          $form->handleRequest($request);
          //ki naml lformulaire bch yitadew donnes fi wisst request
          if ($form->isSubmitted()&& $form->isValid()){//ken ki namr formulaire titada lil persist
              $em->flush();//pour confirmer supprission
              return $this->redirectToRoute('list_categorie');//nhot name mtaa route
  
          }
          return $this->render('categorie/addcategorie.html.twig', [
              'formA'=>$form->createView(),
          ]);  
        }
        #[Route('/deletecategorie/{id}', name: 'del_categorie')]
        public function deleteCategorie( $id,CategorieRepository $repo,EntityManagerInterface $em): Response
        { $categorie=$repo->find($id);
            $em->remove($categorie);//remove bch nekhdhouha mil manager
            $em->flush();//pour confirmer supprission
    
            return $this->redirectToRoute('list_categorie');//nhot name mtaa route
        }
}
