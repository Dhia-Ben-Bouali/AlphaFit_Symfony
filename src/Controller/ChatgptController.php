<?php

namespace App\Controller;

use OpenAI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
class ChatgptController extends AbstractController
{

    #[Route('/chatgpt', name: 'chat')]
    public function index( ? string $question, ? string $response): Response
    {
        return $this->render('chatgpt/index.html.twig', [
            'question' => $question,
            'response' => $response
        ]);
    }

    #[Route('/chat', name: 'send_chat', methods:"POST")]
    public function chat(Request $request): Response
    {
        $question=$request->request->get('text');

        //Implémentation du chat gpt

        $myApiKey = $_ENV['OPENAI_KEY'];


        $client = OpenAI::client($myApiKey);

        $result = $client->completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => $question,
            'max_tokens'=>2048
        ]);

        $response=$result->choices[0]->text;


        return $this->forward('App\Controller\ChatgptController::index', [

            'question' => $question,
            'response' => $response
        ]);
    }
}
