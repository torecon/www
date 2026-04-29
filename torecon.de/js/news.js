/* torecon – News / Financial Trends data & renderer */

// Inline fallback – used if /data/news.json cannot be fetched.
// Inhalte und URLs sind verifizierte echte Online-Artikel mit Original-
// Veröffentlichungsdatum (siehe Memory feedback_news_articles_must_be_real:
// niemals editorial-synthetische News). Reihenfolge: Datum DESC.
const NEWS_DATA_FALLBACK = [
  {
    id: 1,
    date: '2026-04-23',
    tag_de: 'Legacy Transformation',
    tag_en: 'Legacy Transformation',
    title_de: 'Rot-blaues Core-Banking-Match: Wer baut die beste Brücke?',
    title_en: 'Red-Blue Core Banking Match: Who builds the better bridge?',
    excerpt_de: 'Vergleich der Migrationsstrategien von Atruvia und Finanz Informatik: Atruvia setzt auf ein industrialisiertes Serien-Modell, das jetzt auch für Privatbanken außerhalb des Genossenschaftssektors geöffnet wird, während FI auf modulare Modernisierung des COBOL-Mainframes Richtung Java setzt. Beide Häuser betonen, dass Kernbankmigrationen kein reines IT-Projekt sind, sondern eine Chance zur Prozess-Standardisierung.',
    excerpt_en: 'Side-by-side analysis of the migration strategies of Atruvia and Finanz Informatik: Atruvia is doubling down on an industrialised serial-migration model now opening up to private banks beyond the cooperative sector, while FI pursues modular modernisation of its COBOL mainframe towards Java. Both providers stress that core-banking migrations are not just IT projects but a one-off chance to standardise processes.',
    url: 'https://www.it-finanzmagazin.de/migration-der-kernbanksysteme-rot-blauer-vergleich-242626/',
    source: 'IT-Finanzmagazin',
  },
  {
    id: 2,
    date: '2026-04-21',
    tag_de: 'Customer Experience',
    tag_en: 'Customer Experience',
    title_de: 'Acxiom-Report: So sehen Banken die Customer Experience der Zukunft',
    title_en: 'Acxiom Report: How banks see the future of Customer Experience',
    excerpt_de: 'Über die Hälfte der befragten Banker erwartet eine überwiegend KI-getriebene CX innerhalb von zehn Jahren, 32 Prozent sogar nahezu vollständige Automatisierung. 77 Prozent der Banken investieren bereits aktiv in Conversational AI — gleichzeitig wollen 65 Prozent der Konsumenten Kontrolle über KI-Entscheidungen statt Voll-Automatisierung.',
    excerpt_en: 'More than half of the banking executives surveyed expect customer experience to be predominantly AI-driven within a decade, with 32 percent forecasting near-total automation. 77 percent of banks are already investing in conversational AI, yet 65 percent of consumers want control over AI decisions rather than full automation.',
    url: 'https://www.it-finanzmagazin.de/acxiom-report-so-sehen-banken-die-customer-experience-der-zukunft-243188/',
    source: 'IT-Finanzmagazin',
  },
  {
    id: 3,
    date: '2026-04-08',
    tag_de: 'Agentic AI',
    tag_en: 'Agentic AI',
    title_de: 'Scaling Managed Agents: Wie Anthropic Hirn und Hand entkoppelt',
    title_en: 'Scaling Managed Agents: Decoupling the Brain from the Hands',
    excerpt_de: 'Anthropic beschreibt die Architektur seiner neuen Managed-Agents-Plattform: Session-Log, zustandsloser Harness und Sandbox sind voneinander entkoppelt, sodass jede Komponente unabhängig ausfallen oder ersetzt werden kann. Das Ergebnis: Time-to-first-Token sinkt um rund 60 Prozent im Median und über 90 Prozent im 95. Perzentil — bei sicherer Credential-Trennung.',
    excerpt_en: 'Anthropic details the architecture of its new Managed Agents platform: a session log, a stateless harness and sandboxes are decoupled so each component can fail or be swapped independently. Result: time-to-first-token drops roughly 60 percent at the median and over 90 percent at the 95th percentile, while keeping credentials out of untrusted code.',
    url: 'https://www.anthropic.com/engineering/managed-agents',
    source: 'Anthropic Engineering',
  },
  {
    id: 4,
    date: '2026-04-08',
    tag_de: 'Nachhaltigkeit',
    tag_en: 'Sustainability',
    title_de: 'Nachhaltigkeitsberichterstattung im Banking 2026',
    title_en: 'Sustainability Reporting in Banking 2026',
    excerpt_de: 'Die Studie wertet die CSRD-Berichte von 29 deutschen und 28 großen europäischen Banken aus und zeigt erhebliche Qualitätsunterschiede bei Datenintegrität für finanzierte Emissionen sowie Reife der Transitionspläne. Für das zweite Berichtsjahr werden konkrete Empfehlungen zur Optimierung von Datenqualität, Struktur und strategischer Steuerung abgeleitet.',
    excerpt_en: 'The study evaluates the CSRD reports of 29 German and 28 large European banks, revealing significant variation in data integrity for financed emissions and in the maturity of transition planning. It derives concrete recommendations to improve data quality, structure and strategic steering for the second compliance year.',
    url: 'https://www.der-bank-blog.de/nachhaltigkeitsberichterstattung-im-banking-2026/studien/37728614/',
    source: 'Der Bank Blog',
  },
  {
    id: 5,
    date: '2026-03-25',
    tag_de: 'Datenplattform',
    tag_en: 'Data Platform',
    title_de: '2026 wird zeigen, ob die Datenfundamente der Banken ihre KI-Ambitionen tragen',
    title_en: 'Year 2026 will test whether banks\' data foundations can sustain their AI ambitions',
    excerpt_de: 'Wettbewerbsvorteil verschiebt sich von Datenmenge zu Datenvertrauen — Provenance wird vom Nice-to-have zum strukturellen Designprinzip für erklärbare KI und Compliance. Hybrid-Architekturen aus Lakehouse, Data Fabric und selektiven Data-Mesh-Prinzipien lösen die Either-or-Frage zwischen zentraler Governance und domänen-eigener Verantwortung ab.',
    excerpt_en: 'Competitive advantage in banking shifts from data volume to data trustworthiness — provenance evolves from afterthought to structural design principle for explainable AI and compliance. Hybrid architectures combining lakehouse, data fabric and selective data-mesh principles supersede the either-or choice between central governance and domain ownership.',
    url: 'https://ibsintelligence.com/blogs/year-2026-will-test-whether-banks-data-foundations-can-sustain-their-ai-ambitions/',
    source: 'IBS Intelligence',
  },
  {
    id: 6,
    date: '2026-03-19',
    tag_de: 'Geldpolitik',
    tag_en: 'Monetary Policy',
    title_de: 'EZB-Geldpolitik-Statement vom 19. März 2026',
    title_en: 'ECB Monetary Policy Statement, 19 March 2026',
    excerpt_de: 'Der EZB-Rat hält die drei Leitzinsen unverändert (Einlagensatz 2,00 %, Hauptrefinanzierung 2,15 %, Spitzenrefinanzierung 2,40 %) und revidiert die Inflationsprognose 2026 wegen der Energiepreisfolgen des Nahost-Konflikts auf 2,6 % nach oben. Lagarde unterstreicht den datenabhängigen Meeting-by-Meeting-Ansatz ohne Vorfestlegung auf einen Zinspfad.',
    excerpt_en: 'The Governing Council keeps the three key ECB rates unchanged (deposit 2.00 %, main refinancing 2.15 %, marginal lending 2.40 %) and revises the 2026 inflation projection upwards to 2.6 % on the back of energy price effects from the Middle East conflict. Lagarde reiterates a data-dependent, meeting-by-meeting approach with no pre-commitment to a particular rate path.',
    url: 'https://www.ecb.europa.eu/press/press_conference/monetary-policy-statement/2026/html/ecb.is260319~93b1cbad97.en.html',
    source: 'European Central Bank',
  },
  {
    id: 7,
    date: '2026-03-11',
    tag_de: 'Pricing',
    tag_en: 'Pricing',
    title_de: 'IT-Berater-Tagessätze 2026: Das Ende der Preisrallye',
    title_en: 'IT Consultant Day Rates 2026: The end of the price rally',
    excerpt_de: 'Top-Tagessätze fallen 2026 erstmals deutlich: in der Infrastruktur um 7,2 %, in der Anwendungsentwicklung auf rund 1.500 € (–4,6 %). Treiber laut Metrics: Architektur- und PM-Aufgaben werden durch Standardisierung und KI-Tools automatisierbar, gleichzeitig erweitert Remote-Work den globalen Talent-Pool und drückt regionale Premiums.',
    excerpt_en: 'Top-tier IT consultant day rates fall meaningfully in 2026 for the first time: down 7.2 % in infrastructure and around 4.6 % in application development to roughly EUR 1,500. Per Metrics, the drivers are standardisation and AI-driven automation of architecture and PM tasks, plus remote work expanding the global talent pool and eroding regional premiums.',
    url: 'https://www.cio.de/article/4141771/it-berater-tagessaetze-2026-das-ende-der-preisrallye.html',
    source: 'CIO.de',
  },
  {
    id: 8,
    date: '2026-02-12',
    tag_de: 'Digitalisierung & KI',
    tag_en: 'Digitalisation & AI',
    title_de: 'Aufsichtsbehörden warnen vor Finanzbetrug mit KI und Kryptowerten',
    title_en: 'Supervisors warn against financial fraud using AI and crypto-assets',
    excerpt_de: 'BaFin, EBA, EIOPA und ESMA veröffentlichen gemeinsame interaktive Factsheets in allen EU-Sprachen, die typische KI-gestützte Betrugsmaschen mit Krypto-Assets erklären — von Deepfake-Investment-Calls bis zu KI-generierten Fake-Plattformen. Krypto-Investments wurden im 2026er Risikoausblick der Behörden explizit als Verbraucherrisiko-Priorität gelistet.',
    excerpt_en: 'BaFin, EBA, EIOPA and ESMA jointly publish interactive fact sheets in all EU languages explaining typical AI-enabled fraud schemes involving crypto-assets, from deepfake investment calls to AI-generated fake trading platforms. Crypto investments were explicitly listed as a consumer-risk priority in the supervisors\' 2026 risk outlook.',
    url: 'https://www.bafin.de/SharedDocs/Veroeffentlichungen/DE/Meldung/2026/meldung_2026_02_12_finanzbetrug_ki_kryptowerten.html',
    source: 'BaFin',
  },
  {
    id: 9,
    date: '2025-12-17',
    tag_de: 'Regulierung',
    tag_en: 'Regulation',
    title_de: 'EBA-Leitlinien zum erweiterten Operational-Risk-Reporting ab Juni 2026',
    title_en: 'EBA guidance on enhanced operational risk reporting ahead of June 2026',
    excerpt_de: 'Die European Banking Authority hat Leitlinien zur Umsetzung erweiterter Operational-Risk-Reporting-Anforderungen veröffentlicht. Banken haben bis Juni 2026 Zeit — drei Monate länger als ursprünglich geplant — um die neuen Reporting-Templates einzuführen. Die Leitlinien klären, welche Templates dann verpflichtend sind und welche freiwillig früher eingereicht werden können, samt aktualisierter IT-Tools.',
    excerpt_en: 'The European Banking Authority issued guidance helping banks comply with enhanced operational risk reporting requirements that take effect on the new June 2026 reference date — three months later than originally planned. The guidance clarifies which reporting templates become mandatory at that point versus those banks may submit voluntarily earlier, plus updated IT tools.',
    url: 'https://www.eba.europa.eu/publications-and-media/press-releases/eba-provides-guidance-banks-enhanced-reporting-requirements-operational-risk-ahead-new-june-2026',
    source: 'European Banking Authority',
  },
];

