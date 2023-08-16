<?php

namespace App\Core\Features;

use Discord\Builders\MessageBuilder;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Lichess
{
    public static function getLichessGameId($lichessUrl = '')
    {
        try {
            $strArray = explode("/", $lichessUrl);
            $gameId = $strArray[3];
            return $gameId;
        }catch(\Exception $e){
            Log::channel('db')->debug($e->getMessage());
        }
    }

    public function initLichessAPI($endpoint, $gameId)
    {
        try {
            $url = getenv('LICHESS_URL');
            $key = getenv('LICHESS_API_KEY');
            //initialize guzzle client
//            $client = new Client([
//                'base_uri' => $url,
//                'timeout' => 20,
//            ]);

            $client = new Client([
                'base_uri' => "https://lichess1.org/game/export/gif/white/",
                'timeout' => 20,
            ]);


            $response = $client->get("$gameId.gif", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'content-type' => 'image/gif',
                ],
//                'verify' => false,
//                'pgnInJson' => true,
//                'accuracy' => true,
            ]);

            $output = $response;

            $image = file_get_contents("https://lichess1.org/game/export/gif/white/$gameId.gif");

            $filename = 'chess' . Carbon::now()->toDateTimeString().'.gif';
            $put = file_put_contents($filename, $image);
            Log::channel('db')->debug(json_encode($put));
            $builder = MessageBuilder::new();

            $output = $builder->addFile($filename);

            return $output;

        }catch(\Exception $e){
                Log::channel('db')->debug($e->getMessage() . " " . $e->getLine() . " " . $e->getFile());
        }

    }

    public function exportGame($gameId)
    {
        $endpoint = "game/export/$gameId";
        $pgn = $this->initLichessAPI($endpoint, $gameId);
        return $pgn;
    }

}
