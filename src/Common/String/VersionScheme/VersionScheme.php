<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

use LWP\Common\OptionManager;
use LWP\Common\String\Clause\SortByComponent;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\sortByColumns;

class VersionScheme implements \Stringable
{
    public const DEFAULT_VERSION_NUMBER_PREFIX = 'v';
    public const COMPARE_RESULT_LOWER = 0;
    public const COMPARE_RESULT_EQUAL = 1;
    public const COMPARE_RESULT_HIGHER = 2;

    protected OptionManager $options;


    public function __construct(
        protected object $structure,
        ?array $opts = null,
    ) {

        $this->options = ($opts)
            ? new OptionManager($opts, self::getDefaultOptions())
            : new OptionManager(VersionSchemeSemanticVersioning::getDefaultOptions(), self::getDefaultOptions());

        $version_number_part_defs = $this->options->version_number_part_defs;

        // Sort by position.
        $this->options->version_number_part_defs = array_values(sortByColumns(
            $version_number_part_defs,
            ['pos' => array_column($version_number_part_defs, 'pos')],
            SortByComponent::fromString("pos ASC")
        ));

        $this->structure = $structure;
    }


    //

    final public function getObject(): self
    {

        return $this;
    }


    //

    public function getOptions(): OptionManager
    {

        return $this->options;
    }


    //

    public function getStructure(): object
    {

        return $this->structure;
    }


    //

    public function __toString(): string
    {

        return $this->createSchemeString();
    }


    // Get full scheme as numbers with dot dividers (including pre-release tag), no matter what options are used.

    public function getAsNumberString(): string
    {

        $version_number_parts = $this->getVersionNumberParts();
        $str = implode('.', $version_number_parts);

        if ($tag_status = $this->getPreReleaseTagStatus()) {

            $str .= ('.' . $tag_status['numeric_label']);

            if ($pre_release_version_number = $this->getPreReleaseVersionNumber()) {

                $str .= ('.' . $pre_release_version_number);

            } elseif ($tag_status['label'] == 'Final') {

                $str .= '.0';
            }
        }

        return $str;
    }


    // Compare with another scheme and find out if the latter is lower, equal, or higher.

    public function compareWith(string|self $compare_scheme_string): false|\LWP\Common\UniformityComparisonResult
    {

        $current_scheme_string = $this->getAsNumberString();

        if (is_object($compare_scheme_string) && method_exists($compare_scheme_string, 'getAsNumberString')) {

            $compare_scheme_string = $compare_scheme_string->getAsNumberString();

        } elseif (!is_string($compare_scheme_string)) {

            return false;
        }

        $comparison = new VersionSchemeUniformityComparison($current_scheme_string);

        return $comparison->getResult($compare_scheme_string);
    }


    //

    public static function getDefaultVersionPartDefs(): array
    {

        return [
            'min' => 0,
            'max' => false,
            'omit_zero' => false,
        ];
    }


    // Returns the default options array.

    public static function getDefaultOptions(): array
    {

        return [
            'version_number_prefix' => false, // Ehether a string prefix is prepended to the version number.
            'version_number_divider' => '.', // Character dividing version number parts.
            'pre_release_tag' => true, // Whether the scheme contains a pre-release tag at the end.
            'pre_release_tag_from' => null, // Since which version can the pre-release tag be appended.
            'pre_release_tag_prefix' => '-', // Character which is used to divide pre-release tag from the version number.
            'pre_release_tag_label' => 'string', // Whether the label should be a string or a number.
            'pre_release_tag_divider' => '', // Divider between the label and the iteration number.
            'pre_release_tag_shortening' => true, // Whether pre-release tag label is shortened.
            'pre_release_tag_label_final_omit' => true, // Whether to omit the pre release tag when the version status is 'final'.
        ];
    }


    // Returns the default status definitions array.

