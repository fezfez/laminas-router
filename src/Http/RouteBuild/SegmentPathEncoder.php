<?php

declare(strict_types=1);

namespace Laminas\Router\Http\RouteBuild;

use function rawurldecode;
use function rawurlencode;
use function strtr;

/**
 * @internal
 *
 * @psalm-internal \Laminas\Router
 * @psalm-internal \LaminasTest\Router
 */
final class SegmentPathEncoder
{
    /**
     * Cache for the encode output.
     *
     * @var array<string, string>
     */
    private static array $cacheEncode = [];

    /**
     * Map of allowed special chars in path segments.
     *
     * http://tools.ietf.org/html/rfc3986#appendix-A
     * segement      = *pchar
     * pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     * unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     * sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *               / "*" / "+" / "," / ";" / "="
     *
     * @var array<string, string>
     */
    private static array $urlencodeCorrectionMap = [
        '%21' => "!", // sub-delims
        '%24' => "$", // sub-delims
        '%26' => "&", // sub-delims
        '%27' => "'", // sub-delims
        '%28' => "(", // sub-delims
        '%29' => ")", // sub-delims
        '%2A' => "*", // sub-delims
        '%2B' => "+", // sub-delims
        '%2C' => ",", // sub-delims
//      '%2D' => "-", // unreserved - not touched by rawurlencode
//      '%2E' => ".", // unreserved - not touched by rawurlencode
        '%3A' => ":", // pchar
        '%3B' => ";", // sub-delims
        '%3D' => "=", // sub-delims
        '%40' => "@", // pchar
//      '%5F' => "_", // unreserved - not touched by rawurlencode
//      '%7E' => "~", // unreserved - not touched by rawurlencode
    ];

    public static function encode(string $value): string
    {
        if (! isset(self::$cacheEncode[$value])) {
            self::$cacheEncode[$value] = rawurlencode($value);
            self::$cacheEncode[$value] = strtr(self::$cacheEncode[$value], self::$urlencodeCorrectionMap);
        }

        return self::$cacheEncode[$value];
    }

    public static function decode(string $value): string
    {
        return rawurldecode($value);
    }
}
