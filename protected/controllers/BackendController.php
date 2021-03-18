<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.Notifi');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.webRequest.webRequest');

class BackendController extends Controller
{
	public function init()
	{
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
			Yii::app()->user->logout();
			$this->redirect('site/index');
		}
	}

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
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
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array(
					'notify', //visualizzo le notifiche
					'updateNews', // aggiorno i messaggi cliccati da 0 a 1 (unread -> read)
					'updateAllNews', // aggiorno tutti i messaggi  da 0 a 1 (unread -> read)
					'checkInvoices',
					'getExchangeBalance',
					'ajaxSelectComune', //crea una lista con gli elenchi provinciali e comuni dal DB
					'SelectComuneByCap', //crea una lista con gli elenchi provinciali e comuni dal internet
					'checkSingleInvoice', // verifica lo stato di una singola transazione
					'checkSinglePayment', // verifica lo stato di una singolo pagamento su btcpayserver
				),
				'users'=>array('@'),
			),
		);
	}

	// aggiorna tutte le notifiche in "letta"
	// update all rows
	public function actionUpdateAllNews(){
		$updateAll = Yii::app()->db->createCommand(
    					"UPDATE np_notifications_readers nr
        				SET nr.alreadyread = 1
        				WHERE nr.id_user = " . Yii::app()->user->objUser['id_user'] . ";"
            		)->execute();

		 //
		 // echo "<pre>".print_r($updateAll,true)."</pre>";
		 // exit;
		echo CJSON::encode(['success'=>true],true);
	}


	public function actionUpdateNews(){
		$model = Notifications_readers::model()->findByAttributes([
			'id_user'=> Yii::app()->user->objUser['id_user'],
			'id_notification' => $_POST['id_notification'],
		]);
		if (null !== $model){
			$model->alreadyread = 1;
			$model->update();
		}
		echo CJSON::encode(['success'=>true],true);
	}


	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionNotify()
	{
		// echo "<pre>".print_r(Yii::app()->user->objUser['id_user'],true)."</pre>";
		// exit;
		$response['countedRead'] = 0;
		$response['countedUnread'] = 0;
		$response['htmlTitle'] = '';
		$response['htmlContent'] = ''; // ex content
		$response['playSound'] = false;

		$criteria = new CDbCriteria();
		$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		$newsReaders = Notifications_readers::model()->orderById()->findAll($criteria);

		$arrayCondition = array();
		$news = array();
		foreach ($newsReaders as $key => $item) {
			$notify = Notifications::model()->findByPk($item->id_notification);
			if ($notify->type_notification <> 'token'){
				$arrayCondition[] = $item->id_notification;
				($item->alreadyread == 0 ? $response['countedUnread'] ++ : $response['countedRead'] ++);
				$news[] = $item;
			}

		}
		// echo "<pre>".print_r($arrayCondition,true)."</pre>";
		// exit;

		$x=1;
		foreach ($news as $key => $item) {
			// echo "<pre>".print_r($notify,true)."</pre>";
			// exit;
			// Leggo la notifica tramite key
			$notify = Notifications::model()->findByPk($item->id_notification);
			if ($x == 1){
				$response['htmlTitle'] .= '<div class="notifi__title">';
				if ($response['countedUnread']>0){
					$response['htmlTitle'] .= '<p>' . Yii::t('lang','You have {n} unread message.|You have {n} unread messages.',$response['countedUnread']) . '</p>';
				}else{
					$response['htmlTitle'] .= '<p>' . Yii::t('lang','You have read all messages.') . '</p>';
				}
				$response['htmlTitle'] .= '</div>';
			}

			$notifi__icon = Notifi::Icon(
				(strpos($notify->description,'token') !== false ? 'token' : $notify->type_notification )
			);
			$notifi__color = Notifi::Color($notify->status);

			$response['htmlContent'] .= '
			<a href="'.htmlentities($notify->url).'" id="news_'.$notify->id_notification.'">
			<div class="notifi__item">
			<div class="'.$notifi__color.' img-cir img-40">
			<i class="'.$notifi__icon.'"></i>
			</div>
			<div class="content">
			<div onclick="backend.openEnvelope('.$notify->id_notification.');" >';
			if ($item->alreadyread == 0){
				$response['htmlContent'] .= '<p style="font-weight:bold;">';
			}else{
				$response['htmlContent'] .= '<p>';
			}

			$response['htmlContent'] .= WebApp::translateMSG($notify->description);
			$response['htmlContent'] .= '</p>';

			// se il tipo notifica è help o contact ovviamente non mostro il prezzo della transazione
			if ($notify->type_notification <> 'help' && $notify->type_notification <> 'contact'){
				$response['htmlContent'] .= '<p class="text-success">'.$notify->price.'</p>';
				//VERIFICO QUESTE ULTIME 3 TRANSAZIONI PER AGGIORNARE IN REAL-TIME LO STATO (IN CASO CI SI TROVA SULLA PAGINA TRANSACTIONS)
				$response['status'][$notify->id_tocheck] = $notify->status;
			}
			$response['htmlContent'] .= '
			<span class="date text-primary">'.date('d M Y - H:i:s',$notify->timestamp).'</span>
			</div>
			</div>
			</div>
			</a>
			';

			$x++;
			if ($x>3)
				break;
		}
		if ($response['countedRead'] == 0 && $response['countedUnread'] == 0){
			$response['htmlContent'] .= '<div class="notifi__title">';
			$response['htmlContent'] .= '<p>' . Yii::t('lang','You have no messages to read.') . '</p>';
			$response['htmlContent'] .= '</div>';
		}else{
			$response['htmlContent'] .= '
				<div class="notifi__footer">
					<a id="seeAllMessages" onclick="backend.openAllEnvelopes();" href="'.htmlentities(Yii::app()->createUrl('notifications/index')).'">'.Yii::t('lang','See all messages').'</a>
				</div>
			';
		}
		$response['test_basePath'] = Yii::app()->basePath;
		$response['test_DOCUMENT_ROOT'] = $_SERVER["DOCUMENT_ROOT"];

		echo CJSON::encode($response,true);

	}

	/**
     * Carica lista privince e comuni dal DB
     */
    public function actionAjaxSelectComune($id) {
		$criteria=new CDbCriteria();
		$criteria->compare('sigla',$id,false);

		$listaComuni = CHtml::listData(ComuniItaliani::model()->bycity()->findAll($criteria), 'id_comune', function($descrizione) {
		     return CHtml::encode(str_replace("'","`",$descrizione->citta).' ('.$descrizione->sigla.')');
		});
		// echo '<pre>'.print_r($listaComuni,true).'</pre>';
		// exit;
		echo CJSON::encode($listaComuni);
    }

	/**
     * Carica lista province e comuni dal CAP attraverso una chiamata curl
     */
    public function actionSelectComuneByCap($cap) {
		$result = ['success'=>0];

		//check session variables
		if (!(Yii::app()->user->hasState("comuni"))){
			$url = 'https://raw.githubusercontent.com/matteocontrini/comuni-json/master/comuni.json';
			$json = webRequest::request($url,$url,[],"GET");
			//set session variable
			Yii::app()->user->setState("comuni", $json);
		}else{
			//get session variable
			$json = Yii::app()->user->getState("comuni");
		}

		$array = CJSON::decode($json);

		foreach ($array as $id => $item){
			//$provincia[$item['sigla']] = '(' . $item['provincia']['nome'] . ')';
			foreach ($item['cap'] as $val)
				$codicipostali[$val] = $item['sigla'];
        }
		if (array_key_exists($val,$codicipostali))
			$result = ['success'=>1,'sigla'=>$codicipostali[$cap]];

		echo CJSON::encode($result);
    }


	public function actionCheckInvoices()
	{
		$response['success'] = 0;
		if (Yii::app()->user->objUser['privilegi'] >5){
			$criteria=new CDbCriteria();
			$adesso_piu_5_minuti = time() - (5*60);
			//	controllo solo le new che magari non si sono aggiornate ancora
			//	controllo solo le transazioni scadute da oltre 5 minuti oltre al tempo di scadenza fattura
			$criteria->compare('status','new',false);
			$criteria->addCondition('\'expiration_timestamp\' > \''.$adesso_piu_5_minuti.'\'','AND'); // il simbolo è minore
			$dataProvider=new CActiveDataProvider('Transactions', array(
			    'criteria'=>$criteria,
			));

			$iterator = new CDataProviderIterator($dataProvider);
			foreach($iterator as $item) {
				//dalla transazione cerco il pos per vedere se esiste la chiave privata
				$pos=Pos::model()->findByPk($item->id_pos);
				if ($pos !== null){
					//dal $pos-id cerco il file della chiave privata
					$privatekeyFile = Yii::app()->basePath . '/privatekeys/btcpay-'.$item->id_pos.'.pri';
					if (true === file_exists($privatekeyFile)){
						//dalla transazione cerco il merchant
						$merchants=Merchants::model()->findByPk($item->id_merchant);
						if ($merchants !== null){
							//dal merchants cerco il settings
							//$settings=Settings::model()->findByAttributes(array('id_user'=>$merchants->id_user));
							$settings=Settings::loadUser($merchants->id_user);
							// dal settings cerco il gateway
							// se il gateway non è impostato assegno 1 (btcpayserver)
							$gateways=Gateways::model()->findByPk((isset($settings->id_gateway) ? $settings->id_gateway : 1));
							#echo '<br>'.$gateways->action_controller;
							$response['success'] = 1;

							switch (strtolower($gateways->action_controller)){
								case 'coingate':
									$this->checkCoingateInvoice($item);
									break;
								case 'bitpay':
									$this->checkBitpayInvoice($item);
									break;
								case 'btcpayserver':
									$this->checkBtcpayserverInvoice($item);
									break;
							}
						}else{
							$response['message'] = 'id Merchant ('.$item->id_merchant.') non trovato!';
						}
					}else{
						$response['message'] = 'private key ('.$item->id_pos.') non trovato!';
					}
				}else{
					$response['message'] = 'id Pos ('.$item->id_pos.') non trovato!';
				}
			}

		}
		echo CJSON::encode($response,true);
	}
	/*
	 * Verifica il pagamento di una singola transazione che non è nello stato "complete"
	 * Viene attivata premendo il pulsante "in corso..." nella finestra dettagli Transazione
	 */

	public function actionCheckSinglePayment($id)
	{
		$response['success'] = 0;
		$item = Pagamenti::model()->findByPk(crypt::Decrypt($id));

		if ($item->id_invoice_bps == ''){
			$response['message'] = 'id Invoice ('.$item->id_pagamento.') non trovato!';
		}else{
			// id pos dell'associazione è sempre 0
			$id_pos = 0 ;

			$folder = Yii::app()->basePath . '/privatekeys/';
			$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$id_pos));
				// echo '<pre>'.print_r($folder.$pairings->sin,true).'</pre>';
				// exit;

			$privatekeyFile = $folder.$pairings->sin.".pri";
			if (true === file_exists($privatekeyFile)){
				$this->checkBtcpayserverPayment($item);
			}else{
				$response['message'] = 'The requested Private Key does not exist on this Server!';
			}

		}

		echo CJSON::encode($response,true);
	}
	private function checkBtcpayserverPayment($item)
	{
		$settings=Settings::load();
		if($settings===null){
			throw new \Exception("Error. The requested Settings does not exist.");
		}
		// echo '<pre>'.print_r($settings,true).'</pre>';
		// exit;

		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		// Effettuo il login senza dati
		$BTCPay = new BTCPay(null,null);

		// imposto l'url che è associato al merchant dell'associazione...
		$BTCPay->setBTCPayUrl($settings->blockchainAddress);

		// Carico l'URL del Server BTC direttamente dalla CLASSE
		$BPSUrl = $BTCPay->getBTCPayUrl();

		/**
		*	AUTOLOADER GATEWAYS
		*/
		$btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';

		if (true === file_exists($btcpay) &&
			true === is_readable($btcpay))
		{
			require_once $btcpay;
			\Btcpay\Autoloader::register();
		} else {
			throw new Exception('Btcpay Server Library could not be loaded');
		}


		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */

		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>0));
		if($pairings===null){
			throw new \Exception('Error. The requested Pairings does not exist.');
		}

		$folder = Yii::app()->basePath . '/privatekeys/';

		//verifico esistenza coppia chiavi public/private
		if (!file_exists($folder.$pairings->sin.".pub") ){
			throw new \Exception('Error. The requested Public key does not exist.');
		}

		//verifico esistenza coppia chiavi public/private
		if (!file_exists($folder.$pairings->sin.".pri") ){
			throw new \Exception('Error. The requested Private key does not exist.');
		}
		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));

		$publicKey     = $storageEngine->load($folder.$pairings->sin.'.pub');
		$privateKey    = $storageEngine->load($folder.$pairings->sin.'.pri');

		try {
			// Now fetch the invoice from Btcpay
			// This is needed, since the IPN does not contain any authentication
			$client        = new \Btcpay\Client\Client();
			$adapter       = new \Btcpay\Client\Adapter\CurlAdapter();

			$client->setPrivateKey($privateKey);
			$client->setPublicKey($publicKey);

			$client->setUri($BPSUrl);
			$client->setAdapter($adapter);
			// ---------------------------


			$token = new \Btcpay\Token();
			$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
			$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
			$client->setToken($token);

			$pagamenti = Pagamenti::model()->findByPk($item->id_pagamento);
			$StatoInizialeDellaTransazione = $pagamenti->status;
			/**
			* This is where we will fetch the invoice object
			*/
			$invoice = $client->getInvoice($pagamenti->id_invoice_bps);
			if ($invoice){
				//A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO solo se gli status sono DIVERSI !!!!
				if ($invoice->getStatus()=='complete' && $StatoInizialeDellaTransazione <> 'paid'){
					$pagamenti->importo = $invoice->getPrice();
					$pagamenti->status = 'paid';

					$yearEnd = date('Y-m-d', strtotime('last day of december'));
					$pagamenti->data_scadenza	= $yearEnd;

					// quindi aggiorno il progressivo dei pagamenti
					if ($settings->progressivo_ricevute_anno == $pagamenti->anno){
						$settings->progressivo_ricevute_pagamenti ++;
						Settings::save($settings,array('progressivo_ricevute_pagamenti'));
						$pagamenti->progressivo = $settings->progressivo_ricevute_pagamenti;
					}else{
						$settings->progressivo_ricevute_anno = date('Y',time());
						$settings->progressivo_ricevute_pagamenti = 1;
						Settings::save($settings,array('progressivo_ricevute_pagamenti','progressivo_ricevute_anno'));
						$pagamenti->anno = date('Y',time());
						$pagamenti->progressivo = 1;
					}
					//QUINDI INVIO UN MESSAGGIO DI NOTIFICA,
					$this->notifyMail($pagamenti->id_user,$pagamenti->id_invoice_bps);

				}
				$pagamenti->update();

			}
		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}

	}
