<?php

namespace App\Payment;

use Nette;
use App\Exceptions\PaymentException;
use App\Model\Entity\EcommTransaction;
use App\Model\Entity\EventParticipant;
use App\Model\Entity\Event;
use App\Model\Repository\EventParticipants;
use App\Model\Repository\EcommTransactions;
use App\Payment\EcommMerchant\EcommTransactionsHelper;

/**
 * Helper class for internal application transactions concerning events. It
 * provides additional functionality, mainly binding event participants and
 * transaction and correct detection of when participant successfuly paid or
 * not.
 */
class EventsTransactionsHelper
{
    /** @var EcommTransactionsHelper */
    private $ecommTransactionsHelper;
    /** @var EventParticipants */
    private $eventParticipants;
    /** @var EcommTransactions */
    private $ecommTransactions;
    /** @var Nette\Http\Request */
    private $httpRequest;
    /** @var PaymentParams */
    private $paymentParams;

    /**
     * DI Constructor.
     * @param Nette\Http\Request $request
     * @param EcommTransactionsHelper $ecommTransactionsHelper
     * @param EventParticipants $eventParticipants
     * @param \App\Payment\PaymentParams $paymentParams
     */
    public function __construct(
        Nette\Http\Request $request,
        EcommTransactionsHelper $ecommTransactionsHelper,
        EventParticipants $eventParticipants,
        PaymentParams $paymentParams,
        EcommTransactions $ecommTransactions
    ) {
        $this->httpRequest = $request;
        $this->ecommTransactionsHelper = $ecommTransactionsHelper;
        $this->eventParticipants = $eventParticipants;
        $this->paymentParams = $paymentParams;
        $this->ecommTransactions = $ecommTransactions;
    }

    /**
     * Start transaction for given event and its participant. Construct
     * description price and obtain ip address of user. If start of the
     * transaction is successful then participant and transaction are connected
     * and can be further processed.
     * @param Event $event
     * @param EventParticipant $participant participant which wants to pay
     * @return string Redirection URL where payment gateway is placed.
     * @throws PaymentException in case of transaction start error
     */
    public function startTransaction(Event $event, EventParticipant $participant): string
    {
        // description which will be visible on transaction details
        $desc = "***CUK Payment***;" .
                "EvID=" . $event->id . ";" .
                "EvName=" . substr($event->eventName, 0, 20) . ";" .
                "IncID=" . $participant->user->id . ";" .
                "IncUname=" . substr($participant->user->username, 0, 20) . ";";
        $desc = substr($desc, 0, 125);
        $amount = $event->price . '00';
        $ip = $this->httpRequest->getRemoteAddress();

        list($url, $transaction) =
                $this->ecommTransactionsHelper->startTransaction(
                    $desc,
                    $amount,
                    $ip,
                    $this->paymentParams->currency,
                    $this->paymentParams->language
                );

        // let us know that this transaction is binded with appropriate participant
        $transaction->eventParticipant = $participant;
        $this->ecommTransactions->flush();

        return $url;
    }

    /**
     * Process transaction which was marked as ok by the payment gateway server,
     * transaction can be correct and paid or incorrect. If the transaction is
     * correct then appropriate participant connected with transaction is
     * marked as paid for appropriate event.
     * @param EcommTransaction $transaction
     * @throws PaymentException in case of any connection error
     */
    public function processTransactionOk(EcommTransaction $transaction)
    {
        $correct = $this->ecommTransactionsHelper->processTransactionOk($transaction);
        if ($correct) {
            // just let us know that transaction is ok to our database
            $participant = $transaction->eventParticipant;
            $participant->paid = true;
            $this->eventParticipants->flush();
        }

        return $correct;
    }

    /**
     * Reverse given transaction for the given participant. Participant payment
     * is marked as not paid just to be sure.
     * @param EventParticipant $participant event participant connected to given
     * transaction
     * @param EcommTransaction $transaction
     * @throws PaymentException in case of reversal error
     */
    public function reverseTransaction(EventParticipant $participant, EcommTransaction $transaction)
    {
        $this->ecommTransactionsHelper->reverseTransaction($transaction);

        $participant->paid = false;
        $this->eventParticipants->flush();
    }
}
