<?php
// DEBUG – nach Diagnose wieder entfernen
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/config.php';

$drafts_file          = __DIR__ . '/linkedin_drafts.json';
$backup_file          = __DIR__ . '/linkedin_backup.json';
$li_settings_file     = __DIR__ . '/linkedin_settings.json';
$topics_settings_file = __DIR__ . '/topics_settings.json';

// ── helpers ──────────────────────────────────────────────────────────────────
function li_read($path) {
    if (!file_exists($path)) return array();
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : array();
}

function li_write($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Add posts to persistent backup – never overwrites existing entries, deduplicates by ID
function li_backup_add($path, $new_posts) {
    $existing = array();
    if (file_exists($path)) {
        $raw = json_decode(file_get_contents($path), true);
        if (is_array($raw)) $existing = $raw;
    }
    $existing_ids = array();
    foreach ($existing as $e) { $existing_ids[$e['id']] = true; }
    foreach ($new_posts as $p) {
        if (!isset($existing_ids[$p['id']])) {
            $p['backup_status'] = 'ready';
            $p['posted_at']     = null;
            $existing[]         = $p;
        }
    }
    file_put_contents($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Update backup_status of a specific post by ID
function li_backup_set_status($path, $id, $status) {
    $data = array();
    if (file_exists($path)) {
        $raw = json_decode(file_get_contents($path), true);
        if (is_array($raw)) $data = $raw;
    }
    foreach ($data as $k => $p) {
        if ($p['id'] == $id) {
            $data[$k]['backup_status'] = $status;
            if ($status === 'posted') $data[$k]['posted_at'] = date('Y-m-d');
        }
    }
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Delete a post from backup by ID
function li_backup_delete($path, $id) {
    $data = array();
    if (file_exists($path)) {
        $raw = json_decode(file_get_contents($path), true);
        if (is_array($raw)) $data = $raw;
    }
    $filtered = array();
    foreach ($data as $p) { if ($p['id'] != $id) $filtered[] = $p; }
    file_put_contents($path, json_encode($filtered, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Themen aus topics_settings.json laden (Fallback: hardcoded)
function li_load_topics($path) {
    $defaults = array(
        array('label_de'=>'Geldpolitik & Zinsen',                       'sub_de'=>'EZB, Leitzins, Inflation'),
        array('label_de'=>'Digitale Customer Experience & Omnichannel', 'sub_de'=>'CX-Strategie, App, digitale Filiale'),
        array('label_de'=>'Regulierung & Compliance',                   'sub_de'=>'Basel IV, BaFin, EBA'),
        array('label_de'=>'Digitalisierung & KI',                       'sub_de'=>'Fintech, AI, Kreditscoring'),
        array('label_de'=>'Nachhaltigkeit & ESG',                       'sub_de'=>'CSRD, Green Finance, Taxonomie'),
        array('label_de'=>'Strategische Bankplanung',                   'sub_de'=>'CIR, PCR, Gesamtbanksteuerung'),
        array('label_de'=>'Internationale Märkte',                      'sub_de'=>'EBRD, IMF, Osteuropa'),
        array('label_de'=>'Legacy Transformation',                      'sub_de'=>'Kernbanksysteme, Migration, Modernisierung'),
    );
    // Fallback-Strings ohne Closures (PHP 5.x kompatibel)
    $default_strings = array();
    foreach ($defaults as $t) {
        $default_strings[] = $t['label_de'] . ' (' . $t['sub_de'] . ')';
    }
    if (!file_exists($path)) return $default_strings;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || count($data) < 8) return $default_strings;
    $result = array();
    foreach ($data as $t) {
        if (isset($t['label_de']) && isset($t['sub_de'])) {
            $result[] = $t['label_de'] . ' (' . $t['sub_de'] . ')';
        }
    }
    return $result;
}

function li_read_settings($path) {
    $defaults = array(
        'topic1'     => 'Digitalisierung & KI (Fintech, AI, Kreditscoring)',
        'topic2'     => 'Legacy Transformation (Kernbanksysteme, Migration, Modernisierung)',
        'post_hint'  => '',
        'post_count' => 4,
    );
    if (!file_exists($path)) return $defaults;
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) return $defaults;
    // Migrate old key 'tone_hint' → 'post_hint'
    if (!isset($data['post_hint']) && isset($data['tone_hint'])) {
        $data['post_hint'] = $data['tone_hint'];
    }
    return array_merge($defaults, $data);
}

// Fix unescaped newlines inside JSON string values (PHP 5.x compatible)
function li_escape_newlines_in_json($json) {
    $result    = '';
    $in_string = false;
    $escape    = false;
    $len       = strlen($json);
    for ($i = 0; $i < $len; $i++) {
        $c = $json[$i];
        if ($escape) {
            $result .= $c;
            $escape  = false;
        } elseif ($c === '\\' && $in_string) {
            $result .= $c;
            $escape  = true;
        } elseif ($c === '"') {
            $in_string = !$in_string;
            $result   .= $c;
        } elseif ($in_string && ($c === "\n" || $c === "\r")) {
            $result .= ($c === "\n") ? '\\n' : '\\r';
        } else {
            $result .= $c;
        }
    }
    return $result;
}

// Extract JSON array from Claude response using bracket-matching (no regex, fully robust)
function li_parse_json_response($text) {
    $text = trim($text);

    // Find the first '[' – start of JSON array
    $start = strpos($text, '[');
    if ($start === false) return null;

    // Walk forward tracking depth to find the matching ']'
    $depth     = 0;
    $in_string = false;
    $escape    = false;
    $end       = -1;
    $len       = strlen($text);
    for ($i = $start; $i < $len; $i++) {
        $c = $text[$i];
        if ($escape)                        { $escape = false; continue; }
        if ($c === '\\' && $in_string)      { $escape = true;  continue; }
        if ($c === '"')                     { $in_string = !$in_string; continue; }
        if ($in_string)                     { continue; }
        if ($c === '[' || $c === '{')       { $depth++; }
        elseif ($c === ']' || $c === '}')   {
            $depth--;
            if ($depth === 0) { $end = $i; break; }
        }
    }
    if ($end === -1) return null;

    $json = substr($text, $start, $end - $start + 1);

    // Try direct parse
    $posts = json_decode($json, true);
    if (is_array($posts) && count($posts) > 0) return $posts;

    // Fix unescaped literal newlines inside strings and retry
    $fixed = li_escape_newlines_in_json($json);
    $posts = json_decode($fixed, true);
    if (is_array($posts) && count($posts) > 0) return $posts;

    return null;
}

// Single API call for exactly $n posts
function li_call_claude_single($api_key, $today, $n, $topic1, $topic2, $post_hint) {
    $prompt = 'Du bist Thomas Reinke, Unternehmensberater fuer Banken und Kreditinstitute (torecon.de), 25+ Jahre Erfahrung. '
            . 'Deine Kernthemen: ' . $topic1 . ' und ' . $topic2 . '. '
            . "\n\nHeutiges Datum: " . $today . "\n\n"
            . "Erstelle " . $n . " LinkedIn-Posts zu aktuellen, praxisrelevanten Themen aus diesen zwei Bereichen. Abwechslung ist wichtig.\n\n"
            . "Jeder Post muss:\n"
            . "- Mit einem starken Hook beginnen (1 Satz: provokante These, ueberraschende Zahl oder offene Frage)\n"
            . "- Ca. 900-1.300 Zeichen lang sein (ohne Hashtags)\n"
            . "- Aus Ich-Perspektive geschrieben sein, praxisnah und ohne Buzzword-Bingo\n"
            . "- Einen konkreten Insight oder Handlungsempfehlung enthalten\n"
            . "- Mit einer Frage oder einem klaren Call-to-Action enden\n"
            . "- Mit 4-5 Hashtags abschliessen (z.B. #Digitalisierung #Banking #KI #LegacyTransformation #Fintech)\n\n"
            . ($post_hint !== '' ? "Zusaetzliche Hinweise (Ton, Stil & Inhalt):\n" . $post_hint . "\n\n" : '')
            . "Antworte AUSSCHLIESSLICH als valides JSON-Array, kein Text davor oder danach:\n"
            . '[{"topic":"Kurztitel max 40 Zeichen","text":"Vollstaendiger Post\n\n#Hashtag1 #Hashtag2"},...]';

    $payload = json_encode(array(
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => max(2000, $n * 1200),
        'messages'   => array(array('role' => 'user', 'content' => $prompt))
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

    if ($curl_err) return array('error' => 'cURL-Fehler: ' . $curl_err);
    $decoded = json_decode($response, true);
    if (!isset($decoded['content'][0]['text'])) {
        if (isset($decoded['error']['message']))
            return array('error' => 'HTTP ' . $http_code . ': ' . $decoded['error']['message']);
        return array('error' => 'HTTP ' . $http_code . ' · ' . substr($response, 0, 200));
    }
    $posts = li_parse_json_response($decoded['content'][0]['text']);
    if ($posts !== null) return $posts;
    return array('error' => 'Parse-Fehler: ' . substr($decoded['content'][0]['text'], 0, 200));
}

function li_call_claude($api_key, $today, $settings) {
    $topic1     = $settings['topic1'];
    $topic2     = $settings['topic2'];
    $post_count = intval($settings['post_count']);
    $post_hint  = isset($settings['post_hint']) ? trim($settings['post_hint']) : (isset($settings['tone_hint']) ? trim($settings['tone_hint']) : '');

    // Split large requests into two calls to avoid server timeout
    if ($post_count >= 4) {
        $n1 = (int)ceil($post_count / 2);  // e.g. 5→3, 4→2
        $n2 = $post_count - $n1;           // e.g. 5→2, 4→2
        $r1 = li_call_claude_single($api_key, $today, $n1, $topic1, $topic2, $post_hint);
        if (isset($r1['error'])) return $r1;
        $r2 = li_call_claude_single($api_key, $today, $n2, $topic1, $topic2, $post_hint);
        if (isset($r2['error'])) return $r2;
        return array_merge($r1, $r2);
    }

    // 1–3 posts: single call
    $prompt = 'Du bist Thomas Reinke, Unternehmensberater fuer Banken und Kreditinstitute (torecon.de), 25+ Jahre Erfahrung. '
            . 'Deine Kernthemen: ' . $topic1 . ' und ' . $topic2 . '. '
            . "\n\nHeutiges Datum: " . $today . "\n\n"
            . "Erstelle " . $post_count . " LinkedIn-Posts zu aktuellen, praxisrelevanten Themen aus diesen zwei Bereichen. Abwechslung ist wichtig.\n\n"
            . "Jeder Post muss:\n"
            . "- Mit einem starken Hook beginnen (1 Satz: provokante These, ueberraschende Zahl oder offene Frage)\n"
            . "- Ca. 900-1.300 Zeichen lang sein (ohne Hashtags)\n"
            . "- Aus Ich-Perspektive geschrieben sein, praxisnah und ohne Buzzword-Bingo\n"
            . "- Einen konkreten Insight oder Handlungsempfehlung enthalten\n"
            . "- Mit einer Frage oder einem klaren Call-to-Action enden\n"
            . "- Mit 4-5 Hashtags abschliessen (z.B. #Digitalisierung #Banking #KI #LegacyTransformation #Fintech)\n\n"
            . ($post_hint !== '' ? "Zusaetzliche Hinweise (Ton, Stil & Inhalt):\n" . $post_hint . "\n\n" : '')
            . "Antworte AUSSCHLIESSLICH als valides JSON-Array, kein Text davor oder danach:\n"
            . '[{"topic":"Kurztitel max 40 Zeichen","text":"Vollstaendiger Post\n\n#Hashtag1 #Hashtag2"},...]';

    $payload = json_encode(array(
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => max(2000, $post_count * 1200),
        'messages'   => array(array('role' => 'user', 'content' => $prompt))
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

    if ($curl_err) return array('error' => 'cURL-Fehler: ' . $curl_err);

    $decoded = json_decode($response, true);
    if (!isset($decoded['content'][0]['text'])) {
        if (isset($decoded['error']['message']))
            return array('error' => 'HTTP ' . $http_code . ': ' . $decoded['error']['message']);
        return array('error' => 'HTTP ' . $http_code . ' · ' . substr($response, 0, 200));
    }

    $posts = li_parse_json_response($decoded['content'][0]['text']);
    if ($posts !== null) return $posts;
    return array('error' => 'Parse-Fehler: ' . substr($decoded['content'][0]['text'], 0, 200));
}

function li_call_claude_series($api_key, $today, $topic, $count, $post_hint = '') {
    $parts_hint = '';
    if ($count == 3) {
        $parts_hint = "Post 1: Einstieg – Warum ist dieses Thema gerade fuer Banken/Genossenschaftsbanken dringend relevant?\n"
                    . "Post 2: Vertiefung – Konkretes Praxisbeispiel oder aktuelle Entwicklung aus dem Markt.\n"
                    . "Post 3: Handlungsempfehlung – Was sollten Entscheider in Kreditinstituten jetzt konkret tun?";
    } elseif ($count == 4) {
        $parts_hint = "Post 1: Einstieg – Warum ist dieses Thema gerade fuer Banken/Genossenschaftsbanken dringend relevant?\n"
                    . "Post 2: Problemanalyse – Wo scheitert die Praxis heute noch, und warum?\n"
                    . "Post 3: Best Practice – Was machen die Vorreiter anders?\n"
                    . "Post 4: Handlungsempfehlung – Was sollten Entscheider in Kreditinstituten jetzt konkret tun?";
    } else {
        for ($i = 1; $i <= $count; $i++) {
            $parts_hint .= "Post " . $i . ": Blickwinkel " . $i . " auf das Thema.\n";
        }
    }

    $prompt = 'Du bist Thomas Reinke, Unternehmensberater fuer Banken und Kreditinstitute (torecon.de), 25+ Jahre Erfahrung. '
            . "\nHeutiges Datum: " . $today . "\n\n"
            . 'Erstelle eine LinkedIn-Postserie von ' . $count . ' Beitraegen zum Thema: ' . $topic . "\n\n"
            . "Aufbau der Serie:\n" . $parts_hint . "\n\n"
            . "Regeln fuer jeden Post:\n"
            . "- Jeder Post funktioniert fuer sich allein – kein 'wie ich gestern schrieb'\n"
            . "- Mit einem starken Hook beginnen (1 Satz: provokante These, ueberraschende Zahl oder offene Frage)\n"
            . "- Ca. 900-1.300 Zeichen lang (ohne Hashtags)\n"
            . "- Ich-Perspektive, praxisnah, ohne Buzzword-Bingo\n"
            . "- Konkreter Insight oder Handlungsempfehlung enthalten\n"
            . "- Mit einer Frage oder einem klaren Call-to-Action enden\n"
            . "- Mit 4-5 Hashtags abschliessen\n\n"
            . ($post_hint !== '' ? "Zusaetzliche Hinweise (Ton, Stil & Inhalt):\n" . $post_hint . "\n\n" : '')
            . "Antworte AUSSCHLIESSLICH als valides JSON-Array, kein Text davor oder danach:\n"
            . '[{"part":1,"topic":"Kurztitel max 40 Zeichen","text":"Vollstaendiger Post\n\n#Hash1 #Hash2"},...]';

    $payload = json_encode(array(
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => intval($count * 1000 + 500),
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

    if ($curl_err) return array('error' => 'cURL-Fehler: ' . $curl_err);

    $decoded = json_decode($response, true);
    if (!isset($decoded['content'][0]['text'])) {
        if (isset($decoded['error']['message']))
            return array('error' => 'HTTP ' . $http_code . ': ' . $decoded['error']['message']);
        return array('error' => 'HTTP ' . $http_code . ' · ' . substr($response, 0, 200));
    }

    $posts = li_parse_json_response($decoded['content'][0]['text']);
    if ($posts !== null) return $posts;
    return array('error' => 'Parse-Fehler: ' . substr($decoded['content'][0]['text'], 0, 200));
}

// ── AJAX handler (generate / generate_series) ────────────────────────────────
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    $ajax_action = isset($_POST['action']) ? $_POST['action'] : '';
    $ajax_key    = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '';
    header('Content-Type: application/json; charset=utf-8');
    @set_time_limit(180);
    @ini_set('max_execution_time', 180);

    if (!$ajax_key) {
        echo json_encode(array('error' => 'Kein Claude API-Key konfiguriert.'));
        exit;
    }

    $ajax_settings = li_read_settings(__DIR__ . '/linkedin_settings.json');

    if ($ajax_action === 'generate') {
        $result = li_call_claude($ajax_key, date('Y-m-d'), $ajax_settings);
        if (isset($result['error'])) {
            echo json_encode(array('error' => $result['error']));
        } else {
            $ajax_drafts = array();
            foreach ($result as $i => $post) {
                $ajax_drafts[] = array(
                    'id'           => intval(round(microtime(true) * 1000)) + $i,
                    'generated_at' => date('Y-m-d'),
                    'topic'        => isset($post['topic']) ? trim($post['topic']) : 'Post ' . ($i + 1),
                    'text'         => isset($post['text'])  ? trim($post['text'])  : '',
                    'status'       => 'pending',
                );
            }
            li_write(__DIR__ . '/linkedin_drafts.json', $ajax_drafts);
            li_backup_add(__DIR__ . '/linkedin_backup.json', $ajax_drafts);
            echo json_encode(array('ok' => true, 'count' => count($ajax_drafts)));
        }
        exit;
    }

    if ($ajax_action === 'generate_series') {
        $series_topic = isset($_POST['series_topic']) ? trim($_POST['series_topic']) : '';
        $series_count = isset($_POST['series_count']) ? intval($_POST['series_count']) : 3;
        if ($series_count < 2) $series_count = 2;
        if ($series_count > 5) $series_count = 5;
        $result = li_call_claude_series($ajax_key, date('Y-m-d'), $series_topic, $series_count,
                      isset($ajax_settings['post_hint']) ? $ajax_settings['post_hint'] : '');
        if (isset($result['error'])) {
            echo json_encode(array('error' => $result['error']));
        } else {
            $series_id  = intval(round(microtime(true) * 1000));
            $new_series = array();
            foreach ($result as $i => $post) {
                $part = isset($post['part']) ? intval($post['part']) : ($i + 1);
                $new_series[] = array(
                    'id'           => $series_id + $i,
                    'generated_at' => date('Y-m-d'),
                    'topic'        => isset($post['topic']) ? trim($post['topic']) : 'Teil ' . $part,
                    'text'         => isset($post['text'])  ? trim($post['text'])  : '',
                    'status'       => 'pending',
                    'type'         => 'series',
                    'series_id'    => $series_id,
                    'series_part'  => $part,
                    'series_total' => $series_count,
                    'series_topic' => $series_topic,
                );
            }
            $existing = li_read(__DIR__ . '/linkedin_drafts.json');
            $kept = array();
            foreach ($existing as $d) {
                if (!isset($d['type']) || $d['type'] !== 'series') $kept[] = $d;
            }
            li_write(__DIR__ . '/linkedin_drafts.json', array_merge($kept, $new_series));
            li_backup_add(__DIR__ . '/linkedin_backup.json', $new_series);
            echo json_encode(array('ok' => true, 'count' => count($new_series)));
        }
        exit;
    }

    echo json_encode(array('error' => 'Unbekannte Aktion.'));
    exit;
}

// ── actions ──────────────────────────────────────────────────────────────────
$action      = isset($_POST['action']) ? $_POST['action'] : '';
$msg         = '';
$msg_type    = 'success';
$api_key     = defined('CLAUDE_API_KEY') ? CLAUDE_API_KEY : '';
$li_settings = li_read_settings($li_settings_file);
$topics      = li_load_topics($topics_settings_file);

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
        @set_time_limit(180);
        @ini_set('max_execution_time', 180);
        $result = li_call_claude($api_key, date('Y-m-d'), $li_settings);
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
            li_backup_add($backup_file, $drafts);
            $msg = count($drafts) . ' Entwuerfe wurden generiert. Waehle einen aus und genehmige ihn.';
        }
    }
}

if ($action === 'generate_series') {
    if (!$api_key) {
        $msg      = 'Kein Claude API-Key hinterlegt.';
        $msg_type = 'error';
    } else {
        @set_time_limit(180);
        @ini_set('max_execution_time', 180);
        $series_topic = isset($_POST['series_topic']) ? trim($_POST['series_topic']) : '';
        $series_count = isset($_POST['series_count']) ? intval($_POST['series_count']) : 3;
        if ($series_count < 2) $series_count = 2;
        if ($series_count > 5) $series_count = 5;

        $result = li_call_claude_series($api_key, date('Y-m-d'), $series_topic, $series_count, isset($li_settings['post_hint']) ? $li_settings['post_hint'] : '');
        if (isset($result['error'])) {
            $msg      = 'API-Fehler: ' . $result['error'];
            $msg_type = 'error';
        } else {
            $series_id  = intval(round(microtime(true) * 1000));
            $new_series = array();
            foreach ($result as $i => $post) {
                $part = isset($post['part']) ? intval($post['part']) : ($i + 1);
                $new_series[] = array(
                    'id'           => $series_id + $i,
                    'generated_at' => date('Y-m-d'),
                    'topic'        => isset($post['topic']) ? trim($post['topic']) : 'Teil ' . $part,
                    'text'         => isset($post['text'])  ? trim($post['text'])  : '',
                    'status'       => 'pending',
                    'type'         => 'series',
                    'series_id'    => $series_id,
                    'series_part'  => $part,
                    'series_total' => $series_count,
                    'series_topic' => $series_topic,
                );
            }
            // Keep existing non-series drafts, replace all previous series
            $existing = li_read($drafts_file);
            $kept = array();
            foreach ($existing as $d) {
                if (!isset($d['type']) || $d['type'] !== 'series') $kept[] = $d;
            }
            li_write($drafts_file, array_merge($kept, $new_series));
            li_backup_add($backup_file, $new_series);
            $msg = $series_count . '-teilige Serie zu "' . $series_topic . '" generiert.';
        }
    }
}

if ($action === 'approve') {
    $approve_id   = intval($_POST['approve_id']);
    $drafts       = li_read($drafts_file);
    $approved_post = null;
    foreach ($drafts as $k => $d) {
        if ($d['id'] == $approve_id) {
            $drafts[$k]['status'] = 'approved';
            $approved_post        = $drafts[$k];
        }
    }
    li_write($drafts_file, $drafts);
    if ($approved_post !== null) {
        li_backup_add($backup_file, array($approved_post));
    }
    $msg = 'Post ins Backup übernommen – du findest ihn unter „Bereit zum Posten".';
}

if ($action === 'queue_series') {
    $queue_sid = isset($_POST['queue_sid']) ? intval($_POST['queue_sid']) : 0;
    $drafts    = li_read($drafts_file);
    $to_backup = array();
    foreach ($drafts as $k => $d) {
        if (isset($d['type']) && $d['type'] === 'series'
            && isset($d['series_id']) && $d['series_id'] == $queue_sid
            && ($d['status'] === 'pending' || $d['status'] === 'queued')) {
            $drafts[$k]['status'] = 'approved';
            $to_backup[]          = $drafts[$k];
        }
    }
    li_write($drafts_file, $drafts);
    if (!empty($to_backup)) {
        li_backup_add($backup_file, $to_backup);
    }
    $msg = count($to_backup) . ' Serie-Posts ins Backup übernommen – du findest sie unter „Bereit zum Posten".';
}


if ($action === 'backup_mark_posted') {
    $bid = intval($_POST['backup_id']);
    li_backup_set_status($backup_file, $bid, 'posted');
    $msg = 'Post als gepostet markiert.';
}

if ($action === 'backup_mark_ready') {
    $bid = intval($_POST['backup_id']);
    li_backup_set_status($backup_file, $bid, 'ready');
    $msg = 'Post zurück in "Bereit zum Posten" gesetzt.';
}

if ($action === 'backup_delete') {
    $bid = intval($_POST['backup_id']);
    li_backup_delete($backup_file, $bid);
    $msg = 'Post aus Backup gelöscht.';
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
$drafts        = li_read($drafts_file);
$backup_all    = li_read($backup_file);
// Sort backup: newest first
usort($backup_all, function($a, $b) { return $b['id'] - $a['id']; });
$backup_ready  = array();
$backup_posted = array();
foreach ($backup_all as $b) {
    $bs = isset($b['backup_status']) ? $b['backup_status'] : 'ready';
    if ($bs === 'posted') $backup_posted[] = $b;
    else                  $backup_ready[]  = $b;
}
$pending       = array();
$series_groups = array();
$edit_id       = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

foreach ($drafts as $d) {
    // Only show pending posts (approved/rejected/posted are done)
    if ($d['status'] !== 'pending') continue;
    if (isset($d['type']) && $d['type'] === 'series') {
        $sid = $d['series_id'];
        if (!isset($series_groups[$sid])) {
            $series_groups[$sid] = array(
                'topic' => isset($d['series_topic']) ? $d['series_topic'] : '',
                'total' => isset($d['series_total']) ? $d['series_total'] : 1,
                'date'  => $d['generated_at'],
                'posts' => array(),
            );
        }
        $series_groups[$sid]['posts'][] = $d;
    } else {
        $pending[] = $d;
    }
}
foreach ($series_groups as $sid => $sg) {
    usort($series_groups[$sid]['posts'], function($a, $b) { return $a['series_part'] - $b['series_part']; });
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

    /* ── Backup Archive ─────────────────────────────────── */
    .backup-section { margin-top:40px; }

    .backup-section-header {
      display:flex; align-items:center; justify-content:space-between;
      cursor:pointer; user-select:none; padding:18px 24px;
      background:#fff; border:1.5px solid rgba(0,0,0,0.09);
      border-radius:14px; margin-bottom:0; transition:border-color 0.15s;
    }
    .backup-section-header:hover { border-color:rgba(0,113,227,0.3); }
    .backup-section-header.open { border-radius:14px 14px 0 0; border-bottom:none; }
    .backup-section-title { display:flex; align-items:center; gap:10px; font-size:15px; font-weight:600; }
    .backup-badge {
      font-size:11px; font-weight:700; padding:2px 9px; border-radius:20px; white-space:nowrap;
    }
    .backup-badge.ready  { background:#dbeafe; color:#1d4ed8; }
    .backup-badge.posted { background:#d1fae5; color:#065f46; }
    .backup-chevron { font-size:12px; color:var(--text-secondary); transition:transform 0.2s; }
    .backup-chevron.open { transform:rotate(180deg); }

    .backup-body {
      display:none; background:#fafafa;
      border:1.5px solid rgba(0,0,0,0.09); border-top:none;
      border-radius:0 0 14px 14px; padding:20px 24px;
    }
    .backup-body.open { display:block; }

    .backup-subsection { margin-bottom:28px; }
    .backup-subsection:last-child { margin-bottom:0; }
    .backup-sublabel {
      font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.07em;
      margin-bottom:12px; display:flex; align-items:center; gap:8px;
    }
    .backup-sublabel.ready  { color:#1d4ed8; }
    .backup-sublabel.posted { color:#065f46; }
    .backup-sublabel-dot {
      width:8px; height:8px; border-radius:50%; display:inline-block;
    }
    .backup-sublabel-dot.ready  { background:#3b82f6; }
    .backup-sublabel-dot.posted { background:#22c55e; }

    .backup-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    @media(max-width:900px) { .backup-grid { grid-template-columns:1fr; } }

    .backup-card {
      background:#fff; border-radius:12px; padding:16px 18px;
      display:flex; flex-direction:column; gap:10px;
      border:1.5px solid rgba(0,0,0,0.08);
      border-left:4px solid #93c5fd;
      transition:box-shadow 0.15s;
    }
    .backup-card:hover { box-shadow:0 2px 12px rgba(0,0,0,0.07); }
    .backup-card.posted { border-left-color:#86efac; background:#f0fdf4; }

    .backup-card-meta { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }
    .backup-card-topic { font-size:12px; font-weight:700; color:#1d4ed8; text-transform:uppercase; letter-spacing:0.04em; }
    .backup-card.posted .backup-card-topic { color:#15803d; }
    .backup-card-date { font-size:11px; color:var(--text-secondary); }
    .backup-card-series-badge {
      font-size:10px; background:#ede9fe; color:#6d28d9; border-radius:20px;
      padding:2px 8px; font-weight:600; white-space:nowrap;
    }
    .backup-card.posted .backup-card-series-badge { background:#dcfce7; color:#15803d; }
    .backup-card-posted-badge {
      font-size:10px; background:#d1fae5; color:#065f46; border-radius:20px;
      padding:2px 8px; font-weight:600; white-space:nowrap;
    }

    .backup-preview { font-size:12.5px; color:var(--text); line-height:1.6; white-space:pre-wrap; }
    .backup-preview.collapsed { display:-webkit-box; -webkit-line-clamp:4; -webkit-box-orient:vertical; overflow:hidden; }
    .backup-chars { font-size:11px; color:var(--text-secondary); }

    .backup-actions { display:flex; gap:7px; flex-wrap:wrap; margin-top:2px; }
    .btn-backup-posted {
      background:#dcfce7; color:#15803d; border:1px solid #86efac;
      padding:6px 13px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:600;
    }
    .btn-backup-posted:hover { background:#bbf7d0; }
    .btn-backup-undo {
      background:#f5f5f7; color:#555; border:1px solid rgba(0,0,0,0.15);
      padding:6px 13px; border-radius:7px; font-size:12px; cursor:pointer;
    }
    .btn-backup-copy {
      background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;
      padding:6px 13px; border-radius:7px; font-size:12px; cursor:pointer; font-weight:500;
    }
    .btn-backup-copy:hover { background:#dbeafe; }
    .btn-backup-delete {
      background:transparent; color:#9ca3af; border:1px solid rgba(0,0,0,0.12);
      padding:6px 11px; border-radius:7px; font-size:12px; cursor:pointer; margin-left:auto;
    }
    .btn-backup-delete:hover { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }

    .backup-empty { text-align:center; padding:28px 16px; color:var(--text-secondary); font-size:13px; }
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

      <!-- Ajax error display -->
      <div id="ajax-error" class="msg-error" style="display:none;"></div>

      <!-- Generate bar -->
      <div class="generate-bar">
        <form method="post" action="./linkedin.php" onsubmit="return ajaxGenerate(this)">
          <input type="hidden" name="action" value="generate">
          <button type="submit" class="btn-primary" <?php echo !$api_key ? 'disabled title="API-Key fehlt"' : ''; ?>>
            ✨ Posts generieren
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
        <span class="hint">Themen aus Einstellungen · <?php echo intval($li_settings['post_count']); ?> Posts</span>
      </div>

      <!-- Serie generieren -->
      <div class="form-panel" style="margin-bottom:28px;">
        <h4>📋 LinkedIn-Serie generieren</h4>
        <form method="post" action="./linkedin.php" onsubmit="return ajaxGenerate(this)">
          <input type="hidden" name="action" value="generate_series">
          <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <label style="flex:1;min-width:220px;font-size:13px;color:var(--text-secondary);display:flex;flex-direction:column;gap:5px;">
              Thema
              <select name="series_topic" style="border:1px solid rgba(0,0,0,0.18);border-radius:8px;padding:8px 11px;font-size:14px;font-family:inherit;background:#fafafa;">
                <?php foreach ($topics as $t): ?>
                  <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label style="font-size:13px;color:var(--text-secondary);display:flex;flex-direction:column;gap:5px;">
              Teile
              <select name="series_count" style="border:1px solid rgba(0,0,0,0.18);border-radius:8px;padding:8px 11px;font-size:14px;font-family:inherit;background:#fafafa;">
                <option value="3">3 Posts</option>
                <option value="4">4 Posts</option>
                <option value="5">5 Posts</option>
              </select>
            </label>
            <button type="submit" class="btn-primary" <?php echo !$api_key ? 'disabled title="API-Key fehlt"' : ''; ?>>
              📋 Serie generieren
            </button>
          </div>
        </form>
      </div>

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

      <?php elseif (empty($pending) && empty($series_groups)): ?>
        <div class="form-panel">
          <div class="empty-state">
            <div class="icon">✍️</div>
            <p>Noch keine Entwürfe vorhanden.<br>Klicke auf <strong>„Posts generieren"</strong> oder <strong>„Serie generieren"</strong> um zu starten.</p>
          </div>
        </div>
      <?php endif; ?>

      <!-- ══ SERIE ════════════════════════════════════════════════════════════ -->
      <?php foreach ($series_groups as $sid => $sg): ?>
        <div class="form-panel">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
            <h4 style="margin:0;">📋 Serie: <?php echo htmlspecialchars($sg['topic']); ?> &nbsp;<span style="font-size:12px;font-weight:400;color:var(--text-secondary);">· <?php echo count($sg['posts']); ?> Teile · <?php echo htmlspecialchars($sg['date']); ?></span></h4>
            <form method="post" action="./linkedin.php">
              <input type="hidden" name="action" value="queue_series">
              <input type="hidden" name="queue_sid" value="<?php echo intval($sid); ?>">
              <button type="submit" class="btn-secondary" style="font-size:13px;padding:7px 16px;border-color:rgba(0,113,227,0.3);color:#0071E3;"
                title="Alle Posts dieser Serie ins Backup übernehmen – dort jederzeit verfügbar">
                📥 Alle ins Backup
              </button>
            </form>
          </div>
          <div class="draft-grid">
            <?php foreach ($sg['posts'] as $d): ?>
              <?php $is_edit = ($edit_id === $d['id']); ?>
              <div class="draft-card">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                  <div class="draft-topic"><?php echo htmlspecialchars($d['topic']); ?></div>
                  <span style="font-size:11px;background:#e8f5e9;color:#1a7f37;border-radius:20px;padding:2px 9px;font-weight:600;white-space:nowrap;">
                    Teil <?php echo intval($d['series_part']); ?>/<?php echo intval($d['series_total']); ?>
                  </span>
                </div>

                <?php if ($is_edit): ?>
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
      <?php endforeach; ?>


      <!-- ══ BACKUP ARCHIVE ════════════════════════════════════════════════════ -->
      <?php
        $backup_total = count($backup_ready) + count($backup_posted);
      ?>
      <div class="backup-section">

        <!-- Header: Bereit zum Posten -->
        <div class="backup-section-header <?php echo !empty($backup_ready) ? 'open' : ''; ?>"
             onclick="toggleBackup('ready')" id="backup-hdr-ready">
          <div class="backup-section-title">
            <span>📋 Bereit zum Posten</span>
            <?php if (!empty($backup_ready)): ?>
              <span class="backup-badge ready"><?php echo count($backup_ready); ?> Posts</span>
            <?php endif; ?>
          </div>
          <span class="backup-chevron <?php echo !empty($backup_ready) ? 'open' : ''; ?>" id="backup-chv-ready">▼</span>
        </div>
        <div class="backup-body <?php echo !empty($backup_ready) ? 'open' : ''; ?>" id="backup-body-ready">
          <?php if (empty($backup_ready)): ?>
            <div class="backup-empty">Noch keine Posts im Backup. Generiere Posts – sie erscheinen automatisch hier.</div>
          <?php else: ?>
            <div class="backup-grid">
              <?php foreach ($backup_ready as $bp): ?>
                <?php
                  $bp_is_series = isset($bp['type']) && $bp['type'] === 'series';
                  $bp_preview_id = 'bkp-' . intval($bp['id']);
                ?>
                <div class="backup-card">
                  <div class="backup-card-meta">
                    <span class="backup-card-topic"><?php echo htmlspecialchars($bp['topic']); ?></span>
                    <?php if ($bp_is_series): ?>
                      <span class="backup-card-series-badge">
                        Serie · Teil <?php echo intval($bp['series_part']); ?>/<?php echo intval($bp['series_total']); ?>
                      </span>
                    <?php endif; ?>
                    <span class="backup-card-date"><?php echo htmlspecialchars($bp['generated_at']); ?></span>
                  </div>
                  <div class="backup-preview collapsed" id="<?php echo $bp_preview_id; ?>">
                    <?php echo htmlspecialchars($bp['text']); ?>
                  </div>
                  <div class="backup-chars"><?php echo mb_strlen($bp['text']); ?> Zeichen</div>
                  <div class="backup-actions">
                    <button class="btn-backup-copy"
                      onclick="backupCopy(this, '<?php echo $bp_preview_id; ?>')">📋 Kopieren</button>
                    <button class="btn-secondary" style="padding:6px 12px;font-size:12px;"
                      onclick="toggleBackupExpand('<?php echo $bp_preview_id; ?>', this)">Mehr</button>
                    <form method="post" action="./linkedin.php" style="display:inline;">
                      <input type="hidden" name="action" value="backup_mark_posted">
                      <input type="hidden" name="backup_id" value="<?php echo intval($bp['id']); ?>">
                      <button type="submit" class="btn-backup-posted">✓ Gepostet</button>
                    </form>
                    <form method="post" action="./linkedin.php" style="display:inline;margin-left:auto;">
                      <input type="hidden" name="action" value="backup_delete">
                      <input type="hidden" name="backup_id" value="<?php echo intval($bp['id']); ?>">
                      <button type="submit" class="btn-backup-delete"
                        onclick="return confirm('Post dauerhaft aus dem Backup löschen?')">🗑</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Header: Auf LinkedIn gepostet -->
        <div class="backup-section-header" style="margin-top:10px;<?php echo !empty($backup_posted) ? '' : ''; ?>"
             onclick="toggleBackup('posted')" id="backup-hdr-posted">
          <div class="backup-section-title">
            <span>✅ Auf LinkedIn gepostet</span>
            <?php if (!empty($backup_posted)): ?>
              <span class="backup-badge posted"><?php echo count($backup_posted); ?> Posts</span>
            <?php endif; ?>
          </div>
          <span class="backup-chevron" id="backup-chv-posted">▼</span>
        </div>
        <div class="backup-body" id="backup-body-posted">
          <?php if (empty($backup_posted)): ?>
            <div class="backup-empty">Noch keine geposteten Posts. Markiere einen Post als gepostet – er erscheint hier.</div>
          <?php else: ?>
            <div class="backup-grid">
              <?php foreach ($backup_posted as $bp): ?>
                <?php
                  $bp_is_series = isset($bp['type']) && $bp['type'] === 'series';
                  $bp_preview_id = 'bkp-' . intval($bp['id']);
                  $bp_posted_at  = isset($bp['posted_at']) && $bp['posted_at'] ? $bp['posted_at'] : '';
                ?>
                <div class="backup-card posted">
                  <div class="backup-card-meta">
                    <span class="backup-card-topic"><?php echo htmlspecialchars($bp['topic']); ?></span>
                    <?php if ($bp_is_series): ?>
                      <span class="backup-card-series-badge">
                        Serie · Teil <?php echo intval($bp['series_part']); ?>/<?php echo intval($bp['series_total']); ?>
                      </span>
                    <?php endif; ?>
                    <span class="backup-card-posted-badge">✓ gepostet<?php echo $bp_posted_at ? ' ' . htmlspecialchars($bp_posted_at) : ''; ?></span>
                    <span class="backup-card-date"><?php echo htmlspecialchars($bp['generated_at']); ?></span>
                  </div>
                  <div class="backup-preview collapsed" id="<?php echo $bp_preview_id; ?>">
                    <?php echo htmlspecialchars($bp['text']); ?>
                  </div>
                  <div class="backup-chars"><?php echo mb_strlen($bp['text']); ?> Zeichen</div>
                  <div class="backup-actions">
                    <button class="btn-backup-copy"
                      onclick="backupCopy(this, '<?php echo $bp_preview_id; ?>')">📋 Kopieren</button>
                    <button class="btn-secondary" style="padding:6px 12px;font-size:12px;"
                      onclick="toggleBackupExpand('<?php echo $bp_preview_id; ?>', this)">Mehr</button>
                    <form method="post" action="./linkedin.php" style="display:inline;">
                      <input type="hidden" name="action" value="backup_mark_ready">
                      <input type="hidden" name="backup_id" value="<?php echo intval($bp['id']); ?>">
                      <button type="submit" class="btn-backup-undo" title="Zurück in 'Bereit zum Posten' verschieben">↩ Rückgängig</button>
                    </form>
                    <form method="post" action="./linkedin.php" style="display:inline;margin-left:auto;">
                      <input type="hidden" name="action" value="backup_delete">
                      <input type="hidden" name="backup_id" value="<?php echo intval($bp['id']); ?>">
                      <button type="submit" class="btn-backup-delete"
                        onclick="return confirm('Post dauerhaft aus dem Backup löschen?')">🗑</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div><!-- /backup-section -->

    </div><!-- /dash-content -->
  </div><!-- /dash-main -->
</div>

<script>
function showLoading(msg) {
  var overlay = document.getElementById('loading-overlay');
  if (msg) overlay.querySelector('span').textContent = msg;
  overlay.classList.add('active');
}
function hideLoading() {
  document.getElementById('loading-overlay').classList.remove('active');
}
function showAjaxError(msg) {
  var el = document.getElementById('ajax-error');
  el.textContent = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ajaxGenerate(form) {
  document.getElementById('ajax-error').style.display = 'none';
  var action = form.querySelector('[name="action"]').value;
  var postCount = form.querySelector('[name="post_count"]');
  var isMany = postCount && parseInt(postCount.value) >= 4;
  var label  = action === 'generate_series'
    ? 'Serie wird generiert\u00a0\u2013 bitte warten\u2026'
    : (isMany ? 'Posts werden generiert (2 Anfragen)\u00a0\u2013 bitte warten\u2026'
              : 'Posts werden generiert\u00a0\u2013 bitte warten\u2026');
  showLoading(label);

  var data = new FormData(form);
  data.append('ajax', '1');

  fetch('./linkedin.php', { method: 'POST', body: data })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(j) {
      hideLoading();
      if (j.error) {
        showAjaxError('Fehler: ' + j.error);
      } else {
        location.reload();
      }
    })
    .catch(function(e) {
      hideLoading();
      showAjaxError('Netzwerkfehler: ' + e.message);
    });

  return false; // prevent normal form submit
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

function toggleBackup(section) {
  var hdr  = document.getElementById('backup-hdr-'  + section);
  var body = document.getElementById('backup-body-' + section);
  var chv  = document.getElementById('backup-chv-'  + section);
  var open = body.classList.contains('open');
  if (open) {
    body.classList.remove('open');
    hdr.classList.remove('open');
    chv.classList.remove('open');
  } else {
    body.classList.add('open');
    hdr.classList.add('open');
    chv.classList.add('open');
  }
}

function toggleBackupExpand(previewId, btn) {
  var el = document.getElementById(previewId);
  if (!el) return;
  if (el.classList.contains('collapsed')) {
    el.classList.remove('collapsed');
    btn.textContent = 'Weniger';
  } else {
    el.classList.add('collapsed');
    btn.textContent = 'Mehr';
  }
}

function backupCopy(btn, previewId) {
  var el = document.getElementById(previewId);
  if (!el) return;
  var text = el.textContent || el.innerText;
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text.trim()).then(function() {
      var orig = btn.textContent;
      btn.textContent = '✓ Kopiert!';
      setTimeout(function() { btn.textContent = orig; }, 2000);
    });
  } else {
    var ta = document.createElement('textarea');
    ta.value = text.trim();
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    var orig = btn.textContent;
    btn.textContent = '✓ Kopiert!';
    setTimeout(function() { btn.textContent = orig; }, 2000);
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
