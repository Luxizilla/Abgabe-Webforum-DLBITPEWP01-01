<?php
// --- INITIALISIERUNG ---
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

// Standard-Tab beim ersten Aufruf der Seite
$aktiverTab = "login";

// ==========================================
// REGISTRIERUNG VERARBEITEN
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['RegEmail'])) {
    // Tab auf "register" setzen, damit bei Fehlern das richtige Formular offen bleibt
    $aktiverTab = "register";
    $benutzer = $_POST['Benutzername'];
    $email = $_POST['RegEmail'];
    $passwordRaw = $_POST['Password'];
    $vorname = $_POST['Vorname'];
    $nachname = $_POST['Nachname'];
    $errors = [];

    //Password Hashen
    $passwordHash = password_hash($passwordRaw, PASSWORD_DEFAULT);

    //E-Mail Adresse auf Dopplungen prüfen
    $emailCheck = $con->prepare("SELECT * FROM Nutzer WHERE Email= ? ");
    $emailCheck->bind_param("s", $email);
    $emailCheck->execute();
    $emailCheckResult = $emailCheck->get_result();

    //Benutzername auf Dopplungen prüfen
    $benutzerCheck = $con->prepare("SELECT * FROM Nutzer WHERE Benutzername=? ");
    $benutzerCheck->bind_param("s", $benutzer);
    $benutzerCheck->execute();
    $benutzerCheckResult = $benutzerCheck->get_result();

    if ($emailCheckResult->num_rows > 0) {
        $errors['email'] = "Die eingegebene Email-Adrresse: " . htmlspecialchars($email) . " ist schon Registriert.";
    } elseif ($benutzerCheckResult->num_rows > 0) {
        $errors['benutzername'] = "Der eingegebene Benutzername: " . htmlspecialchars($benutzer) . " ist schon vergeben.";
    } else {

        //Registrierungsstatement Vorbereiten
        $sqlRegistrieren = $con->prepare("INSERT INTO Nutzer (Benutzername, Email, PasswordHash, Vorname, Nachname, RollenID) 
            VALUES (?, ?, ?, ?, ?, 1)");
        $sqlRegistrieren->bind_param("sssss", $benutzer, $email, $passwordHash, $vorname, $nachname);

        if ($sqlRegistrieren->execute()) {
            $_SESSION['meldung'] = "<div class='alert alert-success mb-3'>Registrierung erfolgreich!</div>";
            $aktiverTab = "login"; // Nach Erfolg zurück zum Login springen
            header("Location: login.php");
            exit();
        } else {
            $meldung = "<div class='alert alert-danger mt-3'>Fehler: " . $con->error . "</div>";
        }

    }
}

// ==========================================
// LOGIN VERARBEITEN
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['LoginEmail'])) {
    $loginEmail = $_POST['LoginEmail'];
    $loginPasswortRaw = $_POST['LoginPassword'];

    //Password Check
    $loginPasswordCheck = $con->prepare("SELECT * FROM Nutzer WHERE Email= ? ");
    $loginPasswordCheck->bind_param("s", $loginEmail);
    $loginPasswordCheck->execute();
    $serverReturn = $loginPasswordCheck->get_result();


    if ($serverReturn->num_rows > 0) {
        $serverReturnAs = $serverReturn->fetch_assoc();
        if (password_verify($loginPasswortRaw, $serverReturnAs['PasswordHash'])) {
            $_SESSION['userID'] = $serverReturnAs['NutzerID'];
            $_SESSION['Benutzername'] = $serverReturnAs['Benutzername'];
            $_SESSION['eingeloggt'] = true;
            header("Location: index.php");
            exit();
        } else {
            $errors['loginPassword'] = "Das eingegebene Passwort ist falsch.";
        }
    } else {
        $errors['loginEmail'] = "Die eingegebene Email-Adrresse: " . htmlspecialchars($loginEmail) . " wurde nicht gefunden";
    }

}

