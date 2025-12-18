<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidateFilesTable extends Migration
{
    public function up()
    {
        Schema::create('candidate_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidate_files');
    }
}
