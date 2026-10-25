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
