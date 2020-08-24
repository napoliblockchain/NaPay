<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class StoresController extends Controller
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
			// array('allow',  // allow all users to perform 'index' and 'view' actions
			// 	'actions'=>array('index','view'),
			// 	'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'index',
					'view',
					'create',
					'update',
					'delete',
					'ajaxSelectMerchantAddress',
					'showpassword', //mostra la password in chiaro per il collegamento a btcpay server

					'general', //impostazioni generali
					'exchange', //imposta exchange rates
					'checkout', // imposto il checkout experience
					'savempk', // imposta la Master Public Key
					'checkoutLogo', // carica il logo sul server
					'checkoutCss', // carica il logo sul server
			),
				'users'=>array('@'),
			),
			// array('allow', // allow admin user to perform 'admin' and 'delete' actions
			// 	'actions'=>array('admin','delete'),
			// 	'users'=>array('admin'),
			// ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionShowpassword(){
		//$id_merchant = crypt::Decrypt($_POST['id_merchant']);
		$id_merchant = $_POST['id_merchant'];
		$BpsUsers = BpsUsers::model()->findByAttributes(array("id_merchant"=>$id_merchant));
		if (null === $BpsUsers)
			$password = '';
		else
			$password = crypt::Decrypt($BpsUsers->bps_auth);

		echo CJSON::encode(array("password"=>$password));
	}
	/**
	 * Carica l'immagine del LOGO
	 * If Carica is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCheckoutLogo($id)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
		$model = new StoreForm;

		$storeSettings = Settings::loadStore(crypt::Decrypt($id));
		foreach ($storeSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		// echo '<pre>'.print_r($model->attributes,true).'</pre>';
		// exit;

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
							$save->WriteLog('napay','stores','CheckoutLogo', 'Store LOGO Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
							$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
						}
						$model->addError('CustomLogo', 'Cannot update Checkout LOGO');
						$save->WriteLog('napay','stores','CheckoutLogo', 'Error while setting Store LOGO Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
					}else{
						$model->CustomLogo = '';
					}
		        }else{
					$model->CustomLogo = '';
				}
			}
		}

		$this->render('checkoutLogo/update',array(
			'model'=>$model,
		));
	}
	/**
	 * Carica l'immagine del CSS
	 * If Carica is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCheckoutCss($id)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
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
							$save->WriteLog('napay','stores','CheckoutCss', 'Store CSS Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
							$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
						}
						$model->addError('CustomLogo', 'Cannot update Checkout CSS');
						$save->WriteLog('napay','stores','CheckoutCss', 'Error while setting Store CSS Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
					}else{
						$model->CustomCSS = '';
					}
		        }else{
					$model->CustomCSS = '';
				}
			}
		}

		$this->render('checkoutCss/update',array(
			'model'=>$model,
		));
	}


	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$stores = $this->loadModel(crypt::Decrypt($id));
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$userSettings = Settings::loadUser($merchants->id_user);

		$this->render('view',array(
			'model'=>$stores,
			'storeSettings'=>Settings::loadStore(crypt::Decrypt($id)),
			'userSettings'=>$userSettings,
		));
	}



	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionOLDView($id)
	{
		$criteria=new CDbCriteria();
		$criteria->compare('deleted',0,false);

		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 10){
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
		}

		$dataStores=new CActiveDataProvider('Stores', array(
			'criteria'=>$criteria,
		));
		$stores = $dataStores->getData();

		$dataPos=new CActiveDataProvider('Pos', array(
			'criteria'=>$criteria,
		));
		$pos = $dataPos->getData();

		$this->render('view',array(
			'model'=>$this->loadModel(crypt::Decrypt($id)),
			'pos'=>$pos,
			'stores'=>$stores,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Stores;
		if (Yii::app()->user->objUser['privilegi'] == 10){

			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));

			$model->id_merchant = $merchants->id_merchant;
			$model->address = $merchants->address;
			$model->city = $merchants->city;
			$model->county = $merchants->county;
			$model->cap = $merchants->cap;
			$model->vat = $merchants->vat;
		}else{
			$merchants = new Merchants;
		}

		if(isset($_POST['Stores']))
		{
			#echo "<pre>".print_r($_POST,true)."</pre>";
			#exit;
			$model->attributes=$_POST['Stores'];
			#echo "<pre>".print_r($model->attributes,true)."</pre>";
			#exit;

			if($model->save()){
				//creo lo store su BTCPay Server
				$this->createBTCPayStore($model->id_merchant,$model->id_store);
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_store)));
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'merchants'=>$merchants,
		));
	}

	/**
	 * Crea lo store anche su BTCPay Server
	 * @return : id_store_bps
	 * @param integer $id_merchant : id del commerciante
	 * @param integer $id_store : id del nuovo store creato
	 */
	public function createBTCPayStore($id_merchant, $id_store) {
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$merchants = Merchants::model()->findByPk($id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);
		$stores = Stores::model()->findByPk($id_store);

		// Effettuo il login con il nuovo user e creo lo store
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));

		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// creo lo store
		$storeId = $BTCPay->newStore($stores->denomination);

		//aggiorno lo store con il parametro appena ricevuto
		$stores->bps_storeid = $storeId;
		$stores->update();

		// aggiorno i settings dello stores
		Settings::saveStore($stores->id_store,['bps_storeid'=>$storeId]);
		Settings::saveStore($stores->id_store,['store_denomination'=>$stores->denomination]);

		return true;
	}


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Stores']))
		{
			$model->attributes=$_POST['Stores'];
			#$model->id_changer = Yii::app()->user->objUser['id_user']; //id utente collegato
			#$date = new DateTime();
			#$model->date_modify = $date->format('Y-m-d H:i:s');

			if($model->save())
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_store)));
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
		//$this->loadModel($id)->delete();
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
			//$merchants=Merchants::model()->findByAttributes(array('id_user'=>Yii::app()->user->objUser['id_user']));
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
		}

        $dataProvider=new CActiveDataProvider('Stores',array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'denomination'=>false
	    		)
	  		),
			'pagination'=>array('pageSize'=>20)
		));

		$dataPos=new CActiveDataProvider('Pos', array(
			'criteria'=>$criteria,
		));
		$pos = $dataPos->getData();

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'pos'=>$pos,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Stores('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Stores']))
			$model->attributes=$_GET['Stores'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Stores the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Stores::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Stores $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='stores-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
     * Carica gli Stores di un particolare Merchant
     */
    public function actionAjaxSelectMerchantAddress($id) {
		$merchants=Merchants::model()->findByPk($id);

		$response = [
			'address'=>$merchants->address,
			'cap'=>$merchants->cap,
			'city'=>$merchants->city,
			'county'=>$merchants->county,
			'vat'=>$merchants->vat,
			'provincia' =>ComuniItaliani::model()->findByPk($merchants->city)->sigla,
		];
		echo CJSON::encode($response);
    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionGeneral($id)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
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
					$save->WriteLog('napay','stores','General', 'Store General Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
				}
				$model->addError('store_denomination', 'Cannot update General Settings');
				$save->WriteLog('napay','stores','General', 'Error while setting Store General Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}
		}

		$this->render('general/update',array(
			'model'=>$model,
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

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);
		$BTCPay->setStoreName($post['store_denomination']);

		//aggiorno la denominazione dello store in caso sia stata modificata
		Settings::saveStore($stores->id_store,['store_denomination'=>$post['store_denomination']]);
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
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionExchange($id)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
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
					$save->WriteLog('napay','stores','Exchange', 'Store Exchange Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
				}
				$model->addError('preferred_exchange', 'Cannot update Exchange');
				$save->WriteLog('napay','stores','Exchange', 'Error while setting Store Exchange Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}

		}

		$this->render('exchange/update',array(
			'model'=>$model,
			'preferredPriceSource'=>$this->getPreferredPriceSource(crypt::Decrypt($id))
		));
	}

	/*
	 * Questa funzione recupera la lista degli exchange del server
	*/
	public function getPreferredPriceSource($id_store){
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// carico gli exchanges gestiti dalla webapp
		$exchanges = Exchanges::model()->findAll();
		$listaExchanges = CHtml::listData( $exchanges, 'id_exchange' , 'denomination');

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

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
	public function BTCPayStoreExchange($id_store, $post) {
		#echo '<pre>'.print_r($post,true).'</pre>';
		#exit;
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

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
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionSavempk($id,$asset)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
		$model = Mpk::model()->findByAttributes(['id_store'=>$store->id_store,'asset'=>$asset]);

		if (null === $model)
			$model = new Mpk;

		// echo '<pre>'.print_r($_GET,true).'</pre>';
		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;

		if(isset($_POST['Mpk']))
		{
			$model->attributes=$_POST['Mpk'];
			$model->id_store = $store->id_store;
			$model->Addresses = ''; //fix empty value
			if ($model->save()){
				//Settings::saveStore($store->id_store,$model->attributes);
				$mpk = $this->BTCPayStoreMPK($store->id_store,$_POST['Mpk']);
				if ($mpk == true){
					$save->WriteLog('napay','stores','Savempk', 'Store MPK Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
				}else{
					$model->addError('asset', 'Cannot update Master Public Key');
					$save->WriteLog('napay','stores','Savempk', 'Cannot update Master Public Key' );
				}
			}else{
				$model->addError('asset', 'Cannot update Master Public Key');
				$save->WriteLog('napay','stores','Savempk', 'Cannot update Master Public Key' );
			}

		}

		$this->render('savempk/update',array(
			'model'=>$model,
			'asset'=>$asset,
		));
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

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// echo '<pre>'.print_r($BTCPay->getBTCPayUrl(),true).'</pre>';
		// exit;

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
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionCheckout($id)
	{
		$save = new Save;
		$store = $this->loadModel(crypt::Decrypt($id));
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
					$save->WriteLog('napay','stores','Checkout', 'Store Checkout Settings ['.$store->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('view','id'=>crypt::Encrypt($store->id_store)));
				}
				$model->addError('CustomLogo', 'Cannot update Checkout');
				$save->WriteLog('napay','stores','Checkout', 'Error while setting Store Checkout Settings ['.$store->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}

		}
		$getDefaults = $this->getDefaultsCheckout(crypt::Decrypt($id));
		// echo "<pre>".print_r($getDefaults,true)."</pre>";
		// exit;

		$this->render('checkout/update',array(
			'model'=>$model,
			'DefaultPaymentMethod'=>(isset($getDefaults['DefaultPaymentMethod']) ? $getDefaults['DefaultPaymentMethod'] : [0=>'You must first setup your Master Public Key']),
			'DefaultLang'=>$getDefaults['DefaultLang'],
		));
	}
	/*
	 * Questa funzione recupera la lista delle tabelle in checkout
	*/
	public function getDefaultsCheckout($id_store){
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo storeId prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		return $BTCPay->DefaultsCheckout();
	}
	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_store : id del nuovo store creato
	 * @param array $post : $_POST
	 */
	public function BTCPayStoreCheckout($id_store, $post) {
		#echo '<pre>'.print_r($post,true).'</pre>';
		// exit;
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$stores = Stores::model()->findByPk($id_store);
		$merchants = Merchants::model()->findByPk($stores->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));

		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setStoreId($stores->bps_storeid);

		foreach ($post as $key => $val){
			$makeObj[$key] = $val;
		}
		// creo l'object da inviare al server
		$object =(object) $makeObj;

		// echo '<pre>'.print_r($object,true).'</pre>';
		// exit;



		$checkout = $BTCPay->checkout($object);

		// if ($checkout === false)
		// 	return false;

		return true;
	}
}
