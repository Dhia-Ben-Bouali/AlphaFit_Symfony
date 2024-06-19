<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenWeatherMapService
{ private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getWeather(string $location, string $apiKey): array
    {
        $response = $this->httpClient->request('GET', 'http://api.openweathermap.org/data/2.5/weather', [
            'query' => [
                'q' => $location,
                'appid' => $apiKey,
            ],
        ]);

        // Handle the response from the OpenWeatherMap API
        $weatherData = $response->toArray();

        return $weatherData;
    }
}