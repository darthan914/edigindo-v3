<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('offer_id');
            $table->string('name', 191);
            $table->text('detail')->nullable();
            $table->integer('quantity');
            $table->string('unit', 10)->nullable();
            $table->double('value');
            $table->text('photo')->nullable();

            $table->string('status', 10)->default('WAITING')->comment('[WAITING, CANCEL, SUCCESS, FAILED]');
            $table->string('reason', 191)->nullable()->comment('[PRICING, TIMELINE, OTHER]');
            $table->string('note_other', 191)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_details');
    }
}
