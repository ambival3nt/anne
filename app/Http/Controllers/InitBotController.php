<?php

namespace App\Http\Controllers;

use App\Jobs\RunBotProcessCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;


class InitBotController extends Controller
{
    public function testBotRunFromUI(){
       RunBotProcessCommand::dispatch();
    }
}
