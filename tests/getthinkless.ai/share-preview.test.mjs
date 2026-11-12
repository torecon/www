// ThinkLess v1.6 BA3 — Gegenprobe T4 (ADR 2026-11-11-adr-v1.6-universal-link):
// getthinkless.ai/d/preview.js dekodiert und prüft dieselben Testvektoren wie
// Backend (Python) und App (Swift).
// Fixture: Kopie von GetThinkLess.AI backend/tests/fixtures/share_vectors.json.
// Liegt außerhalb des Domain-Ordners und wird nicht hochgeladen.
//
// Ausführen (Node ≥ 18): node --test tests/getthinkless.ai/share-preview.test.mjs

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { deflateRawSync } from 'node:zlib';

const require = createRequire(import.meta.url);
const core = require('../../getthinkless.ai/d/preview.js');
const fixture = JSON.parse(readFileSync(new URL('./share_vectors.json', import.meta.url), 'utf8'));

const TEST_KEYS = Object.fromEntries(fixture.public_keys.map((k) => [k.kid, k.spki_der_b64url]));
const b64url = (buf) => Buffer.from(buf).toString('base64url');
const frag = (payload, sig) => {
  const d = b64url(deflateRawSync(Buffer.from(payload, 'utf8')));
  return sig ? `1.${d}.${sig}` : `1.${d}`;
};
const base = { v: 1, t: 'decision', kid: 'test-1', d: '2026-11-11', lang: 'de', q: 'Frage?', rec: 'Ja', conf: 'high', ai: true };
const fragOf = (obj) => frag(JSON.stringify(obj));

test('Fixture-Format 1 mit Testschlüssel', () => {
  assert.equal(fixture.format, 1);
  assert.ok(fixture.vectors.length >= 9);
});

for (const v of fixture.vectors) {
  test(`Vektor ${v.name} → ${v.expect}`, async () => {
    const decoded = await core.decodeFragment(v.fragment);
    assert.equal(decoded.error, undefined, `Dekodierfehler ${decoded.error}`);
    assert.equal(Buffer.from(decoded.payload).toString('utf8'), v.payload, 'Payload-Bytes exakt');
    assert.equal(decoded.signature, v.sig ?? null);
    const status = await core.verify(decoded.payload, decoded.signature, decoded.content.kid, TEST_KEYS);
    assert.equal(status, v.expect);

    const page = await core.evaluate('#' + v.fragment, TEST_KEYS);
    if (v.expect === 'invalid') {
      assert.equal(page.state, 'invalid');
      assert.equal(page.content, undefined, 'ungültig → kein Inhalt');
    } else {
      assert.equal(page.state, v.expect);
      assert.equal(page.content.question, JSON.parse(v.payload).q);
    }
  });
}

test('Vollnachweis: Felder why/next/fac/alt/crit', async () => {
  const v = fixture.vectors.find((x) => x.name === 'guided_de_full');
  const { content } = await core.decodeFragment(v.fragment);
  assert.deepEqual(content.options, ['Berlin', 'Lissabon']);
  assert.equal(content.recommendation, 'Lissabon');
  assert.equal(content.confidence, 'medium');
  assert.equal(content.factors.length, 2);
  assert.equal(content.factors[1].impact, 'negative');
  assert.deepEqual(content.criteria, [{ name: 'Kosten', weight: 0.6 }, { name: 'Wetter', weight: 0.4 }]);
  assert.ok(content.reasoning && content.nextStep && content.alternativeNote);
});

test('Produktivschlüssel: Testsignatur gilt dort als „nicht bestätigt“', async () => {
  const v = fixture.vectors.find((x) => x.name === 'fast_en_basic');
  const page = await core.evaluate('#' + v.fragment);
  assert.equal(page.state, 'unconfirmed');
  assert.ok(core.PRODUCTION_KEYS['tl-2026-11']);
  assert.equal(core.PRODUCTION_KEYS['test-1'], undefined);
});

test('Produktivschlüssel ist ein gültiger P-256-SPKI', async () => {
  const spki = core.b64urlDecode(core.PRODUCTION_KEYS['tl-2026-11']);
  const key = await crypto.subtle.importKey('spki', spki, { name: 'ECDSA', namedCurve: 'P-256' }, false, ['verify']);
  assert.equal(key.algorithm.namedCurve, 'P-256');
});

test('Prozent-kodiertes Fragment (Messenger) wird gelesen', async () => {
  const v = fixture.vectors.find((x) => x.name === 'fast_en_basic');
  const page = await core.evaluate('#' + v.fragment.replace(/\./g, '%2E'), TEST_KEYS);
  assert.equal(page.state, 'confirmed');
});

