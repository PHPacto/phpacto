<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) Damian Długosz
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

use Bigfoot\PHPacto\Controller\ProxyRecorder;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use GuzzleHttp\Client;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/bootstrap.php';

$logger = new StdoutLogger();

if (!mkdir(CONTRACTS_DIR, 0777, true) || is_dir(CONTRACTS_DIR) || is_writable(CONTRACTS_DIR)) {
    throw new \RuntimeException(sprintf('Directory "%s" is not writeable', CONTRACTS_DIR));
}

if (!getenv('RECORDER_PROXY_TO')) {
    throw new \Exception(sprintf('Environment variable "RECORDER_PROXY_TO" is not set.'));
}

$httpClient = new Client();
$controller = new ProxyRecorder($httpClient, $logger, getenv('RECORDER_PROXY_TO'), CONTRACTS_DIR);

$handler = function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($logger, $controller): ResponseInterface {
    $logger->log(sprintf(
        '[%s] %s: %s',
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'] ?? '',
        $_SERVER['REQUEST_URI'] ?? ''
    ));

    $response = $controller->handle($request);

    $logger->log(sprintf('Pact responded with Status Code %d', $response->getStatusCode()));

    return $response;
};

$app = new \Laminas\Stratigility\MiddlewarePipe();
$app->pipe(new \Bigfoot\PHPacto\Controller\CorsMiddleware());
$app->pipe(\Laminas\Stratigility\middleware($handler));

$server = new \Laminas\HttpHandlerRunner\RequestHandlerRunner(
    $app,
    new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter(),
    static function(): ServerRequestInterface {
        return HttpFactory::serverRequestFactory()::fromGlobals();
    },
    static function(\Throwable $t) use ($logger): ResponseInterface {
        $logger->log($t->getMessage());

        function throwableToArray(\Throwable $t): array
        {
            return [
                'message' => $t->getMessage(),
                'trace' => $t->getTrace(),
                'line' => $t->getLine(),
                'file' => $t->getFile(),
                'code' => $t->getCode(),
                'previous' => $t->getPrevious() ? throwableToArray($t->getPrevious()) : null,
            ];
        }

        $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'rw');
        $stream->write(json_encode(throwableToArray($t)));

        return HttpFactory::responseFactory()->createResponse(500)
            ->withAddedHeader('Content-type', 'application/json')
            ->withBody($stream);
    }
);

$server->run();
