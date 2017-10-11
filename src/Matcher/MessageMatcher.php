<?php

namespace Bigfoot\PHPacto\Matcher;

use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\MessageInterface;

interface MessageMatcher
{
    /**
     * Match the message with given rules
     *
     * @param Rule|Rule[]|array $rules
     * @param MessageInterface $message
     * @throws MismatchCollection
     */
    public function assertMatch($rules, MessageInterface $message): void;
}
