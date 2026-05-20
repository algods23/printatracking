# 🎨 Printa Signages & Stickers Management System

A professional, fully-featured desktop application for managing signages and stickers production operations. Built with **Laravel 12**, **Electron.js**, **Tailwind CSS**, and **SQLite** for complete offline functionality.

![Printa Signages](https://img.shields.io/badge/Printa%20Signages-v1.0.0-yellow)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![Electron](https://img.shields.io/badge/Electron-Latest-blue)
![License](https://img.shields.io/badge/License-Proprietary-black)

---

## ✨ Key Features

### 🏢 Business Management
- **Task Tracking** - Full lifecycle management of production jobs
- **Receipt Printing** - Professional receipts with thermal printer support
- **Expense Tracking** - Monitor and categorize business expenses
- **Financial Reports** - Sales, expenses, and profitability analysis
- **User Management** - Role-based access control (Admin/Staff)

### 💻 Desktop Application
- **Completely Offline** - No internet required
- **Standalone Executable** - Double-click to launch
- **Windows Optimized** - Native Windows 10/11 support
- **Auto-Start Backend** - Laravel runs internally
- **Professional UI** - Modern interface with dark mode

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| **[COMPLETE_DOCUMENTATION.md](COMPLETE_DOCUMENTATION.md)** | Full user and developer guide |
| **[SETUP_GUIDE.md](SETUP_GUIDE.md)** | Installation and configuration |
| **[BUILD_AND_DEPLOY.md](BUILD_AND_DEPLOY.md)** | Building and distribution guide |

---

## 🚀 Quick Start### For Development

```bash
# 1. Navigate to project
cd c:\laragon\www\printatracking

# 2. Run automatic setup
setup.bat

# 3. Start development
npm run electron-dev
```

### For Production

```bash
# 1. Build everything
npm run build

# 2. Create installer
npm run electron-build-win

# 3. Find installer in dist/ folder
# Distribute: dist/Printa Signages Setup 1.0.0.exe
```

---

## 🔐 Default Login Credentials

| User | Email | Password |
|------|-------|----------|
| Admin | admin@printa.local | password |
| Staff | staff@printa.local | password |

⚠️ **Change immediately after first login!**

---

## 📦 Core Modules

### ✅ Dashboard
- Real-time statistics and analytics
- Task status breakdown
- Monthly revenue chart
- Recent activity feed
- Quick action buttons

### ✅ Task Management
- Create, edit, delete tasks
- Assign to staff members
- Priority and status tracking
- Image uploads
- Payment status monitoring

### ✅ Receipt Management  
- Professional receipt generation
- Thermal printer support
- Itemized product details
- PDF export
- Auto-generated receipt numbers

### ✅ Expense Tracking
- Record business expenses
- Category classification
- Receipt uploads
- Monthly summaries
- Expense reporting

### ✅ Reports & Analytics
- Sales reports
- Expense analysis
- Task completion tracking
- Productivity metrics
- PDF export support

### ✅ Settings & Administration
- Company information
- Receipt customization
- Printer configuration
- User management
- Database backup/restore

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12.x with SQLite
- **Frontend**: Blade templates + Tailwind CSS
- **Desktop**: Electron.js
- **Charts**: Chart.js
- **Icons**: Lucide Icons
- **Build**: Vite + Electron Builder

---

## 📋 System Requirements

| Component | Requirement |
|-----------|-------------|
| OS | Windows 10/11 (64-bit) |
| RAM | 2GB minimum, 4GB+ recommended |
| Storage | 500MB minimum |
| Display | 1024x768 or higher |

---

## 🚀 Development Commands

```bash
npm run dev              # Start Vite dev server
npm run electron-dev    # Full development mode
npm run build           # Build production assets
npm run electron-build-win  # Create Windows installer
npm run electron        # Run the app
```

---

## 📁 Project Structure

```
printatracking/
├── app/Http/Controllers/     # Application controllers
├── app/Models/               # Database models
├── database/migrations/      # Database schema
├── resources/views/          # Blade templates
├── routes/web.php            # Route definitions
├── electron/                 # Electron configuration
├── storage/                  # Database and logs
└── public/                   # Compiled assets
```

---

## 🎯 Features Checklist

- [x] Complete authentication system
- [x] Task management module
- [x] Receipt printing with thermal support
- [x] Expense tracking
- [x] Financial reports
- [x] User role management
- [x] Dark mode support
- [x] Database backup/restore
- [x] Activity logging
- [x] Offline-first architecture

---

## 📞 Support & Documentation

- **Full Guide**: [COMPLETE_DOCUMENTATION.md](COMPLETE_DOCUMENTATION.md)
- **Setup Instructions**: [SETUP_GUIDE.md](SETUP_GUIDE.md)
- **Build Guide**: [BUILD_AND_DEPLOY.md](BUILD_AND_DEPLOY.md)

---

## 📄 License

**Proprietary Software** - © 2026 Printa Signages. All rights reserved.

---

## 🎉 Get Started Now!

1. Run `setup.bat` for automatic setup
2. Run `npm run electron-dev` to start development
3. Read [COMPLETE_DOCUMENTATION.md](COMPLETE_DOCUMENTATION.md) for full guide

**Version**: 1.0.0 | **Release**: May 2026

Thank you for using Printa Signages Management System!
