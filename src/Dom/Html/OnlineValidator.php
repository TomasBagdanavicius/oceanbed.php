<?php

declare(strict_types=1);

namespace LWP\Dom\Html;

use LWP\Common\String\Str;
use LWP\Dom\Dom;
use LWP\Network\CurlWrapper\Client as HttpClient;
use LWP\Network\Uri\Url;

class OnlineValidator
{
    public const USER_AGENT = 'Validator.nu/LV http://validator.w3.org/services';
    public const ADDRESS_LEGACY = 'https://validator.w3.org/';
    public const URL_LEGACY = 'https://validator.w3.org/check';
    public const URL_NU = 'https://html5.validator.nu/';

    private $result;
    private $errors;
    private $doc;
    private int $errors_length = 0;
    private int $errors_i = 0;
    private \DOMXpath $doc_xpath;


    public function __construct(
        public readonly string $html,
        public readonly HttpClient $http_client = new HttpClient(),
    ) {

    }


    // Sent HTTP request through to the legacy validator at https://validator.w3.org/.

    public function requestLegacy()
    {

        $data = [
            'fragment' => $this->html,
            'prefill' => '0',
            'doctype' => 'Inline',
            'prefill_doctype' => 'html401',
            'group' => '0',
        ];

        try {

            $response = $this->http_client->post(new Url(self::URL_LEGACY), [
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Content-Type' => 'multipart/form-data',
                    'Referer' => self::ADDRESS_LEGACY,
                ],
                'form_params' => $data,
            ]);

        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage());
        }

        return $response;
    }


    // Sent HTTP request through to the HTML5 tailored destination at https://html5.validator.nu/.
    // - this request is faster and better

    public function requestNuHTML5()
    {

        $data = [
            'showsource' => 'yes',
            'content' => $this->html,
        ];

        try {

            $response = $this->http_client->post(new Url(self::URL_NU), [
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Referer' => self::URL_NU,
                ],
                'multipart_data' => $data,
                'throw_errors' => true,
                'throw_status_errors' => true,
            ]);

        } catch (\Exception $exception) {

            throw new \Exception($exception->getMessage());
        }

        return $response->getBody();
    }


    // Check if HTML markup contains errors

    public function hasErrors()
    {

        $response = $this->requestNuHTML5();

        if (empty($response)) {
            throw new \Exception("The response was empty.");
        }

        $this->doc = new DOM($response);
        $this->doc_xpath = new \DOMXpath($this->doc->getDoc());

        $query = $this->doc->buildClassQuery([
            'success',
            'failure',
        ], 'or');

        $entries = $this->doc_xpath->query($query);

        // abnormally, none of the classes (success or failure) were found
        if ($entries->length == 0) {
            throw new \Exception("Invalid response.");
        }

        $result = [
            'success' => [
                'count' => 0,
            ],
            'failure' => [
                'count' => 0,
            ],
        ];

        for ($i = 0; $i < $entries->length; $i++) {

            $node = $entries[$i];
            $next = $this->doc->getNextElement($node);

            if ($next && $next->getAttribute('id') == 'source') {

                $node_classes = Str::splitIntoWords($node->getAttribute('class'));

                $class = in_array('success', $node_classes)
                    ? 'success'
                    : 'failure';

                $result[$class]['count']++;
                $result[$class][] = ['message' => $node->textContent];
            }
        }

        $this->result = $result;

        // abnormally, both - success and failure - classes were found
        if (
            $result['success']['count'] > 0
            && $result['failure']['count'] > 0
        ) {
            throw new \Exception("Invalid response.");
        }

        // - errors found
        if ($result['failure']['count'] > 0) {

            $query = $this->doc->buildClassQuery('error');
            $this->errors = $this->doc_xpath->query($query);
            $this->errors_length = $this->errors->length;

            return $this->errors_length;
        }

        // - no errors found
        return 0;
    }


    // Get validator's (success or error) message

    public function getMessage()
    {

        if (empty($this->result)) {
            return null;
        }

        if ($this->errors_length > 0) {
            return $this->result['failure'][0]['message'];
        } elseif (isset($this->result['success'][0]['message'])) {
            return $this->result['success'][0]['message'];
        } else {
            return null;
        }
    }


    // Get next error

    public function getError()
    {

        if ($this->errors_length == 0) {
            return null;
        }

        if ($this->errors_length == $this->errors_i) {
            $this->errors_i = 0;
            return false;
        }

        $node = $this->errors[$this->errors_i];

        if ($node->childNodes->length == 0) {
            return null;
        }

        $elem = $this->doc_xpath->query(
            './/span',
            $node->childNodes->item(0)
        );

        $error = [];

        if ($elem->length > 0) {
            $error['message'] = $elem->item(0)->textContent;
        }

        // Look for "location" class name.
        $location_elem = $this->doc_xpath->query('.//' . $this->doc->buildClassQuery('location'), $node);

        if ($location_elem->length > 0) {
            $error['location'] = $location_elem->item(0)->textContent;
        }

        // Look for "extract" class name.
        $extract_elem = $this->doc_xpath->query('.//' . $this->doc->buildClassQuery('extract'), $node);

        if ($extract_elem->length > 0) {
            $error['extract'] = htmlspecialchars($extract_elem->item(0)->textContent);
        }

        $this->errors_i++;

        return $error;
    }
}
