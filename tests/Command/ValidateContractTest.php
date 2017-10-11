<?php

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
        $this->markTestIncomplete('Write tests');

        // Define my virtual file system
        $directory = [
            'contracts' => [
                'missing.php' => sprintf(
                    '<?php $matcher = new %s(0); return new %s(new %s($matcher, $matcher), new %s($matcher));',
                    EqualsRule::class,
                    Pact::class,
                    PactRequest::class,
                    PactResponse::class
                ),

                'not-matching.php' => sprintf(
                    '<?php $matcher = new %s(0); return new %s(new %s($matcher, $matcher), new %s($matcher));',
                    EqualsRule::class,
                    Pact::class,
                    PactRequest::class,
                    PactResponse::class
                ),
                'not-matching.json' => json_encode(['Something different than expected']),

                'matching.php' => sprintf(
                    '<?php $matcher = new %s(0); return new %s(new %s($matcher, $matcher), new %s($matcher));',
                    EqualsRule::class,
                    Pact::class,
                    PactRequest::class,
                    PactResponse::class
                ),
                'matching.json' => json_encode([
                    'version' => 'dev',
                    'description' => '',
                    'request' => [
                        'method' => 0,
                        'uri' => 0,
                    ],
                    'response' => [
                        'status_code' => 0,
                    ],
                ]),
            ]
        ];

        // Setup and cache the virtual file system
        $this->fs = vfsStream::setup('root', 444, $directory);

        $command = new BuilderValidateContract(SerializerFactory::getInstance());

        $this->commandTester = new CommandTester($command);
    }

    public function test_it_reads_contract_builder_and_check_that_contracts_are_still_valid()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url() . '/contracts',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertContains('missing.json        ✖ Pact missing', $output);
        $this->assertContains('matching.json       ✔ Matching', $output);
        $this->assertContains('not-matching.json   ✖ Not matching', $output);
    }
}
