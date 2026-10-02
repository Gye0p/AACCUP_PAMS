<?php
// OpenSSL 3.x on Windows requires explicit configuration path
// Try with explicit openssl.cnf path
$phpDir = dirname(PHP_BINARY);
$opensslCnf = $phpDir . '/extras/openssl.cnf';
if (!file_exists($opensslCnf)) {
    // Try common locations
    $locations = [
        'C:/Program Files/OpenSSL-Win64/bin/openssl.cfg',
        'C:/OpenSSL-Win64/bin/openssl.cfg',
        'C:/xampp/apache/conf/openssl.cnf',
        $phpDir . '/openssl.cnf',
    ];
    foreach ($locations as $loc) {
        if (file_exists($loc)) {
            $opensslCnf = $loc;
            break;
        }
    }
}

if (file_exists($opensslCnf)) {
    putenv("OPENSSL_CONF=$opensslCnf");
    echo "Using openssl.cnf: $opensslCnf\n";
} else {
    echo "No openssl.cnf found, trying without...\n";
}

$passphrase = 'aaccup_pams_jwt_2025';
$config = [
    'digest_alg' => 'sha512',
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

if (file_exists($opensslCnf)) {
    $config['config'] = $opensslCnf;
}

$res = openssl_pkey_new($config);
if (!$res) {
    $errs = [];
    while ($e = openssl_error_string()) $errs[] = $e;
    echo "ERROR generating key:\n" . implode("\n", $errs) . "\n";
    exit(1);
}

openssl_pkey_export($res, $privateKey, $passphrase, isset($config['config']) ? ['config' => $config['config']] : []);
$details = openssl_pkey_get_details($res);
$pubKey = $details['key'];

file_put_contents('config/jwt/private.pem', $privateKey);
file_put_contents('config/jwt/public.pem', $pubKey);
echo "JWT keys generated OK!\n";
echo "Private key size: " . strlen($privateKey) . " bytes\n";
echo "Public key size: " . strlen($pubKey) . " bytes\n";
