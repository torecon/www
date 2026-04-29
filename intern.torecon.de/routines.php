<?php
require_once __DIR__ . '/check_auth.php';

// ── Geplante Routinen / Cloud-Agents ─────────────────────────────────────────
// Statische Liste der claude.ai-Code-Routinen, die für /www/-Sites Pflege-Tasks
// übernehmen. Jede Routine hat eine eindeutige trigger_id, die in der URL
// https://claude.ai/code/routines/<trigger_id> manuell aufgerufen werden kann
// (Button „Run now").
//
// Neue Routinen hier ergänzen, sobald sie über /schedule oder die Web-UI
// angelegt werden.
$routines = array(
    array(
        'id'          => 'trig_01FFi6QaGfeM6uAGbNPTdj4D',
        'site'        => 'torecon.de',
        'name'        => 'Finanztrends — monatlicher News-Refresh',
        'cadence'     => 'Am 1. jedes Monats, 09:05 Berlin',
        'cron'        => '0 7 1 * *',
        'repo'        => 'torecon/www',
        'description' => 'Recherchiert pro Themen-Pillar (9 Stück) je einen aktuellen verifizierten Online-Artikel mit Deep-Link, ersetzt damit data/news.json + js/news.js (Fallback) auf der Homepage, bumpt den Cache-Buster in allen HTML-Files und aktualisiert sitemap.xml lastmod. Commit + Push direkt auf main; Deploy per FTP danach manuell.',
        'created_at'  => '2026-04-29',
    ),
);

$active_page = 'routines';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Routinen – torecon</title>
  <link rel="stylesheet" href="https://www.torecon.de/css/style.css">
  <style>
    .form-panel { background:#fff; border:1px solid rgba(0,0,0,0.09); border-radius:14px;
                  padding:24px 28px; margin-bottom:28px; }
    .form-panel > h4 { margin:0 0 8px; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px; }
    .form-panel > .panel-sub { margin:0 0 22px; font-size:13px; color:var(--text-secondary); line-height:1.6; }
    .btn-primary { background:#0071E3; color:#fff; border:none; padding:11px 22px;
                   border-radius:8px; font-size:14px; cursor:pointer; font-weight:500;
                   text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
    .btn-primary:hover { background:#005bb5; color:#fff; }
    .btn-secondary { background:#f5f5f7; color:#333; border:1px solid rgba(0,0,0,0.15);
                     padding:10px 18px; border-radius:8px; font-size:13px; cursor:pointer;
                     text-decoration:none; display:inline-flex; align-items:center; gap:6px; }

    .routine-card {
      border:1px solid rgba(0,0,0,0.10); border-radius:12px;
      padding:22px 24px; margin-bottom:18px; background:#fafbfc;
      display:flex; flex-direction:column; gap:14px;
    }
    .routine-card:hover { border-color:rgba(0,113,227,0.30); }
    .routine-head {
      display:flex; align-items:flex-start; justify-content:space-between;
      gap:16px; flex-wrap:wrap;
    }
    .routine-title {
      font-size:15px; font-weight:700; line-height:1.35;
    }
    .routine-title .site {
      display:inline-block; font-size:11px; font-weight:700;
      letter-spacing:0.05em; text-transform:uppercase;
      color:#0071E3; margin-right:8px;
    }
    .routine-badge {
      font-size:10px; font-weight:700; letter-spacing:0.06em;
      text-transform:uppercase; padding:3px 10px; border-radius:999px;
      background:#dcfce7; color:#15803d; white-space:nowrap;
    }
    .routine-meta {
      display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
      gap:8px 18px; font-size:12px; color:var(--text-secondary);
    }
    .routine-meta strong { color:var(--text); font-weight:600; }
    .routine-meta code {
      background:#fff; border:1px solid rgba(0,0,0,0.10);
      border-radius:4px; padding:1px 5px; font-size:11px;
    }
    .routine-desc {
      font-size:13px; color:var(--text); line-height:1.6;
      padding:14px 16px; background:#fff; border-radius:8px;
      border:1px solid rgba(0,0,0,0.06);
    }
    .routine-actions {
      display:flex; gap:10px; flex-wrap:wrap; align-items:center;
      padding-top:4px;
    }
    .info-box {
      background:#f0f7ff; border:1px solid rgba(0,113,227,0.20);
      border-radius:10px; padding:13px 16px; font-size:13px;
      color:#1a4a7a; line-height:1.6;
    }
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
      <li><a href="./routines.php" class="active">🔄 Routinen</a></li>
      <li><a href="./settings.php">⚙️ Einstellungen</a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="./logout.php">Abmelden</a>
    </div>
  </aside>

  <div class="dash-main">
    <div class="dash-topbar">
      <h1>🔄 Routinen</h1>
      <a href="https://www.torecon.de/" style="font-size:13px;color:var(--text-secondary);">← Website</a>
    </div>

    <div class="dash-content">

      <div class="form-panel">
        <h4>Geplante Cloud-Agenten</h4>
        <p class="panel-sub">
          Automatisierte Pflege-Tasks für die torecon-Sites. Laufen auf claude.ai-Cloud-Infrastruktur,
          unabhängig von deinem Mac. Manueller Trigger über den Button „Routine öffnen" (öffnet
          claude.ai in neuem Tab — dort dann „Run now" klicken).
        </p>

        <?php foreach ($routines as $r): ?>
        <div class="routine-card">
          <div class="routine-head">
            <div class="routine-title">
              <span class="site"><?php echo htmlspecialchars($r['site']); ?></span><br>
              <?php echo htmlspecialchars($r['name']); ?>
            </div>
            <span class="routine-badge">aktiv</span>
          </div>
          <div class="routine-meta">
            <div><strong>Cadence:</strong> <?php echo htmlspecialchars($r['cadence']); ?></div>
            <div><strong>Cron:</strong> <code><?php echo htmlspecialchars($r['cron']); ?></code></div>
            <div><strong>Repo:</strong> <code><?php echo htmlspecialchars($r['repo']); ?></code></div>
            <div><strong>Angelegt:</strong> <?php echo htmlspecialchars($r['created_at']); ?></div>
          </div>
          <div class="routine-desc">
            <?php echo htmlspecialchars($r['description']); ?>
          </div>
          <div class="routine-actions">
            <a href="https://claude.ai/code/routines/<?php echo htmlspecialchars($r['id']); ?>"
               target="_blank" rel="noopener" class="btn-primary">
              🚀 Routine öffnen / manuell triggern →
            </a>
            <a href="https://github.com/<?php echo htmlspecialchars($r['repo']); ?>/commits/main"
               target="_blank" rel="noopener" class="btn-secondary">
              GitHub-Commits
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="info-box">
        ℹ️ <strong>Neue Routinen anlegen:</strong> über Claude Code im Terminal mit
        <code>/schedule</code> oder direkt im Browser unter
        <a href="https://claude.ai/code/routines" target="_blank" rel="noopener" style="color:#1a4a7a;">claude.ai/code/routines</a>.
        Nach dem Anlegen <strong>hier in <code>routines.php</code> manuell ergänzen</strong>
        (Array <code>$routines</code> oben in der Datei).
      </div>

    </div>
  </div>
</div>
</body>
</html>
