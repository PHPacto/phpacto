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

namespace PHPacto\Matcher;

use PHPacto\Matcher\Mismatches\TypeMismatch;
use PHPacto\Matcher\Rules\RuleMockFactory;
use PHPUnit\Framework\TestCase;

class BodyMatcherTest extends TestCase
{
    /** @var BodyMatcher */
    private $matcher;

    /**
     * @var RuleMockFactory
     */
    private $rule;

    protected function setUp(): void
    {
        $this->matcher = new BodyMatcher();
        $this->rule = new RuleMockFactory();
    }

    public function test_it_match_if_rules_are_satisfied_with_body_plain_string()
    {
        $rules = [
            $this->rule->matching(),
        ];

        $body = 'String';

        $this->matcher->assertMatch($rules, $body);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_plain_string
     */
    public function test_it_match_if_rules_are_satisfied_with_body_url_encoded()
    {
        $rules = [
            'a' => $this->rule->matching(),
            'b' => $this->rule->matching(),
        ];

        $body = [
            'a' => '1',
            'b' => [2, true],
        ];

        $this->matcher->assertMatch($rules, $body);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_url_encoded
     */
    public function test_it_match_if_rules_are_satisfied_with_body_json_encoded()
    {
        $rules = [
            'a' => $this->rule->matching(),
            0 => $this->rule->matching(),
        ];

        $body = [
            'a' => '1',
            0 => [2, true],
        ];

        $this->matcher->assertMatch($rules, $body);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_url_encoded
     */
    public function test_it_throws_mismatch_if_key_is_missing_with_body_url_encoded()
    {
        $rules = [
            'missing-key' => $this->rule->empty(),
        ];

        $body = [
            'a' => '1',
            'b' => [2, true],
        ];

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing-key']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_json_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_url_encoded
     */
    public function test_it_throws_mismatch_if_key_is_missing_with_body_json_encoded()
    {
        $rules = [
            'missing-key' => $this->rule->empty(),
        ];

        $body = [
            'a' => '1',
            'b' => [2, true],
        ];

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing-key']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_plain_string
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_plain_string()
    {
        $rules = [
            $this->rule->notMatching(),
        ];

        $body = 'String';

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches[0]);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_url_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_url_encoded
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_url_encoded()
    {
        $rules = [
            'a' => $this->rule->notMatching(),
        ];

        $body = [
            'a' => '1',
            'b' => [2, true],
        ];

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches['a']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_json_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_json_encoded
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_json_encoded()
    {
        $rules = [
            'a' => $this->rule->notMatching(),
        ];

        $body = [
            'a' => '1',
            'b' => [2, true],
        ];

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches['a']);

            return;
        }

        self::fail('This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_json_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_json_encoded
     */
    public function test_it_throws_mismatch_if_expected_array_but_got_string()
    {
        $rules = [
            'a' => $this->rule->matching(),
        ];

        $body = 'a string';

        try {
            $this->matcher->assertMatch($rules, $body);
        } catch (TypeMismatch $mismatch) {
            self::assertInstanceOf(Mismatches\TypeMismatch::class, $mismatch);

            return;
        }

        self::fail('This test should end in the catch');
    }
}
