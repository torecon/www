<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

/*
 * Anfrageformular. Keine Cookies, keine Session, kein Captcha.
 * Spam-Schutz: Honeypot, Mindest-Ausfüllzeit, Link-Limit, Ratenbegrenzung
 * (gehashte IP, nur im temporären Verzeichnis, max. 1 Stunde).
 */

$interests = ['bewertung' => 'f_i_eval', 'entwicklung' => 'f_i_dev', 'sonstiges' => 'f_i_other'];
$platforms = ['ios' => 'f_p_ios', 'mac' => 'f_p_mac', 'android' => 'f_p_android', 'offen' => 'f_p_unsure'];

$values = [
    'name'     => '',
    'company'  => '',
    'email'    => '',
    'interest' => isset($_GET['anliegen'], $interests[$_GET['anliegen']]) ? (string) $_GET['anliegen'] : 'entwicklung',
    'platform' => [],
    'message'  => '',
    'consent'  => false,
];
$errors = [];
$state  = isset($_GET['gesendet']) ? 'sent' : 'form';
$alert  = '';

function clean_line(string $s, int $max): string
{
    $s = trim(preg_replace('/[\r\n\t]+/', ' ', $s) ?? '');
    return mb_substr($s, 0, $max);
}

function rate_limited(): bool
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/torecon-contact-' . hash('sha256', 'torecon' . $ip) . '.json';
    $now  = time();
    $hits = [];
    if (is_file($file)) {
        $hits = json_decode((string) file_get_contents($file), true) ?: [];
    }
    $hits = array_values(array_filter($hits, fn($t) => is_int($t) && $t > $now - 3600));
    if (count($hits) >= 5) {
        return true;
    }
    $hits[] = $now;
    @file_put_contents($file, json_encode($hits), LOCK_EX);
    return false;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $values['name']     = clean_line((string) ($_POST['name'] ?? ''), 120);
    $values['company']  = clean_line((string) ($_POST['company'] ?? ''), 160);
    $values['email']    = clean_line((string) ($_POST['email'] ?? ''), 200);
    $values['interest'] = isset($interests[$_POST['interest'] ?? '']) ? (string) $_POST['interest'] : 'sonstiges';
    $values['platform'] = array_values(array_intersect(array_keys($platforms), (array) ($_POST['platform'] ?? [])));
    $values['message']  = mb_substr(trim((string) ($_POST['message'] ?? '')), 0, 5000);
    $values['consent']  = isset($_POST['consent']);

    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $started  = (int) ($_POST['ts'] ?? 0);
    $tooFast  = $started <= 0 || (time() - $started) < 3;
    $tooMany  = preg_match_all('~https?://~i', $values['message']) > 3;

    if ($values['name'] === '') {
        $errors['name'] = true;
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = true;
    }
    if (mb_strlen($values['message']) < 10) {
        $errors['message'] = true;
    }
    if (!$values['consent']) {
        $errors['consent'] = true;
    }

    if ($honeypot !== '' || $tooFast || $tooMany) {
        $alert = t('f_err_spam', ['email' => CONTACT_EMAIL]);
    } elseif ($errors) {
        $alert = t('f_err');
    } elseif (rate_limited()) {
        $alert = t('f_err_spam', ['email' => CONTACT_EMAIL]);
    } else {
        $platformText = implode(', ', array_map(fn($p) => t($platforms[$p]), $values['platform'])) ?: '–';
        $body = "Neue Anfrage über torecon.de\n\n"
              . 'Name:        ' . $values['name'] . "\n"
              . 'Unternehmen: ' . ($values['company'] ?: '–') . "\n"
              . 'E-Mail:      ' . $values['email'] . "\n"
              . 'Anliegen:    ' . t($interests[$values['interest']]) . "\n"
              . 'Plattformen: ' . $platformText . "\n"
              . 'Sprache:     ' . $lang . "\n\n"
              . "Nachricht:\n" . $values['message'] . "\n";
        $subject = mb_encode_mimeheader('Anfrage torecon.de: ' . t($interests[$values['interest']]) . ' – ' . $values['name'], 'UTF-8', 'Q');
        $headers = [
            'From'                      => 'torecon.de <' . CONTACT_EMAIL . '>',
            'Reply-To'                  => $values['email'],
            'MIME-Version'              => '1.0',
            'Content-Type'              => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];
        $ok = mail(CONTACT_EMAIL, $subject, $body, $headers, '-f' . CONTACT_EMAIL);
        if ($ok) {
            header('Location: ' . url('kontakt/?gesendet=1'), true, 303);
            exit;
        }
        $alert = t('f_err_send', ['email' => CONTACT_EMAIL]);
    }
}

