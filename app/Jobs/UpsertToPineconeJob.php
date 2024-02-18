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
     * @return array | void
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
                'base_uri' => getenv('PINECONE_URL').'/vectors/upsert',
            'timeout'  => 20,
        ]);

        //build request object
        $response = $client->request('POST', options: [
            'headers' => [
//                'Api-Key' => getenv('PINECONE_API_KEY'),
            //the fuck is that
                'Api-Key' => 'eyJhbGciOiJkaXIiLCJlbmMiOiJBMjU2R0NNIiwiaXNzIjoiaHR0cHM6Ly9sb2dpbi5waW5lY29uZS5pby8ifQ..pAeFbp7a-owrfS1U.wkFcTFP2vRTms_z2woh7-N4Sx5-Z74L4yUSORyvGrp889dkyen6vZxeE04pbl7kjr1kcxFKjYvbwDpSbE9o5LDbCOIilZVD6favyPBoX2hZIk934na1NLgu40-tJxse5v2tSjZ310q24m-yw1vfC0EOOLS7pSmyy3MTZjO-a6f3ApPVd26NLNduQUuhrwoP0AfYYnLguwoufjku6wfkj0JyW2G1e1fAC2ZFeoS5ZqfnAf_K4U_Q6ujDM1x20aU5xoQGx5DNPaAx15g2nrSNRIUCjnuY48Cd_LocrJK7tn1XHAPLRPU2j34veidbOaRbP-sXKJnconAAihdLpTUn3-IPCmddU_ZB87kBTKqW30RnOac-Bm4SR4OCaw1p4pdU7pdPHFxA7CZqrvkqq09euXLZYtVI_9dLo1LjI6kgAgUnM-xgt-cc0KqFxDd6fpBNWWUvxuhSxrsII-e_7c5eDumnrZVrLTbtPoFr7Tt5t-8eTI5HOIVOnQ823BatA5RpreV7jePjbEUH_GJR1EJiVPF5lmJa6bc0Yrs8Watn_Lv2hrLOIKMMsWOYVhLQl4FbXHwIgvAqGBnMqJ4scZPP9AHoNqK4UA0ZJn1FAkblAE84R_TAh0kh9BQrIQXX3Xde6XOrrkymEoy4jSPjuJgUM57_Ec1vD5F65-zCm6sYUe7lgnpJCcinOQfda75p-YvcB6Uzw179y_bkWNlYXHRPguoWUF9Xln2m4iMZLIDurvkU92hZcSnITun46NCDzA9ir5Be6HKVn1RU-cGDaZO0sArKc-Q5kYEan698fAMbVD2qji0PzQOoYlbBKap4mxIMrizwv_FuF2qD_cVwCcgiEDkPs5uESKz4g8EvmIXnFY838P9mGbPRIYxZRFEEei0naooGv_MQ8MYiX_yzmSOA8rCPYIqXtd3G3IAnBPLO-eS0nP6t2JGrOg-5t-u8QtCu2ajisI4mAh5J15-vFQEja2la0gioKxZRUqO7A7h_XwmCVcj8Epnp4Lq_1-ru3vgPQHbWVNVlfZFT-iSjG3Y6TGXTp1GVIEIz99NhxjFR7C45kOc8lc3rLBg9GPr8CjMlqvCwx93wtgG0pRSEKkat7yqScaf0YuyTSxk3SbGlQ_k-0eMm6P1FArCM13ckV8bHMchzqpf-LDPZubvEcOvByEOBrio0hObjiUQrUJGeLFDfkTxEiXnd3exbX8OoWJDg3mX6YsVL2IVtFWstp2ouQKH1Mk_fKMPeqBAin0NHGaTFZ-4cnL0yTYu0OGxYNqVPAnJxzoOqz50czi5DhSQLWlEkjhMO0UyLT1UDg5sojehiInwGF6zTmWbU8H3M6hkasgaQrM-6lyK3R6ZqA_sgwPAPfJqGTDYDiywjFPChBFvu0h9xIX2bmPhqt0iAxAqfJ1cB9wVgzVRRXoE0Y7iI8o_6pXQ6a23Tbc_Nj0TzEfkGu1AUgcn9uMtJlHogMzkv6KnIwnZVB2IfEVEMetPaRXYU0ZSq3E3_MMg7uuNVKOxIevMQ2WzZdl8H_zUHFP8cmxykDilhI0J2q2lKoEv31E9bv5vItN2I1D-QZPOZaWfuEd9kmdso-gc7doZ7bxeYLLC1F_YT8DNUkSUmzi123ItTNL0KPfYy6r7S7sD0sZGHGTjRLkBVvu9Pqwpj8pwu2Suwl8E1X4tq3PQS6q9JgHaBMNAo2L7W3VopMBAHs6-85lNQrGChhOe3GEGpnyc82O9AjSfYRaGivN4oHjFuJrMpCA0Io_tQ3BbljPVFkx8inK1pUtwPfa-5oSNYd-_TKCkxMoVIrSbU93xEqNtQXFVokmH5aQq2enW9fXflxygsAkilzzAta9HMJCvthN7SAkMOVuP-ucGX7ixr2KCYcKV-3pum3VDUDe8aAWM3U-uF753QYGLbsl3bZZPvPUiXc0Dx1-vk2fT-c7Ojh5n-CqOgrSmpgD1_-ZqEn15x0rprmusOM25y1E7-PR4cRiKsxB9jFMH4BhBshVIvpKMV601voITybd3uppZjDv6VhBWScQWkS67qNJ8aLMOFbplTYIQ212y4Q3aiLJkQgle1pkiZLiMOuHav9zqGnNEXqg7ru51CdNcIvewmHe8SF4_OSPhsFI4mfmqUViWLZzBiK2NLF4djfGNPYyrP-hdhLAZAfASwVbSOGwOsNFsIBG3UoihDemlrOZW-R7UZ9gjV6dDzkW55pB4M3EvWhiDTOlD4mEQcjjR3RzCLcNLlKukUmVdB8h7FYt93nnap1iIi5j2mwTzQjgCAHgUdJx3tDy0VEZPinXcOTaTijkZZRjcmT1R6lC2L3BxalVfJUDoIxjPq_pTosW0sLuZkw-a2ybhRdpK9oxvn9rBZBtXefWPxNCowW8ddlbAumYtgCtSzhg6oKcKVAjVjXIu3gnUEMHXZrRhAF8eBHTZeBOkUa3L4JzskZv_I5E5aMLtz63Fw4mmnb6ajVyVBxkDXbd8W1scQNHy6uGfL32v3wIL28vD0dnP9u3r2NTEfwrI-9-qFS8iTf3r9Vg2mQcYiPLihSra-wub_rKeyya0cLtQ2KEe5CQK8SCPZMaS7QD576rQpjWtuRcP3ow1B2GrY_gma_fC0in9E9sN0115Ya5-gdXSltQDoE8XLI23w8UoMruMfaxiHnce0X-4jEWNaFjNeiAgDXPtEAzesX7_nNuS_pHSxJqlWr2E84LP9U0IxU0st5ivGhFZg4Nnb2aIyxZBgG4y87sDtHE039yS5LaKWu753sz_tEQy_P5ZijzDQ1vFOWBouO0pMSbV_X_LzG4cBHDH7MPhBNwEdUHSvgeq7jh1F5Nu8x55a9QJM7ysSDEAwI8azVd-xW3bxhpqyVWENgRRDQYi9Y2XPFdj3HNS7iJh2pxbQ8PsW4xrSoPVKMsFuhkROQAzdXcH9mAuxEF_ari2LiFqeQF9fpQfwGRO4c4-xRSJ5Sf23vpMnFtE3G174zE-ow0VJ3aA.4z0RVndeOCmBOKajU7YKnA',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'verify' => false,
            'json' => [

                //vector, and id are required, metadata is not but is used for filtering
                'vectors' => [

                    //user
                    [
                        'values' => $vector,
                        'metadata' => [
                            'discord_user_id' => (string)$discordUserId,
                            'anne' => false,
                            'dateTime' => $dateTime,
                        ],
                        'id' => $id
                    ],

                    //anne
                    [
                        'values' => $anneEmbed,
                        'metadata' => [
                            'discord_user_id' => -1,
                            'anne' => true,
                            'dateTime' => $dateTime,
                        ],
                        'id' => "anne-$id"
                    ]
                ],

                //namespace is also optional
                'namespace' => '',
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
