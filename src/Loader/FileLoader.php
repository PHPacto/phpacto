<?php

namespace Bigfoot\PHPacto\Loader;

use Bigfoot\PHPacto\PactInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;

class FileLoader
{
    private const CONFIG_EXTS = ['json', 'yml', 'yaml'];

    private const CONFIG_FORMATS = ['json', 'yaml'];

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

//        $format = self::getExtensionFromPath($path)
//            |> self::getFormatFromFileExtension($$);
        $format = self::getFormatFromFileExtension(self::getExtensionFromPath($path));

        try {
            /** @var PactInterface $pact */
            $pact = $this->serializer->deserialize(file_get_contents($path), PactInterface::class, $format);

            return $pact;
        } catch (\Exception | \Error $e) {
            throw new \Exception(sprintf('File `%s` do not contains a valid pact', $path), 0, $e);
        }
    }

    /**
     * @param string $path
     * @return PactInterface[]
     * @throws \Exception
     */
    public function loadFromDirectory(string $path): array
    {
        $pacts = [];

        if (is_dir($path)) {
            $finder = new Finder();
            $finder->files()->in($path)->name(sprintf('*.{%s}', implode(',', self::CONFIG_EXTS)));

            if ($finder->count() == 0) {
                throw new \Exception(sprintf('No contract builders found in `%s`', $path));
            }

            foreach ($finder->files() as $i => $file) {
                $filePath = (string) $file;
                $pacts[$filePath] = $this->loadFromFile($filePath);
            }
        } else {
            throw new \Exception(sprintf('Directory `%s` does not exist', $path));
        }

        return $pacts;
    }

    public final static function getExtensionFromPath(string $path): string
    {
        $file = new \SplFileInfo($path);

        return $file->getExtension();
    }

    public final static function getFormatFromFileExtension(string $extension): string
    {
        if ($extension == 'yml') {
            return 'yaml';
        }

        return  $extension;
    }

    /**
     * @return string[]
     */
    public final static function getSupportedFormats(): array
    {
        return self::CONFIG_FORMATS;
    }

    public final static function isFormatSupported(string $format): bool
    {
        return in_array($format, self::CONFIG_FORMATS);
    }
}
