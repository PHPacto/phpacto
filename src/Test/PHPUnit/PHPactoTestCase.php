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

namespace PHPacto\Test\PHPUnit;

use PHPacto\PHPacto;
use PHPacto\Test\PHPactoTestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Blacklist;

abstract class PHPactoTestCase extends TestCase
{
    use PHPactoTestTrait;

    /**
     * @var PHPacto
     */
    protected $phpacto;

    protected $contractsBasePath = '';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        Blacklist::$blacklistedClassNames[__CLASS__] = 1;
    }

    public function setUp(): void
    {
        $this->phpacto = new PHPacto($this->contractsBasePath);
    }
}
