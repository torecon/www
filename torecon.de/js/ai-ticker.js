/* torecon – Live AI News Ticker via RSS-to-JSON proxy */
(function () {
  var PROXY = 'https://api.rss2json.com/v1/api.json?count=6&rss_url=';

  var FEEDS = [
    'https://www.theverge.com/rss/ai-artificial-intelligence/index.xml',
    'https://venturebeat.com/category/ai/feed/',
    'https://www.technologyreview.com/feed/',
  ];

  // Built-in fallback – used only if ticker.json also fails
  var FALLBACK_BUILTIN = [
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
    var links = items
      .map(function(item) {
        return '<a href="' + (item.link || item.url || '#') + '" target="_blank" rel="noopener">' + item.title + '</a>';
      })
      .join('');
    // Duplicate for seamless CSS loop
    inner.innerHTML = links + links;
    // Adjust speed to content length
    var totalWidth = inner.scrollWidth / 2;
    var duration = Math.max(40, totalWidth / 110);
    inner.style.animationDuration = duration + 's';
  }

  function fetchFeed(url) {
    return new Promise(function(resolve) {
      try {
        var controller = new AbortController();
        var timer = setTimeout(function() { controller.abort(); }, 6000);
        fetch(PROXY + encodeURIComponent(url), { signal: controller.signal })
          .then(function(res) { return res.json(); })
          .then(function(data) {
            clearTimeout(timer);
            if (data.status === 'ok' && Array.isArray(data.items)) {
              resolve(data.items);
            } else {
              resolve([]);
            }
          })
          .catch(function() { resolve([]); });
      } catch(e) { resolve([]); }
    });
  }

  function loadTickerFallback() {
    var root = (typeof window.TORECON_ROOT !== 'undefined') ? window.TORECON_ROOT : '/';
    return fetch(root + 'data/ticker.json?v=' + Date.now())
      .then(function(r) { return r.json(); })
      .then(function(data) {
        return (Array.isArray(data) && data.length > 0) ? data : FALLBACK_BUILTIN;
      })
      .catch(function() { return FALLBACK_BUILTIN; });
  }

  function initTicker() {
    var wrap = document.getElementById('ai-ticker');
    if (!wrap) return;
    var inner = wrap.querySelector('.ai-ticker-inner');
    if (!inner) return;

    // Load custom fallback from JSON, show immediately, then try live RSS
    loadTickerFallback().then(function(fallbackItems) {
      renderItems(fallbackItems, inner);

      // Then try to load live RSS data
      Promise.all(FEEDS.map(fetchFeed)).then(function(results) {
        var items = [];
        results.forEach(function(r) { items = items.concat(r); });
        items = items.slice(0, 14);
        if (items.length >= 3) {
          renderItems(items, inner);
        }
        // If live data fails, fallback stays visible
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initTicker);
})();
