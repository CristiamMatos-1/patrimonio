<?php

namespace Core;

class Security
{
    private const CIPHER_METHOD = 'aes-256-cbc';
    private const INSECURE_FALLBACK = 'fallback_key_do_not_use_in_prod';
    private const PLACEHOLDER_KEY = 'troque_por_uma_chave_segura_de_32_caracteres_no_cpanel!';

    private static function resolveKey(): string
    {
        $config = require BASE_PATH . '/config/app.php';
        $encryptionKey = $config['security']['encryption_key'] ?? $config['encryption_key'] ?? '';
        $encryptionKey = trim((string) $encryptionKey);

        if ($encryptionKey === '' || $encryptionKey === self::INSECURE_FALLBACK || $encryptionKey === self::PLACEHOLDER_KEY) {
            throw new \RuntimeException('ENCRYPTION_KEY não configurada corretamente.');
        }

        return hash('sha256', $encryptionKey, true);
    }

    /**
     * Criptografa um texto (usado para senhas de banco de dados dos tenants)
     */
    public static function encrypt(string $data): string
    {
        $key = self::resolveKey();
        
        $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv = random_bytes($ivLength);
        
        $encrypted = openssl_encrypt($data, self::CIPHER_METHOD, $key, 0, $iv);
        
        // Retorna IV e dado criptografado codificados em base64
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Descriptografa um texto
     */
    public static function decrypt(string $data): string|false
    {
        $key = self::resolveKey();
        
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return false;
        }
        
        if (strpos($decoded, '::') === false) {
            return false;
        }

        list($iv, $encrypted) = explode('::', $decoded, 2);
        
        return openssl_decrypt($encrypted, self::CIPHER_METHOD, $key, 0, $iv);
    }
}
