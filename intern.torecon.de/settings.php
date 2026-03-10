<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = isset($_POST['current_password'])  ? $_POST['current_password']  : '';
    $new_user = isset($_POST['new_username'])       ? trim($_POST['new_username']) : '';
    $new_pass = isset($_POST['new_password'])       ? $_POST['new_password']      : '';
    $confirm  = isset($_POST['confirm_password'])   ? $_POST['confirm_password']  : '';

    if ($current !== ADMIN_PASSWORD) {
        $error = 'Aktuelles Passwort ist falsch.';
    } elseif ($new_user === '') {
        $error = 'Benutzername darf nicht leer sein.';
    } elseif ($new_pass !== '' && $new_pass !== $confirm) {
        $error = 'Neues Passwort und Bestätigung stimmen nicht überein.';
    } elseif ($new_pass !== '' && strlen($new_pass) < 8) {
        $error = 'Das neue Passwort muss mindestens 8 Zeichen haben.';
    } else {
        $save_user = $new_user;
        $save_pass = ($new_pass !== '') ? $new_pass : ADMIN_PASSWORD;

        $config_content = '<?php' . "\n"
            . '// ─── torecon – Zugangsdaten Interner Bereich ───────────────────────────────' . "\n"
            . '// Diese Datei direkt in Plesk File Manager bearbeiten, um Zugangsdaten zu ändern.' . "\n"
            . '// Die Datei wird NICHT an den Browser ausgeliefert – nur der Server liest sie.' . "\n\n"
            . "define('ADMIN_USER',     " . var_export($save_user, true) . ");\n"
            . "define('ADMIN_PASSWORD', " . var_export($save_pass, true) . ");\n";

        if (file_put_contents(__DIR__ . '/config.php', $config_content) !== false) {
            $msg = 'Zugangsdaten erfolgreich gespeichert.';
        } else {
            $error = 'Fehler beim Speichern – bitte Dateiberechtigung in Plesk prüfen (config.php muss schreibbar sein).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Einstellungen – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:28px; max-width:520px; }
    .form-panel h4 { margin:0 0 18px; font-size:16px; }
    .fg { display:flex; flex-direction:column; gap:14px; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input { border:1px solid rgba(0,0,0,0.18); border-radius:8px; padding:8px 11px;
                font-size:14px; font-family:inherit; background:#fafafa; }
    .fg input:focus { outline:none; border-color:#0071E3; background:#fff; }
    .hint { font-size:12px; color:var(--text-tertiary); margin-top:2px; }
    .btn-row { display:flex; gap:10px; margin-top:20px; }
    .btn-primary { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                   border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .msg   { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
             border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .err   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
             border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .divider { border:none; border-top:1px solid rgba(0,0,0,0.08); margin:20px 0; }
  </style>
</head>
<body>
<script>window.TORECON_ROOT = 'https://www.torecon.de/';</script>

<div class="dashboard-wrap">

  <aside class="sidebar">
    <div class="sidebar-logo">tore<span>con</span></div>
    <ul class="sidebar-nav">
      <li><a href="./dashboard.php">📊 Übersicht</a></li>
      <li><a href="./news.php">📰 News verwalten</a></li>
      <li><a href="./references.php">🏢 Referenzkunden</a></li>
      <li><a href="./settings.php" class="active">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <div class="dash-main">
    <div class="dash-topbar">
      <h1>Einstellungen</h1>
      <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);">← Website</a>
    </div>

    <div class="dash-content">

      <?php if ($msg): ?>
        <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="err"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <div class="form-panel">
        <h4>Zugangsdaten ändern</h4>
        <form method="post" action="./settings.php">
          <div class="fg">
            <label>Benutzername
              <input type="text" name="new_username" required autocomplete="username"
                value="<?php echo htmlspecialchars(ADMIN_USER); ?>">
            </label>
            <label>Bisheriges Passwort
              <input type="password" name="current_password" required autocomplete="current-password">
            </label>
            <hr class="divider">
            <label>Neues Passwort
              <input type="password" name="new_password" autocomplete="new-password">
              <span class="hint">Leer lassen, um das bisherige Passwort beizubehalten.</span>
            </label>
            <label>Neues Passwort bestätigen
              <input type="password" name="confirm_password" autocomplete="new-password">
            </label>
          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary">Speichern</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
</body>
</html>
