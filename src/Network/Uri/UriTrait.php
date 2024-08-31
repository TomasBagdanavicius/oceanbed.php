<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Filesystem\Path\SearchPath;
use LWP\Network\Uri\UriReference;

trait UriTrait
{
    // Resolves relative URI reference having $this as the base URI.

    public function resolve(UriReference $uri_ref, bool $strict_mode = false): self
    {

        $base_scheme = $this->getScheme();
        $uri_ref_has_authority = $uri_ref->hasAuthority();

        // Scheme is not defined. This is the most common case.
        if (
            $uri_ref->isRelative()
            // In non-strict mode, when schemes match and uri reference has no authority, interpret authority as path, eg. "http:x" resolves to "http://a/b/c/x"
            || (!$strict_mode && $this->getScheme() == $uri_ref->getScheme() && !$uri_ref_has_authority)
        ) {

            // URI reference contains authority.
            if ($uri_ref_has_authority) {

                $target_uri = new self(UriReference::joinSchemeAndAuthority($base_scheme, $uri_ref->getAuthorityString()));

                $target_uri->setPathComponent($uri_ref->getPathComponent());
                $target_uri->setQueryString($uri_ref->getQueryString());

                // URI reference doesn't contain authority part.
            } else {

                $target_uri = new self(UriReference::joinSchemeAndAuthority($base_scheme, $this->getAuthorityString()));

                $target_uri->setAuthorityString($this->getAuthorityString());
                $uri_ref_path_str = $uri_ref->getPathString();

                if ($uri_ref_path_str == '') {

                    $target_uri->setPathComponent($this->getPathComponent());

                    $query_string = ($uri_ref->getQueryString() != '')
                        ? $uri_ref->getQueryString()
                        : $this->getQueryString();

                    $target_uri->setQueryString($query_string);

                } else {

                    if (substr($uri_ref_path_str, 0, 1) == UriPathComponent::SEPARATOR) {

                        $path_component = $uri_ref->getPathComponent();
                        $path_component->compress(SearchPath::RESOLVE_DOT_SEGMENTS, false);

                        $target_uri->setPathComponent($path_component);

                        // Reference URI's path starts with something other than a forward slash.
                    } else {

                        $base_path_str = $this->getPathString();

                        if ($base_path_str != '') {

                            $base_path_str_last_slash = strrpos($base_path_str, UriPathComponent::SEPARATOR);

                            $joined_path = ($base_path_str_last_slash !== false)
                                ? substr($base_path_str, 0, $base_path_str_last_slash + 1)
                                : '';

                        } else {

                            $joined_path = UriPathComponent::SEPARATOR;
                        }

                        $path_component = new UriPathComponent($joined_path . $uri_ref->getPathString(), $strict_mode);
                        $path_component->compress(SearchPath::RESOLVE_DOT_SEGMENTS, false);

                        $target_uri->setPathComponent($path_component);
                    }

                    $target_uri->setQueryString($uri_ref->getQueryString());
                }
            }

            // Scheme is defined.
        } else {

            $target_uri = new self($uri_ref->getUri());
        }

        $uri_ref_fragment = $uri_ref->getFragment(true);

        if ($uri_ref_fragment != '') {

            $target_uri->setFragment($uri_ref_fragment);
        }

        return $target_uri;
    }
}
