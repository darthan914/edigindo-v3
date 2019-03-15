<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesigners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('designers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('designer_id');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('process', 10)->comment('[TEASER,FA]');
            $table->unsignedInteger('spk_id')->nullable();

            $table->string('status', 20)->default('WAITING')->comment('[WAITING, DESIGNER_REJECT, SALES_REJECT, FINISH, APPROVED]');
            $table->datetime('datetime_start')->nullable();
            $table->datetime('datetime_end')->nullable();
            $table->text('note_designer')->nullable();
            $table->text('note_sales')->nullable();

            $table->datetime('datetime_approved')->nullable();
            $table->integer('rating')->nullable();

            $table->integer('revision')->default(0);
            $table->unsignedInteger('revision_list_designer_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('designer_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('designers');
    }
}
