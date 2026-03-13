<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$li_settings_file = __DIR__ . '/linkedin_settings.json';

$topics = array(
    'Geldpolitik & Zinsen (EZB, Leitzins, Inflation)',
    'Digitale Customer Experience & Omnichannel (CX-Strategie, App, digitale Filiale)',
    'Regulierung & Compliance (Basel IV, BaFin, EBA)',
    'Digitalisierung & KI (Fintech, AI, Kreditscoring)',
    'Nachhaltigkeit & ESG (CSRD, Green Finance, Taxonomie)',
    'Strategische Bankplanung (CIR, PCR, Gesamtbanksteuerung)',
    'Internationale Märkte (EBRD, IMF, Osteuropa)',
    'Legacy Transformation (Kernbanksysteme, Migration, Modernisierung)',
);

function read_li_settings($path) {
    $defaults = array(
        'topic1'     => 'Digitalisierung & KI (Fintech, AI, Kreditscoring)',
        'topic2'     => 'Legacy Transformation (Kernbanksysteme, Migration, Modernisierung)',
        'tone_hint'  => '',
        'post_count' => 4,
    );
    if (!file_exists($path)) return $defaults;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) return $defaults;
    return array_merge($defaults, $data);
}

$msg   = '';
$error = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ── Zugangsdaten ──────────────────────────────────────────────────────────────
if ($action === 'credentials_save') {
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
            . "define('ADMIN_PASSWORD', " . var_export($save_pass, true) . ");\n"
            . (defined('CLAUDE_API_KEY') ? "\ndefine('CLAUDE_API_KEY', " . var_export(CLAUDE_API_KEY, true) . ");\n" : '');

        if (file_put_contents(__DIR__ . '/config.php', $config_content) !== false) {
            $msg = 'Zugangsdaten erfolgreich gespeichert.';
        } else {
            $error = 'Fehler beim Speichern – bitte Dateiberechtigung in Plesk prüfen (config.php muss schreibbar sein).';
        }
    }
}

// ── LinkedIn-Einstellungen ────────────────────────────────────────────────────
if ($action === 'li_settings_save') {
    $post_count = isset($_POST['post_count']) ? intval($_POST['post_count']) : 4;
    if ($post_count < 1) $post_count = 1;
    if ($post_count > 6) $post_count = 6;

    $li_data = array(
        'topic1'     => isset($_POST['topic1'])    ? trim($_POST['topic1'])    : '',
        'topic2'     => isset($_POST['topic2'])    ? trim($_POST['topic2'])    : '',
        'tone_hint'  => isset($_POST['tone_hint']) ? trim($_POST['tone_hint']) : '',
        'post_count' => $post_count,
    );
    if (file_put_contents($li_settings_file, json_encode($li_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        $msg = 'LinkedIn-Einstellungen gespeichert.';
    } else {
        $error = 'Fehler beim Speichern der LinkedIn-Einstellungen.';
    }
}

$li = read_li_settings($li_settings_file);
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
                  padding:24px 28px; margin-bottom:28px; max-width:580px; }
    .form-panel h4 { margin:0 0 18px; font-size:16px; }
    .fg { display:flex; flex-direction:column; gap:14px; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input, .fg textarea, .fg select {
        border:1px solid rgba(0,0,0,0.18); border-radius:8px; padding:8px 11px;
        font-size:14px; font-family:inherit; background:#fafafa; resize:vertical; }
    .fg input:focus, .fg textarea:focus, .fg select:focus { outline:none; border-color:#0071E3; background:#fff; }
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
      <li><a href="./linkedin.php">💼 LinkedIn Posts</a></li>
      <li><a href="./links.php">🔖 Linkfavoriten</a></li>
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

      <!-- LinkedIn-Generator Einstellungen -->
      <div class="form-panel">
        <h4>💼 LinkedIn-Generator Einstellungen</h4>
        <form method="post" action="./settings.php">
          <input type="hidden" name="action" value="li_settings_save">
          <div class="fg">
            <label>Thema 1
              <select name="topic1">
                <?php foreach ($topics as $t): ?>
                  <option value="<?php echo htmlspecialchars($t); ?>"<?php echo ($li['topic1'] === $t ? ' selected' : ''); ?>><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
              </select>
              <span class="hint">Erster Themenschwerpunkt für die Post-Generierung.</span>
            </label>
            <label>Thema 2
              <select name="topic2">
                <?php foreach ($topics as $t): ?>
                  <option value="<?php echo htmlspecialchars($t); ?>"<?php echo ($li['topic2'] === $t ? ' selected' : ''); ?>><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
              </select>
              <span class="hint">Zweiter Themenschwerpunkt für die Post-Generierung.</span>
            </label>
            <label>Ton-Hinweis <span style="font-weight:400;">(optional)</span>
              <textarea name="tone_hint" rows="2" placeholder="z.B. Schreib etwas provokativer, nutze mehr konkrete Zahlen, vermeide Anglizismen …"><?php echo htmlspecialchars($li['tone_hint']); ?></textarea>
              <span class="hint">Wird am Ende des Prompts ergänzt. Leer lassen für den Standardton.</span>
            </label>
            <label>Anzahl Posts pro Generierung
              <select name="post_count">
                <?php for ($i = 2; $i <= 6; $i++): ?>
                  <option value="<?php echo $i; ?>"<?php echo ($li['post_count'] == $i ? ' selected' : ''); ?>><?php echo $i; ?></option>
                <?php endfor; ?>
              </select>
              <span class="hint">Wie viele Entwürfe sollen auf einmal generiert werden? Standard: 4</span>
            </label>
          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary">Einstellungen speichern</button>
          </div>
        </form>
      </div>

      <!-- Zugangsdaten -->
      <div class="form-panel">
        <h4>🔐 Zugangsdaten ändern</h4>
        <form method="post" action="./settings.php">
          <input type="hidden" name="action" value="credentials_save">
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
