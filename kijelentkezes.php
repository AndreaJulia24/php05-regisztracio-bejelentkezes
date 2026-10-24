<?php
declare(strict_types=1);

require __DIR__ . '/functions.php';
$_SESSION = [];
$p = session_get_cookie_params();
setcookie(
    session_name(), 
    '', 
    [
        'expires' => time() - 3600, 
        'path' => $p['path'], 
        'domain' => $p['domain'] ?? '',
        'secure' => $p['secure'] ?? false,
        'httponly' => true, 
        'samesite' => 'Lax'
    ]
);

session_destroy();

header('Location: bejelentkezes.php', true, 303); 
exit;