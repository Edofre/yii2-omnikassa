<?php

namespace edofre\omnikassa;

/**
 * Class PaymentRequest
 *
 * @package edofre\omnikassa
 */
class PaymentRequest extends \yii\base\Model
{
	/** @var  integer $12,52 would be 1252 */
	public $amount;

	/** @var  string The url omnikassa will automatically push a repsonse to */
	public $automaticResponseUrl;

	/**
	 * @var  integer
	 *
	 * The number of days after authorisation of a credit card transaction
	 * after which automatic validation of the transaction follows
	 */
	public $captureDay;

	/** @var string This can be used to indicate that the user of the Rabo OmniKassa dashboard must manually validate credit card transactions after the automatic authorisation of this transaction */
	public $captureMode;

	/** @var  string Defines the currency of the transaction. */
	public $currencyCode;

	/** @var  string Language in which the Rabo OmniKassa payment page should be displayed. */
	public $customerLanguage;

	/** @var  string example: 2016-02-20T01:12:34-05:00 */
	public $expirationDate;

	/** @var  string The URL to which the customer is redirected after payment. POST data is sent to this URL to verify the transaction status (return-URL) */
	public $normalReturnUrl;

	/** @var  string This is an optional field that may be used to give the transaction a unique reference code */
	public $orderId;

	/** @var  string List of payment methods from which the customer can choose on the Rabo OmniKassa payment page */
	public $paymentMeanBrandList;

	/** @var  string The unique reference to the */
	public $transactionReference;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['amount', 'normalReturnUrl', 'transactionReference'], 'required'],
			[['currencyCode'], 'string', 'length' => 3],
			[['customerLanguage'], 'string', 'length' => 2],
			[['captureMode'], 'string', 'length' => 20],
			[['expirationDate'], 'date', 'format' => 'yyyy-MM-dd\'T\'HH:mm:ssxxx'],
			[['orderId', 'transactionReference'], 'string', 'max' => 32],
			[['amount'], 'integer'],
			[['automaticResponseUrl', 'normalReturnUrl', 'paymentMeanBrandList'], 'string', 'max' => 512],
			[['captureDay'], 'integer', 'max' => 99],
		];
	}
}