<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountPurchasings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_purchasings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('no_po', 20);
            $table->date('date');
            $table->unsignedInteger('spk_id');
            $table->unsignedInteger('supplier_id');
            $table->text('note')->nullable();
            $table->datetime('datetime_order')->nullable();
            $table->datetime('datetime_open_bill')->nullable();
            $table->datetime('datetime_debit')->nullable();
            $table->datetime('datetime_closed')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('spk_id')->references('id')->on('spk')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
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
        Schema::dropIfExists('account_purchasings');
    }
}
