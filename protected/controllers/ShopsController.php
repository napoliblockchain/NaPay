<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class ShopsController extends Controller
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
					'delete',
					'ajaxCreateSelectStore',

					'general', //impostazioni generali
					'products', // impostazioni prodotti
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
		$criteria = new CDbCriteria;
		$criteria->compare('id_shop',crypt::Decrypt($id));
		$products=new CActiveDataProvider('Products', array(
		    'criteria'=>$criteria,
		));


		$this->render('view',array(
			'model'=>$this->loadModel(crypt::Decrypt($id)),
			'shopSettings'=>Settings::loadShop(crypt::Decrypt($id)),
			'products'=>$products,
		));
	}

	// // questa funzione genera i prodotti nel db dal template standard di btcpayserver
	// public function generateStandardProducts($template,$id_shop){
	// 	// innanzitutto cerco di suddividere le linee in base ai 'EOF'
	// 	$text = trim($template);
	// 	$lines = explode("\n", $text);
	// 	$lines = array_filter($lines, 'trim'); // remove any extra \r characters left behind
	//
	// 	// echo "<pre>".print_r($lines,true)."</pre>";
	// 	// exit;
	//
	// 	// adesso analizzo ogni singola linea in base ai ":"
	// 	// quella che non ha valori è la prima linea
	// 	$products = [];
	// 	$field_name = '';
	// 	foreach ($lines as $line){
	// 		$right = null;
	// 		$e = explode(":",$line);
	//
	// 		$left = trim($e[0]);
	//
	// 		// se ci sono ulteriori ':' li concatena
	// 		for ($x=1; $x < count($e); $x++)
	// 		   $right[] = trim($e[$x]);
	//
	// 	   	if (empty($right[0])){
	// 		   $field_name = str_replace(" ","_",$left);
	// 	   	}else{
	// 		   $value = '';
	// 		   foreach ($right as $r)
	// 			   $value .= $r;
	//
	// 			// fix ':' concatenati
	// 		   	$products[$field_name][$left] = str_replace("https//","https://",$value);
	// 	    }
	//     }
	//
	// 	// echo "<pre>".print_r($products,true)."</pre>";
	// 	// exit;
	// 	$shop = Shops::model()->findByPk($id_shop);
	//
	// 	$success = true;
	// 	foreach ($products as $key => $product){
	// 		$model = new Products;
	// 		$model->filename = $product['image'];
	// 		$model->title = $product['title'];
	// 		$model->price = $product['price'];
	// 		$model->description = $product['description'];
	// 		$model->id_shop = $shop->id_shop;
	// 		$model->id_store = $shop->id_store;
	// 		$model->id_merchant = $shop->id_merchant;
	// 		if ($model->insert())
	// 			$success = true;
	// 		else
	// 			$success = false;
	// 	}
	// 	return $success;
	// }

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Shops;
		if(isset($_POST['Shops']))
		{
			// echo '<pre>'.print_r($_POST,true).'</pre>';
			$model->attributes=$_POST['Shops'];

			// SE NON C'è ID_MERCHANT VUOL DIRE CHE NON SONO ADMIN e quindi inserisco l'id di chi è collegato
			if (!isset($_POST['Shops']['id_merchant'])){
				$merchants=Merchants::model()->findByAttributes(array(
					'id_user'=>Yii::app()->user->objUser['id_user'],
					'deleted'=>0,
				));
				$model->id_merchant = $merchants->id_merchant; //id del commerciante
			}
			// echo '<pre>'.print_r($model->attributes,true).'</pre>';
			// exit;
			if($model->save()){
				//creo la app shop su BTCPay Server
				$this->createBTCPayShop($model->id_shop);
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_shop)));

			}

		}

		$this->render('create',array(
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
		$model = Shops::model()->findByPk(crypt::Decrypt($id));
		$model->delete();

		// cancello anche tutti gli shopsettings
		SettingsShops::model()->deleteAllByAttributes(['id_shop'=>crypt::Decrypt($id)]);

		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$merchants = Merchants::model()->findByPk($model->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$model->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);
		$stores = Stores::model()->findByPk($model->id_store);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo shopId prima di chiamare la funzione
		$BTCPay->setShopId($model->bps_shopid);

		// elimino lo shop da btcpayserver
		// eseguo il salvataggio del solo template
		$result = $BTCPay->ShopDelete();

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
		$dataProvider=new CActiveDataProvider('Shops', array(
		    'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'denomination'=>false
	    		)
	  		),
			'pagination'=>array('pageSize'=>20)
		));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}



	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Shops the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Shops::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
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
	 * Crea una app Shop su BTCPay Server
	 * @return : bps_shopid
	 * @param integer $id_merchant : id del commerciante
	 * @param integer $id_shop : id della nuova app shop creata
	 */
	public function createBTCPayShop($id_shop) {
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$shops = Shops::model()->findByPk($id_shop);
		$merchants = Merchants::model()->findByPk($shops->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$shops->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);
		$stores = Stores::model()->findByPk($shops->id_store);

		// Effettuo il login con il nuovo user e creo lo store
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));

		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// creo lo store
		$appId = $BTCPay->newShop($stores->bps_storeid,$shops->denomination);

		// recupero il template che invio ad una funzione che mi genera i prodotti
		// nella tabella np_products
		//$template = $BTCPay->getNewTemplate();
		//$this->generateStandardProducts('',$id_shop);

		//aggiorno lo store con il parametro appena ricevuto
		$shops->bps_shopid = $appId;
		$shops->update();

		return true;
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionGeneral($id)
	{
		$save = new Save;
		$shop = $this->loadModel(crypt::Decrypt($id));
		$model = new ShopForm;

		$shopSettings = Settings::loadShop(crypt::Decrypt($id));
		foreach ($shopSettings as $key => $value)
			if (array_key_exists($key, $model->attributes))
				$model->$key = $value;

		if(isset($_POST['ShopForm']))
		{
			foreach ($_POST['ShopForm'] as $key => $value)
				$model->$key = $value;

			// <!-- Hidden fields -->
			// per distinguere test da produzione...
			if (gethostname() == 'blockchain1'){
				$URLIpn = 'https://napay.blockchain-napoli.tk'.Yii::app()->createUrl('ipn/shop',array('id_shop'=>$shop->id_shop));
			}elseif (gethostname()=='CGF6135T' || gethostname()=='NUNZIA'){ // SERVE PER LE PROVE IN UFFICIO
				$URLIpn = 'https://'.$_SERVER['HTTP_HOST'].Yii::app()->createUrl('ipn/shop',array('id_shop'=>$shop->id_shop));
			}else{
				$URLIpn = 'https://napay.napoliblockchain.it'.Yii::app()->createUrl('ipn/shop',array('id_shop'=>$shop->id_shop));
			}

			$model->Currency = 'EUR';
			$model->CustomCSSLink = '';
			$model->NotificationUrl = $URLIpn;
			$model->NotificationEmail = '';
			$model->RedirectAutomatically = 'false';
			$model->Description = '';
			$model->files = '';
			$model->EmbeddedCSS = '';
			$model->NotificationEmailWarning = 'True';
			$model->EnableShoppingCart = 'true';

			if ($model->validate()){
				Settings::saveShop($shop->id_shop,$model->attributes);
				if ($this->BTCPayShopGeneral(crypt::Decrypt($id),$model->attributes) == true){
					$save->WriteLog('napay','shops','General', 'Shop General Settings ['.$shop->denomination.'] set by '.Yii::app()->user->objUser['email'] );
					$this->redirect(array('view','id'=>crypt::Encrypt($shop->id_shop)));
				}
				$model->addError('Title', 'Cannot update General Settings');
				$save->WriteLog('napay','shops','General', 'Error while setting Shop General Settings ['.$shop->denomination.'] by '.Yii::app()->user->objUser['email'] );
			}
		}

		$this->render('general/update',array(
			'model'=>$model,
		));
	}

	/**
	 * Aggiorna lo store
	 * @return : id_store_bps
	 * @param integer $id_shop : id del nuovo shop creato
	 * @param array $post : $_POST
	 */
	public function BTCPayShopGeneral($id_shop, $attributes) {
		// echo '<pre>'.print_r($post,true).'</pre>';
		// exit;
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		$shop = Shops::model()->findByPk($id_shop);
		$stores = Stores::model()->findByPk($shop->id_store);
		$merchants = Merchants::model()->findByPk($shop->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$stores->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo storeId e il Name, prima di chiamare la funzione
		$BTCPay->setShopId($shop->bps_shopid);

		$attributes['StoreId'] = $stores->bps_storeid;

		// creo l'object da inviare al server
		$object =(object) $attributes;
		// echo '<pre>'.print_r($object,true).'</pre>';
		// exit;
		$general = $BTCPay->ShopGeneral($object);

		// if ($general === false)
		// 	return false;

		return true;
	}
}
