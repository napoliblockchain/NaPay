<?php
Yii::import('libs.crypt.crypt');
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
Yii::import('libs.NaPacks.SaveModels');
Yii::import('libs.NaPacks.Save');

class TransactionsController extends Controller
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
				'actions'=>array('index','view','print','export','delete'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$save = new Save;
		$this->loadModel(crypt::Decrypt($id))->delete();

		$save->WriteLog('napay','transactions','Delete', 'Transaction ['.crypt::Decrypt($id).'] deleted by '.Yii::app()->user->objUser['email'] );
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
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
		$modelc=new Transactions('search');
		$modelc->unsetAttributes();

		if(isset($_GET['Transactions']))
			$modelc->attributes=$_GET['Transactions'];

		$this->render('index',array(
			'modelc'=>$modelc,
		));
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
		$model=Transactions::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
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
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
		}

		if ($_GET['typelist'] == 0){
			$criteria->addCondition("status = 'complete'");
		}else if ($_GET['typelist'] == 1){
			$criteria->addCondition("(status = 'paid' OR status = 'confirmed')");
		}else if ($_GET['typelist'] == 2){
			$criteria->addCondition("status = 'new'");
		}

		//carico la tabella
		$dataProvider= new CActiveDataProvider('Transactions', array(
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
			$loadData[$x][] = "€ ".$data->price;
			$loadData[$x][] = "€ ".$data->rate;
			$loadData[$x][] = $data->btc_price;
			$loadData[$x][] = (Pos::model()->findByPk($data->id_pos) === null ? 'null' : Pos::model()->findByPk($data->id_pos)->denomination);
			$loadData[$x][] = $data->id_invoice_bps;

			$x--;
		}
		// echo "<pre>".print_r($loadData, true)."</pre>";
		// exit;

		if (!isset($loadData)){
			echo "No data found!";
			die();
		}


		$header['head'] = array('#', 'Data', 'Stato', 'Importo', 'Tasso','Importo Btc','POS','Codice transazione');
		$header['title'] = 'Elenco Pagamenti';

		// print colored table
		$pdf->ColoredTable($header, $loadData);
		// reset pointer to the last page
		$pdf->lastPage();

		//Close and output PDF document
		ob_end_clean();

		//Close and output PDF document
		$pdf->Output('transazioni.pdf', 'I');
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
			$merchants=Merchants::model()->findByAttributes(array(
				'id_user'=>Yii::app()->user->objUser['id_user'],
				'deleted'=>'0',
			));
		 	$criteria->compare('id_merchant',$merchants->id_merchant,false);
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

		//carico le impostazioni dell'applicazione
		$settings=Settings::load();
		if ($settings === null || empty($settings->gdpr_titolare)){//} || empty($settings->poa_port)){
			echo CJSON::encode(array("error"=>'Errore: I parametri di configurazione non sono stati trovati'));
			exit;
		}
		$dataProvider=new CActiveDataProvider('Transactions', array(
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
		$colonne = array('a','b','c','d','e','f','g','h','i');
		$intestazione = array(
			"#",
			"Data Emissione",
			"Pos",
			"Codice Transazione",
			"Stato",
			"Importo",
			"Rate",
			"Importo Btc",
			"Indirizzo"
		);

		foreach ($colonne as $n => $l){
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($l.'1', $intestazione[$n]);
		}
		$transactions = new CDataProviderIterator($dataProvider);
		$riga = 2;
		$Rows = $transactions->totalItemCount;

		foreach($transactions as $item) {
			// Miscellaneous glyphs, UTF-8
			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A'.$riga, $Rows)
			            ->setCellValue('B'.$riga, date("d/m/Y H:i:s",$item->invoice_timestamp))
						->setCellValue('C'.$riga, Pos::model()->findByPk($item->id_pos) === null ? 'null' : Pos::model()->findByPk($item->id_pos)->denomination)
						->setCellValue('D'.$riga, $item->id_invoice_bps)
						->setCellValue('E'.$riga, str_replace("&nbsp;",' ',strip_tags(WebApp::walletStatus($item->status))))
						->setCellValue('F'.$riga, $item->price)
						->setCellValue('G'.$riga, $item->rate)
						->setCellValue('H'.$riga, $item->btc_price)
						->setCellValue('I'.$riga, $item->bitcoin_address);

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


}
