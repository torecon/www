<?php
require_once __DIR__ . '/check_auth.php';

$ref_file = __DIR__ . '/references.json';

function read_json($path, $default) {
    if (!file_exists($path)) return $default;
    $raw  = file_get_contents($path);
    $data = json_decode($raw, true);
    return (is_array($data) && count($data) > 0) ? $data : $default;
}

function write_json($path, $data) {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$msg    = '';

// Download JSON
if (isset($_GET['dl']) && $_GET['dl'] === 'references') {
    $data = read_json($ref_file, array());
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="references.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Save (add or update)
if ($action === 'ref_save') {
    $refs = read_json($ref_file, array());
    $id   = isset($_POST['id']) ? trim($_POST['id']) : '';
    $item = array(
        'id'      => $id ? $id : strval(intval(round(microtime(true) * 1000))),
        'name'    => isset($_POST['name'])    ? trim($_POST['name'])    : '',
        'year'    => isset($_POST['year'])    ? trim($_POST['year'])    : '',
        'website' => isset($_POST['website']) ? trim($_POST['website']) : '',
        'slogan'  => isset($_POST['slogan'])  ? trim($_POST['slogan'])  : '',
    );
    if ($id) {
        foreach ($refs as $k => $v) {
            if ($v['id'] === $id) { $refs[$k] = $item; break; }
        }
    } else {
        $refs[] = $item;
    }
    write_json($ref_file, array_values($refs));
    $msg = 'Referenz gespeichert.';
}

// Delete
if ($action === 'ref_delete') {
    $id   = isset($_POST['del_id']) ? trim($_POST['del_id']) : '';
    $refs = read_json($ref_file, array());
    $refs = array_values(array_filter($refs, function($v) use ($id) { return $v['id'] !== $id; }));
    write_json($ref_file, $refs);
    $msg = 'Referenz gelöscht.';
}

$refs     = read_json($ref_file, array());
$edit_ref = null;
if (isset($_GET['edit'])) {
    $eid = trim($_GET['edit']);
    foreach ($refs as $r) {
        if ($r['id'] === $eid) { $edit_ref = $r; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Referenzkunden – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:28px; }
    .form-panel h4 { margin:0 0 18px; font-size:16px; }
    .fg { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input, .fg textarea { border:1px solid rgba(0,0,0,0.18); border-radius:8px;
                               padding:8px 11px; font-size:14px; font-family:inherit;
                               background:#fafafa; resize:vertical; }
    .fg input:focus, .fg textarea:focus { outline:none; border-color:#0071E3; background:#fff; }
    .fg .span2 { grid-column:1/-1; }
    .btn-row { display:flex; gap:10px; margin-top:18px; }
    .btn-primary   { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                     border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-secondary { background:#f5f5f7; color:#333; border:1px solid rgba(0,0,0,0.15);
                     padding:9px 18px; border-radius:8px; font-size:14px; cursor:pointer; }
    .btn-danger { background:#ff3b30; color:#fff; border:none; padding:6px 14px;
                  border-radius:7px; font-size:12px; cursor:pointer; }
    .btn-edit   { background:#f5f5f7; color:#0071E3; border:1px solid rgba(0,113,227,0.25);
                  padding:6px 14px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:500; }
    .item-list { display:flex; flex-direction:column; gap:10px; }
    .item-row  { display:flex; align-items:flex-start; gap:12px; background:#fff;
                 border:1px solid rgba(0,0,0,0.09); border-radius:12px; padding:14px 16px; }
    .item-meta { flex:1; min-width:0; }
    .item-meta strong { font-size:14px; display:block; margin-bottom:3px; }
    .item-meta span   { font-size:12px; color:var(--text-secondary); }
    .item-actions { display:flex; gap:8px; align-items:center; flex-shrink:0; }
    .dl-row { display:flex; gap:12px; margin-bottom:24px; }
    .dl-btn { display:inline-flex; align-items:center; gap:6px; background:#f5f5f7;
              border:1px solid rgba(0,0,0,0.15); padding:8px 18px; border-radius:8px;
              font-size:13px; color:#0071E3; text-decoration:none; font-weight:500; }
    .dl-btn:hover { background:#e8e8ed; }
    .msg { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; border-radius:9px;
           padding:10px 16px; margin-bottom:20px; font-size:14px; }
    @media(max-width:700px) { .fg { grid-template-columns:1fr; } }
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
      <li><a href="./references.php" class="active">🏢 Referenzkunden</a></li>
      <li><a href="./settings.php">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <div class="dash-main">
    <div class="dash-topbar">
      <h1>Referenzkunden</h1>
      <a href="https://www.torecon.de/references.html" style="font-size:13px;color:var(--text-secondary);">← Referenzseite</a>
    </div>

    <div class="dash-content">

      <?php if ($msg): ?>
        <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <div class="dl-row">
        <a href="?dl=references" class="dl-btn">⬇ references.json herunterladen</a>
      </div>
      <p style="font-size:12px;color:var(--text-secondary);margin:-14px 0 22px;">Nach dem Download → via Plesk hochladen nach <strong>torecon.de/data/</strong></p>

      <!-- Form -->
      <div class="form-panel">
        <h4><?php echo $edit_ref ? 'Referenz bearbeiten' : 'Neue Referenz hinzufügen'; ?></h4>
        <form method="post" action="./references.php">
          <input type="hidden" name="action" value="ref_save">
          <?php if ($edit_ref): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_ref['id']); ?>">
          <?php endif; ?>
          <div class="fg">
            <label>Name / Unternehmen
              <input type="text" name="name" required
                value="<?php echo htmlspecialchars($edit_ref ? $edit_ref['name'] : ''); ?>">
            </label>
            <label>Jahr
              <input type="number" name="year" required min="1990" max="2099"
                value="<?php echo htmlspecialchars($edit_ref ? $edit_ref['year'] : date('Y')); ?>">
            </label>
            <label>Website (URL)
              <input type="url" name="website"
                value="<?php echo htmlspecialchars($edit_ref ? $edit_ref['website'] : ''); ?>">
            </label>
            <label class="span2">Kurzbeschreibung / Slogan
              <textarea name="slogan" rows="2"><?php echo htmlspecialchars($edit_ref ? $edit_ref['slogan'] : ''); ?></textarea>
            </label>
          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary"><?php echo $edit_ref ? 'Speichern' : 'Hinzufügen'; ?></button>
            <?php if ($edit_ref): ?>
              <a href="./references.php" class="btn-secondary">Abbrechen</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- List -->
      <div class="form-panel">
        <h4>Vorhandene Referenzen (<?php echo count($refs); ?>)</h4>
        <div class="item-list">
          <?php foreach ($refs as $r): ?>
            <div class="item-row">
              <div class="item-meta">
                <strong><?php echo htmlspecialchars($r['name']); ?> <span style="font-weight:400;color:var(--text-secondary);">(<?php echo htmlspecialchars($r['year']); ?>)</span></strong>
                <span><?php echo htmlspecialchars($r['slogan']); ?></span>
              </div>
              <div class="item-actions">
                <a href="?edit=<?php echo urlencode($r['id']); ?>" class="btn-edit">Bearbeiten</a>
                <form method="post" action="./references.php" onsubmit="return confirm('Referenz löschen?')">
                  <input type="hidden" name="action" value="ref_delete">
                  <input type="hidden" name="del_id" value="<?php echo htmlspecialchars($r['id']); ?>">
                  <button type="submit" class="btn-danger">Löschen</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
