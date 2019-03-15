<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('pic_id')->nullable()->comment('CLIENT');
            $table->string('pic_name', 191)->nullable()->comment('PROSPECT');
            $table->string('activity', 20)->comment('[PRESENTATION, FOLLOWUP, SAMPLE, QUOTATION, PO]');
            $table->datetime('datetime_activity');
            $table->string('status', 20)->comment('[WAITING, MEETING, FEEDBACK, FINISH]');

            $table->datetime('datetime_check_in')->nullable();
            $table->double('check_in_latitude')->nullable();
            $table->double('check_in_longitude')->nullable();

            $table->datetime('datetime_check_out')->nullable();
            $table->double('check_out_latitude')->nullable();
            $table->double('check_out_longitude')->nullable();

            $table->string('feedback_token', 30)->nullable();
            $table->string('feedback_email', 191)->nullable();
            $table->string('feedback_phone', 15)->nullable();

            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->text('option_performance')->nullable();
            $table->boolean('recommendation')->nullable();
            $table->text('recommendation_yes')->nullable();
            $table->text('recommendation_no')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pic_id')->references('id')->on('pic')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_details');
    }
}
