<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $table = 'funcionarios';

    protected $fillable = [
        'secullum_id',
        'banco_id',
        'nome',
        'numero_folha',
        'cargo',
        'foto',
        'ativo',
        'demissao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'demissao' => 'datetime',
    ];

    public function getFotoAttribute($value): ?string
    {
        return $this->normalizarFoto($value);
    }

    public function setFotoAttribute($value): void
    {
        $this->attributes['foto'] = $this->normalizarFoto($value);
    }

    private function normalizarFoto(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || strtolower($value) === 'null') {
            return null;
        }

        $jsonDecoded = json_decode($value, true);
        if (is_string($jsonDecoded) && trim($jsonDecoded) !== '') {
            $value = trim($jsonDecoded);
        }

        for ($i = 0; $i < 3; $i++) {
            if (str_starts_with($value, 'data:image')) {
                $partes = explode(',', $value, 2);

                if (count($partes) !== 2 || empty($partes[1])) {
                    return null;
                }

                $cabecalho = trim($partes[0]);
                $conteudo = trim($partes[1]);

                $decodificado = base64_decode($conteudo, true);

                if ($decodificado !== false) {
                    $decodificado = trim($decodificado);

                    if (str_starts_with($decodificado, 'data:image')) {
                        $value = $decodificado;
                        continue;
                    }
                }

                return $cabecalho . ',' . $conteudo;
            }

            if ($this->pareceBase64($value)) {
                $decodificado = base64_decode($value, true);

                if ($decodificado === false) {
                    return null;
                }

                $decodificado = trim($decodificado);

                if (str_starts_with($decodificado, 'data:image')) {
                    $value = $decodificado;
                    continue;
                }

                if ($this->ehImagemBinaria($decodificado)) {
                    return 'data:image/jpeg;base64,' . $value;
                }

                $value = $decodificado;
                continue;
            }

            if ($this->ehImagemBinaria($value)) {
                return 'data:image/jpeg;base64,' . base64_encode($value);
            }

            break;
        }

        return null;
    }

    private function pareceBase64(string $valor): bool
    {
        if ($valor === '' || preg_match('/\s/', $valor)) {
            return false;
        }

        $decodificado = base64_decode($valor, true);

        return $decodificado !== false && base64_encode($decodificado) === $valor;
    }

    private function ehImagemBinaria(string $conteudo): bool
    {
        if (str_starts_with($conteudo, "\xFF\xD8\xFF")) {
            return true;
        }

        if (str_starts_with($conteudo, "\x89PNG")) {
            return true;
        }

        if (str_starts_with($conteudo, "GIF87a") || str_starts_with($conteudo, "GIF89a")) {
            return true;
        }

        if (substr($conteudo, 0, 4) === 'RIFF' && substr($conteudo, 8, 4) === 'WEBP') {
            return true;
        }

        return false;
    }
}