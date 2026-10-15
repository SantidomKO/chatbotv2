<?php

require_once __DIR__ . '/../model/Authenticator.class.php';

$secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
$validCode = Authenticator::verifyCode($secret, '287082', 59);
if ($validCode !== 1) {
    throw new RuntimeException('RFC 6238 TOTP vector did not verify.');
}

if (Authenticator::verifyCode($secret, '287083', 59) !== false) {
    throw new RuntimeException('An invalid TOTP code was accepted.');
}

if (Authenticator::verifyCode($secret, '12ab', 59) !== false) {
    throw new RuntimeException('A malformed TOTP code was accepted.');
}

$generatedSecret = Authenticator::generateSecret();
if (!preg_match('/^[A-Z2-7]{32}$/', $generatedSecret)) {
    throw new RuntimeException('Generated secret has an invalid Base32 format.');
}

echo "Authenticator TOTP checks passed.\n";