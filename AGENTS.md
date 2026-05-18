# M7_Project — Agent Guidelines

## Project
PHP/MySQL monitoring dashboard for ponds (charcos). No framework. Language: Portuguese.

## Entry
- `index.php` renders the dashboard (requires login). `Palmela/index.php` is public weather page (Open-Meteo API, no auth).
- `config.php` starts session & defines `BASE_URL`/`BASE_PATH` constants. Always included first via `require_once __DIR__ . '/config.php'`.
- `db.php` already includes `config.php`, so `include BASE_PATH . 'db.php'` is sufficient for DB pages.

## Setup
1. Import `DataBase/charco_db.sql` into MySQL
2. Edit credentials in `db.php`
3. Run at `http://localhost/M7_Project/`
4. All passwords hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` (plaintext = `password`)
5. Login auto-migrates plaintext passwords to bcrypt

## Auth
- `$_SESSION['user_id']` = logged in, `$_SESSION['user_admin']` = admin (both needed for admin pages)
- Admin checks `ADMIN` column (uppercase) in DB

## Directories
| Dir | Purpose |
|-----|---------|
| `auth/` | Login, register, logout, password reset, perfil |
| `Admin/` | User management (admin only) |
| `LT/` | Readings (leituras) CRUD |
| `SN/` | Sensors (sensores) CRUD |
| `RT/` | Report downloads (CSV, JSON, "PDF" via `window.print()`) |
| `Palmela/` | Public weather forecast (Chart.js CDN) |
| `struct/` | Header/footer templates |
| `assets/` | CSS, JS, `form-validation.js`, `script.js` |

## Key Quirks
- **DB**: mysqli only, `utf8mb4_unicode_ci` throughout, `preferencias` is JSON column
- **Reports CSV**: delimiter is `;` (Portuguese locale). `RT/gerar_relatorio.php` handles all types
- **PDF**: not real PDF — renders HTML then calls `window.print()` (browser print dialog)
- **Form validation**: client-side `assets/form-validation.js` validates ranges per unit (`°C`: -50–150, `%`: 0–100, `cm`/`m`: ≥0, `Lux`: 0–100k, `µg/m3`: 0–500). Server-side uses `includes/functions.php:validar_valor_unidade()`  
- **Shared helpers**: `includes/functions.php` — `tipo_para_unidade()`, `tipo_para_icone()`, `validar_valor_unidade()`, `csrf_input()`, `redirect()`, `tipos_ordenados()`
- **Dashboard alerts**: `assets/script.js` monitors cards against thresholds, shows click-to-dismiss overlay
- **Double-submit prevention**: `assets/script.js` disables submit buttons on any form submit
- **Admin search**: `#userSearch` input filters the user table in real-time (JS)
- **`data_hora` format**: datetime-local inputs use `Y-m-d\TH:i` (e.g. `2026-05-18T14:30`)
- **Password reset**: uses `password_reset_tokens` table (token stored, emailed)
- **`gerar_leituras.php`**: `TRUNCATE`s the `leituras` table then inserts realistic test data (run in browser)
- **`.gitignore`**: explicitly ignores `AGENTS.md` — do not commit changes to it