<?php

declare(strict_types=1);

namespace LWP\Filesystem;

use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Filesystem\FileType\File;
use LWP\Filesystem\FileType\Directory;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class FileCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct(
            $data,
            element_filter: function (
                mixed $element,
                null|int|string $key
            ): true {

                if (!($element instanceof File) && !($element instanceof Directory)) {
                    throw new InvalidMemberException(sprintf(
                        "Collection %s accepts elements of class %s only",
                        self::class,
                        (File::class . ' or ' . Directory::class)
                    ));
                }

                return true;
            },
            // Use element class name as the name identifier in the collection.
            obtain_name_filter: function (mixed $element): ?string {

                if ($element instanceof Relationship) {
                    return $element::class;
                }

                return null;
            }
        );
    }


    // Creates a new file instance and attaches it to this collection.

    public function createNewMember(array $params = []): File
    {

        #todo: add option to construct directory
        $file = new File(...$params);
        $index = $this->add($file);
        $file->registerCollection($this, $index);

        return $file;
    }


    //

    public function addFromDirectory(Directory $directory, bool $recurse = false): void
    {

        #tbd
    }
}
