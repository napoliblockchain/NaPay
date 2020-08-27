<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');
Yii::import('libs.NaPacks.WebApp');

class SiteController extends Controller
{
	public function init()
	{

	}

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array(
					'register', // registrazione socio
					'presentazione', //????
					'index', //pagina iniziale: fa il redirect a login
					'error', //pagina di errore
					'login', //pagina di login
					'logout', //si scollega dall'applicazione e redirect a login
					'loginquota', //pagina di collegamento pagamento quote associazione
					'activate', // attiva un utenza ??? FUNZIONA ANCORA??
					'recoverypassword',// PAGINA DI LOGIN PER IL RIPRISTINO DELLA PASSWORD
					'regenerate', // ACTIONI CHE RIGENERA LA PASSWORD
					'printPrivacy', // STAMPA LA PRIVACY ?? FUNZIONA??

				),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'dash', // pagina dashboard dopo aver effettuato il login
				),
				'users'=>array('@'),
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$settings=Settings::load();
		if ($settings->version == '0000 0000'){
			$this->redirect(array('site/register'));
		}

		$this->redirect(array('site/login'));
	}





	public function actionRecoverypassword()
	{
		$this->layout='//layouts/column_login'; //NON ATTIVA IL BACKEND

		$model=new RecoverypasswordForm;
		if(isset($_POST['RecoverypasswordForm']))
		{
			$model->attributes=$_POST['RecoverypasswordForm'];
			$model->reCaptcha=$_POST['reCaptcha'];
			// validate user input and redirect to the previous page if valid
			if($model->validate()){

				// cerco user
				$users=Users::model()->findByAttributes(array('email'=>$model->username));
				$users->activation_code = md5(Utils::passwordGenerator()); //creo un nuovo activation_code

				//$users->status_activation_code = 0; // lo user adesso è inattivo
				// no posso impostarlo a zero, altrimenti qualunque malintenzionato potrebbe inserire una
				//qualunque mail e disattivare l'account di chiunque....
				$users->save();
				NMail::SendMail('recovery',crypt::Encrypt($users->id_user),$model->username,'123456',$users->activation_code);
				$this->render('recovery/sent');
				exit;
			}
		}
		$this->render('recovery/login',array('model'=>$model));
	}

	public function actionRegenerate($activation_code)
	{
		// echo '<pre>'.print_r($_POST,true).'</pre>';
		// exit;

		$this->layout='//layouts/column_login';
		$explode = explode(',',crypt::Decrypt($activation_code));

		if (isset($explode[1])){
			$model=Users::model()->findByPk(crypt::Decrypt($explode[1]));
			if ($model !== null && $model->activation_code == $explode[0]){
				if(isset($_POST['Users']))
				{
					$flag = true;
					$model->password = $_POST['Users']['new_password'];
					if ($_POST['Users']['new_password'] != $_POST['Users']['new_password_confirm']){
						$model->addError('new_password', 'Le password non coincidono.');
						$flag = false;
					}
					if (empty($_POST['Users']['new_password'])){
						$model->addError('new_password', 'Il campo Password non può essere vuoto.');
						$flag = false;
					}
					if (empty($_POST['Users']['new_password_confirm'])){
						$model->addError('new_password_confirm', 'Il campo Ripeti Password non può essere vuoto.');
						$flag = false;
					}
					if ($flag){
						$model->password = CPasswordHelper::hashPassword($_POST['Users']['new_password']);
						$model->activation_code = '';
						// echo '<pre>'.print_r($model->attributes,true).'</pre>';
						// exit;
						if ($model->update()){
							$this->render('recovery/ok');
							exit(1);
						}
					}
				}
				$this->render('recovery/password',array('model'=>$model));
				exit(1);
			}

		}
		$this->render('recovery/error');

	}


	public function actionCookies()
	{
		$informativa_cookies = $this->getInformativaCookies();
		$this->render('pages/cookies',array('informativa_cookies'=>$informativa_cookies));
	}

	public function actionDash()
	{
		// se sei guest vai a login
		if (Yii::app()->user->isGuest){
			Yii::app()->user->logout();
			$this->redirect(array('site/index'));
		}
		// se non è impostata la variabile objUser vai a login
		if (!(isset(Yii::app()->user->objUser))) {
			Yii::app()->user->logout();
			$this->redirect(array('site/index'));
		}

		// inizializzo i criteri di ricerca
		$criteria=new CDbCriteria();

		//sei socio semplice
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 5){
		 	$criteria->compare('id_merchant',0,false);
		}

		//sei commerciante
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 10){
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
		}

		// carico la lista delle transazioni bitcoin
		$dataProvider=new CActiveDataProvider('Transactions', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'invoice_timestamp'=>false
	    		)
	  		),
		    'criteria'=>$criteria,
		));

		//carico la lista delle transazioni token
		//carico il wallet selezionato nei settings
		// $settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
		// if (empty($settings->id_wallet)){
		// 	$from_address = '0x0000000000000000000000000000000000000000';
		// }else{
		// 	$wallet = Wallets::model()->findByPk($settings->id_wallet);
		// 	$from_address = $wallet->wallet_address;
		// }

		$criteriaTokens=new CDbCriteria;
		if (Yii::app()->user->objUser['privilegi'] == 10){
		 	$criteriaTokens->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}
		//$criteriaTokens->addInCondition('from_address',[$from_address]);
		// $criteriaTokens->addInCondition('to_address',[$from_address],'OR');
		// $criteriaTokens->compare('type','token',false);
		// $criteriaTokens->compare('to_address',$from_address,false);


		$dataProviderTokens=new CActiveDataProvider('PosInvoices', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'id_token'=>false
	    		)
	  		),
		    'criteria'=>$criteriaTokens,
		));

		// echo "<pre>".print_r($dataProviderTokens,true)."</pre>";
		// exit;


		//genero il criterio di ricerca per la guida
		$criteriaHelp=new CDbCriteria();
		if (isset($merchants->id_merchant))
			$criteriaHelp->compare('id_merchant',$merchants->id_merchant,false);

		$criteriaHelp->compare('deleted',0,false);

		// carico i negozi e i pos
		$stores = Stores::model()->findAll($criteriaHelp);
		$pos = Pos::model()->findAll($criteriaHelp);

		// solo in caso di commerciante
		$warningmessage = null;
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 10){

			// verifico l'esistenza di un wallet token e in caso di assenza mostro l'avviso per collegarsi
			$wallets = Wallets::model()->findByAttributes(array("id_user"=>Yii::app()->user->objUser['id_user']));
			if (null === $wallets)
				$warningmessage[] = $this->writeMessage('wallet');

			// verifico se è in scadenza
			$deadline = WebApp::StatoPagamenti(Yii::app()->user->objUser['id_user'],true);
			if ($deadline >= -31-28){
				$warningmessage[] = $this->writeMessage('deadline', 28+31 - $deadline);
			}
		}

		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'dataProviderTokens'=>$dataProviderTokens,
			'stores'=>$stores,
			'pos'=>$pos,
			'warningmessage'=>$warningmessage,
		));
	}

	/**
	 * Genero il div con l'avviso di creare un wallet Token
	 */
	public function writeMessage($what,$days=null){
		$http_host = $_SERVER['HTTP_HOST'];

		if ($what == 'wallet'){
			$Url = 'https://wallet.' . Utils::get_domain($http_host) . '/index.php?r=wallet/index';
			return '
			<div class="col m-t-25">
				<div class="alert alert-success" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					Hai completato le operazioni per ricevere Bitcoin tramite POS.
					Adesso, se vuoi ricevere anche i token, crea il tuo portafoglio digitale personale cliccando sul pulsante seguente:
					<a href="'.$Url.'" target="_blank">
						<span style="margin-left:30px;">
							<button type="button" class="btn btn-info text-dark" data-dismiss="modal" >
								<img style="max-width: 40px;" src="css/images/napay-yellow.png"/>&nbsp; Wallet
							</button>
						</span>
					</a>
				</div>
			</div>
			';
		}
		if ($what == 'deadline'){
			$Url = Yii::app()->createUrl('payfee/index');

			return '
			<div class="col m-t-25">
				<div class="alert alert-danger" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
					<b>ATTENZIONE! Per continuare a sostenere l\'associazione e beneficiare dei servizi offerti è importante versare la quota
					associativa entro il 28 febbraio.</b>
					<br>Restano alla scadenza '.abs($days).' giorni, dopodiché non potrai più usare l\'applicazione.<br><br>
					Provvedi al pagamento della quota associativa entro la data di scadenza cliccando sul pulsante seguente:
					<a href="'.$Url.'" target="_self">
						<span style="margin-left:30px;">
							<button type="button" class="btn btn-dark" data-dismiss="modal" >
								Paga Iscrizione
							</button>
						</span>
					</a>
				</div>
			</div>
			';
		}

	}




	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
	/**
	* Displays the register page
	*/
	public function actionRegister()
	{
		$this->layout='//layouts/column_login';
		$model=new UsersRegisterForm;
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;
		if(isset($_POST['UsersRegisterForm']))
		{
			$model->attributes=$_POST['UsersRegisterForm'];
			$model->corporate = $_POST['UsersRegisterForm']['corporate'];

			#echo "<pre>".print_r($model->attributes,true)."</pre>";

			if ($_POST['UsersRegisterForm']['repeat_password'] != $_POST['UsersRegisterForm']['password']){
				$model->addError('password', 'Le password non coincidono.');
			}else{
				$model->reCaptcha=$_POST['reCaptcha'];
				//frego il sistema quando l'utente è socio standard e non commerciante
				if ($_POST['UsersRegisterForm']['corporate'] == 0)
					$model->consenso_pos = 1;

				#echo "<pre>".print_r($model,true)."</pre>";
				#echo "<pre>".print_r($_POST,true)."</pre>";
				#exit;

				if ($model->validate()){
					$settings=Settings::load();
					$savedModel = $_POST['UsersRegisterForm'];
					#echo "<pre>".print_r($savedModel,true)."</pre>";
					#exit;

					$savedPassword = $model->password;
					//$model->password = CPasswordHelper::hashPassword($model->password);
					$model->activation_code = md5($model->password);
					$model->status_activation_code = 0;
					$model->corporate = $_POST['UsersRegisterForm']['corporate'];
					$model->denomination = $_POST['UsersRegisterForm']['denomination'];

					if ($settings->version == '0000 0000'){
						$settings->version = Utils::passwordGenerator(8);
						Settings::save($settings,array('version'));

						$model->id_users_type = 3; //UTENTE AMMINISTRATORE alla prima registrazine
					}else{
						$model->id_users_type = 5; //UTENTE CON PROFILO SOCIO quando si registra (DIVENTA 2 QUANDO E' COMMERCIANTE)
					}
					$model->id_carica = 5; //UTENTE SOCIO ORDINARIO quando si registra da solo
					#echo "<pre>".print_r($model->attributes,true)."</pre>";
					#exit;

					if($model->save()){
						$timestamp = time();

						//Salvo i timestamp dei consensi al trattamento dei dati e statuto
						$array['timestamp_consenso_statuto'] = $timestamp;
						$array['timestamp_consenso_privacy'] = $timestamp;
						$array['timestamp_consenso_termini'] = $timestamp;

						if ($savedModel['consenso_marketing'] == 1){
							$array['timestamp_consenso_marketing'] = $timestamp;
						}else{
							$array['timestamp_consenso_marketing'] = 0;
						}
						//se sei corporate ho necessità di un ulteriore consenso
						if ($model->corporate == 1){
							$array['timestamp_consenso_pos'] = $timestamp;
						}
						$array['numero_mail_approvazione'] = 0;
						$array['telefono'] = $_POST['UsersRegisterForm']['telefono'];

						// salva i dati dell'user
						Settings::saveUser($model->id_user,$array); //creo il numero di mail inviate per approve/disclaim iscrizione
						#exit;
						//Invio mail all'user
						NMail::SendMail('iscrizione',crypt::Encrypt($model->id_user),$model->email,$savedPassword,$model->activation_code);

						//cerco tutti gli admin per inoltrare la mail di nuova iscrizione
						$criteria=new CDbCriteria();
						$criteria->compare('id_users_type',3,false);
						$admins = Users::model()->findAll($criteria);

						$listaAdmins = CHtml::listData($admins,'id_user' , 'email');
						#echo "<pre>".print_r($listaAdmins,true)."</pre>";
						#exit;
						foreach ($listaAdmins as $id => $adminEmail)
							NMail::SendMail('iscrizioneAdmin',crypt::Encrypt($model->id_user),$adminEmail,0,0);

						$this->render('register/ok');
						exit(1);
					}
					//reimposto le password in caso di errore
					$model->password = $savedPassword;
					$model->repeat_password = $savedPassword;
				}
			}
		}

		$this->render('register/index',array(
			'model'=>$model,
		));
	}

	/**
	* Displays the login page
	*/
	public function actionLogin()
	{
		//$this->layout = "/column2";
		$model=new LoginForm;
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			$model->reCaptcha=$_POST['reCaptcha'];

			#echo "<pre>".print_r($model,true)."</pre>";
			#exit;

			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(array('site/dash')); // per correggere errore con pwa che non fa il redirect dopo il login
				//$this->redirect(Yii::app()->user->returnUrl);
		}
		#echo Yii::app()->user->objUser['facade'];
		#exit;

		if (!isset(Yii::app()->user->objUser)){
			$this->layout='//layouts/column_login';
			$this->render('login',array('model'=>$model)); // display the login form if not connected or validated user
		}else {
			switch (Yii::app()->user->objUser['facade']){
				case 'dashboard':
					$this->redirect(array('site/dash'));
					break;

				case 'quota':
					$this->redirect(array('payfee/index'));
					break;

				default:
					$this->redirect(array('site/logout'));
					break;
			}
		}
	}

	/**
	* Displays the login page for quota payment
	*/
	public function actionLoginquota()
	{
		#echo "<pre>".print_r($_POST,true)."</pre>";
		#exit;
		$model=new LoginQuotaForm;
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		// collect user input data
		if(isset($_POST['LoginQuotaForm']))
		{
			$model->attributes=$_POST['LoginQuotaForm'];
			$model->reCaptcha=$_POST['reCaptcha'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(array('payfee/index'));
		}
		#echo Yii::app()->user->objUser['facade'];
		#exit;

		if (!isset(Yii::app()->user->objUser)){
			$this->layout='//layouts/column_login';
			$this->render('loginquota',array('model'=>$model)); // display the login form if not connected or validated user
		}else{
			$this->redirect(array('site/logout'));
		}
	}

	/**
	* Logs out the current user and redirect to homepage.
	*/
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(array('site/index'));
		//$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionActivate($activation_code)
	{
		$this->layout='//layouts/column_login';
		$explode = explode(',',crypt::Decrypt($activation_code));
		//$explode = explode(',',$activation_code);
		$model=Users::model()->findByPk(crypt::Decrypt($explode[1]));

		if (isset($model->activation_code)){
			switch ($model->status_activation_code){
				case 0:
					if ($model->activation_code == $explode[0]){
						$model->status_activation_code = 1;
						$model->activation_code = '0'; //Deve essere 0 e non (vuoto) altrimenti va in errore!
						if($model->update())
							$this->render('activate/ok');
						else
							$this->render('activate/error');
					}else{
						$this->render('activate/error');
					}
					break;
				case 1:
					$this->render('activate/ok');
					break;
			}
		}else{
			$this->render('activate/error');
		}
	}



	public function actionPrintPrivacy(){
		//carico i SETTINGS della WebApp
		$settingsWebApp = Settings::load();

		//carico l'estensione pdf
		Yii::import('application.extensions.MYPDF.*');

		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(Yii::app()->params['adminName']);
		$pdf->SetAuthor(Yii::app()->params['shortName']);
		$pdf->SetTitle('Informativa sulla privacy e consenso al trattamento dei dati.');
		$pdf->SetSubject('Informativa sulla privacy e consenso al trattamento dei dati.');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, myPDF_HEADER_STRING);
		$gdpr_address = $settingsWebApp->gdpr_address
			."\n".$settingsWebApp->gdpr_cap
			." - ".ComuniItaliani::model()->findByPk($settingsWebApp->gdpr_city)->citta
			."\nC.F./P.Iva: ".$settingsWebApp->gdpr_vat;
		$pdf->SetHeaderData(Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazionePrint'], 26, $settingsWebApp->gdpr_titolare, $gdpr_address);

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

		//$pdf->SetPrintFooter(false);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		// ---------------------------------------------------------
		// set font
		$pdf->SetFont('dejavusans', '', 10);
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// // add a page
		$pdf->AddPage();

		//
		$style = '<style>ul {list-style-type: none; margin-left: 20px;}</style>';
		$titolo = '<strong class="card-title mb-3"><h2 class="pb-2 display-5">INFORMATIVA EX ART. 13 GDPR PER SOCI E ASPIRANTI SOCI E CONSENSO AL TRATTAMENTO</h2></strong>';

		$informativa = $this->getInformativa();
		$consenso = $this->getConsenso();

		$html = $style.$titolo.$informativa.$consenso;

		// // output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');
		 // reset pointer to the last page
		$pdf->lastPage();
		//Close and output PDF document
		ob_end_clean();
		$pdf->Output('Informativa-privacy.pdf', 'I');
	}
	private function getDatiRichiedente($id){
		$settings = Settings::load();

		if ($id == 0){

			$text =
			'
			<table width="100%" border="1" style="font-size:0.8em;">
			<tr>
				<td height="40" colspan="2">Nome:</td>
				<td colspan="2">Cognome:</td>
			</tr>
			<tr>
				<td height="40" colspan="2">Luogo di nascita:</td>
				<td colspan="2">Data:</td>
			</tr>
			<tr>
				<td height="40" colspan="4">Codice Fiscale:</td>
			</tr>
			<tr>
				<td height="40" colspan="3">Numero Documento:</td>
				<td colspan="1">Tipo Documento:</td>
			</tr>
			<tr>
				<td height="40" colspan="3">Residente in via:</td>
				<td colspan="1">n.</td>
			</tr>
			<tr>
				<td height="40" colspan="2">C.A.P.</td>
				<td colspan="2">Città:</td>
			</tr>
			<tr>
				<td height="40" colspan="2">e-mail:</td>
				<td colspan="2">Tel/cell:</td>
			</tr>
			</table>
			<p></hr></p>
			<table width="100%" border="0">
			<tr>
				<td height="140">SOCIO ORDINARIO (Persona fisica): Quota annuale € '.$settings->quota_iscrizione_socio.'</td>
			</tr>
			</table>
			<div class="row">
				<p>
				________________________, lì __________________
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				L’INTERESSATO<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;
				(firma leggibile)</p>
				<p>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;
				_______________________</p>
			</div>
			';
		}else{
			$text =
			'
			<table width="100%" border="1" style="font-size:0.8em;">
			<tr>
				<td height="40" colspan="4">Denominazione:</td>
			</tr>
			<tr>
				<td height="40" colspan="4">Partita Iva/Cod. Fisc:</td>
			</tr>
			<tr>
				<td height="40" colspan="3">Sede legale:</td>
				<td colspan="1">n.</td>
			</tr>
			<tr>
				<td height="40" colspan="2">C.A.P.</td>
				<td colspan="2">Città:</td>
			</tr>
			</table>
			<p>Dati Rappresentante legale</p>
			<table width="100%" border="1" style="font-size:0.8em;">
			<tr>
				<td height="40" colspan="2">Nome:</td>
				<td colspan="2">Cognome:</td>
			</tr>
			<tr>
				<td height="40" colspan="2">Luogo di nascita:</td>
				<td colspan="2">Data:</td>
			</tr>
			<tr>
				<td height="40" colspan="4">Codice Fiscale:</td>
			</tr>
			<tr>
				<td height="40" colspan="3">Numero Documento:</td>
				<td colspan="1">Tipo Documento:</td>
			</tr>
			<tr>
				<td height="40" colspan="3">Residente in via:</td>
				<td colspan="1">n.</td>
			</tr>
			<tr>
				<td height="40" colspan="2">C.A.P.</td>
				<td colspan="2">Città:</td>
			</tr>
			<tr>
				<td height="40" colspan="2">e-mail:</td>
				<td colspan="2">Tel/cell:</td>
			</tr>
			</table>
			<p></hr></p>
			<table width="100%" border="0">
			<tr>
				<td height="110">SOCIO ORDINARIO (Persona giuridica): Quota annuale € '.$settings->quota_iscrizione_socioGiuridico.'</td>
			</tr>
			</table>
			<div class="row">
				<p>
				________________________, lì __________________
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				L’INTERESSATO<br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;
				(firma leggibile)</p>
				<p>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				&nbsp;&nbsp;
				_______________________</p>
			</div>
			';
		}
		return $text;
	}


	private function getInformativa(){
		$settings = Settings::load();

		//$text = file_get_contents(Yii::app()->basePath .'/../documenti/informativa_privacy.htm');
		$stream = fopen(Yii::app()->basePath .'/../documenti/informativa_privacy.txt',"r");
		$text = stream_get_contents($stream);
		fclose($stream);

		//echo $text;
		//exit;

		// $text =
		// '
		// <p>
		// 	Caro socio/a o aspirante socio/a,<br>
		// 	ai sensi degli art. 13 del Regolamento UE 2016/679 in materia di protezione dei dati personali (“GDPR”) ti informiamo di quanto segue.
		// </p>
		// <h3 class="pb-2 display-5">Titolare del trattamento</h3>
		// <p>
		// 	Il titolare del trattamento è <strong>'.$settings->gdpr_titolare.'</strong>,
		// 	con sede in '.ComuniItaliani::model()->findByPk($settings->gdpr_city)->citta.' – '.$settings->gdpr_address.'
		// 	– tel: '.$settings->gdpr_telefono.' – fax: '.$settings->gdpr_fax.' – mail: '.$settings->gdpr_email.'
		// </p>
		// ';
		// // <p>
		// // 	Il Data Protection Officer (DPO) nominato dall’Associazione è '.$settings->gdpr_dpo_denomination.',
		// // 	a cui ciascun interessato può scrivere, in relazione al trattamento dei dati svolto dall’Associazione e/o in relazione ai Suoi diritti,
		// // 	all’indirizzo '.$settings->gdpr_dpo_email.'. <br>
		// // 	Il DPO può essere altresì contattato telefonicamente tramite l’Associazione al numero '.$settings->gdpr_dpo_telefono.'.
		// // </p>
		// $text .= '
		// <h3 class="pb-2 display-5">Finalità del trattamento e base giuridica</h3>
		// <p>
		// 	L’Associazione tratta i tuoi dati personali esclusivamente per lo svolgimento dell’attività istituzionale ed in particolare:
		// </p>
		// <div class="row">
		// 	<div class="col-md-12">
		// 		<ul>
		// 		<li>a)	per la gestione del rapporto associativo (invio della corrispondenza, convocazione alle sedute degli organi, procedure amministrative interne) e per l’organizzazione ed esecuzione del servizio;</li>
		// 		<li>b)	per adempiere agli obblighi di legge (es. fiscali, assicurativi, ecc.) riferiti ai soci dell’Associazione;</li>
		// 		<li>c)	per l’invio (tramite posta, posta elettronica, newsletter o numero di cellulare o altri mezzi informatici) di comunicazioni legate all’attività e iniziative dell’Associazione;</li>
		// 		<li>d)	in relazione alle immagini/video, per la pubblicazione nel sito dell’Associazione, sui social network dell’Associazione o su newsletter o su materiale cartaceo di promozione delle attività istituzionali dell’Associazione previo Tuo esplicito consenso;</li>
		// 		<li>e)	in relazione alla foto personale, per l’inserimento nel tesserino di riconoscimento;</li>
		// 		<li>f)	per la partecipazione dei soci a corsi, incontri e iniziative e per l’organizzazione e gestione dei corsi;</li>
		// 		<li>g)	per analisi statistiche, anche in forma aggregata.</li>
		// 		</ul>
		// 	</div>
		// </div>
		// <p>Per nessun motivo i dati raccolti saranno oggetto di valutazioni discriminatorie o che potranno arrecare effetti pregiudizievoli alle persone
		// 	coinvolte.</p>
		// <p>La base giuridica del trattamento è rappresentata dalla richiesta di adesione e dal contratto associativo (art. 6 comma 1 lett. b GDPR),
		// 	dal consenso  espresso al trattamento (art. 6 comma 1 lett. a – art. 9 comma 2 lett. a GDPR), dai contatti regolari con l’Associazione
		// 	(art. 9 comma 2 lett. d GDPR), dagli obblighi legali a cui è tenuta l’Associazione (art. 6 comma 1 lett. c GDPR).
		// </p>
		// <h3 class="pb-2 display-5">Modalità e principi del trattamento</h3>
		// <p>Il trattamento avverrà nel rispetto del GDPR 679/2016 e del D.Lgs. n. 196/03 (“Codice in materia di protezione dei dati personali”),
		// 	nonché dei principi di liceità, correttezza e trasparenza, adeguatezza e pertinenza, con modalità cartacee ed informatiche,
		// 	ad opera di persone autorizzate dall’Associazione e con l’adozione di misure adeguate di protezione, in modo da garantire la sicurezza
		// 	e la riservatezza dei dati.
		// 	<i>Non verrà svolto alcun processo decisionale automatizzato, ivi compresa la profilazione.</i>
		// </p>
		// <h3 class="pb-2 display-5">Necessità del conferimento</h3>
		// <p>Il conferimento dei dati anagrafici e di contatto è necessario in quanto strettamente legato alla gestione del rapporto associativo.
		// 	<i>Il consenso all’utilizzo delle immagini/video e alla diffusione dei dati nel sito istituzionale e nelle altre modalità sopra descritte è facoltativo.</i>
		// </p>
		// <h3 class="pb-2 display-5">Comunicazione dei dati e trasferimento all’estero dei dati</h3>
		// <p>
		// <strong><i>I dati potranno essere comunicati agli altri soci ai fini dell’organizzazione ed esecuzione del servizio.</i></strong>
		// I dati potranno essere comunicati ai soggetti deputati allo svolgimento di attività a cui l’Associazione è tenuta in base ad obbligo di
		// legge (commercialista, assicuratore, sistemista, ecc.) e a tutte quelle persone fisiche e/o giuridiche, pubbliche e/o private quando la
		// comunicazione risulti necessaria o funzionale allo svolgimento dell’attività istituzionale (formatori, Enti Locali, ditte che curano la
		// manutenzione informatica, società organizzatrici dei corsi, ecc.). I dati potranno essere trasferiti a destinatari con sede extra UE che
		// hanno sottoscritto accordi diretti ad assicurare un livello di protezione adeguato dei dati personali, o comunque previa verifica che il
		// destinatario garantisca adeguate misure di protezione. Ove necessario o opportuno, i soggetti cui vengono trasmessi i dati per lo svolgimento
		// di attività per conto dell’Associazione saranno nominati Responsabili del trattamento dei dati personali ai sensi dell’art. 28 GDPR.
		// </p>
		// <h3 class="pb-2 display-5">Periodo di conservazione dei dati</h3>
		// <p>I dati saranno utilizzati dall’Associazione fino alla cessazione del rapporto associativo. Dopo tale data, saranno conservati per
		// 	finalità di archivio, obblighi legali o contabili o fiscali o per esigenze di tutela dell’Associazione, con esclusione di comunicazioni
		// 	a terzi e diffusione in ogni caso applicando i principi di proporzionalità e minimizzazione. Infine i tuoi dati saranno cancellati.
		// </p>
		// <h3 class="pb-2 display-5">Diritti dell’interessato</h3>
		// <p>Tra i diritti a Lei riconosciuti dal GDPR rientrano quelli di:
		// </p>
		// <div class="row">
		// 	<div class="col-md-12">
		// 		<ul class="vue-list-inner">
		// 		<li>&bull;	chiedere l’accesso ai tuoi dati personali ed alle informazioni relative agli stessi; la rettifica dei dati inesatti o
		// 			l’integrazione di quelli incompleti; la cancellazione dei propri dati personali (al verificarsi di una delle condizioni indicate
		// 			nell’art. 17, paragrafo 1 del GDPR e nel rispetto delle eccezioni previste nel paragrafo 3 dello stesso articolo); la limitazione
		// 			del trattamento dei dati personali (al ricorrere di una delle ipotesi indicate nell’art. 18, paragrafo 1 del GDPR);</li>
		// 		<li>&bull;	richiedere ed ottenere - nelle ipotesi in cui la base giuridica del trattamento sia il contratto o il consenso, e lo stesso
		// 			sia effettuato con mezzi automatizzati - i Suoi dati personali in un formato strutturato e leggibile da dispositivo automatico, anche
		// 			al fine di comunicare tali dati ad un altro titolare del trattamento (c.d. diritto alla portabilità dei dati personali);</li>
		// 		<li>&bull;	opporsi in qualsiasi momento al trattamento dei propri dati personali al ricorrere di situazioni particolari che ti riguardano;</li>
		// 		<li>&bull;	revocare il consenso in qualsiasi momento, limitatamente alle ipotesi in cui il trattamento sia basato sul tuo consenso per una
		// 			o più specifiche finalità e riguardi dati personali comuni (ad esempio data e luogo di nascita o luogo di residenza), oppure particolari
		// 			categorie di dati (ad esempio dati che rivelano l’origine razziale, le opinioni politiche, le convinzioni religiose, lo stato di salute
		// 			o la vita sessuale). Il trattamento basato sul consenso ed effettuato antecedentemente alla revoca dello stesso conserva, comunque,
		// 			la sua liceità;</li>
		// 		<li>&bull;	proporre reclamo a un’autorità di controllo (Autorità Garante per la protezione dei dati personali – <b>www.garanteprivacy.it</b>)</li>
		// 		</ul>
		// 	</div>
		// </div>
		// <p>
		// 	I suddetti diritti possono essere esercitati mediante comunicazione scritta da inviare a mezzo posta elettronica,
		// 	p.e.c. o fax, o a mezzo Raccomandata presso la sede dell’Associazione al titolare del trattamento.</p>
		//
		// <p>Si applicano le leggi nazionali in materia.</p>
		// ';
		return $text;
	}
	private function getConsenso(){
		$text =
		'
		<p>&nbsp;</p>
		<h3 class="pb-2 display-5">CONSENSO AL TRATTAMENTO DEI DATI PERSONALI</h3>
		<p>
		Io sottoscritto/a, ____________________________________________, C.F.____________________________ nella qualità di interessato,
		letta la suddetta informativa resa ai sensi dell’art. 13 GDPR, <strong>autorizzo/do il consenso </strong>
		</p>
		<div class="row">
			<div class="col-md-12">
				<ul class="vue-list-inner">
				<li>
					□	al trattamento dei miei dati personali, da svolgersi in conformità a quanto indicato nella suddetta informativa e nel
					rispetto delle disposizioni del GDPR e del D.Lgs. n. 196/03 <b>(*)</b>
				</li>
				<li>&nbsp;</li>
				<li>
					□	<i>alla diffusione del mio nome e cognome, della mia immagine o di video che mi riprendono nel sito istituzionale, nei
					social network (es. pagina Facebook/Instagram/Youtube) e sul materiale informativo cartaceo dell’Associazione, per soli fini di
					descrizione e promozione dell’attività istituzionale, nel rispetto delle disposizioni del GDPR e del D.Lgs. n. 196/03 e delle
					autorizzazioni/indicazioni della Commissione UE e del Garante per la Protezione dei Dati Personali</i> <b>(**)</b>
				</li>
				<li>&nbsp;</li>
				<li>
					□	<i>al ricevimento della newsletter e/o di altre comunicazioni da parte dell’Associazione</i> <b>(**)</b>
				</li>
				</ul>
			</div>
		</div>
		<div class="row">
			<p>
			________________________, lì __________________
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			L’INTERESSATO<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;
			(firma leggibile)</p>
			<p>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;
			_______________________</p>
		</div>
		<div class="row">
			<p><small><b>(*)</b> Il consenso al trattamento è indispensabile ai fini del perseguimento delle finalità associative e quindi la mancata autorizzazione comporta l’impossibilità di perfezionare l’adesione o il mantenimento della qualifica di socio
			</small></p>
			<p><small><b>(**)</b> Il consenso al trattamento è facoltativo</small></p>
		</div>
		';
		return $text;
	}
	private function getInformativaCookies(){
		$text =
		'
		<div id="c557" class="csc-default"><div class="csc-header csc-header-n1"><h1 class="csc-firstHeader">Informativa sui Cookies</h1></div><div class="csc-textpic-text"><p class="bodytext">L’utilizzo di questo sito Web (e di ogni altro sito gestito da noi) comporta l’accettazione, da parte dell’utente, del ricorso ai cookies (e tecnologie analoghe) conformemente alla presente informativa. In particolare, si accetta l’uso dei cookies di analisi, pubblicità, marketing e profilazione, funzionalità per gli scopi di seguito descritti. Se l’utente accetta i cookies di profilazione, come più oltre identificati, questi saranno aggiunti al proprio profilo già esistente nelle nostre banche dati, qualora l’utente abbia prestato il consenso ad attività di marketing e profilazione in occasione della sua adesione a una o più delle iniziative condotte dal nostro sito.
</p>
<p class="bodytext">Qualora l’utente non intenda accettare l’uso dei cookies per finalità di analisi, pubblicità, profilazione, ha la possibilità di modificare l’impostazione del proprio browser secondo le modalità descritte nella sezione “Come controllare ed eliminare i cookies”.
</p>
<p class="bodytext"><b>Cosa sono i cookies</b>
</p>
<p class="bodytext">I cookies sono informazioni salvate sul disco fisso del terminale dell’utente e che sono inviate dal browser dell’utente a un Web server e che si riferiscono all’utilizzo della rete. Di conseguenza, permettono di conoscere i servizi, i siti frequentati e le opzioni che, navigando in rete, sono state manifestate. In altri termini, i cookies sono piccoli file, contenenti lettere e numeri, che vengono scaricati sul computer o dispositivo mobile dell’utente quando si visita un sito Web. I cookies vengono poi re-inviati al sito originario a ogni visita successiva, o a un altro sito Web che riconosce questi cookies. I cookies sono utili poiché consentono a un sito Web di riconoscere il dispositivo dell’utente. Queste informazioni non sono, quindi, fornite spontaneamente e direttamente, ma lasciano traccia della navigazione in rete da parte dell’utente.
</p>
<p class="bodytext">I cookies svolgono diverse funzioni e consentono di navigare tra le pagine in modo efficiente, ricordando le preferenze dell’utente e, più in generale, migliorando la sua esperienza. Possono anche contribuire a garantire che le pubblicità mostrate durante la navigazione siano di suo interesse e che le attività di marketing realizzate siano conformi alle sue preferenze, evitando iniziative promozionali sgradite o non in linea con le esigenze dell’utente.
</p>
<p class="bodytext">Durante la navigazione su di un sito, l’utente può ricevere sul suo terminale anche cookies che sono inviati da siti o da web server diversi (c.d. “terze parti”), sui quali possono risiedere alcuni elementi (es.: immagini, mappe, suoni, specifici link a pagine di altri domini) presenti sul sito che lo stesso sta visitando. Possono esserci, quindi, sotto questo profilo:</p><ol><li>Cookies diretti, inviati direttamente da questo sito al dispositivo dell’utente</li> <li>Cookies di terze parti, provenienti da una terza parte ma inviati per nostro conto. Il presente sito usa i cookies di terze parti per agevolare l’analisi del nostro sito Web e del loro utilizzo, e per consentire pubblicità mirate (come descritto di seguito)</li></ol><p class="bodytext">Inoltre, in funzione della finalità di utilizzazione dei cookies, questi si possono distinguere in:</p><ol><li>Cookies tecnici: sono utilizzati per permettere la trasmissione di una comunicazione su una rete di comunicazione elettronica o per erogare un servizio espressamente richiesto dall’utente. Non sono utilizzati per altre finalità. Nella categoria dei cookies tecnici, la cui utilizzazione non richiede il consenso dell’utente, si distinguono:<p style="margin-left:72.0pt; text-indent:-18.0pt">a.<span style="font-stretch: normal; font-size: 7pt; font-family: \'Times New Roman\'; ">&nbsp;&nbsp;&nbsp; </span>Cookies di navigazione o di sessione: garantiscono la normale navigazione e fruizione del sito Web (es.: per autenticarsi ed accedere alle aree riservate). L’uso questi cookies (che non sono memorizzati in modo persistente sul dispositivo dell’utente e sono automaticamente eliminati con la chiusura del browser) è strettamente limitato alla trasmissione d’identificativi di sessione (costituiti da numeri casuali generati dal server) necessari per consentire l’esplorazione sicura ed efficiente del sito.</p> <p style="margin-left:72.0pt; text-indent:-18.0pt">b.<span style="font-stretch: normal; font-size: 7pt; font-family: \'Times New Roman\'; ">&nbsp;&nbsp;&nbsp;&nbsp; </span>Cookies analytics: sono utilizzati soltanto per raccogliere informazioni in forma aggregata, sul numero di utenti che visitano il sito e come lo visitano</p> <p style="margin-left:72.0pt; text-indent:-18.0pt">c.<span style="font-stretch: normal; font-size: 7pt; font-family: \'Times New Roman\'; ">&nbsp;&nbsp;&nbsp;&nbsp; </span>Cookies di funzionalità: permettono all’utente di navigare in base a una serie di criteri selezionati (es.: lingua, prodotti o servizi acquistati) per migliorare il servizio reso all’utente.</p></li> <li>Cookies di profilazione: sono volti a creare profili relativi all’utente e utilizzati per inviare messaggi promozionali mirati in funzione delle preferenze manifestate durante la sua navigazione in rete. Questi cookies sono utilizzabili per tali scopi soltanto con il consenso dell’utente.</li></ol><p class="bodytext"><b>Come sono utilizzati i cookies da questo sito</b>
</p>
<p class="bodytext">La tabella seguente riassume il modo in cui questo sito e le terzi parti utilizzano i cookies. Questi usi comprendono il ricorso ai cookies per:</p><ul><li>conoscere il numero totale di visitatori in modo continuativo oltre ai tipi di browser (ad es. Firefox, Safari o Internet Explorer) e sistemi operativi (ad es. Windows o Macintosh) utilizzati;</li> <li>monitorare le prestazioni del sito, incluso il modo in cui i visitatori lo utilizzano, nonché per migliorarne le funzionalità;</li> <li>personalizzare e migliorare l’esperienza utente on-line;</li> <li>consentire la profilazione dell’utente, per scopi promozionali mirati e personalizzati in funzione delle preferenze ed interessi manifestati in rete dall’utente, da nostra parte e di terze parti, sia su questo sito che al di fuori dello stesso.</li></ul><p class="bodytext"><b>Quali categorie di cookies utilizziamo e come possono essere gestiti dall’utente</b>
</p>
<p class="bodytext">I tipi di cookie utilizzati possono essere classificati in una delle categorie, di seguito riportate nella tabella seguente, che permette all’utente di prestare il proprio consenso o modificarlo (se precedentemente già espresso) per accettare i vari tipi di cookies:</p><table border="1" cellpadding="0" cellspacing="0" height="1941" width="657" style="border-collapse: collapse; border: medium none;" class="contenttable"> <tbody><tr> <td valign="top" width="163" style="width:122.2pt; border:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Tipo di cookie</p></td> <td valign="top" width="317" style="width:237.6pt; border:solid windowtext 1.0pt; border-left:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Finalità del cookie</p></td> <td valign="top" width="85" style="width:63.8pt; border:solid windowtext 1.0pt; border-left:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Origine</p></td> <td valign="top" width="87" style="width:65.3pt; border:solid windowtext 1.0pt; border-left:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Azione / Link</p></td> </tr> <tr> <td valign="top" width="163" style="width:122.2pt; border:solid windowtext 1.0pt; border-top:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Cookie essenziali del sito web (di sessione e di navigazione)</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="317" style="width:237.6pt; border-top:none; border-left: none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Questi cookie sono essenziali per una corretta navigazione e per fruire delle funzioni del sito. Questi cookies non raccolgono informazioni personali utilizzabili a scopo di marketing, né memorizzano i siti visitati. L’uso questi cookies (che non sono memorizzati in modo persistente sul dispositivo dell’utente e sono automaticamente eliminati con la chiusura del browser) è strettamente limitato alla trasmissione d’identificativi di sessione (costituiti da numeri casuali generati dal server) necessari per consentire l’esplorazione sicura ed efficiente del sito. Questa categoria di cookies non può essere disattivata.</p></td> <td valign="top" width="85" style="width:63.8pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">(cookie diretti)</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="87" style="width:65.3pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">&nbsp;</p></td> </tr> <tr> <td valign="top" width="163" style="width:122.2pt; border:solid windowtext 1.0pt; border-top:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Cookie di funzionalità</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="317" style="width:237.6pt; border-top:none; border-left: none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Questi cookies ci consentono di ricordare le scelte effettuate (come il nome utente, la lingua o la posizione geografica) e forniscono funzioni migliorate e personalizzate. Le informazioni raccolte da tali cookies possono essere rese anonime e non tengono traccia dell’attività di navigazione su altri siti web. Questa categoria di cookies non può essere disattivata.</p></td> <td valign="top" width="85" style="width:63.8pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">(cookie diretti)</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="87" style="width:65.3pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">&nbsp;</p></td> </tr> <tr> <td valign="top" width="163" style="width:122.2pt; border:solid windowtext 1.0pt; border-top:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Cookie Analytics</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="317" style="width:237.6pt; border-top:none; border-left: none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Questi cookies, anche i cache cookies, sono impostati da Google Analytics, sono impiegati al fine di raccogliere informazioni sul modo in cui i visitatori utilizzano il sito, ivi compreso il numero di visitatori, i siti di provenienza e le pagine visitate sul nostro sito web. Utilizziamo queste informazioni per compilare rapporti e per migliorare il nostro sito web; questo ci consente, ad esempio, di conoscere eventuali errori rilevati dagli utenti e di assicurare loro una navigazione immediata, per trovare facilmente quello che cercano. In generale, questi cookie restano sul computer del visitatore fino a quando non vengono eliminati.</p></td> <td valign="top" width="85" style="width:63.8pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext"><span lang="EN-US">Google Analytics </span></p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="87" style="width:65.3pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext"><a href="http://www.google.com/policies/" target="_blank"><span lang="EN-US">privacy policy cookie</span></a></p></td> </tr> <tr> <td valign="top" width="163" style="width:122.2pt; border:solid windowtext 1.0pt; border-top:none; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Cookie pubblicitari o di profilazione</p> <p style="margin-bottom: 0.0001pt; ">&nbsp;</p></td> <td valign="top" width="317" style="width:237.6pt; border-top:none; border-left: none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Questi cookies sono utilizzati per raccogliere informazioni sui siti web e sulle singole pagine visitate dagli utenti, sia sul nostro sito che al di fuori dello stesso (il che potrebbe indicare, a titolo esemplificativo, interessi o altre caratteristiche degli utenti). Tali cookies sono utilizzati per fornire pubblicità e comunicazioni commerciali più pertinenti agli interessi dell’utente, profilandolo. Sono utilizzati, inoltre, per limitare il numero di volte in cui una pubblicità è mostrata e per misurare l’efficacia delle campagne pubblicitarie. Restano sul computer dell’utente fino alla loro eliminazione e fanno da promemoria dell’avvenuta visita del sito web. Inoltre, essi possono indicare se l’utente è arrivato sul nostro sito web attraverso un link pubblicitario.</p> <p style="margin-bottom: 0.0001pt; ">Queste informazioni vengono condivise con altre organizzazioni come gli inserzionisti e i nostri appaltatori, i quali potrebbero utilizzarle unitamente alle informazioni relative alle modalità di fruizione di altri siti web dell’utente stesso, ad esempio individuando interessi e comportamenti comuni a diversi gruppi di utenti che visitano i nostri (e altri) siti web.</p></td> <td valign="top" width="85" style="width:63.8pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext">Adform</p><p style="margin-bottom: 0.0001pt; "></p><p style="margin-bottom: 0.0001pt; ">HasOffers by TUNE</p></td> <td valign="top" width="87" style="width:65.3pt; border-top:none; border-left:none; border-bottom:solid windowtext 1.0pt; border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt"><p style="margin-bottom: 0.0001pt; " class="bodytext"><b><a href="http://site.adform.com/privacy-policy/en/" target="_blank">Privacy policy Adform</a></b></p><p style="margin-bottom: 0.0001pt; "><b><a href="https://www.hasoffers.com/privacy-policy/" title="Privacy Policy HasOffers" target="_blank" class="external-link-new-window">Privacy policy HasOffers</a></b></p></td> </tr> </tbody></table><p class="bodytext">&nbsp;</p>
<p class="bodytext"><b>Remarketing e behavioral targeting</b>
</p>
<p class="bodytext">Questo tipo di servizi consente a questa Applicazione ed ai suoi partner di comunicare, ottimizzare e servire annunci pubblicitari basati sull’utilizzo passato di questa Applicazione da parte dell’Utente.<br>Questa attività viene effettuata tramite il tracciamento dei Dati di Utilizzo e l’uso di Cookie, informazioni che vengono trasferite ai partner a cui l’attività di remarketing e behavioral targeting è collegata.<br><br><i><b>AdWords Remarketing (Google Inc.)</b></i><br>AdWords Remarketing è un servizio di remarketing e behavioral targeting fornito da Google Inc. che collega l’attività di questa Applicazione con il network di advertising Adwords ed il Cookie Doubleclick.<br>Dati Personali raccolti: Cookie e Dati di utilizzo. <br>Luogo del trattamento: USA – <b><a href="http://www.google.com/intl/it/policies/privacy/" title="Privacy Policy" target="_blank" class="external-link-new-window">Privacy Policy</a></b> – <b><a href="https://www.google.com/settings/ads/onweb/optout" title="Privacy" target="_blank" class="external-link-new-window">Opt Out</a></b>
</p>

<p class="bodytext">Come nel caso di molti siti web, utilizziamo tecnologie analoghe ai cookies, compresi gli oggetti condivisi locali (anche noti come “flash cookie”), l’analisi della cronologia del browser, le impronte digitali dei browser e i pixel tag (anche noti come “web beacon”). Queste tecnologie forniscono a noi e ai nostri fornitori informazioni su come il sito e il suo contenuto sono utilizzati dai visitatori, e ci consentono di identificare se il computer o il dispositivo ha visitato i nostri o altri siti in passato.
</p>
<p class="bodytext">I nostri server raccolgono automaticamente l’indirizzo IP dell’utente; in questo modo, possiamo associare tale indirizzo al nome del dominio utente o a quello del suo Internet Provider. Potremmo anche raccogliere alcuni “dati clickstream” relativi all’utilizzo del sito. I dati clickstream includono, ad esempio, informazioni relative al computer o dispositivo utente, al browser e al sistema operativo e relative impostazioni, alla pagina di provenienza, alle pagine e ai contenuti visualizzati o cliccati durante la visita oltre ai tempi e alle modalità di tali operazioni, agli elementi scaricati, al sito visitato successivamente al nostro, e a qualsiasi termine di ricerca inserito su nostro sito o su un sito di reindirizzamento.
</p>
<p class="bodytext"><b>Come controllare o eliminare i cookies</b>
</p>
<p class="bodytext">L’utente ha il diritto di scegliere se accettare o meno i cookies. Nel seguito alcune informazioni su come esercitare tale diritto. Tuttavia, si ricorda che scegliendo di rifiutare i cookies, l’utente potrebbe non essere in grado di utilizzare tutte le funzionalità del sito e, se ha prestato consenso alla profilazione in altre occasioni, le comunicazioni che riceverà potrebbero essere poco pertinenti ai suoi interessi. Si evidenzia, inoltre, che se si sceglie di gestire le preferenze dei cookies, il nostro strumento di gestione dei cookies imposterà comunque un cookie tecnico sul dispositivo per consentirci di dare esecuzione alle preferenze dell’utente. L’eliminazione o la rimozione dei cookies dal dispositivo utente comporta anche la rimozione del cookie relativo alle preferenze; tali scelte dovranno, pertanto, essere nuovamente effettuate.
</p>
<p class="bodytext">È possibile bloccare i cookie usando i link di selezione presenti nella tabella sopra riportata. In alternativa, è possibile modificare le impostazioni del browser affinché i cookies non possano essere memorizzati sul dispositivo. A tal fine, occorre seguire le istruzioni fornite dal browser (in genere si trovano nei menu “Aiuto”, “Strumenti o “Modifica”). La disattivazione di un cookie o di una categoria di cookies non li elimina dal browser. Pertanto, tale operazione dovrà essere effettuata direttamente nel browser.
</p>
<p class="bodytext">Per ulteriori informazioni sui cookie, anche su come visualizzare quelli che sono stati impostati sul dispositivo, su come gestirli ed eliminarli, visitare <a href="http://www.garanteprivacy.it/" title="Garante Privacy" target="_blank" class="external-link-new-window">www.garanteprivacy.it</a> e <a href="http://www.youronlinechoices.com/it/" title="Your online choices" target="_blank" class="external-link-new-window">www.youronlinechoices.com/it/</a>
</p>
<p class="bodytext"><b>Cookie impostati precedentemente</b>
</p>
<p class="bodytext">Se l’utente ha disabilitato uno o più cookies, saremo comunque in grado di utilizzare le informazioni raccolte prima di tale disabilitazione effettuata mediante le preferenze. Tuttavia, a partire da tale momento, cesseremo di utilizzare i cookies disabilitati per raccogliere ulteriori informazioni.</p></div></div>
		';
		return $text;
	}


}
