<!-- Pagina de administração Gerir usuarios -->
<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

include BASE_PATH . 'db.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Gerir Utilizadores</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title">Gerir Utilizadores</h1>

        <?php if (isset($_SESSION['admin_msg'])): ?>
            <div class="admin-msg"><?= htmlspecialchars($_SESSION['admin_msg']) ?></div>
            <?php unset($_SESSION['admin_msg']); ?>
        <?php elseif (isset($_GET['msg'])): ?>
            <div class="admin-msg"><?= htmlspecialchars(match($_GET['msg']) {
                'adicionado' => 'Utilizador adicionado com sucesso.',
                'atualizado' => 'Utilizador atualizado com sucesso.',
                'excluido' => 'Utilizador excluído com sucesso.',
                default => 'Operação realizada.'
            }) ?></div>
        <?php elseif (isset($_GET['erro'])): ?>
            <div class="admin-msg" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;"><?= htmlspecialchars(match($_GET['erro']) {
                'csrf' => 'Erro de validação. Tente novamente.',
                'bd' => 'Erro na base de dados.',
                default => 'Ocorreu um erro.'
            }) ?></div>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>Admin/utilizadores_add.php" class="admin-action-btn">Adicionar Novo Utilizador</a>
        <a href="<?= BASE_URL ?>Admin/gerar_leitura_rapida.php?csrf_token=<?= $_SESSION['csrf_token'] ?>" class="admin-action-btn" style="background:#10b981;" onclick="return confirm('Gerar 1 leitura aleatória para cada sensor agora?')">⚡ Gerar Leitura Rápida</a>
        <a href="<?= BASE_URL ?>Admin/auditoria.php" class="admin-action-btn" style="background:#6366f1;"><i class="fas fa-history"></i> Log de Auditoria</a>
        <a href="<?= BASE_URL ?>Admin/api_keys.php" class="admin-action-btn" style="background:#f59e0b;"><i class="fas fa-key"></i> API Keys</a>
        <div style="margin: 1rem 0;">
            <input type="text" id="userSearch" placeholder="🔍 Pesquisar utilizadores..." style="padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;width:300px;font-size:14px;">
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nome Completo</th>
                    <th>Admin</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT cod_utilizador, username, email, nome_completo, ADMIN FROM utilizadores");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['cod_utilizador']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nome_completo']) . "</td>";
                    echo "<td>" . ($row['ADMIN'] ? 'Sim' : 'Não') . "</td>";
                    echo "<td><a href='" . BASE_URL . "Admin/utilizadores_edit.php?id={$row['cod_utilizador']}' class='admin-action-btn'>Editar</a> ";
                    echo "<a href='" . BASE_URL . "Admin/utilizadores_delete.php?id={$row['cod_utilizador']}&csrf_token=" . $_SESSION['csrf_token'] . "' class='admin-action-btn btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir este utilizador?\")'>Excluir</a></td>";
                    echo "</tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>