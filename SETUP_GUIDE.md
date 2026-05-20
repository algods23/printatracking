# Printa Signages & Stickers Management System

A professional desktop application for managing signages and stickers production, built with Laravel, Electron, and Tailwind CSS.

## Features

### 1. **Authentication & Security**
- Secure login system with role-based access
- Admin and Staff account types
- Secure session management
- Activity logging for all operations

### 2. **Dashboard**
- Real-time statistics (total tasks, pending, ongoing, completed)
- Daily and monthly sales tracking
- Expense overview
- Task status distribution charts
- Monthly revenue analytics
- Recent activity feed

### 3. **Task Management**
- Complete task tracking system
- Support for multiple product types (Signage, Stickers, Banners, Labels)
- Task assignment to staff members
- Priority levels (Low, Medium, High, Urgent)
- Task status tracking (Pending, Designing, Printing, Installing, Completed, Cancelled)
- Image upload for task references
- Payment status tracking
- Advanced filtering and search

### 4. **Receipt Management**
- Generate professional receipts
- Thermal printer support (ESC/POS)
- PDF export
- Receipt number auto-generation
- Itemized details
- Payment method tracking
- Discount and tax support

### 5. **Expense Management**
- Track business expenses
- Multiple expense categories
- Receipt file upload
- Daily/monthly summaries
- Expense reports and analytics

### 6. **Reports**
- Sales reports
- Expense reports
- Task completion reports
- Employee productivity tracking
- Monthly summaries
- PDF export functionality
- Excel export ready

### 7. **Settings**
- Company information management
- Logo upload
- Receipt customization
- Printer configuration
- User management (Admin only)
- Database backup/restore
- Dark/Light theme toggle

## System Requirements

- **Windows 10/11** (Intel or AMD processor)
- **RAM**: Minimum 2GB, Recommended 4GB+
- **Disk Space**: Minimum 500MB
- **Display**: 1024x768 or higher resolution
- No additional software installation required

## Installation

### Development Setup

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Create Environment File**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Setup Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Start Development Server**
   ```bash
   npm run electron-dev
   ```

### Production Build

1. **Build Assets**
   ```bash
   npm run build
   ```

2. **Create Installer**
   ```bash
   npm run electron-build-win
   ```

   The installer will be located in `dist/` folder.

## Default Credentials

### Admin Account
- **Email**: admin@printa.local
- **Password**: password

### Staff Account
- **Email**: staff@printa.local
- **Password**: password

## Project Structure

```
printatracking/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── TaskController.php
│   │   │   ├── ReceiptController.php
│   │   │   ├── ExpenseController.php
│   │   │   ├── ReportController.php
│   │   │   └── SettingController.php
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Task.php
│       ├── Receipt.php
│       ├── ReceiptItem.php
│       ├── Expense.php
│       ├── ActivityLog.php
│       └── Setting.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   └── DatabaseSeeder.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── tasks/
│   │   ├── receipts/
│   │   ├── expenses/
│   │   ├── reports/
│   │   ├── settings/
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   └── components/
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── routes/
│   └── web.php
├── electron/
│   ├── main.js
│   └── preload.js
├── public/
│   ├── css/
│   └── js/
├── storage/
├── vite.config.js
├── tailwind.config.js
├── package.json
└── composer.json
```

## Database Schema

### Users Table
- id, name, email, password, role, is_active, timestamps

### Tasks Table
- id, task_id, customer_name, contact_number, product_type, signage_type, sticker_type, assigned_to, due_date, status, priority, notes, amount, payment_status, image_path, timestamps

### Receipts Table
- id, receipt_number, task_id, customer_name, customer_phone, customer_email, subtotal, discount, tax, total, cash_received, change, payment_method, notes, issued_by, timestamps

### Receipt Items Table
- id, receipt_id, product_name, description, quantity, unit_price, total, timestamps

### Expenses Table
- id, expense_name, category, amount, date, description, receipt_path, recorded_by, timestamps

### Activity Logs Table
- id, user_id, action, model_type, model_id, changes, ip_address, description, timestamps

### Settings Table
- id, key, value, timestamps

## Configuration

### Printer Setup

1. Go to Settings
2. Click "Printer Settings"
3. Select your thermal printer
4. Choose paper width (80mm or 58mm)
5. Test print

### Company Settings

1. Go to Settings
2. Fill in company information
3. Upload company logo
4. Configure receipt footer message

## Troubleshooting

### Application Won't Start
- Check if port 8000 is available
- Ensure Laravel can execute PHP
- Check storage folder permissions

### Printer Issues
- Ensure printer is online
- Check printer drivers are installed
- Verify ESC/POS compatibility

### Database Issues
- Ensure SQLite database file has write permissions
- Check storage/app folder permissions
- Verify database connection in .env

## Support

For issues or feature requests, please contact the development team.

## License

Proprietary - All rights reserved

## Version

1.0.0

## Release Date

May 2026

---

**Developed with ❤️ for efficient business management**
