<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Settings');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.Logo');

class PagamentiController extends Controller
{
	public $totalBTC = 0,
			$totalIMP = 0,
			$totalIVA = 0;

	public function init()
	{
		// echo "<pre>".print_r(Yii::app()->user->objUser,true)."</pre>";
		// exit;
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['facade'] != 'dashboard'){
			if (Yii::app()->user->objUser['facade'] != 'quota'){
				Yii::app()->user->logout();
				$this->redirect(Yii::app()->homeUrl);
			}
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
				'actions'=>array('index','view','create','update','chart','delete','print','printlist','export'),
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
		if (Yii::app()->user->objUser['privilegi'] < 20){
		 	$this->render('error', ['message'=>'Non sei abilitato!']);
		}
		$model=new Pagamenti;

		#echo "<pre>".print_r($_GET,true)."</pre>";
		#exit;

		if (!isset($_GET['id'])){
			//lista utenti
			$listaUtenti = CHtml::listData(Users::model()->orderBySurname()->findAll(), 'id_user', function($utenti) {
				return $utenti->surname . chr(32). $utenti->name;
			});
		}else{
			$criteria=new CDbCriteria();
			$criteria->compare('id_user',crypt::Decrypt($_GET['id']),false);

			$listaUtenti = CHtml::listData(Users::model()->findAll($criteria), 'id_user', function($utenti) {
				return $utenti->surname . chr(32). $utenti->name;
			});
		}
		#echo "<pre>".print_r($listaUtenti,true)."</pre>";
		#exit;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Pagamenti']))
		{
			#echo "<pre>".print_r($_POST,true)."</pre>";
			#exit;
			$model->attributes=$_POST['Pagamenti'];
			$model->id_invoice_bps = 'NAPAY-'.Utils::passwordGenerator(24);

			$model->status = 'paid';
			if ($model->importo <0)
				$model->status = 'cancellation';

			$model->anno = date('Y',time());
			$model->paypal_txn_id = 0;

			//salva la transazione in pagamenti
			$settings=Settings::load();

			// quindi aggiorno il progressivo dei pagamenti
			if ($settings->progressivo_ricevute_anno == date('Y',time())){
				$settings->progressivo_ricevute_pagamenti ++;
				Settings::save($settings,array('progressivo_ricevute_pagamenti'));
				$model->progressivo = $settings->progressivo_ricevute_pagamenti;
			}else{
			  $settings->progressivo_ricevute_anno = date('Y',time());
			  $settings->progressivo_ricevute_pagamenti = 1;
			  Settings::save($settings,array('progressivo_ricevute_pagamenti','progressivo_ricevute_anno'));
			  $model->progressivo = 1;
			}

			if($model->validate()){
				$model->data_registrazione = date('Y/m/d', strtotime(str_replace('/', '-',($model->data_registrazione))));
				$model->data_inizio		= date('Y/m/d', strtotime(str_replace('/', '-',($model->data_inizio))));
				$model->data_scadenza	= date('Y/m/d', strtotime(str_replace('/', '-',($model->data_scadenza))));

				if($model->insert())
					$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pagamento)));
			}
		}

		$this->render('create',array(
			'model'=>$model,
			'listaUtenti'=>$listaUtenti
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


		$criteria=new CDbCriteria();
		$criteria->compare('id_user',$model->id_user,false);

		$listaUtenti = CHtml::listData(Users::model()->findAll($criteria), 'id_user', function($utenti) {
			return CHtml::encode($utenti->surname . chr(32). $utenti->name);
		});


		//VERIFICO CHE L'UTENTE COLLEGATO NON SIA IL SEGRETARIO
		// il segretario non può inserire i pagamenti
		$users = Users::model()->findByPk(Yii::app()->user->objUser['id_user']);
		if ($users->id_carica != 4){
			// Uncomment the following line if AJAX validation is needed
			// $this->performAjaxValidation($model);

			// echo "<pre>".print_r($_POST,true)."</pre>";
			// exit;

			if(isset($_POST['Pagamenti']))
			{
				$model->attributes=$_POST['Pagamenti'];
				if($model->validate()){
					$model->data_registrazione = date('Y/m/d', strtotime(str_replace('/', '-',($model->data_registrazione))));
					$model->data_inizio		= date('Y/m/d', strtotime(str_replace('/', '-',($model->data_inizio))));
					$model->data_scadenza	= date('Y/m/d', strtotime(str_replace('/', '-',($model->data_scadenza))));

					if($model->update())
						$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pagamento)));
				}
			}

			$this->render('update',array(
				'model'=>$model,
				'listaUtenti'=>$listaUtenti
			));
		}else{
			$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pagamento)));
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$users = Users::model()->findByPk(Yii::app()->user->objUser['id_user']);
		if ($users->id_carica != 4){
			$this->loadModel(crypt::Decrypt($id))->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}else{
			$model=$this->loadModel(crypt::Decrypt($id));
			$this->redirect(array('view','id'=>crypt::Encrypt($model->id_pagamento)));
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$id = null;
		$users = null;
		if (isset($_GET['id'])){
			$id = $_GET['id'];
			$users = Users::model()->findByPk(crypt::Decrypt($_GET['id']));
		}

		$modelc=new Pagamenti('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Pagamenti']))
			$modelc->attributes=$_GET['Pagamenti'];

		$this->render('index',array(
			'modelc'=>$modelc,
			'id'=>$id,
			'users'=>$users,
		));
	}

	/**
	 * Lists all models for chart.
	 */
	public function actionChart()
	{
		$criteria=new CDbCriteria();
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] < 20){
		 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}


		$dataProvider=new CActiveDataProvider('Pagamenti', array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'data_registrazione'=>false
	    		)
	  		),
		));
		$this->render('chart',array(
			'dataProvider'=>$dataProvider,
		));
	}

		/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Pagamenti the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Pagamenti::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Pagamenti $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='pagamenti-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionPrint($id){
		//carico i dati della fattura
		$pagamenti = $this->loadModel(crypt::Decrypt($id));

		//carico i dati del socio
		$users = Users::model()->findByPk($pagamenti->id_user);

		//carico i SETTINGS del socio
		$settings = Settings::loadUser($pagamenti->id_user);

		//carico i SETTINGS della WebApp
		$settingsWebApp = Settings::load();

		//carico l'estensione pdf
		Yii::import('application.extensions.MYPDF.*');

		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(Yii::app()->params['adminName']);
		$pdf->SetAuthor(Yii::app()->params['shortName']);
		$pdf->SetTitle('Ricevuta '.$pagamenti->progressivo.'-'.$pagamenti->anno);
		$pdf->SetSubject('Ricevuta '.Quote::model()->findByPk($pagamenti->id_quota)->description);
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, myPDF_HEADER_STRING);
		//$pdf->SetHeaderData(Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazione'], 42, Yii::app()->params['adminName'], Yii::app()->params['indirizzo']);
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

		$p_invoice = 'background-color: #ccc; margin: 5px; padding: 8px; min-width:100px; min-height: 80px; ';
		$div_cliente = 'background-color: #ccc; padding: 10px; width:320px; ';
		$p_cliente = 'padding: 0px; margin:0px;';
		$thead = 'background-color: #ccc; ';
		//
		$sub['invoice'] = '<table border="0" cellspacing="6" cellpadding="6">
							<tr>
								<th><p><small>Ricevuta nr.</small></p>
									<table border="0" cellspacing="2" cellpadding="2" style="'.$p_invoice.'">
										<tr><td align="center">'.$pagamenti->progressivo.'/'.$pagamenti->anno.'</td></tr>
									</table>
								</th>

								<th><p><small>del</small></p>
									<table border="0" cellspacing="2" cellpadding="2" style="'.$p_invoice.'">
										<tr><td align="center">'.date('d/m/Y', strtotime(str_replace('/', '-',($pagamenti->data_registrazione)))).'</td></tr>
									</table>
								</th>
							</tr>
							</table>';
		//controlla se persona fisica o giuridica...
		if ($users->corporate == 0)
			$denomination = $users->name. ' '.$users->surname;
		else
			$denomination = $users->denomination;

		$sub['cliente'] = '<table border="0" cellspacing="2" cellpadding="2">
							<tr>
								<td colspan=3>
									<p><small><b>Cliente</b></small></p>
									<table border="0" cellspacing="2" cellpadding="2" style="'.$div_cliente.'">
										<tr><td>'.$denomination.'</td></tr>
										<tr><td>'.$users->address.'</td></tr>
										<tr><td>'.$users->cap.'</td></tr>
										<tr><td>'.ComuniItaliani::model()->findByPk($users->city)->citta.'</td></tr>
										<tr><td></td></tr>
										<tr><td>P.IVA/Cod.Fiscale: '.$users->vat.'</td></tr>
									</table>
								</td>
							</tr>
							</table>';

		$sub['items'] 	= '<table border="1" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

		$html = '
		<table border="0" cellspacing="2" cellpadding="4">
			<tr>
				<th style="width: 300px;">&nbsp;</th>
			</tr>
			<tr>
				<th valign="top">'.$sub['invoice'].'</th>
				<th>'.$sub['cliente'].'</th>
			</tr>
		</table>';
		$html .= '<p></hr></p>';

		$html .= '
		<table border="1" cellspacing="0" cellpadding="4">
		<tr>
			<th width="25" style="'.$thead.'">#</th>
			<th width="430" style="'.$thead.'">Descrizione</th>
			<th width="110" style="'.$thead.'" align="center">Importo</th>
			<th width="80" style="'.$thead.'" align="center">Iva</th>
		</tr>
		';
		//
		//$html .= '<tbody>';
		if (empty($pagamenti->id_invoice_bps))
			if ($pagamenti->importo == 0)
				$text = 'Operazione';
			else
				$text = 'Versamento';
		else
			if ($pagamenti->paypal_txn_id != '0'){
				$text = 'Transazione n. <b>'.$pagamenti->id_invoice_bps.'</b>';
				$text .= '<br>Paypal ID n. <b>'.$pagamenti->paypal_txn_id.'</b>';
			}else{
				$text = 'Transazione n. <b>'.$pagamenti->id_invoice_bps.'</b>';
			}

		$text .= '<br>Effettuata in data: '.WebApp::data_it($pagamenti->data_registrazione)
			.'<br>Importo: €. '.$pagamenti->importo
			.'<br>Tipo quota: <b>'.Quote::model()->findByPk($pagamenti->id_quota)->description.'</b>'
			.'<br>Validità dal: '.WebApp::data_it($pagamenti->data_inizio)
			. ' al: '.WebApp::data_it($pagamenti->data_scadenza)
			;
		$textAr = explode("\n", $text);
		$textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind
		#echo "<pre>".print_r($textAr,true)."</pre>";
		#exit;

		$x=1;
		$this->totalBTC = 0;
		$this->totalIMP = 0;
		$this->totalIVA = 0;
		$this->setImporto($pagamenti->importo);
		foreach ($textAr as $line) {
			$html .= '<tr>';
			$html .= '<td width="25">'.$x.'</td>';
			$html .= '	<td width="430">'.$line.'</td>';
			$html .= '	<td width="110" align="center">'.$pagamenti->importo.'</td>';
			$html .= '	<td width="80" align="center">Esente</td>';
			$html .= '</tr>';

			$x++;
		}
		for ($x ; $x< 9;$x++){
			$html .= '
			<tr>
				<td width="25" height="38"></td>
				<td width="430"></td>
				<td width="110"></td>
				<td width="80"></td>
			</tr>';
		}
		$html .= '</table>';

		$html .= '
		<table border="0" cellspacing="1" cellpadding="4">
		';
		$html .= '
		<tr>
			<td width="25" height="25"></td>
			<td width="430"></td>
			<td width="110">Imponibile</td>
			<td width="90">€. '.$this->totalIMP.'</td>
			<td width="60"></td>
		</tr>';
		$html .= '
		<tr>
			<td width="25" height="25"></td>
			<td width="430"></td>
			<td width="110">Imposta IVA</td>
			<td width="90">€. '.$this->totalIVA.'</td>
			<td width="60"></td>
		</tr>';
		$html .= '
		<tr>
			<td width="25" height="25"></td>
			<td width="430"></td>
			<td width="110"><b>TOTALE </b></td>
			<th width="75" style="'.$thead.'">€. '.($this->totalIMP + $this->totalIVA).' </th>
		</tr>';
		$html .= '</table>';

		$html .= '
		<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<td><p><small><b>Modalità di pagamento</b></small></p></td>
		</tr>
		<tr>
			<th width="645" style="'.$thead.'">'.TipoPagamenti::model()->findByPk($pagamenti->id_tipo_pagamento)->description.'</th>
		</tr>
		';
		$html .= '</table>';
		$html .= '
		<table border="0" cellspacing="0" cellpadding="4">
		<tr><td></td></tr>
		<tr>
			<td><small>Esenzione da imposte dirette, ex art. 148, comma 1, D.P.R. 917 del 1986</small></td>
		</tr>
		</table>';


		// // output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');
		 // reset pointer to the last page
		$pdf->lastPage();
		//Close and output PDF document
		ob_end_clean();
		$pdf->Output('ricevuta-'.$pagamenti->progressivo.'-'.$pagamenti->anno.'.pdf', 'I');
	}

	public function setImporto($importo){
		//$scorpora = 1 + (Yii::app()->params['vat']/100);
		$scorpora = 1 + (0/100);

		$imponibile = round($importo / $scorpora, 2);
		$iva = $importo - $imponibile;

		$this->totalIMP += $imponibile;
		$this->totalIVA += $iva;

		return $imponibile;
	}

	/**
	 * esporta in un foglio excel le transazioni
	 */
	public function actionExport()
	{
		$criteria=new CDbCriteria();
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] < 20){
		 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}

		$dataProvider=new CActiveDataProvider('Pagamenti', array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'data_registrazione'=>false
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
		if (Yii::app()->user->objUser['privilegi'] == 20){
			$colonne = array('a','b','c','d','e','f','g','h','i');
			$intestazione = array(
				"#",
				"Data Registrazione",
				"Utente", //solo amministratore
				"Importo",
				"Tipo quota",
				"Tipo pagamento",
				"Data scadenza",
				"Codice transazione",
				"Stato",
			);
		}else{
			$colonne = array('a','b','c','d','e','f','g','h');
			$intestazione = array(
				"#",
				"Data Registrazione",
				"Importo",
				"Tipo quota",
				"Tipo pagamento",
				"Data scadenza",
				"Codice transazione",
				"Stato",
			);
		}

		//creazione foglio excel
		foreach ($colonne as $n => $l){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($l.'1', $intestazione[$n]);
		}
		$transactions = new CDataProviderIterator($dataProvider);
		$riga = 2;
		foreach($transactions as $item) {
			// Miscellaneous glyphs, UTF-8
			if (Yii::app()->user->objUser['privilegi'] == 20){
				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$riga, $item->id_pagamento)
				            ->setCellValue('B'.$riga, WebApp::data_it($item->data_registrazione))
							->setCellValue('C'.$riga, Users::model()->findByPk($item->id_user)->surname .' '. Users::model()->findByPk($item->id_user)->name)
							->setCellValue('D'.$riga, $item->importo)
							->setCellValue('E'.$riga, Quote::model()->findByPk($item->id_quota)->description)
							->setCellValue('F'.$riga, TipoPagamenti::model()->findByPk($item->id_tipo_pagamento)->description)
							->setCellValue('G'.$riga, WebApp::data_it($item->data_scadenza))
							->setCellValue('H'.$riga, $item->id_invoice_bps)
							->setCellValue('I'.$riga, $item->status);
			}else{
				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$riga, $item->id_pagamento)
				            ->setCellValue('B'.$riga, WebApp::data_it($item->data_registrazione))
							->setCellValue('C'.$riga, $item->importo)
							->setCellValue('D'.$riga, Quote::model()->findByPk($item->id_quota)->description)
							->setCellValue('E'.$riga, TipoPagamenti::model()->findByPk($item->id_tipo_pagamento)->description)
							->setCellValue('F'.$riga, WebApp::data_it($item->data_scadenza))
							->setCellValue('G'.$riga, $item->id_invoice_bps)
							->setCellValue('H'.$riga, $item->status);
			}

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
	public function actionPrintlist(){
		//carico i SETTINGS della WebApp
		$settingsWebApp = Settings::load();

		//carico l'estensione pdf
		Yii::import('application.extensions.MYPDF.*');

		// create new PDF document
		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(Yii::app()->params['adminName']);
		$pdf->SetAuthor(Yii::app()->params['shortName']);
		$pdf->SetTitle("Elenco Pagamenti");
		$pdf->SetSubject('Elenco Pagamenti');

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
		if (isset(Yii::app()->user->objUser) && Yii::app()->user->objUser['privilegi'] < 20){
		 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}

		//carico la tabella
		$dataProvider=new CActiveDataProvider('Pagamenti', array(
			'criteria'=>$criteria,
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'progressivo'=>true
	    		)
	  		),
		));

		$iterator = new CDataProviderIterator($dataProvider);
		$x = $dataProvider->totalItemCount;
		foreach($iterator as $data) {
			//$loadData[$x][] = $x;
			$loadData[$x][] = $data->anno;
			$loadData[$x][] = $data->progressivo;
			$loadData[$x][] = str_replace("&nbsp;"," ",strip_tags(WebApp::walletStatus($data->status)));
			$loadData[$x][] = isset(Users::model()->findByPk($data->id_user)->surname) ? Users::model()->findByPk($data->id_user)->surname.chr(32).Users::model()->findByPk($data->id_user)->name : "";
			$loadData[$x][] = "€ " . $data->importo;
			$loadData[$x][] = TipoPagamenti::model()->findByPk($data->id_tipo_pagamento)->description;
			$loadData[$x][] = Quote::model()->findByPk($data->id_quota)->description;
			$loadData[$x][] = WebApp::data_it($data->data_scadenza);
			$loadData[$x][] = $data->id_invoice_bps;

			$x--;
		}
		// echo "<pre>".print_r($loadData, true)."</pre>";
		// exit;



		$header['head'] = array('Anno', 'Prog.', 'Stato', 'Nominativo', 'Imp.','Tipo pag.','Tipo quota', 'Scadenza', 'ID Transazione');
		$header['title'] = 'Lista Pagamenti';

		// print colored table
		$pdf->ColoredTable($header, $loadData, 'pagamenti');
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		ob_end_clean();

		//Close and output PDF document
		$pdf->Output('pagamenti.pdf', 'I');
	}
}
