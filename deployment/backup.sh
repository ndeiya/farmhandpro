#!/bin/bash

# FarmHandPro Backup Script
# Automatically backs up database and files daily

set -e

BACKUP_DIR="/var/backups/farmhandpro"
DB_NAME="farmhandpro"
DB_USER="farmhandpro"
DB_PASSWORD="${DB_PASSWORD}"
KEEP_DAYS=30
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
echo "🗄️  Backing up database..."
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" | gzip > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz"

# Backup application files
echo "📦 Backing up application files..."
tar -czf "$BACKUP_DIR/app_backup_$TIMESTAMP.tar.gz" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    /var/www/farmhandpro

# Remove old backups
echo "🧹 Cleaning up old backups..."
find $BACKUP_DIR -type f -name "*.gz" -mtime +$KEEP_DAYS -delete

echo "✅ Backup completed!"
