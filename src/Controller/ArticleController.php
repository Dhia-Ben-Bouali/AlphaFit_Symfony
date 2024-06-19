<?php

namespace App\Controller;
use App\Form\SearchRefType;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'list_article')]
    public function index(Request $request, ArticleRepository $arrepo,PaginatorInterface $paginator): Response
    {
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
        $pagination = $paginator->paginate(
            $articles, // Requête contenant les données à paginer
            $request->query->getInt('page', 1), // Numéro de page
            4 // Nombre d'éléments par page

        );
        $currentPage = $pagination->getCurrentPageNumber();




        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'formC' => $form->createView(),
            'pagination' => $pagination,
            'currentPage' => $currentPage,



        ]);
    }


    public function addArticle(EntityManagerInterface $em, Request $request): Response
    {
        $article = new Article;
        // Création du formulaire
        $form = $this->createForm(ArticleType::class, $article);
        $form->add('Ajouter', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                // Utilisation du nom original du fichier
                $fileName = $file->getClientOriginalName(); // Obtient le nom original du fichier
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $article->setImage($fileName);
            }
            $em->persist($article);
            $em->flush(); // pour confirmer la suppression
            return $this->redirectToRoute('list_article');
        }
        return $this->render('article/addarticle.html.twig', [
            'formB' => $form->createView(),
        ]);
    }

    #[Route('/deletearticle/{id}', name: 'del_article')]
    public function deleteArticle( $id,ArticleRepository $repo,EntityManagerInterface $em): Response
    { $article=$repo->find($id);
        $em->remove($article);//remove bch nekhdhouha mil manager
        $em->flush();//pour confirmer supprission

        return $this->redirectToRoute('list_article');
    }
    public function updateArticle($id, EntityManagerInterface $em, Request $request, ArticleRepository $repo): Response
    {
        $article = $repo->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $form->add('Modifier', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();

            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
                $article->setImage($fileName);
            }

            $em->flush();
            return $this->redirectToRoute('list_article');
        }

        return $this->render('article/addarticle.html.twig', [
            'formB' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show($id, ArticleRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }


        return $this->render('article/show.html.twig', [
            'product' => $product,

        ]);
    }
}