// invia mail agli admin dell'avvenuta ricezione tramite ipn dello status dell'invoice dell'iscrizione
 private function notifyMail($id_user,$ipn_id){
		 //cerco tutti gli admin per inoltrare la mail di nuova iscrizione
		 $criteria=new CDbCriteria();
		 $criteria->compare('id_users_type',3,false);
		 $admins = Users::model()->findAll($criteria);

		 $listaAdmins = CHtml::listData($admins,'id_user' , 'email');
		 foreach ($listaAdmins as $id => $adminEmail)
				 NMail::SendMail('subscriptionAdmin',crypt::Encrypt($id_user),$adminEmail,$ipn_id);

 }

	/*
	 * Verifica il pagamento di una singola transazione che non è nello stato "complete"
	 * Viene attivata premendo il pulsante "in corso..." nella finestra dettagli Transazione
	 */

	public function actionCheckSingleInvoice($id)
	{
		$response['success'] = 0;

		$item = Transactions::model()->findByPk(crypt::Decrypt($id));

		if ($item->id_invoice_bps == ''){
			$response['message'] = 'id Invoice ('.$item->id_pos.') non trovato!';
		}else{
			//dalla transazione cerco il pos per vedere se esiste la chiave privata
			// se id_pos == -1 allora vuol dire che ho usato il SelfPOS
			if ($item->id_pos == -1){
				$shop = Shops::model()->findByPk($item->item_code);
				if($shop!==null){
					// CERCO UN POS QUALUNQUE PER CARICARE LA PRIVATE E PUBLIC KEY TRAMITE id_store
				    $pos = Pos::model()->findByAttributes(array('id_store'=>$shop->id_store,'deleted'=>0));
				}
			}else{
				$pos=Pos::model()->findByPk($item->id_pos);
			}

			if ($pos !== null){
				$folder = Yii::app()->basePath . '/privatekeys/';
				$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$pos->id_pos));
				// echo '<pre>'.print_r($folder.$pairings->sin,true).'</pre>';
				// exit;

				$privatekeyFile = $folder.$pairings->sin.".pri";
				if (true === file_exists($privatekeyFile)){
					//dalla transazione cerco il merchant
					$merchants=Merchants::model()->findByPk($item->id_merchant);
					if ($merchants !== null){
						//dal merchants cerco il settings
						$settings=Settings::loadUser($merchants->id_user);
						//dal settings cerco il gateway
						$gateways=Gateways::model()->findByPk($settings->id_gateway);
						$response['success'] = 1;

						switch (strtolower($gateways->action_controller)){
							case 'coingate':
								$this->checkCoingateInvoice($item);
								break;
							case 'bitpay':
								$this->checkBitpayInvoice($item);
								break;
							case 'btcpayserver':
								$this->checkBtcpayserverInvoice($item);
								break;
						}
					}else{
						$response['message'] = 'id Merchant ('.$item->id_merchant.') non trovato!';
					}
				}else{
					$response['message'] = 'The requested Private Key does not exist on this Server!';
				}
			}else{
				$response['message'] = 'id Pos ('.$item->id_pos.') non trovato!';
			}
		}

		echo CJSON::encode($response,true);
	}

	private function checkBtcpayserverInvoice($item)
	{
		// carico l'estensione
		//require_once Yii::app()->params['libsPath'] . '/BTCPay/BTCPay.php';
		Yii::import('libs.BTCPay.BTCPayWebRequest');
		Yii::import('libs.BTCPay.BTCPay');

		// Effettuo il login senza dati
		$BTCPay = new BTCPay(null,null);
		// imposto l'url che è associato a ciascun merchant
		$merchants = Merchants::model()->findByPk($item->id_merchant);
		$BTCPay->setBTCPayUrl(Settings::loadUser($merchants->id_user)->blockchainAddress);

		// Carico l'URL del Server BTC direttamente dalla CLASSE
		$BPSUrl = $BTCPay->getBTCPayUrl();

		/**
		*	AUTOLOADER GATEWAYS
		*/
		$btcpay = Yii::app()->params['libsPath'] . '/gateways/btcpayserver-php-v1/Btcpay/Autoloader.php';
		if (true === file_exists($btcpay) &&
				true === is_readable($btcpay))
		{
				require_once $btcpay;
				\Btcpay\Autoloader::register();
		} else {
				throw new Exception('Btcpay Server Library could not be loaded');
		}


		/**
		 * To load up keys that you have previously saved, you need to use the same
		 * storage engine. You also need to tell it the location of the key you want
		 * to load.
		 */
		 $pairing_search = $item->id_pos;

		 if ($item->id_pos == -1){
			 $shop = Shops::model()->findByPk($item->item_code);
			 if($shop!==null){
				 // CERCO UN POS QUALUNQUE PER CARICARE LA PRIVATE E PUBLIC KEY TRAMITE id_store
				 $pos = Pos::model()->findByAttributes(array('id_store'=>$shop->id_store,'deleted'=>0));
				if($pos!==null){
					$pairing_search = $pos->id_pos;
				}
			 }
		 }
		$pairings = Pairings::model()->findByAttributes(array('id_pos'=>$pairing_search));
		if($pairings===null){
			throw new \Exception('Error. The requested Pairings does not exist.');
		}

		$folder = Yii::app()->basePath . '/privatekeys/';

		//verifico esistenza coppia chiavi public/private
		if (!file_exists($folder.$pairings->sin.".pub") ){
			throw new \Exception('Error. The requested Public key does not exist.');
		}

		//verifico esistenza coppia chiavi public/private
		if (!file_exists($folder.$pairings->sin.".pri") ){
			throw new \Exception('Error. The requested Private key does not exist.');
		}

		$settings=Settings::load();  //$settings = SettingsNapos::model()->findByPk(1);
		if($settings===null){
			throw new \Exception("Error. The requested Settings does not exist.");
		}

		$storageEngine = new \Btcpay\Storage\EncryptedFilesystemStorage(crypt::Decrypt($settings->fileSystemStorageKey));

		$publicKey     = $storageEngine->load($folder.$pairings->sin.'.pub');
		$privateKey    = $storageEngine->load($folder.$pairings->sin.'.pri');

		try {
			// Now fetch the invoice from Btcpay
			// This is needed, since the IPN does not contain any authentication
			$client        = new \Btcpay\Client\Client();
			$adapter       = new \Btcpay\Client\Adapter\CurlAdapter();

			$client->setPrivateKey($privateKey);
			$client->setPublicKey($publicKey);
			$client->setUri($BPSUrl);
			$client->setAdapter($adapter);
			// ---------------------------


			$token = new \Btcpay\Token();
			$token->setToken(crypt::Decrypt($pairings->token)); // UPDATE THIS VALUE
			$token->setFacade('merchant'); //IMPORTANTE PER RECUPERARE LO STATO DELLE FATTURE DA BTCPAYSERVER :::SERGIO CASIZZONE
			$client->setToken($token);


			/**
			* This is where we will fetch the invoice object
			*/
			$invoice = $client->getInvoice($item->id_invoice_bps);
			if ($invoice){
				// $invoice['Id'] 		= $invoice['Obj']->getId();			//per recuperare l'invoice dall'archivio
				// $invoice['Status'] 	= $invoice['Obj']->getStatus(); 	//per impostare il nuovo stato
				// $invoice['btcPaid'] = $invoice['Obj']->getBtcPaid();	//per impostare quanto è stato pagato
				// $invoice['btcPrice']= $invoice['Obj']->getBtcPrice();	//per impostare quanto era il dovuto da pagare
				// // !!! IMPORTANTE !!!
				// $invoice['Price']= $invoice['Obj']->getPrice();	//per impostare quanto era il dovuto da pagare in moneta fiat

				//A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO solo se gli status sono DIVERSI !!!!
				if ($item->status <> $invoice->getStatus()){
					$transactions = Transactions::model()->findByPk($item->id_transaction);
					$transactions->status = $invoice->getStatus();
					$transactions->btc_paid = $invoice->getBtcPaid();
					$transactions->update();

					// aggiorno la transactions_info
					$cryptoInfo = Save::CryptoInfo($transactions->id_transaction, $invoice->getCryptoInfo());

					//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
				    //salva la notifica
				    $notification = array(
					   'type_notification' => 'invoice',
					   'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
					   'id_tocheck' => $transactions->id_transaction,
					   'status' => $invoice->getStatus(),
					   'description' => Notifi::description($invoice->getStatus(),'invoice'),
					   // 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
					   // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
		   				'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
					   'timestamp' => time(),
					   'price' => $invoice->getPrice(),
					   'deleted' => 0,
				    );
				    // NaPay::save_notification($notification);
					$save = new Save;
					Push::Send($save->Notification($notification,true),'dashboard');
				}
			}
		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}

	}

	/*TODO: FUNZIONE DA modificare secondo la nuova *vision* */

	private function checkBitpayInvoice($item)
	{
		/**
		*	AUTOLOADER GATEWAY
		*/
		$bitpay = Yii::app()->basePath . '/extensions/gateways/bitpay/Bitpay/Autoloader.php';
		if (true === file_exists($bitpay) &&
		    true === is_readable($bitpay))
		{
		    require_once $bitpay;
		    \Bitpay\Autoloader::register();
		} else {
		    throw new Exception('Bitpay Server Library could not be loaded');
		}
		$folder = Yii::app()->basePath . '/privatekeys/bitpay-';
		$storageEngine = new \Bitpay\Storage\EncryptedFilesystemStorage('mnewhdo3yy4yFDASc156MdhshuUYTF5365');
		if (file_exists ($folder.$item->id_pos.'.pri')){
			$privateKey    = $storageEngine->load($folder.$item->id_pos.'.pri');
		}
		if (file_exists ($folder.$item->id_pos.'.pub')){
			$publicKey     = $storageEngine->load($folder.$item->id_pos.'.pub');
		}
		try {
			//Yii::app()->user->setState("agenzia_entrate", Yii::app()->params['agenziaentrate']);
			$client        = new \Bitpay\Client\Client();
			//$network        = new \Bitpay\Network\Testnet();
			$network        = new \Bitpay\Network\Livenet();
			$adapter       = new \Bitpay\Client\Adapter\CurlAdapter();
			$client->setNetwork($network);
			$client->setAdapter($adapter);
			$client->setPrivateKey($privateKey);
			$client->setPublicKey($publicKey);
			//
			$token = new \Bitpay\Token(crypt::Decrypt($item->token));
	   		$client->setToken($token);
			$invoice['Obj'] 	= $client->getInvoice($item->id_invoice_bps);
			//echo '<pre>invoice'.print_r($invoice,true).'</pre>';
			if ($invoice){
				$invoice['Id'] 		= $invoice['Obj']->getId();			//per recuperare l'invoice dall'archivio
				$invoice['Status'] 	= $invoice['Obj']->getStatus(); 	//per impostare il nuovo stato
				$invoice['Price']	= $invoice['Obj']->getPrice();	//per impostare quanto era il dovuto da pagare

				//A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO solo se gli status sono DIVERSI !!!!
				$transactions = Transactions::model()->findByPk($item->id_transaction);

				if ($transactions->status != $invoice['Status']){
					$transactions->status = $invoice['Status'];
					$transactions->update();
					//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
				    //salva la notifica CoinGate
				    $notification = array(
					   'type_notification' => 'invoice',
					   //'id_merchant' => $transactions->id_merchant,
					   'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
					   'id_tocheck' => $transactions->id_transaction,
					   'status' => $invoice['Status'],
					   'description' => ' da '. $transactions->item_desc,
					   // 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
					   // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
					   'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
					   'timestamp' => time(),
					   'price' => $invoice['Price'],
					   'deleted' => 0,
				    );
				    // NaPay::save_notification($notification);
					$save = new Save;
					Push::Send($save->Notification($notification,true),'dashboard');
				}
			}
		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}

	}

	/*TODO: FUNZIONE DA modificare secondo la nuova *vision* */
	private function checkCoingateInvoice($item)
	{
		$token = crypt::Decrypt($item->token);
		//init CoinGate
		WebApp::CoingateInitialize($token);

		try {
			$invoice = \CoinGate\Merchant\Order::find($item->id_invoice_bps);
			#echo '<pre>'.print_r($invoice,true).'</pre>';
			#exit;
			if ($invoice){
				//A QUESTO PUNTO AGGIORNO LA TRANSAZIONE IN ARCHIVIO solo se gli status sono DIVERSI !!!!
				$transactions = Transactions::model()->findByPk($item->id_transaction);

				if ($transactions->status != $invoice->status){
					$transactions->status = $invoice->status;
					if (isset($invoice->pay_amount)){
				 		$transactions->btc_paid = $invoice->pay_amount;
						$transactions->btc_due = $invoice->pay_amount;
						$transactions->total_fee = $invoice->pay_amount - $invoice->receive_amount;
					}
		         	$transactions->btc_price = $invoice->receive_amount;
		          	if (isset($invoice->payment_address))
		          		$transactions->bitcoin_address = $invoice->payment_address;
					else
						$transactions->bitcoin_address = $invoice->payment_url;
					$transactions->update();
					//QUINDI INVIO UN MESSAGGIO DI NOTIFICA
				    //salva la notifica CoinGate
				    $notification = array(
					   'type_notification' => 'invoice',
					   //'id_merchant' => $transactions->id_merchant,
					   'id_user' => Merchants::model()->findByPk($transactions->id_merchant)->id_user,
					   'id_tocheck' => $transactions->id_transaction,
					   'status' => $invoice->status,
					   'description' => ' da '. $transactions->item_desc,
					   // 'url' => Yii::app()->createUrl("transactions/view")."&id=".crypt::Encrypt($transactions->id_transaction),
					   // La URL non deve comprendere l'hostname in quanto deve essere raggiungibile da più applicazioni
					   'url' => 'index.php?r=transactions/view&id='.crypt::Encrypt($transactions->id_transaction),
					   'timestamp' => time(),
					   'price' => $invoice->price_amount,
					   'deleted' => 0,
				    );
				    // NaPay::save_notification($notification);
					$save = new Save;
					Push::Send($save->Notification($notification,true),'dashboard');
				}
			}
		} catch (Exception $e) {
			die(get_class($e) . ': ' . $e->getMessage());
		}

	}

	public function actionGetExchangeBalance(){
		$balance['btc'] = 0;
		$balance['eur'] = 0;

		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] == 20)
			$settings=Settings::load();  //$settings=SettingsNapos::model()->findByPk(1);
		else
			$settings=Settings::loadUser(Yii::app()->user->objUser['id_user']);
			//$settings=Settings::model()->findByAttributes(array('id_user'=>Yii::app()->user->objUser['id_user']));

		#echo "<pre>".print_r($settings,true)."</pre>";
		if (!(isset($settings->only_for_bitstamp_id)))
			$settings->only_for_bitstamp_id = 0;

		#exit;
		if (	isset($settings->exchange_key)
			&&  isset($settings->exchange_secret)
			&&  $settings->exchange_key <> ''
			&&  $settings->exchange_secret <> ''
		){
			switch ($settings->id_exchange){
				case 1:	//Bitstamp
					$api = new Bitstamp('v2/',$settings->exchange_key,crypt::Decrypt($settings->exchange_secret),$settings->only_for_bitstamp_id);
					$result = $api->balance();
					#echo "<pre>".print_r($result,true)."</pre>";
					#exit;
					if (null !== $result){
						//controlla se è stato ricevuto un errore
						if (isset($result['status'])) {
							if ($result['status'] == 'error'){
								$balance['eur'] = $result['reason'];
								$balance['btc'] = $result['code'];
							}
						}else{
							$balance['btc'] = (isset($result['btc_available']) ? $result['btc_available'] : 0);
							$balance['eur'] = (isset($result['eur_available']) ? $result['eur_available'] : 0);
						}
					}
					break;

				case 2: // Binance
					$api = new Binance\API($settings->exchange_key,crypt::Decrypt($settings->exchange_secret));

					if (gethostname()=='CGF6135T'){
						$proxyConf = [
					       'address' => 'proxy.agenziaentrate.it',
					       'port' => '80',
					       'user' => "entratead".chr(92)."cszsrg70d18f839o",
					       'pass' => 'gabriele.73'
					       ];
						$api->setProxy($proxyConf);
					}

					try {
						$result = $api->balances();
						if (null !== $result){
							if (isset($result['code'])) {
								$balance['btc'] = $result['code'];
								$balance['eur'] = $result['error'];
							}else{
								// binance mi risponde USDT non ha euro, pertanto converto con coinaverage il valore usd/eur
								$average = WebApp::getFiatRate();
								$usd = round($average['BTCUSD']['last'] / $average['BTCEUR']['last'] , 4);

								$balance['btc'] = (isset($result['BTC']['available']) ? $result['BTC']['available'] : 0);
								$balance['eur'] = (isset($result['USDT']['available']) ? $result['USDT']['available']*$usd : 0);
							}
						}
					} catch(Exception $e) {
						$balance = $e->getMessage();
					}
					break;

				case 3: //Kraken
				// echo "<pre>".print_r(crypt::Decrypt($settings->exchange_secret),true)."</pre>";
				// exit;
					require_once (Yii::app()->params['libsPath'] . '/exchanges/kraken/Kraken.php');
					$api = new Kraken($settings->exchange_key, crypt::Decrypt($settings->exchange_secret));

					if (gethostname()=='CGF6135T'){
						$proxyConf = [
					       'address' => 'proxy.agenziaentrate.it',
					       'port' => '80',
					       'user' => "entratead".chr(92)."cszsrg70d18f839o",
					       'pass' => 'gabriele.73'
					       ];
						$api->setProxy($proxyConf);
					}
					$result = $api->balance();
					//$result = $api->ticker();

					// echo "<pre>".print_r($result,true)."</pre>";
					// exit;

					if (null !== $result){
						//controlla se è stato ricevuto un errore
						if (isset($result['error']) && !empty($result['error'])) {
							$balance['eur'] = $result['error'][0];
							$balance['btc'] = '';
						}else{
							//
							#echo "<pre>".print_r($result,true)."</pre>";
							#exit;
							$balance['btc'] = (isset($result['result']['XXBT']) ? $result['result']['XXBT'] : 0);
							$balance['eur'] = (isset($result['result']['ZEUR']) ? $result['result']['ZEUR'] : 0);
						}
					}
					break;


			}
		}
		echo CJSON::encode($balance,true);
	}
}
