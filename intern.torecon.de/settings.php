<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$li_settings_file     = __DIR__ . '/linkedin_settings.json';
$topics_settings_file = __DIR__ . '/topics_settings.json';

// ── Default-Themen (identisch mit topics.js) ─────────────────────────────────
function default_topics() {
    return array(
        array('id'=>'geldpolitik',   'icon'=>'📈', 'label_de'=>'Geldpolitik & Zinsen',                          'sub_de'=>'EZB, Leitzins, Inflation',               'label_en'=>'Monetary Policy & Rates',                    'sub_en'=>'ECB, key rates, inflation'),
        array('id'=>'cx',            'icon'=>'📱', 'label_de'=>'Digitale Customer Experience & Omnichannel',    'sub_de'=>'CX-Strategie, App, digitale Filiale',    'label_en'=>'Digital Customer Experience & Omnichannel',   'sub_en'=>'CX strategy, app, digital branch'),
        array('id'=>'regulierung',   'icon'=>'⚖️', 'label_de'=>'Regulierung & Compliance',                     'sub_de'=>'Basel IV, BaFin, EBA',                   'label_en'=>'Regulation & Compliance',                    'sub_en'=>'Basel IV, BaFin, EBA'),
        array('id'=>'digitalisierung','icon'=>'🤖','label_de'=>'Digitalisierung & KI',                          'sub_de'=>'Fintech, AI, Kreditscoring',              'label_en'=>'Digitalisation & AI',                        'sub_en'=>'Fintech, AI, credit scoring'),
        array('id'=>'esg',           'icon'=>'🌱', 'label_de'=>'Nachhaltigkeit & ESG',                          'sub_de'=>'CSRD, Green Finance, Taxonomie',          'label_en'=>'Sustainability & ESG',                       'sub_en'=>'CSRD, green finance, taxonomy'),
        array('id'=>'bankplanung',   'icon'=>'🗺️', 'label_de'=>'Strategische Bankplanung',                     'sub_de'=>'CIR, PCR, Gesamtbanksteuerung',           'label_en'=>'Strategic Bank Planning',                    'sub_en'=>'CIR, PCR, bank-wide management'),
        array('id'=>'international', 'icon'=>'🌍', 'label_de'=>'Internationale Märkte',                         'sub_de'=>'EBRD, IMF, Osteuropa',                   'label_en'=>'International Markets',                      'sub_en'=>'EBRD, IMF, Eastern Europe'),
        array('id'=>'legacy',        'icon'=>'🔄', 'label_de'=>'Legacy Transformation',                         'sub_de'=>'Kernbanksysteme, Migration, Modernisierung','label_en'=>'Legacy Transformation',                   'sub_en'=>'Core banking, migration, modernisation'),
    );
}

function read_topics($path) {
    if (!file_exists($path)) return default_topics();
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || count($data) < 8) return default_topics();
    // Merge with defaults to ensure all keys exist
    $defaults = default_topics();
    foreach ($data as $i => $t) {
        if (isset($defaults[$i])) {
            $data[$i] = array_merge($defaults[$i], $t);
        }
    }
    return $data;
}

function topic_li_string($t) {
    return $t['label_de'] . ' (' . $t['sub_de'] . ')';
}

function read_li_settings($path) {
    $defaults = array(
        'topic1'     => 'Digitalisierung & KI (Fintech, AI, Kreditscoring)',
        'topic2'     => 'Legacy Transformation (Kernbanksysteme, Migration, Modernisierung)',
        'post_hint'  => '',
        'post_count' => 4,
    );
    if (!file_exists($path)) return $defaults;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) return $defaults;
    // Migrate old key 'tone_hint' → 'post_hint'
    if (!isset($data['post_hint']) && isset($data['tone_hint'])) {
        $data['post_hint'] = $data['tone_hint'];
    }
    return array_merge($defaults, $data);
}

