<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_bookings', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('stock_id');
            $table->string('name_borrow');
            $table->text('note')->nullable();
            $table->integer('quantity_borrow');
            $table->datetime('datetime_borrow');
            $table->datetime('deadline_borrow');
            $table->string('need', 10)->comment('[OFFICE, EXPO]');
            $table->string('status', 20)->default('BORROWING')->comment('[BORROWING, RETURNED]');
            $table->integer('quantity_return')->default(0);
            $table->datetime('datetime_return')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_bookings');
    }
}
