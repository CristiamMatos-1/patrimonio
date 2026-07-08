<?php

namespace Core;

class Security
{
    private const CIPHER_METHOD = 'aes-256-cbc';

    /**
     * Criptografa um texto (usado para senhas de banco de dados dos tenants)
     */
    public static function encrypt(string $data): string
    {
        $config = require BASE_PATH . '/config/app.php';
        $encryptionKey = $config['security']['encryption_key'] ?? $config['encryption_key'] ?? 'fallback_key_do_not_use_in_prod';
        $key = hash('sha256', $encryptionKey, true);
        
        $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt($data, self::CIPHER_METHOD, $key, 0, $iv);
        
        // Retorna IV e dado criptografado codificados em base64
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Descriptografa um texto
     */
    public static function decrypt(string $data): string|false
    {
        $config = require BASE_PATH . '/config/app.php';
        $encryptionKey = $config['security']['encryption_key'] ?? $config['encryption_key'] ?? 'fallback_key_do_not_use_in_prod';
        $key = hash('sha256', $encryptionKey, true);
        
        $decoded = base64_decode($data);
        
        if (strpos($decoded, '::') === false) {
            return false;
        }

        list($iv, $encrypted) = explode('::', $decoded, 2);
        
        return openssl_decrypt($encrypted, self::CIPHER_METHOD, $key, 0, $iv);
    }
}
