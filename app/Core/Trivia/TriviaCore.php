<?php

namespace App\Core\Trivia;

use App\Core\bot_main;
use App\Models\Person;
use App\Models\TriviaGame;
use App\Models\TriviaPlayers;
use App\Models\TriviaScores;
use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\Async;
use React\EventLoop\TimerInterface;
use React\Promise;
use React\Promise\Timer;
use function React\Async\delay;

class TriviaCore
{

    public static TimerInterface $staticTimer;


    public function __construct()
    {
        //
    }

    public function gameHandler($discord, $triviaGame, $message)
    {

        $isCorrect = false;

        $explodedAnswer = explode(' ', $triviaGame->answer);
        array_splice($explodedAnswer, 1, (count($explodedAnswer)) - 1);
        $removedFirstWordAnswer = implode(' ', $explodedAnswer);
        if(!$triviaGame->locked) {
            if ($this->isFuzzyMatch(strtolower($message->content), strtolower($triviaGame->answer))
                || $this->isFuzzyMatch(strtolower($message->content), strtolower($removedFirstWordAnswer))
                || strtolower($message->content) == strtolower($triviaGame->answer)
                || strtolower($message->content) == strtolower($removedFirstWordAnswer)
            ) {

                $isCorrect = true;

            }

            if ($isCorrect) {

                $triviaGame->lock();
                $loop = Loop::get();
                $loop->cancelTimer(self::$staticTimer);

                $triviaGame->round++;
                $triviaGame->save();


// grab player from list
                $players = new TriviaPlayers();
                $correctPlayer = $players->find($message->author->id) ?? null;
                $correctPlayerAvatar = new Person;
                $correctPlayerAvatar = $correctPlayerAvatar->find($message->author->id)->avatar ?? null;

                $details = [
                    'winner' => $message->author->username,
                    'question' => $triviaGame->question,
                    'answer' => $triviaGame->answer,
                    'score' => $correctPlayer->score ?? 1,
                    'avatar' => $message->author->avatar ?? '',
                ];

// announce win
                $correctEmbed = self::buildCorrectEmbed($discord, $details);
                $correctEmbedMessage = new MessageBuilder();
                $correctEmbedMessage->addEmbed($correctEmbed);


//            $correctReply = $message->author->username . ' got it! It was: ' . $triviaGame->answer;
                $message->channel->sendMessage($correctEmbedMessage);


// add player if they aren't on it
                if (!$correctPlayer) {
                    $players->addPlayer($message->author->id);
                    $correctPlayer = $players->find($message->author->id);
                }

// add point to player
                $correctPlayer->addPoint($message->author->id, 1);

                try {
                    // Check for the win condition
                    $isWinner = $this->checkWinCondition($correctPlayer, $message);
                    if ($isWinner) {
                        //end the game
                        return $this->announceWinner($correctPlayer, $message, $discord);
                    } else {
                        try {
                            //start the next round
                            delay(5.0);

                        } catch (Exception $e) {
                            Log::debug($e->getMessage() . ' L' . $e->getLine() . ' TriviaCore');
                        }


                        return $this->startNewQuestion($discord, $triviaGame, false);
                    }
                } catch (\Exception $e) {
                    Log::debug($e->getMessage());
                    $message->channel->sendMessage('I tried to break. Shame on me.');
                }
            } else {
                return ['timer' => null, 'error' => false, 'message' => null];
            }
        }
    }


    public function initGame($discord, $message)
    {


        $triviaGame = TriviaGame::first() ?? null;
        if (!$triviaGame) {

            //Start new game by wiping the players table and the game table

            $message->channel->sendMessage('Starting new game. Just answer to play.');

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
            $triviaGame->lock();
            //if all 50 questions have been burned, get 50 new ones
            if ($triviaGame->question_key == 49) {
                $triviaGame->getNewQuestionBlob();
                if ($triviaGame->question_key !== 0) {
                    $triviaGame->abort();
                }
            }


            $triviaGame->round++;
            $triviaGame->question_key++;

            $timeoutResponse = false;


            $output = "";
            $error = false;
            $questionBlob = json_decode($triviaGame->question_blob);

            $question = $questionBlob[$triviaGame->question_key]->question->text;
            $lowerQuestion = strtolower(trim(($question)));

            Log::debug($lowerQuestion);

            if (str_contains($lowerQuestion, "which of") || str_contains($lowerQuestion, "which one of")
                || str_contains($lowerQuestion, "which of the following")
                || str_contains($lowerQuestion, "which one of the following")
                || str_contains($lowerQuestion, "which of these")
                || str_contains($lowerQuestion, "which one of these")
                || str_contains($lowerQuestion, "which")
                || str_contains($lowerQuestion, "Which")) {
                $newQA = $this->convertMultipleChoice($questionBlob[$triviaGame->question_key]->question->text,
                    $questionBlob[$triviaGame->question_key]->correctAnswer,
                    $questionBlob[$triviaGame->question_key]->incorrectAnswers
                );

                $questionDisplay = $newQA['question'];
                $triviaGame->question = $questionBlob[$triviaGame->question_key]->question->text;
                $triviaGame->answer = $newQA['answer'];

            } else {

                $triviaGame->question = $questionBlob[$triviaGame->question_key]->question->text;
                $triviaGame->answer = $questionBlob[$triviaGame->question_key]->correctAnswer;

                $questionDisplay = $triviaGame->question;
            }
            $triviaGame->save();

            try {
                delay(5.0);
            }catch(Exception $e){
                Log::debug($e->getMessage() . ' L' . $e->getLine() . ' TriviaCore');
            }

            $questionOutput = $output .
                "Round " . $triviaGame->round . "\nQuestion: " . $questionDisplay;

            $triviaGame->unlock();
            $discord->getChannel($triviaGame->channel)->sendMessage($questionOutput);

        } else {


            if ($triviaGame->question_key == 49) {
                $triviaGame->getNewQuestionBlob();
                if ($triviaGame->question_key !== 0) {
                    $triviaGame->abort();
                }
            }

            $triviaGame->question_key++;

            $output = "New question started.\n";
            $error = false;
            $questionBlob = json_decode($triviaGame->question_blob);

            $triviaGame->question = $questionBlob[$triviaGame->question_key]->question->text;
            $triviaGame->answer = $questionBlob[$triviaGame->question_key]->correctAnswer;

            $triviaGame->save();


            $questionOutput = $output .
                "Round " . $triviaGame->round . "\nQuestion: " . $triviaGame->question;
            $triviaGame->unlock();
        }

        // Start a timer for 30 seconds


        self::$staticTimer = $loop->addTimer(30, function () use ($discord, $triviaGame) {
            try {
                Loop::get()->cancelTimer(TriviaCore::$staticTimer);
                delay(2.0);
            }catch(Exception $e){
                Log::debug($e->getMessage() . ' L' . $e->getLine() . $e->getFile());
            }
            $this->startNewQuestion($discord, $triviaGame, true);
        });


        return [
            'game' => $triviaGame,
            'message' => $questionOutput,
            'error' => $error
        ];
    }


