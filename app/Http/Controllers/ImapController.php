<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * CONTROLLER STUB — VERSÃO PÚBLICA
 *
 * Responsável por simular a ingestão de currículos via e-mail
 * e o processamento automatizado para triagem.
 *
 * A implementação real de:
 * - Conexão IMAP
 * - Extração de anexos reais
 * - Integração com IA generativa
 * foi intencionalmente omitida por conter lógica proprietária.
 */
class ImapController extends Controller
{
    /**
     * MOCK de mensagem de currículo para ambiente público.
     */
    protected function mockCurriculoMessage()
    {
        return (object) [
            'getSubject' => fn() => 'Candidatura - Exemplo Público',
            'getAttachments' => fn() => collect([
                (object)[
                    'getName' => fn() => 'curriculo_stub.pdf',
                    'save' => function ($path) {
                        Storage::put($path, 'Conteúdo fictício de currículo (stub).');
                        return true;
                    }
                ]
            ]),
        ];
    }

    /**
     * STUB da análise automatizada por IA.
     *
     * A implementação real (prompt, parsing e avaliação)
     * foi removida nesta versão pública.
     */
    protected function callAIStub(string $filePath, string $context): array
    {
        return [
            'Resumo_Executivo_IA' => 'Análise indisponível na versão pública.',
            'Score_Relevancia' => 0,
            'Area_Foco' => 'N/A',
            'Confianca_Extracao' => 0.0,
            'Nome_Completo' => 'Candidato Exemplo',
            'Email_Contato' => 'stub@example.com',
            'Telefone' => '',
            'Localizacao' => '',
            'Cargo_Desejado' => '',
            'Experiencia_Total_Anos' => 0,
            'Experiencia' => [],
            'Educacao' => [],
            'Habilidades_Tecnicas' => [],
            'Habilidades_Comportamentais' => [],
        ];
    }

    /**
     * Endpoint público de teste.
     * Simula a disponibilidade do pipeline IMAP.
     */
    public function testConnection()
    {
        return response()->json([
            'success' => true,
            'message' => 'IMAP STUB: Pipeline ativo em modo demonstração.',
            'is_stub' => true,
        ]);
    }

    /**
     * API pública para simular ingestão e processamento de currículos.
     */
    public function getCurriculosApi()
    {
        $curriculos = collect([
            $this->mockCurriculoMessage(),
            $this->mockCurriculoMessage(),
        ]);

        $candidatos_processados = [];

        foreach ($curriculos as $index => $message) {
            $attachment = $message->getAttachments()->first();

            if (!$attachment) {
                continue;
            }

            try {
                // 1. Persistência fictícia do currículo
                $fileName = 'stub_curriculo_' . uniqid() . '.pdf';
                $path = 'imap-stub/' . $fileName;
                $attachment->save($path);

                // 2. Chamada de IA STUB
                $analise = $this->callAIStub($path, $message->getSubject());

                // 3. Montagem do payload público
                $candidatos_processados[] = array_merge([
                    'id' => 'stub_' . uniqid(),
                    'timestamp' => now()->toDateTimeString(),
                    'nome_arquivo' => $fileName,
                    'url_pdf' => Storage::url($path),
                ], $analise);

            } catch (Exception $e) {
                Log::error('Erro no ImapController STUB: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'modo' => 'STUB / DEMONSTRAÇÃO',
            'total_candidatos' => count($candidatos_processados),
            'candidatos' => $candidatos_processados,
        ]);
    }
}
