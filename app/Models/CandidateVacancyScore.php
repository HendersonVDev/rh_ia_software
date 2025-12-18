<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateVacancyScore extends Model
{
    use HasFactory;

    // Garante que o nome da tabela esteja correto (se for plural, o Laravel já descobre)
    protected $table = 'candidate_vacancy_scores';

    protected $fillable = [
        'candidate_id',
        'vacancy_name',
        'score',
        // Outros campos...
    ];
}
