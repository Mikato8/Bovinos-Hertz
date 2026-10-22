<?php
declare(strict_types=1);

final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => isset($_SERVER['HTTPS']),
            ]);
            session_start();
        }
    }

    public static function attempt(string $user, string $pass): bool
    {
        $expectedUser = $_ENV['ADMIN_USER'] ?? '';
        $expectedHash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';
        if ($user !== $expectedUser || $expectedHash === '') return false;
        if (!password_verify($pass, $expectedHash)) return false;
        $_SESSION['admin'] = $user;
        session_regenerate_id(true);
        return true;
    }

    public static function check(): bool
    {
        return ($_SESSION['admin'] ?? null) === ($_ENV['ADMIN_USER'] ?? null);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
