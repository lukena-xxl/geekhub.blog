<?php


namespace App\Services\Common;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailInformer
{
    private $mailer;
    private $email_admin;
    private $email_transfer;

    public function __construct(MailerInterface $mailer, $email_admin, $email_transfer)
    {
        $this->mailer = $mailer;
        $this->email_admin = $email_admin;
        $this->email_transfer = $email_transfer;
    }

    public function messageToEmail($msg, $subject = 'Announcement')
    {
        $email = (new Email())
            ->from($this->email_transfer)
            ->to($this->email_admin)
            ->subject($subject)
            ->text($msg);
        //->html('');

        $this->mailer->send($email);
    }
}
