<?php
// ==========================================
// INITIALISIERUNG
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// --- Prüfung ob User angemeldet ist ---
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}
include 'connection.php';

// ==========================================
// PROFIL LADEN & AKTIONEN VERARBEITEN
// ==========================================
if (isset($_SESSION['userID'])) {
    $sqlPrepare = $con->prepare("SELECT * FROM Nutzer WHERE NutzerID= ?");
    $sqlPrepare->bind_param("s", $_SESSION['userID']);
    $sqlPrepare->execute();
    $sqlReturn = $sqlPrepare->get_result();

    if ($sqlReturn->num_rows === 1) {
        $userData = $sqlReturn->fetch_assoc();
    }

    // --- Spezial Admin Bereich ---
    if ($userData['RollenID'] === 2) {
        // Member Laden
        $sqlBenutzerRolle = $con->prepare("SELECT NutzerID, Benutzername FROM Nutzer WHERE RollenID=1");
        $sqlBenutzerRolle->execute();
        $BenutzerRolleListMember = $sqlBenutzerRolle->get_result();
        
        // Admin Laden
        $sqlBenutzerRolle = $con->prepare("SELECT NutzerID, Benutzername FROM Nutzer WHERE RollenID=2 AND NutzerID != ?");
        $sqlBenutzerRolle->bind_param("i", $_SESSION['userID']);
        $sqlBenutzerRolle->execute();
        $BenutzerRolleListAdmin = $sqlBenutzerRolle->get_result();

        // Themen Laden für Lösch-Auswahl
        $sqlThemen = $con->prepare("SELECT ThemenID, Kategorie FROM Thema ORDER BY Kategorie ASC");
        $sqlThemen->execute();
        $themenList = $sqlThemen->get_result();

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateRoles'])) {
            $zuMember = isset($_POST['zuMember']) ? $_POST['zuMember'] : [];
            $zuAdmin = isset($_POST['zuAdmin']) ? $_POST['zuAdmin'] : [];
            $neueKategorie = isset($_POST['neueKategorie']) ? trim($_POST['neueKategorie']) : '';
            $zuLoeschendeThemen = isset($_POST['zuLoeschendeThemen']) ? $_POST['zuLoeschendeThemen'] : [];

            // Rollen Upgrades
            if (!empty($zuAdmin)) {
                foreach ($zuAdmin as $gewaehlteID) {
                    $sqlUpgrade = $con->prepare("UPDATE Nutzer SET RollenID=2 WHERE NutzerID= ?");
                    $sqlUpgrade->bind_param("i", $gewaehlteID);
                    $sqlUpgrade->execute();
                }
            }
            // Rollen Downgrades
            if (!empty($zuMember)) {
                foreach ($zuMember as $gewaehlteID) {
                    $sqlDowngrade = $con->prepare("UPDATE Nutzer SET RollenID=1 WHERE NutzerID= ?");
                    $sqlDowngrade->bind_param("i", $gewaehlteID);
                    $sqlDowngrade->execute();
                }
            }

            // Kategorien hinzufügen
            $katWarnung = "";
            if (!empty($neueKategorie)) {
                $sqlCheckKat = $con->prepare("SELECT * FROM Thema WHERE Kategorie = ?");
                $sqlCheckKat->bind_param("s", $neueKategorie);
                $sqlCheckKat->execute();
                if ($sqlCheckKat->get_result()->num_rows === 0) {
                    $sqlInsertKat = $con->prepare("INSERT INTO Thema (NutzerID, Kategorie) VALUES (?, ?)");
                    $sqlInsertKat->bind_param("is", $_SESSION['userID'], $neueKategorie);
                    $sqlInsertKat->execute();
                } else {
                    $katWarnung = "<div class='alert alert-warning mt-2'>Die Kategorie <strong>'" . htmlspecialchars($neueKategorie) . "'</strong> existiert bereits und konnte nicht doppelt angelegt werden.</div>";
                }
            }

            // Kategorien löschen (nur wenn sie keine Beiträge haben)
            $nichtGeloescht = 0;
            if (!empty($zuLoeschendeThemen)) {
                foreach ($zuLoeschendeThemen as $themenID) {
                    $sqlCheckBeitrag = $con->prepare("SELECT COUNT(*) as anz FROM BeitragKategorie WHERE ThemenID = ?");
                    $sqlCheckBeitrag->bind_param("i", $themenID);
                    $sqlCheckBeitrag->execute();
                    $res = $sqlCheckBeitrag->get_result()->fetch_assoc();
                    if ($res['anz'] == 0) {
                        $sqlDelKat = $con->prepare("DELETE FROM Thema WHERE ThemenID = ?");
                        $sqlDelKat->bind_param("i", $themenID);
                        $sqlDelKat->execute();
                    } else {
                        $nichtGeloescht++;
                    }
                }
            }

            $warnungText = "";
            if ($nichtGeloescht > 0) {
                $warnungText = "<div class='alert alert-warning mt-2'>Einige Kategorien konnten nicht gelöscht werden, da sie noch Beiträgen zugeordnet sind.</div>";
            }

            $_SESSION['meldung'] = "<div class='alert alert-success'>Admin-Aktionen wurden ausgeführt.</div>" . $katWarnung . $warnungText;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // --- Profil Update ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateProfil'])) {
        $benutzer = $_POST['Benutzername'];
        $email = $_POST['ChEmail'];
        $passwordRaw = $_POST['Password'];
        $vorname = $_POST['Vorname'];
        $nachname = $_POST['Nachname'];
        $errors = [];

        // E-Mail Adressänderung auf Dopplungen prüfen
        if ($userData['Email'] != $email) {
            $emailCheck = $con->prepare("SELECT * FROM Nutzer WHERE Email= ? ");
            $emailCheck->bind_param("s", $email);
            $emailCheck->execute();
            $emailCheckResult = $emailCheck->get_result();
            if ($emailCheckResult->num_rows > 0) {
                $errors['email'] = "Die eingegebene Email-Adrresse: " . htmlspecialchars($email) . " ist schon Registriert.";
            }
        }

        // Benutzername Änderung auf Dopplungen prüfen
        if ($userData['Benutzername'] != $benutzer) {
            $benutzerCheck = $con->prepare("SELECT * FROM Nutzer WHERE Benutzername=? ");
            $benutzerCheck->bind_param("s", $benutzer);
            $benutzerCheck->execute();
            $benutzerCheckResult = $benutzerCheck->get_result();
            if ($benutzerCheckResult->num_rows > 0) {
                $errors['benutzername'] = "Der eingegebene Benutzername: " . htmlspecialchars($benutzer) . " ist schon vergeben.";
            }
        }

        if (empty($errors)) {

            // Entscheidung: Wird das Passwort auch geändert?
            if (!empty($passwordRaw)) {
                // Mit Passwort-Update
                $passwordHash = password_hash($passwordRaw, PASSWORD_DEFAULT);
                $sql = $con->prepare("UPDATE Nutzer SET Benutzername=?, Email=?, Vorname=?, Nachname=?, PasswordHash=? WHERE NutzerID=?");
                $sql->bind_param("sssssi", $benutzer, $email, $vorname, $nachname, $passwordHash, $_SESSION['userID']);
            } else {
                // Ohne Passwort-Update (Passwort-Spalte wird ignoriert)
                $sql = $con->prepare("UPDATE Nutzer SET Benutzername=?, Email=?, Vorname=?, Nachname=? WHERE NutzerID=?");
                $sql->bind_param("ssssi", $benutzer, $email, $vorname, $nachname, $_SESSION['userID']);
            }

            if ($sql->execute()) {
                $_SESSION['meldung'] = "<div class='alert alert-success'>Profil erfolgreich aktualisiert!</div>";
                header("Location: profil.php"); // Seite neu laden, um neue Daten anzuzeigen
                exit();
            } else {
                $meldung = "<div class='alert alert-danger'>Fehler beim Speichern.</div>";
            }

        }
    }

    // --- Nutzer Account löschen und Anonymisieren ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnDeleteProfil'])) {
        $dummyEmail = "deleted_" . $_SESSION['userID'] . "@webforum.local";
        $leeresPasswort = "";
        $sqlDeleteProfil = $con->prepare("UPDATE Nutzer SET Email=?, PasswordHash=?, Benutzername = CONCAT(Benutzername, ' (gelöscht)') WHERE NutzerID=?");
        $sqlDeleteProfil->bind_param("ssi", $dummyEmail, $leeresPasswort, $_SESSION['userID']);
        if ($sqlDeleteProfil->execute()) {
            unset($_SESSION['userID']);
            unset($_SESSION['answerID']);
            $_SESSION['meldung'] = "<div class='alert alert-success'>Profil erfolgreich Gelöscht!</div>";
            header("Location: index.php"); // Seite neu laden, um neue Daten anzuzeigen
            exit();

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
    <script src="scripte.js" defer></script>

    <!--Tom Select Kategorie Multiauswahl-->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.ts-select').forEach((el) => {
                new TomSelect(el, {
                    plugins: ['remove_button'],
                    create: false,
                    maxItems: 5,
                });
            });
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
                        <a class="nav-link active" href="profil.php">Mein Profil</a>
                        <a class="nav-link text-danger" href="logout.php">Abmelden</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Anmelden</a>
                    <?php endif; ?>
                </div>
            </div>
    </nav>
    
    <!-- ========================================== -->
    <!-- HAUPTINHALT                                -->
    <!-- ========================================== -->
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div style="width: 40rem;">
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
            
            <!-- BEREICH: PROFIL DATEN -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Profil</h5>
                    <div class="card-subtitle mb-2 text-body-secondary">
                        <form class="profil-info m-3 needs-validation" method="post" novalidate>
                            <div class="mb-3">
                                <label for="Vorname" class="form-label">Vorname</label>
                                <input placeholder="Server" type="text" class="form-control" id="Vorname" name="Vorname"
                                    value="<?php if (isset($userData['Vorname'])) {
                                        echo htmlspecialchars($userData['Vorname']);
                                    } ?>">
                            </div>
                            <div class="mb-3">
                                <label for="Nachname" class="form-label">Nachname</label>
                                <input placeholder="Server" type="text" class="form-control" id="Nachname"
                                    name="Nachname" value="<?php if (isset($userData['Nachname'])) {
                                        echo htmlspecialchars($userData['Nachname']);
                                    } ?>">
                            </div>
                            <div class="mb-3">
                                <label for="Benutzername" class="form-label">Benutzername</label>
                                <input placeholder="Server" type="text" class="form-control <?php
                                if (isset($errors['benutzername'])) {
                                    echo 'is-invalid';
                                } elseif (!empty($userData['Benutzername'])) {
                                    echo '';
                                }
                                ?>" id="Benutzername" name="Benutzername" value="<?php if (isset($userData['Benutzername'])) {
                                    echo htmlspecialchars($userData['Benutzername']);
                                } ?>">
                                <?php if (isset($errors['benutzername'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['benutzername'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="ChEmail" class="form-label">E-Mail Adresse</label>
                                <input placeholder="Server" type="email" class="form-control <?php
                                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                    if (isset($errors['email'])) {
                                        echo 'is-invalid';
                                    } elseif (!empty($_POST['ChEmail'])) {
                                        echo '';
                                    }
                                }
                                ?>" id="ChEmail" name="ChEmail" value="<?php if (isset($userData['Email'])) {
                                    echo htmlspecialchars($userData['Email']);
                                } ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['email'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div id="PwChange">
                                <div class="mb-3">
                                    <label for="Password" class="form-label">Password ändern</label>
                                    <input placeholder="Neues Password" type="password" class="form-control"
                                        id="Password" name="Password">
                                    <div class="invalid-feedback">Mindestens 8 Zeichen erforderlich.</div>
                                </div>
                                <div class="mb-4">
                                    <label for="PasswordWd" class="form-label">Password
                                        wiederholen</label>
                                    <input placeholder="Neues Password wiederholen" type="password" class="form-control"
                                        id="PasswordWd" name="PasswordWd">
                                    <div class="invalid-feedback">Passwörter müssen übereinstimmen.</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="ChSpeichern"
                                name="updateProfil">Speichern</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- BEREICH: ADMIN -->
            <?php if ($userData['RollenID'] === 2): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Admin Bereich</h5>
                        <form method="post" class="m-3 needs-validation" novalidate>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="BenutzerRoller">Rolle auf Admin Ändern</label>
                                <select class="ts-select" name="zuAdmin[]" multiple placeholder="Benutzername wählen..."
                                    autocomplete="off">
                                    <?php while ($row = $BenutzerRolleListMember->fetch_assoc()): ?>
                                        <option value="<?= $row['NutzerID'] ?>">
                                            <?= htmlspecialchars($row['Benutzername']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="BenutzerRoller">Rolle auf Member Ändern</label>
                                <select class="ts-select" name="zuMember[]" multiple placeholder="Benutzername wählen..."
                                    autocomplete="off">
                                    <?php while ($row = $BenutzerRolleListAdmin->fetch_assoc()): ?>
                                        <option value="<?= $row['NutzerID'] ?>">
                                            <?= htmlspecialchars($row['Benutzername']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Neue Kategorie</span>
                            <input type="text" class="form-control" name="neueKategorie" placeholder="Neue Kategorie hinzufügen">
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="zuLoeschendeThemen">Kategorie löschen</label>
                            <select class="ts-select" name="zuLoeschendeThemen[]" multiple placeholder="Kategorien wählen..." autocomplete="off">
                                <?php while ($row = $themenList->fetch_assoc()): ?>
                                    <option value="<?= $row['ThemenID'] ?>"><?= htmlspecialchars($row['Kategorie']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                            <button type="submit" class="btn btn-primary" id="updateRoles"
                                name="updateRoles">Speichern</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- BEREICH: KONTO LÖSCHEN -->
            <div class="d-flex justify-content-end">
                <form method="post" class="py-3" onsubmit="return confirm('Möchtest du dein Konto wirklich löschen?');">
                    <button type="submit" name="btnDeleteProfil" class="btn btn-outline-danger"
                        aria-label="Löschen">Konto
                        Löschen</button>
                </form>
            </div>
        </div>
    </div>
</body>