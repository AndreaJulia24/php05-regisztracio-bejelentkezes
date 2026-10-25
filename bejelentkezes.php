<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// 1. Próbálkozások számláló inicializálása
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// 60 mp után nullázunk
if (time() - $_SESSION['last_attempt_time'] > 60) {
    $_SESSION['login_attempts'] = 0;
}

// 2. Rate limit ellenőrzés (ha már tiltva van)
if ($_SESSION['login_attempts'] >= 6) {
    http_response_code(429);
    header('Retry-After: 60');
    header('Content-Type: text/plain; charset=utf-8');
    echo '429 Too Many Requests - Túl sok próbálkozás. Várj 60 másodpercet!';
    exit;
}

$error = '';

// 3. POST kérés feldolgozása (ha szerveroldali beküldés vagy curl teszt érkezik)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();

    if ($_SESSION['login_attempts'] >= 6) {
        http_response_code(429);
        header('Retry-After: 60');
        header('Content-Type: text/plain; charset=utf-8');
        echo '429 Too Many Requests - Túl sok próbálkozás. Várj 60 másodpercet!';
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $jelszo = (string)($_POST['password'] ?? $_POST['jelszo'] ?? '');

    // Lekérjük a felhasználót az adatbázisból
    $stmt = get_pdo()->prepare('SELECT id, nev, email, jelszo_hash FROM felhasznalo WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Jelszó ellenőrzése
    if ($user && password_verify($jelszo, $user['jelszo_hash'])) {
        $_SESSION['login_attempts'] = 0;
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$user['id'];

        if (!empty($_POST['remember_me'])) {
            emlekezz_ram_letrehoz((int)$user['id']);
        }

        header('Location: index.php');
        exit;
    }

    // Hibás belépés esetén
    http_response_code(401);
    $error = 'Hibás e-mail cím vagy jelszó.';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>
<body>
    <button class="theme-toggle" type="button" aria-label="Sötét mód">Dark</button>
    <main>
        <h1>Bejelentkezés</h1>
        <p>Jelentkezz be a fiókodba.</p>

        <p class="success" id="siker" hidden>Sikeres regisztráció! Most már bejelentkezhetsz.</p>

        <ul class="errors" id="error-list" hidden></ul>

        <form id="login-form" novalidate>
            <label>
                E-mail cím
                <input type="email" id="email" required>
            </label>
            <label>
                Jelszó
                <input type="password" id="jelszo" required>
            </label>
            <button class="primary" type="submit">Bejelentkezés</button>
            <div class="remember-box">
                <label for="remember_me">
                    <input type="checkbox" name="remember_me" id="remember_me" value="1">
                    <span>Emlékezzen rám</span>
                </label>
            </div>
        </form>

        <p class="small">Még nincs fiókod? <a href="regisztracio.php">Regisztrálj</a></p>
    </main>

    <script type="module">
        import { auth, belep } from './firebase.js';

        // Ha már be van jelentkezve, irány az index vagyis a főoldal
        await auth.authStateReady();
        if (auth.currentUser) {
            window.location.replace('index.php');
        }

        const form = document.querySelector('#login-form');
        const errorList = document.querySelector('#error-list');
        const siker = document.querySelector('#siker');

        // Regisztráció után (?registered=1) mutatjuk a zöld üzenetet
        if (new URLSearchParams(window.location.search).has('registered')) {
            siker.hidden = false;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // ne küldje el a PHP-nak, ne töltse újra az oldalt
            siker.hidden = true; // ne látszódjon egyszerre a zöld és a piros üzenet
            errorList.hidden = true;
            errorList.innerHTML = '';

            const email = document.querySelector('#email').value.trim().toLowerCase();
            const jelszo = document.querySelector('#jelszo').value;

            try {
                await belep(email, jelszo);
                window.location.replace('index.php');
            } catch (err) {
                const li = document.createElement('li');
                li.textContent = err.message;
                errorList.appendChild(li);
                errorList.hidden = false;
            }
        });
    </script>
</body>
</html>
