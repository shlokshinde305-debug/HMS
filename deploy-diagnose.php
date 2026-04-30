<?php
/**
 * HMS Deployment Diagnostics
 * 
 * Run this on your deployed server to diagnose login issues
 * Visit: https://hms.xo.je/deploy-diagnose.php
 */

declare(strict_types=1);

$diagnostics = [];
$criticalIssues = [];

// 1. Check HTTPS/SSL
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$diagnostics[] = [
    'name' => 'HTTPS/SSL Status',
    'status' => $isHttps ? 'success' : 'warning',
    'value' => $isHttps ? 'Enabled' : 'HTTP (Not Secure)',
    'critical' => !$isHttps,
    'fix' => $isHttps ? '' : 'Enable SSL certificate on your domain'
];

// 2. Check Server Type
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$diagnostics[] = [
    'name' => 'Web Server',
    'status' => 'info',
    'value' => $serverSoftware,
    'critical' => false
];

// 3. Check PHP Version
$phpVersion = phpversion();
$diagnostics[] = [
    'name' => 'PHP Version',
    'status' => version_compare($phpVersion, '7.4', '>=') ? 'success' : 'error',
    'value' => $phpVersion,
    'critical' => version_compare($phpVersion, '7.4', '<')
];

// 4. Check Required Extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'session', 'curl'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $diagnostics[] = [
        'name' => "Extension: $ext",
        'status' => $loaded ? 'success' : 'error',
        'value' => $loaded ? 'Loaded' : 'Missing',
        'critical' => true
    ];
    if (!$loaded) {
        $criticalIssues[] = "Missing extension: $ext";
    }
}

// 5. Check File Permissions
$filesToCheck = [
    'config/database.php',
    'auth/login.php',
    'auth/auth_check.php',
    'includes/layout.php'
];

foreach ($filesToCheck as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $readable = $exists && is_readable($path);
    
    $diagnostics[] = [
        'name' => "File: $file",
        'status' => $readable ? 'success' : 'error',
        'value' => $readable ? 'Readable' : ($exists ? 'Not readable' : 'Not found'),
        'critical' => !$readable
    ];
    
    if (!$readable) {
        $criticalIssues[] = "Cannot read $file - check permissions";
    }
}

// 6. Check Session Configuration
$sessionSavePath = ini_get('session.save_path');
$sessionAutoStart = ini_get('session.auto_start');

$diagnostics[] = [
    'name' => 'Session Save Path',
    'status' => 'info',
    'value' => $sessionSavePath ?: 'Default',
    'critical' => false
];

$diagnostics[] = [
    'name' => 'Session Auto Start',
    'status' => $sessionAutoStart ? 'warning' : 'success',
    'value' => $sessionAutoStart ? 'Enabled' : 'Disabled',
    'critical' => false
];

// 7. Check Database Connection
$dbStatus = 'error';
$dbMessage = 'Not tested yet';
$tables = [];

require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDatabaseConnection();
    $dbStatus = 'success';
    $dbMessage = 'Connected to database';
    
    // Check tables
    $tableCheck = ['admins', 'students', 'rooms'];
    foreach ($tableCheck as $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count = $result->fetch()['cnt'] ?? 0;
            $tables[] = "$table ($count rows)";
        } catch (Exception $e) {
            $tables[] = "$table (ERROR)";
        }
    }
    
} catch (Throwable $e) {
    $dbStatus = 'error';
    $dbMessage = substr($e->getMessage(), 0, 100);
    $criticalIssues[] = "Database connection failed: " . $e->getMessage();
}

$diagnostics[] = [
    'name' => 'Database Connection',
    'status' => $dbStatus,
    'value' => $dbMessage,
    'critical' => $dbStatus !== 'success'
];

if (!empty($tables)) {
    $diagnostics[] = [
        'name' => 'Database Tables',
        'status' => 'success',
        'value' => implode(', ', $tables),
        'critical' => false
    ];
}

// 8. Check error_reporting
$errorReporting = ini_get('error_reporting');
$diagnostics[] = [
    'name' => 'Error Reporting',
    'status' => 'info',
    'value' => $errorReporting,
    'critical' => false
];

