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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 */

namespace PHPacto\Matcher\Rules;

use PHPacto\Matcher\Mismatches;

class EachItemRule extends AbstractRecursiveRule
{
    public function assertMatch($test): void
    {
        if (!\is_array($test)) {
            throw new Mismatches\TypeMismatch('array', \gettype($test));
        }


        $mismatches = [];

        foreach ($test as $i => $value) {
            if ($this->rules instanceof Rule) {
                try {
                    $this->rules->assertMatch($value);
                } catch (Mismatches\Mismatch $e) {
                    $mismatches[$i] = $e;
                }

                continue;
            }

            foreach ($this->rules as $key => $rule) {
                try {
                    if (!\array_key_exists($key, $value)) {
                        throw new Mismatches\KeyNotFoundMismatch($key);
                    }

                    $rule->assertMatch($value[$key]);
                } catch (Mismatches\Mismatch $e) {
                    $mismatches[$i.'.'.$key] = $e;
                }
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, sprintf('{{ count }} items of %d elements doesn\'t match the rule', count($test)));
        }
    }

    public function getSample()
    {
        if (null !== $this->sample) {
            return $this->sample;
        }

        return [$this->rules->getSample()];
    }
}
