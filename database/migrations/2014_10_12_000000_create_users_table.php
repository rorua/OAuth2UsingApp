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
            $table->string('provider');
            $table->string('provider_id');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name');
            $table->string('surname');
            $table->string('avatar')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('birthday')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
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
