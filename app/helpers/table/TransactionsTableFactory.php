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
            $participate = $trans->eventParticipant;

            $paid = "No";
            $firstname = "";
            $surname = "";
            $eventName = "";
            if ($participate) {
                $firstname = $participate->user->firstname;
                $surname = $participate->user->surname;
                $eventName = $participate->event->eventName;
            }

            $endDate = "";
            if ($trans->transEndDate) {
                $endDate = $trans->transEndDate->format("d.m.Y H:i:s");
            }
            if ($trans->isOk()) {
                $paid = "Yes";
            }

            $content .= $trans->tDate->format("d.m.Y H:i:s") . ";";
            $content .= $endDate . ";";
            $content .= $firstname . ";";
            $content .= $surname . ";";
            $content .= ($trans->amount / 100) . ";";
            $content .= "CZK;";
            $content .= $paid . ";";
            $content .= "\"" . $eventName . "\";";
            $content .= "\n";
        }

        return $content;
    }
}
