# FarmHandPro - cPanel Production Deployment Guide

## Prerequisites

1. **Shared Hosting Account** with cPanel access
   - PHP 8.2+ support required
   - MySQL 5.7+ support required
   - Sufficient disk space (~500MB minimum)

2. **Domain Name** (already pointed to your hosting)

3. **FTP Client** (optional, for file management)
   - FileZilla, WinSCP, or use cPanel File Manager

4. **cPanel Credentials**
   - You'll need username and password

## Step 1: Access cPanel

1. Log in to cPanel: `https://yourdomain.com:2083` or `https://your-ip:2083`
2. Or via hosting provider's control panel
3. Search for "MySQL Database Wizard" and "File Manager"

## Step 2: Create MySQL Database

### Using MySQL Database Wizard (Easiest)

1. In cPanel, find **MySQL Database Wizard**
2. **Step 1 - Create Database:**
   - Database Name: `farmhandpro` (or `username_farmhandpro`)
   - Click "Next >"

3. **Step 2 - Create User:**
   - Username: `farmhandpro_user` (or similar)
   - Password: Create a strong password, save it!
   - Click "Create User >"

4. **Step 3 - Add Privileges:**
   - Select user and database
   - Check "All Privileges"
   - Click "Next >"

5. **Step 4 - Complete**
   - Note the database name, username, and password

### Alternative: phpMyAdmin

1. In cPanel, click **phpMyAdmin**
2. Create new database: `farmhandpro`
3. Create new user with full privileges

## Step 3: Upload Project Files

### Option A: Using File Manager (Easiest)

1. In cPanel, click **File Manager**
2. Navigate to `public_html` folder
3. Create new folder: `farmhandpro`
4. Upload your project files there
5. **Important**: The `backend/public` folder should be at root level or referenced properly

### Option B: Using FTP

```bash
# FTP Credentials (from cPanel > FTP Accounts)
Server: your-domain.com
Username: cpanel-username
Password: cpanel-password
Port: 21

# Upload to: public_html/farmhandpro/
```

### Recommended Directory Structure

```
public_html/
├── farmhandpro/
│   ├── backend/
│   │   ├── app/
│   │   ├── database/
│   │   ├── storage/
│   │   ├── bootstrap/
│   │   ├── public/
│   │   ├── routes/
│   │   ├── .env
│   │   ├── composer.json
│   │   └── ...
│   └── frontend/
└── (other domains)
```

## Step 4: Configure .env File

### Method A: Using File Manager

1. Open **File Manager** in cPanel
2. Navigate to `public_html/farmhandpro/backend/`
3. Edit `.env.example` file:
   - Click **Edit** (or use text editor)
   - Copy contents to new file called `.env`

### Edit .env with cPanel Values

```env
APP_NAME="FarmHandPro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=farmhandpro_db          # From Step 2
DB_USERNAME=farmhandpro_user       # From Step 2
DB_PASSWORD=your-strong-password   # From Step 2

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Save .env

1. Save the file with name `.env`
2. Verify it's in: `public_html/farmhandpro/backend/.env`

## Step 5: Install Composer Dependencies

### Using cPanel Terminal (If Available)

1. In cPanel, search **Terminal** or **SSH**
2. Run:

```bash
cd public_html/farmhandpro/backend
composer install --no-dev --optimize-autoloader
```

### If Terminal/SSH Not Available

Use a **Web-Based Installer Script**:

1. Create file `public_html/farmhandpro/install.php`:

```php
<?php
$output = shell_exec('cd ' . __DIR__ . '/backend && composer install --no-dev 2>&1');
echo '<pre>' . htmlspecialchars($output) . '</pre>';
?>
```

2. Visit: `https://your-domain.com/farmhandpro/install.php`
3. **DELETE THIS FILE AFTER INSTALLATION**

## Step 6: Generate App Key

### Via Terminal (Preferred)

```bash
cd public_html/farmhandpro/backend
php artisan key:generate
```

### Via cPanel File Manager Script

Create `public_html/farmhandpro/keygen.php`:

```php
<?php
require 'backend/vendor/autoload.php';
$app = require_once 'backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->call('key:generate');
echo 'Key generated!';
?>
```

Visit: `https://your-domain.com/farmhandpro/keygen.php`
**DELETE THIS FILE AFTER**

## Step 7: Run Database Migrations

### Via Terminal

```bash
cd public_html/farmhandpro/backend
php artisan migrate --force
```

### Via Artisan Script

Create `public_html/farmhandpro/migrate.php`:

```php
<?php
require 'backend/vendor/autoload.php';
$app = require_once 'backend/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->call('migrate', ['--force' => true]);
echo 'Migrations completed!';
?>
```

