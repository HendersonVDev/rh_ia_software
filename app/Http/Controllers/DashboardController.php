<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;


class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal com a lista de candidatos e estatísticas.
     */
    public function index(Request $request)
    {
        // 1. OBTÉM AS CONTAGENS GERAIS PARA OS CARDS DO TOPO (Stats)
        // CRÍTICO: Incluir orWhereNull para 'inicial' na contagem total.
        $stats = [
            'total' => Candidate::count(),
            'inicial' => Candidate::where('recruiter_status', 'Triagem Inicial')
                                    ->orWhereNull('recruiter_status') // Inclui NULLs aqui!
                                    ->count(),
            'aprovados' => Candidate::where('recruiter_status', 'Entrevista Agendada')->count(),
            'espera' => Candidate::where('recruiter_status', 'Em Espera')->count(),
            'rejeitados' => Candidate::where('recruiter_status', 'Rejeitado')->count(),
        ];

        // 2. INICIA A QUERY PARA A TABELA DE CANDIDATOS
        $query = Candidate::query()
            ->with(['files' => function ($q) {
                // Carrega o arquivo mais recente para fins de link 'Ver Detalhes'
                $q->latest()->limit(1);
            }])
            ->orderBy('created_at', 'desc');

        // 3. Aplica Filtros de Busca na URL (q, vaga, min_score)
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('vaga')) {
            $query->where('applied_role', 'like', '%' . $request->input('vaga') . '%');
        }

        if ($request->filled('min_score')) {
            $query->where('rating', '>=', (float) $request->input('min_score'));
        }

        // 4. Aplica FILTRO DE STATUS DE RECRUTADOR (Usado pelas Abas)
        $recruiterStatus = $request->input('status', 'ATIVOS'); // Default: ATIVOS
        $currentStatus = $recruiterStatus;

        switch ($recruiterStatus) {
            case 'ENTREVISTA':
                $query->where('recruiter_status', 'Entrevista Agendada');
                break;
            case 'ESPERA':
                $query->where('recruiter_status', 'Em Espera');
                break;
            case 'REJEITADOS':
                $query->where('recruiter_status', 'Rejeitado');
                break;
            case 'INICIAL':
                // CRÍTICO: Filtra explicitamente por Triagem Inicial OU NULL
                $query->where(function ($q) {
                    $q->where('recruiter_status', 'Triagem Inicial')
                      ->orWhereNull('recruiter_status');
                });
                break;
            case 'ATIVOS':
            default:
                // CRÍTICO: ATIVOS deve incluir os NULLs
                $query->where(function ($q) {
                    $q->whereIn('recruiter_status', ['Triagem Inicial', 'Entrevista Agendada', 'Em Espera'])
                      ->orWhereNull('recruiter_status');
                });
                break;
        }

        // 5. Executa a consulta da TABELA (Paginada)
        $candidates = $query->paginate(10)->appends($request->except('page'));

        // 6. Retorna a View
        return view('dashboard.index', compact(
            'candidates',
            'stats',
            'currentStatus'
        ));
    }

    /**
     * Exibe os detalhes da análise de um currículo específico.
     *
     * @param \App\Models\Resume $resume
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Resume $resume)
    {
        // 1. Garante que a análise já foi concluída
        if ($resume->status !== 'Concluído') {
            return back()->with('warning', 'A análise deste currículo ainda está em andamento ou falhou.');
        }

        // Carrega o candidato associado e a coleção de arquivos/resumes dele
        $candidate = $resume->candidate()->with('files')->first();

        if (!$candidate) {
            return back()->with('error', 'Candidato principal não encontrado.');
        }

        // 2. Obtém o resultado da análise.
        $analysis = $resume->analysis_result ?? []; // Usa array vazio como fallback inicial.

        // 3. Fallback: Garante que $analysis seja um array válido E contenha as chaves essenciais
        if (!is_array($analysis) || empty($analysis)) {
             $analysis = [
                 'Nome_Completo' => $candidate->name,
                 'Email' => $candidate->email ?? 'N/A',
                 'Score_Relevancia' => $candidate->rating,
                 'Area_Foco' => $candidate->applied_role,
                 'Resumo_Executivo_IA' => $candidate->summary,
                 'Habilidades_Chave' => $candidate->skills ?? [],
                 'Experiencia_Profissional' => [],
                 'Formacao_Academica' => [],
                 'Pontos_Fortes_Candidato' => 'Análise detalhada indisponível (dados brutos ausentes).',
                 'Pontos_Fracos_Candidato' => 'Análise detalhada indisponível (dados brutos ausentes).',
                 'Motivacao_Para_Vaga' => 'Análise detalhada indisponível (dados brutos ausentes).',
                 'Recomendacao_Proxima_Etapa' => 'Análise detalhada indisponível (dados brutos ausentes).',
                 'Aviso' => 'O campo analysis_result (JSON Bruto) estava inválido ou vazio. Exibindo dados normalizados do Candidato.'
             ];
             \Illuminate\Support\Facades\Log::warning("JSON de análise do currículo #{$resume->id} estava vazio ou inválido. Exibindo dados normalizados.");
        }

        // 4. Retorna a view
        return view('dashboard.show', [
            'resume' => $resume, // O currículo que está sendo visualizado
            'analysis' => $analysis, // Dados brutos da análise (ou fallback)
            'candidate' => $candidate // Candidato e seus currículos (relação files)
        ]);
    }

    /**
     * Registra a decisão do recrutador (Aprovar, Esperar, Rejeitar) - Usada no show.blade.php
     * @param Request $request
     * @param int $candidateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRecruiterStatus(Request $request, int $candidateId)
    {
        $candidate = Candidate::find($candidateId);

        if (!$candidate) {
            return response()->json(['success' => false, 'message' => 'Candidato não encontrado.'], 404);
        }

        $request->validate([
            'action' => 'required|in:APROVAR,ESPERA,REJEITAR',
        ]);

        $statusMap = [
            'APROVAR' => 'Entrevista Agendada',
            'ESPERA' => 'Em Espera',
            'REJEITAR' => 'Rejeitado',
        ];

        $newStatus = $statusMap[$request->input('action')];

        // Atualiza o status
        $candidate->recruiter_status = $newStatus;
        $candidate->save();

        return response()->json([
            'success' => true,
            'message' => "Status atualizado para: {$newStatus}",
            'newStatus' => $newStatus,
        ]);
    }

    /**
     * Busca candidatos por status via AJAX para exibição em modal (Drill-Down).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCandidatesByStatus(Request $request)
    {
        $statusKey = $request->query('status');

        // Mapeamento das chaves da URL para os valores do ENUM no BD
        $statusMap = [
            'total' => null, // Não filtra status, pega todos
            'inicial' => 'Triagem Inicial',
            'aprovados' => 'Entrevista Agendada',
            'espera' => 'Em Espera',
            'rejeitados' => 'Rejeitado',
        ];

        $dbStatus = $statusMap[$statusKey] ?? null;

        $query = Candidate::query();

        // CRÍTICO: Se for 'inicial', inclui NULLs
        if ($statusKey === 'inicial') {
            $query->where(function ($q) {
                $q->where('recruiter_status', 'Triagem Inicial')
                  ->orWhereNull('recruiter_status'); // CRÍTICO: Inclui NULLs
            });
        } elseif ($dbStatus) {
             // Para Aprovados, Espera e Rejeitados
            $query->where('recruiter_status', $dbStatus);
        } elseif ($statusKey === 'total') {
            // Se for 'total', não aplica filtro de status (pega todos)
        } else {
            // Status inválido ou não coberto, retorna vazio
            return response()->json(['candidates' => []]);
        }


        // Seleciona apenas os campos necessários para o modal
        $candidates = $query
            ->select('id', 'name', 'applied_role', 'rating')
            ->orderBy('rating', 'desc')
            ->get();

        // Adiciona o ID do Resume mais recente para o link 'Detalhes'
        $candidates->each(function ($candidate) {
            $latestResume = $candidate->files()->latest()->first();
            $candidate->latest_resume_id = $latestResume ? $latestResume->id : null;
        });

        return response()->json(['candidates' => $candidates]);
    }
}
