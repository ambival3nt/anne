<?php

namespace App\Core\Trivia;

use App\Core\bot_main;
use App\Models\TriviaGame;
use App\Models\TriviaPlayers;
use App\Models\TriviaScores;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\Async;
use React\EventLoop\TimerInterface;
use React\Promise;
use React\Promise\Timer;

class TriviaCore
{

    public static TimerInterface $staticTimer;

    public function __construct()
    {
    //
    }
    public function gameHandler($discord, $triviaGame, $message){
            if (strtolower($message->content) == strtolower($triviaGame->answer)) {
                 $loop= Loop::get();
                 $loop->cancelTimer(self::$staticTimer);
                $triviaGame->round++;
                $triviaGame->save();

// announce win
                $correctReply = $message->author->username . ' got it! It was: ' . $triviaGame->answer;
                $message->channel->sendMessage($correctReply);


// grab player from list
                $players = new TriviaPlayers();
                $correctPlayer = $players->find($message->author->id) ?? null;

// add player if they aren't on it
                if (!$correctPlayer) {
                    $players->addPlayer($message->author->id);
                    $correctPlayer = $players->find($message->author->id);
                }

// add point to player
                $correctPlayer->addPoint($message->author->id, 1);

        // Check for the win condition
        $isWinner = $this->checkWinCondition($correctPlayer, $message) ;
        if ($isWinner) {
            //end the game
            return $this->announceWinner($correctPlayer, $message);
        } else {
            //start the next round
            return $this->startNewQuestion($discord, $triviaGame, false);
        }
            }else {
            return ['timer'=>null, 'error'=>false, 'message'=>null];
        }
    }


    public function initGame($discord, $message)
    {


        $triviaGame = TriviaGame::first() ?? null;
        if (!$triviaGame) {

            //Start new game by wiping the players table and the game table

            $message->channel->sendMessage('Starting new game. Just answer to play. Or don\'t. I don\'t care.');

            $triviaGame = new TriviaGame();

            $triviaGame = $triviaGame->init($message) ?? false;

            //new player object for game starter
            $players = new TriviaPlayers();

            $players->addPlayer($message->author->id);


            return $this->startNewQuestion($discord, $triviaGame, false);


        } else return [
            'message' => 'Trivia already running. You lose a point. For shame.',
            'error' => true,
            'isNewTimer' => false
        ];
    }


    /**
     * @param Discord $discord
     * @param TriviaGame $triviaGame
     * @return array
     */
    private function startNewQuestion(Discord $discord, TriviaGame $triviaGame, $timeoutResponse = false)
    {

        $loop = Loop::get();


        if ($timeoutResponse) {
            $discord->getChannel($triviaGame->channel)->sendMessage("Time's up. It was: " . $triviaGame->answer);

            $triviaGame->round++;
            $timeoutResponse = false;


            $output = "New question started.\n";
            $error = false;
            $questionBlob = json_decode($triviaGame->question_blob);

            $triviaGame->question = $questionBlob[$triviaGame->round - 1]->question->text;
            $triviaGame->answer = $questionBlob[$triviaGame->round - 1]->correctAnswer;

            $triviaGame->save();

            $questionOutput = $output .
                "Round " . $triviaGame->round . "\nQuestion: " . $triviaGame->question;

//            return [
//                'game' => $triviaGame,
//                'message' => $questionOutput,
//                'error' => $error
//            ];

            $discord->getChannel($triviaGame->channel)->sendMessage($questionOutput);

        } else {

            $output = "New question started.\n";
            $error = false;
            $questionBlob = json_decode($triviaGame->question_blob);

            $triviaGame->question = $questionBlob[$triviaGame->round - 1]->question->text;
            $triviaGame->answer = $questionBlob[$triviaGame->round - 1]->correctAnswer;

            $triviaGame->save();

            $questionOutput = $output .
                "Round " . $triviaGame->round . "\nQuestion: " . $triviaGame->question;
        }

        // Start a timer for 30 seconds


        self::$staticTimer = $loop->addTimer(30, function () use ($discord, $triviaGame) {
            Loop::get()->cancelTimer(self::$staticTimer);
            $this->startNewQuestion($discord, $triviaGame, true);
        });


        return [
            'game' => $triviaGame,
            'message' => $questionOutput,
            'error' => $error
        ];
    }


    private function announceWinner($winner, $message)
    {
        $scoreOutput = "";
        $message->channel->sendMessage($winner->name . ' wins!');
        foreach (TriviaPlayers::orderByDesc('score')->all() as $endPlayer) {
            $playerAllTimeScore = TriviaScores::firstOrCreate([
                'user_id' => $endPlayer->user_id
            ], [
                'score' => 0
            ]);
            $playerAllTimeScore->score += $endPlayer->score;
            $playerAllTimeScore->save();

            $scoreOutput .= "\n" . $endPlayer->name . ' had ' . $endPlayer->score . ' points.';
        }
        return ['message' =>
            'Final scores:' . $scoreOutput,
            'error' => false,
            'timer' => null
        ];
    }


    private function checkWinCondition($correctPlayer, Message $message)
    {
        if ($correctPlayer->score >= 10) {
            return true;
        }
        return false;
    }

}
