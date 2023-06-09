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
        Schema::table('playlist', function (Blueprint $table) {
            $table->string('artist')->nullable()->default('Unknown');
            $table->integer('user_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlist', function (Blueprint $table) {
            $table->dropColumn('artist');
            $table->dropColumn('user_id');
        });

    }
};
