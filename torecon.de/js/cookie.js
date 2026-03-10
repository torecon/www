/* torecon – Cookie Consent (DSGVO/GDPR)
   Speichert Zustimmung in localStorage unter 'torecon_cookie_consent'
   Werte: 'all' | 'necessary' */

const COOKIE_KEY = 'torecon_cookie_consent';

function getCookieConsent() {
  return localStorage.getItem(COOKIE_KEY);
}

function setCookieConsent(value) {
  localStorage.setItem(COOKIE_KEY, value);
}

function hideCookieBanner() {
  const banner = document.getElementById('cookie-banner');
  if (banner) {
    banner.classList.add('cookie-banner--hidden');
    setTimeout(() => banner.remove(), 400);
  }
}

function acceptAllCookies() {
  setCookieConsent('all');
  hideCookieBanner();
}

function acceptNecessaryCookies() {
  setCookieConsent('necessary');
  hideCookieBanner();
}

function buildCookieBanner() {
  const root = window.TORECON_ROOT || './';
  const t = (typeof TRANSLATIONS !== 'undefined' && typeof currentLang !== 'undefined')
    ? (TRANSLATIONS[currentLang] || TRANSLATIONS['de'])
    : TRANSLATIONS['de'];

  const banner = document.createElement('div');
  banner.id = 'cookie-banner';
  banner.setAttribute('role', 'dialog');
  banner.setAttribute('aria-label', t.cookie_label || 'Cookie-Einstellungen');
  banner.innerHTML = `
    <div class="cookie-inner">
      <div class="cookie-text">
        <p>${t.cookie_text || 'Wir verwenden Cookies, um die Nutzererfahrung zu verbessern und anonyme Nutzungsstatistiken zu erheben. Technisch notwendige Cookies sind immer aktiv.'} <a href="${root}imprint.html#privacy">${t.cookie_more || 'Mehr erfahren'}</a></p>
      </div>
      <div class="cookie-actions">
        <button class="cookie-btn cookie-btn--secondary" onclick="acceptNecessaryCookies()">${t.cookie_necessary || 'Nur notwendige'}</button>
        <button class="cookie-btn cookie-btn--primary" onclick="acceptAllCookies()">${t.cookie_accept_all || 'Alle akzeptieren'}</button>
      </div>
    </div>`;
  return banner;
}

function initCookieBanner() {
  if (getCookieConsent()) return; // bereits entschieden
  const banner = buildCookieBanner();
  document.body.appendChild(banner);
  // kurze Verzögerung für Einblend-Animation
  requestAnimationFrame(() => {
    requestAnimationFrame(() => banner.classList.add('cookie-banner--visible'));
  });
}
