<?php

declare(strict_types=1);

namespace LWP\Network\Http\Cookies;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Array\IndexableArrayCollection;
use LWP\Network\Domain\Domain;
use LWP\Network\Uri\Url;
use LWP\Network\Uri\UriPathComponent;
use LWP\Network\Http\Cookies\Exceptions\ExpiredCookieException;
use LWP\Network\Hostname;
use LWP\Filesystem\Path\Path;
use LWP\Common\Criteria;
use LWP\Network\Http\Server;
use LWP\Common\String\Str;
use LWP\Network\IpAddress;
use LWP\Network\Exceptions\InvalidIpAddressException;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

class CookieStorage
{
    protected IndexableArrayCollection $indexable_collection;


    public function __construct(
        private ?Url $url = null
    ) {

        $this->indexable_collection = new IndexableArrayCollection();
    }


    // Gets "Indexable Array Collection" instance, which is the main indexing mechanism for this class.

    public function getIndexableCollection(): IndexableArrayCollection
    {

        return $this->indexable_collection;
    }


    // Gets the URL that was added through the constructor.

    public function getUrl(): Url
    {

        return $this->url;
    }


    // Exports data into an array.

    public function getData(): array
    {

        return $this->indexable_collection->toArray();
    }


    // Checks if URL was provided through the contextual data or if it is available globally.

    public function getContextUrl(array $context): Url
    {

        if (!empty($context['url']) && ($context['url'] instanceof Url)) {
            return $context['url'];
        } elseif ($this->url) {
            return $this->url;
        } else {
            throw new \BadMethodCallException("Request URL must be provided and it must be an instance of \"" . Url::class . "\".");
        }
    }


    // Adds additional info (eg. size, expires) into the data container.

    public static function populateData(array &$data): void
    {

        if (isset($data['name'], $data['value']) && is_string($data['name']) && is_string($data['value'])) {
            $data['size'] = (strlen($data['name']) + strlen($data['value']));
        }

        if (isset($data['time_created'], $data['max-age']) && !isset($data['expires']) && is_numeric($data['time_created']) && is_numeric($data['max-age'])) {
            $data['expires'] = ($data['time_created'] + $data['max-age']);
        }
    }


    // Adds URI domain info into the data container.

    public static function populateDomainUriComponent(array &$data, Url $url): void
    {

        // When domain name is not provided, take FULL host.
        // No leading dot should be added.
        if (!isset($data['domain'])) {

            $data['domain'] = $url->getHost();

        } elseif (is_string($data['domain'])) {

            $is_ip_address = true;

            try {

                $ip_address = new IpAddress(trim($data['domain'], '.'));

            } catch (InvalidIpAddressException $exception) {

                $is_ip_address = false;
            }

            $domain_first_char = substr($data['domain'], 0, 1);

            // When domain name is provided and it's not an IP address, but it didn't contain a leading dot, prepend a dot.
            if (!$is_ip_address && $domain_first_char !== Hostname::LABEL_SEPARATOR) {
                $data['domain'] = (Hostname::LABEL_SEPARATOR . $data['domain']);
            } elseif ($is_ip_address && $domain_first_char === Hostname::LABEL_SEPARATOR) {
                $data['domain'] = substr($data['domain'], 1);
            }
        }
    }


    // Adds URI path info into the data container.

    public static function populatePathUriComponent(array &$data, Url $url): void
    {

        // When path is not provided or it doesn't start with a forward slash, use dirname of URL's path or a path forward slash when the former doesn't exist.
        if (!isset($data['path']) || !str_starts_with($data['path'], UriPathComponent::SEPARATOR)) {

            $dirname = $url->getPathComponent()->getDirname();

            /* A single trailing forward slash should be trimmed off, when it's not a root path. */
            $data['path'] = ($dirname !== Path::CURRENT_DIR_SYMBOL && $dirname !== UriPathComponent::SEPARATOR)
                ? Str::rtrimSubstring($dirname, UriPathComponent::SEPARATOR)
                : UriPathComponent::SEPARATOR;
        }
    }


    // Attempts to store a new cookie.

    public function add(array $data, array $context = []): int
    {

        $url = $this->getContextUrl($context);

        self::populatePathUriComponent($data, $url);

        $data = self::validToSet($data, $url);

        // This is required to create "expires" parameter in the "populateData" method.
        $data['time_created'] = time();

        self::populateData($data);
        self::populateDomainUriComponent($data, $url);

        // (1) First off, filter out a set of entries under this domain and path.
        /* Essentially, cookie name is not unique, unless it's within the same domain and path group name. */
        $criteria = new Criteria();
        $criteria->condition(new Condition('domain', $data['domain']));
        $criteria->condition(new Condition('path', $data['path']));

        $filtered_collection = $this->indexable_collection->matchCriteria($criteria);
        $domain_path_group_count = $filtered_collection->count();

        if ($domain_path_group_count) {

            // (2) Secondly, search for a matching name within the filtered set.
            $criteria = new Criteria();
            $criteria->condition(new Condition('name', $data['name']));

            $has_name = $this->indexable_collection->matchCriteria($criteria);
            $name_group_count = $has_name->count();

            if ($name_group_count) {

                // This should be a unique entry. Will not check if there are more than one item.

                $index_num = $has_name->key();
                $this->indexable_collection->update($index_num, $data);

                return $index_num;
            }
        }

        return $this->indexable_collection->add($data);
    }


