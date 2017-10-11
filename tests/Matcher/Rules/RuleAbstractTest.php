<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Serializer\RuleNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

abstract class RuleAbstractTest extends TestCase
{
    /**
     * @var RuleNormalizer
     */
    protected $normalizer;

    /**
     * @var RuleMockFactory
     */
    protected $rule;

    public function setUp()
    {
        $this->normalizer = new RuleNormalizer();
        $this->normalizer->setSerializer(new Serializer([$this->normalizer]));
        $this->rule = self::getRuleMockFactory();
    }

    protected static function getRuleMockFactory() {
        return new RuleMockFactory();
    }
}
