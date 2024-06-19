<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HomeController extends AbstractController
{
    #[Route('/logadmin', name:'logadmin')]
    public function logadmin(): Response
    {
        return $this->render('security/adminlogin.html.twig');
    }
    #[Route('/home', name:'home')]
    public function home(): Response
    {
        return $this->render('base.html.twig');
    }
    #[Route('/profileback', name:'profileback')]
    public function profileback(): Response
    {
        return $this->render('adminregister/profileback.html.twig');
    }
    #[Route('/back', name:'back')]
    public function back(UserRepository $userRepository, SerializerInterface $serializer): Response
    {
        $userStats = $userRepository->countUsersByRole();
        dump($userStats);

        $jsonData = $serializer->serialize($userStats, 'json');

        return $this->render('baseback.html.twig', [
            'userStats' => $jsonData,
        ]);
    }

}
