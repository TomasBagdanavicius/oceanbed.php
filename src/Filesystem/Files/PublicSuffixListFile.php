<?php

/* Theoretically, this file could have been created as iterator wrapper, but it was chosen to extend the "File" class instead in order to emphasize that it's dealing with a file object, eg. retain other file methods, such as `getSize()`, etc. */

declare(strict_types=1);

namespace LWP\Filesystem\Files;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\FilePath;
use LWP\Common\String\Str;
use LWP\Common\Iterators\ColumnDataIteratorInterface;

class PublicSuffixListFile extends File implements ColumnDataIteratorInterface
{
    /* In some comment lines the text follows the comment tag without any whitespace in between. */
    public const COMMENT_LINE_PREFIX = '//';
    public const BEGIN_CATEGORY_DELIMITER = '// ===BEGIN';
    public const IDN_CCTLDS_DELIMITER = '// IDN ccTLDs';

    // Will store the current domain category - "ICANN" (ICANN domain) or "PRIVATE" (private domain).
    private string $domain_category;
    // Will tell if it's currently reading through internationalized country code top-level domains (IDN ccTLDs).
    // Public suffix list file contains a section "// IDN ccTLDs" where it provides country info for a domain.
    private bool $is_idn_cctlds_range = false;
    // While in IDN ccTLDs range, will store domains categorized by ISO 3166 country code.
    private array $idn_cache_data = [];
    // Current IDN ccTLD country code info.
    private ?array $idn_country_code_info = null;
    // Whether the previous ccTLD matches the current one.
    private bool $idn_content_match;
    private ?string $a_label;
    private static array $default_column_data;


    public function __construct(
        FilePath $file_path,
    ) {

        parent::__construct($file_path);

        // Empty lines don't play any role. Drop new line chars as well.
        $this->setFlags(\SplFileObject::READ_AHEAD | \SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY);

        self::$default_column_data = self::getDefaultColumnData();
    }


    // Gets column list.

    public function getColumnList(): array
    {

        return [
            'u_label' => 'U Label',
            'category' => 'Domain Category',
            'iso_3166_1_alpha_2_code' => 'Country Code',
            'a_label' => 'A Label',
        ];
    }


    // Gets default column data.

    public function getDefaultColumnData(): array
    {

        return array_fill_keys(array_keys($this->getColumnList()), '');
    }


    // Returns the current element data.

    public function current(): string|array|false
    {

        $current = trim(parent::current());

        $result = [
            'u_label' => $current,
            'category' => $this->domain_category,
        ];

        // We have an IDN ccTLD entry.
        if ($this->is_idn_cctlds_range) {

            // Store into cache container to be used in validation.
            $this->idn_cache_data[$this->idn_country_code_info[0]][] = $current;
            $result['iso_3166_1_alpha_2_code'] = $this->idn_country_code_info[0]; // ISO 3166 country code.
            $result['a_label'] = $this->idn_country_code_info[1];
        }

        if (!empty($this->a_label)) {
            $result['a_label'] = $this->a_label;
        }

        return $result;
    }


    // Checks if current position is valid.

    public function valid(): bool
    {

        $current = trim(parent::current());

        // Skip comment lines, but process the info inside them.
        while (!$this->eof() && ($is_comment_line = self::isCommentLine($current))) {

            if (!empty($this->a_label)) {
                $this->a_label = null;
            }

            // Categories are delimited by a special prefix in a comment line.
            if (self::isSectionStartLine($current)) {

                $this->domain_category = trim(Str::rtrimSubstring(trim(substr($current, 11), '='), 'DOMAINS'));

                // Marks the start of "IDN ccTLDs" section. Although, there will be no ending mark.
            } elseif (self::isIDNccTLDsSectionStartLine($current)) {

                $this->is_idn_cctlds_range = true;

                // Currently inside "IDN ccTLDs" section.
            } elseif ($this->is_idn_cctlds_range) {

                $comment_content = trim(substr($current, 2));

                // Capture ISO 3166 country code from the comment line, which starts a new ccTLD.
                // 1 - A-Label, 2 - language name, 3 - ISO 3166 ccTLD
                if (str_starts_with($comment_content, 'xn--') && preg_match('/^(xn--[^\s]*).+\(".+",\s+(.+)\)\s+:\s+(.*)/', $comment_content, $matches)) {

                    // Another portion of ccTLDs with the same ISO 3166 code.
                    if (!is_null($this->idn_country_code_info) && $this->idn_country_code_info[0] == $matches[3]) {

                        $this->idn_content_match = true;

                        // However, the a-label and language name can change.
                        $this->idn_country_code_info[1] = $matches[1];
                        $this->idn_country_code_info[2] = $matches[2];

                        // A different ccTLD.
                    } else {

                        $this->idn_country_code_info = [
                            $matches[3], // ISO 3166 code
                            $matches[1], // A-Label
                            $matches[2], // language name
                        ];

                        $this->idn_content_match = false;
                    }

                    // Since there is no ending mark for the "IDN ccTLDs" section, it needs to be autodetected.
                } elseif (!is_null($this->idn_country_code_info) && !empty($this->idn_cache_data[$this->idn_country_code_info[0]]) && empty($this->idn_content_match)) {

                    $this->is_idn_cctlds_range = false;
                }

                // In the "// newGTLDs" section there are a bunch of A-label comment succeeded entries.
            } elseif (str_starts_with($current, '// xn--')) {

                $line_no_comment_prefix = substr($current, 3);

                $this->a_label = (($pos = strpos($line_no_comment_prefix, " ")) !== false)
                    ? substr($line_no_comment_prefix, 0, $pos)
                    : $line_no_comment_prefix;
            }

            $this->next();

            $current = parent::current();

            if (is_string($current)) {
                $current = trim($current);
            }
        }

        return parent::valid();
    }


    // Tells if the given line starts a new section (eg. ICANN or PRIVATE).

    public static function isSectionStartLine(string $line): bool
    {

        return (str_starts_with($line, self::BEGIN_CATEGORY_DELIMITER));
    }


    // Tells if the given line starts the IDN ccTLDs section.

    public static function isIDNccTLDsSectionStartLine(string $line): bool
    {

        return (str_starts_with($line, self::IDN_CCTLDS_DELIMITER));
    }


    // Tells is the given line qualifies as a comment line.

    public static function isCommentLine(string $line): bool
    {

        return (str_starts_with($line, self::COMMENT_LINE_PREFIX));
    }
}
