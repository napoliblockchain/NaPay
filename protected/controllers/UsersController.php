<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels'); // viene prima di Save
Yii::import('libs.NaPacks.Save');
Yii::import('libs.NaPacks.Push');

class UsersController extends Controller
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
					'index', // mostra elenco soci
					'view', //visualizza dettagli socio
					'create', //crea manualmente un socio
					'update', //modifica socio
					'delete', //elimina socio
					'changepwd', //fa il cambio della password
					'resetpwd', //resetta la password del socio (admin)
					'2fa', //abilita il 2fa
					'2faRemove', //rimuove il 2fa
					'print', //stampa lista soci
					'export',//exporta in excel lista soci
					'approve', //(admin) approva o esclude dall'iscrizione il nuovo socio
					'sollecito', //(admin) invia un SINGOLO sollecito per il pagamento della quota al SINGOLO USER
					'reminder', //(admin) Gestione lista invio di un promemoria per l'avvicinarsi della scadenza o scaduti da meno di 1 anno
					'saveSubscription', //salva lo sottoscrizinoe dell'user per le notifiche push
					'consensus', // modifica il consenso marketing dell'utente salvandone lo storico in tabella np_consensus
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	* Modifica il consenso e lo salva nello storico
	*/
	public function actionConsensus()
	{
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;
		$id = crypt::Decrypt($_POST['id']);
		$userSettings = Settings::loadUser($id);
		$consenso = new Consensus;
		$consenso->id_user = $id;
		$consenso->type = $_POST['type'];
		$consenso->timestamp = time();

		if (isset($userSettings->timestamp_consenso_marketing) && $userSettings->timestamp_consenso_marketing <> 0) {
			$consenso->type_operation = 0;
			$userSettings->timestamp_consenso_marketing = 0;
			$return['text'] = 'Consenso rimosso.';
		}else {
			$consenso->type_operation = 1;
			$userSettings->timestamp_consenso_marketing = time();
			$return['text'] = 'Consenso confermato.';
		}
		$attributes = (array) $userSettings;

		$return['success'] = false;
		if ($consenso->insert()){
			$return['success'] = true;
			Settings::saveUser($id, $attributes);
			$user = $this->loadModel($id);
			NMail::SendMail('consensus',$_POST['id'],$user->email);
		}
	 	echo CJSON::encode($return);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$userSettings = Settings::loadUser(crypt::Decrypt($id));

		$this->render('view',array(
			'model'=>$this->loadModel(crypt::Decrypt($id)),
			'userSettings'=>$userSettings,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Users;
		// echo "<pre>".print_r($_POST,true)."</pre>";
		// exit;

		if(isset($_POST['Users']))
		{
			$model->attributes=$_POST['Users'];
			// echo "<pre>".print_r($model->attributes,true)."</pre>";
			// exit;

			// issue #41
			// Quando creo utenti con carica Presidente, Vice Presidente, Tesoriere, Segretario, questi devono
			// essere Amministratori, inoltre non possono essere commercianti.
			// quindi:

			if ($model->id_carica <=4)
				$model->id_users_type = 3; // AMMINISTRATORE
			else{
				$model->id_users_type = 5; // (SOCIO)
			}


			$savedModel = $_POST['Users'];
			$savedPassword = $model->password;
			$model->password = CPasswordHelper::hashPassword($model->password);

			//if ($_POST['Users']['send_mail'] == 1){
				$model->activation_code = md5($model->password);
			//	$model->status_activation_code = 0;
			//}else{
				//l'utente nasce già attivo se non c'è il flag alla mail
			//	$model->activation_code = 0;
				$model->status_activation_code = 0;
			//}
			//echo "<pre>".print_r($_POST,true)."</pre>";
			// echo "<pre>".print_r($model->attributes,true)."</pre>";
			// exit;

			if($model->save()){
				if ($_POST['Users']['send_mail'] == 1){
					NMail::SendMail('users',crypt::Encrypt($model->id_user),$model->email,$savedPassword,$model->activation_code);
				}
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_user)));
			}
		}

		$this->render('create',array(
			'model'=>$model,
			//'userSettings'=>$userSettings,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionChangepwd($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		#echo "<pre>".print_r($_POST,true)."</pre>";
		//exit;

		if(isset($_POST['Users']))
		{
			#echo "<pre>".print_r($model->attributes,true)."</pre>";
			#exit;
			if(CPasswordHelper::verifyPassword($_POST['Users']['password'],$model->password)){
				if ($_POST['Users']['new_password'] == $_POST['Users']['new_password_confirm']
					&& !empty($_POST['Users']['new_password'])
					&& !empty($_POST['Users']['new_password_confirm']))
				{
					$model->password = CPasswordHelper::hashPassword($_POST['Users']['new_password']);
					if($model->save())
						$this->redirect(array('view','id'=>crypt::Encrypt($model->id_user)));

				}else{
					if (!empty($_POST['Users']['new_password']) && !empty($_POST['Users']['new_password_confirm']))
						$model->addError('new_password', 'Le password non coincidono.');
					else
						$model->addError('new_password', 'La nuova password non può essere nulla.');
				}
			}else{
				$model->addError('password', 'La password inserita non è valida.');
			}
		}

		$this->render('changepwd',array(
			'model'=>$model,
		));
	}
	/**
	 * Invia via mail il sollecito di pagamento della quota di iscrizione all'associazione
	 * @param integer $id the ID dell'UTENTE
	 */
	public function actionSollecito(){
		$id = $_POST['id'];
		$users=Users::model()->findByPk(crypt::Decrypt($id));
		NMail::SendMail('sollecito',$id,$users->email);
 		$return['txt'] = 'Inviata mail di sollecito!';
 		echo CJSON::encode($return);
	}

	/**
	 * Effettua il reset della password dell'utente selezionato e la invia via mail.
	 * @param integer $id the ID dell'UTENTE
	 */
	public function actionResetpwd()
	{
		$id = $_POST['id'];
		$users=Users::model()->findByPk(crypt::Decrypt($id));
		$users->activation_code = md5(Utils::passwordGenerator()); //creo un nuovo activation_code
		$users->save();
		#$activation_code = crypt::Encrypt($users->activation_code.','.$id);
		#echo $activation_code;
		NMail::SendMail('recovery',crypt::Encrypt($users->id_user),$users->email,'Password0',$users->activation_code);
		$return['txt'] = 'Inviata mail per il reset della password!';
		echo CJSON::encode($return);
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;

		if(isset($_POST['Users']))
		{
			$model->attributes=$_POST['Users'];
			$model->activation_code = 0;

			//Se modifico la carica devo aggiornare anche lo user type
			/* NON E' DETTO!!! AL MOMENTO L'HO DISABILITATO*/
			//$cariche = Cariche::model()->findByPk($model->id_carica);
			//$model->id_users_type = $cariche->id_users_type;

			if($model->save()){
				$array['telefono'] = $_POST['Users']['telefono'];
				// salva i dati dell'user
				Settings::saveUser($model->id_user,$array);
				$this->redirect(array('view','id'=>crypt::Encrypt($model->id_user)));
			}

		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function action2fa($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;

		if(isset($_POST['Users']))
		{
			$key = CHtml::encode($_POST['Users']['ga_cod']);
			$user  = Users::model()->findByPk(crypt::Decrypt($id));
			$ga = new GoogleAuthenticator();
			$checkResult = $ga->verifyCode($_POST['Users']['ga_secret_key'], $key, 2);    // 2 = 2*30sec clock tolerance

			if ($checkResult)
			{
				$model->ga_secret_key = $_POST['Users']['ga_secret_key'];
			#	echo "<pre>".print_r($model->attributes,true)."</pre>";
			#	exit;

				if($model->save())
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_user)));
			}
		}

		$ga         = new GoogleAuthenticator();
        $secret     = $ga->createSecret();
        $qrCodeUrl  = $ga->getQRCodeGoogleUrl(Yii::app()->name, $secret);

        $this->render('2fa',array(
			'model'=>$model,
			'qrCodeUrl'=>$qrCodeUrl,
			'secret'=>$secret
		));
	}
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function action2faRemove($id)
	{
		$model=$this->loadModel(crypt::Decrypt($id));

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;

		if(isset($_POST['Users']))
		{
			$key = CHtml::encode($_POST['Users']['ga_cod']);
			$user  = Users::model()->findByPk(crypt::Decrypt($id));
			$ga = new GoogleAuthenticator();
			$checkResult = $ga->verifyCode($user->ga_secret_key, $key, 2);    // 2 = 2*30sec clock tolerance

			if ($checkResult)
			{
				$model->ga_secret_key = '';
				if($model->save())
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_user)));
			}
		}
        $this->render('2faRemove',array(
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
		//elimino tutte le impostazioni dell'utente
		SettingsUser::model()->deleteAllByAttributes(['id_user' => crypt::Decrypt($id)]);


		$this->loadModel(crypt::Decrypt($id))->delete();
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	/**
	 * Mostra la lista degli utenti che sono in scadenza con i pagamenti
	 * spiegazione giorni:
	 * - x >17000 Nessun Pagamento
	 * - 17000 < x > 46 Scaduta da x giorni
	 * - x = 45 ultimo giorno di scadenza
	 * - 0 < x > 45 Prossimo alla scadenza. Sono Questi che devono essere visualizzati per prima!!!
	 * - x < 0 Sono negativi e pertanto sono in ordine con il pagamento!
	 * - -45 < x Mancano 45 giorni alla scadenza
	 */
	public function actionReminder()
	{
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;
		if(isset($_POST['selectedReminder'])){
			foreach ($_POST['selectedReminder'] as $x => $id_user){
				//per salvare il numero di solleciti li devo prima caricare...
				$sollecito = WebApp::ContaSollecitiPagamenti($id_user);
				$sollecito ++;
				// salvo il numero di solleciti inviati
				Settings::saveUser($id_user,array('SollecitiPagamenti'=>$sollecito), $fields=array('SollecitiPagamenti'));

				//invio la mail
				$usersReminder=Users::model()->findByPk($id_user);
				NMail::SendMail('sollecito',crypt::Encrypt($id_user),$usersReminder->email);
			}
		}
		$criteria=new CDbCriteria();
		$criteria->compare('status_activation_code',1,false);

		//creo la lista degli utenti abilitati
		$users = Users::model()->findAll($criteria);
		$reminders = array();

		// per ciascun utente verifico i pagamenti
		foreach($users as $item) {
			$timelapse = WebApp::StatoPagamenti($item->id_user,true);
			if ($timelapse > -45 && $timelapse < 365){
				$reminders[] = $item;
			}
		}

		$dataProvider=new CActiveDataProvider('Users', array(
			'data' => $reminders,
		));

		$this->render('reminder',array(
			'dataProvider'=>$dataProvider, // non invio il dataProvider bensì il reminders appena creato sulla base delle scadenze dei pagamenti
		));
	}



	/**
	 * Mostra gli utenti approvati e slo quelli ATTVI, cioè che hanno un pagamento valido!
	 */
	public function actionIndex()
	{
		// $yearEnd = date('Y-m-d H:i', strtotime('last day of december'));

		$modelc=new Users('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Users']))
			$modelc->attributes=$_GET['Users'];

		// echo "<pre>".print_r($yearEnd,true)."</pre>";
		// exit;


		$this->render('index',array(
			'modelc'=>$modelc,
		));



		// $criteria=new CDbCriteria();
		// $criteria->compare('status_activation_code',1,false);
		//
		// //creo la lista degli utenti abilitati
		// $users = Users::model()->findAll($criteria);
		// $reminders = array();
		// $limit = 44;
		// if (isset($_GET['list']) && $_GET['list']=='all')
		// 	$limit=-20000;
		//
		//
		// // per ciascun utente verifico i pagamenti
		// foreach($users as $item) {
		// 	$timelapse = WebApp::StatoPagamenti($item->id_user,true);
		// 	if ($timelapse < -$limit){
		// 		$reminders[] = $item;
		// 	}
		// }
		//
		// $dataProvider=new CActiveDataProvider('Users', array(
		// 	'data' => $reminders,
		// 	'sort'=>array(
	    // 		'defaultOrder'=>array(
	    //   			'id_user'=>true
	    // 		)
	  	// 	),
		// ));
		//
		//
		// $this->render('index',array(
		// 	'dataProvider'=>$dataProvider,
		// ));
	}

	/**
	 * Mostra gli utenti da approvare.
	 */
	public function actionApprove()
	{
		$settings=new SettingsUserForm;

		 // echo "<pre>".print_r($_POST,true)."</pre>";
		 // exit;

		if(isset($_POST['selectedUsers'])){
			foreach ($_POST['selectedUsers'] as $x => $id_user){
				$model = $this->loadModel($id_user);
				//vanno approvati
				if ($_POST['rifiuto']==0){
					//aggiorno il suo stato da inattivo ad attivo
					$model->status_activation_code = 1;
					$model->activation_code = 0;

					//se l'utente che si registra è corporate, creo subito il merchant
					if ($model->corporate == 1){

						$model->id_users_type = 2; // DIVENTA COMMERCIANTE

						// imposto i settings del commerciante in modo tale da poter
						// creare l'account su btcpayserver
						// salvo i settings per il merchant
						foreach ($_POST['SettingsUserForm'] as $key => $value)
							$settings->$key = $value;

						$settings->blockchainAsset = CJSON::encode($_POST['blockchainAsset']);
						$settings->id_user = $model->id_user;
						Settings::saveUser($model->id_user,$settings->attributes);

						// salvo il merchant
						$merchants = new Merchants;
						$merchants->id_user = $model->id_user;
						$merchants->denomination = $model->denomination;
						$merchants->vat = $model->vat;
						$merchants->address = $model->address;
						$merchants->city = $model->city;
						$merchants->county = $model->country;
						$merchants->cap = $model->cap;
						$merchants->deleted = 0;
						if (!($merchants->save())){
							echo "<pre>".print_r($merchants->attributes,true)."</pre>";
							echo "Impossibile creare account commerciante!";
							die();
						}
						//CREO LE NOTIFICHE PER L'HELP IN LINEA ... e le assegno all'id user
						$this->helpNotifications($merchants->id_user);

						//CREO anche lo USER su BTCPay Server
						if ($this->createBTCServerUser($merchants->id_merchant,$model->email)){
							//invio la mail
							NMail::SendMail('users',crypt::Encrypt($model->id_user),$model->email,'',0);
						}
					}else{
						NMail::SendMail('users',crypt::Encrypt($model->id_user),$model->email,'',0);
					}
					// aggiorno il model se non ci sono errori
					$model->save();
				}else{
					// echo "<pre>".print_r($settings->attributes,true)."</pre>";
					// exit;
					//Invio mail all'user del rifiuto
					NMail::SendMail('usersDisclaim',crypt::Encrypt($model->id_user),$model->email,$_POST['motivazione'],0);
					//aggiorno il numero di mail inviate
					$set = Settings::loadUser($model->id_user);
					#echo "<pre>".print_r($settings,true)."</pre>";
					#exit;
					if (isset($set->numero_mail_approvazione))
						$numeroMail = $set->numero_mail_approvazione + 1;
					else
						$numeroMail = 1;

					Settings::saveUser($model->id_user,array('numero_mail_approvazione'=>$numeroMail));
				}
			}
		}

		$criteria=new CDbCriteria();
		$criteria->compare('status_activation_code',0,false);

		$dataProvider=new CActiveDataProvider('Users', array(
		    'criteria'=>$criteria,
		));
		$this->render('approve',array(
			'dataProvider'=>$dataProvider,
			'preferredCoinList'=>WebApp::getPreferredCoinList(),
			'settings'=>$settings,
		));
	}

	/**
	 * CREO LO USER SU BTCPAYSERVER
	 * Effettuo il login a Btcpay Server e creo un nuovo user
	 */
	public function createBTCServerUser($id_merchant,$email){
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		// carico i settings per l'accesso al server
		$merchants = Merchants::model()->findByPk($id_merchant);
		 // echo "<pre>".print_r($merchants,true)."</pre>";
		$BTCPayServerAddress = Settings::loadUser($merchants->id_user)->blockchainAddress;
		 // echo "<pre>".print_r($BTCPayServerAddress,true)."</pre>";
		$blockchain = Blockchains::model()->findByAttributes(['url'=>$BTCPayServerAddress]);
        // exit;

		//  echo "<pre>".print_r(crypt::Decrypt($blockchain->email),true)."</pre>";
		//  echo "<pre>".print_r(crypt::Decrypt($blockchain->password),true)."</pre>";
		// exit;

		// inizializzo la classe con user e password di administrator
		$BTCPay = new BTCPay(crypt::Decrypt($blockchain->email),crypt::Decrypt($blockchain->password));
		// imposto l'url
		$BTCPay->setBTCPayUrl($BTCPayServerAddress);
		// echo "<pre>".print_r($BTCPay->getBTCPayUrl(),true)."</pre>";
		// exit;

		//creo una password di 32 caratteri casuali
		$password = Utils::passwordGenerator(32);

		$BpsUsers = new BpsUsers;
		$BpsUsers->id_merchant = $id_merchant;
		$BpsUsers->bps_auth = crypt::Encrypt($password);

		if (!($BpsUsers->save())){
			return false;
		}

		//ok. Adesso creo lo user.
		$newUser = $BTCPay->newUser($email,$password);
		return true;
	}





	/**
	 * CREO LE NOTIFICHE PER L'HELP IN LINEA
	 */
	public function helpNotifications($id_user){
		$notification[0] = array(
			'type_notification' => 'help',
			'id_user' => $id_user,
			'id_tocheck' => 0,
			'status' => 'help',
			'description' => '<b>Crea il tuo Negozio.</b><br>Se vuoi accettare pagamenti in Bitcoin devi creare un Negozio ed attivare il POS',
			'url' => Yii::app()->createUrl("stores/create"),
			'timestamp' => time(),
			'price' => 0,
			'deleted' => 0,
		);
		$notification[1] = array(
			'type_notification' => 'help',
			'id_user' => $id_user,
			'id_tocheck' => 0,
			'status' => 'help',
			'description' => '<b>Attiva il tuo Pos.</b><br>Se vuoi accettare pagamenti in Bitcoin devi attivare il POS',
			'url' => Yii::app()->createUrl("pos/create"),
			'timestamp' => time(),
			'price' => 0,
			'deleted' => 0,
		);
		$notification[2] = array(
			'type_notification' => 'help',
			'id_user' => $id_user,
			'id_tocheck' => 0,
			'status' => 'help',
			'description' => '<b>Crea il tuo Wallet Token.</b><br>Se vuoi accettare pagamenti con il Token TTS, apri il widget verde in alto e clicca su wallet TTS.<br>Se desideri un livello di sicurezza superiore attiva la protezione 2fa nel menù <b>Account utente</b>',
			'url' => Yii::app()->createUrl("users/2fa",array("id"=>crypt::Encrypt($id_user))),
			'timestamp' => time(),
			'price' => 0,
			'deleted' => 0,
		);

		foreach ($notification as $n){
			$save = new Save;
			// false indica che la notifica non viene salvata per gli Admin
			Push::Send($save->Notification($n,false),'dashboard');
		}
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

	/**
	 * Performs the AJAX validation.
	 * @param Users $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='users-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * esporta in un foglio excel le transazioni
	 */
	public function actionExport()
	{
		$dataProvider=new CActiveDataProvider('Users', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'id_user'=>false
	    		)
	  		),
		));

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null || empty($settings->gdpr_titolare)){//} || empty($settings->poa_port)){
			echo CJSON::encode(array("error"=>'Errore: I parametri di configurazione non sono stati trovati'));
			exit;
		}

		#echo "<pre>".print_r($transactions, true)."</pre>";
		#exit;
		$Creator = $settings->gdpr_titolare; //"ICT Nucleo Informatico - Napoli";
		$LastModifiedBy = ''; //"Sergio Casizzone";
		$Title = "Office 2007 XLSX Test Document";
		$Subject = "Office 2007 XLSX Test Document";
		$Description = "Estrazione dati per Office 2007 XLSX, generated using PHP classes.";
		$Keywords = "office 2007 openxml php";
		$Category = "export";

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set document properties
		$objPHPExcel->getProperties()->setCreator($Creator)
									 ->setLastModifiedBy($LastModifiedBy)
									 ->setTitle($Title)
									 ->setSubject($Subject)
									 ->setDescription($Description)
									 ->setKeywords($Keywords)
									 ->setCategory($Category);

		// Add header

		$colonne = array('a','b','c','d','e','f','g','h','i','j','k','l','m');
		$intestazione = array(
			"#",
			"Tipo Utente",
			"Carica Statutaria",
			"Cognome",
			"Nome",
			"email",
			"Persona Giuridica",
			"Denominazione",
			"C.F./P.Iva",
			"Indirizzo",
			"Cap",
			"Città",
			"Nazione",
		);
		$listCorporate = [0=>'No',1=>'Si'];
		$countryData = WebApp::CountryDataset();

		//creazione foglio excel
		foreach ($colonne as $n => $l){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($l.'1', $intestazione[$n]);
		}
		$transactions = new CDataProviderIterator($dataProvider);
		$riga = 2;
		foreach($transactions as $item) {
			// Miscellaneous glyphs, UTF-8
			if ($item->country == '')
				$item->country = 'IT';

			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$riga, $item->id_user)
			            ->setCellValue('B'.$riga, UsersType::model()->findByPk($item->id_users_type)->desc)
						->setCellValue('C'.$riga, Cariche::model()->findByPk($item->id_carica)->description)
						->setCellValue('D'.$riga, $item->surname)
						->setCellValue('E'.$riga, $item->name)
						->setCellValue('F'.$riga, $item->email)
						->setCellValue('G'.$riga, $listCorporate[$item->corporate])
						->setCellValue('H'.$riga, $item->denomination)
						->setCellValue('I'.$riga, $item->vat)
						->setCellValue('J'.$riga, $item->address)
						->setCellValue('K'.$riga, $item->cap)
						->setCellValue('L'.$riga, ComuniItaliani::model()->findByPk($item->city)->citta)
						->setCellValue('M'.$riga, $countryData[$item->country]);

			$riga++;
		}

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('export');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		// Redirect output to a client’s web browser (Excel5)
		$time = time();
		$date = date('Y/m/d H:i:s', $time);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$date.'-export.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	/**
	 * Prints all models.
	 */
	public function actionPrint(){
		//carico i SETTINGS della WebApp
		$settingsWebApp = Settings::load();

		//carico l'estensione pdf
		Yii::import('application.extensions.MYPDF.*');

		// create new PDF document
		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(Yii::app()->params['adminName']);
		$pdf->SetAuthor(Yii::app()->params['shortName']);
		$pdf->SetTitle("Elenco Soci");
		$pdf->SetSubject('Elenco Soci');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, myPDF_HEADER_STRING);
		//$pdf->SetHeaderData(Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazione'], 42, Yii::app()->params['adminName'], Yii::app()->params['indirizzo']);
		$gdpr_address = $settingsWebApp->gdpr_address
			."\n".$settingsWebApp->gdpr_cap
			." - ".ComuniItaliani::model()->findByPk($settingsWebApp->gdpr_city)->citta
			."\nC.F./P.Iva: ".$settingsWebApp->gdpr_vat;
		$pdf->SetHeaderData(
			Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazionePrint'],
			26,
			$settingsWebApp->gdpr_titolare,
			$gdpr_address
		);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		// ---------------------------------------------------------

		// stabilisco i criteri di ricerca
		$criteria=new CDbCriteria;

		if ($_GET['typelist'] == 0){
			$criteria->addCondition("status = 'complete'");
		}else if ($_GET['typelist'] == 1){
			$criteria->addCondition("(status = 'paid' OR status = 'confirmed')");
		}else if ($_GET['typelist'] == 2){
			$criteria->addCondition("status = 'new'");
		}


		//carico la tabella
		$dataProvider= new CActiveDataProvider('Users', array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'id_user'=>true,
				)
			),
		));

		$iterator = new CDataProviderIterator($dataProvider);
		$x = $dataProvider->totalItemCount;
		foreach($iterator as $data) {
			$loadData[$x][] = $x;
			$loadData[$x][] = UsersType::model()->findByPk($data->id_users_type)->desc;
			$loadData[$x][] = Cariche::model()->findByPk($data->id_carica)->description;
			$loadData[$x][] = $data->surname.' '.$data->name;
			$loadData[$x][] = $data->email;
			$loadData[$x][] = ($data->corporate == 0 ? 'No' : 'Si');
			$loadData[$x][] = str_replace("&nbsp;"," ",strip_tags(WebApp::StatoPagamenti($data->id_user)));

			$x--;
		}
		// echo "<pre>".print_r($loadData, true)."</pre>";
		// exit;



		$header['head'] = array('#', 'Tipo', 'Carica', 'Nominativo', 'email','P.G.','Scadenza');
		$header['title'] = 'Lista Soci';

		// print colored table
		$pdf->ColoredTable($header, $loadData, 'soci');
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		ob_end_clean();

		//Close and output PDF document
		$pdf->Output('soci.pdf', 'I');
	}
	/**
	 * Saves the Subscription for push messages.
	 * @param POST VAPID KEYS
	 * this function NOT REQUIRE user to login
	 */
	public function actionSaveSubscription()
	{
		ini_set("allow_url_fopen", true);
		//
 		$raw_post_data = file_get_contents('php://input');
 		if (false === $raw_post_data) {
 			throw new \Exception('Could not read from the php://input stream or invalid Subscription object received.');
 		}
 		$raw = json_decode($raw_post_data);
		$browser = $_SERVER['HTTP_USER_AGENT'];

		// echo '<pre>'.print_r($raw,true).'</pre>';
		// exit;

		$Criteria=new CDbCriteria();
		$Criteria->compare('id_user',Yii::app()->user->objUser['id_user'], false);
		$Criteria->compare('browser',$browser, false);
		$Criteria->compare('type','dashboard', false);

		$vapidProvider=new CActiveDataProvider('PushSubscriptions', array(
			'criteria'=>$Criteria,
		));

		if ($vapidProvider->totalItemCount == 0 && $raw !== null ){
			//save
			$vapid = new PushSubscriptions;
			$vapid->id_user = Yii::app()->user->objUser['id_user'];
			$vapid->browser = $browser;
			$vapid->endpoint = $raw->endpoint;
			$vapid->auth = $raw->keys->auth;
			$vapid->p256dh = $raw->keys->p256dh;
			$vapid->type = 'dashboard'; //definisco il tipo di sottoscrizione, esclusivo per la dashboard

			if (!$vapid->save()){
				echo 'Cannot save subscription on server!';
				exit;//
			}
			// echo 'Subscription saved on server!';
		}else{
			//delete
			$iterator = new CDataProviderIterator($vapidProvider);
			foreach($iterator as $data) {
				//echo '<pre>'.print_r($data->id_subscription,true).'</pre>';
				#exit;
				$vapid=PushSubscriptions::model()->findByPk($data->id_subscription)->delete();

				// if($vapid!==null)
				// 	$vapid->delete();
			}
			//echo 'Subscriptions deleted on server!';
		}
	}
}