Visit: `https://your-domain.com/farmhandpro/migrate.php`
**DELETE THIS FILE AFTER**

## Step 8: Configure Public Folder

### Option A: Addon Domain

If using a separate domain for the app:

1. In cPanel, click **Addon Domains**
2. Add domain pointing to `public_html/farmhandpro/backend/public`

### Option B: Subdomain with public_html pointer

1. Create subdomain: `app.yourdomain.com`
2. Point to: `public_html/farmhandpro/backend/public`

### Option C: Folder Access

Access via: `https://yourdomain.com/farmhandpro/backend/public/`

(Less ideal, but works for testing)

## Step 9: Set File Permissions

### Via cPanel Terminal

```bash
chmod -R 755 public_html/farmhandpro/
chmod -R 777 public_html/farmhandpro/backend/storage
chmod -R 777 public_html/farmhandpro/backend/bootstrap/cache
```

### Via File Manager

1. Right-click folder → **Change Permissions**
2. Set to:
   - `storage/` → 777
   - `bootstrap/cache/` → 777
   - Other folders → 755

## Step 10: Enable SSL Certificate

1. In cPanel, click **AutoSSL** or **Let's Encrypt SSL**
2. Select your domain
3. Click **Issue Certificate**
4. Wait for completion (usually instant)

## Step 11: Enable .htaccess for Laravel

Laravel needs `.htaccess` for pretty URLs.

### Check if it exists

Navigate to `public_html/farmhandpro/backend/public/`

Look for `.htaccess` file. If not present, create it:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_friendlyurls.c>
        RewriteBase /
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
    </IfModule>
</IfModule>
```

## Step 12: Verify Installation

### Test Frontend

Visit: `https://yourdomain.com` (or your configured domain)

You should see the FarmHandPro app interface

### Test API

```bash
curl https://yourdomain.com/api/auth/me
# Should return 401 Unauthorized (expected - not logged in)
```

### Check Logs

```bash
# In cPanel File Manager, navigate to:
public_html/farmhandpro/backend/storage/logs/
# View laravel.log for any errors
```

## Post-Deployment

### 1. Hide Installation Files

Delete these if you created them:
- `install.php`
- `keygen.php`
- `migrate.php`

### 2. Set Up Cron Job for Laravel Scheduler

In cPanel, find **Cron Jobs**:

Add this cron:
```bash
* * * * * /usr/bin/php /home/username/public_html/farmhandpro/backend/artisan schedule:run >> /dev/null 2>&1
```

(Replace `username` with your cPanel username)

### 3. Setup Automatic Backups

Use cPanel's **Backup Wizard** to schedule automatic backups

### 4. Enable Error Reporting (Optional)

In `.env`, set:
```env
APP_DEBUG=false
APP_LOG_LEVEL=error
```

### 5. Test Offline Functionality

1. Open app in browser
2. Clock in
3. Open DevTools → Network tab → **Offline**
4. Try to clock out
5. Go back **Online**
6. Check if data syncs

## Troubleshooting

### "Composer: command not found"

Use the web installer script method instead, or contact hosting provider to enable SSH access.

### "500 Internal Server Error"

1. Check `/backend/storage/logs/laravel.log`
2. Verify `.env` file exists and has correct DB credentials
3. Verify `composer install` ran successfully
4. Check PHP version is 8.2+

### "Database connection failed"

1. Verify DB credentials in `.env` match cPanel database
2. Verify `DB_HOST=localhost` (usually correct for shared hosting)
3. Check database exists in phpMyAdmin

### "storage/ permission denied"

Set permissions:
```bash
chmod 777 backend/storage
chmod 777 backend/bootstrap/cache
```

### "Service Worker not registering"

1. Ensure HTTPS is enabled (SSL certificate installed)
2. Check browser console for CORS errors
3. Service Worker requires HTTPS in production

### App works but API returns 404

1. Verify `.htaccess` is in place
2. Check mod_rewrite is enabled in cPanel
3. Verify URL is correct: `yourdomain.com/api/endpoint`

## Performance Tips

1. **Enable cPanel Caching**
   - Use Redis or Memcached if available
   - Update `CACHE_DRIVER` in `.env`

2. **Optimize Laravel**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Enable GZIP Compression**
   - Usually enabled by default in cPanel

4. **Use CDN for Static Assets**
   - Frontend CSS/JS files

## Next Steps

1. Create admin user account
2. Test complete user flow
3. Monitor error logs
4. Set up email notifications
5. Plan regular backups

## Support Resources

- cPanel Documentation: https://docs.cpanel.net/
- Laravel on Shared Hosting: https://laravel.com/docs/deployment
- PHP Issues: Check hosting provider's support

