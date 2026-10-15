<?php

require_once __DIR__ . '/Database.class.php';

class Authenticator
{
    private const PERIOD = 30;
    private const DIGITS = 6;
    private const ISSUER = 'Chatbot';
    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::getInstance()->getConnection();
    }

    public static function generateSecret()
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function verifyCode($secret, $code, $timestamp = null)
    {
        $code = preg_replace('/\s+/', '', (string) $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $key = self::base32Decode($secret);
        if ($key === false || $key === '') {
            return false;
        }

        $currentCounter = intdiv($timestamp ?? time(), self::PERIOD);
        for ($offset = -1; $offset <= 1; $offset++) {
            $counter = $currentCounter + $offset;
            if ($counter < 0) {
                continue;
            }
            if (hash_equals(self::codeForCounter($key, $counter), $code)) {
                return $counter;
            }
        }

        return false;
    }

    public static function provisioningUri($email, $secret)
    {
        $label = rawurlencode(self::ISSUER . ':' . $email);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => self::ISSUER,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/' . $label . '?' . $query;
    }

    public function isEnabled($userId)
    {
        $stmt = $this->conexion->prepare('SELECT two_factor_enabled FROM usuarios WHERE id_usuario = ?');
        $stmt->execute([$userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function verifyAndConsume($userId, $code)
    {
        $stmt = $this->conexion->prepare(
            'SELECT two_factor_secret, two_factor_last_counter FROM usuarios WHERE id_usuario = ? AND two_factor_enabled = 1'
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return false;
        }

        $secret = $this->decryptSecret($user['two_factor_secret']);
        $counter = self::verifyCode($secret, $code);
        if ($counter === false || ($user['two_factor_last_counter'] !== null && $counter <= (int) $user['two_factor_last_counter'])) {
            return false;
        }

        $update = $this->conexion->prepare(
            'UPDATE usuarios SET two_factor_last_counter = ? WHERE id_usuario = ? AND two_factor_enabled = 1 AND (two_factor_last_counter IS NULL OR two_factor_last_counter < ?)'
        );
        $update->execute([$counter, $userId, $counter]);
        return $update->rowCount() === 1;
    }

    public function enable($userId, $secret, $counter)
    {
        $encryptedSecret = $this->encryptSecret($secret);
        $stmt = $this->conexion->prepare(
            'UPDATE usuarios SET two_factor_secret = ?, two_factor_enabled = 1, two_factor_last_counter = ? WHERE id_usuario = ?'
        );
        return $stmt->execute([$encryptedSecret, $counter, $userId]);
    }

    public function disable($userId)
    {
        $stmt = $this->conexion->prepare(
            'UPDATE usuarios SET two_factor_secret = NULL, two_factor_enabled = 0, two_factor_last_counter = NULL WHERE id_usuario = ?'
        );
        return $stmt->execute([$userId]);
    }

    private function encryptSecret($secret)
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', $this->encryptionKey(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new RuntimeException('No se pudo proteger el secreto TOTP.');
        }

        return 'v1.' . base64_encode($iv . $tag . $ciphertext);
    }

    private function decryptSecret($payload)
    {
        if (!is_string($payload) || strpos($payload, 'v1.') !== 0) {
            throw new RuntimeException('El secreto TOTP guardado no tiene un formato válido.');
        }

        $data = base64_decode(substr($payload, 3), true);
        if ($data === false || strlen($data) < 29) {
            throw new RuntimeException('El secreto TOTP guardado está dañado.');
        }

        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $secret = openssl_decrypt(substr($data, 28), 'aes-256-gcm', $this->encryptionKey(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($secret === false) {
            throw new RuntimeException('No se pudo descifrar el secreto TOTP. Verifica CHATBOT_TOTP_KEY.');
        }

        return $secret;
    }

    private function encryptionKey()
    {
        $encodedKey = getenv('CHATBOT_TOTP_KEY');
        $key = $encodedKey === false ? false : base64_decode($encodedKey, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('CHATBOT_TOTP_KEY debe contener una clave Base64 de 32 bytes.');
        }

        return $key;
    }

    private static function codeForCounter($key, $counter)
    {
        $counterBytes = pack('N2', ($counter >> 32) & 0xffffffff, $counter & 0xffffffff);
        $hash = hash_hmac('sha1', $counterBytes, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode($data)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $buffer = 0;
        $bits = 0;
        $encoded = '';
        for ($index = 0, $length = strlen($data); $index < $length; $index++) {
            $buffer = ($buffer << 8) | ord($data[$index]);
            $bits += 8;
            while ($bits >= 5) {
                $bits -= 5;
                $encoded .= $alphabet[($buffer >> $bits) & 31];
            }
        }
        if ($bits > 0) {
            $encoded .= $alphabet[($buffer << (5 - $bits)) & 31];
        }

        return $encoded;
    }

    private static function base32Decode($encoded)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = strtoupper(str_replace('=', '', preg_replace('/\s+/', '', (string) $encoded)));
        $buffer = 0;
        $bits = 0;
        $decoded = '';
        for ($index = 0, $length = strlen($encoded); $index < $length; $index++) {
            $value = strpos($alphabet, $encoded[$index]);
            if ($value === false) {
                return false;
            }
            $buffer = ($buffer << 5) | $value;
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $decoded .= chr(($buffer >> $bits) & 0xff);
            }
        }

        return $decoded;
    }
}