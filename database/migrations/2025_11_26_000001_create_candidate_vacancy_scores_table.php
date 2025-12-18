<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateVacancyScoresTable extends Migration
{
    public function up()
    {
        Schema::create('candidate_vacancy_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_id');
            $table->string('vacancy_name');
            $table->float('score')->default(0);
            $table->timestamps();

            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
            $table->unique(['candidate_id', 'vacancy_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_vacancy_scores');
    }
}
