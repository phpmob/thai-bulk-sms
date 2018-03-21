# thai-bulk-sms
Thai bulk sms api in php.

### Installation
This package is not release yet that mean you can't install it by `composer require phpmob/thai-bulk-sms` at this time.
For now to install you need to require it directly in your `composer.json`:

```json
"require": {
    "phpmob/thai-bulk-sms": "1.0.x-dev"
}
```
and ..

```shell
$ composer update
```

### Usage

```php
// auto detect http-client
$client = \Http\Discovery\HttpClientDiscovery::find();

$sender = new \PhpMob\ThaiBulkSms\Sender($client, [
    'username' => 'thaibulksms',
    'password' => 'thisispassword',
    'force' => 'standard',
    'sender' => 'SMS',
    'sandbox' => true,
]);

// send one number
$sender->send('0818282829', 'message');

// send bulk numbers
$sender->send(['0818282829', '..number..'], 'message');

// check credit remain
$sender->checkCredit();

// All `Sender` APIs are return `PhpMob\ThaiBulkSms\Result` object.
```

### Use `GuzzleAdapter` as client.

```bash
$ composer req php-http/guzzle6-adapter
```

and construct sender with guzzle client.

```php
$client = new \Http\Adapter\Guzzle6\Client(...);
$sender = new \PhpMob\ThaiBulkSms\Sender($client, [...]);
```

### LICENSE
MIT
