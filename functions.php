<?php
declare(strict_types=1);

//biztonsagi session beallitasok
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0, 
    'path' => '/', 
    'httponly' => true,
    'samesite' => 'Lax', 
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();
emlekezz_ram_ellenoriz(); 

require_once __DIR__ . '/database.php';

const USERS_FILE = __DIR__ . '/data/users.json';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function load_users(): array
{
    if (!is_file(USERS_FILE)) {
        return [];
    }

    $users = json_decode((string) file_get_contents(USERS_FILE), true);

    return is_array($users) ? $users : [];
}

function save_users(array $users): void
{
    if (!is_dir(dirname(USERS_FILE))) {
        mkdir(dirname(USERS_FILE), 0777, true);
    }

    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function find_user(string $email): ?array
{
    foreach (load_users() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }

    return null;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}


function regisztral(PDO $pdo, string $nev, string $email, string $jelszo, string $jelszo2): array|string
{
    $hibak = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hibak[] = 'Érvénytelen e-mail cím.';
    }
    if (mb_strlen($jelszo) < 10) {
        $hibak[] = 'A jelszónak legalább 10 karakter hosszúnak kell lennie.';
    }
    if ($jelszo !== $jelszo2) {
        $hibak[] = 'A két jelszó nem egyezik.';
    }

    if (!empty($hibak)) {
        return $hibak; 
    }

    $hash = password_hash($jelszo, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO felhasznalo (nev, email, jelszo_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nev, $email, $hash]);
    } catch (PDOException $e) {
        // SQLite/MySQL UNIQUE constraint megsértése (pl. duplikált e-mail)
        if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE')) {
            return "Ezzel az e-maillel már regisztráltak.";
        }
        throw $e; 
    }

    header('Location: bejelentkezes.php');
    exit;
}
function bejelentkezve_kell(): int 
{
    if (empty($_SESSION['uid'])) { 
        header('Location: bejelentkezes.php', true, 302); 
        exit; 
    }
    return (int) $_SESSION['uid'];
}

function emlekezz_ram_letrehoz(int $felhasznaloId): void {
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $validatorHash = hash('sha256', $validator);
    $lejarat = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 nap

    $stmt = get_pdo()->prepare('
        INSERT INTO emlekezz_token (felhasznalo_id, selector, validator_hash, lejarat)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$felhasznaloId, $selector, $validatorHash, $lejarat]);

    $cookieErtek = $selector . ':' . $validator;
    setcookie('remember_me', $cookieErtek, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false
    ]);
}

function emlekezz_ram_ellenoriz(): ?int {
    if (empty($_COOKIE['remember_me'])) {
        return null;
    }

    [$selector, $validator] = explode(':', $_COOKIE['remember_me'], 2);
    $stmt = get_pdo()->prepare('SELECT * FROM emlekezz_token WHERE selector = ? AND lejarat > CURRENT_TIMESTAMP');
    $stmt->execute([$selector]);
    $token = $stmt->fetch();

    if ($token && hash_equals($token['validator_hash'], hash('sha256', $validator))) {
        $_SESSION['uid'] = (int) $token['felhasznalo_id'];
        return (int) $token['felhasznalo_id'];
    }

    // Ha a token nem létezik vagy lejárt, vagy a validator nem egyezik, töröljük a cookie-t
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false
    ]);

    emlekezz_ram_torol();
    return null;
    
}
function emlekezz_ram_torol(): void 
{
    if (!empty($_COOKIE['remember_me'])) {
        $reszek = explode(':', $_COOKIE['remember_me'], 2);
        if (count($reszek) === 2) {
            $stmt = get_pdo()->prepare('DELETE FROM emlekezz_token WHERE selector = ?');
            $stmt->execute([$reszek[0]]);
        }
    }

    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS'])
    ]);
}

emlekezz_ram_ellenoriz();