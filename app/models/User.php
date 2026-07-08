<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class User
{
    public const PERFIL_ADMIN = 'administrador';
    public const PERFIL_OPERADOR = 'operador';
    public const PERFIL_CONSULTA = 'consulta';

    public static function findByEmail(PDO $pdo, string $email): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function list(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, nome, email, perfil, ativo, criado_em FROM usuarios ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (:nome, :email, :senha_hash, :perfil, :ativo)'
        );
        $stmt->execute([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha_hash' => $data['senha_hash'],
            'perfil' => $data['perfil'],
            'ativo' => $data['ativo'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            'UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, ativo = :ativo WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nome' => $data['nome'],
            'email' => $data['email'],
            'perfil' => $data['perfil'],
            'ativo' => $data['ativo'],
        ]);
    }

    public static function updatePassword(PDO $pdo, int $id, string $hash): void
    {
        $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id');
        $stmt->execute(['id' => $id, 'senha_hash' => $hash]);
    }
}

