<?php

declare(strict_types=1);

namespace Tests\PhpMob\ThaiBulkSms\Sender;

use Http\Discovery\HttpClientDiscovery;
use PhpMob\ThaiBulkSms\Sender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class SenderTest extends TestCase
{
    public static function createSender(array $options = []): Sender
    {
        return new Sender(HttpClientDiscovery::find(), array_replace_recursive([
            'username' => 'thaibulksms',
            'password' => 'thisispassword',
            'sender' => 'SMS',
        ], $options));
    }

    public function test_invalid_construct()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required options "password", "sender", "username" are missing');

        new Sender(HttpClientDiscovery::find(), []);
    }

    public function test_invalid_runtime_options()
    {
        $this->expectException(UndefinedOptionsException::class);

        self::createSender()->send('test', 'test', null, ['invalid_option' => 1]);
    }

    public function test_not_provide_username_and_password()
    {
        $result = self::createSender()->send('test', 'test', null, [
            'username' => '',
            'password' => '',
        ]);

        self::assertEquals('100', $result->getErrorCode());
    }

    public function test_invalid_username_or_password()
    {
        $result = self::createSender()->send('0123456789', 'test', null, [
            'username' => 'xxx',
            'password' => 'xxx',
        ]);

        self::assertEquals('101', $result->getErrorCode());
    }

    public function test_invalid_phone_number()
    {
        $result = self::createSender()->send('', 'test');

        self::assertEquals('102', $result->getErrorCode());
    }

    public function test_invalid_message()
    {
        $result = self::createSender()->send('0123456789', '');

        self::assertEquals('103', $result->getErrorCode());
    }

    public function test_valid_message()
    {
        $result = self::createSender()->send('0123456789', 'test message');

        self::assertTrue($result->isOk());
    }

    public function test_valid_bulk_numbers()
    {
        $result = self::createSender()->send(['0123456789', '0123456780'], 'test message');

        self::assertTrue($result->isOk());
        self::assertEquals(2, $result->getTotalOk());
    }

    public function test_valid_some_numbers()
    {
        $result = self::createSender()->send(['0123456789', 'teest', 'andgroup'], 'test message');

        self::assertTrue($result->isOk());
        self::assertEquals(1, $result->getTotalOk());
    }

    public function test_blocked_ip()
    {
        $result = self::createSender()->send('0123456789', 'TestBlockIP');

        self::assertEquals('104', $result->getErrorCode());
    }

    public function test_invalid_sender_name()
    {
        $result = self::createSender()->send('0123456789', 'test', null, ['sender' => 'Test']);

        self::assertEquals('105', $result->getErrorCode());
    }

    public function test_no_credit_remain()
    {
        $result = self::createSender()->send('0123456789', 'TestNoCredit');

        self::assertEquals('106', $result->Queues[0]->getErrorCode());
    }

    public function test_internal_error()
    {
        $result = self::createSender()->send('0123456789', 'TestInternalError');

        self::assertEquals('107', $result->Queues[0]->getErrorCode());
    }
}
