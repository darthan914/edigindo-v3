<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 20)->comment('[CLIENT, PROSPECT]');
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('company_id')->nullable()->comment('CLIENT');
            $table->unsignedInteger('brand_id')->nullable()->comment('CLIENT');
            $table->unsignedInteger('pic_id')->nullable()->comment('CLIENT');
            $table->unsignedInteger('address_id')->nullable()->comment('CLIENT');

            $table->string('company_name')->nullable()->comment('PROSPECT');
            $table->string('company_phone')->nullable()->comment('PROSPECT');
            $table->string('company_fax')->nullable()->comment('PROSPECT');

            $table->string('pic_first_name')->nullable()->comment('PROSPECT');
            $table->string('pic_last_name')->nullable()->comment('PROSPECT');
            $table->string('pic_gender')->nullable()->comment('PROSPECT');
            $table->string('pic_position')->nullable()->comment('PROSPECT');
            $table->string('pic_phone')->nullable()->comment('PROSPECT');
            $table->string('pic_email')->nullable()->comment('PROSPECT');

            $table->string('address')->nullable()->comment('PROSPECT');
            $table->string('latitude')->nullable()->comment('PROSPECT');
            $table->string('longitude')->nullable()->comment('PROSPECT');
            
            $table->string('brand')->nullable()->comment('PROSPECT');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('pic_id')->references('id')->on('pic')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm');
    }
}
