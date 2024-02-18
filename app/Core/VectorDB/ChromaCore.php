<?php

namespace App\Core\VectorDB;

use Carbon\Carbon;
use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Embeddings\OpenAIEmbeddingFunction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class ChromaCore
{
  public function initClient(){
      $chromaDB = ChromaDB::factory()
          ->withHost('https://shaggy-flowers-film.loca.lt');
      return $chromaDB;




  }


  //TODO: I need to run once when the bot initializes the first time, this needs a DB entry somewhere
  public function initCollection(){
      $embedder = new OpenAIEmbeddingFunction(getenv('OPENAI_API_KEY'));


       return $this->initClient()->connect(true)->createCollection(
           name: getenv('CHROMA_COLLECTION_NAME'),
           embeddingFunction: $embedder,
       );

  }




    /**
     * @param $chroma
     * @param $embedArray
     * @param $collectionName
     * @return array
     */
    public function upsert($vector, $id, $discordUserId, $anneEmbed, $userMessage, $anneMessage): array
    {
        $success=true;
//build array of anne and user vectors for upsertion
        $dateTime = Carbon::now()->toDateTimeString();


            $userMetadata =
                [
                    'discord_user_id' => (string)$discordUserId,
                    'type' => "user_message",
                    'anne' => "0",
                    'dateTime' => $dateTime,
                ];


                //anne//////////////////////////////////////////////

             $anneMetadata =
                        [
                        'discord_user_id' => "-1",
                        'type' => "anne_message",
                        'anne' => "1",
                        'dateTime' => $dateTime,
                    ];






      try {
          $chroma = $this->initClient()->connect(true);
        $embedding = new OpenAIEmbeddingFunction(getenv('OPENAI_API_KEY'));

          //get collection
          $collection = $chroma->getCollection(getenv('CHROMA_COLLECTION_NAME'), $embedding);

          //add passed in data to the collection (I think you can pass it all at once which means probably not but fuck it)
          $collection->add(ids:[(string)$id, (string)($id+1)],documents:[(string)$userMessage, (string)$anneMessage], metadatas: [$userMetadata, $anneMetadata]);
      }catch(Exception $e){
          $success=false;
          Log::debug('Exception thrown on L' . __LINE__ . ' in ' . __METHOD__ . ' in ' . __FILE__);
      }

//      Arr::forget($embedArray, 'vector');

        return [
            'success' => $success,
            'payload' => ['not what i wanted it to be'],
        ];

    }

    //query the vector DB w/ a message
    public function query($queryVector, $message=null)
    {
        $success = true;
        $result = [];

        try {

            //innit
            $chroma = $this->initClient();
            $queryResponse = [];

            //grab collection from env
            if ($chroma) {
                $collection = $chroma->connect(true)->getCollection(getenv('CHROMA_COLLECTION_NAME'));
                if ($collection) {
                    $queryResponse = $collection->query(

            //query vector passed in
                        queryEmbeddings: [
                            $queryVector
                        ],
                        nResults: 10,
                        include: ['documents','distances', 'metadatas','data'],
                    );
                }
            }

        } catch (\Exception $e) {
            \Log::debug('Exception thrown on L' . __LINE__ . ' in ' . __METHOD__ . ' in ' . __FILE__ . ': ' . $e->getMessage());
        }

        $result = [];
        $length = sizeof($queryResponse->documents[0]);


        for($i=0; $i<$length; $i++){
            $result[$i] = [
                'documents' => $queryResponse->documents[0][$i],
                'distances' => $queryResponse->distances[0][$i],
                'metadatas' => $queryResponse->metadatas[0][$i],
            ];
        }



        return $result;
    }

}
