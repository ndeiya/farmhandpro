# FarmHandPro - Farm Workforce & Operations Management System

A Progressive Web App (PWA) for farm owners to manage workers, operations, payroll, and analytics.

## Features

- **Worker Management**: Track attendance and clock in/out
- **Operations Monitoring**: Manage crops, animals, equipment, and inventory
- **Payroll Management**: Automated payroll processing
- **Analytics & Reports**: Data-driven insights with charts and dashboards
- **Offline Support**: Full functionality offline with background sync
- **Mobile-First**: Responsive design optimized for field use

## Technology Stack

- **Backend**: PHP 8.2+ with Laravel
- **Database**: MySQL 8+
- **Frontend**: HTML5, Tailwind CSS, Alpine.js
- **PWA**: Service Worker, IndexedDB, manifest.json
- **Deployment**: Nginx, PHP-FPM, Ubuntu VPS

## Project Structure

```
farmhandpro/
├── backend/           # Laravel API & backend logic
│   ├── app/          # Application models & controllers
│   ├── database/     # Migrations & seeders
│   ├── routes/       # API routes
│   └── resources/    # View templates
├── frontend/         # PWA frontend
│   ├── public/       # Static assets
│   ├── assets/       # CSS, JS, images
│   └── service-worker/ # Service Worker & offline logic
└── deployment/       # Configuration & deployment scripts
```

## Getting Started

### Local Development (XAMPP)

The application targets PHP 8.2 in production because Laravel 11 requires it.
You cannot run Laravel 11 on PHP 8.0; attempting to install dependencies will
fail just as you're seeing. The composer.json file has a relaxed PHP constraint
only so that some ancillary packages are installable, but the framework itself
will still demand 8.2. You can satisfy that requirement by one of the following:

- Upgrade your XAMPP installation to a version bundling PHP 8.2 or later.
- Install PHP 8.2/8.3 separately (for example via **winget** on Windows) and run
  Composer with that binary:
  `"C:\Program Files\PHP\8.3\php.exe" composer install`.

Either approach works; the key is that the `php` command executing Composer must
be 8.2+.  Once you have a PHP 8.3 binary from winget, just ensure it’s on your
PATH and `php -v` reports 8.3 before invoking Composer.

See [XAMPP Setup Guide](./deployment/XAMPP_SETUP.md) for complete instructions:

```bash
# Quick start:
cd backend
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Then visit: http://localhost:8000

### Production Deployment (cPanel)

See [cPanel Deployment Guide](./deployment/CPANEL_DEPLOY.md) for complete instructions.

**Quick Overview**:
1. Create MySQL database in cPanel
2. Upload files via FTP/File Manager
3. Configure `.env` with DB credentials
4. Run `composer install` (via terminal or web script)
5. Run migrations
6. Set SSL certificate
7. Point domain to `public_html/farmhandpro/backend/public`

### Troubleshooting

Having issues? Check [Troubleshooting Guide](./deployment/TROUBLESHOOTING.md)

## User Roles

- **Worker**: Clocks in/out, submits reports
- **Supervisor**: Manages workers, approves reports
- **Farm Owner**: Full system access, analytics, payroll
- **Admin**: System configuration & user management

## PWA Features

- Offline-first architecture with IndexedDB
- Background sync for pending operations
- Add to homescreen capability
- Push notifications (future)

## API Endpoints

- `POST /api/auth/login` - User authentication
- `POST /api/attendance/clock-in` - Clock in
- `POST /api/attendance/clock-out` - Clock out
- `GET /api/reports` - List reports
- `POST /api/reports` - Submit report
- `GET /api/payroll` - Payroll information
- `GET /api/analytics` - Farm analytics

## Development

```bash
# Run tests
php artisan test

# Format code
php artisan pint

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## Deployment

See [deployment/README.md](deployment/README.md) for production deployment instructions.

## License

Proprietary
