-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 08. Jun 2026 um 10:27
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `Webforum`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Beitrag`
--

CREATE TABLE `Beitrag` (
  `BeitragID` int(10) UNSIGNED NOT NULL,
  `Erstellungsdatum` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Titel` varchar(100) NOT NULL,
  `Textinhalt` varchar(10000) NOT NULL,
  `ParentID` int(10) UNSIGNED DEFAULT NULL,
  `NutzerID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Beitrag`
--

INSERT INTO `Beitrag` (`BeitragID`, `Erstellungsdatum`, `Titel`, `Textinhalt`, `ParentID`, `NutzerID`) VALUES
(32, '2026-05-19 15:33:50', 'Wie fange ich am besten mit PHP an?', '<p>Hallo zusammen,</p>\r\n<p>ich m&ouml;chte f&uuml;r ein Uni-Projekt PHP lernen, bin aber absoluter Anf&auml;nger in der Webentwicklung.</p>\r\n<p>Habt ihr Tipps f&uuml;r gute Tutorials oder B&uuml;cher?</p>\r\n<p>Sollte ich direkt mit einem Framework wie Laravel starten oder erst mal klein anfangen?</p>', NULL, 11),
(33, '2026-05-19 15:38:07', 'Wie fange ich am besten mit PHP an?', '<p>Hey!</p>\r\n<p>Ich w&uuml;rde dir raten, zuerst die Grundlagen von <strong>\"Vanilla PHP\"</strong> zu lernen (Variablen, Schleifen, PDO f&uuml;r Datenbanken).</p>\r\n<p>Wenn du das verstanden hast, macht Laravel viel mehr Sinn!</p>', 32, 13),
(34, '2026-05-19 15:38:59', 'Welcher Laptop für das Informatik-Studium?', '<p>Mein alter Laptop gibt langsam den Geist auf.</p>\n<p>Brauche ich für das Studium wirklich ein teures MacBook, wie oft behauptet wird, oder reicht ein Windows/Linux-Gerät für ca. 600€?</p>', NULL, 13),
(35, '2026-05-19 15:42:11', 'Erste Gehversuche bei C_Programmierung', '<p>Guten Abend,<br /><br />ich beschäftige mich erstmals mir der C-Programmierung.<br /><br />Hintergrund:<br /><br />ich errichte gerade ein Netzwerk (Ethernet TCP) bestehend aus mehreren Siemens Logos und einem HMI (Bedienpanel) der Fa. ACE.<br /><br />Den ersten Teil habe ich soweit fertig. Allerdings ist die Bedienung vom HMI aus schon etwas langsamer geworden. Das liegt sicher daran, dass für jeden Fließeffekt einer Rohrleitung jeweil ein Analogwert (Wert 8) übertragen werden muss. Weil noch einiges zu Programmieren ist möchte ich das Datenvolumen gleich zu Beginn minimieren.<br /><br />Hierzu möchte ich Makrofunktionen des HMIs nutzen, die in C-Sprache programmiert werden. Auf diesem Gebiet bin ich Neuling.<br /><br />Der jeweilige Schritt einer Schrittkette (1 Datenwort) wird als Text auf dem HMI angezeigt. Aus dieser Information heraus möchte ich den größten Teil der Visualisierung abbilden. Mir ist ein bereits erstes Makro (siehe Anhang) gelungen und es werden die Klappenstellungen und Fließeffekte einwandfrei angezeigt. Füge ich jedoch ein weiteres Makro hinzu, treten jedoch Störungen auf. Es wird mal mehr oder zu wenig angezeigt.<br /><br />Ich habe schon diverse Versuche gestartet, bisher ohne Erfolgt. Für einen Hinweis wäre ich sehr dankbar</p>', NULL, 14),
(36, '2026-05-19 15:44:10', 'Welcher Laptop für das Informatik-Studium?', '<p data-path-to-node=\"3,1\">Hey! Lass dir blo&szlig; nicht einreden, dass man f&uuml;r ein Informatik-Studium zwingend ein MacBook f&uuml;r 1.500&euro;+ braucht. Das ist ein absoluter Mythos.</p>\r\n<p data-path-to-node=\"3,2\">Im Studium tippst du Code (das geht auch auf einem 200&euro; Handtuch), l&auml;sst ein paar Skripte laufen und nutzt Kommandozeilen-Tools. Ein Windows-Laptop f&uuml;r rund 600&euro; ist daf&uuml;r absolut ausreichend. Mein Tipp: Achte beim Kauf vor allem auf zwei Dinge:</p>\r\n<ul data-path-to-node=\"3,3\">\r\n<li>\r\n<p data-path-to-node=\"3,3,0,0\"><strong data-path-to-node=\"3,3,0,0\" data-index-in-node=\"0\">Mindestens 16 GB RAM:</strong> Das ist das absolute Minimum heute. Wenn du sp&auml;ter mal Docker-Container oder eine virtuelle Maschine (VM) laufen l&auml;sst, zieht das ordentlich Arbeitsspeicher.</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"3,3,1,0\"><strong data-path-to-node=\"3,3,1,0\" data-index-in-node=\"0\">Ein guter Prozessor:</strong> Ein AMD Ryzen 5/7 oder Intel Core i5/i7 der neueren Generationen reicht dicke.</p>\r\n</li>\r\n</ul>\r\n<p data-path-to-node=\"3,4\"><strong data-path-to-node=\"3,4\" data-index-in-node=\"0\">Der Linux-Bonus:</strong> Auf einem 600&euro; Windows-Notebook kannst du wunderbar Linux (z. B. Ubuntu oder Mint) im Dual-Boot oder via WSL2 (Windows-Subsystem f&uuml;r Linux) installieren. Da die meisten Unis im Bereich Betriebssysteme und Netzwerke ohnehin auf Linux setzen, lernst du so direkt das System kennen, das sp&auml;ter auch auf den meisten Servern weltweit l&auml;uft.</p>\r\n<p data-path-to-node=\"3,5\">Spar dir das Geld f&uuml;r teuren Apple-Luxus und steck es lieber in einen guten, gro&szlig;en Monitor f&uuml;r deinen Schreibtisch zu Hause &ndash; deine Augen werden es dir beim n&auml;chtlichen Debuggen danken!</p>', 34, 14),
(37, '2026-05-19 15:45:14', 'Wie fange ich am besten mit PHP an?', '<p data-path-to-node=\"3,1\">Willkommen in der Webentwicklung! Die wichtigste Antwort zuerst: <strong data-path-to-node=\"3,1\" data-index-in-node=\"65\">Bitte fang erst mal ganz klein an und lass Laravel links liegen.</strong></p>\r\n<p data-path-to-node=\"3,2\">Frameworks wie Laravel sind gro&szlig;artig, aber sie nehmen dir extrem viel Arbeit ab und verstecken die Magie im Hintergrund. Wenn du die Grundlagen von PHP (Variablen, Schleifen, Funktionen) und das Zusammenspiel mit HTML/CSS nicht verstehst, wirst du bei Fehlern in Laravel komplett aufgeschmissen sein. Au&szlig;erdem verlangt Laravel direkt Verst&auml;ndnis von Konzepten wie MVC (Model-View-Controller) oder dem Composer (Paketmanager) &ndash; das &uuml;berrollt dich als Anf&auml;nger komplett.</p>\r\n<p data-path-to-node=\"3,3\"><strong data-path-to-node=\"3,3\" data-index-in-node=\"0\">Mein Fahrplan f&uuml;r dich:</strong></p>\r\n<ol start=\"1\" data-path-to-node=\"3,4\">\r\n<li>\r\n<p data-path-to-node=\"3,4,0,0\">Schau dir die Grundlagen von HTML und CSS an (falls du das noch nicht kannst). PHP erzeugt am Ende n&auml;mlich nur HTML-Code f&uuml;r den Browser.</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"3,4,1,0\">Lerne \"Vanilla PHP\" (also reines PHP ohne Frameworks).</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"3,4,2,0\">Baue ein Mini-Projekt: Ein einfaches Kontaktformular, das Daten per Mail sendet, oder ein primitives G&auml;stebuch mit einer MySQL-Datenbank.</p>\r\n</li>\r\n</ol>\r\n<p data-path-to-node=\"3,5\"><strong data-path-to-node=\"3,5\" data-index-in-node=\"0\">Ressourcen-Tipp:</strong> F&uuml;r den Einstieg ist die Videoreihe von <strong data-path-to-node=\"3,5\" data-index-in-node=\"57\">Traversy Media</strong> (YouTube, meist auf Englisch, aber super verst&auml;ndlich) genial. Wenn es Deutsch sein soll, schau mal bei <strong data-path-to-node=\"3,5\" data-index-in-node=\"176\">Programmieren Starten</strong> oder <strong data-path-to-node=\"3,5\" data-index-in-node=\"203\">Peter Kropff</strong> vorbei. Viel Erfolg beim Uni-Projekt!</p>', 32, 14),
(38, '2026-05-19 15:47:09', 'Welcher Laptop für das Informatik-Studium?', '<p data-path-to-node=\"6,1\">Hi, die kurze Antwort: Nein, du brauchst definitiv kein teures MacBook. Ein Windows- oder Linux-Ger&auml;t reicht v&ouml;llig aus. Allerdings solltest du bei einem Budget von 600&euro; genau hinschauen, <em data-path-to-node=\"6,1\" data-index-in-node=\"188\">was</em> du kaufst.</p>\r\n<p data-path-to-node=\"6,2\">Wenn du ein brandneues 600&euro;-Notebook im Elektromarkt kaufst, kriegst du zwar oft gute Hardware-Innereien, aber das Geh&auml;use, das Display und vor allem die Tastatur sind oft billiges Plastik. Und glaub mir: Im Info-Studium tippst du <em data-path-to-node=\"6,2\" data-index-in-node=\"231\">viel</em>. Eine schlechte Tastatur nervt nach zwei Wochen extrem. Zudem willst du kein Ger&auml;t, dessen Akku in der Vorlesung nach 2 Stunden schlappmacht oder dessen L&uuml;fter in der ruhigen Bibliothek wie ein F&ouml;hn klingt.</p>\r\n<p data-path-to-node=\"6,3\"><strong data-path-to-node=\"6,3\" data-index-in-node=\"0\">Mein Geheimtipp f&uuml;r Informatik-Studenten:</strong> Schau nach <strong data-path-to-node=\"6,3\" data-index-in-node=\"53\">refurbished (general&uuml;berholten) Business-Laptops</strong>. F&uuml;r 500&ndash;600&euro; bekommst du erstklassige, gebrauchte Leasing-R&uuml;ckl&auml;ufer aus der Firmenwelt, zum Beispiel:</p>\r\n<ul data-path-to-node=\"6,4\">\r\n<li>\r\n<p data-path-to-node=\"6,4,0,0\"><strong data-path-to-node=\"6,4,0,0\" data-index-in-node=\"0\">Lenovo ThinkPad</strong> (T- oder X-Serie, z. B. T14)</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"6,4,1,0\"><strong data-path-to-node=\"6,4,1,0\" data-index-in-node=\"0\">HP EliteBook</strong></p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"6,4,2,0\"><strong data-path-to-node=\"6,4,2,0\" data-index-in-node=\"0\">Dell Latitude</strong></p>\r\n</li>\r\n</ul>\r\n<p data-path-to-node=\"6,5\">Diese Ger&auml;te sind extrem robust, haben fantastische Tastaturen, lassen sich im Gegensatz zu MacBooks oft super einfach aufschrauben und reparieren (oder mit mehr RAM/SSD aufr&uuml;sten) und laufen perfekt mit Linux und Windows.</p>', 34, 15),
(39, '2026-05-19 15:47:40', 'Wie fange ich am besten mit PHP an?', '<p data-path-to-node=\"6,1\">Hi! PHP ist f&uuml;r den Einstieg in die Webentwicklung super dankbar, weil man extrem schnell erste Erfolge sieht. Statt dicke B&uuml;cher zu w&auml;lzen, w&uuml;rde ich dir empfehlen, direkt mit interaktiven Kursen oder Video-Tutorials zu starten, wo du parallel mitcodest.</p>\r\n<p data-path-to-node=\"6,2\">Hier sind die meiner Meinung nach besten Anlaufstellen f&uuml;r Einsteiger:</p>\r\n<ul data-path-to-node=\"6,3\">\r\n<li>\r\n<p data-path-to-node=\"6,3,0,0\"><strong data-path-to-node=\"6,3,0,0\" data-index-in-node=\"0\">Laracasts (Website):</strong> Lass dich vom Namen nicht t&auml;uschen. Ja, das ist die Plattform von Laravel, aber es gibt dort die Tutorial-Reihe <strong data-path-to-node=\"6,3,0,0\" data-index-in-node=\"133\">\"PHP for Beginners\"</strong>. Die ist absolut kostenlos, unfassbar professionell produziert und erkl&auml;rt PHP von der Pike auf &ndash; inklusive der Frage, wie man einen lokalen Server aufsetzt. F&uuml;r mich der Goldstandard!</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"6,3,1,0\"><strong data-path-to-node=\"6,3,1,0\" data-index-in-node=\"0\">The Net Ninja (YouTube):</strong> Er hat eine tolle \"PHP Tutorial for Beginners\"-Playlist. Er baut dort Schritt f&uuml;r Schritt ein kleines Projekt (z.B. eine Pizza-Bestellseite) auf. Das motiviert mega.</p>\r\n</li>\r\n<li>\r\n<p data-path-to-node=\"6,3,2,0\"><strong data-path-to-node=\"6,3,2,0\" data-index-in-node=\"0\">PHP.net (Offizielle Doku):</strong> Nutze die offizielle Dokumentation nicht als Lehrbuch, aber als Nachschlagewerk. Die User-Kommentare unter den einzelnen Funktionen sind oft Gold wert, weil dort reale Praxisbeispiele stehen.</p>\r\n</li>\r\n</ul>\r\n<p data-path-to-node=\"6,4\">Ein Buch brauchst du heutzutage eigentlich nicht mehr, die Online-Community rund um PHP ist gigantisch. Wenn du mal feststeckst: Stack Overflow oder der PHP-Subreddit helfen eigentlich immer.</p>', 32, 15),
(40, '2026-05-19 15:48:16', 'Erste Gehversuche bei C_Programmierung', '<p>Das Macro schreibt immer wen das LocalWord[73] !=1 ist alles auf 0<br>Wie sieht das n&auml;chste makro aus?<br>wen 1 etwas schreibt und das n&auml;chste mit anderen Werten gegenh&auml;lt passiert irgendetwas.</p>', 35, 15),
(41, '2026-05-21 13:15:47', 'das ist eine Frage von der Chefin', '<p>kannst du mir sagen, was es heute zum essen gibt.&nbsp;<br>Ich h&auml;tte hier gerne 3 Optionen zur Auswahl.</p>\r\n<p>&nbsp;</p>\r\n<p>Vielen Dank</p>', NULL, 16),
(42, '2026-05-21 12:52:22', 'Erste Gehversuche bei C_Programmierung', '<p>hier ist meine Antwprt zu deinem Thema.</p>\r\n<p>Viel Spa&szlig; damit&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>hier noch eine erg&auml;nzung</p>', 35, 16);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `BeitragKategorie`
--

