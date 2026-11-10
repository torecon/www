<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

/**
 * Seitenkopf inkl. Navigation.
 * $path: Seitenpfad ohne Sprachpräfix, z. B. '' (Start), 'apps/', 'angebot/'.
 * $active: Menüpunkt, der markiert wird.
 */
function page_head(string $title, string $desc, string $path, string $active = '', array $jsonld = [], bool $noindex = false): void
{
    global $lang;
    $other = $lang === 'de' ? 'en' : 'de';
    $css   = '/assets/css/site.css?v=' . (string) @filemtime(__DIR__ . '/../assets/css/site.css');
    $js    = '/assets/js/site.js?v=' . (string) @filemtime(__DIR__ . '/../assets/js/site.js');
    $nav   = [
        ['apps',    url('apps/'),        t('nav_apps')],
        ['work',    url('#leistungen'),  t('nav_work')],
        ['offer',   url('angebot/'),     t('nav_offer')],
        ['example', url('beispiel/'),    t('nav_example')],
        ['about',   url('#ueber-mich'),  t('nav_about')],
    ];
    ?>
<!doctype html>
<html lang="<?= e(t('html_lang')) ?>" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php if ($noindex): ?>
<meta name="robots" content="noindex">
<?php else: ?>
<link rel="canonical" href="<?= e(abs_url($path)) ?>">
<?php endif; ?>
<link rel="alternate" hreflang="de" href="<?= e(abs_url($path, 'de')) ?>">
<link rel="alternate" hreflang="en" href="<?= e(abs_url($path, 'en')) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e(abs_url($path, 'de')) ?>">
<meta name="theme-color" content="#0071e3">
<meta name="color-scheme" content="light dark">
<link rel="icon" href="/assets/img/favicon-32.png" sizes="32x32" type="image/png">
<link rel="icon" href="/assets/img/logo-192.png" sizes="192x192" type="image/png">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<meta property="og:type" content="website">
<meta property="og:site_name" content="torecon">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e(abs_url($path)) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/assets/img/og-torecon.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="675">
<meta property="og:locale" content="<?= $lang === 'en' ? 'en_US' : 'de_DE' ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="stylesheet" href="<?= e($css) ?>">
<script src="<?= e($js) ?>" defer></script>
<?php foreach ($jsonld as $block): ?>
<script type="application/ld+json"><?= json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body>
<a class="skip" href="#main"><?= t('skip') ?></a>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="<?= e(url('')) ?>" aria-label="<?= e(t('home_link')) ?>">
      <img src="/assets/img/logo-rounded-128.png" alt="" width="32" height="32">
      <span>torecon</span>
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="menu-icon" aria-hidden="true"></span><span class="sr-only"><?= t('nav_menu') ?></span>
    </button>
    <nav id="site-nav" class="site-nav" aria-label="<?= e(t('nav_menu')) ?>">
      <ul>
<?php foreach ($nav as [$key, $href, $label]): ?>
        <li><a href="<?= e($href) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
      </ul>
      <a class="lang-switch" href="<?= e(url($path, $other)) ?>" hreflang="<?= $other ?>" lang="<?= $other ?>" aria-label="<?= e(t('lang_switch_aria')) ?>"><?= e(t('lang_switch_code')) ?></a>
      <a class="btn btn-small" href="<?= e(url('kontakt/')) ?>"<?= $active === 'contact' ? ' aria-current="page"' : '' ?>><?= e(t('nav_cta')) ?></a>
    </nav>
  </div>
</header>
<main id="main">
<?php
}

function page_foot(): void
{
    global $APPS;
    ?>
</main>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a class="brand" href="<?= e(url('')) ?>"><img src="/assets/img/logo-rounded-128.png" alt="" width="32" height="32" loading="lazy"><span>torecon</span></a>
      <p><?= e(t('footer_tagline')) ?></p>
      <p><a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a></p>
    </div>
    <div>
      <h2 class="footer-h"><?= e(t('footer_apps')) ?></h2>
      <ul>
<?php foreach ($APPS as $app): ?>
        <li><a href="https://<?= e($app['domain']) ?>/" rel="noopener"><?= e($app['name']) ?></a></li>
<?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h2 class="footer-h"><?= e(t('footer_site')) ?></h2>
      <ul>
        <li><a href="<?= e(url('apps/')) ?>"><?= e(t('nav_apps')) ?></a></li>
        <li><a href="<?= e(url('angebot/')) ?>"><?= e(t('nav_offer')) ?></a></li>
        <li><a href="<?= e(url('beispiel/')) ?>"><?= e(t('bsp_kicker')) ?></a></li>
        <li><a href="<?= e(url('angebot/#vertragsentwuerfe')) ?>"><?= e(t('footer_downloads')) ?></a></li>
        <li><a href="<?= e(url('kontakt/')) ?>"><?= e(t('nav_contact')) ?></a></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h"><?= e(t('footer_legal')) ?></h2>
      <ul>
        <li><a href="<?= e(url('impressum/')) ?>"><?= e(t('footer_imprint')) ?></a></li>
        <li><a href="<?= e(url('datenschutz/')) ?>"><?= e(t('footer_privacy')) ?></a></li>
      </ul>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <p><?= e(t('footer_copy', ['year' => date('Y')])) ?></p>
  </div>
</footer>
</body>
</html>
<?php
}

