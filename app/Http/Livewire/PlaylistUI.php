<?php

namespace App\Http\Livewire;

use App\Models\Person;
use App\Models\Playlist;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PlaylistUI extends Component
{

 public $paginator;
 public $userList;
 public $selectedUser;

  public function render()
    {

        return view('livewire.playlist-u-i');

    }

    public function mount(){
        $paginated = Playlist::with('person')->paginate(5);

        $this->paginator = $paginated->toJson();
        $this->userList = $this->getAllUsers();
        $this->selectedUser = 0;
    }

    public function pageHandler($page=null)
    {

        if($page) {
            $page = (int)$page;
        }else{
            return false;
        }

        //TODO: cache this
        $paginated = Playlist::with('person');

        //check for dropdown selection, add this condition if a user is selected
        if ($this->selectedUser > 0) {
            $paginated = $paginated->where('user_id', $this->selectedUser);
        }

        $paginated = $paginated->paginate(5, ['*'], 'page', $page);

        $this->paginator = $paginated->toJson();

    }


    public function getUserName($id){
        return Person::find($id)->name;
    }

    public function getAllUsers(){
        return Person::all()->pluck('name', 'id');
    }

    public function getIcon($source)
    {
        switch($source){
            case 'Youtube':
                return 'resources\img\youtube-svgrepo-com.svg';
            case 'Spotify':
                return 'resources\img\spotify-svgrepo-com.svg';
            case 'soundcloud':
                return'resources\img\soundcloud-svgrepo-com.svg';
            default:
                return '0';
        }
    }
}
