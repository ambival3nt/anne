<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PlaylistUI extends Component
{
    
    public function render()
    {
        return view('livewire.playlist-u-i', [
            'songs' => \App\Models\Playlist::with('person')->take(5)->get()
        ]);
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
