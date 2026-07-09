<?php

namespace App\Controllers\Superadmin;

use Core\Controller;
use Core\Database;
use Exception;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login do Superadmin
     */
    public function loginForm()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se já estiver logado, redireciona para a área de criação de tenant ou dashboard
        if (!empty($_SESSION['superadmin_logged_in']) && $_SESSION['superadmin_logged_in'] === true) {
            header("Location: " . APP_URL . "/superadmin/tenants");
            exit;
        }

        $this->render('superadmin/login', [
            'title' => 'Superadmin Login'
        ]);
    }

    /**
     * Processa a autenticação do Superadmin
     */
    public function authenticate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->render('superadmin/login', [
                'title' => 'Superadmin Login',
                'error' => 'Por favor, preencha todos os campos.'
            ]);
            return;
        }

        try {
            $dbInstance = Database::getInstance();
            $dbInstance->switchToMaster();
            $pdo = $dbInstance->getConnection();

            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM super_admins WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $superadmin = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($superadmin && password_verify($password, $superadmin['password_hash'])) {
                $_SESSION['superadmin_logged_in'] = true;
                $_SESSION['superadmin_id'] = $superadmin['id'];
                $_SESSION['superadmin_email'] = $superadmin['email'];

                header("Location: " . APP_URL . "/superadmin/tenants");
                exit;
            } else {
                $this->render('superadmin/login', [
                    'title' => 'Superadmin Login',
                    'error' => 'Credenciais inválidas.'
                ]);
            }
        } catch (Exception $e) {
            error_log('Erro no login de superadmin: ' . $e->getMessage());
            $this->render('superadmin/login', [
                'title' => 'Superadmin Login',
                'error' => 'Erro interno do servidor. Tente novamente.'
            ]);
        }
    }

    /**
     * Encerra a sessão do Superadmin
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['superadmin_logged_in']);
        unset($_SESSION['superadmin_id']);
        unset($_SESSION['superadmin_email']);

        header("Location: " . APP_URL . "/superadmin/login");
        exit;
    }
}
