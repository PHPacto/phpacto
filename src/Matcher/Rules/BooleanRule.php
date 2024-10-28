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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;

class BooleanRule extends AbstractRule
{
    /**
     * @var Rule
     */
    protected $rule;

    public function __construct($sample = null)
    {
        parent::__construct($sample);
    }

    public function assertMatch($test): void
    {
        if (!\is_bool($test)) {
            throw new Mismatches\TypeMismatch('boolean', \gettype($test));
        }
    }

    /**
     * @param Rule|Rule[] $rules
     * @param mixed       $value
     */
    protected function assertSupport($value): void
    {
        if (!\is_bool($value)) {
            throw new Mismatches\TypeMismatch('boolean', \gettype($value), 'Should be an instance of {{ expected }}');
        }
    }

    public function getSample()
    {
        return $this->sample ?? false;
    }
}
