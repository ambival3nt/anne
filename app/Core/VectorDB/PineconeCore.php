<?php

namespace App\Core\VectorDB;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use App\Core\OpenAI\OpenAICore;
use Psr\Http\Message\ResponseInterface;

class PineconeCore
{
    //Inserts a vector embedded via OpenAI API into Pinecone
    /**
     * @param $vector
     * @param $id
     * @param $discordUserId
     * @param $anneEmbed
     * @return array|ResponseInterface
     * @throws GuzzleException
     */
    public function upsert($vector, $id, $discordUserId, $anneEmbed) : array | ResponseInterface
    {

        $dateTime = Carbon::now()->toDateTimeString();

        //initialize guzzle client
        $client = new Client([
            'base_uri' => getenv('PINECONE_URL'),
            'timeout'  => 20,
        ]);

        //build request object
        $response = $client->request('POST', 'vectors/upsert', [
            'headers' => [
                'Api-Key' => getenv('PINECONE_API_KEY'),
                'Content-Type' => 'application/json'
            ],
            'verify' => false,
            'json' => [

                //vector, and id are required, metadata is not but is used for filtering
                'vectors'=>[

                    //user
                    [
                        'values'=>$vector,
                        'metadata'=> [
                            'discord_user_id' => (string)$discordUserId,
                            'anne' => false,
                            'dateTime' => $dateTime,
                        ],
                        'id'=>$id
                    ],

                    //anne
                    [
                        'values'=>$anneEmbed,
                        'metadata'=> [
                            'discord_user_id' => -1,
                            'anne' => true,
                            'dateTime' => $dateTime,
                        ],
                        'id'=>"anne-$id"
                    ]
                ],

                //namespace is also optional
                'namespace'=>'',
            ],
        ]);

        //if it is fuckered, wellp
        if (!$response->getStatusCode() == 200) {
            return [
                'success'   => false,
                'message' => $response->getStatusCode() . " - " . $response->getReasonPhrase(),
                ];
        } else {
            return $response;
        }

    }

    /**
     * @param $queryVector
     * @return mixed[]
     * @throws GuzzleException
     * This is the main pinecone query function. It wants your query, pre-embedded into a vector.
     */
    public function query($queryVector){
        $queryClient = new Client([
            'base_uri' => getenv('PINECONE_URL'),
            'timeout'  => 20,
        ]);

        $body = json_encode([
            //this will include every mf 1500 dimension vector... careful
            'includeValues'=>false,
            //includes any categories given to the  vector when it was upserted
            'includeMetadata'=>true,
            //the vector you're looking for similarities with by uploading
            "vector"=>$queryVector,
            //max result return
            "topK"=>10]);

        //build request object
        $response = $queryClient->post( '/query', [
            'headers' => [
                'Api-Key' => getenv('PINECONE_API_KEY'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
            'debug' => true,
        ]);

        //TODO: cmaaaaaaaaaaaaan
        //What a nightmare, right? I'm so sorry. It's an array return. I'm sorry.
        return collect(json_decode($response->getBody()->read(42621)))->except(['values'])->toArray();


    }

}
