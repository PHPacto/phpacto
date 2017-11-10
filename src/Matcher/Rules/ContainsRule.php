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

class ContainsRule extends AbstractRule
{
    /**
     * @var Rule
     */
    protected $rule;

    public function __construct(Rule $rule, $sample = null)
    {
        $this->rule = $rule;

        parent::__construct($sample);
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function assertMatch($test): void
    {
        if (!is_array($test)) {
            throw new Mismatches\TypeMismatch('array', gettype($test));
        }

        $mismatches = [];

        foreach ($test as $key => $item) {
            try {
                $this->rule->assertMatch($item);

                // If at least one item match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        throw new Mismatches\MismatchCollection($mismatches, 'At least one item of array should match the rule');
    }
}