CREATE TABLE `BeitragKategorie` (
  `BKategorieID` int(10) UNSIGNED NOT NULL,
  `ThemenID` int(10) UNSIGNED NOT NULL,
  `BeitragID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `BeitragKategorie`
--

INSERT INTO `BeitragKategorie` (`BKategorieID`, `ThemenID`, `BeitragID`) VALUES
(17, 1, 32),
(18, 6, 32),
(19, 10, 35),
(20, 9, 41);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Nutzer`
--

CREATE TABLE `Nutzer` (
  `NutzerID` int(10) UNSIGNED NOT NULL,
  `Benutzername` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Vorname` varchar(100) NOT NULL,
  `Nachname` varchar(100) NOT NULL,
  `RollenID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Nutzer`
--

INSERT INTO `Nutzer` (`NutzerID`, `Benutzername`, `Email`, `PasswordHash`, `Vorname`, `Nachname`, `RollenID`) VALUES
(11, 'luxizilla', 'lukas@test.de', '$2y$10$OZYSuk8okTUmSrqpk2Ufpe0L5xSp52HBBo7p7I/OB/gyD8gIvXqOq', 'Lukas', 'Mustermann', 2),
(13, 'Pixelmaster', 'Pixelmaster@gmail.com', '$2y$10$TCMqYYZ4j3/rlEqgv0I9Xue26QRhh2/NpQHlO8qz4Vi3gY5VmqMPC', 'Nadine', 'Mustermann', 1),
(14, 'MaMu12', 'mamu12@gmx.de', '$2y$10$H0xf37AHSdiYNEzyDIZoAu1FFGZmY2/utAbLNOa8Gva27V34A8Hqy', 'Maxi', 'Mustermann', 1),
(15, 'Daniblue', 'daniblue@gmx.de', '$2y$10$0BubYkcpKqPa5mVTVeBFZ.9MIZzFJFEQPm/pB7rw8s87bWP5COdqK', 'Daniela ', 'Mayer', 1),
(16, 'NVettl (gelöscht)', 'deleted_16@webforum.local', '', '', '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Rollen`
--

CREATE TABLE `Rollen` (
  `RollenID` int(11) UNSIGNED NOT NULL,
  `Bezeichnung` varchar(500) NOT NULL,
  `Berechtigung` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Rollen`
--

INSERT INTO `Rollen` (`RollenID`, `Bezeichnung`, `Berechtigung`) VALUES
(1, 'Lesen, Schreiben, Kommentieren, Bearbeiten (Eigener)', 'Member'),
(2, 'Lesen, Schreiben, Kommentieren, Löschen, Bearbeiten, Themen anlegen', 'Admin');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Thema`
--

CREATE TABLE `Thema` (
  `ThemenID` int(10) UNSIGNED NOT NULL,
  `NutzerID` int(10) UNSIGNED NOT NULL,
  `Kategorie` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Thema`
--

INSERT INTO `Thema` (`ThemenID`, `NutzerID`, `Kategorie`) VALUES
(1, 11, 'PHP'),
(5, 11, 'Java'),
(6, 11, 'HTML'),
(7, 11, 'Python'),
(8, 11, 'C#'),
(9, 11, 'C++'),
(10, 11, 'C'),
(11, 11, 'JavaScript'),
(12, 11, 'Swift');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Beitrag`
--
ALTER TABLE `Beitrag`
  ADD PRIMARY KEY (`BeitragID`),
  ADD UNIQUE KEY `BeitragID` (`BeitragID`,`Titel`,`ParentID`,`NutzerID`),
  ADD KEY `NutzerID` (`NutzerID`),
  ADD KEY `ParentID` (`ParentID`);

--
-- Indizes für die Tabelle `BeitragKategorie`
--
ALTER TABLE `BeitragKategorie`
  ADD PRIMARY KEY (`BKategorieID`),
  ADD UNIQUE KEY `BKategorieID` (`BKategorieID`,`ThemenID`,`BeitragID`),
  ADD KEY `ThemenID` (`ThemenID`),
  ADD KEY `beitragkategorie_ibfk_2` (`BeitragID`);

--
-- Indizes für die Tabelle `Nutzer`
--
ALTER TABLE `Nutzer`
  ADD PRIMARY KEY (`NutzerID`),
  ADD UNIQUE KEY `Benutzername` (`Benutzername`,`Email`),
  ADD UNIQUE KEY `NutzerID` (`NutzerID`),
  ADD KEY `RollenID` (`RollenID`);

--
-- Indizes für die Tabelle `Rollen`
--
ALTER TABLE `Rollen`
  ADD PRIMARY KEY (`RollenID`),
  ADD UNIQUE KEY `RolleID` (`RollenID`);

--
-- Indizes für die Tabelle `Thema`
--
ALTER TABLE `Thema`
  ADD PRIMARY KEY (`ThemenID`),
  ADD UNIQUE KEY `ThemenID` (`ThemenID`),
  ADD KEY `thema_ibfk_1` (`NutzerID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Beitrag`
--
ALTER TABLE `Beitrag`
  MODIFY `BeitragID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT für Tabelle `BeitragKategorie`
--
ALTER TABLE `BeitragKategorie`
  MODIFY `BKategorieID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT für Tabelle `Nutzer`
--
ALTER TABLE `Nutzer`
  MODIFY `NutzerID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT für Tabelle `Rollen`
--
ALTER TABLE `Rollen`
  MODIFY `RollenID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `Thema`
--
ALTER TABLE `Thema`
  MODIFY `ThemenID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Beitrag`
--
ALTER TABLE `Beitrag`
  ADD CONSTRAINT `beitrag_ibfk_1` FOREIGN KEY (`NutzerID`) REFERENCES `Nutzer` (`NutzerID`),
  ADD CONSTRAINT `beitrag_ibfk_2` FOREIGN KEY (`ParentID`) REFERENCES `Beitrag` (`BeitragID`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `BeitragKategorie`
--
ALTER TABLE `BeitragKategorie`
  ADD CONSTRAINT `beitragkategorie_ibfk_1` FOREIGN KEY (`ThemenID`) REFERENCES `Thema` (`ThemenID`),
  ADD CONSTRAINT `beitragkategorie_ibfk_2` FOREIGN KEY (`BeitragID`) REFERENCES `Beitrag` (`BeitragID`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `Nutzer`
--
ALTER TABLE `Nutzer`
  ADD CONSTRAINT `nutzer_ibfk_1` FOREIGN KEY (`RollenID`) REFERENCES `Rollen` (`RollenID`);

--
-- Constraints der Tabelle `Thema`
--
ALTER TABLE `Thema`
  ADD CONSTRAINT `thema_ibfk_1` FOREIGN KEY (`NutzerID`) REFERENCES `Nutzer` (`NutzerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
