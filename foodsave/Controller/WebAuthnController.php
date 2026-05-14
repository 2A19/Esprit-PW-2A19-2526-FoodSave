<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Model/UserPasskey.php';

/**
 * Self-contained WebAuthn / passkey controller (no external dependencies).
 *
 * Supports:
 *   - Registration with attestation "none" (Windows Hello / platform authenticators)
 *   - Login (assertion) with ES256 (alg -7) and RS256 (alg -257)
 *   - Challenge / origin / rpId / signCount validation
 *
 * Returns JSON for every endpoint.
 */
class WebAuthnController {

    private string $rpId;
    private string $rpName = 'FoodSave';
    private string $origin;

    public function __construct() {
        if (ob_get_level() === 0) {
            ob_start();
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->rpId = preg_replace('/:\d+$/', '', $host);
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $this->origin = $proto . '://' . $host;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // -----------------------------------------------------------------
    //  ROUTES
    // -----------------------------------------------------------------

    /** GET — produce options for navigator.credentials.create(). User must be authenticated. */
    public function optionsRegister(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->fail('Vous devez être connecté pour activer une passkey.', 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $user = UserPasskey::findUserById($userId);
        if (!$user) {
            $this->fail('Utilisateur introuvable.', 404);
        }

        $challenge = random_bytes(32);
        $userHandle = pack('J', $userId); // 8-byte big-endian user handle

        $existing = UserPasskey::findByUserId($userId);
        $excludeCreds = array_map(function ($p) {
            return [
                'type' => 'public-key',
                'id'   => $p['credential_id'],
                'transports' => $p['transports']
                    ? explode(',', $p['transports'])
                    : ['internal'],
            ];
        }, $existing);

        $_SESSION['webauthn_register'] = [
            'challenge'   => self::b64uEncode($challenge),
            'user_id'     => $userId,
            'user_handle' => self::b64uEncode($userHandle),
            'expires_at'  => time() + 300,
        ];

        $this->jsonOut([
            'ok' => true,
            'options' => [
                'rp' => [
                    'id'   => $this->rpId,
                    'name' => $this->rpName,
                ],
                'user' => [
                    'id'          => self::b64uEncode($userHandle),
                    'name'        => $user['email'],
                    'displayName' => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
                ],
                'challenge'        => self::b64uEncode($challenge),
                'pubKeyCredParams' => [
                    ['type' => 'public-key', 'alg' => -7],   // ES256
                    ['type' => 'public-key', 'alg' => -257], // RS256
                ],
                'timeout'             => 60000,
                'attestation'         => 'none',
                'excludeCredentials'  => $excludeCreds,
                'authenticatorSelection' => [
                    'authenticatorAttachment' => 'platform',
                    'residentKey'             => 'preferred',
                    'requireResidentKey'      => false,
                    'userVerification'        => 'required',
                ],
            ],
        ]);
    }

    /** POST — verify the navigator.credentials.create() response. */
    public function verifyRegister(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->fail('Non authentifié.', 401);
        }
        if (empty($_SESSION['webauthn_register'])) {
            $this->fail('Aucune session d\'enregistrement.', 400);
        }

        $reg = $_SESSION['webauthn_register'];
        if (time() > ($reg['expires_at'] ?? 0)) {
            unset($_SESSION['webauthn_register']);
            $this->fail('Session expirée.', 400);
        }
        if ((int) $reg['user_id'] !== (int) $_SESSION['user_id']) {
            $this->fail('Incohérence de session.', 400);
        }

        $body = $this->readJsonBody();

        $rawId        = $body['rawId'] ?? null;
        $clientB64    = $body['response']['clientDataJSON'] ?? null;
        $attestB64    = $body['response']['attestationObject'] ?? null;
        $transports   = $body['response']['transports'] ?? null;
        $label        = isset($body['label']) ? substr((string) $body['label'], 0, 150) : null;

        if (!$rawId || !$clientB64 || !$attestB64) {
            $this->fail('Données manquantes.');
        }

        $clientJson = self::b64uDecode($clientB64);
        $clientData = json_decode($clientJson, true);
        if (!is_array($clientData)) {
            $this->fail('clientDataJSON invalide.');
        }
        if (($clientData['type'] ?? '') !== 'webauthn.create') {
            $this->fail('Type clientData invalide.');
        }
        if (!hash_equals($reg['challenge'], (string) ($clientData['challenge'] ?? ''))) {
            $this->fail('Challenge invalide.');
        }
        if (($clientData['origin'] ?? '') !== $this->origin) {
            $this->fail('Origin invalide.');
        }

        $attestationObject = self::b64uDecode($attestB64);
        $offset = 0;
        try {
            $att = self::cborDecode($attestationObject, $offset);
        } catch (Throwable $e) {
            $this->fail('attestationObject illisible.');
        }
        if (!is_array($att) || empty($att['authData'])) {
            $this->fail('attestationObject invalide.');
        }

        $authData = $att['authData'];
        $parsed = self::parseAuthData($authData, true);

        if ($parsed['rpIdHash'] !== hash('sha256', $this->rpId, true)) {
            $this->fail('rpIdHash invalide.');
        }
        if (!$parsed['userPresent']) {
            $this->fail('User Presence absent.');
        }
        if (!$parsed['userVerified']) {
            $this->fail('User Verification absent.');
        }
        if (empty($parsed['credentialId']) || empty($parsed['cosePublicKey'])) {
            $this->fail('Credential manquant dans authData.');
        }

        $pem = self::coseToPem($parsed['cosePublicKey']);
        if (!$pem) {
            $this->fail('Algorithme de clé non supporté.');
        }

        $credentialIdB64 = self::b64uEncode($parsed['credentialId']);
        if (!hash_equals($credentialIdB64, (string) $rawId)) {
            $this->fail('rawId incohérent avec authData.');
        }

        $transportsStr = (is_array($transports) && $transports)
            ? implode(',', array_map('strval', $transports))
            : null;

        $existing = UserPasskey::findByCredentialId($credentialIdB64);
        if ($existing) {
            $this->fail('Cette passkey est déjà enregistrée.', 409);
        }

        $created = UserPasskey::create(
            (int) $_SESSION['user_id'],
            $credentialIdB64,
            $pem,
            (int) $parsed['signCount'],
            $transportsStr,
            $label
        );

        if (!$created) {
            $this->fail('Échec de l\'enregistrement de la passkey.', 500);
        }

        unset($_SESSION['webauthn_register']);
        $this->jsonOut(['ok' => true]);
    }

    /** GET — produce options for navigator.credentials.get(). Public endpoint. */
    public function optionsLogin(): void {
        $challenge = random_bytes(32);

        $_SESSION['webauthn_login'] = [
            'challenge'  => self::b64uEncode($challenge),
            'expires_at' => time() + 300,
        ];

        $allowCreds = [];
        $email = isset($_GET['email']) ? trim((string) $_GET['email']) : '';
        if ($email !== '') {
            $user = UserPasskey::findUserByEmail($email);
            if ($user) {
                $passkeys = UserPasskey::findByUserId((int) $user['id']);
                foreach ($passkeys as $pk) {
                    $allowCreds[] = [
                        'type' => 'public-key',
                        'id'   => $pk['credential_id'],
                        // Restrict login prompts to device-bound authenticators.
                        'transports' => ['internal'],
                    ];
                }
            }
        }

        $this->jsonOut([
            'ok' => true,
            'options' => [
                'rpId'             => $this->rpId,
                'challenge'        => self::b64uEncode($challenge),
                'timeout'          => 60000,
                'userVerification' => 'required',
                'allowCredentials' => $allowCreds,
            ],
        ]);
    }

    /** POST — verify navigator.credentials.get() response and start a session. */
    public function verifyLogin(): void {
        if (empty($_SESSION['webauthn_login'])) {
            $this->fail('Aucune session de connexion.', 400);
        }
        $loginSess = $_SESSION['webauthn_login'];
        if (time() > ($loginSess['expires_at'] ?? 0)) {
            unset($_SESSION['webauthn_login']);
            $this->fail('Session expirée.', 400);
        }

        $body = $this->readJsonBody();

        $rawId      = $body['rawId'] ?? null;
        $clientB64  = $body['response']['clientDataJSON'] ?? null;
        $authB64    = $body['response']['authenticatorData'] ?? null;
        $sigB64     = $body['response']['signature'] ?? null;

        if (!$rawId || !$clientB64 || !$authB64 || !$sigB64) {
            $this->fail('Données manquantes.');
        }

        $passkey = UserPasskey::findByCredentialId((string) $rawId);
        if (!$passkey) {
            $this->fail('Identifiant de passkey inconnu.', 404);
        }

        $clientJson = self::b64uDecode($clientB64);
        $clientData = json_decode($clientJson, true);
        if (!is_array($clientData)) {
            $this->fail('clientDataJSON invalide.');
        }
        if (($clientData['type'] ?? '') !== 'webauthn.get') {
            $this->fail('Type clientData invalide.');
        }
        if (!hash_equals($loginSess['challenge'], (string) ($clientData['challenge'] ?? ''))) {
            $this->fail('Challenge invalide.');
        }
        if (($clientData['origin'] ?? '') !== $this->origin) {
            $this->fail('Origin invalide.');
        }

        $authData = self::b64uDecode($authB64);
        $parsed = self::parseAuthData($authData, false);

        if ($parsed['rpIdHash'] !== hash('sha256', $this->rpId, true)) {
            $this->fail('rpIdHash invalide.');
        }
        if (!$parsed['userPresent']) {
            $this->fail('User Presence absent.');
        }
        if (!$parsed['userVerified']) {
            $this->fail('User Verification absent.');
        }

        $signedData = $authData . hash('sha256', $clientJson, true);
        $signature  = self::b64uDecode($sigB64);

        $publicKey = openssl_pkey_get_public($passkey['public_key']);
        if (!$publicKey) {
            $this->fail('Clé publique invalide.', 500);
        }

        $verified = openssl_verify($signedData, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            $this->fail('Signature invalide.', 401);
        }

        $signCount       = (int) $parsed['signCount'];
        $storedSignCount = (int) $passkey['sign_count'];
        if (($signCount > 0 || $storedSignCount > 0) && $signCount <= $storedSignCount) {
            $this->fail('Compteur de signature invalide (authenticator possiblement cloné).', 401);
        }
        UserPasskey::updateSignCount((int) $passkey['id'], $signCount);

        $user = UserPasskey::findUserById((int) $passkey['user_id']);
        if (!$user) {
            $this->fail('Utilisateur introuvable.', 404);
        }
        if (($user['statut'] ?? '') !== 'actif') {
            $this->fail('Votre compte n\'est pas actif.', 403);
        }

        $_SESSION['user_id']      = $user['id'];
        $_SESSION['user_prenom']  = $user['prenom'];
        $_SESSION['user_nom']     = $user['nom'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_role']    = $user['role'];
        unset($_SESSION['webauthn_login']);

        $redirect = ($user['role'] === 'admin')
            ? 'admin.php?action=dashboard'
            : 'index.php?action=dashboard';

        $this->jsonOut(['ok' => true, 'redirect' => $redirect]);
    }

    /** GET — list current user's passkeys. */
    public function listMyPasskeys(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->fail('Non authentifié.', 401);
        }
        $list = UserPasskey::findByUserId((int) $_SESSION['user_id']);
        $items = array_map(function ($p) {
            return [
                'id'           => (int) $p['id'],
                'label'        => $p['label'],
                'created_at'   => $p['created_at'],
                'last_used_at' => $p['last_used_at'],
            ];
        }, $list);
        $this->jsonOut(['ok' => true, 'passkeys' => $items]);
    }

    /** POST — delete a passkey owned by the current user. */
    public function deletePasskey(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->fail('Non authentifié.', 401);
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->fail('Identifiant manquant.');
        }
        UserPasskey::deleteByIdForUser($id, (int) $_SESSION['user_id']);
        $this->jsonOut(['ok' => true]);
    }

    // -----------------------------------------------------------------
    //  HELPERS — IO
    // -----------------------------------------------------------------

    private function readJsonBody(): array {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        return is_array($body) ? $body : [];
    }

    private function jsonOut($data, int $code = 200): void {
        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    private function fail(string $msg, int $code = 400): void {
        $this->jsonOut(['ok' => false, 'error' => $msg], $code);
    }

    // -----------------------------------------------------------------
    //  HELPERS — base64url
    // -----------------------------------------------------------------

    private static function b64uEncode(string $bin): string {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function b64uDecode(string $s): string {
        $s = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad > 0) {
            $s .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($s, true);
        return $decoded === false ? '' : $decoded;
    }

    // -----------------------------------------------------------------
    //  HELPERS — authenticatorData parsing
    // -----------------------------------------------------------------

    /**
     * Parse the binary authenticatorData / authData blob.
     * If $expectAttested is true, also extract the credentialId + COSE public key.
     */
    private static function parseAuthData(string $authData, bool $expectAttested): array {
        if (strlen($authData) < 37) {
            throw new RuntimeException('authData trop court.');
        }
        $rpIdHash  = substr($authData, 0, 32);
        $flags     = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        $result = [
            'rpIdHash'        => $rpIdHash,
            'flags'           => $flags,
            'userPresent'     => (bool) ($flags & 0x01),
            'userVerified'    => (bool) ($flags & 0x04),
            'attestedCredentialDataIncluded' => (bool) ($flags & 0x40),
            'extensionDataIncluded' => (bool) ($flags & 0x80),
            'signCount'       => $signCount,
            'credentialId'    => null,
            'cosePublicKey'   => null,
        ];

        if (!$expectAttested) {
            return $result;
        }

        if (!$result['attestedCredentialDataIncluded']) {
            throw new RuntimeException('Attested Credential Data manquant.');
        }
        if (strlen($authData) < 37 + 18) {
            throw new RuntimeException('authData incomplet.');
        }

        $pos = 37;
        $aaguid = substr($authData, $pos, 16);
        $pos += 16;
        $credIdLen = unpack('n', substr($authData, $pos, 2))[1];
        $pos += 2;
        if (strlen($authData) < $pos + $credIdLen) {
            throw new RuntimeException('credentialId tronqué.');
        }
        $credentialId = substr($authData, $pos, $credIdLen);
        $pos += $credIdLen;

        $coseBytes = substr($authData, $pos);
        $coseOffset = 0;
        $cose = self::cborDecode($coseBytes, $coseOffset);

        $result['credentialId']  = $credentialId;
        $result['cosePublicKey'] = $cose;
        return $result;
    }

    // -----------------------------------------------------------------
    //  HELPERS — minimal CBOR decoder (RFC 8949 subset)
    // -----------------------------------------------------------------

    private static function cborDecode(string $data, int &$offset) {
        if ($offset >= strlen($data)) {
            throw new RuntimeException('CBOR: fin inattendue.');
        }
        $byte = ord($data[$offset++]);
        $major = $byte >> 5;
        $info  = $byte & 0x1f;
        $value = self::cborReadLength($data, $offset, $info);

        switch ($major) {
            case 0:
                return $value;
            case 1:
                return -1 - $value;
            case 2:
            case 3:
                $bytes = substr($data, $offset, $value);
                $offset += $value;
                return $bytes;
            case 4:
                $arr = [];
                for ($i = 0; $i < $value; $i++) {
                    $arr[] = self::cborDecode($data, $offset);
                }
                return $arr;
            case 5:
                $map = [];
                for ($i = 0; $i < $value; $i++) {
                    $k = self::cborDecode($data, $offset);
                    $v = self::cborDecode($data, $offset);
                    $map[$k] = $v;
                }
                return $map;
            case 6:
                return self::cborDecode($data, $offset);
            case 7:
                if ($info === 20) return false;
                if ($info === 21) return true;
                if ($info === 22) return null;
                throw new RuntimeException('CBOR: flottant non supporté.');
        }
        throw new RuntimeException('CBOR: type majeur inconnu.');
    }

    private static function cborReadLength(string $data, int &$offset, int $info): int {
        if ($info < 24) {
            return $info;
        }
        if ($info === 24) {
            $v = ord($data[$offset]);
            $offset += 1;
            return $v;
        }
        if ($info === 25) {
            $v = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
            return $v;
        }
        if ($info === 26) {
            $v = unpack('N', substr($data, $offset, 4))[1];
            $offset += 4;
            return $v;
        }
        if ($info === 27) {
            $hi = unpack('N', substr($data, $offset, 4))[1];
            $lo = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
            return ($hi << 32) | $lo;
        }
        throw new RuntimeException('CBOR: longueur indéfinie non supportée.');
    }

    // -----------------------------------------------------------------
    //  HELPERS — COSE public key -> PEM (ES256 / RS256)
    // -----------------------------------------------------------------

    private static function coseToPem(array $cose): ?string {
        $kty = $cose[1] ?? null;
        $alg = $cose[3] ?? null;

        // ES256 — kty=2 (EC2), alg=-7, crv=1 (P-256)
        if ($kty === 2 && $alg === -7) {
            $crv = $cose[-1] ?? null;
            if ($crv !== 1) return null;
            $x = $cose[-2] ?? '';
            $y = $cose[-3] ?? '';
            if (strlen($x) !== 32 || strlen($y) !== 32) return null;
            return self::ec256ToPem($x, $y);
        }

        // RS256 — kty=3 (RSA), alg=-257
        if ($kty === 3 && $alg === -257) {
            $n = $cose[-1] ?? '';
            $e = $cose[-2] ?? '';
            if ($n === '' || $e === '') return null;
            return self::rsaToPem($n, $e);
        }

        return null;
    }

    private static function ec256ToPem(string $x, string $y): string {
        // SubjectPublicKeyInfo for prime256v1 / secp256r1 / P-256 ecPublicKey
        $prefix = hex2bin('3059301306072a8648ce3d020106082a8648ce3d03010703420004');
        return self::derToPem($prefix . $x . $y, 'PUBLIC KEY');
    }

    private static function rsaToPem(string $n, string $e): string {
        $rsaKey = self::asn1Sequence(self::asn1Int($n) . self::asn1Int($e));
        $bitStr = "\x03" . self::asn1Length(strlen($rsaKey) + 1) . "\x00" . $rsaKey;
        $algId  = hex2bin('300d06092a864886f70d0101010500'); // rsaEncryption + NULL
        $spki   = self::asn1Sequence($algId . $bitStr);
        return self::derToPem($spki, 'PUBLIC KEY');
    }

    private static function derToPem(string $der, string $type): string {
        $b64 = chunk_split(base64_encode($der), 64, "\n");
        return "-----BEGIN $type-----\n" . $b64 . "-----END $type-----\n";
    }

    private static function asn1Int(string $bytes): string {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '') {
            $bytes = "\x00";
        }
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }
        return "\x02" . self::asn1Length(strlen($bytes)) . $bytes;
    }

    private static function asn1Sequence(string $contents): string {
        return "\x30" . self::asn1Length(strlen($contents)) . $contents;
    }

    private static function asn1Length(int $len): string {
        if ($len < 0x80) {
            return chr($len);
        }
        $bytes = '';
        while ($len > 0) {
            $bytes = chr($len & 0xff) . $bytes;
            $len >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
