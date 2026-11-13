<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

$B = require __DIR__ . '/../data/beispiel.php';

$devPhases = array_filter($B['phases'], fn($p) => $p['days'] > 0);
$devDays   = array_sum(array_map(fn($p) => $p['days'], $devPhases));
$pt = fn(int $d): string => t($d === 1 ? 'bsp_pt_one' : 'bsp_pt', ['n' => $d]);
$weeks     = $B['weeks'];
$prioLabel = ['must' => t('bsp_must'), 'should' => t('bsp_should'), 'could' => t('bsp_could')];

page_head(t('bsp_title'), t('bsp_desc'), 'beispiel/', 'example', [json_ld_base()]);
?>
<section class="page-hero">
  <div class="wrap">
    <p class="kicker"><?= e(t('bsp_kicker')) ?></p>
    <h1><?= e(t('bsp_h1')) ?></h1>
    <p class="lead"><?= e(t('bsp_lead')) ?></p>
    <p class="note"><?= e(t('bsp_fictional')) ?></p>
    <ul class="facts facts-3">
      <li><strong><?= $weeks ?></strong><span><?= e(t('bsp_fact_weeks')) ?></span></li>
      <li><strong><?= count($devPhases) ?></strong><span><?= e(t('bsp_fact_phases')) ?></span></li>
      <li><strong><?= $devDays ?></strong><span><?= e(t('bsp_fact_total')) ?></span></li>
    </ul>
  </div>
</section>

<section class="section section-tight">
  <div class="wrap split">
    <div>
      <h2><?= e(t('bsp_situation_h')) ?></h2>
      <p><?= e(t('bsp_situation_p1')) ?></p>
      <p><?= e(t('bsp_situation_p2')) ?></p>
    </div>
    <div class="card">
      <h3><?= e(t('bsp_goal_h')) ?></h3>
      <ul class="checklist">
        <li><?= e(t('bsp_goal1')) ?></li>
        <li><?= e(t('bsp_goal2')) ?></li>
        <li><?= e(t('bsp_goal3')) ?></li>
      </ul>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <p class="kicker">1</p>
    <h2><?= e(t('bsp_stories_h')) ?></h2>
    <p class="section-lead"><?= e(t('bsp_stories_lead')) ?></p>
    <div class="stories">
<?php foreach ($B['stories'] as $s): [$who, $why, $accept] = $s[$lang]; ?>
      <article class="story">
        <span class="prio prio-<?= e($s['prio']) ?>"><?= e($prioLabel[$s['prio']]) ?></span>
        <p class="story-text"><strong><?= e($who) ?></strong> <?= e($why) ?></p>
        <p class="story-accept"><span><?= e(t('bsp_accept')) ?>:</span> <?= e($accept) ?></p>
      </article>
<?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <p class="kicker">2</p>
    <h2><?= e(t('bsp_plan_h')) ?></h2>
    <p class="section-lead"><?= e(t('bsp_plan_lead')) ?></p>

    <div class="roadmap" role="table" aria-label="<?= e(t('bsp_plan_h')) ?>">
      <div class="rm-row rm-head" role="row">
        <div class="rm-label" role="columnheader"><?= e(t('bsp_phase')) ?></div>
        <div class="rm-track" role="columnheader">
<?php for ($w = 1; $w <= $weeks; $w++): ?>
          <span class="rm-week"><?= e(t('bsp_week_short')) ?> <?= $w ?></span>
<?php endfor; ?>
        </div>
      </div>
<?php foreach ($B['phases'] as $i => $p): if ($p['days'] === 0) { continue; } ?>
      <div class="rm-row" role="row">
        <div class="rm-label" role="rowheader"><span class="rm-n"><?= $i ?></span><?= e($p['title'][$lang]) ?></div>
        <div class="rm-track" role="cell">
          <div class="rm-bar rm-<?= e($p['key']) ?> s-<?= $p['start'] ?> e-<?= $p['end'] ?>">
            <span class="rm-bar-text"><?= e(t('bsp_week_short')) ?> <?= $p['start'] ?>–<?= $p['end'] ?></span>
<?php for ($w = $p['start']; $w <= $p['end']; $w++): ?>
            <span class="rm-loop" title="<?= e(t('bsp_legend_loop')) ?>" aria-hidden="true">↻</span>
