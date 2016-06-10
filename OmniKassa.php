<?php

namespace edofre\omnikassa;

/**
 * Class OmniKassa
 *
 * @package edofre\omnikassa
 */
class OmniKassa extends \yii\base\Component
{
	/**
	 * Basic constants that determine the status of the transaction.
	 * All other statuses will be handled as failures
	 */
	const STATUS_SUCCESSFUL = '00';
	const STATUS_AWAITING_STATUS_REPORT = '60';

	/** @var   */
	public $dataField;
	/** @var   */
	public $seal;
	/** @var  bool */
	private $automaticResponse = false;
	/**
	 * @var  string
	 *
	 * The following options are available
	 * Euro = 978
	 * American Dollar = 840
	 * Swiss Franc = 756
	 * Pound Sterling = 826
	 * Canadian Dollar = 124
	 * Japanese Yen = 392
	 * Australian Dollar = 036
	 * Norwegian Crown = 578
	 * Swedish Crown = 752
	 * Danish Crown = 208
	 */
	private $currencyCode = '978';
	/**
	 * @var  string
	 *
	 * The following options are available
	 * CS = Czech
	 * CY = Welsh
	 * DE = German
	 * EN = English
	 * ES = Spanish
	 * FR = French
	 * NL = Dutch
	 * SK = Slovak
	 *
	 * I this field is not included in the payment request,
	 * then the payment page will be displayed by default
	 * in the language (setting) of the browser your customer is using at that time
	 */
	private $customerLanguage;
	/** @var  string The version of the */
	private $interfaceVersion = 'HP_1.0';
	/** @var  string */
	private $keyVersion = 1;
	/** @var  string Identity of the merchant/webshop */
	private $merchantId = '002020000000001';
	/**
	 * @var  string
	 *
	 * The following options are available
	 * IDEAL
	 * VISA
	 * MASTERCARD
	 * MAESTRO
	 * VPAY
	 * BCMC
	 * MINITIX
	 * INCASSO (direct debit)
	 * ACCEPTGIRO (giro collection form)
	 * REMBOURS (cash on delivery)
	 */
	private $paymentMeanBrandList = 'IDEAL, VISA, MASTERCARD, MAESTRO, VPAY, BCMC, MINITIX, INCASSO. ACCEPTGIRO, REMBOURS';
	/** @var  string */
	private $secretKey = '002020000000001_KEY1';
	/** @var  bool */
	private $testMode = true;
	/** @var  string */
	private $url = 'https://payment-webinit.simu.omnikassa.rabobank.nl/paymentServlet';

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		parent::init();

