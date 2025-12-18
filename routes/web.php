<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResumeUploadController;
use App\Http\Controllers\ImapController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AdminCheck; // ADICIONADO: Importa a classe AdminCheck

// ===============================================
// ROTAS DE AUTENTICAÇÃO (LOGIN / LOGOUT)
// ===============================================

// Rota que mostra a tela de login (GET /login)
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard.index');
    }
    return view('auth.login');
})->name('login');

// Rota que processa a tentativa de login (POST /login)
Route::post('/login', [AuthController::class, 'login']);

// Rota de Logout (POST /logout)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ===============================================
// ROTAS DE CADASTRO (PROTEGIDA POR ADMIN USANDO FQCN)
// ===============================================

// Rota que processa o cadastro de um novo usuário (POST /register)
// Mantemos fora do middleware para que a rota POST esteja sempre acessível para processar o formulário.
Route::post('/register', [AuthController::class, 'register']);

// Rota que mostra o formulário de cadastro (GET /register)
// CORRIGIDO: Usamos o FQCN [AdminCheck::class] no lugar do alias ['admin'] para evitar o erro de resolução.
Route::middleware([AdminCheck::class])->group(function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});


// ===============================================
// ROTAS PROTEGIDAS (APENAS PARA USUÁRIOS AUTENTICADOS)
// Adiciona o middleware 'auth' para todas as rotas do Dashboard
// ===============================================
Route::middleware(['auth'])->group(function () {

    // ROTA PRINCIPAL: Dashboard e Filtros de Status (GET /)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // ROTA DE DETALHES DO CANDIDATO (GET /candidate/{resume})
    Route::get('/candidate/{resume}', [DashboardController::class, 'show'])->name('dashboard.show');

    // ROTA AJAX: Para buscar a lista de candidatos por status para o Modal (GET /candidates/by-status)
    Route::get('/candidates/by-status', [DashboardController::class, 'getCandidatesByStatus'])
        ->name('candidates.by_status');

    // ROTA POST: Para registrar a decisão do recrutador (POST /candidate/{candidateId}/status)
    Route::post('/candidate/{candidateId}/status', [DashboardController::class, 'updateRecruiterStatus'])
        ->name('candidate.update_status');

    // Rota POST para receber o arquivo e disparar o Job (POST /upload-resume)
    Route::post('/upload-resume', [ResumeUploadController::class, 'upload'])->name('resume.upload');

    // ===============================================
    // ROTAS IMAP (Para Teste e Processamento de E-mail)
    // ===============================================

    // 1. Rota para TESTAR a conexão IMAP
    Route::get('/imap/test', [ImapController::class, 'testConnection'])->name('imap.test');

    // 2. Rota para BUSCAR, PROCESSAR e RETORNAR o JSON dos candidatos.
    Route::get('/imap/process', [ImapController::class, 'getCurriculosApi'])->name('imap.process');
});
