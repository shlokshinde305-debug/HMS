<?php

declare(strict_types=1);

/**
 * Common authentication and session helper functions.
 */

function normalizePathString(string $path): string
{
    return str_replace('\\', '/', $path);
}

function appBasePath(): string
{
    static $basePath = null;

    if (is_string($basePath)) {
        return $basePath;
    }

    $projectRoot = normalizePathString(dirname(__DIR__));
    $documentRoot = normalizePathString($_SERVER['DOCUMENT_ROOT'] ?? '');

    if ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
        $relativePath = trim(substr($projectRoot, strlen($documentRoot)), '/');
        $basePath = $relativePath === '' ? '' : '/' . $relativePath;

        return $basePath;
    }

    $scriptName = normalizePathString($_SERVER['SCRIPT_NAME'] ?? '');
    $projectFolder = basename($projectRoot);
    $needle = '/' . trim($projectFolder, '/');
    $position = $needle !== '/' ? strpos($scriptName, $needle) : false;

    if ($position !== false) {
        $basePath = substr($scriptName, 0, $position + strlen($needle));

        return $basePath;
    }

    $scriptDirectory = trim(normalizePathString(dirname($scriptName)), '/');

    if ($scriptDirectory === '' || $scriptDirectory === '.') {
        $basePath = '';

        return $basePath;
    }

    $segments = explode('/', $scriptDirectory);
    $knownChildDirectories = ['auth', 'admin', 'student', 'assets', 'includes', 'config', 'modules', 'middleware'];

    if (in_array((string) end($segments), $knownChildDirectories, true)) {
        array_pop($segments);
    }

    $basePath = empty($segments) ? '' : '/' . implode('/', $segments);

    return $basePath;
}

function appUrl(string $path = ''): string
{
    $basePath = appBasePath();
    $path = ltrim($path, '/');

    if ($path === '') {
        return $basePath === '' ? '/' : $basePath;
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_name('HMSSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => appBasePath() === '' ? '/' : appBasePath(),
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

function isAuthenticated(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

function requireAuthentication(): void
{
    startSecureSession();

    if (!isAuthenticated()) {
        header('Location: ' . appUrl('auth/login.php'));
        exit;
    }
}

function requireRole(string $role): void
{
    requireAuthentication();

    if (($_SESSION['user_role'] ?? '') !== $role) {
        header('Location: ' . appUrl('auth/logout.php?reason=unauthorized'));
        exit;
    }
}

function currentUser(): array
{
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ];
}

function supportedLoginRoles(): array
{
    return ['admin', 'student'];
}

function redirectByRole(string $role): void
{
    $routeMap = [
        'admin' => 'admin/dashboard.php',
        'student' => 'student/dashboard.php'
    ];

    $target = $routeMap[$role] ?? 'auth/login.php';
    header('Location: ' . appUrl($target));
    exit;
}

function sanitizeString(string $value): string
{
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}
