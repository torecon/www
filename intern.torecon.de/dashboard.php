<?php
require_once __DIR__ . '/check_auth.php';

// Load counts from JSON files
function count_json($path) {
    if (!file_exists($path)) return 0;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? count($data) : 0;
}

$count_news   = count_json(__DIR__ . '/news.json');
$count_refs   = count_json(__DIR__ . '/references.json');
$count_ticker = count_json(__DIR__ . '/ticker.json');
$count_links  = count_json(__DIR__ . '/links.json');

// Recent news for the panel (read raw JSON, no inline fallback needed here)
$news_items = array();
if (file_exists(__DIR__ . '/news.json')) {
    $raw = json_decode(file_get_contents(__DIR__ . '/news.json'), true);
    if (is_array($raw)) $news_items = array_slice($raw, 0, 5);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
</head>
<body>
<script>window.TORECON_ROOT = 'https://www.torecon.de/';</script>

<div class="dashboard-wrap">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">tore<span>con</span></div>
    <ul class="sidebar-nav">
      <li><a href="./dashboard.php" class="active">📊 <span data-i18n="dash_nav_overview">Übersicht</span></a></li>
      <li><a href="./news.php">📰 <span data-i18n="dash_nav_news">News verwalten</span></a></li>
      <li><a href="./references.php">🏢 <span data-i18n="dash_nav_clients">Referenzkunden</span></a></li>
      <li><a href="./links.php">🔖 Linkfavoriten</a></li>
      <li><a href="./settings.php">⚙️ <span data-i18n="dash_nav_settings">Einstellungen</span></a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php" data-i18n="logout">Abmelden</a>
    </div>
  </aside>

  <!-- Main content -->
  <div class="dash-main">
    <div class="dash-topbar">
      <h1 data-i18n="dash_title">Dashboard</h1>
      <div style="display:flex;gap:8px;align-items:center;">
        <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);margin-left:12px;">← Website</a>
      </div>
    </div>

    <div class="dash-content">

      <!-- KPIs -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label" data-i18n="dash_news">News-Artikel</div>
          <div class="kpi-value"><?php echo $count_news; ?></div>
          <div class="kpi-delta"><a href="./news.php" style="color:var(--accent);text-decoration:none;">Verwalten →</a></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Ticker-Meldungen</div>
          <div class="kpi-value"><?php echo $count_ticker; ?></div>
          <div class="kpi-delta"><a href="./news.php?tab=ticker" style="color:var(--accent);text-decoration:none;">Verwalten →</a></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label" data-i18n="dash_clients">Referenzkunden</div>
          <div class="kpi-value"><?php echo $count_refs; ?></div>
          <div class="kpi-delta"><a href="./references.php" style="color:var(--accent);text-decoration:none;">Verwalten →</a></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Linkfavoriten</div>
          <div class="kpi-value"><?php echo $count_links; ?></div>
          <div class="kpi-delta"><a href="./links.php" style="color:var(--accent);text-decoration:none;">Verwalten →</a></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Recent news -->
        <div class="dash-panel">
          <h3 data-i18n="dash_recent_news">Zuletzt veröffentlicht</h3>
          <ul class="dash-list">
            <?php if (empty($news_items)): ?>
              <li><span style="color:var(--text-tertiary);">Keine Artikel vorhanden.</span></li>
            <?php else: foreach ($news_items as $item): ?>
              <li>
                <span><?php echo htmlspecialchars($item['title_de']); ?></span>
                <span style="font-size:12px;color:var(--text-tertiary);flex-shrink:0;"><?php echo htmlspecialchars($item['date']); ?></span>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>

        <!-- Quick links -->
        <div class="dash-panel">
          <h3 data-i18n="dash_quick_links">Schnellzugriff</h3>
          <ul class="dash-list">
            <li><span>Website-Startseite</span><a href="https://www.torecon.de/" target="_blank" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Finanztrends</span><a href="https://www.torecon.de/news.html" target="_blank" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Referenzen</span><a href="https://www.torecon.de/references.html" target="_blank" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Kontaktseite</span><a href="https://www.torecon.de/contact.html" target="_blank" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Leistungen</span><a href="https://www.torecon.de/services.html" target="_blank" style="font-size:13px;">Öffnen →</a></li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://www.torecon.de/js/i18n.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof applyTranslations === 'function') applyTranslations();
  });
</script>
</body>
</html>
