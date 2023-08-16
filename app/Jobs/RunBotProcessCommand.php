<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class RunBotProcessCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo("Running bot init job.");
        $basePath = base_path();
        $process = Process::fromShellCommandline('php ' . $basePath .'/artisan disdain:go &');
//        $process->setTimeout(36000);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo '\nERR > '.$buffer;
            } else {
                echo '\nOUT > '.$buffer;
            }
            Log::channel('db')->debug($buffer);
        });

    }
}
