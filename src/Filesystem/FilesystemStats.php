<?php

declare(strict_types=1);

namespace LWP\Filesystem;

use LWP\Common\Enums\StatusEnum;
use LWP\Filesystem\Path\FilePath;
use LWP\Common\FileLogger;
use LWP\Filesystem\Enums\FileActionEnum;

class FilesystemStats
{
    protected array $results = [];
    protected array $last_results = [];
    protected bool $is_session = false;
    protected array $session = [];


    public function __construct(
        protected ?FileLogger $file_logger = null,
    ) {

        $this->results['__index'] = $this->getNewIndexDataStructure();
    }


    //

    public function getResults(): array
    {

        return $this->results;
    }


    //

    public function getLastResults(): array
    {

        return $this->last_results;
    }


    //

    public function resetLastResults(): void
    {

        $this->last_results = [];
    }


    //

    public function startSession(): void
    {

        $this->is_session = true;
        $this->session = [
            '__index' => $this->getNewIndexDataStructure(),
        ];
    }


    //

    public function isInSession(): bool
    {

        return $this->is_session;
    }


    //

    public function getSessionResults(): array
    {

        return $this->session;
    }


    //

    public function endSession(): array
    {

        $this->is_session = false;
        $session_results = $this->getSessionResults();
        $this->session = [];

        return $session_results;
    }


    //

    public function registerSuccess(
        FileActionEnum $file_action,
        string|FilePath $filename,
        null|string|FilePath $to_filename = null,
    ): void {

        $this->registerResult($file_action, 'success_count');

        $text_message = FilesystemStats::fileActionToTextMessage(
            $file_action,
            StatusEnum::SUCCESS,
            (string)$filename,
            $to_filename ? [(string)$to_filename] : null
        );

        $this->logMessage($text_message);
    }


    //

    public function registerFailure(
        FileActionEnum $file_action,
        string|FilePath $filename,
        null|string|FilePath $to_filename = null,
    ): void {

        $this->registerResult($file_action, 'failure_count');

        $text_message = FilesystemStats::fileActionToTextMessage(
            $file_action,
            StatusEnum::ERROR,
            (string)$filename,
            $to_filename ? [(string)$to_filename] : null
        );

        $this->logMessage($text_message);

        $file_action_name = $file_action->name;
        $filename = (string)$filename;
        $this->results[$file_action_name]['failure_files'][$filename] = $filename;
        $this->last_results['failure_files'][$filename] = $filename;
        $dirname = dirname($filename);
        $this->results[$file_action_name]['failure_dirs'][$dirname] = $dirname;
        $this->last_results['failure_dirs'][$dirname] = $dirname;
    }


    //

    public function registerFound(
        FileActionEnum $file_action,
        string|FilePath $filename
    ): void {

        $this->registerResult($file_action, 'found_count');

        $text_message = sprintf("File %s was found", $filename);

        $this->logMessage($text_message);
    }


    //

    public function registerNotFound(
        FileActionEnum $file_action,
        string|FilePath $filename
    ): void {

        $this->registerResult($file_action, 'not_found_count');

        $text_message = sprintf("File %s was not found", $filename);

        $this->logMessage($text_message);
    }


    //

    protected function registerResult(
        FileActionEnum $file_action,
        string $count_name
    ): void {

        $file_action_name = $file_action->name;

        $this->last_results = $this->getNewResultsDataStructure();
        $this->registerActionOn($this->results, $file_action);

        $this->results['__index'][$count_name]++;
        $this->results[$file_action_name][$count_name]++;
        $this->last_results['file_action'] = $file_action;
        $this->last_results[$count_name]++;

        if ($this->isInSession()) {

            $this->registerActionOn($this->session, $file_action);
            $this->session['__index'][$count_name]++;
            $this->session[$file_action_name][$count_name]++;
        }
    }


    //

    protected function registerActionOn(array &$array, FileActionEnum $file_action)
    {

        $file_action_name = $file_action->name;

        if (!isset($array[$file_action_name])) {

            $array[$file_action_name] = $this->getNewResultsDataStructure();
            $array[$file_action_name]['file_action'] = $file_action;
        }
    }


    //

