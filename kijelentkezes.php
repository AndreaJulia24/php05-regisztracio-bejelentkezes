<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '405 Method Not Allowed - Kijelentkezés csak POST kéréssel lehetséges.';
    exit;
}

require __DIR__ . '/functions.php';

emlekezz_ram_torol(); // töröljük a split-token-t az adatbázisból

$_SESSION = [];

$p = session_get_cookie_params(); // toroljuk a seession cookie-t is
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

// 4. Megszüntetjük a szerveroldali munkamenetet
session_destroy();

// 5. Átirányítunk a bejelentkezési oldalra
header('Location: bejelentkezes.php', true, 303); 
exit;