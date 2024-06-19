<?php

namespace App\Controller;

use App\Entity\Article;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use DateTime;

class CatalogController extends AbstractController

{

    #[Route('/catalog/generate-pdf', name: ' catalog_generate_pdf')]

    public function generatePdfCatalog(Pdf $pdf, ArticleRepository $productRepository)
    {
        $produits = $productRepository->findAll();
        $dateCreationCatalogue = new DateTime('2024-02-27');

        $html = $this->renderView('catalog/catalog.html.twig', [
            'produits'=>$produits,
            'dateCreationCatalogue' => $dateCreationCatalogue,


            // Pass any data required by your template
        ]);

        $filename = 'example.pdf';

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
}