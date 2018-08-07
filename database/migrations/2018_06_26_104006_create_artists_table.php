<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("name",255);
            $table->string("sort_name",255)->nullable();
            $table->string("gender",255)->nullable();
            $table->string("bio")->nullable();;
            $table->string("avatar",255)->nullable();
            $table->string("area",255)->nullable();
            $table->string("type",255)->nullable();
            $table->string("slug",100)->unique();
            $table->dateTime('date_of_birth')->nullable();
            $table->dateTime('begin_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->float("price")->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('artists');
    }
}
