<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CRÍTICO: Token CSRF para requisições AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Aurum RH-AI | @yield('title')</title>

    {{-- Bootstrap CSS (REMOVIDO INTEGRITY CONFLITANTE) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    {{-- Font Awesome (REMOVIDO INTEGRITY CONFLITANTE) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Estilos globais */
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        /* Estilo para o botão de acesso na barra de navegação */
        .btn-primary.nav-link {
            color: white !important; /* Força a cor branca para texto em botões primários */
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('dashboard.index') }}">AURUM RH</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">

                        {{-- VERIFICA SE O USUÁRIO ESTÁ LOGADO --}}
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard.index') }}">Dashboard</a>
                            </li>

                            {{-- NOVO: LINK DE CADASTRO, VISÍVEL APENAS SE FOR ADMIN --}}
                            {{-- Usa o helper Auth::user() para acessar a coluna is_admin --}}
                            @if (Auth::user()->is_admin ?? false)
                            <li class="nav-item">
                                <a class="nav-link btn btn-sm btn-info text-white py-1 px-2 rounded me-2" href="{{ route('register') }}" title="Cadastrar Novo Recrutador/Admin">
                                    <i class="fas fa-user-plus me-1"></i> Cadastrar Usuário
                                </a>
                            </li>
                            @endif

                            <li class="nav-item">
                                {{-- Formulário de Logout --}}
                                <form action="{{ route('logout') }}" method="POST" class="d-flex">
                                    @csrf
                                    <button type="submit" class="btn btn-link nav-link text-white-50 p-2 me-2">
                                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                                    </button>
                                </form>
                            </li>
                        @else
                            {{-- Se não estiver autenticado, mostra o link de Login --}}
                             <li class="nav-item">
                                <a class="nav-link btn btn-primary py-1 px-3 rounded" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-1"></i> Acessar
                                </a>
                            </li>
                        @endauth

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4">
        @yield('content')
    </main>

    {{-- CRÍTICO: JQUERY (REMOVIDO INTEGRITY CONFLITANTE) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

    {{-- CRÍTICO: BOOTSTRAP JS (REMOVIDO INTEGRITY CONFLITANTE) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    {{-- CORRIGIDO: Usa @yield('scripts') para injetar os scripts específicos do Dashboard/Show --}}
    @yield('scripts')

</body>
</html>
