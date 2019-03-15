<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('spk_id');
            $table->datetime('datetime_add_complete')->nullable();

            $table->string('no_invoice', 15)->nullable();
            $table->double('value_invoice')->nullable();
            $table->datetime('datetime_add_invoice')->nullable();

            $table->date('date_faktur')->nullable();
            $table->datetime('datetime_add_faktur')->nullable();

            $table->date('date_received')->nullable();
            $table->datetime('datetime_add_received')->nullable();

            $table->string('no_sending', 20)->nullable();
            $table->text('address_sending')->nullable();
            $table->datetime('datetime_add_sending')->nullable();

            $table->text('note')->nullable();
            $table->boolean('check_finance')->default(0);

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('invoices');
    }
}
