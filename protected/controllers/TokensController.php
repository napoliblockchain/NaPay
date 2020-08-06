<?php
Yii::import('libs.crypt.crypt');

class TokensController extends Controller
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
			'postOnly + delete', // we only allow deletion via POST request
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
				'actions'=>array('index','view','print','export','check'),
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
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$modelc=new PosInvoices('search');
		$modelc->unsetAttributes();

		if(isset($_GET['PosInvoices']))
			$modelc->attributes=$_GET['PosInvoices'];

		$this->render('index',array(
			'modelc'=>$modelc,
		));
	}

	public function actionCheck($id)
	{

		#echo '<pre>'.print_r($id,true).'</pre>';
		#exit;
		$model=$this->loadModel(crypt::Decrypt($id));
		$command = 'receive';
		// if ($model->token_price >=0)
		// 	$command = 'receive';

		//eseguo lo script che si occuperà in background di verificare lo stato dell'invoice appena creata...
		$cmd = Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic ' .$command. ' --id='.$id;
		Utils::execInBackground($cmd);

		$response['message'] = 'Please wait the page reloading... ';
		$response['success'] = 1;
		sleep(2);
		echo CJSON::encode($response,true);
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Transactions the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=PosInvoices::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
	/**
	 * esporta in un foglio excel le transazioni
 	 *
 	 * $typelist = [
 	 *    0 => '',    // completati
 	 *    1 => '',    // pagati
 	 *    2 => '',    // in corso
 	 *    3 => '',    // tutti
 	 *	];
 	 */
 	public function actionExport($typelist)
	{
		$criteria=new CDbCriteria();
		if (Yii::app()->user->objUser['privilegi'] == 10){
		 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}

		if ($_GET['typelist'] == 0){
			$criteria->addCondition("status = 'complete'");
		}else if ($_GET['typelist'] == 1){
			$criteria->addCondition("(status = 'paid' OR status = 'confirmed')");
		}else if ($_GET['typelist'] == 2){
			$criteria->addCondition("status = 'new'");
		}

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null || empty($settings->gdpr_titolare)){//} || empty($settings->poa_port)){
			echo CJSON::encode(array("error"=>'Errore: I parametri di configurazione non sono stati trovati'));
			exit;
		}
		$dataProvider=new CActiveDataProvider('PosInvoices', array(
			'sort'=>array(
	    		'defaultOrder'=>array(
	      			'invoice_timestamp'=>true
	    		)
	  		),
		    'criteria'=>$criteria,
		));


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
				"Data Emissione",
				"Commerciante", //solo amministratore
				"Pos",
				"Codice Transazione",
				"Stato",
				"Importo",
				"Tasso",
				"Indirizzo"
			);
		}else{
			$colonne = array('a','b','c','d','e','f','g','h');
			$intestazione = array(
				"#",
				"Data Emissione",
				"Pos",
				"Codice Transazione",
				"Stato",
				"Importo",
				"Tasso",
				"Indirizzo"
			);
		}

		foreach ($colonne as $n => $l){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($l.'1', $intestazione[$n]);
		}
		$transactions = new CDataProviderIterator($dataProvider);
		$riga = 2;
		$Rows = $transactions->totalItemCount;

		foreach($transactions as $item) {
			// Miscellaneous glyphs, UTF-8

			if (Yii::app()->user->objUser['privilegi'] == 20){
				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$riga, $Rows)
				            ->setCellValue('B'.$riga, date("d/m/Y H:i:s",$item->invoice_timestamp))
							->setCellValue('C'.$riga, Pos::model()->findByPk($item->id_pos) === null ? 'null' : Merchants::model()->findByPk(Pos::model()->findByPk($item->id_pos)->id_merchant)->denomination)
							->setCellValue('D'.$riga, Pos::model()->findByPk($item->id_pos) === null ? 'null' : Pos::model()->findByPk($item->id_pos)->denomination)
							->setCellValue('E'.$riga, crypt::Encrypt($item->id_token))
							->setCellValue('F'.$riga, str_replace("&nbsp;",' ',strip_tags(WebApp::walletStatus($item->status))))
							->setCellValue('G'.$riga, $item->token_price)
							->setCellValue('H'.$riga, $item->rate)
							->setCellValue('I'.$riga, $item->from_address);
			}else{
				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$riga, $Rows)
				            ->setCellValue('B'.$riga, date("d/m/Y H:i:s",$item->invoice_timestamp))
							->setCellValue('C'.$riga, Pos::model()->findByPk($item->id_pos) === null ? 'null' : Pos::model()->findByPk($item->id_pos)->denomination)
							->setCellValue('D'.$riga, crypt::Encrypt($item->id_token))
							->setCellValue('E'.$riga, str_replace("&nbsp;",' ',strip_tags(WebApp::walletStatus($item->status))))
							->setCellValue('F'.$riga, $item->token_price)
							->setCellValue('G'.$riga, $item->rate)
							->setCellValue('H'.$riga, $item->from_address);
			}

			$riga++;
			$Rows--;
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
	 * Prints transactions.
	 *
	 * $typelist = [
	 *    0 => '',    // completati
	 *    1 => '',    // pagati
	 *    2 => '',    // in corso
	 *    3 => '',    // tutti
	 *	];
	 */
	public function actionPrint($typelist)
	{
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
			." - ".$settingsWebApp->gdpr_city
			."\nC.F./P.Iva: ".$settingsWebApp->gdpr_vat;
		$pdf->SetHeaderData(
			Yii::app()->basePath.'../../'.Yii::app()->params['logoAssociazionePrint'],
			21,
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
		if (Yii::app()->user->objUser['privilegi'] == 10){
		 	$criteria->compare('id_user',Yii::app()->user->objUser['id_user'],false);
		}

		if (isset($_GET['tag'])){
			$criteria->compare('type_transaction',$_GET['tag'],false);
		}

		if ($_GET['typelist'] == 0){
			$criteria->addCondition("status = 'complete'");
		}else if ($_GET['typelist'] == 1){
			$criteria->addCondition("(status = 'paid' OR status = 'confirmed')");
		}else if ($_GET['typelist'] == 2){
			$criteria->addCondition("status = 'new'");
		}

		//carico la tabella
		$dataProvider= new CActiveDataProvider('PosInvoices', array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'id_transaction'=>true,
				)
			),
		));

		$iterator = new CDataProviderIterator($dataProvider);
		$x = $dataProvider->totalItemCount;
			foreach($iterator as $data) {
				$loadData[$x][] = $x;
				$loadData[$x][] = date("d/m/Y",$data->invoice_timestamp);
				$loadData[$x][] = str_replace("&nbsp;"," ",strip_tags(WebApp::walletStatus($data->status)));
				$loadData[$x][] = "€ ".$data->token_price;
				$loadData[$x][] = (Pos::model()->findByPk($data->id_pos) === null ? 'null' : Pos::model()->findByPk($data->id_pos)->denomination);
				$loadData[$x][] = crypt::Encrypt($data->id_token);

				$x--;
			}
		// echo "<pre>".print_r($loadData, true)."</pre>";
		// exit;

		if (!isset($loadData)){
			echo "No data found!";
			die();
		}


		// $header['head'] = array('#', 'Data', 'Stato', 'Importo', 'Tasso','Importo Btc','POS','Codice transazione');
		// $header['title'] = 'Elenco Pagamenti';
			$header['head'] = array('#', 'Data', 'Stato', 'Importo', 'POS', 'Codice transazione');
			$header['title'] = 'Elenco Pagamenti Token';

		// print colored table
		$pdf->ColoredTable($header, $loadData, 'token');
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		ob_end_clean();

		//Close and output PDF document
		$pdf->Output('transazioni.pdf', 'I');
	}

}
