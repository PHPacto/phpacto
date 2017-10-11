<?php

namespace Bigfoot\PHPacto\Factory;

use Bigfoot\PHPacto\Command\BuilderWriteContract;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactInterface;
use Bigfoot\PHPacto\PactRequest;
use Bigfoot\PHPacto\PactResponse;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\Serializer;

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
                'pact.php' => $contractBuilderStr
            ]
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
                'path' => $this->fs->url() . '/empty.php',
            ]);
        } catch (\Exception $e) {
            $output = $this->commandTester->getDisplay();
            $this->assertContains('empty.php', $output, 'Command should print what file is reading');

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }

    public function test_it_reads_directory_and_builds_writes_contracts()
    {
        $this->commandTester->execute([
            'path' => $this->fs->url() . '/contracts',
        ]);

        $jsonPath = $this->fs->url() . '/contracts/pact.json';
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
