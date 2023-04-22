<?php


namespace App\Core\GooseAI;

use http\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;


class GooseAICore
{
    public function gooseInit($promptWithPreloads)
    {
        $engineId = 'gpt-neo-2-7b';


        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://api.goose.ai/v1/engines/'.$engineId .'/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . getenv('GOOSE_API_KEY')
            ],

            'json' => [
                'prompt' => $promptWithPreloads,
//                        'top_p' => .25,
                            'temperature' => 0.75,
                            'max_tokens' => 600,
                            'stop' => [
                                '-----',
                                '<|endoftext|>',
                            ],
//                            'frequency_penalty' => 0.4,
//                            'presence_penalty' => 1.2,
//                            'best_of' => 2,
                            'n' => 1,
            ]
        ]);
        Log::debug($response->getBody());
//        Log::debug(json_decode($response->getBody()->read(42621))->choices[0]->text);
        return $response->getBody();
    }
    }
