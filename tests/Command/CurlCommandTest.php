<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

use Bigfoot\PHPacto\Command\CurlCommand;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CurlCommandTest extends TestCase
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
        $contract = json_encode([
            'version' => 'dev',
            'description' => '',
            'request' => [
                'method' => 'GET',
                'path' => '/',
            ],
            'response' => [
                'status_code' => 200,
            ],
        ]);

        // Define my virtual file system
        $directory = [
            'single.json' => $contract,
            'directory' => [
                'first.json' => $contract,
                'second.json' => $contract,
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $command = new CurlCommand(SerializerFactory::getInstance());

        $this->commandTester = new CommandTester($command);
    }

    public function test_it_generates_curl_command_for_single_file()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url() . '/single.json',
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertContains('curl \'http://localhost/\'', $output);
    }

    public function test_it_generates_curl_command_for_all_files_in_directory()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url() . '/directory',
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertContains('first.json    curl \'http://localhost/\'', $output);
        self::assertContains('second.json   curl \'http://localhost/\'', $output);
    }
}
