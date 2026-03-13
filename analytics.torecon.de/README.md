# analytics.torecon.de

DSGVO-konformes Self-Hosted Analytics mit Matomo + PHP-Session-Auth (gleiche Technik wie intern.torecon.de).

## Struktur

```
analytics.torecon.de/
├── login.php        ← Auth-Gate (gleicher Stil wie intern)
├── index.php        ← Nach Login: Auto-Redirect zu Matomo
├── check_auth.php   ← Session-Check (in jede geschützte Seite einbinden)
├── logout.php       ← Session beenden
├── config.php       ← Zugangsdaten + Matomo Auth-Token
└── matomo/          ← Matomo-Installation (manuell hochladen)
```

## Ersteinrichtung auf dem Server

### 1. Matomo herunterladen und hochladen
1. Matomo ZIP herunterladen: https://matomo.org/download/
2. ZIP entpacken → Ordner in `matomo/` umbenennen
3. Via Plesk File Manager nach `analytics.torecon.de/matomo/` hochladen

### 2. MySQL-Datenbank in Plesk anlegen
- Plesk → Datenbanken → Datenbank hinzufügen
- Name z.B. `matomo_db`, Benutzer `matomo_user`
- Zugangsdaten notieren

### 3. Matomo-Installer durchlaufen
- `https://analytics.torecon.de/matomo/` aufrufen
- Datenbankdaten eingeben
- Admin-Konto erstellen (Username: `thomas`, Passwort: `torecon2026!`)
- torecon.de als erste Website eintragen

### 4. Tracking-Code in alle HTML-Seiten einbauen
Nach der Installation zeigt Matomo den Tracking-Code. Diesen in alle `.html`-Seiten von torecon.de vor `</body>` einfügen.

### 5. Auth-Token konfigurieren (für Single-Login)
1. In Matomo einloggen → Einstellungen → Persönlich → Sicherheit
2. Auth-Token erstellen → kopieren
3. In `config.php` bei `MATOMO_TOKEN` eintragen
4. Ab jetzt: Login auf analytics.torecon.de → direkt ins Dashboard (kein zweites Login)

## Zugangsdaten
Gleich wie intern.torecon.de – in `config.php` gepflegt.
