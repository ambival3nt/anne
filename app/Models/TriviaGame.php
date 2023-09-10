<?php

namespace App\Models;

use Carbon\Carbon;
use Discord\Parts\Channel\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class TriviaGame extends Model
{
    use HasFactory;

    protected $table = 'trivia_game';

    protected $fillable = [
        'round',
        'leader',
        'channel',
        'start_time',
        'question',
        'answer',
        'round',
        'question_blob'
    ];

    /**
     * Starts a new game. Empties the table.
     * @param Message $message
     * @return self
     */
    public function init(Message $message) : self {

        TriviaPlayers::all()->each(function($player){
            $player->delete();
        });

        $blob = Http::get('https://the-trivia-api.com/v2/questions', [
            'limit'=> 50,
        ])->json();

        $blob=json_encode($blob);

        $newGame = self::create([
            'round' => 1,
            'leader' => null,
            'channel' => $message->channel->id,
            'start_time' => Carbon::now()->toTimeString(),
            'question' => null,
            'answer' => null,
            'question_blob' => $blob,
        ]) ?? null;

            return $newGame;

    }

    public function abort() : void {
        $this->delete();
    }



}
