<?php

namespace Bigfoot\PHPacto\Command;

use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\Pact;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BuilderWriteContract extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('builder:write')
            ->setDescription('Run contract builders and write or update all contracts')
            ->addOption('format', 'f', InputArgument::OPTIONAL, 'The contract\'s file format <fg=cyan>('.implode('|', FileLoader::getSupportedFormats()).')</>', 'json')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');
        $path = $input->getArgument('path');

        if (!FileLoader::isFormatSupported($format)) {
            throw new \Exception('Unsupported file format');
        }

        if (is_file($path) && is_readable($path)) {
            $this->processFile($output, $path, $format);
        } elseif (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name('*.php');

            if ($finder->count() == 0) {
                throw new \Exception('No contract builders found in '. $path);
            }

            foreach ($finder->files() as $i => $file) {
                $this->processFile($output, (string) $file, $format);
            }
        } else {
            throw new \Exception('Path "'. $path .'" must be a readable file or directory');
        }
    }

    protected function processFile(OutputInterface $output, string $path, string $format): void
    {
        $output->writeln(sprintf('Executing <fg=cyan>%s</>', $path));

        $pact = $this->runPactBuilder($path);

        $pactPath = rtrim($path, '.php') . '.' . $format;
        $this->writeContractFile($pactPath, $pact, $format);
    }

    /**
     * @throws \Exception
     */
    protected final function runPactBuilder(string $path): Pact
    {
        $pact = require $path;

        if (!$pact instanceof Pact) {
            throw new \Exception('Must return an instance of ' . Pact::class);
        }

        return $pact;
    }

    protected final function writeContractFile(string $path, PactInterface $pact, string $format): void
    {
        file_put_contents($path, $this->serializer->serialize($pact, $format));
    }
}
