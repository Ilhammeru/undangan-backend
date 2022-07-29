<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('couple', function (Blueprint $table) {
            $table->id();
            $table->string('male_nickname')->nullable();
            $table->string('male_name')->nullable();
            $table->text('male_photo')->nullable();
            $table->string('male_father')->nullable();
            $table->string('male_mother')->nullable();
            $table->string('male_instagram')->nullable();
            $table->text('male_address')->nullable();
            $table->string('female_nickname')->nullable();
            $table->string('female_name')->nullable();
            $table->text('female_photo')->nullable();
            $table->string('female_father')->nullable();
            $table->string('female_mother')->nullable();
            $table->string('female_instagram')->nullable();
            $table->text('female_address')->nullable();
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
        Schema::dropIfExists('couple');
    }
};
