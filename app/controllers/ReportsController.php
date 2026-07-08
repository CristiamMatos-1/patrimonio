<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\SimplePdf;
use App\Lib\View;

final class ReportsController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('relatorios:ver');

        View::render($this->config, 'relatorios/index', [
            'title' => 'Relatórios',
        ]);
    }

    public function csv(): void
    {
        Auth::requireCapability('relatorios:ver');

        $tipo = (string) ($_GET['tipo'] ?? 'geral');
        $rows = $this->fetchReport($tipo);
        $filename = 'relatorio_' . preg_replace('/[^a-z0-9_]+/i', '_', $tipo) . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'wb');
        if (!is_array($rows) || $out === false) {
            return;
        }

        fwrite($out, "\xEF\xBB\xBF");

        if ($rows === []) {
            fputcsv($out, ['Sem dados'], ';');
            fclose($out);
            return;
        }

        fputcsv($out, array_keys($rows[0]), ';');
        foreach ($rows as $r) {
            fputcsv($out, array_values($r), ';');
        }
        fclose($out);
    }

    public function pdf(): void
    {
        Auth::requireCapability('relatorios:ver');

        $tipo = (string) ($_GET['tipo'] ?? 'geral');
        $rows = $this->fetchReport($tipo);

        $pdf = new SimplePdf();
        $pdf->addLine('Relatório: ' . $tipo);
        $pdf->addLine('Gerado em: ' . (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $pdf->addLine('');

        $max = 55;
        $i = 0;
        foreach ($rows as $r) {
            if ($i >= $max) {
                $pdf->addLine('...');
                break;
            }
            $pdf->addLine($this->formatRowForPdf($tipo, $r));
            $i++;
        }

        $pdf->output('relatorio_' . preg_replace('/[^a-z0-9_]+/i', '_', $tipo) . '.pdf');
    }

    private function fetchReport(string $tipo): array
    {
        return match ($tipo) {
            'por_setor' => $this->porSetor(),
            'por_centro_custo' => $this->porCentroCusto(),
            'emprestados' => $this->emprestados(),
            'vencidos' => $this->vencidos(),
            'sem_responsavel' => $this->semResponsavel(),
            default => $this->geral(),
        };
    }

    private function porSetor(): array
    {
        $stmt = $this->pdo->query('SELECT COALESCE(setor, \'\') AS setor, COUNT(*) AS quantidade, SUM(COALESCE(valor_aquisicao,0)) AS valor_total FROM bens GROUP BY setor ORDER BY quantidade DESC');
        return $stmt->fetchAll();
    }

    private function porCentroCusto(): array
    {
        $stmt = $this->pdo->query('SELECT COALESCE(centro_custo, \'\') AS centro_custo, COUNT(*) AS quantidade, SUM(COALESCE(valor_aquisicao,0)) AS valor_total FROM bens GROUP BY centro_custo ORDER BY quantidade DESC');
        return $stmt->fetchAll();
    }

    private function emprestados(): array
    {
        $sql = "SELECT b.tombamento, b.descricao, b.setor, b.responsavel, e.data_saida, e.data_prevista
                FROM bens b
                LEFT JOIN emprestimos e ON e.bem_id = b.id AND e.status = 'aberto'
                WHERE b.status = 'emprestado'
                ORDER BY e.data_prevista IS NULL, e.data_prevista ASC, b.tombamento ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    private function vencidos(): array
    {
        $sql = "SELECT b.tombamento, b.descricao, b.setor, b.responsavel, e.data_saida, e.data_prevista
                FROM emprestimos e
                JOIN bens b ON b.id = e.bem_id
                WHERE e.status = 'aberto' AND e.data_prevista IS NOT NULL AND e.data_prevista < CURDATE()
                ORDER BY e.data_prevista ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    private function semResponsavel(): array
    {
        $sql = "SELECT tombamento, descricao, setor, localizacao, status
                FROM bens
                WHERE responsavel IS NULL OR responsavel = ''
                ORDER BY tombamento ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    private function geral(): array
    {
        $sql = "SELECT
                    tombamento,
                    codigo_interno,
                    descricao,
                    categoria,
                    marca,
                    modelo,
                    numero_serie,
                    valor_aquisicao,
                    data_aquisicao,
                    vida_util,
                    CASE
                        WHEN valor_aquisicao IS NOT NULL AND vida_util IS NOT NULL AND vida_util > 0 THEN (valor_aquisicao / vida_util)
                        ELSE NULL
                    END AS depreciacao_anual,
                    CASE
                        WHEN data_aquisicao IS NOT NULL AND vida_util IS NOT NULL AND vida_util > 0
                            THEN LEAST(vida_util, GREATEST(0, FLOOR(DATEDIFF(CURDATE(), data_aquisicao) / 365)))
                        ELSE NULL
                    END AS anos_depreciados,
                    CASE
                        WHEN valor_aquisicao IS NOT NULL AND data_aquisicao IS NOT NULL AND vida_util IS NOT NULL AND vida_util > 0
                            THEN (valor_aquisicao / vida_util) * LEAST(vida_util, GREATEST(0, FLOOR(DATEDIFF(CURDATE(), data_aquisicao) / 365)))
                        ELSE NULL
                    END AS depreciacao_acumulada,
                    CASE
                        WHEN valor_aquisicao IS NOT NULL AND data_aquisicao IS NOT NULL AND vida_util IS NOT NULL AND vida_util > 0
                            THEN GREATEST(0, valor_aquisicao - ((valor_aquisicao / vida_util) * LEAST(vida_util, GREATEST(0, FLOOR(DATEDIFF(CURDATE(), data_aquisicao) / 365)))))
                        ELSE NULL
                    END AS valor_atual,
                    centro_custo,
                    setor,
                    localizacao,
                    responsavel,
                    status
                FROM bens
                ORDER BY tombamento ASC
                LIMIT 5000";
        return $this->pdo->query($sql)->fetchAll();
    }

    private function formatRowForPdf(string $tipo, array $row): string
    {
        if ($tipo === 'por_setor') {
            return (string) $row['setor'] . ' - ' . (string) $row['quantidade'] . ' - R$ ' . number_format((float) $row['valor_total'], 2, ',', '.');
        }
        if ($tipo === 'por_centro_custo') {
            return (string) $row['centro_custo'] . ' - ' . (string) $row['quantidade'] . ' - R$ ' . number_format((float) $row['valor_total'], 2, ',', '.');
        }
        if ($tipo === 'emprestados' || $tipo === 'vencidos') {
            return (string) $row['tombamento'] . ' - ' . (string) $row['descricao'] . ' - Prev: ' . (string) ($row['data_prevista'] ?? '');
        }
        if ($tipo === 'sem_responsavel') {
            return (string) $row['tombamento'] . ' - ' . (string) $row['descricao'] . ' - ' . (string) $row['setor'];
        }
        return (string) $row['tombamento'] . ' - ' . (string) $row['descricao'] . ' - ' . (string) ($row['setor'] ?? '') . ' - ' . (string) ($row['status'] ?? '');
    }
}
