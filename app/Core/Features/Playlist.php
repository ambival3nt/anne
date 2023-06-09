<?php

namespace App\Core\Features;

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
        foreach($strArray as $item) {
            if(str_starts_with($item, 'http')){
                $url = $item;
            }
        };

        Log::debug(json_encode($message->embeds[0]->title ?? null));

if($url) {
   try {
       $playlist = new \App\Models\Playlist();
       $embeds = $message->embeds[0] ?? null;
       $playlist->url = $url;
       $playlist->user_id = $message->author->id;
       $playlist->type = $embeds->type ?? null;
       $playlist->title = $embeds->title ?? 'Unknown';
       $playlist->artist = $embeds->author->name ?? 'Unknown';
       $playlist->duration = $embeds->fields[0]->value ?? 'Unknown';
       $playlist->api_data = $embeds->thumbnail ?? null;
       $playlist->save();
   }catch(\Exception $e) {
       Log::debug($e->getMessage());
   }
    return true;
}
return false;
    }


}
