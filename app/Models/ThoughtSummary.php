<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThoughtSummary extends Model
{
    use HasFactory;

    protected $table = 'anne_thought_summary';
    protected $fillable = [
        'message_id',
        'response_id',
        'summary'
    ];

    public function message()
    {
        return $this->belongsTo(Messages::class);
    }

    public function response()
    {
        return $this->belongsTo(AnneMessages::class);
    }
}
