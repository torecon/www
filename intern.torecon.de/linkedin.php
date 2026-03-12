<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$drafts_file = __DIR__ . '/linkedin_drafts.json';

// ── helpers ──────────────────────────────────────────────────────────────────
function li_read($path) {
    if (!file_exists($path)) return array();
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : array();
}

function li_write($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function li_call_claude($api_key, $today) {
    $prompt = 'Du bist Thomas Reinke, Unternehmensberater fuer Banken und Kreditinstitute (torecon.de), 25+ Jahre Erfahrung. '
            . 'Deine Kernthemen: Digitalisierung & KI in Banken/Versicherungen und Legacy Transformation (Kernbanksysteme, Migration, Modernisierung). '
            . "\n\nHeutiges Datum: " . $today . "\n\n"
            . "Erstelle 4 LinkedIn-Posts zu aktuellen, praxisrelevanten Themen aus diesen zwei Bereichen. Abwechslung ist wichtig.\n\n"
            . "Jeder Post muss:\n"
            . "- Mit einem starken Hook beginnen (1 Satz: provokante These, ueberraschende Zahl oder offene Frage)\n"
            . "- Ca. 900-1.300 Zeichen lang sein (ohne Hashtags)\n"
            . "- Aus Ich-Perspektive geschrieben sein, praxisnah und ohne Buzzword-Bingo\n"
            . "- Einen konkreten Insight oder Handlungsempfehlung enthalten\n"
            . "- Mit einer Frage oder einem klaren Call-to-Action enden\n"
            . "- Mit 4-5 Hashtags abschliessen (z.B. #Digitalisierung #Banking #KI #LegacyTransformation #Fintech)\n\n"
            . "Antworte AUSSCHLIESSLICH als valides JSON-Array, kein Text davor oder danach:\n"
            . '[{"topic":"Kurztitel max 40 Zeichen","text":"Vollstaendiger Post\n\n#Hashtag1 #Hashtag2"},...]';

    $payload = json_encode(array(
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => 3500,
        'messages'   => array(
            array('role' => 'user', 'content' => $prompt)
        )
    ));

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-api-key: ' . $api_key,
        'anthropic-version: 2023-06-01',
        'content-type: application/json'
    ));

    $response  = curl_exec($ch);
    $curl_err  = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_err) {
        return array('error' => 'cURL-Fehler: ' . $curl_err);
    }

    $decoded = json_decode($response, true);
    if (!isset($decoded['content'][0]['text'])) {
        $api_type = isset($decoded['error']['type'])    ? $decoded['error']['type']    : '';
        $api_msg  = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unbekannter Fehler';
        return array('error' => 'HTTP ' . $http_code . ' · ' . $api_type . ': ' . $api_msg);
    }

    $text = $decoded['content'][0]['text'];

    // Extract JSON array even if wrapped in markdown code block
    if (preg_match('/\[[\s\S]+\]/u', $text, $matches)) {
        $posts = json_decode($matches[0], true);
        if (is_array($posts) && count($posts) > 0) return $posts;
    }

    return array('error' => 'Antwort konnte nicht geparst werden: ' . substr($text, 0, 300));
}

// ── actions ──────────────────────────────────────────────────────────────────
$action   = isset($_POST['action']) ? $_POST['action'] : '';
$msg      = '';
$msg_type = 'success';
$api_key  = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '';

// ── API TEST ─────────────────────────────────────────────────────────────────
$api_test_result = '';
if ($action === 'api_test') {
    if (!$api_key) {
        $api_test_result = 'Kein API-Key konfiguriert.';
    } else {
        // List available models on this account
        $ch = curl_init('https://api.anthropic.com/v1/models');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ));
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        // Pretty-print model IDs if successful
        $decoded_models = json_decode($resp, true);
        if ($http === 200 && isset($decoded_models['data'])) {
            $ids = array();
            foreach ($decoded_models['data'] as $m) {
                $ids[] = $m['id'];
            }
            $api_test_result = 'HTTP 200 – Verfügbare Modelle (' . count($ids) . '):\n' . implode("\n", $ids);
        } else {
            $api_test_result = 'HTTP ' . $http . "\n" . ($cerr ? 'cURL: ' . $cerr . "\n" : '') . $resp;
        }
    }
}

