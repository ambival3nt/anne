<?php

namespace App\Http\Livewire;

use App\Models\AnneLogs;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShowLogs extends Component
{

    public $logData;

    public function mount(){

        $paginated = DB::table('log_messages')->paginate(20);

        $this->logData = $paginated->toJson();  //paginated output

    }


    public function pageHandler($page = null)
    {
            echo($page);
        if ($page) {
            $page = (int)$page;
        } else {
            return false;
        }


        $paginated = DB::table('log_messages');

        $paginated = $paginated->paginate(20, ['*'], 'page', $page);

        $this->logData = $paginated->toJson();

    }


    public function render()
    {
        return view('livewire.show-logs');
    }


}
