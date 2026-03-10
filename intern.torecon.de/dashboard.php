<?php require_once __DIR__ . '/check_auth.php'; ?>
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
      <li><a href="#">✏️ <span data-i18n="dash_nav_news">Finanztrends verwalten</span></a></li>
      <li><a href="./references.php">🏢 <span data-i18n="dash_nav_clients">Referenzkunden</span></a></li>
      <li><a href="#">⚙️ <span data-i18n="dash_nav_settings">Einstellungen</span></a></li>
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
        <button class="lang-btn" data-lang="de" onclick="setLang('de')" style="border-color:rgba(0,0,0,0.2);color:#333;">DE</button>
        <button class="lang-btn" data-lang="en" onclick="setLang('en')" style="border-color:rgba(0,0,0,0.2);color:#333;">EN</button>
        <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);margin-left:12px;">← Website</a>
      </div>
    </div>

    <div class="dash-content">

      <!-- KPIs -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label" data-i18n="dash_visits">Seitenbesuche</div>
          <div class="kpi-value">—</div>
          <div class="kpi-delta">Live-Daten nach Deployment</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label" data-i18n="dash_news">Artikel</div>
          <div class="kpi-value" id="kpi-news">8</div>
          <div class="kpi-delta">↑ aktuell</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label" data-i18n="dash_clients">Referenzkunden</div>
          <div class="kpi-value">2</div>
          <div class="kpi-delta">DGRV, HTW Saarland</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Sprachen</div>
          <div class="kpi-value">2</div>
          <div class="kpi-delta">DE / EN</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Recent news -->
        <div class="dash-panel">
          <h3 data-i18n="dash_recent_news">Zuletzt veröffentlicht</h3>
          <ul class="dash-list" id="dash-news-list">
            <!-- rendered by JS -->
          </ul>
        </div>

        <!-- Quick links -->
        <div class="dash-panel">
          <h3 data-i18n="dash_quick_links">Schnellzugriff</h3>
          <ul class="dash-list">
            <li><span>Website-Startseite</span><a href="https://www.torecon.de/" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Finanztrends</span><a href="https://www.torecon.de/news.html" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Kontaktseite</span><a href="https://www.torecon.de/contact.html" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Leistungen</span><a href="https://www.torecon.de/services.html" style="font-size:13px;">Öffnen →</a></li>
            <li><span>Referenzen</span><a href="./references.php" style="font-size:13px;">Verwalten →</a></li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://www.torecon.de/js/i18n.js"></script>
<script src="https://www.torecon.de/js/news.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    applyTranslations();

    const list = document.getElementById('dash-news-list');
    if (list) {
      const lang = currentLang || 'de';
      list.innerHTML = NEWS_DATA.slice(0, 5).map(item => `
        <li>
          <span>${lang === 'de' ? item.title_de : item.title_en}</span>
          <span style="font-size:12px;color:var(--text-tertiary);">${item.date}</span>
        </li>
      `).join('');
    }
  });
</script>
</body>
</html>