if ($action === 'generate') {
    if (!$api_key) {
        $msg      = 'Kein Claude API-Key hinterlegt. Bitte in config.php eintragen: define(\'CLAUDE_API_KEY\', \'sk-ant-...\');';
        $msg_type = 'error';
    } else {
        $result = li_call_claude($api_key, date('Y-m-d'));
        if (isset($result['error'])) {
            $msg      = 'API-Fehler: ' . $result['error'];
            $msg_type = 'error';
        } else {
            $drafts = array();
            foreach ($result as $i => $post) {
                $drafts[] = array(
                    'id'           => intval(round(microtime(true) * 1000)) + $i,
                    'generated_at' => date('Y-m-d'),
                    'topic'        => isset($post['topic']) ? trim($post['topic']) : 'Post ' . ($i + 1),
                    'text'         => isset($post['text'])  ? trim($post['text'])  : '',
                    'status'       => 'pending',
                );
            }
            li_write($drafts_file, $drafts);
            $msg = count($drafts) . ' Entwuerfe wurden generiert. Waehle einen aus und genehmige ihn.';
        }
    }
}

if ($action === 'approve') {
    $approve_id = intval($_POST['approve_id']);
    $drafts = li_read($drafts_file);
    foreach ($drafts as $k => $d) {
        $drafts[$k]['status'] = ($d['id'] == $approve_id) ? 'approved' : 'rejected';
    }
    li_write($drafts_file, $drafts);
    $msg = 'Post genehmigt! Kopiere ihn unten und poste ihn auf LinkedIn.';
}

if ($action === 'edit_save') {
    $edit_id  = intval($_POST['edit_id']);
    $new_text = isset($_POST['edit_text']) ? trim($_POST['edit_text']) : '';
    $drafts = li_read($drafts_file);
    foreach ($drafts as $k => $d) {
        if ($d['id'] == $edit_id) { $drafts[$k]['text'] = $new_text; break; }
    }
    li_write($drafts_file, $drafts);
    $msg = 'Post aktualisiert.';
}

if ($action === 'reset') {
    li_write($drafts_file, array());
    $msg = 'Entwuerfe zurueckgesetzt. Du kannst neue generieren.';
}

// ── load data ─────────────────────────────────────────────────────────────────
$drafts   = li_read($drafts_file);
$approved = null;
$pending  = array();
$edit_id  = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

