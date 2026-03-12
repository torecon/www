<?php
require_once __DIR__ . '/check_auth.php';

$news_file   = __DIR__ . '/news.json';
$ticker_file = __DIR__ . '/ticker.json';

// ── helpers ──────────────────────────────────────────────────────────────────
function read_json($path, $default) {
    if (!file_exists($path)) return $default;
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return (is_array($data) && count($data) > 0) ? $data : $default;
}

function write_json($path, $data) {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ── actions ──────────────────────────────────────────────────────────────────
$action = isset($_POST['action']) ? $_POST['action'] : '';
$tab    = (isset($_POST['tab']) && $_POST['tab'] === 'ticker') ? 'ticker' : 'news';
$msg    = '';

// Download JSON
if (isset($_GET['dl'])) {
    if ($_GET['dl'] === 'news') {
        $data = read_json($news_file, array());
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="news.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($_GET['dl'] === 'ticker') {
        $data = read_json($ticker_file, array());
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="ticker.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ── NEWS actions ─────────────────────────────────────────────────────────────
if ($action === 'news_save') {
    $news = read_json($news_file, array());
    $id   = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $item = array(
        'id'         => $id ? $id : intval(round(microtime(true) * 1000)),
        'date'       => isset($_POST['date'])       ? trim($_POST['date'])       : '',
        'tag_de'     => isset($_POST['tag_de'])     ? trim($_POST['tag_de'])     : '',
        'tag_en'     => isset($_POST['tag_en'])     ? trim($_POST['tag_en'])     : '',
        'title_de'   => isset($_POST['title_de'])   ? trim($_POST['title_de'])   : '',
        'title_en'   => isset($_POST['title_en'])   ? trim($_POST['title_en'])   : '',
        'excerpt_de' => isset($_POST['excerpt_de']) ? trim($_POST['excerpt_de']) : '',
        'excerpt_en' => isset($_POST['excerpt_en']) ? trim($_POST['excerpt_en']) : '',
        'url'        => isset($_POST['url'])        ? trim($_POST['url'])        : '',
    );
    if ($id) {
        // update existing
        foreach ($news as $k => $v) {
            if ($v['id'] == $id) { $news[$k] = $item; break; }
        }
    } else {
        array_unshift($news, $item);
    }
    write_json($news_file, $news);
    $msg = 'Artikel gespeichert.';
    $tab = 'news';
}

if ($action === 'news_delete') {
    $id   = intval($_POST['del_id']);
    $news = read_json($news_file, array());
    $news = array_values(array_filter($news, function($v) use ($id) { return $v['id'] != $id; }));
    write_json($news_file, $news);
    $msg = 'Artikel gelöscht.';
    $tab = 'news';
}

if ($action === 'news_reorder') {
    $order = isset($_POST['order']) ? $_POST['order'] : array();
    $news  = read_json($news_file, array());
    $map   = array();
    foreach ($news as $item) { $map[$item['id']] = $item; }
    $reordered = array();
    foreach ($order as $id) { if (isset($map[$id])) $reordered[] = $map[$id]; }
    write_json($news_file, $reordered);
    header('Content-Type: application/json');
    echo json_encode(array('ok' => true));
    exit;
}

// ── TICKER actions ────────────────────────────────────────────────────────────
if ($action === 'ticker_save') {
    $ticker = read_json($ticker_file, array());
    $idx    = isset($_POST['idx']) && $_POST['idx'] !== '' ? intval($_POST['idx']) : -1;
    $item   = array(
        'title' => isset($_POST['title']) ? trim($_POST['title']) : '',
        'link'  => isset($_POST['link'])  ? trim($_POST['link'])  : '',
    );
    if ($idx >= 0 && isset($ticker[$idx])) {
        $ticker[$idx] = $item;
    } else {
        array_unshift($ticker, $item);
    }
    write_json($ticker_file, $ticker);
    $msg = 'Ticker-Meldung gespeichert.';
    $tab = 'ticker';
}

if ($action === 'ticker_delete') {
    $idx    = intval($_POST['del_idx']);
    $ticker = read_json($ticker_file, array());
    array_splice($ticker, $idx, 1);
    write_json($ticker_file, $ticker);
    $msg = 'Ticker-Meldung gelöscht.';
    $tab = 'ticker';
}

// ── load data for display ─────────────────────────────────────────────────────
$news_items   = read_json($news_file,   array());
$ticker_items = read_json($ticker_file, array());
$edit_news    = null;
$edit_ticker_idx = -1;

if (isset($_GET['edit_news'])) {
    $eid = intval($_GET['edit_news']);
    foreach ($news_items as $item) {
        if ($item['id'] == $eid) { $edit_news = $item; break; }
    }
    $tab = 'news';
}
if (isset($_GET['edit_ticker'])) {
    $edit_ticker_idx = intval($_GET['edit_ticker']);
    $tab = 'ticker';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News verwalten – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .ntab-bar { display:flex; gap:4px; margin-bottom:24px; }
    .ntab { padding:8px 22px; border-radius:8px; border:1.5px solid rgba(0,113,227,0.25);
            background:none; font-size:14px; cursor:pointer; color:var(--text-secondary); font-weight:500; }
    .ntab.active { background:#0071E3; color:#fff; border-color:#0071E3; }

    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:28px; }
    .form-panel h4 { margin:0 0 18px; font-size:16px; }
    .fg { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; }
    .fg.single { grid-template-columns:1fr; }
    .fg label { display:flex; flex-direction:column; gap:5px; font-size:13px; color:var(--text-secondary); }
    .fg input, .fg textarea { border:1px solid rgba(0,0,0,0.18); border-radius:8px;
                               padding:8px 11px; font-size:14px; font-family:inherit;
                               background:#fafafa; resize:vertical; }
    .fg input:focus, .fg textarea:focus { outline:none; border-color:#0071E3; background:#fff; }
    .fg .span2 { grid-column:1/-1; }
    .btn-row { display:flex; gap:10px; margin-top:18px; }
    .btn-primary { background:#0071E3; color:#fff; border:none; padding:9px 22px;
                   border-radius:8px; font-size:14px; cursor:pointer; font-weight:500; }
    .btn-secondary { background:#f5f5f7; color:#333; border:1px solid rgba(0,0,0,0.15);
                     padding:9px 18px; border-radius:8px; font-size:14px; cursor:pointer; }
    .btn-danger { background:#ff3b30; color:#fff; border:none; padding:6px 14px;
                  border-radius:7px; font-size:12px; cursor:pointer; }
    .btn-edit { background:#f5f5f7; color:#0071E3; border:1px solid rgba(0,113,227,0.25);
                padding:6px 14px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:500; }

    .item-list { display:flex; flex-direction:column; gap:10px; }
    .item-row { display:flex; align-items:flex-start; gap:12px; background:#fff;
                border:1px solid rgba(0,0,0,0.09); border-radius:12px; padding:14px 16px; }
    .item-row .drag-handle { cursor:grab; color:#aaa; font-size:18px; padding-top:2px; user-select:none; }
    .item-meta { flex:1; min-width:0; }
    .item-meta strong { font-size:14px; display:block; margin-bottom:3px; }
    .item-meta span { font-size:12px; color:var(--text-secondary); }
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

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">tore<span>con</span></div>
    <ul class="sidebar-nav">
      <li><a href="./dashboard.php">📊 Übersicht</a></li>
      <li><a href="./news.php" class="active">📰 News verwalten</a></li>
      <li><a href="./linkedin.php">💼 LinkedIn Posts</a></li>
      <li><a href="./links.php">🔖 Linkfavoriten</a></li>
      <li><a href="./settings.php">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <!-- Main content -->
  <div class="dash-main">
    <div class="dash-topbar">
      <h1>News verwalten</h1>
      <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);">← Website</a>
    </div>

    <div class="dash-content">

      <?php if ($msg): ?>
        <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <!-- Tabs -->
      <div class="ntab-bar">
        <button class="ntab <?php echo $tab === 'news' ? 'active' : ''; ?>" onclick="switchTab('news')">📰 News-Kacheln</button>
        <button class="ntab <?php echo $tab === 'ticker' ? 'active' : ''; ?>" onclick="switchTab('ticker')">📡 Ticker</button>
      </div>

      <!-- Download-Buttons -->
      <div class="dl-row">
        <a href="?dl=news" class="dl-btn" id="dl-news-btn">⬇ news.json herunterladen</a>
        <a href="?dl=ticker" class="dl-btn" id="dl-ticker-btn">⬇ ticker.json herunterladen</a>
      </div>
      <p style="font-size:12px;color:var(--text-secondary);margin:-14px 0 22px;">Nach dem Download → via Plesk hochladen nach <strong>torecon.de/data/</strong></p>

      <!-- ═══ NEWS TAB ══════════════════════════════════════════════════════ -->
      <div id="tab-news" style="display:<?php echo $tab === 'news' ? 'block' : 'none'; ?>">

        <!-- Form: Add / Edit -->
        <div class="form-panel">
          <h4><?php echo $edit_news ? 'Artikel bearbeiten' : 'Neuen Artikel hinzufügen'; ?></h4>
          <form method="post" action="./news.php">
            <input type="hidden" name="action" value="news_save">
            <input type="hidden" name="tab" value="news">
            <?php if ($edit_news): ?>
              <input type="hidden" name="id" value="<?php echo intval($edit_news['id']); ?>">
            <?php endif; ?>
            <div class="fg">
              <label>Datum (YYYY-MM-DD)
                <input type="date" name="date" required
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['date'] : date('Y-m-d')); ?>">
              </label>
              <label>URL (Quelle)
                <input type="url" name="url" required
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['url'] : ''); ?>">
              </label>
              <label>Tag DE
                <input type="text" name="tag_de" required maxlength="40"
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['tag_de'] : ''); ?>">
              </label>
              <label>Tag EN
                <input type="text" name="tag_en" required maxlength="40"
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['tag_en'] : ''); ?>">
              </label>
              <label class="span2">Titel (DE)
                <input type="text" name="title_de" required
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['title_de'] : ''); ?>">
              </label>
              <label class="span2">Titel (EN)
                <input type="text" name="title_en" required
                  value="<?php echo htmlspecialchars($edit_news ? $edit_news['title_en'] : ''); ?>">
              </label>
              <label class="span2">Teaser (DE)
                <textarea name="excerpt_de" rows="3" required><?php echo htmlspecialchars($edit_news ? $edit_news['excerpt_de'] : ''); ?></textarea>
              </label>
              <label class="span2">Teaser (EN)
                <textarea name="excerpt_en" rows="3" required><?php echo htmlspecialchars($edit_news ? $edit_news['excerpt_en'] : ''); ?></textarea>
              </label>
            </div>
            <div class="btn-row">
              <button type="submit" class="btn-primary"><?php echo $edit_news ? 'Speichern' : 'Hinzufügen'; ?></button>
              <?php if ($edit_news): ?>
                <a href="./news.php" class="btn-secondary">Abbrechen</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- List -->
        <div class="form-panel">
          <h4>Vorhandene Artikel (<?php echo count($news_items); ?>)</h4>
          <div class="item-list" id="news-sortable">
            <?php foreach ($news_items as $item): ?>
              <div class="item-row" data-id="<?php echo intval($item['id']); ?>">
                <span class="drag-handle" title="Reihenfolge ändern">&#9776;</span>
                <div class="item-meta">
                  <strong><?php echo htmlspecialchars($item['title_de']); ?></strong>
                  <span><?php echo htmlspecialchars($item['date']); ?> &middot; <?php echo htmlspecialchars($item['tag_de']); ?></span>
                </div>
                <div class="item-actions">
                  <a href="?edit_news=<?php echo intval($item['id']); ?>" class="btn-edit">Bearbeiten</a>
                  <form method="post" action="./news.php" onsubmit="return confirm('Artikel löschen?')">
                    <input type="hidden" name="action" value="news_delete">
                    <input type="hidden" name="tab" value="news">
                    <input type="hidden" name="del_id" value="<?php echo intval($item['id']); ?>">
                    <button type="submit" class="btn-danger">Löschen</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /tab-news -->

      <!-- ═══ TICKER TAB ════════════════════════════════════════════════════ -->
      <div id="tab-ticker" style="display:<?php echo $tab === 'ticker' ? 'block' : 'none'; ?>">

        <!-- Form: Add / Edit -->
        <div class="form-panel">
          <h4><?php echo $edit_ticker_idx >= 0 ? 'Ticker-Meldung bearbeiten' : 'Neue Ticker-Meldung'; ?></h4>
          <form method="post" action="./news.php">
            <input type="hidden" name="action" value="ticker_save">
            <input type="hidden" name="tab" value="ticker">
            <input type="hidden" name="idx" value="<?php echo $edit_ticker_idx; ?>">
            <div class="fg single">
              <label>Meldungstext
                <input type="text" name="title" required
                  value="<?php echo htmlspecialchars($edit_ticker_idx >= 0 && isset($ticker_items[$edit_ticker_idx]) ? $ticker_items[$edit_ticker_idx]['title'] : ''); ?>">
              </label>
              <label>Link (URL)
                <input type="url" name="link"
                  value="<?php echo htmlspecialchars($edit_ticker_idx >= 0 && isset($ticker_items[$edit_ticker_idx]) ? $ticker_items[$edit_ticker_idx]['link'] : ''); ?>">
              </label>
            </div>
            <div class="btn-row">
              <button type="submit" class="btn-primary"><?php echo $edit_ticker_idx >= 0 ? 'Speichern' : 'Hinzufügen'; ?></button>
              <?php if ($edit_ticker_idx >= 0): ?>
                <a href="./news.php?tab=ticker" class="btn-secondary">Abbrechen</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- List -->
        <div class="form-panel">
          <h4>Ticker-Meldungen (<?php echo count($ticker_items); ?>)</h4>
          <div class="item-list">
            <?php foreach ($ticker_items as $idx => $item): ?>
              <div class="item-row">
                <div class="item-meta">
                  <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                  <?php if (!empty($item['link'])): ?>
                    <span><a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank" rel="noopener" style="color:#0071E3;"><?php echo htmlspecialchars($item['link']); ?></a></span>
                  <?php endif; ?>
                </div>
                <div class="item-actions">
                  <a href="?edit_ticker=<?php echo $idx; ?>" class="btn-edit">Bearbeiten</a>
                  <form method="post" action="./news.php" onsubmit="return confirm('Meldung löschen?')">
                    <input type="hidden" name="action" value="ticker_delete">
                    <input type="hidden" name="tab" value="ticker">
                    <input type="hidden" name="del_idx" value="<?php echo $idx; ?>">
                    <button type="submit" class="btn-danger">Löschen</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /tab-ticker -->

    </div><!-- /dash-content -->
  </div><!-- /dash-main -->
</div>

<script>
function switchTab(name) {
  document.getElementById('tab-news').style.display   = name === 'news'   ? 'block' : 'none';
  document.getElementById('tab-ticker').style.display = name === 'ticker' ? 'block' : 'none';
  document.querySelectorAll('.ntab').forEach(function(btn) {
    btn.classList.toggle('active', btn.textContent.indexOf(name === 'news' ? 'Kacheln' : 'Ticker') >= 0);
  });
}

// Drag-to-reorder for news items
(function() {
  var list = document.getElementById('news-sortable');
  if (!list) return;
  var dragged = null;

  list.addEventListener('dragstart', function(e) {
    dragged = e.target.closest('.item-row');
    e.dataTransfer.effectAllowed = 'move';
  });
  list.addEventListener('dragover', function(e) {
    e.preventDefault();
    var target = e.target.closest('.item-row');
    if (target && target !== dragged) {
      var rect = target.getBoundingClientRect();
      var after = e.clientY > rect.top + rect.height / 2;
      list.insertBefore(dragged, after ? target.nextSibling : target);
    }
  });
  list.addEventListener('dragend', function() {
    var order = Array.from(list.querySelectorAll('.item-row')).map(function(r) {
      return r.getAttribute('data-id');
    });
    var fd = new FormData();
    fd.append('action', 'news_reorder');
    order.forEach(function(id) { fd.append('order[]', id); });
    fetch('./news.php', { method: 'POST', body: fd }).catch(function() {});
    dragged = null;
  });

  // Enable draggable on rows
  list.querySelectorAll('.item-row').forEach(function(row) {
    row.setAttribute('draggable', 'true');
  });
})();
</script>
</body>
</html>
