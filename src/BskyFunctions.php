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
            throw new \Exception('Could not create session.');
        }

        $session = json_decode($response->getBody());

        if (!isset($session->accessJwt)) {
            throw new \Exception('Could not create session, accessJwt not set.');
        }

        self::log('Bsky session created.');

        $GLOBALS['BSKY_SESSION'] = $session;
        return $session;
    }

    public static function log($text): void
    {
        echo '[' . date("Y-m-d H:i:s") . '] ' . $text . PHP_EOL;

        file_put_contents(
            filename: __DIR__ . '/../log.txt',
            data: '[' . date("Y-m-d H:i:s") . '] ' . $text . PHP_EOL,
            flags: FILE_APPEND | LOCK_EX
        );

        // if log file is bigger than 1MB, delete first 100 lines
        if (filesize(__DIR__ . '/../log.txt') > 1000000) {
            self::log('Log file too big, deleting first 100 lines.');
            $lines = file(__DIR__ . '/../log.txt');
            $lines = array_slice($lines, 100);
            file_put_contents(__DIR__ . '/../log.txt', implode('', $lines));
        }
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

        self::log('Post created: ' . $textPrelink . $link);

        dump($response->getBody());
    }
}
