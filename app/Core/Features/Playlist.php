<?php

namespace App\Core\Features;

use App\Core\Spotify\QueryAPI;
use App\Core\YouTube\VideoQuery;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Facades\Log;

class Playlist
{

    /**
     * @param Message $message
     */
    public static function grabMusicLinkUrl(Message $message)
    {
        $url = null;

        $strArray = explode(' ', $message->content);
        foreach ($strArray as $item) {
            if (str_starts_with($item, 'http')) {
                $url = $item;
            }
        };


        if ($url) {
            try {

                //turns out you shouldn't try to take the output from a 3rd party to populate your 4th party output. Huh.
                if (stripos($url, 'spotify')) {
                    $playlist = self::parseSpotifyData($message, $url);
                }


                //ok
                if (stripos($url, 'youtube') || stripos($url, 'youtu.be')) {
                    self::parseYoutubeData($message, $url);
                }

                return true;


            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
            return false;
        }
        return false;
    }

    /**
     * @param Message $message
     * @param string $url
     * @return \App\Models\Playlist
     */
    protected static function parseSpotifyData(Message $message, string $url): \App\Models\Playlist
    {
        $spotify = new QueryAPI();

        $songId = substr($url, strrpos($url, '/') + 1);

        $spotifyData = $spotify->getTrackMetadata($songId);

        $spotifyArray = $spotify->parseResponseForEmbed($spotifyData, $message);


        $playlist = new \App\Models\Playlist();
        $playlist->url = $url;
        $playlist->user_id = $message->author->id;
        $playlist->type = $spotifyArray['type'] ?? null;
        $playlist->title = $spotifyArray['name'] ?? 'Unknown';
        $playlist->artist = $spotifyArray['artist'] ?? 'Unknown';
        $playlist->duration = $spotifyArray['duration'] ?? 'Unknown';
        $playlist->thumbnail = $spotifyArray['image'] ?? null;
        $playlist->source = 'Spotify';
        $playlist->save();
        return $playlist;
    }

    /**
     * @param Message $message
     * @param string $url
     * @return void
     */
    protected static function parseYoutubeData(Message $message, string $url): \App\Models\Playlist
    {


      $youtube = new VideoQuery();
if(str_contains($url, 'youtube.com')) {
    $songId = substr($url, strrpos($url, '/watch?v=') + 9);
}elseif(str_contains($url, 'youtu.be')) {
    $songId = substr($url, strrpos($url, '/') + 1);
}

        $youtubeData = $youtube->getData($songId);



        $playlist = new \App\Models\Playlist();
        $playlist->url = $url;
        $playlist->user_id = $message->author->id;
        $playlist->type = $youtubeData['type'] ?? null;
        $playlist->title = $youtubeData['name'] ?? 'Unknown';
        $playlist->artist = $youtubeData['artist'] ?? 'Unknown';
        $playlist->duration = $youtubeData['duration'] ?? 'Unknown';
        $playlist->thumbnail = $youtubeData['image'] ?? null;
        $playlist->source = 'Youtube';
        $playlist->save();
        return $playlist;
    }


}
