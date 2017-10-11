<?php

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\PactResponse;
use Bigfoot\PHPacto\PactResponseInterface;
use Psr\Http\Message\ResponseInterface;

abstract class PactResponseFactory extends PactMessageFactory
{
    public static function createFromPSR7(ResponseInterface $response): PactResponseInterface
    {
        $statusCode = self::getStatusCodeRule($response);
        $headers = self::getHeadersRules($response);
        $body = self::getBodyRules($response);

        return new PactResponse($statusCode, $headers, $body);
    }
}
