<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plat', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_code');
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->double('cost',19,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plat');
    }
}
