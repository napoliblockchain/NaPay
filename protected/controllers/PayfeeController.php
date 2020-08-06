<?php
class PayfeeController extends Controller
{
	public function init()
	{
		#echo "<pre>".print_r(Yii::app()->user->objUser,true)."</pre>";
		#exit;
		//vado alla pagina di login se non autorizzato
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'quota')
		{
			if (Yii::app()->user->objUser['facade'] != 'dashboard'){
				Yii::app()->user->logout();
				$this->redirect('index.php?r=site/index');
			}
		}


		$settings = Settings::load();
		define("PAYPAL_CLIENT_ID", $settings->PAYPAL_CLIENT_ID);
		define("PAYPAL_CLIENT_SECRET", $settings->PAYPAL_CLIENT_SECRET);
		define("PAYPAL_MODE", $settings->PAYPAL_MODE);
	}


	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','review','bitcoinInvoice','paypalInvoice'),
				'users'=>array('@'),
			),
		);
	}


	public function actionIndex()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] == 'dashboard'){
			$renderfile = 'int_';
		}else{
			$this->layout='//layouts/column_login';
			$renderfile = 'index';
		}


		//carico i dati dell'utente
		$users=Users::model()->findByPk(Yii::app()->user->objUser['id_user']);

		//creo un model nuovo di pagamento
		$model=new Pagamenti;
		$criteria=new CDbCriteria();

		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		$dataProvider=new CActiveDataProvider('Pagamenti', array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'id_pagamento'=>true
	    		)
	  		),
		));

		$this->render($renderfile,array(
			'users'=>$users,
			'model'=>$model,
			'dataProvider'=>$dataProvider,
		));
	}

	// #Execute Payment Sample
	// This is the second step required to complete
	// PayPal checkout. Once user completes the payment, paypal
	// redirects the browser to "redirectUrl" provided in the request.
	// This sample will show you how to execute the payment
	// that has been approved by
	// the buyer by logging into paypal site.
	// You can optionally update transaction
	// information by passing in one or more transactions.
	// API used: POST '/v1/payments/payment/<payment-id>/execute'.
	public function actionReview()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] == 'dashboard'){
			$renderfile = ['approved'=>'int_a','canceled'=>'int_c'];
		}else{
			$this->layout='//layouts/column_login';
			$renderfile = ['approved'=>'approved','canceled'=>'canceled'];
		}
		#echo "<pre>".print_r($_GET,true)."</pre>";
		#exit;
		//carico i dati dell'utente
		$users=Users::model()->findByPk(Yii::app()->user->objUser['id_user']);

		//creo un modello nuovo di pagamento ???
		//$model=new Pagamenti;
		$criteria=new CDbCriteria();
		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);

		$dataProvider=new CActiveDataProvider('Pagamenti', array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'id_pagamento'=>true
	    		)
	  		),
		));

		// ### Approval Status
		// Determine if the user approved the payment or not
		if (isset($_GET['paymentId'])){
			// Step 1 - Autoload the SDK Package. This will include all the files and classes to your autoloader
			require Yii::app()->params['libsPath'] . '/PayPal/PayPal-PHP-SDK/autoload.php';
			// require Yii::app()->basePath . '/extensions/PayPal-PHP-SDK/autoload.php';

			// Step 2 - Provide the ClientId and Secret
			$apiContext = new \PayPal\Rest\ApiContext(
			        new \PayPal\Auth\OAuthTokenCredential(
			            PAYPAL_CLIENT_ID,     // ClientID
			            PAYPAL_CLIENT_SECRET      // ClientSecret
			        )
			);
			$apiContext->setConfig(
			      array(
			        'log.LogEnabled' => true,
			        'log.FileName' => Yii::app()->basePath.'/log/PayPal.log',
			        'log.LogLevel' => 'DEBUG',

					'mode' => PAYPAL_MODE //'live' //	PAYPAL LIVE CONTEXT
					//'mode' => 'sandbox' //	PAYPAL LIVE CONTEXT
			      )
			);
		    $paymentId = $_GET['paymentId'];
		    $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
		    $execution = new \PayPal\Api\PaymentExecution();
		    $execution->setPayerId($_GET['PayerID']);
		    try {
		        // Execute the payment
		        $result = $payment->execute($execution, $apiContext);

		        try {
		            $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
		        } catch (\PayPal\Api\Exception $ex) {
				   echo "<pre>".print_r($ex,true)."</pre>";
		            exit(1);
		        }
		    } catch (\PayPal\Api\Exception $ex) {
		    	 echo "<pre>".print_r($ex,true)."</pre>";
		         exit(1);
		    }
			// ricavo l'id della transazione da payment, perchè sarà questa che viene trasmessa su ipn da Paypal
			$paypal_txn_id = $payment->transactions[0]->related_resources[0]->sale->id;

			#echo "<pre>Result: ".print_r($payment->transactions[0]->related_resources[0]->sale->id,true)."</pre>";
			#echo "<pre>Payment: ".print_r($payment,true)."</pre>";
			#exit;

			//salva la transazione in pagamenti SOLO se non l'ho già salvata
			$settings=Settings::load();
			$pagamenti = Pagamenti::model()->findByAttributes(array('id_invoice_bps'=>$paymentId));

			// echo "<pre>Payment: ".print_r($pagamenti->paypal_txn_id,true)."</pre>";
			// exit;
			if (empty($pagamenti->paypal_txn_id)){
				// quindi aggiorno il progressivo dei pagamenti
				if ($settings->progressivo_ricevute_anno == date('Y',time())){
				   $settings->progressivo_ricevute_pagamenti ++;
				   Settings::save($settings,array('progressivo_ricevute_pagamenti'));
				}else{
				   $settings->progressivo_ricevute_anno = date('Y',time());
				   $settings->progressivo_ricevute_pagamenti = 1;
				   Settings::save($settings,array('progressivo_ricevute_pagamenti','progressivo_ricevute_anno'));
				}
				$pagamenti->anno = $settings->progressivo_ricevute_anno;
				$pagamenti->progressivo = $settings->progressivo_ricevute_pagamenti;

				// verifico se esiste un pagamento precedente con una scadenza (ad esempio tra 6 gg.)
				// questi giorni si sommano alla data di scadenza, poichè sto pagando prima della sua
				// effettiva scadenza
				// Visto che mi viene restituito un valore negativo dalla funzione (inserendo la variabile true)
				// nel caso sia ancora valido l'account ma mancano ancora alcuni giorni... mi basta aggiungere
				// questo valore in valore assoluto al timestamp
				// $leftdays = 0;
				// if (WebApp::StatoPagamenti($pagamenti->id_user,true) < 0) {
				// 	$leftdays = 60*60*24 * abs(WebApp::StatoPagamenti($pagamenti->id_user,true));
				// }
				// SEMBRA CHE LA SCADENZA DEBBA COINCIDERE CON L'ULTIMO GIORNO DELL'ANNO
				// SOLARE IN CUI AVVIENE IL PAGAMENTO
				// QUINDI:
				// $yearEnd = date('Y-m-d H:i', strtotime('last day of december'));
				$yearEnd = date('Y-m-d', strtotime('last day of december'));

		   		$timestamp = time();
		   		$attributes = array(
		   			'id_user'	=> Yii::app()->user->objUser['id_user'],
		   			'id_quota'  => 1, //pagamento quota annuale
					'anno'	=> $pagamenti->anno,
					'progressivo' => $pagamenti->progressivo,
		   			'importo'	=> $payment->transactions[0]->getAmount()->total,
		   			'id_tipo_pagamento'	=> 2, //pagamento paypal
		   			'data_registrazione' => date('Y-m-d', $timestamp ),
		   			'data_inizio' => date('Y-m-d', $timestamp ),
		   			// 'data_scadenza' => date('Y/m/d',time()+ 60*60*24*365 + $leftdays), //+ 1 anno,
						'data_scadenza' => $yearEnd,
		   			'id_invoice_bps' => $payment->id, //necessario per la ricerca
					'paypal_txn_id' => $paypal_txn_id, //necessario per la ricerca per ipn paypal
		   			'status' => 'paid', // lo status va in 'paid'.. successivamente l'ipn lo trasforma in confirmed/completed!
		   		);
		   		$pagamenti = $this->update_pagamenti($attributes);

				//QUINDI INVIO UNa mail all'utente!
				NMail::SendMailIscrizione($paymentId);
			}

			$this->render($renderfile['approved'],array(
				'users'=>$users,
				'model'=>$pagamenti,
				'dataProvider'=>$dataProvider,
			));


		} else {
			//l'utente ha cancellato il pagamento
 	   		$timestamp = time();
 	   		$attributes = array(
 	   			'id_user'	=> Yii::app()->user->objUser['id_user'],
 	   			'id_quota'  => 1, //pagamento quota annuale
				'anno'	=> date('Y',time()),
				'progressivo' => 0,
 	   			'importo'	=> 0,
 	   			'id_tipo_pagamento'	=> 2, //pagamento paypal
 	   			'data_registrazione' => date('Y-m-d', $timestamp ),
 	   			'data_inizio' => date('Y-m-d', $timestamp ),
 	   			'data_scadenza' => date('Y-m-d', $timestamp ),
 	   			//'id_invoice_bps' => $payment->id, // NON HO QUESTA INFORMAZIONE AL MOMENTO
 	   			'status' => 'invalid', // lo status va in 'invalid'
 	   		);
 	   		$pagamenti = $this->cancel_pagamenti($attributes);
 			$this->render($renderfile['canceled'],array(
 				'users'=>$users,
 				//'model'=>$model,
 				'dataProvider'=>$dataProvider,
 			));
		}
	}

	/**
	  * Performs Paypal Invoice Request
	  *
	  * SANDBOX API CREDENTIALS
 	  *
 	  * Sandbox account
	  *
	  * Merchant: info-facilitator@napoliblockchain.it
	  * password: XTGVYMCFG6D4E7ZK
	  *
	  * Simple user: info-buyer@napoliblockchain.it
	  * password: maradona10
	  *
 	 */
	public function actionPaypalInvoice()
	{
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;

		if (true === isset($_POST['amount']) && trim($_POST['amount']) != 0) {
 			$_POST['amount'] = trim($_POST['amount']);
 		} else {
 			echo CJSON::encode(array("error"=>"Amount invalid!"));
 			exit;
 		}

		// Step 1 - Autoload the SDK Package. This will include all the files and classes to your autoloader
		// require Yii::app()->basePath . '/extensions/PayPal-PHP-SDK/autoload.php';
		require Yii::app()->params['libsPath'] . '/PayPal/PayPal-PHP-SDK/autoload.php';

		// Step 2 - Provide the ClientId and Secret
		$apiContext = new \PayPal\Rest\ApiContext(
		        new \PayPal\Auth\OAuthTokenCredential(
					PAYPAL_CLIENT_ID,     // ClientID
					PAYPAL_CLIENT_SECRET      // ClientSecret
		        )
		);

		$apiContext->setConfig(
		      array(
		        'log.LogEnabled' => true,
		        'log.FileName' => Yii::app()->basePath.'/log/PayPal.log',
		        'log.LogLevel' => 'DEBUG',

				'mode' => PAYPAL_MODE //'live' //	PAYPAL LIVE CONTEXT
				//'mode' => 'sandbox' //	PAYPAL LIVE CONTEXT
		      )
		);
		#echo "<pre>".print_r($apiContext,true)."</pre>";

		// Step 3 - create a new Payment
		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod('paypal');

		$amount = new \PayPal\Api\Amount();
		$amount->setTotal($_POST['amount']);
		$amount->setCurrency('EUR');

		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount($amount);

		$redirectUrls = new \PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl($_POST['redirectUrl'])
		    ->setCancelUrl($_POST['cancelUrl']);

		$payment = new \PayPal\Api\Payment();
		$payment->setIntent('sale')
		    ->setPayer($payer)
		    ->setTransactions(array($transaction))
		    ->setRedirectUrls($redirectUrls);

		#echo "<pre>".print_r($payment,true)."</pre>";
		#exit;

		// Step 4 - Make a Create Call
		try {
		    $payment->create($apiContext);
		}
		catch (\PayPal\Exception\PayPalConnectionException $ex) {
		    // This will print the detailed information on the exception.
		    //REALLY HELPFUL FOR DEBUGGING
		    #echo $ex->getData();
			echo CJSON::encode(array("error"=>$ex->getData()));
 			exit;
		}

		$timestamp = time();
		$attributes = array(
			'id_user'	=> $_POST['id_user'],
			'id_quota'  => 1, //pagamento quota annuale
			'anno'	=> date('Y',$timestamp),
			'progressivo' => 0,
			'importo'	=> $_POST['amount'],
			'id_tipo_pagamento'	=> 2, //pagamento paypal
			'data_registrazione' => date('Y-m-d', $timestamp ),
			'data_inizio' => date('Y-m-d', $timestamp ),
			'data_scadenza' => date('Y-m-d', $timestamp ),
			'id_invoice_bps' => $payment->id,
			'paypal_txn_id' => 0, //necessario per la ricerca per ipn paypal
			'status' => 'new',
		);
		#echo "<pre>".print_r($attributes,true)."</pre>";
		#exit;
		$pagamenti = $this->save_pagamenti($attributes);

		//finalmente ritorno all'app e restituisco l'url con la transazione da pagare!!!
		$send_json = array(
			'success' => 1,
			'url' => $payment->getApprovalLink(),
		);
    	echo CJSON::encode($send_json);
	}

	/**
	 * action BTCPAY SERVER Transaction
	 */
	public function actionBitcoinInvoice()
	{
		if (true === isset($_POST['amount']) && trim($_POST['amount']) != 0) {
 			$amount = trim($_POST['amount']);
 		} else {
 			echo CJSON::encode(array("error"=>"Amount invalid!"));
 			exit;
 		}

		$settings = Settings::load();
		if($settings->pos_sin == ''){
			echo CJSON::encode(array("error"=>"The requested Settings Pairing does not exist!"));
 			exit;
		}

		$pairings = Pairings::model()->findByAttributes(['id_pos'=>crypt::Decrypt($_POST['id_pos'])]);
		if($pairings->sin == '' || $pairings->token == ''){
			echo CJSON::encode(array("error"=>"The requested Pairing does not exist!"));
 			exit;
		}
		// echo "<pre>".print_r($pairings->attributes,true)."</pre>";

		/**
		*	AUTOLOADER GATEWAYS
		*/
		$btcpayserver = Yii::app()->params['libsPath'] . '/gateways/btcpayserver/Btcpay/Autoloader.php';
		// $btcpayserver = Yii::app()->basePath . '/extensions/gateways/btcpayserver/Btcpay/Autoloader.php';
		if (true === file_exists($btcpayserver) &&
		    true === is_readable($btcpayserver))
		{
		    require_once $btcpayserver;
		    \Btcpay\Autoloader::register();
		} else {
			echo CJSON::encode(array("error"=>"BtcPay Server Library could not be loaded!"));
		    //throw new Exception('BtcPay Server Library could not be loaded');
			exit;
		}



		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		$folder = Yii::app()->basePath . '/privatekeys/';
		if (true === file_exists($folder.$pairings->sin.'.pri') &&
		    true === is_readable($folder.$pairings->sin.'.pri'))
		{
			$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage('mc156MdhshuUYTF5365');
			$privateKey    = $storageEngine->load($folder.$pairings->sin.'.pri');
			$publicKey     = $storageEngine->load($folder.$pairings->sin.'.pub');
		}else{
			echo CJSON::encode(array("error"=>"Public and private keys cannot be found!"));
			exit;
		}

		$client        = new \Btcpay\Client\Client();
		$adapter       = new \Btcpay\Client\Adapter\CurlAdapter();

		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);
		$client->setUri($settings->blockchainAddress);
		$client->setAdapter($adapter);
		// ---------------------------

		/**
		 * The last object that must be injected is the token object.
		 */
		$token = new \Btcpay\Token();
		$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
		$token->setFacade('merchant'); // BTCPAYSERVER :::SERGIO CASIZZONE

		/**
		 * Token object is injected into the client
		 */
		$client->setToken($token);

		/**
		 * This is where we will start to create an Invoice(for bitcpay and transaction for Napos) object, make sure to check
		 * the InvoiceInterface for methods that you can use.
		 */
		$invoice = new \Btcpay\Invoice();
		$invoice->setFullNotifications(true);
		$invoice->setExtendedNotifications(true);

		$buyer = new \Btcpay\Buyer();
		$buyer
		    ->setEmail($_POST['email']);

		// Add the buyers info to invoice
		$invoice->setBuyer($buyer);

		/**
		 * Item is used to keep track of a few things
		 */
		 //PRODUCT INFORMATIONS
		 //item code
		 //item description

		$item = new \Btcpay\Item();
		$item
		    ->setCode($_POST['id_pos'])
		    ->setDescription($_POST['email'])
		    ->setPrice($_POST['amount']);

		$invoice->setItem($item);

		/**
		 * BitPay supports multiple different currencies. Most shopping cart applications
		 * and applications in general have defined set of currencies that can be used.
		 * Setting this to one of the supported currencies will create an invoice using
		 * the exchange rate for that currency.
		 *
		 * @see https://test.bitpay.com/bitcoin-exchange-rates for supported currencies
		 */
		$invoice->setCurrency(new \Btcpay\Currency('EUR'));

		// Configure the rest of the invoice
		//non c'è nessun order id in quanto non viene generato prima un carrello ed un ordine, ma passa subito
		//all'acquisto!
		$invoice
		    ->setOrderId($_POST['email'])
		    // You will receive IPN's at this URL, should be HTTPS for security purposes!
		    ->setNotificationUrl($_POST['ipnUrl'])
			->setRedirectUrl($_POST['redirectUrl']);

			// echo "<pre>".print_r($token,true)."</pre>";
			// echo "<pre>".print_r($client,true)."</pre>";
			// echo "<pre>".print_r($item,true)."</pre>";
			// echo "<pre>".print_r($invoice,true)."</pre>";
			// exit;

		/**
		 * Updates invoice with new information such as the invoice id and the URL where
		 * a customer can view the invoice.
		 */
		try {
		    $client->createInvoice($invoice);
		} catch (\Exception $e) {

		    // $request  = $client->getRequest();
		    // $response = $client->getResponse();

			$body = $this->getJsonBody($client->getResponse());
			echo CJSON::encode($body);
		    exit(1); // We do not want to continue if something went wrong

			// echo "<pre>".print_r($request,true)."</pre>";
			// echo "<pre>".print_r($body,true)."</pre>";
			// exit;
		    // 	#echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
		    // 	#echo (string) $response.PHP_EOL.PHP_EOL;
			// //$send_json = array ('error'=>(string) $response);
			// $send_json = array ('error'=>'Non è possibile creare la transazione!');
			// echo CJSON::encode($send_json);
		    // exit(1); // We do not want to continue if something went wrong
		}
		$body = $this->getJsonBody($client->getResponse());
		$result = $body['data'];
		//

		#echo "<pre>".print_r($response,true)."</pre>";
		#exit;

		//salva la transazione BtcPay in pagamenti
		$timestamp = time();
		$attributes = array(
			'id_user'	=> $_POST['id_user'],
			'id_quota'  => 1, //pagamento quota annuale
			'anno'	=> date('Y',$timestamp),
			'progressivo' => 0,
			'importo'	=> $_POST['amount'],
			'id_tipo_pagamento'	=> 1, //pagamento in bitcoin
			'data_registrazione' => date('Y-m-d', $timestamp ),
			'data_inizio' => date('Y-m-d', $timestamp ),
			'data_scadenza' => date('Y-m-d', $timestamp ),
			'id_invoice_bps' => $invoice->getId(),
			'status' => 'new',
		);
		$pagamenti = $this->save_pagamenti($attributes);

		// in questo caso non invio notifica all'applicazione

		//finalmente ritorno all'app e restituisco l'url con il qr-code della transazione da pagare!!!
		$send_json = array(
			'url' => $invoice->getUrl(),
		);
    	echo CJSON::encode($send_json);
	}

	//recupera lo streaming json dal contenuto txt del body
	private function getJsonBody($response)
	{
		$start = strpos($response,'{',0);
        $substr = substr($response,$start);
        return json_decode($substr, true);
	}

	private function save_pagamenti($array){
		$pagamenti = new Pagamenti;
		$pagamenti->attributes = $array;
		#echo '<pre>'.print_r($pagamenti,true).'</pre>';
		#exit;
		$pagamenti->insert();

		$return  = (object) $pagamenti->attributes;
		// echo '<pre>'.print_r($pagamenti,true).'</pre>';
		// exit;
		return $return;
	}
	private function update_pagamenti($array){
		$pagamenti = Pagamenti::model()->findByAttributes(array('id_invoice_bps'=>$array['id_invoice_bps']));
		if (null !== $pagamenti){
			$pagamenti->attributes = $array;
			$pagamenti->update();
			#echo '<pre>'.print_r($transaction,true).'</pre>';
			#exit;
			$return  = (object) $pagamenti->attributes;
			return $return;
		}
		return false;
	}
	private function cancel_pagamenti($array){
		$pagamenti = Pagamenti::model()->findByAttributes(
			array(
				'status'=>'new',
				'data_registrazione'=>$array['data_registrazione'],
				'id_user'=>$array['id_user'],
				'id_tipo_pagamento'=>2, //PAGAMENTO PAYPAL
			)
		);
		if (null !== $pagamenti){
			$pagamenti->attributes = $array;
			$pagamenti->update();
			#echo '<pre>'.print_r($transaction,true).'</pre>';
			#exit;
			$return  = (object) $pagamenti->attributes;
			return $return;
		}
		return false;
	}
}
