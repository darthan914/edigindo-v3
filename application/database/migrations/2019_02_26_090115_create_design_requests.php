<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('design_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('client_id');
            $table->string('name', 191);
            $table->text('description');
            $table->unsignedInteger('division_id');
            $table->double('budget');
            $table->datetime('deadline');
            $table->string('status', 20)->default('WAITING')->comment('[WAITING, APPROVED, CANCEL, FINISH]');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('design_requests');
    }
}
