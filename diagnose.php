<?php
/**
 * HMS Login Diagnostic Tool
 * 
 * This script helps diagnose login and database connectivity issues.
 * Visit: http://localhost/HMS/diagnose.php
 */

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'hostel_management';
const DB_USER = 'root';
const DB_PASS = '';

$diagnostics = [];

// Test 1: PHP Version
$diagnostics[] = [
    'name' => 'PHP Version',
    'status' => 'info',
    'value' => phpversion(),
    'expected' => '7.4+',
    'critical' => false
];

// Test 2: Required Extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'session'];
foreach ($requiredExtensions as $ext) {
    $installed = extension_loaded($ext);
    $diagnostics[] = [
        'name' => "Extension: $ext",
        'status' => $installed ? 'success' : 'error',
        'value' => $installed ? 'Loaded' : 'Missing',
        'expected' => 'Loaded',
        'critical' => true
    ];
}

// Test 3: MySQL Connection
$mysqlStatus = 'error';
$mysqlMessage = 'Unable to connect';
$mysqlTables = [];

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT
    );
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    $mysqlStatus = 'success';
    $mysqlMessage = 'Connected successfully';
    
    // Test 4: Database Exists
    try {
        $pdo->exec('USE ' . '`' . DB_NAME . '`');
        $diagnostics[] = [
            'name' => 'Database Selection',
            'status' => 'success',
            'value' => DB_NAME,
            'expected' => DB_NAME,
            'critical' => true
        ];
        
        // Test 5: Tables Exist
        $tables = ['admins', 'students', 'rooms', 'allocations', 'fees'];
        foreach ($tables as $table) {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $result->rowCount() > 0;
            $diagnostics[] = [
                'name' => "Table: $table",
                'status' => $exists ? 'success' : 'error',
                'value' => $exists ? 'Exists' : 'Missing',
                'expected' => 'Exists',
                'critical' => true
            ];
            
            if ($exists) {
                $count = $pdo->query("SELECT COUNT(*) as cnt FROM $table")->fetch();
                $mysqlTables[] = "$table (" . $count['cnt'] . " rows)";
            }
        }
        
        // Test 6: Test Admin Account
        $adminCheck = $pdo->query("SELECT id, full_name, email FROM admins WHERE email = 'admin@hostel.local' LIMIT 1")->fetch();
        $diagnostics[] = [
            'name' => 'Test Admin Account',
            'status' => $adminCheck ? 'success' : 'warning',
            'value' => $adminCheck ? $adminCheck['full_name'] . ' (' . $adminCheck['email'] . ')' : 'Not found',
            'expected' => 'admin@hostel.local',
            'critical' => false
        ];
        
        // Test 7: Test Student Account
        $studentCheck = $pdo->query("SELECT id, full_name, email FROM students WHERE email = 'student@hostel.local' LIMIT 1")->fetch();
        $diagnostics[] = [
            'name' => 'Test Student Account',
            'status' => $studentCheck ? 'success' : 'warning',
            'value' => $studentCheck ? $studentCheck['full_name'] . ' (' . $studentCheck['email'] . ')' : 'Not found',
            'expected' => 'student@hostel.local',
            'critical' => false
        ];
        
    } catch (Exception $e) {
        $diagnostics[] = [
            'name' => 'Database Selection',
            'status' => 'error',
            'value' => $e->getMessage(),
            'expected' => DB_NAME,
            'critical' => true
        ];
    }
    
} catch (PDOException $e) {
    $diagnostics[] = [
        'name' => 'MySQL Connection',
        'status' => 'error',
        'value' => $e->getMessage(),
        'expected' => 'host=' . DB_HOST . ', user=' . DB_USER,
        'critical' => true
    ];
}

$diagnostics[] = [
    'name' => 'MySQL Connection',
    'status' => $mysqlStatus,
    'value' => $mysqlMessage,
    'expected' => 'Connected',
    'critical' => true
];

