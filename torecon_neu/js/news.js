/* torecon – News / Financial Trends data & renderer */
const NEWS_DATA = [
  {
    id: 1,
    date: '2024-03-07',
    tag_de: 'Geldpolitik',
    tag_en: 'Monetary Policy',
    title_de: 'EZB hält Leitzins stabil – Zinswende im Sommer erwartet',
    title_en: 'ECB holds key rate steady – rate cut expected in summer',
    excerpt_de: 'Die Europäische Zentralbank hat ihren Leitzins unverändert bei 4,5 % belassen. Marktteilnehmer rechnen mit ersten Senkungen ab Juni 2024.',
    excerpt_en: 'The European Central Bank has kept its key rate unchanged at 4.5%. Market participants expect the first cuts from June 2024.',
  },
  {
    id: 2,
    date: '2024-03-06',
    tag_de: 'Genossenschaftsbanken',
    tag_en: 'Cooperative Banks',
    title_de: 'Volksbanken und Raiffeisenbanken: Stabiles Wachstum trotz Marktdruck',
    title_en: 'Cooperative banks: Stable growth despite market pressure',
    excerpt_de: 'Der genossenschaftliche Bankensektor zeigt sich trotz steigender Kosten robust. Die Cost-Income-Ratio verbessert sich im Jahresvergleich.',
    excerpt_en: 'The cooperative banking sector proves resilient despite rising costs. The cost-income ratio improves year-on-year.',
  },
  {
    id: 3,
    date: '2024-03-05',
    tag_de: 'Regulierung',
    tag_en: 'Regulation',
    title_de: 'Basel IV: Neue Kapitalanforderungen ab 2025',
    title_en: 'Basel IV: New capital requirements from 2025',
    excerpt_de: 'Die schrittweise Einführung von Basel IV stellt Kreditinstitute vor neue Herausforderungen bei der Eigenkapitalplanung.',
    excerpt_en: 'The phased introduction of Basel IV presents credit institutions with new challenges in capital planning.',
  },
  {
    id: 4,
    date: '2024-03-04',
    tag_de: 'Digitalisierung',
    tag_en: 'Digitalisation',
    title_de: 'KI im Banking: Chancen und Risiken für Regionalbanken',
    title_en: 'AI in Banking: Opportunities and risks for regional banks',
    excerpt_de: 'Künstliche Intelligenz verändert das Bankgeschäft grundlegend. Regionalbanken müssen ihre Strategien anpassen, um wettbewerbsfähig zu bleiben.',
    excerpt_en: 'Artificial intelligence is fundamentally transforming banking. Regional banks must adapt their strategies to remain competitive.',
  },
  {
    id: 5,
    date: '2024-03-03',
    tag_de: 'Immobilienmarkt',
    tag_en: 'Real Estate',
    title_de: 'Immobilienfinanzierung: Stabilisierung nach Preiskorrektur',
    title_en: 'Real estate financing: Stabilisation after price correction',
    excerpt_de: 'Nach deutlichen Preiskorrekturen zeichnet sich eine Stabilisierung auf dem Immobilienfinanzierungsmarkt ab.',
    excerpt_en: 'After significant price corrections, stabilisation is emerging in the real estate financing market.',
  },
  {
    id: 6,
    date: '2024-03-02',
    tag_de: 'Ukraine',
    tag_en: 'Ukraine',
    title_de: 'Wiederaufbaufinanzierung Ukraine: Europäische Banken gefragt',
    title_en: 'Ukraine reconstruction financing: European banks in demand',
    excerpt_de: 'Die Planung des Wiederaufbaus der Ukraine schreitet voran. Europäische Kreditinstitute spielen eine zentrale Rolle bei der Finanzierung.',
    excerpt_en: 'Planning for Ukraine\'s reconstruction is progressing. European credit institutions play a central role in financing.',
  },
  {
    id: 7,
    date: '2024-03-01',
    tag_de: 'Zinsen',
    tag_en: 'Interest Rates',
    title_de: 'Margenentwicklung 2024: Druck auf Kreditinstitute bleibt',
    title_en: 'Margin development 2024: Pressure on credit institutions persists',
    excerpt_de: 'Die Zinsmarge der deutschen Kreditinstitute steht weiterhin unter Druck. Effizienzsteigerungen durch Digitalisierung sind unerlässlich.',
    excerpt_en: 'The interest margin of German credit institutions remains under pressure. Efficiency gains through digitalisation are indispensable.',
  },
  {
    id: 8,
    date: '2024-02-29',
    tag_de: 'Nachhaltigkeit',
    tag_en: 'Sustainability',
    title_de: 'ESG-Reporting: Neue Pflichten für Finanzinstitute',
    title_en: 'ESG reporting: New obligations for financial institutions',
    excerpt_de: 'Die CSRD-Richtlinie erweitert die Nachhaltigkeitsberichtspflichten erheblich. Finanzinstitute stehen vor umfangreichen Anpassungsaufgaben.',
    excerpt_en: 'The CSRD directive significantly expands sustainability reporting obligations. Financial institutions face extensive adaptation tasks.',
  },
];

function formatNewsDate(dateStr, lang) {
  const d = new Date(dateStr);
  return d.toLocaleDateString(lang === 'de' ? 'de-DE' : 'en-GB', {
    day: '2-digit', month: 'long', year: 'numeric'
  });
}

function renderNews(containerId = 'news-container', limit = null) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const lang = currentLang || 'de';
  const items = limit ? NEWS_DATA.slice(0, limit) : NEWS_DATA;
  container.innerHTML = items.map(item => `
    <article class="news-card">
      <div class="news-card-top">
        <span class="news-tag">${lang === 'de' ? item.tag_de : item.tag_en}</span>
        <span class="news-date">${formatNewsDate(item.date, lang)}</span>
      </div>
      <div class="news-card-body">
        <h3>${lang === 'de' ? item.title_de : item.title_en}</h3>
        <p>${lang === 'de' ? item.excerpt_de : item.excerpt_en}</p>
      </div>
    </article>
  `).join('');
}
