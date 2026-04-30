# Repository Setup Documentation

## Project Overview
This is a Hotel Management System (HMS) built with PHP. The project includes features for admin dashboards, student/guest management, booking systems, and payment integration using Razorpay.

## Repository Configuration

### Remote Repository
- **Remote Name:** `origin`
- **URL:** https://github.com/shlokshinde305-debug/HMS.git
- **Push URL:** https://github.com/shlokshinde305-debug/HMS.git

To verify remote configuration, run:
```bash
git remote -v
```

## Project Structure

```
HMS/
├── admin/                  # Admin dashboard and management
│   ├── add_fee.php
│   ├── dashboard.php
│   └── live_map.php
├── auth/                   # Authentication system
│   ├── auth_check.php
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── config/                 # Configuration files
│   ├── database.php        # Database connection
│   └── razorpay.php        # Razorpay payment gateway config
├── includes/               # Reusable components
│   ├── fake_razorpay.php  # Payment fallback
│   └── layout.php         # Common layout template
├── modules/                # Feature modules
│   ├── create_razorpay_order.php
│   ├── get_locations.php
│   ├── location.php
│   └── verify_payment.php
├── student/                # Student/Guest area
│   ├── dashboard.php
│   ├── pay_fee.php
│   └── pay_rent.php
├── assets/                 # Static files
│   └── css/
│       └── site.css
├── sql/                    # Database scripts
│   ├── schema.sql
│   └── advanced_features.sql
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
├── .gitignore              # Git ignore rules
├── index.php               # Entry point
├── login.php               # Login page
└── daily_booking.php       # Booking management
```

## Key Features

### Payment Integration
- **Razorpay Integration:** For live payment processing
- **Fallback System:** Fake Razorpay for testing

### User Roles
- **Admin:** Dashboard for managing system
- **Student/Guest:** Dashboard for viewing bookings and payments

### Database
- Schema defined in `sql/schema.sql`
- Advanced features in `sql/advanced_features.sql`

## Git Workflow

### First-time setup (already configured):
```bash
git init                    # Initialize repository
git remote add origin https://github.com/shlokshinde305-debug/HMS.git
```

### Committing changes:
```bash
git add .
git commit -m "Your commit message"
git push origin main       # Push to GitHub (main branch)
```

### Pulling updates:
```bash
git pull origin main
```

## Dependencies

### Composer Packages
- **Razorpay SDK:** For payment processing (`razorpay/razorpay`)
- **Requests Library:** HTTP requests handling (`rmccue/requests`)

Install dependencies with:
```bash
composer install
```

## Next Steps

1. **Update GitHub Repository:** Once you push code, GitHub will populate with your commits
2. **Configure Database:** Update `config/database.php` with your database credentials
3. **Set Environment Variables:** Create `.env` file for sensitive configuration (will be ignored by git)
4. **Make your first commit:** All existing files are ready to be committed

## Usage

```bash
# Stage all changes
git add .

# Create initial commit
git commit -m "Initial HMS project setup with Git repository"

# Push to GitHub (creates main/master branch)
git push -u origin main
```

---
**Created:** April 2026
**Purpose:** Track HMS project development with version control
