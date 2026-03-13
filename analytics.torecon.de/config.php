<?php
// ─── analytics.torecon.de – Zugangsdaten ────────────────────────────────────
// Diese Datei direkt in Plesk File Manager bearbeiten.
// Wird NICHT an den Browser ausgeliefert – nur der Server liest sie.

define('ADMIN_USER',     'thomas');
define('ADMIN_PASSWORD', 'torecon2026!');

// Matomo Auth-Token: nach Matomo-Installation ausfüllen
// Matomo → Einstellungen → Persönlich → Sicherheit → Auth-Token erstellen
define('MATOMO_TOKEN', '');  // <-- hier den Token eintragen

// Matomo-URL (relativer Pfad im gleichen Verzeichnis)
define('MATOMO_URL', '/matomo/index.php');
