<?php

namespace App\Service;
use OpenAI\Client;

class OpenAIService
{
    private $openAI;

    public function __construct(string $apiKey)
    {
        // Instantiate the OpenAI\Client directly
        $this->openAI = new Client([
            'key' => $apiKey,
            // Other configuration options if needed
        ]);
    }

    public function getClient(): Client
    {
        return $this->openAI;
    }
}