<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2018  Damian Długosz
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

class OrRule extends AbstractRule
{
    /**
     * @var Rule[]
     */
    protected $rules;

    /**
     * @param Rule[] $rules
     * @param mixed  $sample
     */
    public function __construct(array $rules, $sample = null)
    {
        $this->assertSupport($this->rules = $rules);

        parent::__construct($sample);
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function assertMatch($test, $rules = null, $isMatchingArray = false): void
    {
        if (null === $rules) {
            $rules = $this->rules;
            $exitOnMatch = true;
        }

        if ($rules instanceof Rule) {
            $rules->assertMatch($test);

            return;
        }

        if ($isMatchingArray) {
            if (!\is_array($test)) {
                throw new Mismatches\TypeMismatch('array', \gettype($test));
            }
        }

        $mismatches = [];

        foreach ($rules as $key => $rule) {
            try {
                if ($isMatchingArray) {
                    if (!\array_key_exists($key, $test)) {
                        throw new Mismatches\KeyNotFoundMismatch((string) $key);
                    }

                    $testValue = $test[$key];
                } else {
                    $testValue = $test;
                }

                $this->assertMatch($testValue, $rule, @$exitOnMatch && \is_array($rule));

                if (@$exitOnMatch) {
                    // If at least one Rule match the value, its OK
                    return;
                }
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        if (@$exitOnMatch && \count($mismatches) === \count($this->rules) || \count($mismatches)) {
            throw new Mismatches\MismatchCollection($mismatches, 'None of the {{ count }} rules is matching');
        }
    }

    public function getSample()
    {
        if (null !== $this->sample) {
            return $this->sample;
        }
    }

    /**
     * @param Rule[] $rules
     */
    protected function assertSupport(array $rules): void
    {
        if (!\count($rules)) {
            throw new Mismatches\ValueMismatch('The array is empty', 'An array with values', 'An empty array');
        }

        foreach ($rules as $item) {
            if (\is_array($item)) {
                $this->assertSupport($item);
            } elseif (!$item instanceof Rule) {
                throw new Mismatches\TypeMismatch('Rule', \gettype($rules), 'Each item should be an instance of {{ expected }}');
            }
        }
    }
}
