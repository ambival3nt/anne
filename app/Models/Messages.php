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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'id', 'user_id');
    }

    public function anneReply(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AnneMessages::class, 'input_id', 'id');
    }

    public function attachment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Attachments::class, 'message_id', 'id');
    }
}
