<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\OtpService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use RedBeanPHP\R;
use App\Services\FlashHelper;

class AuthController
{
    public function __construct(
        private Environment $twig,
        private OtpService  $otpService,
        private string      $basePath,
    ) {}

    // ── GET /register ─────────────────────────────────────────────────────────
    public function showRegister(Request $request, Response $response): Response
    {
        $html = $this->twig->render('auth/register.html.twig', [
            'base_path' => $this->basePath,
            'step'      => 'register',
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    // ── POST /register ────────────────────────────────────────────────────────
    public function register(Request $request, Response $response): Response
    {
        $data      = (array) $request->getParsedBody();
        $firstName = trim($data['first_name'] ?? '');
        $lastName  = trim($data['last_name']  ?? '');
        $email     = trim($data['email']      ?? '');
        $password  = trim($data['password']   ?? '');


        // Basic validation
        if (!$firstName || !$lastName || !$email || !$password) {
            FlashHelper::add('danger', 'All fields are required.');
            
            $html = $this->twig->render('auth/register.html.twig', [
                'base_path' => $this->basePath,
                'step'      => 'register',
            ]);
            $response->getBody()->write($html);
            return $response;
        }

        // Check if email already exists
        $existing = R::findOne('user', 'email = ?', [$email]);
        if ($existing) {
            FlashHelper::add('danger', 'An account with this email already exists.');
            $html = $this->twig->render('auth/register.html.twig', [
                'base_path' => $this->basePath,
                'step'      => 'register',
            ]);
            $response->getBody()->write($html);
            return $response;
        }

        // Generate TOTP secret
        $secret = $this->otpService->createSecret();

        // Save user to DB
        $user              = R::dispense('user');
        $user->first_name  = $firstName;
        $user->last_name   = $lastName;
        $user->email       = $email;
        $user->password_hash   = password_hash($password, PASSWORD_BCRYPT);
        $user->totp_secret = $secret;
        $user->role        = 'user';
        R::store($user);

        // Show QR code so user can scan it once
        $qrCode = $this->otpService->getQrCode($email, $secret);

        $html = $this->twig->render('auth/register.html.twig', [
            'base_path' => $this->basePath,
            'step'      => 'qr_setup',
            'qr_code'   => $qrCode,
            'user_id'   => $user->id,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    // ── GET /login ────────────────────────────────────────────────────────────
    public function showLogin(Request $request, Response $response): Response
    {
        $html = $this->twig->render('auth/login.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    // ── POST /login ───────────────────────────────────────────────────────────
    public function login(Request $request, Response $response): Response
    {
        $data     = (array) $request->getParsedBody();
        $email    = trim($data['email']    ?? '');
        $password = trim($data['password'] ?? '');

        $user = R::findOne('user', 'email = ?', [$email]);

        // Verify email + password
        if (!$user || !password_verify($password, $user->password_hash)) {
            FlashHelper::add('danger', 'Invalid email or password.');
            return $response
                ->withHeader('Location', $this->basePath . '/auth/login')
                ->withStatus(302);
        }

        if($user->role === 'admin'){
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = (int)$user->id;
            $_SESSION['user_name'] = $user->first_name;
            $_SESSION['user_role'] = $user->role;

            return $response
                ->withHeader('Location', $this->basePath . '/admin')
                ->withStatus(302);
        }

        // Store user ID in session temporarily — not fully authenticated yet
        $_SESSION['2fa_pending_user_id'] = (int) $user->id;

        // Redirect to 2FA verification
        return $response
            ->withHeader('Location', $this->basePath . '/auth/verify-2fa')
            ->withStatus(302);
    }

    // ── GET /verify-2fa ───────────────────────────────────────────────────────
    public function showVerify(Request $request, Response $response): Response
    {
        if (empty($_SESSION['2fa_pending_user_id'])) {
            return $response
                ->withHeader('Location', $this->basePath . '/auth/login')
                ->withStatus(302);
        }

        $html = $this->twig->render('auth/verify.html.twig', [
            'base_path' => $this->basePath,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    // ── POST /verify-2fa ──────────────────────────────────────────────────────
    public function verify(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $code = trim($data['code'] ?? '');

        $userId = $_SESSION['2fa_pending_user_id'] ?? null;

        if (!$userId) {
            return $response
                ->withHeader('Location', $this->basePath . '/auth/login')
                ->withStatus(302);
        }

        // Fetch secret from DB
        $user = R::load('user', $userId);

        if (!$user->id) {
            return $response
                ->withHeader('Location', $this->basePath . '/auth/login')
                ->withStatus(302);
        }

        // Verify the TOTP code
        if ($this->otpService->verify($user->totp_secret, $code)) {
            // Fully authenticate the user
            unset($_SESSION['2fa_pending_user_id']);
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id']       = (int) $user->id;
            $_SESSION['user_name']     = $user->first_name;
            $_SESSION['user_role']     = $user->role;

            $redirectTo = $user->role === 'admin' ? '/admin' : '/';

            return $response
                ->withHeader('Location', $this->basePath . $redirectTo)
                ->withStatus(302);
        }

        // Wrong code
        FlashHelper::add('danger', 'Invalid code. Please try again.');
        return $response
            ->withHeader('Location', $this->basePath . '/auth/verify-2fa')
            ->withStatus(302);
    }

    // ── POST /logout ──────────────────────────────────────────────────────────
    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $response
            ->withHeader('Location', $this->basePath . '/auth/login')
            ->withStatus(302);
    }
}