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

require __DIR__ . '/bootstrap.php';

use PHPacto\Command;
use PHPacto\Factory\SerializerFactory;
use Symfony\Component\Console\Application;

print_banner();

$application = new Application('PHPacto Cli');

$serializer = SerializerFactory::getInstance();

$application->add(new Command\CurlCommand($serializer, CONTRACTS_DIR ?? null));
$application->add(new Command\ValidateContract($serializer, CONTRACTS_DIR ?? null));

$application->run();
