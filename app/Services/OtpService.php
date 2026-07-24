<?php

namespace App\Services;

class OtpService
{
    public function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * HMAC-SHA256 salé par la clé applicative — jamais de code en clair stocké.
     */
    public function hash(string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key'));
    }

    public function verify(string $code, string $hash): bool
    {
        return hash_equals($hash, $this->hash($code));
    }
}