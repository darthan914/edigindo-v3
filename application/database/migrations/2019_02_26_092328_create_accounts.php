<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

class CreateAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('relation', 20)->comment('[PARENT, CHILD]');
            $table->unsignedInteger('account_class_id');
            $table->unsignedInteger('account_type_id');
            $table->string('number', 10)->unique();
            $table->string('name', 10);
            $table->double('value')->nullable();
            $table->boolean('active')->default(1);

            NestedSet::columns($table);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_class_id')->references('id')->on('account_classes')->onDelete('cascade');
            $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
