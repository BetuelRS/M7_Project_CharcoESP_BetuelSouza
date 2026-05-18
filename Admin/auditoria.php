<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

include BASE_PATH . 'db.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$total = $conn->query("SELECT COUNT(*) FROM auditoria")->fetch_row()[0];
$total_pages = ceil($total / $per_page);

$result = $conn->query("
    SELECT a.*, u.username, u.nome_completo
    FROM auditoria a
    LEFT JOIN utilizadores u ON a.utilizador_id = u.cod_utilizador
    ORDER BY a.created_at DESC
    LIMIT $per_page OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title"><i class="fas fa-history"></i> Log de Auditoria</h1>

        <div style="margin: 1rem 0; color: #6b7280; font-size: 14px;">
            <i class="fas fa-database"></i> <?= $total ?> registo(s) encontrado(s)
            <?php if ($total_pages > 1): ?> | Página <?= $page ?> de <?= $total_pages ?><?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Utilizador</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>ID</th>
                        <th>Detalhes</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                        <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>
                        <td>
                            <span class="status <?= match($row['acao']) {
                                'criar' => 'status-active',
                                'editar' => 'status-active',
                                'eliminar' => 'status-inactive',
                                'login' => 'status-active',
                                default => ''
                            } ?>">
                                <?= htmlspecialchars($row['acao']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['entidade']) ?></td>
                        <td><?= $row['entidade_id'] ?></td>
                        <td><?= htmlspecialchars($row['detalhes'] ?? '-') ?></td>
                        <td style="font-size:12px;color:#9ca3af;"><?= htmlspecialchars($row['ip'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination" style="margin-top:1rem;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>">Próximo <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top:1.5rem;">
            <a href="<?= BASE_URL ?>Admin/admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>
