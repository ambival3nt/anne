<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText('prompt')->nullable();
            $table->string('prompt_type')->default('invalid');
            $table->integer('model_id')->nullable();
            $table->tinyInteger('is_active')->nullable();
        });

        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('model_name')->default('No model name set.');
            $table->integer('active_prompt_id')->nullable();
            $table->tinyInteger('is_active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('models');
    }
};
