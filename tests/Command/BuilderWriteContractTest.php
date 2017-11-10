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

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\Command\BuilderWriteContract;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactRequest;
use Bigfoot\PHPacto\PactResponse;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BuilderWriteContractTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fs;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        $pactClass = Pact::class;
        $pactRequestClass = PactRequest::class;
        $pactResponseClass = PactResponse::class;
        $equalsRuleClass = EqualsRule::class;

        $contractBuilderStr = "<?php return new $pactClass(new $pactRequestClass(new $equalsRuleClass('GET'), new $equalsRuleClass('/')), new $pactResponseClass(new $equalsRuleClass(200)));";

        // Define my virtual file system
        $directory = [
            'empty.php' => '',
            'contracts' => [
                'pact.php' => $contractBuilderStr,
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $command = new BuilderWriteContract(SerializerFactory::getInstance());

        $this->commandTester = new CommandTester($command);
    }

    public function test_it_runs_pact_builder_and_throws_exception_if_no_pact_is_returned()
    {
        try {
            $this->commandTester->execute([
                'path' => $this->fs->url().'/empty.php',
            ]);
        } catch (\Exception $e) {
            $output = $this->commandTester->getDisplay();
            $this->assertContains('empty.php', $output, 'Command should print what file is reading');

            return;
        }

        self::fail('This test should end in the catch');
    }

    public function test_it_reads_directory_and_builds_writes_contracts()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url().'/contracts',
        ]);

        $jsonPath = $this->fs->url().'/contracts/pact.json';
        self::assertFileExists($jsonPath);

        $expected = json_encode([
            'version' => 'dev',
            'description' => '',
            'request' => [
                'method' => 'GET',
                'uri' => '/',
            ],
            'response' => [
                'status_code' => 200,
            ],
        ]);

        self::assertJsonStringEqualsJsonFile($jsonPath, $expected, 'The generated json is not like expected');
    }
}
