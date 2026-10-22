<?php
declare(strict_types=1);
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/config/database.php';
Auth::start();
