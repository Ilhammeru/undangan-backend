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
        Schema::table('couple', function (Blueprint $table) {
            $table->dropColumn('male_father');
            $table->dropColumn('male_mother');
            $table->dropColumn('female_father');
            $table->dropColumn('female_mother');
        });

        Schema::table('couple', function (Blueprint $table) {
            $table->text('male_parents')->nullable();
            $table->text('female_parents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('couple', function (Blueprint $table) {
            $table->string('male_father')->nullable();
            $table->string('male_mother')->nullable();
            $table->string('female_father')->nullable();
            $table->string('female_mother')->nullable();
        });

        Schema::table('couple', function (Blueprint $table) {
            $table->dropColumn('male_parents');
            $table->dropColumn('female_parents');
        });
    }
};
