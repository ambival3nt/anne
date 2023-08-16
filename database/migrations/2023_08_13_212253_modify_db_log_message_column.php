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
        Schema::table('log_messages', function (Blueprint $table) {
            $table->dropColumn('message');
        });

        Schema::table('log_messages', function (Blueprint $table) {
            $table->longText('message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_messages', function (Blueprint $table) {
            $table->dropColumn('message');
        });

        Schema::table('log_messages', function (Blueprint $table) {
            $table->string('message');
        });
    }
};
