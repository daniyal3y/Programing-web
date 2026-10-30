import assert from 'node:assert/strict';
import { createHmac, randomBytes } from 'node:crypto';

process.env.RAFFLE_LINK_SECRET = '0123456789abcdef0123456789abcdef0123456789abcdef';
const { default: guard } = await import('../../nginx/njs/raffle_guard.js');

const token = randomBytes(16).toString('base64url');
const signature = createHmac('sha256', process.env.RAFFLE_LINK_SECRET)
    .update(token)
    .digest('hex')
    .slice(0, 32);

function run(handler, uri) {
    let status;
    handler({ uri, return(code) { status = code; } });
    return status;
}

assert.equal(run(guard.verify, `/r/${token}.${signature}`), undefined, 'valid GET path is allowed');
assert.equal(run(guard.verify, `/r/${token}.${signature}/entries`), undefined, 'valid POST path is allowed');
assert.equal(run(guard.verify, `/r/${token}.${'0'.repeat(32)}`), 404, 'modified signature is denied');
assert.equal(run(guard.verify, `/r/${'A'.repeat(22)}.${signature}`), 404, 'modified token is denied');
assert.equal(run(guard.verify, `/r/${token}.${signature}/entry`), 404, 'modified endpoint suffix is denied');
assert.equal(run(guard.rejectNonCanonicalRafflePath, `/x/${token}.${signature}`), 404, 'modified fixed prefix is denied');
assert.equal(run(guard.rejectNonCanonicalRafflePath, `/r.${token}.${signature}`), 404, 'modified separator is denied');
assert.equal(run(guard.rejectNonCanonicalRafflePath, '/'), undefined, 'ordinary application paths pass through');

console.log('Nginx njs guard smoke tests: 8 assertions passed.');
