<?php

class MailingController extends Controller
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
				'actions'=>array('index','ajaxValidation','send'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		$model = new MailingForm;

		$users = Users::model()->findAll();
		$list = array();

		// per ciascun utente
		foreach($users as $item) {
			$list[] = crypt::Encrypt($item->id_user);
		}

		$this->render('index',array(
			'model'=>$model,
			'list'=>CJSON::Encode($list)
		));
	}

	// invia la mail a tutti gli iscritti
	public function actionSend($id)
	{
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		sleep(1);
		$users=Users::model()->findByPk(crypt::Decrypt($id));
		NMail::SendMail('mailings',$id,$users->email,$_POST);
 		$return['text'] = 'Mail inviata con successo!';
 		echo CJSON::encode($return);
	}

	// valida i campi via ajax
	public function actionAjaxValidation()
	{
		$model = new MailingForm;
		$model->attributes = $_POST;

		$result = CJSON::Decode($this->performAjaxValidation($model));
		$success = (empty($result)) ? true : false;

		echo CJSON::Encode(['success'=>$success,'result'=>$result]);
	}

	/**
	 * Performs the AJAX validation.
	 * @param Mailings $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		return CActiveForm::validate($model);
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Users the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Users::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}


}
