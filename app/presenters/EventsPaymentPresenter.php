<?php

namespace App\Presenters;

use App;
use App\Exceptions\PaymentException;
use App\Model\Repository\Events;
use App\Model\Repository\EventParticipants;
use App\Model\Repository\EcommTransactions;
use App\Payment\EventsTransactionsHelper;
use App\Payment\ExternalTransactionsHelper;
use App\Payment\PaymentParams;
use App\Payment\EcommMerchant\EcommTransactionsHelper;
use Nette;

/**
 * Events presenter.
 */
class EventsPaymentPresenter extends BasePresenter
{
    /**
     * @var EventsTransactionsHelper
     * @inject
     */
    public $eventsTransactionsHelper;
    /**
     * @var ExternalTransactionsHelper
     * @inject
     */
    public $externalTransactionsHelper;
    /**
     * @var EcommTransactionsHelper
     * @inject
     */
    public $ecommTransactionsHelper;
    /**
     * @var App\Forms\TransactionsFormsFactory
     * @inject
     */
    public $transactionsFormsFactory;
    /**
     * @var App\Helpers\ResponseHelper
     * @inject
     */
    public $responseHelpers;
    /**
     * @var App\Helpers\Table\TransactionsTableFactory
     * @inject
     */
    public $transactionsTableFactory;
    /**
     * @var Events
     * @inject
     */
    public $events;
    /**
     * @var EcommTransactions
     * @inject
     */
    public $ecommTransactions;
    /**
     * @var EventParticipants
     * @inject
     */
    public $eventParticipants;
    /**
     * @var PaymentParams
     * @inject
     */
    public $paymentParams;

