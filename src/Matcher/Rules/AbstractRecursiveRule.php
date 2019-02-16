<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

abstract class AbstractRecursiveRule extends AbstractRule
{
    /**
     * @var Rule|Rule[]
     */
    protected $rules;

    /**
     * @param Rule|Rule[] $rules
     * @param mixed       $sample
     */
    public function __construct($rules, $sample = null)
    {
        $this->assertSupport($this->rules = $rules);

        parent::__construct($sample);
    }

    /**
     * @return Rule|Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    public function assertMatchRec($rules, $test): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($test);

            return;
        }

        if (\is_array($rules) && !\is_array($test)) {
            throw new Mismatches\TypeMismatch('array', \gettype($test));
        }

        if (!\count($test)) {
            return;
        }

        $mismatches = [];

        foreach ($rules as $key => $rule) {
            try {
                if (!\array_key_exists($key, $test)) {
                    throw new Mismatches\KeyNotFoundMismatch($key);
                }

                $item = $test[$key];

                $this->assertMatchRec($rule, $item);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} values not matching the rule');
        }
    }

    /**
     * @param Rule|Rule[] $rules
     */
    protected function assertSupport($rules): void
    {
        if (\is_array($rules)) {
            if (0 === \count($rules)) {
                throw new Mismatches\ValueMismatch('The array is empty', 'An array with values', 'An empty array');
            }

            foreach ($rules as $item) {
                $this->assertSupport($item);
            }
        } elseif (!$rules instanceof Rule) {
            throw new Mismatches\TypeMismatch('Rule', \gettype($rules), 'Should be an instance of {{ expected }}');
        }
    }
}
