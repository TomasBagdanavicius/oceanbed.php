<?php

declare(strict_types=1);

namespace LWP\Network\Http\Cookies;

use LWP\Network\Http\Cookies\CookieStorage;
use LWP\Network\Uri\Url;
use LWP\Filesystem\Filesystem;

class CookieFileStorage extends CookieStorage
{
    public function __construct(
        private string $filename,
        URL $url
    ) {

        parent::__construct($url);

        $this->load($filename);
    }


    // Saves all changes.

    public function __destruct()
    {

        $this->save();
    }


    // Loads HTTP cookies from a file.

    public function load(string $filename): ?int
    {

        if (!str_ends_with($filename, '.json')) {
            throw new \Exception("HTTP cookie file must be a JSON file.");
        }

        Filesystem::exists($filename, "HTTP cookie storage file \"" . $filename . "\" was not found.");

        $contents = Filesystem::fileGetContents($filename, "Could not read HTTP cookie storage file \"" . $filename . "\".");

        if ($contents !== '') {

            $data = $this->unserialize($contents);

            if (!empty($data)) {

                $rejected_count = 0;

                foreach ($data as $index => $element) {

                    try {

                        $this->add($element);

                    } catch (\Throwable) {

                        $rejected_count++;
                    }
                }

                return $rejected_count;
            }
        }

        return null;
    }


    // Serializes data.

    public static function serialize(array $data): string
    {

        return json_encode($data, JSON_THROW_ON_ERROR);
    }


    // Unserializes data.

    public static function unserialize(string $data): array
    {

        return json_decode($data, associative: true, flags: JSON_THROW_ON_ERROR);
    }


    // Truncates the HTTP cookies file.

    public function truncate()
    {

        return self::storeToFile($this->filename, '');
    }


    // Saves all entries into the HTTP cookie file.

    public function save(): int
    {

        return self::storeToFile($this->filename, $this->serialize($this->getData()));
    }


    // A static method to store data to file, showing how data should be stored.

    public static function storeToFile(string $filename, string $data): int
    {

        return Filesystem::filePutContents($filename, $data, "Could not write to HTTP cookie file \"" . $filename . "\".");
    }
}
