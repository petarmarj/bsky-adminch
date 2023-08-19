<?php

require __DIR__ . '/vendor/autoload.php';

use Piit\Bsky\BskyFunctions;

BskyFunctions::init();
BskyFunctions::login();

$date = date("Y-m-d");

$feed = \Laminas\Feed\Reader\Reader::import(
    'https://www.newsd.admin.ch/newsd/feeds/rss?lang=de&' .
    'org-nr=1070&topic=&keyword=&offer-nr=&catalogueElement=' .
    '&kind=M%2CR&start_date=' . $date . '&end_date=' . $date
);

foreach ($feed as $entry) {
    if (
        file_exists(__DIR__ . '/hashes.txt') &&
        strpos(file_get_contents(__DIR__ . '/hashes.txt'), md5($entry->getLink())) !== false
    ) {
        continue;
    }

    dump([
        trim($entry->getTitle()),
        trim($entry->getDescription()),
        $entry->getLink(),
    ]);

    BskyFunctions::post(
        "🇨🇭 " . trim($entry->getTitle()) . "\n",
        $entry->getLink(),
        date("c")
    );

    file_put_contents(__DIR__ . '/hashes.txt', md5($entry->getLink()) . "\n", FILE_APPEND | LOCK_EX);

    sleep(10);
}
