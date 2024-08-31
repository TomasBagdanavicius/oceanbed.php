<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\UriReference;

$uris = [
    [
        'uri' => 'http://a/b/c/d;p?q',
        'parts' => [
            'scheme' => 'http',
            'host' => 'a',
            'path' => '/b/c/d;p',
            'query' => '?q',
        ],
    ],[
        'uri' => 'http://username:password@example.com:80/dir?one[]=Vienas&two=Du&two=Zwei#fragment',
        'parts' => [
            'scheme' => 'http',
            'username' => 'username',
            'password' => 'password',
            'host' => 'example.com',
            'port' => '80',
            'path' => '/dir',
            'query' => '?one[]=Vienas&two=Du&two=Zwei',
            'fragment' => '#fragment',
        ],
    ],[
        'uri' => 'ftp://ftp.is.co.za/rfc/rfc1808.txt',
        'parts' => [
            'scheme' => 'ftp',
            'host' => 'ftp.is.co.za',
            'path' => '/rfc/rfc1808.txt',
        ],
    ],[
        'uri' => 'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
        'parts' => [
            'scheme' => 'urn',
            'path' => 'oasis:names:specification:docbook:dtd:xml:4.1.2',
        ],
    ],[
        'uri' => 'ldap://[2001:db8::7]/c=GB?objectClass?one',
        'parts' => [
            'scheme' => 'ldap',
            // Square brackets will be removed.
            'host' => '2001:db8::7',
            'path' => '/c=GB',
            'query' => '?objectClass?one',
        ],
    ],[
        'uri' => 'mailto:someone@example.com,someoneelse@example.com?subject=Hello!&body=This%20is%20the%20body',
        'parts' => [
            'scheme' => 'mailto',
            'path' => 'someone@example.com,someoneelse@example.com',
            'query' => '?subject=Hello!&body=This%20is%20the%20body',
        ],
    ],[
        'uri' => 'tel:+1-816-555-1212',
        'parts' => [
            'scheme' => 'tel',
            'path' => '+1-816-555-1212',
        ],
    ],[
        'uri' => 'file://localhost/c:/WINDOWS/clock.avi',
        'parts' => [
            'scheme' => 'file',
            'host' => 'localhost',
            'path' => '/c:/WINDOWS/clock.avi',
        ],
    ],[
        'uri' => 'file:///c:/WINDOWS/clock.avi',
        'parts' => [
            'scheme' => 'file',
            'path' => '/c:/WINDOWS/clock.avi',
        ],
    ],[
        'uri' => 'news:comp.infosystems.www.servers.unix',
        'parts' => [
            'scheme' => 'news',
            'path' => 'comp.infosystems.www.servers.unix',
        ],
    ],[
        'uri' => 'data:image/png;charset=UTF-8;page=21;base64,VGhpcyBpcyBjbGFzc2lmaWVkIGluZm9ybWF0aW9uLg==',
        'parts' => [
            'scheme' => 'data',
            'path' => 'image/png;charset=UTF-8;page=21;base64,VGhpcyBpcyBjbGFzc2lmaWVkIGluZm9ybWF0aW9uLg==',
        ],
    ],[
        'uri' => '//www.domain.com/',
        'parts' => [
            'host' => 'www.domain.com',
            'path' => '/',
        ],
    ],[
        'uri' => '/one/two/three',
        'parts' => [
            'path' => '/one/two/three',
        ],
    ],[
        'uri' => 'this:that',
        'parts' => [
            'scheme' => 'this',
            'path' => 'that',
        ],
    ],[
        'uri' => './this:that',
        'parts' => [
            'path' => './this:that',
        ],
    ],
];

$parts_meta = [
    [
        'title' => 'scheme',
        'method' => 'getScheme',
    ],[
        'title' => 'username',
        'method' => 'getUsername',
    ],[
        'title' => 'password',
        'method' => 'getPassword',
    ],[
        'title' => 'host',
        'method' => 'getHost',
    ],[
        'title' => 'port',
        'method' => 'getPortNumber',
    ],[
        'title' => 'path',
        'method' => 'getPathString',
    ],[
        'title' => 'query',
        'method' => 'getQueryString',
    ],[
        'title' => 'fragment',
        'method' => 'getFragment',
    ],
];

$no_errors = true;

foreach ($uris as $uri_data) {

    $uri = new UriReference($uri_data['uri']);
    $parts = $uri_data['parts'];

    // Chose to loop through all possible parts (instead of actual parts in URI
    // meta data), because the rule of thumb is that when part is not set, its
    // associated method should return an empty string. So this also checks that
    // methods correctly return empty strings when part is missing.
    foreach ($parts_meta as $part_meta) {

        $received_part = $uri->{$part_meta['method']}();
        $expected_part = ($parts[$part_meta['title']] ?? '');

        if ($received_part != $expected_part) {
            $no_errors = false;
            break 2;
        }
    }
}

Demo\assert_true(
    $no_errors,
    sprintf(
        "Expected %s \"%s\" in URI \"%s\"; got \"%s\"",
        $part_meta['title'],
        $expected_part,
        $uri_data['uri'],
        $received_part
    )
);