// ── Generates topics.js content from topics array ────────────────────────────
function topics_to_js($topics) {
    $lines = array();
    foreach ($topics as $t) {
        $lines[] = "  {\n"
            . "    id: "        . json_encode($t['id'],       JSON_UNESCAPED_UNICODE) . ",\n"
            . "    icon: "      . json_encode($t['icon'],     JSON_UNESCAPED_UNICODE) . ",\n"
            . "    label_de: "  . json_encode($t['label_de'], JSON_UNESCAPED_UNICODE) . ",\n"
            . "    label_en: "  . json_encode($t['label_en'], JSON_UNESCAPED_UNICODE) . ",\n"
            . "    sub_de:   "  . json_encode($t['sub_de'],   JSON_UNESCAPED_UNICODE) . ",\n"
            . "    sub_en:   "  . json_encode($t['sub_en'],   JSON_UNESCAPED_UNICODE) . ",\n"
            . "  }";
    }
    return "/* torecon \xe2\x80\x93 Shared topic definitions (Homepage & Newsletter \xe2\x80\x93 always identical) */\n"
         . "const TOPICS = [\n" . implode(",\n", $lines) . "\n];\n";
}

$msg   = '';
$error = '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ── Schwerpunktthemen speichern ───────────────────────────────────────────────
if ($action === 'topics_save') {
    $raw_topics = isset($_POST['topics']) && is_array($_POST['topics']) ? $_POST['topics'] : array();
    $defaults   = default_topics();
    $new_topics = array();

    foreach ($defaults as $i => $def) {
        $t = isset($raw_topics[$i]) ? $raw_topics[$i] : array();
        $new_topics[] = array(
            'id'       => $def['id'], // ID ist nicht editierbar
            'icon'     => isset($t['icon'])     ? trim($t['icon'])     : $def['icon'],
            'label_de' => isset($t['label_de']) ? trim($t['label_de']) : $def['label_de'],
            'sub_de'   => isset($t['sub_de'])   ? trim($t['sub_de'])   : $def['sub_de'],
            'label_en' => isset($t['label_en']) ? trim($t['label_en']) : $def['label_en'],
            'sub_en'   => isset($t['sub_en'])   ? trim($t['sub_en'])   : $def['sub_en'],
        );
    }

    if (file_put_contents($topics_settings_file, json_encode($new_topics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        $msg = 'Schwerpunktthemen gespeichert.';

        // Optional: topics.js auf der Hauptseite automatisch aktualisieren
        if (defined('SITE_TOPICS_JS_PATH') && SITE_TOPICS_JS_PATH !== '') {
            $existing_js = file_exists(SITE_TOPICS_JS_PATH) ? file_get_contents(SITE_TOPICS_JS_PATH) : '';
            // Neuen TOPICS-Block bauen (PHP 5.x kompatibel, kein Closure)
            $new_topics_block  = "/* torecon \xe2\x80\x93 Shared topic definitions (Homepage & Newsletter \xe2\x80\x93 always identical) */\n";
            $new_topics_block .= "const TOPICS = [\n";
            foreach ($new_topics as $t) {
                $new_topics_block .= "  {\n";
                $new_topics_block .= "    id: "        . json_encode($t['id'],       JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "    icon: "      . json_encode($t['icon'],     JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "    label_de: "  . json_encode($t['label_de'], JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "    label_en: "  . json_encode($t['label_en'], JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "    sub_de:   "  . json_encode($t['sub_de'],   JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "    sub_en:   "  . json_encode($t['sub_en'],   JSON_UNESCAPED_UNICODE) . ",\n";
                $new_topics_block .= "  },\n";
            }
            $new_topics_block .= "];\n";
            // Nur den Kommentar + TOPICS-Array ersetzen, Render-Funktionen bleiben erhalten
            $new_js = preg_replace('/\/\*[^*]*\*\/\s*\nconst TOPICS = \[[\s\S]*?\];\n/s', $new_topics_block, $existing_js);
            if ($new_js === null || $new_js === $existing_js) {
                $msg .= ' ⚠️ TOPICS-Block in topics.js nicht gefunden – Datei unverändert.';
            } elseif (file_put_contents(SITE_TOPICS_JS_PATH, $new_js) !== false) {
                $msg .= ' topics.js auf der Website wurde ebenfalls aktualisiert.';
            } else {
                $msg .= ' ⚠️ topics.js konnte nicht geschrieben werden (Dateiberechtigung prüfen).';
            }
        }
    } else {
        $error = 'Fehler beim Speichern der Schwerpunktthemen.';
    }
}

// ── LinkedIn-Einstellungen speichern ─────────────────────────────────────────
if ($action === 'li_settings_save') {
    $post_count = isset($_POST['post_count']) ? intval($_POST['post_count']) : 4;
    if ($post_count < 1) $post_count = 1;
    if ($post_count > 6) $post_count = 6;

    $li_data = array(
        'topic1'     => isset($_POST['topic1'])     ? trim($_POST['topic1'])     : '',
        'topic2'     => isset($_POST['topic2'])     ? trim($_POST['topic2'])     : '',
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
            . (defined('CLAUDE_API_KEY') ? "\ndefine('CLAUDE_API_KEY', " . var_export(CLAUDE_API_KEY, true) . ");\n" : '')
            . (defined('SITE_TOPICS_JS_PATH') ? "\n// Pfad zur topics.js auf der Hauptwebsite (für automatische Aktualisierung)\ndefine('SITE_TOPICS_JS_PATH', " . var_export(SITE_TOPICS_JS_PATH, true) . ");\n" : '');

        if (file_put_contents(__DIR__ . '/config.php', $config_content) !== false) {
            $msg = 'Zugangsdaten erfolgreich gespeichert.';
        } else {
            $error = 'Fehler beim Speichern – bitte Dateiberechtigung in Plesk prüfen.';
        }
    }
}

// ── Daten laden ───────────────────────────────────────────────────────────────
$topics = read_topics($topics_settings_file);
$li     = read_li_settings($li_settings_file);
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
    .badge { display:inline-block; font-size:11px; background:#e8f4ff; color:#0071E3;
             border-radius:20px; padding:2px 9px; font-weight:600; vertical-align:middle; }
    .btn-row { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; align-items:center; }
    .btn-primary { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                   border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-primary:hover { background:#005bb5; }
    .msg  { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
            border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .err  { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
            border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .divider { border:none; border-top:1px solid rgba(0,0,0,0.08); margin:20px 0; }

    /* ── Topics table ─────────────────────────────────────────────────────── */
    .topics-table { width:100%; border-collapse:collapse; }
    .topics-table th { font-size:11px; font-weight:600; color:var(--text-secondary); text-align:left;
                        padding:6px 8px; border-bottom:2px solid rgba(0,0,0,0.08); }
    .topics-table td { padding:6px 8px; border-bottom:1px solid rgba(0,0,0,0.05); vertical-align:top; }
    .topics-table tr:last-child td { border-bottom:none; }
    .topics-table tr:hover td { background:#fafafa; }
    .topics-table input[type=text] { width:100%; box-sizing:border-box;
        border:1px solid rgba(0,0,0,0.15); border-radius:6px; padding:5px 8px;
        font-size:13px; font-family:inherit; background:#fff; }
    .topics-table input[type=text]:focus { outline:none; border-color:#0071E3; }
    .inp-icon { width:52px !important; text-align:center; font-size:18px; }
    .col-de { background:#f9f9f9; }
    .col-en { background:#f5f8ff; }
    .lang-badge { font-size:10px; font-weight:700; color:#fff; border-radius:4px;
                  padding:1px 5px; margin-right:4px; }
    .lang-badge.de { background:#1a1a2e; }
    .lang-badge.en { background:#0071E3; }
    .info-box { background:#f0f7ff; border:1px solid rgba(0,113,227,0.2); border-radius:10px;
                padding:12px 16px; font-size:13px; color:#1a4a7a; margin-top:16px; line-height:1.6; }
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

      <!-- ══ SCHWERPUNKTTHEMEN ════════════════════════════════════════════════ -->
      <div class="form-panel">
        <h4>🎯 Schwerpunktthemen <span class="badge">Generisch</span></h4>
        <p style="margin:0 0 16px;font-size:13px;color:var(--text-secondary);line-height:1.6;">
          Diese 8 Themen sind die Basis für den LinkedIn-Generator und die Homepage-Anzeige.
          Änderungen hier wirken sich auf Themenauswahl, Post-Generierung und – sofern konfiguriert – auch auf die Website aus.
        </p>
        <form method="post" action="./settings.php">
          <input type="hidden" name="action" value="topics_save">
          <div style="overflow-x:auto;">
            <table class="topics-table">
              <thead>
                <tr>
                  <th style="width:36px;">#</th>
                  <th style="width:62px;">Icon</th>
                  <th class="col-de"><span class="lang-badge de">DE</span> Bezeichnung</th>
                  <th class="col-de"><span class="lang-badge de">DE</span> Schlagworte</th>
                  <th class="col-en"><span class="lang-badge en">EN</span> Label</th>
                  <th class="col-en"><span class="lang-badge en">EN</span> Keywords</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($topics as $i => $t): ?>
                <tr>
                  <td style="font-size:12px;color:var(--text-secondary);font-weight:600;text-align:center;"><?php echo $i+1; ?></td>
                  <td>
                    <input type="hidden" name="topics[<?php echo $i; ?>][id]" value="<?php echo htmlspecialchars($t['id']); ?>">
                    <input type="text" class="inp-icon" name="topics[<?php echo $i; ?>][icon]"
                           value="<?php echo htmlspecialchars($t['icon']); ?>" maxlength="4">
                  </td>
                  <td class="col-de">
                    <input type="text" name="topics[<?php echo $i; ?>][label_de]"
                           value="<?php echo htmlspecialchars($t['label_de']); ?>"
                           placeholder="Bezeichnung Deutsch">
                  </td>
                  <td class="col-de">
                    <input type="text" name="topics[<?php echo $i; ?>][sub_de]"
                           value="<?php echo htmlspecialchars($t['sub_de']); ?>"
                           placeholder="Schlagworte, kommasepariert">
                  </td>
                  <td class="col-en">
                    <input type="text" name="topics[<?php echo $i; ?>][label_en]"
                           value="<?php echo htmlspecialchars($t['label_en']); ?>"
                           placeholder="English label">
                  </td>
                  <td class="col-en">
                    <input type="text" name="topics[<?php echo $i; ?>][sub_en]"
                           value="<?php echo htmlspecialchars($t['sub_en']); ?>"
                           placeholder="Keywords, comma-separated">
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary">Themen speichern</button>
          </div>
        </form>

        <div class="info-box">
          💡 <strong>Homepage-Synchronisierung:</strong>
          Um Änderungen automatisch in <code>topics.js</code> zu übernehmen,
          trage den Serverpfad in <code>config.php</code> ein:<br>
          <code>define('SITE_TOPICS_JS_PATH', '/var/www/vhosts/torecon.de/httpdocs/js/topics.js');</code><br>
          Den genauen Pfad siehst du im Plesk File Manager (Eigenschaften der Datei).
          <?php if (defined('SITE_TOPICS_JS_PATH') && SITE_TOPICS_JS_PATH !== ''): ?>
            <br><strong style="color:#1a7f37;">✓ Pfad konfiguriert:</strong> <code><?php echo htmlspecialchars(SITE_TOPICS_JS_PATH); ?></code>
          <?php endif; ?>
        </div>
      </div>

      <!-- ══ KONFIGURATION LINKEDIN ══════════════════════════════════════════ -->
      <div class="form-panel">
        <h4>💼 Konfiguration LinkedIn</h4>
        <form method="post" action="./settings.php">
          <input type="hidden" name="action" value="li_settings_save">
          <div class="fg">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <label>Thema 1 – Schwerpunkt
                <select name="topic1">
                  <?php foreach ($topics as $t):
                    $val = topic_li_string($t); ?>
                    <option value="<?php echo htmlspecialchars($val); ?>"<?php echo ($li['topic1'] === $val ? ' selected' : ''); ?>>
                      <?php echo htmlspecialchars($t['icon'] . ' ' . $t['label_de']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="hint">Hauptthema für einzelne Posts.</span>
              </label>
              <label>Thema 2 – Schwerpunkt
                <select name="topic2">
                  <?php foreach ($topics as $t):
                    $val = topic_li_string($t); ?>
                    <option value="<?php echo htmlspecialchars($val); ?>"<?php echo ($li['topic2'] === $val ? ' selected' : ''); ?>>
                      <?php echo htmlspecialchars($t['icon'] . ' ' . $t['label_de']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="hint">Zweites Thema – sorgt für Abwechslung.</span>
              </label>
            </div>

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
