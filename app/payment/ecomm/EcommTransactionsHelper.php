<?php

namespace App\Payment\EcommMerchant;

use App\Model\Entity\EcommBatch;
use App\Model\Entity\EcommError;
use App\Model\Entity\EcommTransaction;
use App\Model\Repository\EcommBatchs;
use App\Model\Repository\EcommErrors;
use App\Model\Repository\EcommTransactions;
use App\Exceptions\PaymentException;
use DateTime;
use EcommMerchant\Merchant;

/**
 * Wrapper around EcommMerchant client and database operations above transaction
 * entities. This helper should be used for internal application transactions
 * and also for external transactions, all database operations are general and
 * have no effect on the non-payment parts of the application.
 */
class EcommTransactionsHelper
{
    /** @var EcommParams */
    private $ecommParams;
    /** @var EcommBatchs */
    private $ecommBatchs;
    /** @var EcommErrors */
    private $ecommErrors;
    /** @var EcommTransactions */
    private $ecommTransactions;

    /** @var Merchant */
    private $merchant;

    /**
     * DI Constructor
     * @param EcommParams $ecommParams
     * @param EcommBatchs $ecommBatchs
     * @param EcommErrors $ecommErrors
     * @param EcommTransactions $ecommTransactions
     */
    public function __construct(
        EcommParams $ecommParams,
        EcommBatchs $ecommBatchs,
        EcommErrors $ecommErrors,
        EcommTransactions $ecommTransactions
    ) {
        $this->ecommParams = $ecommParams;
        $this->ecommBatchs = $ecommBatchs;
        $this->ecommErrors = $ecommErrors;
        $this->ecommTransactions = $ecommTransactions;

        $this->merchant = new Merchant(
            $this->ecommParams->serverUrl,
            $this->ecommParams->certPath,
            $this->ecommParams->certPass
        );
    }

    /**
     * Start new payment transaction by calling payment gateway API which will
     * return transaction identification and further details which are stored
     * in database as started transaction. In case of any error appropriate
     * data are again stored in transaction error entity.
     * @param string $description description of the transaction
     * @param int $amount amount in the lowest possible part of currency
     * @param string $ip ip address of the client which want to pay
     * @param string $currency currency code
     * @param string $lang language shortcut
     * @return array($url, $transaction) Redirection URL where payment gateway
     * is placed and transaction entity.
     * @throws PaymentException if transaction initialization failed
     */
    public function startTransaction(
        string $description,
        int $amount,
        string $ip,
        $currency,
        $lang
    ): array {

        // create merchant and register transaction
        $response = $this->merchant->startSMSTrans($amount, $currency, $ip, $description, $lang);

        // process response from ecomm server
        if (substr($response, 0, 14) == "TRANSACTION_ID") {
            $transactionId = substr($response, 16, 28);
            $url = $this->getTransactionUrl($transactionId);

            $transaction = new EcommTransaction(
                $transactionId,
                $amount,
                $currency,
                $ip,
                $description,
                $lang,
                $response
            );
            $this->ecommTransactions->persist($transaction);

            return array($url, $transaction);
        } else {
            $error = new EcommError('startSmsTrans', $response);
            $this->ecommErrors->persist($error);

            throw new PaymentException('Transaction initialization failed, error logged');
        }
    }

    /**
     * After payment user was redirected to the transaction ok page and now is
     * time to check transaction status, if it was correct and money were
     * transmitted. If transaction was correct and is paid, then true is
     * returned, if transaction was not paid false is returned. Appropriate
     * payment gateway response and results are stored into database.
     * @param EcommTransaction $transaction doctrine transaction entity
     * @return boolean True if transaction was successful, false if transaction
     * was incorrect.
     * @throws PaymentException if transaction failed critically
     */
    public function processTransactionOk(EcommTransaction $transaction)
    {
        $response = $this->merchant->getTransResult(
            urlencode($transaction->getTransId()),
            $transaction->getClientIpAddr()
        );

        if (strstr($response, 'RESULT:')) {
            $transInfo = $this->getInfoFromOkResponse($response);

            // write information about transaction into db
            $transaction->setTransEndDate(new DateTime);
            $transaction->setResult($transInfo["result"]);
            $transaction->setResultCode($transInfo['resultCode']);
            $transaction->setResult3dsecure($transInfo['result3dsecure']);
            $transaction->setCardNumber($transInfo['cardNumber']);
            $transaction->setResponse($response);
            $this->ecommTransactions->flush();

            return $this->isSuccessfullTransactionCorrect($transInfo);
        } else {
            $error = new EcommError('returnOkURL', $response);
            $this->ecommErrors->persist($error);

            throw new PaymentException('Transaction failed');
        }
    }

    /**
     * If transaction failed quite badly then user will be redirected by the
     * payment gateway client to transaction fail page in this application and
     * therefore it is needed to check the state of the transaction and store
     * results in the database for further consideration.
     * @param EcommTransaction $transaction doctrine transaction entity
     * @param string $errorMsg error message given by the payment gateway
     */
    public function processTransactionFail(EcommTransaction $transaction, string $errorMsg)
    {
        // get information from merchant and store them in database
        $response = $this->merchant->getTransResult(
            urlencode($transaction->getTransId()),
            $transaction->getClientIpAddr()
        );
        $response = $errorMsg . ' + ' . $response;

        $error = new EcommError('returnFailURL', $response);
        $this->ecommErrors->persist($error);
    }

