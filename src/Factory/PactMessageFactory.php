<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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

        return new EqualsRule((@$uri['path'] ?: '/').(@$uri['query'] ? '?'.$uri['query'] : ''));
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
            'transfer-encoding',
        ];

        return array_filter($headers, function ($key) use ($array) {
            return !in_array(strtolower($key), $array, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected static function getHeaderRulesFromArray(array $headers): array
    {
        $map = function ($value) {
            if (1 === count($value)) {
                $value = $value[0];
            }

            return new EqualsRule($value);
        };

        return array_map($map, $headers);
    }
}
