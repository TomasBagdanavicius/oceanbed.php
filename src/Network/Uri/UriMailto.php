<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\Uri\UriReference;
use LWP\Network\Uri\SearchParams;
use LWP\Network\Uri\Exceptions\InvalidUriException;
use LWP\Network\EmailAddress;
use LWP\Common\String\EnclosedCharsIterator;
use LWP\Common\String\Str;
use LWP\Filesystem\Path\Path;

class UriMailto extends UriReference
{
    public const SCHEME = 'mailto';
    public const EMAIL_ADDRESSES_DIVIDER = ',';


    public function __construct(string $uri)
    {

        if ($uri == '') {
            throw new \Exception("URI cannot be empty.");
        }

        parent::__construct($uri);

        $scheme = $this->getScheme();

        if ($scheme != '' && $scheme != self::SCHEME) {
            throw new InvalidUriException("URI \"" . $uri . "\" must start with a \"" . self::SCHEME . "\" scheme.");
        }
    }


    // Gets parts for the whole URI or a portion of it.

    public function getUriReferenceParts(string $from = null, string $until = null): array
    {

        $parts = parent::getUriReferenceParts($from, $until);

        if (isset($parts['query']) && $parts['query'] instanceof SearchParams) {
            $parts['query'] = $parts['query']->outputWithPrefix();
        }

        if (isset($parts['path']) && is_array($parts['path'])) {
            $parts['path'] = implode(self::EMAIL_ADDRESSES_DIVIDER, $parts['path']);
        }

        return $parts;
    }


    // Adds custom query handling behavior when parsing path, query, and fragment parts.

    public function splitPathQueryFragment(): void
    {

        parent::splitPathQueryFragment();

        if (!($this->parts['query'] instanceof SearchParams)) {

            $this->setQueryString($this->parts['query']);
        }

        if (!is_array($this->parts['path'])) {

            $path_string = $this->parts['path']->__toString();

            $email_addresses = [];

            if ($path_string != '' && $path_string != Path::CURRENT_DIR_SYMBOL) {

                // Since email addresse's local-part can contain a quoted comma, the splitter must exclude quoted commas.
                $enclosed_chars_iterator = new EnclosedCharsIterator($path_string, [
                    '"' => ['"', true],
                ]);

                $comma_positions = [];

                foreach ($enclosed_chars_iterator as $key => $segment) {

                    if (!$enclosed_chars_iterator->hasEnclosingChars()) {

                        $comma_positions = array_merge($comma_positions, array_map(function ($value) use ($enclosed_chars_iterator) {

                            return ($value + $enclosed_chars_iterator->getStartPosition());

                        }, Str::posAll($segment, self::EMAIL_ADDRESSES_DIVIDER)));
                    }
                }

                $email_addresses = (empty($comma_positions))
                    ? [$path_string]
                    : Str::splitAtMultiplePos($path_string, $comma_positions);

                foreach ($email_addresses as &$email_address) {

                    $email_address = new EmailAddress($email_address);
                }

            }

            $this->parts['path'] = $email_addresses;
        }
    }


    // Sets a given query string.

    public function setQueryString(string $query_string): void
    {

        $this->parts['query'] = SearchParams::fromString($query_string);
    }


    // Prepends a new email address to the "To" list.

    public function addEmailAddress(EmailAddress $email_address): void
    {

        $this->splitPathQueryFragment();

        $this->parts['path'][] = $email_address;
    }


    // Removes an email address by index number from the "To" list.

    public function removeEmailAddress(int $index_number): void
    {

        $this->splitPathQueryFragment();

        $count_email_addresses = count($this->parts['path']);

        if ($index_number >= 0 && $index_number <= ($count_email_addresses - 1)) {
            unset($this->parts['path'][$index_number]);
        }
    }


    // Removes the last email address from the "To" list.

    public function popEmailAddress(): void
    {

        $this->splitPathQueryFragment();

        $count_email_addresses = count($this->parts['path']);

        $this->removeEmailAddress(($count_email_addresses - 1));
    }


    // Gets all "To" email addresses.

    public function getToEmailAddresses(): array
    {

        $this->splitPathQueryFragment();

        return $this->parts['path'];
    }


    // Sets regular param.

    private function setRegularParam(string $name, string $value): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query']->replace($name, $value);
    }


    // Gets regular param.

    private function getRegularParam(string $name): string
    {

        $this->splitPathQueryFragment();

        return $this->parts['query']->get($name);
    }


    // Unsets regular param.

    private function unsetRegularParam(string $name): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query']->remove($name);
    }


    // Sets email param.

    private function setEmailParam(string $name, EmailAddress $email_address): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query']->replace($name, $email_address->__toString());
    }


    // Gets subject.

    public function getSubject(): string
    {

        return $this->getRegularParam('subject');
    }


    // Sets subject.

    public function setSubject(string $subject): void
    {

        $this->setRegularParam('subject', $subject);
    }

    // Unsets subject.

    public function unsetSubject(): void
    {

        $this->unsetRegularParam('subject');
    }


    // Gets body.

    public function getBody(): string
    {

        return $this->getRegularParam('body');
    }


    // Sets subject.

    public function setBody(string $body): void
    {

        $this->setRegularParam('body', $body);
    }


    // Unsets subject.

    public function unsetBody(): void
    {

        $this->unsetRegularParam('body');
    }


    // Gets "CC" email address.

    public function getCCEmailAddress(): string
    {

        return $this->getRegularParam('cc');
    }


    // Sets "CC" email address.

    public function setCCEmailAddress(EmailAddress $email_address): void
    {

        $this->setEmailParam('cc', $email_address);
    }


    // Unsets "CC" email address.

    public function unsetCCEmailAddress(): void
    {

        $this->unsetRegularParam('cc');
    }


    // Gets "BCC" email address.

    public function getBCCEmailAddress(): string
    {

        return $this->getRegularParam('bcc');
    }


    // Sets "BCC" email address.

    public function setBCCEmailAddress(EmailAddress $email_address): void
    {

        $this->setEmailParam('bcc', $email_address);
    }


    // Unsets "BCC" email address.

    public function unsetBCCEmailAddress(): void
    {

        $this->unsetRegularParam('bcc');
    }
}
