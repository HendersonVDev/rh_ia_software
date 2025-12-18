<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações (Cria a tabela 'resumes').
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para a tabela 'users' (se houver autenticação)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Informações do Arquivo
            $table->string('file_name');
            $table->string('file_path')->unique(); // Caminho onde o arquivo está salvo no storage
            $table->string('file_mime_type')->nullable(); // Ex: application/pdf

            // Status da Análise
            $table->enum('status', ['Pendente', 'Em Análise', 'Concluído', 'Falha'])->default('Pendente');

            // Resultado da Análise da IA (JSON)
            // Armazena a estrutura JSON retornada pelo Job (nome, email, skills, etc.)
            $table->json('analysis_result')->nullable();

            // Coluna de Compatibilidade (Opcional, se você for fazer a pontuação no Job)
            $table->integer('compatibility_score')->nullable(); // Pontuação de compatibilidade (0-10)

            $table->timestamps();
        });
    }

    /**
     * Reverte as migrações (Remove a tabela 'resumes').
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
