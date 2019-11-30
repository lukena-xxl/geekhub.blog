<?php


namespace App\Services;

use App\Services\Common\LogWriter;
use App\Services\Common\MailInformer;
use App\Services\Common\TelegramInformer;

class UpdateManager
{
    private $logger;
    private $mailer;
    private $telegram;

    public function __construct(LogWriter $logger, MailInformer $mailer, TelegramInformer $telegram)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->telegram = $telegram;
    }

    public function notifyOfUpdate($message)
    {
        $this->logger->recordLog($message);
        $this->mailer->messageToEmail($message);
        $this->telegram->messageToTelegram($message);
    }
}