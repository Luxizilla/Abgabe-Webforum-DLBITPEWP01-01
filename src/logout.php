<?php
// ==========================================
// LOGOUT-PROZESS VERARBEITEN
// ==========================================

session_start(); // 1. Verbindung zum Aktenkoffer herstellen

// 2. Den Inhalt des Koffers leeren (Variablen löschen)
$_SESSION = array();

// 3. Den Session-Cookie im Browser löschen (den "Schlüssel" wegwerfen)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Den Aktenkoffer auf dem Server verbrennen
session_destroy();

// 5. Zurück zum Login oder zur Startseite
header("Location: index.php");
exit();
?>