<?php

namespace App\Console\Commands;

use App\Models\Funcionario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SincronizarFuncionariosSecullum extends Command
{
    protected $signature = 'app:sincronizar-funcionarios-secullum';
    protected $description = 'Sincroniza os funcionários ativos da API da Secullum de um ou mais bancos';

    public function handle()
    {
        $this->info('Iniciando sincronização...');

        $bancos = array_filter(array_map('trim', explode(',', env('SECULLUM_BANCO_IDS', ''))));

        if (empty($bancos)) {
            $this->error('Nenhum banco configurado em SECULLUM_BANCO_IDS.');
            return Command::FAILURE;
        }

        $auth = Http::asForm()->post('https://autenticador.secullum.com.br/Token', [
            'grant_type' => 'password',
            'username'   => env('SECULLUM_USERNAME'),
            'password'   => env('SECULLUM_PASSWORD'),
            'client_id'  => '3',
        ]);

        if (! $auth->successful()) {
            $this->error('Não foi possível gerar o token.');
            $this->error('Status: ' . $auth->status());
            $this->error($auth->body());
            return Command::FAILURE;
        }

        $token = $auth->json()['access_token'] ?? null;

        if (! $token) {
            $this->error('Token não retornado.');
            return Command::FAILURE;
        }

        $this->info('Token gerado com sucesso.');
        $this->warn('Limpando tabela de funcionários antes da sincronização...');
        Funcionario::truncate();

        $salvos = 0;
        $atualizados = 0;
        $comFoto = 0;
        $idsAtivos = [];

        try {
            foreach ($bancos as $bancoId) {
                $this->info('Sincronizando banco: ' . $bancoId);

                $response = Http::withToken($token)
                    ->withHeaders([
                        'secullumidbancoselecionado' => $bancoId,
                    ])
                    ->get('https://pontowebintegracaoexterna.secullum.com.br/IntegracaoExterna/Funcionarios');

                if (! $response->successful()) {
                    $this->error('Erro ao buscar funcionários do banco ' . $bancoId);
                    $this->error('Status: ' . $response->status());
                    $this->error($response->body());
                    continue;
                }

                $funcionarios = $response->json();

                if (! is_array($funcionarios)) {
                    $this->error('A resposta da API do banco ' . $bancoId . ' não veio em formato de lista.');
                    continue;
                }

                $this->info('Funcionários recebidos do banco ' . $bancoId . ': ' . count($funcionarios));

                foreach ($funcionarios as $item) {
                    $secullumId = $item['Id'] ?? null;
                    $demissao = $item['Demissao'] ?? null;

                    if (empty($secullumId) || ! empty($demissao)) {
                        continue;
                    }

                    $fotoBase64 = null;

                    $fotoResponse = Http::withToken($token)
                        ->withHeaders([
                            'secullumidbancoselecionado' => $bancoId,
                        ])
                        ->get('https://pontowebintegracaoexterna.secullum.com.br/IntegracaoExterna/Funcionarios/fotos', [
                            'funcionarioId' => $secullumId,
                        ]);

                    if ($fotoResponse->successful()) {
                        $body = $fotoResponse->body();

                        if ($body !== null && trim($body) !== '') {
                            $fotoBase64 = $this->normalizarFoto($body);
                        }
                    }

                    $cargo = $item['Funcao']['Descricao'] ?? null;
                    if ($cargo === '(Não informado)') {
                        $cargo = null;
                    }

                    $funcionario = Funcionario::updateOrCreate(
                        [
                            'banco_id'    => $bancoId,
                            'secullum_id' => $secullumId,
                        ],
                        [
                            'nome'         => trim((string) ($item['Nome'] ?? '')),
                            'numero_folha' => trim((string) ($item['NumeroFolha'] ?? '')),
                            'cargo'        => $cargo,
                            'foto'         => $fotoBase64,
                            'ativo'        => 1,
                            'demissao'     => null,
                        ]
                    );

                    if ($funcionario->wasRecentlyCreated) {
                        $salvos++;
                    } else {
                        $atualizados++;
                    }

                    $idsAtivos[] = $bancoId . '-' . $secullumId;

                    if (! empty($fotoBase64)) {
                        $comFoto++;
                    }
                }
            }

            $this->info('Sincronização concluída com sucesso.');
            $this->info('Bancos sincronizados: ' . count($bancos));
            $this->info('Funcionários ativos sincronizados: ' . count($idsAtivos));
            $this->info('Funcionários salvos: ' . $salvos);
            $this->info('Funcionários atualizados: ' . $atualizados);
            $this->info('Funcionários com foto: ' . $comFoto);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erro durante a sincronização: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function normalizarFoto(string $body): ?string
    {
        $body = trim($body);

        if ($body === '' || strtolower($body) === 'null') {
            return null;
        }

        $jsonDecoded = json_decode($body, true);
        if (is_string($jsonDecoded) && $jsonDecoded !== '') {
            $body = trim($jsonDecoded);
        }

        for ($i = 0; $i < 3; $i++) {
            if (str_starts_with($body, 'data:image')) {
                $partes = explode(',', $body, 2);

                if (count($partes) !== 2 || empty($partes[1])) {
                    return null;
                }

                $mime = $this->extrairMimeType($partes[0]);
                $conteudo = trim($partes[1]);

                $decodificado = base64_decode($conteudo, true);

                if ($decodificado !== false) {
                    $decodificado = trim($decodificado);

                    if (str_starts_with($decodificado, 'data:image')) {
                        $body = $decodificado;
                        continue;
                    }
                }

                return 'data:' . $mime . ';base64,' . $conteudo;
            }

            if ($this->pareceBase64($body)) {
                $decodificado = base64_decode($body, true);

                if ($decodificado === false) {
                    return null;
                }

                $decodificado = trim($decodificado);

                if (str_starts_with($decodificado, 'data:image')) {
                    $body = $decodificado;
                    continue;
                }

                if ($this->ehImagemBinaria($decodificado)) {
                    return 'data:image/jpeg;base64,' . $body;
                }

                $body = $decodificado;
                continue;
            }

            if ($this->ehImagemBinaria($body)) {
                return 'data:image/jpeg;base64,' . base64_encode($body);
            }

            break;
        }

        return null;
    }

    private function extrairMimeType(string $cabecalho): string
    {
        if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64$/', trim($cabecalho), $matches)) {
            return $matches[1];
        }

        return 'image/jpeg';
    }

    private function pareceBase64(string $valor): bool
    {
        if ($valor === '' || preg_match('/\s/', $valor)) {
            return false;
        }

        $decoded = base64_decode($valor, true);

        return $decoded !== false && base64_encode($decoded) === $valor;
    }

    private function ehImagemBinaria(string $conteudo): bool
    {
        if (str_starts_with($conteudo, "\xFF\xD8\xFF")) {
            return true; // JPG
        }

        if (str_starts_with($conteudo, "\x89PNG")) {
            return true; // PNG
        }

        if (str_starts_with($conteudo, "GIF87a") || str_starts_with($conteudo, "GIF89a")) {
            return true; // GIF
        }

        if (substr($conteudo, 0, 4) === "RIFF" && substr($conteudo, 8, 4) === "WEBP") {
            return true; // WEBP
        }

        return false;
    }
}