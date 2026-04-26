# zettelkueche.app — Landing-Page

Marketing-/Listing-Site für **Zettelküche AI** (iOS/iPadOS Rezept-Capture-App).

Domain: `https://zettelkueche.app`

## Status (2026-04-25)

🔧 **Skelett angelegt, Inhalte noch nicht implementiert.**

Folgt Pattern und Struktur der Landing-Pages anderer Torecon-Apps:
- `getbrainbites.ai/`
- `getthinkless.ai/`
- `eladung.torecon.de/`

## Geplante Struktur (v1.0-Submission-Pflicht)

```
zettelkueche.app/
├── index.html              # Hero + Features + Pricing + Download-Link
├── logo.png                # 1024×1024 (Single-Source mit App-Icon)
├── apple-touch-icon.png
├── favicon-32.png
├── privacy/                # Pflicht-URL für ASC
│   └── index.html
├── terms/                  # Pflicht-URL für ASC
│   └── index.html
├── support/                # Pflicht-URL für ASC
│   └── index.html
└── CLAUDE.md               # Site-spezifischer Kontext für Claude-Sessions
```

## Pflicht-URLs (vor App-Store-Submission v1.0)

- `https://zettelkueche.app/` — Marketing
- `https://zettelkueche.app/privacy` — Privacy Policy (DE+EN)
- `https://zettelkueche.app/terms` — AGB (DE+EN)
- `https://zettelkueche.app/support` — Support / FAQ / Kontakt

## Inhalts-Aufbau (Vorlage aus BrainBites/ThinkLess)

1. **Hero:** Tagline + App-Store-Badge + 1–2 Hero-Screenshots
2. **Pain Point → Lösung:** „Rezepte gesammelt — aber wo?" / „Foto rein, strukturiertes Rezept raus"
3. **Features:** Capture, OCR+AI, iCloud-Sync, Edit, Suche/Filter, Sharing, ChatGPT-Adaption
4. **Wie es funktioniert:** 3-Schritt-Erklärung
5. **Pricing:** Free vs Premium (Tabelle, OHNE feste €-Beträge, da ASC regional steuert)
6. **Privacy-Versprechen:** „Deine Rezepte bleiben in deiner iCloud. Wir sehen sie nie."
7. **Download-CTA:** App Store Badge
8. **Footer:** Privacy, Terms, Support, Impressum, EU-DSA-Contact

## Lokalisierung

- **DE** als Primärsprache (Brand „Zettelküche" deutsch)
- **EN** parallel; Sprachumschaltung via Header-Toggle (analog `getbrainbites.ai/index.html`)

## Deploy

Wird in `torecon/www`-Monorepo via FTP-Deploy-Skripten ausgespielt (siehe `../DEPLOY.md`). Domain-Config + Hosting beim Provider beim Site-Launch erledigen.

## Update-Pflicht

Bei jedem Minor-Update der iOS-App (v1.x → v1.(x+1)) muss diese Landing-Page mit neuen Highlights in allen unterstützten Sprachen aktualisiert werden (gemäß globaler Memory-Regel `feedback_homepage_update.md`).

## Memory-Verweis

Site-spezifische Memory liegt im App-Projekt-Memory unter:
`~/.claude/projects/-Users-thomas-Documents-GitHub-Zettelkueche/memory/app_store_strategy.md`
