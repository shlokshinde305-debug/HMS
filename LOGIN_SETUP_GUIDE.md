# HMS Login Issue - Resolved ✅

## Problem
"Unable to login right now. Please try again."

## Root Cause
The database `hostel_management` hasn't been created or tables haven't been initialized yet.

## Solution

### Step 1: Start XAMPP Services
Make sure both **Apache** and **MySQL** are running in XAMPP Control Panel.

### Step 2: Run Database Setup
1. Open your browser and navigate to:
   ```
   http://localhost/HMS/setup-database.php
   ```

2. The setup script will:
   - ✅ Create the `hostel_management` database
   - ✅ Create all required tables
   - ✅ Create test admin account
   - ✅ Create test student account
   - ✅ Add sample rooms

### Step 3: Login to the Application
Once setup completes successfully, go to:
```
http://localhost/HMS/index.php
```

**Use these test credentials:**

#### Admin Login
- **Email:** `admin@hostel.local`
- **Password:** `admin123`
- **Role:** Admin

#### Student Login
- **Email:** `student@hostel.local`
- **Password:** `student123`
- **Role:** Student

---

## Database Configuration

The application uses these credentials (defined in `config/database.php`):
- **Host:** 127.0.0.1
- **Port:** 3306
- **Database:** hostel_management
- **User:** root
- **Password:** (empty/no password)

If your MySQL setup is different, update `config/database.php` accordingly.

---

## Troubleshooting

### Issue: "SQLSTATE[HY000]: General error"
**Solution:** Ensure MySQL is running in XAMPP

### Issue: "Unable to connect to the database"
**Solution:** 
1. Check MySQL credentials in `config/database.php`
2. Verify MySQL is accessible on localhost:3306

### Issue: "Access denied for user 'root'"
**Solution:** 
1. Update `config/database.php` with correct MySQL credentials
2. Or reset MySQL root password in XAMPP

### Issue: Setup script still shows errors after setup
**Solution:** 
1. Visit `http://localhost/phpmyadmin`
2. Check if database `hostel_management` exists
3. Manually execute SQL files if needed

---

## Manual Database Setup (Alternative)

If the automated setup doesn't work, you can manually set up using phpMyAdmin:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create new database: `hostel_management`
3. Select the database
4. Go to "Import" tab
5. Import `sql/schema.sql`
6. Then import `sql/advanced_features.sql`

---

## Features After Login

### Admin Dashboard
- View all students and rooms
- Manage allocations
- Track fees and payments
- Handle complaints
- Generate reports

### Student Dashboard
- View room allocation
- Check fee status
- Pay rent online (Razorpay integration)
- Submit and track complaints
- View payment history

---

**Created:** April 2026
**Purpose:** Quick setup and troubleshooting guide for HMS login issues
