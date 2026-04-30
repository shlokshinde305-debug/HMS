# 🌐 HMS Deployment Troubleshooting Guide

## ⚠️ Your Situation
Deployed to: **https://hms.xo.je**  
Problem: **Login fails on phone/laptop**

---

## 🔍 Step 1: Run Deployment Diagnostics

Visit: **https://hms.xo.je/deploy-diagnose.php**

This will show:
- ✅ Server configuration
- ✅ PHP version & extensions
- ✅ Database connection status
- ✅ Exact problems & how to fix

**Screenshot this page if issues appear - send to your hosting provider**

---

## 🔧 Common Deployment Issues & Fixes

### Issue 1: Database Connection Fails

**Symptom:** "Unable to login right now"

**Causes:**
- Database credentials are wrong
- Database not created on server
- Database user permissions missing

**Fix:**
1. Check `config/database.php` has correct credentials for your server:
   ```php
   const DB_HOST = 'your-db-host';
   const DB_PORT = '3306';
   const DB_NAME = 'hostel_management';
   const DB_USER = 'your-db-user';
   const DB_PASS = 'your-db-password';
   ```

2. Verify database exists on your server (use phpMyAdmin)

3. If missing, upload `sql/schema.sql` to your server and execute it

---

### Issue 2: Missing PHP Extensions

**Symptom:** Blank page or "500 Internal Server Error"

**Required Extensions:**
- ✅ PDO
- ✅ PDO MySQL
- ✅ Session
- ✅ cURL

**Fix:**
1. Contact your hosting provider
2. Ask them to enable these extensions
3. Wait 5-10 minutes for changes to take effect
4. Refresh: https://hms.xo.je/deploy-diagnose.php

---

### Issue 3: Sessions Not Working (Login doesn't stick)

**Symptom:** Can login, but session dies on next page

**Causes:**
- Session path not writable
- Session config issues
- Cookie problems on HTTPS

**Fix:**
Update `auth/auth_check.php` session configuration:

```php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'hms.xo.je',  // ← Add your domain here
    'secure' => true,          // ← HTTPS enabled
    'httponly' => true,
    'samesite' => 'Lax'
]);
```

---

### Issue 4: CORS/SSL Certificate Issues

**Symptom:** Browser shows security warnings or login page loads but nothing works

**Fix:**
1. Verify SSL certificate is valid (browser shows 🔒 lock icon)
2. Check certificate doesn't have errors:
   - Visit: https://www.sslshopper.com/ssl-checker.html
   - Enter: hms.xo.je
3. If errors, contact your hosting provider to fix SSL

---

### Issue 5: File Permissions

**Symptom:** "500 Internal Server Error" or blank pages

**Fix - Set correct permissions:**
```bash
# On your server via SSH:
chmod 755 /home/username/public_html/HMS
chmod 644 /home/username/public_html/HMS/*.php
chmod 755 /home/username/public_html/HMS/auth
chmod 644 /home/username/public_html/HMS/auth/*.php
chmod 755 /home/username/public_html/HMS/config
chmod 600 /home/username/public_html/HMS/config/database.php
```

Or use your hosting provider's file manager to set permissions

---

### Issue 6: Wrong URL Paths

**Symptom:** CSS/JS don't load, redirects broken

**Cause:** Application doesn't know the base URL

**Fix:**
The app auto-detects the URL, but if issues:

1. Edit `auth/auth_check.php`
2. Find `appBasePath()` function
3. Add debugging:

```php
function appBasePath(): string
{
    // Force it if auto-detection fails
    return '/HMS';  // Your app folder
}
```

---

## 📋 Complete Deployment Checklist

- [ ] All files uploaded to server
- [ ] Database created on server
- [ ] Database credentials updated in `config/database.php`
- [ ] Database tables created (schema.sql imported)
- [ ] Test accounts created (run setup script or import data)
- [ ] File permissions set (644 for files, 755 for folders)
- [ ] SSL certificate valid and HTTPS enabled
- [ ] PHP extensions enabled: PDO, PDO_MySQL, Session, cURL
- [ ] `deploy-diagnose.php` shows all green ✅
- [ ] Can access: https://hms.xo.je/deploy-diagnose.php
- [ ] Can access: https://hms.xo.je/index.php
- [ ] Can login with test credentials
- [ ] Session persists after login

---

## 🚀 Emergency Fix: Redeploy Setup

If completely stuck:

1. **Upload `setup-database.php`** to your server
2. **Visit:** https://hms.xo.je/setup-database.php
3. **Click Run** to recreate everything
4. **Login** with credentials shown

---

## 🔐 Test Credentials (After Setup)

```
Admin:
  Email: admin@hostel.local
  Password: admin123

Student:
  Email: student@hostel.local
  Password: student123
```

---

## 🛠️ Debugging Login Issues

### Check Browser Console (Press F12)
1. Go to https://hms.xo.je/index.php
2. Press **F12** to open Developer Tools
3. Go to **Console** tab
4. Look for red error messages
5. Screenshot and share with support

### Check Network Tab
1. Open Developer Tools (F12)
2. Go to **Network** tab
3. Try to login
4. Look for failed requests (red)
5. Click each one to see error details

### Test on Different Devices
- Mobile phone
- Different browser
- Different WiFi/network
- Check if works on one device but not another

---

## 📞 If Still Having Issues

1. **Run:** https://hms.xo.je/deploy-diagnose.php
2. **Take screenshot** of any red ❌ issues
3. **Check error logs** in your hosting control panel
4. **Contact support** with:
   - Diagnostic screenshot
   - Error log details
   - Which device/browser is affected
   - Exact error message shown

---

## ✅ Success Indicators

When deployment is working:
- ✅ https://hms.xo.je loads the login page
- ✅ https://hms.xo.je/deploy-diagnose.php shows all green
- ✅ Can login and see admin/student dashboard
- ✅ Session persists (stays logged in)
- ✅ Can navigate between pages
- ✅ No browser console errors

---

**Created:** April 2026  
**For:** HMS Deployment on Production Servers  
**Domain:** hms.xo.je
