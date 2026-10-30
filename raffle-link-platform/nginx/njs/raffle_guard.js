import crypto from 'crypto';

const TOKEN_PATTERN = /^[A-Za-z0-9_-]{22}$/;
const SIGNATURE_PATTERN = /^[a-f0-9]{32}$/;

function deny(r) {
    r.return(404);
}

function constantTimeEqual(left, right) {
    if (left.length !== right.length) {
        return false;
    }

    let mismatch = 0;
    for (let i = 0; i < left.length; i++) {
        mismatch |= left.charCodeAt(i) ^ right.charCodeAt(i);
    }

    return mismatch === 0;
}

function hasCanonicalRequestTarget(r) {
    // $request_uri is the original, unnormalized URI including query args.
    // Requiring it to equal $uri disallows encoded paths, repeated slashes and query strings.
    const original = (r.variables && r.variables.request_uri) || r.uri;
    return original === r.uri;
}

function rejectNonCanonicalRafflePath(r) {
    // If a single character in the fixed /r/ prefix or separators is changed,
    // the intact token.signature fingerprint still identifies a tampered link.
    if (/[A-Za-z0-9_-]{22}\.[a-f0-9]{32}/.test(r.uri)) {
        return deny(r);
    }
}

function verify(r) {
    if (!hasCanonicalRequestTarget(r)) {
        return deny(r);
    }

    // Only these two endpoints are valid below /r/.
    const match = /^\/r\/([A-Za-z0-9_-]{22})\.([a-f0-9]{32})(?:\/entries)?$/.exec(r.uri);
    if (match === null) {
        return deny(r);
    }

    const token = match[1];
    const supplied = match[2];
    if (!TOKEN_PATTERN.test(token) || !SIGNATURE_PATTERN.test(supplied)) {
        return deny(r);
    }

    // Nginx must be started with RAFFLE_LINK_SECRET in its environment and
    // preserve it via `env RAFFLE_LINK_SECRET;` in the main nginx.conf.
    const secret = process.env.RAFFLE_LINK_SECRET || '';
    if (secret.length < 32) {
        return deny(r); // fail closed; never forward unsigned links to Laravel
    }

    const expected = crypto.createHmac('sha256', secret)
        .update(token)
        .digest('hex')
        .slice(0, 32); // same 128-bit truncation as RaffleLinkSigner.php

    if (!constantTimeEqual(expected, supplied)) {
        return deny(r);
    }
}

export default { verify, rejectNonCanonicalRafflePath };
