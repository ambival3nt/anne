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
                if (stripos($url, 'spotify')
                || (stripos($url, 'spotify.link')
                || stripos($url, 'open.spotify.com')
                || stripos($url, 'spotify.app.goo.gl'))
                ){
                    $playlist = self::parseSpotifyData($message, $url);
                }


                //ok
                if (stripos($url, 'youtube')
                    || stripos($url, 'youtu.be')
                    || stripos($url, 'youtube.app.goo.gl')) {

                    self::parseYoutubeData($message, $url);
                }

                if(stripos($url, 'soundcloud') || stripos($url, 'snd.sc')
                    || stripos($url, 'on.soundcloud.com')
                    || stripos($url, 'scdl')
                    || stripos($url, 'soundcloud.app.goo.gl')
                    || stripos($url, 'soundcloud.com')
                    || stripos($url, 'soundcloud.app.link')) {
                    Log::channel('db')->debug('soundcloud link detected');
                    self::parseSoundcloudData($message, $url);
                }

                return true;


            } catch (\Exception $e) {
                Log::channel('db')->debug($e->getMessage());
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

        $songId = null;

      $youtube = new VideoQuery();
        if (stripos($url, '?feature=shared')) {
            $offset = strrpos($url, '/') + 1;
            $length = strrpos($url, '?feature=shared') - $offset;
            $songId = substr($url, $offset, $length);
        } elseif (str_contains($url, 'youtube.com') && !$songId) {
            $songId = substr($url, strrpos($url, '/watch?v=') + 9, 11);
        } elseif (str_contains($url, 'youtu.be') && !$songId) {
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

    //this shit is ridiculous
    private static function parseSoundcloudData(Message $message, string $url)
    {

        sleep(2);
        Log::channel('db')->debug(json_encode($message->embeds));
        $embed = json_decode(json_encode($message->embeds))[0] ?? null;
        if(!$embed) {
        $embed = data_get($message->embeds, '*', null)[0] ?? null;
        }
        if(!$embed){
            $embed = data_get($message->embeds, '0', null) ?? null;
        }

        $soundcloudData = [
            'title'=>data_get($embed, 'title', 'Unknown'),
            'artist'=>data_get($embed, 'author.name', 'Unknown'),
            'thumbnail'=>data_get($embed, 'thumbnail.url', null),
        ];

        Log::channel('db')->debug($soundcloudData);

        $playlist = new \App\Models\Playlist();
        $playlist->url = $url;
        $playlist->user_id = $message->author->id;
        $playlist->type = $soundcloudData['type'] ?? null;
        $playlist->title = $soundcloudData['title'] ?? 'Unknown';
        $playlist->artist = $soundcloudData['artist'] ?? 'Unknown';
        $playlist->duration = '-:--';
        $playlist->thumbnail = $soundcloudData['thumbnail'] ?? null;
        $playlist->source = 'Soundcloud';
        $playlist->save();


        return $playlist;

    }


}
