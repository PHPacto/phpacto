<?php

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\BodyEncoder;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class PactMessageFactory
{
    protected static function getMethodRule(RequestInterface $request): Rule
    {
        return new EqualsRule(strtoupper($request->getMethod()));
    }

    protected static function getUriRule(RequestInterface $request): Rule
    {
        $uri = parse_url((string) $request->getUri());

        return new EqualsRule((@$uri['path'] ?: '/') . (@$uri['query'] ? '?'.$uri['query']  : ''));
    }

    protected static function getStatusCodeRule(ResponseInterface $response): Rule
    {
        return new EqualsRule($response->getStatusCode());
    }

    protected static function getHeadersRules(MessageInterface $response)
    {
        return self::getHeaderRulesFromArray(self::filterHeaders($response->getHeaders()));
    }

    protected static function getBodyRules(MessageInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $decodedBody = BodyEncoder::decode((string) $response->getBody(), $contentType);

        return !empty($decodedBody) ? new EqualsRule($decodedBody) : null;
    }

    protected static function filterHeaders(array $headers): array
    {
        $array = [
            'host',
            'date',
            'accept-encoding',
            'connection',
            'content-length',
            'transfer-encoding'
        ];

        return array_filter($headers, function ($key) use ($array) {
            return !in_array(strtolower($key), $array);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected static function getHeaderRulesFromArray(array $headers): array
    {
        $map = function ($value) {
            if (count($value) == 1) {
                $value = $value[0];
            }

            return new EqualsRule($value);
        };

        return array_map($map, $headers);
    }
}
