<?php

namespace App\Models;

use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Person;
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

    private function getListForDate($date=null, $discord){
        $listData = self::whereDate('created_at', $date)->get()->toArray();
        return $this->outputPlaylist($listData, $discord);
    }

    public function getListForToday($discord){
        $date = Carbon::today()->toDateString();
        return $this->getListForDate($date, $discord);
    }

    //Builds a single song embed for output
    public function buildEmbed($embedOutput, $discord, $item, $i, $personName, $url){
        try {

            $color = $this->getTrackColor($item['source']) ?? null;

            $timeString = Carbon::parse($item['timestamp'])->settings([
                'toStringFormat' => 'g:i:s a',
            ]);
            $embed = new Embed($discord);
            $embed->setTitle(("$i. " . $item['artist'] . " - " . $item['name']));
            $embed->setDescription('' . $personName . ' @ '. $timeString . ' -=- ' . 'on ' . $item['source']  . " -=- ". $item['duration']);
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

    public function getSource($url, $id)
    {
        if (stripos($url, 'youtube') || stripos($url, 'youtu.be')) {
            $track = Playlist::find($id);
            $track->source = 'Youtube';
            $track->save();
            return 'Youtube';
        } elseif (stripos($url, 'spotify')) {
            $track = Playlist::find($id);
            $track->source = 'Spotify';
            $track->save();
            return 'Spotify';
        }
        return null;
    }


    //this function processes the playlist data from the db into embeds to output to discord
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

}
