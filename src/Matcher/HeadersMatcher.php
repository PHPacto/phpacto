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

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Rules\ContainsItemRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;

class HeadersMatcher
{
    public function assertMatch($rules, array $headers): void
    {
        $mismatches = [];

        /** @var Rule $rule */
        foreach ($rules as $key => $rule) {
            if (!\array_key_exists($key, $headers)) {
                $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                continue;
            }

            try {
                $this->assertMatchItem($rule, $headers[$key]);
            } catch (Mismatches\Mismatch $mismatch) {
                $mismatches[$key] = $mismatch;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, '{{ count }} Headers do not match');
        }
    }

    private function assertMatchItem($rule, $value)
    {
        if ($rule instanceof Rule) {
            if (\is_array($value)) {
                $contains = new ContainsItemRule($rule);
                $contains->assertMatch($value);
            } else {
                $rule->assertMatch($value);
            }
        } elseif (\is_array($rule)) {
            $mismatches = [];

            /** @var Rule $rule */
            foreach ($rule as $key => $childRule) {
                $contains = new ContainsItemRule($childRule);

                try {
                    $contains->assertMatch($value);
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[$key] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, '{{ count }} Headers do not match');
            }
        } else {
            throw new \Exception('Headers should be a Rule or an array of Rules');
        }
    }
}
