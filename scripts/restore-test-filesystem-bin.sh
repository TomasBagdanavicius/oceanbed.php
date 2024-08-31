#!/usr/bin/env bash

dir="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
project_dir=$(readlink -f "$dir/..")

bin_pathname=$project_dir/test/bin/filesystem

# Restore "to-delete.txt" file
file_to_delete_pathname=$bin_pathname/tmp/to-delete.txt
if [ ! -f $file_to_delete_pathname ]; then
    echo "TO BE DELETED" > $file_to_delete_pathname
fi

# Restore "to-truncate.txt" file
file_to_truncate_pathname=$bin_pathname/tmp/to-truncate.txt
source_file_pathname=$bin_pathname/original/to-truncate.txt
if [ ! -s file_to_truncate_pathname ]; then
    cat $source_file_pathname > $file_to_truncate_pathname
fi

# Sync source dir to the "to-truncate" dir
# Trailing slash is important to make it sync contents
rsync -av --delete $bin_pathname/read/ $bin_pathname/tmp/to-truncate/

# Restore "to-delete" dir
dir_to_delete_pathname=$bin_pathname/tmp/to-delete
if [ ! -f $dir_to_delete_pathname ]; then
    mkdir $dir_to_delete_pathname
fi
rsync -av --delete $bin_pathname/read/ $dir_to_delete_pathname/

# Empty "create-inside" directory
dir_create_inside_pathname=$bin_pathname/tmp/create-inside
rm -rf "$dir_create_inside_pathname"/*

# Remove temporary files
for file in $bin_pathname/tmp/*; do
    basename=$(basename "$file")
    if [[ $basename == tmpfile-* ]]; then
        rm "$file"
    fi
done

# Delete copied file
rm $bin_pathname/tmp/copy-destination/copied.txt

# Cleanup "duplicate-inside" directory
dir_duplicate_inside_pathname=$bin_pathname/tmp/duplicate-inside
files_to_keep="to-duplicate.txt"
# Delete all files except the ones we want to keep
find "$dir_duplicate_inside_pathname" -type f ! -name "$files_to_keep" -delete

# Move back "moved.txt"
moved_file_pathname=$bin_pathname/tmp/move-destination/moved.txt
mv "$moved_file_pathname" "$bin_pathname/tmp/to-move.txt"

moved_file_pathname=$bin_pathname/tmp/move-destination/to-move.txt
mv "$moved_file_pathname" "$bin_pathname/tmp"

# Rename
renamed_file_pathname=$bin_pathname/tmp/new_name.txt
if test -f $renamed_file_pathname; then
    mv $renamed_file_pathname $bin_pathname/tmp/to-rename.txt
fi
renamed_file_pathname=$bin_pathname/tmp/new_name
if test -d $renamed_file_pathname; then
    mv $renamed_file_pathname $bin_pathname/tmp/to-rename
else
    echo "Not found\n"
fi