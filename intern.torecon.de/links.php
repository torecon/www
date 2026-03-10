<?php
require_once __DIR__ . '/check_auth.php';

$links_file = __DIR__ . '/links.json';

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

if ($action === 'link_save') {
    $links = read_json($links_file, array());
    $id    = isset($_POST['id']) ? trim($_POST['id']) : '';
    $item  = array(
        'id'    => $id ? $id : strval(intval(round(microtime(true) * 1000))),
        'title' => isset($_POST['title']) ? trim($_POST['title']) : '',
        'url'   => isset($_POST['url'])   ? trim($_POST['url'])   : '',
        'desc'  => isset($_POST['desc'])  ? trim($_POST['desc'])  : '',
    );
    if ($id) {
        foreach ($links as $k => $v) {
            if ($v['id'] === $id) { $links[$k] = $item; break; }
        }
    } else {
        $links[] = $item;
    }
    write_json($links_file, array_values($links));
    $msg = 'Link gespeichert.';
}

if ($action === 'link_delete') {
    $id    = isset($_POST['del_id']) ? trim($_POST['del_id']) : '';
    $links = read_json($links_file, array());
    $links = array_values(array_filter($links, function($v) use ($id) { return $v['id'] !== $id; }));
    write_json($links_file, $links);
    $msg = 'Link gelöscht.';
}

$links    = read_json($links_file, array());
$edit_lnk = null;
if (isset($_GET['edit'])) {
    $eid = trim($_GET['edit']);
    foreach ($links as $l) {
        if ($l['id'] === $eid) { $edit_lnk = $l; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Linkfavoriten – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:28px; }
    .form-panel h4 { margin:0 0 18px; font-size:16px; }
    .fg { display:flex; flex-direction:column; gap:14px; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input, .fg textarea { border:1px solid rgba(0,0,0,0.18); border-radius:8px;
                               padding:8px 11px; font-size:14px; font-family:inherit;
                               background:#fafafa; resize:vertical; }
    .fg input:focus, .fg textarea:focus { outline:none; border-color:#0071E3; background:#fff; }
    .btn-row { display:flex; gap:10px; margin-top:20px; }
    .btn-primary   { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                     border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-secondary { background:#f5f5f7; color:#333; border:1px solid rgba(0,0,0,0.15);
                     padding:9px 18px; border-radius:8px; font-size:14px; cursor:pointer; }
    .btn-danger { background:#ff3b30; color:#fff; border:none; padding:6px 14px;
                  border-radius:7px; font-size:12px; cursor:pointer; }
    .btn-edit   { background:#f5f5f7; color:#0071E3; border:1px solid rgba(0,113,227,0.25);
                  padding:6px 14px; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; text-decoration:none; }
    .msg { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46;
           border-radius:9px; padding:10px 16px; margin-bottom:20px; font-size:14px; }

    /* Link tiles grid */
    .links-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:16px; }
    .link-tile {
      position:relative;
      background:#fff;
      border:1px solid rgba(0,0,0,0.09);
      border-radius:14px;
      padding:22px 20px 18px;
      transition:box-shadow .18s, border-color .18s;
    }
    .link-tile:hover { box-shadow:0 4px 20px rgba(0,0,0,0.10); border-color:rgba(0,113,227,0.2); }
    .link-tile-icon {
      width:40px; height:40px; border-radius:10px;
      background:linear-gradient(135deg,#0071E3 0%,#34aadc 100%);
      display:flex; align-items:center; justify-content:center;
      margin-bottom:14px;
    }
    .link-tile-icon svg { width:20px; height:20px; fill:#fff; }
    .link-tile h4 { font-size:14px; font-weight:600; margin:0 0 6px; line-height:1.3; }
    .link-tile p  { font-size:13px; color:var(--text-secondary); margin:0 0 14px; line-height:1.5; }
    .link-tile-footer { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
    .link-tile-open {
      display:inline-flex; align-items:center; gap:5px;
      background:#0071E3; color:#fff; text-decoration:none;
      font-size:12px; font-weight:500; padding:5px 13px;
      border-radius:20px; transition:background .15s;
    }
    .link-tile-open:hover { background:#005bbf; }
    .link-tile-actions { display:flex; gap:6px; }
    .link-tile-domain { font-size:11px; color:var(--text-tertiary); margin-bottom:10px; }
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
      <li><a href="./links.php" class="active">🔖 Linkfavoriten</a></li>
      <li><a href="./settings.php">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <div class="dash-main">
    <div class="dash-topbar">
      <h1>Linkfavoriten</h1>
      <span style="font-size:13px;color:var(--text-secondary);"><?php echo count($links); ?> gespeicherte Links</span>
    </div>

    <div class="dash-content">

      <?php if ($msg): ?>
        <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <!-- Form -->
      <div class="form-panel">
        <h4><?php echo $edit_lnk ? 'Link bearbeiten' : 'Neuen Link hinzufügen'; ?></h4>
        <form method="post" action="./links.php">
          <input type="hidden" name="action" value="link_save">
          <?php if ($edit_lnk): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_lnk['id']); ?>">
          <?php endif; ?>
          <div class="fg">
            <label>Bezeichnung
              <input type="text" name="title" required placeholder="z. B. BaFin – Aktuelles"
                value="<?php echo htmlspecialchars($edit_lnk ? $edit_lnk['title'] : ''); ?>">
            </label>
            <label>URL
              <input type="url" name="url" required placeholder="https://…"
                value="<?php echo htmlspecialchars($edit_lnk ? $edit_lnk['url'] : ''); ?>">
            </label>
            <label>Beschreibung (optional)
              <textarea name="desc" rows="2" placeholder="Worum geht es auf dieser Seite?"><?php echo htmlspecialchars($edit_lnk ? $edit_lnk['desc'] : ''); ?></textarea>
            </label>
          </div>
          <div class="btn-row">
            <button type="submit" class="btn-primary"><?php echo $edit_lnk ? 'Speichern' : 'Hinzufügen'; ?></button>
            <?php if ($edit_lnk): ?>
              <a href="./links.php" class="btn-secondary">Abbrechen</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Tiles -->
      <?php if (empty($links)): ?>
        <p style="color:var(--text-secondary);font-size:14px;text-align:center;padding:40px 0;">Noch keine Links gespeichert.</p>
      <?php else: ?>
        <div class="links-grid">
          <?php foreach ($links as $l):
            $host = '';
            if (!empty($l['url'])) {
              $p = parse_url($l['url']);
              $host = isset($p['host']) ? preg_replace('/^www\./', '', $p['host']) : $l['url'];
            }
          ?>
            <div class="link-tile">
              <div class="link-tile-icon">
                <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <h4><?php echo htmlspecialchars($l['title']); ?></h4>
              <?php if ($host): ?><div class="link-tile-domain"><?php echo htmlspecialchars($host); ?></div><?php endif; ?>
              <?php if (!empty($l['desc'])): ?><p><?php echo htmlspecialchars($l['desc']); ?></p><?php endif; ?>
              <div class="link-tile-footer">
                <a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank" rel="noopener" class="link-tile-open">
                  Öffnen
                  <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
                <div class="link-tile-actions">
                  <a href="?edit=<?php echo urlencode($l['id']); ?>" class="btn-edit">Bearbeiten</a>
                  <form method="post" action="./links.php" onsubmit="return confirm('Link löschen?')" style="display:inline;">
                    <input type="hidden" name="action" value="link_delete">
                    <input type="hidden" name="del_id" value="<?php echo htmlspecialchars($l['id']); ?>">
                    <button type="submit" class="btn-danger">Löschen</button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
