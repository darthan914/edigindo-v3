<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountSalesDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_sales_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_sales_id');
            $table->unsignedInteger('account_id');
            $table->text('item')->nullable();
            $table->double('value');
            $table->integer('quantity');
            $table->integer('discount');
            $table->integer('ppn');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_sales_id')->references('id')->on('account_sales')->onDelete('cascade');
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
        Schema::dropIfExists('account_sales_details');
    }
}
