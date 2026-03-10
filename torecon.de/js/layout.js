/* torecon – Shared nav & footer injection
   Each page sets: window.TORECON_ROOT (relative path back to root)
                   window.TORECON_PAGE (active nav key) */

function buildNav() {
  const root = window.TORECON_ROOT || './';
  const page = window.TORECON_PAGE || '';
  const isActive = (p) => page === p ? ' class="active"' : '';

  return `
  <div class="lang-bar">
    <button class="lang-btn ${currentLang === 'de' ? 'active' : ''}" data-lang="de" onclick="setLang('de')">DE</button>
    <button class="lang-btn ${currentLang === 'en' ? 'active' : ''}" data-lang="en" onclick="setLang('en')">EN</button>
  </div>
  <nav class="site-nav">
    <div class="nav-inner">
      <a href="${root}index.html" class="nav-logo">tore<span>con</span></a>
      <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="${root}index.html"${isActive('home')} data-i18n="nav_home">Home</a></li>
        <li><a href="${root}services.html"${isActive('services')} data-i18n="nav_services">Leistungen</a></li>
        <li><a href="${root}news.html"${isActive('news')} data-i18n="nav_news">Finanztrends</a></li>
        <li><a href="${root}newsletter.html"${isActive('newsletter')} data-i18n="nav_newsletter">Newsletter</a></li>
        <li><a href="${root}references.html"${isActive('references')} data-i18n="nav_references">Referenzen</a></li>
        <li><a href="${root}contact.html"${isActive('contact')} data-i18n="nav_contact">Kontakt</a></li>
        <li><a href="https://intern.torecon.de/" class="nav-internal" data-i18n="nav_internal">Interner Bereich</a></li>
      </ul>
    </div>
  </nav>`;
}

function buildFooter() {
  const root = window.TORECON_ROOT || './';
  return `
  <footer class="site-footer">
    <div class="container-wide">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="${root}index.html" class="footer-logo">tore<span>con</span></a>
          <p data-i18n="footer_desc">Beratung für Finanzinstitutionen – strategisch, kompetent, zuverlässig.</p>
        </div>
        <div class="footer-col">
          <h4 data-i18n="footer_nav_title">Navigation</h4>
          <ul>
            <li><a href="${root}index.html" data-i18n="nav_home">Home</a></li>
            <li><a href="${root}services.html" data-i18n="nav_services">Leistungen</a></li>
            <li><a href="${root}news.html" data-i18n="nav_news">Finanztrends</a></li>
            <li><a href="${root}newsletter.html" data-i18n="nav_newsletter">Newsletter</a></li>
            <li><a href="${root}references.html" data-i18n="nav_references">Referenzen</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4 data-i18n="footer_legal_title">Rechtliches</h4>
          <ul>
            <li><a href="${root}imprint.html" data-i18n="footer_imprint">Impressum</a></li>
            <li><a href="${root}imprint.html#privacy" data-i18n="footer_privacy">Datenschutz</a></li>
            <li><a href="${root}agb.html">AGB</a></li>
            <li><a href="${root}vertrag.html" data-i18n="footer_contract">Mustervertrag</a></li>
            <li><a href="${root}avv.html" data-i18n="footer_avv">AVV</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4 data-i18n="footer_contact_title">Kontakt</h4>
          <ul>
            <li><a href="${root}contact.html">info@torecon.de</a></li>
            <li style="color:rgba(255,255,255,0.45);font-size:13px;margin-top:4px;">Roesgenstr. 22<br>53474 Ahrweiler</li>
            <li style="margin-top:12px;">
              <a href="https://www.linkedin.com/in/thomas-reinke-a60092109" target="_blank" rel="noopener"
                 style="display:inline-flex;align-items:center;gap:6px;color:#fff;font-size:13px;background:#0A66C2;padding:5px 12px;border-radius:20px;">
                <svg viewBox="0 0 24 24" width="13" height="13" style="fill:#fff;flex-shrink:0"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                LinkedIn
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span data-i18n="footer_copy">© 2026 torecon – Thomas Reinke. Alle Rechte vorbehalten.</span>
        <div class="footer-bottom-links">
          <a href="${root}imprint.html" data-i18n="footer_imprint">Impressum</a>
          <a href="${root}imprint.html#privacy" data-i18n="footer_privacy">Datenschutz</a>
          <a href="${root}agb.html">AGB</a>
          <a href="${root}vertrag.html" data-i18n="footer_contract">Mustervertrag</a>
          <a href="${root}avv.html" data-i18n="footer_avv">AVV</a>
        </div>
      </div>
    </div>
  </footer>`;
}

function buildTicker() {
  return `
  <div class="ai-ticker-wrap" id="ai-ticker">
    <div class="ai-ticker-label">AI Live</div>
    <div class="ai-ticker-track">
      <div class="ai-ticker-inner"></div>
    </div>
  </div>`;
}

function buildWhatsAppFab() {
  return `
  <a href="https://wa.me/491723207681" target="_blank" rel="noopener"
     class="whatsapp-fab" aria-label="WhatsApp-Kontakt">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
  </a>`;
}

function initLayout() {
  const navEl = document.getElementById('site-nav');
  const footerEl = document.getElementById('site-footer');
  if (navEl) navEl.outerHTML = buildNav() + buildTicker();
  if (footerEl) footerEl.outerHTML = buildFooter();

  // Mobile hamburger
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open);
    });
  }

  // WhatsApp FAB
  document.body.insertAdjacentHTML('beforeend', buildWhatsAppFab());

  applyTranslations();

  // Cookie Consent Banner (nach Translations, damit Sprache bekannt ist)
  if (typeof initCookieBanner === 'function') initCookieBanner();
}

document.addEventListener('DOMContentLoaded', initLayout);
