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

namespace PHPacto\Serializer;

use PHPacto\Factory\SerializerFactory;
use PHPacto\Matcher\Rules\RuleMockFactory;
use PHPUnit\Framework\TestCase;

abstract class SerializerAwareTestCase extends TestCase
{
    /**
     * @var RuleNormalizer
     */
    protected $normalizer;

    /**
     * @var RuleMockFactory
     */
    protected $rule;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->normalizer = SerializerFactory::getInstance();
        $this->rule = new RuleMockFactory(SerializerFactory::getRuleMap());
    }
}
