/* torecon – Shared topic definitions (Homepage & Newsletter – always identical)
   Pflege-Quelle (Single Source of Truth):
   ~/Obsidian/MyBrain/03_Development/_projects/linkedin/pillars/index.md (Pillar 1–9) */
const TOPICS = [
  {
    id: 'geldpolitik',
    icon: '📈',
    label_de: 'Geldpolitik & Zinsen',
    label_en: 'Monetary Policy & Rates',
    sub_de:   'EZB, Leitzins, Inflation',
    sub_en:   'ECB, key rates, inflation',
  },
  {
    id: 'cx',
    icon: '📱',
    label_de: 'Digitale Customer Experience & Omnichannel',
    label_en: 'Digital Customer Experience & Omnichannel',
    sub_de:   'CX-Strategie, App, digitale Filiale',
    sub_en:   'CX strategy, app, digital branch',
  },
  {
    id: 'regulierung',
    icon: '⚖️',
    label_de: 'Regulierung & Compliance',
    label_en: 'Regulation & Compliance',
    sub_de:   'Basel IV, BaFin, EBA',
    sub_en:   'Basel IV, BaFin, EBA',
  },
  {
    id: 'digitalisierung',
    icon: '🤖',
    label_de: 'Digitalisierung & KI',
    label_en: 'Digitalisation & AI',
    sub_de:   'Fintech, AI, Kreditscoring',
    sub_en:   'Fintech, AI, credit scoring',
  },
  {
    id: 'esg',
    icon: '🌱',
    label_de: 'Nachhaltigkeit & ESG',
    label_en: 'Sustainability & ESG',
    sub_de:   'CSRD, Green Finance, Taxonomie',
    sub_en:   'CSRD, green finance, taxonomy',
  },
  {
    id: 'datenplattform',
    icon: '📊',
    label_de: 'Datenplattform für KI',
    label_en: 'Data Platform for AI',
    sub_de:   'AI-Readiness, Data Mesh, Governance',
    sub_en:   'AI readiness, data mesh, governance',
  },
  {
    id: 'agentic-ai',
    icon: '🧩',
    label_de: 'Agentic AI in der Praxis',
    label_en: 'Agentic AI in Practice',
    sub_de:   'Agent-Orchestrierung, Memory, Tool-Use',
    sub_en:   'Agent orchestration, memory, tool use',
  },
  {
    id: 'legacy',
    icon: '🔄',
    label_de: 'Legacy Transformation',
    label_en: 'Legacy Transformation',
    sub_de:   'Kernbanksysteme, Migration, Modernisierung',
    sub_en:   'Core banking, migration, modernisation',
  },
  {
    id: 'pricing',
    icon: '💼',
    label_de: 'Pricing',
    label_en: 'Pricing',
    sub_de:   'Outcome-Based, Sprint-Tier, Quality-Gates',
    sub_en:   'Outcome-based, sprint-tier, quality gates',
  },
];

/* Render topics into a container.
   opts.interactive = true  → clickable chips with selection state
   opts.max          = N    → max selectable (interactive mode only) */
function renderTopics(containerId, opts) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const lang = (typeof currentLang !== 'undefined' ? currentLang : 'de');
  const interactive = !!(opts && opts.interactive);
  const max = (opts && opts.max) ? opts.max : 99;

  container.innerHTML = TOPICS.map(t => {
    const label = lang === 'de' ? t.label_de : t.label_en;
    const sub   = lang === 'de' ? t.sub_de   : t.sub_en;
    return `
    <div class="topic-chip${interactive ? ' topic-interactive' : ''}"
         data-topic="${t.id}"
         data-label="${label}">
      <span class="topic-chip-icon">${t.icon}</span>
      <div class="topic-chip-text">
        <div class="topic-chip-label">${label}</div>
        <div class="topic-chip-freq">${sub}</div>
      </div>
      ${interactive ? '<span class="topic-check"></span>' : ''}
    </div>`;
  }).join('');

  if (interactive) _initTopicSelection(container, max);
}

function _initTopicSelection(container, max) {
  const chips     = container.querySelectorAll('.topic-chip');
  const counterEl = document.getElementById('topic-count');
  const displayEl = document.getElementById('selected-topics-display');

  function getSelected() {
    return Array.from(chips)
      .filter(c => c.classList.contains('selected'))
      .map(c => c.getAttribute('data-label'));
  }

  function updateUI() {
    const selected = getSelected();
    const count = selected.length;
    if (counterEl) counterEl.textContent = count;
    chips.forEach(chip => {
      const atMax = !chip.classList.contains('selected') && count >= max;
      chip.classList.toggle('chip-disabled', atMax);
    });
    if (displayEl) {
      displayEl.textContent = count === 0
        ? (typeof currentLang !== 'undefined' && currentLang === 'en'
            ? 'Please select at least one topic above.'
            : 'Bitte wählen Sie oben mindestens ein Thema aus.')
        : selected.join(' · ');
      displayEl.style.color = count === 0 ? 'var(--text-secondary)' : 'var(--text)';
    }
  }

  chips.forEach(chip => {
    chip.addEventListener('click', function () {
      if (this.classList.contains('chip-disabled')) return;
      this.classList.toggle('selected');
      updateUI();
    });
  });

  updateUI();
}

/* Expose selected topics for form submission */
function getSelectedTopics(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return [];
  return Array.from(container.querySelectorAll('.topic-chip.selected'))
    .map(c => c.getAttribute('data-label'));
}
