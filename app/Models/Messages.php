<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    public $table = 'messages';

    protected $fillable = [
        'message',
        'user_id',
        'vector'
    ];

    public function user()
    {
        return $this->belongsTo(Person::class, 'id', 'user_id');
    }

    public function anneReply(){
        return $this->hasOne(AnneMessages::class, 'reply_id', 'id');
    }

    public function attachment(){
        return $this->hasOne(Attachments::class, 'message_id', 'id');
    }
}
