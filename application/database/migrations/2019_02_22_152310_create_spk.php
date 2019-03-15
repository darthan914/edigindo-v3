<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spk', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->string('name', 191);
            $table->string('no_spk', 10)->unique();
            $table->unsignedInteger('main_division_id');
            $table->date('date_spk');
            
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('brand_id')->nullable();
            $table->unsignedInteger('pic_id');
            $table->text('address');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('additional_phone')->nullable();
            
            $table->integer('ppn')->default(0);
            $table->boolean('do_transaction')->default(0);
            $table->text('note')->nullable();
            $table->datetime('finish_spk_at')->nullable();
            $table->boolean('quality')->nullable();
            $table->text('comment')->nullable();
            
            $table->datetime('datetime_confirm')->nullable();

            $table->integer('code_admin')->default(0);
            $table->text('address_for_admin')->nullable();
            $table->text('note_invoice')->nullable();
            $table->boolean('check_master')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');;
            $table->foreign('main_division_id')->references('id')->on('divisions')->onDelete('cascade');;
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');;
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');;
            $table->foreign('pic_id')->references('id')->on('pic')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spk');
    }
}
