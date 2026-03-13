/* webdesign.torecon.de – Nav & Footer
   Eigenständige Navigation für den Webdesign-Auftritt */

function buildNav() {
  return `
  <nav class="site-nav">
    <div class="nav-inner">
      <a href="./" class="nav-logo">tore<span>con</span><span style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.45);margin-left:6px;letter-spacing:0.02em;">Webdesign</span></a>
      <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="#nutzen">Vorteile</a></li>
        <li><a href="#preise">Preise</a></li>
        <li><a href="#briefing">Briefing</a></li>
        <li><a href="https://www.torecon.de/" style="color:rgba(255,255,255,0.4);font-size:13px;" target="_blank" rel="noopener">torecon.de</a></li>
      </ul>
    </div>
  </nav>`;
}

function buildFooter() {
  return `
  <footer class="site-footer">
    <div class="container-wide">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="./" class="footer-logo">tore<span>con</span></a>
          <p>Webdesign für Steuerberater, Rechtsanwälte, Notare, Handwerker &amp; Freelancer – Festpreis, DSGVO-konform, kein Monatsabo.</p>
        </div>
        <div class="footer-col">
          <h4>Auf dieser Seite</h4>
          <ul>
            <li><a href="#nutzen">Vorteile</a></li>
            <li><a href="#preise">Preise</a></li>
            <li><a href="#briefing">Briefing starten</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Rechtliches</h4>
          <ul>
            <li><a href="https://www.torecon.de/imprint.html">Impressum</a></li>
            <li><a href="https://www.torecon.de/imprint.html#privacy">Datenschutz</a></li>
            <li><a href="https://www.torecon.de/agb.html">AGB</a></li>
            <li><a href="https://www.torecon.de/vertrag.html">Mustervertrag</a></li>
            <li><a href="https://www.torecon.de/avv.html">AVV</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Kontakt</h4>
          <ul>
            <li><a href="mailto:info@torecon.de">info@torecon.de</a></li>
            <li style="color:rgba(255,255,255,0.45);font-size:13px;margin-top:4px;">Roesgenstr. 22<br>53474 Ahrweiler</li>
            <li style="margin-top:12px;">
              <a href="https://www.torecon.de/" target="_blank" rel="noopener"
                 style="display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,0.5);font-size:13px;border:1px solid rgba(255,255,255,0.18);padding:5px 12px;border-radius:20px;">
                Bankberatung: torecon.de →
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 torecon – Thomas Reinke. Alle Rechte vorbehalten.</span>
        <div class="footer-bottom-links">
          <a href="https://www.torecon.de/imprint.html">Impressum</a>
          <a href="https://www.torecon.de/imprint.html#privacy">Datenschutz</a>
          <a href="https://www.torecon.de/agb.html">AGB</a>
        </div>
      </div>
    </div>
  </footer>`;
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

  // WhatsApp FAB
  document.body.insertAdjacentHTML('beforeend', buildWhatsAppFab());

  // Cookie Consent Banner
  if (typeof initCookieBanner === 'function') initCookieBanner();
}

document.addEventListener('DOMContentLoaded', initLayout);
