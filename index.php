<?php
require_once __DIR__ . '/auth/auth_check.php';
require_once __DIR__ . '/includes/layout.php';

startSecureSession();

if (!empty($_SESSION['user_role'])) {
    redirectByRole($_SESSION['user_role']);
}
?>
<?php renderPageHeader('College Hostel Management System', 'home'); ?>

<section class="hero-shell p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-7">
            <p class="text-uppercase fw-semibold text-success mb-2">Connected Campus Living</p>
            <h1 class="display-title mb-3">Smarter Hostel Operations, One Unified Dashboard</h1>
            <p class="lead mb-4">
                Coordinate allocations, support requests, payments, and communication through an interface built for
                admins and students, with hostel operations managed through the admin side.
            </p>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a class="btn btn-success px-4" href="<?php echo htmlspecialchars(appUrl('auth/login.php')); ?>">Go to Login</a>
                <a class="btn btn-outline-dark px-4" href="<?php echo htmlspecialchars(appUrl('auth/register.php')); ?>">Create Student Account</a>
            </div>
            <div class="row g-2">
                <div class="col-sm-4">
                    <div class="stat-pair">
                        <strong>24/7</strong>
                        <small class="muted-text">Support visibility</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-pair">
                        <strong>2 Roles</strong>
                        <small class="muted-text">Admin and Student</small>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-pair">
                        <strong>Single Flow</strong>
                        <small class="muted-text">From request to resolution</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="soft-panel p-4 h-100">
                <h5 class="mb-3">Core Capabilities</h5>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="feature-chip">R</span>
                    <div>
                        <div class="fw-semibold">Room Management</div>
                        <small class="muted-text">Track occupancy, capacity, and availability across blocks.</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="feature-chip">F</span>
                    <div>
                        <div class="fw-semibold">Fee Monitoring</div>
                        <small class="muted-text">Follow payments and dues with clear status tracking.</small>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <span class="feature-chip">C</span>
                    <div>
                        <div class="fw-semibold">Complaint Workflow</div>
                        <small class="muted-text">Collect requests and close them with accountability.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h3 class="mb-2">Daily Rental Rooms Available</h3>
            <div class="d-flex align-items-start gap-3 mt-3">
                <span class="feature-chip text-bg-primary">D</span>
                <div>
                    <div class="fw-semibold fs-5">Daily Rooms</div>
                    <p class="muted-text mb-0">Temporary stay rooms available for ₹500 per day.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="<?php echo htmlspecialchars(appUrl('daily_booking.php')); ?>" class="btn btn-primary btn-lg px-4 shadow-sm">Book Now</a>
        </div>
    </div>
</section>

<?php renderPageFooter(); ?>
