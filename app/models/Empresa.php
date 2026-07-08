<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Empresa
{
    public static function list(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM empresas ORDER BY ativo DESC, razao_social ASC')->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM empresas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare('INSERT INTO empresas (razao_social, nome_fantasia, cnpj, endereco, ativo) VALUES (:razao_social, :nome_fantasia, :cnpj, :endereco, :ativo)');
        $stmt->execute([
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'endereco' => $data['endereco'],
            'ativo' => $data['ativo'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare('UPDATE empresas SET razao_social = :razao_social, nome_fantasia = :nome_fantasia, cnpj = :cnpj, endereco = :endereco, ativo = :ativo WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'endereco' => $data['endereco'],
            'ativo' => $data['ativo'],
        ]);
    }
}

