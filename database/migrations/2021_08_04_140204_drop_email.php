<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashiers', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('password');
            $table->dropColumn('email_verified_at');
            $table->dropColumn('remember_token');
            $table->string('pin');
            $table->string('picture');
            $table->string('account_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashiers', function (Blueprint $table) {
            //
        });
    }
}
