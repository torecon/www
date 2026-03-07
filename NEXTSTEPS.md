# torecon – Nächste Schritte & Optimierungsvorschläge

## Sofort umsetzbar (hohe Wirkung)

### 1. Google Search Console einrichten
- Konto auf search.google.com/search-console anlegen
- Domain torecon.de verifizieren (TXT-Record oder HTML-Datei)
- Sitemap einreichen: https://www.torecon.de/sitemap.xml
- Crawl-Fehler und Index-Status überwachen

### 2. Favicon
- Aktuell kein Favicon → Browser-Tab zeigt kein Logo
- Einfach: torecon-Initialen "TC" oder Symbol als 32x32 PNG
- Einbinden: `<link rel="icon" type="image/png" href="./img/favicon.png">`

### 3. Kontaktformular-Backend
- Das Formular sendet aktuell keine echten E-Mails (nur Darstellung)
- Einfachste Lösung ohne Server-Code: EmailJS (kostenloser Tarif)
- Alternative: Formspree oder PHP-Script auf Plesk-Server

## Mittelfristig

### 4. Newsletter-Backend
- Aktuell: localStorage (nur lokal, kein echter Versand)
- Empfehlung: Brevo (ehemals Sendinblue) – kostenlos bis 300 Mails/Tag, DSGVO-konform
- Alternative: Mailchimp (kostenlos bis 500 Kontakte), Cleverreach
- Double-Opt-In nach DSGVO zwingend erforderlich!

### 5. Google Analytics / Matomo
- Für DSGVO-konformes Tracking: Matomo (self-hosted oder Cloud)
- Alternativ: Google Analytics 4 mit IP-Anonymisierung + Cookie-Banner
- Datenschutzerklärung muss dann aktualisiert werden

### 6. Google Business Profile
- Kostenloses Profil für lokale Suchanfragen anlegen
- Wichtig für: "Bankberater Ahrweiler", "Unternehmensberater Rheinland-Pfalz"
- Bewertungen sammeln → stärkt Vertrauen und lokales Ranking

### 7. FAQ-Sektion auf services.html
- Google zeigt FAQs direkt in den Suchergebnissen (Rich Snippets)
- Vorgeschlagene Fragen:
  - "Was ist die Cost-Income-Ratio (CIR)?"
  - "Warum einen spezialisierten Genossenschaftsbank-Berater?"
  - "Was kostet eine Webseite bei torecon?"
  - "Wie läuft ein Beratungsprojekt ab?"

## Längerfristig

### 8. Blog / Fachartikel-Sektion
- Eigene Artikel würden die SEO-Stärke massiv erhöhen
- Themenideen:
  - CIR-Optimierung in der Praxis
  - Basel IV: Was Genossenschaftsbanken jetzt wissen müssen
  - Legacy-Migration ohne Betriebsunterbrechung
  - Credit Unions in Osteuropa – Erfahrungsbericht
- Einfach: weitere statische HTML-Seiten im gleichen Layout

### 9. Testimonials / Referenzen
- Kundenstimmen stärken Vertrauen erheblich
- Auch anonym möglich: "Volksbank, Bayern" / "Kreditinstitut, Osteuropa"
- JSON-LD Review-Schema → Google kann Sterne-Bewertungen anzeigen

### 10. Performance-Optimierung
- thomas-reinke.jpeg auf WebP konvertieren (kleinere Dateigröße)
- Lazy Loading für News-Cards
- CSS/JS minifizieren für Produktionsdeploy

## Keywords mit ungenutztem Potenzial
- "Gesamtbanksteuerung Beratung" – kaum Konkurrenz, sehr spezifisch
- "CIR Optimierung Volksbank" – sehr gezielt
- "Bankplanung Software unabhängig" – Thomas ist unabhängig, kein Vendor
- "Credit Union Beratung Deutschland" – Nischenthema, hohe Relevanz
- "Legacy Banking Transformation Osteuropa" – sehr spezifisch
