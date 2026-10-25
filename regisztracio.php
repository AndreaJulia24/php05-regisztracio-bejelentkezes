<?php
declare(strict_types=1);

require __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

if (isset($_SESSION['uid'])) {
    redirect('index.php');
}

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordAgain = (string) ($_POST['password_again'] ?? '');

    if ($name === '') {
        $errors[] = 'A név megadása kötelező.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adj meg egy érvényes e-mail címet.';
    } elseif (find_user($email) !== null) {
        $errors[] = 'Ezzel az e-mail címmel már regisztráltak.';
    }

    if (mb_strlen($password) < 8) {
        $errors[] = 'A jelszónak legalább 8 karakter hosszúnak kell lennie.';
    }

    if ($password !== $passwordAgain) {
        $errors[] = 'A két jelszó nem egyezik.';
    }

    if ($errors === []) {
       $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = get_pdo()->prepare('INSERT INTO felhasznalo (nev, email, jelszo_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, mb_strtolower($email), $hash]);

            // Sikeres regisztráció után átirányítás a bejelentkezéshez
            redirect('bejelentkezes.php?registered=1');

        } catch (PDOException $ex) {
            // UNIQUE megsértés kezelése (ha az e-mail már létezik az adatbázisban)
            if ($ex->getCode() === '23000' || str_contains($ex->getMessage(), 'UNIQUE')) {
                $errors['email'] = 'Ezzel az e-maillel már regisztráltak.';
            } else {
                throw $ex; // Egyéb váratlan adatbázis hiba dobása
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>
<body>
    <button class="theme-toggle" type="button" aria-label="Sötét mód">Dark</button>
    <main>
        <h1>Regisztráció</h1>
        <p>Hozz létre egy új felhasználói fiókot.</p>

        <?php if ($errors !== []): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>
                Név
                <input type="text" name="name" value="<?= e($name) ?>" required>
            </label>
            <label>
                E-mail cím
                <input type="email" name="email" value="<?= e($email) ?>" required>
            </label>
            <label>
                Jelszó
                <input type="password" name="password" minlength="8" required>
            </label>
            <label>
                Jelszó még egyszer
                <input type="password" name="password_again" minlength="8" required>
            </label>
            <button class="primary" type="submit">Regisztráció</button>
        </form>

        <p class="small">Van már fiókod? <a href="bejelentkezes.php">Jelentkezz be</a></p>
    </main>
</body>
</html>
