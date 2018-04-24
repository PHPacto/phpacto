<?php

/*
 * PHPacto - Contract testing solution
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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class ContainsRule extends AbstractRule
{
    /**
     * @var Rule[]
     */
    protected $rules;

    /**
     * @param Rule|Rule[] $rules
     * @param mixed  $sample
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

    public function assertMatch($test): void
    {
        if (!is_array($test)) {
            throw new Mismatches\TypeMismatch('array', gettype($test));
        }

        $mismatches = [];

        foreach ($test as $key => $item) {
            try {
                if ($this->rules instanceof Rule) {
                    $this->rules->assertMatch($item);
                } elseif (is_array($this->rules)) {
                    if (!is_array($item)) {
                        throw new Mismatches\TypeMismatch('array', gettype($item));
                    }

                    $itemMismatches = [];
                    foreach ($this->rules as $rKey => $rule) {
                        if (!array_key_exists($rKey, $item)) {
                            throw new Mismatches\KeyNotFoundMismatch($rKey);
                        }

                        try {
                            $rule->assertMatch($item[$rKey]);
                        } catch (Mismatches\Mismatch $e) {
                            $itemMismatches[$rKey] = $e;
                        }
                    }

                    if ($itemMismatches) {
                        throw new Mismatches\MismatchCollection($itemMismatches, 'One or more of the {{ count }} values are not matching the rule');
                    }
                }

                // If at least one item match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        throw new Mismatches\MismatchCollection($mismatches, 'At least one item of array should match the rule');
    }

    public function getSample()
    {
        if (null !== $this->sample) {
            return $this->sample;
        }

        if ($this->rules instanceof Rule) {
            return $this->rules->getSample();
        }

        $sample = [];
        foreach ($this->rules as $key => $rule) {
            $sample[$key] = $rule->getSample();
        }

        return [$sample];
    }

    /**
     * @param Rule|Rule[] $rules
     */
    protected function assertSupport($rules): void
    {
        if (is_array($rules)) {
            foreach ($rules as $rule) {
                if (!$rule instanceof Rule) {
                    throw new Mismatches\TypeMismatch('Rule', gettype($rules), 'Each item should be an instance of {{ expected }}');
                }
            }
        } elseif (!$rules instanceof Rule) {
            throw new Mismatches\TypeMismatch('Rule', gettype($rules), 'Should be an instance of {{ expected }}');
        }
    }
}
