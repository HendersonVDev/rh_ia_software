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
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->string('vacancy_name');
            $table->float('score')->default(0.0);
            $table->timestamps();

            $table->index(['vacancy_name']);
            $table->index(['candidate_id']);
            $table->unique(['candidate_id','vacancy_name']); // um candidato por vaga (evita duplicidade)
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_vacancy_scores');
    }
}
