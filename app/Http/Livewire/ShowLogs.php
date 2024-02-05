<?php

namespace App\Http\Livewire;

use App\Models\AnneLogs;
use Livewire\Component;

class ShowLogs extends Component
{

    public $logData;

    public function mount(){



        $paginated = AnneLogs::paginate(20);

        $this->logData = $paginated->toJson();  //paginated output

    }


    public function pageHandler($page = null)
    {

        if ($page) {
            $page = (int)$page;
        } else {
            return false;
        }

        //TODO: cache this
        $paginated = new AnneLogs;

        //check for dropdown selection, add this condition if a user is selected
//        if ($this->selectedUser > 0) {
//            $paginated = $paginated->where('user_id', $this->selectedUser);
//        }

        $paginated = $paginated->paginate(5, ['*'], 'page', $page);

        $this->logData = $paginated->toJson();

    }


    public function render()
    {
        return view('livewire.show-logs');
    }


}
