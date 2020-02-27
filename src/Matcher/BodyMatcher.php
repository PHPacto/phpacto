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

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Mismatches\TypeMismatch;
use Bigfoot\PHPacto\Matcher\Rules\Rule;

class BodyMatcher
{
    public function assertMatch($rules, $body): void
    {
        switch (true) {
            case \is_string($body):
                $this->matchBodySting($rules, $body);
                break;
            case \is_array($body):
                $this->matchBodyArray($rules, $body);
                break;
            default:
                throw new \Exception('Body should be a string or array');
        }
    }

    private function matchBodySting($rules, string $body): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($body);
        } elseif (\is_array($rules)) {
            $mismatches = [];

            /** @var Rule $rule */
            foreach ($rules as $i => $rule) {
                if (!is_numeric($i)) {
                    throw new TypeMismatch('array', 'string');
                }

                try {
                    $rule->assertMatch($body);
                } catch (Mismatches\Mismatch $mismatch) {
                    $mismatches[] = $mismatch;
                }
            }

            if ($mismatches) {
                throw new Mismatches\MismatchCollection($mismatches, 'Body does not match');
            }
        } else {
            throw new \Exception('Body should be a Rule or an array of Rules');
        }
    }

    private function matchBodyArray($rules, array $body): void
    {
        if ($rules instanceof Rule) {
            $rules->assertMatch($body);
        } elseif (\is_array($rules)) {
            $mismatches = [];

            /** @var Rule|Rule[] $rule */
            foreach ($rules as $key => $rule) {
                if (!\array_key_exists($key, $body)) {
                    $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                    continue;
                }

                try {
                    if ($rule instanceof Rule) {
                        $rule->assertMatch($body[$key]);
                    } elseif (\is_array($rule)) {
                        $this->matchBodyArray($rule, $body[$key]);
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
