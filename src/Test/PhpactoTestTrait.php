<?php

namespace Bigfoot\PHPacto\Test;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\Guzzle;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Serializer\Serializer;

trait PhpactoTestTrait
{
    protected function createServerMock(): Guzzle\ServerMock
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        if (version_compare($guzzleVersion, 6, '<')) {
            return new Guzzle\ServerMock5();
        }

        return new Guzzle\ServerMock6();
    }

    protected function loadPact($path): PactInterface
    {
        return $this->getLoader()->loadFromFile($path);
    }

    private function getLoader(): FileLoader
    {
        return new FileLoader(SerializerFactory::getInstance());
    }
}
