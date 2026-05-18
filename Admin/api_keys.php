<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit();
}

include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        header('Location: ' . BASE_URL . 'Admin/api_keys.php?erro=csrf');
        exit();
    }
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'gerar') {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome) {
            $key = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("INSERT INTO api_keys (utilizador_id, nome, `key`) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $_SESSION['user_id'], $nome, $key);
            $stmt->execute();
            $stmt->close();
            $_SESSION['nova_api_key'] = $key;
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM api_keys WHERE id = $id");
    }
    header('Location: ' . BASE_URL . 'Admin/api_keys.php');
    exit();
}

$keys = $conn->query("SELECT id, nome, `key`, ultimo_uso, created_at, ativo FROM api_keys ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Keys - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="sidebar">
        <?php include '../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <h1 class="admin-title"><i class="fas fa-key"></i> API Keys</h1>

        <?php if (isset($_SESSION['nova_api_key'])): ?>
        <div class="admin-msg" style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;padding:12px 16px;border-radius:8px;margin-bottom:16px;">
            <strong>Nova API Key gerada!</strong><br>
            Guarde-a agora. Não será mostrada novamente.<br>
            <code style="display:block;background:#fff;padding:8px;border-radius:4px;margin-top:8px;font-size:14px;word-break:break-all;"><?= htmlspecialchars($_SESSION['nova_api_key']) ?></code>
        </div>
        <?php unset($_SESSION['nova_api_key']); ?>
        <?php endif; ?>

        <form method="POST" style="margin-bottom:2rem;display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="acao" value="gerar">
            <div class="form-group" style="margin:0;">
                <label for="nome">Nome da Key:</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Integração Home Assistant" required style="padding:8px 12px;">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Gerar Nova Key</button>
        </form>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Key</th>
                        <th>Criada em</th>
                        <th>Último uso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($k = $keys->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($k['nome']) ?></td>
                        <td><code style="font-size:12px;"><?= substr($k['key'], 0, 16) ?>...<?= substr($k['key'], -8) ?></code></td>
                        <td><?= date('d/m/Y', strtotime($k['created_at'])) ?></td>
                        <td><?= $k['ultimo_uso'] ? date('d/m/Y H:i', strtotime($k['ultimo_uso'])) : '-' ?></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta API Key?');">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="acao" value="eliminar">
                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                <button type="submit" class="btn-icon btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem;">
            <h3>📡 Como usar a API REST</h3>
            <pre style="background:#f3f4f6;padding:12px;border-radius:8px;margin-top:8px;font-size:13px;overflow-x:auto;">
# Listar todos os sensores
curl -H "Authorization: Bearer SUA_API_KEY" <?= env('APP_URL', 'http://localhost/M7_Project/') ?>api/sensores

# Ver leituras (com filtros opcionais)
curl -H "Authorization: Bearer SUA_API_KEY" <?= env('APP_URL', 'http://localhost/M7_Project/') ?>api/leituras?sensor=1&limite=10

# Estatísticas
curl -H "Authorization: Bearer SUA_API_KEY" <?= env('APP_URL', 'http://localhost/M7_Project/') ?>api/estatisticas</pre>
        </div>
    </main>
    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>
