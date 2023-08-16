<?php

namespace App\Http\Livewire;

use Livewire\Component;

class MessageHistory extends Component
{
    public function render()
    {

        return view('livewire.message-history',
            [
                'messages' => \App\Models\Messages::with(['user', 'thoughts'])->orderByDesc('created_at')->take(5)->get()
                ]
        );
    }
}
