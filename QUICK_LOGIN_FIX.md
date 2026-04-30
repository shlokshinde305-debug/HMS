# 🔐 HMS Login Troubleshooting Guide

## Quick Fix Steps

### Step 1: Check System Health
Visit: **http://localhost/HMS/diagnose.php**

This will show you:
- ✅ PHP Version & Extensions
- ✅ MySQL Connection Status
- ✅ Database Status
- ✅ Test Accounts Status
- ✅ All Issues & How to Fix Them

---

## Most Common Login Issues

### ❌ "Unable to login right now. Please try again"

**Cause:** Database not connected or not set up

**Fix:**
1. Ensure MySQL is running in XAMPP
2. Visit: `http://localhost/HMS/setup-database.php`
3. Wait for setup to complete
4. Try logging in again

---

### ❌ "Invalid credentials or inactive account"

**Cause:** Test accounts don't exist or are inactive

**Fix:**
1. Visit: `http://localhost/HMS/setup-database.php` 
2. It will create test accounts:
   - Email: `admin@hostel.local` / Password: `admin123`
   - Email: `student@hostel.local` / Password: `student123`

---

### ❌ MySQL Connection Errors

**Cause:** MySQL not running or wrong credentials

**Fix:**
1. Open XAMPP Control Panel
2. Check that **MySQL** is running (green status)
3. If not running, click **Start**
4. Visit: `http://localhost/HMS/diagnose.php` to verify

---

## Complete Setup Workflow

### For First Time Setup:

```
1. Start XAMPP (Apache + MySQL)
   ↓
2. Visit http://localhost/HMS/diagnose.php
   ↓
3. If there are errors, follow the recommendations
   ↓
4. Visit http://localhost/HMS/setup-database.php
   ↓
5. Wait for "Setup Complete!" message
   ↓
6. Visit http://localhost/HMS/index.php
   ↓
7. Login with test credentials:
   - Admin: admin@hostel.local / admin123
   - Student: student@hostel.local / student123
```

---

## Test Credentials

### Admin Login
```
Email:    admin@hostel.local
Password: admin123
Role:     Admin
```

### Student Login
```
Email:    student@hostel.local
Password: student123
Role:     Student
```

---

## Database Configuration

If your MySQL setup is different from the defaults, edit:
```
c:\xampp\htdocs\HMS\config\database.php
```

Default settings:
```php
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'hostel_management';
const DB_USER = 'root';
const DB_PASS = '';  // Empty password (default XAMPP)
```

---

## Manual Troubleshooting

### Check MySQL in phpMyAdmin:
1. Visit: `http://localhost/phpmyadmin`
2. Check if database `hostel_management` exists
3. Check if tables exist: `admins`, `students`, `rooms`
4. Verify test accounts exist:
   - In `admins` table: email = `admin@hostel.local`
   - In `students` table: email = `student@hostel.local`

### Re-run Setup:
If database is corrupted or incomplete:
1. Delete database `hostel_management` in phpMyAdmin
2. Visit: `http://localhost/HMS/setup-database.php`
3. It will recreate everything fresh

---

## Advanced: View Error Logs

Check PHP error logs:
```
c:\xampp\apache\logs\error.log
c:\xampp\mysql\data\*.err
```

---

## Still Having Issues?

1. **Run Diagnostics**: `http://localhost/HMS/diagnose.php`
2. **Follow All Recommendations** from the diagnostic report
3. **Take Screenshot** of diagnostic results
4. **Check XAMPP Control Panel** - ensure Apache & MySQL are green (running)
5. **Restart XAMPP** if services aren't responding

---

**Created:** April 2026
**Purpose:** Quick reference for resolving HMS login issues
