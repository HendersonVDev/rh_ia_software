<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Adiciona a nova coluna para rastrear o status de triagem do RH
            $table->enum('recruiter_status', ['Triagem Inicial', 'Entrevista Agendada', 'Em Espera', 'Rejeitado'])
                  ->default('Triagem Inicial')
                  ->after('rating') // Colocado após o campo rating
                  ->comment('Status do candidato definido pelo recrutador no fluxo de RH.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('recruiter_status');
        });
    }
};
