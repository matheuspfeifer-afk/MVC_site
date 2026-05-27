<?php
declare(strict_types=1);

namespace App\Validators;

class UsuarioValidator
{
    /**
     * Valida e sanitiza os dados do usuário.
     * Retorna array de erros (vazio = ok).
     *
     * @param array<string,string> $data  Passa por referência para aplicar strip_tags.
     */
    public function validate(array &$data, bool $senhaObrigatoria = true): array
    {
        // Sanitização básica
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = strip_tags(trim($value));
            }
        }

        $errors = [];

        // Nome
        if (empty($data['nome'])) {
            $errors[] = 'O nome é obrigatório.';
        } elseif (strlen($data['nome']) < 3) {
            $errors[] = 'O nome deve ter pelo menos 3 caracteres.';
        } elseif (strlen($data['nome']) > 255) {
            $errors[] = 'O nome não pode ultrapassar 255 caracteres.';
        }

        // E-mail
        if (empty($data['email'])) {
            $errors[] = 'O e-mail é obrigatório.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail válido.';
        } elseif (strlen($data['email']) > 255) {
            $errors[] = 'O e-mail não pode ultrapassar 255 caracteres.';
        }

        // Senha (obrigatória apenas na criação)
        if ($senhaObrigatoria && empty($data['senha'])) {
            $errors[] = 'A senha é obrigatória.';
        }

        if (!empty($data['senha'])) {
            if (strlen($data['senha']) < 8) {
                $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
            }
            if (!preg_match('/[A-Z]/', $data['senha'])) {
                $errors[] = 'A senha deve conter pelo menos uma letra maiúscula.';
            }
            if (!preg_match('/[0-9]/', $data['senha'])) {
                $errors[] = 'A senha deve conter pelo menos um número.';
            }
        }

        return $errors;
    }
}