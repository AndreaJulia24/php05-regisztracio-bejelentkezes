<?php
declare(strict_types=1);

require __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

$pdo = get_pdo();
$user = null;

if (isset($_SESSION['uid'])) {
    $stmt = $pdo->prepare('SELECT nev AS name, email FROM felhasznalo WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch() ?: null;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció és bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
    <script src="firebase.js"></script> 
</head>
<body>
    <button class="theme-toggle" type="button" aria-label="Sötét mód">Dark</button>
    <main>
        <?php if ($user !== null): ?>
            <h1>Szia, <?= e($user['name']) ?>!</h1>
            <p>Sikeresen bejelentkeztél ezzel az e-mail címmel: <?= e($user['email']) ?></p>
            <div class="actions">
                <a class="primary" href="kijelentkezes.php" style="grid-column: 1 / -1;">Kijelentkezés</a>
            </div>
        <?php else: ?>
            <h1>Üdvözlünk!</h1>
            <p>Jelentkezz be a fiókodba, vagy hozz létre egy új felhasználói fiókot.</p>
            <div class="actions">
                <a class="primary" href="bejelentkezes.php">Bejelentkezés</a>
                <a class="secondary" href="regisztracio.php">Regisztráció</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
