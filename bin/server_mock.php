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

use Bigfoot\PHPacto\Controller\Mock;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\PactLoader;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/bootstrap.php';

$logger = new StdoutLogger();

$handler = function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($logger): ResponseInterface {
    $logger->log(sprintf(
        '[%s] %s: %s',
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'] ?? '',
        $_SERVER['REQUEST_URI'] ?? ''
    ));

    try {
        $headerContract = $request->getHeaderLine('PHPacto-Contract');

        $pacts = (new PactLoader(SerializerFactory::getInstance()))
            ->loadFromPath($headerContract ? CONTRACTS_DIR . $headerContract : CONTRACTS_DIR);

        if (0 === count($pacts)) {
            throw new \Exception(sprintf('No Pacts found in %s', realpath(CONTRACTS_DIR)));
        }

        $controller = new Mock($logger, $pacts);

        $response = $controller->handle($request);

        $logger->log(sprintf('Pact responded with Status Code %d', $response->getStatusCode()));

        return $response;
    } catch (MismatchCollection $mismatches) {
        $stream = HttpFactory::streamFactory()->createStreamFromFile('php://memory', 'rw');
        $stream->write(json_encode([
            'message' => $mismatches->getMessage(),
            'contracts' => $mismatches->toArray(),
        ]));

        $logger->log($mismatches->getMessage() . "\n");

        return HttpFactory::responseFactory()->createResponse(418)
            ->withAddedHeader('Content-type', 'application/json')
            ->withBody($stream);
    }
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
