# FarmHandPro - Deployment Configuration

## Two Environments

### Local Development
- **Platform**: XAMPP (Windows/Mac/Linux)
- **PHP**: 8.2+
- **MySQL**: 5.7+
- **See**: [XAMPP Setup Guide](./XAMPP_SETUP.md)

### Production
- **Platform**: cPanel Shared Hosting
- **PHP**: 8.2+ (must support Laravel)
- **MySQL**: 5.7+
- **See**: [cPanel Deployment Guide](./CPANEL_DEPLOY.md)

## Quick Links

- [Local XAMPP Development](./XAMPP_SETUP.md)
- [cPanel Production Deployment](./CPANEL_DEPLOY.md)
- [Troubleshooting](./TROUBLESHOOTING.md)

## Key Differences

| Aspect | XAMPP (Local) | cPanel (Production) |
|--------|---------------|-------------------|
| Database | Local MySQL | Hosting MySQL |
| File Upload | Direct copy | FTP/File Manager |
| Configuration | .env file | cPanel + .env |
| SSL | Optional | Automatic (AutoSSL) |
| Backups | Manual | cPanel Backups |
| Domain | localhost | yourdomain.com |

## Support

For issues specific to your environment, see the relevant guide above.
