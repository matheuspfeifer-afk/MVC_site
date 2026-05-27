<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? \getPdo();
    }

    //Busca filtrada, ordenada e paginada
    public function all(
        string $busca,
        string $ordem = 'criado_em',
        string $dir   = 'DESC',
        int    $page  = 1,
        int    $limit = 10
    ): array {
        $sql    = "SELECT * FROM usuarios WHERE 1=1";
        $params = [];

        if ($busca !== '') {
            $sql .= " AND (nome LIKE :nome OR email LIKE :email)";
            $params['nome']  = "%$busca%";
            $params['email'] = "%$busca%";
        }

        $colunasPermitidas = ['id', 'nome', 'email', 'criado_em'];

        $ordem = in_array($ordem, $colunasPermitidas, true) ? $ordem : 'criado_em';
        $dir   = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $offset  = ($page - 1) * $limit;
        $sql    .= " ORDER BY $ordem $dir LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($r) => Usuario::fromRow($r),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    //Conta total de registros filtrados
    public function countFiltered(string $busca): int
    {
        $sql    = "SELECT COUNT(*) FROM usuarios WHERE 1=1";
        $params = [];

        if ($busca !== '') {
            $sql .= " AND (nome LIKE :nome OR email LIKE :email)";
            $params['nome']  = "%$busca%";
            $params['email'] = "%$busca%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    //Busca por ID
    public function find(int $id): ?Usuario
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Usuario::fromRow($row) : null;
    }

    //Busca por e-mail
    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Usuario::fromRow($row) : null;
    }

    //Cria um novo usuário
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nome, email, senha)
            VALUES (:nome, :email, :senha)
        ");

        $stmt->execute([
            'nome'  => $data['nome'],
            'email' => $data['email'],
            'senha' => password_hash($data['senha'], PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 2,
            ]),
        ]);

        return (int) $this->db->lastInsertId();
    }

    //Atualiza nome e e-mail (senha é tratada separadamente)
    public function update(int $id, array $data): void
    {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email";

        if (!empty($data['senha'])) {
            $sql .= ", senha = :senha";
        }

        $sql .= " WHERE id = :id";

        $params = [
            'nome'  => $data['nome'],
            'email' => $data['email'],
            'id'    => $id,
        ];

        if (!empty($data['senha'])) {
            $params['senha'] = password_hash($data['senha'], PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 2,
            ]);
        }

        $this->db->prepare($sql)->execute($params);
    }

    //Remove um usuário
    public function delete(int $id): void
    {
        $this->db
            ->prepare("DELETE FROM usuarios WHERE id = :id")
            ->execute(['id' => $id]);
    }

    //Atualiza apenas a senha
    public function updateSenha(int $id, string $novaSenhaHash): void
    {
        $this->db
            ->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id")
            ->execute(['senha' => $novaSenhaHash, 'id' => $id]);
    }
}