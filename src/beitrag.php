<?php
// ==========================================
// INITIALISIERUNG
// ==========================================
session_start();
include 'connection.php';

// ==========================================
// BEITRAG PRÜFEN UND LADEN
// ==========================================
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $beitragID = $_GET['id'];

    // --- Frage Laden ---
    $sqlBeitragFrage = $con->prepare("SELECT Beitrag.*, Nutzer.Benutzername, Rollen.Berechtigung FROM Beitrag JOIN Nutzer ON Beitrag.NutzerID = Nutzer.NutzerID JOIN Rollen ON Nutzer.RollenID = Rollen.RollenID WHERE Beitrag.BeitragID = ? AND Beitrag.ParentID is NULL");
    $sqlBeitragFrage->bind_param("i", $beitragID);
    $sqlBeitragFrage->execute();
    $sqlReturnFrage = $sqlBeitragFrage->get_result();

    if ($sqlReturnFrage->num_rows === 1) {
        $beitragData = $sqlReturnFrage->fetch_assoc();
    } else {
        $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Dieser Beitrag existiert nicht (mehr).</div>";
        header("Location: index.php");
        exit();
    }

    // --- Kategorien für diesen speziellen Beitrag laden ---
    $sqlKategorien = $con->prepare("SELECT Thema.Kategorie FROM Thema JOIN BeitragKategorie ON Thema.ThemenID = BeitragKategorie.ThemenID WHERE BeitragKategorie.BeitragID = ?");
    $sqlKategorien->bind_param("i", $beitragID);
    $sqlKategorien->execute();
    $resKategorien = $sqlKategorien->get_result();

    // --- Antwort Laden ---
    $sqlBeitragAntwort = $con->prepare("SELECT Beitrag.*, Nutzer.Benutzername, Rollen.Berechtigung FROM Beitrag JOIN Nutzer ON Beitrag.NutzerID = Nutzer.NutzerID JOIN Rollen ON Nutzer.RollenID = Rollen.RollenID WHERE Beitrag.ParentID= ? ORDER BY Erstellungsdatum DESC");
    $sqlBeitragAntwort->bind_param("i", $beitragID);
    $sqlBeitragAntwort->execute();
    $sqlReturnAntwort = $sqlBeitragAntwort->get_result();

    // ==========================================
    // AKTIONEN & FORMULARVERARBEITUNG
    // ==========================================

    // --- HTMLPurifier für POST-Anfragen vorbereiten ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once 'library/HTMLPurifier.auto.php';
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
    }

    // --- Auf Beitrag Antworten ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnAntwort']) && isset($_SESSION['userID']) && !empty(trim($_POST['Antwort']))) {
        $beitragAntwort = $purifier->purify($_POST['Antwort']);

        $sqlAntwort = $con->prepare("INSERT INTO Beitrag (NutzerID, Titel, Textinhalt, ParentID) VALUES (?, ?, ?, ?)");
        $sqlAntwort->bind_param("issi", $_SESSION['userID'], $beitragData['Titel'], $beitragAntwort, $beitragID);
        if ($sqlAntwort->execute()) {
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        }
    }

    // --- Rolle des Users Prüfen ---
    $rollenID = 1; // Standardmäßig Gast/Member
    if (isset($_SESSION['userID'])) {
        $sqlBenutzerRolle = $con->prepare("SELECT RollenID FROM Nutzer WHERE NutzerID= ?");
        $sqlBenutzerRolle->bind_param('i', $_SESSION['userID']);
        $sqlBenutzerRolle->execute();
        $benutzerRolleRow = ($sqlBenutzerRolle->get_result())->fetch_assoc();
        $rollenID = (int) $benutzerRolleRow['RollenID'];
    }

    // --- Beitrag Frage Löschen nur als Admin oder wenn noch keine Antwort auf die Frage vorhanden ist ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnDelete'])) {
        if (($sqlReturnAntwort->num_rows === 0 && $beitragData['NutzerID'] === $_SESSION['userID']) || $rollenID === 2) {
            $sqlDeleteFrage = $con->prepare("DELETE FROM Beitrag WHERE BeitragID=?");
            $sqlDeleteFrage->bind_param("i", $beitragID);
            if ($sqlDeleteFrage->execute()) {
                $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Löschen Erfolgreich!</div>";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Löschen nicht möglich! Die Frage hat bereits Antworten oder du bist kein Admin.</div>";
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        }
    }

    // --- Beitrag Frage Bearbeiten nur als Admin oder wenn noch keine Antwort auf die Frage vorhanden ist ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnEditQuestion'])) {
        if (($sqlReturnAntwort->num_rows === 0 && $beitragData['NutzerID'] === $_SESSION['userID']) || $rollenID === 2) {
            $_SESSION['questionEdit'] = true;
        } else {
            $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Bearbeiten nicht möglich! Die Frage hat bereits Antworten oder du bist kein Admin.</div>";
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        }
    }

    // --- Beitrag Frage Bearbeiten Speichern ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnEditQuestionSave'])) {
        unset($_SESSION['questionEdit']);
        $titelEdit = $_POST['FrageTitelEdit'];
        $frageEdit = $purifier->purify($_POST['FrageEdit']);

        $sqlEditFrage = $con->prepare("UPDATE Beitrag SET Titel=?, Textinhalt=? WHERE BeitragID=?");
        $sqlEditFrage->bind_param("ssi", $titelEdit, $frageEdit, $beitragID);
        if ($sqlEditFrage->execute()) {
            $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Frage erfolgreich bearbeitet!</div>";
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        } else {
            $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Speichern Fehlgeschlagen!</div>";
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        }
    }

    // --- Löschen einer einzelnen Antwort ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnDeleteAnswer'])) {
        $answerID = (int) $_POST['btnDeleteAnswer'];
        $sqlBesitzerCheck = $con->prepare("SELECT NutzerID FROM Beitrag WHERE BeitragID= ?");
        $sqlBesitzerCheck->bind_param("i", $answerID);
        $sqlBesitzerCheck->execute();
        $besitzerAntwort = $sqlBesitzerCheck->get_result()->fetch_assoc();

        if ($besitzerAntwort) {
            // Erlaubt, wenn: (Nutzer ist Autor der Antwort) ODER (Nutzer ist Admin)
            if ($besitzerAntwort['NutzerID'] == $_SESSION['userID'] || $rollenID === 2) {
                $sqlDeleteAntwort = $con->prepare("DELETE FROM Beitrag WHERE BeitragID = ?");
                $sqlDeleteAntwort->bind_param("i", $answerID);
                if ($sqlDeleteAntwort->execute()) {
                    $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Antwort erfolgreich gelöscht!</div>";
                    header("Location: beitrag.php?id=" . $beitragID);
                    exit();
                } else {
                    $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Keine Berechtigung zum Löschen dieser Antwort!</div>";
                    header("Location: beitrag.php?id=" . $beitragID);
                    exit();
                }
            }

        }

    }

    // --- Bearbeiten einer einzelnen Antwort ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnEditAnswer'])) {
        $answerID = (int) $_POST['btnEditAnswer'];
        $sqlBesitzerCheck = $con->prepare("SELECT NutzerID, Textinhalt FROM Beitrag WHERE BeitragID= ?");
        $sqlBesitzerCheck->bind_param("i", $answerID);
        $sqlBesitzerCheck->execute();
        $besitzerAntwort = $sqlBesitzerCheck->get_result()->fetch_assoc();

        if ($besitzerAntwort) {
            // Erlaubt, wenn: (Nutzer ist Autor der Antwort) ODER (Nutzer ist Admin)
            if ($besitzerAntwort['NutzerID'] == $_SESSION['userID'] || $rollenID === 2) {
                $_SESSION['answerText'] = $besitzerAntwort['Textinhalt'];
                $_SESSION['answerID'] = $answerID;
            }

        }
    }

    // --- Bearbeitung Speichern ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnEditAnswerSave'])) {
        unset($_SESSION['answerID']);
        $answerIDSave = (int) $_POST['btnEditAnswerSave'];
        $bearbeiteterText = $purifier->purify($_POST['AntwortEdit']);

        $sqlUpdateAntwort = $con->prepare("UPDATE Beitrag SET Textinhalt=?  WHERE BeitragID = ?");
        $sqlUpdateAntwort->bind_param("si", $bearbeiteterText, $answerIDSave);
        if ($sqlUpdateAntwort->execute()) {
            $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Antwort erfolgreich bearbeitet!</div>";
            header("Location: beitrag.php?id=" . $beitragID);
            exit();
        } else {
            $_SESSION['meldung'] = "<div class='alert alert-danger mb-3'>Speichern Fehlgeschlagen!</div>";
            header("Location: beitrag.php?id=" . $beitragID);
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

    <!--TinyMCE implementierung-->
    <script src="tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#Antwort, #AntwortEdit , #FrageEdit',
            license_key: 'gpl',
            promotion: false
        });
    </script>
    
    <!--Bootstrap Icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <!-- ========================================== -->
    <!-- NAVBAR -->
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
    <!-- HAUPTINHALT -->
    <!-- ========================================== -->
    <div class="container d-flex flex-column justify-content-center mt-5">
        
        <!-- System-Meldungen -->
        <?php if (isset($_SESSION['meldung'])) {
            echo $_SESSION['meldung'];
            unset($_SESSION['meldung']);
        }
        ?>

        <!-- ========================================== -->
        <!-- BEREICH: FRAGE -->
        <!-- ========================================== -->
        <h2>Frage</h2>
        <div class="card mb-3 w-100">
        <?php if ((isset($_SESSION['userID']) && $beitragData['NutzerID'] === $_SESSION['userID']) || $rollenID === 2): ?>
                <div class="position-absolute top-0 end-0 d-flex">
                    <form method="post" class="py-3 pe-1">
                        <button type="submit" name="btnEditQuestion" class="btn-delete-icon" aria-label="Bearbeiten"
                        value="<?= $beitragID ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </form>
                    <form method="post" class="py-3 pe-3"
                        onsubmit="return confirm('Möchtest du diesen Beitrag wirklich löschen?');">
                        <button type="submit" name="btnDelete" class="btn-delete-icon" aria-label="Löschen">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            <div class="row g-0">
                <div class="col-md-3 ms-3 mt-3 ">
                    <h4><?= htmlspecialchars($beitragData['Benutzername']) ?><small class="text-muted fs-6">
                            <?= htmlspecialchars($beitragData['Berechtigung']) ?></small></h4>
                    <p>Last update: <?= date("d.m.Y H:i", strtotime($beitragData['Erstellungsdatum'])) ?></p>
                    <div class="mt-2">
                        <p class="small text-muted mb-1">Kategorien:</p>
                        <?php if ($resKategorien->num_rows > 0): ?>
                            <?php while ($kat = $resKategorien->fetch_assoc()): ?>
                                <span class="badge rounded-pill me-1 kategorie-pille">
                                    <?= htmlspecialchars($kat['Kategorie']) ?>
                                </span>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <span class="text-muted small">Keine Kategorien</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php if (isset($_SESSION['questionEdit']) && $_SESSION['questionEdit']): ?>
                    <div class="col-md-8">
                        <div class="card-body">
                            <form method="post" class="m-3">
                                <div class="mb-4">
                                    <input class="form-control" rows="5" id="FrageTitelEdit" name="FrageTitelEdit"
                                        value="<?= htmlspecialchars($beitragData['Titel']) ?>"></input>
                                </div>
                                <div class="mb-4">
                                    <textarea class="form-control" rows="5" id="FrageEdit"
                                        name="FrageEdit"><?= $beitragData['Textinhalt'] ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" name="btnEditQuestionSave">Speichern</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-8 position-relative">
                        <div class="card-body p-4 pe-5">
                            <h5 class="card-title"><?= htmlspecialchars($beitragData['Titel']) ?></h5>
                            <hr>
                            <p class="card-text">
                                <?= $beitragData['Textinhalt'] ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- BEREICH: ANTWORTEN -->
        <!-- ========================================== -->
        <h2>Antworten</h2>
        <?php if ($sqlReturnAntwort->num_rows > 0): ?>
            <?php while ($row = $sqlReturnAntwort->fetch_assoc()): ?>
                <?php if (isset($_SESSION['answerID']) && $_SESSION['answerID'] === $row['BeitragID']): ?>
                    <div class="card mb-3 w-100">
                        <div class="row g-0">
                            <div class="col-md-3 ms-3 mt-3">
                                <h4><?= htmlspecialchars($row['Benutzername']) ?><small class="text-muted fs-6">
                                        <?= htmlspecialchars($row['Berechtigung']) ?></small></h4>
                                <p>Last update: <?= date("d.m.Y H:i", strtotime($row['Erstellungsdatum'])) ?></p>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <form method="post" class="m-3">
                                        <div class="mb-4">
                                            <textarea class="form-control" rows="5" id="AntwortEdit"
                                                name="AntwortEdit"><?= $row['Textinhalt'] ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="btnEditAnswerSave"
                                            value="<?= $row['BeitragID'] ?>">Speichern</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card mb-3 w-100">
                        <?php if ($row['NutzerID'] === $_SESSION['userID'] || $rollenID === 2): ?>
                            <div class="position-absolute top-0 end-0 d-flex">
                                <form method="post" class="py-3 pe-1">
                                    <button type="submit" name="btnEditAnswer" class="btn-delete-icon" aria-label="Bearbeiten"
                                        value="<?= $row['BeitragID'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </form>
                                <form method="post" class="py-3 pe-3"
                                    onsubmit="return confirm('Möchtest du diesen Beitrag wirklich löschen?');">
                                    <button type="submit" name="btnDeleteAnswer" class="btn-delete-icon" aria-label="Löschen"
                                        value="<?= $row['BeitragID'] ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        <div class="row g-0">
                            <div class="col-md-3 ms-3 mt-3">
                                <h4><?= htmlspecialchars($row['Benutzername']) ?><small class="text-muted fs-6">
                                        <?= htmlspecialchars($row['Berechtigung']) ?></small></h4>
                                <p>Last update: <?= date("d.m.Y H:i", strtotime($row['Erstellungsdatum'])) ?></p>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <p class="card-text"><?= $row['Textinhalt'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card mb-3">
                <p class="text-center mb-2 mt-2">Bisher gibt es keine Antworten.</p>
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- BEREICH: NEUE ANTWORT VERFASSEN -->
        <!-- ========================================== -->
        <?php if (isset($_SESSION['userID'])): ?>
            <div class="card w-100">
                <div class="card-body">
                    <h5 class="card-title">Antworte auf diese Frage</h5>
                    <div class="card-text">
                        <form method="post" class="m-3">
                            <div class="mb-4">
                                <textarea class="form-control" rows="5" id="Antwort" name="Antwort"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="btnAntwort">Antworten</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card w-100">
                <div class="card-body">
                    <h5 class="card-title">Um auf Fragen zu Antworten musst du dich Anmelden</h5>
                    <div class="card-text">
                        <a class="btn btn-primary" href="login.php" role="button">Login</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>