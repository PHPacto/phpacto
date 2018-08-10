<?php

declare(strict_types=1);

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

class ContainsItemRule extends AbstractRule
{
    /**
     * @var Rule
     */
    protected $rule;

    public function __construct(Rule $rule, $sample = null)
    {
        $this->assertSupport($this->rule = $rule);

        parent::__construct($sample);
    }

    /**
     * @return Rule|Rule[]
     */
    public function getRule()
    {
        return $this->rule;
    }

    public function assertMatch($test): void
    {
        if (!is_array($test)) {
            throw new Mismatches\TypeMismatch('array', gettype($test));
        }

        if (!count($test)) {
            throw new Mismatches\ValueMismatch('The array is empty', 'An array with values', 'An empty array');
        }

        $mismatches = [];

        foreach ($test as $key => $item) {
            try {
                $this->rule->assertMatch($item);

                // If at least one item matches the rule, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        throw new Mismatches\MismatchCollection($mismatches, 'At least one item of array should match the rule');
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
