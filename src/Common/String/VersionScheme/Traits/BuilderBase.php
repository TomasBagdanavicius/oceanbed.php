<?php

declare(strict_types=1);

namespace LWP\Common\String\VersionScheme\Traits;

trait BuilderBase
{
    // Get first possible versions.

    public function getBaseOpportunities(int $extra_iterate_major = 0)
    {

        $default_part_defs = parent::getDefaultVersionPartDefs();
        $available_statuses = parent::getAllStatuses();
        $last_available_status = $available_statuses[array_key_last($available_statuses)];

        $version_numer_part_defs = $this->options->version_number_part_defs;
        $result = [];
        $extra_iterate_major = max(0, $extra_iterate_major);
        $i = 0;

        // this loop is designed to support extra major part iterations
        // eg. if major min value is '0', there might be a need for '0' and '1' starting point
        do {

            $base_version_parts = [];

            foreach ($version_numer_part_defs as $key => $defs) {

                $defs = array_merge($default_part_defs, $defs);

                if (!empty($base_version_parts)) {

                    $namespace = implode('.', $base_version_parts);

                    if (isset($defs['branch'], $defs['branch'][$namespace])) {

                        $defs = array_merge($defs, $defs['branch'][$namespace]);
                    }
                }

                if (!$defs['omit_zero'] || $defs['min'] > 0) {

                    if (isset($defs['from'], $namespace) && $namespace < implode('.', $defs['from'])) {

                        continue;
                    }

                    $base_version_parts[] = $defs['min'];
                }
            }

            $version_numer_part_defs[0]['min'] = ($base_version_parts[0] + 1);

            $base_version_namespace = implode('.', $base_version_parts);

            if (!$this->options->pre_release_tag_from || (implode('.', $this->options->pre_release_tag_from) <= $base_version_namespace)) {

                // get succeeding statuses, eg. beta would succeed to rc, etc.
                foreach ($available_statuses as $key => $status) {

                    // exclude Final
                    if ($status['label'] != "Final") {

                        $new = new self($this->getOptions()->toArray());
                        $new->setVersionNumberParts($base_version_parts);

                        $new->setPreReleaseTagStatus($status);
                        $new->setPreReleaseVersionNumber(1);

                        $result[] = $new;
                    }
                }
            }

            $new = new self($this->getOptions()->toArray());
            $new->setVersionNumberParts($base_version_parts);
            $new->setPreReleaseTagStatus($last_available_status);

            $result[] = $new;

            $i++;

        } while ($i < (1 + $extra_iterate_major));

        return $result;
    }
}
