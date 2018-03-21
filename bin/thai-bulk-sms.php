<?php

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        break;
    }
}

$client = \Http\Discovery\HttpClientDiscovery::find();
$sender = new \PhpMob\ThaiBulkSms\Sender($client, [
    'username' => 'thaibulksms',
    'password' => 'thisispassword',
    'sender' => 'SMS',
    'sandbox' => true,
]);

//$result = $sender->send(['1234567890', '1234567890'], 'test');
$result = $sender->checkCredit();

dump($result);
