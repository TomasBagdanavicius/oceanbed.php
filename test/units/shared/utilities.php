<?php

declare(strict_types=1);

function generateTempFilePathname(string $path, false|string $extension = '.txt'): string
{
    do {
        $pathname = ($path . '/tmpfile-' . mt_rand());
        if ($extension) {
            $pathname .= $extension;
        }
    } while (file_exists($pathname));
    return $pathname;
}

function createTempFile(string $path): array
{
    $pathname = generateTempFilePathname($path);
    $handle = fopen($pathname, 'w');
    if (!$handle) {
        throw new \RuntimeException(sprintf(
            "Could not open file handle to %s",
            $pathname
        ));
    }
    return [$pathname, $handle];
}

function createTempDir(string $path): string
{
    $pathname = generateTempFilePathname($path, false);
    if (!mkdir($pathname)) {
        throw new \RuntimeException(sprintf(
            "Could not create directory %s",
            $pathname
        ));
    }
    return $pathname;
}

function getFilesystemBinDirPathname(): string
{
    return realpath(__DIR__ . '/../../bin/filesystem');
}

function getFilesystemBinTmpDirPathname(): string
{
    return (getFilesystemBinDirPathname() . '/tmp');
}

function getLocationInFilesystemBin(string $relative_path): string
{
    $pathname = (getFilesystemBinDirPathname() . DIRECTORY_SEPARATOR . ltrim($relative_path, DIRECTORY_SEPARATOR));
    if (!file_exists($pathname)) {
        throw new \RuntimeException(sprintf(
            "File %s does not exist",
            $pathname
        ));
    }
    return $pathname;
}

function createTempFileInFilesystemBin(): array
{
    return createTempFile(getFilesystemBinTmpDirPathname());
}

function createTempDirInFilesystemBin(): string
{
    return createTempDir(getFilesystemBinTmpDirPathname());
}

function closeFileHandle($file_handle, string $pathname): void
{
    if (!fclose($file_handle)) {
        throw new \RuntimeException(sprintf(
            "Could not close file handle for file %s",
            $pathname
        ));
    }
}

function deleteFile(string $pathname): void
{
    if (!unlink($pathname)) {
        throw new \RuntimeException(sprintf(
            "Could not delete file %s",
            $pathname
        ));
    }
}

function deleteDir(string $pathname): void
{
    if (!rmdir($pathname)) {
        throw new \RuntimeException(sprintf(
            "Could not delete directory %s",
            $pathname
        ));
    }
}

function calculateAge(string $date_of_birth)
{
    $current_date = date("Y-m-d");
    $diff = date_diff(date_create($date_of_birth), date_create($current_date));
    return (int)$diff->format('%y');
}

function generateUniqueDatabaseName(mysqli $db_link, string $base_name = 'database'): string
{
    do {
        // Since create/drop database command are not transactional, we have to rely on unique ID, which should be random enough in parallel calls
        $database_name = $base_name . uniqid();
        $result = $db_link->execute_query("SELECT `SCHEMA_NAME` FROM `INFORMATION_SCHEMA`.SCHEMATA WHERE `SCHEMA_NAME` = ? LIMIT 1", [$database_name]);
    } while ($result->num_rows);
    return $database_name;
}
