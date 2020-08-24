<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.Utils.Utils');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class PosController extends Controller
{
	public function init()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}
	}
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
			//'postOnly + delete', // we only allow deletion via POST request
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
					'index',
					'view',
					'create',
					'update',
					'delete',
					'ajaxCreateSelectStore',
					'BtcpayserverPairing',
					//'BitpayPairing',
					'BtcpayserverRevoke',
					//'BitpayRevoke',
					//'CoingatePairing',
					//'CoingateRevoke',
					'sendmail',
					//'activate', // creo un nuovo pairing token su btcpay server
					//'savempk', // salvo la MPK in btcpay server
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Action che Invia mail del sin
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionSendmail()
	{
		$response = $this->sendSINMail(crypt::Decrypt($_POST['id_pos']));
		echo CJSON::encode($response);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel(crypt::Decrypt($id)),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Pos;
		if(isset($_POST['Pos']))
		{
			#echo '<pre>'.print_r($_POST,true).'</pre>';
			#exit;
			$model->attributes=$_POST['Pos'];

			// SE NON C'è ID_MERCHANT VUOL DIRE CHE NON SONO ADMIN e quindi inserisco l'id di chi è collegato
			if (!isset($_POST['Pos']['id_merchant'])){
				$merchants=Merchants::model()->findByAttributes(array(
					'id_user'=>Yii::app()->user->objUser['id_user'],
					'deleted'=>0,
				));
				$model->id_merchant = $merchants->id_merchant; //id del commerciante
			}

			if($model->save()){
				$pairingCode = $this->BTCPayNewToken($model->id_pos);
				if ($pairingCode !== null){
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pos)));
				}
				$model->addError('denomination', 'Cannot retrieve pairingCode');
			}

		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		if(isset($_POST['Pos']))
		{
			$model->attributes=$_POST['Pos'];
			// SE NON C'è ID_MERCHANT VUOL DIRE CHE NON SONO ADMIN e quindi inserisco l'id di chi è collegato
			if (!isset($_POST['Pos']['id_merchant'])){
				$merchants=Merchants::model()->findByAttributes(array(
					'id_user'=>Yii::app()->user->objUser['id_user'],
					'deleted'=>0,
				));
				$model->id_merchant = $merchants->id_merchant; //id del commerciante
			}

			if($model->save()){
				$pairingCode = $this->BTCPayNewToken($model->id_pos);
				if ($pairingCode !== null){
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pos)));
				}
				$model->addError('denomination', 'Cannot retrieve pairingCode');
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		#$this->loadModel($id)->delete();
		//$this->loadModel(crypt::Decrypt($id))->delete();
		//NON CANCELLO MA IMPONGO UNO STATO AD 1
		$model = $this->loadModel(crypt::Decrypt($id));
		$model->deleted = 1;
		$model->save();


		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$criteria=new CDbCriteria();
		$criteria->compare('deleted',0,false);

		if (Yii::app()->user->objUser['privilegi'] == 10){
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>0
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
		}

		$dataProvider=new CActiveDataProvider('Pos', array(
		    'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'denomination'=>false
	    		)
	  		),
			'pagination'=>array('pageSize'=>20)
		));

		$dataStores=new CActiveDataProvider('Stores', array(
			'criteria'=>$criteria,
		));
		$stores = $dataStores->getData();

		#echo "<pre>".print_r($dataProvider,true)."</pre>";
		#exit;

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'stores'=>$stores,
		));
	}

	/**
	 * Manages all models.
	 */
	// public function actionAdmin()
	// {
	// 	$model=new Pos('search');
	// 	$model->unsetAttributes();  // clear any default values
	// 	if(isset($_GET['Pos']))
	// 		$model->attributes=$_GET['Pos'];
	//
	// 	$this->render('admin',array(
	// 		'model'=>$model,
	// 	));
	// }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Pos the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Pos::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Pos $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='pos-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	/**
     * FA IL PAIRING CON LO STORE DI BTCPAYSERVER
     */
	 public function actionBtcpayserverPairing()
 	{
		$save = new Save;

 		if (!isset($_POST)){
			$save->WriteLog('napay','pos','BtcpayserverPairing','POST parameter is required!', true);
 		}

 		// Validate the Pairing Code
 		if (true === isset($_POST['pairingCode']) && trim($_POST['pairingCode']) !== '') {
 			$pairing_code = trim($_POST['pairingCode']);
 		} else {
			$save->WriteLog('napay','pos','BtcpayserverPairing','Pairing Code is required!', true);
 		}
 		if (!preg_match('/^[a-zA-Z0-9]{7}$/', $pairing_code)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','Invalid Pairing Code!', true);
 		}

 		if (!isset($_POST['label']) || $_POST['label'] ==''){
			$save->WriteLog('napay','pos','BtcpayserverPairing','Label is required!', true);
 		}
 		if (!isset($_POST['id_pos']) || $_POST['id_pos'] ==''){
			$save->WriteLog('napay','pos','BtcpayserverPairing','Pos Id is required!', true);
 		}

 		$pos = Pos::model()->findByPk(crypt::Decrypt($_POST['id_pos']));
 		if($pos===null){
			$save->WriteLog('napay','pos','BtcpayserverPairing','The requested id does not exist on this Server!', true);
 		}
 		$merchants = Merchants::model()->findByPk($pos->id_merchant);
 		if($merchants===null){
			$save->WriteLog('napay','pos','BtcpayserverPairing','The requested merchant does not exist on this Server!', true);
 		}

		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

 		// Effettuo il login senza dati
 		$BTCPay = new BTCPay(null,null);

 		// imposto l'url
 		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

 		$url = $BTCPay->getBTCPayUrl();


 		/**
 		*	AUTOLOADER GATEWAYS
 		*/
 		// $btcpay = Yii::app()->basePath . '/extensions/gateways/btcpayserver/Btcpay/Autoloader.php';
		$btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';
 		if (true === file_exists($btcpay) && true === is_readable($btcpay)){
 		    require_once $btcpay;
 		    \Btcpay\Autoloader::register();
 		} else {
			$save->WriteLog('napay','pos','BtcpayserverPairing','Btc Library could not be loaded.', true);
 		}

 		// set folder & file
 		$folder = Yii::app()->basePath . '/privatekeys/';
 		$id_pos = "000000";

 		// Generate Private Key
 		$key = new \Btcpay\PrivateKey($folder.$id_pos.'.pri');
 		if (true === empty($key)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','The BTCPay payment plugin was called to process a pairing code but could not instantiate a PrivateKey object. Cannot continue!', true);
 		}
 		// Generate a random number
 		$key->generate();
 		$key = \Btcpay\PrivateKey::create($folder.$id_pos.'.pri')->generate();

 		// Generate Public Key
 		$pub = new \Btcpay\PublicKey($folder.$id_pos.'.pub');
 		if (true === empty($pub)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','The BTCPay payment plugin was called to process a pairing code but could not instantiate a PublicKey object. Cannot continue!', true);
 		}
 		// Inject the private key into the public key
 		$pub->setPrivateKey($key);
 		// Generate the public key
 		$pub->generate();

 		// Get SIN Format
 		$sin = new \Btcpay\SinKey();
 		if (true === empty($sin)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','The BTCPay payment plugin was called to process a pairing code but could not instantiate a SinKey object. Cannot continue!', true);
 		}
 		$sin->setPublicKey($pub);
 		$sin->generate();

 		// Create an API Client
 		$client = new \Btcpay\Client\Client();
 		if (true === empty($client)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','The BTCPay payment plugin was called to process a pairing code but could not instantiate a Client object. Cannot continue!', true);
 		}

 		$client->setUri($url);
 		$curlAdapter = new \Btcpay\Client\Adapter\CurlAdapter();
 		if (true === empty($curlAdapter)) {
			$save->WriteLog('napay','pos','BtcpayserverPairing','The BTCPay payment plugin was called to process a pairing code but could not instantiate a CurlAdapter object. Cannot continue!', true);
 		}

 		$client->setAdapter($curlAdapter);
 		$client->setPrivateKey($key);
 		$client->setPublicKey($pub);

 		// Sanitize label
 		$label = preg_replace('/[^a-zA-Z0-9 \-\_\.]/', '', $_POST['label']);
 		$label = substr($label, 0, 59);

 		try {
 			$token = $client->createToken(
 				array(
 					'id'          => (string) $sin,
 					'pairingCode' => $pairing_code,
 					'label'       => $label,
 				)
 			);
 		} catch (\Exception $e) {
			$save->WriteLog('napay','pos','BtcpayserverPairing',$e->getMessage(), true);
 		}
 		//TOKEN
 		$persistThisValue = $token->getToken();

 		//SAVE PAIRING INFORMATIONS
 		$model = new Pairings;
 		$model->id_pos = crypt::Decrypt($_POST['id_pos']);
 		$model->token = crypt::Encrypt($persistThisValue);
 		$model->label = $label;
 		$model->sin = (string) $sin;

 		// echo "<pre>".print_r($model->attributes,true)."</pre>";
 		// exit;

 		if (!$model->save()){
			$save->WriteLog('napay','pos','BtcpayserverPairing','Cannot save pairing informations.', true);
 		}
 		$updatepos = Pos::model()->findByPk(crypt::Decrypt($_POST['id_pos']));
 		$updatepos->pairingCode = '';
 		if (!$updatepos->save()){
			$save->WriteLog('napay','pos','BtcpayserverPairing','Cannot delete old pairingCode.', true);
 		}


 		/**
 		 * It's recommended that you use the EncryptedFilesystemStorage engine to persist your
 		 * keys. You can, of course, create your own as long as it implements the StorageInterface
 		 */
 		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage('mc156MdhshuUYTF5365');
 		$storageEngine->persist($key);
 		$storageEngine->persist($pub);

 		// rinomino i file con il nome SIN
 		$newfile = (string) $sin;

 		rename ($folder.$id_pos.'.pri',$folder.$newfile.'.pri');
 		rename ($folder.$id_pos.'.pub',$folder.$newfile.'.pub');

 		$return = array(
 			'sin' => (string) $sin,
 			'token' =>  $persistThisValue,
 			'message' => 'Successful pairing',
 			'success' => true,
 		);

 		//restituisco alla funzione i nuovi valori inseriti
		$save->WriteLog('napay','pos','BtcpayserverPairing', $newfile. ' paired successfully.');
        echo CJSON::encode($return);
 	}


	/**
	 * INVIA LA MAIL DEL SIN del POS al Commerciante
	 * $id è l'id del POS
	 */
	 public function sendSINMail($id){
		$pos=Pos::model()->findByPk($id);
		$pairings=Pairings::model()->findByAttributes(array('id_pos'=>$id));
 		$merchants=Merchants::model()->findByPk($pos->id_merchant);
 		$users=Users::model()->findByPk($merchants->id_user);

 		if (NMail::SendMail('sin',crypt::Encrypt($users->id_user),$users->email,$pairings->sin))
			return ['success'=>1,'message'=>'Mail inviata correttamente.'];
		else
			return ['success'=>0,'message'=>'Non è stato possibile inviare la mail!'];
	 }


	/**
     * Carica gli Stores di un particolare Merchant
     */
    public function actionAjaxCreateSelectStore($id) {
        $stores = Stores::model()->findAll(array("condition"=>"id_merchant = '$id' AND deleted=0"));
		$list = CHtml::listData($stores, 'id_store', 'denomination');
        echo CJSON::encode($list);
    }

	/**
     * FA IL PAIRING CON LO STORE DI bitpay
	 * TODO:: DA RIVDERE funzionamento Bitpay e/o escludere completamente
     */
    public function actionBitpayPairing() {
		if (true === isset($_POST['pairing_code']) && trim($_POST['pairing_code']) !== '') {
			// Validate the Pairing Code
			$pairing_code = trim($_POST['pairing_code']);
		} else {
			echo CJSON::encode(array("error"=>"Pairing Code is required"));
			return;
		}
		if (!preg_match('/^[a-zA-Z0-9]{7}$/', $pairing_code)) {
			echo CJSON::encode(array("error"=>"Invalid Pairing Code"));
			return;
		}

		// $url = Yii::app()->params['BTCPayServerAddress'];
		// if (!filter_var($url, FILTER_VALIDATE_URL) || (substr( $url, 0, 7 ) !== "http://" && substr( $url, 0, 8 ) !== "https://")) {
		// 	echo CJSON::encode(array("error"=>"Invalid url"));
		// 	return;
		// }

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

		// Generate Private Key
		$folder = Yii::app()->basePath . '/privatekeys/bitpay-';
		$id_pos = crypt::Decrypt($_POST['id_pos']);
		#echo $folder;
		#exit;
		$key = new \Bitpay\PrivateKey($folder.$id_pos.'.pri');
		if (true === empty($key)) {
			echo CJSON::encode(array("error"=>"The BTCPay payment plugin was called to process a pairing code but could not instantiate a PrivateKey object. Cannot continue!"));
			return;
		}
		// Generate a random number
		$key->generate();
		$key = \Bitpay\PrivateKey::create($folder.$id_pos.'.pri')->generate();

		// Generate Public Key
		$pub = new \Bitpay\PublicKey($folder.$id_pos.'.pub');
		if (true === empty($pub)) {
			echo CJSON::encode(array("error"=>"The BTCPay payment plugin was called to process a pairing code but could not instantiate a PublicKey object. Cannot continue!"));
			return;
		}
		// Inject the private key into the public key
		$pub->setPrivateKey($key);
		// Generate the public key
		$pub->generate();

		// Get SIN Format ----- IN BITPAY SEMBRA CHE VENGA PRIMO O POI DISMESSA QUESTA FUNZIONE 002_PAIR.php examples of bitpay github
		$sin = new \Bitpay\SinKey();
		if (true === empty($sin)) {
			echo CJSON::encode(array("error"=>"The BTCPay payment plugin was called to process a pairing code but could not instantiate a SinKey object. Cannot continue!"));
			return;
		}
		$sin->setPublicKey($pub);
		$sin->generate();

		// Create an API Client ClientBitpay
		$client = new \Bitpay\Client\Client();
		if (true === empty($client)) {
			echo CJSON::encode(array("error"=>"The BTCPay payment plugin was called to process a pairing code but could not instantiate a Client object. Cannot continue!"));
			return;
		}


		//DIFFERENZE TRA BTCPAYSERVER E BitPay
		// $client->setUri($url);
		//$network = new \Bitpay\Network\Testnet();
		$network = new \Bitpay\Network\Livenet();

		$curlAdapter = new \Bitpay\Client\Adapter\CurlAdapter();
		if (true === empty($curlAdapter)) {
			echo CJSON::encode(array("error"=>"The BTCPay payment plugin was called to process a pairing code but could not instantiate a CurlAdapter object. Cannot continue!"));
			return;
		}

		$client->setNetwork($network); ////DIFFERENZE TRA BTCPAYSERVER E BitPay
		$client->setAdapter($curlAdapter);
		$client->setPrivateKey($key);
		$client->setPublicKey($pub);

		// Sanitize label
		$label = preg_replace('/[^a-zA-Z0-9 \-\_\.]/', '', $_POST['label']);
		$label = substr($label, 0, 59);

		try {
			$token = $client->createToken(
				array(
					'id'          => (string) $sin,
					'pairingCode' => $pairing_code,
					'label'       => $label,
				)
			);
		} catch (\Exception $e) {
			$send_json = array ('error'=>$e->getMessage());
			echo CJSON::encode($send_json);
			return;
		}
		//TOKEN
		$persistThisValue = $token->getToken();

		//SAVE PAIRING INFORMATIONS
		$model = new Pairings;
		$model->id_pos = $id_pos;
		$model->token = crypt::Encrypt($persistThisValue);
		$model->label = $label;
		$model->sin = (string) $sin;

		if (!$model->save()){
			$send_json = array ('error'=>'Cannot save pairing informations!');
			echo CJSON::encode($send_json);
			return;
		}
		/**
		 * It's recommended that you use the EncryptedFilesystemStorage engine to persist your
		 * keys. You can, of course, create your own as long as it implements the StorageInterface
		 */
		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mnewhdo3yy4yFDASc156MdhshuUYTF5365');
		$storageEngine->persist($key);
		$storageEngine->persist($pub);

		$send_json = array(
			'sin' => (string) $sin,
			'label' => $label,
		);
        echo CJSON::encode($send_json);
    }

	/**
     * FA IL PAIRING CON LO STORE DI COINGATE
     */
    public function actionCoingatePairing() {
		if (true === isset($_POST['pairing_code']) && trim($_POST['pairing_code']) !== '') {
			// Validate the Pairing Code
			$pairing_code = trim($_POST['pairing_code']);
		} else {
			echo CJSON::encode(array("error"=>"Pairing Code is required"));
			return;
		}
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;

		//TOKEN
		$persistThisValue = $_POST['pairing_code'];
		$id_pos = crypt::Decrypt($_POST['id_pos']);
		$label = $_POST['label'];
		$sin = $_POST['id_pos'];

		//SAVE PAIRING INFORMATIONS
		$model = new Pairings;
		$model->id_pos = $id_pos;
		$model->token = crypt::Encrypt($persistThisValue);
		$model->label = $label;
		$model->sin = $sin;

		if (!$model->save()){
			$send_json = array ('error'=>'Cannot save pairing informations!');
			echo CJSON::encode($send_json);
			return;
		}
		$send_json = array(
			'sin' => $sin,
			'label' => $label,
		);
        echo CJSON::encode($send_json);
    }

	public function actionBtcpayserverRevoke(){
		$save = new Save;
		$id_pos = crypt::Decrypt($_POST['id_pos']);
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));

		$folder = Yii::app()->basePath . '/privatekeys/';
		if (file_exists($folder.$pairings->sin.'.pri'))
			unlink ($folder.$pairings->sin.'.pri');

		if (file_exists($folder.$pairings->sin.'.pub'))
			unlink ($folder.$pairings->sin.'.pub');

		Pairings::model()->findByPk($pairings->id_pairing)->delete();

		$send_json = array(
			'success' => 1,
		);
		$save->WriteLog('napay','pos','BtcpayserverRevoke', $pairings->sin. ' revoked successfully.');
		echo CJSON::encode($send_json);
	}

	public function actionBitpayRevoke(){
		$id_pos = crypt::Decrypt($_POST['id_pos']);
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));;

		$folder = Yii::app()->basePath . '/privatekeys/btcpay-';
		if (file_exists($folder.$id_pos.'.pri'))
			unlink ($folder.$id_pos.'.pri');

		if (file_exists($folder.$id_pos.'.pub'))
			unlink ($folder.$id_pos.'.pub');

		Pairings::model()->findByPk($pairings->id_pairing)->delete();

		$send_json = array(
			'success' => 1,
		);
		echo CJSON::encode($send_json);
	}

	public function actionCoingateRevoke(){
		$id_pos = crypt::Decrypt($_POST['id_pos']);
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));;

		Pairings::model()->findByPk($pairings->id_pairing)->delete();

		$send_json = array(
			'success' => 1,
		);
		echo CJSON::encode($send_json);
	}

	/**
	 * Eichiede il pairingCode
	 * @param array $post : $_POST
	 */
	public function BTCPayNewToken($id_pos) {
		$save = new Save;
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$pos = Pos::model()->findByPk($id_pos);
		$stores = Stores::model()->findByPk($pos->id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));

		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		//richiedo il pairing code
		$pairingCode = $BTCPay->newToken();

		$pos->pairingCode = $pairingCode;
		$pos->update();

		$save->WriteLog('napay','pos','BTCPayNewToken', $pairingCode. ' token requested.');

		return $pairingCode;
	}
}
