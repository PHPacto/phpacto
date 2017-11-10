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

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;

class HeadersMatcher implements MessageMatcher
{
    public function assertMatch($rules, MessageInterface $message): void
    {
        $expectedHeaders = $this->normalizeKeys($rules);
        $actualHeaders = $this->normalizeKeys($message->getHeaders());

        $this->compareHeaders($expectedHeaders, $actualHeaders);
    }

    private function normalizeKeys($headers)
    {
        return array_change_key_case($headers, CASE_LOWER);
    }

    private function compareHeaders($rules, array $headers): void
    {
        $mismatches = [];

        /** @var Rule $rule */
        foreach ($rules as $key => $rule) {
            if (!array_key_exists($key, $headers)) {
                $mismatches[$key] = new Mismatches\KeyNotFoundMismatch($key);
                continue;
            }

            if (is_array($headers[$key]) && 1 === count($headers[$key])) {
                $headers[$key] = $headers[$key][0];
            }

            try {
                if ($rule instanceof Rule) {
                    $rule->assertMatch($headers[$key]);
                } elseif (is_array($rule)) {
                    $this->compareHeaders($rule, $headers[$key]);
                } else {
                    throw new \Exception('Headers should be a Rule or an array of Rules');
                }
            } catch (Mismatches\Mismatch $mismatch) {
                $mismatches[] = $mismatch;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, '{{ count }} Headers do not match');
        }
    }
}
