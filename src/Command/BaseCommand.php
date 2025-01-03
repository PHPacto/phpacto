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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Serializer\Serializer;

abstract class BaseCommand extends Command
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $defaultContractsDir;

    public function __construct(Serializer $serializer, string $defaultContractsDir = null)
    {
        $this->serializer = $serializer;
        $this->defaultContractsDir = $defaultContractsDir;

        parent::__construct();
    }
}