/** App-Kachel. $detail = ausführliche Variante für /apps/. */
function app_card(string $key, array $app, bool $detail = false): void
{
    global $lang;
    $store = 'https://apps.apple.com/' . ($lang === 'en' ? 'us' : 'de') . '/app/id' . $app['appstore'];
    $status = 'status_' . $app['status'];
    ?>
<article class="app-card<?= $detail ? ' app-card-detail' : '' ?>">
  <a class="app-main" href="https://<?= e($app['domain']) ?>/" rel="noopener">
    <img class="app-icon" src="/assets/img/apps/<?= e($key) ?>.png" alt="" width="72" height="72" loading="lazy">
    <span class="app-text">
      <span class="badge badge-<?= e($app['status']) ?>"><?= e(t($status)) ?></span>
      <span class="app-name"><?= e($app['name']) ?></span>
      <span class="app-claim"><?= e($app['claim'][$lang]) ?></span>
    </span>
  </a>
<?php if ($detail): ?>
  <dl class="app-meta">
    <div><dt><?= e(t('apps_platforms')) ?></dt><dd><?= e(implode(' · ', $app['platforms'])) ?> · <?= e(t('app_langs', ['n' => $app['langs']])) ?></dd></div>
<?php if ($app['releases'] > 0): ?>
    <div><dt>App Store</dt><dd><?= e(t('app_releases', ['n' => $app['releases']])) ?></dd></div>
<?php endif; ?>
    <div><dt><?= e(t('apps_tech')) ?></dt><dd><?= e(implode(' · ', $app['tech'])) ?></dd></div>
  </dl>
<?php endif; ?>
  <div class="app-links">
    <a class="link-arrow" href="https://<?= e($app['domain']) ?>/" rel="noopener"><?= e(t('app_site')) ?><span class="sr-only"> <?= e($app['name']) ?></span></a>
<?php if ($app['status'] === 'live'): ?>
    <a class="link-arrow" href="<?= e($store) ?>" rel="noopener"><?= e(t('app_store')) ?><span class="sr-only"> <?= e($app['name']) ?></span></a>
<?php if ($app['macstore']): ?>
    <a class="link-arrow" href="https://apps.apple.com/<?= $lang === 'en' ? 'us' : 'de' ?>/app/id<?= e($app['macstore']) ?>" rel="noopener"><?= e(t('app_macstore')) ?><span class="sr-only"> <?= e($app['name']) ?></span></a>
<?php endif; ?>
<?php endif; ?>
  </div>
</article>
<?php
}

function json_ld_base(): array
{
    return [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'    => 'Person',
                '@id'      => SITE_URL . '/#person',
                'name'     => 'Thomas Reinke',
                'jobTitle' => 'App Developer',
                'url'      => SITE_URL . '/',
                'worksFor' => ['@id' => SITE_URL . '/#org'],
                'knowsAbout' => ['iOS', 'iPadOS', 'macOS', 'Android', 'SwiftUI', 'App Store', 'Google Play', 'AI'],
            ],
            [
                '@type' => 'Organization',
                '@id'   => SITE_URL . '/#org',
                'name'  => 'torecon',
                'url'   => SITE_URL . '/',
                'logo'  => SITE_URL . '/assets/img/logo-512.png',
                'email' => CONTACT_EMAIL,
                'founder' => ['@id' => SITE_URL . '/#person'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress'   => 'Roesgenstr. 22',
                    'postalCode'      => '53474',
                    'addressLocality' => 'Ahrweiler',
                    'addressCountry'  => 'DE',
                ],
            ],
        ],
    ];
}
