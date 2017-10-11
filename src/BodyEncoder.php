<?php

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Mismatches\TypeMismatch;

abstract class BodyEncoder
{
    public static function encode($body, string $contentType): string
    {
        $isJson = stripos($contentType, 'application/json') !== false;

        if ($isJson) {
            return json_encode($body);
        } elseif (is_array($body)) {
            return http_build_query($body);
        }

        return (string) $body;
    }

    public static function decode(string $body, ?string $contentType)
    {
        if (false !== strpos($contentType, 'application/json')) {
            return static::decodeJsonEncoded($body);
        } elseif (false !== stripos($contentType, 'application/x-www-form-urlencoded') || false !== strpos($contentType, 'multipart/form-data')) {
            return static::decodeUrlEncoded($body);
        }

        return $body;
    }

    protected static function decodeUrlEncoded(string $body)
    {
        $decoded = [];
        parse_str($body, $decoded);

        return $decoded;
    }

    protected static function decodeJsonEncoded(string $body)
    {
        $decoded = json_decode($body, true);

        if (null === $decoded) {
            throw new TypeMismatch('json', 'string', 'Body content is not a valid JSON');
        }

        return $decoded;
    }
}
