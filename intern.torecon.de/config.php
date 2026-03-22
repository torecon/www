<?php
// ─── torecon – Zugangsdaten Interner Bereich ───────────────────────────────
// Diese Datei direkt in Plesk File Manager bearbeiten, um Zugangsdaten zu ändern.
// Die Datei wird NICHT an den Browser ausgeliefert – nur der Server liest sie.

define('ADMIN_USER',     'thomas');
define('ADMIN_PASSWORD', 'torecon2026!');

// Claude API Key für LinkedIn-Post-Generierung
// Key erstellen unter: https://console.anthropic.com/settings/keys
define('CLAUDE_API_KEY', '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');
define('SITE_TOPICS_JS_PATH', '/var/www/vhosts/torecon.de/html/js/topics.js');