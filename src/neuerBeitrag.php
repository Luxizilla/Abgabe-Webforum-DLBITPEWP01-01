<?php
// ==========================================
// INITIALISIERUNG
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

// --- Prüfung ob User angemeldet ist ---
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
} else {
    include 'connection.php';

    // --- Kategorien aus der Datenbank abfragen ---
    $sqlKategorie = $con->prepare("SELECT * FROM Thema");
    $sqlKategorie->execute();
    $kategorieList = $sqlKategorie->get_result();

    // ==========================================
    // FORMULARVERARBEITUNG
    // ==========================================
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titel = $_POST['Titel'];
        $beitrag = $_POST['Beitrag'];
        $gewaehlteKategorien = isset($_POST['Kategorie']) ? $_POST['Kategorie'] : [];
        
        // --- HTMLPurifier einbinden ---
        // HTMLPurifier einbinden (Passe den Pfad an, je nachdem wo der Ordner liegt)
        // require_once 'vendor/autoload.php'; // <-- Nutze dies, wenn du Composer verwendest
        require_once 'library/HTMLPurifier.auto.php';

        // Purifier konfigurieren: Standard-Whitelist nutzen (erlaubt alle sicheren HTML-Tags von TinyMCE, blockiert XSS)
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        
        // Das gefährliche HTML vom TinyMCE bereinigen lassen
        $sichererBeitrag = $purifier->purify($beitrag);

        // --- Validierung ---
        // Titel-Check
        if (mb_strlen($titel) < 5 || mb_strlen($titel) > 100) {
            $errors['Titel'] = "Titel muss zwischen 5 und 100 Zeichen lang sein.";
        }

        // Beitrag-Check
        if (mb_strlen(strip_tags($sichererBeitrag)) < 50) {
            $errors['Beitrag'] = "Dein Text ist zu kurz. Bitte schreibe mindestens 50 Zeichen (ohne Formatierung).";
        } elseif (mb_strlen($sichererBeitrag) > 10000) {
            $errors['Beitrag'] = "Der Beitrag ist inklusive Formatierung zu lang (max. 10.000 Zeichen). Bitte entferne etwas Text oder Formatierung.";
        }

        // --- SQL Speicherung ---
        if (empty($errors)) {
            $sqlBeitrag = $con->prepare("INSERT INTO Beitrag (NutzerID, Titel, Textinhalt) 
            VALUES (?, ?, ?)");
            $sqlBeitrag->bind_param("iss", $_SESSION['userID'], $titel, $sichererBeitrag);

            if ($sqlBeitrag->execute()) {
                $neueBeitragID = $con->insert_id;
                if (!empty($gewaehlteKategorien)) {
                    foreach ($gewaehlteKategorien as $gewaehlteID) {
                        $sqlKat = $con->prepare("INSERT INTO BeitragKategorie (BeitragID, ThemenID) VALUES (?, ?)");
                        $sqlKat->bind_param("ii", $neueBeitragID, $gewaehlteID);
                        $sqlKat->execute();
                    }
                }
                $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Beitrag erfolgreich veröffentlicht</div>";
                header("Location: index.php");
                exit();
            } else {
                $meldung = "<div class='alert alert-danger mt-3'>Fehler: " . $con->error . "</div>";
            }
        }


    }
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webforum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">

    <!--Tom Select Kategorie Multiauswahl-->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Warten, bis das Dokument geladen ist
        document.addEventListener("DOMContentLoaded", function () {
            new TomSelect("#Kategorie", {
                plugins: ['remove_button'],
                create: false,
                maxItems: 5,
            });
        });
    </script>

    <!--TinyMCE Texteditor-->
    <script src="tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#Beitrag',
            license_key: 'gpl',
            promotion: false
        });
    </script>
</head>

<body>
    <!-- ========================================== -->
    <!-- NAVBAR                                     -->
    <!-- ========================================== -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="./pictures/Logo-Webforum.png" alt="Logo" width="50" height="50"
                    class="d-inline-block align-text-center me-4">
                Webforum
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['userID'])): ?>
                        <a class="nav-link" href="profil.php">Mein Profil</a>
                        <a class="nav-link text-danger" href="logout.php">Abmelden</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Anmelden</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- HAUPTINHALT                                -->
    <!-- ========================================== -->
    <div class="d-flex justify-content-center align-items-center container mt-5">
        <div class="card w-100">
            
            <!-- System-Meldungen -->
            <?php
            if (isset($_SESSION['meldung'])) {
                echo $_SESSION['meldung'];
                unset($_SESSION['meldung']); // Sofort wieder löschen, damit sie nicht ewig stehen bleibt
            }

            if (isset($meldung)) {
                echo $meldung;
            }
            ?>
            <div class="card-body">
                <h5 class="card-title">Neuen Beitrag erstellen</h5>
                <div class="card-subtitle mb-2 text-body-secondary">
                    <form method="post" class="m-3 needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="Titel" class="form-label">Titel</label>
                            <input type="text" class="form-control <?php
                            if (isset($errors['Titel'])) {
                                echo 'is-invalid';
                            } elseif (!empty('Titel')) {
                                echo '';
                            }
                            ?>" id="Titel" name="Titel">
                            <div class="invalid-feedback"><?= $errors['Titel'] ?></div>
                        </div>
                        <div class="mb-4">
                            <label for="Beitrag" class="form-label">Beitrag / Frage</label>
                            <textarea class="form-control <?php
                            if (isset($errors['Beitrag'])) {
                                echo 'is-invalid';
                            } elseif (!empty('Beitrag')) {
                                echo '';
                            }
                            ?>" id="Beitrag" name="Beitrag"></textarea>
                            <div class="invalid-feedback"><?= $errors['Beitrag'] ?></div>
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="Kategorie">Kategorien</label>
                            <select id="Kategorie" name="Kategorie[]" multiple placeholder="Kategorien wählen..."
                                autocomplete="off">
                                <?php while ($row = $kategorieList->fetch_assoc()): ?>
                                    <option value="<?= $row['ThemenID'] ?>"><?= htmlspecialchars($row['Kategorie']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Erstellen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>