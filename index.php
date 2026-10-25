<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció és bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js"></script>
</head>
<body>
    <button class="theme-toggle" type="button" aria-label="Sötét mód">Dark</button>
    <main>
        <!-- Amíg a Firebase ellenőrzi, ki van bejelentkezve -->
        <p id="betoltes">Betöltés...</p>

        <!-- Bejelentkezett állapot: kezdetben elrejtve, a Firebase jeleníti meg -->
        <section id="auth-belepve" style="display: none;">
            <h1 id="udv">Szia!</h1>
            <p id="udv-email"></p>
            <div class="actions">
                <button id="logout-btn" class="primary" type="button" style="grid-column: 1 / -1; width: 100%;">Kijelentkezés</button>
            </div>
        </section>

        <!-- Vendég (kijelentkezett) állapot: szintén elrejtve, hogy ne villanjon fel -->
        <section id="auth-vendeg" style="display: none;">
            <h1>Üdvözlünk!</h1>
            <p>Jelentkezz be a fiókodba, vagy hozz létre egy új felhasználói fiókot.</p>
            <div class="actions">
                <a class="primary" href="bejelentkezes.php">Bejelentkezés</a>
                <a class="secondary" href="regisztracio.php">Regisztráció</a>
            </div>
        </section>
    </main>


<script type="module">
        import { auth, db, kilep } from './firebase.js';
        import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
        import { doc, getDoc } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

        const belepveSzekcio = document.querySelector('#auth-belepve');
        const vendegSzekcio = document.querySelector('#auth-vendeg');
        const udvCim = document.querySelector('#udv');
        const udvEmail = document.querySelector('#udv-email');
        const logoutGomb = document.querySelector('#logout-btn');
        const betoltes = document.querySelector('#betoltes');

        onAuthStateChanged(auth, async (user) => {
            if (user) {
                try {
                    const p = await getDoc(doc(db, 'profilok', user.uid));
                    const nev = p.data()?.nev ?? user.displayName ?? user.email;

                    if (udvCim) udvCim.textContent = 'Szia, ' + nev + '!';
                    if (udvEmail) udvEmail.textContent = 'Sikeresen bejelentkeztél ezzel az e-mail címmel: ' + user.email;
                } catch (e) {
                    console.error('Hiba a profil betöltésekor:', e);
                }

                if (vendegSzekcio) vendegSzekcio.style.display = 'none';
                if (belepveSzekcio) belepveSzekcio.style.display = 'block';
            } else {
                if (belepveSzekcio) belepveSzekcio.style.display = 'none';
                if (vendegSzekcio) vendegSzekcio.style.display = 'block';
            }

            // Megvan a válasz, a betöltés szöveg eltűnhet
            if (betoltes) betoltes.style.display = 'none';
        });

        if (logoutGomb) {
            logoutGomb.addEventListener('click', async () => {
                // Az onAuthStateChanged magától átvált a vendég nézetre, nem kell újratölteni
                await kilep();
            });
        }
    </script>

</body>
</html>


