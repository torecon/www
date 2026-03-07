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
            <li style="color:rgba(255,255,255,0.45);font-size:13px;margin-top:4px;">Berliner Str. 8<br>10715 Berlin</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span data-i18n="footer_copy">© 2024 torecon – Thomas Reinke. Alle Rechte vorbehalten.</span>
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
