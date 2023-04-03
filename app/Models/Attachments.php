<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    use HasFactory;

    protected $table = 'attachments';

    protected $fillable = [
        'data',
        'person_id',
        'message_id',
        'type',
        'keywords',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function getKeywordsAttribute($value)
    {
        return json_decode($value);
    }

}
