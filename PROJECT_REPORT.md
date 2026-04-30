# College Hostel Management System (HMS) - Project Report

## 1. Project Overview
The College Hostel Management System (HMS) is a role-based PHP + MySQL web application designed around two login user groups:
- Admin
- Student

The currently implemented user-facing features include:
- Public landing page
- Two-role login (admin and student)
- Student self-registration
- Session-based authentication utilities
- Shared visual layout (global header/footer + styling)

## 2. Objectives
The system is intended to centralize hostel management processes such as:
- Student onboarding and account management
- Room allocation and occupancy tracking
- Hostel fee management
- Complaint lifecycle tracking
- Admin and student access control

## 3. Technology Stack
- Backend: PHP (procedural style with strict types in auth modules)
- Database: MySQL 8+ (InnoDB, normalized relational schema)
- Frontend: Bootstrap 5.3 + custom CSS theme
- Environment: XAMPP (Apache + MySQL)

## 4. Current Project Structure (Key Files)
- `index.php`: Public entry point and landing page
- `auth/auth_check.php`: Authentication/session helper functions
- `auth/login.php`: Role-based login workflow
- `auth/register.php`: Student registration workflow
- `auth/logout.php`: Session termination/logout handler
- `config/database.php`: Shared PDO connection bootstrap
- `includes/layout.php`: Reusable page header/footer rendering
- `assets/css/site.css`: Global theme styles
- `sql/schema.sql`: Full database schema and optional seed admin

## 5. Functional Flow
### 5.1 Landing
- User visits `index.php`.
- If already authenticated, user is redirected by role.
- Otherwise, user sees landing page with navigation to login/register.

### 5.2 Registration (Student)
- `auth/register.php` collects student profile + password.
- Validation checks include email, password length/match, year, and required fields.
- Duplicate checks are performed for registration number and email.
- Passwords are securely stored via `password_hash(..., PASSWORD_BCRYPT)`.

### 5.3 Login (Role-based)
- `auth/login.php` requires role selection (admin/student).
- Role maps to the relevant table and active-status condition.
- Credentials are verified using `password_verify`.
- Session values (`user_id`, `user_role`, `user_name`, `user_email`) are created.
- User is redirected to role dashboard route.

### 5.4 Logout
- `auth/logout.php` clears session state, invalidates cookie, destroys session, and redirects to login with confirmation flag.

## 6. Database Design Summary
The schema in `sql/schema.sql` models key hostel entities:
- `admins`: Admin user accounts
- `students`: Student accounts + academic/personal profile
- `rooms`: Room inventory, capacity, occupancy, fee, status
- `allocations`: Student-room assignment lifecycle
- `fees`: Due/paid status and transaction information
- `complaints`: Student grievance tracking and resolution metadata

Design strengths:
- Foreign keys with explicit update/delete behavior
- Useful operational indexes (status, dates, owner IDs)
- Enumerated statuses for business-state consistency
- Audit timestamps (`created_at`, `updated_at`) throughout

## 7. UI/UX Status
The project now has a shared UI shell:
- Consistent global header and footer via `includes/layout.php`
- Modern visual theme (custom typography, gradients, card styles) via `assets/css/site.css`
- Reused layout across landing, login, and registration pages

This improves consistency and maintainability by removing repeated page chrome from individual screens.

## 8. Security and Engineering Notes
Implemented good practices:
- Prepared statements for DB operations
- Password hashing and verification APIs
- Session regeneration and secure cookie flags (`HttpOnly`, `SameSite=Lax`)
- Basic server-side input sanitation/validation

Items to improve next:
- Add CSRF protection tokens for all POST forms
- Add brute-force/rate limiting on login endpoint
- Move DB credentials to environment variables
- Add central error handling + audit logging strategy
- Consider stronger password policy and email verification

## 9. Risks / Gaps Identified
1. Role dashboards are referenced by redirect flow.
- Redirect map points to:
  - `admin/dashboard.php`
  - `student/dashboard.php`
- These should stay aligned with the two-login architecture.

2. No automated tests present in the current visible scope.
- Core auth/register/login flows are functional but currently untested by unit/integration suites.

Resolved item:
- Base URL resolution has been updated to derive the application path dynamically from the active server environment, so moving the project from `/hostel-management` to `/HMS` no longer breaks generated links and redirects.

## 10. Recommended Next Milestones
1. Stabilize routing/base URL
- Make `appBasePath()` dynamic (derive from script path or config value).

2. Build role dashboards
- Create role-specific home pages with authorization guards.

3. Implement operational modules
- Room CRUD + occupancy updates
- Allocation workflows (approve/reject/vacate)
- Fee posting + payment reconciliation
- Complaint raise/assign/resolve pipeline

4. Strengthen security
- CSRF middleware
- Login rate limiting
- Security-focused validation and logging

5. Add quality controls
- Basic test suite (auth + registration + access guard)
- Lint/check pipeline
- Deployment checklist for XAMPP/production

## 11. Conclusion
The project has a solid foundation for a hostel ERP-style system: clear schema, role-based auth flow, and a now-consistent UI layer. The next phase should focus on completing operational modules, hardening security, and improving deployability/testing.
