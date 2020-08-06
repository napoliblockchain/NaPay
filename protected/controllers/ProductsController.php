<?php

class ProductsController extends Controller
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
			// 'postOnly + delete', // we only allow deletion via POST request
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
					//'index',
					//'view',
					'create',
					'update',
					'delete', // cancella un prodotto e ricostruisxce il template
					//'ajaxSelectCategories',
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function generateTemplate($id_shop){
		// carico tutti i prodotti
		$criteria = new CDbCriteria;
		$criteria->compare('id_shop',$id_shop,false);
		$products = Products::model()->findAll($criteria);

			 // echo "<pre>".print_r($products,true)."</pre>";
			 // exit;

		$templates = [];
		foreach ($products as $key =>  $product){
			$templates[$product->title] = [
				'image' => $product->filename,
				'title' => $product->title,
				'price' => $product->price,
				'description' => $product->description
			];
		}
		// echo "<pre>".print_r($templates,true)."</pre>";
		// exit;
		$text = '';
		foreach ($templates as $item){
			$text .= $item['title'].": ".PHP_EOL;
			$text .= '  price: '.$item['price'].PHP_EOL;
			$text .= '  title: '.$item['title'].PHP_EOL;
			$text .= '  description: '.$item['description'].PHP_EOL;
			$text .= '  image: '.$item['image'].PHP_EOL;
			$text .= '  custom: false'.PHP_EOL;
			$text .= PHP_EOL;
		}
		// echo "<pre>".print_r($text,true)."</pre>";
		// exit;

		// una volta che ho il template, mi collego a btcpayserver
		// e lo carico su
		require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';

		$shop = Shops::model()->findByPk($id_shop);
		$merchants = Merchants::model()->findByPk($shop->id_merchant);
		$BpsUsers = BpsUsers::model()->findByAttributes(array('id_merchant'=>$shop->id_merchant));
		$users = Users::model()->findByPk($merchants->id_user);
		$stores = Stores::model()->findByPk($shop->id_store);

		// Effettuo il login
		$BTCPay = new BTCPay($users->email,crypt::Decrypt($BpsUsers->bps_auth));
		// imposto l'url
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Imposto lo shopId prima di chiamare la funzione
		$BTCPay->setShopId($shop->bps_shopid);

		// eseguo il salvataggio del solo template
		$result = $BTCPay->ShopTemplate($text);

		return $result;
	}




	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($id_shop)
	{
		$model=new Products;


		if(isset($_POST['Products']) && isset($_FILES['Products']))
		{
			$model->attributes = $_POST['Products'];

			$shop=Shops::model()->findByPk(crypt::Decrypt($id_shop));
			$model->id_merchant = $shop->id_merchant;
			$model->id_store = $shop->id_store;
			$model->id_shop = $shop->id_shop;
			// echo '<pre>'.print_r($model->attributes,true).'</pre>';
			// exit;

			if($_FILES['Products']['error']['filename']==0){
				if($_FILES['Products']['size']['filename'] < 3000000){ //< 3Mb

					$path = Yii::app()->basePath . '/../custom/products/shop-' . crypt::Decrypt($id_shop) . "/" . $_FILES['Products']['name']['filename'];
					if (gethostname() == 'blockchain1'){
						$host = 'https://napay.napoliblockchain.tk';
					}elseif (gethostname()=='CGF6135T' || gethostname()=='NUNZIA'){ // SERVE PER LE PROVE IN UFFICIO
						$host = 'https://'.$_SERVER['HTTP_HOST'].'/napay';
					}else{
						$host = 'https://napay.napoliblockchain.it';
					}
					$wwwpath = $host.'/custom/products/shop-' . crypt::Decrypt($id_shop) . "/" . $_FILES['Products']['name']['filename'];
					$model->filename = $wwwpath;
					// $model->filename = basename($path);
				}else{
					$model->filename = '';
				}
	        }else{
				$model->filename = '';
			}
			//$model->image = $model->product_filename;
			//  echo '<pre>'.print_r(Yii::app()->basePath,true).'</pre>';
			// exit;
	        if ($model->validate() && $model->save()) {
				//se non esiste la cartella dell'utente viene creata
				if (!file_exists(Yii::app()->basePath . '/../custom/products/shop-' . crypt::Decrypt($id_shop) . "/")) {
				    mkdir(Yii::app()->basePath . '/../custom/products/shop-' . crypt::Decrypt($id_shop) . "/", 0755, true);
				}
	            move_uploaded_file($_FILES['Products']['tmp_name']['filename'], $path);

				// genero il nuovo template con il nuovo prodotto aggiunto
				$this->generateTemplate(crypt::Decrypt($id_shop));

	            // redirect to success page
				$this->redirect(array('shops/view','id'=>$id_shop));
	        }
		}

		$this->render('create',array(
			'model'=>$model,
			//'stores'=>$stores,
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
		#echo '<pre>'.print_r($_FILES,true).'</pre>';
		#echo '<pre>'.print_r($_POST,true).'</pre>';
		#exit;


		if(isset($_POST['Products']))
		{
			$model->attributes=$_POST['Products'];

			if($_FILES['Products']['error']['filename']==0){
				if($_FILES['Products']['size']['filename'] < 3000000){ //< 3Mb
					// $path = Yii::app()->basePath . '/../products/shop-' . $model->id_shop . "/" . $_FILES['Products']['name']['filename'];
					// $model->filename = basename($path);
					$path = Yii::app()->basePath . '/../custom/products/shop-' . $model->id_shop . "/" . $_FILES['Products']['name']['filename'];
					if (gethostname() == 'blockchain1'){
						$host = 'https://napay.napoliblockchain.tk';
					}elseif (gethostname()=='CGF6135T' || gethostname()=='NUNZIA'){ // SERVE PER LE PROVE IN UFFICIO
						$host = 'https://'.$_SERVER['HTTP_HOST'].'/napay';
					}else{
						$host = 'https://napay.napoliblockchain.it';
					}
					$wwwpath = $host.'/custom/products/shop-' . $model->id_shop . "/" . $_FILES['Products']['name']['filename'];
					$model->filename = $wwwpath;
				}else{
					$model->filename = '';
				}
	        }else{
				$model->filename = '';
			}

			if ($model->validate() && $model->save()) {
				//se non esiste la cartella dell'utente viene creata
				if (!file_exists(Yii::app()->basePath . '/../custom/products/shop-' . $model->id_shop . "/")) {
				    mkdir(Yii::app()->basePath . '/../custom/products/shop-' . $model->id_shop . "/", 0755, true);
				}
	            move_uploaded_file($_FILES['Products']['tmp_name']['filename'], $path);

				// genero il nuovo template con il nuovo prodotto modificato
				$this->generateTemplate(crypt::Decrypt($id_shop));

	            // redirect to success page
				$this->redirect(array('shops/view','id'=>crypt::Encrypt($model->id_shop)));
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
		// echo '<pre>'.print_r(crypt::Decrypt($id),true).'</pre>';
		// exit;
		//$this->loadModel(crypt::Decrypt($id))->delete();
		$model = $this->loadModel(crypt::Decrypt($id));
		$file = $model->filename;
		if (file_exists($file))
			unlink ($file);

		$model->delete();

		// prima cancell, poi genero il nuovo template
		// con il prodotto eliminato
		$this->generateTemplate($model->id_shop);

		$this->redirect(array('shops/view','id'=>crypt::Encrypt($model->id_shop)));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$criteria=new CDbCriteria();
		if (Yii::app()->user->objUser['privilegi'] == 10){
			$merchants = Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>0,
			));
			$criteria->compare('id_merchant',$merchants->id_merchant,false);
			$stores = Stores::model()->findAll($criteria);
		}else{
			$stores = Stores::model()->findAll();
		}

		$dataProvider=new CActiveDataProvider('Products', array(
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
		$model=Products::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

}
