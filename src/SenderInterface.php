<?php

declare(strict_types=1);

namespace PhpMob\ThaiBulkSms;

interface SenderInterface
{
    /**
     * @param $numbers
     * @param string $message
     * @param \DateTimeInterface|null $scheduled
     * @param array $options
     *
     * @return Result
     *
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function send($numbers, string $message, ?\DateTimeInterface $scheduled = null, array $options = []): Result;

    /**
     * @param array $options
     *
     * @return Result
     *
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function checkCredit(array $options = []): Result;
}
