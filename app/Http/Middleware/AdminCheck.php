<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Garante que o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Verifica se o usuário logado possui permissão de administrador.
        // Assume que a coluna 'is_admin' na tabela users é 1 (true) para administradores.
        if (Auth::user()->is_admin) {
            return $next($request);
        }

        // 3. Se estiver logado, mas não for admin, nega o acesso e redireciona
        return redirect('/')->with('error', 'Acesso negado. Apenas administradores podem acessar esta função.');
    }
}
