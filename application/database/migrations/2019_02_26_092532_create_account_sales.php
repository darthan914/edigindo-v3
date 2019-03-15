<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice', 20);
            $table->date('date');
            $table->unsignedInteger('spk_id');
            $table->unsignedInteger('sales_id');
            $table->text('note')->nullable();
            $table->datetime('datetime_order')->nullable();
            $table->datetime('datetime_invoice')->nullable();
            $table->datetime('datetime_closed')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('spk_id')->references('id')->on('spk')->onDelete('cascade');
            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('account_sales');
    }
}
