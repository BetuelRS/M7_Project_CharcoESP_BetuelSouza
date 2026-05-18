<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
require_once BASE_PATH . 'includes/functions.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

// Busca sensores ativos com tipo para definir unidade automaticamente
$sensores = $conn->query("SELECT cod_sensor, nome, tipo FROM sensores WHERE ativo = 1 ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Leitura</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="<?= BASE_URL ?>assets/form-validation.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">Nova Leitura</h1>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'validacao' && isset($_SESSION['erros_validacao'])): ?>
            <div class="form-container" style="margin-bottom: 1rem;">
                <div class="alert alert-error">
                    <strong>Erros de validação:</strong>
                    <ul style="margin: 0.5rem 0 0 1rem;">
                        <?php foreach ($_SESSION['erros_validacao'] as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php unset($_SESSION['erros_validacao']); ?>
        <?php endif; ?>

        <div class="form-container">
            <form action="Leituras_add_process.php" method="post" class="readings-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="cod_sensor">Sensor:</label>
                    <select name="cod_sensor" id="cod_sensor" required>
                        <option value="" data-tipo="">Selecione um sensor</option>
                        <?php while ($sensor = $sensores->fetch_assoc()): ?>
                            <option value="<?= $sensor['cod_sensor'] ?>" data-tipo="<?= htmlspecialchars($sensor['tipo']) ?>">
                                <?= htmlspecialchars($sensor['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor">Valor:</label>
                    <input type="number" step="0.01" id="valor" name="valor" required placeholder="Ex: 23.5">
                </div>
                <!-- Unidade pode ser algo como °C, %, m, etc.Select -->
                 
                <div class="form-group">
                    <label for="unidade">Unidade:</label>
                    <select id="unidade" name="unidade" required>
                        <option value="">Selecione a unidade</option>
                        <option value="°C">°C</option>
                        <option value="%">%</option>
                        <option value="cm">cm</option>
                        <option value="µg/m3">µg/m3</option>
                        <option value="lux">lux</option>
                    </select>
                    <small style="color: #666;">A unidade será definida automaticamente ao selecionar o sensor</small>
                </div>

                <div class="form-group">
                    <label for="data_hora">Data/Hora:</label>
                    <input type="datetime-local" id="data_hora" name="data_hora" required>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Opcional"></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Adicionar Leitura
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="Leituras.php" class="btn btn-water">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
    <script>
        const unidadesPorTipo = <?= json_encode(array_combine(tipos_ordenados(), array_map('tipo_para_unidade', tipos_ordenados()))) ?>;
        
        document.getElementById('cod_sensor').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const tipo = selectedOption.getAttribute('data-tipo');
            const unidadeSelect = document.getElementById('unidade');
            
            if (tipo && unidadesPorTipo[tipo]) {
                unidadeSelect.value = unidadesPorTipo[tipo];
            }
        });
    </script>
</body>
</html>