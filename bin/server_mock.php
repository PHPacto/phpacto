<?php

use Bigfoot\PHPacto\Controller\MockController;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\Logger\StdoutLogger;
use Psr\Http\Message\RequestInterface;

require __DIR__ . '/autoload.php';

$logger = new StdoutLogger();

$logger->log(sprintf(
    "[%s] %s: %s",
    date('Y-m-d H:i:s'),
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
));

$pacts = (new FileLoader(SerializerFactory::getInstance()))
    ->loadFromDirectory(CONTRACTS_DIR);

if (count($pacts) == 0) {
    throw new \Exception(sprintf('No Pacts found in %s', realpath(CONTRACTS_DIR)));
}

$controller = function (RequestInterface $request) use ($logger, $pacts) {
    $controller = new MockController($logger, $pacts);

    return $controller->action($request);
};

$server = Zend\Diactoros\Server::createServer($controller, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

$server->listen();
