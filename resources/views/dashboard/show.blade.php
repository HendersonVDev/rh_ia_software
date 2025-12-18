@extends('layouts.app')

@section('title', 'Perfil do Candidato')

@section('scripts')
{{-- CDN do Chart.js para desenhar o Gráfico de Radar --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // --- LÓGICA DE EXTRAÇÃO DE DADOS (RENDERIZADA PELO BLADE) ---
    @php
        // Extrai as habilidades do array de análise ou do campo skills do candidato
        $skills_data = $analysis['Habilidades_Chave'] ?? $candidate->skills;

        if (is_string($skills_data)) {
            $skills_data = json_decode($skills_data, true);
        }

        // Garante que é um array para iteração
        $skills_data = is_array($skills_data) ? $skills_data : [];

        // Cria um array de labels (nomes das skills)
        $labels = array_map(function($s) {
            // Remove caracteres indesejados antes de limitar a string
            $s = str_replace(['[', ']', '"'], '', $s);
            return \Illuminate\Support\Str::limit($s, 15);
        }, $skills_data);

        // Cria um array de dados (atribuímos o valor 5 para todas as skills para fins visuais)
        $data_points = array_fill(0, count($labels), 5);

        // Estrutura final dos dados do gráfico
        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Proficiência (Escala 1-5)',
                    'data' => $data_points,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)', // Azul claro
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'pointBackgroundColor' => 'rgba(54, 162, 235, 1)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2
                ]
            ]
        ];
    @endphp

    // Variável JS populada pelo Blade, eliminando a função PHP aninhada
    const chartData = @json($chartData);


    // JQuery Ready para garantir a configuração e o Chart.js
    $(document).ready(function() {
        // Token CSRF do Laravel para requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 🚨 DELEGAÇÃO DE EVENTOS: Garante que o clique funcione.
        $(document).on('click', '.btn-recruiter-action', function() {
            const $button = $(this);
            const action = $button.data('action'); // APROVAR, ESPERA, REJEITAR
            const candidateName = "{{ $analysis['Nome_Completo'] ?? $candidate->name }}";

            // Desabilita todos os botões para evitar cliques duplos
            $('.btn-recruiter-action').prop('disabled', true).addClass('disabled');

            // Exibe feedback
            const feedbackArea = $('#feedback-message');
            feedbackArea.html(`<div class="alert alert-info py-2">Registrando decisão...</div>`);

            // Chamada AJAX para o Controller
            $.ajax({
                url: "{{ route('candidate.update_status', $candidate->id) }}",
                method: 'POST',
                data: { action: action },
                success: function(response) {
                    // 1. Atualiza o feedback com sucesso
                    feedbackArea.html(`<div class="alert alert-success py-2">✅ ${candidateName}: Status atualizado para <strong>${response.newStatus}</strong>!</div>`);

                    // 2. Atualiza o badge de status do recrutador na view
                    $('#recruiter-status-label').text(response.newStatus);

                    // 3. Atualiza a cor do badge (opcional, mas profissional)
                    let badgeClass = 'bg-secondary';
                    if (response.newStatus === 'Entrevista Agendada') {
                        badgeClass = 'bg-success';
                    } else if (response.newStatus === 'Em Espera') {
                        badgeClass = 'bg-warning text-dark';
                    } else if (response.newStatus === 'Rejeitado') {
                        badgeClass = 'bg-danger';
                    } else if (response.newStatus === 'Triagem Inicial') {
                        badgeClass = 'bg-info';
                    }
                    $('#recruiter-status-display').removeClass('bg-secondary bg-success bg-warning text-dark bg-danger bg-info').addClass(badgeClass);

                    // 4. Re-habilita os botões (mantém desabilitado se APROVADO ou REJEITADO)
                    if (response.newStatus !== 'Entrevista Agendada' && response.newStatus !== 'Rejeitado') {
                         $('.btn-recruiter-action').prop('disabled', false).removeClass('disabled');
                    }
                },
                error: function(xhr) {
                    // Em caso de erro
                    let errorMessage = 'Ocorreu um erro desconhecido.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    feedbackArea.html(`<div class="alert alert-danger py-2">❌ Erro ao atualizar status: ${errorMessage}</div>`);
                    // Re-habilita em caso de falha de comunicação/servidor
                    $('.btn-recruiter-action').prop('disabled', false).removeClass('disabled');
                }
            });
        });
    });

    // Lógica de inicialização do Chart.js
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('skillsRadarChart');
        const data = chartData; // Usa a variável populada pelo Blade acima

        if (data.labels.length === 0) {
            // Se não houver dados, oculta o canvas e adiciona uma mensagem de fallback
            if(ctx) {
                ctx.style.display = 'none';
                const container = ctx.closest('.col-md-7');
                if (container) {
                    container.innerHTML = '<div class="alert alert-secondary text-center">Gráfico indisponível: A IA não forneceu habilidades chave para análise visual.</div>';
                }
            }
            return;
        }

        if (ctx) {
            new Chart(ctx, {
                type: 'radar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { display: false },
                            suggestedMin: 0,
                            suggestedMax: 5, // Define a escala máxima de proficiência
                            pointLabels: {
                                font: { size: 10 }
                            },
                            ticks: {
                                display: false // Oculta os números da escala
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index' }
                    }
                }
            });
        }
    });
