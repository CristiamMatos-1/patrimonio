<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Http;
use App\Lib\View;
use App\Models\User;

final class AdminUsersController extends BaseController
{
    public function index(): void
    {
        Auth::requireCapability('usuarios:gerir');
        View::render($this->config, 'admin/users/index', [
            'title' => 'Usuários',
            'users' => User::list($this->pdo),
        ]);
    }

    public function createForm(): void
    {
        Auth::requireCapability('usuarios:gerir');
        View::render($this->config, 'admin/users/form', [
            'title' => 'Novo usuário',
            'user' => null,
            'profiles' => [User::PERFIL_ADMIN, User::PERFIL_OPERADOR, User::PERFIL_CONSULTA],
        ]);
    }

    public function create(): void
    {
        Auth::requireCapability('usuarios:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $perfil = (string) ($_POST['perfil'] ?? User::PERFIL_CONSULTA);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $senha = (string) ($_POST['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            Flash::add('danger', 'Nome, e-mail e senha são obrigatórios.');
            Http::redirect('/admin/usuarios/novo');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::add('danger', 'E-mail inválido.');
            Http::redirect('/admin/usuarios/novo');
        }

        if (!in_array($perfil, [User::PERFIL_ADMIN, User::PERFIL_OPERADOR, User::PERFIL_CONSULTA], true)) {
            Flash::add('danger', 'Perfil inválido.');
            Http::redirect('/admin/usuarios/novo');
        }

        if (User::findByEmail($this->pdo, $email) !== null) {
            Flash::add('danger', 'E-mail já cadastrado.');
            Http::redirect('/admin/usuarios/novo');
        }

        $id = User::create($this->pdo, [
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            'perfil' => $perfil,
            'ativo' => $ativo,
        ]);

        Flash::add('success', 'Usuário criado.');
        Http::redirect('/admin/usuarios/editar?id=' . $id);
    }

    public function editForm(): void
    {
        Auth::requireCapability('usuarios:gerir');
        $id = (int) ($_GET['id'] ?? 0);
        $user = $id > 0 ? User::findById($this->pdo, $id) : null;
        if ($user === null) {
            Flash::add('danger', 'Usuário não encontrado.');
            Http::redirect('/admin/usuarios');
        }

        View::render($this->config, 'admin/users/form', [
            'title' => 'Editar usuário',
            'user' => $user,
            'profiles' => [User::PERFIL_ADMIN, User::PERFIL_OPERADOR, User::PERFIL_CONSULTA],
        ]);
    }

    public function update(): void
    {
        Auth::requireCapability('usuarios:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $user = $id > 0 ? User::findById($this->pdo, $id) : null;
        if ($user === null) {
            Flash::add('danger', 'Usuário não encontrado.');
            Http::redirect('/admin/usuarios');
        }

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $perfil = (string) ($_POST['perfil'] ?? User::PERFIL_CONSULTA);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($nome === '' || $email === '') {
            Flash::add('danger', 'Nome e e-mail são obrigatórios.');
            Http::redirect('/admin/usuarios/editar?id=' . $id);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::add('danger', 'E-mail inválido.');
            Http::redirect('/admin/usuarios/editar?id=' . $id);
        }

        if (!in_array($perfil, [User::PERFIL_ADMIN, User::PERFIL_OPERADOR, User::PERFIL_CONSULTA], true)) {
            Flash::add('danger', 'Perfil inválido.');
            Http::redirect('/admin/usuarios/editar?id=' . $id);
        }

        $existing = User::findByEmail($this->pdo, $email);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            Flash::add('danger', 'E-mail já cadastrado.');
            Http::redirect('/admin/usuarios/editar?id=' . $id);
        }

        User::update($this->pdo, $id, [
            'nome' => $nome,
            'email' => $email,
            'perfil' => $perfil,
            'ativo' => $ativo,
        ]);

        Flash::add('success', 'Usuário atualizado.');
        Http::redirect('/admin/usuarios/editar?id=' . $id);
    }

    public function updatePassword(): void
    {
        Auth::requireCapability('usuarios:gerir');
        Csrf::verify($_POST['csrf_token'] ?? null);

        $id = (int) ($_POST['id'] ?? 0);
        $user = $id > 0 ? User::findById($this->pdo, $id) : null;
        if ($user === null) {
            Flash::add('danger', 'Usuário não encontrado.');
            Http::redirect('/admin/usuarios');
        }

        $senha = (string) ($_POST['senha'] ?? '');
        if ($senha === '') {
            Flash::add('danger', 'Informe a nova senha.');
            Http::redirect('/admin/usuarios/editar?id=' . $id);
        }

        User::updatePassword($this->pdo, $id, password_hash($senha, PASSWORD_DEFAULT));
        Flash::add('success', 'Senha atualizada.');
        Http::redirect('/admin/usuarios/editar?id=' . $id);
    }
}

