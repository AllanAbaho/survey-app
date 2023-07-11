<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('survey.database.tables.surveys'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('practice_name');
            $table->string('assessment_officer');
            $table->string('reporting_officer');
            $table->string('next_review_date');
            $table->json('settings')->nullable();
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
        Schema::dropIfExists(config('survey.database.tables.surveys'));
    }
}