    public function getSummaryText(bool $meta_format = false, bool $split_to_lines = false): string
    {

        $index = &$this->results['__index'];

        if (!$meta_format) {

            $sentences = [
                'success_count' => sprintf(
                    "%d successful operation%s.",
                    $index['success_count'],
                    $index['success_count'] === 1 ? '' : 's'
                ),
                'failure_count' => sprintf(
                    "%d failure%s.",
                    $index['failure_count'],
                    $index['failure_count'] === 1 ? '' : 's'
                ),
                'found_count' => sprintf(
                    "%d file%s found.",
                    $index['found_count'],
                    $index['found_count'] === 1 ? '' : 's'
                ),
                'not_found_count' => sprintf(
                    "%d file%s not found.",
                    $index['not_found_count'],
                    $index['not_found_count'] === 1 ? '' : 's'
                ),
            ];

            return (!$split_to_lines)
                ? implode(' ', $sentences)
                : implode("\n", $sentences);

        } else {

            $sentences = [
                sprintf("Successful operations: %d", $index['success_count']),
                sprintf("Failures: %d", $index['failure_count']),
                sprintf("Found: %d", $index['found_count']),
                sprintf("Not found: %d", $index['not_found_count']),
            ];

            return (!$split_to_lines)
                ? implode(". ", $sentences)
                : implode("\n", $sentences);
        }
    }


    //

    public function logMessage(string $text_message): void
    {

        if ($this->file_logger) {
            $this->file_logger->logText($text_message);
        }
    }


    // Creates new instance by param option.

    public static function getInstanceByParam(?self $filesystem_stats = null): self
    {

        return ($filesystem_stats ?: new self());
    }


    //

    public static function getNewIndexDataStructure(): array
    {

        return [
            'success_count' => 0,
            'failure_count' => 0,
            'found_count' => 0,
            'not_found_count' => 0,
        ];
    }


    //

    public static function getNewResultsDataStructure(): array
    {

        return [
            'file_action' => null,
            'success_count' => 0,
            'failure_count' => 0,
            'found_count' => 0,
            'not_found_count' => 0,
            'failure_files' => [],
            'failure_dirs' => [],
        ];
    }


    //

    public static function getFileActionWords(FileActionEnum $file_action): array
    {

        return match ($file_action) {
            FileActionEnum::CREATE => ["Create", "Created"],
            FileActionEnum::RENAME => ["Rename", "Renamed"],
            FileActionEnum::DELETE => ["Delete", "Deleted"],
            FileActionEnum::DUPLICATE => ["Duplicate", "Duplicated"],
            FileActionEnum::TRUNCATE => ["Truncate", "Truncated"],
            FileActionEnum::COPY => ["Copy", "Copied"],
            FileActionEnum::COPY_TO => ["Copy", "Copied"],
            FileActionEnum::MOVE => ["Move", "Moved"],
            FileActionEnum::MOVE_TO => ["Move", "Moved"],
            FileActionEnum::CUT => ["Cut", "Cut"],
            FileActionEnum::COMPRESS => ["Compress", "Compressed"],
            FileActionEnum::ZIP => ["Zip", "Zipped"],
            FileActionEnum::UNZIP => ["Unzip", "Unzipped"],
        };
    }


    //

    public static function fileActionToTextMessage(
        FileActionEnum $file_action,
        StatusEnum $status,
        string $filename,
        ?array $extra = null,
    ): string {

        $words = self::getFileActionWords($file_action);

        if ($status === StatusEnum::SUCCESS) {
            $msg_format = "%s file %s";
            $msg_format_extended = "%s file %s to %s";
            $word = $words[1];
        } else {
            $msg_format = "Failed to %s file %s";
            $msg_format_extended = "Failed to %s file %s to %s";
            $word = strtolower($words[0]);
        }

        $regular_format = fn (string $word): string => sprintf($msg_format, $word, $filename);
        $extended_format = fn (string $word): string => (($extra && isset($extra[0]))
            ? sprintf($msg_format_extended, $word, $filename, $extra[0])
            : sprintf($msg_format, $word, $filename));

        return match ($file_action) {
            FileActionEnum::CREATE => $regular_format($word),
            FileActionEnum::RENAME => $extended_format($word),
            FileActionEnum::DELETE => $regular_format($word),
            FileActionEnum::DUPLICATE => $extended_format($word),
            FileActionEnum::TRUNCATE => $regular_format($word),
            FileActionEnum::COPY => $regular_format($word),
            FileActionEnum::COPY_TO => $extended_format($word),
            FileActionEnum::MOVE => $regular_format($word),
            FileActionEnum::MOVE_TO => $extended_format($word),
            FileActionEnum::CUT => $regular_format($word),
            FileActionEnum::COMPRESS => $extended_format($word),
            FileActionEnum::ZIP => $extended_format($word),
            FileActionEnum::UNZIP => $regular_format($word),
        };
    }
}
