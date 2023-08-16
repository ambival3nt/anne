<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnneLogs extends Model
{
    use HasFactory;

    protected $table='log_messages';

    protected $fillable = [
        'message',
        'level',
        'level_name',
        'logged_at',
        'context',
        'extra'
    ];

}
