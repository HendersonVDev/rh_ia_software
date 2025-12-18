<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AURUM RH | Login de Acesso</title>

    <!-- Carregamento do Tailwind CSS (via CDN para simplicidade) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configuração do Tailwind para usar uma fonte moderna -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            /* Fundo escuro sutil para um visual profissional e moderno */
            background-color: #1f2937;
        }
        /* Estilo customizado para o botão primário */
        .btn-primary {
            background-color: #10b981; /* Verde esmeralda */
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #059669;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-lg mx-auto p-6 md:p-10">

        <!-- Logo e Título (Usando um dos nomes sugeridos, ex: CogniFlow) -->
        <div class="text-center mb-10">
            <!-- Você pode substituir por um logo PNG ou SVG -->
            <h1 class="text-4xl font-bold text-white mb-2 tracking-wide">
                AURUM<span class="text-[#10b981]">RH</span>
            </h1>
            <p class="text-gray-400">Plataforma de Triagem Inteligente de Currículos</p>
        </div>

        <!-- Card de Login -->
        <div class="bg-white p-8 md:p-12 rounded-xl shadow-2xl border border-gray-700/30">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Acesso Restrito</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Campo Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-600 mb-2">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-red-500 @enderror"
                        placeholder="seu.email@empresa.com"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                    >
                    @error('email')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo Senha -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-2">Senha</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 @error('password') border-red-500 @enderror"
                        placeholder="********"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Lembrar-me e Botão de Login -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Lembrar-me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-emerald-600 hover:text-emerald-500">
                            Esqueceu a senha?
                        </a>
                    @endif
                </div>

                <div>
                    <button type="submit" class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500 focus:ring-opacity-50">
                        Acessar a Plataforma
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center border-t pt-6">
                <p class="text-gray-500 text-sm">
                    Desenvolvido por <span class="font-medium text-gray-700">Henderson Vieira - HVDev</span>
                </p>
            </div>

        </div>
        <!-- Fim do Card de Login -->

    </div>

</body>
</html>
