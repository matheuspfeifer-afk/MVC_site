<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="/admin.css">
    <style>
        .date-info { font-size: 11px; color: #6c757d; display: block; line-height: 1.2; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: #4e73df; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 15px;
        }
    </style>
</head>
<body>

<header class="admin-header">
    <h1>Gerenciar Usuários</h1>
    <nav>
        <a href="/admin/usuarios/create" class="btn-nav" style="background:#198754;">+ Novo Usuário</a>
        <a href="/admin/viacoes"         class="btn-nav">Viações</a>
        <a href="/admin/historico"       class="btn-nav">Ver Histórico</a>
        <a href="/admin/viacoes"         class="btn-nav" style="background:#5e6569;color:#fff;">Voltar</a>
        <a href="/logout"                class="btn-nav" style="background:#dc3545;">Logout</a>
    </nav>
</header>

<main class="admin-main">

    <!-- Filtros -->
    <div class="admin-card" style="margin-bottom:20px;">
        <form method="GET" action="/admin/usuarios" class="filter-form"
              style="display:flex;gap:15px;align-items:flex-end;flex-wrap:wrap;">

            <div style="display:flex;flex-direction:column;gap:5px;flex:1;min-width:200px;">
                <label style="font-size:12px;font-weight:bold;color:#666;">🔎 Pesquisar Usuário</label>
                <input type="text" name="nome" placeholder="Buscar por nome ou e-mail..."
                       value="<?= htmlspecialchars((string)($filtros['busca'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       style="padding:10px;border:1px solid #ddd;border-radius:4px;">
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn-nav"
                        style="background:#4e73df;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
                    Filtrar
                </button>
                <a href="/admin/usuarios" class="btn-nav"
                   style="background:#858796;color:white;padding:10px 15px;border-radius:4px;
                          text-decoration:none;font-size:14px;display:flex;align-items:center;">
                    Limpar
                </a>
            </div>

        </form>
    </div>

    <!-- Tabela -->
    <div class="admin-card" style="padding:0;">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                <tr>
                    <th width="50">Avatar</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Criado em</th>
                    <th width="150">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:20px;">
                            Nenhum usuário cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <div class="avatar">
                                    <?= mb_strtoupper(mb_substr($u->nome, 0, 1, 'UTF-8'), 'UTF-8') ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($u->nome, ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="date-info">
                                    <?= $u->criadoEm
                                        ? date('d/m/Y H:i', strtotime($u->criadoEm))
                                        : '-' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <a href="/admin/usuarios/<?= $u->id ?>/edit"
                                       class="btn-nav"
                                       style="padding:5px 10px;font-size:12px;background:#f6c23e;">
                                        Editar
                                    </a>

                                    <form action="/admin/usuarios/<?= $u->id ?>" method="POST"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn-nav"
                                                style="padding:5px 10px;font-size:12px;background:#e74a3b;border:none;cursor:pointer;">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginação -->
    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;padding:20px;flex-wrap:wrap;">

            <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?= $pagination['page'] - 1 ?>&nome=<?= urlencode($filtros['busca'] ?? '') ?>&order=<?= urlencode($filtros['ordem'] ?? '') ?>&dir=<?= urlencode($filtros['dir'] ?? '') ?>"
                   class="btn-nav" style="background:#6c757d;">
                    ← Anterior
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                <a href="?page=<?= $i ?>&nome=<?= urlencode($filtros['busca'] ?? '') ?>&order=<?= urlencode($filtros['ordem'] ?? '') ?>&dir=<?= urlencode($filtros['dir'] ?? '') ?>"
                   class="btn-nav"
                   style="background:<?= $i === $pagination['page'] ? '#4e73df' : '#e9ecef' ?>;
                       color:<?= $i === $pagination['page'] ? '#fff' : '#000' ?>;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="?page=<?= $pagination['page'] + 1 ?>&nome=<?= urlencode($filtros['busca'] ?? '') ?>&order=<?= urlencode($filtros['ordem'] ?? '') ?>&dir=<?= urlencode($filtros['dir'] ?? '') ?>"
                   class="btn-nav" style="background:#6c757d;">
                    Próxima →
                </a>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</main>
</body>
</html>