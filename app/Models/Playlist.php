<?php

namespace App\Models;

use App\Models\Person;
use App\Services\ButtonService;
use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Playlist extends Model
{
    use HasFactory;

    protected $table = 'playlist';

    protected $fillable = [
        'url',
        'title',
        'type',
        'duration',
        'api_data',
        'artist',
        'user_id',
        'thumbnail',
        'whole_embed',
    ];

    public static function controller($discord, $message, mixed $arg)
    {
        switch($arg){
            case 'top':
                $playlist = new Playlist();
                $count = Playlist::select('user_id', DB::raw('COUNT(*) as count'))
                    ->groupBy('user_id')
                    ->orderBy('count', 'DESC')
                    ->get();

                $output = [];
                $i = 1;

                foreach($count as $topUser){

                    $personModel = Person::find($topUser->user_id) ?? null;

                    if($personModel) {
                        $person = $personModel->name;
                        $id = $personModel->id;
                        $avatar = $personModel->avatar ?? static::getUserAvatarUrl($discord, $personModel);
                    }else{
                        $person = 'THAN S DISCORD';
                        $id = null;
                        $avatar = null;
                    }


                    $output[] = [
                        'id' => $id,
                        'rank' => $i,
                        'name' =>  $person,
                        'score' => $topUser->count,
                        'avatar' => $avatar
                    ];

                    $i++;
                }

                $outputList = static::embedListBuilder($discord, $message, $output, 'playlist');

                return $message->channel->sendMessage($outputList->content, false, $outputList->embed);

            default:
                return '';
        }
    }

    /**
     * @param $discord
     * @param $userData
     * @return Embed|string
     */
    protected static function playlistSongPosts($discord, $userData): Embed|string
    {
        try {
            $embed = new Embed($discord);
            $embed->setTitle($userData['rank'] . ". " . ($userData['name']));
            $embed->setDescription($userData['score'] . " songs posted.");
            $embed->setThumbnail($userData['avatar']);
            $embed->setColor(self::getRankColor($userData['rank']));
            return $embed;

        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
        }
    }

    public static function getUserAvatarUrl($discord, $person)
    {
        $url = $discord->users->get('id0', $person->id)->avatar ?? null;
        $person->avatar = $url;
        $person->save();
        return $url;
    }


    /**
     * Get the playlist for specific date
     * @param $date
     * @param $discord
     * @return array|array[]
     */
    private function getListForDate($date=null, $discord){
        $listData = self::whereDate('created_at', $date)->get()->toArray();
        return $this->outputPlaylist($listData, $discord);
    }

    /**
     * Get the playlist for today
     * @param $listData
     * @param $discord
     * @return array
     */
    public function getListForToday($discord){
        $date = Carbon::today()->toDateString();
        return $this->getListForDate($date, $discord);
    }

    /**
     * Build a single song embed for output
     * @param $embedOutput
     * @param $discord
     * @param $item
     * @param $i
     * @param $personName
     * @param $url
     * @return Embed|string
     */
    public function buildEmbed($embedOutput, $discord, $item, $i, $personName, $url){
        try {

            $color = $this->getTrackColor($item['source']) ?? null;

            $timeString = Carbon::parse($item['timestamp'])->toDayDateTimeString();

            $embed = new Embed($discord);
            $embed->setTitle(("$i. " . $item['artist'] . " - " . $item['name']));
            $embed->setDescription('' . $personName . ' @ '. $timeString . ' -=- '. $item['duration'] . "                   ");
            $embed->setURL($url ?? null);
            $embed->setColor($color);
            $embed->setThumbnail($item['thumbnail']);


            return $embed;

        }catch(\Exception $e){
            Log::channel('db')->debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
        }
    }


    public static function buildTopListEmbed($discord, $message, $userData, $type='playlist'){



        switch($type){
            case 'playlist':
                return self::playlistSongPosts($discord, $userData);
        }


    }

    /**
     * Gets the source of the track from the url and sets it in the db.
     * @param $url
     * @param $id
     * @return string|null
     */
    public function getSource($url, $id)
    {
        if (stripos($url, 'youtube')
            || stripos($url, 'youtu.be')
            || stripos($url, 'youtube.app.goo.gl')
            || stripos($url, 'youtube.com')
            || stripos($url, 'youtube-nocookie.com')
            || stripos($url, 'youtube.googleapis.com')
        ) {


            $track = Playlist::find($id);
            $track->source = 'Youtube';
            $track->save();
            return 'Youtube';
        }

        elseif (stripos($url, 'spotify')
            || stripos($url, 'open.spotify.com')
            || stripos($url, 'spoti.fi')
            || stripos($url, 'spotify.app.goo.gl')
            || stripos($url, 'spotify.link')){
            $track = Playlist::find($id);
            $track->source = 'Spotify';
            $track->save();
            return 'Spotify';
        }

        elseif(stripos($url, 'soundcloud')
            || stripos($url, 'snd.sc')
            || stripos($url, 'on.soundcloud.com')
            || stripos($url, 'scdl')
            || stripos($url, 'soundcloud.app.goo.gl')
            || stripos($url, 'soundcloud.com')
            || stripos($url, 'soundcloud.app.link')){
            $track = Playlist::find($id);
            $track->source = 'Soundcloud';
            $track->save();
            return 'Soundcloud';
        }
        return null;
    }

    /**
     * Processes the playlist data from the db into embeds to output to discord.
     * @param $listData
     * @param $discord
     * @return array|array[]
     */
    private function outputPlaylist($listData, $discord){

      try {
          $output = [];
          //$listData is the raw playlist data, gathering it into a more usable format
          foreach ($listData as $track) {
              $output[] = [
                  'user_id' => $track['user_id'] ?? null,
                  'url' => $track['url'] ?? 'Bad URL data',
                  'name' => $track['title'] ?? 'Unknown',
                  'artist' => $track['artist'] ?? 'Unknown',
                  'timestamp' => Carbon::parse($track['created_at'])->toDateTimeString() ?? 'Unknown',
                  'thumbnail' => $track['thumbnail'] ?? null,
                  'duration'    => str_replace(['PT','S','M'],['','',':'], $track['duration']) ?? '-:--',
                  'source' => $track['source'] ?? $this->getSource($track['url'], $track['id']),
              ];
          }

          //counter for numbered list
          $i = 0;


                count($output) > 0 ? $embedArray['hasItems'] = true : $embedArray['hasItems'] = false;

                    //loop through the playlist data

                foreach ($output as $item) {
                    $i++;

                    //prepare one row of data for embed builder
                    $personName = 'Unknown';
                    if ($item['user_id']) {
                        $personName = Person::where('id', $item['user_id'])->first();
                        $personName = data_get($personName, 'name', 'Unknown');
                    }


                    $embedOutput = [
                        json_decode(json_encode(
                            [
                                'name' => $item['artist'] . " - " . $item['name'],
                                'value' => $item['url'],
                            ]
                        ))];

                    $footer = [
                        'text' => 'Posted by ' . $personName . ' on ' . Carbon::parse($item['timestamp'])->toDateString()
                    ];
                    //send this row to the embed builder, push to output array
                    $embedArray['embeds'][] = $this->buildEmbed($embedOutput, $discord, $item, $i, $personName, $item['url']);




            }
      }catch(\Exception $e){
            Log::channel('db')->debug($e->getMessage());
            $outputStr = "Uh yeah so it didn't work..." . $e->getMessage();
      }
        return $embedArray;
    }

    /**
     * Selects a color for the item based on the source of the track.
     * @param $source
     * @return string
     */
    private function getTrackColor($source)
    {
        switch($source){
            case 'Youtube':
                return '0xff0000';
            case 'Spotify':
                return '0x1DB954';
            case 'Soundcloud':
                return '0xFF5500';
            default:
                return '0x0B5394';
        }
    }

    /**
     * Relation to Person model
     * @return BelongsTo
     */
    public function person(){
        return $this->belongsTo(Person::class);
    }

    /**
     * Creates playlist for all of user's posts.
     * @param $userId
     * @param $discord
     * @return array|array[]
     */
    public function getPlaylistForUser($userId, $discord){
        $listData = $this->where('user_id', $userId)->get()->toArray();
        return $this->outputPlaylist($listData, $discord);
    }

    public static function embedListBuilder($discord, $message, array $output, $type='playlist')
    {

        //alright i'm going to try to shoehorn the paginator into this
        $totalItems = count($output);
        $totalPages = ceil($totalItems / 2);

        //i'm pretty terrified tbh
        $pageArray = [];
        $pageArray[] = null;
        $messagePageBuilder = new MessageBuilder();

        //nothing ever just like works, yknow?

        $c = 0;
        $pageIndex = 1;
        //surely this will though
        foreach($output as $person){

            $messagePageBuilder->addEmbed(static::buildTopListEmbed($discord, $message, $person, $type));

            if($c%2!=0 && $c != 0){
                $pageArray[] = $messagePageBuilder;
                $messagePageBuilder = new MessageBuilder();
            }

            $c++;

        }

        //so anyway here's wonderwall
             $thing = ButtonService::buildPaginator($totalPages, 1, $discord, $pageArray, 'topten');

        return $message->channel->sendMessage($thing);

    }

    public static function getRankColor($rank)
    {
        switch($rank){
            case 1:
                return '0xb45f06';
            case 2:
                return '0xbb6f1e';
            case 3:
                return '0xc37e37';
            case 4:
                return '0xca8f50';
            case 5:
                return '0xd29f69';
            case 6:
                return '0xd9af82';
            case 7:
                return '0xe1bf9b';
            case 8:
                return '0xe8cfb4';
            case 9:
                return '0xf0dfcd';
            case 10:
                return '0xf7efe6';
            default:
                return '0xffffff';
        }
    }

}
