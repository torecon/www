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

| Name        | Lokaler Ordner          | Ziel auf Server                   |
|-------------|-------------------------|-----------------------------------|
| main        | torecon.de/             | /html                             |
| intern      | intern.torecon.de/      | /subdomains/intern/html           |
| analytics   | analytics.torecon.de/   | /subdomains/analytics/html        |

`analytics` wird automatisch übersprungen, solange dort nur die README liegt.

---

## Hinweise

- Credentials niemals in den Code oder ins Git-Repository schreiben
- Die Remote-Pfade für Subdomains in Plesk können abweichen – ggf. anpassen
- Nach dem Deploy: Browser-Cache leeren (Strg+Shift+R) zum Testen
