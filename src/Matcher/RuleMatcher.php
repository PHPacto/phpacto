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

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Rules\Rule;

class RuleMatcher
{
    public function assertMatch($rules, $value): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($value);
        } elseif (is_array($rules)) {
        } else {
            throw new \Exception('$rules parameter should be an instance of Rule or an array of Rules');
        }
    }

    private function matchScalar($rules, $value): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($value);
        } elseif (is_array($rules)) {
            $mismatches = [];

            /** @var Rule $rule */
            foreach ($rules as $rule) {
                try {
                    $rule->assertMatch($value);
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'Body does not match');
            }
        }
    }

    private function matchArray($rules, array $values): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($values);
        } elseif (is_array($rules)) {
            $mismatches = [];

            /** @var Rule|Rule[] $rule */
            foreach ($rules as $key => $rule) {
                if (!array_key_exists($key, $values)) {
                    $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                    continue;
                }

                try {
                    if ($rule instanceof Rule) {
                        $rule->assertMatch($values[$key]);
                    } elseif (is_array($rule)) {
                        $this->matchArray($rule, $values[$key]);
                    } else {
                        throw new \Exception('Body should be a Rule or an array of Rules');
                    }
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[$key] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'Body does not match');
            }
        } else {
            throw new \Exception('Body should be a Rule or an array of Rules');
        }
    }
}
