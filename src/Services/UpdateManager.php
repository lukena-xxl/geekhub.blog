<?php


namespace App\Services;

use App\Services\Common\MailInformer;
use App\Services\Common\TelegramInformer;

class UpdateManager
{
    private $mailer;
    private $telegram;

    public function __construct(MailInformer $mailer, TelegramInformer $telegram)
    {
        $this->mailer = $mailer;
        $this->telegram = $telegram;
    }

    public function notifyOfUpdate($message)
    {
        $this->mailer->messageToEmail($message);
        $this->telegram->messageToTelegram($message);
    }
}