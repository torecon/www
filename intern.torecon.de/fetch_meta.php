<?php
require_once __DIR__ . '/check_auth.php';

header('Content-Type: application/json');

$url = isset($_GET['url']) ? trim($_GET['url']) : '';

if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Ungültige URL']);
    exit;
}

// Only allow http/https
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, array('http', 'https'))) {
    echo json_encode(['error' => 'Nur HTTP/HTTPS erlaubt']);
    exit;
}

$ctx = stream_context_create(array(
    'http' => array(
        'timeout'         => 8,
        'follow_location' => 1,
        'max_redirects'   => 3,
        'header'          => "User-Agent: Mozilla/5.0 (compatible; torecon-bot/1.0)\r\n",
        'ignore_errors'   => true,
    ),
    'ssl' => array(
        'verify_peer'      => false,
        'verify_peer_name' => false,
    )
));

$html = @file_get_contents($url, false, $ctx);

if ($html === false) {
    echo json_encode(['error' => 'Website nicht erreichbar']);
    exit;
}

// Extract meta description or og:description
$slogan = '';

// Try og:description first
if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
    $slogan = $m[1];
} elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\'][^>]*>/i', $html, $m)) {
    $slogan = $m[1];
} elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
    $slogan = $m[1];
} elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\'][^>]*>/i', $html, $m)) {
    $slogan = $m[1];
}

// Try title as fallback
if (!$slogan && preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
    $slogan = trim($m[1]);
}

$slogan = html_entity_decode(trim($slogan), ENT_QUOTES, 'UTF-8');
$slogan = substr($slogan, 0, 160);

if ($slogan) {
    echo json_encode(array('slogan' => $slogan));
} else {
    echo json_encode(array('error' => 'Kein Slogan gefunden'));
}
