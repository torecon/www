<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$li_settings_file = __DIR__ . '/linkedin_settings.json';

// ── Pillar-Liste (Single Source of Truth):
// ~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md (Pillar 1–9)
function pillars_list() {
    return array(
        array('icon'=>'📈',  'label_de'=>'Geldpolitik & Zinsen',                       'sub_de'=>'EZB, Leitzins, Inflation'),
        array('icon'=>'📱',  'label_de'=>'Digitale Customer Experience & Omnichannel', 'sub_de'=>'CX-Strategie, App, digitale Filiale'),
        array('icon'=>'⚖️', 'label_de'=>'Regulierung & Compliance',                    'sub_de'=>'Basel IV, BaFin, EBA'),
        array('icon'=>'🤖',  'label_de'=>'Digitalisierung & KI',                       'sub_de'=>'Fintech, AI, Kreditscoring'),
        array('icon'=>'🌱',  'label_de'=>'Nachhaltigkeit & ESG',                       'sub_de'=>'CSRD, Green Finance, Taxonomie'),
        array('icon'=>'📊',  'label_de'=>'Datenplattform für KI',                      'sub_de'=>'AI-Readiness, Data Mesh, Governance'),
        array('icon'=>'🧩',  'label_de'=>'Agentic AI in der Praxis',                   'sub_de'=>'Agent-Orchestrierung, Memory, Tool-Use'),
        array('icon'=>'🔄',  'label_de'=>'Legacy Transformation',                      'sub_de'=>'Kernbanksysteme, Migration, Modernisierung'),
        array('icon'=>'💼',  'label_de'=>'Pricing',                                    'sub_de'=>'Outcome-Based, Sprint-Tier, Quality-Gates'),
    );
}

function topic_li_string($t) {
    return $t['label_de'] . ' (' . $t['sub_de'] . ')';
}

function read_li_settings($path) {
    $defaults = array(
        'topic'      => 'Digitalisierung & KI (Fintech, AI, Kreditscoring)',
        'post_hint'  => '',
        'post_count' => 4,
    );
    if (!file_exists($path)) return $defaults;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) return $defaults;
    if (!isset($data['post_hint']) && isset($data['tone_hint'])) {
        $data['post_hint'] = $data['tone_hint'];
    }
    if (!isset($data['topic']) && isset($data['topic1'])) {
        $data['topic'] = $data['topic1'];
    }
    unset($data['topic1'], $data['topic2']);
    return array_merge($defaults, $data);
}

$msg    = '';
$error  = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ── LinkedIn-Einstellungen speichern ─────────────────────────────────────────
if ($action === 'li_settings_save') {
    $post_count = isset($_POST['post_count']) ? intval($_POST['post_count']) : 4;
    if ($post_count < 1) $post_count = 1;
    if ($post_count > 6) $post_count = 6;

    $li_data = array(
        'topic'      => isset($_POST['topic'])      ? trim($_POST['topic'])      : '',
        'post_hint'  => isset($_POST['post_hint'])  ? trim($_POST['post_hint'])  : '',
        'post_count' => $post_count,
    );
    if (file_put_contents($li_settings_file, json_encode($li_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        $msg = 'LinkedIn-Konfiguration gespeichert.';
    } else {
        $error = 'Fehler beim Speichern der LinkedIn-Konfiguration.';
    }
}

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
            $error = 'Fehler beim Speichern – bitte Dateiberechtigung in Plesk prüfen.';
        }
    }
}

