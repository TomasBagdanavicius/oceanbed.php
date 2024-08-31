<?php

declare(strict_types=1);

$project_pathname = realpath(__DIR__ . '/..');
include $project_pathname . '/var/config.php';

require_once $user_paths['stonetable_path'] . '/src/lib/project-directory/Autoload.php';
require_once $user_paths['stonetable_path'] . '/src/web/utilities.php';

use PD\ProjectFile;
use PD\ProjectRootDirectory;

$project_pathname = realpath(__DIR__ . '/..');
$project_root_directory = new ProjectRootDirectory(
    $project_pathname,
    // Enable "App" special comment.
    on_special_comment_setup: on_special_comment_setup(...)
);

/* Source Files */

$source_file_iterator = $project_root_directory
    ->getSourceFileRecursiveIterator();
$remove_count = 0;
$remove_error_count = 0;

foreach ($source_file_iterator as $project_file_object) {

    if ($project_file_object instanceof ProjectFile) {

        $remove = $project_file_object->removeAllSpecialCommentLines();

        if (!$remove) {

            echo sprintf(
                "Could not remove %s\n",
                $project_file_object->getRelativePathName()
            );

            $remove_error_count++;

        } else {

            $remove_count++;
        }

        $project_file_object->fileClose();
    }
}

echo sprintf(
    "Comments removed from source files: %d\n",
    $remove_count
);

echo sprintf(
    "Error count in source files: %d\n",
    $remove_error_count
);

/* Demo Files */

$demo_file_iterator = $project_root_directory->getDemoFileRecursiveIterator();
$remove_count = 0;
$remove_error_count = 0;

foreach ($demo_file_iterator as $project_file_object) {

    if ($project_file_object instanceof ProjectFile) {

        $remove = $project_file_object->removeAllSpecialCommentLines();

        if (!$remove) {

            echo sprintf(
                "Could not remove %s\n",
                $project_file_object->getRelativePathName()
            );

            $remove_error_count++;

        } else {

            $remove_count++;
        }

        $project_file_object->fileClose();
    }
}

echo sprintf(
    "Comments removed from demo files: %d\n",
    $remove_count
);

echo sprintf(
    "Error count in demo files: %d\n",
    $remove_error_count
);

/* Unit Files */

$unit_file_iterator = $project_root_directory->getUnitsFileRecursiveIterator();
$remove_count = 0;
$remove_error_count = 0;

foreach ($unit_file_iterator as $project_file_object) {

    if ($project_file_object->isFile() && $project_file_object instanceof ProjectFile) {

        $project_file_object->fileOpen();
        $remove = $project_file_object->removeAllSpecialCommentLines();

        if (!$remove) {

            echo sprintf(
                "Could not remove %s\n",
                $project_file_object->getRelativePathName()
            );

            $remove_error_count++;

        } else {

            $remove_count++;
        }

        $project_file_object->fileClose();
    }
}

echo sprintf(
    "Comments removed from unit files: %d\n",
    $remove_count
);

echo sprintf(
    "Error count in unit files: %d\n",
    $remove_error_count
);