// 9. Check URL Rewriting (if used)
$requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
$diagnostics[] = [
    'name' => 'Current Request URI',
    'status' => 'info',
    'value' => $requestUri,
    'critical' => false
];

// 10. Document Root
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';
$diagnostics[] = [
    'name' => 'Document Root',
    'status' => 'info',
    'value' => $docRoot,
    'critical' => false
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Deployment Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .diagnostic-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
        }
        .diagnostic-header {
            text-align: center;
            margin-bottom: 35px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        .diagnostic-header h1 {
            color: #333;
            font-size: 28px;
            font-weight: bold;
        }
        .diagnostic-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
            align-items: center;
        }
        .diagnostic-item.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .diagnostic-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .diagnostic-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .diagnostic-item.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .diagnostic-name {
            font-weight: 600;
            flex: 1;
            font-size: 14px;
        }
        .diagnostic-value {
            font-size: 12px;
            color: #666;
            max-width: 400px;
            text-align: right;
            word-break: break-word;
        }
        .diagnostic-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            min-width: 60px;
            text-align: center;
            margin-left: 10px;
        }
        .diagnostic-item.success .diagnostic-badge {
            background: #28a745;
            color: white;
        }
        .diagnostic-item.error .diagnostic-badge {
            background: #dc3545;
            color: white;
        }
        .diagnostic-item.warning .diagnostic-badge {
            background: #ffc107;
            color: #333;
        }
        .diagnostic-item.info .diagnostic-badge {
            background: #17a2b8;
            color: white;
        }
        .issues-box {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
            color: #721c24;
        }
        .issues-box h5 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .fix-steps {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            color: #0c5460;
        }
        .fix-steps h5 {
            color: #17a2b8;
            margin-bottom: 15px;
        }
        .fix-steps ol {
            margin-bottom: 0;
        }
        .fix-steps li {
            margin-bottom: 10px;
        }
        .fix-steps code {
            background: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <div class="diagnostic-header">
            <h1>🔧 HMS Deployment Diagnostics</h1>
            <p>Server Configuration & Login Issue Analysis</p>
        </div>

        <div class="diagnostics-list">
            <?php foreach ($diagnostics as $diag): ?>
                <div class="diagnostic-item <?php echo $diag['status']; ?>">
                    <span class="diagnostic-name"><?php echo htmlspecialchars($diag['name']); ?></span>
                    <span class="diagnostic-value"><?php echo htmlspecialchars($diag['value']); ?></span>
                    <span class="diagnostic-badge"><?php echo ucfirst($diag['status']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($criticalIssues)): ?>
            <div class="issues-box">
                <h5>❌ Critical Issues Found</h5>
                <ul style="margin-bottom: 0;">
                    <?php foreach ($criticalIssues as $issue): ?>
                        <li><?php echo htmlspecialchars($issue); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="fix-steps">
                <h5>🔧 How to Fix</h5>
                <ol>
                    <li><strong>Check PHP Extensions:</strong> Contact your hosting provider to enable missing extensions</li>
                    <li><strong>Check File Permissions:</strong> Ensure all PHP files have correct read permissions (644)</li>
                    <li><strong>Verify Database Access:</strong> Test database connection in phpMyAdmin or equivalent</li>
                    <li><strong>Check error logs:</strong> Look for errors in your hosting provider's error logs</li>
                    <li><strong>Restart PHP:</strong> Ask your hosting provider to restart PHP service</li>
                </ol>
            </div>
        <?php else: ?>
            <div style="background: #d4edda; border: 2px solid #28a745; border-radius: 5px; padding: 20px; margin-top: 30px; color: #155724;">
                <h5 style="color: #28a745; margin-top: 0;">✅ System Looks Good</h5>
                <p>All critical components are functioning. If you're still having login issues:</p>
                <ol style="margin-bottom: 0;">
                    <li>Try clearing your browser cache (Ctrl+Shift+Delete)</li>
                    <li>Try using private/incognito mode</li>
                    <li>Verify you're using correct test credentials</li>
                    <li>Check browser console for error messages (F12)</li>
                </ol>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="<?php echo htmlspecialchars('index.php'); ?>" class="btn btn-primary">🔐 Go to Login</a>
            <button class="btn btn-primary" onclick="location.reload()">🔄 Refresh</button>
        </div>
    </div>
</body>
</html>
