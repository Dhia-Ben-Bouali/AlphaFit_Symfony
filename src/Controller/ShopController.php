<?php
namespace App\Controller;

use App\Form\SearchRefType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Endroid\QrCode\Encoding\Encoding;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer;
use Symfony\Component\Serializer\SerializerInterface;

class ShopController extends AbstractController
    {    #[Route('/shop', name: 'app_shop')]
        public function index(ArticleRepository $articleRepository, BuilderInterface $builder,Request $request): Response
        {
            // Récupérer tous les produits depuis la base de données
            $produits = $articleRepository->findAll();
            $searchLetter = $request->query->get('searchLetter');

    if ($searchLetter) {
        $produits = $articleRepository->findByFirstLetter($searchLetter); // Créez la méthode findByFirstLetter dans votre repository
    } else {
        $produits = $articleRepository->findAll();
    }

  
            // Tableau pour stocker les données du code QR associées à chaque produit
        
            return $this->render('shop/index.html.twig', [
                'produits' => $produits,

            ]);
        }
    
         
    #[Route('/dash', name: 'app_dash')]
    // Récupérer tous les produits depuis la base de données
public function list(Request $request, ArticleRepository $arrepo): Response {
        // Récupérer tous les produits depuis la base de données
        $form = $this->createForm(SearchRefType::class);
        $form->handleRequest($request);
    
        // Si le formulaire est soumis
        if ($form->isSubmitted()) {
            // Récupérer le nom de la catégorie du formulaire
            $nom = $form->get('nom')->getData();
    
            // Vérifier si le champ de recherche est vide
            if (!empty($nom)) {
                // Effectuer la recherche dans la base de données
                $articles = $arrepo->findByArticleName($nom);
            } else {
                // Si le champ de recherche est vide, afficher tous les articles
                $articles = $arrepo->findAll();
            }
        } else {
            // Si le formulaire n'est pas soumis, afficher tous les articles
            $articles = $arrepo->findAll();
        }
    
        return $this->render('shop/view.html.twig', [
            'articles' => $articles,
            'formC' => $form->createView()
        ]);
        
    
}
#[Route('/backk', name: 'backk')]
public function backk(CategorieRepository $categorieRepository, ArticleRepository $produitRepository,UserRepository $userRepository, SerializerInterface $serializer): Response
{
    // Récupérer toutes les catégories
    $categories = $categorieRepository->findAll();

    // Initialiser un tableau pour stocker les statistiques par catégorie
    $statistiquesParCategorie = [];

    // Calculer les statistiques pour chaque catégorie
    foreach ($categories as $categorie) {
        // Récupérer les produits pour la catégorie actuelle
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        // Calculer le nombre de produits dans la catégorie
        $nombreProduits = count($produits);

        // Stocker les statistiques dans le tableau
        $statistiquesParCategorie[] = [
            'categorie' => $categorie,
            'nombreProduits' => $nombreProduits,
        ];
    }
    $userStats = $userRepository->countUsersByRole();
    dump($userStats);

    $jsonData = $serializer->serialize($userStats, 'json');


    // Rendre la vue Twig avec les statistiques
    return $this->render('baseback.html.twig', [
        'statistiquesParCategorie' => $statistiquesParCategorie,   'userStats' => $jsonData,
    ]);
}

}