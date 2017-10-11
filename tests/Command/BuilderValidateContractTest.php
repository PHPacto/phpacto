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
                        'uri' => '/',
                    ],
                    'response' => [
                        'status_code' => 200,
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
        $this->assertContains('invalid.json        ✖ Invalid', $output);
        $this->assertContains('matching.json       ✔ Matching', $output);
        $this->assertContains('not-matching.json   ✖ Not matching', $output);
    }
}
