<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::logout();
header('Location: /admin/login.php');
