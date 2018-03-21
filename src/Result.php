<?php

declare(strict_types=1);

namespace PhpMob\ThaiBulkSms;

final class Result
{
    const ERRORS = [
        '000' => 'N/A',
        '100' => 'Unable Authentication, No username provided.',
        '101' => 'Unable Authentication, Ref #2',
        '102' => 'MSISDN Error, No msisdn provided.',
        '103' => 'Message Error, No message provided.',
        '104' => 'Unable Authentication, Your IP Address has been blocked.',
        '105' => 'Invalid Sender.',
        '106' => 'No Credit',
        '107' => 'Internal Error Ref #3',
    ];

    /**
     * @var int
     */
    public $Status = 0;

    /**
     * @var string
     */
    public $Detail;

    /**
     * @var string
     */
    public $Msisdn;

    /**
     * @var string
     */
    public $Transaction;

    /**
     * @var string
     */
    public $UsedCredit;

    /**
     * @var string
     */
    public $RemainCredit;

    /**
     * @var Result[]
     */
    public $Queues = [];

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return 1 === intval($this->Status);
    }

    /**
     * @return int
     */
    public function getTotalOk(): int
    {
        if (empty($this->Queues)) {
            return $this->isOk() ? 1 : 0;
        }

        $total = 0;

        foreach ($this->Queues as $queue) {
            if ($queue->isOk()) {
                $total++;
            }
        }

        return $total;
    }

    /**
     * @return int
     */
    public function getTotalError(): int
    {
        if (empty($this->Queues)) {
            return $this->isOk() ? 0 : 1;
        }

        return count($this->Queues) - $this->getTotalOk();
    }

    /**
     * @return null|string
     */
    public function getErrorCode(): ?string
    {
        if ($this->isOk()) {
            return null;
        }

        $code = (string)array_search($this->Detail, self::ERRORS);

        if (empty($code)) {
            return '000';
        };

        return $code;
    }
}
