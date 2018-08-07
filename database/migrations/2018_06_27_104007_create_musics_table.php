<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('musics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('artist_id');
            $table->string("name",500);
            $table->string("slug",100)->unique();
            $table->string("path",500)->nullable();
            $table->string("thumb",500)->nullable();
            $table->string("size",255)->nullable();
            $table->string("extension",255)->nullable();
            $table->string('type',100)->nullable();
            $table->string('version',255)->nullable();
            $table->string('description')->nullable();
            $table->float('price')->nullable();
            $table->dateTime('day_of_creation')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('sort')->nullable();
            $table->timestamps();
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('musics');
    }
}
