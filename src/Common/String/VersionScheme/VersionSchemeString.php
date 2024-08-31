<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme;

use LWP\Common\OptionManager;
use LWP\Common\String\VersionScheme\VersionScheme;
use LWP\Common\String\Clause\SortByComponent;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\sortByColumns;

class VersionSchemeString
{
    public readonly OptionManager $options;


    public function __construct(
        private string $scheme,
        private VersionSchemeBuilderInterface $builder,
    ) {

        $this->options = $builder->getOptions();
    }


    //

    public function getScheme(): string
    {

        return $this->scheme;
    }


    //

    public function getBuilder(): VersionSchemeBuilder
    {

        return $this->builder;
    }


    //

    public function getOptions(): OptionManager
    {

        return $this->options;
    }


    //

    public function parseVersionScheme()
    {

        $scheme = $this->scheme;

        // version number prefix
        if ($this->options->version_number_prefix) {

            if (is_string($this->options->version_number_prefix)) {
                $version_number_prefix = $this->options->version_number_prefix;
            } elseif ($this->options->version_number_prefix === true) {
                $version_number_prefix = VersionScheme::DEFAULT_VERSION_NUMBER_PREFIX;
            } else {
                $version_number_prefix = null;
            }

            if ($version_number_prefix) {

                $version_number_prefix_len = strlen($version_number_prefix);

                if ($version_number_prefix && substr($scheme, 0, $version_number_prefix_len) != $version_number_prefix) {
                    return false;
                }

                $scheme = substr($scheme, $version_number_prefix_len);
            }
        }

        $version_number_part_defs = $this->options->version_number_part_defs;

        // Sort by position.
        $version_number_part_defs = array_values(sortByColumns(
            $version_number_part_defs,
            ['pos' => array_column($version_number_part_defs, 'pos')],
            SortByComponent::fromString("pos ASC")
        ));

        $version_number_part_defs_count = count($version_number_part_defs);
        $scheme_len = strlen($scheme);

        $offset = 0;
        $version_number_divider_count = 0;
        $version_number = '';

        while ($scheme_len > $offset && (($is_num = is_numeric($scheme[$offset])) || $scheme[$offset] == $this->options->version_number_divider)) {

            if (!$is_num) {

                $version_number_divider_count++;

                if ($version_number_divider_count >= $version_number_part_defs_count) {
                    break;
                }
            }

            $version_number .= $scheme[$offset];

            $offset++;
        }

        if ($version_number == '') {
            return false;
        }

        $version_parts = explode($this->options->version_number_divider, $version_number);
        $default_part_defs = VersionScheme::getDefaultVersionPartDefs();
        $version_number_parts = [];

        foreach ($version_number_part_defs as $key => $part) {

            $part = array_merge($default_part_defs, $part);

            if ($part['omit_zero'] && !isset($version_parts[$key])) {
                break;
            }

            if (isset($version_parts[$key])) {

                $version_num = $version_parts[$key];

                if (is_numeric($part['min']) && $version_num < $part['min']) {
                    return false;
                }

                if (is_numeric($part['max']) && $version_num > $part['max']) {
                    return false;
                }

                $version_number_parts[] = $version_parts[$key];
            }
        }

        $this->builder->setVersionNumberParts($version_number_parts);

        if (isset($version_number_prefix)) {

            $this->builder->setVersionNumberPrefix($version_number_prefix);
        }

        if ($this->options->pre_release_tag) {

            if ($scheme_len > $offset) {

                $prefix_len = strlen($this->options->pre_release_tag_prefix);

                if (substr($scheme, $offset, $prefix_len) != $this->options->pre_release_tag_prefix) {
                    return false;
                }

                $offset += $prefix_len;

                if ($this->options->pre_release_tag_from && $version_number < implode('.', $this->options->pre_release_tag_from)) {
                    return false;
                }

                if (!$pre_release_tag = $this->parsePreReleaseTag(substr($scheme, $offset))) {
                    return false;
                }

                $this->builder->setPreReleaseTagStatus($pre_release_tag['pre_release_tag_status']);
                $this->builder->setPreReleaseTagStatusNeedle($pre_release_tag['pre_release_tag_status_needle']);
                $this->builder->setPreReleaseVersionNumber(intval($pre_release_tag['pre_release_version_number']));

                // expecting a pre-release tag, but there are no more characters in the scheme
            } else {

                $statuses = VersionScheme::getAllStatuses();
                $this->builder->setPreReleaseTagStatus($statuses[array_key_last($statuses)]);

                if (!$this->options->pre_release_tag_label_final_omit) {

                    return false;
                }
            }
        }

        return $this->builder->getObject();
    }


    // Using global/class options in case new ones emerge in the future

    public function parsePreReleaseTag(string $pre_release_tag)
    {

        $available_statuses = VersionScheme::getAllStatuses();
        $result = [];
        $pre_release_tag_status = false;

        if ($this->options->pre_release_tag_label == 'string') {

            foreach ($available_statuses as $status) {

                foreach ($status['tag_names'] as $tag_short => $tag_name) {

                    $tag = ($this->options->pre_release_tag_shortening)
                        ? $tag_short
                        : $tag_name;

                    // checks if pre release tag starts with one of the status labels
                    if (($sub = substr($pre_release_tag, 0, strlen($tag))) == $tag) {

                        $pre_release_tag_status_needle = $sub;
                        $pre_release_tag_status = $status;
                        break 2;
                    }
                }
            }

        } else {

            foreach ($available_statuses as $status) {

                if (($sub = substr($pre_release_tag, 0, strlen($status['numeric_label']))) == $status['numeric_label']) {

                    $pre_release_tag_status_needle = $sub;
                    $pre_release_tag_status = $status;

                    break;
                }
            }
        }

        if (!$pre_release_tag_status) {
            return false;
        }

        $result['pre_release_tag_status'] = $pre_release_tag_status;
        $result['pre_release_tag_status_needle'] = $pre_release_tag_status_needle;
        $offset = strlen($pre_release_tag_status_needle);

        if ($result['pre_release_tag_status']['required_version_number']) {

            if ($this->options->pre_release_tag_divider && $this->options->pre_release_tag_divider != '') {

                $len = strlen($this->options->pre_release_tag_divider);

                if (substr($pre_release_tag, $offset, $len) != $this->options->pre_release_tag_divider) {
                    return false;
                }

                $offset += $len;
            }

            $pre_release_tag_len = strlen($pre_release_tag);
            $pre_release_version = '';

            while ($pre_release_tag_len > $offset && is_numeric($pre_release_tag[$offset])) {

                $pre_release_version .= $pre_release_tag[$offset];
                $offset++;
            }

            if ($pre_release_version == '' && $result['pre_release_tag_status']['required_version_number']) {
                return false;
            }

            $result['pre_release_version_number'] = $pre_release_version;

        } elseif ($scheme_len > $offset) {

            return false;
        }

        return $result;
    }
}
