<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TriviaPlayers extends Model
{
    use HasFactory;

    protected $table = 'trivia_players';

    protected $fillable = [
        'user_id',
        'score',
        'last_answer',
    ];

    protected $primaryKey='user_id';

    /**
     * Every player has one person.
     * @return HasOne
     */
    public function person(){
        return $this->hasOne(Person::class, 'id', 'user_id');
    }

    /**
     * Checks if a user is playing the game.
     * @param $userId
     * @return bool
     */
    public function isPlaying($userId) : bool
    {
        $player = $this->where('user_id', $userId)->first();
        if($player){
            return true;
        }
        return false;
    }

    /**
     * Adds new player to the game.
     * @param int $userId
     * @return self
     */
    public function addPlayer(int $userId) : self
    {
       return self::create([
            'user_id' => $userId,
            'score' => 0,
            'last_answer' => null,
        ]);
    }

    /**
     * Adds one point to a player's score for the game.
     * @param $userId
     * @param $score
     * @return self
     */
    public function addPoint($userId) : self
    {
        $this->user_id = $userId;
        $this->score++;
        $this->save();
        return $this;
    }



}
