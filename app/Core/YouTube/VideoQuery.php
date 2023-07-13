<?php

namespace App\Core\YouTube;

use Google\Service\YouTube;
use Google;
use Google_Client;
use Illuminate\Support\Facades\Http;

class VideoQuery
{

    public function getData($videoId) : array
    {

        $res = Http::get('https://www.googleapis.com/youtube/v3/videos',

            [
                'key' => getenv('YOUTUBE_API_KEY'),
                'id' => $videoId,
                'part' => 'snippet,contentDetails',
                ])->body();
        $res = json_decode($res);

        $output = [
            'name'=>$res->items[0]->snippet->title,
            'artist'=>$res->items[0]->snippet->channelTitle,
            'image'=>$res->items[0]->snippet->thumbnails->default->url,
            'duration'=>$res->items[0]->contentDetails->duration,
            'url'=>$videoId,
            'type'=>$res->items[0]->kind,
        ];

        return $output;
    }

//    public function getData($videoId)
//    {
//        $client = new Google_Client();
//        $client->setApplicationName(getenv('YOUTUBE_APP_NAME'));
//        $client->setScopes([
//            'https://www.googleapis.com/auth/youtube.readonly',
//        ]);
//
//        $client->setke
////        >setAuthConfig(base_path('anne-yt-discord.json'));
//
//        $client->setAccessType('offline');
////
////// Request authorization from the user.
////        $authUrl = $client->createAuthUrl();
////        printf("Open this link in your browser:\n%s\n", $authUrl);
////        print('Enter verification code: ');
////        $authCode = trim(fgets(STDIN));
//
//// Exchange authorization code for an access token.
////        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
////        $client->setAccessToken($accessToken);
//
//// Define service object for making API requests.
//        $service = new YouTube;
//        $service->
//
//        $response = $service->channels->listChannels('');
//      return json_encode($response,128);
//    }

}
