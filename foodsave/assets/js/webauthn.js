(function (global) {
    'use strict';

    function readErrorMessage(payload, fallback) {
        if (!payload || typeof payload !== 'object') return fallback;
        const base = payload.error || fallback;
        if (payload.details) return base + ' (' + payload.details + ')';
        return base;
    }

    async function parseJsonSafe(response) {
        const raw = await response.text();
        return JSON.parse(raw.replace(/^\uFEFF/, ''));
    }

    function b64uToBuf(b64u) {
        if (!b64u) return new ArrayBuffer(0);
        const b64 = b64u.replace(/-/g, '+').replace(/_/g, '/') +
            '==='.slice((b64u.length + 3) % 4);
        const bin = atob(b64);
        const buf = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
        return buf.buffer;
    }

    function bufToB64u(buf) {
        const bytes = new Uint8Array(buf);
        let bin = '';
        for (let i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
        return btoa(bin)
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');
    }

    function isSupported() {
        return !!(window.PublicKeyCredential && navigator.credentials &&
            typeof navigator.credentials.create === 'function' &&
            typeof navigator.credentials.get === 'function');
    }

    function transformCreationOptions(opts) {
        const out = Object.assign({}, opts);
        out.challenge = b64uToBuf(opts.challenge);
        out.user = Object.assign({}, opts.user, {
            id: b64uToBuf(opts.user.id)
        });
        if (Array.isArray(opts.excludeCredentials)) {
            out.excludeCredentials = opts.excludeCredentials.map(function (c) {
                return Object.assign({}, c, { id: b64uToBuf(c.id) });
            });
        }
        return out;
    }

    function transformRequestOptions(opts) {
        const out = Object.assign({}, opts);
        out.challenge = b64uToBuf(opts.challenge);
        if (Array.isArray(opts.allowCredentials)) {
            out.allowCredentials = opts.allowCredentials.map(function (c) {
                return Object.assign({}, c, { id: b64uToBuf(c.id) });
            });
        }
        return out;
    }

    async function registerPasskey(label) {
        if (!isSupported()) {
            throw new Error('Votre navigateur ne supporte pas WebAuthn.');
        }

        const optsRes = await fetch('index.php?action=webauthnRegisterOptions', {
            method: 'GET',
            credentials: 'same-origin'
        });
        const optsJson = await parseJsonSafe(optsRes);
        if (!optsJson.ok) throw new Error(readErrorMessage(optsJson, 'Erreur de préparation.'));

        const publicKey = transformCreationOptions(optsJson.options);
        const cred = await navigator.credentials.create({ publicKey: publicKey });
        if (!cred) throw new Error('Aucune passkey créée.');

        const transports = (cred.response.getTransports && cred.response.getTransports()) || [];

        const payload = {
            id: cred.id,
            rawId: bufToB64u(cred.rawId),
            type: cred.type,
            response: {
                clientDataJSON: bufToB64u(cred.response.clientDataJSON),
                attestationObject: bufToB64u(cred.response.attestationObject),
                transports: transports
            },
            label: label || null
        };

        const verifyRes = await fetch('index.php?action=webauthnRegisterVerify', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const verifyJson = await parseJsonSafe(verifyRes);
        if (!verifyJson.ok) throw new Error(readErrorMessage(verifyJson, 'Vérification refusée.'));
        return verifyJson;
    }

    async function loginWithPasskey(email) {
        if (!isSupported()) {
            throw new Error('Votre navigateur ne supporte pas WebAuthn.');
        }
        if (!email || !String(email).trim()) {
            throw new Error('Saisissez votre email avant d\'utiliser la connexion par empreinte.');
        }

        const url = 'index.php?action=webauthnLoginOptions' +
            (email ? '&email=' + encodeURIComponent(email) : '');
        const optsRes = await fetch(url, { credentials: 'same-origin' });
        const optsJson = await parseJsonSafe(optsRes);
        if (!optsJson.ok) throw new Error(readErrorMessage(optsJson, 'Erreur de préparation.'));
        if (!optsJson.options ||
            !Array.isArray(optsJson.options.allowCredentials) ||
            optsJson.options.allowCredentials.length === 0) {
            throw new Error('Aucune passkey locale trouvée pour cet email. Connectez-vous avec mot de passe puis créez-en une.');
        }

        const publicKey = transformRequestOptions(optsJson.options);
        // Hint modern browsers to prioritize on-device authenticators.
        const request = { publicKey: publicKey };
        request.hints = ['client-device'];
        const assertion = await navigator.credentials.get(request);
        if (!assertion) throw new Error('Aucune assertion reçue.');

        const payload = {
            id: assertion.id,
            rawId: bufToB64u(assertion.rawId),
            type: assertion.type,
            response: {
                clientDataJSON: bufToB64u(assertion.response.clientDataJSON),
                authenticatorData: bufToB64u(assertion.response.authenticatorData),
                signature: bufToB64u(assertion.response.signature),
                userHandle: assertion.response.userHandle
                    ? bufToB64u(assertion.response.userHandle)
                    : null
            }
        };

        const verifyRes = await fetch('index.php?action=webauthnLoginVerify', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const verifyJson = await parseJsonSafe(verifyRes);
        if (!verifyJson.ok) throw new Error(readErrorMessage(verifyJson, 'Connexion refusée.'));
        return verifyJson;
    }

    async function listPasskeys() {
        const res = await fetch('index.php?action=webauthnPasskeysList', {
            credentials: 'same-origin'
        });
        const data = await parseJsonSafe(res);
        if (!data.ok) throw new Error(readErrorMessage(data, 'Erreur de chargement.'));
        return data.passkeys;
    }

    async function deletePasskey(id) {
        const body = new URLSearchParams();
        body.append('id', String(id));
        const res = await fetch('index.php?action=webauthnPasskeyDelete', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        });
        const data = await parseJsonSafe(res);
        if (!data.ok) throw new Error(readErrorMessage(data, 'Suppression refusée.'));
        return true;
    }

    global.FoodSaveWebAuthn = {
        isSupported: isSupported,
        registerPasskey: registerPasskey,
        loginWithPasskey: loginWithPasskey,
        listPasskeys: listPasskeys,
        deletePasskey: deletePasskey
    };
})(window);
