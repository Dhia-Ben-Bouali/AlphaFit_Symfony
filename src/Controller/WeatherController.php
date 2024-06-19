<?php

namespace App\Controller;

use App\Service\OpenWeatherMapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class WeatherController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }



    /**
     * @Route("/weather", name="weather")
     */
    public function index(OpenWeatherMapService $weatherService): Response
    {
        // Replace 'Paris' with the specific location of the user

        $location = $this->security->getUser()->getAdresse();
        // Set your OpenWeatherMap API key directly here
        $apiKey = 'eae938e18ca3c4e4088acca3fdf35bfa';

        // Get weather information
        $weatherData = $weatherService->getWeather($location, $apiKey);

        // Render the response with weather data
        return $this->render('weather/index.html.twig', [
            'location' => $location,
            'weather' => $weatherData,
        ]);
    }
}
