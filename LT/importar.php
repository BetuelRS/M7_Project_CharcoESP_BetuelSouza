<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

$sensores = $conn->query("SELECT cod_sensor, nome, tipo FROM sensores WHERE ativo = 1 ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Leituras - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <style>
        .drop-zone { border: 2px dashed #d1d5db; border-radius: 12px; padding: 40px; text-align: center; cursor: pointer; transition: .2s; background: #f9fafb; }
        .drop-zone:hover, .drop-zone.dragover { border-color: #3b82f6; background: #eff6ff; }
        .drop-zone i { font-size: 48px; color: #9ca3af; margin-bottom: 12px; }
        .drop-zone p { color: #6b7280; margin: 0; }
        .preview-table { width:100%; border-collapse:collapse; margin-top:16px; font-size:13px; }
        .preview-table th { background:#f3f4f6; padding:8px; text-align:left; border:1px solid #e5e7eb; }
        .preview-table td { padding:8px; border:1px solid #e5e7eb; }
        .preview-table .valid-row { background:#f0fdf4; }
        .preview-table .invalid-row { background:#fef2f2; }
        .import-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin:16px 0; }
        .import-stat { background:white; padding:16px; border-radius:8px; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .import-stat .num { font-size:24px; font-weight:700; }
        .import-stat .label { font-size:12px; color:#6b7280; }
    </style>
</head>
<body>
    <header><?php include '../struct/header.php'; ?></header>
    <main class="readings-container">
        <h1 class="page-title"><i class="fas fa-upload"></i> Importar Leituras</h1>

        <?php if (isset($_SESSION['import_result'])): $r = $_SESSION['import_result']; ?>
        <div class="import-stats">
            <div class="import-stat"><div class="num" style="color:#10b981;"><?= $r['sucesso'] ?></div><div class="label">Importadas</div></div>
            <div class="import-stat"><div class="num" style="color:#ef4444;"><?= $r['erros_count'] ?></div><div class="label">Com erros</div></div>
            <div class="import-stat"><div class="num" style="color:#6b7280;"><?= $r['total'] ?></div><div class="label">Total linhas</div></div>
        </div>
        <?php if (!empty($r['erros'])): ?>
        <div class="alert alert-error"><strong>Erros:</strong>
            <ul style="margin:8px 0 0 16px;"><?php foreach ($r['erros'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <?php unset($_SESSION['import_result']); ?>
        <?php endif; ?>

        <div class="form-container">
            <div class="alert alert-info" style="margin-bottom:1rem;">
                <strong>📋 Formatos aceites:</strong><br>
                <b>CSV</b> (delimitador <code>;</code>) — colunas: <code>cod_sensor;valor;unidade;data_hora;observacoes</code><br>
                <b>JSON</b> — array de objetos com os mesmos campos<br>
                <small>A data/hora deve estar no formato <code>YYYY-MM-DD HH:MM:SS</code> ou <code>YYYY-MM-DD\THH:MM</code></small>
            </div>

            <form action="processar_importacao.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="drop-zone" id="dropZone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Clique ou arraste</strong> um ficheiro CSV ou JSON</p>
                    <p style="font-size:12px;color:#9ca3af;">.csv ou .json</p>
                    <input type="file" id="fileInput" name="ficheiro" accept=".csv,.json" required style="display:none;">
                </div>
                <div id="fileInfo" style="display:none;margin-top:12px;padding:12px;background:#f3f4f6;border-radius:8px;"></div>
                <div id="previewArea" style="display:none;margin-top:16px;"></div>
                <button type="submit" id="submitBtn" class="btn btn-primary" style="margin-top:16px;display:none;">
                    <i class="fas fa-upload"></i> Importar Leituras
                </button>
            </form>

            <div style="margin-top:2rem;">
                <h3>📥 Exemplo de ficheiro CSV</h3>
                <pre style="background:#f3f4f6;padding:12px;border-radius:8px;font-size:12px;overflow-x:auto;">cod_sensor;valor;unidade;data_hora;observacoes
1;23.5;°C;2026-05-18 14:30:00;Leitura de teste
2;68;%;2026-05-18 14:30:00;
3;450;lux;2026-05-18 14:30:00;Pico solar</pre>
            </div>

            <div style="margin-top:1.5rem;">
                <a href="Leituras.php" class="btn btn-water"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>
        </div>
    </main>
    <footer><?php include '../struct/footer.php'; ?></footer>
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const previewArea = document.getElementById('previewArea');
        const submitBtn = document.getElementById('submitBtn');

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('dragover'); fileInput.files = e.dataTransfer.files; handleFile(); });
        fileInput.addEventListener('change', handleFile);

        function handleFile() {
            const file = fileInput.files[0];
            if (!file) return;
            fileInfo.style.display = 'block';
            fileInfo.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            submitBtn.style.display = 'inline-flex';
            dropZone.style.borderColor = '#10b981';

            if (file.name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const lines = e.target.result.split('\n').filter(l => l.trim());
                    if (lines.length < 2) return;
                    const headers = lines[0].split(';');
                    let html = '<table class="preview-table"><thead><tr>';
                    headers.forEach(h => html += `<th>${h.trim()}</th>`);
                    html += '</tr></thead><tbody>';
                    for (let i = 1; i < Math.min(lines.length, 6); i++) {
                        const cols = lines[i].split(';');
                        html += '<tr>';
                        cols.forEach(c => html += `<td>${c.trim()}</td>`);
                        html += '</tr>';
                    }
                    if (lines.length > 6) html += `<tr><td colspan="${headers.length}" style="text-align:center;color:#9ca3af;">... e mais ${lines.length - 6} linha(s)</td></tr>`;
                    html += '</tbody></table>';
                    previewArea.innerHTML = '<h4>Pré-visualização (primeiras linhas):</h4>' + html;
                    previewArea.style.display = 'block';
                };
                reader.readAsText(file);
            } else if (file.name.endsWith('.json')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = JSON.parse(e.target.result);
                        if (!Array.isArray(data) || data.length === 0) return;
                        const headers = Object.keys(data[0]);
                        let html = '<table class="preview-table"><thead><tr>';
                        headers.forEach(h => html += `<th>${h}</th>`);
                        html += '</tr></thead><tbody>';
                        for (let i = 0; i < Math.min(data.length, 5); i++) {
                            html += '<tr>';
                            headers.forEach(h => html += `<td>${data[i][h] ?? ''}</td>`);
                            html += '</tr>';
                        }
                        if (data.length > 5) html += `<tr><td colspan="${headers.length}" style="text-align:center;color:#9ca3af;">... e mais ${data.length - 5} registro(s)</td></tr>`;
                        html += '</tbody></table>';
                        previewArea.innerHTML = '<h4>Pré-visualização (primeiros registos):</h4>' + html;
                        previewArea.style.display = 'block';
                    } catch(e) {
                        previewArea.innerHTML = '<div class="alert alert-error">JSON inválido</div>';
                        previewArea.style.display = 'block';
                    }
                };
                reader.readAsText(file);
            }
        }
    </script>
</body>
</html>
