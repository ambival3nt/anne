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
    ];

    private function getListForDate($date=null, $discord){
        $listData = self::whereDate('created_at', $date)->get()->toArray();
        return $this->outputPlaylist($listData, $discord);
    }

    public function getListForToday($discord){
        $date = Carbon::today()->toDateString();
        return $this->getListForDate($date, $discord);
    }

    public function buildEmbed($embedOutput, $discord){
        try {
            $embed = new Embed($discord);
            $embed->fields = $embedOutput;
            $embed->description = "Playlist for: " . Carbon::today()->toDateString();
            $embed->title = "Playlist";
            $embed->color = 0x00FF00;
            $embed->footer = ['text' => 'Playlist for: ' . Carbon::today()->toDateString()];
            return $embed;
        }catch(\Exception $e){
            Log::debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
        }
    }

    private function outputPlaylist($listData, $discord){

      try {
          $output = [];
          foreach ($listData as $item) {
              Log::debug(json_encode($item));
              $output[] = [
                  'user_id' => $item['user_id'] ?? null,
                  'url' => $item['url'] ?? 'Bad URL data',
                  'name' => $item['title'] ?? 'Unknown',
                  'artist' => $item['artist'] ?? 'Unknown',
                  'timestamp' => Carbon::parse($item['created_at'])->toDateTimeString() ?? 'Unknown',
              ];

          }
          $outputStr = "```"
          . "\nPlaylist for: " . Carbon::today()->toDateString() . "\n-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n";
          $i = 0;
          foreach ($output as $item) {
              $personName = 'Unknown';
              if ($item['user_id']) {
                  $personName = Person::where('id', $item['user_id'])->first();
                  $personName = data_get($personName, 'name', 'Unknown');
              }
              $embedOutput[] = json_decode(json_encode([
                  'name' => $personName,
                  'value' => $item['url'],
              ]));

              Log::debug(json_encode($embedOutput));

//              $outputStr .= ($i+1) . ". " . $personName . " - " . $item['timestamp'] . "\n";
//              $outputStr .= $item['name'] . " by " . $item['artist'] . " - " . $item['url'] . "\n";
          $i++;
          }
//            $outputStr .= "\n" . "```";
      }catch(\Exception $e){
            Log::debug($e->getMessage());
            $outputStr = "Yeah if you had like a good programmer working on this it would work, but..." . $e->getMessage();
      }
        return $this->buildEmbed($embedOutput, $discord);
    }

}
