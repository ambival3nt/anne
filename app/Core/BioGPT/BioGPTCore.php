<?php

namespace App\Core\BioGPT;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Core\OpenAI\OpenAICore;

class BioGPTCore
{
    //queries bioGPT for a response
    public function query($prompt){

    //initialize guzzle client
        $client = new Client([
            'base_uri' => getenv('HUGGINGFACE_URL'),
            'timeout'  => 20,
        ]);

        $body = json_encode([
                'inputs'=>$prompt,
                'parameters'=>[
                        'return_full_text'=>false,
                    'early_stopping'=>true,
                    'max_length'=>500,
                    'min_length'=>100,
                    'num_return_sequences'=>1,
                    ]
                ]);
try {
    //build request object
    $response = $client->request('POST', getenv('BIOGPT_ENDPOINT'), [
        'headers' => [
            "Authorization" => "Bearer " . getenv('HUGGINGFACE_API_KEY'),
            'Content-Type' => 'application/json',
        ],
//            'verify' => false,
        'body' => $body,
    ]);

    //if it is fuckered, wellp
    if (!$response->getStatusCode() == 200) {
        return [
            'success' => false,
            'message' => $response->getStatusCode() . " - " . $response->getReasonPhrase(),
        ];
    } else {
        $output = $response->getBody()->getContents();
        Log::channel('db')->debug($output);
        $format = str_ireplace(`][{"generated_text". < / FREETEXT > < / TITLE > ▃ < ABSTRACT > < FREETEXT > '`, "", $output);
        return $format;
    }
} catch (\Exception $e) {
    return $e->getMessage();
}
    }

//    public function query($queryVector){
//        $queryClient = new Client([
//            'base_uri' => getenv('PINECONE_URL'),
//            'timeout'  => 20,
//        ]);
//
//        $body = json_encode([
//            'includeValues'=>false,
//            'includeMetadata'=>true,
//            "vector"=>$queryVector,
//            "topK"=>5]);
//
//        //build request object
//        $response = $queryClient->post( '/query', [
//            'headers' => [
//                'Api-Key' => getenv('PINECONE_API_KEY'),
//                'Accept' => 'application/json',
//                'Content-Type' => 'application/json',
//            ],
//            'body' => $body,
//            'debug' => true,
//        ]);
//
//        //TODO: cmaaaaaaaaaaaaan
//        //What a nightmare, right? I'm so sorry. It's an array return. I'm sorry.
//        return collect(json_decode($response->getBody()->read(42621)))->except(['values'])->toArray();
//
//
//    }

}
