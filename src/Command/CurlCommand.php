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
use Bigfoot\PHPacto\PactInterface;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class CurlCommand extends BaseCommand
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
            ->setName('curl')
            ->setDescription('Generate cURL commands for contracts')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $curlFormatter = new CurlFormatter(INF);

        if (is_file($path) && is_readable($path)) {
//            try {
                $pact = $this->loadPact((string) $path);
                $this->printCurlCommand($output, $curlFormatter, $pact, $path, false);
//            } catch (\Throwable $e) {
//                if ($e instanceof Mismatch) {
//                    self::outputResult($output, $path, '<fg=red>✖ Not valid</>');
//                } elseif ($e->getPrevious() && 'Syntax error' === $e->getPrevious()->getMessage()) {
//                    self::outputResult($output, $path, '<fg=red>✖ Syntax error</>');
//                }
//            }
        } elseif (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', ContractLoader::CONFIG_EXTS)));

            if (0 === $finder->count()) {
                throw new \Exception(sprintf('No files found in `%s`', $path));
            }

            foreach ($finder->files() as $file) {
                $shortPath = self::getShortPath((string) $file, $path);

                try {
                    $pact = $this->loadPact((string) $file);
                    $this->printCurlCommand($output, $curlFormatter, $pact, $shortPath, true);
                } catch (\Throwable $e) {
                    if ($e instanceof Mismatch) {
                        self::outputResult($output, $shortPath, '<fg=red>✖ Not valid</>');
                    } elseif ('Syntax error' === $e->getPrevious()->getMessage()) {
                        self::outputResult($output, $shortPath, '<fg=red>✖ Syntax error</>');
                    }
                }
            }

            self::getTable($output)->render();
        } else {
            throw new \Exception(sprintf('Path "%s" must be a readable file or directory', $path));
        }
    }

    protected function loadPact(string $filePath): PactInterface
    {
        return $this->loader->loadFromFile($filePath);
    }

    protected function printCurlCommand(OutputInterface $output, CurlFormatter $formatter, PactInterface $pact, string $path, bool $multipleFiles = false): void
    {
        $request = $pact->getRequest()->getSample();

        $uri = $request->getUri()
            ->withScheme('http')
            ->withHost('localhost')
            ->withPort(80);

        $request = $request->withUri($uri);

        $curlCommand = $formatter->format($request);

        self::outputResult($output, $path, $curlCommand, $multipleFiles);
    }

    private static function getTable(OutputInterface $output): Table
    {
        static $table;

        if (!$table) {
            $table = new Table($output);
            $table->setStyle('borderless');
            $table->setHeaders([
                'Contract',
                'cURL command',
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

    private static function outputResult(OutputInterface $output, string $filePath, string $curlCommand, $multipleFiles): void
    {
        if ($multipleFiles) {
            self::getTable($output)->addRow([$filePath, $curlCommand]);
        } else {
            $output->writeln($curlCommand);
        }
    }
}
