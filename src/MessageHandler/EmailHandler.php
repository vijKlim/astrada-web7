<?php

namespace App\MessageHandler;

use App\Message\Email;
use App\Service\EmailManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailHandler implements MessageHandlerInterface
{
    private EmailManager $emailManager;

    public function __construct(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
    }

    public function __invoke(Email $message)
    {
        $this->emailManager->sendTo($message->getMessage(), $message->getTo());
    }
}