    private function createFilterTransactionsListAjaxForm($year, $month, $paid)
    {
        $form = $this->transactionsFormsFactory->createFilterTransactionsListForm($year, $month, $paid);
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    public function actionStartEventTransaction($id)
    {
        $event = $this->events->findOrThrow($id);
        if (!$this->isLoggedIn()) {
            $this->error("Access Denied");
        }

        $participant = $this->currentUser->getParticipatedEvent($event);
        if (!$participant) {
            $this->error("Non existing participation");
        }

        // incoming already paid for event
        if ($participant->paid != 0) {
            $this->error("Already paid event");
        }

        // price has to be at least 1 CZK
        if ($event->getPrice() < 1) {
            $this->error("Price of event is 0 CZK");
        }

        // check for deadline
        if (date_create() > $event->getSignupDeadline()) {
            $this->error("Signup deadline exceeded");
        }

        try {
            $url = $this->eventsTransactionsHelper->startTransaction($event, $participant);
            $this->redirectUrl($url);
        } catch (PaymentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function actionReverseEventTransaction($id)
    {
        /* *** NOT SUPPORTED *** */
        throw new PaymentException("Reversal not supported!");
        /* *** NOT SUPPORTED *** */

//        $transaction = $this->ecommTransactions->findOrThrow($id);
//        if (!$this->isLoggedIn() ||
//                !$this->user->isAllowed('EventTransaction', 'reverse')) {
//            $this->error("Access Denied");
//        }
//
//        $this->template->participant = $participant = $transaction->getEventParticipant();
//        if (!$participant) {
//            $this->error("Non existing participation");
//        }
//
//        try {
//            $this->eventsTransactionsHelper->reverseTransaction($participant, $transaction);
//        } catch (PaymentException $e) {
//            $this->error($e->getMessage());
//        }
    }

    public function actionTransactionOk()
    {
        if (!$this->request->isMethod('post')) {
            $this->error('Access Denied');
        }

        // get transaction
        $transId = $this->request->getPost('trans_id');
        $transaction = $this->ecommTransactions->findOneByTransactionId($transId);

        if ($transaction->isOk()) {
            $this->error("Already processed payment");
        }

        if ($transaction->isExternal()) {
            // if transaction is external, we need to redirect to appropriate service
            $externalService = $this->externalTransactionsHelper->findService($transaction->getExternalService());
            $this->ecommTransactionsHelper->processTransactionOk($transaction);
            $this->redirectUrl($externalService->getOkRedirectionUrl($transaction->getTransId()));
        } else {
            if (!$this->isLoggedIn()) {
                $this->error('Access Denied');
            }

            // events transaction
            $this->template->participate = $participant = $transaction->getEventParticipant();
            if ($participant->getPaid()) {
                $this->error("Already paid");
            }

            $correct = $this->eventsTransactionsHelper->processTransactionOk($transaction);
            if (!$correct) {
                // redirect to incorrect transaction
                $this->forward(
                    'EventsPayment:transactionIncorrect',
                    $transaction->getId()
                );
            }
        }
    }

    public function actionTransactionIncorrect($id)
    {
        if (!$this->isLoggedIn()) {
            $this->error('Access Denied');
        }

        $transaction = $this->ecommTransactions->findOrThrow($id);

        $this->template->result = $transaction->getResult();
        $this->template->participate = $transaction->getEventParticipant();
    }

    public function actionTransactionFail()
    {
        if (!$this->request->isMethod('post')) {
            $this->error('Access Denied');
        }

        // get params
        $transId = $this->request->getPost('trans_id');
        $errorMsg = $this->request->getPost('error');

        $transaction = $this->ecommTransactions->findOneByTransactionId($transId);
        if ($transaction->isExternal()) {
            // if transaction is external, we need to redirect to appropriate service
            $externalService = $this->externalTransactionsHelper->findService($transaction->getExternalService());
            $this->ecommTransactionsHelper->processTransactionFail($transaction, $errorMsg);
            $this->redirectUrl($externalService->getFailRedirectionUrl($transaction->getTransId(), $errorMsg));
        } else {
            if (!$this->isLoggedIn()) {
                $this->error('Access Denied');
            }

            $this->template->participate = $transaction->getEventParticipant();
            $this->ecommTransactionsHelper->processTransactionFail($transaction, $errorMsg);
        }
    }

    public function renderCloseBusinessDay($key)
    {
        if ($key != $this->paymentParams->publicAccessToken) {
            $this->error("Access Denied");
        }

        try {
            $this->ecommTransactionsHelper->closeBusinessDay();
        } catch (PaymentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function actionTransactionList($year, $month, $paid)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("EventTransaction", "view")) {
            $this->error("Access Denied");
        }

        $paidBool = $paid == "on";

        $this["filterTransactionsListForm"] =
                $this->createFilterTransactionsListAjaxForm($year, $month, $paidBool);

        $transactions = $this->ecommTransactions->getTransactionListQuery($year, $month, $paidBool)->getResult();
        $transactionsList = array();
        foreach ($transactions as $trans) {
            $transactionsList[] = $trans->id;
        }

        $this->template->transactions = $transactions;
        $this->template->transactionIdsList = $transactionsList;

        if ($this->isAjax()) {
            $this->redrawControl('transactionsList');
        }
    }

    public function actionTransactionDetail($id)
    {
        $transaction = $this->ecommTransactions->findOrThrow($id);
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("EventTransaction", "view")) {
            $this->error("Access Denied");
        }

        $this->template->transaction = $transaction;
        $this->template->participation = $transaction->getEventParticipant();
    }

    public function actionGenerateTransactionsTable(array $transactionsList)
    {
        if (!$this->isLoggedIn() || !$transactionsList ||
                !$this->user->isAllowed("EventTransaction", "view")) {
            $this->error("Access Denied");
        }

        $content = $this->transactionsTableFactory->createTransactionsTable($transactionsList);
        $this->responseHelpers->setCsvFileResponse($this->getHttpResponse(), 'table.csv');
        $this->sendResponse(new Nette\Application\Responses\TextResponse($content));
    }

    public function actionStartExternalTransaction($key)
    {
        if (!$this->request->isMethod('post') ||
                $key != $this->paymentParams->publicAccessToken) {
            $this->sendJson(array(
                "error" => "Access Denied"
            ));
        }

        $service = $this->request->getPost("service");
        $amount = $this->request->getPost("amount");
        $currency = $this->request->getPost("currency");
        $ipAddress = $this->request->getPost("ipAddress");
        $description = $this->request->getPost("description");

        if (!$amount || !ctype_digit($amount) || !$service ||
                !$ipAddress || !$description || !$currency) {
            $this->sendJson(array(
                "error" => "Bad Params"
            ));
        }

        try {
            list($url, $transaction) =
                    $this->externalTransactionsHelper->startTransaction(
                        $service,
                        $amount,
                        $ipAddress,
                        $description,
                        $currency
                    );
            $this->sendJson(array(
                "url" => $url,
                "transactionId" => $transaction->transId
            ));
        } catch (PaymentException $e) {
            $this->sendJson(array(
                "error" => $e->getMessage()
            ));
        }
    }

    public function actionGetExternalTransactionResult($key)
    {
        if (!$this->request->isMethod('post') ||
                $key != $this->paymentParams->publicAccessToken) {
            $this->sendJson(array(
                "error" => "Access Denied"
            ));
        }

        $service = $this->request->getPost("service");
        $transactionId = $this->request->getPost("transactionId");

        $transaction = $this->ecommTransactions->findOneByTransactionId($transactionId);
        $this->externalTransactionsHelper->findService($service); // just checks if service exists

        if (!$transaction) {
            $this->sendJson(array(
                "error" => "Bad Params"
            ));
        }

        $this->sendJson(array(
            "transactionId" => $transaction->getTransId(),
            "result" => $transaction->getResult(),
            "resultCode" => $transaction->getResultCode(),
            "result3dsecure" => $transaction->getResult3dsecure(),
            "cardNumber" => $transaction->getCardNumber()
        ));
    }
}
