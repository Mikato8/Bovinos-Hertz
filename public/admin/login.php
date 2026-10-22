<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    if (Auth::attempt($_POST['user'] ?? '', $_POST['pass'] ?? '')) {
        header('Location: /admin/index.php'); exit;
    }
    $error = 'Credenciales inválidas';
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Admin login</title></head>
<body style="font-family:system-ui;max-width:400px;margin:3em auto">
<h1>Acceso administrador</h1>
<?php if ($error): ?><p style="color:#c62828"><?= Security::e($error) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= Security::e(Security::csrfToken()) ?>">
<p><label>Usuario <input name="user" required></label></p>
<p><label>Clave <input type="password" name="pass" required></label></p>
<button>Entrar</button>
</form>
</body></html>
