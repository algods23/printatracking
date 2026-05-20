# Printa Signages - Desktop Application Build & Deployment Guide

## Overview

This guide covers building and deploying the Printa Signages desktop application as a standalone Electron-based application.

## Prerequisites

- **Node.js** v16+ and npm
- **PHP** 8.1+
- **Composer**
- **Git** (optional)
- **Windows 10/11** for building Windows installers
- **Visual Studio Build Tools** (for Windows native modules)

## Setup Instructions

### 1. Initial Setup

```bash
# Navigate to project directory
cd c:\laragon\www\printatracking

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Configuration

```bash
# Create .env file
copy .env.example .env

# Generate application key
php artisan key:generate

# Set database to SQLite (offline mode)
# Update .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed
```

## Development

### Run Development Environment

```bash
# Terminal 1: Start Vite dev server
npm run dev

# Terminal 2: Start Electron in development mode
npm run electron-dev
```

This will:
1. Start Vite development server on http://localhost:5173
2. Automatically start Laravel backend on http://localhost:8000
3. Launch Electron window with hot reload

### Build Assets for Development

```bash
npm run build
```

## Building for Production

### Step 1: Build Frontend Assets

```bash
npm run build
```

This creates optimized assets in the `public/` directory.

### Step 2: Build Electron Installer

#### For Windows (Recommended)

```bash
# Build Windows installer (.exe)
npm run electron-build-win

# Or all Windows formats
npm run electron-build
```

The installer will be created in the `dist/` folder:
- `Printa Signages Setup 1.0.0.exe` - NSIS Installer
- `Printa Signages 1.0.0.exe` - Portable Executable

#### For macOS

```bash
npm run electron-build-mac
```

#### For Linux

```bash
npm run electron-build-linux
```

### Step 3: Package for Distribution

```bash
# This creates a distributable package without building
npm run electron-pack
```

## Application Icon Setup

1. Create a professional icon (256x256 PNG)
2. Place in `assets/icon.png`
3. Convert to `.ico` format for Windows
4. Update `electron/main.js` icon path if needed

## Code Signing (Optional, for Distribution)

### Windows Code Signing

```bash
# Install signing certificate or use test certificate
# Update package.json:
"win": {
    "certificateFile": "path/to/certificate.pfx",
    "certificatePassword": "password"
}
```

## Distribution Methods

### Method 1: Direct Installer Distribution

1. Build installer: `npm run electron-build-win`
2. Host `dist/Printa Signages Setup 1.0.0.exe` on download server
3. Users download and run installer
4. Application automatically installed and ready to use

### Method 2: Portable Version

1. Use portable executable from `dist/`
2. Can be run from USB drive
3. No installation required
4. Useful for enterprise deployment

### Method 3: Auto-Update (Optional)

Add electron-updater for auto-updates:

```bash
npm install electron-updater
```

Update `electron/main.js`:

```javascript
const { autoUpdater } = require("electron-updater");

app.on('ready', () => {
    autoUpdater.checkForUpdatesAndNotify();
});
```

## System Requirements

- **OS**: Windows 10/11 (64-bit)
- **RAM**: 2GB minimum, 4GB recommended
- **Disk Space**: 500MB minimum
- **Display**: 1024x768 or higher
- **.NET Framework**: 4.5+ (bundled with installer)

## File Structure After Build

```
dist/
├── Printa Signages Setup 1.0.0.exe     # NSIS Installer
├── Printa Signages 1.0.0.exe           # Portable executable
├── latest.yml                          # Update metadata (if enabled)
└── builder-effective-config.yaml       # Build configuration
```

## Troubleshooting

### Build Fails with "Cannot find php"

**Solution**: Ensure PHP is in system PATH or specify full path in electron/main.js

```javascript
const phpPath = 'C:\\laragon\\bin\\php\\php-8.x.x-Win32-vs16-x64\\php.exe';
```

### Installer Fails on User's Machine

**Possible Causes**:
1. .NET Framework not installed → Bundle with installer
2. Missing Visual C++ Redistributable → Add to installer dependencies
3. Antivirus blocking → Have user whitelist application

### Application Won't Start

**Troubleshooting Steps**:
1. Check if port 8000 is available
2. Ensure SQLite database is not locked
3. Check `storage/logs/laravel.log` for errors
4. Verify all migrations ran successfully

### Database Issues in Deployment

**Solution**: SQLite database should be at `database/database.sqlite`

Ensure folder permissions:
- `storage/` - writable
- `database/` - writable
- `public/` - readable

## Performance Optimization

### For Production Build:

1. **Minimize bundle size**
   ```bash
   npm run build -- --minify=terser
   ```

2. **Enable compression in Laravel**
   ```php
   // config/app.php
   'compression' => true,
   ```

3. **Use SQLite pragmas for better performance**
   ```php
   DB::statement('PRAGMA journal_mode=WAL');
   DB::statement('PRAGMA synchronous=NORMAL');
   ```

## Security Considerations

1. **Disable DevTools in Production**
   
   ```javascript
   // electron/main.js
   if (isDev) {
       mainWindow.webContents.openDevTools();
   }
   ```

2. **Use Content Security Policy**
   
   ```html
   <meta http-equiv="Content-Security-Policy" content="default-src 'self'">
   ```

3. **Implement HTTPS for API calls** (if needed)

4. **Protect sensitive data**
   - Store API keys in environment variables
   - Use encrypted storage for credentials

## Update Notifications

Users should be notified when updates are available. Consider:

1. In-app notification system
2. Email notifications
3. GitHub releases page
4. Company website announcements

## Version Management

Update version in:
1. `package.json` - "version" field
2. Track in Git tags
3. Update `SETUP_GUIDE.md`
4. Document changelog

```json
{
    "version": "1.0.1"
}
```

## Continuous Deployment (Optional)

### GitHub Actions Example

```yaml
name: Build and Release

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: windows-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
      - run: npm install
      - run: npm run electron-build
      - uses: softprops/action-gh-release@v1
        with:
          files: dist/**
```

## Post-Deployment Checklist

- [ ] Test installer on clean Windows machine
- [ ] Verify database initializes correctly
- [ ] Check all features work offline
- [ ] Test file uploads and downloads
- [ ] Verify printer integration
- [ ] Test report exports
- [ ] Check error logging
- [ ] Verify auto-backup functionality
- [ ] Test backup/restore feature
- [ ] Verify performance is acceptable

## Support Resources

- **Documentation**: `SETUP_GUIDE.md`
- **Electron Docs**: https://www.electronjs.org/docs
- **Electron Builder**: https://www.electron.build/
- **Laravel Docs**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs

## Additional Notes

- All data is stored locally in SQLite - no cloud dependency
- Application works completely offline
- No server-side account needed
- Database can be backed up and restored
- Multi-user support through role-based access

---

**Version**: 1.0.0  
**Last Updated**: May 2026  
**Maintained by**: Development Team
