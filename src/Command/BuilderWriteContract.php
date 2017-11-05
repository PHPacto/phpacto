<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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

            if (0 === $finder->count()) {
                throw new \Exception('No contract builders found in '.$path);
            }

            foreach ($finder->files() as $i => $file) {
                $this->processFile($output, (string) $file, $format);
            }
        } else {
            throw new \Exception('Path "'.$path.'" must be a readable file or directory');
        }
    }

    protected function processFile(OutputInterface $output, string $path, string $format): void
    {
        $output->writeln(sprintf('Executing <fg=cyan>%s</>', $path));

        $pact = $this->runPactBuilder($path);

        $pactPath = rtrim($path, '.php').'.'.$format;
        $this->writeContractFile($pactPath, $pact, $format);
    }

    /**
     * @throws \Exception
     */
    final protected function runPactBuilder(string $path): Pact
    {
        $pact = require $path;

        if (!$pact instanceof Pact) {
            throw new \Exception('Must return an instance of '.Pact::class);
        }

        return $pact;
    }

    final protected function writeContractFile(string $path, PactInterface $pact, string $format): void
    {
        file_put_contents($path, $this->serializer->serialize($pact, $format));
    }
}
