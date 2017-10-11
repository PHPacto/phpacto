<?php

namespace Bigfoot\PHPacto\Loader;

use Bigfoot\PHPacto\Factory\SerializerFactory;
use Bigfoot\PHPacto\PactInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class FileLoaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fs;

    /**
     * @var Serializer
     */
    private $serializer;

    public function setUp()
    {
        $this->serializer = SerializerFactory::getInstance();

        // Define my virtual file system
        $directory = [
            'empty.json' => '',
            'contracts' => [
                'pact.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 'GET',
                        'uri' => '/',
                        'headers' => [],
                        'body' => null,
                    ],
                    'response' => [
                        'status_code' => 200,
                        'headers' => [],
                        'body' => null,
                    ],
                ])
            ]
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);
    }

    public function test_it_throws_exception_if_file_not_found()
    {
        $loader = new FileLoader($this->serializer);

        self::expectExceptionMessage('not exist');

        $loader->loadFromFile($this->fs->url() . '/not-exist.json');
    }

    public function test_it_throws_exception_if_is_not_a_valid_contract()
    {
        $loader = new FileLoader($this->serializer);

        self::expectExceptionMessage('do not contains a valid pact');

        $loader->loadFromFile($this->fs->url() . '/empty.json');
    }

    public function test_it_reads_file_and_returns_a_pact()
    {
        $loader = new FileLoader($this->serializer);

        $pact = $loader->loadFromFile($this->fs->url() . '/contracts/pact.json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }

    public function test_it_returns_pact_array_from_directory()
    {
        $loader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['loadFromDirectory'])
            ->getMock();

        $loader
            ->expects(self::once())
            ->method('loadFromFile')
            ->willReturn($this->createMock(PactInterface::class));

        $pacts = $loader->loadFromDirectory($this->fs->url() . '/contracts');

        self::assertCount(1, $pacts);
        self::assertInstanceOf(PactInterface::class, current($pacts));
    }
}
