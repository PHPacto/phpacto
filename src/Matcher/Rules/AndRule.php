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

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class AndRule extends AbstractRule
{
    /**
     * @var Rule[]
     */
    protected $rules;

    /**
     * @param Rule[] $rules
     * @param mixed  $sample
     */
    public function __construct(array $rules, $sample)
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

    public function assertMatch($test): void
    {
        $mismatches = [];

        foreach ($this->rules as $rule) {
            try {
                $rule->assertMatch($test);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} rules not matching the value');
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
            if (!$item instanceof Rule) {
                throw new Mismatches\TypeMismatch('Rule', \gettype($item), 'Each item should be an instance of {{ expected }}');
            }
        }
    }
}
