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

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;

class ObjectRule extends AbstractRecursiveRule
{
    /**
     * @var Rule[]
     */
    protected $properties;

    /**
     * @param Rule[] $properties
     * @param mixed  $sample
     */
    public function __construct(array $properties, $sample = null)
    {
        parent::__construct($this->properties = $properties, $sample);
    }

    /**
     * @return Rule[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function assertMatch($test): void
    {
        if (!\is_array($test)) {
            throw new Mismatches\TypeMismatch('array', \gettype($test));
        }

        $mismatches = [];

        foreach ($this->properties as $key => $rule) {
            try {
                if (!\array_key_exists($key, $test)) {
                    throw new Mismatches\KeyNotFoundMismatch($key);
                }

                $this->assertMatchRec($rule, $test[$key]);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} properties not matching the rule');
        }
    }

    /**
     * @param Rule[] $properties
     */
    protected function assertSupport($properties): void
    {
        if (!\count($properties)) {
            throw new Mismatches\ValueMismatch('The array is empty', 'An array with values', 'An empty array');
        }

        foreach ($properties as $item) {
            if (!(\is_array($item) || $item instanceof Rule)) {
                throw new Mismatches\TypeMismatch('Rule', \gettype($properties), 'Each item should be an instance of {{ expected }}');
            }
        }
    }
}
