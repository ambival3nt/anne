<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ShowLogs extends Component
{

    public $logData;

    public function mount(){
        $this->logData = "I'm a log look at me";
     }

     public function render()
    {
        return view('livewire.show-logs');
    }
}
