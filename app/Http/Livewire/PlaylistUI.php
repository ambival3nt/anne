<?php

namespace App\Http\Livewire;

use App\Models\Playlist;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PlaylistUI extends Component
{

 public $paginator;

  public function render()
    {

        return view('livewire.playlist-u-i');

    }

    public function mount(){
        $paginated = Playlist::with('person')->paginate(5);

        $this->paginator = $paginated->toJson();

    }

    public function pageHandler($button){

            //convert to int
            $page = (int)$button;

            //TODO: cache this
            $paginated = Playlist::with('person')->paginate(5, ['*'], 'page', $page);
            $this->paginator = $paginated->toJson();

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
