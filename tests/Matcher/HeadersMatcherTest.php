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

use Bigfoot\PHPacto\Matcher\Rules\RuleMockFactory;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Request;

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
            'Key1' => $this->rule->isMatching(),
            'key-2' => $this->rule->isMatching(),
        ];

        $message = (new Request())
            ->withHeader('key1', 'A')
            ->withHeader('KEY-2', ['B', 'C'])
            ->withHeader('other', '3');

        $this->matcher->assertMatch($rules, $message);

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

        $message = new Request();

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @-depends test_it_throws_mismatch_if_key_is_missing
     */
    public function test_it_throws_mismatch_if_value_doesnt_match()
    {
        $rules = [
            'key' => $this->rule->isNotMatching(),
        ];

        $message = (new Request())
            ->withHeader('key', '');

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches[0]);

            return;
        }

        self::fail('This test should end in the catch');
    }
}
