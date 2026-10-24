<?php
declare(strict_types=1);

require __DIR__ . '/functions.php';
require __DIR__ . '/database.php';

// Ha már be van jelentkezve, irány az index
if (isset($_SESSION['uid'])) {
    redirect('index.php');
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = get_pdo()->prepare('SELECT id, jelszo_hash FROM felhasznalo WHERE email = ?');
    $stmt->execute([mb_strtolower($email)]); //megvedi az appot az sql injection ellen
    $f = $stmt->fetch();

    if ($f && password_verify($password, $f['jelszo_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $f['id'];

        if (password_needs_rehash($f['jelszo_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = get_pdo()->prepare('UPDATE felhasznalo SET jelszo_hash = ? WHERE id = ?');
            $updateStmt->execute([$newHash, $f['id']]);
        }

        redirect('index.php');
    } else {
        $error = 'Hibás e-mail cím vagy jelszó.';
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <h1>Bejelentkezés</h1>
        <p>Jelentkezz be a fiókodba.</p>

        <?php if (isset($_GET['registered'])): ?>
            <p class="success">Sikeres regisztráció! Most már bejelentkezhetsz.</p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <ul class="errors">
                <li><?= e($error) ?></li>
            </ul>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>
                E-mail cím
                <input type="email" name="email" value="<?= e($email) ?>" required>
            </label>
            <label>
                Jelszó
                <input type="password" name="password" required>
            </label>
            <button class="primary" type="submit">Bejelentkezés</button>
        </form>

        <p class="small">Még nincs fiókod? <a href="regisztracio.php">Regisztrálj</a></p>
    </main>
</body>
</html>