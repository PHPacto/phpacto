<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
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
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

require __DIR__.'/autoload.php';

$logger = new StdoutLogger();

$logger->log(sprintf(
    '[%s] %s: %s',
    date('Y-m-d H:i:s'),
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
));

$pacts = (new FileLoader(SerializerFactory::getInstance()))
    ->loadFromDirectory(CONTRACTS_DIR);

if (0 === count($pacts)) {
    throw new \Exception(sprintf('No Pacts found in %s', realpath(CONTRACTS_DIR)));
}

$requestHandler = function (RequestInterface $request) use ($logger, $pacts) {
    try {
        $controller = new MockController($logger, $pacts);

        return $controller->action($request);
    } catch (\Throwable $e) {
        $stream = new Stream('php://memory', 'rw');

        $response = new Response($stream, 518, ['Content-type' => 'text/plain']);

        $stream->write($e->getMessage());

        return $response;
    }
};

$server = Zend\Diactoros\Server::createServer($requestHandler, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();
