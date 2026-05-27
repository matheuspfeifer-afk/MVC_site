<?php
declare(strict_types=1);

namespace App\DTOs;

final class UsuarioDTO
{
    public function __construct(
        public readonly string $nome,
        public readonly string $email,
        public readonly string $senha,
    ) {}

    public static function fromRequest(array $post): self
    {
        return new self(
            nome:  trim((string) ($post['nome']  ?? '')),
            email: trim((string) ($post['email'] ?? '')),
            senha: trim((string) ($post['senha'] ?? '')),
        );
    }

    public function toArray(): array
    {
        return [
            'nome'  => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
        ];
    }
}