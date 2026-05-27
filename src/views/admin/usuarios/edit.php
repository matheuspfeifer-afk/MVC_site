<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="/admin.css">
</head>
<body>

<header class="admin-header">
    <h1>Editar Usuário</h1>
    <nav>
        <a href="/admin/usuarios" class="btn-nav" style="background:#6c757d;">← Voltar</a>
        <a href="/logout"         class="btn-nav" style="background:#dc3545;">Logout</a>
    </nav>
</header>

<main class="admin-main">
    <div class="admin-card">

        <?php if (!empty($errors)): ?>
            <div style="background:#fdeaea;border:1px solid #f5c2c7;border-radius:6px;
                        padding:12px 16px;margin-bottom:20px;color:#842029;">
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin:8px 0 0 16px;padding:0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/usuarios/<?= $usuario->id ?>"
              style="display:flex;flex-direction:column;gap:18px;">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="_method" value="PUT">

            <!-- Nome -->
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-weight:bold;font-size:13px;">Nome <span style="color:#dc3545;">*</span></label>
                <input type="text" name="nome" required
                       value="<?= htmlspecialchars((string)($old['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Nome completo"
                       style="padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>

            <!-- E-mail -->
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-weight:bold;font-size:13px;">E-mail <span style="color:#dc3545;">*</span></label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="email@exemplo.com"
                       style="padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>

            <!-- Nova senha (opcional) -->
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-weight:bold;font-size:13px;">Nova senha
                    <span style="font-weight:normal;color:#6c757d;">(deixe em branco para manter a atual)</span>
                </label>
                <input type="password" name="senha"
                       placeholder="Mínimo 8 caracteres, 1 maiúscula e 1 número"
                       style="padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn-nav"
                        style="background:#f6c23e;border:none;cursor:pointer;padding:10px 24px;
                               font-size:14px;color:#000;">
                    Atualizar Usuário
                </button>
                <a href="/admin/usuarios" class="btn-nav" style="background:#6c757d;">Cancelar</a>
            </div>
        </form>

    </div>
</main>
</body>
</html>