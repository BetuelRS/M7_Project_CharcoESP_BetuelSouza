# DashBoard ESP

Sistema de monitorização ambiental para charcos, com gestão de sensores e leituras.

## Tech Stack

- **Backend:** PHP 8+ (sem framework)
- **Base de Dados:** MySQL
- **Frontend:** HTML, CSS (Tailwind-like), JavaScript
- **Servidor:** Apache/Nginx

## Quick Start

### 1. Importar Base de Dados

```bash
mysql -u root -p charco_db < DataBase/charco_db.sql
```

### 2. Configurar Credenciais

Editar `db.php` com as credenciais MySQL:
```php
$servername = "localhost";
$username = "seu_user";
$password = "sua_senha";
$dbname = "charco_db";
```

### 3. Executar Servidor

Aceder a `http://localhost/M7_Project/`

### 4. Login

| Username | Password |
|----------|----------|
| admin1   | password |
| tecnico1 | password |

## Estrutura

```
M7_Project/
├── auth/           # Login, registo, recuperação de password
├── Admin/          # Gestão de utilizadores (admin only)
├── LT/             # Leituras (CRUD)
├── SN/             # Sensores (CRUD)
├── RT/             # Relatórios e downloads
├── struct/         # Header/Footer templates
├── assets/         # CSS, JS
├── DataBase/       # SQL e scripts auxiliares
├── config.php      # Configuração global
└── db.php          # Conexão MySQL
```

## Funcionalidades

- Dashboard com estatísticas em tempo real
- Gestão completa de sensores (CRUD)
- Registo de leituras com validação por unidade
- Gráficos de evolução
- Relatórios exportáveis (CSV, JSON, PDF)
- Sistema de autenticação com roles (admin/utilizador)
- Recuperação de password por email

## Desenvolvimento

### Gerar Leituras de Teste
Executar via navegador: `http://localhost/M7_Project/DataBase/gerar_leituras.php`

### Validar Formulários
O ficheiro `assets/form-validation.js` faz validação client-side com regras específicas por unidade:
- `°C`: -50 a 150
- `%`: 0 a 100
- `Lux`: 0 a 100.000
- `µg/m3`: 0 a 500