<?php

namespace App\Controllers\Tenant;

use Core\Controller;
use Core\Middleware\AuthMiddleware;
use Core\Middleware\AclMiddleware;
use Core\Services\AuditService;
use App\Lib\Csrf;
use App\Lib\SimplePdf;
use App\Models\AssetModel;
use App\Models\AssetLoanModel;
use App\Models\BranchModel;

class AssetController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    private function handlePhotoUpload(array $files): ?string
    {
        if (empty($files['photo']) || !isset($files['photo']['tmp_name']) || $files['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($files['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpFile = $files['photo']['tmp_name'];
        if (!is_uploaded_file($tmpFile)) {
            return null;
        }

        $mime = mime_content_type($tmpFile) ?: '';
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedTypes[$mime])) {
            return null;
        }

        $extension = $allowedTypes[$mime];
        $filename = 'asset_photo_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        $config = require BASE_PATH . '/config/app.php';
        $uploadDir = rtrim($config['upload_dir'] ?? BASE_PATH . '/public/uploads', '/');
        $uploadBase = rtrim($config['upload_base'] ?? '/uploads', '/');

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return null;
        }

        $destination = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($tmpFile, $destination)) {
            return null;
        }

        return $uploadBase . '/' . $filename;
    }

    public function index()
    {
        // Verifica permissão via ACL (Opcional, pois view geralmente é liberada para staff, 
        // mas protege contra quem não tem perfil algum)
        // AclMiddleware::check('asset.view');

        $assetModel = new AssetModel();
        
        // Trata busca caso exista
        $search = $_GET['q'] ?? '';
        if (!empty($search)) {
            $assets = $assetModel->search($search);
        } else {
            $assets = $assetModel->getAll();
        }

        $this->render('tenant/assets/index', [
            'title' => 'Gestão Patrimonial',
            'assets' => $assets,
            'search' => $search
        ]);
    }

    public function create()
    {
        AclMiddleware::check('asset.create');

        $branchModel = new BranchModel();
        
        $this->render('tenant/assets/create', [
            'title' => 'Novo Bem Patrimonial',
            'branches' => $branchModel->getAll(),
            'asset' => null,
            'action' => '/assets'
        ]);
    }

    public function scan()
    {
        $this->render('tenant/assets/scan', [
            'title' => 'Buscar Patrimônio por QR Code'
        ]);
    }

    public function pdf()
    {
        AclMiddleware::check('asset.view');

        $assetModel = new AssetModel();
        $assets = $assetModel->getAll();

        $pdf = new SimplePdf();
        $pdf->addLine('Lista de Patrimônios');
        $pdf->addLine('Gerado em: ' . (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'));
        $pdf->addLine('');

        if (empty($assets)) {
            $pdf->addLine('Nenhum patrimônio cadastrado.');
        } else {
            foreach ($assets as $asset) {
                $pdf->addLine(sprintf(
                    '%s | %s | %s | %s | %s | %s',
                    $asset['asset_number'] ?? '-',
                    $asset['name'] ?? '-',
                    $asset['branch_name'] ?? '-',
                    $asset['location'] ?? '-',
                    $asset['current_responsible'] ?? '-',
                    $asset['status'] ?? '-'
                ));
            }
        }

        $pdf->output('patrimonio_lista_' . date('Ymd_His') . '.pdf');
        exit;
    }

    public function store()
    {
        AclMiddleware::check('asset.create');
        Csrf::verify($_POST['csrf_token'] ?? null);

        if (empty($_POST['asset_number'])) {
            $_POST['asset_number'] = (new AssetModel())->generateNextAssetNumber();
        }

        $photoUrl = $this->handlePhotoUpload($_FILES);
        if ($photoUrl !== null) {
            $_POST['photo_url'] = $photoUrl;
        }

        $uniqueHash = hash('sha256', $_POST['asset_number'] . ($_SESSION['tenant_cnpj'] ?? '') . uniqid());
        $_POST['qr_code_hash'] = $uniqueHash;

        $assetModel = new AssetModel();
        $newId = $assetModel->create($_POST);

        if ($newId) {
            $this->redirect('/assets/labels?ids=' . $newId);
        } else {
            $this->redirect('/assets');
        }
    }

    public function view()
    {
        AclMiddleware::check('asset.view');

        $assetModel = new AssetModel();
        $id = (int) ($_GET['id'] ?? 0);
        $asset = null;

        if ($id > 0) {
            $asset = $assetModel->find($id);
        }

        if (!$asset && !empty($_GET['hash'])) {
            $asset = $assetModel->findByHash(trim((string) $_GET['hash']));
        }

        if (!$asset && !empty($_GET['asset_number'])) {
            $asset = $assetModel->findByAssetNumber(trim((string) $_GET['asset_number']));
        }

        if (!$asset) {
            $this->redirect('/assets');
        }

        $loanModel = new \App\Models\AssetLoanModel();
        $activeLoan = $loanModel->getActiveByAsset((int) $asset['id']);
        $loanHistory = $loanModel->getByAsset((int) $asset['id']);

        $branchModel = new BranchModel();
        $this->render('tenant/assets/view', [
            'title' => 'Detalhes do Patrimônio',
            'asset' => $asset,
            'activeLoan' => $activeLoan,
            'loanHistory' => $loanHistory,
            'branches' => $branchModel->getAll()
        ]);
    }

    public function edit()
    {
        AclMiddleware::check('asset.update');

        $id = (int) ($_GET['id'] ?? 0);
        $assetModel = new AssetModel();
        $asset = $assetModel->find($id);

        if (!$asset) {
            $this->redirect('/assets');
        }

        $branchModel = new BranchModel();
        $this->render('tenant/assets/create', [
            'title' => 'Editar Bem Patrimonial',
            'branches' => $branchModel->getAll(),
            'asset' => $asset,
            'action' => '/assets/update'
        ]);
    }

    public function update()
    {
        AclMiddleware::check('asset.update');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $assetModel = new AssetModel();
        $asset = $assetModel->find($id);
        if (!$asset) {
            $this->redirect('/assets');
        }

        $photoUrl = $this->handlePhotoUpload($_FILES);
        if ($photoUrl !== null) {
            $_POST['photo_url'] = $photoUrl;
        } else {
            $_POST['photo_url'] = $asset['photo_url'];
        }

        $before = $asset;
        $assetModel->update($id, $_POST);
        AuditService::log('UPDATE', 'assets', $id, $before, $_POST);

        $this->redirect('/assets/view?id=' . $id);
    }

    public function labels(): void
    {
        $assetModel = new AssetModel();
        $allAssets  = $assetModel->getAll();

        $preSelectedIds = [];
        if (!empty($_GET['ids'])) {
            foreach (explode(',', (string) $_GET['ids']) as $raw) {
                $id = (int) trim($raw);
                if ($id > 0) {
                    $preSelectedIds[] = $id;
                }
            }
        }

        $this->render('tenant/assets/labels', [
            'title'          => 'Imprimir Etiquetas',
            'assets'         => $allAssets,
            'preSelectedIds' => $preSelectedIds,
        ]);
    }

    public function labelPrint(): void
    {
        $ids = [];
        if (!empty($_GET['ids'])) {
            foreach (explode(',', (string) $_GET['ids']) as $raw) {
                $id = (int) trim($raw);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        if (empty($ids)) {
            $this->redirect('/assets/labels');
            return;
        }

        $assetModel = new AssetModel();
        $assets     = $assetModel->findByIds($ids);

        if (empty($assets)) {
            $this->redirect('/assets/labels');
            return;
        }

        // Standalone print page — no layout wrapper
        $this->render('tenant/assets/label_print', [
            'assets' => $assets,
        ]);
    }

    public function loan()
    {
        AclMiddleware::check('asset.update');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $assetId = (int) ($_POST['asset_id'] ?? 0);
        $assetModel = new AssetModel();
        $asset = $assetModel->find($assetId);
        if (!$asset) {
            $this->redirect('/assets');
        }

        if ($asset['status'] !== 'active') {
            $this->redirect('/assets/view?id=' . $assetId);
        }

        $outDate = trim((string) ($_POST['out_date'] ?? '')) ?: date('Y-m-d');
        $loanData = [
            'asset_id' => $assetId,
            'borrowed_by' => trim((string) ($_POST['borrowed_by'] ?? '')) ?: 'Não informado',
            'out_date' => $outDate,
            'expected_return_date' => trim((string) ($_POST['expected_return_date'] ?? '')) ?: null,
            'status' => 'active',
            'destination_location' => trim((string) ($_POST['destination_location'] ?? '')) ?: null,
        ];

        $loanModel = new AssetLoanModel();
        $loanId = $loanModel->create($loanData);

        $updatedAssetData = [
            'branch_id' => $asset['branch_id'],
            'internal_code' => $asset['internal_code'],
            'name' => $asset['name'],
            'description' => $asset['description'],
            'category' => $asset['category'],
            'brand' => $asset['brand'],
            'model' => $asset['model'],
            'serial_number' => $asset['serial_number'],
            'acquisition_date' => $asset['acquisition_date'],
            'value' => $asset['value'],
            'useful_life_years' => $asset['useful_life_years'],
            'cost_center' => $asset['cost_center'],
            'sector' => $asset['sector'],
            'location' => $asset['location'],
            'current_responsible' => $loanData['borrowed_by'],
            'photo_url' => $asset['photo_url'],
            'observations' => $asset['observations'],
            'status' => 'borrowed',
        ];

        $assetModel->update($assetId, $updatedAssetData);
        AuditService::log('CREATE', 'asset_loans', is_int($loanId) ? $loanId : null, null, $loanData);
        AuditService::log('UPDATE', 'assets', $assetId, $asset, $updatedAssetData);

        $this->redirect('/assets/view?id=' . $assetId);
    }
}