    // Removes an entry by given HTTP cookie name and, optionally, domain and path. These 3 params form a unique entry.

    public function removeByAttrs(string $name, ?string $domain = null, string $path = UriPathComponent::SEPARATOR): mixed
    {

        if (!$domain) {
            $domain = Server::getHost();
        }

        $criteria = new Criteria();
        $criteria->condition(new Condition('name', $name));
        $criteria->condition(new Condition('domain', $domain));
        $criteria->condition(new Condition('path', $path));

        $filtered_collection = $this->indexable_collection->matchCriteria($criteria);

        return ($filtered_collection->count())
            ? $this->indexable_collection->remove($filtered_collection->key())
            : null;
    }


    // Tells if current dataset contains expired entries.

    public function containsExpiredEntries(): int
    {

        $criteria = new Criteria();
        $criteria->condition(new Condition('expires', time(), ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO));

        return $this->indexable_collection->matchCriteriaCount($criteria);
    }


    // Deletes all entries that have expired.

    public function clearExpiredEntries(): int
    {

        $criteria = new Criteria();
        $criteria->condition(new Condition('expires', time(), ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO));
        $filtered_collection = $this->indexable_collection->matchCriteria($criteria);
        $deleted_count = 0;

        foreach ($filtered_collection as $index => $element) {

            $this->indexable_collection->remove($index);

            $deleted_count++;
        }

        return $deleted_count;
    }


    // Cleans up all entries that should be no longer in storage on session end.
    // @param $path - works when domain name is provided.

    public function clearSessionEntries(string $domain = null, string $path = null): int
    {

        $criteria = new Criteria();
        $criteria->condition(new Condition('expires', -1));

        if ($domain) {

            $criteria->condition(new Condition('domain', $domain));

            if ($path) {
                $criteria->condition(new Condition('path', $path));
            }
        }

        $filtered_collection = $this->indexable_collection->matchCriteria($criteria);

        $deleted_count = 0;

        foreach ($filtered_collection as $index => $element) {

            $this->indexable_collection->remove($index);

            $deleted_count++;
        }

        return $deleted_count;
    }


    // Get cookies that should be sent.

    public function fetch(Url $url): ArrayCollection
    {

        $searchable_domains = ($domain = $url->getDomain())
            ? self::createSearchableEntriesFromDomain($domain)
            : self::createSearchableEntriesFromString($url->getHost());

        $criteria = new Criteria();

        foreach ($searchable_domains as $searchable_domain) {

            $criteria->setOrConditionEqualTo('domain', $searchable_domain);
        }

        $filtered_collection = $this->indexable_collection->match($criteria);

        $resulting_collection = new ArrayCollection();

        foreach ($filtered_collection as $index => $element) {

            $try_success = true;

            try {
                self::validToGet($element, $url);
            } catch (ExpiredCookieException $expired_cookie_exception) {
                $this->indexable_collection->remove($index);
                $try_success = false;
            } catch (\Throwable $exception) {
                $try_success = false;
            }

            if ($try_success) {
                $resulting_collection->add($element);
            }
        }

        return $resulting_collection;
    }


    // Gets an implicit list of domain entries that should be searched for. See method "createSearchableEntriesFromDomain" for an explicit list.
    /* The problem comes from the registrable part retrieval issue. Unlike method "createSearchableEntriesFromDomain",
    this method does not support domain instance object, which contains the domain data reader and the capability to
    extract registrable part. */

    public static function createSearchableEntriesFromString(string $domain): array
    {

        $segments = explode(Hostname::LABEL_SEPARATOR, $domain);
        $segments = array_reverse($segments);

        $domain_str = '';
        $result = [];

        foreach ($segments as $key => $segment) {

            $domain_str = ($domain_str === '')
                ? $segment
                : ($segment . Hostname::LABEL_SEPARATOR . $domain_str);

            // Skip top-level domain names.
            if ($key) {

                if ($domain === $domain_str) {
                    $result[] = $domain_str;
                }

                $result[] = (Hostname::LABEL_SEPARATOR . $domain_str);
            }
        }

        return $result;
    }


    // Gets an explicit list of domain entries that should be searched for.

