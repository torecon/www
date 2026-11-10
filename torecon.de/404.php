<?php
declare(strict_types=1);

require __DIR__ . '/inc/layout.php';

http_response_code(404);
page_head(t('nf_title'), t('nf_lead'), '', '', [], true);
?>
<section class="page-hero">
  <div class="wrap narrow">
    <h1><?= e(t('nf_h1')) ?></h1>
    <p class="lead"><?= e(t('nf_lead')) ?></p>
    <div class="actions">
      <a class="btn" href="<?= e(url('')) ?>"><?= e(t('nf_home')) ?></a>
      <a class="btn btn-ghost" href="<?= e(url('apps/')) ?>"><?= e(t('nav_apps')) ?></a>
      <a class="btn btn-ghost" href="<?= e(url('kontakt/')) ?>"><?= e(t('nav_contact')) ?></a>
    </div>
  </div>
</section>
<?php page_foot();
