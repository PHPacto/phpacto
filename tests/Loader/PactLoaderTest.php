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

namespace PHPacto\Loader;

use PHPacto\Factory\SerializerFactory;
use PHPacto\PactInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class PactLoaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fs;

    /**
     * @var Serializer
     */
    private $serializer;

    public function setUp(): void
    {
        $this->serializer = SerializerFactory::getInstance();

        // Define my virtual file system
        $directory = [
            'empty.json' => '',
            'empty-directory' => [],
            'contracts' => [
                'pact.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/',
                        'headers' => [],
                        'body' => null,
                    ],
                    'response' => [
                        'status_code' => 200,
                        'headers' => [],
                        'body' => null,
                    ],
                ]),
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);
    }

    public function test_it_throws_exception_if_file_not_found()
    {
        $loader = new PactLoader($this->serializer);

        $this->expectExceptionMessage('not exist');

        $loader->loadFromFile($this->fs->url() . '/not-exist.json');

        self::fail('An exception should be thrown');
    }

    public function test_it_throws_exception_if_is_not_a_valid_contract()
    {
        $loader = new PactLoader($this->serializer);

        $this->expectExceptionMessage('does not contain a valid pact');

        $loader->loadFromFile($this->fs->url() . '/empty.json');

        self::fail('An exception should be thrown');
    }

    public function test_it_reads_file_and_returns_a_pact()
    {
        $loader = new PactLoader($this->serializer);

        $pact = $loader->loadFromFile($this->fs->url() . '/contracts/pact.json');

        self::assertInstanceOf(PactInterface::class, $pact);
    }

    public function test_it_returns_pact_array_from_directory()
    {
        $loader = $this->getMockBuilder(PactLoader::class)
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

    public function test_it_throws_exception_if_directory_does_not_exists()
    {
        $loader = $this->getMockBuilder(PactLoader::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['loadFromDirectory'])
            ->getMock();

        // This line is for PhpUnit:6 back-compatibility
        $this->expectException(\Exception::class);

        // This method doesn't work on PhpUnit:6 without calling `expectException` before. (Dont'ask my why?!)
        $this->expectExceptionMessageMatches('/^Directory .* does not exist$/');

        $pacts = $loader->loadFromDirectory($this->fs->url() . '/not-a-directory');

        self::fail('An exception should be thrown');
    }

    public function test_it_throws_exception_if_no_pact_was_fount_in_directory()
    {
        $loader = $this->getMockBuilder(PactLoader::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['loadFromDirectory'])
            ->getMock();

        $this->expectExceptionMessage('No contracts found');

        $pacts = $loader->loadFromDirectory($this->fs->url() . '/empty-directory');

        self::fail('An exception should be thrown');
    }

    public function test_load_path()
    {
        $loader = $this->getMockBuilder(PactLoader::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['loadFromPath'])
            ->getMock();

        $loader->expects(self::once())
            ->method('loadFromDirectory')
            ->willReturn([]);

        $pacts = $loader->loadFromPath($this->fs->url() . '/empty-directory');
    }
}
