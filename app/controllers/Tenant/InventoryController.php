<?php

namespace App\Controllers\Tenant;

use Core\Controller;
use Core\Middleware\AuthMiddleware;
use App\Lib\Csrf;
use Core\Services\AuditService;
use App\Models\AssetInventoryModel;
use App\Models\AssetModel;

class InventoryController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    public function index()
    {
        $inventoryModel = new AssetInventoryModel();
        $sessions = $inventoryModel->getAllSessions();

        $this->render('tenant/inventory/index', [
            'title' => 'Conferência Patrimonial',
            'sessions' => $sessions
        ]);
    }

    public function start()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        $sector = trim((string) ($_POST['sector'] ?? ''));
        if ($sector === '') {
            $this->redirect('/inventory');
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $inventoryModel = new AssetInventoryModel();
        $sessionId = $inventoryModel->createSession($sector, $userId);
        AuditService::log('CREATE', 'asset_inventory_sessions', $sessionId, null, ['sector' => $sector, 'created_by' => $userId]);

        $this->redirect('/inventory/view?id=' . $sessionId);
    }

    public function view()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $inventoryModel = new AssetInventoryModel();
        $session = $inventoryModel->findSession($id);
        if (!$session) {
            $this->redirect('/inventory');
        }

        $onlyPending = (string) ($_GET['pendentes'] ?? '') === '1';
        $total = $inventoryModel->countTotalItems($id);
        $done = $inventoryModel->countCheckedItems($id);
        $items = $inventoryModel->getItems($id, $onlyPending);

        $this->render('tenant/inventory/view', [
            'title' => 'Inventário #' . $id,
            'session' => $session,
            'total' => $total,
            'done' => $done,
            'onlyPending' => $onlyPending,
            'items' => $items,
            'isAdmin' => isset($_SESSION['role_id']) && (int) $_SESSION['role_id'] === 1,
        ]);
    }

    public function verify()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        $inventoryId = (int) ($_POST['inventory_id'] ?? 0);
        $assetId = (int) ($_POST['asset_id'] ?? 0);
        $assetNumber = trim((string) ($_POST['asset_number'] ?? ''));

        $inventoryModel = new AssetInventoryModel();
        $session = $inventoryModel->findSession($inventoryId);
        if (!$session || !empty($session['finalized_at'])) {
            $this->redirect('/inventory');
        }

        $assetModel = new AssetModel();
        if ($assetId <= 0 && $assetNumber !== '') {
            $asset = $assetModel->findByAssetNumber($assetNumber);
            $assetId = $asset ? (int) $asset['id'] : 0;
        }

        if ($assetId <= 0) {
            $this->redirect('/inventory/view?id=' . $inventoryId);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $inventoryModel->verifyItem($inventoryId, $assetId, $userId);
        AuditService::log('UPDATE', 'asset_inventory_items', null, null, ['inventory_id' => $inventoryId, 'asset_id' => $assetId, 'checked_by' => $userId]);

        $this->redirect('/inventory/view?id=' . $inventoryId);
    }

    public function finalize()
    {
        Csrf::verify($_POST['csrf_token'] ?? null);

        if (!isset($_SESSION['role_id']) || (int) $_SESSION['role_id'] !== 1) {
            $this->redirect('/inventory');
        }

        $inventoryId = (int) ($_POST['inventory_id'] ?? 0);
        $inventoryModel = new AssetInventoryModel();
        $inventoryModel->finalize($inventoryId);
        AuditService::log('UPDATE', 'asset_inventory_sessions', $inventoryId, null, ['finalized_by' => $_SESSION['user_id'] ?? null]);

        $this->redirect('/inventory/view?id=' . $inventoryId);
    }
}
