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

use PHPacto\Loader\PactLoader;
use PHPacto\Matcher\Mismatches\Mismatch;
use PHPacto\Matcher\Mismatches\MismatchCollection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class ValidateContract extends BaseCommand
{
    /**
     * @var PactLoader
     */
    protected $loader;

    public function __construct(Serializer $serializer, string $defaultContractsDir = null)
    {
        parent::__construct($serializer, $defaultContractsDir);

        $this->loader = new PactLoader($serializer);
    }

    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Check that all contracts rules are still matching samples')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir)
            ->addOption('only-failing', 'f', InputOption::VALUE_NONE, 'Show failing only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $onlyFailing = $input->getOption('only-failing');
        $errors = 0;

        if (is_file($path) && is_readable($path)) {
            $errors += (int) !$this->isPactValid($output, $path, $this->defaultContractsDir, $onlyFailing);
        } elseif (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', PactLoader::CONFIG_EXTS)));

            if (0 === $finder->count()) {
                throw new \Exception(sprintf('No files found in `%s`', $path));
            }

            foreach ($finder->files() as $file) {
                $errors += (int) !$this->isPactValid($output, (string) $file, $path, $onlyFailing);
            }
        } else {
            throw new \Exception(sprintf('Path "%s" must be a readable file or directory', $path));
        }

        if (!$onlyFailing || 0 !== $errors) {
            self::getTable($output)->render();
        }

        if (!$errors) {
            $output->writeln('<fg=green>✔️ All your contracts are correct!</>');
        } elseif (is_dir($path)) {
            $output->writeln(sprintf('<fg=red>✖ You have %d invalid contracts!</>', $errors));
        }

        return $errors;
    }

    protected function isPactValid(OutputInterface $output, string $filePath, string $rootDir = null, bool $onlyFailing = false): bool
    {
        $shortPath = self::getShortPath($filePath, $rootDir);

        try {
            $pact = $this->loader->loadFromFile($filePath);

            if (!$onlyFailing) {
                self::outputResult($output, $shortPath, '<fg=green>✔ Valid</>');
            }

            return true;
        } catch (\Throwable $e) {
            if ($e instanceof Mismatch) {
                self::outputResult($output, $shortPath, '<fg=red>✖ Not valid</>', $e);
            } else {
                self::outputResult($output, $shortPath, '<fg=red>✖ Error</>', $e->getPrevious() ?? $e);
            }
        }

        return false;
    }

    private static function getTable(OutputInterface $output): Table
    {
        static $table;

        if (!$table) {
            $table = new Table($output);
            $table->setStyle('borderless');
            $table->setHeaders([
                'Contract',
                'Status',
                'Error',
            ]);
        }

        return $table;
    }

    private static function getShortPath(string $filePath, string $rootDir = null): string
    {
        if ($rootDir) {
            return str_replace($rootDir . '/', '', $filePath);
        }

        return $filePath;
    }

    private static function outputResult(OutputInterface $output, string $filePath, string $status, \Throwable $error = null): void
    {
        $row = [$filePath, $status];

        if ($error) {
            if ($error instanceof MismatchCollection) {
                $row[] = (string) $error;
            } else {
                $row[] = sprintf('<options=bold>%s</>', $error->getMessage());
            }
        }

        self::getTable($output)->addRow($row);
    }
}
