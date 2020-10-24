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

namespace Bigfoot\PHPacto\Serializer;

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
     *
     * @internal this exists to bridge Symfony Serializer 3 to Symfony Serializer 4, and can be removed
     *   once PHPacto requires Symfony 4.2 or higher.
     */
    private function getJsonEncode()
    {
        $json_encoding_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        $reflection = new \ReflectionClass(JsonEncode::class);
        if (\array_key_exists('OPTIONS', $reflection->getConstants())) {
            return new JsonEncode([JsonEncode::OPTIONS => $json_encoding_options]);
        }

        return new JsonEncode($json_encoding_options);
    }

    /**
     * Instantiates a JsonDecode instance.
     *
     * @internal this exists to bridge Symfony 3 to Symfony 4, and can be removed
     *   once Drupal requires Symfony 4.2 or higher.
     */
    private function getJsonDecode()
    {
        $reflection = new \ReflectionClass(JsonDecode::class);
        if (\array_key_exists('ASSOCIATIVE', $reflection->getConstants())) {
            return new JsonDecode([JsonDecode::ASSOCIATIVE => true]);
        }

        return new JsonDecode(true);
    }
}
