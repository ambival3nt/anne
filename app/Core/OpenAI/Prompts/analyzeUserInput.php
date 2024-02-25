<?php

namespace App\Core\OpenAI\Prompts;

use Illuminate\Support\Facades\Log;
use OpenAI;

class analyzeUserInput
{

    public function actions($input, $user){
        $prompt= "You are an AI that analyzes user input. You have a list of available commands at your disposal. \n
        Here are those commands, carefully familiarize yourself with them:\n
        ". $this->modelCommands($input,$user) . "\n-----\n
        Your task is to return an appropriate command to handle the user's request.\n
        Most requests will likely not need any of these commands.\n
        Give each command a confidence score between 1 and 10 on the likelihood it is required.\n
        Return the command with the highest score, but only if the score is above 9.\n
        If you are unsure, return nothing.\n
        Do not change the user's input.\n\n
        -----
        User: $input
        -----\n\n
        Your response:\n
        "
        ;

        $client=OpenAI::client(getenv('OPENAI_API_KEY'));
        $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',

                'messages'=> [
                    [
                        "role"=>"system",
                        "content"=>$prompt,
                    ]
                ],

//                'prompt' => $prompt,
//                        'top_p' => .25,
                'temperature' => .1,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
                'frequency_penalty' => 1.2,
                'presence_penalty' => 1.2,
                'n' => 1,
            ]
        );
        Log::channel('db')->debug(json_encode($result, 128));
        return $result['choices'][0]['message']['content'];

    }

    public function formatted(string $input, string $user, $client)
    {

        $prompt = "You are a message analyst. You will be given a message and asked to analyze it, rate it according \n
        to certain parameters, and extract information from it if you deem it necessary. You will be given a template \n
        you are to use to output your response, do not deviate from the format.
        \n\n
        Instructions:\n

        For the 'Scores' section, use your best judgement on the intended intensity or presence of each listed emotion in the message.\n\n
        For the 'Scores' section, replace all example scores with your own score from 0-100.\n\n
        For the 'Requests' section, answer each question using the message content. Replace the example answers with your own answers.\n\n
        For the 'Additional' section, answer each question using the message content. Replace the example answers with your own answers.\n\n

        The following is the example template:\n\n

        Example:\n
        \`\`\`\n
        anne-brain-output-mk.1-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n
        User: $user\n
        Message: $input\n
        Scores-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n
        .Happy:          46         .Sad:              2\n
        .Angry:          9          .Fear:             12\n
        .Funny:          41         .Interesting:      43\n
        .Polite:         2          .Rude:             3\n
        .Friendly:       99         .Hostile:          95\n
        \n
        Requests-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n
        User query requires web search?             yes\n
        If yes, what is the search term?            skee-ball
        User query requires write to database?      yes\n
        If yes, data type?                          json\n
        \n
        Additional=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n
        Is prompt suitable for davinci to read? yes\n
        People mentioned in message: [list, of, people]\n
        \`\`\`\n
        -----\n\n
        Your output:\n\n
        anne-brain-output-mk.1-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
        ";
        $result = $client->completions()->create(['model' => 'text-curie-001',
                'prompt' => $prompt,
//                        'top_p' => .25,
                'temperature' => 1,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
//                'frequency_penalty' => 0.5,
//                'presence_penalty' => 1,
//                'best_of' => 3,
                'n' => 1,
            ]
        );
        Log::channel('db')->debug(json_encode($result, 128));
        return $result['choices'][0]['text'];

    }
public function modelCommands($input, $user){

        $prompt = "
            Command: -save\n

            Description: Saves user request to the database, that the user specifically asks to save or to be remembered.\n
            Example Case: User says 'Can you remember [user input] for me?'\n
            Usage: -save [data type] [data]]\n
            Example Output: -save ".gettype($input)." $input\n
            -----
            Command: -get\n

            Description: Query user specifically requested memory from the database. Only select this if user asks you to remember something.\n
            Example Case: User says 'What was that code that i told you to remember?'\n
            Usage: -get [data type] [data]\n
            Example Output: -get ".gettype($input)."\n
            -----
            Command: -websearch\n

            Description: Searches the internet for user query.\n
            Example Case: User says 'Can you do a search for [search query]?'\n
            Usage: -websearch [input]\n
            Example Output: -websearch $input\n
            -----
            Command: -recall\n

            Description: Recalls a specific previous message from a user.\n
            Example Case: User says 'do you remember when [user references event]'\n
            Usage: -recall [user] [input]\n
            Example Output: -recall $user $input\n
            -----
            Command: -ban\n
            Description: Bans a user from the server.\n
            Example Case: User says 'Ban @user for being a dick'\n
            Usage: -ban [user]\n
            Example Output: -ban @user\n
            -----
            Command: -like\n

            Description: Like a message, because the user said something polite, kind, or positive. Increase user's reputation.\n
            Example Case: User says 'You did a great job on that, [another user]'.\n
            Usage: -like [user]\n
            Example Output: -like $user\n
            -----
            Command: -dislike\n

            Description: User says something rude, mean, or aggressive. Dislike a message, decrease user's reputation.\n
            Example Case: User says: 'Wow, you look ugly in that picture, [another user].'\n
            Usage: -dislike [user]\n
            Example Output: -dislike $user\n
            -----
            Command: -yoot\n
            Description: Get a youtube url for a user's query for a video, or user mentions youtube.\n
            Example Case: User says: 'Can you find that video [user request] on youtube?.'\n
            Usage: -yoot [subject of user request]\n
            Example Output: -yoot [subject of user request]\n
            -----
            ";

        return $prompt;
}
    public function basic(string $input, string $user, $client=null){

      $prompt = "
        You are an emotion and sentiment analyzer. You will be given a message and asked to analyze it, and rate the intensity of a given list of emotions.\n
        All scores should be from 0-100, where 0 is the least intense and 100 is the most intense.\n
        Score on the following:\n
        Happy, Sad, Angry, Fear, Funny, Interesting, Polite, Rude, Friendly, Hostile\n
        Here is the message:\n" .
        $input;
//        Here are a few examples of inputs as well as the expected output:\n\n
//        -----\n
//        User:\n
//        I'm so happy to see you, it's been a long time since we hung out.\n
//        Response:\n
//        Happy:          91\n
//        Sad:              9\n
//        Angry:          0\n
//        Fear:             0\n
//        Funny:          9\n
//        Interesting:      30\n
//        Polite:         92\n
//        Rude:             0\n
//        Friendly:       89\n
//        Hostile:          95\n
//        -----\n\n
//        User:\n
//        god i hate that guy, why do we even let him hang out here? he's worse than a cold hotdog on a cold day\n
//        Response:\n
//         Happy:          11\n
//        Sad:              60\n
//        Angry:          80\n
//        Fear:             11\n
//        Funny:          50\n
//        Interesting:      50\n
//        Polite:         5\n
//        Rude:             90\n
//        Friendly:       17\n
//        Hostile:          93\n
//        -----\n\n
//        User:
//        $input\n
//        Response:\n
//        ";
        $client = OpenAI::client(getenv('OPENAI_API_KEY'));

      $result = $client->completions()->create([
              'model' => 'gpt-3.5-turbo-instruct',
                'prompt' => $prompt,
//                        'top_p' => .25,
                'temperature' => 1,
                'max_tokens' => 600,
                'stop' => [
                    '-----',
                ],
//                'frequency_penalty' => 0.5,
//                'presence_penalty' => 1,
//                'best_of' => 3,
                'n' => 1,
            ]
        );
        Log::channel('db')->debug(json_encode($result, 128));
        return $result['choices'][0]['text'];
    }
}
