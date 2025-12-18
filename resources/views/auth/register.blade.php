@extends('layouts.app')

@section('title', 'Cadastrar Novo Recrutador')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">Cadastro de Novo Recrutador</h4>
                    <p class="small mb-0">Disponível apenas para Administradores.</p>
                </div>

                <div class="card-body p-4">

                    {{-- Exibe a mensagem de sucesso após o cadastro (do AuthController) --}}
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Este formulário envia para a rota POST /register --}}
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        {{-- Nome --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Endereço de Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Senha --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirmação de Senha --}}
                        <div class="mb-4">
                            <label for="password-confirm" class="form-label">Confirmar Senha</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        {{-- Botão de Cadastro --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                Cadastrar Recrutador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
