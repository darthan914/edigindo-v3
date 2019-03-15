<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('type', 10)->comment('[ITEM, SERVICES]');
            $table->string('name', 191);
            $table->unsignedInteger('division_id');
            $table->text('attachment')->nullable();
            $table->unsignedInteger('respond_id')->nullable();
            $table->string('feedback', 191)->nullable();
            $table->datetime('datetime_feedback')->nullable();
            $table->datetime('datetime_confirm')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('respond_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('list_requests');
    }
}
