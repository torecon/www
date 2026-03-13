<?php
// ── Öffentlicher Endpunkt – liefert Schwerpunktthemen als JSON ───────────────
// Kein Login erforderlich. Wird für zukünftige Homepage-Integration genutzt.
// CORS erlaubt für torecon.de (beide Varianten).

$origin  = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed = array('https://www.torecon.de', 'https://torecon.de');
if (in_array($origin, $allowed)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

$file = __DIR__ . '/topics_settings.json';

if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    // Fallback: Standardthemen
    echo json_encode(array(
        array('id'=>'geldpolitik',    'icon'=>'📈', 'label_de'=>'Geldpolitik & Zinsen',                       'sub_de'=>'EZB, Leitzins, Inflation',                'label_en'=>'Monetary Policy & Rates',                   'sub_en'=>'ECB, key rates, inflation'),
        array('id'=>'cx',            'icon'=>'📱', 'label_de'=>'Digitale Customer Experience & Omnichannel', 'sub_de'=>'CX-Strategie, App, digitale Filiale',     'label_en'=>'Digital Customer Experience & Omnichannel',  'sub_en'=>'CX strategy, app, digital branch'),
        array('id'=>'regulierung',   'icon'=>'⚖️', 'label_de'=>'Regulierung & Compliance',                   'sub_de'=>'Basel IV, BaFin, EBA',                    'label_en'=>'Regulation & Compliance',                   'sub_en'=>'Basel IV, BaFin, EBA'),
        array('id'=>'digitalisierung','icon'=>'🤖','label_de'=>'Digitalisierung & KI',                        'sub_de'=>'Fintech, AI, Kreditscoring',               'label_en'=>'Digitalisation & AI',                       'sub_en'=>'Fintech, AI, credit scoring'),
        array('id'=>'esg',           'icon'=>'🌱', 'label_de'=>'Nachhaltigkeit & ESG',                        'sub_de'=>'CSRD, Green Finance, Taxonomie',           'label_en'=>'Sustainability & ESG',                      'sub_en'=>'CSRD, green finance, taxonomy'),
        array('id'=>'bankplanung',   'icon'=>'🗺️', 'label_de'=>'Strategische Bankplanung',                   'sub_de'=>'CIR, PCR, Gesamtbanksteuerung',            'label_en'=>'Strategic Bank Planning',                   'sub_en'=>'CIR, PCR, bank-wide management'),
        array('id'=>'international', 'icon'=>'🌍', 'label_de'=>'Internationale Märkte',                       'sub_de'=>'EBRD, IMF, Osteuropa',                     'label_en'=>'International Markets',                     'sub_en'=>'EBRD, IMF, Eastern Europe'),
        array('id'=>'legacy',        'icon'=>'🔄', 'label_de'=>'Legacy Transformation',                       'sub_de'=>'Kernbanksysteme, Migration, Modernisierung','label_en'=>'Legacy Transformation',                    'sub_en'=>'Core banking, migration, modernisation'),
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
