<?php

namespace App\Http\Livewire;

use App\Models\AnneLogs;
use Livewire\Component;

class ShowLogs extends Component
{

    public $logData;

    public function mount(){
        $this->logData = AnneLogs::all();
    }

     public function render()
    {
        return view('livewire.show-logs');
    }


}
