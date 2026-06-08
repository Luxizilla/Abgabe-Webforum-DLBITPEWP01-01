<?php
// ==========================================
// INITIALISIERUNG
// ==========================================
session_start();
include 'connection.php';

// ==========================================
// EINSTELLUNGEN & PAGINATION
// ==========================================
// --- Einstellungen ---
$limit = 10; // Beiträge pro Seite
$seite = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($seite < 1)
  $seite = 1;
$offset = ($seite - 1) * $limit;

// --- Gesamtanzahl der Beiträge ermitteln ---
$resultCount = $con->query("SELECT COUNT(*) AS total FROM Beitrag WHERE ParentID IS NULL");
$totalPosts = $resultCount->fetch_assoc()['total'];
$gesamtSeiten = ceil($totalPosts / $limit);

// ==========================================
// HAUPTABFRAGE (BEITRÄGE LADEN)
// ==========================================
$sqlBeitrag = $con->prepare("SELECT Main.BeitragID, Main.Titel, Main.Textinhalt, Main.Erstellungsdatum, Nutzer.Benutzername, COUNT(Antwort.BeitragID) AS AnzahlAntworten, COALESCE(MAX(Antwort.Erstellungsdatum), Main.Erstellungsdatum) AS LetzteAktivitaet
    FROM Beitrag AS Main
    JOIN Nutzer ON Main.NutzerID = Nutzer.NutzerID
    LEFT JOIN Beitrag AS Antwort ON Main.BeitragID = Antwort.ParentID
    WHERE Main.ParentID IS NULL 
    GROUP BY Main.BeitragID
    ORDER BY LetzteAktivitaet DESC
    LIMIT ? OFFSET ?
");

$sqlBeitrag->bind_param("ii", $limit, $offset);
$sqlBeitrag->execute();
$sqlReturn = $sqlBeitrag->get_result();
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
  <div class="container mt-5">
    
    <!-- HEADER BEREICH -->
    <h1>
      Forum Übersicht
    </h1>
    <div class="row">
      <div class="col">
        <p>
          Diskutiere mit der Community
        </p>
      </div>
      <div class="col text-end">
        <a type="button" class="btn btn-secondary btn-lg" href="neuerBeitrag.php">Frage Stellen</a>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- BEITRAGSÜBERSICHT                          -->
    <!-- ========================================== -->
    <section>
      <h1>
        Letzten Beiträge
      </h1>
      
      <!-- System-Meldungen -->
      <?php if (isset($_SESSION['meldung'])) {
        echo $_SESSION['meldung'];
        unset($_SESSION['meldung']);
      }
      ?>
      
      <!-- Beitrags-Tabelle -->
      <table class="table table-striped border border-3 border-dark">
        <thead class="border border-3 border-dark text-center">
          <tr>
            <th scope="col" style="width: 55%;">Beitrag</th>
            <th scope="col" style="width: 20%;">Autor</th>
            <th scope="col" style="width: 15%;">letzte Antwort</th>
            <th scope="col" style="width: 10%;">Antworten</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $sqlReturn->fetch_assoc()): ?>
            <tr>
              <td>
                <a href="beitrag.php?id=<?= $row['BeitragID'] ?>">
                  <div class="fw-bold"><?= htmlspecialchars($row['Titel']) ?></div>
                </a>
                <div class="text-muted small">
                <?= htmlspecialchars(mb_substr(html_entity_decode(strip_tags($row['Textinhalt']), ENT_QUOTES, 'UTF-8'), 0, 150, 'UTF-8')) ?> ...
                </div>
              </td>
              <td class="text-center"><?= htmlspecialchars($row['Benutzername']) ?></td>
              <td class="text-center"><?= date("d.m.Y H:i", strtotime($row['LetzteAktivitaet'])) ?></td>
              <td class="text-center"><?= $row['AnzahlAntworten'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= ($seite <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $seite - 1 ?>">Zurück</a>
          </li>
          <?php
          $radius = 2; // Wie viele Seiten links und rechts der aktuellen Seite gezeigt werden sollen
          for ($i = 1; $i <= $gesamtSeiten; $i++):
            // Zeige immer die erste Seite, die letzte Seite und alles im Radius um die aktuelle Seite
            if ($i == 1 || $i == $gesamtSeiten || ($i >= $seite - $radius && $i <= $seite + $radius)): ?>
              <li class="page-item <?= ($i == $seite) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
              <?php
              // Setze Pünktchen ein, wenn eine Lücke entsteht
            elseif ($i == $seite - $radius - 1 || $i == $seite + $radius + 1): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif;
          endfor; ?>
          <li class="page-item <?= ($seite >= $gesamtSeiten) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $seite + 1 ?>">Weiter</a>
          </li>
        </ul>
      </nav>
    </section>
  </div>
</body>

</html>