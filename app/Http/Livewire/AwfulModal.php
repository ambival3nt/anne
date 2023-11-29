<?php

namespace App\Http\Livewire;

use Livewire\Component;

class AwfulModal extends Component
{

public $modalData = '';

    public function render()
    {
        return view('livewire.awful-modal', [
            'modalData' => $this->modalData,
        ]);
    }

}
