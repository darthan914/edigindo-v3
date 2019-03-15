<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

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
            $table->increments('id');
            $table->string('username', 191)->unique();
            $table->string('email', 191)->unique();
            $table->string('password', 191);
            $table->unsignedInteger('position_id');
            $table->unsignedInteger('division_id')->nullable();
            $table->integer('no_ae');

            $table->string('first_name', 191);
            $table->string('last_name', 191)->nullable();
            $table->string('phone', 15)->nullable();

            $table->text('photo')->nullable();
            $table->text('signature')->nullable();

            $table->boolean('active')->default(0);
            
            $table->text('grant')->nullable();
            $table->text('denied')->nullable();

            $table->string('verification', 30)->nullable();
            $table->string('forgot_password', 30)->nullable();
            $table->datetime('expired_forgot_password')->nullable();

            NestedSet::columns($table);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');;
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');;
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
