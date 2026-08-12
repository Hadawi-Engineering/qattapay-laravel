<?php

namespace QattaPay\Laravel\Support;

/**
 * Resolves QattaPay API / checkout hosts from mode.
 *
 * `qatta.sa` is tried first; the previous brand's deployment is kept as an
 * automatic fallback during the domain migration.
 */
final class Hosts
{
    /**
     * @var array<string, array{primary: string, fallback: string}>
     */
    private const HOSTS = [
        'dev' => [
            'primary' => 'https://dev.qatta.sa',
            'fallback' => 'https://dev.hadawi.sa',
        ],
        'live' => [
            'primary' => 'https://qatta.sa',
            'fallback' => 'https://beta.hadawi.sa',
        ],
    ];

    /**
     * Ordered API base URLs (`…/api`) for the given mode.
     *
     * @return list<string>
     */
    public static function resolveApiHosts(string $mode): array
    {
        $pair = self::pair($mode);

        return [
            rtrim($pair['primary'], '/').'/api',
            rtrim($pair['fallback'], '/').'/api',
        ];
    }

    /**
     * Primary API base URL for the given mode.
     */
    public static function resolveApiBaseUrl(string $mode): string
    {
        return self::resolveApiHosts($mode)[0];
    }

    /**
     * Ordered checkout / web app origins for the given mode.
     *
     * @return list<string>
     */
    public static function resolveCheckoutHosts(string $mode): array
    {
        $pair = self::pair($mode);

        return [
            rtrim($pair['primary'], '/'),
            rtrim($pair['fallback'], '/'),
        ];
    }

    /**
     * @return array{primary: string, fallback: string}
     */
    private static function pair(string $mode): array
    {
        if (! isset(self::HOSTS[$mode])) {
            throw new \InvalidArgumentException(
                '[QattaPay] Invalid mode "'.$mode.'". Expected "dev" or "live".'
            );
        }

        return self::HOSTS[$mode];
    }
}
