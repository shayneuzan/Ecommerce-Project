<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChatBotController
{
    private string $apiKey;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->apiKey = $_ENV['GOOGLE_AI_API_KEY'] ?? '';
        $this->basePath = $basePath;
    }

    public function message(Request $request, Response $response) : Response {
        $data = $request->getParsedBody();
        $userMessage = trim($data['message'] ?? '');

        if(!$userMessage){
            $response->getBody()->write(json_encode([
                'error' => 'No Message'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $systemPrompt = "Traventa's own AI assistant. Only answer questions related to travel and Traventa's services. If you don't know the answer, say you don't know. Be concise and helpful.";

        $apiResponse = $this->callAi($systemPrompt, $userMessage);

        if(!$apiResponse){ 
            $response->getBody()->write(json_encode([
                'error' => 'Failed to get response'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'reply' => $apiResponse
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function callAi(string $systemPrompt, string $userMessage) : ?string{

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . $this->apiKey;
    
        $body = json_encode([
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $userMessage]]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 300,
                'temperature'     => 0.7,
            ]

        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        if(!$result) return null;

        $data = json_decode($result, true);

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

}