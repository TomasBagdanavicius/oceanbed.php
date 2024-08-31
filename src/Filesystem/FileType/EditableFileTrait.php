<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType;

use LWP\Filesystem\Filesystem;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Exceptions\NotFoundException;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Filesystem\Exceptions\FileRenameError;
use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Enums\FilesystemStatsStatusEnum;
use LWP\Filesystem\Enums\FileActionEnum;
use LWP\Filesystem\Enums\DuplicateHandlingOptionsEnum;
use LWP\Filesystem\Exceptions\DuplicateFileException;
use LWP\Filesystem\Path\FilePath;

trait EditableFileTrait
{
    public array $custom_data = [];


    // Gets information about the file

    public function getInfo(): array
    {

        return $this->getIndexableData();
    }


    // Adds statistics entry for this file

    protected function addStats(
        FilesystemStatsStatusEnum $status,
        FileActionEnum $file_action,
        string $pathname = null,
        array $extra_params = [],
    ): void {

        if ($this->filesystem_stats) {

            $method_name = match ($status) {
                FilesystemStatsStatusEnum::SUCCESS => 'registerSuccess',
                FilesystemStatsStatusEnum::FAILURE => 'registerFailure',
                FilesystemStatsStatusEnum::FOUND => 'registerFound',
                FilesystemStatsStatusEnum::NOT_FOUND => 'registerNotFound',
            };

            $this->filesystem_stats->{$method_name}(
                $file_action,
                ($pathname ?? $this->pathname),
                ...$extra_params
            );
        }
    }


    // Renames the file

    public function rename(
        string $new_name,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        // Note: number zero can be a valid file name
        if ($new_name === '') {
            throw new \ValueError("File name cannot be empty");
        }

        $file_action = FileActionEnum::RENAME;
        $new_pathname = ($this->getPath() . $this->file_path->getDefaultSeparator() . $new_name);
        $new_file_path = $this->file_path->fromSelf($new_pathname);

        if ($new_file_path->existsInFilesystem()) {

            $handle_existing = $this->handleExistingFile($file_action, $new_file_path, $duplicate_handling, $duplicate_pattern);

            if ($handle_existing) {

                $new_file_path = $handle_existing;
                $new_pathname = $new_file_path->__toString();
            }
        }

        $rename = rename($this->pathname, $new_pathname);

        if ($rename) {
            $this->addStats(FilesystemStatsStatusEnum::SUCCESS, $file_action, extra_params: [$new_pathname]);
        } else {
            $this->addStats(FilesystemStatsStatusEnum::FAILURE, $file_action, extra_params: [$new_pathname]);
            throw new FileRenameError(sprintf(
                "Could not rename %s to %s",
                $this->pathname,
                $new_pathname
            ));
        }

        return ($this instanceof File)
            ? new File($new_file_path, $this->filesystem_stats, $this->flags)
            : new Directory($new_file_path, $this->filesystem_stats);
    }


    //

    public function handleExistingFile(
        FileActionEnum $file_action,
        FilePath $file_path,
        DuplicateHandlingOptionsEnum $duplicate_handling,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): ?FilePath {

        $pathname = $file_path->__toString();
        $this->addStats(FilesystemStatsStatusEnum::FOUND, $file_action, $pathname);

        // Abort
        if ($duplicate_handling === DuplicateHandlingOptionsEnum::ABORT) {

            $this->addStats(FilesystemStatsStatusEnum::FAILURE, $file_action, extra_params: [$pathname]);

            throw new DuplicateFileException(sprintf(
                "File %s already exists",
                $pathname
            ));

            // Keep both
        } elseif ($duplicate_handling === DuplicateHandlingOptionsEnum::KEEP_BOTH) {

            return Filesystem::buildUniqueBasename($file_path, $duplicate_pattern);

            // Replace
        } else {

            return null;
        }
    }


    // Returns the list of indexable properties

    public function getIndexablePropertyList(): array
    {

        return [
            'type',
            'size',
            'real_size',
            'path',
            'pathname',
            'name',
            'basename',
            'filename',
            'extension',
            'date_last_modified',
            'count'
        ];
    }


    // Returns value of a given indexable property

    public function getIndexablePropertyValue(string $property_name): mixed
    {

        $this->assertIndexablePropertyExistence($property_name);

        return match ($property_name) {
            'type' => $this->getType(),
            'size' => $this->getSize(),
            'real_size' => $this->getRealSize(),
            'path' => $this->getPath(),
            'pathname' => $this->getPathname(),
            'name' => $this->getPathname(),
            'basename' => $this->getBasename(),
            'filename' => $this->getFilename(),
            'extension' => $this->getExtension(),
            'date_last_modified' => $this->getMTime(),
            'count' => $this->count()
        };
    }


    //

    public function setCustomData(int|string $key, mixed $value): void
    {

        $this->custom_data[$key] = $value;
    }


    // Tells whether file matches given condition

    public function matchCondition(Condition $condition): bool
    {

        $property_name = $condition->keyword;

        // The special RCTE iterator
        if ($property_name === 'rcte_i') {
            $this->setCustomData('rcte_id', $condition->value);
            return true;
        }

        $this->assertIndexablePropertyExistence($property_name);
        $property_value = $this->getIndexablePropertyValue($property_name);

        return $condition->matchValue($property_value);
    }


    // Tells whether file matches given condition group

    public function matchConditionGroup(ConditionGroup $condition_group): bool
    {

        return $condition_group->reactiveMatch(function (Condition $condition): bool {
            return $this->matchCondition($condition);
        });
    }


    // Updates a file from array data

    public function update(array $data)
    {

        $accepted_keys = [
            'basename',
            'filename',
            'extension',
        ];
        $diff = array_diff(array_keys($data), $accepted_keys);

        if ($diff) {
            throw new \Exception(sprintf(
                "Some elements are not required: %s",
                ('"' . implode('", "', $diff) . '"')
            ));
        }

        $has_basename = isset($data['basename']);
        $has_filename = isset($data['filename']);
        $has_extension = isset($data['extension']);

        if ($has_basename) {

            [
                'filename' => $filename,
                'extension' => $extension,
            ] = Path::parseBasename($data['basename']);

            // Compare filename and extension components
            if (
                ($has_filename && $has_extension && Path::buildBasename($data['filename'], $data['extension']) !== $data['basename'])
                || $has_filename && $data['filename'] !== $filename
                || $has_extension && $data['extension'] !== $extension
            ) {
                throw new \Exception("When basename is given, filename and extension should be absent");
            }
        }

        if ($has_basename) {

            $args = [
                $data['basename'],
            ];

            if ($this instanceof File) {
                $args['with_extension'] = true;
            }

            return $this->rename(...$args);

        } elseif ($this instanceof File) {

            if ($has_filename && $has_extension) {
                return $this->rename(File::buildBasename($data['filename'], $data['extension']), with_extension: true);
            } elseif ($has_filename) {
                return $this->rename($data['filename']);
            } elseif ($has_extension) {
                return $this->changeExtension($data['extension']);
            }

        } elseif ($this instanceof Directory) {

            if ($has_filename) {
                return $this->rename($data['filename']);
            }
        }
    }
}
