<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnneMessages extends Model
{
    use HasFactory;

    protected $table='anne_messages';

    protected $fillable = [
        'message',
        'input_id',
        'vector',
        'user_id'
    ];

    public function userMessage(){
        return $this->belongsTo(Messages::class, 'input_id', 'id');
    }
}
