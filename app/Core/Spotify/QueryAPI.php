<?php

namespace App\Core\Spotify;

use GuzzleHttp\Client;

class QueryAPI
{

//This grabs the token and queries spotify for the track data
    public function getTrackMetadata($trackId){
    $token = new getAPIToken();
    $token = $token->get()->getBody();

    $tokenObj = json_decode($token);

        $client = new Client([
            'base_uri' => 'https://api.spotify.com/v1/tracks/',
            'timeout' => 20,
        ]);

        //build request object
        $response = $client->request('GET', "$trackId", [
            'headers' => [
                'Authorization' =>'Bearer ' . $tokenObj->access_token,
            ],
        ]);
        $data = json_decode($response->getBody());
        return $data;

    }


    //This takes a response from the spotify API and parses it into an array we can use to build an embed
    public function parseResponseForEmbed($apiResponse, $message = null) : array{

        //Stringify artists
        $artists = "";
        $artistCount = 0;

        foreach($apiResponse->artists as $artist){


            $artistCount++;
            $artists .= $artist->name;


            if(count($apiResponse->artists)>$artistCount){
                $artists .= ', ';
            }
        }


        //convert duration (who uses milliseconds seriously)
        $input = $apiResponse->duration_ms;
        $uSec = $input % 1000;
        $input = floor($input / 1000);

        $seconds = $input % 60;
        $input = floor($input / 60);

        $minutes = $input % 60;
        $input = floor($input / 60);

        $seconds = $seconds > 9 ? (string)$seconds : "0" . $seconds;

        $songDuration = $minutes .":" . $seconds;



        //Build Array
        $output = [
            'name'=>$apiResponse->name,
            'artist'=>$artists,
            'url'=>$apiResponse->external_urls->spotify,
            'image'=>$apiResponse->album->images[count($apiResponse->album->images)-1]->url,
            'duration'=>$songDuration,
            'type'=>$apiResponse->type,
        ];

        return $output;
    }
}
