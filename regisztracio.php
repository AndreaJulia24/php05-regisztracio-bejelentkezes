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

        <ul class="errors" id="error-list" hidden></ul>

        <form id="reg-form" novalidate>
            <label>
                Név
                <input type="text" id="nev" maxlength="80" required>
            </label>
            <label>
                E-mail cím
                <input type="email" id="email" required>
            </label>
            <label>
                Jelszó
                <input type="password" id="jelszo" minlength="8" required>
            </label>
            <label>
                Jelszó még egyszer
                <input type="password" id="jelszo2" minlength="8" required>
            </label>
            <button class="primary" type="submit">Regisztráció</button>
        </form>

        <p class="small">Van már fiókod? <a href="bejelentkezes.php">Jelentkezz be</a></p>
    </main>

    <script type="module">
        import { auth, regisztral, kilep } from './firebase.js';

        // Ha már be van jelentkezve, ne regisztráljon újra
        await auth.authStateReady();
        if (auth.currentUser) {
            window.location.replace('index.php');
        }

        const form = document.querySelector('#reg-form');
        const errorList = document.querySelector('#error-list');

        function mutatHibak(lista) {
            errorList.innerHTML = '';
            for (const szoveg of lista) {
                const li = document.createElement('li');
                li.textContent = szoveg;
                errorList.appendChild(li);
            }
            errorList.hidden = lista.length === 0;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // ne küldje el a PHP-nak, ne töltse újra az oldalt

            const nev = document.querySelector('#nev').value.trim();
            const email = document.querySelector('#email').value.trim().toLowerCase();
            const jelszo = document.querySelector('#jelszo').value;
            const jelszo2 = document.querySelector('#jelszo2').value;

            const hibak = [];
            if (nev.length < 2) hibak.push('A név legalább 2 karakter legyen.');
            if (jelszo.length < 8) hibak.push('A jelszónak legalább 8 karakter hosszúnak kell lennie.');
            if (jelszo !== jelszo2) hibak.push('A két jelszó nem egyezik.');

            if (hibak.length > 0) {
                mutatHibak(hibak);
                return;
            }

            try {
                await regisztral(nev, email, jelszo);
                // A Firebase regisztráció után automatikusan bejelentkeztet,
                // ezért kiléptetjük, és a bejelentkezés oldalra küldjük
                await kilep();
                window.location.replace('bejelentkezes.php?registered=1');
            } catch (err) {
                console.error('Hiba a regisztráció során:', err);
                if (err.code === 'auth/email-already-in-use') {
                    mutatHibak(['Ezzel az e-mail címmel már regisztráltak.']);
                } else if (err.code === 'auth/invalid-email') {
                    mutatHibak(['Adj meg egy érvényes e-mail címet.']);
                } else if (err.code === 'auth/weak-password') {
                    mutatHibak(['Túl gyenge jelszó.']);
                } else {
                    mutatHibak(['Hiba történt: ' + err.message]);
                }
            }
        });
    </script>
</body>
</html>
