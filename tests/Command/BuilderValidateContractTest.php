<?php

declare(strict_types=1);

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

use Bigfoot\PHPacto\Command\BuilderValidateContract;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactRequest;
use Bigfoot\PHPacto\PactResponse;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BuilderValidateContractTest extends TestCase
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
            'contracts' => [
                'missing.php' => $contractBuilderStr,

                'invalid.php' => $contractBuilderStr,
                'invalid.json' => 'invalid content, not a json',

                'not-matching.php' => $contractBuilderStr,
                'not-matching.json' => json_encode(['Everything different than expected']),

                'matching.php' => $contractBuilderStr,
                'matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 'GET',
                        'path' => '/',
                    ],
                    'response' => [
                        'status_code' => 200,
                    ],
                ]),
            ],
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $command = new BuilderValidateContract(SerializerFactory::getInstance());

        $this->commandTester = new CommandTester($command);
    }

    public function test_it_reads_contract_builder_and_check_that_contracts_are_still_valid()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url().'/contracts',
        ]);

        $output = $this->commandTester->getDisplay();

        self::assertContains('missing.json        ✖ Pact missing', $output);
        self::assertContains('invalid.json        ✖ Invalid', $output);
        self::assertContains('matching.json       ✔ Matching', $output);
        self::assertContains('not-matching.json   ✖ Not matching', $output);
    }
}
