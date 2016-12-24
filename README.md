# Yii2 omnikassa component

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

To install, either run

```
$ php composer.phar require edofre/yii2-omnikassa "V1.0.5"
```

or add

```
"edofre/yii2-omnikassa": "V1.0.5"
```

to the ```require``` section of your `composer.json` file.

## Usage

### Add the components to the configuration, the following configuration is the test environment for omnikassa
```php
return [
	...
	'components'          => [
		'omniKassa'    => [
			'class'                => '\edofre\omnikassa\OmniKassa',
			'automaticResponse'    => false,
			'currencyCode'         => '978',
			'interfaceVersion'     => 'HP_1.0',
			'keyVersion'           => '1',
			'merchantId'           => '002020000000001',
			'paymentMeanBrandList' => 'IDEAL,VISA,MASTERCARD,MAESTRO',
			'secretKey'            => '002020000000001_KEY1',
			'testMode'             => true,
			'url'                  => 'https://payment-webinit.simu.omnikassa.rabobank.nl/paymentServlet',
		],
		...
	],
	...
];
```

### Create the PaymentRequest object and create a form
```php
$paymentRequest = new \edofre\omnikassa\PaymentRequest([
	'amount'               => 12354, // Amount in cents, 12345 = 123,45
	'orderId'              => 'your-order-id',
	'normalReturnUrl'      => \yii\helpers\Url::to(['site/return'], true),
	'transactionReference' => "your-transaction-reference",
]);
Yii::$app->omniKassa->prepareRequest($paymentRequest);
```

```HTML+PHP
<form method="post" action="<?= Yii::$app->omniKassa->url ?>">
	<input type="hidden" name="Data" value="<?= Yii::$app->omniKassa->dataField ?>">
	<input type="hidden" name="InterfaceVersion" value="<?= Yii::$app->omniKassa->interfaceVersion ?>">
	<input type="hidden" name="Seal" value="<?= Yii::$app->omniKassa->seal ?>">
	<?= \yii\helpers\Html::submitButton('Click here to make your payment', ['class' => 'btn btn-success']) ?>
</form>
```

### Create the controller action you specified in the PaymentRequest and process the request
```php
public function actionReturn()
{
	$response = Yii::$app->omniKassa->processRequest();

	var_dump($response->attributes);
	var_dump('Pending', $response->isPending);
	var_dump('Successful', $response->isSuccessful);
	var_dump('Failure', $response->isFailure);
}
```

### Don't forgot to disable CSRF protection for this action as it is an external action
```php
public function beforeAction($action)
{
	if ($action->id == 'return') {
		$this->enableCsrfValidation = false;
	}

	return parent::beforeAction($action);
}
```