<?php

declare(strict_types=1);

if (!function_exists('renderPageHeader')) {
    function renderPageHeader(string $title, string $activePage = 'home'): void
    {
        $baseUrl = appUrl();
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?></title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="<?php echo htmlspecialchars(appUrl('assets/css/site.css')); ?>" rel="stylesheet">
        </head>
        <body class="site-shell">
        <div class="ambient-bg" aria-hidden="true"></div>

        <header class="site-header sticky-top">
            <nav class="navbar navbar-expand-lg">
                <div class="container py-2">
                    <a class="navbar-brand brand-mark" href="<?php echo htmlspecialchars($baseUrl); ?>">
                        <span class="brand-dot"></span>
                        Hostel Nexus
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hmsMainNav" aria-controls="hmsMainNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="hmsMainNav">
                        <ul class="navbar-nav me-auto ms-lg-4 gap-lg-2">
                            <li class="nav-item"><a class="nav-link <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($baseUrl); ?>">Home</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo $activePage === 'register' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(appUrl('auth/register.php')); ?>">Student Register</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo $activePage === 'login' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(appUrl('auth/login.php')); ?>">Login</a></li>
                        </ul>
                        <a class="btn btn-header" href="<?php echo htmlspecialchars(appUrl('auth/login.php')); ?>">Access Portal</a>
                    </div>
                </div>
            </nav>
        </header>

        <main class="site-main container py-5">
        <?php
    }
}

if (!function_exists('renderPageFooter')) {
    function renderPageFooter(): void
    {
        ?>
        </main>

        <footer class="site-footer mt-4">
            <div class="container py-4 py-lg-5">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-6">
                        <p class="footer-title mb-1">College Hostel Management System</p>
                        <p class="mb-0 footer-text">Manage students, rooms, fees, and service requests from one place.</p>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                            <a class="btn btn-footer" href="<?php echo htmlspecialchars(appUrl('auth/login.php')); ?>">Login</a>
                            <a class="btn btn-footer" href="<?php echo htmlspecialchars(appUrl('auth/register.php')); ?>">Register</a>
                            <a class="btn btn-footer" href="<?php echo htmlspecialchars(appUrl()); ?>">Home</a>
                        </div>
                    </div>
                </div>
                <hr class="footer-separator">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                    <small class="footer-text">&copy; <?php echo date('Y'); ?> HMS Portal.</small>
                    <small class="footer-text">Built for admins and students.</small>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}
