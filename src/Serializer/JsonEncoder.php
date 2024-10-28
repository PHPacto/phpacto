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

namespace PHPacto\Serializer;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;

class JsonEncoder extends BaseJsonEncoder implements EncoderInterface, DecoderInterface
{
    /**
     * The formats that this Encoder supports.
     *
     * {@inheritdoc}
     */
    public function __construct(JsonEncode $encodingImpl = null, JsonDecode $decodingImpl = null)
    {
        $this->encodingImpl = $encodingImpl ?: $this->getJsonEncode();
        $this->decodingImpl = $decodingImpl ?: $this->getJsonDecode();
    }

    /**
     * Instantiates a JsonEncode instance.
     */
    private function getJsonEncode()
    {
        return new JsonEncode([JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Instantiates a JsonDecode instance.
     */
    private function getJsonDecode()
    {
        return new JsonDecode([JsonDecode::ASSOCIATIVE => true]);
    }
}