// Test 8: File Permissions
$diagnostics[] = [
    'name' => 'config/database.php',
    'status' => file_exists(__DIR__ . '/config/database.php') ? 'success' : 'error',
    'value' => file_exists(__DIR__ . '/config/database.php') ? 'Readable' : 'Not found',
    'expected' => 'Readable',
    'critical' => true
];

$diagnostics[] = [
    'name' => 'auth/login.php',
    'status' => file_exists(__DIR__ . '/auth/login.php') ? 'success' : 'error',
    'value' => file_exists(__DIR__ . '/auth/login.php') ? 'Readable' : 'Not found',
    'expected' => 'Readable',
    'critical' => true
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Login Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .diagnostic-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 800px;
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
            margin-bottom: 10px;
        }
        .diagnostic-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .diagnostic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .diagnostic-item.success {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .diagnostic-item.error {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .diagnostic-item.warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .diagnostic-item.info {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .diagnostic-name {
            font-weight: 600;
            flex: 1;
        }
        .diagnostic-value {
            font-size: 12px;
            margin: 0 15px;
            text-align: right;
            max-width: 300px;
            word-break: break-word;
        }
        .diagnostic-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            min-width: 70px;
            text-align: center;
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
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .action-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .action-btn.primary {
            background: #667eea;
            color: white;
        }
        .action-btn.primary:hover {
            background: #5568d3;
            color: white;
            text-decoration: none;
        }
        .action-btn.secondary {
            background: #6c757d;
            color: white;
        }
        .action-btn.secondary:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        .critical-error {
            background: #f8d7da !important;
            border: 2px solid #dc3545;
            margin-top: 20px;
            padding: 20px;
            border-radius: 5px;
            color: #721c24;
        }
        .critical-error h5 {
            color: #dc3545;
            margin-top: 0;
        }
        .critical-error ol {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .critical-error li {
            margin-bottom: 10px;
        }
        .critical-error code {
            background: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            border: 1px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <div class="diagnostic-header">
            <h1>🔍 HMS Login Diagnostics</h1>
            <p>System Health Check & Login Troubleshooting</p>
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

        <?php
        $hasError = array_some($diagnostics, fn($d) => $d['status'] === 'error' && $d['critical']);
        if ($hasError):
        ?>
            <div class="critical-error">
                <h5>❌ Critical Issues Found - Login Cannot Work</h5>
                <p>Your system has critical issues preventing login. Follow these steps to fix:</p>
                <ol>
                    <li><strong>Ensure MySQL is Running</strong>
                        <br><small>Open XAMPP Control Panel and click "Start" next to MySQL</small>
                    </li>
                    <li><strong>Run Database Setup</strong>
                        <br><small>Visit: <code>http://localhost/HMS/setup-database.php</code></small>
                    </li>
                    <li><strong>Check MySQL Credentials</strong>
                        <br><small>Edit <code>config/database.php</code> if MySQL user/password is different</small>
                    </li>
                    <li><strong>Refresh This Page</strong>
                        <br><small>Press F5 or click the Refresh button after fixing issues</small>
                    </li>
                </ol>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="<?php echo htmlspecialchars('setup-database.php'); ?>" class="action-btn primary">📦 Run Setup</a>
            <a href="<?php echo htmlspecialchars('index.php'); ?>" class="action-btn secondary">🔐 Go to Login</a>
            <button class="action-btn secondary" onclick="location.reload()">🔄 Refresh</button>
        </div>
    </div>

    <script>
        // Utility function for array_some (PHP equivalent in JS)
        if (!Array.prototype.some) {
            Array.prototype.some = function(predicate) {
                for (let i = 0; i < this.length; i++) {
                    if (predicate(this[i])) return true;
                }
                return false;
            };
        }
    </script>
</body>
</html>

<?php
// Helper function
function array_some($array, $callback) {
    foreach ($array as $item) {
        if ($callback($item)) {
            return true;
        }
    }
    return false;
}