    public static function createSearchableEntriesFromDomain(Domain $domain): array
    {

        $domain_str = $domain->__toString();
        $registrable_domain = $domain->getRegistrableDomain();
        $registrable_domain_reached = false;
        $result = [];

        $domain->walkThroughDomains(function (string $member_domain) use ($domain_str, $registrable_domain, &$registrable_domain_reached, &$result) {

            if ($registrable_domain_reached || $registrable_domain === $member_domain) {

                $registrable_domain_reached = true;

                if ($member_domain === $domain_str) {
                    $result[] = $member_domain;
                }

                $result[] = (Hostname::LABEL_SEPARATOR . $member_domain);
            }

        });

        return $result;
    }


    // Checks if cookie data is valid to be sent.
    // @var $action_url - basically, the location of a trigger (eg. hyperlink) that was used to navigate to the request URL.

    /* The whole point of the $action_url is to emphasize that the request conducted navigation to the cookie's origin site. In essence, this variable could and might have been replaced by a boolean type variable that would indicate that a navigation took place, eg. pointer click. However, URL type has been left for sanity verification, where action URL's domain must logically match request URL's domain. Also, there is another logical pathway here that was considered. It involves using referrer URL instead of action URL and matching it against the current site URL, because it seems logical that the action must take place on the current site. However, action URL was chosen, because it seams to be closer to how browsers identify navigation. */