    private function announceWinner($winner, $message, Discord $discord)
    {

        $message->channel->sendMessage($winner->person->name . ' wins!');
        $rank=1;
        $messageBuilder = new MessageBuilder();



        foreach (TriviaPlayers::orderByDesc('score')->with('person')->get() as $endPlayer) {

            $embedArray = [];

            $playerAllTimeScore = TriviaScores::firstOrCreate([
                'user_id' => $endPlayer->user_id
            ], [
                'score' => 0
            ]);
            $playerAllTimeScore->updateScore($endPlayer->user_id, $endPlayer->score, $endPlayer->user_id == $winner->user_id);

//            $scoreOutput .= "\n" . $endPlayer->person->name . ' had ' . $endPlayer->score . ' points.' . "\n";

            $embedArray = [
                'name' => $endPlayer->person->name,
                'score' => $endPlayer->score,
                'avatar' =>$discord->users->get('id', $endPlayer->user_id)->avatar ?? null,
                'rank' =>   $rank,
            ];

            $currentRankEmbed = self::buildFinalScoreEmbed($discord, $embedArray);

            $messageBuilder->addEmbed($currentRankEmbed);

            $rank++;
        }

        $game = TriviaGame::where('id', '>', 0)->first() ?? null;
        if (data_get($game, 'id', null) !== null) {
            TriviaGame::destroy($game->id);
        }
        $players = TriviaPlayers::where('user_id', '>', 0)->first() ?? null;
        if (data_get($players, 'user_id', null) !== null) {
            TriviaPlayers::destroy($players->user_id);
        }
        return [
            'message' => $messageBuilder,
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

    //does what it says on the tin
    private function convertMultipleChoice($question, $answer, $answerArray)
    {
        //push correct answer to incorrect array
        $answerArray[] = $answer;

        //shake it around
        shuffle($answerArray);

        //find the correct answer
        $correct = array_search($answer, $answerArray) + 1;

        //format the question
        $outputQuestion = $question . "\n1. " . $answerArray[0] . "\n2. " . $answerArray[1] . "\n3. " . $answerArray[2] . "\n4. " . $answerArray[3];

        //bobs your uncle
        return [
            'question' => $outputQuestion,
            'answer' => $correct,
            'answerText' => $answerArray[$correct - 1]
        ];
    }

    private function isFuzzyMatch($guess, $answer)
    {
        $metaphoneLev = levenshtein(metaphone($guess), metaphone($answer));

        $similarity = similar_text($guess, $answer, $percent);

        if ($metaphoneLev <= 2 && $percent >= 90) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Embed to announce question answered correctly
     * @param $discord
     * @param $details
     * @return Embed|string
     */
    public static function buildCorrectEmbed($discord, $details)
    {

        try {
            $embed = new Embed($discord);
//            $embed->setTitle();
            $embed->setDescription($details['winner'] . ' has ' . ($details['score'] + 1) . ' points');
            $embed->setAuthor($details['question']);
            $embed->setTitle($details['winner'] . " got it! It was: " . $details["answer"]);
            $embed->setThumbnail($details['avatar'] ?? null);
            $embed->setColor(0x00AE86);
            return $embed;

        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
        }
    }

    public static function buildFinalScoreEmbed(Discord $discord, $details)
    {


        try {
            $embed = new Embed($discord);
//            $embed->setTitle();
            $embed->setDescription($details['name'] . ' - ' . $details['score'] . ' points');
//            $embed->setAuthor($details['question']);
            $embed->setTitle(self::getRankText($details['rank']));
            $embed->setThumbnail($details['avatar'] ?? null);
            $embed->setColor(self::getRankColor($details['rank']));
            return $embed;

        } catch (\Exception $e) {
            Log::channel('db')->debug($e->getMessage());
            return $e->getMessage() . ' L' . $e->getLine();
        }

    }

    public static function getRankColor(int $rank)
    {
        switch ($rank) {
            case 1:
                return 0xFFD700;
            case 2:
                return 0xC0C0C0;
            case 3:
                return 0xCD7F32;
            default:
                return 0x00AE86;
        }
    }

    public static function getRankText(int $rank)
    {
        switch ($rank) {
            case 1:
                return '1st!';
            case 2:
                return '2nd!';
            case 3:
                return '3rd!';
            default:
                return $rank . 'th!';
        }
    }
}