		// Make sure the configuration is complete
		if (
			empty($this->url) ||
			empty($this->interfaceVersion) ||
			empty($this->merchantId) ||
			empty($this->secretKey) ||
			empty($this->keyVersion)
		) {
			throw new \yii\base\InvalidConfigException('Invalid config for OmniKassa components. Make sure url, interfaceVersion, merchantId, secretKey and keyVersion are all set');
		}
	}

	/**
	 *
	 */
	public function prepareRequest(PaymentRequest $paymentRequest)
	{
		// Make sure we have a valid PaymentRequest
		if ($paymentRequest->validate()) {
			// Prepare the data so we can create a seal
			$data = [
				'amount'               => $paymentRequest->amount,
				'automaticResponseUrl' => $paymentRequest->automaticResponseUrl,
				'captureDay'           => $paymentRequest->captureDay,
				'captureMode'          => $paymentRequest->captureMode,
				'currencyCode'         => empty($paymentRequest->currencyCode) ? $this->currencyCode : $paymentRequest->currencyCode,
				'customerLanguage'     => empty($paymentRequest->customerLanguage) ? $this->customerLanguage : $paymentRequest->customerLanguage,
				'expirationDate'       => $paymentRequest->expirationDate,
				'keyVersion'           => $this->keyVersion,
				'merchantId'           => $this->merchantId,
				'normalReturnUrl'      => $paymentRequest->normalReturnUrl,
				'orderId'              => $paymentRequest->orderId,
				'paymentMeanBrandList' => empty($paymentRequest->paymentMeanBrandList) ? $this->paymentMeanBrandList : $paymentRequest->paymentMeanBrandList,
				'transactionReference' => $paymentRequest->transactionReference,
			];

			$this->dataField = [];
			foreach ($data as $key => $value) {
				if (!empty($value)) {
					$this->dataField[] = "$key=$value";
				}
			}
			$this->dataField = implode('|', $this->dataField);
			$this->seal = hash('sha256', $this->dataField . $this->secretKey);
		} else {
			throw new \yii\base\InvalidConfigException('Invalid config for PaymentRequest object, check requirements');
		}
	}

	/**
	 *
	 */
	public function processRequest()
	{
		$dataField = \Yii::$app->request->post('Data');
		$interfaceVersion = \Yii::$app->request->post('InterfaceVersion');
		$seal = \Yii::$app->request->post('Seal');

		// Check InterfaceVersion and validate Data-field integrity
		if (
			$dataField === null ||
			$interfaceVersion !== $this->interfaceVersion ||
			hash('sha256', $dataField . $this->secretKey) !== $seal
		) {
			throw new \yii\web\BadRequestHttpException;
		}

		// Parse Data-field
		$data = [];
		$dataField = explode('|', $dataField);
		foreach ($dataField as $concat) {
			$field = explode('=', $concat, 2);
			if (count($field) == 2) {
				$data[$field[0]] = $field[1];
			}
		}

		// Create the response object and make sure everything's there
		$paymentResponse = new PaymentResponse($data);
		if ($paymentResponse->validate()) {
			return $paymentResponse;
		}

		throw new \yii\web\BadRequestHttpException;
	}

	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param mixed $url
	 */
	protected function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @return mixed
	 */
	public function getInterfaceVersion()
	{
		return $this->interfaceVersion;
	}

	/**
	 * @param mixed $interfaceVersion
	 */
	protected function setInterfaceVersion($interfaceVersion)
	{
		$this->interfaceVersion = $interfaceVersion;
	}

	/**
	 * @param mixed $merchantId
	 */
	protected function setMerchantId($merchantId)
	{
		$this->merchantId = $merchantId;
	}

	/**
	 * @param mixed $secretKey
	 */
	protected function setSecretKey($secretKey)
	{
		$this->secretKey = $secretKey;
	}

	/**
	 * @param mixed $keyVersion
	 */
	protected function setKeyVersion($keyVersion)
	{
		$this->keyVersion = $keyVersion;
	}

	/**
	 * @param mixed $testMode
	 */
	protected function setTestMode($testMode)
	{
		$this->testMode = $testMode;
	}

	/**
	 * @param mixed $automaticResponse
	 */
	protected function setAutomaticResponse($automaticResponse)
	{
		$this->automaticResponse = $automaticResponse;
	}

	/**
	 * @param mixed $currencyCode
	 */
	protected function setCurrencyCode($currencyCode)
	{
		$this->currencyCode = $currencyCode;
	}

	/**
	 * @param mixed $paymentMeanBrandList
	 */
	protected function setPaymentMeanBrandList($paymentMeanBrandList)
	{
		$this->paymentMeanBrandList = $paymentMeanBrandList;
	}

	/**
	 * @param mixed $customerLanguage
	 */
	protected function setCustomerLanguage($customerLanguage)
	{
		$this->customerLanguage = $customerLanguage;
	}

	/**
	 * @return mixed
	 */
	private function getMerchantId()
	{
		return $this->merchantId;
	}

	/**
	 * @return mixed
	 */
	private function getSecretKey()
	{
		return $this->secretKey;
	}

	/**
	 * @return mixed
	 */
	private function getKeyVersion()
	{
		return $this->keyVersion;
	}

	/**
	 * @return mixed
	 */
	private function getTestMode()
	{
		return $this->testMode;
	}

	/**
	 * @return mixed
	 */
	private function getAutomaticResponse()
	{
		return $this->automaticResponse;
	}

	/**
	 * @return mixed
	 */
	private function getCurrencyCode()
	{
		return $this->currencyCode;
	}

	/**
	 * @return mixed
	 */
	private function getPaymentMeanBrandList()
	{
		return $this->paymentMeanBrandList;
	}

	/**
	 * @return mixed
	 */
	private function getCustomerLanguage()
	{
		return $this->customerLanguage;
	}

}