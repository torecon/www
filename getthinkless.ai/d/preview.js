/*
 * ThinkLess AI — Vorschauseite für geteilte Entscheidungen (v1.6, Backlog 121)
 *
 * Link-Format v1 (identisch zur App, SharePayload.swift / ShareSignature.swift):
 *   https://getthinkless.ai/d/#1.<D>.<S>   signiert
 *   https://getthinkless.ai/d/#1.<D>       unsigniert („nicht bestätigt“)
 *   D = base64url(deflate-raw(UTF-8-JSON)), S = base64url(r||s), ES256 über
 *   die entpackten Payload-Bytes.
 *
 * Regeln: Der Inhalt steht nur im Fragment und verlässt den Browser nie
 * (kein Netzwerkzugriff, kein Tracking, kein Cookie). Darstellung ausschließlich
 * per textContent. Grenzen werden vor dem Parsen geprüft (Fragment ≤ 8 KB,
 * entpackt ≤ 16 KB). Öffentliche Schlüssel nur anhängen, nie entfernen.
 */
'use strict';

(function (root) {
  // ── Format ────────────────────────────────────────────────────────────────
  const FORMAT_VERSION = 1;
  const PAYLOAD_TYPE = 'decision';
  const MAX_FRAGMENT = 8192;
  const MAX_PAYLOAD = 16384;
  const SIGNATURE_LENGTH = 64;
  const LIMIT = Object.freeze({
    question: 500, option: 120, options: 5, criterion: 50, criteria: 3,
    recommendation: 500, aiText: 4000, factors: 12, factorLabel: 300, shortField: 32
  });

  /** Produktivschlüssel (SPKI DER, base64url). Anhängen, nie entfernen. */
  const PRODUCTION_KEYS = Object.freeze({
    'tl-2026-11': 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEpbX3klnbNABBM1iJCLXYQtRcy0g9vHlor8G1Kowgeydh5KQ8pRJzPY1Q5M0wIOZzVO9Dvz2KnwWhVJdxjvwl_Q'
  });

  const B64URL = /^[A-Za-z0-9_-]+$/;

  /** Strenges base64url (RFC 4648 §5, ohne Padding) → Uint8Array oder null. */
  function b64urlDecode(text) {
    if (typeof text !== 'string' || text.length === 0 || !B64URL.test(text) || text.length % 4 === 1) return null;
    let b64 = text.replace(/-/g, '+').replace(/_/g, '/');
    b64 += '='.repeat((4 - (b64.length % 4)) % 4);
    try {
      const bin = atob(b64);
      const out = new Uint8Array(bin.length);
      for (let i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
      return out;
    } catch (_) {
      return null;
    }
  }

  function capabilities() {
    return typeof DecompressionStream === 'function' &&
      typeof TextDecoder === 'function' &&
      typeof crypto === 'object' && crypto !== null && typeof crypto.subtle === 'object';
  }

  /** deflate-raw entpacken, höchstens limit Bytes (Schutz vor Kompressionsbomben). */
  async function inflateRaw(bytes, limit) {
    let ds;
    try { ds = new DecompressionStream('deflate-raw'); } catch (_) { return { error: 'unsupported' }; }
    const writer = ds.writable.getWriter();
    writer.write(bytes).catch(() => {});
    writer.close().catch(() => {});
    const reader = ds.readable.getReader();
    const chunks = [];
    let total = 0;
    try {
      for (;;) {
        const { done, value } = await reader.read();
        if (done) break;
        total += value.length;
        if (total > limit) {
          reader.cancel().catch(() => {});
          return { error: 'tooLarge' };
        }
        chunks.push(value);
      }
    } catch (_) {
      return { error: 'malformed' };
    }
    if (total === 0) return { error: 'malformed' };
    const out = new Uint8Array(total);
    let offset = 0;
    for (const c of chunks) { out.set(c, offset); offset += c.length; }
    return { bytes: out };
  }

  /** Zeichen wie Swift String.count (Grapheme), Fallback Codepoints. */
  function charCount(text) {
    if (typeof Intl === 'object' && typeof Intl.Segmenter === 'function') {
      let n = 0;
      for (const _ of new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(text)) n++;
      return n;
    }
    return Array.from(text).length;
  }

  const isStr = (v) => typeof v === 'string';
  const opt = (v) => (v === null || v === undefined ? undefined : v);

  /** Prüft die Feldtypen wie Swift Codable (fehlend/null = nicht gesetzt). */
  function readWire(obj) {
    const w = {};
    const strFields = ['kid', 'd', 'lang', 'mode', 'cat', 'q', 'rec', 'conf', 'why', 'next', 'alt'];
    for (const k of strFields) {
      const v = opt(obj[k]);
      if (v !== undefined && !isStr(v)) return null;
      w[k] = v;
    }
    const ai = opt(obj.ai);
    if (ai !== undefined && typeof ai !== 'boolean') return null;
    w.ai = ai;
    const opts = opt(obj.opts);
    if (opts !== undefined && !(Array.isArray(opts) && opts.every(isStr))) return null;
    w.opts = opts || [];
    const fac = opt(obj.fac);
    if (fac !== undefined) {
      if (!Array.isArray(fac)) return null;
      for (const f of fac) {
        if (f === null || typeof f !== 'object' || !isStr(f.l) || !isStr(f.i)) return null;
      }
    }
    w.fac = (fac || []).map((f) => ({ l: f.l, i: f.i }));
    const crit = opt(obj.crit);
    if (crit !== undefined) {
      if (!Array.isArray(crit)) return null;
      for (const c of crit) {
        if (c === null || typeof c !== 'object' || !isStr(c.n) || typeof c.w !== 'number' || !isFinite(c.w)) return null;
      }
    }
    w.crit = (crit || []).map((c) => ({ n: c.n, w: c.w }));
    return w;
  }

  function validate(w) {
    const L = LIMIT;
    const ok = (text, max, required) => {
      if (text === undefined) return !required;
      return charCount(text) <= max && (!required || text.trim().length > 0);
    };
    const valid =
      ok(w.q, L.question, true) && ok(w.rec, L.recommendation, true) &&
      ok(w.kid, L.shortField) && ok(w.d, L.shortField) && ok(w.lang, L.shortField) &&
      ok(w.mode, L.shortField) && ok(w.cat, L.shortField) && ok(w.conf, L.shortField) &&
      w.opts.length <= L.options && w.opts.every((o) => ok(o, L.option)) &&
      ok(w.why, L.aiText) && ok(w.next, L.aiText) && ok(w.alt, L.aiText) &&
      w.fac.length <= L.factors && w.fac.every((f) => ok(f.l, L.factorLabel) && ok(f.i, L.shortField)) &&
      w.crit.length <= L.criteria && w.crit.every((c) => ok(c.n, L.criterion) && c.w >= 0 && c.w <= 1);
    if (!valid) return null;
    const conf = ['high', 'medium', 'low'].includes(w.conf) ? w.conf : 'low';
    return {
      kid: w.kid === undefined ? null : w.kid,
      day: w.d === undefined ? null : w.d,
      language: w.lang === undefined ? null : w.lang,
      mode: w.mode === undefined ? null : w.mode,
      category: w.cat === undefined ? null : w.cat,
      question: w.q,
      options: w.opts,
      recommendation: w.rec,
      confidence: conf,
      isAIGenerated: w.ai === true,
      reasoning: w.why === undefined ? null : w.why,
      nextStep: w.next === undefined ? null : w.next,
      factors: w.fac.map((f) => ({
        label: f.l, impact: ['positive', 'negative', 'neutral'].includes(f.i) ? f.i : 'neutral'
      })),
      alternativeNote: w.alt === undefined ? null : w.alt,
      criteria: w.crit.map((c) => ({ name: c.n, weight: c.w }))
    };
  }

  /** Payload-Bytes → { content } oder { error }. */
  function parseContent(payload) {
    if (payload.length > MAX_PAYLOAD) return { error: 'tooLarge' };
    let text;
    try { text = new TextDecoder('utf-8', { fatal: true }).decode(payload); } catch (_) { return { error: 'malformed' }; }
    let obj;
    try { obj = JSON.parse(text); } catch (_) { return { error: 'malformed' }; }
    if (obj === null || typeof obj !== 'object' || Array.isArray(obj)) return { error: 'malformed' };
    if (!Number.isInteger(obj.v) || !isStr(obj.t)) return { error: 'malformed' };
    if (obj.v !== FORMAT_VERSION) return { error: 'unsupportedVersion', version: obj.v };
    if (obj.t !== PAYLOAD_TYPE) return { error: 'unsupportedType', type: obj.t };
    const wire = readWire(obj);
    if (!wire) return { error: 'malformed' };
    const content = validate(wire);
    return content ? { content } : { error: 'malformed' };
  }

  /**
   * Fragment `1.<D>[.<S>]` dekodieren (ohne Prüfung der Signatur).
   * Ergebnis: { payload, signature, content } oder { error, version?, type? }.
   * error ∈ notShareLink | tooLarge | malformed | unsupportedVersion | unsupportedType | unsupported
   */
  async function decodeFragment(fragment) {
    if (typeof fragment !== 'string' || fragment.length === 0) return { error: 'notShareLink' };
    if (new TextEncoder().encode(fragment).length > MAX_FRAGMENT) return { error: 'tooLarge' };
    const parts = fragment.split('.');
    if (!/^[0-9]+$/.test(parts[0])) return { error: 'malformed' };
    const version = Number(parts[0]);
    if (!Number.isSafeInteger(version)) return { error: 'malformed' };
    if (version !== FORMAT_VERSION) return { error: 'unsupportedVersion', version };
    if (parts.length !== 2 && parts.length !== 3) return { error: 'malformed' };
    const signature = parts.length === 3 ? parts[2] : null;
    if (signature !== null && b64urlDecode(signature) === null) return { error: 'malformed' };
    const compressed = b64urlDecode(parts[1]);
    if (!compressed) return { error: 'malformed' };
    const inflated = await inflateRaw(compressed, MAX_PAYLOAD);
    if (inflated.error) return { error: inflated.error };
    const parsed = parseContent(inflated.bytes);
    if (parsed.error) return parsed;
    return { payload: inflated.bytes, signature, content: parsed.content };
  }

  /** Fragment aus location.hash (ohne „#“, Prozent-Kodierung aufgelöst). */
  function fragmentFromHash(hash) {
    const raw = (hash || '').replace(/^#/, '');
    if (raw.length > MAX_FRAGMENT * 3) return { tooLarge: true };
    try { return { fragment: decodeURIComponent(raw) }; } catch (_) { return { fragment: null }; }
  }

  /**
   * ES256-Prüfung über die exakten Payload-Bytes.
   * → 'confirmed' | 'unconfirmed' (ohne Signatur / unbekannter kid) | 'invalid'
   */
  async function verify(payload, signature, kid, keys) {
    const ring = keys || PRODUCTION_KEYS;
    if (signature === null || signature === undefined) return 'unconfirmed';
    if (!isStr(kid) || !Object.prototype.hasOwnProperty.call(ring, kid)) return 'unconfirmed';
    const spki = b64urlDecode(ring[kid]);
    let key = null;
    if (spki) {
      try {
        key = await crypto.subtle.importKey('spki', spki, { name: 'ECDSA', namedCurve: 'P-256' }, false, ['verify']);
      } catch (_) {
        key = null;
      }
    }
    // Unlesbarer Schlüssel zählt wie ein unbekannter (wie ShareKeyring in der App).
    if (!key) return 'unconfirmed';
    const raw = b64urlDecode(signature);
    if (!raw || raw.length !== SIGNATURE_LENGTH) return 'invalid';
    try {
      const good = await crypto.subtle.verify({ name: 'ECDSA', hash: 'SHA-256' }, key, raw, payload);
      return good ? 'confirmed' : 'invalid';
    } catch (_) {
      return 'invalid';
    }
  }

  /** Gesamtergebnis für die Seite: state + content (nur wenn anzeigbar). */
  async function evaluate(hash, keys) {
    const raw = (hash || '').replace(/^#/, '');
    if (raw.length === 0) return { state: 'empty' };
    if (!capabilities()) return { state: 'browser', fragment: null };
    const fh = fragmentFromHash(hash);
    if (fh.tooLarge || fh.fragment === null) return { state: 'broken' };
    const decoded = await decodeFragment(fh.fragment);
    if (decoded.error === 'unsupported') return { state: 'browser' };
    if (decoded.error === 'unsupportedVersion' || decoded.error === 'unsupportedType') {
      return { state: 'tooNew', fragment: /^[A-Za-z0-9_.-]+$/.test(fh.fragment) ? fh.fragment : null };
    }
    if (decoded.error) return { state: 'broken' };
    const status = await verify(decoded.payload, decoded.signature, decoded.content.kid, keys);
    if (status === 'invalid') return { state: 'invalid' };
    return { state: status, content: decoded.content, fragment: fh.fragment };
  }

  const core = Object.freeze({
    FORMAT_VERSION, MAX_FRAGMENT, MAX_PAYLOAD, LIMIT, PRODUCTION_KEYS,
    b64urlDecode, decodeFragment, parseContent, verify, evaluate
  });

  // ── Texte (DE · EN · PT-BR · UK, wie die Landing) ────────────────────────
  const T = {
    de: {
      page_title: 'Geteilte Entscheidung – ThinkLess AI',
      kicker: 'Mit dir geteilt',
      loading: 'Entscheidung wird geladen …',
      q_label: 'Frage',
      opts_label: 'Optionen',
      rec_label: 'Empfehlung',
      conf_label: 'Sicherheit',
      conf_high: 'hoch', conf_medium: 'mittel', conf_low: 'niedrig',
      why_label: 'Begründung',
      next_label: 'Nächster Schritt',
      fac_label: 'Faktoren',
      alt_label: 'Alternative',
      crit_label: 'Kriterien',
      imp_positive: 'spricht dafür', imp_negative: 'spricht dagegen', imp_neutral: 'neutral',
      day: 'Analysiert am {d}',
      ai_off: 'Ohne KI berechnet',
      st_confirmed_t: 'Von ThinkLess bestätigt',
      st_confirmed_p: 'Das Ergebnis kommt unverändert aus ThinkLess.',
      st_unconfirmed_t: 'Nicht bestätigt',
      st_unconfirmed_p: 'Dieses Ergebnis trägt keine Bestätigung von ThinkLess und kann verändert worden sein.',
      st_invalid_t: 'Link beschädigt oder verändert',
      st_invalid_p: 'Die Bestätigung passt nicht zum Inhalt, deshalb zeigen wir ihn nicht an. Bitte lass dir den Link noch einmal schicken.',
      st_tooNew_t: 'Neuere ThinkLess-Version nötig',
      st_tooNew_p: 'Dieser Link stammt aus einer neueren Version. Öffne ihn in der App und aktualisiere sie bei Bedarf.',
      st_broken_t: 'Link unvollständig',
      st_broken_p: 'Der Link ist abgeschnitten oder fehlerhaft. Bitte lass ihn dir noch einmal schicken.',
      st_empty_t: 'Hier erscheinen geteilte Entscheidungen',
      st_empty_p: 'Wenn dir jemand eine Entscheidung aus ThinkLess schickt, siehst du sie hier.',
      st_browser_t: 'Vorschau in diesem Browser nicht möglich',
      st_browser_p: 'Öffne den Link in der App oder in einem aktuellen Browser.',
      note: 'Geteilt von einer Person, die ThinkLess nutzt. Frage und Optionen sind ihre eigenen Worte und wurden nicht geprüft.',
      privacy: 'Der Inhalt steht nur im Link und wird nur in deinem Browser angezeigt. Er wird nicht an unseren Server übertragen.',
      btn_open: 'In der App öffnen',
      btn_store: 'ThinkLess im App Store',
      cta_t: 'Selbst klarer entscheiden',
      cta_p: 'ThinkLess AI hilft dir, in unter einer Minute zu einer Entscheidung zu kommen.',
      footer_brand: 'eine App von',
      footer_privacy: 'Datenschutz', footer_terms: 'Nutzungsbedingungen', footer_imprint: 'Impressum',
      lang_label: 'Sprache'
    },
    en: {
      page_title: 'Shared decision – ThinkLess AI',
      kicker: 'Shared with you',
      loading: 'Loading decision …',
      q_label: 'Question',
      opts_label: 'Options',
      rec_label: 'Recommendation',
      conf_label: 'Confidence',
      conf_high: 'high', conf_medium: 'medium', conf_low: 'low',
      why_label: 'Reasoning',
      next_label: 'Next step',
      fac_label: 'Factors',
      alt_label: 'Alternative',
      crit_label: 'Criteria',
      imp_positive: 'in favour', imp_negative: 'against', imp_neutral: 'neutral',
      day: 'Analysed on {d}',
      ai_off: 'Calculated without AI',
      st_confirmed_t: 'Confirmed by ThinkLess',
      st_confirmed_p: 'This result comes from ThinkLess unchanged.',
      st_unconfirmed_t: 'Not confirmed',
      st_unconfirmed_p: 'This result has no confirmation from ThinkLess and may have been changed.',
      st_invalid_t: 'Link damaged or changed',
      st_invalid_p: 'The confirmation does not match the content, so we are not showing it. Please ask for the link again.',
      st_tooNew_t: 'Newer ThinkLess version needed',
      st_tooNew_p: 'This link comes from a newer version. Open it in the app and update it if needed.',
      st_broken_t: 'Link incomplete',
      st_broken_p: 'The link is cut off or broken. Please ask for it again.',
      st_empty_t: 'Shared decisions appear here',
      st_empty_p: 'When someone sends you a decision from ThinkLess, you will see it here.',
      st_browser_t: 'Preview not available in this browser',
      st_browser_p: 'Open the link in the app or in an up-to-date browser.',
      note: 'Shared by someone who uses ThinkLess. The question and options are their own words and have not been reviewed.',
      privacy: 'The content is only in the link and is only shown in your browser. It is not sent to our server.',
      btn_open: 'Open in the app',
      btn_store: 'ThinkLess on the App Store',
      cta_t: 'Decide more clearly yourself',
      cta_p: 'ThinkLess AI helps you reach a decision in under a minute.',
      footer_brand: 'an app by',
      footer_privacy: 'Privacy', footer_terms: 'Terms of Use', footer_imprint: 'Imprint',
      lang_label: 'Language'
    },
    pt: {
      page_title: 'Decisão compartilhada – ThinkLess AI',
      kicker: 'Compartilhada com você',
      loading: 'Carregando a decisão …',
      q_label: 'Pergunta',
      opts_label: 'Opções',
      rec_label: 'Recomendação',
      conf_label: 'Confiança',
      conf_high: 'alta', conf_medium: 'média', conf_low: 'baixa',
      why_label: 'Justificativa',
      next_label: 'Próximo passo',
      fac_label: 'Fatores',
      alt_label: 'Alternativa',
      crit_label: 'Critérios',
      imp_positive: 'a favor', imp_negative: 'contra', imp_neutral: 'neutro',
      day: 'Analisada em {d}',
      ai_off: 'Calculada sem IA',
      st_confirmed_t: 'Confirmada pelo ThinkLess',
      st_confirmed_p: 'Este resultado vem do ThinkLess sem alterações.',
      st_unconfirmed_t: 'Não confirmada',
      st_unconfirmed_p: 'Este resultado não tem confirmação do ThinkLess e pode ter sido alterado.',
      st_invalid_t: 'Link danificado ou alterado',
      st_invalid_p: 'A confirmação não corresponde ao conteúdo, por isso não o exibimos. Peça o link novamente.',
      st_tooNew_t: 'É preciso uma versão mais nova do ThinkLess',
      st_tooNew_p: 'Este link vem de uma versão mais nova. Abra-o no app e atualize-o, se necessário.',
      st_broken_t: 'Link incompleto',
      st_broken_p: 'O link está cortado ou com erro. Peça-o novamente.',
      st_empty_t: 'Decisões compartilhadas aparecem aqui',
      st_empty_p: 'Quando alguém enviar uma decisão do ThinkLess para você, ela aparecerá aqui.',
      st_browser_t: 'Prévia indisponível neste navegador',
      st_browser_p: 'Abra o link no app ou em um navegador atualizado.',
      note: 'Compartilhada por alguém que usa o ThinkLess. A pergunta e as opções são palavras dessa pessoa e não foram revisadas.',
      privacy: 'O conteúdo está apenas no link e é exibido somente no seu navegador. Ele não é enviado ao nosso servidor.',
      btn_open: 'Abrir no app',
      btn_store: 'ThinkLess na App Store',
      cta_t: 'Decida com mais clareza você também',
      cta_p: 'O ThinkLess AI ajuda você a chegar a uma decisão em menos de um minuto.',
      footer_brand: 'um app da',
      footer_privacy: 'Privacidade', footer_terms: 'Termos de uso', footer_imprint: 'Informações legais',
      lang_label: 'Idioma'
    },
    uk: {
      page_title: 'Поширене рішення – ThinkLess AI',
      kicker: 'Поширено для тебе',
      loading: 'Завантаження рішення …',
      q_label: 'Питання',
      opts_label: 'Варіанти',
      rec_label: 'Рекомендація',
      conf_label: 'Впевненість',
      conf_high: 'висока', conf_medium: 'середня', conf_low: 'низька',
      why_label: 'Обґрунтування',
      next_label: 'Наступний крок',
      fac_label: 'Чинники',
      alt_label: 'Альтернатива',
      crit_label: 'Критерії',
      imp_positive: 'за', imp_negative: 'проти', imp_neutral: 'нейтрально',
      day: 'Проаналізовано {d}',
      ai_off: 'Розраховано без ШІ',
      st_confirmed_t: 'Підтверджено ThinkLess',
      st_confirmed_p: 'Цей результат надійшов із ThinkLess без змін.',
      st_unconfirmed_t: 'Не підтверджено',
      st_unconfirmed_p: 'Цей результат не має підтвердження від ThinkLess і міг бути змінений.',
      st_invalid_t: 'Посилання пошкоджене або змінене',
      st_invalid_p: 'Підтвердження не відповідає вмісту, тому ми його не показуємо. Попроси надіслати посилання ще раз.',
      st_tooNew_t: 'Потрібна новіша версія ThinkLess',
      st_tooNew_p: 'Це посилання з новішої версії. Відкрий його в застосунку й за потреби онови застосунок.',
      st_broken_t: 'Посилання неповне',
      st_broken_p: 'Посилання обрізане або пошкоджене. Попроси надіслати його ще раз.',
      st_empty_t: 'Тут з’являються поширені рішення',
      st_empty_p: 'Коли хтось надішле тобі рішення з ThinkLess, ти побачиш його тут.',
      st_browser_t: 'Попередній перегляд у цьому браузері неможливий',
      st_browser_p: 'Відкрий посилання в застосунку або в сучасному браузері.',
      note: 'Поширено людиною, яка користується ThinkLess. Питання й варіанти — її власні слова, їх не перевіряли.',
      privacy: 'Вміст міститься лише в посиланні й показується лише у твоєму браузері. Він не передається на наш сервер.',
      btn_open: 'Відкрити в застосунку',
      btn_store: 'ThinkLess в App Store',
      cta_t: 'Ухвалюй рішення чіткіше й сам',
      cta_p: 'ThinkLess AI допомагає дійти до рішення менш ніж за хвилину.',
      footer_brand: 'застосунок від',
      footer_privacy: 'Конфіденційність', footer_terms: 'Умови використання', footer_imprint: 'Вихідні дані',
      lang_label: 'Мова'
    }
  };
  const LOCALE = { de: 'de-DE', en: 'en', pt: 'pt-BR', uk: 'uk' };
  const HTML_LANG = { de: 'de', en: 'en', pt: 'pt-BR', uk: 'uk' };
  const STORE_URL = 'https://apps.apple.com/app/thinkless-ai/id6762284671?ct=share-d';

  function detectLang() {
    try {
      const saved = root.localStorage.getItem('tl_lang');
      if (saved && T[saved]) return saved;
    } catch (_) { /* privater Modus / blockiert */ }
    const list = (root.navigator && (root.navigator.languages || [root.navigator.language])) || [];
    for (const item of list) {
      const l = String(item || '').toLowerCase();
      if (l.startsWith('de')) return 'de';
      if (l.startsWith('pt')) return 'pt';
      if (l.startsWith('uk')) return 'uk';
      if (l.startsWith('en')) return 'en';
    }
    return 'en';
  }

  function isAppleTouchDevice() {
    const nav = root.navigator || {};
    const ua = nav.userAgent || '';
    return /iPhone|iPad|iPod/.test(ua) || (/Macintosh/.test(ua) && (nav.maxTouchPoints || 0) > 1);
  }

  function formatDay(day, lang) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(day || '');
    if (!m) return null;
    const date = new Date(Date.UTC(Number(m[1]), Number(m[2]) - 1, Number(m[3])));
    if (isNaN(date.getTime())) return null;
    try {
      return new Intl.DateTimeFormat(LOCALE[lang], { dateStyle: 'long', timeZone: 'UTC' }).format(date);
    } catch (_) {
      return day;
    }
  }

  // ── Darstellung (nur createElement + textContent) ─────────────────────────
  function el(doc, tag, cls, text) {
    const node = doc.createElement(tag);
    if (cls) node.className = cls;
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
  }

  function section(doc, label, body) {
    const wrap = el(doc, 'section', 'd-block');
    wrap.appendChild(el(doc, 'h2', 'd-label', label));
    if (body) wrap.appendChild(body);
    return wrap;
  }

  function renderContent(doc, t, lang, c) {
    const frag = doc.createDocumentFragment();

    const q = section(doc, t.q_label, el(doc, 'p', 'd-question', c.question));
    frag.appendChild(q);

    if (c.options.length > 0) {
      const ul = el(doc, 'ul', 'd-options');
      for (const o of c.options) {
        const li = el(doc, 'li', o === c.recommendation ? 'is-rec' : '', o);
        ul.appendChild(li);
      }
      frag.appendChild(section(doc, t.opts_label, ul));
    }

    const rec = el(doc, 'div', 'd-rec');
    rec.appendChild(el(doc, 'p', 'd-rec-text', c.recommendation));
    const meta = el(doc, 'p', 'd-rec-meta');
    meta.textContent = t.conf_label + ': ' + t['conf_' + c.confidence];
    rec.appendChild(meta);
    frag.appendChild(section(doc, t.rec_label, rec));

    if (c.reasoning) frag.appendChild(section(doc, t.why_label, el(doc, 'p', 'd-text', c.reasoning)));
    if (c.factors.length > 0) {
      const ul = el(doc, 'ul', 'd-factors');
      for (const f of c.factors) {
        const li = el(doc, 'li', 'imp-' + f.impact);
        li.appendChild(el(doc, 'span', 'd-factor-label', f.label));
        li.appendChild(el(doc, 'span', 'd-factor-impact', t['imp_' + f.impact]));
        ul.appendChild(li);
      }
      frag.appendChild(section(doc, t.fac_label, ul));
    }
    if (c.criteria.length > 0) {
      const ul = el(doc, 'ul', 'd-criteria');
      let pct;
      try { pct = new Intl.NumberFormat(LOCALE[lang], { style: 'percent', maximumFractionDigits: 0 }); } catch (_) { pct = null; }
      for (const cr of c.criteria) {
        const li = el(doc, 'li');
        li.appendChild(el(doc, 'span', 'd-factor-label', cr.name));
        li.appendChild(el(doc, 'span', 'd-factor-impact', pct ? pct.format(cr.weight) : String(Math.round(cr.weight * 100)) + ' %'));
        ul.appendChild(li);
      }
      frag.appendChild(section(doc, t.crit_label, ul));
    }
    if (c.nextStep) frag.appendChild(section(doc, t.next_label, el(doc, 'p', 'd-text', c.nextStep)));
    if (c.alternativeNote) frag.appendChild(section(doc, t.alt_label, el(doc, 'p', 'd-text', c.alternativeNote)));

    const info = [];
    const day = formatDay(c.day, lang);
    if (day) info.push(t.day.replace('{d}', day));
    if (!c.isAIGenerated) info.push(t.ai_off);
    if (info.length) frag.appendChild(el(doc, 'p', 'd-info', info.join(' · ')));
    return frag;
  }

  function render(doc, state, result, lang) {
    const t = T[lang];
    doc.documentElement.lang = HTML_LANG[lang];
    doc.title = t.page_title;
    doc.querySelectorAll('[data-t]').forEach((node) => {
      const key = node.getAttribute('data-t');
      if (t[key] !== undefined) node.textContent = t[key];
    });
    doc.querySelectorAll('[data-lang]').forEach((btn) => {
      const active = btn.getAttribute('data-lang') === lang;
      btn.classList.toggle('active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    const toggle = doc.getElementById('d-lang');
    if (toggle) toggle.setAttribute('aria-label', t.lang_label);

    const status = doc.getElementById('d-status');
    const body = doc.getElementById('d-body');
    const note = doc.getElementById('d-note');
    const open = doc.getElementById('d-open');
    const store = doc.getElementById('d-store');
    const card = doc.getElementById('d-card');
    if (!status || !body) return;

    store.setAttribute('href', STORE_URL);
    status.className = 'd-status st-' + (state === 'loading' ? 'loading' : state);
    status.replaceChildren();
    body.replaceChildren();

    if (state === 'loading') {
      status.appendChild(el(doc, 'p', 'd-status-title', t.loading));
    } else {
      status.appendChild(el(doc, 'p', 'd-status-title', t['st_' + state + '_t']));
      status.appendChild(el(doc, 'p', 'd-status-text', t['st_' + state + '_p']));
    }

    const showContent = (state === 'confirmed' || state === 'unconfirmed') && result && result.content;
    if (showContent) body.appendChild(renderContent(doc, t, lang, result.content));
    body.hidden = !showContent;
    if (note) note.hidden = !showContent;
    if (card) card.setAttribute('data-state', state);

    const canOpen = (state === 'confirmed' || state === 'unconfirmed' || state === 'tooNew') &&
      result && result.fragment && /^[A-Za-z0-9_.-]+$/.test(result.fragment) && isAppleTouchDevice();
    if (canOpen) {
      open.setAttribute('href', 'thinkless://d#' + result.fragment);
      open.hidden = false;
    } else {
      open.removeAttribute('href');
      open.hidden = true;
    }
  }

  /**
   * Seite einhängen. options.keys nur für Tests (Testschlüssel); im Betrieb
   * gelten ausschließlich PRODUCTION_KEYS.
   */
  function mount(doc, options) {
    const keys = (options && options.keys) || PRODUCTION_KEYS;
    let lang = detectLang();
    let state = 'loading';
    let result = null;
    let run = 0;

    doc.querySelectorAll('[data-lang]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const l = btn.getAttribute('data-lang');
        if (!T[l]) return;
        lang = l;
        try { root.localStorage.setItem('tl_lang', l); } catch (_) { /* egal */ }
        render(doc, state, result, lang);
      });
    });

    async function update() {
      const mine = ++run;
      state = 'loading';
      result = null;
      render(doc, state, result, lang);
      let r;
      try { r = await evaluate(root.location.hash, keys); } catch (_) { r = { state: 'broken' }; }
      if (mine !== run) return;
      state = r.state;
      result = r;
      render(doc, state, result, lang);
    }

    root.addEventListener('hashchange', update);
    return update();
  }

  // ── Export / Start ────────────────────────────────────────────────────────
  if (typeof module === 'object' && module && module.exports) {
    module.exports = core;
  }
  if (typeof document === 'object' && document) {
    root.TLShare = Object.freeze({ core, mount });
    const start = () => {
      if (document.documentElement.hasAttribute('data-tl-manual')) return;
      if (document.getElementById('d-card')) mount(document);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
  }
})(typeof window === 'object' ? window : globalThis);
