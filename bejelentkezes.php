<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// Inicializáljuk a próbálkozások számlálóját
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Ha már eltelt legalább 60 másodperc az utolsó próbálkozás óta, lenullázzuk a számlálót
if (time() - $_SESSION['last_attempt_time'] > 60) {
    $_SESSION['login_attempts'] = 0;
}

// 1. Ellenőrzés: ha elérte a 6 hibás próbálkozást, 429-es választ adunk
if ($_SESSION['login_attempts'] >= 6) {
    http_response_code(429);
    header('Retry-After: 60');
    header('Content-Type: text/plain; charset=utf-8');
    echo '429 Too Many Requests - Túl sok próbálkozás. Várj 60 másodpercet!';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Növeljük a hibás próbálkozások számát és frissítjük az időbélyeget
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();

    // Ha ezzel a POST-tal érte el a 6-ot, egyből 429-et dobunk
    if ($_SESSION['login_attempts'] >= 6) {
        http_response_code(429);
        header('Retry-After: 60');
        header('Content-Type: text/plain; charset=utf-8');
        echo '429 Too Many Requests - Túl sok próbálkozás. Várj 60 másodpercet!';
        exit;
    }

    // 1–5. próbálkozás esetén a sima 401 Unauthorized hiba megy ki
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
        </form>

        <p class="small">Még nincs fiókod? <a href="regisztracio.php">Regisztrálj</a></p>
    </main>

    <script type="module">
        import { auth, belep } from './firebase.js';

        // Ha már be van jelentkezve, irány az index
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
