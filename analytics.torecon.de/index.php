<?php
require_once dirname(__FILE__) . '/check_auth.php';
require_once dirname(__FILE__) . '/config.php';

// Wenn Token konfiguriert ist: direkt zu Matomo mit Auto-Login weiterleiten
if (MATOMO_TOKEN !== '') {
    $matomo = 'https://analytics.torecon.de/matomo/index.php'
        . '?module=CoreHome&action=index&idSite=1&period=day&date=today'
        . '&token_auth=' . urlencode(MATOMO_TOKEN);
    header('Location: ' . $matomo);
    exit;
}

// Fallback: Matomo noch nicht konfiguriert – Hinweisseite zeigen
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .setup-card {
      max-width: 560px;
      margin: 80px auto;
      padding: 40px;
      background: var(--surface);
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.08);
      text-align: center;
    }
    .setup-card h2 { margin-bottom: 12px; }
    .setup-card p { color: var(--text-secondary); margin-bottom: 24px; }
    .setup-steps {
      text-align: left;
      background: var(--bg);
      border-radius: 10px;
      padding: 20px 24px;
      font-size: 14px;
      line-height: 1.8;
    }
    .setup-steps code {
      background: rgba(0,113,227,0.08);
      padding: 2px 6px;
      border-radius: 4px;
      font-family: monospace;
    }
  </style>
</head>
<body>
<script>window.TORECON_ROOT = 'https://www.torecon.de/';</script>

<div class="setup-card">
  <span class="login-logo">tore<span>con</span></span>
  <h2>Matomo noch nicht konfiguriert</h2>
  <p>Matomo ist installiert aber der Auth-Token fehlt noch in <code>config.php</code>.</p>

  <div class="setup-steps">
    <strong>Nächste Schritte:</strong><br>
    1. Matomo öffnen: <a href="/matomo/" target="_blank">analytics.torecon.de/matomo/</a><br>
    2. Einstellungen → Persönlich → Sicherheit<br>
    3. Auth-Token erstellen und kopieren<br>
    4. In <code>config.php</code> bei <code>MATOMO_TOKEN</code> eintragen<br>
    5. Diese Seite neu laden – Auto-Login aktiv
  </div>

  <div style="margin-top:24px;">
    <a href="/matomo/" class="btn btn-primary">Matomo öffnen</a>
    &nbsp;
    <a href="logout.php" style="font-size:14px;color:var(--text-secondary);">Abmelden</a>
  </div>
</div>

<script src="https://www.torecon.de/js/i18n.js"></script>
</body>
</html>
