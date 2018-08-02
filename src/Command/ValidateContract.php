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

namespace Bigfoot\PHPacto\Command;

use Bigfoot\PHPacto\Loader\ContractLoader;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class ValidateContract extends BaseCommand
{
    /**
     * @var ContractLoader
     */
    protected $loader;

    public function __construct(Serializer $serializer, string $defaultContractsDir = null)
    {
        parent::__construct($serializer, $defaultContractsDir);

        $this->loader = new ContractLoader($serializer);
    }

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

        if (is_file($path) && is_readable($path)) {
            $this->loadPact($output, $path, $this->defaultContractsDir);
        } elseif (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', ContractLoader::CONFIG_EXTS)));

            if (0 === $finder->count()) {
                throw new \Exception(sprintf('No contract builders found in `%s`', $path));
            }

            foreach ($finder->files() as $file) {
                $this->loadPact($output, (string) $file, $path);
            }
        } else {
            throw new \Exception(sprintf('Path "%s" must be a readable file or directory', $path));
        }

        self::getTable($output)->render();
    }

    protected function loadPact(OutputInterface $output, string $filePath, string $rootDir = null): void
    {
        $shortPath = self::getShortPath($filePath, $rootDir);

        try {
            $this->loader->loadFromFile($filePath);

            self::outputResult($output, $shortPath, '<fg=green>✔ Valid</>', $rootDir);
        } catch (\Throwable $e) {
            if ($e instanceof Mismatch) {
                self::outputResult($output, $shortPath, '<fg=red>✖ Not valid</>', $rootDir);
            } elseif ('Syntax error' === $e->getPrevious()->getMessage()) {
                self::outputResult($output, $shortPath, '<fg=red>✖ Syntax error</>', $rootDir);
            } else {
                self::outputResult($output, $shortPath, '<fg=red>✖ Malformed</>', $rootDir);
            }
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
                'Status',
            ]);
        }

        return $table;
    }

    private static function getShortPath(string $filePath, string $rootDir = null): string
    {
        if ($rootDir) {
            return str_replace($rootDir.'/', '', $filePath);
        }

        return $filePath;
    }

    private static function outputResult(OutputInterface $output, string $filePath, string $status): void
    {
        self::getTable($output)->addRow([$filePath, $status]);
    }
}
