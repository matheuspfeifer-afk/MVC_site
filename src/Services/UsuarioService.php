<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;
use App\DTOs\UsuarioDTO;
use App\Repositories\UsuarioRepository;
use App\Validators\UsuarioValidator;
use Exception;

final class UsuarioService
{
    private UsuarioRepository $repo;
    private UsuarioValidator  $validator;

    public function __construct(
        ?UsuarioRepository $repo      = null,
        ?UsuarioValidator  $validator = null,
    ) {
        $this->repo      = $repo      ?? new UsuarioRepository();
        $this->validator = $validator ?? new UsuarioValidator();
    }

    //Listagem paginada
    public function all(
        string $busca,
        string $ordem = 'criado_em',
        string $dir   = 'DESC',
        int    $page  = 1,
        int    $limit = 10
    ): array {
        return $this->repo->all($busca, $ordem, $dir, $page, $limit);
    }

    //Contagem para paginação
    public function countFiltered(string $busca): int
    {
        return $this->repo->countFiltered($busca);
    }

    //Busca por ID
    public function find(int $id): ?Usuario
    {
        return $this->repo->find($id);
    }

    //Criação
    public function create(UsuarioDTO $dto): int
    {
        $data = $dto->toArray();

        $errors = $this->validator->validate($data, senhaObrigatoria: true);
        if ($errors !== []) {
            throw new Exception(implode('|', $errors));
        }

        // Verifica e-mail duplicado
        if ($this->repo->findByEmail($data['email']) !== null) {
            throw new Exception('Este e-mail já está em uso.');
        }

        return $this->repo->create($data);
    }

    //Atualização
    public function update(int $id, UsuarioDTO $dto): void
    {
        $usuario = $this->repo->find($id);
        if (!$usuario) {
            throw new Exception('Usuário não encontrado.');
        }

        $data = $dto->toArray();

        // Senha é opcional na edição
        $errors = $this->validator->validate($data, senhaObrigatoria: false);
        if ($errors !== []) {
            throw new Exception(implode('|', $errors));
        }

        // Verifica e-mail duplicado (ignora o próprio usuário)
        $existente = $this->repo->findByEmail($data['email']);
        if ($existente !== null && $existente->id !== $id) {
            throw new Exception('Este e-mail já está em uso por outro usuário.');
        }

        $this->repo->update($id, $data);
    }

    //Exclusão
    public function delete(int $id): void
    {
        $usuario = $this->repo->find($id);
        if (!$usuario) {
            return;
        }

        $this->repo->delete($id);
    }
}