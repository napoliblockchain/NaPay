<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');

class MerchantsController extends Controller
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
				'actions'=>array('index','view','create','update','delete',
				// 'config' // configurazione del wallet bitcoin
			),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	// public function actionConfig($id)
	// {
	// 	#echo "<pre>".print_r($_POST,true)."</pre>";
	// 	#exit;
	// 	//$id è il valore criptato dell'id del merchant
	// 	//cerco l'id_user del merchant
	// 	$merchants = Merchants::model()->findByPk(crypt::Decrypt($id));
	//
	// 	$model = DockerWallets::model()->findByAttributes(array('id_user'=>$merchants->id_user));
	// 	if (null === $model || $model->configured == 0)
	// 		$model = new DockerWallets;
	//
	// 	$model->id_user = $merchants->id_user;
	// 	if(isset($_POST['DockerWallets']))
	// 	{
	// 				$model->attributes=$_POST['DockerWallets'];
	// 				$model->id_user = $merchants->id_user;
	// 				$model->save();
	// 	}
	//
	// 		$this->render('dkWallet/config',array('model'=>$model,'merchants'=>$merchants));
	// }


	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$model = $this->loadModel(crypt::Decrypt($id));

		$this->render('view',array(
			'model'=>$model,
			'userSettings'=>Settings::loadUser($model->id_user),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$settings=new SettingsUserForm;
		$model=new Merchants;

		if (!isset($_GET['id'])){
			$criteria=new CDbCriteria();
		 	$criteria->compare('id_users_type',5,false);// carico solo gli utenti 'SOCI'. Gli altri o sono amministratori o già commercianti ...
			$users=new CActiveDataProvider('Users', array(
				'criteria'=>$criteria,
			));
			//onde evitare errori nel form
			if (null === $users)
				$users = new Users;
		}else{
			$merchants = Merchants::model()->findByAttributes(array('id_user'=>Yii::app()->user->objUser['id_user'],'deleted'=>0));
			if ($merchants !== null)
				$this->redirect(array('view','id'=>crypt::Encrypt($merchants->id_merchant)));

			$users=Users::model()->findByPk(Yii::app()->user->objUser['id_user']);

			if(!isset($_POST['Merchants'])){
				$model->denomination = $users->denomination;
				$model->vat = $users->vat;
				$model->address = $users->address;
				$model->city = $users->city;
				$model->county = $users->country;
				$model->cap = $users->cap;
			}
		}

		if(isset($_POST['Merchants']))
		{
			// echo "<pre>".print_r($_POST,true)."</pre>";
			// exit;
			$model->attributes=$_POST['Merchants'];

			//$settings->attributes = $_POST['SettingsUserForm'];
			$settings->id_user = $model->id_user;
			$settings->id_exchange = 1; // inizializzo a btcpaysevrer

			if($settings->validate()){
				if($model->save()){// se salvo il merchant, allora...
					Settings::saveUser($model->id_user,$settings->attributes,['id_exchange']);

					//imposto il tipo utente da SOCIO a COMMERCIANTE
					$user = Users::model()->findByPk($model->id_user);

					$user->id_users_type = 2; // diventi commerciante
					$user->corporate = 1; // diventi corporate
					$user->save();

					//CREO anche lo USER su BTCPay Server
					WebApp::createBTCServerUser($model->id_merchant,$user->email);

					//invio eventualmente la mail
					if (isset($_POST['Merchants']['send_mail']))
						NMail::SendMail('merchants',crypt::Encrypt($model->id_user),Users::model()->findByPk($model->id_user)->email,'','');
					//ritorno alla visualizzazione
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_merchant)));
				}
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
			'settings'=>$settings,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		//caricamenti singoli
		$model=$this->loadModel(crypt::Decrypt($id));
		$settings = new SettingsUserForm;
		$settingsLoaded = Settings::loadUser($model->id_user);

		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;
		$settings->attributes = (array)$settingsLoaded;


		$criteriaUser=new CDbCriteria();
  	 	$criteriaUser->compare('id_users_type',5,true);// carico solo gli utenti 'SOCI'. Gli altri o sono amministratori o già commercianti ...
  		$users=new CActiveDataProvider('Users', array(
  			'criteria'=>$criteriaUser,
  		));

		//onde evitare errori nel form
  		if (null === $users)
  			$users = new Users;


		if(isset($_POST['Merchants']))
		{
			$model->attributes=$_POST['Merchants'];
			$settings->attributes = $_POST['SettingsUserForm'];
			$settings->blockchainAsset = CJSON::encode($_POST['blockchainAsset']);

			Settings::saveUser($model->id_user,$settings->attributes);
			if($model->update())
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_merchant)));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'settings'=>$settings,
			'preferredCoinList'=>WebApp::getPreferredCoinList(),
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		//NON CANCELLO MA IMPONGO UNO STATO AD 1
		$merchants = $this->loadModel(crypt::Decrypt($id));
		$merchants->deleted = 1;
		$merchants->save();

		$users = Users::model()->findByPk($merchants->id_user);
		$users->id_users_type = 5; // ritorna socio semplice!
		$users->save();

		// i settings restano !! NON VANNO CANCELLATI PER SALVAGUARDARE IL timestamp delle approvazioni
		//$settings = Settings::model()->findByAttributes(array('id_user'=>$merchants->id_user));

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

		if (Yii::app()->user->objUser['privilegi'] == 5){
			$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}

		$dataProvider=new CActiveDataProvider('Merchants',array(
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
	 * @return Merchants the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Merchants::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Merchants $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='merchants-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}
