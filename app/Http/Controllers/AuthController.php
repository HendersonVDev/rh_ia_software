<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * CONTROLLER STUB — VERSÃO PÚBLICA
 *
 * Responsável por demonstrar o fluxo de autenticação
 * (login, logout e cadastro) em modo de simulação.
 *
 * A implementação real de:
 * - Persistência de usuários
 * - Regras administrativas
 * - Políticas de segurança avançadas
 * foi intencionalmente omitida para proteção do domínio.
 */
class AuthController extends Controller
{
    /**
     * Exibe a tela de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa a tentativa de login (STUB).
     *
     * Nesta versão pública, o login é apenas simulado
     * para permitir navegação no dashboard.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // LOGIN SIMULADO
        session([
            'auth_stub' => true,
            'user_stub' => [
                'name' => 'Usuário Demonstração',
                'email' => $request->email,
                'role' => 'recruiter',
            ],
        ]);

        return redirect()->intended(route('dashboard.index'));
    }

    /**
     * Processa o logout (STUB).
     */
    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Cadastro de usuário (STUB).
     *
     * O cadastro real é omitido nesta versão pública.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed'],
        ]);

        return redirect()
            ->route('register')
            ->with('status', 'Cadastro simulado com sucesso (modo demonstração).');
    }
}