<?php endfor; ?>
            <span class="rm-gate" title="<?= e(t('bsp_legend_gate')) ?>" aria-hidden="true">◆</span>
          </div>
        </div>
      </div>
<?php endforeach; ?>
    </div>
    <ul class="rm-legend">
      <li><span class="rm-loop" aria-hidden="true">↻</span> <?= e(t('bsp_legend_loop')) ?></li>
      <li><span class="rm-gate" aria-hidden="true">◆</span> <?= e(t('bsp_legend_gate')) ?></li>
      <li><span class="rm-store" aria-hidden="true">★</span> <?= e(t('bsp_legend_store')) ?></li>
    </ul>

    <ol class="phase-cards">
<?php foreach ($B['phases'] as $i => $p): ?>
      <li class="card phase-card<?= !empty($p['optional']) ? ' phase-optional' : '' ?>">
        <div class="phase-head">
          <span class="step-n" aria-hidden="true"><?= $i ?></span>
          <div>
            <h3><?= e($p['title'][$lang]) ?></h3>
            <p class="muted small"><?= $p['days'] > 0 ? e(t('bsp_weeks_range', ['s' => $p['start'], 'e' => $p['end']])) : e(t('bsp_before_start')) ?><?= !empty($p['optional']) ? ' · ' . e(t('bsp_optional')) : '' ?></p>
          </div>
        </div>
        <ul class="checklist">
<?php foreach ($p['items'][$lang] as $item): ?>
          <li><?= e($item) ?></li>
<?php endforeach; ?>
        </ul>
        <p class="phase-result"><?= e($p['result'][$lang]) ?></p>
        <p class="price"><?= $p['days'] > 0 ? e(t('bsp_effort') . ' ' . $pt($p['days'])) : e(t('bsp_flat')) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <p class="kicker">3</p>
    <h2><?= e(t('bsp_cost_h')) ?></h2>
    <p class="section-lead"><?= e(t('bsp_cost_lead')) ?></p>
    <div class="split">
      <div class="card">
        <h3><?= e(t('bsp_cost_dev')) ?></h3>
        <table class="cost-table">
          <tbody>
<?php foreach ($devPhases as $p): ?>
            <tr><th scope="row"><?= e($p['title'][$lang]) ?></th><td><?= e($pt($p['days'])) ?></td></tr>
<?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><th scope="row"><?= e(t('bsp_cost_sum')) ?></th><td><?= e($pt($devDays)) ?></td></tr>
          </tfoot>
        </table>
        <p class="muted small"><?= e(t('bsp_cost_dev_note')) ?></p>
      </div>
      <div class="card">
        <h3><?= e(t('bsp_cost_run')) ?></h3>
        <table class="cost-table">
          <tbody>
<?php foreach ($B['running'] as $r): [$name, $why, $cost] = $r[$lang]; ?>
            <tr><th scope="row"><?= e($name) ?><span class="muted small block"><?= e($why) ?></span></th><td><?= e($cost) ?></td></tr>
<?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><th scope="row"><?= e(t('bsp_cost_sum_run')) ?></th><td><?= e($B['running_total'][$lang]) ?></td></tr>
          </tfoot>
        </table>
        <p class="muted small"><?= e(t('bsp_cost_run_note')) ?></p>
      </div>
    </div>
    <div class="card android-note">
      <h3><?= e(t('bsp_android_h')) ?></h3>
      <p><?= e($B['android'][$lang]) ?></p>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap narrow">
    <h2><?= e(t('bsp_rules_h')) ?></h2>
    <ul class="checklist">
      <li><?= e(t('bsp_rule1')) ?></li>
      <li><?= e(t('bsp_rule2')) ?></li>
      <li><?= e(t('bsp_rule3')) ?></li>
      <li><?= e(t('bsp_rule4')) ?></li>
    </ul>
    <p class="actions">
      <a class="btn" href="<?= e(url('kontakt/?anliegen=entwicklung')) ?>"><?= e(t('bsp_cta')) ?></a>
      <a class="btn btn-ghost" href="<?= e(url('angebot/#vertragsentwuerfe')) ?>"><?= e(t('footer_downloads')) ?></a>
    </p>
  </div>
</section>
<?php page_foot();
