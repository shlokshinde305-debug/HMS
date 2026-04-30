<?php
/**
 * Login Debug Helper
 * 
 * Add ?debug=1 to login URL to see detailed debug information
 * Example: https://hms.xo.je/auth/login.php?debug=1
 */

declare(strict_types=1);

// Check if debug mode is enabled
$debugMode = !empty($_GET['debug']) && $_GET['debug'] === '1';

if (!$debugMode) {
    return;
}

// Only show debug info if explicitly requested
?>

<!-- Debug Info Panel -->
<style>
.debug-panel {
    background: #f5f5f5;
    border: 2px solid #999;
    border-radius: 5px;
    padding: 15px;
    margin-top: 20px;
    font-size: 12px;
    font-family: monospace;
    color: #333;
    max-height: 400px;
    overflow-y: auto;
}
.debug-header {
    font-weight: bold;
    color: #666;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #999;
}
.debug-item {
    margin-bottom: 5px;
    padding: 3px 0;
}
.debug-label {
    color: #0066cc;
    font-weight: bold;
}
.debug-value {
    color: #333;
    word-break: break-all;
}
</style>

<div class="debug-panel">
    <div class="debug-header">🔍 DEBUG INFO (Remove ?debug=1 in production)</div>
    
    <div class="debug-item">
        <span class="debug-label">Server:</span>
        <span class="debug-value"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Protocol:</span>
        <span class="debug-value"><?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] ?? 'Unknown'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Host:</span>
        <span class="debug-value"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Unknown'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">PHP Version:</span>
        <span class="debug-value"><?php echo phpversion(); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Session Status:</span>
        <span class="debug-value"><?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Session Name:</span>
        <span class="debug-value"><?php echo htmlspecialchars(session_name()); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">HTTPS:</span>
        <span class="debug-value"><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Yes' : 'No'; ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Document Root:</span>
        <span class="debug-value"><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Script Path:</span>
        <span class="debug-value"><?php echo htmlspecialchars(__FILE__); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Database Host:</span>
        <span class="debug-value"><?php echo htmlspecialchars(DB_HOST ?? 'Not defined'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Database Name:</span>
        <span class="debug-value"><?php echo htmlspecialchars(DB_NAME ?? 'Not defined'); ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">PDO Available:</span>
        <span class="debug-value"><?php echo extension_loaded('pdo') && extension_loaded('pdo_mysql') ? 'Yes' : 'No'; ?></span>
    </div>
    
    <div class="debug-item">
        <span class="debug-label">Session Cookie Params:</span>
        <span class="debug-value">
            Path: <?php echo htmlspecialchars(ini_get('session.cookie_path')); ?><br>
            Domain: <?php echo htmlspecialchars(ini_get('session.cookie_domain') ?: '(none)'); ?><br>
            Secure: <?php echo ini_get('session.cookie_secure') ? 'Yes' : 'No'; ?><br>
            HttpOnly: <?php echo ini_get('session.cookie_httponly') ? 'Yes' : 'No'; ?>
        </span>
    </div>
</div>

<?php
