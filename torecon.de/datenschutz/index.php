<?php
declare(strict_types=1);

require __DIR__ . '/../inc/layout.php';

page_head(t('privacy_title'), 'Datenschutzerklärung von torecon.de – keine Cookies, kein Tracking.', 'datenschutz/');
?>
<section class="page-hero">
  <div class="wrap narrow">
    <h1>Datenschutzerklärung</h1>
<?php if (t('legal_de_only') !== ''): ?>
    <p class="note"><?= e(t('legal_de_only')) ?></p>
<?php endif; ?>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow legal" lang="de">
    <p class="muted">Stand: November 2026</p>

    <h2>1. Kurz gesagt</h2>
    <p>Diese Website setzt keine Cookies, nutzt kein Tracking und lädt keine Inhalte von Dritten (keine externen Schriften, keine eingebetteten Videos, keine Analyse-Werkzeuge). Personenbezogene Daten verarbeite ich nur, soweit es für den Betrieb der Website und die Beantwortung Ihrer Anfrage nötig ist.</p>

    <h2>2. Verantwortlicher</h2>
    <p>Thomas Reinke, torecon, Roesgenstr. 22, 53474 Ahrweiler, Deutschland<br>
    E-Mail: <a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a></p>

    <h2>3. Aufruf der Website (Server-Protokolle)</h2>
    <p>Beim Aufruf speichert der Webserver automatisch technische Daten: IP-Adresse, Datum und Uhrzeit, aufgerufene Seite, übertragene Datenmenge, Referrer-URL sowie Browser und Betriebssystem. Das ist nötig, um die Website sicher und stabil bereitzustellen (Art. 6 Abs. 1 lit. f DSGVO). Die Protokolle werden vom Hoster nach kurzer Zeit automatisch gelöscht und nicht mit anderen Daten zusammengeführt.</p>
    <p>Hoster: nethosting24 (Webhosting mit Plesk). Mit dem Hoster besteht ein Vertrag zur Auftragsverarbeitung nach Art. 28 DSGVO.</p>

    <h2>4. Kontaktformular und E-Mail</h2>
    <p>Wenn Sie mir über das Formular oder per E-Mail schreiben, verarbeite ich Ihre Angaben (Name, E-Mail-Adresse, optional Unternehmen, Anliegen, gewählte Plattformen und Ihre Nachricht), um Ihre Anfrage zu beantworten und gegebenenfalls ein Angebot zu erstellen (Art. 6 Abs. 1 lit. b DSGVO – vorvertragliche Maßnahmen). Die Formulardaten werden per E-Mail an mich übermittelt und nicht in einer Datenbank auf dem Webserver gespeichert.</p>
    <p>Zum Schutz vor massenhaften Spam-Anfragen wird beim Absenden ein Einweg-Hashwert Ihrer IP-Adresse höchstens eine Stunde lang im temporären Speicher des Servers abgelegt und danach verworfen (Art. 6 Abs. 1 lit. f DSGVO).</p>
    <p>Ich lösche Anfragen, wenn sie erledigt sind und keine gesetzlichen Aufbewahrungspflichten bestehen. Wird aus der Anfrage ein Auftrag, gelten die handels- und steuerrechtlichen Aufbewahrungsfristen.</p>

<?php if (BOOKING_URL): ?>
    <h2>Terminbuchung</h2>
    <p>Für die Buchung eines Erstgesprächs verlinke ich auf die Terminplanung von Google Kalender (Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irland). Erst wenn Sie den Link anklicken, werden Sie zu Google weitergeleitet; dort gelten die Datenschutzhinweise von Google. Ihre Angaben bei der Buchung (Name, E-Mail, gewählter Termin) verarbeite ich zur Durchführung des Gesprächs (Art. 6 Abs. 1 lit. b DSGVO). Eine Übermittlung in die USA durch Google kann nicht ausgeschlossen werden; Google ist nach dem EU-US Data Privacy Framework zertifiziert.</p>
<?php endif; ?>
    <h2>5. Downloads</h2>
    <p>Die Vertragsentwürfe zum Download sind PDF-Dateien ohne eingebettete Tracker. Beim Herunterladen fallen nur die unter 3. genannten Server-Protokolle an.</p>

    <h2>6. Links zu App-Websites und App Stores</h2>
    <p>Links zu meinen App-Websites und zum App Store werden erst beim Anklicken aufgerufen. Dann gelten die Datenschutzhinweise der jeweiligen Seite bzw. von Apple.</p>

    <h2>7. Ihre Rechte</h2>
    <p>Sie haben das Recht auf Auskunft (Art. 15 DSGVO), Berichtigung (Art. 16), Löschung (Art. 17), Einschränkung der Verarbeitung (Art. 18), Datenübertragbarkeit (Art. 20) und Widerspruch gegen Verarbeitungen auf Grundlage berechtigter Interessen (Art. 21). Eine E-Mail an <a href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a> genügt.</p>
    <p>Sie können sich bei einer Datenschutz-Aufsichtsbehörde beschweren, zum Beispiel beim Landesbeauftragten für den Datenschutz und die Informationsfreiheit Rheinland-Pfalz.</p>

    <h2>8. Verschlüsselung</h2>
    <p>Die Website wird ausschließlich über HTTPS ausgeliefert. Ihre Eingaben im Kontaktformular werden verschlüsselt übertragen.</p>
  </div>
</section>
<?php page_foot();
