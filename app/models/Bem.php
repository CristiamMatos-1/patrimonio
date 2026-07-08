<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Bem
{
    public static function list(PDO $pdo, array $filters = []): array
    {
        $where = [];
        $params = [];

        if (($filters['setor'] ?? '') !== '') {
            $where[] = 'b.setor = :setor';
            $params['setor'] = $filters['setor'];
        }
        if (($filters['centro_custo'] ?? '') !== '') {
            $where[] = 'b.centro_custo = :centro_custo';
            $params['centro_custo'] = $filters['centro_custo'];
        }
        if (($filters['status'] ?? '') !== '') {
            $where[] = 'b.status = :status';
            $params['status'] = $filters['status'];
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = '(b.descricao LIKE :q OR b.marca LIKE :q OR b.modelo LIKE :q OR b.numero_serie LIKE :q OR b.responsavel LIKE :q OR b.codigo_interno LIKE :q OR CAST(b.tombamento AS CHAR) LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $sql = 'SELECT b.*, l.nome AS local_nome, l.tipo AS local_tipo FROM bens b LEFT JOIN locais l ON l.id = b.local_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY b.id DESC LIMIT 1000';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT b.*, l.nome AS local_nome, l.tipo AS local_tipo, e.razao_social AS empresa_razao_social FROM bens b LEFT JOIN locais l ON l.id = b.local_id LEFT JOIN empresas e ON e.id = l.empresa_id WHERE b.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO bens (tombamento, codigo_interno, descricao, categoria, marca, modelo, numero_serie, valor_aquisicao, data_aquisicao, vida_util, centro_custo, setor, localizacao, responsavel, local_id, status, foto_url, observacoes)
                 VALUES (NULL, :codigo_interno, :descricao, :categoria, :marca, :modelo, :numero_serie, :valor_aquisicao, :data_aquisicao, :vida_util, :centro_custo, :setor, :localizacao, :responsavel, :local_id, :status, :foto_url, :observacoes)'
            );
            $stmt->execute([
                'codigo_interno' => $data['codigo_interno'],
                'descricao' => $data['descricao'],
                'categoria' => $data['categoria'],
                'marca' => $data['marca'],
                'modelo' => $data['modelo'],
                'numero_serie' => $data['numero_serie'],
                'valor_aquisicao' => $data['valor_aquisicao'],
                'data_aquisicao' => $data['data_aquisicao'],
                'vida_util' => $data['vida_util'],
                'centro_custo' => $data['centro_custo'],
                'setor' => $data['setor'],
                'localizacao' => $data['localizacao'],
                'responsavel' => $data['responsavel'],
                'local_id' => $data['local_id'],
                'status' => $data['status'],
                'foto_url' => $data['foto_url'],
                'observacoes' => $data['observacoes'],
            ]);
            $id = (int) $pdo->lastInsertId();
            $upd = $pdo->prepare('UPDATE bens SET tombamento = :tombamento WHERE id = :id');
            $upd->execute(['tombamento' => $id, 'id' => $id]);
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            'UPDATE bens SET codigo_interno = :codigo_interno, descricao = :descricao, categoria = :categoria, marca = :marca, modelo = :modelo, numero_serie = :numero_serie,
             valor_aquisicao = :valor_aquisicao, data_aquisicao = :data_aquisicao, vida_util = :vida_util, centro_custo = :centro_custo, setor = :setor, localizacao = :localizacao,
             responsavel = :responsavel, local_id = :local_id, status = :status, foto_url = :foto_url, observacoes = :observacoes WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'codigo_interno' => $data['codigo_interno'],
            'descricao' => $data['descricao'],
            'categoria' => $data['categoria'],
            'marca' => $data['marca'],
            'modelo' => $data['modelo'],
            'numero_serie' => $data['numero_serie'],
            'valor_aquisicao' => $data['valor_aquisicao'],
            'data_aquisicao' => $data['data_aquisicao'],
            'vida_util' => $data['vida_util'],
            'centro_custo' => $data['centro_custo'],
            'setor' => $data['setor'],
            'localizacao' => $data['localizacao'],
            'responsavel' => $data['responsavel'],
            'local_id' => $data['local_id'],
            'status' => $data['status'],
            'foto_url' => $data['foto_url'],
            'observacoes' => $data['observacoes'],
        ]);
    }
}
