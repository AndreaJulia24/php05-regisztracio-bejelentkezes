# PHP-05 Regisztráció és Bejelentkezés Mintaalkalmazás

Egy biztonságos, egyszerű és moduláris felhasználókezelő webalkalmazás natív PHP, SQLite és JSON perzisztencia felhasználásával.

---

## Főbb funkciók

- **Felhasználói regisztráció:** Jelszó-megerősítéssel, bemeneti validációval és biztonságos jelszó-hasheléssel (`password_hash`).
- **Bejelentkezés & Kijelentkezés:** Munkamenet-kezelés (`session_start`), védett oldalak jogosultság-ellenőrzése.
- **Hibrid adatkezelés:**
  - SQLite adatbázis (`database.sqlite`, `database.php`)
  - JSON alapú tartalék / alternatív tárolás (`data/users.json`)
- **Téma választó (Light/Dark mode):** Dinamikus váltás a felületen (`theme.js`, nap és hold ikonokkal).

---

## Mappaszerkezet

```text
├── assets/
│   └── icons/
│       ├── moon.png         # Sötét téma ikon
│       └── sun.png          # Világos téma ikon
├── data/
│   └── users.json           # JSON alapú felhasználói adatok
├── bejelentkezes.php        # Bejelentkezési űrlap és feldolgozás
├── database.php             # SQLite adatbázis-kapcsolat és inicializálás
├── database.sqlite          # SQLite adatbázis fájl
├── firebase.js              # Kliensoldali kiegészítő szkript
├── functions.php            # Segédfüggvények (validáció, session, authentikáció)
├── index.php                # Védett kezdőlap (csak bejelentkezve érhető el)
├── kijelentkezes.php        # Munkamenet lezárása és átirányítás
├── regisztracio.php         # Regisztrációs felület és logika
├── style.css                # Felhasználói felület stíluslapja
└── theme.js                 # Világos/sötét téma kezelése böngészőoldalon


### 3. Futtatás és indítás

A projekt mappájában indítsd el a beépített webszervert:

```bash
php -S localhost:8000