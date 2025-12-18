@extends('layouts.app')

@section('title')
    Painel
@endsection

@section('content')
{{-- INJEÇÃO CRÍTICA PARA ÍCONES --}}
{{-- O Font Awesome é necessário para o ícone do WhatsApp e ícones de status/loading --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .card-status-clickable {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .card-status-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>

<h1 class="mb-4">Painel de Candidatos</h1>

<!-- 1. SEÇÃO DE ESTATÍSTICAS (CLICÁVEL) -->
<div class="row g-3 mb-4">
    @php
        // Usa o array $stats do Controller
        $statusCards = [
            'total' => ['label' => 'Total de Candidatos (Base)', 'count' => $stats['total'] ?? 0, 'bg' => 'bg-secondary', 'data-key' => 'total', 'icon' => 'fas fa-users'],
            'inicial' => ['label' => 'Novos para Triagem', 'count' => $stats['inicial'] ?? 0, 'bg' => 'bg-info', 'data-key' => 'inicial', 'icon' => 'fas fa-inbox'],
            'aprovados' => ['label' => 'Aprovados (Entrevista Agendada)', 'count' => $stats['aprovados'] ?? 0, 'bg' => 'bg-success', 'data-key' => 'aprovados', 'icon' => 'fas fa-user-check'],
            'espera' => ['label' => 'Em Espera', 'count' => $stats['espera'] ?? 0, 'bg' => 'bg-warning text-dark', 'data-key' => 'espera', 'icon' => 'fas fa-hourglass-half'],
        ];
    @endphp

    @foreach ($statusCards as $card)
        <div class="col-md-3">
            <div
                class="card card-brief p-3 text-white card-status-clickable {{ $card['bg'] }}"
                data-status-key="{{ $card['data-key'] }}"
                data-status-label="{{ $card['label'] }}"
                data-bs-toggle="modal"
                data-bs-target="#candidatesModal"
            >
                <div class="small d-flex align-items-center">
                    <i class="{{ $card['icon'] }} me-2"></i> {{ $card['label'] }}
                </div>
                <h2>{{ $card['count'] }}</h2>
                <span class="small opacity-75 mt-1">Clique para ver a lista</span>
            </div>
        </div>
    @endforeach
</div>

<!-- 2. SEÇÃO DE UPLOAD E MENSAGENS -->
<div class="card p-4 mb-4 shadow-sm">
    <h4 class="mb-3">Processamento de Currículos e E-mails</h4>

    <!-- Mensagens de Feedback (Laravel Sessions) -->
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            <p><strong>Sucesso!</strong> {{ session('success') }}</p>
            <p class="small">O processamento está em andamento na fila. O candidato aparecerá aqui em breve.</p>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            <strong>Erro!</strong> {{ session('error') }}
        </div>
    @endif

    <!-- BLOCO: Processamento IMAP -->
    <div class="p-3 bg-light rounded mb-4 border border-info">
        <h5 class="mb-2 text-info">Processar Caixa de Entrada (IMAP)</h5>
        <p class="small text-muted mb-3">
            Clique para se conectar à caixa de entrada configurada (`{{ env('IMAP_USERNAME', 'N/A') }}`) e extrair/analisar novos currículos automaticamente.
            <strong class="text-danger">Atenção:</strong> No modo de simulação, isso adicionará currículos mockados para teste.
        </p>
        <a href="{{ route('imap.process') }}" class="btn btn-info btn-sm">
            <i class="fas fa-sync-alt"></i> Iniciar Processamento IMAP
        </a>
        <a href="{{ route('imap.test') }}" target="_blank" class="btn btn-outline-info btn-sm">
            <i class="fas fa-plug"></i> Testar Conexão
        </a>
    </div>

    <!-- Formulário de Upload Manual -->
    <h5 class="mb-3 mt-4">Enviar Currículo Manualmente</h5>
    <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
        @csrf

        <!-- CAMPO 2: ARQUIVO -->
        <div class="col-md-5">
            <label for="resume_file" class="form-label">Arquivo (.pdf, .doc, .docx, max 5MB)</label>
            <input
                type="file"
                name="resume_file"
                id="resume_file"
                class="form-control @error('resume_file') is-invalid @enderror"
                required
            >
            @error('resume_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- CAMPO 3: BOTÃO DE ENVIO -->
        <div class="col-md-2">
            <button
                type="submit"
                class="btn btn-primary w-100"
            >
                Analisar e Enviar
            </button>
        </div>
    </form>
</div>
<!-- FIM DA SEÇÃO DE UPLOAD -->

<!-- 3. SEÇÃO DE FILTRO E ABAS DE TRIAGEM -->
<div class="card p-3 shadow-sm mb-4">
    <h4 class="mb-3">Filtrar Candidatos Existentes</h4>

    {{-- ABAS DE STATUS DE TRIAGEM --}}
    @php
        // Cria um helper para manter os filtros de busca/vaga ativos ao trocar de aba de status
        $filterParams = request()->only(['q', 'vaga', 'min_score']);

        $statusLinks = [
            'ATIVOS' => 'Todos Ativos',
            'INICIAL' => 'Novos para Triagem',
            'ENTREVISTA' => 'Aprovados',
            'ESPERA' => 'Em Espera',
            'REJEITADOS' => 'Rejeitados (Histórico)',
        ];

        // Mapeia o statusKey para o Label usado na exibição (para evitar Undefined Variable)
        $currentStatusLabel = $statusLinks[$currentStatus] ?? 'Todos Ativos';
    @endphp

    <ul class="nav nav-pills mb-3">
        @foreach ($statusLinks as $statusKey => $statusLabel)
            <li class="nav-item me-2">
                <a
                    class="nav-link @if($currentStatus === $statusKey) active @else bg-light text-dark @endif"
                    href="{{ route('dashboard.index', array_merge($filterParams, ['status' => $statusKey])) }}"
                >
                    {{ $statusLabel }} ({{ $stats[strtolower($statusKey)] ?? ($statusKey === 'ATIVOS' ? ($stats['inicial'] + $stats['aprovados'] + $stats['espera']) : 0) }})
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Formulário de Filtro e Busca --}}
    <form class="row g-2 align-items-center" method="GET">
        {{-- Campo de status oculto para manter o filtro ao aplicar a busca/vaga --}}
        <input type="hidden" name="status" value="{{ $currentStatus }}">

        <div class="col-md-4">
            <label class="form-label visually-hidden" for="q">Buscar por nome</label>
            <input class="form-control" id="q" name="q" placeholder="Buscar por nome..." value="{{ request('q') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label visually-hidden" for="vaga">Vaga</label>
            <input class="form-control" id="vaga" name="vaga" placeholder="Vaga (ex: Analista de Dados)" value="{{ request('vaga') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label visually-hidden" for="min_score">Score Mínimo</label>
            <input class="form-control" id="min_score" name="min_score" placeholder="Min Score (Ex: 7.0)" value="{{ request('min_score') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-secondary" type="submit">
                <i class="fas fa-filter"></i> Aplicar Filtro
            </button>
        </div>
        @if(request()->anyFilled(['q', 'vaga', 'min_score']) || $currentStatus !== 'ATIVOS')
        <div class="col-auto">
            <a href="{{ route('dashboard.index', ['status' => 'ATIVOS']) }}" class="btn btn-outline-danger">Limpar Filtros</a>
        </div>
        @endif
    </form>
</div>
<!-- FIM DA SEÇÃO DE FILTRO E ABAS -->

<!-- 4. TABELA DE CANDIDATOS -->
<div class="card p-3 shadow-sm">
    <h4 class="mb-3">Candidatos - Status:
        <span class="badge
            @if($currentStatus === 'ENTREVISTA') bg-success
            @elseif($currentStatus === 'ESPERA') bg-warning text-dark
            @elseif($currentStatus === 'REJEITADOS') bg-danger
            @elseif($currentStatus === 'INICIAL') bg-info
            @else bg-secondary
            @endif"
        >{{ $currentStatusLabel }}</span>
    </h4>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Área de Foco (IA)</th>
                    <th>Score Relevância</th>
                    <th>Resumo Executivo (IA)</th>
                    <th>Status RH</th> {{-- NOVO: Status do Recrutador --}}
                    <th>Contato</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($candidates as $c)
                    @php
                        // Carrega o Resume mais recente (limitado a 1 no Controller)
                        $resume = $c->files->first();

                        $ia_score = $c->rating;
                        $ia_foco = $c->applied_role;
                        $ia_resumo = $c->summary;
                        $ia_nome = $c->name;
                        $ia_telefone = $c->phone_number;

                        // NOVO: Status do Recrutador (vem do Model Candidate)
                        $status_rh = $c->recruiter_status ?? 'Triagem Inicial';

                        // Lógica de cor do badge de Score
                        $badge_class = 'bg-secondary';
                        if ($ia_score !== null) {
                            $score = (float) $ia_score;
                            if ($score >= 7.0) {
                                $badge_class = 'bg-success';
                            } elseif ($score >= 4.0) {
                                $badge_class = 'bg-warning text-dark';
                            } else {
                                $badge_class = 'bg-danger';
                            }
                        }

                        // Lógica de cor do badge de Status RH
                        $rh_badge_class = match($status_rh) {
                            'Entrevista Agendada' => 'bg-success',
                            'Em Espera' => 'bg-warning text-dark',
                            'Rejeitado' => 'bg-danger',
                            default => 'bg-info', // Triagem Inicial
                        };

                        // Apenas permite ver detalhes se o Job de Análise estiver concluído (e há um currículo)
                        $is_completed = ($resume?->status === 'Concluído') && !empty($ia_score);
                    @endphp
                    <tr>
                        {{-- Nome --}}
                        <td class="font-weight-bold">
                            {{ $ia_nome ?? 'Candidato Desconhecido' }}
                        </td>

                        {{-- Área de Foco (IA) --}}
                        <td>{{ $ia_foco ?? 'Aguardando Análise' }}</td>

                        {{-- Score de Relevância (IA) --}}
                        <td>
                            @if($ia_score !== null)
                                <span class="badge {{ $badge_class }}">{{ number_format($ia_score, 1) }}</span>
                            @else
                                <span class="badge bg-secondary">Aguardando</span>
                            @endif
                        </td>

                        {{-- Resumo Executivo (IA) --}}
                        <td class="text-muted small" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                            {{ Str::limit($ia_resumo, 40) ?? 'N/A' }}
                        </td>

                        {{-- Status RH (NOVO) --}}
                        <td>
                            <span class="badge {{ $rh_badge_class }}">
                                {{ $status_rh }}
                            </span>
                        </td>

                        {{-- Contato (WhatsApp) --}}
                        <td>
                            @if($ia_telefone)
                                @php
                                    $clean_phone = preg_replace('/[^0-9]/', '', $ia_telefone);
                                    $whatsapp_text = urlencode("Olá {$ia_nome}, vi seu currículo para a vaga de {$ia_foco} e gostaria de conversar!");
                                    $whatsapp_link = "https://wa.me/55{$clean_phone}?text={$whatsapp_text}";
                                @endphp
                                <a href="{{ $whatsapp_link }}" target="_blank" class="btn btn-sm btn-outline-success" title="Iniciar Conversa no WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @else
                                <button class="btn btn-sm btn-outline-secondary disabled" title="Telefone não encontrado no currículo">
                                    <i class="fab fa-whatsapp"></i> N/A
                                </button>
                            @endif
                        </td>


                        {{-- Ações/Detalhes --}}
                        <td>
                            @if($is_completed && $resume)
                                {{-- Passa o ID do Resume para a rota 'dashboard.show' --}}
                                <a href="{{ route('dashboard.show', $resume->id) }}" class="btn btn-sm btn-primary">Ver Detalhes</a>
                            @else
                                <a href="#" class="btn btn-sm btn-secondary disabled">Em Processo</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhum candidato encontrado com o status selecionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $candidates->links() }}
    </div>
