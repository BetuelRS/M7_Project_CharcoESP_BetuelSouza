# M7_Project Agent Guidelines

## Project
- PHP/MySQL web app (no framework). Entry: `index.php`
- Language: Portuguese throughout

## Setup
1. Import `DataBase/charco_db.sql` into MySQL
2. Match credentials in `db.php` (server/username/password/dbname)
3. Run on Apache/Nginx at `http://localhost/M7_Project/`
4. Default credentials: `admin1`/`password` (bcrypt hash is `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`)

## PHP Architecture
- **Always** `require_once __DIR__ . '/config.php'` first (starts session, defines `BASE_URL`/`BASE_PATH`)
- Auth: `$_SESSION['user_id']` for login, `$_SESSION['user_admin']` for admin check
- Database: mysqli directly (no ORM). Include `db.php` after `config.php`

## Directories
| Dir | Purpose |
|-----|---------|
| `auth/` | Login, register, logout, password recovery |
| `Admin/` | User management (requires admin session) |
| `LT/` | Readings (leituras) - list, add, edit, delete |
| `SN/` | Sensors (sensores) - list, add, edit, delete |
| `RT/` | Reports (relatórios) - download CSV/JSON exports |
| `struct/` | Header/footer templates |
| `assets/` | CSS, JS, form-validation.js |

## Form Validation
- Client-side: `assets/form-validation.js`
- Unit-aware range validation: `°C` (-50 to 150), `%` (0-100), `cm`/`m` (non-neg), `Lux` (0-100k), `µg/m3` (0-500)
- Real-time validation on registration and CRUD forms

## Development Utilities
- `DataBase/gerar_leituras.php` - generate test readings (run via browser)