const errorCases = [
  ['leer', '', 'notShareLink'],
  ['Version 2 im Fragment', '2.abc', 'unsupportedVersion'],
  ['Version keine Zahl', 'x.abc', 'malformed'],
  ['vier Teile', '1.a.b.c', 'malformed'],
  ['nur Version', '1', 'malformed'],
  ['kein base64url', '1.@@@@', 'malformed'],
  ['Padding verboten', '1.AAAA==', 'malformed'],
  ['Signatur kein base64url', null, 'malformed', () => fragOf(base) + '.+++'],
  ['kein deflate', '1.' + b64url(Buffer.from('not deflate at all, sorry')), 'malformed'],
  ['Payload kein JSON', null, 'malformed', () => frag('hello')],
  ['Payload ohne v/t', null, 'malformed', () => fragOf({ q: 'x', rec: 'y' })],
  ['Payload v=2', null, 'unsupportedVersion', () => fragOf({ ...base, v: 2 })],
  ['Payload Typ challenge', null, 'unsupportedType', () => fragOf({ ...base, t: 'challenge' })],
  ['q fehlt', null, 'malformed', () => fragOf({ ...base, q: undefined })],
  ['rec leer', null, 'malformed', () => fragOf({ ...base, rec: '   ' })],
  ['q zu lang (501)', null, 'malformed', () => fragOf({ ...base, q: 'a'.repeat(501) })],
  ['6 Optionen', null, 'malformed', () => fragOf({ ...base, opts: ['1', '2', '3', '4', '5', '6'] })],
  ['Option falscher Typ', null, 'malformed', () => fragOf({ ...base, opts: [1] })],
  ['Gewicht > 1', null, 'malformed', () => fragOf({ ...base, crit: [{ n: 'K', w: 1.5 }] })],
  ['UTF-8 kaputt', null, 'malformed', () => '1.' + b64url(deflateRawSync(Buffer.from([0x7b, 0xff, 0xfe, 0x7d])))],
  ['Kompressionsbombe (> 16 KB)', null, 'tooLarge', () =>
    '1.' + b64url(deflateRawSync(Buffer.from(JSON.stringify({ ...base, pad: 'x'.repeat(20000) }))))],
  ['Fragment > 8 KB', '1.' + 'A'.repeat(8200), 'tooLarge']
];

for (const [name, fixed, expected, build] of errorCases) {
  test(`Fehlerfall: ${name} → ${expected}`, async () => {
    const fragment = build ? build() : fixed;
    const r = await core.decodeFragment(fragment);
    assert.equal(r.error, expected);
  });
}

test('Grenze genau 500 Zeichen (Grapheme) ist gültig, auch mit Emoji', async () => {
  const r = await core.decodeFragment(fragOf({ ...base, q: '👍🏽'.repeat(500) }));
  assert.equal(r.error, undefined);
});

test('Unbekannte Felder werden ignoriert, unbekannte Enums fallen sicher zurück', async () => {
  const r = await core.decodeFragment(fragOf({ ...base, zzz: { a: 1 }, conf: 'extreme', fac: [{ l: 'A', i: 'weird' }] }));
  assert.equal(r.error, undefined);
  assert.equal(r.content.confidence, 'low');
  assert.equal(r.content.factors[0].impact, 'neutral');
});

test('Signatur falscher Länge mit bekanntem kid → ungültig', async () => {
  const r = await core.decodeFragment(fragOf(base) + '.' + b64url(Buffer.alloc(10)));
  assert.equal(await core.verify(r.payload, r.signature, r.content.kid, TEST_KEYS), 'invalid');
});

test('Signatur eines anderen Vektors → ungültig', async () => {
  const a = fixture.vectors.find((x) => x.name === 'fast_en_basic');
  const b = fixture.vectors.find((x) => x.name === 'fast_en_full');
  const page = await core.evaluate('#' + a.fragment.split('.').slice(0, 2).join('.') + '.' + b.sig, TEST_KEYS);
  assert.equal(page.state, 'invalid');
});

test('Seitenzustände: leer, defekt, zu neu', async () => {
  assert.equal((await core.evaluate('', TEST_KEYS)).state, 'empty');
  assert.equal((await core.evaluate('#', TEST_KEYS)).state, 'empty');
  assert.equal((await core.evaluate('#1.%E0%A4%A', TEST_KEYS)).state, 'broken');
  assert.equal((await core.evaluate('#1.@@', TEST_KEYS)).state, 'broken');
  const tooNew = await core.evaluate('#2.abc', TEST_KEYS);
  assert.equal(tooNew.state, 'tooNew');
  assert.equal(tooNew.fragment, '2.abc');
});

test('XSS-Payload bleibt Text (Parser liefert Strings unverändert)', async () => {
  const evil = '<img src=x onerror=alert(1)><script>alert(2)</script>';
  const r = await core.decodeFragment(fragOf({ ...base, q: evil }));
  assert.equal(r.content.question, evil);
});
