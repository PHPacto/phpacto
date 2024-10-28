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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto\Command;

use PHPacto\Factory\SerializerFactory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 */
class ValidateContractTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fs;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp(): void
    {
        // Define my virtual file system
        $directory = [
            'contracts' => [
                'not-a-json.json' => 'Not a JSON',

                'malformed.json' => '{[""]}',

                'invalid.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                ]),

                'valid.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 'method',
                        'path' => '/',
                    ],
                    'response' => [
                        'status_code' => 200,
                    ],
                ]),

                'matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => [
                            '_rule' => 'regex',
                            'case_sensitive' => false,
                            'pattern' => '(get|post)',
                            'sample' => 'get',
                        ],
                        'path' => '/',
                    ],
                    'response' => [
                        'status_code' => 202,
                    ],
                ]),

                'not-matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => [
                            '_rule' => 'regex',
                            'case_sensitive' => false,
                            'pattern' => '(get|post)',
                            'sample' => 'put',
                        ],
                        'path' => '/',
                    ],
                    'response' => [
                        'status_code' => 404,
                    ],
                ]),
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 0666, $directory);

        $command = new ValidateContract(SerializerFactory::getInstance());

        $this->tester = new CommandTester($command);
    }

    public function test_it_reads_contracts_and_returns_contracts_states()
    {
        $this->tester->execute([
            'path' => $this->fs->url() . '/contracts',
        ]);

        $output = $this->tester->getDisplay();

        self::assertStringContainsString('not-a-json.json     ✖ Error', $output);
        self::assertStringContainsString('malformed.json      ✖ Error', $output);
        self::assertStringContainsString('invalid.json        ✖ Error', $output);
        self::assertStringContainsString('valid.json          ✔ Valid', $output);
        self::assertStringContainsString('matching.json       ✔ Valid', $output);
        self::assertStringContainsString('not-matching.json   ✖ Not valid', $output);

        self::assertMatchesRegularExpression('/You have \d+ invalid contracts/', $output);
        self::assertNotEquals(0, $this->tester->getStatusCode(), 'Exit code should be different than 0 since there are failed contracts');
    }

    public function test_it_has_correct_exit_code_if_all_contracts_are_valid()
    {
        $path = $this->fs->url() . '/contracts';

        // Remove invalid contracts from test
        unlink($path . '/not-a-json.json');
        unlink($path . '/malformed.json');
        unlink($path . '/invalid.json');
        unlink($path . '/not-matching.json');

        $this->tester->execute([
            'path' => $path,
        ]);

        $output = $this->tester->getDisplay();

        self::assertStringContainsString('valid.json      ✔ Valid', $output);
        self::assertStringContainsString('matching.json   ✔ Valid', $output);

        self::assertEquals(0, $this->tester->getStatusCode(), 'Exit code should be 0 since all contracts are valid');
    }

    public function test_it_shows_only_failing_conctracts()
    {
        $this->tester->execute([
            'path' => $this->fs->url() . '/contracts',
            '--only-failing' => true,
        ]);

        $output = $this->tester->getDisplay();

        self::assertStringNotContainsString('✔ Valid', $output);
        self::assertStringContainsString('✖ Error', $output);
        self::assertStringContainsString('✖ Not valid', $output);

        self::assertNotEquals(0, $this->tester->getStatusCode(), 'Exit code should be different than 0 since there are failed contracts');
    }
}