    public static function getAllStatuses(): array
    {

        return [
            [
                'label' => 'Alpha',
                'numeric_label' => '0',
                'tag_names' => [
                    'a' => 'alpha',
                ],
                'required_version_number' => true,
            ], [
                'label' => 'Beta',
                'numeric_label' => '1',
                'tag_names' => [
                    'b' => 'beta',
                ],
                'required_version_number' => true,
            ], [
                'label' => 'Release Candidate',
                'numeric_label' => '2',
                'tag_names' => [
                    'rc' => 'release-candidate',
                ],
                'required_version_number' => true,
            ], [
                'label' => 'Final',
                'numeric_label' => '3',
                'tag_names' => [
                    'r' => 'release',
                    'f' => 'final',
                ],
                'required_version_number' => false,
            ],
        ];
    }


    //

    public function getStructureElement(string $name): mixed
    {

        return (property_exists($this->structure, $name))
            ? $this->structure->$name
            : null;
    }


    //

    public function getVersionNumber(): string
    {

        return implode($this->options->version_number_divider, $this->getVersionNumberParts());
    }


    //

    public function getVersionNumberFull(): string
    {

        if ($this->options->version_number_prefix === true) {
            $number = self::DEFAULT_VERSION_NUMBER_PREFIX;
        } elseif (is_string($this->options->version_number_prefix)) {
            $number = $this->options->version_number_prefix;
        } else {
            $number = '';
        }

        return ($number . $this->getVersionNumber());
    }


    //

    public function getVersionNumberPrefix(): string
    {

        return $this->getStructureElement('version_number_prefix');
    }


    //

    public function setVersionNumberPrefix(string $version_number_prefix): void
    {

        $this->structure->version_number_prefix = $version_number_prefix;
    }


    //

    public function getVersionNumberParts(): array
    {

        return $this->getStructureElement('version_number_parts');
    }


    //

    public function getVersionNumberPartByIndex(int $index): ?int
    {

        if (!$version_number_parts = $this->getVersionNumberParts()) {
            return null;
        }

        return (isset($version_number_parts[$index]))
            ? intval($version_number_parts[$index])
            : null;
    }


    //

    public function setVersionNumberParts(array $version_number_parts): void
    {

        $this->structure->version_number_parts = $version_number_parts;
    }


    //

    public function getPreReleaseTagStatus(): ?array
    {

        return $this->getStructureElement('pre_release_tag_status');
    }


    //

    public function setPreReleaseTagStatus(array $pre_release_tag_status): void
    {

        $this->structure->pre_release_tag_status = $pre_release_tag_status;
    }


    //

    public function getPreReleaseTagStatusNeedle(): string
    {

        return $this->getStructureElement('pre_release_tag_status_needle');
    }


    //

    public function setPreReleaseTagStatusNeedle(string $pre_release_tag_status_needle): void
    {

        $this->structure->pre_release_tag_status_needle = $pre_release_tag_status_needle;
    }


    //

    public function getPreReleaseVersionNumber(): ?int
    {

        return $this->getStructureElement('pre_release_version_number');
    }


    //

    public function setPreReleaseVersionNumber(int $pre_release_version_number): void
    {

        $this->structure->pre_release_version_number = $pre_release_version_number;
    }


    //

    public function createSchemeString(): string
    {

        $scheme = $this->getVersionNumberFull();

        if ($tag_status = $this->getPreReleaseTagStatus()) {

            if ($tag_status['label'] != "Final" || ($tag_status['label'] == "Final" && !$this->options->pre_release_tag_label_final_omit)) {

                $scheme .= $this->options->pre_release_tag_prefix;

                if ($this->options->pre_release_tag_label == 'string') {

                    $scheme .= ($this->options->pre_release_tag_shortening)
                        ? key($tag_status['tag_names'])
                        : current($tag_status['numeric_label']);

                } elseif ($this->options->pre_release_tag_label == 'number') {

                    $scheme .= $tag_status['numeric_label'];
                }

                if ($iteration_number = $this->getPreReleaseVersionNumber()) {

                    $scheme .= ($this->options->pre_release_tag_divider . $iteration_number);
                }
            }
        }

        return $scheme;
    }


    //

