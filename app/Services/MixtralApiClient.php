<?php

namespace App\Services;

use App\Core\config\CommonKnowledge;
use App\Models\Anne;
use App\Models\AnneMessages;
use App\Models\Messages;
use App\Models\Person;
use Discord\Parts\Channel\Message;
use Illuminate\Support\Facades\Http;


class MixtralApiClient
{
    private const BASE_URI = 'https://api.mistral.ai/v1/chat/completions';

    public function __construct()
    {
        $this->client = Http::baseUrl('');

    }

    // Add methods for each API endpoint below
    public function chat($message,Message $messageModel)
    {

        $messages = new Messages;
        $anneMessages = new AnneMessages;

        $messages = $messages->all();

        $anneMessageIdLast = $anneMessages->all()->last()->id;

        $userId = $messageModel->user_id;

        $options = [];

$messageArray = [
    [
        'role'=>'system',
        'content'=> CommonKnowledge::selfAwareness() . CommonKnowledge::basicInstructions(),
    ],
    [
        'role' => 'user',
        'content' => $messages->find(($anneMessages->find($anneMessageIdLast-3)->input_id))->message,
    ],
    [
        'role' => 'assistant',
        'content' => $anneMessages->find($anneMessageIdLast-3)->message,
    ],
    [
        'role' => 'user',
        'content' => $messages->find(($anneMessages->find($anneMessageIdLast-2)->input_id))->message,
    ],
    [
        'role' => 'assistant',
        'content' => $anneMessages->find($anneMessageIdLast-2)->message,
    ],
    [
        'role' => 'user',
        'content' => $messages->find(($anneMessages->find($anneMessageIdLast-1)->input_id))->message,
    ],
    [
        'role' => 'assistant',
        'content' => $anneMessages->find($anneMessageIdLast-1)->message,
    ],
    [
        'role' => 'user',
        'content' => $messages->find(($anneMessages->find($anneMessageIdLast)->input_id))->message,
    ],
    [
        'role' => 'assistant',
        'content' => $anneMessages->find($anneMessageIdLast)->message,
    ],
    [
        'role' => 'user',
        'content' => $message,
    ]
];

        $client=$this->client;
        try {
            $response = $client->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . getenv('MISTRAL_API_KEY'),
            ])->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => getenv('MISTRAL_API_MODEL'),
                'messages' => $messageArray,
                'safe_prompt'=>false,
                ]);

            \Log::debug(json_encode($messageArray));


            if ($response->status() !== 200) {
              return ['error' => 'Unexpected status code received.'];
            }



            $responsePath = data_get($response->json(), 'choices.0.message.content', 'oop');

            //update person model
            $person = new Person;
            $person = $person->find($userId);
            $person->update([
                'last_message' => $message,
                'last_response' => $responsePath,
                'message_count' => $person->message_count + 1,
                'avatar' => $messageModel->author->avatar,
            ]);


            //init message model
            $messageModel = new Messages();


            $messageModel = $messageModel->create([
                'user_id' => (string)$person->id,
                'message' => $message,
                'response' => $responsePath
            ]);

            //init Anne's message model
            $anneMessage = new AnneMessages();

            //add to anne's message archive
            $anneMessage = $anneMessage->create([
                'user_id' => $person->id,
                'message' => $responsePath,
                'input_id' => $messageModel->id,
                'anne_vector_index' => "anne-$messageModel->id",
//                    'vector' => json_encode($anneEmbed),
            ]);




            return $responsePath;
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
            return ['error' => 'Failed to communicate with Mistral AI.'];
        }
    }
    /**
     * Create embeddings for given texts.
     */
    public function createEmbeddings(array $texts, array $options = []): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('/embeddings', [
                'input' => $texts,
                ...$options,
            ]);

            if ($response->status() !== 200) {
                return ['error' => 'Unexpected status code received.'];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to communicate with Mistral AI.'];
        }
    }

    /**
     * Get a list of available models.
     */
    public function listModels(): array
    {
        try {
            $response = Http::get('/models');

            if ($response->status() !== 200) {
                return ['error' => 'Unexpected status code received.'];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Failed to communicate with Mistral AI.'];
        }
    }


}
