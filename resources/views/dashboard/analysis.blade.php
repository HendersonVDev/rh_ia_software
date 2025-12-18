@extends('layouts.app')

@php
    // Decodifica o JSON se for uma string (garantia de que a variável de análise é um array/objeto)
    $analysis = $resume->analysis_result;
    if (is_string($analysis)) {
        $analysis = json_decode($analysis, true) ?? [];
    }
    // Nome do candidato
    $candidateName = $analysis['Nome_Completo'] ?? ($resume->file_name ?? 'Candidato');
@endphp

@section('title', 'Análise Detalhada: ' . $candidateName)

@section('styles')
    {{-- Estilos customizados para a visualização, se você usa Bootstrap/CSS Customizado --}}
    <style>
        /* Estilo para a barra de progresso (Score de Relevância) */
        .progress-bar {
            height: 10px;
            border-radius: 9999px;
            overflow: hidden;
            background-color: #e0e0e0;
        }
        .progress-fill {
            height: 100%;
            transition: width 0.5s ease-in-out;
            box-shadow: 0 0 2px rgba(0,0,0,0.1);
        }
        /* Estilo para tags de habilidade */
        .tag-skill {
            display: inline-block;
            padding: 0.25em 0.6em;
            margin-right: 0.5em;
            margin-bottom: 0.5em;
            font-size: 80%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
            color: #4338ca; /* Indigo 700 */
            background-color: #eef2ff; /* Indigo 100 */
        }
    </style>
@endsection

@section('content')
<div class="container py-4">
    <div id="analysis-container" class="max-w-4xl mx-auto">
        {{-- Botão Voltar para o Dashboard --}}
        <a href="{{ route('dashboard.index') }}" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left me-2"></i> Voltar para o Dashboard
        </a>

        <h1 class="mb-4">Análise Detalhada de Currículo</h1>
        <p class="text-muted mb-4">Análise gerada pela IA para o arquivo: <strong>{{ $resume->file_name }}</strong></p>

        <!-- Card Principal da Análise -->
        <div id="analysis-card" class="card p-4 shadow-lg border-0">

            <header class="mb-4 border-bottom pb-3">
                <h2 class="text-2xl font-weight-bold text-dark" id="candidate-name">Carregando Nome...</h2>
                <p class="text-lg text-primary font-weight-bold" id="candidate-role">Área de Foco</p>
                <p class="text-muted" id="candidate-email">email@exemplo.com</p>
            </header>

            <!-- Seção de Métricas (Score de Relevância) -->
            <div class="row g-4 mb-4">

                <!-- Card de Avaliação (Score de Relevância) -->
                <div class="col-md-12">
                    <div class="card p-4 bg-light border-success border-2 shadow-sm">
                        <h3 class="h5 mb-2 d-flex align-items-center">
                            Score de Relevância (IA)
                            <span class="ml-2 h4 mb-0" id="score-emoji">🌟</span>
                        </h3>
                        <p class="h1 font-weight-bolder text-success mb-2" id="score-value">0%</p>
                        <div class="progress-bar">
                            <div id="score-fill" class="progress-fill bg-success" style="width: 0%"></div>
                        </div>
                        <p class="text-muted small mt-2">
                            Métrica que avalia o nível de adequação do perfil do candidato à vaga (0-100%).
                        </p>
                    </div>
                </div>

                <!-- O Card de Compatibilidade foi removido para focar na métrica única (Score_Relevancia) -->

            </div>

            <!-- Seção de Habilidades -->
            <section class="mb-4">
                <h3 class="h4 border-bottom pb-2 mb-3">Habilidades Chave</h3>
                <div id="skills-list">
                    {{-- Tags de Habilidades serão injetadas aqui --}}
                </div>
                <p id="no-skills-message" class="text-muted" style="display:none;">Nenhuma habilidade chave identificada pela IA.</p>
            </section>

            <!-- Seção de Experiência (Resumo da IA) -->
            <section>
                <h3 class="h4 border-bottom pb-2 mb-3">Resumo Executivo (Gerado pela IA)</h3>
                <p class="text-secondary leading-relaxed" id="profile-summary">
                    O resumo da experiência e qualificações será carregado aqui.
                </p>
            </section>

        </div>
    </div>
</div>

<script>
    // INJEÇÃO DE DADOS DO LARAVEL
    // A única linha Blade é esta, que injeta o JSON
    const analysisData = @json($analysis);
</script>

@verbatim
<script>
    // Função principal para carregar os dados
    function loadAnalysis(data) {
        // Campos Mapeados do Novo Schema JSON:

        const name = data.Nome_Completo || 'Nome Não Informado';
        const areaFoco = data.Area_Foco || 'Área de Foco Não Informada';
        const email = data.Email || 'Email Não Informado';
        const resumo = data.Resumo_Executivo_IA || 'Resumo não disponível.';
        const scoreValue = parseInt(data.Score_Relevancia) || 0;
        const skills = data.Habilidades_Chave || []; // Assumindo o novo nome de chave

        // Headers e Resumo
        document.getElementById('candidate-name').textContent = name;
        document.getElementById('candidate-role').textContent = areaFoco;
        document.getElementById('candidate-email').textContent = email;
        document.getElementById('profile-summary').textContent = resumo;


        // Métricas (Score de Relevância - 0 a 100%)
        const scoreFill = document.getElementById('score-fill');
        const scorePercentage = Math.min(100, scoreValue);

        document.getElementById('score-value').textContent = `${scoreValue}%`;
        scoreFill.style.width = `${scorePercentage}%`;

        const scoreEmoji = document.getElementById('score-emoji');
        const scoreFillElement = document.getElementById('score-fill');

        // Lógica de cores e emojis baseada no Score (0-100)
        scoreFillElement.className = 'progress-fill';
        if (scoreValue >= 90) {
            scoreEmoji.textContent = '👑';
            scoreFillElement.classList.add('bg-success');
        } else if (scoreValue >= 60) {
            scoreEmoji.textContent = '⭐';
            scoreFillElement.classList.add('bg-warning');
        } else {
            scoreEmoji.textContent = '🔍';
            scoreFillElement.classList.add('bg-danger');
        }


        // Habilidades
        const skillsList = document.getElementById('skills-list');
        skillsList.innerHTML = '';

        if (skills && Array.isArray(skills) && skills.length > 0) {
            skills.forEach(skill => {
                const skillTag = document.createElement('span');
                skillTag.className = 'tag-skill';
                skillTag.textContent = skill;
                skillsList.appendChild(skillTag);
            });
            document.getElementById('no-skills-message').style.display = 'none';
        } else {
            document.getElementById('no-skills-message').style.display = 'block';
        }
    }

    // Carrega os dados reais injetados pelo Blade
    document.addEventListener('DOMContentLoaded', () => {
        // analysisData é definido fora do @verbatim
        if (typeof analysisData !== 'undefined' && analysisData && Object.keys(analysisData).length > 0) {
            loadAnalysis(analysisData);
        } else {
            // Se a análise estiver vazia ou for nula, mostra uma mensagem de erro na tela
            document.getElementById('analysis-container').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <strong>Erro!</strong> Dados de análise não encontrados ou currículo ainda não foi processado com sucesso.
                    <p class="small mt-2">Verifique se o Job de processamento foi concluído ou se o arquivo JSON da IA está estruturado corretamente.</p>
                </div>
                <a href="{{ route('dashboard.index') }}" class="btn btn-secondary mt-3">
                    <i class="fas fa-arrow-left me-2"></i> Voltar para o Dashboard
                </a>
            `;
        }
    });

</script>
@endverbatim
@endsection
