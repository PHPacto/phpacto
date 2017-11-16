<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
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

use Bigfoot\PHPacto\Command\ValidateContract;
use Bigfoot\PHPacto\Matcher\Rules;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ValidateContractTest extends TestCase
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
        // Define my virtual file system
        $directory = [
            'contracts' => [
                'not-a-json.json' => 'Not a JSON',

                'malformed.json' => '[{}]',

                'invalid.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 5,
                        'uri' => '/',
                    ],
                    'response' => [
                        'status_code' => 200,
                    ],
                ]),

                'valid.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 'method',
                        'uri' => '/',
                    ],
                    'response' => [
                        'status_code' => 200,
                    ],
                ]),

                'matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
//                        'method' => [
//                            '@rule' => OrRule::class,
//                            'rules' => [
//                                'get',
//                                'post',
//                                'put',
//                                'patch'
//                            ],
//                            'sample' => 'post'
//                        ],
                        'method' => [
                            '@rule' => Rules\RegexpRule::class,
                            'pattern' => '(get|post)',
                            'sample' => 'get',
                        ],
                        'uri' => '/',
                    ],
                    'response' => [
//                        'status_code' => [
//                            '@rule' => OrRule::class,
//                            'rules' => [
//                                200,
//                                201,
//                            ],
//                            'sample' => 201
//                        ],
                        'status_code' => 202,
                    ],
                ]),

                'not-matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
//                        'method' => [
//                            '@rule' => OrRule::class,
//                            'rules' => [
//                                'get',
//                                'post',
//                                'put',
//                                'patch'
//                            ],
//                            'sample' => 'post'
//                        ],
                        'method' => [
                            '@rule' => Rules\RegexpRule::class,
                            'pattern' => '(get|post)',
                            'sample' => 'put',
                        ],
                        'uri' => '/',
                    ],
                    'response' => [
//                        'status_code' => [
//                            '@rule' => OrRule::class,
//                            'rules' => [
//                                200,
//                                201,
//                            ],
//                            'sample' => 201
//                        ],
                        'status_code' => 404,
                    ],
                ]),
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $command = new ValidateContract(SerializerFactory::getInstance());

        $this->commandTester = new CommandTester($command);
    }

    public function test_it_reads_contracts_and_check_that_contracts_are_still_valid()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url().'/contracts',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('not-a-json.json     ✖ Syntax error', $output);
        $this->assertContains('malformed.json      ✖ Malformed', $output);
        $this->assertContains('invalid.json        ✖ Not valid', $output);
        $this->assertContains('valid.json          ✔ Valid', $output);
        $this->assertContains('matching.json       ✔ Valid', $output);
        $this->assertContains('not-matching.json   ✖ Not valid', $output);
    }
}