page_head(t('contact_title'), t('contact_desc'), 'kontakt/', 'contact', [json_ld_base()]);

$err = fn(string $f) => isset($errors[$f]) ? ' aria-invalid="true"' : '';
?>
<section class="page-hero">
  <div class="wrap narrow">
    <h1><?= e(t('contact_h1')) ?></h1>
    <p class="lead"><?= e(t('contact_lead')) ?></p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow">
<?php if ($state === 'sent'): ?>
    <div class="alert alert-ok" role="status">
      <h2><?= e(t('f_ok_t')) ?></h2>
      <p><?= e(t('f_ok_d')) ?></p>
    </div>
<?php else: ?>
<?php if ($alert !== ''): ?>
    <div class="alert alert-error" role="alert"><p><?= e($alert) ?></p></div>
<?php endif; ?>
    <form class="form" method="post" action="<?= e(url('kontakt/')) ?>" novalidate>
      <input type="hidden" name="ts" value="<?= time() ?>">
      <div class="hp" aria-hidden="true">
        <label for="website"><?= e(t('f_hp')) ?></label>
        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
      </div>

      <div class="field">
        <label for="name"><?= e(t('f_name')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input id="name" name="name" type="text" required autocomplete="name" maxlength="120" value="<?= e($values['name']) ?>"<?= $err('name') ?>>
      </div>
      <div class="field">
        <label for="company"><?= e(t('f_company')) ?></label>
        <input id="company" name="company" type="text" autocomplete="organization" maxlength="160" value="<?= e($values['company']) ?>">
      </div>
      <div class="field">
        <label for="email"><?= e(t('f_email')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input id="email" name="email" type="email" required autocomplete="email" maxlength="200" value="<?= e($values['email']) ?>"<?= $err('email') ?>>
      </div>

      <fieldset class="field">
        <legend><?= e(t('f_interest')) ?></legend>
        <div class="choices">
<?php foreach ($interests as $k => $label): ?>
          <label class="choice"><input type="radio" name="interest" value="<?= e($k) ?>"<?= $values['interest'] === $k ? ' checked' : '' ?>> <?= e(t($label)) ?></label>
<?php endforeach; ?>
        </div>
      </fieldset>

      <fieldset class="field">
        <legend><?= e(t('f_platform')) ?></legend>
        <div class="choices">
<?php foreach ($platforms as $k => $label): ?>
          <label class="choice"><input type="checkbox" name="platform[]" value="<?= e($k) ?>"<?= in_array($k, $values['platform'], true) ? ' checked' : '' ?>> <?= e(t($label)) ?></label>
<?php endforeach; ?>
        </div>
      </fieldset>

      <div class="field">
        <label for="message"><?= e(t('f_message')) ?> <span class="req" aria-hidden="true">*</span></label>
        <textarea id="message" name="message" rows="7" required maxlength="5000"<?= $err('message') ?>><?= e($values['message']) ?></textarea>
      </div>

      <div class="field">
        <label class="choice consent"><input type="checkbox" name="consent" value="1" required<?= $values['consent'] ? ' checked' : '' ?><?= $err('consent') ?>> <span><?= t('f_consent', ['privacy' => e(url('datenschutz/'))]) ?></span></label>
      </div>

      <button class="btn" type="submit"><?= e(t('f_send')) ?></button>
      <p class="muted small"><span aria-hidden="true">*</span> <?= e(t('f_req')) ?> · <?= e(t('f_email_alt')) ?> <a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a></p>
    </form>
<?php endif; ?>
  </div>
</section>
<?php page_foot();
