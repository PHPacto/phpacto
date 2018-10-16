<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

use Bigfoot\PHPacto\Controller\MockController;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\PactLoader;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

require __DIR__.'/bootstrap.php';

if (false !== $allowOrigin = getenv('ALLOW_ORIGIN')) {
    if ('all' === strtolower($allowOrigin)) {
        $allowOrigin = '*';
    }
} else {
    $allowOrigin = null;
}

$logger = new StdoutLogger();

$logger->log(sprintf(
    '[%s] %s: %s',
    date('Y-m-d H:i:s'),
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
));

$pacts = (new PactLoader(SerializerFactory::getInstance()))
    ->loadFromDirectory(CONTRACTS_DIR);

if (0 === count($pacts)) {
    throw new \Exception(sprintf('No Pacts found in %s', realpath(CONTRACTS_DIR)));
}

$handler = function (RequestInterface $request) use ($logger, $pacts, $allowOrigin) {
    try {
        $controller = new MockController($logger, $pacts, $allowOrigin);

        $response = $controller->action($request);

        $logger->log(sprintf('Pact responded with Status Code %d', $response->getStatusCode()));

        return $response;
    } catch (\Throwable $e) {
        $stream = new Stream('php://memory', 'rw');
        $stream->write($e->getMessage());

        $logger->log($e->getMessage());

        return new Response($stream, 418, ['Content-type' => 'text/plain']);
    }
};

$server = Zend\Diactoros\Server::createServer($handler, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();
