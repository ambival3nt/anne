<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $total_games_played
 * @property $total_score
 * @property $user_id
 * @property int $total_game_wins
 */
class TriviaScores extends Model
{
    use HasFactory;

    public $primaryKey = 'user_id';

    protected $table = 'trivia_scores';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'total_score',
        'total_game_wins',
        'total_games_played',
    ];


    /**
     * Updates a player's score
     * @param $userId
     * @param $score
     * @param bool $isWinner
     * @return bool
     */
    public function updateScore($userId, $score, bool $isWinner=false) : bool {
if($userId){
    $userScore = self::find($userId);
          $userScore->total_score += $score;
          $userScore->total_games_played += 1;
          if ($isWinner) {
              $userScore->total_game_wins += 1;
          }
          $userScore->save();
      }else{
          return false;
      }
        return true;
    }

    /**
     * Return a player's score
     * @param $userId
     * @return self | null
     */
    public function getScore($userId): self | null{
        $score = $this->where('user_id', $userId)->first();
        if ($score) {
            return $score;
        }
        return null;
    }



    /**
     * Return a table of top scoring players
     * @return string
     */
    public function getTopScores() : string
    {

        $mask = "|%5.5s | %10.10s | %10.10s | %-10.10s | %-10.10s | %-5.5s | \n";

        $scores = $this->orderBy('total_score', 'desc')->with('player')->limit(10)->get();
        $outputString = "```\n";
        $outputString .= "Top 10 Scores-------------------------------------------------------\n";
        $outputString .= sprintf($mask, '#', 'Name', 'Score', 'Wins', 'Games', ' % ');

        $i = 0;
        foreach ($scores as $score) {
            $i++;
            $playerRank = $i;
            $playerName = $score->player->name ?? 'Unknown';
            $playerScore = $score->total_score ?? 0;
            $playerWins = $score->total_game_wins ?? 0;
            $playerGames = $score->total_games_played ?? 0;
            if ($playerGames && $playerWins) {
                $playerWinRate = round(($playerWins / $playerGames) * 100, 1) . '%';
            } else {
                $playerWinRate = '0%';
            }
            $outputString .= sprintf($mask, $playerRank, $playerName, $playerScore, $playerWins, $playerGames, $playerWinRate);
        }
        $outputString = $outputString . "\n```";
        return $outputString;
    }

    /**
     * Gets the person class for a player
     * @return HasOne
     */
    public function player() : HasOne
    {
        return $this->hasOne(Person::class, 'id', 'user_id');
    }
}
