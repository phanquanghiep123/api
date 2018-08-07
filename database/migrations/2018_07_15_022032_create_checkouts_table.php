<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("full_name",255);
            $table->string("email",255);
            $table->unsignedBigInteger('artist_id');  
            $table->float('price');  
            $table->tinyInteger('status');
            $table->string('key',255);
            $table->string('return_url',255)->nullable();
            $table->string('cancel_url',255)->nullable();
            $table->string("payment_option",255);
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
        Schema::dropIfExists('checkouts');
    }
}
