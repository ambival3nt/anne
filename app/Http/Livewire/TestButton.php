<?php

namespace App\Http\Livewire;

use App\Jobs\RunBotProcessCommand;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use React\ChildProcess\Process;

class TestButton extends Component
{
    public function render()
    {
        return view('livewire.test-button');
    }

    public function testBotClick()
    {
        RunBotProcessCommand::dispatch();
//        $process = new Process('php artisan disdain:go');
//        $process->start();
    }
}
