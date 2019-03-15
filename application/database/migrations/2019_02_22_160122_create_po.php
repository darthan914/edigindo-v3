<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pr_detail_id');
            $table->integer('quantity');
            $table->string('no_po', 20);
            $table->datetime('datetime_po');
            $table->string('type', 10);
            $table->string('bank', 191)->nullable();
            $table->string('name_supplier', 191)->nullable();
            $table->string('no_rekening', 15)->nullable();
            $table->string('name_rekening', 191)->nullable();
            $table->double('value');
            $table->boolean('check_audit')->default(0);
            $table->boolean('check_finance')->default(0);
            $table->text('note_audit')->nullable();
            $table->string('status_received', 12)->default('PROCESSING')->comment('[PROCESSING, CONFIRMED, COMPLAINT]');
            $table->datetime('datetime_received')->nullable();
            $table->text('note_received')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pr_detail_id')->references('id')->on('pr_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('po');
    }
}
