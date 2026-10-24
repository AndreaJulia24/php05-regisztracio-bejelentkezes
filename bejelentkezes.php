<?php
declare(strict_types=1);

require __DIR__ . '/functions.php';

if (current_user() !== null) {
    redirect('index.php');
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = find_user($email);

    if ($user === null || !password_verify($password, $user['password'])) {
        $error = 'Hibás e-mail cím vagy jelszó.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'name' => $user['name'],
            'email' => $user['email'],
        ];

        redirect('index.php');
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
