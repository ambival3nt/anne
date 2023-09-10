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
        Schema::create('trivia_players', function (Blueprint $table) {
            $table->bigInteger('user_id')->primary();
            $table->integer('score')->default(0);
            $table->string('last_answer')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create('trivia_game', function (Blueprint $table) {
            $table->id();
            $table->string('leader')->nullable();
            $table->string('channel')->nullable();
            $table->time('start_time');
            $table->string('question')->nullable();
            $table->string('answer')->nullable();
            $table->integer('round')->nullable();
            $table->mediumText('question_blob')->nullable();
            $table->timestamps();
        });

        Schema::create('trivia_scores', function (Blueprint $table) {
            $table->bigInteger('user_id')->primary();
            $table->integer('total_score')->default(0);
            $table->integer('total_games_played')->default(0);
            $table->integer('total_game_wins')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trivia_players');
        Schema::dropIfExists('trivia_game');
        Schema::dropIfExists('trivia_scores');
    }
};
