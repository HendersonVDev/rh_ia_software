<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateFile extends Model
{
    protected $fillable = [
        'candidate_id',
        'file_path',
        'original_name'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
