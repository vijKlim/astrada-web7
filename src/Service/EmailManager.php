<?php

namespace App\Service;


use App\Entity\Booking;
use App\Entity\Invitation;
use App\Entity\LocalBusiness;
use NotFloran\MjmlBundle\Renderer\RendererInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;

class EmailManager
{
    private $mailer;
    private $templating;
    private $mjml;
    private $translator;
    private $settingsManager;
    private $transactionalAddress;

    public function __construct(
        MailerInterface $mailer,
        TwigEnvironment $templating,
        RendererInterface $mjml,
        TranslatorInterface $translator,
        SettingsManager $settingsManager,
        $transactionalAddress)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->mjml = $mjml;
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->transactionalAddress = $transactionalAddress;
    }

    private function getFrom(): Address
    {
        return Address::fromString(
            sprintf('%s <%s>', $this->settingsManager->get('brand_name'), $this->transactionalAddress)
        );
    }

    private function getReplyTo(): Address
    {
        return Address::fromString(
            sprintf('%s <%s>', $this->settingsManager->get('brand_name'), $this->settingsManager->get('administrator_email'))
        );
    }

    public function createHtmlMessage($subject = null, $body = null): Email
    {
        $message = (new Email())
            ->subject($subject)
            ->sender($this->getFrom())
            ->from($this->getFrom());

        if ($body) {
            $message = $message->html($body);
        }

        return $message;
    }

    public function createHtmlMessageWithReplyTo($subject = null, $body = null): Email
    {
        // Allow replying to the administrator
        return $this->createHtmlMessage($subject, $body)
            ->replyTo($this->getReplyTo());
    }

    public function send(Email $message)
    {
        $addresses = [];
        foreach ($message->getTo() as $address) {
            if (1 === preg_match('/demo\.aquastrada\.com$/', $address->getAddress())) {
                continue;
            }
            $addresses[] = $address;
        }

        if (count($addresses) === 0) {
            return;
        }

        $message->to(...$addresses);

        $this->mailer->send($message);
    }

    /**
     * @param Email $message
     * @param Address|string ...$to
     */
    public function sendTo(Email $message, ...$to)
    {
        $this->send($message->to(...$to));
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestMessageToAsker(Booking $booking)
    {

    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestMessageToOfferer(Booking $booking)
    {
//        $listing = $booking->getListing();
//        $business = $listing->getBusiness();
//        $users = $business->getOwners();
//
//        foreach ($users as $user){
//
//        }

    }

    /**
     * @param Booking $booking
     */
    public function sendBookingAcceptedMessageToAsker(Booking $booking)
    {

    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestExpiredMessageToAsker(Booking $booking)
    {

    }

    /**
     * @param Booking $booking
     */
    public function sendBookingCanceledByAskerMessageToAsker(Booking $booking)
    {

    }

}
