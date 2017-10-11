<?php

namespace Bigfoot\PHPacto\Test;

use Bigfoot\PHPacto\Guzzle\ServerMock;
use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\PactInterface;
use PHPUnit\Framework\TestCase;

class PhpactoTestTraitTest extends TestCase
{
    /**
     * @var PhpactoTestTrait
     */
    private $trait;

    /**
     * @var FileLoader
     */
    private $loader;

    protected function setUp()
    {
        $loader = $this->loader = $this->createMock(FileLoader::class);

        $this->trait = new class($loader) {
            use PhpactoTestTrait;

            public function __construct(FileLoader $loader)
            {
                $this->loader = $loader;
            }

            protected function getLoader(): FileLoader
            {
                return $this->loader;
            }
        };
    }

    public function test_it_returns_server_mock()
    {
        $method = new \ReflectionMethod($this->trait, 'createServerMock');
        $method->setAccessible(true);
        $server = $method->invoke($this->trait);

        self::assertInstanceOf(ServerMock::class, $server);
    }

    public function test_it_calls_file_loader()
    {
        $this->loader
            ->expects(self::once())
            ->method('loadFromFile');

        $method = new \ReflectionMethod($this->trait, 'loadPact');
        $method->setAccessible(true);
        $pact = $method->invoke($this->trait, '/file.json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }
}
