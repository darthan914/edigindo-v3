<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_document', 30)->unique();
            $table->string('name', 191);
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('brand_id')->nullable();
            $table->unsignedInteger('pic_id');
            $table->string('additional_phone')->nullable();
            $table->text('address');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('division_id');
            $table->datetime('date_offer');
            $table->integer('ppn')->default(0);
            $table->text('note')->nullable();
            $table->double('total_price')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('pic_id')->references('id')->on('pic')->onDelete('cascade');
            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
