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
        $engineId = 'convo-6b';


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
                            'frequency_penalty' => 1,
                            'presence_penalty' => 1,
//                            'best_of' => 2,
                            'n' => 1,
            ]
        ]);

        $json =$response->getBody()->getContents();
        $jsonDecoded = json_decode($json);
        Log::debug(json_encode($jsonDecoded, 128));


//        Log::debug(json_decode($response->getBody()->read(42621))->choices[0]->text);
        return $jsonDecoded->choices[0]->text;
    }
    }
