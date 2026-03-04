# FarmHandPro - Troubleshooting Guide

## Local Development (XAMPP)

### XAMPP Won't Start Apache/MySQL

**Problem**: Apache or MySQL services fail to start in XAMPP Control Panel

**Solutions**:
1. Check port conflicts:
   ```bash
   netstat -ano | findstr :80   # Apache port
   netstat -ano | findstr :3306 # MySQL port
   ```

2. Stop conflicting services (Skype, other Apache, etc.)

3. Try running as Administrator

4. Check XAMPP installation folder permissions

### Composer Issues

**Problem**: `composer` command not found

**Solution**:
1. Verify Composer is installed: `composer --version`
2. Add Composer to PATH environment variable
3. Restart terminal/shell after installation

### Database Connection Errors

**Problem**: "SQLSTATE[HY000] [2002] Connection refused"

**Solutions**:
1. Verify MySQL is running in XAMPP Control Panel
2. Check DB credentials in `.env`:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_USERNAME=root
   DB_PASSWORD=  # Empty for XAMPP default
   ```

3. Test connection via phpMyAdmin: `http://localhost/phpmyadmin`

### "Class not found" Errors

**Problem**: Errors like "Class 'App\Models\User' not found"

**Solutions**:
```bash
cd backend

# Regenerate autoloader
composer dump-autoload

# Clear caches
php artisan cache:clear
php artisan config:clear
```

### Service Worker Not Registering

**Problem**: PWA features not working, Service Worker not found

**Solutions**:
1. HTTPS is NOT required for localhost (only for production)
2. Check browser console for errors: F12 → Console tab
3. Verify `service-worker.js` path is correct
4. Clear browser cache: DevTools → Right-click → Empty cache and hard reload
5. Check Application tab → Service Workers in DevTools

### .env File Not Found

**Problem**: "Unable to find the .env file"

**Solutions**:
```bash
cd backend
copy .env.example .env      # Windows
cp .env.example .env        # Mac/Linux

php artisan key:generate
```

### Storage Permissions Error

**Problem**: "Unable to create storage directories"

**Solutions (Windows)**:
1. Run Command Prompt as Administrator:
   ```bash
   icacls "C:\xampp\htdocs\farmhandpro\backend\storage" /grant Everyone:F /T
   icacls "C:\xampp\htdocs\farmhandpro\backend\bootstrap" /grant Everyone:F /T
   ```

2. Or set folder permissions manually:
   - Right-click folder → Properties → Security
   - Add "Everyone" with Full Control

**Solutions (Mac/Linux)**:
```bash
chmod -R 775 backend/storage backend/bootstrap/cache
```

### Migration Issues

**Problem**: Migration fails or "no such table" errors

**Solutions**:
```bash
cd backend

# Reset all migrations
php artisan migrate:reset

# Redo migrations
php artisan migrate

# Or refresh (reset + run)
php artisan migrate:refresh --seed
```

### Port 8000 Already in Use

**Problem**: `php artisan serve` says port 8000 is in use

**Solutions**:
```bash
# Use different port
php artisan serve --port=8001

# Or find what's using 8000
netstat -ano | findstr :8000  # Windows
lsof -i :8000                 # Mac/Linux

# Kill the process and retry
```

---

## cPanel Production

### 500 Internal Server Error

**Problem**: Blank page with 500 error

**Solutions**:
1. **Check error logs**:
   - File Manager → `backend/storage/logs/laravel.log`
   - Check most recent entries

2. **Common causes**:
   ```
   A) .env file missing → Create it in backend/ folder
   B) Database credentials wrong → Update .env
   C) Permissions wrong → chmod 777 storage/ bootstrap/cache/
   D) Composer not installed → Run via web installer
   ```

3. **Enable debug mode** (temporarily):
   ```env
   APP_DEBUG=true
   ```
   Then check error message on page

### "This site can't be reached" / Connection Refused

**Problem**: Domain not loading at all

**Solutions**:
1. Verify domain points to hosting IP address
2. Check cPanel Addon Domains or DNS settings
3. Wait 24 hours for DNS propagation
4. Test via IP address: `https://hosting-ip-address:2083/public_html/`

### Database Connection Failed

**Problem**: "SQLSTATE[HY000] [2002] No such file or directory"

**Solutions**:
1. Verify DB credentials in `.env`:
   ```env
   DB_HOST=localhost  # Usually localhost on shared hosting
   DB_PORT=3306
   DB_DATABASE=username_farmhandpro
   DB_USERNAME=username_farmhandpro_user
   DB_PASSWORD=your-password
   ```

2. Verify database exists:
   - cPanel → MySQL Databases → Check list
   - Or phpMyAdmin → Left sidebar

3. Test connection via phpMyAdmin

### 403 Forbidden Error

**Problem**: "You don't have permission to access this resource"

**Solutions**:
1. **Check .htaccess**:
   - File should exist in `public_html/farmhandpro/backend/public/.htaccess`
   - If missing, create it with proper rules

2. **Check permissions**:
   ```bash
   chmod 755 public_html/farmhandpro/
   chmod 755 public_html/farmhandpro/backend/public/
   ```

