/* torecon – News / Financial Trends data & renderer */

// Inline fallback – used if /data/news.json cannot be fetched
const NEWS_DATA_FALLBACK = [
  {
    id: 1,
    date: '2026-03-12',
    tag_de: 'Geldpolitik',
    tag_en: 'Monetary Policy',
    title_de: 'EZB bestätigt Kurs: Leitzins bei 2,25 % – nächster Schritt im April erwartet',
    title_en: 'ECB holds course: key rate at 2.25% – next step expected in April',
    excerpt_de: 'Die Europäische Zentralbank hält ihren Einlagenzins bei 2,25 % stabil. Angesichts rückläufiger Inflation und schwacher Konjunktur in der Eurozone wird für April ein weiterer Schritt diskutiert.',
    excerpt_en: 'The European Central Bank is keeping its deposit rate stable at 2.25%. With inflation declining and weak eurozone growth, another move is being discussed for April.',
    url: 'https://www.ecb.europa.eu/press/govcdec/mopo/html/index.en.html',
  },
  {
    id: 2,
    date: '2026-03-11',
    tag_de: 'Genossenschaftsbanken',
    tag_en: 'Cooperative Banks',
    title_de: 'DZ Bank Gruppe: Rekordjahr 2025 – Genossenschaftssektor weiter stark',
    title_en: 'DZ Bank Group: Record year 2025 – cooperative sector remains strong',
    excerpt_de: 'Die DZ Bank hat für 2025 ein Rekordergebnis bekanntgegeben. Der genossenschaftliche Sektor profitiert von stabiler Kundenbasis und gesundem Kreditportfolio.',
    excerpt_en: 'DZ Bank has announced a record result for 2025. The cooperative sector benefits from a stable customer base and healthy loan portfolio.',
    url: 'https://www.dzbank.de/content/dzbank/de/home/presse.html',
  },
  {
    id: 3,
    date: '2026-03-10',
    tag_de: 'Regulierung',
    tag_en: 'Regulation',
    title_de: 'Basel IV vollständig in Kraft: Erste Bilanzierungseffekte sichtbar',
    title_en: 'Basel IV fully in force: First balance sheet effects visible',
    excerpt_de: 'Seit Januar 2026 gelten alle Basel-IV-Vorschriften. Kreditinstitute melden erste spürbare Auswirkungen auf Eigenkapitalquoten und Kreditvergabespielräume.',
    excerpt_en: 'All Basel IV rules have applied since January 2026. Credit institutions report the first noticeable effects on capital ratios and lending capacity.',
    url: 'https://www.bis.org/bcbs/basel3.htm',
  },
  {
    id: 4,
    date: '2026-03-09',
    tag_de: 'Digitalisierung',
    tag_en: 'Digitalisation',
    title_de: 'KI-gestütztes Kreditscoring: BaFin veröffentlicht Leitlinien',
    title_en: 'AI-based credit scoring: BaFin publishes guidelines',
    excerpt_de: 'Die BaFin hat erstmals verbindliche Leitlinien für den Einsatz von KI-Modellen im Kreditvergabeprozess veröffentlicht. Transparenz und Erklärbarkeit stehen im Fokus.',
    excerpt_en: 'BaFin has published binding guidelines for the use of AI models in the lending process for the first time. Transparency and explainability are the focus.',
    url: 'https://www.bafin.de/DE/Aufsicht/FinTech/Kuenstliche_Intelligenz/ki_artikel.html',
  },
  {
    id: 5,
    date: '2026-03-08',
    tag_de: 'Digitaler Euro',
    tag_en: 'Digital Euro',
    title_de: 'Digitaler Euro: EZB startet Pilotprogramm mit ausgewählten Banken',
    title_en: 'Digital Euro: ECB launches pilot programme with selected banks',
    excerpt_de: 'Die EZB hat die erste Pilotphase des digitalen Euro gestartet. Zwölf europäische Kreditinstitute nehmen an dem Programm teil, darunter zwei deutsche Genossenschaftsbanken.',
    excerpt_en: 'The ECB has launched the first pilot phase of the digital euro. Twelve European credit institutions are participating, including two German cooperative banks.',
    url: 'https://www.ecb.europa.eu/paym/digital_euro/html/index.en.html',
  },
  {
    id: 6,
    date: '2026-03-07',
    tag_de: 'Ukraine',
    tag_en: 'Ukraine',
    title_de: 'Ukraine-Wiederaufbau: EBRD verdoppelt Kreditlinie für Finanzsektor',
    title_en: 'Ukraine reconstruction: EBRD doubles credit line for financial sector',
    excerpt_de: 'Die Europäische Bank für Wiederaufbau und Entwicklung stellt weitere 5 Mrd. Euro für den ukrainischen Finanzsektor bereit. Europäische Banken sind als Durchleitinstitute gefragt.',
    excerpt_en: 'The EBRD is providing a further €5bn for the Ukrainian financial sector. European banks are in demand as intermediary institutions.',
    url: 'https://www.ebrd.com/ukraine.html',
  },
  {
    id: 7,
    date: '2026-03-05',
    tag_de: 'Zinsmarge',
    tag_en: 'Interest Margin',
    title_de: 'Sinkende Leitzinsen: Regionalbanken überdenken Einlagenstrategie',
    title_en: 'Falling key rates: Regional banks rethink deposit strategy',
    excerpt_de: 'Mit dem Rückgang der EZB-Zinsen gerät das Einlagengeschäft unter Druck. Viele Regionalbanken und Sparkassen passen ihre Konditionenmodelle neu an.',
    excerpt_en: 'With ECB rates falling, the deposit business is under pressure. Many regional banks and savings banks are adjusting their pricing models.',
    url: 'https://www.bundesbank.de/de/aufgaben/themen/banksteuerung',
  },
  {
    id: 8,
    date: '2026-03-04',
    tag_de: 'Nachhaltigkeit',
    tag_en: 'Sustainability',
    title_de: 'CSRD-Umsetzung: Kreditinstitute stehen vor operativer Bewährungsprobe',
    title_en: 'CSRD implementation: Credit institutions face operational test',
    excerpt_de: 'Die ersten verpflichtenden Nachhaltigkeitsberichte nach CSRD sind fällig. Viele Kreditinstitute kämpfen mit Datenlücken und fehlendem Fachpersonal.',
    excerpt_en: 'The first mandatory sustainability reports under CSRD are due. Many credit institutions are struggling with data gaps and a lack of specialist staff.',
    url: 'https://finance.ec.europa.eu/capital-markets-union-and-financial-markets/company-reporting-and-auditing/company-reporting/corporate-sustainability-reporting_en',
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
        '<span class="news-read-more">' + (lang === 'de' ? 'Weiterlesen \u2192' : 'Read more \u2192') + '</span>' +
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
