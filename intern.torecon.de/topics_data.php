<?php
// ── Öffentlicher Endpunkt – liefert Schwerpunktthemen als JSON ───────────────
// Kein Login erforderlich. Wird für Homepage-Integration genutzt.
// CORS erlaubt für torecon.de (beide Varianten).
//
// Pflege-Quelle (Single Source of Truth):
// ~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md (Pillar 1–9)

$origin  = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed = array('https://www.torecon.de', 'https://torecon.de');
if (in_array($origin, $allowed)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

echo json_encode(array(
    array('id'=>'geldpolitik',     'icon'=>'📈',  'label_de'=>'Geldpolitik & Zinsen',                       'sub_de'=>'EZB, Leitzins, Inflation',                  'label_en'=>'Monetary Policy & Rates',           'sub_en'=>'ECB, key rates, inflation'),
    array('id'=>'cx',              'icon'=>'📱',  'label_de'=>'Digitale Customer Experience & Omnichannel', 'sub_de'=>'CX-Strategie, App, digitale Filiale',       'label_en'=>'Digital Customer Experience & Omnichannel', 'sub_en'=>'CX strategy, app, digital branch'),
    array('id'=>'regulierung',     'icon'=>'⚖️', 'label_de'=>'Regulierung & Compliance',                   'sub_de'=>'Basel IV, BaFin, EBA',                      'label_en'=>'Regulation & Compliance',           'sub_en'=>'Basel IV, BaFin, EBA'),
    array('id'=>'digitalisierung', 'icon'=>'🤖',  'label_de'=>'Digitalisierung & KI',                       'sub_de'=>'Fintech, AI, Kreditscoring',                'label_en'=>'Digitalisation & AI',               'sub_en'=>'Fintech, AI, credit scoring'),
    array('id'=>'esg',             'icon'=>'🌱',  'label_de'=>'Nachhaltigkeit & ESG',                       'sub_de'=>'CSRD, Green Finance, Taxonomie',            'label_en'=>'Sustainability & ESG',              'sub_en'=>'CSRD, green finance, taxonomy'),
    array('id'=>'datenplattform',  'icon'=>'📊',  'label_de'=>'Datenplattform für KI',                      'sub_de'=>'AI-Readiness, Data Mesh, Governance',       'label_en'=>'Data Platform for AI',              'sub_en'=>'AI readiness, data mesh, governance'),
    array('id'=>'agentic-ai',      'icon'=>'🧩',  'label_de'=>'Agentic AI in der Praxis',                   'sub_de'=>'Agent-Orchestrierung, Memory, Tool-Use',    'label_en'=>'Agentic AI in Practice',            'sub_en'=>'Agent orchestration, memory, tool use'),
    array('id'=>'legacy',          'icon'=>'🔄',  'label_de'=>'Legacy Transformation',                      'sub_de'=>'Kernbanksysteme, Migration, Modernisierung','label_en'=>'Legacy Transformation',             'sub_en'=>'Core banking, migration, modernisation'),
    array('id'=>'pricing',         'icon'=>'💼',  'label_de'=>'Pricing',                                    'sub_de'=>'Outcome-Based, Sprint-Tier, Quality-Gates', 'label_en'=>'Pricing',                           'sub_en'=>'Outcome-based, sprint-tier, quality gates'),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
