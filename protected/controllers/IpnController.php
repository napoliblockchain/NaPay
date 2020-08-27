<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Notifi');
Yii::import('libs.NaPacks.Push');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');
Yii::import('libs.NaPacks.WebApp');


use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;


class IpnController extends Controller
{
    public function init()
	{
        $settings = Settings::load();
        define("PAYPAL_CLIENT_ID", $settings->PAYPAL_CLIENT_ID);
        define("PAYPAL_CLIENT_SECRET", $settings->PAYPAL_CLIENT_SECRET);
        define("PAYPAL_MODE", $settings->PAYPAL_MODE);

    }
	/** @var bool Indicates if the sandbox endpoint is used. */
    private $use_sandbox = false;
    /** @var bool Indicates if the local certificates are used. */
    private $use_local_certs = false;

    /** Production Postback URL */
    const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    /** Sandbox Postback URL */
    const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    /** Response from PayPal indicating validation was successful */
    const VALID = 'VERIFIED';
    /** Response from PayPal indicating validation failed */
    const INVALID = 'INVALID';




	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column1';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
                    'Btcpayserver', // ricevute di BtcpayServer per le invoices degli user
                    'Bitpay',       // ricevute di BITPAY per le invoices degli user
                    'Coingate',     // ricevute di CoinGate per le invoices degli user
                    'iscrizione',   // ricevute di BtcpayServer sull'account personale dell'associazione per l'iscrizione
                    'paypal',       // ricevute PayPal per l'iscrizione all'associazione
                    'shop',         // ricevute Web Shop POS
                ),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    /**
	 * Performs the IPNLOGGER validation.
	 * @param none
	 */
	public function actionShop()
	{
    $save = new Save;
    $save->WriteLog('napay','ipn','shop','Start Ipnlogger.');

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);

		$raw_post_data = file_get_contents('php://input');
		if (false === $raw_post_data) {
      $save->WriteLog('napay','ipn','shop','Error. Could not read from the php://input stream or invalid Btcpay Server IPN received.',true);
		}else{
      $save->WriteLog('napay','ipn','shop','Stream ok.');
		}

		$raw = json_decode($raw_post_data);
		if (true === empty($raw)) {
      $save->WriteLog('napay','ipn','shop','Error. Could not decode the JSON payload from Btcpay Server.',true);
		}else{
      $save->WriteLog('napay','ipn','shop','Json ok.');
		}
    // $save->WriteLog('napay','ipn','shop','<pre>'.print_r($raw,true).'</pre>');

		if (isset($raw->data))
			$ipn = $raw->data;
		else
			$ipn = $raw;

    // $save->WriteLog('napay','ipn','shop','<pre>'.print_r($ipn,true).'</pre>');
    //exit;

		if (true === empty($ipn->id)) {
      $save->WriteLog('napay','ipn','shop','Error. Invalid Btcpay Server payment notification message received - did not receive invoice ID.',true);
		}else{
      $save->WriteLog('napay','ipn','shop','Ipn ok.');
		}

    // In questo ipn legato al SELF POS, non è possibile usare id_pos ma DEVE essere usato id_shop.
    // Il campo id_shop viene inviato tramite GET nell'URL Callback Notification impostato nello store.
    // Una prima difficoltà sta che nella generazione dell'invoice, la webapp non viene coinvolta.
    // Quindi ho solo la possibiltà di intercettare l'ipn in questa fase.
    if (!isset($_GET['id_shop'])){
      $save->WriteLog('napay','ipn','shop','Error. Invalid GET Call received.',true);
    }else{
      $shop = Shops::model()->findByPk($_GET['id_shop']);
      if($shop===null){
        $save->WriteLog('napay','ipn','shop','Error. The requested Shop does not exist.',true);
    	}else{
        $save->WriteLog('napay','ipn','shop','GET id_shop ok.');
    	}
    }

		//vado a cercare direttamente il proprietario nella tabella transaction
		$transaction = Transactions::model()->findByAttributes(array('id_invoice_bps'=>$ipn->id));

    if($transaction===null){
      //$transactions = new Transactions;
      $save->WriteLog('napay','ipn','shop','Transaction not exist.');

      //CREO UNA NUOVA TRANSAZIONE SU DB PER PERMETTERNE IL RECUPERO PER LA STAMPA ricevuta
    	$timestamp = time();
    	$attributes = array(
    		'id_pos'	=> -1, // in questo caso è sempre -1 (mi serve )
    		'id_merchant' => $shop->id_merchant,
    		'status'	=> 'new', //è sicuramente new
    		'btc_price'	=> $ipn->btcPrice,
    		'price'		=> $ipn->price,
    		'currency'	=> $ipn->currency,
    		'item_desc' => '', //$ipn->posData,
    		'item_code' => $shop->id_shop,
    		'id_invoice_bps' => $ipn->id,
    		'invoice_timestamp' => $timestamp,
    		'expiration_timestamp' => $timestamp,
    		'current_tempo' => $timestamp,
    		'btc_paid' => $ipn->btcPaid,
    		'rate' => $ipn->rate,
    		'bitcoin_address' => '',
    		'token' => '',
    		'btc_due' => $ipn->btcDue,
    		'satoshis_perbyte' => 0,
    		'total_fee' => 0,
    	);
    	// $save = new Save;
    	$transaction = $save->Transaction($attributes,'new'); //viene restituito un oggetto non un array
      $save->WriteLog('napay','ipn','shop','Transaction created.');
		}else {
      $save->WriteLog('napay','ipn','shop','Transaction exist.');
    }
    $StatoInizialeDellaTransazione = $transaction->status;

    // CERCO UN POS QUALUNQUE PER CARICARE LA PRIVATE E PUBLIC KEY TRAMITE id_store
    $pos = Pos::model()->findByAttributes(array('id_store'=>$shop->id_store,'deleted'=>0));
    if($pos===null){
      $save->WriteLog('napay','ipn','shop','Error. The requested Pos does not exist.',true);
		}else{
      $save->WriteLog('napay','ipn','shop','Pos exist.');
		}

		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$pos->id_pos));
		if($pairings===null){
      $save->WriteLog('napay','ipn','shop','Error. The requested Pairings does not exist.',true);
		}else{
      $save->WriteLog('napay','ipn','shop','Pairings exist.');
		}

		/**
		*	AUTOLOADER GATEWAYS
		*/
    $btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';
		if (true === file_exists($btcpay) &&
		  true === is_readable($btcpay))
		{
		  require_once $btcpay;
		  \Btcpay\Autoloader::register();
		} else {
      $save->WriteLog('napay','ipn','shop','Error. Btcpay Server Library could not be loaded.',true);
		}

		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
    $settings = Settings::load();

		$folder = Yii::app()->basePath . '/privatekeys/';
		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));
		if (file_exists ($folder.$pairings->sin.'.pri')){
			$privateKey    = $storageEngine->load($folder.$pairings->sin.'.pri');
      $save->WriteLog('napay','ipn','shop','Private key loaded.');
		}else{
      $save->WriteLog('napay','ipn','shop','Error. The requested Private key does not exist.',true);

		}
		if (file_exists ($folder.$pairings->sin.'.pub')){
			$publicKey     = $storageEngine->load($folder.$pairings->sin.'.pub');
      $save->WriteLog('napay','ipn','shop','Public key loaded.');
		}else{
      $save->WriteLog('napay','ipn','shop','Error. The requested Public key does not exist.',true);
		}

    // carico l'estensione
    //require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

    // Effettuo il login senza dati
		$BTCPay = new BTCPay(null,null);
		// imposto l'url
		$merchants = Merchants::model()->findByPk($transaction->id_merchant);
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Carico l'URL del Server BTC direttamente dalla CLASSE
		$BPSUrl = $BTCPay->getBTCPayUrl();

        $save->WriteLog('napay','ipn','shop','Server URL is: '.$BPSUrl);

	    // Now fetch the invoice from Btcpay
		// This is needed, since the IPN does not contain any authentication
		$client        = new \Btcpay\Client\Client();
	    $adapter       = new \Btcpay\Client\Adapter\CurlAdapter();
		$client->setUri($BPSUrl); //l'indirizzo del server BTCPay recuperato dai settings
	   	$client->setAdapter($adapter);

		//woocommerce plugin
		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);

		$token = new \Btcpay\Token();
		//$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
		$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
   		$client->setToken($token);
		//$client->setToken(crypt::Decrypt($pairings->token));
        $save->WriteLog('napay','ipn','shop','Set symply merchant token.');

	 	/**
		* This is where we will fetch the invoice object
		*/
        $save->WriteLog('napay','ipn','shop','Trying get invoice id: '.$ipn->id);
		$invoice = $client->getInvoice($ipn->id);
        $save->WriteLog('napay','ipn','shop','Got Btcpay invoice.');

        //A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO
		$transaction->status = $invoice->getStatus();
		$transaction->btc_paid = $invoice->getBtcPaid();
		if(!$transaction->update()){
            $save->WriteLog('napay','ipn','shop','Error. Cannot update transaction.',true);
		}else{
            $save->WriteLog('napay','ipn','shop','Transaction updated.');
		}
        // aggiorno la transactions_info
		$cryptoInfo = Save::CryptoInfo($transaction->id_transaction, $invoice->getCryptoInfo());
        $save->WriteLog('napay','ipn','shop','Transaction info updated.');

        // aggiorno il posData
		$posData = Save::PosData($transaction->id_transaction,$invoice->getPosData());
        $save->WriteLog('napay','ipn','shop','Transaction posData updated.');

		//QUINDI INVIO UN MESSAGGIO DI NOTIFICA SOLO SE LO STATUS DELLA INVOICE ATTUALE è != DAL PRECEDENTE
		//NON HA SENSO UNA NOTIFICA PER STATUS UGUALI visto che btcpayserver può inviare anche + notifiche
        //per lo stesso status... !!!
		if ($StatoInizialeDellaTransazione <> $invoice->getStatus()){
			$notification = array(
				'type_notification' => 'invoice',
                'id_user' => Merchants::model()->findByPk($transaction->id_merchant)->id_user,
				'id_tocheck' => $transaction->id_transaction,
				'status' => $invoice->getStatus(),
                'description' => Notifi::description($invoice->getStatus(),'invoice'),
                // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
                 'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transaction->id_transaction),
				'timestamp' => time(),
				'price' => $invoice->getPrice(),
				'deleted' => 0,
			);
            //a questo punto posso anche inviare un messaggio push!!!
            Push::Send($save->Notification($notification,true),'dashboard');
            $save->WriteLog('napay','ipn','shop','Push message sent.');
		}

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
        $save->WriteLog('napay','ipn','shop',"End: IPN received for BtcPay Server transaction ".$invoice->getId().". Status=" .$invoice->getStatus().", Price=". $invoice->getPrice(). ", Paid=".$invoice->getBtcPaid() );

		//Respond with HTTP 200, so BitPay knows the IPN has been received correctly
		//If BitPay receives <> HTTP 200, then BitPay will try to send the IPN again with increasing intervals for two more hours.
		header("HTTP/1.1 200 OK");
	}



	/**
	 * Performs the IPNLOGGER validation.
	 * @param none
	 */
	public function actionBtcpayserver()
	{
    $save = new Save;
    $save->WriteLog('napay','ipn','Btcpayserver','Start Ipnlogger.');

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);

		$raw_post_data = file_get_contents('php://input');
		if (false === $raw_post_data) {
      $save->WriteLog('napay','ipn','Btcpayserver','Error. Could not read from the php://input stream or invalid Btcpay Server IPN received.',true);
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Stream ok.');
		}

		$raw = json_decode($raw_post_data);
		if (true === empty($raw)) {
      $save->WriteLog('napay','ipn','Btcpayserver','Error. Could not decode the JSON payload from Btcpay Server.',true);
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Json ok.');
		}

		if (isset($raw->data))
			$ipn = $raw->data;
		else
			$ipn = $raw;

		if (true === empty($ipn->id)) {
      $save->WriteLog('napay','ipn','Btcpayserver','Error. Invalid Btcpay Server payment notification message received - did not receive invoice ID.',true);
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Ipn ok.');
		}

		//vado a cercare direttamente il proprietario nella tabella transaction
		$transactions = Transactions::model()->findByAttributes(array('id_invoice_bps'=>$ipn->id));
		if($transactions===null){
      $save->WriteLog('napay','ipn','Btcpayserver',"Error. The requested Transaction invoice (".$ipn->id.") does not exist.");
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver',"The requested Transaction invoice (".$ipn->id.") exist.");
			$StatoInizialeDellaTransazione = $transactions->status;
		}

		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$transactions->id_pos));
		if($pairings===null){
      $save->WriteLog('napay','ipn','Btcpayserver','Error. The requested Pairings does not exist.',true);
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Pairings exist.');
		}

    $settings=Settings::load();
		if($settings===null){
      $save->WriteLog('napay','ipn','Btcpayserver','Error. The requested Settings does not exist.',true);
		}

		/**
		*	AUTOLOADER GATEWAYS
		*/
    $btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';
		if (true === file_exists($btcpay) &&
	    true === is_readable($btcpay))
		{
	    require_once $btcpay;
	    \Btcpay\Autoloader::register();
		} else {
      $save->WriteLog('napay','ipn','Btcpayserver','Error. Btcpay Server Library could not be loaded.',true);
		}

		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		$folder = Yii::app()->basePath . '/privatekeys/';
		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));
		if (file_exists ($folder.$pairings->sin.'.pri')){
			$privateKey    = $storageEngine->load($folder.$pairings->sin.'.pri');
      $save->WriteLog('napay','ipn','Btcpayserver','Private key loaded.');
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Error. The requested Private key does not exist.',true);
		}
		if (file_exists ($folder.$pairings->sin.'.pub')){
			$publicKey     = $storageEngine->load($folder.$pairings->sin.'.pub');
      $save->WriteLog('napay','ipn','Btcpayserver','Public key loaded.');
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver','Error. The requested Public key does not exist.',true);
		}
    // carico l'estensione
    //require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
    Yii::import('libs.BTCPay.BTCPayWebRequest');
    Yii::import('libs.BTCPay.BTCPay');

    // Effettuo il login senza dati
		$BTCPay = new BTCPay(null,null);
		// imposto l'url
		$merchants = Merchants::model()->findByPk($transactions->id_merchant);
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Carico l'URL del Server BTC direttamente dalla CLASSE
		$BPSUrl = $BTCPay->getBTCPayUrl();

    $save->WriteLog('napay','ipn','Btcpayserver',"Server URL is: ".$BPSUrl);

	  // Now fetch the invoice from Btcpay
		// This is needed, since the IPN does not contain any authentication
		$client        = new \Btcpay\Client\Client();
	  $adapter       = new \Btcpay\Client\Adapter\CurlAdapter();
		$client->setUri($BPSUrl); //l'indirizzo del server BTCPay recuperato dai settings
	  $client->setAdapter($adapter);

		//woocommerce plugin
		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);
    $save->WriteLog('napay','ipn','Btcpayserver','Set private and public key.');

		$token = new \Btcpay\Token();
		$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
		$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
   	$client->setToken($token);
		//$client->setToken(crypt::Decrypt($pairings->token));
    $save->WriteLog('napay','ipn','Btcpayserver','Set token.');

	 	/**
		* This is where we will fetch the invoice object
		*/
    $save->WriteLog('napay','ipn','Btcpayserver',"Trying get invoice id: ".$ipn->id);
		$invoice = $client->getInvoice($ipn->id);
    $save->WriteLog('napay','ipn','Btcpayserver',"Got Btcpay invoice.");

    //A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO
		$transactions->status = $invoice->getStatus();
		$transactions->btc_paid = $invoice->getBtcPaid();
		if(!$transactions->update()){
      $save->WriteLog('napay','ipn','Btcpayserver',"Cannot update Transaction.",true);
		}else{
      $save->WriteLog('napay','ipn','Btcpayserver',"Transaction updated.");
		}
    // aggiorno la transactions_info
		$cryptoInfo = Save::CryptoInfo($transactions->id_transaction, $invoice->getCryptoInfo());
    $save->WriteLog('napay','ipn','Btcpayserver',"Transaction info updated.");

		//QUINDI INVIO UN MESSAGGIO DI NOTIFICA SOLO SE LO STATUS DELLA INVOICE ATTUALE è != DAL PRECEDENTE
		//NON HA SENSO UNA NOTIFICA PER STATUS UGUALI visto che btcpayserver può inviare anche + notifiche
    //per lo stesso status... !!!
		if ($StatoInizialeDellaTransazione <> $invoice->getStatus()){
			$notification = array(
				'type_notification' => 'invoice',
        'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
				'id_tocheck' => $transactions->id_transaction,
				'status' => $invoice->getStatus(),
        'description' => Notifi::description($invoice->getStatus(),'invoice'),
				//'description' => ' da '. $transactions->item_desc,
				// 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
        // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
        'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
				'timestamp' => time(),
				'price' => $invoice->getPrice(),
				'deleted' => 0,
			);
      //a questo punto posso anche inviare un messaggio push!!!
      Push::Send($save->Notification($notification,true),'dashboard');
		}

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
    $save->WriteLog('napay','ipn','Btcpayserver',"End: IPN received for BtcPay Server transaction ".$invoice->getId()." . Status = " .$invoice->getStatus()." Price = ". $invoice->getPrice(). " Paid = ".$invoice->getBtcPaid());

		//Respond with HTTP 200, so BitPay knows the IPN has been received correctly
		//If BitPay receives <> HTTP 200, then BitPay will try to send the IPN again with increasing intervals for two more hours.
		header("HTTP/1.1 200 OK");
	}




	/**
	 * Performs the IPNLOGGER validation.
	 * @param none
	 */
	public function actionBitpay()
	{
    	$myfile = fopen(Yii::app()->basePath."/log/bitpay.log", "a");
        $date = date('Y/m/d h:i:s a', time());
		fwrite($myfile, $date . " : 0. Start Ipnlogger v. 0.1\n");

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);
		$raw_post_data = file_get_contents('php://input');
		if (false === $raw_post_data) {
			fwrite($myfile, $date . " : Error. Could not read from the php://input stream or invalid Bitpay IPN received.\n");
			fclose($myfile);
			throw new \Exception('Could not read from the php://input stream or invalid Bitpay IPN received.');
		}else{
			fwrite($myfile, $date . " : 1. Stream ok.\n");
		}

        $ipn = json_decode($raw_post_data,true);
		if (true === empty($ipn)) {
			fwrite($myfile, $date . " : Error. Could not decode the JSON payload from BitPay.\n");
			fclose($myfile);
			throw new \Exception('Could not decode the JSON payload from BitPay.');
		}else{
			fwrite($myfile, $date . " : 2. Json ok.\n");
		}

		if (false === array_key_exists('id', $ipn)) {
			fwrite($myfile, $date . " : Error. Invalid Bitpay payment notification message received - did not receive invoice ID.\n");
			fclose($myfile);
			throw new \Exception('Invalid Bitpay payment notification message received - did not receive invoice ID.');
		}else{
			fwrite($myfile, $date . " : 3. Ipn ok.\n");
		}

		//siccome bitpay non mi invia chi ha creato l'invoice, vado a cercare direttamente il proprietario nella tabella transaction
		$transactions = new Transactions;
		$transactions = Transactions::model()->findByAttributes(array('id_invoice_bps'=>$ipn['id']));
		if($transactions===null){
			fwrite($myfile, $date . " : Error. The requested Transaction invoice (".$ipn['id'].") does not exist.\n");
			fclose($myfile);
			throw new \Exception('Error. The requested Transaction invoice does not exist.');
		}else{
			fwrite($myfile, $date . " : 4. Transaction invoice exist.\n");
		}
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$transactions->id_pos));
		if($pairings===null){
			fwrite($myfile, $date . " : Error. The requested Pairings does not exist.\n");
			fclose($myfile);
			throw new \Exception('Error. The requested Pairings does not exist.');
		}else{
			fwrite($myfile, $date . " : 5. Pairings exist.\n");
		}

		/**
		*	AUTOLOADER GATEWAYS
		*/
		$bitpay = Yii::app()->basePath . '/extensions/gateways/bitpay/Bitpay/Autoloader.php';
		if (true === file_exists($bitpay) &&
		    true === is_readable($bitpay))
		{
		    require_once $bitpay;
		    \Bitpay\Autoloader::register();
		} else {
		    throw new Exception('Bitpay Server Library could not be loaded');
		}
		//Yii::app()->user->setState("agenzia_entrate", Yii::app()->params['agenziaentrate']);

		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		$folder = Yii::app()->basePath . '/privatekeys/bitpay-';
		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mnewhdo3yy4yFDASc156MdhshuUYTF5365');
		if (file_exists ($folder.$transactions->id_pos.'.pri')){
			$privateKey    = $storageEngine->load($folder.$transactions->id_pos.'.pri');
			fwrite($myfile, $date . " : 6. Private key loaded.\n");
		}else{
			fwrite($myfile, $date . " : 6. Private key not loaded.\n");
			fclose($myfile);
			throw new \Exception('Error. The requested Private key does not exist.');
		}
		if (file_exists ($folder.$transactions->id_pos.'.pub')){
			$publicKey     = $storageEngine->load($folder.$transactions->id_pos.'.pub');
			fwrite($myfile, $date . " : 7. Public key loaded.\n");
		}else{
			fwrite($myfile, $date . " : 7. Public key not loaded.\n");
			fclose($myfile);
			throw new \Exception('Error. The requested Public key does not exist.');
		}


		// $privateKey    = $storageEngine->load($folder.$transactions->id_pos.'.pri');
		// $publicKey     = $storageEngine->load($folder.$transactions->id_pos.'.pub');

        // Now fetch the invoice from BitPay
		// This is needed, since the IPN does not contain any authentication
		$client        = new \Bitpay\Client\Client();

		//$network        = new \Bitpay\Network\Testnet();
		$network        = new \Bitpay\Network\Livenet();

		$adapter       = new \Bitpay\Client\Adapter\CurlAdapter();
		$client->setNetwork($network);
		$client->setAdapter($adapter);

		//woocommerce plugin
		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);
		fwrite($myfile, $date . " : 8. Set private and public key.\n");

		//
		$token = new \Bitpay\Token();
   		$client->setToken(crypt::Decrypt($pairings->token));
		fwrite($myfile, $date . " : 9. Set token.\n");

	 	/**
		* This is where we will fetch the invoice object
		*/
		$invoice['Obj'] 	= $client->getInvoice($ipn['id']);
		fwrite($myfile, $date . " : 10. Get Btcpay invoice.\n");
		$invoice['Id'] 		= $invoice['Obj']->getId();			//per recuperare l'invoice dall'archivio
		$invoice['Status'] 	= $invoice['Obj']->getStatus(); 	//per impostare il nuovo stato
		//$invoice['btcPaid'] = $invoice['Obj']->getBtcPaid();	//per impostare quanto è stato pagato
		$invoice['Price']= $invoice['Obj']->getPrice();	//per impostare quanto era il dovuto da pagare
		fwrite($myfile, $date . " : 11. Invoice object created.\n");

		//A QUESTO PUNTO SALVO LA TRANSAZIONE IN ARCHIVIO
		$transactions->status = $invoice['Status'];
		if(!$transactions->update()){
			fwrite($myfile, $date . " : Error. Cannot save transaction!\n");
			fclose($myfile);
			throw new \Exception('Cannot save transaction!');
		}else{
			fwrite($myfile, $date . " : 12. Transaction updated.\n");
		}
		//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
		//salva la notifica CoinGate
		$notification = array(
			'type_notification' => 'invoice',
			// 'id_merchant' => $transactions->id_merchant,
            'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
			'id_tocheck' => $transactions->id_transaction,
			'status' => $invoice['Status'],
			'description' => ' da '. $transactions->item_desc,
			// 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
            // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
             'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
			'timestamp' => time(),
			'price' => $invoice['Price'],
			'deleted' => 0,
		);
		// NaPay::save_notification($notification);
        $save = new Save;
        Push::Send($save->Notification($notification,true),'dashboard');

        //a questo punto posso anche inviare un messaggio push!!!
        $this->sendPushMessage($notification);

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
		fwrite($myfile, $date . " : End: IPN received for BitPay transaction ".$invoice['Id']." . Status = " .$invoice['Status']." Price = ". $invoice['Price'] ."\n");
		fclose($myfile);

		//Respond with HTTP 200, so BitPay knows the IPN has been received correctly
		//If BitPay receives <> HTTP 200, then BitPay will try to send the IPN again with increasing intervals for two more hours.
		header("HTTP/1.1 200 OK");
	}

	/**
	 * Performs the IPNLOGGER validation.
	 * @param _POST
	 */
	public function actionCoingate()
	{
    	$myfile = fopen(Yii::app()->basePath."/log/coingate.log", "a");
        $date = date('Y/m/d h:i:s a', time());
		#fwrite($myfile, $date . " : 0. Start Ipnlogger v. 0.1\n");

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);

		$raw_post_data = file_get_contents('php://input');
		if (false === $raw_post_data) {
			fwrite($myfile, $date . " : Error. Could not read from the php://input stream or invalid Btcpay Server IPN received.\n");
			fclose($myfile);
			throw new \Exception('Could not read from the php://input stream or invalid Btcpay Server IPN received.');
		}else{
			#fwrite($myfile, $date . " : 1. Stream ok.\n");
		}

        parse_str($raw_post_data, $order);
		#fwrite($myfile, $date .  '<pre>'.print_r($order,true).'</pre>');
		//$order è un array
		try {
			if (!$order || !$order['id']) {
				throw new Exception('Order does not exists');
			}else{
				#fwrite($myfile, $date . " : 2. Order ok.\n");
			}

			//vado a cercare direttamente il proprietario nella tabella transaction
			$transactions = new Transactions;
			$transactions = Transactions::model()->findByAttributes(array('id_invoice_bps'=>$order['id']));
			if($transactions===null){
				fwrite($myfile, $date . " : Error. The requested Transaction invoice (".$order['id'].") does not exist.\n");
				fclose($myfile);
				throw new \Exception('Error. The requested Transaction invoice does not exist.');
			}else{
				#fwrite($myfile, $date . " : 3. Transaction invoice exist.\n");
			}
			$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$transactions->id_pos));
			if($pairings===null){
				fwrite($myfile, $date . " : Error. The requested Pairings does not exist.\n");
				fclose($myfile);
				throw new \Exception('Error. The requested Pairings does not exist.');
			}else{
				#fwrite($myfile, $date . " : 4. Pairings exist.\n");
			}
			//decripto il token
			$token = crypt::Decrypt($pairings->token);
            #fwrite($myfile, $date .  '<pre>'.print_r($token,true).'</pre>');
			if ($order['token'] != $token) {
				throw new Exception('Callback token does not match');
			}else{
				#fwrite($myfile, $date . " : 5. Tokens matched.\n");
			}

			//init CoinGate
			WebApp::CoingateInitialize($token);

			//$invoice è un object
			$invoice = \CoinGate\Merchant\Order::find($order['id']);
			if (!$invoice) {
				throw new Exception('CoinGate Order #' . $order['id'] . ' does not exists');
			}else{
				#fwrite($myfile, $date . " : 6. Coingate Order exist.\n");
			}
			#fwrite($myfile, $date . " : <pre>" . print_r($invoice,true)."</pre>\n");

            //AGGIORNO LA Transazione
			//l'unica cosa che faccio è modificare l'oggetto status da 'pending' in 'new' per omologare coingate a bitpay e btcpayserver
			if ($invoice->status == 'pending')
				$invoice->status = 'new';

			$transactions->status = $invoice->status;
		 	$transactions->btc_paid = $invoice->pay_amount;
         	$transactions->btc_price = $invoice->receive_amount;
         	$transactions->btc_due = $invoice->pay_amount;
          	$transactions->total_fee = $invoice->pay_amount - $invoice->receive_amount;
          	$transactions->bitcoin_address = $invoice->payment_address;
			if(!$transactions->update()){
			 	fwrite($myfile, $date . " : Error. Cannot save Coingate transaction!\n");
			 	fclose($myfile);
			 	throw new \Exception('Cannot save Coingate transaction!');
			}else{
			 	#fwrite($myfile, $date . " : 12. Transaction updated.\n");
			}
	        //QUINDI INVIO UN MESSAGGIO DI NOTIFICA
			//salva la notifica CoinGate
	 		$notification = array(
	 			'type_notification' => 'invoice',
	 			// 'id_merchant' => $transactions->id_merchant,
                'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
	 			'id_tocheck' => $transactions->id_transaction,
	 			'status' => $invoice->status,
	 			'description' => ' da '. $transactions->item_desc,
	 			// 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
                // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
                 'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
	 			'timestamp' => time(),
	 			'price' => $invoice->price_amount,
	 			'deleted' => 0,
	 		);
	 		// NaPay::save_notification($notification);
            $save = new Save;
            Push::Send($save->Notification($notification,true),'dashboard');

            //a questo punto posso anche inviare un messaggio push!!!
            $this->sendPushMessage($notification);

		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}



		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
		fwrite($myfile, $date . " : End: IPN received for Coingate Server transaction ".$invoice->id." . Status = " .$invoice->status." Price = ". $invoice->pay_amount. " Paid = ".$invoice->receive_amount."\n");
		fclose($myfile);


		//Respond with HTTP 200, so BitPay knows the IPN has been received correctly
		//If BitPay receives <> HTTP 200, then BitPay will try to send the IPN again with increasing intervals for two more hours.
		header("HTTP/1.1 200 OK");
	}

	/**
	 * Performs the IPNLOGGER validation.
	 * @param none
	 */
	public function actionIscrizione()
	{
    $save = new Save;
    $save->WriteLog('napay','ipn','iscrizione','Start Ipnlogger.');

    $settings=Settings::load();
    if($settings===null){
      $save->WriteLog('napay','ipn','iscrizione','Error. Error. The requested Settings does not exist.',true);
    }

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);

		$raw_post_data = file_get_contents('php://input');
		if (false === $raw_post_data) {
      $save->WriteLog('napay','ipn','iscrizione','Error. Could not read from the php://input stream or invalid Btcpay Server IPN received.',true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Stream ok.');
		}

    $raw = json_decode($raw_post_data);
		if (true === empty($raw)) {
      $save->WriteLog('napay','ipn','iscrizione','Error. Could not decode the JSON payload from Btcpay Server.',true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Json ok.');
		}

		if (isset($raw->data))
			$ipn = $raw->data;
		else
			$ipn = $raw;

		if (true === empty($ipn->id)) {
      $save->WriteLog('napay','ipn','iscrizione','Error. Invalid Btcpay Server payment notification message received - did not receive invoice ID.',true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Ipn ok.');
		}

		// carico la riga dei pagamenti
		$pagamenti = Pagamenti::model()->findByAttributes(array('id_invoice_bps'=>$ipn->id));
		if($pagamenti===null){
      $save->WriteLog('napay','ipn','iscrizione',"Error. The requested Payment invoice (".$ipn->id.") does not exist.",true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione',"The requested Payment invoice (".$ipn->id.") exist.");
      $StatoInizialeDellaTransazione = $pagamenti->status;
		}


    // id_pos è zero per l'associazione
    $pairings = Pairings::model()->findByAttributes(array('id_pos'=>0));
		if($pairings===null){
      $save->WriteLog('napay','ipn','iscrizione',"Error. The requested Pairings does not exist.",true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Pairings exist.');
		}

		/**
		*	AUTOLOADER GATEWAYS
		*/
    $btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';
		if (true === file_exists($btcpay) &&
		  true === is_readable($btcpay))
		{
		  require_once $btcpay;
		  \Btcpay\Autoloader::register();
		} else {
      $save->WriteLog('napay','ipn','iscrizione',"Error. Btcpay Server Library could not be loaded.",true);
		}
		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		$folder = Yii::app()->basePath . '/privatekeys/webapp';
		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));
		if (file_exists ($folder.'.pri')){
			$privateKey    = $storageEngine->load($folder.'.pri');
      $save->WriteLog('napay','ipn','iscrizione','Private key loaded.');
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Error. The requested Private key does not exist.',true);
		}
		if (file_exists ($folder.'.pub')){
			$publicKey     = $storageEngine->load($folder.'.pub');
      $save->WriteLog('napay','ipn','iscrizione','Public key loaded.');
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Error. The requested Public key does not exist.',true);
		}

    // Now fetch the invoice from Btcpay
		// This is needed, since the IPN does not contain any authentication
		$client        = new \Btcpay\Client\Client();
    $adapter       = new \Btcpay\Client\Adapter\CurlAdapter();

    //l'indirizzo del server BTCPay recuperato dai settings dell'associazione
    $client->setUri($settings->BTCPayServerAddress);
   	$client->setAdapter($adapter);

		//woocommerce plugin
		$client->setPrivateKey($privateKey);
		$client->setPublicKey($publicKey);
    $save->WriteLog('napay','ipn','iscrizione','Set private and public key.');

		$token = new \Btcpay\Token();
		$token->setToken(crypt::Decrypt($pairings->token));
		$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
   	$client->setToken($token);
    $save->WriteLog('napay','ipn','iscrizione','Set token.');

	 	/**
		* This is where we will fetch the invoice object
		*/
    $save->WriteLog('napay','ipn','iscrizione',"Try getting invoice id: ".$ipn->id);

  	$myInvoice = $client->getInvoice($ipn->id);

    $save->WriteLog('napay','ipn','iscrizione','Got Btcpay invoice.');

		$invoice['Id'] 		= $myInvoice->getId();			//per recuperare l'invoice dall'archivio
		$invoice['Status'] 	= $myInvoice->getStatus(); 	//per impostare il nuovo stato
		$invoice['btcPaid'] = $myInvoice->getBtcPaid();	//per impostare quanto è stato pagato
		$invoice['btcPrice']= $myInvoice->getBtcPrice();	//per impostare quanto era il dovuto da pagare
		// !!! IMPORTANTE !!!
		$invoice['Price']= $myInvoice->getPrice();	//per impostare quanto era il dovuto da pagare in moneta fiat
    $save->WriteLog('napay','ipn','iscrizione','Invoice object created.');

		//A QUESTO PUNTO AGGIORNO il pagamento  IN ARCHIVIO inserendo la data del pagamento + 365 giorni
    if ($myInvoice->getStatus()=='complete' && $StatoInizialeDellaTransazione <> 'paid'){
      $pagamenti->importo = $invoice['Price'];
      $pagamenti->status = 'paid';

    	$yearEnd = date('Y-m-d', strtotime('last day of december'));

			// $pagamenti->data_scadenza	= date('Y/m/d',time()+ 60*60*24*365 + $leftdays); //+ 1 anno
      $pagamenti->data_scadenza	= $yearEnd;

			// quindi aggiorno il progressivo dei pagamenti
			if ($settings->progressivo_ricevute_anno == $pagamenti->anno){
				$settings->progressivo_ricevute_pagamenti ++;
				Settings::save($settings,array('progressivo_ricevute_pagamenti'));
				$pagamenti->progressivo = $settings->progressivo_ricevute_pagamenti;
			}else{
				$settings->progressivo_ricevute_anno = date('Y',time());
				$settings->progressivo_ricevute_pagamenti = 1;
				Settings::save($settings,array('progressivo_ricevute_pagamenti','progressivo_ricevute_anno'));
				$pagamenti->anno = date('Y',time());
				$pagamenti->progressivo = 1;
			}

      //QUINDI INVIO UN MESSAGGIO DI NOTIFICA,
      $this->notifyMail($pagamenti->id_user,$ipn->id);
		}

		if(!$pagamenti->update()){
      $save->WriteLog('napay','ipn','iscrizione','Error. Cannot update transaction.',true);
		}else{
      $save->WriteLog('napay','ipn','iscrizione','Transaction updated.');
		}

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
    $save->WriteLog('napay','ipn','iscrizione',"End: IPN received for BtcPay Server transaction ".$invoice['Id']." . Status = " .$invoice['Status']." Price = ". $invoice['btcPrice']. " Paid = ".$invoice['btcPaid']);

		//Respond with HTTP 200, so BitPay knows the IPN has been received correctly
		//If BitPay receives <> HTTP 200, then BitPay will try to send the IPN again with increasing intervals for two more hours.
		header("HTTP/1.1 200 OK");
	}

  //invia mail agli admin dell'avvenuta ricezione tramite ipn dello status dell'invoice dell'iscrizione
  private function notifyMail($id_user,$ipn_id){
    //cerco tutti gli admin per inoltrare la mail di nuova iscrizione
    $criteria=new CDbCriteria();
    $criteria->compare('id_users_type',3,false);
    $admins = Users::model()->findAll($criteria);

    $listaAdmins = CHtml::listData($admins,'id_user' , 'email');
    foreach ($listaAdmins as $id => $adminEmail)
      NMail::SendMail('subscriptionAdmin',crypt::Encrypt($id_user),$adminEmail,$ipn_id);
  }

	/**
     * Sets the IPN verification to sandbox mode (for use when testing,
     * should not be enabled in production).
     * @return void
     */
    public function useSandbox()
    {
      $this->use_sandbox = true;
    }

    /**
     * Sets curl to use php curl's built in certs (may be required in some
     * environments).
     * @return void
     */
    public function usePHPCerts()
    {
      $this->use_local_certs = false;
    }
	/**
	* Determine endpoint to post the verification data to.
	*
	* @return string
	*/
   public function getPaypalUri()
   {
	   if ($this->use_sandbox) {
		   return self::SANDBOX_VERIFY_URI;
	   } else {
		   return self::VERIFY_URI;
	   }
   }

	/**
	 * Performs the PAYPAL IPNLOGGER validation.
	 * @param none
	 */
	public function actionPaypal()
	{
        $save = new Save;
        $save->WriteLog('napay','ipn','Paypal','Start Ipnlogger.');

		// Questa opzione abilita i wrapper URL per fopen (file_get_contents), in modo da potere accedere ad oggetti URL come file
		ini_set("allow_url_fopen", true);

		$raw_post_data = file_get_contents('php://input');

        if (false === $raw_post_data) {
            $save->WriteLog('napay','ipn','Paypal','Error. Could not read from the php://input stream or invalid Paypal IPN received.',true);
		}else{
            $save->WriteLog('napay','ipn','Paypal','Stream ok.');
		}

        if (is_array($raw_post_data))
            $raw_post_array = $raw_post_data;
        else
            $raw_post_array = explode('&', $raw_post_data);


        $save->WriteLog('napay','ipn','Paypal',"raw_post_array<pre>".print_r($raw_post_array,true)."</pre>");

		$myPost = array();
		foreach ($raw_post_array as $keyval) {
		    $keyval = explode('=', $keyval);
		    if (count($keyval) == 2) {
			   // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
			    if ($keyval[0] === 'payment_date') {
				    if (substr_count($keyval[1], '+') === 1) {
					   $keyval[1] = str_replace('+', '%2B', $keyval[1]);
				    }
			    }
			    $myPost[$keyval[0]] = urldecode($keyval[1]);
		    }
		}

        $save->WriteLog('napay','ipn','Paypal',"myPost<pre>".print_r($myPost,true)."</pre>");

		// Build the body of the verification post request, adding the _notify-validate command.
        $req = 'cmd=_notify-validate';
        $get_magic_quotes_exists = false;
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

		// Post the data back to PayPal, using curl. Throw exceptions if errors occur.
        $ch = curl_init($this->getPaypalUri());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		// This is often required if the server is missing a global cert bundle, or is using an outdated one.
        if ($this->use_local_certs) {
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem");
        }
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close',
        ));
        $res = curl_exec($ch);
        $save->WriteLog('napay','ipn','Paypal',"curl response <pre>".print_r($res,true)."</pre>");

        if ( ! ($res)) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            $save->WriteLog('napay','ipn','Paypal',"cURL error: [$errno] $errstr",true);
        }

        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        if ($http_code != 200) {
            $save->WriteLog('napay','ipn','Paypal',"PayPal responded with http code $http_code",true);
        }

        curl_close($ch);

        // Check if PayPal verifies the IPN data, and if so, return true.
        if ($res != self::VALID) {
            $save->WriteLog('napay','ipn','Paypal',"Error. Invalid PayPal Ipn",true);
        }
        $save->WriteLog('napay','ipn','Paypal',"PayPal Ipn valid");
	    /*
	     * Process IPN
	     * A list of variables is available here:
	     * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
	     */
         // Codice transazione: 79Y57005W2899811K
         //
         // mc_gross=20.00
         // protection_eligibility=Eligible
         // address_status=confirmed
         // payer_id=X6S3TCES8KTBE
         // address_street=
         // payment_date=
         // payment_status=Completed
         // charset=windows-1252
         // address_zip=81031
         // first_name=
         // mc_fee=1.03
         // address_country_code=IT
         // address_name=
         // notify_version=3.9
         // custom=
         // payer_status=verified
         // business=info%40napoliblockchain.it
         // address_country=Italy
         // address_city=
         // quantity=1
         // verify_sign=AD69DJqBYbYLJMMN8XecYqqbtU.lACd69D8AUojQZTBHGPKisYx2a3cI
         // payer_email=%40gmail.com
         // txn_id=79Y57005W2899811K
         // payment_type=instant
         // last_name=Verde
         // address_state=CE
         // receiver_email=info%40napoliblockchain.it
         // payment_fee=
         // shipping_discount=0.00
         // insurance_amount=0.00
         // receiver_id=VJYQPL7QFNYVE
         // txn_type=express_checkout
         // item_name=
         // discount=0.00
         // mc_currency=EUR
         // item_number=
         // residence_country=IT
         // shipping_method=Default
         // transaction_subject=
         // payment_gross=
         // ipn_track_id=700b70feedcd4


         // Step 1 - Autoload the SDK Package. This will include all the files and classes to your autoloader
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

        $paymentId = $raw_post_array['id'];

         try {
             $payment = \PayPal\Api\Payment::get($paymentId, $apiContext);
         } catch (\PayPal\Api\Exception $ex) {
             $save->WriteLog('napay','ipn','Paypal',"Error. The requested Paypal Payment id (".$paymentId.") does not exist.",true);
         }
        $paypal_txn_id = $payment->transactions[0]->related_resources[0]->sale->id;
        $save->WriteLog('napay','ipn','Paypal',"Paypal txn id (".$paypal_txn_id.") loaded.");

		//vado a cercare direttamente il proprietario nella tabella transaction
		$pagamenti = Pagamenti::model()->findByAttributes(array('paypal_txn_id'=>$paypal_txn_id));
		if($pagamenti===null){
            $save->WriteLog('napay','ipn','Paypal',"Error. The requested Paypal Payment invoice (".$paypal_txn_id.") does not exist.",true);
		}else{
            $save->WriteLog('napay','ipn','Paypal',"Paypal txn id (".$paypal_txn_id.") loaded.");
		}

		$settings = Settings::load();

		//A QUESTO PUNTO AGGIORNO il pagamento  IN ARCHIVIO inserendo la data del pagamento + 366 giorni
		$pagamenti->importo = $payment->transactions[0]->related_resources[0]->sale->amount->total;
        if ($payment->transactions[0]->related_resources[0]->sale->state == 'completed'){
            $pagamenti->status = 'complete';
			$pagamenti->data_scadenza	= date('Y/m/d',time()+ 60*60*24*365); //+ 1 anno
		}

		if(!$pagamenti->update()){
            $save->WriteLog('napay','ipn','Paypal',"Error. Cannot update pagamenti transaction.",true);
		}else{
            $save->WriteLog('napay','ipn','Paypal',"pagamenti transaction updated.");
		}

		//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
		$notification = array(
			'type_notification' => 'fattura',
			// 'id_merchant' => Merchants::model()->findByAttributes(array(
            //     'id_user'=>$pagamenti->id_user,
            //     'deleted'=>'0',
            // )->id_merchant),
            'id_user' => $pagamenti->id_user,
			'id_tocheck' => $pagamenti->id_pagamento,
			'status' => $pagamenti->status,
			'description' => ' ', //. $pagamenti->item_desc,
			// 'url' => Yii::app()->createUrl("pagamenti/view")."&id=".crypt::Encrypt($pagamenti->id_pagamento),
            // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
            'url' => 'index.php?r=pagamenti/view&id='.crypt::Encrypt($pagamenti->id_pagamento),
			'timestamp' => time(),
			'price' => $pagamenti->importo,
			'deleted' => 0,
		);
        Push::Send($save->Notification($notification,true),'dashboard');

        //QUINDI INVIO UNa mail
        $this->notifyMail($pagamenti->id_user,$pagamenti->id_invoice_bps);

		//ADESSO POSSO USCIRE CON UN MESSAGGIO POSITIVO ;^)
        $save->WriteLog('napay','ipn','Paypal'," : End: IPN received for Paypal transaction ".$invoice['Id']." . Status = " .$invoice['Status']." Price = ". $invoice['btcPrice']. " Paid = ".$invoice['btcPaid']);

		//Respond with HTTP 200
		header("HTTP/1.1 200 OK");
	}
}
