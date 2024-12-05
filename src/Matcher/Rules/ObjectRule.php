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

class ObjectRule extends AbstractRecursiveRule
{
    /**
     * @param Rule[] $properties
     * @param mixed  $sample
     */
    public function __construct(array $properties, $sample = null)
    {
        parent::__construct($properties, $sample);
    }

    /**
     * @return Rule[]
     */
    public function getProperties(): array
    {
        return $this->rules;
    }

    public function assertMatch($test): void
    {
        if (!\is_array($test)) {
            throw new Mismatches\TypeMismatch('array', \gettype($test));
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
            throw new Mismatches\MismatchCollection($mismatches, 'One or more of the {{ count }} properties not matching the rule');
        }
    }

    /**
     * @param Rule[] $properties
     */
    protected function assertSupport($properties): void
    {
        foreach ($properties as $item) {
            if (!(\is_array($item) || $item instanceof Rule)) {
                throw new Mismatches\TypeMismatch('Rule', \gettype($properties), 'Each item should be an instance of {{ expected }}');
            }
        }
    }

    public function getSample()
    {
        if ($this->sample !== null) {
            return $this->sample;
        }

        $sample = [];

        foreach ($this->rules as $key => $rule) {
            $sample[$key] = $this->getSampleRecursive($rule);
        }

        return $sample;
    }

    protected function getSampleRecursive($rule)
    {
        if ($rule instanceof Rule) {
            $sample = $rule->getSample();

            if (null === $sample) {
                if ($rule instanceof ObjectRule) {
                    $sample = $this->getSampleRecursive($rule->getProperties());
                } elseif ($rule instanceof EachItemRule) {
                    $sample = [
                        $this->getSampleRecursive($rule->getRules()),
                    ];
                } elseif ($rule instanceof OrRule) {
                    $childRules = $rule->getRules();

                    $sample = $childRules[array_rand($childRules)];
                } elseif (method_exists($rule, 'getRule')) {
                    $sample = $rule->getRule();
                }
            }

            return $sample;
        } elseif (\is_array($rule)) {
            $result = [];

            foreach ($rule as $key => $value) {
                $result[$key] = $this->getSampleRecursive($value);
            }

            return $result;
        }

        return $rule;
    }
}
