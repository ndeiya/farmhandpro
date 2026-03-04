#!/bin/bash

# FarmHandPro Deployment Script
# Usage: ./deploy.sh [staging|production]

set -e

ENVIRONMENT=${1:-production}
DEPLOY_DIR="/var/www/farmhandpro"
BACKUP_DIR="/var/backups/farmhandpro"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting FarmHandPro deployment to $ENVIRONMENT..."

# Create backup
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
if [ -d "$DEPLOY_DIR" ]; then
    tar -czf "$BACKUP_DIR/backup_$TIMESTAMP.tar.gz" "$DEPLOY_DIR"
fi

# Pull latest code
echo "📥 Pulling latest code..."
cd $DEPLOY_DIR
git pull origin main

# Install dependencies
echo "📚 Installing dependencies..."
cd backend
composer install --no-dev --optimize-autoloader

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Seed database (only in staging)
if [ "$ENVIRONMENT" = "staging" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed
fi

# Clear and cache
echo "🧹 Clearing and caching configuration..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart services
echo "🔄 Restarting services..."
systemctl restart nginx
systemctl restart php8.2-fpm

echo "✅ Deployment completed successfully!"
echo "📋 Backup saved to: $BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
