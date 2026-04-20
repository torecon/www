# Website deployen per FTP

## Befehl zum Kopieren:

```
Bitte deploye die torecon-Website per FTP auf den Server.

Script: ftp_deploy.py

Ich benötige folgende Informationen von dir:
- FTP_HOST: der FTP-Hostname (z.B. ftp.torecon.de oder IP-Adresse)
- FTP_USER: der FTP-Benutzername
- FTP_PASS: das FTP-Passwort
- FTP_PORT: (optional, Standard: 21)

Prüfe außerdem, ob die Remote-Pfade für die Subdomains korrekt sind.
Die Standardwerte im Script sind:
  - torecon.de       → /html
  - intern.torecon.de → /subdomains/intern/html
  - analytics.torecon.de → /subdomains/analytics/html

Falls die Pfade in Plesk anders lauten, teile mir die korrekten Pfade mit.
Dann führe das Deploy für alle Seiten aus:

  FTP_HOST=... FTP_USER=... FTP_PASS=... python3 ftp_deploy.py

Oder nur für eine bestimmte Subdomain:

  FTP_HOST=... FTP_USER=... FTP_PASS=... python3 ftp_deploy.py --site intern
```

---

## Verfügbare Sites

| Name           | Lokaler Ordner            | Ziel auf Server                   |
|----------------|---------------------------|-----------------------------------|
| main           | torecon.de/               | /html                             |
| intern         | intern.torecon.de/        | /subdomains/intern/html           |
| analytics      | analytics.torecon.de/     | /subdomains/analytics/html        |
| downloads      | downloads.torecon.de/     | /subdomains/downloads/html        |
| getbrainbites  | getbrainbites.ai/         | /getbrainbites.ai                 |
| getthinkless   | getthinkless.ai/          | /getthinkless.ai                  |
| meisterlich    | meisterlich.torecon.de/   | /subdomains/meisterlich/html      |

`analytics` wird automatisch übersprungen, solange dort nur die README liegt.

### downloads.torecon.de – Besonderheit
DMG-Dateien werden **nicht** per FTP-Script deployed (zu groß).
Stattdessen: manuell per Plesk File Manager oder SFTP hochladen.
Das Script deployed nur `.htaccess` und `eladung/latest.json`.

---

## Nach einem Serverwechsel (PHP-Version / Hosting-Stack)

### intern.torecon.de – Pflichtschritte

| # | Schritt | Details |
|---|---------|---------|
| 1 | **config.php anlegen** | `cp intern.torecon.de/config.php.example .../intern/html/config.php` – dann Werte anpassen: `ADMIN_USER`, `ADMIN_PASSWORD`, `CLAUDE_API_KEY` |
| 2 | **Schreibrechte prüfen** | JSON-Dateien (`linkedin_drafts.json`, `linkedin_backup.json`, `topics_settings.json`, `linkedin_settings.json`) müssen vom Webserver beschreibbar sein |
| 3 | **PHP-FPM vs. mod_php** | Bei PHP-FPM (Standard seit PHP 7+): `.user.ini` wird automatisch gelesen. Bei mod_php: `.htaccess` greift. Beides ist jetzt im Repo vorhanden. |

> **Symptom HTTP 500 nach Serverwechsel:** Ursache war `php_value` in `.htaccess` – funktioniert nur mit mod_php, nicht mit PHP-FPM. Behoben durch `<IfModule>`-Guards + `.user.ini`.

---

## Pre-Deployment Checkliste

Vor jedem Deploy prüfen:

| # | Prüfpunkt | Details |
|---|-----------|---------|
| 1 | **.htaccess vorhanden** | `torecon.de/.htaccess` muss existieren. Enthält: HTTP→HTTPS (301) + non-www→www (301) + Security Headers |
| 2 | **Canonical Tags korrekt** | Alle HTML-Seiten: `<link rel="canonical" href="https://www.torecon.de/...">` |
| 3 | **robots.txt vorhanden** | `torecon.de/robots.txt` prüfen |
| 4 | **sitemap.xml aktuell** | Neue Seiten ergänzt? Datum aktualisiert? |
| 5 | **Browser-Cache leeren** | Nach Deploy: Strg+Shift+R |

> **Hintergrund .htaccess:** Google Search Console meldet "Alternative Seite mit richtigem kanonischen Tag", wenn die Seite über http:// oder ohne www erreichbar ist. Die .htaccess-Weiterleitungen verhindern das.

---

## Hinweise

- Credentials niemals in den Code oder ins Git-Repository schreiben
- Die Remote-Pfade für Subdomains in Plesk können abweichen – ggf. anpassen
- Nach dem Deploy: Browser-Cache leeren (Strg+Shift+R) zum Testen
