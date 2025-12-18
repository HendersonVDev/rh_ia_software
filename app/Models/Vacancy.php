<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public function candidateScores()
    {
        return $this->hasMany(CandidateVacancyScore::class, 'vacancy_name', 'name');
    }
}
