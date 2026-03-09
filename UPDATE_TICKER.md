# Ticker-Fallback aktualisieren

## Befehl zum Kopieren:

```
Bitte aktualisiere die statischen Fallback-Schlagzeilen im Ticker der torecon-Website.

Datei: torecon.de/js/ai-ticker.js
Bereich: const FALLBACK = [ ... ] (Zeilen 12–23)

Ersetze alle 10 Einträge durch aktuelle, glaubwürdige Schlagzeilen zu diesen Themen:
- KI & Digitalisierung im Bankensektor
- EZB, Leitzins, Geldpolitik
- Regulierung: BaFin, Basel IV, EBA
- Genossenschaftsbanken / Volksbanken
- ESG / Nachhaltigkeit im Finanzsektor
- Legacy-Transformation / Kernbanksysteme

Regeln:
- Schlagzeilen auf Deutsch, präzise und seriös
- Links nur zu echten, bekannten Domains (bafin.de, bundesbank.de, dzbank.de, ecb.europa.eu, finextra.com etc.)
- Kein erfundener Inhalt – wenn kein aktuelles Ereignis bekannt ist, formuliere eine zeitlose Aussage im Nachrichtenstil
- Format beibehalten: { title: '...', link: '...' }
```

---

## Was danach zu tun ist:

1. Claude aktualisiert `ai-ticker.js`
2. ZIP neu erstellen: `cd torecon_website && zip -r ../torecon_deploy.zip .`
3. ZIP in Plesk hochladen → `html/` extrahieren → fertig
