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
    ];

    public function earmuffs($val)
    {
        $anne = Anne::find(1);
        if($val === true){
            $anne->earmuffs = true;
        }else{
            $anne->earmuffs = false;
        }
        $anne->save();
        return($anne->earmuffs);
    }
}
