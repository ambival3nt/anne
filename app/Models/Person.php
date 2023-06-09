<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = [
        'name',
        'id',
        'last_message',
        'last_response',
        'last_message_time',
        'message_count',
        'recent_alias',
    ];

    public function nameMapping($currentAlias){
        $this->recent_alias = $currentAlias;
        $this->save();

        PeopleNameMapping::firstOrCreate([
            'person_id' => $this->id,
            'username' => $this->name,
            'alias' => $currentAlias,
        ]);
    }
}
