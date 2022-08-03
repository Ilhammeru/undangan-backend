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
        Schema::create('couple_main_setting', function (Blueprint $table) {
            $table->id();
            $table->integer('couple_id');
            $table->string('link')->nullable();
            $table->integer('theme_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('reception_time_zone')->nullable();
            $table->date('reception_date')->nullable();
            $table->string('reception_start')->nullable();
            $table->string('reception_end')->nullable();
            $table->boolean('reception_until_finish')->nullable();
            $table->text('reception_address')->nullable();
            $table->text('reception_embed_maps')->nullable();
            $table->date('contract_date')->nullable();
            $table->boolean('contract_date_is_same_with_reception')->nullable();
            $table->string('contract_time_zone')->nullable();
            $table->string('contract_start')->nullable();
            $table->string('contract_end')->nullable();
            $table->boolean('contract_until_finish')->nullable();
            $table->text('contract_address')->nullable();
            $table->text('contract_address_is_same_with_reception')->nullable();
            $table->text('contract_embed_maps')->nullable();
            $table->text('contract_embed_maps_is_same_with_reception')->nullable();
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
        Schema::dropIfExists('couple_main_setting');
    }
};
