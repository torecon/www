<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

page_head(t('imprint_title'), 'Impressum von torecon, Thomas Reinke, App-Entwicklung.', 'impressum/');
?>
<section class="page-hero">
  <div class="wrap narrow">
    <h1>Impressum</h1>
<?php if (t('legal_de_only') !== ''): ?>
    <p class="note"><?= e(t('legal_de_only')) ?></p>
<?php endif; ?>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow legal" lang="de">
    <h2>Angaben gemäß § 5 DDG</h2>
    <p>Thomas Reinke<br>torecon – App-Entwicklung<br>Roesgenstr. 22<br>53474 Ahrweiler<br>Deutschland</p>

    <h2>Kontakt</h2>
    <p>E-Mail: <a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a><br>
    Kontaktformular: <a href="<?= e(url('kontakt/')) ?>">torecon.de/kontakt</a></p>

    <h2>Rechtsform</h2>
    <p>Einzelunternehmer, keine Eintragung im Handelsregister.</p>

    <h2>Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2>
    <p>Thomas Reinke, Anschrift wie oben</p>

    <h2>Verbraucherstreitbeilegung</h2>
    <p>Die Angebote auf dieser Website richten sich an Unternehmer. Ich bin nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

    <h2>Apps</h2>
    <p>Für die Apps von torecon gelten die Impressums-, Datenschutz- und Nutzungsangaben auf der jeweiligen App-Website (siehe <a href="<?= e(url('apps/')) ?>">Apps</a>).</p>

    <h2>Haftung für Links</h2>
    <p>Diese Website enthält Links zu externen Websites. Auf deren Inhalte habe ich keinen Einfluss; verantwortlich ist der jeweilige Anbieter. Bei Bekanntwerden von Rechtsverletzungen entferne ich solche Links umgehend.</p>
  </div>
</section>
<?php page_foot();
