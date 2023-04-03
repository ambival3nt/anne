<?php

namespace App\Http\Livewire;

use Livewire\Component;

class LoginModal extends Component
{


    public $showModal = false;


    public function render()
    {
        return view('livewire.login-modal');
    }
}
