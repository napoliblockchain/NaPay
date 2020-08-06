<?php

class CategoriesController extends Controller
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
		$model=new ProductsCategories;

		if (Yii::app()->user->objUser['privilegi'] == 10){
			$criteriaStores=new CDbCriteria();
			$merchants = Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>0,
			));
			$criteriaStores->compare('id_merchant',$merchants->id_merchant,false);
			$stores = Stores::model()->findAll($criteriaStores);
		}else{
			$stores = Stores::model()->findAll();
		}

		#echo '<pre>'.print_r($_POST,true).'</pre>';
		#exit;

		if(isset($_POST['ProductsCategories']))
		{
			$model->attributes = $_POST['ProductsCategories'];
			#echo '<pre>'.print_r($model->attributes,true).'</pre>';

			$findstores=Stores::model()->findByPk($model->id_store);
			$findmerchants=Merchants::model()->findByPk($findstores->id_merchant);
			$model->id_merchant = $findmerchants->id_merchant;
		#	echo '<pre>'.print_r($model->attributes,true).'</pre>';
		#	exit;
	    	if ($model->save()) {
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_category)));
	    	}
		}

		$this->render('create',array(
			'model'=>$model,
			'stores'=>$stores,
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
		if (Yii::app()->user->objUser['privilegi'] == 10){
			$merchants = Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>0,
			));
			$stores = Stores::model()->findAll(array('id_merchants'=>$merchants->id_merchant));
		}else{
			$stores = Stores::model()->findAll();
		}

		if(isset($_POST['ProductsCategories']))
		{
			$model->attributes=$_POST['ProductsCategories'];
			if($model->save())
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_category)));
		}

		$this->render('update',array(
			'model'=>$model,
			'stores'=>$stores,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model = $this->loadModel(crypt::Decrypt($id))->delete();


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
		if (Yii::app()->user->objUser['privilegi'] == 10){

			$criteriaStores=new CDbCriteria();
			$merchants = Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>0,
			));
			$criteria->compare('id_merchant',$merchants->id_merchant,false);
			$criteriaStores->compare('id_merchant',$merchants->id_merchant,false);
			$stores = Stores::model()->findAll($criteriaStores);
		}else{
			$stores = Stores::model()->findAll();
		}

		$dataProvider=new CActiveDataProvider('ProductsCategories', array(
			'criteria'=>$criteria,
		));

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'stores'=>$stores,
		));
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
		$model=ProductsCategories::model()->findByPk($id);
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
}
