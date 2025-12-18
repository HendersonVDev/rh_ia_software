<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory;

    // A tabela é 'resumes' conforme sua imagem
    protected $table = 'resumes';

    protected $fillable = [
        'candidate_id',
        'user_id',
        'file_name',
        'file_path',
        'file_mime_type',
        'status',
        'analysis_result',
        'compatibility_score',
    ];

    /**
     * Define as conversões (casts) de tipos de atributos.
     * O campo 'analysis_result' é lido como JSON e convertido para um ARRAY PHP.
     * O campo 'compatibility_score' é convertido para um número de ponto flutuante.
     * @var array
     */
    protected $casts = [
        'analysis_result' => 'array', // CRUCIAL: O Laravel já o converte para array
        'compatibility_score' => 'float',
    ];

    /**
     * Relação: Um currículo pertence a um candidato.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
