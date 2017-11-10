<?php

/*
 * This file is part of PHPacto
 *
 * Copyright (c) 2017  Damian Długosz <bigfootdd@gmail.com>
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
     * @param mixed $sample
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

    public function assertMatch($test): void
    {
        $mismatches = [];

        foreach ($this->rules as $rule) {
            try {
                $rule->assertMatch($test);

                // If at least one Rule match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if (count($mismatches) === count($this->rules)) {
            throw new Mismatches\MismatchCollection($mismatches, 'None of the {{ count }} rules is matching');
        }
    }

    public function getSample()
    {
        if (null !== $this->sample) {
            return $this->sample;
        }

        if (count($this->rules)) {
            $rule = $this->rules[array_rand($this->rules)];

            return $rule->getSample();
        }
    }

    /**
     * @param Rule[] $rules
     */
    protected function assertSupport(array $rules): void
    {
        foreach ($rules as $item) {
            if (!$item instanceof Rule) {
                throw new Mismatches\TypeMismatch('Rule', gettype($rules), 'Each item should be an instance of {{ expected }}');
            }
        }
    }
}
