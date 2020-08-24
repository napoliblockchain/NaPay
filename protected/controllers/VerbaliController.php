<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');

class VerbaliController extends Controller
{
	public function init()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect(Yii::app()->homeUrl);
		}
	}

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('index','view','create','update','delete','download'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionDownload($id){
		$model = $this->loadModel(crypt::Decrypt($id));
		if (null !== $model){
			$path = Yii::app()->basePath . '/verbali/';
			$filename = $path .$model->url_verbale;

			header("Content-type:application/pdf"); //for pdf file
			//header('Content-Type:text/plain; charset=ISO-8859-15');
			//if you want to read text file using text/plain header
			header('Content-Disposition: attachment; filename="'.basename($filename).'"');
			header('Content-Length: ' . filesize($filename));

			readfile($filename);

			Yii::app()->end();
		}
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
		$model=new Verbali;

		#echo '<pre>'.print_r($_FILES,true).'</pre>';
		#echo '<pre>'.print_r($_POST,true).'</pre>';
		#exit;

		if(isset($_POST['Verbali']) && isset($_FILES['Verbali']))
		{
			$model->attributes=$_POST['Verbali'];
			#echo '<pre>'.print_r($model->attributes,true).'</pre>';

			if($_FILES['Verbali']['error']['tempfile']==0){
				if($_FILES['Verbali']['size']['tempfile'] < 3000000){ //< 3Mb
					$path = Yii::app()->basePath . '/verbali/';
					// verifico la collisione del filename
					while (true){
						$filename = $path . md5(Utils::passwordGenerator()) . '.pdf';
						if (!file_exists($filename))
							break;
					}
					$model->url_verbale = basename($filename);
				}else{
					$model->url_verbale = null;
				}
	        }else{
				$model->url_verbale = null;
			}
			#echo '<pre>'.print_r($model->attributes,true).'</pre>';
			#exit;
			if($model->validate()){
				$model->data_verbale = date('Y/m/d', strtotime(str_replace('/', '-',($model->data_verbale))));
				if($model->insert()){
					// salvo il file
		            move_uploaded_file($_FILES['Verbali']['tmp_name']['tempfile'], $filename);

					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_verbali)));
				}

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

		#echo '<pre>'.print_r($_FILES,true).'</pre>';
		#echo '<pre>'.print_r($_POST,true).'</pre>';
		#exit;

		if(isset($_POST['Verbali']))
		{
			$oldurl = $model->url_verbale;
			$model->attributes=$_POST['Verbali'];
			$model->url_verbale = $oldurl;

			if($_FILES['Verbali']['error']['tempfile']==0){
				if($_FILES['Verbali']['size']['tempfile'] < 3000000){ //< 3Mb
					$path = Yii::app()->basePath . '/verbali/';
					$filename = $path . $oldurl;

					$model->url_verbale = basename($filename);
				}else{
					$model->url_verbale = null;
				}
	        }else{
				$model->url_verbale = null;
			}
			#echo '<pre>'.print_r($model->attributes,true).'</pre>';
			#exit;
			if($model->validate()){
				$model->data_verbale = date('Y/m/d', strtotime(str_replace('/', '-',($model->data_verbale))));
				if($model->update()){
					// salvo il file
		            move_uploaded_file($_FILES['Verbali']['tmp_name']['tempfile'], $filename);

					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_verbali)));
				}

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
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Verbali', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'data_verbale'=>true
	    		)
	  		),
		));
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Verbali::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='verbali-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
