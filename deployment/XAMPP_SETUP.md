# FarmHandPro - Local XAMPP Development Guide

## Prerequisites

1. **XAMPP Installed** (PHP 8.2+ strongly required)
   - Laravel 11 requires PHP 8.2; the framework itself will not install or run on
     earlier versions. Relaxing the `php` constraint in `composer.json` does **not**
     make Laravel work on PHP 8.0 – it only affects third‑party packages. If your
     current XAMPP has PHP 8.0 you must upgrade your PHP before installing
     dependencies. Options:
     * Download a XAMPP build with PHP 8.2 or newer and reinstall.
     * Install a standalone PHP 8.2/8.3 binary (for example via **winget**) and
       call Composer with that executable:
       `"C:\Program Files\PHP\8.3\php.exe" composer install`
       – use `where php` or `Get-Command php` to locate the binary.
   - Installing PHP 8.3 through winget works fine and is often easier than
     replacing XAMPP’s bundled PHP, just make sure the `php` command on your
     PATH points to the newer version when you run Composer or the development
     server.
   - You can temporarily run `composer install --ignore-platform-reqs` to
     bypass the version check, but this is not recommended as the code may crash
     at runtime.
   - The relaxed version in `composer.json` is only for very short-term
     convenience; always target PHP 8.2 for any real development or deployment.
   - [Download XAMPP](https://www.apachefriends.org/)
   - Includes: Apache, MySQL, PHP, Perl

2. **Git** (for cloning/version control)
   - [Download Git](https://git-scm.com/)

3. **Composer** (for PHP dependencies)
   - [Download Composer](https://getcomposer.org/)

## Step 1: Start XAMPP

1. Open XAMPP Control Panel
2. Click **Start** for:
   - Apache
   - MySQL
3. Verify both show "Running" in green

## Step 2: Clone Project to htdocs

```bash
cd C:\xampp\htdocs  # Windows
# or
cd /Applications/XAMPP/htdocs  # Mac
# or
cd /opt/lampp/htdocs  # Linux

git clone https://github.com/your-repo/farmhandpro.git
cd farmhandpro
```

## Step 3: Setup Database

### Method A: Using phpMyAdmin (GUI)

1. Open http://localhost/phpmyadmin in browser
2. Click "New" in left sidebar
3. Enter database name: `farmhandpro`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Method B: Using MySQL Command Line

```bash
# Open MySQL Command Line Client from XAMPP
# Or use terminal:
cd C:\xampp\mysql\bin
mysql -u root

CREATE DATABASE farmhandpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

## Step 4: Backend Setup (PHP compatibility)

```bash
cd farmhandpro/backend

# Copy environment file
copy .env.example .env

# Or on Mac/Linux:
# cp .env.example .env
```

### Edit `.env` File

Open `backend/.env` and update (verify PHP version compatibility first):

```env
APP_NAME="FarmHandPro"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# NOTE: if you see an error running `composer install` complaining about the
# PHP version, you **must** upgrade to PHP 8.2. The project uses Laravel 11,
# which cannot be downgraded without potentially rewriting parts of the code.
# The relaxed composer constraint only affects non-Laravel packages and will
# not prevent the "requires php ^8.2" error from Laravel itself.

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=farmhandpro
DB_USERNAME=root
DB_PASSWORD=

# Optional - for later
JWT_SECRET=your-secret-key-here
```

## Step 5: Install Dependencies

```bash
cd farmhandpro/backend

# Install PHP dependencies
composer install

# Generate app key
php artisan key:generate

# Run migrations (creates tables)
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
```

## Step 6: Setup Frontend

```bash
cd farmhandpro/frontend

# If using npm (optional for local PWA testing)
npm install
npm run dev
```

## Step 7: Run Development Server

### Option A: Using Laravel's Built-in Server

```bash
cd farmhandpro/backend
php artisan serve
```

Then access at: http://localhost:8000

### Option B: Using XAMPP Apache

1. Create alias in XAMPP htdocs: `farmhandpro` → points to `backend/public`
2. Or access via: `http://localhost/farmhandpro/public`

**Recommended: Use Option A** for better local development

## Step 8: Verify Installation

Visit: http://localhost:8000

You should see the Laravel welcome page or your frontend application.

### Check API Endpoints

```bash
# Open terminal/PowerShell
curl http://localhost:8000/api/auth/me

# You should get a 401 (Unauthenticated) response, which is correct
```

## Common Development Tasks

### Reset Database

```bash
cd backend

# Drop all tables and re-run migrations
php artisan migrate:refresh

# Reset and seed sample data
php artisan migrate:refresh --seed
```

### View Laravel Logs

```bash
cd backend
type storage/logs/laravel.log  # Windows
# or
tail -f storage/logs/laravel.log  # Mac/Linux
```

### Check Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select `farmhandpro` database
3. Browse tables to verify data

### Create Auth Token (for API testing)

```bash
# First, create a test user via artisan
php artisan tinker

# In tinker shell:
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role' => 'worker'
]);

# If using JWT, generate token manually or modify auth controller
exit
```

## Troubleshooting

### "Composer command not found"
- Install Composer: https://getcomposer.org/
- Restart terminal after installation

### "XAMPP MySQL won't start"
- Check if port 3306 is already in use
- Try: `netstat -ano | findstr :3306` (Windows)
- Change MySQL port in XAMPP config

### "Class not found" errors
```bash
cd backend
composer dump-autoload
php artisan cache:clear
```

### ".env file not found"
```bash
cd backend
copy .env.example .env
php artisan key:generate
```

### Migration errors
```bash
# Reset migrations
php artisan migrate:reset

# Then re-run
php artisan migrate
```

### Storage permissions error
```bash
# Windows PowerShell (as Admin):
icacls "C:\xampp\htdocs\farmhandpro\backend\storage" /grant Everyone:F /T

# Mac/Linux:
chmod -R 775 backend/storage backend/bootstrap/cache
```

## Testing the App

1. **Web Frontend**: http://localhost:8000
2. **API Testing**: Use Postman or cURL
3. **Database**: phpMyAdmin at http://localhost/phpmyadmin

## Next Steps

1. Create test worker account
2. Test clock in/out functionality
3. Submit sample reports
4. Verify offline mode with DevTools
5. Review [Production cPanel Deployment](./CPANEL_DEPLOY.md) when ready

## Need Help?

- Check Laravel docs: https://laravel.com/docs
- Check PHP docs: https://www.php.net/docs.php
- See [Troubleshooting Guide](./TROUBLESHOOTING.md)