// Active data – populated by loadNewsData(), fallback used until then
let NEWS_DATA = NEWS_DATA_FALLBACK.slice();

function formatNewsDate(dateStr, lang) {
  const d = new Date(dateStr);
  return d.toLocaleDateString(lang === 'de' ? 'de-DE' : 'en-GB', {
    day: '2-digit', month: 'long', year: 'numeric'
  });
}

function renderNews(containerId, limit) {
  containerId = containerId || 'news-container';
  const container = document.getElementById(containerId);
  if (!container) return;
  const lang = (typeof currentLang !== 'undefined' ? currentLang : null) || 'de';
  const items = limit ? NEWS_DATA.slice(0, limit) : NEWS_DATA;
  container.innerHTML = items.map(function(item) {
    return '<a class="news-card" href="' + item.url + '" target="_blank" rel="noopener">' +
      '<div class="news-card-top">' +
        '<span class="news-tag">' + (lang === 'de' ? item.tag_de : item.tag_en) + '</span>' +
        '<span class="news-date">' + formatNewsDate(item.date, lang) + '</span>' +
      '</div>' +
      '<div class="news-card-body">' +
        '<h3>' + (lang === 'de' ? item.title_de : item.title_en) + '</h3>' +
        '<p>' + (lang === 'de' ? item.excerpt_de : item.excerpt_en) + '</p>' +
        '<span class="news-read-more">' + (lang === 'de' ? 'Weiterlesen →' : 'Read more →') + '</span>' +
      '</div>' +
    '</a>';
  }).join('');

  // Update "last updated" indicator on news page
  var standEl = document.getElementById('news-stand');
  if (standEl && NEWS_DATA.length) {
    var newest = NEWS_DATA.reduce(function(a, b) { return a.date > b.date ? a : b; });
    var label = lang === 'de' ? 'Stand: ' : 'Last updated: ';
    standEl.textContent = label + formatNewsDate(newest.date, lang);
  }
}

function loadNewsData(containerId, limit) {
  var root = (typeof window.TORECON_ROOT !== 'undefined') ? window.TORECON_ROOT : '/';
  fetch(root + 'data/news.json?v=' + Date.now())
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (Array.isArray(data) && data.length > 0) {
        NEWS_DATA = data;
        renderNews(containerId, limit);
      }
    })
    .catch(function() { /* fallback stays */ });
}
