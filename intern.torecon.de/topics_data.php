<?php
// ── Öffentlicher Endpunkt – liefert Schwerpunktthemen als JSON ───────────────
// Kein Login erforderlich. Wird für Homepage-Integration genutzt.
// Quelle: _pillars.php (Single-Source innerhalb intern.torecon.de).

require_once __DIR__ . '/_pillars.php';

$origin  = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed = array('https://www.torecon.de', 'https://torecon.de');
if (in_array($origin, $allowed)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

// Nur Felder ausliefern, die der Frontend-Konsument braucht — Hashtags etc. bleiben intern.
$out = array();
foreach (torecon_pillars() as $p) {
    $out[] = array(
        'id'       => $p['id'],
        'icon'     => $p['icon'],
        'label_de' => $p['label_de'],
        'sub_de'   => $p['sub_de'],
        'label_en' => $p['label_en'],
        'sub_en'   => $p['sub_en'],
    );
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
