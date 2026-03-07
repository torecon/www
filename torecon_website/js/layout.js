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
        <li><a href="${root}references.html"${isActive('references')} data-i18n="nav_references">Referenzen</a></li>
        <li><a href="${root}contact.html"${isActive('contact')} data-i18n="nav_contact">Kontakt</a></li>
        <li><a href="${root}internal/login.html" class="nav-internal" data-i18n="nav_internal">Interner Bereich</a></li>
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
            <li><a href="${root}references.html" data-i18n="nav_references">Referenzen</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4 data-i18n="footer_legal_title">Rechtliches</h4>
          <ul>
            <li><a href="${root}imprint.html" data-i18n="footer_imprint">Impressum</a></li>
            <li><a href="${root}imprint.html#privacy" data-i18n="footer_privacy">Datenschutz</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4 data-i18n="footer_contact_title">Kontakt</h4>
          <ul>
            <li><a href="${root}contact.html">info@torecon.de</a></li>
            <li><a href="tel:+491723207681">+49 172 3207681</a></li>
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
        </div>
      </div>
    </div>
  </footer>`;
}

function initLayout() {
  const navEl = document.getElementById('site-nav');
  const footerEl = document.getElementById('site-footer');
  if (navEl) navEl.outerHTML = buildNav();
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

  applyTranslations();
}

document.addEventListener('DOMContentLoaded', initLayout);
