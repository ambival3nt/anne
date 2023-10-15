<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LLM extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_name',
        'active_prompt_id',
        'is_active',
    ];

    protected $table = 'models';

    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

    /**
     * @param $promptIndex
     * @return void
     */
    public function getPromptByType($promptIndex){
       $this->prompts()->where('prompt_type', $promptIndex)->first();
    }

    public function initializeDBModels(){
        $gpt35 = $this->firstOrCreate([
            'model_name' => 'gpt-3.5',
            'active_prompt_id' => 1,
            'is_active' => 0,
        ]);
        $instruct = $this->firstOrCreate([
            'model_name' => 'instruct-3.5',
            'active_prompt_id' => 4,
            'is_active' => 1,
        ]);
        $gpt4 = $this->firstOrCreate([
            'model_name' => 'gpt-4',
            'active_prompt_id' => 1,
            'is_active' => 0,
        ]);

        if($gpt35 && $instruct && $gpt4){
            return true;
        }else{
            return false;
        }
    }

}
