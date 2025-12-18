<?php

namespace App\Models;

// Importações importantes para o sistema de autenticação
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Adicionado de volta se estiver faltando

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Os atributos que são preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // CRÍTICO: Permite a inserção em massa do campo 'is_admin'
    ];

    /**
     * Os atributos que devem ser escondidos para serialização.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser convertidos em tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Garante que a senha seja hash automaticamente
        'is_admin' => 'boolean', // Boa prática: Garante que é tratado como booleano
    ];
}
