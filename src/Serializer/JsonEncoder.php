<?php

namespace Bigfoot\PHPacto\Serializer;

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

class JsonEncoder extends BaseJsonEncoder
{
    protected $defaultContext;

    public function __construct(JsonEncode $encodingImpl = null, JsonDecode $decodingImpl = null, array $defaultContext = [])
    {
        parent::__construct($encodingImpl, $decodingImpl);

        $this->defaultContext = $defaultContext;
    }

    public function encode($data, $format, array $context = array())
    {
        return parent::encode($data, $format, array_merge($this->defaultContext, $context));
    }

    public function decode($data, $format, array $context = array())
    {
        return parent::decode($data, $format, array_merge($this->defaultContext, $context));
    }
}
