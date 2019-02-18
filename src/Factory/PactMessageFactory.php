<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

use Bigfoot\PHPacto\Encoder\BodyEncoder;
use Bigfoot\PHPacto\Encoder\HeadersEncoder;
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
        $uri = $request->getUri()->getPath();

        if ($request->getUri()->getQuery()) {
            $uri .= $request->getUri()->getQuery();
        }

        return new EqualsRule($uri);
    }

    protected static function getStatusCodeRule(ResponseInterface $response): Rule
    {
        return new EqualsRule($response->getStatusCode());
    }

    protected static function getHeadersRules(MessageInterface $response)
    {
        $decodedHeaders = HeadersEncoder::decode($response->getHeaders());

        return self::getHeaderRulesFromArray($decodedHeaders);
    }

    protected static function getBodyRules(MessageInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $decodedBody = BodyEncoder::decode((string) $response->getBody(), $contentType);

        return !empty($decodedBody) ? new EqualsRule($decodedBody) : null;
    }

    protected static function getHeaderRulesFromArray(array $headers): array
    {
        $map = function($value) {
            if (is_array($value)) {
                return self::getHeaderRulesFromArray($value);
            }

            return new EqualsRule($value);
        };

        return array_map($map, $headers);
    }
}
