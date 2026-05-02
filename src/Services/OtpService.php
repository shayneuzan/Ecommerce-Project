<?php

declare(strict_types=1);

namespace App\Services;

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;

class OtpService
{
    private TwoFactorAuth $tfa;

    public function __construct()
    {
        $this->tfa = new TwoFactorAuth(
            issuer:        'Traventa',
            qrcodeprovider: new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg')
        );
    }

    // Generate a new secret and return it and store in database
    public function createSecret(): string
    {
        return $this->tfa->createSecret();
    }

    // Return QR code data URI so user can scan it
    public function getQrCode(string $email, string $secret): string
    {
        return $this->tfa->getQRCodeImageAsDataUri($email, $secret);
    }

    // Verify code against a secret from the DB
    public function verify(string $secret, string $code): bool
    {
        if (!$secret || !$code) {
            return false;
        }
        return $this->tfa->verifyCode($secret, $code);
    }
}