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

namespace PHPacto\Matcher;

use PHPacto\Matcher\Rules\EqualsRule;
use PHPacto\Matcher\Rules\RuleMockFactory;
use PHPUnit\Framework\TestCase;

class HeadersMatcherTest extends TestCase
{
    /** @var HeadersMatcher */
    private $matcher;

    /**
     * @var RuleMockFactory
     */
    private $rule;

    protected function setUp()
    {
        $this->matcher = new HeadersMatcher();
        $this->rule = new RuleMockFactory();
    }

    public function test_it_match_if_rules_are_satisfied()
    {
        $rules = [
            'Key1' => $this->rule->matching(),
            'key-2' => $this->rule->matching(),
        ];

        $headers = [
            'Key1' => 'a matching value',
            'key-2' => ['a matching value', 'another matching value'],
            'other' => 'an extra value',
        ];

        $this->matcher->assertMatch($rules, $headers);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied
     */
    public function test_it_throws_mismatch_if_key_is_missing()
    {
        $rules = [
            'missing' => $this->rule->empty(),
        ];

        $headers = [];

        try {
            $this->matcher->assertMatch($rules, $headers);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_throws_mismatch_if_key_is_missing
     */
    public function test_it_throws_mismatch_if_value_doesnt_match()
    {
        $rules = [
            'key' => $this->rule->notMatching(),
        ];

        $headers = [
            'a key' => 'is not matching',
        ];

        try {
            $this->matcher->assertMatch($rules, $headers);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['key']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    public function test_it_match_rule_with_multiple_values()
    {
        $rules = [
            'key' => new EqualsRule('B'),
        ];

        $headers = [
            'key' => ['A', 'B'],
        ];

        $this->matcher->assertMatch($rules, $headers);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function test_it_match_multiple_values_with_multiple_values()
    {
        $rules = [
            'key' => [
                new EqualsRule('A'),
                new EqualsRule('B'),
            ],
        ];

        $headers = [
            'key' => ['B', 'A'],
        ];

        $this->matcher->assertMatch($rules, $headers);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
