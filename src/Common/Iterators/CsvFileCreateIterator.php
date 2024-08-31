<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\FileType\FileFormats\FileCsv;
use LWP\Filesystem\FileType\FileFormats\Exceptions\CsvFileWriteError;

class CsvFileCreateIterator extends \IteratorIterator
{
    private FileCsv $file;


    public function __construct(
        /* The rule is that the inner iterator must be an instance of "ColumnDataIteratorInterface". */
        ColumnDataIteratorInterface $iterator,
        private FilePath $file_path,
        /* A special map of values that should be replaced with chosen replacements. Note: the replacement is stored in the key part and the value is compared with an additional type checking (to support special values). */
        private ?array $replace_type = null,
        /* Inner enclosure, unless the field value is numeric or it has been replaced with a chosen value. */
        private ?string $inner_enclosure = null,
        ?string $class = null,
    ) {

        parent::__construct($iterator, $class);
    }


    // Sets the delimiter, enclosure and escape characters.

    public function setControl(string $separator = ',', string $enclosure = '"', string $escape = '\\'): void
    {

        $this->separator = $separator;
        $this->enclosure = $enclosure;
        $this->escape = $escape;

        $this->file->setCsvControl($separator, $enclosure, $escape);
    }


    // Initiates the file instance.

    public function rewind(): void
    {

        /* It make more sense to initiate or create the file here instead of inside "__construct", because if the iterator never gets run, an empty or incomplete file artifact won't be created. */
        $this->file = new FileCsv($this->file_path, 'w');
        $this->file->fputcsv($this->getInnerIterator()->getColumnList()); // "getColumnList" method by rule should be implemented in "ColumnDataIteratorInterface".

        parent::rewind();
    }


    // This function primarily deals with situations when one has to discern special values (eg. null, false) from their string representations (eg. "null", "false"). It assumes that there is a distinction.

    public function replaceTypeValue(mixed $var)
    {

        $replace_found = false;

        if ($this->replace_type) {

            // The replacement is in the key part.
            foreach ($this->replace_type as $replacement => $search) {

                // Additional type checking.
                if ($var === $search) {

                    $replace_found = true;
                    $var = $replacement;

                    break;
                }
            }

        }

        // Will not add inner enclosure when field value is numeric or it has been replaced with chosen value.
        if (!$replace_found && $this->inner_enclosure && !is_numeric($var)) {

            $var = ($this->inner_enclosure . $var . $this->inner_enclosure);
        }

        return $var;
    }


    // Intercepts current element and writes data into the file.

    public function current(): array
    {

        $current = parent::current();

        if (is_string($current)) {

            $current = [$this->replaceTypeValue($current)];

        } elseif (!is_array($current)) {

            throw new \TypeError(sprintf("Data provided for CSV file creation must be of type array or string, \"%s\" given.", gettype($current)));

        } else {

            foreach ($current as $key => $val) {
                $current[$key] = $this->replaceTypeValue($val);
            }
        }

        // Returns the length of the written string or false on failure.
        if (!$this->file->fputcsv($current)) {

            throw new CsvFileWriteError(sprintf("Could not write CSV data to file \"%s\".", $this->file->getFilename()));
        }

        return $current;
    }
}
