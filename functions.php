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

require __DIR__ . '/data/database.php';

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

    header('Location: belepes.php');
    exit;
}
function bejelentkezve_kell(): int 
{
    if (empty($_SESSION['uid'])) { 
        header('Location: /belepes.php', true, 302); 
        exit; 
    }
    return (int) $_SESSION['uid'];
}