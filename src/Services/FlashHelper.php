<?php

namespace App\Services;

// This class provides static methods to manage flash messages in the session
// Flash messages are temporary messages that are stored in the session and displayed to the user on the next page load.
class FlashHelper
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function get(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']); // clear after reading
        return $messages;
    }
}