    public static function validToGet(array $data, Url $url, ?URL $current_site_url = null, ?URL $action_url = null): array
    {

        $url_domain = $url->getHost();

        /* Domain validation. */

        // Domain is a required option.
        if (!isset($data['domain'])) {

            throw new \Exception("Domain parameter is missing and must be included.");
        }

        $domain_exception_str = ("URL domain \"" . $url_domain . "\" does not match \"" . $data['domain'] . "\".");

        // Cookie domain starts with a dot character.
        if (substr($data['domain'], 0, 1) == Hostname::LABEL_SEPARATOR) {

            $url_domain_dot_prepended = (Hostname::LABEL_SEPARATOR . $url_domain);

            // Check if url host string ends with cookie domain string excluding the dot.
            // Example: both ".domain.com" and "domain.com" are valid for host "domain.com".
            if (!str_ends_with($url_domain_dot_prepended, $data['domain'])) {

                throw new \Exception($domain_exception_str);
            }

            // Cookie domain doesn't end with a dot, so it must match the url host.
        } elseif ($data['domain'] !== $url_domain) {

            throw new \Exception($domain_exception_str);
        }

        // Checks if the cookie hasn't expired.
        if (
            isset($data['expires'])
            && $data['expires'] > 0 // "-1" implies "end of session".
            && time() >= $data['expires']
        ) {

            throw new ExpiredCookieException("HTTP cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" has expired.");
        }

        /* Path. */

        $url_path_str = $url->getPathString();

        if (!isset($data['path'])) {

            throw new \Exception("Path parameter is missing and must be included.");
        }

        $data_store_path = (!str_ends_with($data['path'], UriPathComponent::SEPARATOR))
            ? ($data['path'] . UriPathComponent::SEPARATOR)
            : $data['path'];

        if (!str_starts_with($url_path_str, $data_store_path)) {

            throw new \Exception("Path \"" . $url_path_str . "\" does not fall under \"" . $data_store_path . "\" in HTTP cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\".");
        }

        // Secure policy.
        if (isset($data['secure']) && $url->getScheme() !== 'https') {

            throw new \Exception("Cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" requires the URL to contain \"https\" scheme protocol.");
        }

        // Samesite policy.
        if (isset($data['samesite']) && $current_site_url) {

            $is_strict_policy = (strcmp($data['samesite'], 'Strict') === 0);
            $is_lax_policy = (strcmp($data['samesite'], 'Lax') === 0);

            if ($is_strict_policy || $is_lax_policy) {

                $current_site_url_registrable_part = $current_site_url->getDomain()?->getRegistrableDomain();
                $request_url_registrable_part = $url->getDomain()?->getRegistrableDomain();

                // Checks if request site matches current site, where site is the registrable domain name.
                $strict_site_match = (
                    $current_site_url_registrable_part
                    && $request_url_registrable_part
                    && $request_url_registrable_part === $current_site_url_registrable_part
                );

                if ($is_strict_policy && !$strict_site_match) {

                    throw new \Exception("Request must be sent within first-party site when using directive \"SameSite=Strict\". Request domain \"" . $request_url_registrable_part  . "\" does not match current site \"" . $current_site_url_registrable_part . "\".");
                }

                // For Lax to fail, it would fail the strict match, plus request site would not match the action site, provided that both are given. Action site essentially is the location of the trigger (eg. hyperlink) that was used
                if (
                    $is_lax_policy
                    && !$strict_site_match
                    && ($action_url_registrable_part = $action_url?->getDomain()?->getRegistrableDomain())
                    && $request_url_registrable_part !== $action_url_registrable_part
                ) {

                    throw new \Exception("Request must be sent within first-party site or the action URL's site must match request site when using directive \"SameSite=Lax\".");
                }
            }
        }

        return $data;
    }


    // Checks if cookie data is valid to be accepted.

    public static function validToSet(array $data, Url $url): array
    {

        $url_domain = $url->getHost();

        // Validate domain, when it's provided.
        // Cookie data domain essentially cannot be higher than URL's host.
        if (isset($data['domain']) && !str_ends_with($url_domain, ltrim($data['domain'], Hostname::LABEL_SEPARATOR))) {
            throw new \Exception("HTTP cookie's domain name \"" . $data['domain'] . "\" is not within bounds of \"" . $url_domain . "\".");
        }

        // Validate lifetime.
        // It can return "-1", which stands for "end of session".
        if (($lifetime = self::canLive($data)) === false) {
            throw new ExpiredCookieException("HTTP cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" has expired.");
        }

        if (is_int($lifetime)) {
            $data['expires'] = $lifetime;
        }

        $url_scheme = $url->getScheme();
        $has_secure_attribute = (isset($data['secure']) && $data['secure'] === true);

        // Validate "secure" attribute, when it's provided.
        if ($has_secure_attribute && $url_scheme !== 'https') {
            throw new \Exception("Cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" requires the URL to contain \"https\" scheme protocol.");
        }

        // "__Secure-" prefix conditional validation.
        if (isset($data['secure_prefix']) && $data['secure_prefix'] === true) {

            $exception_msg = "Cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" contains the \"__Secure-\" prefix and therefore must ";

            if (!$has_secure_attribute) {
                throw new \Exception($exception_msg . "use \"secure\" attribute.");
            } elseif ($url_scheme !== 'https') {
                throw new \Exception($exception_msg . "be set on a location using \"https\" scheme protocol.");
            }
        }

        if (!str_starts_with($data['path'], UriPathComponent::SEPARATOR)) {
            #$data['path'] = UriPathComponent::SEPARATOR;
        }

        // "__Host-" prefix must...
        if (isset($data['host_prefix']) && $data['host_prefix'] === true) {

            $exception = "Cookie \"" . Cookies::joinPair($data['name'], $data['value']) . "\" contains the \"__Host-\" prefix and therefore must ";

            // ... be marked with the "secure" attribute.
            if (!$has_secure_attribute) {
                throw new \Exception($exception . "use \"secure\" attribute.");
                // ... be from a secure location.
            } elseif ($url_scheme !== 'https') {
                throw new \Exception($exception . "be set on a location using \"https\" scheme protocol.");
                // ... have the "path" attribute set to "/".
            } elseif (!isset($data['path']) || $data['path'] !== UriPathComponent::SEPARATOR) {
                throw new \Exception($exception . "have its path set to \"" . UriPathComponent::SEPARATOR . "\".");
                // ... not include a "domain" attribute.
            } elseif (isset($data['domain'])) {
                throw new \Exception($exception . "not include a \"domain\" attribute.");
            }
        }

        if (isset($data['samesite'])) {

            // This is the most strict behavior, not necessarily used by all browsers.
            // Cookies marked with "SameSite=None" directive should also be marked "secure".
            if (strcmp($data['samesite'], 'None') === 0 && !$has_secure_attribute) {
                throw new \Exception("The \"SameSite=None\" directive requires that the \"secure\" attribute also be used.");
            }
        }

        return $data;
    }


    // Checks if cookie has not expired.
    // @return - expiry timestamp (when possible), or "-1" when end of session, or boolean.

    public static function canLive(array $data): int|bool
    {

        // Attribute "max-age" takes precedence over "expires".
        if (isset($data['max-age'])) {

            if ($data['max-age'] <= 0) {
                return false;
            }

            if (isset($data['time_created'])) {

                $expires = intval($data['time_created'] + $data['max-age']);

                return ($expires > time())
                    ? $expires
                    : false;
            }

            return true;

            // Expiry date.
        } elseif (isset($data['expires'])) {

            $timestamp = (is_integer($data['expires']))
                ? $data['expires']
                : date_timestamp_get(date_create($data['expires']));

            return ($timestamp > time())
                ? $timestamp
                : false;

            // End of session.
        } else {

            return -1;
        }
    }


    // Builds "Cookie" header field.

    public function buildCookieHeaderField(Url $url): ?string
    {

        $collection = $this->fetch($url);

        if ($collection->isEmpty()) {
            return null;
        }

        return Cookies::buildCookieHeaderField($collection->toArray());
    }


    // Saves data into file.

    public function saveToFile(string $filename): int
    {

        return CookieFileStorage::storeToFile($filename, CookieFileStorage::serialize($this->indexable_collection->toArray()));
    }
}
