<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeopleNameMapping extends Model
{
    use HasFactory;

    protected $table = 'people_name_mapping';

    protected $fillable = [
        'person_id',
        'username',
        'alias',
    ];

    public function person(){
        return $this->belongsTo(Person::class);
    }
}