3. **Check mod_rewrite**:
   - cPanel → Module Installers → Check if mod_rewrite enabled
   - Contact hosting if disabled

### API Endpoints Return 404

**Problem**: `/api/attendance/clock-in` returns "Not Found"

**Solutions**:
1. **Verify routes exist**:
   - Check `/backend/routes/api.php` file
   - Routes should be prefixed with `/api`

2. **Clear route cache**:
   - Delete `/backend/bootstrap/cache/routes.php` via File Manager
   - Or run (if SSH available):
     ```bash
     php artisan route:cache
     ```

3. **Check .htaccess** is working:
   - Try visiting: `yourdomain.com/api/auth/me`
   - Should return JSON error (not 404 page)

### Service Worker Not Registering

**Problem**: "Service Worker registration failed" in console

**Solutions**:
1. **HTTPS Required**: Service Worker only works on HTTPS
   - Enable SSL/TLS certificate (cPanel AutoSSL)
   - Update `APP_URL` in `.env` to use `https://`

2. **CORS Issues**: Check browser console for CORS errors
   - Verify Service Worker file is accessible
   - Try clearing browser cache

3. **File permissions**:
   ```bash
   chmod 644 public_html/farmhandpro/frontend/service-worker/sw.js
   ```

### Offline Sync Not Working

**Problem**: Data doesn't sync when going back online

**Solutions**:
1. **Check IndexedDB**: 
   - DevTools → Application → IndexedDB
   - Verify `farmhandpro_db` exists

2. **Check sync queue**:
   - Should see items when offline
   - Should clear when online

3. **Verify Service Worker**:
   - DevTools → Application → Service Workers
   - Should show "running"

4. **Check browser console** for sync errors

### High Memory Usage / Slow Performance

**Problem**: App loads slowly, high CPU/memory usage

**Solutions**:
1. **Enable caching**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Optimize Composer**:
   ```bash
   composer install --optimize-autoloader
   composer dump-autoload --optimize
   ```

3. **Check cPanel limits**:
   - Resource Usage in cPanel
   - May need to upgrade plan

### SSL Certificate Issues

**Problem**: "NET::ERR_CERT_AUTHORITY_INVALID" or certificate errors

**Solutions**:
1. **Renew certificate**:
   - cPanel → AutoSSL → Check status
   - Or Let's Encrypt SSL → Generate

2. **Force HTTPS redirect**:
   - Add to `.htaccess`:
     ```apache
     <IfModule mod_rewrite.c>
         RewriteEngine On
         RewriteCond %{HTTPS} off
         RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
     </IfModule>
     ```

3. **Update domain in .env**:
   ```env
   APP_URL=https://yourdomain.com
   ```

### Email Not Sending

**Problem**: Password reset/notification emails don't arrive

**Solutions**:
1. **Check mail configuration**:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=localhost
   MAIL_PORT=25
   MAIL_FROM_ADDRESS="noreply@yourdomain.com"
   ```

2. **Use cPanel Mail settings**:
   - cPanel → Email Accounts → Check defaults

3. **Test email**:
   - Create test script to verify SMTP works

### Migrations Won't Run

**Problem**: "nothing to migrate" or migration errors

**Solutions**:
1. **Check migrations table**:
   - phpMyAdmin → farmhandpro → migrations table
   - Should exist and have entries

2. **Run with force flag**:
   ```bash
   php artisan migrate --force
   ```

3. **Check database exists**:
   - phpMyAdmin → Check if database listed

---

## General Solutions

### Clear All Caches

**Local (XAMPP)**:
```bash
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:cache
php artisan view:cache
```

**Production (cPanel)** - Via file manager, delete:
- `storage/framework/cache/*`
- `storage/framework/views/*`
- `bootstrap/cache/config.php`
- `bootstrap/cache/routes.php`

### Check Application Logs

**Local**:
```bash
cd backend
type storage/logs/laravel.log    # Windows
tail -f storage/logs/laravel.log # Mac/Linux
```

**Production**:
- File Manager → `backend/storage/logs/laravel.log`
- Download and open in text editor

### Browser Console Errors

1. Open DevTools: F12
2. Click Console tab
3. Look for red error messages
4. Check Network tab for failed requests

### Check PHP Version

**Local**:
```bash
php --version
```

**Production**:
- cPanel → PHP Version (or check current)
- Must be 8.2+

### Contact Hosting Provider

If still stuck:
- Provide hosting ticket with:
  - Error message
  - Error logs (from laravel.log)
  - What you were trying to do
  - Steps to reproduce

---

## Quick Checklist

- [ ] Database created and credentials in `.env`
- [ ] Composer dependencies installed
- [ ] Migrations ran successfully
- [ ] File permissions set correctly (storage, bootstrap)
- [ ] HTTPS enabled (production only)
- [ ] App key generated in `.env`
- [ ] No 500 errors in logs
- [ ] API endpoints accessible
- [ ] Service Worker registered (production)
- [ ] Offline mode tested

If all checks pass, app should be working!
