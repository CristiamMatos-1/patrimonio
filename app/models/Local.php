<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Local
{
    public const TIPO_SEDE = 'sede';
    public const TIPO_FILIAL = 'filial';
    public const TIPO_LOCAL_ENCONTRO = 'local_encontro';

    public static function list(PDO $pdo, ?int $empresaId = null): array
    {
        if ($empresaId === null) {
            $sql = 'SELECT l.*, e.razao_social AS empresa_razao_social FROM locais l JOIN empresas e ON e.id = l.empresa_id ORDER BY l.ativo DESC, e.razao_social ASC, l.nome ASC';
            return $pdo->query($sql)->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT l.*, e.razao_social AS empresa_razao_social FROM locais l JOIN empresas e ON e.id = l.empresa_id WHERE l.empresa_id = :empresa_id ORDER BY l.ativo DESC, l.nome ASC');
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public static function listActiveForSelect(PDO $pdo): array
    {
        $sql = 'SELECT l.id, l.nome, l.tipo, e.razao_social AS empresa_razao_social
                FROM locais l
                JOIN empresas e ON e.id = l.empresa_id
                WHERE l.ativo = 1 AND e.ativo = 1
                ORDER BY e.razao_social ASC, l.nome ASC';
        return $pdo->query($sql)->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT l.*, e.razao_social AS empresa_razao_social FROM locais l JOIN empresas e ON e.id = l.empresa_id WHERE l.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare('INSERT INTO locais (empresa_id, nome, tipo, endereco, ativo) VALUES (:empresa_id, :nome, :tipo, :endereco, :ativo)');
        $stmt->execute([
            'empresa_id' => $data['empresa_id'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'endereco' => $data['endereco'],
            'ativo' => $data['ativo'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare('UPDATE locais SET empresa_id = :empresa_id, nome = :nome, tipo = :tipo, endereco = :endereco, ativo = :ativo WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'empresa_id' => $data['empresa_id'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'endereco' => $data['endereco'],
            'ativo' => $data['ativo'],
        ]);
    }
}

