<?php

namespace App\Core\OpenAI\Prompts;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class analyzeUserInput
{

    public function formatted(string $input, string $user)
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
        $result = OpenAI::completions()->create(['model' => 'text-curie-001',
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
        return $result['choices'][0]['text'];

    }

    public function basic(string $input, string $user){

      $prompt = "
        You are an emotion and sentiment analyzer. You will be given a message and asked to analyze it, and rate the intensity of a given list of emotions.\n
        All scores should be from 0-100, where 0 is the least intense and 100 is the most intense.\n
        Here are a few examples of inputs as well as the expected output:\n\n
        -----\n
        User:\n
        I'm so happy to see you, it's been a long time since we hung out.\n
        Response:\n
        Happy:          91\n
        Sad:              9\n
        Angry:          0\n
        Fear:             0\n
        Funny:          9\n
        Interesting:      30\n
        Polite:         92\n
        Rude:             0\n
        Friendly:       89\n
        Hostile:          95\n
        -----\n\n
        User:\n
        god i hate that guy, why do we even let him hang out here? he's worse than a cold hotdog on a cold day\n
        Response:\n
         Happy:          11\n
        Sad:              60\n
        Angry:          80\n
        Fear:             11\n
        Funny:          50\n
        Interesting:      50\n
        Polite:         5\n
        Rude:             90\n
        Friendly:       17\n
        Hostile:          93\n
        -----\n\n
        User:
        $input\n
        Response:\n
        ";

      $result = OpenAI::completions()->create(['model' => 'text-curie-001',
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
        Log::debug(json_encode($result, 128));
        return $result['choices'][0]['text'];
    }
}