$con->close();
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
                    <a class="nav-link active" href="login.php">Anmelden</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- HAUPTINHALT (Zentrierter Login-Bereich) -->
    <!-- ========================================== -->
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div style="width: 40rem;">
            <!-- System-Meldungen (z.B. erfolgreiche Registrierung oder Fehler) -->
            <?php
            if (isset($_SESSION['meldung'])) {
                echo $_SESSION['meldung'];
                unset($_SESSION['meldung']); // Sofort wieder löschen, damit sie nicht ewig stehen bleibt
            }

            if (isset($meldung)) {
                echo $meldung;
            }
            ?>
            <div class="card container">
                <div class="card-body">
                    <!-- TAB-STEUERUNG (Buttons für Login/Registrieren) -->
                    <div class="card-title">
                        <div class="nav nav-tabs justify-content-center nav-justified" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-login-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-login" type="button" role="tab" aria-controls="nav-login"
                                aria-selected="true">Login</button>
                            <button class="nav-link" id="nav-registrieren-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-registrieren" type="button" role="tab"
                                aria-controls="nav-registrieren" aria-selected="false">Registrieren</button>
                        </div>
                    </div>
                    <div class="card-subtitle mb-2 text-body-secondary">
                        <div class="tab-content" id="nav-tabContent">
                            <!-- ============================== -->
                            <!-- TAB-INHALT: LOGIN              -->
                            <!-- ============================== -->
                            <div class="tab-pane fade <?php echo ($aktiverTab == 'login') ? 'show active' : ''; ?>"
                                id="nav-login" role="tabpanel" aria-labelledby="nav-login-tab">
                                <form method="post" class="m-3 needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="exampleInputEmail1" class="form-label">E-Mail Adresse</label>
                                        <input type="email"
                                            class="form-control <?php echo isset($errors['loginEmail']) ? 'is-invalid' : ''; ?>"
                                            id="LoginEmail" name="LoginEmail">
                                        <?php if (isset($errors['loginEmail'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo $errors['loginEmail'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-4">
                                        <label for="exampleInputPassword1" class="form-label">Password</label>
                                        <input type="password"
                                            class="form-control <?php echo isset($errors['loginPassword']) ? 'is-invalid' : ''; ?>"
                                            id="LoginPassword" name="LoginPassword">
                                        <?php if (isset($errors['loginPassword'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo $errors['loginPassword']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Anmelden</button>
                                </form>
                            </div>
                            
                            <!-- ============================== -->
                            <!-- TAB-INHALT: REGISTRIEREN       -->
                            <!-- ============================== -->
                            <div class="tab-pane fade <?php echo ($aktiverTab == 'register') ? 'show active' : ''; ?>"
                                id="nav-registrieren" role="tabpanel" aria-labelledby="nav-registrieren-tab">
                                <form method="post" class="m-3 needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="Vorname" class="form-label">Vorname</label>
                                        <input type="text" class="form-control" id="Vorname" name="Vorname" required>
                                        <div class="invalid-feedback">Bitte gib deinen Vornamen an.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="Nachname" class="form-label">Nachname</label>
                                        <input type="text" class="form-control" id="Nachname" name="Nachname" required>
                                        <div class="invalid-feedback">Bitte gib deinen Nachnamen an.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="Benutzername" class="form-label">Benutzername</label>
                                        <input type="text" class="form-control <?php
                                        if (isset($errors['benutzername'])) {
                                            echo 'is-invalid';
                                    } elseif (isset($_POST['Benutzername']) && !isset($errors['benutzername'])) {
                                            echo 'is-valid';
                                        }
                                        ?>" id="Benutzername" name="Benutzername" required minlength="4" maxlength="20">
                                        <?php if (isset($errors['benutzername'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['benutzername'] ?></div>
                                        <?php endif; ?>
                                        <div class="col-auto">
                                            <span id="passwordHelpInline" class="form-text">
                                                Benutzername muss mindestens 4 und maximal 20 Zeichen lang sein.
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="RegEmail" class="form-label">E-Mail Adresse</label>
                                        <input type="email" class="form-control <?php
                                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                            if (isset($errors['email'])) {
                                                echo 'is-invalid';
                                            } elseif (!empty($_POST['RegEmail'])) {
                                                echo 'is-valid';
                                            }
                                        }
                                        ?>" id="RegEmail" name="RegEmail" required>
                                        <?php if (isset($errors['email'])): ?>
                                            <div class="invalid-feedback"><?php echo $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="Password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="Password" name="Password"
                                            required>
                                        <div class="invalid-feedback">Mindestens 8 Zeichen erforderlich.</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="PasswordWd" class="form-label">Password
                                            wiederholen</label>
                                        <input type="password" class="form-control" id="PasswordWd" name="PasswordWd"
                                            required>
                                        <div class="invalid-feedback">Passwörter müssen übereinstimmen.</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="regButton">Registrieren</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>