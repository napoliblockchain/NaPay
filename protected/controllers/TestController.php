<?php
// // SERVE PER TESTARE MESSAGGI PUSH. Da inserire nel file che effettivamente invia i push (probabilmente nell'IPN)
// require_once Yii::app()->basePath . '/extensions/web-push-php/vendor/autoload.php';
// use Minishlink\WebPush\WebPush;
// use Minishlink\WebPush\Subscription;
// ////////////////////////////////////////////

// carico l'estensione
//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
Yii::import('libs.BTCPay.BTCPayWebRequest');
Yii::import('libs.BTCPay.BTCPay');

class TestController extends Controller
{
	public function init()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}

	}

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
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
				'actions'=>array(
					'sendmailtest', // INVIA UNA MAIL DI Test
					'sendpushtest', // invia un messaggio push di test
					'pairing', // si collega a btcpayserver in remoto e fa il pairing in automatico...

					'checkInvoice', // check btcpayserver invoice by id
					'createInvoice', //crea una invoice di test
				),
				'users'=>array('@'),
			),
		);
	}



	public function actionCheckInvoice($id,$id_pos)
	{
		/**
		*	AUTOLOADER GATEWAYS
		*/
		$btcpay = Yii::app()->basePath . '/extensions/gateways/btcpayserver/Btcpay/Autoloader.php';
		if (true === file_exists($btcpay) &&
				true === is_readable($btcpay))
		{
				require_once $btcpay;
				\Btcpay\Autoloader::register();
		} else {
				throw new Exception('Btcpay Server Library could not be loaded');
		}


		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		$folder = Yii::app()->basePath . '/privatekeys/btcpay-';
		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));
		if (file_exists ($folder.$id_pos.'.pri')){
			$privateKey    = $storageEngine->load($folder.$id_pos.'.pri');
		}else{
			throw new \Exception('Error. The requested Private key does not exist.');
		}
		if (file_exists ($folder.$id_pos.'.pub')){
			$publicKey     = $storageEngine->load($folder.$id_pos.'.pub');
		}else{
			throw new \Exception('Error. The requested Public key does not exist.');
		}

		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));
		if($pairings===null){
			throw new \Exception('Error. The requested Pairings does not exist.');
		}
		$settings=Settings::load();  //$settings = SettingsNapos::model()->findByPk(1);
		if($settings===null){
			throw new \Exception("Error. The requested Settings does not exist.");
		}
		// echo crypt::Decrypt($pairings->token).PHP_EOL;
		// echo $item->token;
		#exit;

		try {
			// Now fetch the invoice from Btcpay
			// This is needed, since the IPN does not contain any authentication
			$client        = new \Btcpay\Client\Client();
			$adapter       = new \Btcpay\Client\Adapter\CurlAdapter();
			$client->setUri($settings->BTCPayServerAddress); //l'indirizzo del server BTCPay recuperato dai settings
			$client->setAdapter($adapter);

			//woocommerce plugin
			$client->setPrivateKey($privateKey);
			$client->setPublicKey($publicKey);

			$token = new \Btcpay\Token();
			$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
			$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
			$client->setToken($token);

			/**
			* This is where we will fetch the invoice object
			*/
			$invoice 	= $client->getInvoice($id);

			echo '<pre>'.print_r($invoice,true).'</pre>';

			// $payout 	= $client->getPayouts();
			// echo '<pre>'.print_r($payout,true).'</pre>';


		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}

	}

	/**
	 * action BTCPAY SERVER Transaction
	 */
	public function actionCreateInvoice($amount,$url,$id_pos)
	{

		//echo $url;
		//exit;

		$pos = new Pos;
		$pos=Pos::model()->findByPk($id_pos);
		if($pos===null){
			echo CJSON::encode(array("error"=>"The requested ID Pos does not exist!"));
 			exit;
		}
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$pos->id_pos));


		/**
		*	AUTOLOADER GATEWAYS
		*/
		$btcpayserver = Yii::app()->basePath . '/extensions/gateways/btcpayserver/Btcpay/Autoloader.php';
		if (true === file_exists($btcpayserver) &&
		    true === is_readable($btcpayserver))
		{
		    require_once $btcpayserver;
		    \Btcpay\Autoloader::register();
		} else {
		    throw new Exception('BtcPay Server Library could not be loaded');
				exit;
		}

		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		//$folder = Yii::app()->basePath . '../npay/protected/privatekeys/btcpay-';
		/** NON AGISCO PIù SU PATH WWW, MA SULLE CARTELLE
		  * Visto che paolo ha modificato il nome delle cartelle da npay a napay su .tk come faccio ad accedervi?
		*/
		if (gethostname()=='blockchain1'){
			$folder = $_SERVER["DOCUMENT_ROOT"].'/../napay/protected/privatekeys/btcpay-';
		}elseif (gethostname()=='CGF6135T'){ // SERVE PER LE PROVE IN UFFICIO
            $folder = $_SERVER["DOCUMENT_ROOT"].'npay/protected/privatekeys/btcpay-';
		}else{
			$folder = $_SERVER["DOCUMENT_ROOT"].'/../npay/protected/privatekeys/btcpay-';
		}


		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));
		$privateKey    = $storageEngine->load($folder.$id_pos.'.pri');
		$publicKey     = $storageEngine->load($folder.$id_pos.'.pub');

		$client        = new \Btcpay\Client\Client();
		$adapter       = new \Btcpay\Client\Adapter\CurlAdapter();

		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);
		$client->setUri($url);
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

		$buyer = new \Btcpay\Buyer();
		$buyer
		    ->setEmail('info@mail.tk');

		// Add the buyers info to invoice
		$invoice->setBuyer($buyer);
		$invoice->setFullNotifications(true); //serve per l'ipn
		$invoice->setExtendedNotifications(true); //serve per l'ipn

		/**
		 * Item is used to keep track of a few things
		 */
		 //PRODUCT INFORMATIONS
		 //item code
		 //item description

		$item = new \Btcpay\Item();
		$item
		    ->setCode($id_pos)
		    ->setDescription(substr($pairings->label,0,20))
		    ->setPrice($amount);

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
		//come order ID salvo l'id della transazione in locale
		$redirectUrl = 'https://'.$_SERVER['HTTP_HOST'];
		$ipnUrl = 'https://'.$_SERVER['HTTP_HOST'].Yii::app()->createUrl('ipn/invoice');
		#echo $redirectUrl;
		#exit;
		$invoice
		    ->setOrderId('#: '.$id_pos)
			//->setNotificationEmail('')
		    // You will receive IPN's at this URL, should be HTTPS for security purposes!
		    ->setNotificationUrl($ipnUrl)
			->setRedirectUrl($redirectUrl);

		/**
		 * Updates invoice with new information such as the invoice id and the URL where
		 * a customer can view the invoice.
		 */

		#echo "<pre>".print_r($invoice,true)."</pre>";
		#exit;

		try {
		    $client->createInvoice($invoice);
		} catch (\Exception $e) {
		    $request  = $client->getRequest();
		    $response = $client->getResponse();
		    	#echo (string) $request.PHP_EOL.PHP_EOL.PHP_EOL;
		    	#echo (string) $response.PHP_EOL.PHP_EOL;
			//$send_json = array ('error'=>(string) $response);
			$send_json = array (
				'error'=>'Non è stato possibile creare la transazione!',
				'message'=>(string)$response,
			);
			echo CJSON::encode($send_json);
		    exit(1); // We do not want to continue if something went wrong
		}


		//TRASFORMA OBJECT $client IN ARRAY
		#$request  = $client->getRequest();
		$response = $client->getResponse();
		#echo "<pre>".print_r($request,true)."</pre>";
		#echo "<pre>".print_r($response,true)."</pre>";
		#exit;

        $start = strpos($response,'{',0);
        $substr = substr($response,$start);
        $body = json_decode($substr, true);
		$array = $body['data'];
		//

		echo "<pre>".print_r($array,true)."</pre>";
		exit;
	}


	/**
	 * CONSERVO A MEMORIA STORICA !!!
	 * AUTOMATISMO PER BTCPAY SERVER
	 * MI COLLEGO A BTCPAY SERVER TRAMITE CURL E CERCO DI CREARE UN PAIRING AUTOMATICO
	 *
	 */

	public function actionPairing(){
		set_time_limit(0); //imposto il time limit unlimited

		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$id_merchant = 40; //test
		$id_store = 24;

		$merchants = Merchants::model()->findByPk($id_merchant); //test
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);
		$stores = Stores::model()->findByPk($id_store);

		#echo "<pre>".print_r($users->email,true)."</pre>";
		#echo "<pre>".print_r(crypt::Decrypt($BpsUsers->bps_auth),true)."</pre>";
		#exit;

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));


		// imposto i general settings
		// $rates = $BTCPay->ratesSettings($stores->bps_storeid);
		// echo "<pre>".print_r($rates,true)."</pre>";
		// exit;

		$BTCPay->setStoreId($stores->bps_storeid);



		$checkout = $BTCPay->checkout($stores->bps_storeid);

		echo "<pre>".print_r($checkout,true)."</pre>";
		exit;
















		//
		// $req = [
	   	// 	"Name" => 'Automatic Store',
	   	// 	"__RequestVerificationToken" => $this->getRequestToken(BTCPAY_NEWSTORE)
	    // ];
		// $newStore = webrequest::request(BTCPAY_NEWSTORE,$req);
		//  echo "<pre>".print_r($newStore,true)."</pre>";


	}





	/**
	 * INVIA UNA MAIL OPER TESTARE LA CONFIGURAZIONE IN config/main.php
	 */
	public function actionSendmailtest(){
		NMail::SendMail('subscriptionAdmin',crypt::Encrypt(18),'sergio.casizzone@gmail.com','PAYID-LUFXRLQ098515411R5520506');
	}

	/**
	 * test messaggio push a sergio.casizzone
	 */
	public function actionSendpushtest(){
		$criteria=new CDbCriteria();

		$id_user = Yii::app()->user->objUser['id_user']; //utente collegato
		//$id_user = 93; //id user test per l'ufficio

		$criteria->compare('id_user',$id_user,false);

		$subscriptions = CHtml::listData(PushSubscriptions::model()->findAll($criteria), 'id_subscription', function($pushsubscriptions) {
			$object['endpoint'] =  $pushsubscriptions->endpoint;
			$object['auth'] =  $pushsubscriptions->auth;
			$object['p256dh'] =  $pushsubscriptions->p256dh;
			return $object;
		});
		echo '<pre>subscriptions: '.print_r($subscriptions,true).'</pre>';
		#exit;

		foreach ($subscriptions as $id => $sub){
			$notifications[] =
				[
					'subscription' => Subscription::create([
						'endpoint' => $sub['endpoint'],
						"keys" => [
							 'p256dh' => $sub['p256dh'],
							 'auth' => $sub['auth']
						 ],
					 ]),
					 'payload' => '
							 {
							   body: "Nuove transazioni trovate. Desideri visualizzarle?",
							 icon: "src/images/icons/app-icon-96x96.png",
							 badge: "src/images/icons/app-icon-96x96.png",
							 vibrate: [100, 50, 100, 50, 100], //in milliseconds vibra, pausa, vibra, ecc.ecc.
							 data: {
								   openUrl: "https://wallet.napoliblockchain.it/index.php?r=wallet/index",
								   //openUrl: data.openUrl,
							  },
							  actions: [
								{action: "confirm", title: "SI", icon: "css/images/chk_on.png"},
								{action: "close", title: "NO", icon: "css/images/chk_off.png"},
							  ],
							 }
					 ',

				];
		}
		echo '<pre>notifications: '.print_r($notifications,true).'</pre>';
		#exit;
		// Genero le autorizzazioni VAPID
		$settings=Settings::load();
		$auth = array(
			'VAPID' => array(
				'subject' => 'Nuove Transazioni!',
				'publicKey' => $settings->VapidPublic, // don't forget that your public key also lives in app.js
				'privateKey' => $settings->VapidSecret, // in the real world, this would be in a secret file
			),
		);
		$webPush = new WebPush($auth);

		// send multiple notifications with payload
		foreach ($notifications as $notification) {
			$webPush->sendNotification(
				$notification['subscription'],
				$notification['payload'] // optional (defaults null)
			);
		}

		/**
		 * Check sent results
		 * @var MessageSentReport $report
		 */
		foreach ($webPush->flush() as $report) {
			$endpoint = $report->getRequest()->getUri()->__toString();
			echo '<pre>endpoint: '.print_r($endpoint,true).'</pre>';

			if ($report->isSuccess()) {
				$this->log( "[v] Message sent successfully for subscription {$endpoint}.");
			} else {
				$this->log( "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
			}
		}

	}
}