foreach ($drafts as $d) {
    if ($d['status'] === 'approved') $approved = $d;
    if ($d['status'] === 'pending')  $pending[] = $d;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LinkedIn Posts – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:24px; }
    .form-panel h4 { margin:0 0 16px; font-size:15px; font-weight:600; }

    .btn-primary   { background:#0071E3; color:#fff; border:none; padding:10px 24px;
                     border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-primary:hover { background:#005bb5; }
    .btn-secondary { background:#f5f5f7; color:#333; border:1px solid rgba(0,0,0,0.15);
                     padding:9px 18px; border-radius:8px; font-size:14px; cursor:pointer; }
    .btn-approve   { background:#34c759; color:#fff; border:none; padding:8px 20px;
                     border-radius:8px; font-size:13px; cursor:pointer; font-weight:600; }
    .btn-approve:hover { background:#28a745; }
    .btn-edit      { background:#f5f5f7; color:#0071E3; border:1px solid rgba(0,113,227,0.3);
                     padding:7px 16px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:500; }
    .btn-danger    { background:#ff3b30; color:#fff; border:none; padding:7px 14px;
                     border-radius:7px; font-size:12px; cursor:pointer; }
    .btn-copy      { background:#0071E3; color:#fff; border:none; padding:10px 28px;
                     border-radius:8px; font-size:14px; cursor:pointer; font-weight:600; }

    .msg-success { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
                   border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }
    .msg-error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b;
                   border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }

    /* Approved post box */
    .approved-box { background:linear-gradient(135deg,#e8f5e9 0%,#f1f8ff 100%);
                    border:2px solid #34c759; border-radius:16px; padding:28px; margin-bottom:28px; }
    .approved-box h3 { margin:0 0 12px; color:#1a7f37; font-size:16px; }
    .approved-box textarea { width:100%; min-height:220px; border:1px solid rgba(0,0,0,0.15);
                              border-radius:10px; padding:14px; font-size:14px; font-family:inherit;
                              line-height:1.6; resize:vertical; background:#fff; box-sizing:border-box; }
    .approved-meta { display:flex; align-items:center; gap:16px; margin-top:14px; flex-wrap:wrap; }
    .char-count { font-size:12px; color:var(--text-secondary); }
    .copy-ok { font-size:13px; color:#1a7f37; display:none; font-weight:500; }

    /* Draft cards */
    .draft-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    @media(max-width:900px) { .draft-grid { grid-template-columns:1fr; } }

    .draft-card { background:#fff; border:1.5px solid rgba(0,0,0,0.1); border-radius:14px;
                  padding:20px; display:flex; flex-direction:column; gap:12px; }
    .draft-card:hover { border-color:rgba(0,113,227,0.3); }
    .draft-topic { font-size:12px; font-weight:700; color:#0071E3; text-transform:uppercase;
                   letter-spacing:0.05em; }
    .draft-preview { font-size:13px; color:var(--text); line-height:1.6; white-space:pre-wrap;
                     flex:1; }
    .draft-preview.collapsed { display:-webkit-box; -webkit-line-clamp:6; -webkit-box-orient:vertical;
                                overflow:hidden; }
    .draft-chars { font-size:11px; color:var(--text-secondary); }
    .draft-actions { display:flex; gap:8px; flex-wrap:wrap; }

    .draft-editarea { width:100%; min-height:180px; border:1px solid #0071E3; border-radius:8px;
                      padding:12px; font-size:13px; font-family:inherit; line-height:1.6;
                      resize:vertical; background:#f8fbff; box-sizing:border-box; }

    .empty-state { text-align:center; padding:48px 24px; color:var(--text-secondary); }
    .empty-state .icon { font-size:40px; margin-bottom:12px; }
    .empty-state p { font-size:14px; margin:0; }

    .generate-bar { display:flex; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:28px; }
    .generate-bar .hint { font-size:12px; color:var(--text-secondary); }

    .loading-overlay { display:none; position:fixed; inset:0; background:rgba(255,255,255,0.85);
                       z-index:999; align-items:center; justify-content:center; flex-direction:column;
                       gap:16px; font-size:15px; color:var(--text-secondary); }
    .loading-overlay.active { display:flex; }
    .spinner { width:36px; height:36px; border:3px solid #e5e5ea; border-top-color:#0071E3;
               border-radius:50%; animation:spin 0.8s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }
  </style>
</head>
<body>
<script>window.TORECON_ROOT = 'https://www.torecon.de/';</script>

<div class="loading-overlay" id="loading-overlay">
  <div class="spinner"></div>
  <span>Posts werden generiert&nbsp;– bitte warten&hellip;</span>
</div>

<div class="dashboard-wrap">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">tore<span>con</span></div>
    <ul class="sidebar-nav">
      <li><a href="./dashboard.php">📊 Übersicht</a></li>
      <li><a href="./news.php">📰 News verwalten</a></li>
      <li><a href="./linkedin.php" class="active">💼 LinkedIn Posts</a></li>
      <li><a href="./links.php">🔖 Linkfavoriten</a></li>
      <li><a href="./settings.php">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="dash-main">
    <div class="dash-topbar">
      <h1>💼 LinkedIn Posts</h1>
      <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);">← Website</a>
    </div>

    <div class="dash-content">

      <?php if ($msg): ?>
        <div class="msg-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <?php if (!$api_key): ?>
        <div class="msg-error">
          ⚠️ Kein Claude API-Key konfiguriert.
          Bitte in <strong>config.php</strong> folgende Zeile ergänzen:<br>
          <code style="font-size:13px;display:block;margin-top:6px;background:#fff;padding:6px 10px;border-radius:6px;">define('CLAUDE_API_KEY', 'sk-ant-...');</code>
          API-Key: <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener" style="color:#0071E3;">console.anthropic.com</a>
        </div>
      <?php endif; ?>

      <!-- API Test Result -->
      <?php if ($api_test_result): ?>
        <div style="background:#1e1e2e;color:#cdd6f4;border-radius:10px;padding:16px 20px;margin-bottom:20px;font-family:monospace;font-size:13px;white-space:pre-wrap;word-break:break-all;"><?php echo htmlspecialchars($api_test_result); ?></div>
      <?php endif; ?>

      <!-- Generate bar -->
      <div class="generate-bar">
        <form method="post" action="./linkedin.php" onsubmit="showLoading()">
          <input type="hidden" name="action" value="generate">
          <button type="submit" class="btn-primary" <?php echo !$api_key ? 'disabled title="API-Key fehlt"' : ''; ?>>
            ✨ Neue Posts generieren
          </button>
        </form>
        <?php if (!empty($drafts)): ?>
          <form method="post" action="./linkedin.php" onsubmit="return confirm('Alle Entwürfe löschen?')">
            <input type="hidden" name="action" value="reset">
            <button type="submit" class="btn-secondary">🗑 Zurücksetzen</button>
          </form>
        <?php endif; ?>
        <form method="post" action="./linkedin.php">
          <input type="hidden" name="action" value="api_test">
          <button type="submit" class="btn-secondary" title="Testet die API-Verbindung mit einem Mini-Request">🔌 API testen</button>
        </form>
        <span class="hint">Themen: Digitalisierung &amp; KI · Legacy Transformation</span>
      </div>

      <!-- ══ APPROVED POST ════════════════════════════════════════════════════ -->
      <?php if ($approved): ?>
        <div class="approved-box">
          <h3>✅ Genehmigter Post – bereit für LinkedIn</h3>
          <textarea id="approved-text" onclick="this.select()"><?php echo htmlspecialchars($approved['text']); ?></textarea>
          <div class="approved-meta">
            <button class="btn-copy" onclick="copyApproved()">📋 In Zwischenablage kopieren</button>
            <a href="https://www.linkedin.com/feed/" target="_blank" rel="noopener" class="btn-secondary"
               style="text-decoration:none;display:inline-block;padding:9px 18px;">LinkedIn öffnen →</a>
            <span class="char-count" id="approved-chars"></span>
            <span class="copy-ok" id="copy-ok">✓ Kopiert!</span>
          </div>
        </div>
      <?php endif; ?>

      <!-- ══ PENDING DRAFTS ════════════════════════════════════════════════════ -->
      <?php if (!empty($pending)): ?>
        <div class="form-panel">
          <h4>Entwürfe vom <?php echo htmlspecialchars($pending[0]['generated_at']); ?> – wähle einen aus</h4>
          <div class="draft-grid">
            <?php foreach ($pending as $d): ?>
              <?php $is_edit = ($edit_id === $d['id']); ?>
              <div class="draft-card">
                <div class="draft-topic"><?php echo htmlspecialchars($d['topic']); ?></div>

                <?php if ($is_edit): ?>
                  <!-- Edit mode -->
                  <form method="post" action="./linkedin.php">
                    <input type="hidden" name="action" value="edit_save">
                    <input type="hidden" name="edit_id" value="<?php echo intval($d['id']); ?>">
                    <textarea name="edit_text" class="draft-editarea"><?php echo htmlspecialchars($d['text']); ?></textarea>
                    <div class="draft-actions" style="margin-top:10px;">
                      <button type="submit" class="btn-primary" style="padding:7px 18px;font-size:13px;">Speichern</button>
                      <a href="./linkedin.php" class="btn-secondary" style="padding:7px 18px;font-size:13px;text-decoration:none;">Abbrechen</a>
                    </div>
                  </form>
                <?php else: ?>
                  <!-- Preview mode -->
                  <div class="draft-preview collapsed" id="preview-<?php echo intval($d['id']); ?>">
                    <?php echo htmlspecialchars($d['text']); ?>
                  </div>
                  <div class="draft-chars"><?php echo mb_strlen($d['text']); ?> Zeichen</div>
                  <div class="draft-actions">
                    <form method="post" action="./linkedin.php">
                      <input type="hidden" name="action" value="approve">
                      <input type="hidden" name="approve_id" value="<?php echo intval($d['id']); ?>">
                      <button type="submit" class="btn-approve">✓ Genehmigen</button>
                    </form>
                    <a href="?edit=<?php echo intval($d['id']); ?>" class="btn-edit">Bearbeiten</a>
                    <button class="btn-secondary" style="padding:7px 14px;font-size:12px;"
                      onclick="toggleExpand(<?php echo intval($d['id']); ?>, this)">Mehr anzeigen</button>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      <?php elseif (empty($drafts)): ?>
        <div class="form-panel">
          <div class="empty-state">
            <div class="icon">✍️</div>
            <p>Noch keine Entwürfe vorhanden.<br>Klicke auf <strong>„Neue Posts generieren"</strong> um zu starten.</p>
          </div>
        </div>
      <?php endif; ?>

    </div><!-- /dash-content -->
  </div><!-- /dash-main -->
</div>

<script>
function showLoading() {
  document.getElementById('loading-overlay').classList.add('active');
}

function copyApproved() {
  var ta = document.getElementById('approved-text');
  ta.select();
  document.execCommand('copy');
  var ok = document.getElementById('copy-ok');
  ok.style.display = 'inline';
  setTimeout(function() { ok.style.display = 'none'; }, 2500);
}

function toggleExpand(id, btn) {
  var el = document.getElementById('preview-' + id);
  if (el.classList.contains('collapsed')) {
    el.classList.remove('collapsed');
    btn.textContent = 'Weniger';
  } else {
    el.classList.add('collapsed');
    btn.textContent = 'Mehr anzeigen';
  }
}

// Live char count for approved textarea
(function() {
  var ta  = document.getElementById('approved-text');
  var ctr = document.getElementById('approved-chars');
  if (!ta || !ctr) return;
  function update() { ctr.textContent = ta.value.length + ' Zeichen (max. LinkedIn: 3.000)'; }
  ta.addEventListener('input', update);
  update();
})();
</script>
</body>
</html>
