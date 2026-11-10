<?php
declare(strict_types=1);

/*
 * torecon.de – gemeinsamer Einstieg für alle Seiten.
 * PHP 8.4, kein Framework, keine Cookies, keine externen Ressourcen.
 */

const SITE_URL      = 'https://www.torecon.de';
const CONTACT_EMAIL = 'info@torecon.de';

// Festpreis der App-Idee-Bewertung in Euro (Kleinunternehmer, keine USt).
// null = Preis wird noch nicht angezeigt („Preis auf Anfrage“).
const BEWERTUNG_PREIS = 490;

// Buchungsseite für ein Erstgespräch (Google Kalender Terminplanung).
// null = Terminlink wird nicht angezeigt.
const BOOKING_URL = null;

const LANGS = ['de', 'en'];

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : 'de';

/** @var array<string,string> $T */
$T = require __DIR__ . '/../lang/' . $lang . '.php';

/** Übersetzter Text (vertrauenswürdig, darf einfaches HTML enthalten). */
function t(string $key, array $vars = []): string
{
    global $T;
    $s = $T[$key] ?? $key;
    foreach ($vars as $k => $v) {
        $s = str_replace('{' . $k . '}', (string) $v, $s);
    }
    return $s;
}

/** Escaping für Ausgabe von Daten. */
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Interner Link in der aktuellen Sprache. $path ohne führenden Slash, z. B. 'angebot/'. */
function url(string $path = '', ?string $forLang = null): string
{
    global $lang;
    $l = $forLang ?? $lang;
    return ($l === 'en' ? '/en/' : '/') . ltrim($path, '/');
}

function abs_url(string $path = '', ?string $forLang = null): string
{
    return SITE_URL . url($path, $forLang);
}

/** Preis der Bewertung als Text, z. B. „490 €“ bzw. „€490“. */
function bewertung_preis(): ?string
{
    global $lang;
    if (BEWERTUNG_PREIS === null) {
        return null;
    }
    $n = number_format((float) BEWERTUNG_PREIS, 0, ',', '.');
    return $lang === 'en' ? '€' . number_format((float) BEWERTUNG_PREIS) : $n . ' €';
}

// Sicherheits-Header (ergänzend zur .htaccess, greifen auch bei reinem nginx-PHP).
if (!headers_sent()) {
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; object-src 'none'");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
    header('Content-Language: ' . $lang);
}

$APPS = require __DIR__ . '/../data/apps.php';
