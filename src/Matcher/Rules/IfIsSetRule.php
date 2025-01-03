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

class IfIsSetRule extends AbstractRecursiveRule
{
    public function assertMatch($test): void
    {
        // If you are here, the key exists
        // If the key exists, and is not NULL, the test must match the rules
        if (null === $test) {
            return;
        }

        if ($this->rules instanceof Rule) {
            $this->rules->assertMatch($test);

            return;
        }

        if (!\is_array($test)) {
            throw new Mismatches\TypeMismatch('array', \gettype($test));
        }

        if (!\count($test)) {
            return;
        }

        $mismatches = [];

        foreach ($this->rules as $key => $rule) {
            try {
                if (!\array_key_exists($key, $test)) {
                    throw new Mismatches\KeyNotFoundMismatch($key);
                }

                $this->assertMatchRec($rule, $test[$key]);
            } catch (Mismatches\Mismatch $e) {
                $mismatches[$key] = $e;
            }
        }

        if ($mismatches) {
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} values not matching the rule');
        }
    }

    public function getSample()
    {
        if (null !== $this->sample) {
            return $this->sample;
        }

        if ($this->rules instanceof Rule) {
            return $this->rules->getSample();
        }

        $sample = [];

        foreach ($this->rules as $key => $rule) {
            $sample[$key] = $rule->getSample();
        }

        return $sample;
    }
}
