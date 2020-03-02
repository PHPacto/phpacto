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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Command;

use Bigfoot\PHPacto\Loader\PactLoader;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\PactInterface;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class CurlCommand extends BaseCommand
{
    /**
     * @var PactLoader
     */
    protected $loader;

    private $exitCode = 0;

    public function __construct(Serializer $serializer, string $defaultContractsDir = null)
    {
        parent::__construct($serializer, $defaultContractsDir);

        $this->loader = new PactLoader($serializer);
    }

    protected function configure()
    {
        $this
            ->setName('curl')
            ->setDescription('Generate cURL commands for contracts')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir);

        $this->addOption('host', null, InputArgument::OPTIONAL, 'On wich host is your service located', 'localhost');
        $this->addOption('port', 'p', InputArgument::OPTIONAL, 'On wich port is your service located', 80);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $host = $input->getOption('host');
        $port = $input->getOption('port');

        $curlFormatter = new CurlFormatter(INF);

        if (is_file($path) && is_readable($path)) {
//            try {
            $pact = $this->loadPact((string) $path);
            $this->printCurlCommand($output, $curlFormatter, $pact, $host, $port, $path, false);
//            } catch (\Throwable $e) {
//                if ($e instanceof Mismatch) {
//                    self::outputResult($output, $path, '<fg=red>✖ Not valid</>');
//                } elseif ($e->getPrevious() && 'Syntax error' === $e->getPrevious()->getMessage()) {
//                    self::outputResult($output, $path, '<fg=red>✖ Syntax error</>');
//                }
//            }
        } elseif (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', PactLoader::CONFIG_EXTS)));

            if (0 === $finder->count()) {
                throw new \Exception(sprintf('No files found in `%s`', $path));
            }

            foreach ($finder->files() as $file) {
                $shortPath = self::getShortPath((string) $file, $path);

                try {
                    $pact = $this->loadPact((string) $file);
                    $this->printCurlCommand($output, $curlFormatter, $pact, $host, $port, $shortPath, true);
                } catch (\Throwable $e) {
                    if ($e instanceof Mismatch) {
                        self::outputResult($output, $shortPath, '<fg=red>✖ Not valid</>');
                    } elseif ($e->getPrevious() && 'Syntax error' === $e->getPrevious()->getMessage()) {
                        self::outputResult($output, $shortPath, '<fg=red>✖ Syntax error</>');
                    } else {
                        throw $e;
                    }

                    $this->exitCode = 1;
                }
            }

            self::getTable($output)->render();
        } else {
            throw new \Exception(sprintf('Path "%s" must be a readable file or directory', $path));
        }

        return $this->exitCode;
    }

    protected function loadPact(string $filePath): PactInterface
    {
        return $this->loader->loadFromFile($filePath);
    }

    private function printCurlCommand(OutputInterface $output, CurlFormatter $formatter, PactInterface $pact, string $host, int $port, string $path, bool $multipleFiles = false): void
    {
        $sample = $pact->getRequest()->getSample();

        $uri = $sample->getUri()
            ->withScheme('http')
            ->withHost($host)
            ->withPort($port);

        $request = $sample->withUri($uri);

        $curlCommand = $this->generateCurlCommand($request, $formatter);

        self::outputResult($output, $path, $curlCommand, $multipleFiles);
    }

    /**
     * This is a compatibility layer for Namshi/Cuzzle ^2 and ^1.
     */
    private function generateCurlCommand(ServerRequestInterface $request, CurlFormatter $formatter): string
    {
        $guzzleVersion = \GuzzleHttp\ClientInterface::VERSION;

        // For Guzzle 5 compatibility
        if (version_compare($guzzleVersion, '6', '<')) {
            $bodyStream = \GuzzleHttp\Stream\Stream::factory($request->getBody()->getContents());

            $request = new \GuzzleHttp\Message\Request($request->getMethod(), (string) $request->getUri(), $request->getHeaders(), $bodyStream);
        }

        return $formatter->format($request);
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
            return str_replace($rootDir . '/', '', $filePath);
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
