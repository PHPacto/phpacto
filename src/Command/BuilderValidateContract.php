<?php

/*
 * This file is part of PHPacto
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

use Bigfoot\PHPacto\Loader\FileLoader;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuilderValidateContract extends BuilderWriteContract
{
    protected function configure()
    {
        $this
            ->setName('builder:validate')
            ->setDescription('Check that all contracts are up to date with their contract builders')
            ->addOption('format', 'f', InputArgument::OPTIONAL, 'The contract\'s file format <fg=cyan>('.implode('|', FileLoader::getSupportedFormats()).')</>', 'json')
            ->addArgument('path', InputArgument::OPTIONAL, 'The path to contracts file or directory', $this->defaultContractsDir);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        self::getTable($output)->render();
    }

    protected function processFile(OutputInterface $output, string $path, string $format): void
    {
        $pact = $this->runPactBuilder($path);

        $pactPath = rtrim($path, '.php').'.'.$format;

        if (!file_exists($pactPath)) {
            self::outputResult($output, $pactPath, '<fg=red>✖ Pact missing</>');

            return;
        }

        try {
            $matching = $this->normalizePact($pact, $format) === $this->decodeContractFile($pactPath, $format);

            self::outputResult($output, $pactPath, $matching ? '<fg=green>✔ Matching</>' : '<fg=red>✖ Not matching</>');
        } catch (\Exception | \Error $e) {
            self::outputResult($output, $pactPath, '<fg=red>✖ Invalid</>');
        }
    }

    final protected function decodeContractFile(string $path, string $format): array
    {
        return $this->serializer->decode(file_get_contents($path), $format);
    }

    final protected function normalizePact(PactInterface $pact, string $format): array
    {
        return $this->serializer->normalize($pact, $format);
    }

    private static function getTable(OutputInterface $output): Table
    {
        static $table;

        if (!$table) {
            $table = new Table($output);
            $table->setStyle('borderless');
            $table->setHeaders([
                'Contract builder',
                'Status',
            ]);
        }

        return $table;
    }

    private static function outputResult(OutputInterface $output, string $filePath, string $status): void
    {
        self::getTable($output)->addRow([$filePath, $status]);
    }
}
