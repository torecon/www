<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

$price = bewertung_preis();

$ld = json_ld_base();
$ld['@graph'][] = [
    '@type'    => 'Service',
    'name'     => t('eval_h2'),
    'provider' => ['@id' => SITE_URL . '/#org'],
    'areaServed' => 'DE',
];
$ld['@graph'][] = [
    '@type'    => 'Service',
    'name'     => t('dev_h2'),
    'provider' => ['@id' => SITE_URL . '/#org'],
    'areaServed' => 'DE',
];

$requirements = [
    // [Name, Zweck, Wann, Kosten]
    [t('req_apple'),     t('req_apple_d'),     t('req_always'), t('req_apple_c')],
    [t('req_google'),    t('req_google_d'),    t('req_android'), t('req_google_c')],
    [t('req_github'),    t('req_github_d'),    t('req_always'), t('req_github_c')],
    [t('req_railway'),   t('req_railway_d'),   t('req_server'), t('req_usage')],
    [t('req_anthropic'), t('req_anthropic_d'), t('req_ai'),     t('req_usage')],
    [t('req_hetzner'),   t('req_hetzner_d'),   t('req_files'),  t('req_usage')],
    [t('req_web'),       t('req_web_d'),       t('req_always'), t('req_web_c')],
];

$downloads = [
    ['01-entwicklungsvertrag.pdf',               t('dl1')],
    ['02-anlage-mitwirkung-beistellungen.pdf',   t('dl2')],
    ['03-anlage-technik-und-drittkosten.pdf',    t('dl3')],
    ['04-anlage-auftragsverarbeitung.pdf',       t('dl4')],
    ['05-bedingungen-app-idee-bewertung.pdf',    t('dl5')],
];

page_head(t('offer_page_title'), t('offer_page_desc'), 'angebot/', 'offer', [$ld]);
?>
<section class="page-hero">
  <div class="wrap">
    <h1><?= e(t('offer_h1')) ?></h1>
    <p class="lead"><?= e(t('offer_lead')) ?></p>
    <p class="note"><?= e(t('b2b_note')) ?></p>
  </div>
</section>

<section id="bewertung" class="section section-tight">
  <div class="wrap split">
    <div>
      <p class="kicker">1</p>
      <h2><?= e(t('eval_h2')) ?></h2>
      <p class="section-lead"><?= e(t('eval_lead')) ?></p>
      <p><?= e(t('eval_format')) ?></p>
      <div class="price-box">
        <p class="price"><?= e($price ? t('eval_price', ['price' => $price]) : t('eval_price_na')) ?></p>
        <p class="muted"><?= e(t('eval_vat')) ?></p>
        <a class="btn" href="<?= e(url('kontakt/?anliegen=bewertung')) ?>"><?= e(t('eval_cta')) ?></a>
      </div>
    </div>
    <div class="card">
      <h3><?= e(t('eval_get')) ?></h3>
      <ul class="checklist">
<?php for ($i = 1; $i <= 6; $i++): ?>
        <li><?= t("eval{$i}") ?></li>
<?php endfor; ?>
      </ul>
    </div>
  </div>
</section>

<section id="entwicklung" class="section section-alt">
  <div class="wrap split">
    <div>
      <p class="kicker">2</p>
      <h2><?= e(t('dev_h2')) ?></h2>
      <p class="section-lead"><?= e(t('dev_lead')) ?></p>
      <h3><?= e(t('dev_terms')) ?></h3>
      <p><?= e(t('dev_terms_d')) ?></p>
      <a class="btn" href="<?= e(url('kontakt/?anliegen=entwicklung')) ?>"><?= e(t('dev_cta')) ?></a>
    </div>
    <div class="card">
      <h3><?= e(t('dev_scope')) ?></h3>
      <ul class="checklist">
<?php for ($i = 1; $i <= 7; $i++): ?>
        <li><?= e(t("dev{$i}")) ?></li>
<?php endfor; ?>
      </ul>
    </div>
  </div>
</section>

<section id="voraussetzungen" class="section">
  <div class="wrap">
    <h2><?= e(t('req_h2')) ?></h2>
    <p class="section-lead"><?= e(t('req_lead')) ?></p>
    <div class="table-wrap">
      <table class="req-table">
        <thead>
          <tr><th scope="col"><?= e(t('req_th_what')) ?></th><th scope="col"><?= e(t('req_th_why')) ?></th><th scope="col"><?= e(t('req_th_when')) ?></th><th scope="col"><?= e(t('req_th_cost')) ?></th></tr>
        </thead>
        <tbody>
<?php foreach ($requirements as [$what, $why, $when, $cost]): ?>
          <tr><th scope="row"><?= e($what) ?></th><td><?= e($why) ?></td><td><?= e($when) ?></td><td><?= e($cost) ?></td></tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p><?= e(t('req_more')) ?></p>
    <p class="muted small"><?= e(t('req_note')) ?></p>
  </div>
</section>

<section id="vertragsentwuerfe" class="section section-alt">
  <div class="wrap">
    <h2><?= e(t('dl_h2')) ?></h2>
    <p class="section-lead"><?= e(t('dl_lead')) ?></p>
    <ul class="downloads">
<?php foreach ($downloads as [$file, $label]):
    $path = __DIR__ . '/../downloads/' . $file;
    if (!is_file($path)) { continue; }
    $kb = (int) ceil(filesize($path) / 1024); ?>
      <li>
        <a href="/downloads/<?= e($file) ?>" download>
          <span class="dl-icon" aria-hidden="true">PDF</span>
          <span class="dl-text"><strong><?= e($label) ?></strong><span class="muted"><?= e(t('dl_pdf')) ?> · <?= $kb ?> KB<?= t('dl_lang') !== '' ? ' · ' . e(t('dl_lang')) : '' ?></span></span>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section cta-band">
  <div class="wrap cta-inner">
    <h2><?= e(t('cta_title')) ?></h2>
    <p><?= e(t('cta_lead')) ?></p>
    <a class="btn btn-light" href="<?= e(url('kontakt/')) ?>"><?= e(t('cta_btn')) ?></a>
  </div>
</section>
<?php page_foot();
