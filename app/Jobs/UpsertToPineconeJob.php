<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use GuzzleHttp\Client;

class UpsertToPineconeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    private $vector;
    private $id;
    private $discordUserId;
    private $anneEmbed;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vector, $id,$discordUserId,$anneEmbed)
    {
        $this->vector=$vector;
        $this->id=$id;
        $this->discordUserId=$discordUserId;
        $this->anneEmbed=$anneEmbed;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $vector=$this->vector;
        $id=$this->id;
        $discordUserId=$this->discordUserId;
        $anneEmbed=$this->anneEmbed;


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
}
