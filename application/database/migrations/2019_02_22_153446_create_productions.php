<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('spk_id');
            $table->string('name', 191);
            $table->unsignedInteger('division_id');
            $table->string('source');
            $table->datetime('deadline');
            $table->integer('quantity');
            $table->double('hm');
            $table->double('he')->default(0);
            $table->double('hj');
            $table->boolean('free');
            $table->boolean('profitable');
            $table->text('detail')->nullable();

            $table->integer('count_finish')->default(0);
            $table->datetime('datetime_finish')->nullable();

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
        Schema::dropIfExists('productions');
    }
}
