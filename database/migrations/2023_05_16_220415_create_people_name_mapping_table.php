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
        Schema::create('people_name_mapping', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('person_id');
            $table->string('username');
            $table->string('alias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people_name_mapping');
    }
};