    public function getNextOpportunities(): array
    {

        $version_number_parts = $this->options->version_number_part_defs;
        $version_number_parts_count = count($version_number_parts);
        $default_part_defs = self::getDefaultVersionPartDefs();

        // If opportunity version array is not set, it is invalid.
        // Unset is used below.
        $opportunities = array_fill(1, $version_number_parts_count, []);

        $vnp = $this->getVersionNumberParts();

        foreach ($version_number_parts as $key => $defs) {

            $defs_original = array_merge($default_part_defs, $defs);

            for ($i = 1; $i <= $version_number_parts_count; $i++) {

                if (!isset($opportunities[$i])) {
                    continue;
                }

                $defs = $defs_original;

                if (!empty($opportunities[$i])) {

                    $namespace = implode('.', $opportunities[$i]);

                    if (isset($defs['branch']) && isset($defs['branch'][$namespace])) {

                        $defs = array_merge($defs, $defs['branch'][$namespace]);
                    }

                    if (isset($defs['from'], $namespace) && $namespace < implode('.', $defs['from'])) {

                        if ($defs['pos'] == $i) {

                            unset($opportunities[$i]);
                        }

                        continue;
                    }
                }

                if ($defs['pos'] == $i) {

                    $new_i = (isset($vnp[$key]))
                        ? ($vnp[$key] + 1)
                        : 1; // assuming zero was omitted

                    if ($defs['max'] && $defs['max'] < $new_i) {

                        unset($opportunities[$i]);

                    } else {

                        $opportunities[$i][] = $new_i;
                    }

                } elseif ($i < $defs['pos']) {

                    if (!$defs['omit_zero']) {

                        $opportunities[$i][] = 0;
                    }

                } elseif (isset($vnp[$key])) {

                    $opportunities[$i][] = $vnp[$key];
                }
            }
        }

        $opportunities_count = count($opportunities);

        $result = [];

        $available_statuses = self::getAllStatuses();
        $pre_release_tag_status = $this->getPreReleaseTagStatus();

        if ($pre_release_tag_status && $pre_release_tag_status['label'] != 'Final') {

            $new = new VersionSchemeBuilder($this->getOptions()->toArray());
            $new->setVersionNumberParts($this->getVersionNumberParts());

            $result[] = $new;

            $new->setPreReleaseTagStatus($pre_release_tag_status);
            $new->setPreReleaseVersionNumber(($this->getPreReleaseVersionNumber() + 1));

            // get succeeding statuses, eg. beta would succeed with rc, etc.
            foreach ($available_statuses as $key => $status) {

                if ($status['label'] == $pre_release_tag_status['label']) {

                    $sp = array_slice($available_statuses, ($key + 1));

                    break;
                }
            }

            if (!empty($sp)) {

                foreach ($sp as $st) {

                    $new = new VersionSchemeBuilder($this->getOptions()->toArray());
                    $new->setVersionNumberParts($this->getVersionNumberParts());

                    $result[] = $new;

                    $new->setPreReleaseTagStatus($st);
                    $new->setPreReleaseVersionNumber(1);
                }
            }
        }

        for ($i = $opportunities_count; $i > 0; $i--) {

            foreach ($available_statuses as $available_status) {

                if ($available_status['label'] != 'Final') {

                    if (!$this->options->pre_release_tag_from || (implode('.', $this->options->pre_release_tag_from) <= implode('.', $opportunities[$i]))) {

                        $new = new VersionSchemeBuilder($this->getOptions()->toArray());
                        $new->setVersionNumberParts($opportunities[$i]);

                        $result[] = $new;

                        $new->setPreReleaseTagStatus($available_status);
                        $new->setPreReleaseVersionNumber(1);
                    }
                }
            }

            $new = new VersionSchemeBuilder($this->getOptions()->toArray());
            $new->setVersionNumberParts($opportunities[$i]);

            $statuses = self::getAllStatuses();
            $new->setPreReleaseTagStatus($statuses[array_key_last($statuses)]);

            $result[] = $new;
        }

        return $result;
    }
}
