<?php

namespace App\Framework;

/**
 * One-shot session flash messages, used to carry success/error feedback
 * across a redirect (e.g. after registration or saving an account).
 */
class Flash
{
    private const KEY = '_flash';

    public static function add(string $type, string $message): void
    {
        $_SESSION[self::KEY][] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('danger', $message);
    }

    /**
     * Queue a list of service result messages, mapping ok => success / fail => error.
     *
     * @param array<int,array{ok:bool,message:string}> $messages
     */
    public static function results(array $messages): void
    {
        foreach ($messages as $m) {
            $m['ok'] ? self::success($m['message']) : self::error($m['message']);
        }
    }

    /**
     * Return and clear all queued messages.
     *
     * @return array<int,array{type:string,message:string}>
     */
    public static function pull(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $messages;
    }
}
