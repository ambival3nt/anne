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

    public $displayData;
    public $userList;
    public $selectedUser;
    public $searchText;

    public function render()
    {

        return view('livewire.playlist-u-i');

    }

    public function mount()
    {
        $paginated = Playlist::with('person')->paginate(5);

        $this->displayData = $paginated->toJson();  //paginated output
        $this->userList = $this->getAllUsers(); //list of all users with playlist data
        $this->selectedUser = 0; // currently selected user id
        $this->searchText = ''; // text currently in search box
    }

    // Whenever someone types something in the search box, this fires
    public function searchHandler()
    {
        if ($this->searchText != '') {
            $searchQuery = Playlist::with('person')->where('title', 'like', '%' . $this->searchText . '%');

            if ($this->selectedUser > 0) {
                $searchQuery = $searchQuery->where('user_id', $this->selectedUser);
            }

            $result = $searchQuery->paginate(5);
            $this->displayData = $result->toJson();
        }
    }

    // This handles the page number changing
    public function pageHandler($page = null)
    {

        if ($page) {
            $page = (int)$page;
        } else {
            return false;
        }

        //TODO: cache this
        $paginated = Playlist::with('person');

        //check for dropdown selection, add this condition if a user is selected
        if ($this->selectedUser > 0) {
            $paginated = $paginated->where('user_id', $this->selectedUser);
        }

        $paginated = $paginated->paginate(5, ['*'], 'page', $page);

        $this->displayData = $paginated->toJson();

    }


    // This handles the dropdown displaying names instead of ids
    public function getUserName($id)
    {
        return Person::find($id)->name;
    }

    // This grabs just the name and id of all users from the people table
    public function getAllUsers()
    {
        return Person::all()->pluck('name', 'id');
    }

    public function getIcon($source)
    {
        switch ($source) {
            case 'Youtube':
                return 'resources\img\youtube-svgrepo-com.svg';
            case 'Spotify':
                return 'resources\img\spotify-svgrepo-com.svg';
            case 'soundcloud':
                return 'resources\img\soundcloud-svgrepo-com.svg';
            default:
                return '0';
        }
    }
}
