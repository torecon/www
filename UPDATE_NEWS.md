# Financial Trends / News aktualisieren

## Befehl zum Kopieren:

```
Bitte aktualisiere die Finanztrends auf der torecon-Website.

Datei: torecon.de/js/news.js
Bereich: const NEWS_DATA = [ ... ] (Zeilen 2–91)

Ersetze alle 8 Einträge durch aktuelle Beiträge. Das heutige Datum ist [DATUM EINSETZEN].

Jeder Eintrag bekommt das Datum des verlinkten Ereignisses – nicht das heutige Datum.
Ein Thema darf auch aus den letzten 3–6 Monaten stammen, wenn es fachlich relevant ist.

Jeder Eintrag muss folgendes Format haben:
{
  id: [1–8],
  date: 'YYYY-MM-DD',         // Datum des verlinkten Ereignisses/Artikels – NICHT das heutige Datum
  tag_de: '...',              // kurzes Schlagwort auf Deutsch
  tag_en: '...',              // kurzes Schlagwort auf Englisch
  title_de: '...',            // Titel auf Deutsch (max. 80 Zeichen)
  title_en: '...',            // Titel auf Englisch (max. 80 Zeichen)
  excerpt_de: '...',          // 2–3 Sätze auf Deutsch
  excerpt_en: '...',          // 2–3 Sätze auf Englisch
  url: 'https://...',         // echte, bekannte Domain
}

Themen – je ein Eintrag pro Bereich:
1. Geldpolitik & Zinsen (EZB, Leitzins, Inflation)
2. Genossenschaftsbanken (DZ Bank, BVR, Volksbanken)
3. Regulierung & Compliance (Basel IV, BaFin, EBA)
4. Digitalisierung & KI im Bankensektor
5. Digitaler Euro oder Zahlungsverkehr
6. Internationale Märkte (EBRD, IMF, Osteuropa, Ukraine)
7. Zinsmarge / Ertragsmodell Regionalbanken
8. Nachhaltigkeit & ESG (CSRD, Green Finance, Taxonomie)

Regeln:
- Inhalte seriös, fachlich korrekt, im Stil einer Finanz-Nachrichtenseite
- Links nur zu echten Domains: ecb.europa.eu, bafin.de, bundesbank.de, bis.org, dzbank.de, ebrd.com, finance.ec.europa.eu, finextra.com etc.
- Wenn kein konkretes aktuelles Ereignis bekannt ist, formuliere eine zeitlos-aktuelle Aussage im Nachrichtenstil
- Datum = Datum des verlinkten Artikels oder Ereignisses (kann auch Wochen zurückliegen, wenn das Thema relevant ist)
- Einträge absteigend sortieren nach Datum (neuestes zuerst)
- Sprache: präzise, sachlich, kein Marketing-Ton
```

---

## Was danach zu tun ist:

1. Claude aktualisiert `news.js`
2. ZIP neu erstellen: `cd torecon_website && zip -r ../torecon_deploy.zip .`
3. ZIP in Plesk hochladen → `html/` extrahieren → fertig
