<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDesignCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('design_candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('design_request_id');
            $table->unsignedInteger('designer_id');
            $table->text('description');
            $table->string('status', 20)->default('WAITING')->comment('[PENDING, SELECTED, REJECTED, REVISION]');
            $table->text('preview')->nullable()->comment('ARRAY');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('designer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('design_request_id')->references('id')->on('design_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('design_candidates');
    }
}
