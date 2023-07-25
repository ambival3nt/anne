<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    public function nameMapping($currentAlias, $personId=null, $personName=null){

      try {
          Log::debug('Current Alias: ' . $currentAlias . ' Person ID: ' . $personId . ' Person Name: ' . $personName);

          if ($currentAlias === '') {
              return false;
          }

          $this->recent_alias = $currentAlias;
          $this->save();


          if (!$personId || !$personName) {
              try {
                  $mapping = PeopleNameMapping::firstOrCreate([
                      'person_id' => $this->id,
                      'username' => $this->name,
                      'alias' => $currentAlias,
                  ]);
              } catch (\Exception $e) {
                  Log::debug($e->getMessage());
                  return $e->getMessage() . ' L' . $e->getLine();
              }
          } else {
              $mapping = PeopleNameMapping::firstOrCreate([
                  'person_id' => $personId,
                  'username' => $personName,
                  'alias' => $currentAlias,
              ]);
          }
      }catch(\Exception $e){
          Log::debug($e->getMessage());
      }
        return $mapping;
    }

    public function mappedNames(){
        return $this->hasMany(PeopleNameMapping::class, 'person_id', 'id');
    }

    public function getNameList(){
        return $this->mappedNames()->pluck('alias')->toArray();
    }
}
