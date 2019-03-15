<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListCars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->date('stnk')->nullable();
            $table->date('kir1')->nullable();
            $table->date('kir2')->nullable();
            $table->date('gps')->nullable();
            $table->date('insurance')->nullable();
            $table->date('date_km')->nullable();
            $table->string('weekly_km')->nullable();
            $table->string('paper_km')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cars');
    }
}
