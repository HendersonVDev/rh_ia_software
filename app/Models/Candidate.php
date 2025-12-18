<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Campos que podem ser preenchidos em massa.
     *
     * CORREÇÃO: 'phone' foi alterado para 'phone_number' para corresponder à coluna do BD.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number', // <--- CORRIGIDO: Deve ser 'phone_number'
        'applied_role',
        'rating',
        'summary',
        'experience_years',
        'skills',
        'recruiter_status', // <--- NOVO CAMPO ADICIONADO para o Status do Recrutador
        // Adicione 'user_id' ou outros campos se existirem na sua tabela 'candidates'
    ];

    /**
     * The attributes that should be cast to native types.
     * Conversões de tipo para garantir que JSON e floats sejam lidos corretamente.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'skills' => 'json', // Converte a string JSON do banco em um array PHP
        'rating' => 'float', // Garante que o rating seja lido como número decimal.
    ];

    /**
     * Relação 1: 'vacancyScores' - Usado para carregar os scores de vaga.
     * Assumindo que o Model se chama 'CandidateVacancyScore'.
     */
    public function vacancyScores(): HasMany
    {
        return $this->hasMany(CandidateVacancyScore::class);
    }

    /**
     * Relação 2: 'files' - Usado para carregar os currículos (Resumes).
     * O DashboardController usa 'files', que deve apontar para o Resume Model.
     */
    public function files(): HasMany
    {
        return $this->hasMany(Resume::class);
    }
}
