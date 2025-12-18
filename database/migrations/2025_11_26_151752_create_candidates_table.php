<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('candidates', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('applied_role')->nullable();
        $table->integer('experience_years')->nullable();
        $table->json('skills')->nullable();
        $table->float('rating')->default(0);
        $table->text('summary')->nullable(); // pequeno resumo gerado pela IA
        $table->timestamps();
    });
}

};
