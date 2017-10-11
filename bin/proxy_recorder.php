<?php

use Bigfoot\PHPacto\Logger\StdoutLogger;
use Bigfoot\PHPacto\Controller\ProxyController;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require __DIR__ . '/autoload.php';

$logger = new StdoutLogger();

$logger->log(sprintf(
    "[%s] %s: %s",
    date('Y-m-d H:i:s'),
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
));

if (!is_dir(CONTRACTS_DIR)) {
    mkdir(CONTRACTS_DIR, 0777, true);
}

if (!isset($_ENV['RECORDER_PROXY_TO'])) {
    throw new \Exception(sprintf('Environment variable "RECORDER_PROXY_TO" is not set.'));
}

define('PROXY_TO', parse_url(getenv('RECORDER_PROXY_TO')));

$callback = function (RequestInterface $request) use ($logger): ResponseInterface {
    $uri = $request->getUri()
        ->withScheme(@PROXY_TO['scheme'] ?: 'http')
        ->withHost(@PROXY_TO['host'] ?: 'localhost')
        ->withPort(@PROXY_TO['port'] ?: @PROXY_TO['scheme'] == 'https' ? 443 : 80);

    $httpClient = new Client();

    $controller = new ProxyController($httpClient, $logger, $uri, CONTRACTS_DIR);

    return $controller->action($request);
};

$server = Zend\Diactoros\Server::createServer($callback, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

$server->listen();
