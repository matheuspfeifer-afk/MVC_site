<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Services\UsuarioService;
use App\Services\AuthService;
use App\DTOs\UsuarioDTO;
use Exception;

final class UsuarioController
{
    private UsuarioService $service;
    private AuthService    $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $this->service = new UsuarioService();
    }

    //Proteção CSRF
    private function verifyCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                die('Erro de segurança: Token CSRF inválido ou expirado.');
            }
        }
    }

    //GET /admin/usuarios
    public function index(): void
    {
        $busca = (string) ($_GET['nome']  ?? '');
        $ordem = (string) ($_GET['order'] ?? 'criado_em');
        $dir   = (string) ($_GET['dir']   ?? 'DESC');

        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;

        $usuarios = $this->service->all($busca, $ordem, $dir, $page, $limit);
        $total    = $this->service->countFiltered($busca);

        $totalPages = (int) ceil($total / $limit);

        View::render('admin/usuarios/index', [
            'usuarios' => $usuarios,

            'filtros' => compact('busca', 'ordem', 'dir'),

            'pagination' => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
        ]);
    }

    //GET /admin/usuarios/create
    public function create(): void
    {
        View::render('admin/usuarios/create', [
            'errors' => [],
            'old'    => ['nome' => '', 'email' => ''],
        ]);
    }

    //POST /admin/usuarios
    public function store(): void
    {
        $this->verifyCsrf();

        try {
            $dto = UsuarioDTO::fromRequest($_POST);
            $this->service->create($dto);

            header('Location: /admin/usuarios');
            exit;
        } catch (Exception $e) {
            View::render('admin/usuarios/create', [
                'errors' => explode('|', $e->getMessage()),
                'old'    => $_POST,
            ]);
        }
    }

    //GET /admin/usuarios/{id}/edit
    public function edit(int $id): void
    {
        $usuario = $this->service->find($id);
        if (!$usuario) {
            header('Location: /admin/usuarios');
            exit;
        }

        View::render('admin/usuarios/edit', [
            'usuario' => $usuario,
            'errors'  => [],
            'old'     => ['nome' => $usuario->nome, 'email' => $usuario->email],
        ]);
    }

    //PUT /admin/usuarios/{id}
    public function update(int $id): void
    {
        $this->verifyCsrf();

        try {
            $dto = UsuarioDTO::fromRequest($_POST);
            $this->service->update($id, $dto);

            header('Location: /admin/usuarios');
            exit;
        } catch (Exception $e) {
            $usuario = $this->service->find($id);
            View::render('admin/usuarios/edit', [
                'usuario' => $usuario,
                'errors'  => explode('|', $e->getMessage()),
                'old'     => $_POST,
            ]);
        }
    }

    //DELETE /admin/usuarios/{id}
    public function destroy(int $id): void
    {
        $this->verifyCsrf();

        $this->service->delete($id);
        header('Location: /admin/usuarios');
        exit;
    }
}