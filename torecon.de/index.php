<?php
declare(strict_types=1);

require __DIR__ . '/inc/layout.php';

$count = count($APPS);
$live  = count(array_filter($APPS, fn($a) => $a['status'] === 'live'));
$releases = array_sum(array_column($APPS, 'releases'));
$price = bewertung_preis();

page_head(t('home_title'), t('home_desc'), '', '', [json_ld_base()]);
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-text">
      <p class="eyebrow"><?= e(t('hero_eyebrow')) ?></p>
      <h1><?= t('hero_title') ?></h1>
      <p class="lead"><?= e(t('hero_lead', ['count' => $count, 'live' => $live])) ?></p>
      <div class="actions">
        <a class="btn" href="<?= e(url('angebot/#bewertung')) ?>"><?= e(t('hero_cta1')) ?></a>
        <a class="btn btn-ghost" href="#apps"><?= e(t('hero_cta2')) ?></a>
      </div>
    </div>
    <div class="hero-icons" aria-hidden="true">
<?php foreach ($APPS as $key => $app): ?>
      <img src="/assets/img/apps/<?= e($key) ?>.png" alt="" width="112" height="112">
<?php endforeach; ?>
    </div>
  </div>
  <div class="wrap">
    <ul class="facts">
      <li><strong><?= e(t('fact1_n', ['count' => $count])) ?></strong><span><?= e(t('fact1_t')) ?></span></li>
      <li><strong><?= e(t('fact2_n', ['live' => $live])) ?></strong><span><?= e(t('fact2_t')) ?></span></li>
      <li><strong><?= e(t('fact3_n', ['releases' => $releases])) ?></strong><span><?= e(t('fact3_t')) ?></span></li>
      <li><strong><?= e(t('fact4_n')) ?></strong><span><?= e(t('fact4_t')) ?></span></li>
    </ul>
  </div>
</section>

<section id="apps" class="section">
  <div class="wrap">
    <p class="kicker"><?= e(t('results_kicker')) ?></p>
    <h2><?= e(t('results_title')) ?></h2>
    <p class="section-lead"><?= e(t('results_lead', ['live' => $live])) ?></p>
    <div class="app-grid">
<?php foreach ($APPS as $key => $app) { app_card($key, $app); } ?>
    </div>
    <p class="more"><a class="link-arrow" href="<?= e(url('apps/')) ?>"><?= e(t('results_all')) ?></a></p>
  </div>
</section>

<section id="leistungen" class="section section-alt">
  <div class="wrap">
    <p class="kicker"><?= e(t('work_kicker')) ?></p>
    <h2><?= e(t('work_title')) ?></h2>
    <p class="section-lead"><?= e(t('work_lead')) ?></p>
    <div class="cards cards-3">
<?php
$icons = ['apple', 'android', 'ai', 'server', 'sub', 'store'];
for ($i = 1; $i <= 6; $i++): ?>
      <div class="card">
        <span class="card-icon icon-<?= $icons[$i - 1] ?>" aria-hidden="true"></span>
        <h3><?= e(t("work{$i}_t")) ?></h3>
        <p><?= e(t("work{$i}_d")) ?></p>
      </div>
<?php endfor; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <p class="kicker"><?= e(t('how_kicker')) ?></p>
    <h2><?= e(t('how_title')) ?></h2>
    <ol class="steps">
<?php for ($i = 1; $i <= 5; $i++): ?>
      <li>
        <span class="step-n" aria-hidden="true"><?= $i ?></span>
        <h3><?= e(t("how{$i}_t")) ?></h3>
        <p><?= e(t("how{$i}_d")) ?></p>
      </li>
<?php endfor; ?>
    </ol>
  </div>
</section>

<section class="section section-dark">
  <div class="wrap">
    <p class="kicker"><?= e(t('quality_kicker')) ?></p>
    <h2><?= e(t('quality_title')) ?></h2>
    <p class="section-lead"><?= e(t('quality_lead')) ?></p>
    <div class="cards cards-4">
<?php for ($i = 1; $i <= 4; $i++): ?>
      <div class="card card-dark">
        <h3><?= e(t("q{$i}_t")) ?></h3>
        <p><?= e(t("q{$i}_d")) ?></p>
      </div>
<?php endfor; ?>
    </div>
  </div>
</section>

<section id="angebot" class="section">
  <div class="wrap">
    <p class="kicker"><?= e(t('offer_kicker')) ?></p>
    <h2><?= e(t('offer_title')) ?></h2>
    <div class="cards cards-2">
      <div class="card card-offer">
        <h3><?= e(t('offer1_t')) ?></h3>
        <p><?= e(t('offer1_d')) ?></p>
        <p class="price"><?= e($price ? t('offer1_price', ['price' => $price]) : t('offer1_price_na')) ?></p>
        <a class="btn" href="<?= e(url('angebot/#bewertung')) ?>"><?= e(t('offer1_cta')) ?></a>
      </div>
      <div class="card card-offer">
        <h3><?= e(t('offer2_t')) ?></h3>
        <p><?= e(t('offer2_d')) ?></p>
        <p class="price"><?= e(t('offer2_price')) ?></p>
        <a class="btn" href="<?= e(url('angebot/#entwicklung')) ?>"><?= e(t('offer2_cta')) ?></a>
      </div>
    </div>
    <p class="note"><?= e(t('b2b_note')) ?></p>
  </div>
</section>

<section id="ueber-mich" class="section section-alt">
  <div class="wrap about">
    <div>
      <p class="kicker"><?= e(t('about_kicker')) ?></p>
      <h2><?= e(t('about_title')) ?></h2>
      <p><?= e(t('about_p1')) ?></p>
      <p><?= e(t('about_p2')) ?></p>
      <p class="muted"><?= e(t('about_place')) ?> · <a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a></p>
    </div>
  </div>
</section>

<section class="section cta-band">
  <div class="wrap cta-inner">
    <h2><?= e(t('cta_title')) ?></h2>
    <p><?= e(t('cta_lead')) ?></p>
    <div class="actions actions-center">
      <a class="btn btn-light" href="<?= e(url('kontakt/')) ?>"><?= e(t('cta_btn')) ?></a>
<?php if (BOOKING_URL): ?>
      <a class="btn btn-outline-light" href="<?= e(BOOKING_URL) ?>" rel="noopener"><?= e(t('book_btn')) ?></a>
<?php endif; ?>
    </div>
  </div>
</section>
<?php page_foot();
