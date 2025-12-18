<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;
use App\Jobs\AnalyzeResumeJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Importa Storage para melhor controle de disco

class ResumeUploadController extends Controller
{
    public function upload(Request $request)
    {
        // 1. VALIDAÇÃO (Adicionando a validação para 'job_context')
        $request->validate([
            'resume_file' => 'required|file|mimes:pdf,doc,docx|max:5120',
            // O contexto da vaga é opcional, mas se vier, deve ser uma string
            'job_context' => 'nullable|string|max:255',
        ]);

        // 2. OBTENÇÃO DO ARQUIVO SEGURO E DO CONTEXTO DA VAGA
        $uploadedFile = $request->file('resume_file');
        // Captura o contexto de análise, usando um valor padrão se não for fornecido
        $jobContext = $request->input('job_context', 'Desenvolvedor Backend Pleno');

        if (!$uploadedFile) {
            Log::error("Tentativa de upload de currículo falhou: arquivo 'resume_file' ausente após validação.");
            return back()->with('error', 'Falha no processamento do arquivo. Por favor, tente novamente.');
        }

        // Variável de controle para limpeza
        $filePath = null;

        try {
            // 3. ARMAZENAMENTO NO DISCO
            $filePath = $uploadedFile->store('resumes');

            // 4. CRIAÇÃO DO OBJETO RESUME (O contexto da vaga NÃO é salvo aqui,
            // ele é apenas passado para o Job. O Job armazena a vaga na tabela Candidates.)
            $resume = Resume::create([
                'user_id' => Auth::id(),
                'file_name' => $uploadedFile->getClientOriginalName(),
                'file_path' => $filePath,
                'status' => 'Pendente', 
            ]);

            // 5. DISPARO DO JOB (Passando o $jobContext)
            AnalyzeResumeJob::dispatch($resume->id, $jobContext);

            return redirect()->route('dashboard.index')->with('success', 'Currículo enviado com sucesso. A análise será processada em breve e o contexto de análise é: ' . $jobContext);

        } catch (\Exception $e) {
            // Limpa o arquivo do disco se a criação do registro falhar
            if ($filePath) {
                Storage::delete($filePath);
            }
            Log::error("Erro no upload e disparo do Job: " . $e->getMessage());
            return back()->with('error', 'Erro no processamento do arquivo. Por favor, tente novamente. Detalhe: ' . $e->getMessage());
        }
    }
}
