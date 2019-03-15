<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_estimator', 10)->unique();
            $table->unsignedInteger('user_estimator_id');

            $table->unsignedInteger('sales_id');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->text('photo')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_estimator_id')->references('id')->on('users')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimators');
    }
}