</div>

<!-- Modal para Exibir a Lista de Candidatos ao Clicar nos Cards (Drill-Down) -->
<div class="modal fade" id="candidatesModal" tabindex="-1" aria-labelledby="candidatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="candidatesModalLabel">Lista de Candidatos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-candidates-list">
                    <p class="text-center text-muted">Carregando candidatos...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- Scripts para Interatividade do Modal (IMPORTANTE: Deve ser incluído onde o jQuery e Bootstrap JS estão disponíveis) --}}
@section('scripts')
<script>
    // Token CSRF (Se você estiver usando esta seção no layout, deve haver uma tag <meta name="csrf-token"> no <head>)
    // Certifique-se de que o jQuery está carregado antes deste script
    if (typeof $ === 'undefined') {
        console.error("jQuery não está carregado. O Modal Drill-Down não funcionará.");
    } else {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Lógica de clique nos cards de estatísticas para abrir o modal
            $('.card-status-clickable').on('click', function() {
                const statusKey = $(this).data('status-key');
                const statusLabel = $(this).data('status-label');
                const modalTitle = $('#candidatesModalLabel');
                const modalBody = $('#modal-candidates-list');

                modalTitle.text(statusLabel);
                modalBody.html('<p class="text-center text-info"><i class="fas fa-spinner fa-spin me-2"></i> Carregando lista...</p>');

                // Chamada AJAX para o Controller
                $.ajax({
                    url: "{{ route('candidates.by_status') }}",
                    method: 'GET',
                    data: { status: statusKey },
                    success: function(response) {
                        if (response.candidates && response.candidates.length > 0) {
                            let html = '<ul class="list-group list-group-flush">';

                            response.candidates.forEach(candidate => {
                                // Lógica de cor do score
                                const score = candidate.rating || 0.0;
                                const badgeColor = score >= 7.0 ? 'bg-success' : (score >= 4.0 ? 'bg-warning text-dark' : 'bg-danger');

                                // Cria o link de detalhes
                                const detailLink = candidate.latest_resume_id
                                    ? `{{ url('candidate') }}/${candidate.latest_resume_id}`
                                    : '#';

                                html += `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold">${candidate.name}</h6>
                                            <small class="text-muted">${candidate.applied_role || 'Área não definida'}</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge ${badgeColor} me-3">${score.toFixed(1)} / 10.0</span>
                                            <a href="${detailLink}" class="btn btn-sm btn-primary" target="_blank">Ver Detalhes</a>
                                        </div>
                                    </li>
                                `;
                            });

                            html += '</ul>';
                            modalBody.html(html);

                        } else {
                            modalBody.html('<div class="alert alert-secondary text-center">Nenhum candidato encontrado com este status.</div>');
                        }
                    },
                    error: function() {
                        modalBody.html('<div class="alert alert-danger text-center">Erro ao carregar os dados. Verifique o console.</div>');
                    }
                });
            });
        });
    }
</script>
@endsection
