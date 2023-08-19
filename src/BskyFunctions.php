<?php

namespace Piit\Bsky;

use Symfony\Component\Dotenv\Dotenv;

class BskyFunctions
{
    public static function init()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');

        $GLOBALS['GUZZLE'] = new \GuzzleHttp\Client();
    }

    public static function login()
    {
        $response = $GLOBALS['GUZZLE']->request(
            'POST',
            'https://bsky.social/xrpc/com.atproto.server.createSession',
            [
                'json' => [
                    'identifier' => $_ENV['BSKY_IDENTIFIER'],
                    'password' =>  $_ENV['BSKY_PW']
                ]
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Could not create session');
        }

        $session = json_decode($response->getBody());
        $GLOBALS['BSKY_SESSION'] = $session;
        return $session;
    }

    public static function post($textPrelink, $link, $date)
    {

        $response = $GLOBALS['GUZZLE']->request(
            'POST',
            'https://bsky.social/xrpc/com.atproto.repo.createRecord',
            [
                'headers' => ['Authorization' => 'Bearer ' . $GLOBALS['BSKY_SESSION']->accessJwt],
                'json' => [
                    'repo' => $GLOBALS['BSKY_SESSION']->did,
                    'collection' => 'app.bsky.feed.post',
                    'record' => [
                        '$type' => 'app.bsky.feed.post',
                        'text' => $textPrelink . $link,
                        'createdAt' => $date,
                        'langs' => ['de', 'de-CH'],
                        'facets' => [
                            [
                                'index' => [
                                    'byteStart' => strlen($textPrelink),
                                    'byteEnd' => strlen($textPrelink) + strlen($link)
                                ],
                                'features' => [
                                    [
                                    '$type' => 'app.bsky.richtext.facet#link',
                                    'uri' => $link,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        dump($response->getBody());
    }
}
