<?php
declare(strict_types=1);

/*
 * Fiktives Beispielprojekt für /beispiel/.
 * Grundlage: Tagessatz 850 € (Thomas 2026-11-10) – auf der Seite erscheinen nur Festpreise je Bauabschnitt.
 * Preise der Anbieter: laut Anbieter, Stand 11/2026, gerundet (Quellen im Vault: 01_Private/_homepage/2026-11-10-relaunch-plan.md).
 */

const BEISPIEL_TAGESSATZ = 850;

return [
    'weeks' => 10,

    // Phasen für den Fahrplan. start/end = Kalenderwochen 1..10 (inklusive), days = Aufwand in Tagen (0 = Festpreis separat)
    'phases' => [
        [
            'key'   => 'eval',
            'start' => 0, 'end' => 0, 'days' => 0, 'fixed' => 490, 'optional' => true,
            'title' => ['de' => 'App-Idee bewerten', 'en' => 'Idea assessment'],
            'items' => [
                'de' => ['Markt, Wettbewerb, Machbarkeit', 'Kosten- und Zeitschätzung', 'Empfehlung: bauen, anpassen, lassen'],
                'en' => ['Market, competition, feasibility', 'Cost and time estimate', 'Recommendation: build, adjust, drop'],
            ],
            'result' => ['de' => 'Bericht + Gespräch', 'en' => 'Report + call'],
        ],
        [
            'key'   => 'concept',
            'start' => 1, 'end' => 2, 'days' => 4,
            'title' => ['de' => 'Fachkonzept & Klick-Prototyp', 'en' => 'Concept & clickable prototype'],
            'items' => [
                'de' => ['User Stories mit Akzeptanzkriterien', 'Bildschirme als Klick-Prototyp', 'Datenmodell und Technik-Entscheidungen', 'Festpreise der Folgeabschnitte bestätigt'],
                'en' => ['User stories with acceptance criteria', 'Screens as clickable prototype', 'Data model and tech decisions', 'Fixed prices of later milestones confirmed'],
            ],
            'result' => ['de' => 'Live-Demo Prototyp · Abnahme 1', 'en' => 'Prototype demo · Acceptance 1'],
        ],
        [
            'key'   => 'core',
            'start' => 3, 'end' => 5, 'days' => 9,
            'title' => ['de' => 'Bauabschnitt: Kern-App', 'en' => 'Milestone: core app'],
            'items' => [
                'de' => ['Termin buchen', 'Reparaturstatus ansehen', 'Kundenkonto mit Anmeldung über Apple', 'Wöchentliche Test-Builds auf Ihrem iPhone'],
                'en' => ['Book an appointment', 'View repair status', 'Customer account with Sign in with Apple', 'Weekly test builds on your iPhone'],
            ],
            'result' => ['de' => 'Live-Demo · Abnahme 2', 'en' => 'Live demo · Acceptance 2'],
        ],
        [
            'key'   => 'server',
            'start' => 6, 'end' => 8, 'days' => 7,
            'title' => ['de' => 'Bauabschnitt: Server, Fotos & KI', 'en' => 'Milestone: server, photos & AI'],
            'items' => [
                'de' => ['Server auf Railway für Termine und Status', 'Schadensfotos sicher bei Hetzner (Deutschland)', 'KI fasst Schadensfoto als Text zusammen', 'Push-Nachricht bei „Rad ist fertig“'],
                'en' => ['Server on Railway for appointments and status', 'Damage photos stored securely at Hetzner (Germany)', 'AI summarises damage photos as text', 'Push notification when the bike is ready'],
            ],
            'result' => ['de' => 'Live-Demo · Abnahme 3', 'en' => 'Live demo · Acceptance 3'],
        ],
        [
            'key'   => 'launch',
            'start' => 9, 'end' => 10, 'days' => 5,
            'title' => ['de' => 'Bauabschnitt: Feinschliff & Store', 'en' => 'Milestone: polish & store'],
            'items' => [
                'de' => ['Deutsch und Englisch, Barrierefreiheit', 'Prüfung: Datenschutz, Sicherheit, Apple-Richtlinien', 'Screenshots und Entwürfe der Store-Texte', 'Einreichung bei Apple'],
                'en' => ['German and English, accessibility', 'Review: privacy, security, Apple guidelines', 'Screenshots and draft store copy', 'Submission to Apple'],
            ],
            'result' => ['de' => 'App im Store · Abnahme 4', 'en' => 'App in the store · Acceptance 4'],
        ],
    ],

    'stories' => [
        ['prio' => 'must',   'de' => ['Als Kunde möchte ich einen Werkstatttermin buchen,', 'damit ich nicht anrufen muss.', 'Freie Zeiten sind sichtbar; nach der Buchung kommt eine Bestätigung.'],
                             'en' => ['As a customer I want to book a workshop appointment', 'so I don’t have to call.', 'Free slots are visible; a confirmation follows the booking.']],
        ['prio' => 'must',   'de' => ['Als Kunde möchte ich den Reparaturstatus sehen,', 'damit ich weiß, wann mein Rad fertig ist.', 'Status „angenommen – in Arbeit – fertig“ ist jederzeit aktuell.'],
                             'en' => ['As a customer I want to see the repair status', 'so I know when my bike is ready.', 'Status “received – in progress – ready” is always up to date.']],
        ['prio' => 'must',   'de' => ['Als Werkstatt möchte ich den Status mit einem Tipp ändern,', 'damit Kunden ohne Anruf informiert sind.', 'Statuswechsel löst eine Push-Nachricht aus.'],
                             'en' => ['As the workshop I want to change the status with one tap', 'so customers are informed without a call.', 'A status change triggers a push notification.']],
        ['prio' => 'should', 'de' => ['Als Kunde möchte ich ein Foto vom Schaden schicken,', 'damit die Werkstatt vorab Bescheid weiß.', 'Die KI fasst das Foto in zwei Sätzen zusammen; das Foto liegt in Deutschland.'],
                             'en' => ['As a customer I want to send a photo of the damage', 'so the workshop knows in advance.', 'AI summarises the photo in two sentences; the photo is stored in Germany.']],
        ['prio' => 'should', 'de' => ['Als Kunde möchte ich die App auf Englisch nutzen,', 'damit auch Touristen buchen können.', 'Alle Texte gibt es auf Deutsch und Englisch.'],
                             'en' => ['As a customer I want to use the app in English', 'so tourists can book too.', 'All texts exist in German and English.']],
        ['prio' => 'could',  'de' => ['Als Kunde möchte ich an die Inspektion erinnert werden,', 'damit mein Rad sicher bleibt.', 'Erinnerung zwölf Monate nach dem letzten Termin.'],
                             'en' => ['As a customer I want a reminder for my service', 'so my bike stays safe.', 'Reminder twelve months after the last appointment.']],
    ],

    // Laufende Kosten der Anbieter (Beispiel: rund 500 aktive Nutzer, 1.000 Foto-Analysen im Monat)
    'running' => [
        ['de' => ['Apple Developer Program', 'App Store, TestFlight, Push', '99 € im Jahr (≈ 8 € im Monat)'],
         'en' => ['Apple Developer Program', 'App Store, TestFlight, push', '€99 per year (≈ €8 per month)']],
        ['de' => ['Railway', 'Server für Termine, Status, KI-Anbindung', 'ab ca. 5 $ im Monat, je nach Last bis ca. 20 $'],
         'en' => ['Railway', 'Server for appointments, status, AI access', 'from approx. $5 per month, up to approx. $20 depending on load']],
        ['de' => ['Hetzner Object Storage', 'Schadensfotos, Speicher in Deutschland', 'ab ca. 5–7 € im Monat (inkl. 1 TB)'],
         'en' => ['Hetzner Object Storage', 'Damage photos, storage in Germany', 'from approx. €5–7 per month (incl. 1 TB)']],
        ['de' => ['Anthropic (Claude API)', 'KI-Zusammenfassung der Fotos', 'ca. 1 Cent pro Foto · 1.000 Fotos ≈ 10 $ im Monat'],
         'en' => ['Anthropic (Claude API)', 'AI summary of the photos', 'approx. 1 cent per photo · 1,000 photos ≈ $10 per month']],
        ['de' => ['Domain & Webhosting', 'Website, Datenschutz, Support-Seite', 'je nach Anbieter, ca. 5–10 € im Monat'],
         'en' => ['Domain & web hosting', 'Website, privacy policy, support page', 'depends on provider, approx. €5–10 per month']],
    ],
    'running_total' => ['de' => 'zusammen etwa 30–60 € im Monat', 'en' => 'together roughly €30–60 per month'],
];
