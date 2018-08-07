<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token')->nullable();
            $table->string('first_name',500)->nullable();
            $table->string('last_name',500)->nullable();;
            $table->string('email',100)->unique();
            $table->string('password');
            $table->string('contact_number',100)->nullable();
            $table->string('photo',500)->nullable();
            $table->tinyInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_sys')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
