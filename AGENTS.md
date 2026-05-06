# M7_Project Agent Guidelines

## Project Structure
- PHP web application with MySQL database
- Main entrypoint: `index.php`
- Core files: `config.php`, `db.php`, `dashboard.php`
- Templates: `struct/header.php`, `struct/footer.php`
- Static assets: `assets/` (includes CSS, JS, and form validation)
- Auth system: `auth/` (login, register, logout)
- Admin panel: `Admin/`
- Database schema: `DataBase/charco_db.sql`
- Database connection in `db.php`

## Setup Instructions
1. Import database: `DataBase/charco_db.sql` into MySQL
2. Verify database credentials in `db.php` match your MySQL setup
3. Ensure PHP server is running (Apache/Nginx)
4. Access application at `http://localhost/M7_Project/`

## Development Commands
- No build system or package manager
- Run directly on PHP server (Apache/Nginx)
- Database: MySQL with credentials in `db.php`
- Base URL defined in `config.php`: `http://localhost/M7_Project/`

## Important Notes
- Session started in `config.php`
- All PHP files require `config.php` first
- Database queries use mysqli directly
- No frameworks or libraries used
- HTML/CSS/JS in PHP files
- Authentication checked via `$_SESSION['user_id']`
- Admin access requires `$_SESSION['user_admin']` set to true
- Client-side form validation implemented in `assets/form-validation.js`
- Registration form (`auth/register.php`) uses real-time validation with visual feedback