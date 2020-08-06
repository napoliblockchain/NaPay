<?php

class InstitutesController extends Controller
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
				'actions'=>array('index','view','create','update','delete'),
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
		$model=$this->loadModel(crypt::Decrypt($id));
		$this->render('view',array(
			'model'=>$model,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Institutes;
		$user=new Users;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;

		if(isset($_POST['Institutes']))
		{
			$model->attributes=$_POST['Institutes'];
			$model->wallet_address = '0x0000000000000000000000000000000000000000';

			if($model->validate()){
				$user->attributes=$_POST['Users'];
				// generare le chiavi di ingresso per il nuovo utente Istituto
				//
				$savedPassword = $user->password;

				$user->id_users_type = 6; // Istituti
				$user->id_carica = 5; // Socio ordinario
				// $user->email = $user->email;
				$user->password = CPasswordHelper::hashPassword($user->password);

				$user->corporate = 1; // è una figura giuridica
				$user->denomination = $model->description;
				$user->activation_code = 0;
				$user->status_activation_code = 1;

				// temporaneamente 0. Poi da richiedere in tabella np_institutes
				$user->name = $user->email;
				$user->surname = $user->email;
				$user->vat = '000';
				$user->address = '000';
				$user->cap = '000';
				$user->city = '1';
				$user->country = '1';

				// echo '<pre>'.print_r($user,true).'</pre>';
				// exit;

				if ($user->save()){
						$model->id_user = $user->id_user;
						$model->save();

						// salvo il merchant
						$merchants = new Merchants;
						$merchants->id_user = $user->id_user;
						$merchants->denomination = $user->denomination;
						$merchants->vat = $user->vat;
						$merchants->address = $user->address;
						$merchants->city = $user->city;
						$merchants->county = $user->country;
						$merchants->cap = $user->cap;
						$merchants->deleted = 0;
						if (!($merchants->save())){
							//echo "<pre>".print_r($merchants->attributes,true)."</pre>";
							echo "Impossibile creare account commerciante!";
							exit;
						}
						
						$timestamp = time();

						//Salvo i timestamp dei consensi al trattamento dei dati e statuto
						$array['timestamp_consenso_statuto'] = $timestamp;
						$array['timestamp_consenso_privacy'] = $timestamp;
						$array['timestamp_consenso_termini'] = $timestamp;
						$array['timestamp_consenso_marketing'] = 0;
						$array['timestamp_consenso_pos'] = $timestamp;
						$array['numero_mail_approvazione'] = 0;
						$array['telefono'] = 0;

						// salva i dati dell'user
						Settings::saveUser($user->id_user,$array); //creo il numero di mail inviate per approve/disclaim iscrizione
						//Invio mail all'user
						NMail::SendMail('iscrizioneInstitutes',crypt::Encrypt($user->id_user),$user->email,$savedPassword,$user->activation_code);

						$this->redirect(array('view','id'=>crypt::Encrypt($model->id_institute)));
				}

			}
		}

		$this->render('create',array(
			'model'=>$model,
			'user'=>$user,
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

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Institutes']))
		{
			$model->attributes=$_POST['Institutes'];
			if($model->save())
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_institute)));
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
		// DELETE DEVE rendere inattive le credenziali dell'Istituto, ma salvaguardarne
		// lo storico


		//$this->loadModel(crypt::Decrypt($id))->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		// if(!isset($_GET['ajax']))
		// 	$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Institutes');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Institutes the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Institutes::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}


}
