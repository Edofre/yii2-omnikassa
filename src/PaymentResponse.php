<?php

namespace edofre\omnikassa;

/**
 * Class PaymentResponse
 * @package edofre\omnikassa
 */
class PaymentResponse extends \yii\base\Model
{
    /** @var  string Total amount that the customer must pay, in cents, without decimal separator */
    public $amount;
    /** @var  string Identifier of the authorisation provided by the acquirer. Configured by the merchant/webshop for manual authorisation */
    public $authorisationId;
    /** @var  string The number of days after authorisation of a credit card transaction after which automatic validation of the transaction follows */
    public $captureDay;
    /** @var  string This can be used to indicate that the user of the Rabobank OmniKassa dashboard must manually validate credit card transactions after the automatic authorisation of this transaction */
    public $captureMode;
    /** @var  string Defines the currency of the transaction */
    public $currencyCode;
    /** @var  string Version number of the secret key */
    public $keyVersion;
    /** @var  string Identity of the merchant/webshop. This code is provided to each webshop after the Rabobank OmniKassa contract is signed */
    public $merchantId;
    /** @var  string Hidden Primary Account Number */
    public $maskedPan;
    /** @var  string This is an optional field that may be used to give the transaction a unique reference code */
    public $orderId;
    /** @var  string Brand name of payment method the customer has selected */
    public $paymentMeanBrand;
    /** @var  string A designation (DEBIT/CREDIT) of the payment method used (‘paymentMeanBrand’) */
    public $paymentMeanType;
    /** @var  string The status of the transaction. A numerical code is returned */
    public $responseCode;
    /** @var  string Time at which the payment is sent to the acquirer or the moment at which the response code is created on the Rabobank OmniKassa server */
    public $transactionDateTime;
    /** @var  string As provided in the payment request field. This unique ID allows the order to be retrieved by a search in the webshop */
    public $transactionReference;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'merchantId', 'transactionDateTime', 'transactionReference', 'keyVersion', 'authorisationId', 'responseCode'], 'required'],
        ];
    }

    /**
     * @return bool
     */
    public function getIsFailure()
    {
        return !$this->getIsSuccessFul() && !$this->getIsPending();
    }

    /**
     * @return bool
     */
    public function getIsSuccessFul()
    {
        return $this->responseCode == OmniKassa::STATUS_SUCCESSFUL;
    }

    /**
     * @return bool
     */
    public function getIsPending()
    {
        return $this->responseCode == OmniKassa::STATUS_AWAITING_STATUS_REPORT;
    }
}