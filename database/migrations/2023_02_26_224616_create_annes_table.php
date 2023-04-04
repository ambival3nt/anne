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
        Schema::create('anne', function (Blueprint $table) {
            $table->id();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('last_message');
            $table->string('last_user');
            $table->string('last_response');
            $table->boolean('earmuffs');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('anne');
    }
};
