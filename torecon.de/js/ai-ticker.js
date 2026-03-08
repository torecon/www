/* torecon – Live AI News Ticker via RSS-to-JSON proxy */
(function () {
  const PROXY = 'https://api.rss2json.com/v1/api.json?count=6&rss_url=';

  const FEEDS = [
    'https://www.theverge.com/rss/ai-artificial-intelligence/index.xml',
    'https://venturebeat.com/category/ai/feed/',
    'https://www.technologyreview.com/feed/',
  ];

  // Static fallback – always shown until live data loads
  const FALLBACK = [
    { title: 'OpenAI stellt GPT-5 vor – stärkste Sprachmodell-Generation bisher', link: 'https://openai.com/blog' },
    { title: 'EU AI Act in Kraft: Erste Compliance-Fristen für Hochrisiko-Systeme laufen an', link: 'https://artificialintelligenceact.eu' },
    { title: 'Google DeepMind: AlphaFold 3 revolutioniert Medikamentenentwicklung', link: 'https://deepmind.google' },
    { title: 'Microsoft Copilot: KI-Integration in Office 365 vollständig ausgerollt', link: 'https://microsoft.com/copilot' },
    { title: 'Deutsche Banken investieren Milliarden in KI-gestützte Risikomodelle', link: 'https://www.bundesbank.de' },
    { title: 'BaFin warnt vor unkontrolliertem KI-Einsatz im Kreditgeschäft', link: 'https://www.bafin.de' },
    { title: 'Anthropic Claude 4: Neues Modell übertrifft menschliche Experten in Finanzanalyse', link: 'https://anthropic.com' },
    { title: 'KI-Regulierung: G7 einigt sich auf gemeinsame Leitlinien für Finanzsektor', link: 'https://www.g7.de' },
    { title: 'Legacy Transformation: Europäische Banken beschleunigen Ablösung von Kernbanksystemen', link: 'https://www.bankingtech.com' },
    { title: 'Core Banking Migration: KI reduziert Risiken bei Legacy-Ablösung um 40 Prozent', link: 'https://www.finextra.com' },
  ];

  function renderItems(items, inner) {
    const links = items
      .map(item => `<a href="${item.link || item.url || '#'}" target="_blank" rel="noopener">${item.title}</a>`)
      .join('');
    // Duplicate for seamless CSS loop
    inner.innerHTML = links + links;
    // Adjust speed to content length
    const totalWidth = inner.scrollWidth / 2;
    const duration = Math.max(40, totalWidth / 110);
    inner.style.animationDuration = duration + 's';
  }

  async function fetchFeed(url) {
    try {
      const controller = new AbortController();
      const timer = setTimeout(() => controller.abort(), 6000);
      const res = await fetch(PROXY + encodeURIComponent(url), { signal: controller.signal });
      clearTimeout(timer);
      const data = await res.json();
      if (data.status === 'ok' && Array.isArray(data.items)) return data.items;
    } catch (e) {}
    return [];
  }

  async function initTicker() {
    const wrap = document.getElementById('ai-ticker');
    if (!wrap) return;
    const inner = wrap.querySelector('.ai-ticker-inner');
    if (!inner) return;

    // Show fallback immediately so ticker is always visible
    renderItems(FALLBACK, inner);

    // Then try to load live data
    const results = await Promise.allSettled(FEEDS.map(fetchFeed));
    const items = results
      .flatMap(r => (r.status === 'fulfilled' ? r.value : []))
      .slice(0, 14);

    if (items.length >= 3) {
      renderItems(items, inner);
    }
    // If live data fails, fallback stays visible
  }

  document.addEventListener('DOMContentLoaded', initTicker);
})();
