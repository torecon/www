<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

$ld = json_ld_base();
foreach ($APPS as $key => $app) {
    $ld['@graph'][] = [
        '@type'               => 'SoftwareApplication',
        'name'                => $app['name'],
        'operatingSystem'     => $app['macstore'] ? 'iOS, iPadOS, macOS' : 'iOS',
        'applicationCategory' => 'MobileApplication',
        'url'                 => 'https://' . $app['domain'] . '/',
        'description'         => $app['claim'][$lang],
        'author'              => ['@id' => SITE_URL . '/#person'],
    ];
}

page_head(t('apps_title'), t('apps_desc'), 'apps/', 'apps', [$ld]);
?>
<section class="page-hero">
  <div class="wrap">
    <h1><?= e(t('apps_h1')) ?></h1>
    <p class="lead"><?= e(t('apps_lead')) ?></p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap">
    <div class="app-grid app-grid-detail">
<?php foreach ($APPS as $key => $app) { app_card($key, $app, true); } ?>
    </div>
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
