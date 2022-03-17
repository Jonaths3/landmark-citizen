<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_outs', function (Blueprint $table) {
            $table->id();
            $table->string('tranx_id');
            $table->string('vendor_id');
            $table->string('from_account');
            $table->string('to_account_no');
            $table->string('to_account_name');
            $table->string('to_bank');
            $table->string('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_outs');
    }
}
