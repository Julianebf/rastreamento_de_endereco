<?php

namespace App\Http\Controllers;

use App\Models\Cep;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class EnderecoController extends Controller
{
    /**
     * Exibe a tela de busca (View)
     */
    public function index()
    {
        return view('rastreio');
    }

    /**
     * Processa a busca e o cadastro (Web + API)
     */
    public function store(Request $request)
    {
        // 1. Validação (Aceita CEP com ou sem hífen, mas limpa para 8 dígitos)
        $request->validate([
            'cep' => 'required|string',
            'numero' => 'required|string',
        ]);

        $cepLimpo = preg_replace('/[^0-9]/', '', $request->cep);

        if (strlen($cepLimpo) !== 8) {
            return $this->retornarErro('O CEP deve conter 8 dígitos.', $request);
        }

        try {
            return DB::transaction(function () use ($request, $cepLimpo) {
                // 2. Consulta ViaCep
                $response = Http::get("https://viacep.com.br/ws/{$cepLimpo}/json/");
                
                if ($response->failed() || isset($response->json()['erro'])) {
                    return $this->retornarErro('CEP não encontrado na base do ViaCep.', $request);
                }

                $dados = $response->json();

                // 3. Evita duplicação de CEP (Requisito Pleno)
                $cepRecord = Cep::firstOrCreate(
                    ['cep' => $dados['cep']],
                    [
                        'logradouro' => $dados['logradouro'],
                        'bairro'     => $dados['bairro'],
                        'cidade'     => $dados['localidade'],
                        'uf'         => $dados['uf'],
                    ]
                );

                // 4. Salva o endereço vinculado
                $endereco = Endereco::create([
                    'cep_id'           => $cepRecord->id,
                    'numero'           => $request->numero,
                    'ponto_referencia' => $request->ponto_referencia,
                ]);

                $resultado = $endereco->load('cep');

                // Se a requisição for AJAX/API, retorna JSON
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json($resultado, 201);
                }

                // Se for via formulário Blade, volta com sucesso
                return view('rastreio', ['endereco' => $resultado, 'success' => 'Endereço rastreado e salvo!']);
            });

        } catch (Exception $e) {
            return $this->retornarErro('Erro interno ao processar: ' . $e->getMessage(), $request);
        }
    }

    /**
     * Helper para lidar com erros em ambos os formatos
     */
    private function retornarErro($mensagem, $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => $mensagem], 422);
        }
        return back()->withInput()->with('error', $mensagem);
    }
}