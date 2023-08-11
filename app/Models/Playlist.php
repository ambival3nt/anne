<?php

namespace App\Models;

use App\Models\Person;
use Carbon\Carbon;
use Discord\Discord;
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

Log::debug(json_encode("blaH BLAJKKLEG0/ " . $topUser . "/" . $message->author->avatar,128));
                    $personModel = Person::find($topUser->user_id) ?? null;

                    if($personModel) {
                        $person = $personModel->name;
                    }else{
                        $person = 'THANKS DISCORD';
                    }

                    $output[] = [
                        'rank' => $i,
                        'name' =>  $person,
                        'score' => $topUser->count,
                    ];
                    $i++;
                }

                return $message->reply('Most songs posted' . "\n\n" . json_encode($output,128));

            default:
                return '';
        }
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
//                ->settings([
//                'toStringFormat' => 'g:i:s a',
//            ]
//        );
            $embed = new Embed($discord);
            $embed->setTitle(("$i. " . $item['artist'] . " - " . $item['name']));
            $embed->setDescription('' . $personName . ' @ '. $timeString . ' -=- '. $item['duration'] . "                   ");
//            $embed->setDescription('' . $personName . ' @ '. $timeString . ' -=- ' . 'on ' . $item['source']  . " -=- ". $item['duration']);
            $embed->setURL($url ?? null);
//            $embed->setAuthor("$i. $personName @ $timeString" ?? "");
            $embed->setColor($color);
            $embed->setThumbnail($item['thumbnail']);
//            $embed->setFooter('by ' . $personName . ' @ '. $timeString);

            return $embed;

        }catch(\Exception $e){
            Log::debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
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

        elseif (stripos($url, 'spotify'
            || stripos($url, 'open.spotify.com')
            || stripos($url, 'spoti.fi')
            || stripos($url, 'spotify.app.goo.gl'))) {
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

                    $embedArray = [
                        'embeds'=>[],
                    ];

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
            Log::debug($e->getMessage());
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
    protected function person(){
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

}
