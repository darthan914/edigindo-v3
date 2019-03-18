<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('spk_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->string('type', 10)->default('PROJECT')->comment('[PROJECT, OFFICE, PAYMENT]');

            $table->string('no_pr', 20)->unique();
            $table->string('name', 191);
            $table->datetime('datetime_order');
            $table->unsignedInteger('division_id');
            
            $table->string('barcode', 30);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('spk_id')->references('id')->on('spk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pr');
    }
}