    /**
     * Reverse given transaction by calling payment gateway server and storing
     * results in the database.
     * @param EcommTransaction $transaction doctrine transaction entity
     * @throws PaymentException if reversal failed
     */
    public function reverseTransaction(EcommTransaction $transaction)
    {
        // get merchant and reverse transaction
        $amount = $transaction->getAmount();
        $response = $this->merchant->reverse(urlencode($transaction->getTransId()), $amount);

        if (substr($response, 8, 2) == "OK" || substr($response, 8, 8) == "REVERSED") {
            $reverseInfo = $this->getInfoFromReverseResponse($response);

            $transaction->setReversalAmount($amount);
            $transaction->setResultCode($reverseInfo["resultCode"]);
            $transaction->setResult($reverseInfo["result"]);
            $transaction->setResponse($response);
            $this->ecommTransactions->flush();
        } else {
            $error = new EcommError('reverse', $response);
            $this->ecommErrors->persist($error);

            throw new PaymentException('Transaction reversal failed');
        }
    }

    /**
     * Close business day, should be invoked once a day by cron. Every closure
     * is stored in batch entities alongside with its results.
     * @throws PaymentException in case of any error
     */
    public function closeBusinessDay()
    {
        $response = $this->merchant->closeDay();

        if (strstr($response, 'RESULT:')) {
            // RESULT: OK RESULT_CODE: 500 FLD_075: 4 FLD_076: 6 FLD_087: 40 FLD_088: 60

            $result = $this->getInfoFromResp('RESULT: ', $response);
            $resultCode = $this->getInfoFromResp('RESULT_CODE: ', $response);
            $countReversal = $this->getInfoFromResp('FLD_075: ', $response);
            $countTransaction = $this->getInfoFromResp('FLD_076: ', $response);
            $amountReversal = $this->getInfoFromResp('FLD_087: ', $response);
            $amountTransaction = $this->getInfoFromResp('FLD_088: ', $response);

            $batch = new EcommBatch(
                $result,
                $resultCode,
                $countReversal,
                $countTransaction,
                $amountReversal,
                $amountTransaction,
                $response
            );
            $this->ecommBatchs->persist($batch);
        } else {
            $error = new EcommError('closeDay', $response);
            $this->ecommErrors->persist($error);

            throw new PaymentException('Close business day failed');
        }
    }

    /**
     * In the given response find key $expl and parse its value.
     * @param string $expl key which should be found
     * @param string $response
     * @return string value of the given key
     */
    private function getInfoFromResp($expl, $response)
    {
        if (strstr($response, $expl)) {
            $result = explode($expl, $response);
            $result = preg_split('/\r\n|\r|\n/', $result[1]);
            $result = $result[0];
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * From the given transaction ok reponse string parse appropriate data into
     * associative array.
     * @param string $response response from payment gateway server
     * @return array transaction response data
     */
    private function getInfoFromOkResponse($response)
    {
        $result = $this->getInfoFromResp('RESULT: ', $response);
        $resultCode = $this->getInfoFromResp('RESULT_CODE: ', $response);
        $result3dsecure = $this->getInfoFromResp('3DSECURE: ', $response);
        $cardNumber = $this->getInfoFromResp('CARD_NUMBER: ', $response);

        $resInfo = array();
        $resInfo['result'] = $result;
        $resInfo['resultCode'] = $resultCode;
        $resInfo['result3dsecure'] = $result3dsecure;
        $resInfo['cardNumber'] = $cardNumber;
        return $resInfo;
    }

    /**
     * From the given reversal response string parse appropriate data into
     * associative array.
     * @param string $response response from payment gateway server
     * @return array reversal response data
     */
    private function getInfoFromReverseResponse($response)
    {
        $result = $this->getInfoFromResp('RESULT: ', $response);
        $resultCode = $this->getInfoFromResp('RESULT_CODE: ', $response);

        $resInfo = array();
        $resInfo['result'] = $result;
        $resInfo['resultCode'] = $resultCode;
        return $resInfo;
    }

    /**
     * Check if given transaction info corresponds to successful transaction.
     * @param array $transInfo parsed transaction info
     * @return boolean true if transaction is successful
     */
    private function isSuccessfullTransactionCorrect($transInfo)
    {
        if ($transInfo['result'] == 'OK' &&
                    strlen($transInfo['resultCode']) == 3 &&
                    $transInfo['resultCode'][0] == '0') {
            return true;
        }

        return false;
    }

    /**
     * Get URL of the payment gateway client to which users should be
     * redirected.
     * @param string $transId transaction identification
     * @return string URL
     */
    private function getTransactionUrl($transId)
    {
        return $this->ecommParams->clientUrl . "?trans_id=" . urlencode($transId);
    }
}
