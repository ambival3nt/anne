<?php

namespace App\Core\Spotify;

use Discord\Http\Drivers\Guzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class GetAPIToken
{
    //get Spotify bearer token
    public function get()
    {
        $spotifyAuth = $this->getCredentials();


        //initialize guzzle client
        $client = new Client([
            'base_uri' => 'https://accounts.spotify.com/',
            'timeout' => 20,
        ]);

        //build request object
        $response = $client->request('POST', 'api/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'verify' => false,
            'form_params' => [
                'grant_type'=>'client_credentials',
                'client_id'=>$spotifyAuth['id'],
                'client_secret'=>$spotifyAuth['secret']
            ],

        ]);

        return $response;
    }

    // Just grabs the env stuff
    private function getCredentials() : array{
        return [
            'id' => getenv('SPOTIFY_ID'),
            'secret' => getenv('SPOTIFY_SECRET'),
        ];
    }

}
