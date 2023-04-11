<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anne extends Model
{
    use HasFactory;

    protected $table = 'anne';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'last_message',
        'last_user',
        'last_response',
        'earmuffs',
        'debug',
    ];

    //toggles earmuffs (makes her only listen to the owner)
    public function earmuffsToggle($val)
    {
//        $anne = Anne::all()->first();
        if($val === true){
//            $anne->earmuffs = true;
            $this->earmuffs = true;
        }else{
            $this->earmuffs = false;
        }
        $this->save();
        return $this;
    }

    //toggle debug mode (makes her output various debug info)
    public function debugToggle($val)
    {
//        $anne = Anne::all()->first();
        if($val === true){
            $this->debug = true;
        }else{
            $this->debug = false;
        }
        $this->save();
        return $this;
    }
}
