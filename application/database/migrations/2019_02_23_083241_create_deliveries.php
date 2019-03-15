<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name', 191);
            $table->string('no_spk', 10)->nullable();
            $table->string('company', 191)->nullable();
            $table->string('brand', 191)->nullable();
            $table->string('pic_name', 191)->nullable();
            $table->string('pic_phone', 191)->nullable();
            $table->text('pickup_address')->nullable();
            $table->double('pickup_latitude')->nullable();
            $table->double('pickup_longitude')->nullable();
            $table->text('send_to_address')->nullable();
            $table->double('send_to_latitude')->nullable();
            $table->double('send_to_longitude_send_to')->nullable();
            $table->string('city', 20);
            $table->string('via', 10);
            $table->datetime('datetime_send');
            $table->boolean('ppn');
            $table->string('task', 10);

            $table->unsignedInteger('courier_id')->nullable();
            $table->string('courier_name', 191)->nullable();
            $table->string('status', 10)->default('WAITING')->comment('[WAITING, TAKEN, SENDING, ARRIVED, APPROVED, REJECTED]');
            $table->datetime('datetime_start')->nullable();
            $table->double('start_latitude')->nullable();
            $table->double('start_longitude')->nullable();
            $table->datetime('datetime_end')->nullable();
            $table->double('end_latitude')->nullable();
            $table->double('end_longitude')->nullable();

            $table->integer('rating')->nullable();
            $table->text('photo_document')->nullable();
            $table->string('reason', 191)->nullable();
            $table->string('received_by', 191)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
}
