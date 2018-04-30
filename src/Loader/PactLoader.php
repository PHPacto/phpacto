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

namespace Bigfoot\PHPacto\Loader;

use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class PactLoader
{
    const CONFIG_EXTS = ['json', 'yml', 'yaml'];

    const CONFIG_FORMATS = ['json', 'yaml'];

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function loadFromFile(string $path): PactInterface
    {
        if (!(file_exists($path) && is_readable($path))) {
            throw new \Exception(sprintf('File `%s` does not exist or is not readable', $path));
        }
//        PHP >= 7.2
//        $format = self::getExtensionFromPath($path)
//            |> self::getFormatFromFileExtension($$);
        $format = self::getFormatFromFileExtension(self::getExtensionFromPath($path));

        try {
            /** @var PactInterface $pact */
            $pact = $this->serializer->deserialize(file_get_contents($path), PactInterface::class, $format);

            return $pact;
        } catch (Mismatch $mismatch) {
            throw $mismatch;
        } catch (\Exception | \Error $e) {
            throw new \Exception(sprintf('File `%s` do not contains a valid pact', $path), 0, $e);
        }
    }

    /**
     * @param string $path
     *
     * @throws \Exception
     *
     * @return PactInterface[]
     */
    public function loadFromDirectory(string $path): array
    {
        $pacts = [];

        if (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', self::CONFIG_EXTS)));

            if (0 === $finder->count()) {
                throw new \Exception(sprintf('No contracts found in `%s`', $path));
            }

            foreach ($finder->files() as $file) {
                $filePath = (string) $file;
                $pacts[$filePath] = $this->loadFromFile($filePath);
            }
        } else {
            throw new \Exception(sprintf('Directory `%s` does not exist', $path));
        }

        return $pacts;
    }

    final public static function getExtensionFromPath(string $path): string
    {
        $file = new \SplFileInfo($path);

        return $file->getExtension();
    }

    final public static function getFormatFromFileExtension(string $extension): string
    {
        if ('yml' === $extension) {
            return 'yaml';
        }

        return  $extension;
    }

    /**
     * @return string[]
     */
    final public static function getSupportedFormats(): array
    {
        return self::CONFIG_FORMATS;
    }

    final public static function isFormatSupported(string $format): bool
    {
        return in_array($format, self::CONFIG_FORMATS, true);
    }
}
