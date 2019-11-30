<?php


namespace App\Services\Common;

use Psr\Log\LoggerInterface;

class LogWriter
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function recordLog($msg, $log = 'info')
    {
        $this->logger->$log($msg);
    }
}