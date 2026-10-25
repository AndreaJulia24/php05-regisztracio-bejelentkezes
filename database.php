<?php
declare(strict_types=1);
function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dbPath = __DIR__ . '/database.sqlite';
        
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $pdo->exec("CREATE TABLE IF NOT EXISTS felhasznalo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nev TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            jelszo_hash TEXT NOT NULL,
            letrehozva DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS emlekezz_token (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            felhasznalo_id INTEGER NOT NULL,
            selector TEXT NOT NULL UNIQUE,
            validator_hash TEXT NOT NULL,
            lejarat DATETIME NOT NULL,
            FOREIGN KEY (felhasznalo_id) REFERENCES felhasznalo(id) ON DELETE CASCADE
        );
    ");
    }

    return $pdo;
}

function db(): PDO
{
    return get_pdo();
}