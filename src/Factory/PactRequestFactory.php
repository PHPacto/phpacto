<?php

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\PactRequest;
use Bigfoot\PHPacto\PactRequestInterface;
use Psr\Http\Message\RequestInterface;

abstract class PactRequestFactory extends PactMessageFactory
{
    public static function createFromPSR7(RequestInterface $request): PactRequestInterface
    {
        $method = self::getMethodRule($request);
        $uri = self::getUriRule($request);
        $headers = self::getHeadersRules($request);
        $body = self::getBodyRules($request);

        return new PactRequest($method, $uri, $headers, $body);
    }
}
