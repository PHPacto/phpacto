<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
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
use Zend\Diactoros\Stream;

/**
 * @coversNothing
 */
class BodyMatcherTest extends TestCase
{
    /** @var BodyMatcher */
    private $matcher;

    /**
     * @var RuleMockFactory
     */
    private $rule;

    protected function setUp()
    {
        $this->matcher = new BodyMatcher();
        $this->rule = new RuleMockFactory();
    }

    public function test_it_match_if_rules_are_satisfied_with_body_plain_string()
    {
        $rules = [
            $this->rule->isMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('String');

        $message = (new Request())
            ->withBody($stream);

        $this->matcher->assertMatch($rules, $message);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_plain_string
     */
    public function test_it_match_if_rules_are_satisfied_with_body_url_encoded()
    {
        $rules = [
            'a' => $this->rule->isMatching(),
            'b' => $this->rule->isMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('a=1&b%5B0%5D=2&b%5B1%5D=3');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($stream);

        $this->matcher->assertMatch($rules, $message);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_url_encoded
     */
    public function test_it_match_if_rules_are_satisfied_with_body_json_encoded()
    {
        $rules = [
            'a' => $this->rule->isMatching(),
            0 => $this->rule->isMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        $this->matcher->assertMatch($rules, $message);

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

        $stream = new Stream('php://memory', 'w');
        $stream->write('a=1&b%5B0%5D=2&b%5B1%5D=3');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($stream);

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing-key']);

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
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

        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\KeyNotFoundMismatch::class, $mismatches['missing-key']);

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_plain_string
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_plain_string()
    {
        $rules = [
            $this->rule->isNotMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('String');

        $message = (new Request())
            ->withBody($stream);

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches[0]);

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_url_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_url_encoded
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_url_encoded()
    {
        $rules = [
            'a' => $this->rule->isNotMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('a=1&b%5B0%5D=2&b%5B1%5D=3');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($stream);

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches['a']);

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }

    /**
     * @depends test_it_match_if_rules_are_satisfied_with_body_json_encoded
     * @depends test_it_throws_mismatch_if_key_is_missing_with_body_json_encoded
     */
    public function test_it_throws_mismatch_if_value_doesnt_match_with_body_json_encoded()
    {
        $rules = [
            'a' => $this->rule->isNotMatching(),
        ];

        $stream = new Stream('php://memory', 'w');
        $stream->write('{"a":1,"0":[2,"3"]}');

        $message = (new Request())
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        try {
            $this->matcher->assertMatch($rules, $message);
        } catch (Mismatches\MismatchCollection $mismatches) {
            self::assertCount(1, $mismatches);
            self::assertInstanceOf(Mismatches\ValueMismatch::class, $mismatches['a']);

            return;
        }

        self::assertFalse(true, 'This test should end in the catch');
    }
}
