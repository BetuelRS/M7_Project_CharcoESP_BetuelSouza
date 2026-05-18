# M7_Project — Roadmap de Funcionalidades

> Ficheiro de planeamento. Riscado = implementado.

---

## APIs Públicas

- [ ] **IPMA API** — dados meteorológicos oficiais de Portugal (temperatura, vento, precipitação, estado do mar) para página Palmela
- [ ] **Telegram Bot** — enviar alertas de sensores e gráficos diários para um chat Telegram
- [ ] **Email alerts (PHPMailer)** — notificações por email com SMTP configurável
- [ ] **OpenStreetMap / Leaflet.js** — mapa interativo com localização dos sensores no charco
- [ ] **Webhooks / IFTTT** — integração com serviços externos (Google Home, Alexa, etc.)

## Exportação e Dados

- [ ] **PDF real (Dompdf)** — exportar relatórios como PDF verdadeiro (substituir `window.print()`)
- [x] **API REST** — endpoint JSON para consulta programática de leituras e sensores (api/index.php, autenticado via Bearer token)
- [ ] **Import CSV/JSON** — carregar leituras em massa a partir de ficheiro
- [ ] **Comparação de períodos** — nos gráficos, comparar mês-a-mês ou ano-a-ano

## Segurança e Admin

- [x] **Log de Auditoria** — registar quem criou/editou/eliminou cada sensor, leitura e utilizador (Admin/auditoria.php)
- [x] **API Keys** — gerar e gerir chaves de API para integrações externas (Admin/api_keys.php)
- [ ] **2FA / TOTP** — autenticação de dois fatores no login
- [x] **Gestão de Sessões** — registo de acessos visível no perfil do utilizador
- [ ] **Rate limiting no login** — bloquear temporariamente após N tentativas falhadas

## Infraestrutura

- [x] **.env + Composer** — variáveis de ambiente (DB, APP_URL, SESSION_LIFETIME, etc.) + autoload via includes/functions.php
- [ ] **Docker** — Dockerfile + docker-compose para ambiente dev/prod padronizado
- [ ] **GitHub Actions CI** — pipeline automática que corre lint e testes em cada push
- [ ] **PHPUnit** — testes unitários para `includes/functions.php` e CRUDs
- [ ] **PWA offline** — service worker + manifest para o dashboard funcionar offline

## IoT e Tempo Real

- [ ] **WebSockets / SSE** — atualização ao vivo do dashboard sem refresh manual
- [ ] **Endpoint ESP32** — endpoint HTTP para sensores enviarem leituras diretamente (sem CRUD manual)
- [ ] **MQTT Broker** — suporte a protocolo MQTT para receber dados de sensores IoT
- [ ] **Notificações push** — alertas no browser (Web Push API)

## Novas Funcionalidades

- [ ] **Modo escuro** — alternância claro/escuro no dashboard
- [ ] **Múltiplos charcos** — suporte a mais que um charco/estação de monitorização
- [ ] **Fotos de sensores** — upload de imagem para cada sensor
- [ ] **Dashboard público** — link partilhável só de leitura (sem login)
- [ ] **Alertas configuráveis** — cada utilizador define os seus thresholds por sensor
- [ ] **Widgets arrastáveis** — dashboard com layout personalizável

---

## Legendas

| Símbolo | Significado |
|---------|-------------|
| ☐ | Planeado / por implementar |
| ☑ | Implementado |
