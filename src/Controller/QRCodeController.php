<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Endroid\QrCode\Builder\BuilderInterface;

class QRCodeController extends AbstractController
{
  
       
    public function generateQrCode(BuilderInterface $customQrCodeBuilder): Response
    {
        // Logique de construction du code QR
        $result = $customQrCodeBuilder
            ->size(400)
            ->margin(20)
            ->build();

        // Création de la réponse QrCodeResponse
        $response = new QrCodeResponse($result);

        return $response;
    }}