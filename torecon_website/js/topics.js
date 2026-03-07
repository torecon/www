/* torecon – Shared topic definitions (Homepage & Newsletter – always identical) */
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
    id: 'genossen',
    icon: '🏦',
    label_de: 'Genossenschaftsbanken',
    label_en: 'Cooperative Banks',
    sub_de:   'DZ Bank, BVR, Volksbanken',
    sub_en:   'DZ Bank, BVR, credit unions',
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
    id: 'bankplanung',
    icon: '🗺️',
    label_de: 'Strategische Bankplanung',
    label_en: 'Strategic Bank Planning',
    sub_de:   'CIR, PCR, Gesamtbanksteuerung',
    sub_en:   'CIR, PCR, bank-wide management',
  },
  {
    id: 'international',
    icon: '🌍',
    label_de: 'Internationale Märkte',
    label_en: 'International Markets',
    sub_de:   'EBRD, IMF, Osteuropa',
    sub_en:   'EBRD, IMF, Eastern Europe',
  },
  {
    id: 'legacy',
    icon: '🔄',
    label_de: 'Legacy Transformation',
    label_en: 'Legacy Transformation',
    sub_de:   'Kernbanksysteme, Migration, Modernisierung',
    sub_en:   'Core banking, migration, modernisation',
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
