<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Viacao;
use PDO;

// Repositório: Responsável exclusivamente pela persistência de dados no MySQL.
// NÃO deve conter regras de negócio (isso fica no Service), apenas Queries SQL.
class ViacaoRepository
{
    private PDO $pdo;

    // Injeção de Dependência do PDO
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \getPdo();
    }

    // Busca filtrada, ordenada e paginada
    public function all(
        string $busca,
        string $status,
        string $ordem = 'nome',
        string $dir = 'ASC',
        int $page = 1,
        int $limit = 10
    ): array {
        $sql = "SELECT * FROM viacoes WHERE 1=1";
        $params = [];

        // Filtro de Busca (Nome ou Cidade)
        if ($busca !== '') {
            $sql .= " AND (nome LIKE :nome OR cidade LIKE :cidade)";
            $params['nome'] = "%$busca%";
            $params['cidade'] = "%$busca%";
        }

        // Filtro de Status
        if (in_array($status, ['ativo', 'inativo'], true)) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        // Segurança no ORDER BY
        $colunasPermitidas = ['id', 'nome', 'criado_em', 'alterado_em'];

        $ordem = in_array($ordem, $colunasPermitidas, true)
            ? $ordem
            : 'nome';

        $dir = strtoupper($dir) === 'DESC'
            ? 'DESC'
            : 'ASC';

        // Paginação
        $offset = ($page - 1) * $limit;

        $sql .= " ORDER BY $ordem $dir LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        // Bind dinâmico dos filtros
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Bind da paginação
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return array_map(
            fn($r) => Viacao::fromRow($r),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    // Conta total de registros filtrados
    public function countFiltered(string $busca, string $status): int
    {
        $sql = "SELECT COUNT(*) FROM viacoes WHERE 1=1";
        $params = [];

        if ($busca !== '') {
            $sql .= " AND (nome LIKE :nome OR cidade LIKE :cidade)";
            $params['nome'] = "%$busca%";
            $params['cidade'] = "%$busca%";
        }

        if (in_array($status, ['ativo', 'inativo'], true)) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    // Busca um registro específico
    public function find(int $id): ?Viacao
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM viacoes WHERE id = :id"
        );

        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Viacao::fromRow($row)
            : null;
    }

    // Cria um registro
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO viacoes
            (nome, url, cidade, status, logo)
            VALUES
            (:nome, :url, :cidade, :status, :logo)
        ");

        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    // Atualiza um registro
    public function update(int $id, array $data): void
    {
        $sql = "
            UPDATE viacoes
            SET
                nome = :nome,
                url = :url,
                cidade = :cidade,
                status = :status
        ";

        if (isset($data['logo'])) {
            $sql .= ", logo = :logo";
        }

        $sql .= " WHERE id = :id";

        $data['id'] = $id;

        $this->pdo
            ->prepare($sql)
            ->execute($data);
    }

    // Remove um registro
    public function delete(int $id): void
    {
        $this->pdo
            ->prepare("DELETE FROM viacoes WHERE id = :id")
            ->execute(['id' => $id]);
    }
}