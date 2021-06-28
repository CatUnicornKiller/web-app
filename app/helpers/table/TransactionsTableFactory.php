<?php

namespace App\Helpers\Table;

use App\Model\Repository\EventParticipants;
use App\Model\Repository\EcommTransactions;

/**
 * Factory which is able to generate tables concerning gateway transactions and
 * their further information.
 */
class TransactionsTableFactory
{
    /** @var EventParticipants */
    private $eventParticipants;
    /** @var EcommTransactions */
    private $ecommTransactions;

    /**
     * DI Constructor.
     * @param EventParticipants $eventParticipants
     * @param EcommTransactions $ecommTransactions
     */
    public function __construct(
        EventParticipants $eventParticipants,
        EcommTransactions $ecommTransactions
    ) {
        $this->eventParticipants = $eventParticipants;
        $this->ecommTransactions = $ecommTransactions;
    }

    /**
     * Create table containing further information about given transactions.
     * @return string csv file content, can be sent as response
     */
    public function createTransactionsTable(array $transactionsList)
    {
        $content = "Transaction Start;Transaction End;Firstname;Surname;Amount;Currency;Paid;Event Name\n";
        foreach ($transactionsList as $transId) {
            $trans = $this->ecommTransactions->findOrThrow($transId);
            $participate = $trans->getEventParticipant();

            $paid = "No";
            $firstname = "";
            $surname = "";
            $eventName = "";
            if ($participate) {
                $firstname = $participate->getUser()->getFirstname();
                $surname = $participate->getUser()->getSurname();
                $eventName = $participate->getEvent()->getEventName();
            }

            $endDate = "";
            if ($trans->getTransEndDate()) {
                $endDate = $trans->getTransEndDate()->format("d.m.Y H:i:s");
            }
            if ($trans->isOk()) {
                $paid = "Yes";
            }

            $content .= $trans->getTDate()->format("d.m.Y H:i:s") . ";";
            $content .= $endDate . ";";
            $content .= $firstname . ";";
            $content .= $surname . ";";
            $content .= ($trans->getAmount() / 100) . ";";
            $content .= "CZK;";
            $content .= $paid . ";";
            $content .= "\"" . $eventName . "\";";
            $content .= "\n";
        }

        return $content;
    }
}
