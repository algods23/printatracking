# Printa Signages & Stickers Management System
## Complete Documentation & Quick Start Guide

---

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Quick Start](#quick-start)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Features Guide](#features-guide)
6. [Development](#development)
7. [Building for Production](#building-for-production)
8. [Troubleshooting](#troubleshooting)
9. [Support & Resources](#support--resources)

---

## System Overview

**Printa Signages & Stickers Management System** is a professional desktop application designed for businesses that produce signages, stickers, banners, and related products.

### Key Characteristics

- ✅ **Completely Offline** - No internet connection required
- ✅ **Standalone Desktop App** - Double-click to launch
- ✅ **Windows Compatible** - Works on Windows 10 & 11
- ✅ **Role-Based Access** - Admin and Staff accounts
- ✅ **Professional UI** - Modern, dark-mode supported interface
- ✅ **Complete Business Suite** - Tasks, Receipts, Expenses, Reports
- ✅ **Data Security** - Local SQLite database, no cloud sync
- ✅ **Thermal Printer Support** - ESC/POS compatibility

### System Requirements

| Requirement | Minimum | Recommended |
|------------|---------|------------|
| **OS** | Windows 10 | Windows 10/11 |
| **RAM** | 2 GB | 4 GB |
| **Storage** | 500 MB | 1 GB |
| **Display** | 1024x768 | 1366x768+ |
| **Processor** | Intel/AMD | Modern CPU |

---

## Quick Start

### For Development (First Time)

```bash
# 1. Navigate to project
cd c:\laragon\www\printatracking

# 2. Run setup script (Windows)
setup.bat

# 3. Start development server
npm run electron-dev
```

**That's it!** The app will open with auto-reload enabled.

### For Production (Building Installer)

```bash
# 1. Build assets
npm run build

# 2. Create installer
npm run electron-build-win

# Find installer in dist/ folder and distribute
```

---

## Installation

### Prerequisites Checklist

- [ ] **Node.js** v16+ (https://nodejs.org)
- [ ] **PHP** 8.1+ with Laravel
- [ ] **Composer** (https://getcomposer.org)
- [ ] **Git** (optional, for version control)

### Step 1: Clone/Download Project

```bash
# If using git
git clone <repository-url> printatracking
cd printatracking

# Or if downloaded as zip
cd c:\laragon\www\printatracking
```

### Step 2: Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### Step 3: Environment Setup

```bash
# Copy example environment file
copy .env.example .env

# Generate encryption key
php artisan key:generate
```

### Step 4: Database Setup

```bash
# Run migrations to create tables
php artisan migrate

# Seed demo data (optional but recommended)
php artisan db:seed
```

### Step 5: Start Application

```bash
# For development with hot reload
npm run electron-dev

# Or just run the app
npm run electron
```

---

## Configuration

### Environment Variables (.env)

Key settings to configure:

```env
# Application Settings
APP_NAME="Printa Signages"
APP_ENV=production          # Set to 'local' for development
APP_DEBUG=false             # Set to 'true' for debugging

# Database (SQLite for offline)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Application URL
APP_URL=http://localhost:8000
```

### Company Settings (In-App)

1. Open application
2. Login as admin (admin@printa.local / password)
3. Go to **Settings** → **Company Information**
4. Fill in:
   - Company Name
   - Email Address
   - Phone Number
   - Physical Address
   - Company Logo

### Printer Configuration

1. Go to **Settings** → **Printer Settings**
2. Select your thermal printer
3. Choose paper width (80mm or 58mm)
4. Click "Test Print" to verify

---

## Features Guide

### 1. Authentication & Dashboard

- **Login**: Secure authentication with role-based access
- **Dashboard**: Overview of tasks, sales, and expenses
- **Activity Log**: Track all user actions
- **Dark Mode**: Toggle for eye comfort

**Access**: `/login` (auto-redirects if not authenticated)

### 2. Task Management

**What are Tasks?**
Tasks represent jobs for customers (signage orders, sticker batches, etc.).

**Features**:
- Create, edit, delete tasks
- Assign to staff members
- Track task progress (Pending → Designing → Printing → Installing → Completed)
- Set priorities and due dates
- Upload reference images
- Track payment status

**Quick Actions**:
1. Click "Tasks" in sidebar
2. Click "New Task" to create
3. Fill in customer details
4. Set timeline and amount
5. Assign to staff member
6. Save

**Status Tracking**:
- **Pending**: New task, waiting to start
- **Designing**: Design phase in progress
- **Printing**: Being printed/manufactured
- **Installing**: Installation/setup phase
- **Completed**: Task finished
- **Cancelled**: Task cancelled

### 3. Receipt Management

**What are Receipts?**
Receipts are issued to customers when payments are made.

**Features**:
- Auto-generated receipt numbers
- Itemized product details
- Tax and discount support
- Multiple payment methods
- PDF export
- Thermal printer support
- Print receipts directly

**How to Create Receipt**:
1. Go to **Receipts** → **New Receipt**
2. Link to existing task or create new
3. Add line items (products, quantities, prices)
4. Set payment details
5. Click Print or Save

**Printer Support**:
- ESC/POS thermal printers
- 80mm and 58mm widths
- Automatic formatting
- Draft mode available

### 4. Expense Management

**What are Expenses?**
Recurring and one-time business costs (materials, rent, utilities, etc.).

**Features**:
- Multiple categories (Materials, Labor, Utilities, etc.)
- Receipt/invoice upload
- Date tracking
- Monthly summaries
- Expense reports

**Categories**:
- Materials
- Labor
- Utilities
- Rent
- Equipment
- Transportation
- Marketing
- Other

### 5. Reports & Analytics

**Available Reports**:

1. **Sales Report**
   - Total sales in period
   - Breakdown by payment method
   - Trend analysis
   - Export to PDF

2. **Expense Report**
   - Total expenses
   - Breakdown by category
   - Period comparison
   - Savings analysis

3. **Task Report**
   - Completion rates
   - Status breakdown
   - Timeline analysis
   - Staff productivity

4. **Productivity Report**
   - Staff performance
   - Tasks completed per person
   - Average completion time
   - Performance metrics

5. **Monthly Summary**
   - Revenue vs. expenses
   - Profit/loss calculation
   - Year-over-year comparison
   - Charts and graphs

**Export Options**:
- PDF format
- Excel format (coming soon)
- Print directly

### 6. Settings

#### Company Information
- Company name and branding
- Contact details
- Logo upload
- Address and location

#### Receipt Settings
- Custom footer message
- Logo on receipts
- Company information display

#### Printer Settings
- Printer selection
- Paper width configuration
- Test printing
- ESC/POS settings

#### User Management (Admin Only)
- Create user accounts
- Set roles (Admin/Staff)
- Activate/deactivate users
- Edit user information
- Delete user accounts

#### Database Management (Admin Only)
- Backup database
- Restore from backup
- Database optimization

---

## Development

### Setting Up Development Environment

```bash
# Install all dependencies
composer install
npm install

# Create and configure .env
copy .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Start development
npm run electron-dev
```

### Development Features

- **Hot Module Reload**: Changes reflect instantly
- **DevTools**: Press F12 to open developer console
- **Database**: SQLite file at `database/database.sqlite`
- **Logs**: Check `storage/logs/laravel.log` for errors

### Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/        (Blade templates)
│   ├── css/          (Tailwind CSS)
│   └── js/           (JavaScript)
├── routes/
│   └── web.php       (Route definitions)
├── electron/
│   ├── main.js       (Electron entry)
│   └── preload.js    (IPC bridge)
└── storage/
    ├── app/          (Uploaded files)
    ├── logs/         (Application logs)
    └── database.sqlite
```

### Adding New Features

**Example: Adding a new page**

1. **Create Controller**
   ```bash
   php artisan make:controller MyController
   ```

2. **Add Route** (routes/web.php)
   ```php
   Route::get('/mypage', [MyController::class, 'index']);
   ```

3. **Create View** (resources/views/mypage.blade.php)
   ```blade
   @extends('layouts.app')
   @section('content')
   <!-- Your content -->
   @endsection
   ```

4. **Test**
   ```bash
   npm run electron-dev
   ```

---

## Building for Production

### One-Command Build

```bash
npm run electron-build-win
```

This will:
1. Optimize all assets
2. Build Electron app
3. Create Windows installer
4. Generate portable executable

### Build Outputs

Located in `dist/` folder:

- `Printa Signages Setup 1.0.0.exe` - Full installer
- `Printa Signages 1.0.0.exe` - Portable version
- Recommended: Distribute the installer

### Distribution Methods

**Method 1: Download Link**
```
https://yourcompany.com/download/printa-setup.exe
```

**Method 2: USB Drive**
- Copy `dist/Printa Signages Setup 1.0.0.exe` to USB
- Users can run from any computer

**Method 3: Network Share**
- Place installer on company network share
- Users access via mapped drive

### System Requirements for Deployment

Users need:
- Windows 10 or 11 (64-bit)
- 500MB free disk space
- .NET Framework 4.5+ (included with Windows 10+)
- No programming knowledge

---

## Troubleshooting

### Application Won't Start

**Problem**: App crashes on launch

**Solution**:
1. Check if port 8000 is available:
   ```bash
   netstat -ano | findstr :8000
   ```
2. Kill process if needed:
   ```bash
   taskkill /PID <pid> /F
   ```
3. Restart application

### Database Errors

**Problem**: "Database is locked" error

**Solution**:
1. Close all instances of app
2. Delete `database/database.sqlite`
3. Run: `php artisan migrate --seed`
4. Restart app

### Printer Not Found

**Problem**: Printer not available in settings

**Solution**:
1. Ensure printer is installed in Windows
2. Test print from Windows Settings
3. Refresh printer list in app
4. Check ESC/POS compatibility

### Port Already in Use

**Problem**: "Port 8000 already in use"

**Solution**:
```bash
# Find and kill process using port
netstat -ano | findstr :8000
taskkill /PID <pid> /F

# Or use different port in app
php artisan serve --port=8001
```

### File Upload Issues

**Problem**: Cannot upload files

**Solution**:
1. Check storage folder permissions:
   ```bash
   icacls storage /grant Everyone:F /T
   ```
2. Ensure `public/storage` is linked:
   ```bash
   php artisan storage:link
   ```

### Database Backup Failing

**Problem**: Cannot backup database

**Solution**:
1. Ensure `storage` folder is writable
2. Check disk space availability
3. Try manual backup:
   ```bash
   copy database\database.sqlite backups\database.sqlite
   ```

---

## Support & Resources

### Documentation Files

- `SETUP_GUIDE.md` - Detailed setup instructions
- `BUILD_AND_DEPLOY.md` - Building and distribution guide
- `README.md` - Project overview
- `.env.example` - Environment variables reference

### External Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Electron Documentation**: https://www.electronjs.org/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Chart.js**: https://www.chartjs.org/docs
- **Lucide Icons**: https://lucide.dev

### Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@printa.local | password |
| Staff | staff@printa.local | password |
| Staff 2 | jane@printa.local | password |
| Staff 3 | mike@printa.local | password |

**⚠️ Important**: Change these passwords immediately after first login!

### Getting Help

1. **Check logs**: `storage/logs/laravel.log`
2. **Review error messages**: Check console (F12)
3. **Read documentation**: See guides above
4. **Contact support**: Email to development team

---

## Quick Reference Commands

```bash
# Development
npm run dev                    # Start Vite dev server
npm run electron-dev          # Start full development
npm run electron              # Run app

# Production
npm run build                 # Build assets
npm run electron-build-win    # Build Windows installer
npm run electron-build-mac    # Build Mac app
npm run electron-build-linux  # Build Linux app

# Database
php artisan migrate           # Run migrations
php artisan db:seed           # Seed demo data
php artisan migrate:rollback  # Rollback migrations

# Utilities
php artisan tinker           # Interactive shell
php artisan serve            # Start PHP server
php artisan cache:clear      # Clear caches
```

---

## Version Information

- **Product**: Printa Signages & Stickers Management System
- **Version**: 1.0.0
- **Release Date**: May 2026
- **Laravel Version**: 12.x
- **Node.js**: 16+
- **Electron**: Latest

---

## License & Terms

© 2026 Printa Signages. All rights reserved.

This software is provided as-is for authorized users only.

---

## Changelog

### Version 1.0.0
- Initial release
- Complete task management system
- Receipt printing with thermal printer support
- Expense tracking
- Comprehensive reporting
- User role management
- Dark mode support
- Offline-first architecture

---

**Thank you for using Printa Signages & Stickers Management System!**

For the best experience, ensure you:
1. ✅ Keep your database backed up regularly
2. ✅ Use strong passwords for user accounts
3. ✅ Review reports monthly for insights
4. ✅ Maintain your computer's antivirus software
5. ✅ Update Windows regularly

---

**Last Updated**: May 2026  
**Maintained by**: Development Team
