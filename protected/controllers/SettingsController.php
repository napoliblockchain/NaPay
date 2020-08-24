<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class SettingsController extends Controller
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
					//'create',
					'update',
					//'delete',
					'BtcpayserverPairing',
					'BtcpayserverRevoke',
					//'token',
					'storeUpdate', // aggiorno il negozio dell'associazione
					'storeGeneral',
					'storeExchange',
					'storeSavempk',
					'storeCheckoutLogo',
					'storeCheckoutCss',
					'storeCheckout',
					'posUpdate',
					'posDelete',
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
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
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		 // echo '<pre>'.print_r($_POST,true).'</pre>';
		 // exit;
		if (crypt::Decrypt($id)==0){
			$model = new SettingsWebappForm;
			$settings= Settings::load();
			$model->attributes = (array)$settings;

			if(isset($_POST['SettingsWebappForm']))
			{
				// echo '<pre class="text-light">'.print_r($_POST,true).'</pre>';
				// echo '<pre class="text-light">'.print_r($_POST,true).'</pre>';
				 // exit;
				$model->attributes=$_POST['SettingsWebappForm'];
				// echo '<pre>'.print_r($model->attributes,true).'</pre>';
				// exit;

				if (isset($_POST['SettingsWebappForm']['sshhost'])){
					$model->sshhost = crypt::Encrypt($_POST['SettingsWebappForm']['sshhost']);
					$model->sshuser = crypt::Encrypt($_POST['SettingsWebappForm']['sshuser']);
					$model->sshpassword = crypt::Encrypt($_POST['SettingsWebappForm']['sshpassword']);
				}
				if (isset($_POST['SettingsWebappForm']['exchange_secret'])){
					$model->exchange_secret = crypt::Encrypt($_POST['SettingsWebappForm']['exchange_secret']);
				}

				if (isset($_POST['SettingsWebappForm']['poa_sealerPrvKey'])){
					$model->poa_sealerPrvKey = crypt::Encrypt($_POST['SettingsWebappForm']['poa_sealerPrvKey']);
				}

				if (isset($_POST['blockchainAsset'])){
					$model->blockchainAsset = CJSON::encode($_POST['blockchainAsset']);
					$blockchain = Blockchains::model()->findByAttributes(['url'=>$model->blockchainAddress]);

					// salvo il nuovo store
					$store=new Stores;
					$store->id_merchant = 0;
					$store->denomination = $model->store_denomination;
					$store->address = $model->gdpr_address;
					$store->city = $model->gdpr_city;
					$store->county = $model->gdpr_country;
					$store->cap = $model->gdpr_cap;
					$store->vat = $model->gdpr_vat;
					$store->bps_storeid = $model->bps_storeid;
					$store->save();
					$model->id_store = $store->id_store;
					$model->bps_storeid = $this->createBTCPayStore($store->id_store,$blockchain->id_blockchain);
				}

				if (isset($_POST['SettingsWebappForm']['confirmPos']) && $_POST['SettingsWebappForm']['confirmPos'] == 'on'){
					$model->id_store = $_POST['SettingsWebappForm']['id_store'];
					$model->pos_denomination = $_POST['SettingsWebappForm']['pos_denomination'];

					if ($model->pos_pairingCode == ''){
						$model->pos_pairingCode = $this->BTCPayNewToken($model->id_store);
					}
				}
				Settings::save($model);
			}
			$this->render('webapp/update',array(
				'model'=>$model,
				'settings'=>$settings,
			));
		}else{
			#echo '<pre>'.print_r($_POST,true).'</pre>';
			#exit;
			$model = new SettingsUserForm;
			$settings = Settings::loadUser(crypt::Decrypt($id));
			$model->attributes = (array)$settings;

			if(isset($_POST['SettingsUserForm']))
			{
				$model->attributes=$_POST['SettingsUserForm'];
				$model->id_user = crypt::Decrypt($id);
				if ($model->validate()){
					if (isset($_POST['SettingsUserForm']['exchange_secret'])){
						$model->exchange_secret = crypt::Encrypt($_POST['SettingsUserForm']['exchange_secret']);
						$model->withdrawal_exchange_secret = crypt::Encrypt($_POST['SettingsUserForm']['withdrawal_exchange_secret']);
					}
					Settings::saveUser($model->id_user,$model->attributes);
				}
			}
			$this->render('update',array(
				'model'=>$model,
			));
		}
	}
	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionPosDelete($id)
	{
		#$this->loadModel($id)->delete();
		//$this->loadModel(crypt::Decrypt($id))->delete();
		//NON CANCELLO MA IMPONGO UNO STATO AD 1
		$model = Pos::model(crypt::Decrypt($id))->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionPosUpdate($id)
	{
		$model = new SettingsWebappForm;
		$settings= Settings::load();
		$model->attributes = (array)$settings;

		if(isset($_POST['SettingsWebappForm']))
		{
			// echo '<pre class="text-light">'.print_r($_POST,true).'</pre>';
			// echo '<pre class="text-light">'.print_r($_POST,true).'</pre>';
			 // exit;
			$model->attributes=$_POST['SettingsWebappForm'];
			// echo '<pre>'.print_r($model->attributes,true).'</pre>';
			// exit;

			if (isset($_POST['SettingsWebappForm']['confirmPos']) && $_POST['SettingsWebappForm']['confirmPos'] == 'on'){
				$model->id_store = $_POST['SettingsWebappForm']['id_store'];
				$model->pos_denomination = $_POST['SettingsWebappForm']['pos_denomination'];

				$model->pos_pairingCode = $this->BTCPayNewToken($model->id_store);
			}
			if (Settings::save($model))
				$this->redirect(array('update','id'=>crypt::Encrypt(0)));
		}
		$this->render('webapp/pos/update',array(
			'model'=>$model,
			'settings'=>$settings,
		));

	}

	/**
	 * Crea lo store anche su BTCPay Server
	 * @return : id_store_bps
	 * @param integer $id_merchant : id del commerciante
	 * @param integer $id_store : id del nuovo store creato
	 */
	public function createBTCPayStore($id_store, $id_blockchain) {
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$blockchain = Blockchains::model()->findByPk($id_blockchain);
		$stores = Stores::model()->findByPk($id_store);

		// Effettuo il login con il nuovo user e creo lo store
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));

		// imposto l'url
		$BTCPay->setBTCPayUrl($blockchain->url);

		// creo lo store
		$storeId = $BTCPay->newStore($stores->denomination);

		//aggiorno lo store con il parametro appena ricevuto
		$stores->bps_storeid = $storeId;
		$stores->update();

		// aggiorno i settings dello stores
		Settings::saveStore($stores->id_store,['bps_storeid'=>$storeId]);
		Settings::saveStore($stores->id_store,['store_denomination'=>$stores->denomination]);

		// Settings::save(['bps_storeid'=>$storeId]);
		// Settings::save(['store_denomination'=>$stores->denomination]);
		// Settings::save(['id_store'=>$id_store]);
		// $return = [
		// 	'bps_storeid'=>$storeId,
		// ];

		return $storeId;
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionToken($id)
	{

		#echo '<pre>'.print_r($_POST,true).'</pre>';
		#exit;
		$model = new SettingsUserForm;
		$settings = Settings::loadUser(crypt::Decrypt($id));
		$model->attributes = (array)$settings;

		$wallets = Wallets::model()->findByAttributes(array('id_wallet'=>$model->id_wallet));
		if ($wallets === null)
			$wallets = new Wallets;

		if(isset($_POST['SettingsUserForm']))
		{
			#echo "sono qui";
			#exit;
			$model->id_wallet=$_POST['SettingsUserForm']['id_wallet'];
			$model->id_user = crypt::Decrypt($id);
			#echo '<pre>'.print_r($model->id_user,true).'</pre>';
			#exit;

			if (isset($_POST['Salva']['new_address']) && $_POST['Salva']['new_address']==1){
				$wallets = new Wallets;
				$wallets->attributes = $_POST['Wallets'];
				$wallets->id_user = $model->id_user;
				$wallets->insert();
				$model->id_wallet = $wallets->id_wallet;
			}
			Settings::saveUser($model->id_user,$model->attributes,array('id_wallet'));
		}
		//carico tutti i wallet del merchants (by user_id)
		$walletCriteria=new CDbCriteria();
		$walletCriteria->compare('id_user',Yii::app()->user->objUser['id_user'], true);
		$walletsProvider=new CActiveDataProvider('Wallets', array(
			'sort'=>array(
				'defaultOrder'=>array(
					'id_wallet'=>true
				)
			),
			'criteria'=>$walletCriteria,
		));
		$this->render('token/_token',array(
			'model'=>$model,
			'wallets'=>$wallets, //il wallet selezionato
			'walletsProvider'=>$walletsProvider, //lista dei wallet del merchant
		));

	}


	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		if (Yii::app()->user->objUser['privilegi'] == 20){
			$settings = Settings::load();
			$this->render('webapp/view',array(
				'model'=>$settings,
			));
		}else {
			$model = new SettingsUserForm;
			$settings = Settings::loadUser(Yii::app()->user->objUser['id_user']);
			$model->attributes = (array)$settings;
			#echo '<pre>'.print_r($model,true).'</pre>';
			#exit;
			//se è vuoto settings allora vado in update, altrimenti visualizzo
			if (empty($model->id_user))
				$this->redirect(array('update','id'=>crypt::Encrypt(Yii::app()->user->objUser['id_user'])));


			$this->render('view',array(
				'model'=>$model,
				//'wallets'=>$wallets, //il wallet selezionato
				//'walletsProvider'=>$walletsProvider, //lista dei wallet del merchant
			));
		}
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Settings the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Settings::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Settingss $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='settings-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	/**
	 * FA IL REVOKE DEL PAIRING CON LO STORE DI BTCPAYSERVER
	 */
	public function actionBtcpayserverRevoke(){
		// carico l'estensione
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		//
		$settings = Settings::load();
		$store = Stores::model()->findByPk($settings->id_store);

		$id_pos = crypt::Decrypt($_POST['id_pos']);
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));

		// cancello i file
		$folder = Yii::app()->basePath . '/privatekeys/';
		if (file_exists($folder.$pairings->sin.'.pri'))
			unlink ($folder.$pairings->sin.'.pri');

		if (file_exists($folder.$pairings->sin.'.pub'))
			unlink ($folder.$pairings->sin.'.pub');

		// cancello i dati di pairings su db
		Pairings::model()->findByPk($pairings->id_pairing)->delete();

		// resetto le informazioni sin e token della webapp
		$save = [
			'pos_pairingCode' => null,
			'pos_sin' => null,
		];
		Settings::save($save);

		// echo '<pre>'.print_r($settings,true).'</pre>';
		// exit;
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId prima di chiamare la funzione
		$BTCPay->setStoreId($store->bps_storeid);
		// Imposto il sin token prima di chiamare la funzione
		$BTCPay->setSinToken(crypt::Decrypt($pairings->token));
		// eseguo il revoke
		$revoke = $BTCPay->TokensRevoke();

		$send_json = array(
			'success' => 1,
		);
		echo CJSON::encode($send_json);
	}


	/**
     * FA IL PAIRING CON LO STORE DI BTCPAYSERVER
     */
	 public function actionBtcpayserverPairing()
 	{
		$save = new Save;
		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;
 		if (!isset($_POST)){
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'POST parameter is required!', true);
 		}

 		// Validate the Pairing Code
 		if (true === isset($_POST['pairingCode']) && trim($_POST['pairingCode']) !== '') {
 			$pairing_code = trim($_POST['pairingCode']);
 		} else {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Pairing Code is required!', true);
 		}
 		if (!preg_match('/^[a-zA-Z0-9]{7}$/', $pairing_code)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Invalid Pairing Code!', true);
 		}

 		if (!isset($_POST['label']) || $_POST['label'] ==''){
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Label is required!', true);
 		}
 		if (!isset($_POST['id_pos']) || $_POST['id_pos'] ==''){
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Pos id is required!', true);
 		}

 		// carico l'estensione
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$settings = Settings::load();
		$stores = Stores::model()->findByPk($settings->id_store);

		// Effettuo il login senza dati
 		$BTCPay = new BTCPay(null,null);
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

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
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Btc Library could not be loaded.', true);
 		}

 		// set folder & file
 		$folder = Yii::app()->basePath . '/privatekeys/';
 		$id_pos = "000000";

 		// Generate Private Key
 		$key = new \Btcpay\PrivateKey($folder.$id_pos.'.pri');
 		if (true === empty($key)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'The BTCPay payment plugin was called to process a pairing code but could not instantiate a PrivateKey object. Cannot continue!', true);
 		}
 		// Generate a random number
 		$key->generate();
 		$key = \Btcpay\PrivateKey::create($folder.$id_pos.'.pri')->generate();

 		// Generate Public Key
 		$pub = new \Btcpay\PublicKey($folder.$id_pos.'.pub');
 		if (true === empty($pub)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'The BTCPay payment plugin was called to process a pairing code but could not instantiate a PublicKey object. Cannot continue!', true);
 		}
 		// Inject the private key into the public key
 		$pub->setPrivateKey($key);
 		// Generate the public key
 		$pub->generate();

 		// Get SIN Format
 		$sin = new \Btcpay\SinKey();
 		if (true === empty($sin)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'The BTCPay payment plugin was called to process a pairing code but could not instantiate a SinKey object. Cannot continue!', true);
 		}
 		$sin->setPublicKey($pub);
 		$sin->generate();

 		// Create an API Client
 		$client = new \Btcpay\Client\Client();
 		if (true === empty($client)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'The BTCPay payment plugin was called to process a pairing code but could not instantiate a Client object. Cannot continue!', true);
 		}

 		$client->setUri($url);
 		$curlAdapter = new \Btcpay\Client\Adapter\CurlAdapter();
 		if (true === empty($curlAdapter)) {
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'The BTCPay payment plugin was called to process a pairing code but could not instantiate a CurlAdapter object. Cannot continue!', true);
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
			$save->WriteLog('napay','settings','BtcpayserverPairing', $e->getMessage(), true);
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
			$save->WriteLog('napay','settings','BtcpayserverPairing', 'Cannot save pairing informations.', true);
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

		// resetto il pairing code e aggiorno il sin e token della webapp
		$save = [
			'pos_pairingCode' => 0,
			'pos_sin' => $newfile,
		];
		Settings::save($save);

 		//restituisco alla funzione i nuovi valori inseriti
		$save->WriteLog('napay','settings','BtcpayserverPairing', $newfile. ' paired successfully.');
        echo CJSON::encode($return);
 	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionStoreUpdate($id)
	{
		$model = new SettingsWebappForm;
		$settings= Settings::load();
		$model->attributes = (array)$settings;

		if(isset($_POST['SettingsWebappForm']))
		{
			$model->attributes=$_POST['SettingsWebappForm'];
			$model->blockchainAsset = CJSON::encode($_POST['blockchainAsset']);

			// echo '<pre>'.print_r($model->attributes,true).'</pre>';
			// exit;

			if (Settings::save($model))
				$this->redirect(array('update','id'=>crypt::Encrypt(0)));
		}

		$this->render('webapp/stores/update',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionStoreGeneral($id)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['StoreForm']))
		{
			foreach ($_POST['StoreForm'] as $key => $value)
				$model->$key = $value;

			if ($model->validate()){
				Settings::saveStore($store->id_store,$model->attributes);
				if ($this->BTCPayStoreGeneral(crypt::Decrypt($id),$_POST['StoreForm']) == true){
					$save->WriteLog('napay','settings','StoreGeneral', 'Store General Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email']);
					$this->redirect(array('update','id'=>crypt::Encrypt(0)));
				}
				$model->addError('store_denomination', 'Cannot update General Settings');
				$save->WriteLog('napay','settings','StoreGeneral', 'Error while setting Store General Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email']);
			}
		}

		$this->render('webapp/stores/general/update',array(
			'model'=>$model,
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionStoreExchange($id)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['StoreForm']))
		{
			foreach ($_POST['StoreForm'] as $key => $value)
				$model->$key = $value;

			if ($model->validate()){
				Settings::saveStore($store->id_store,$model->attributes);
				if ($this->BTCPayStoreExchange(crypt::Decrypt($id),$_POST['StoreForm']) == true){
					$save->WriteLog('napay','settings','StoreExchange', 'Store Exchange Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('update','id'=>crypt::Encrypt(0)));
				}
				$model->addError('preferred_exchange', 'Cannot update Exchange');
				$save->WriteLog('napay','settings','StoreExchange', 'Error while setting Store Exchange Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}

		}

		$this->render('webapp/stores/exchange/update',array(
			'model'=>$model,
			'preferredPriceSource'=>$this->getPreferredPriceSource(crypt::Decrypt($id))
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionStoreSavempk($id,$asset)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = Mpk::model()->findByAttributes(['id_store'=>$store->id_store,'asset'=>$asset]);

		if (null === $model)
			$model = new Mpk;

		if(isset($_POST['Mpk']))
		{
			$model->attributes=$_POST['Mpk'];
			$model->id_store = $store->id_store;
			$model->Addresses = ''; //fix empty value
			if ($model->save()){
				$mpk = $this->BTCPayStoreMPK($store->id_store,$_POST['Mpk']);
				if ($mpk == true){
					$save->WriteLog('napay','settings','StoreSavempk', 'Store MPK Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('update','id'=>crypt::Encrypt(0)));
				}
				$model->addError('asset', 'Cannot update Master Public Key');
				$save->WriteLog('napay','settings','StoreSavempk', 'Error while setting Store MPK Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}else{
				$model->addError('asset', 'Cannot update Master Public Key');
			}

		}

		$this->render('webapp/stores/savempk/update',array(
			'model'=>$model,
			'asset'=>$asset,
		));
	}
	/**
	 * Carica l'immagine del LOGO
	 * If Carica is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionStoreCheckoutLogo($id)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['StoreForm']) && isset($_FILES['StoreForm']))
		{
			foreach ($_POST['StoreForm'] as $key => $value)
				$model->$key = $value;

			if ($model->validate()){
				if($_FILES['StoreForm']['error']['CustomLogo']==0){
					if($_FILES['StoreForm']['size']['CustomLogo'] < 3000000){ //< 3Mb
						$saveName = $_FILES['StoreForm']['name']['CustomLogo'];
						//$saveURL = 'https://'.Utils::get_domain($_SERVER['HTTP_HOST']) . '/custom/' . $store->bps_storeid;
						$saveURL = 'https://'. $_SERVER['HTTP_HOST'] . '/custom/' . $store->bps_storeid;
						$savePath = Yii::app()->basePath . '/../custom/' . $store->bps_storeid;
						$filename = $saveURL .'/'. $saveName;
						if (!file_exists($savePath)) {
						    mkdir($savePath, 0777, true);
						}
						$ext = pathinfo($filename, PATHINFO_EXTENSION);

						move_uploaded_file($_FILES['StoreForm']['tmp_name']['CustomLogo'], $savePath . '/' . $saveName);
						rename ($savePath.'/'.$saveName, $savePath.'/CustomLogo.'.$ext);
						$model->CustomLogo = $saveURL .'/CustomLogo.'. $ext;
						$post['CustomLogo'] = $model->CustomLogo;

						$post['DefaultPaymentMethod'] = (!empty($model->DefaultPaymentMethod) ? $model->DefaultPaymentMethod : 'BTC'); // servono altrimenti va in blocco
						$post['DefaultLang'] = (!empty($model->DefaultLang) ? $model->DefaultLang : 'en');// servono altrimenti va in blocco
						$post['ShowRecommendedFee'] = (!empty($model->ShowRecommendedFee) ? $model->ShowRecommendedFee : 'false'); // servono altrimenti va in blocco
						$post['RecommendedFeeBlockTarget'] = (!empty($model->RecommendedFeeBlockTarget) ? $model->RecommendedFeeBlockTarget : 1); // servono altrimenti va in blocco
						$post['LightningAmountInSatoshi'] = (!empty($model->LightningAmountInSatoshi) ? $model->LightningAmountInSatoshi : 'false'); // servono altrimenti va in blocco
						$post['RedirectAutomatically'] = (!empty($model->RedirectAutomatically) ? $model->RedirectAutomatically : 'false');// servono altrimenti va in blocco
						$post['command'] = (!empty($model->command) ? $model->command : 'Save');// servono altrimenti va in blocco

						Settings::saveStore($store->id_store,$model->attributes,['CustomLogo']);
						if ($this->BTCPayStoreCheckout(crypt::Decrypt($id),$post) == true){
							$save->WriteLog('napay','settings','StoreCheckoutLogo', 'Store LOGO Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
							$this->redirect(array('update','id'=>crypt::Encrypt(0)));
						}
						$model->addError('CustomLogo', 'Cannot update Checkout LOGO');
						$save->WriteLog('napay','settings','StoreCheckoutLogo', 'Error while setting Store LOGO Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
					}else{
						$model->CustomLogo = '';
					}
		        }else{
					$model->CustomLogo = '';
				}
			}
		}

		$this->render('webapp/stores/checkoutLogo/update',array(
			'model'=>$model,
		));
	}
	/**
	 * Carica l'immagine del CSS
	 * If Carica is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionStoreCheckoutCss($id)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['StoreForm']) && isset($_FILES['StoreForm']))
		{
			foreach ($_POST['StoreForm'] as $key => $value)
				$model->$key = $value;

			if ($model->validate()){
				if($_FILES['StoreForm']['error']['CustomCSS']==0){
					if($_FILES['StoreForm']['size']['CustomCSS'] < 3000000){ //< 3Mb
						$saveName = $_FILES['StoreForm']['name']['CustomCSS'];
						//$saveURL = 'https://'.Utils::get_domain($_SERVER['HTTP_HOST']) . '/custom/' . $store->bps_storeid;
						$saveURL = 'https://'. $_SERVER['HTTP_HOST'] . '/custom/' . $store->bps_storeid;
						$savePath = Yii::app()->basePath . '/../custom/' . $store->bps_storeid;
						$filename = $saveURL .'/'. $saveName;
						if (!file_exists($savePath)) {
						    mkdir($savePath, 0777, true);
						}
						$ext = pathinfo($filename, PATHINFO_EXTENSION);

						move_uploaded_file($_FILES['StoreForm']['tmp_name']['CustomCSS'], $savePath . '/' . $saveName);
						rename ($savePath.'/'.$saveName, $savePath.'/CustomCSS.'.$ext);
						$model->CustomCSS = $saveURL .'/CustomCSS.'. $ext;
						$post['CustomCSS'] = $model->CustomCSS;

						$post['DefaultPaymentMethod'] = (!empty($model->DefaultPaymentMethod) ? $model->DefaultPaymentMethod : 'BTC'); // servono altrimenti va in blocco
						$post['DefaultLang'] = (!empty($model->DefaultLang) ? $model->DefaultLang : 'en');// servono altrimenti va in blocco
						$post['ShowRecommendedFee'] = (!empty($model->ShowRecommendedFee) ? $model->ShowRecommendedFee : 'false'); // servono altrimenti va in blocco
						$post['RecommendedFeeBlockTarget'] = (!empty($model->RecommendedFeeBlockTarget) ? $model->RecommendedFeeBlockTarget : 1); // servono altrimenti va in blocco
						$post['LightningAmountInSatoshi'] = (!empty($model->LightningAmountInSatoshi) ? $model->LightningAmountInSatoshi : 'false'); // servono altrimenti va in blocco
						$post['RedirectAutomatically'] = (!empty($model->RedirectAutomatically) ? $model->RedirectAutomatically : 'false');// servono altrimenti va in blocco
						$post['command'] = (!empty($model->command) ? $model->command : 'Save');// servono altrimenti va in blocco

						Settings::saveStore($store->id_store,$model->attributes,['CustomCSS']);
						if ($this->BTCPayStoreCheckout(crypt::Decrypt($id),$post) == true){
							$save->WriteLog('napay','settings','StoreCheckoutCss', 'Store CSS Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
							$this->redirect(array('update','id'=>crypt::Encrypt(0)));
						}
						$model->addError('CustomLogo', 'Cannot update Checkout CSS');
						$save->WriteLog('napay','settings','StoreCheckoutCss', 'Error while setting Store CSS Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
					}else{
						$model->CustomCSS = '';
					}
		        }else{
					$model->CustomCSS = '';
				}
			}
		}

		$this->render('webapp/stores/checkoutCss/update',array(
			'model'=>$model,
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionStoreCheckout($id)
	{
		$save = new Save;
		$store = Stores::model()->findByPk(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['StoreForm']))
		{
			foreach ($_POST['StoreForm'] as $key => $value)
				$model->$key = $value;

			if ($model->validate()){
				Settings::saveStore($store->id_store,$model->attributes);
				if ($this->BTCPayStoreCheckout(crypt::Decrypt($id),$_POST['StoreForm']) == true){
					$save->WriteLog('napay','settings','StoreCheckout', 'Store Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('update','id'=>crypt::Encrypt(0)));
				}
				$model->addError('CustomLogo', 'Cannot update Checkout');
				$save->WriteLog('napay','settings','StoreCheckout', 'Error while setting Store Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}

		}
		$getDefaults = $this->getDefaultsCheckout(crypt::Decrypt($id));
		// echo "<pre>".print_r($getDefaults,true)."</pre>";
		// exit;

		$this->render('webapp/stores/checkout/update',array(
			'model'=>$model,
			'DefaultPaymentMethod'=>(isset($getDefaults['DefaultPaymentMethod']) ? $getDefaults['DefaultPaymentMethod'] : [0=>'You must first setup your Master Public Key']),
			'DefaultLang'=>$getDefaults['DefaultLang'],
		));
	}
	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_store : id del nuovo store creato
	 * @param array $post : $_POST
	 */
	public function BTCPayStoreGeneral($id_store, $post) {
		#echo '<pre>'.print_r($post,true).'</pre>';
		#exit;
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);
		$BTCPay->setStoreName($post['store_denomination']);

		//aggiorno la denominazione dello store in caso sia stata modificata
		Settings::save(['store_denomination'=>$post['store_denomination']]);
		$stores->denomination = $post['store_denomination'];
		$stores->update();

		// creo l'object da inviare al server
		$object =(object) [
			'StoreWebsite' => $post['store_website'],
			'NetworkFeeMode' => $post['network_fee_mode'],
			'InvoiceExpiration' => $post['invoice_expiration'],
			'MonitoringExpiration' => $post['monitoring_expiration'],
			'PaymentTolerance' => $post['payment_tolerance'],
			'SpeedPolicy' => $post['speed_policy'],
		];
		#echo '<pre>'.print_r($object,true).'</pre>';
		#exit;
		$general = $BTCPay->general($object);

		// if ($general === false)
		// 	return false;

		return true;
	}
	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_store : id del nuovo store creato
	 * @param array $post : $_POST
	 */
	public function BTCPayStoreExchange($id_store, $post) {
		#echo '<pre>'.print_r($post,true).'</pre>';
		#exit;
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		// creo l'object da inviare al server
		$object =(object) [
			'PreferredExchange' => strtolower($post['preferred_exchange']),
			'Spread' => $post['spread'],
			'DefaultCurrencyPairs' => $post['default_currency_pairs'],
		];

		$exchange = $BTCPay->exchange($object);

		// if ($exchange === false)
		// 	return false;

		return true;
	}
	/*
	 * Questa funzione recupera la lista degli exchange del server
	*/
	public function getPreferredPriceSource($id_store){
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// carico gli exchanges gestiti dalla webapp
		$exchanges = Exchanges::model()->findAll();
		$listaExchanges = CHtml::listData( $exchanges, 'id_exchange' , 'denomination');

		// Imposto lo storeId prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		return $BTCPay->PreferredPriceSource($listaExchanges);
	}

	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_store : id del nuovo store creato
	 * @param array $post : $_POST
	 */
	public function BTCPayStoreMPK($id_store, $post) {
		// echo '<pre>'.print_r($post,true).'</pre>';
		// exit;
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		//imposto l'asset
		$BTCPay->setAssetName($post['asset']);

		//imposto il tipo di Address
		$BTCPay->setAddressType($post['AddressType']);

		// creo l'object da inviare al server
		$object =(object) [
			'DerivationScheme' => $post['DerivationScheme'],
		];

		$save_mpk = $BTCPay->saveMpk($object);

		if ($save_mpk === null){
			$return = false;
		}else{
			//aggiorno gli indirizzi generati dalla mpk
			$model=Mpk::model()->findByAttributes(['id_store'=>$stores->id_store,'asset'=>$post['asset']]);
			$model->Addresses = CJSON::encode($save_mpk);
			$model->update();
			$return = true;
		}
		return $return;
	}

	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_store : id del nuovo store creato
	 * @param array $post : $_POST
	 */
	public function BTCPayStoreCheckout($id_store, $post) {
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		foreach ($post as $key => $val){
			$makeObj[$key] = $val;
		}
		// creo l'object da inviare al server
		$object =(object) $makeObj;

		$checkout = $BTCPay->checkout($object);

		return true;
	}

	/*
	 * Questa funzione recupera la lista delle tabelle in checkout
	*/
	public function getDefaultsCheckout($id_store){
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		return $BTCPay->DefaultsCheckout();
	}

	/**
	 * Eichiede il pairingCode
	 * @param array $post : $_POST
	 */
	public function BTCPayNewToken($id_store) {
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		// parametri di ricerca con merchants 0, cioè administrator
		$stores = Stores::model()->findByPk($id_store);
		$settings = Settings::load();
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$settings->blockchainAddress]);
		// Effettuo il login
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);
		// end

		// Imposto lo storeId prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		//richiedo il pairing code
		return $BTCPay->newToken();
	}
}
