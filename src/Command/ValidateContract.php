<?php

namespace Bigfoot\PHPacto\Command;

use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateContract extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Check that all contracts rules are still matching samples')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $loader = new FileLoader($this->serializer);

        if (is_file($path) && is_readable($path)) {
            $pact = $loader->loadFromFile($path);
            $this->validatePact($output, $pact, $path);
        } elseif (is_dir($path)) {
            $pacts = $loader->loadFromDirectory($path);

            foreach ($pacts as $filePath => $pact) {
                $this->validatePact($output, $pact, $filePath);
            }
        } else {
            throw new \Exception('Path "'. $path .'" must be a readable file or directory');
        }

        self::getTable($output)->render();
    }

    protected function validatePact(OutputInterface $output, PactInterface $pact, string $filePath): void
    {
        try {
            $pact->getRequest()->assertMatch($pact->getRequest()->getSample());
            $pact->getResponse()->assertMatch($pact->getResponse()->getSample());

            self::getTable($output)
                ->addRow([$filePath, '<fg=green>✔ Matching</>']);

        } catch (Mismatch $e) {
            self::getTable($output)
                ->addRow([$filePath, '<fg=red>✖ Not matching</>']);
        }
    }

    private static function getTable(OutputInterface $output): Table
    {
        static $table;

        if (!$table) {
            $table = new Table($output);
            $table->setStyle('borderless');
            $table->setHeaders([
                'Contract',
                'Status'
            ]);
        }

        return $table;
    }
}
