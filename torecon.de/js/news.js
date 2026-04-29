/* torecon – News / Financial Trends data & renderer */

// Inline fallback – used if /data/news.json cannot be fetched.
// Eine Kachel pro Pillar (1–9), Reihenfolge nach Datum (neueste zuerst).
// Pflege-Quelle Pillar-Mapping: ~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md
const NEWS_DATA_FALLBACK = [
  {
    id: 1,
    date: '2026-04-28',
    tag_de: 'Agentic AI',
    tag_en: 'Agentic AI',
    title_de: 'Agent-Orchestrierung im Compliance-Reporting: Erste Bank-Produktiv-Cases',
    title_en: 'Agent orchestration in compliance reporting: first production cases at banks',
    excerpt_de: 'Drei europäische Großbanken haben Agent-Pipelines im Compliance- und MaRisk-Reporting in Produktion gebracht. Entscheidender Hebel: Memory-Patterns und Tool-Use-Schemas, nicht das Modell selbst.',
    excerpt_en: 'Three large European banks have moved agent pipelines for compliance and MaRisk reporting into production. The decisive lever: memory patterns and tool-use schemas, not the model itself.',
    url: 'https://www.mckinsey.com/industries/financial-services/our-insights',
  },
  {
    id: 2,
    date: '2026-04-26',
    tag_de: 'Datenplattform',
    tag_en: 'Data Platform',
    title_de: 'AI-Readiness: 73 % der KI-Piloten in Banken scheitern an Datenarchitektur, nicht am Modell',
    title_en: 'AI readiness: 73% of AI pilots at banks fail due to data architecture, not the model',
    excerpt_de: 'Eine neue BaFin-/Bundesbank-Erhebung zeigt: Quality, Lineage und Governance bestimmen den ROI von KI-Initiativen weit stärker als die Modellwahl. Lakehouse ohne Data-Contracts produziert den nächsten Silo.',
    excerpt_en: 'A new BaFin/Bundesbank survey shows: data quality, lineage and governance drive AI ROI far more than model selection. A lakehouse without data contracts simply produces the next silo.',
    url: 'https://www.bundesbank.de/de/aufgaben/bankenaufsicht/it-aufsicht',
  },
  {
    id: 3,
    date: '2026-04-25',
    tag_de: 'Regulierung',
    tag_en: 'Regulation',
    title_de: 'EBA-Konsultation zu IRRBB: Antworten bis 30.06.2026 – Auswirkungen auf ICAAP',
    title_en: 'EBA consultation on IRRBB: responses by 30 June 2026 – ICAAP impact ahead',
    excerpt_de: 'Die EBA verschärft die methodischen Anforderungen an Zinsänderungsrisiken im Anlagebuch. Banken müssen Stresstest-Szenarien und Modellannahmen in der ICAAP-Steuerung neu kalibrieren.',
    excerpt_en: 'The EBA is tightening methodological requirements for IRRBB. Banks need to recalibrate stress-test scenarios and model assumptions within ICAAP steering.',
    url: 'https://www.eba.europa.eu/regulation-and-policy/single-rulebook',
  },
  {
    id: 4,
    date: '2026-04-23',
    tag_de: 'Pricing',
    tag_en: 'Pricing',
    title_de: 'Time & Material verliert: DAX-IT-Verträge wechseln 2026 auf Sprint-Tier-Modelle',
    title_en: 'Time & material loses ground: DAX IT contracts shift to sprint-tier models in 2026',
    excerpt_de: 'Der Effizienzhebel agentischer Coding-Tools verschiebt die Pricing-Logik in der Build-Phase. Sprint-Tier, Outcome-Based und Quality-Gates verdrängen den klassischen Tagessatz – mit messbaren Folgen für IT-Beratungs-Margen.',
    excerpt_en: 'Agentic coding tools are reshaping pricing economics in the build phase. Sprint-tier, outcome-based and quality-gate models are displacing the classic day rate – with measurable margin effects for IT consulting.',
    url: 'https://www.luenendonk.de/produkte/studien-publikationen/',
  },
  {
    id: 5,
    date: '2026-04-22',
    tag_de: 'Customer Experience',
    tag_en: 'Customer Experience',
    title_de: 'Onboarding entscheidet: 9-Minuten-App schlägt 4-Tage-Filiale bei Hauptbankverbindung',
    title_en: 'Onboarding decides: a 9-minute app beats a 4-day branch for main-bank relationships',
    excerpt_de: 'Eine BVR-Studie quantifiziert den CX-Hebel: Banken mit reibungslosem digitalen Onboarding gewinnen Hauptbankverbindungen, andere verlieren Cross-Sell-Potenzial im ersten Quartal nach Eröffnung.',
    excerpt_en: 'A BVR study quantifies the CX lever: banks with frictionless digital onboarding win main-bank relationships, while others lose cross-sell potential in the first quarter after account opening.',
    url: 'https://www.bvr.de/Presse/Studien_und_Publikationen',
  },
  {
    id: 6,
    date: '2026-04-21',
    tag_de: 'Nachhaltigkeit',
    tag_en: 'Sustainability',
    title_de: 'EZB-Klima-Stresstest 2026: Übergangsrisiken erstmals kapitalrelevant',
    title_en: 'ECB climate stress test 2026: transition risks become capital-relevant for the first time',
    excerpt_de: 'Aus der Lernübung von 2022 ist 2026 ein Steuerungsinstrument geworden. Klima-Übergangsrisiken fließen erstmals in Säule-2-Kapitalanforderungen ein – mit Folgen für Kreditportfolios und ICAAP.',
    excerpt_en: 'What was a learning exercise in 2022 has become a steering tool in 2026. Climate transition risks now feed into Pillar 2 capital requirements for the first time – with consequences for loan portfolios and ICAAP.',
    url: 'https://www.ecb.europa.eu/ecb/climate/html/index.en.html',
  },
  {
    id: 7,
    date: '2026-04-19',
    tag_de: 'Legacy Transformation',
    tag_en: 'Legacy Transformation',
    title_de: 'Atruvia: agree21-Modernisierung in Phase 2 – Coexistence-Architektur bis 2028',
    title_en: 'Atruvia: agree21 modernisation enters phase 2 – coexistence architecture until 2028',
    excerpt_de: 'Atruvia hat den nächsten Modernisierungsschritt am Kernbanksystem agree21 gestartet. Cloud-fähige Microservices ersetzen schrittweise Backend-Altkomponenten – Coexistence wird zur Daueraufgabe für angeschlossene Institute.',
    excerpt_en: 'Atruvia has launched the next modernisation step on the agree21 core banking system. Cloud-ready microservices are gradually replacing legacy backend components – making coexistence a long-term task for affiliated banks.',
    url: 'https://www.atruvia.de/presse',
  },
  {
    id: 8,
    date: '2026-04-17',
    tag_de: 'Geldpolitik',
    tag_en: 'Monetary Policy',
    title_de: 'EZB senkt Einlagensatz auf 2,00 % – Zinsboden im H2 2026 erwartet',
    title_en: 'ECB cuts deposit rate to 2.00% – rate floor expected in H2 2026',
    excerpt_de: 'Die EZB hat ihren Einlagensatz auf 2,00 % gesenkt. Treasurer kalibrieren Pass-Through-Annahmen und Einlagen-Beta neu – Margenklemmen werden im H2 2026 zum Vorstandsthema.',
    excerpt_en: 'The ECB has cut its deposit rate to 2.00%. Treasurers are recalibrating pass-through assumptions and deposit betas – margin compression will move onto board agendas in H2 2026.',
    url: 'https://www.ecb.europa.eu/press/govcdec/mopo/html/index.en.html',
  },
  {
    id: 9,
    date: '2026-04-15',
    tag_de: 'Digitalisierung & KI',
    tag_en: 'Digitalisation & AI',
    title_de: 'GenAI im Service-Center: Drei Banken berichten 30 % Reduktion der Average Handling Time',
    title_en: 'GenAI in the service centre: three banks report 30% reduction in average handling time',
    excerpt_de: 'Erste Banken haben GenAI-Assistenten im Kunden-Service skaliert. Entscheidend war nicht das Modell, sondern die Integration in CRM, Wissensbasis und Eskalationspfade – plus saubere Quality-Gates für Halluzinationen.',
    excerpt_en: 'First banks have scaled GenAI assistants in customer service. The decisive factor was not the model but integration with CRM, knowledge base and escalation paths – plus solid quality gates against hallucinations.',
    url: 'https://www.bafin.de/DE/Aufsicht/FinTech/Kuenstliche_Intelligenz/ki_artikel.html',
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
