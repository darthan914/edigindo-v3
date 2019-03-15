<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountGeneralDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_general_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_general_id');
            $table->unsignedInteger('account_id');
            $table->double('debit')->nullable();
            $table->double('credit')->nullable();
            $table->integer('ppn');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_general_id')->references('id')->on('account_generals')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_general_details');
    }
}