</script>
@endsection

@section('content')

<div class="container py-4">
    <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i> Voltar para o Dashboard
    </a>

    {{-- Aviso de Fallback (Se o Controller enviou um aviso) --}}
    @if (isset($analysis['Aviso']))
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Atenção na Análise!</h4>
            <p>{{ $analysis['Aviso'] }}</p>
            <hr>
            <p class="mb-0">Alguns dados estão sendo exibidos a partir das colunas normalizadas (`candidates`) e podem estar incompletos.</p>
        </div>
    @endif

    <div class="card p-4 shadow-lg border-0">
        <h1 class="mb-2 display-4">{{ $analysis['Nome_Completo'] ?? $candidate->name }}</h1>

        {{-- FEEDBACK DA AÇÃO (Será preenchido via AJAX) --}}
        <div id="feedback-message" class="mb-3"></div>

        {{-- BOTÕES DE AÇÃO DE WORKFLOW --}}
        <div class="mb-4 d-flex gap-2 align-items-center">
            <button
                class="btn btn-success btn-recruiter-action"
                data-action="APROVAR"
                data-candidate-id="{{ $candidate->id }}"
                title="Mover para a próxima fase do recrutamento: Entrevista Agendada"
            >
                <i class="fas fa-check-circle me-1"></i> Aprovar (Entrevista)
            </button>
            <button
                class="btn btn-warning text-dark btn-recruiter-action"
                data-action="ESPERA"
                data-candidate-id="{{ $candidate->id }}"
                title="Manter candidato em espera: Em Espera"
            >
                <i class="fas fa-pause-circle me-1"></i> Em Espera
            </button>
            <button
                class="btn btn-danger btn-recruiter-action"
                data-action="REJEITAR"
                data-candidate-id="{{ $candidate->id }}"
                title="Rejeitar ou encerrar processo: Rejeitado"
            >
                <i class="fas fa-times-circle me-1"></i> Rejeitar
            </button>

            {{-- Exibição do status atual do recrutador (NOVO) --}}
            @php
                $status_rh = $candidate->recruiter_status ?? 'Triagem Inicial';
                $rh_badge_class = match($status_rh) {
                    'Entrevista Agendada' => 'bg-success',
                    'Em Espera' => 'bg-warning text-dark',
                    'Rejeitado' => 'bg-danger',
                    default => 'bg-info', // Triagem Inicial
                };
            @endphp

            <span class="ms-auto badge {{ $rh_badge_class }} d-flex align-items-center" id="recruiter-status-display">
                <i class="fas fa-info-circle me-1"></i> Status Recrutador:
                <span class="ms-1 fw-bold" id="recruiter-status-label">{{ $status_rh }}</span>
            </span>
        </div>
        {{-- FIM DOS BOTÕES DE AÇÃO --}}

        <div class="row mb-4 g-4">
            {{-- COLUNA 1: Dados de Contato, Foco e Score --}}
            <div class="col-md-5 border-end">
                <h4 class="border-bottom pb-2 mb-3 text-primary">Dados de Contato e Foco</h4>

                <div class="mb-2">
                    <strong>Score Relevância (IA):</strong>
                    @php
                           // Garantindo o formato 7.0 / 10.0
                         $score_display = $analysis['Score_Relevancia'] ?? (number_format($candidate->rating, 1));
                         $score_color = $candidate->rating >= 7.0 ? '#28a745' : ($candidate->rating >= 4.0 ? '#ffc107' : '#dc3545');
                    @endphp
                    <span class="h3 font-weight-bolder ms-2" style="color: {{ $score_color }};">
                           {{ $score_display }} / 10.0
                    </span>
                </div>

                <div class="mb-2"><strong>Email:</strong> {{ $candidate->email ?? '—' }}</div>

                {{-- USANDO DADOS DO ANALYSIS PARA FOCO --}}
                <div class="mb-2">
                    <strong>Área de Foco (IA):</strong>
                    <span class="badge bg-primary">{{ $analysis['Area_Foco'] ?? $candidate->applied_role }}</span>
                </div>

                <div class="mb-2"><strong>Experiência (anos):</strong> {{ $candidate->experience_years ?? '—' }}</div>

                {{-- Skills Chave (Tags/Badges) --}}
                <div class="mb-3">
                    <strong class="d-block mb-2">Habilidades Chave (IA):</strong>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            // Tenta pegar do analysis primeiro, depois do candidate
                            $skills = $analysis['Habilidades_Chave'] ?? (is_string($candidate->skills) ? json_decode($candidate->skills, true) : $candidate->skills);
                        @endphp

                        @if(is_array($skills) && count($skills) > 0)
                            @foreach($skills as $s)
                                <span class="badge bg-info text-dark me-1 py-1 px-2">{{ $s }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">— Nenhuma habilidade chave identificada.</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- COLUNA 2: GRÁFICO DE SKILLS (VISUAL) --}}
            <div class="col-md-7">
                <h4 class="border-bottom pb-2 mb-3 text-success">Perfil de Habilidades (Visual IA)</h4>

                <div class="d-flex justify-content-center">
                    <canvas id="skillsRadarChart" style="max-height: 350px;"></canvas>
                </div>
                <p class="text-muted small mt-2 text-center">O gráfico de radar ilustra as principais habilidades listadas pela IA.</p>
            </div>
        </div>

        <hr class="my-4">

        {{-- SEÇÃO DE RESUMO EXECUTIVO (Usando o Summary do Analysis/Candidate) --}}
        <section class="mb-4">
            <h4 class="border-bottom pb-2 mb-3">Resumo Executivo (IA)</h4>
            <p class="text-secondary">{{ $analysis['Resumo_Executivo_IA'] ?? $candidate->summary ?? 'Nenhum resumo disponível.' }}</p>
        </section>

        <hr class="my-4">

        {{-- SEÇÕES DETALHADAS: EXPERIÊNCIA E FORMAÇÃO (USANDO $ANALYSIS) --}}
        <div class="row mb-4 g-4">

            {{-- EXPERIÊNCIA PROFISSIONAL --}}
            <div class="col-md-6">
                <h4 class="border-bottom pb-2 mb-3 text-secondary">Experiência Profissional</h4>
                @php
                    $experiencias = $analysis['Experiencia_Profissional'] ?? [];
                @endphp
                @forelse ($experiencias as $exp)
                    <div class="mb-4 border-start border-3 ps-3">
                        <h5 class="fw-bold text-dark">{{ $exp['Cargo'] ?? 'Cargo Desconhecido' }}</h5>
                        <p class="mb-0 text-muted">{{ $exp['Empresa'] ?? '' }}</p>
                        <p class="text-muted small">{{ $exp['Periodo'] ?? 'N/A' }}</p>
                        @if (isset($exp['Descricao']))
                            <p class="text-secondary small">{{ $exp['Descricao'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small">Nenhuma experiência profissional detalhada pela IA no campo `analysis_result`.</p>
                @endforelse
            </div>

            {{-- FORMAÇÃO ACADÊMICA --}}
            <div class="col-md-6">
                <h4 class="border-bottom pb-2 mb-3 text-secondary">Formação Acadêmica</h4>
                @php
                    $formacoes = $analysis['Formacao_Academica'] ?? [];
                @endphp
                @forelse ($formacoes as $formacao)
                    <div class="mb-4 border-start border-3 ps-3">
                        <h5 class="fw-bold text-dark">{{ $formacao['Curso'] ?? 'Curso Desconhecido' }}</h5>
                        <p class="mb-0 text-muted">{{ $formacao['Instituicao'] ?? '' }}</p>
                        <p class="text-muted small">{{ $formacao['Periodo'] ?? 'N/A' }}</p>
                    </div>
                @empty
                    <p class="text-muted small">Nenhuma formação acadêmica detalhada pela IA no campo `analysis_result`.</p>
                @endforelse
            </div>
        </div>

        <hr class="my-4">

        {{-- ANÁLISES DETALHADAS DA IA --}}
        <div class="row g-4">
            {{-- Pontos Fortes --}}
            <div class="col-md-4">
                <div class="p-3 rounded border border-success bg-light">
                    <h5 class="text-success mb-2"><i class="fas fa-thumbs-up me-2"></i> Pontos Fortes</h5>
                    <p class="small text-dark">{{ $analysis['Pontos_Fortes_Candidato'] ?? 'Análise indisponível.' }}</p>
                </div>
            </div>

            {{-- Pontos Fracos/Atenção --}}
            <div class="col-md-4">
                <div class="p-3 rounded border border-danger bg-light">
                    <h5 class="text-danger mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Pontos de Atenção</h5>
                    <p class="small text-dark">{{ $analysis['Pontos_Fracos_Candidato'] ?? 'Análise indisponível.' }}</p>
                </div>
            </div>

            {{-- Recomendação --}}
            <div class="col-md-4">
                <div class="p-3 rounded border border-info bg-light">
                    <h5 class="text-info mb-2"><i class="fas fa-lightbulb me-2"></i> Recomendação IA</h5>
                    <p class="small text-dark mb-1">
                        <span class="fw-bold">Próxima Etapa Sugerida:</span> {{ $analysis['Recomendacao_Proxima_Etapa'] ?? 'Análise indisponível.' }}
                    </p>
                    <p class="small text-dark mb-0">
                        <span class="fw-bold">Motivação para a Vaga:</span> {{ $analysis['Motivacao_Para_Vaga'] ?? 'Análise indisponível.' }}
                    </p>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Seção de Arquivos (MANTIDA) --}}
        <section>
            <h4 class="border-bottom pb-2 mb-3">Arquivos Anexados (Currículos)</h4>
            <ul class="list-group list-group-flush">
                @forelse($candidate->files as $f)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ asset('storage/' . $f->file_path) }}" target="_blank" class="text-info font-weight-bold">
                            <i class="fas fa-file-alt me-2"></i> {{ $f->file_name }} (Processado em: {{ $f->updated_at->format('d/m/Y H:i') }})
                        </a>
                        @if($f->status === 'Concluído')
                            <span class="badge bg-success ms-2">Análise Concluída</span>
                        @elseif($f->status === 'Falha')
                            <span class="badge bg-danger ms-2">Análise Falhou</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2">Em Processamento</span>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted">Nenhum arquivo encontrado.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