// ── Daten laden ───────────────────────────────────────────────────────────────
$pillars = pillars_list();
$li      = read_li_settings($li_settings_file);
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
                  padding:24px 28px; margin-bottom:28px; }
    .form-panel > h4 { margin:0 0 18px; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px; }
    .fg { display:flex; flex-direction:column; gap:14px; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input, .fg textarea, .fg select {
        border:1px solid rgba(0,0,0,0.18); border-radius:8px; padding:8px 11px;
        font-size:14px; font-family:inherit; background:#fafafa; resize:vertical; }
    .fg input:focus, .fg textarea:focus, .fg select:focus { outline:none; border-color:#0071E3; background:#fff; }
    .hint  { font-size:12px; color:var(--text-tertiary); margin-top:2px; }
    .btn-row { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; align-items:center; }
    .btn-primary { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                   border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-primary:hover { background:#005bb5; }
    .msg  { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
            border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .err  { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
            border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .divider { border:none; border-top:1px solid rgba(0,0,0,0.08); margin:20px 0; }
    .info-box { background:#f0f7ff; border:1px solid rgba(0,113,227,0.2); border-radius:10px;
                padding:12px 16px; font-size:13px; color:#1a4a7a; line-height:1.6; }
    .info-box code { background:#dbeafe; border-radius:4px; padding:1px 5px; font-size:12px; }
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

      <!-- ══ KONFIGURATION LINKEDIN ══════════════════════════════════════════ -->
      <div class="form-panel">
        <h4>💼 Konfiguration LinkedIn</h4>
        <form method="post" action="./settings.php">
          <input type="hidden" name="action" value="li_settings_save">
          <div class="fg">

            <label>Themencluster (Pillar) für die nächste Generierung
              <select name="topic">
                <?php foreach ($pillars as $t):
                  $val = topic_li_string($t); ?>
                  <option value="<?php echo htmlspecialchars($val); ?>"<?php echo ($li['topic'] === $val ? ' selected' : ''); ?>>
                    <?php echo htmlspecialchars($t['icon'] . ' ' . $t['label_de']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <span class="hint">Pillar 1–9 — Pflege-Quelle: <code>~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md</code>. Hashtag-Pool wird passend automatisch ergänzt.</span>
            </label>

            <label>Anzahl Posts pro Generierung
              <select name="post_count" style="max-width:180px;">
                <?php for ($i = 2; $i <= 6; $i++): ?>
                  <option value="<?php echo $i; ?>"<?php echo ($li['post_count'] == $i ? ' selected' : ''); ?>><?php echo $i; ?> Posts</option>
                <?php endfor; ?>
              </select>
              <span class="hint">Wie viele Entwürfe sollen auf einmal generiert werden?</span>
            </label>

            <label>
              Post-Hinweise <span style="font-weight:400;font-size:12px;color:var(--text-tertiary);">(Ton, Stil & inhaltliche Anweisungen – gilt für Einzel-Posts und Serien)</span>
              <textarea name="post_hint" rows="4"
                placeholder="Beispiele:&#10;– Schreib provokativer und direkter als üblich&#10;– Beziehe aktuelle EZB-Entscheidungen mit ein&#10;– Vermeide Anglizismen, nutze stattdessen deutsche Fachbegriffe&#10;– Jeder Post soll einen konkreten Handlungsimpuls für Vorstände enthalten"><?php echo htmlspecialchars($li['post_hint']); ?></textarea>
              <span class="hint">
                Diese Hinweise werden an den KI-Prompt angehängt und steuern Ton, Sprache und Inhalte.
                Mehrere Anweisungen als Stichpunkte eingeben (eine pro Zeile).
                Gilt für <strong>alle</strong> generierten Posts – Einzel- und Serien-Posts.
              </span>
            </label>

          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary">LinkedIn-Konfiguration speichern</button>
          </div>
        </form>

        <div class="info-box" style="margin-top:18px;">
          ℹ️ <strong>Cluster-Bearbeitung:</strong> Die 9 Pillars werden zentral im Obsidian-Vault gepflegt
          (<code>_projects/linkedin/pillars/index.md</code>) und manuell in den Code synchronisiert.
          In der Web-UI gibt es deshalb keinen Editor mehr.
        </div>
      </div>

      <!-- ══ ZUGANGSDATEN ════════════════════════════════════════════════════ -->
      <div class="form-panel" style="max-width:520px;">
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
