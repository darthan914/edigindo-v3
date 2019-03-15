<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pr_id');
            $table->string('item', 191);
            $table->integer('quantity');
            $table->string('unit', 10)->nullable();
            $table->datetime('datetime_request');
            $table->unsignedInteger('purchasing_id');
            $table->string('status', 10)->default('WAITING')->comment('[WAITING, CONFIRMED, REJECTED, REVISION]');
            $table->boolean('service')->default(0);
            $table->datetime('datetime_confirm')->nullable();
            $table->double('value')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchasing_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pr_id')->references('id')->on('pr')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pr_details');
    }
}
