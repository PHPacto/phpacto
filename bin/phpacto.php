<?php

require __DIR__ . '/autoload.php';

use Bigfoot\PHPacto\Command;
use Bigfoot\PHPacto\Factory\SerializerFactory;
use Symfony\Component\Console\Application;

$application = new Application('PHPacto Cli');

$serializer = SerializerFactory::getInstance();

$application->add(new Command\BuilderWriteContract($serializer, CONTRACTS_DIR));
$application->add(new Command\BuilderValidateContract($serializer, CONTRACTS_DIR));
$application->add(new Command\ValidateContract($serializer, CONTRACTS_DIR));

$application->run();
