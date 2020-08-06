<?php
/* @var $this PosTrxController */
/* @var $dataProvider CActiveDataProvider */

$criteria=new CDbCriteria;

if (Yii::app()->user->objUser['privilegi'] == 10){
    $merchants=Merchants::model()->findByAttributes(array(
        'id_user'=>Yii::app()->user->objUser['id_user'],
        'deleted'=>'0',
    ));
    $criteria->compare('id_merchant',$merchants->id_merchant,false);
}
// if (Yii::app()->user->objUser['privilegi'] == 15){
//     $associations=Associations::model()->findByAttributes(array(
//         'id_user'=>Yii::app()->user->objUser['id_user'],
//         'deleted'=>'0',
//     ));
//     $merchants=Merchants::model()->findByAttributes(array(
//         'id_association'=>$associations->id_association,
//         'deleted'=>'0',
//     ));
//     $criteria->compare('id_merchant',$merchants->id_merchant,false);
// }
//if (Yii::app()->user->objUser['privilegi'] == 20){
    $criteria->compare('deleted',0,false);
//}

$url = Yii::app()->createUrl('transactions/index');
$printURL = Yii::app()->createUrl('transactions/print',['typelist'=>0]);
$exportURL = Yii::app()->createUrl('transactions/export',['typelist'=>0]);

$myList = <<<JS
    lista = {
		cambia: function(val){
            var url = '{$url}' + "&typelist="+val;
            var print = '{$printURL}' + "&typelist="+val;
            var exp = '{$exportURL}' + "&typelist="+val;

           $('#print-button').attr("href", print);
           $('#export-button').attr("href", exp);

           lista.btnClass(val);
           // AGGIORNA yiiGridView in ajax
           $.fn.yiiGridView.update('transactions-grid', {
               type: 'GET',
               url: url,
               success: function() {
                   $('#transactions-grid').yiiGridView('update',{
                       url: url
                   });
               }
           });
       },
       btnClass: function(val){
           $('.btn').removeClass("active");
           $('.btn-'+val).addClass("active");

       }
	}


JS;
Yii::app()->clientScript->registerScript('myList', $myList);


$activeButton = [
    0 => '',    // completati
    1 => '',    // pagati
    2 => '',    // in corso
    3 => '',    // tutti
];

//$activeButton[0] = 'active';

$activeButton[0] = 'active';

if (isset($_GET['typelist']))
    $activeButton[$_GET['typelist']] = 'active';


?>
<style>
table.items thead th{
   color:red!important;
}
</style>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
                <div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fab fa-btc"></i>
						<span class="card-title"><?php echo Yii::t('lang','Transactions');?></span>
					</div>
					<div class="card-body">
                        <span>
                            <button title='Pagamenti che hanno 6 o più conferme' type='button' class='btn-0 btn btn-outline-info btn-sm <?php echo $activeButton[0]; ?>' onclick='lista.cambia(0);'>Completati</button>
                            <button title='Pagamenti che hanno da 0 a 5 conferme' type='button' class='btn-1 btn btn-outline-info btn-sm <?php echo $activeButton[1]; ?>' onclick='lista.cambia(1);'>Pagati</button>
                            <button title='Pagamenti in attesa di pagamento' type='button' class='btn-2 btn btn-outline-info btn-sm <?php echo $activeButton[2]; ?>' onclick='lista.cambia(2);'>In corso...</button>
                            <button title='Tutti i pagamenti' type='button' class='btn-3 btn btn-outline-info btn-sm <?php echo $activeButton[3]; ?>' onclick='lista.cambia(3);'>Tutti</button>
                        </span>
            			<div class="table-responsive table--no-card m-t-40">
            				<?php
            				$this->widget('zii.widgets.grid.CGridView', array(
            					//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
            					//'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
            					//'htmlOptions' => array('class' => 'table table-borderless table-data3'),
                                //'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
            				    //'dataProvider'=>$dataProvider,
                                //'htmlOptions' => array('class' => 'table table-borderless table-data2 table-earning table-wallet text-dark'),
                                // 'htmlOptions' => array('class' => 'table table-borderless text-dark table-data4 table-wallet'),
                                // 'htmlOptions' => array('class' => 'table table-striped text-dark table-data4 table-wallet'),
                                'htmlOptions' => array('class' => 'table table-wallet'),
                                'dataProvider'=>$modelc->search(),
                                'id'=>'transactions-grid',
                                'filter'=>$modelc,
                                'enablePagination'  => true,

            					'columns' => array(
            						//'value'=>'id_transaction',
            						array(
            				      'name'=>'invoice_timestamp',
            							'type' => 'raw',
                          'value' => 'CHtml::link(WebApp::dateLN($data->invoice_timestamp), Yii::app()->createUrl("transactions/view",["id"=>crypt::Encrypt($data->id_transaction)]) )',
                          'filter'=>"",

                        ),
            						array(
            				            'name'=>'status',
            							'type' => 'raw',
            							'value'=>'CHtml::link(WebApp::walletStatus($data->status), Yii::app()->createUrl("transactions/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_transaction)))',
                                        'class'=>'DataColumn',
                                        'evaluateHtmlOptions'=>true,
            			                'htmlOptions'=>array('id'=>'"transactionstatus_{$data->id_transaction}"'),
                                        'filter'=>WebApp::statusList('tx'),
            				        ),
            						array(
            				            'name'=>'price',
            							'type' => 'raw',
            				            'value'=>'"€ ". $data->price',
            				        ),
            						array(
            				            'name'=>'rate',
            							'type' => 'raw',
            				            'value'=>'"€ ". $data->rate',
            				        ),
            						array(
            				            'name'=>'btc_price',
            				            'value'=>'$data->btc_price',
            				        ),
            						// array(
            				        //     'name'=>'id_pos',
            						// 	'value'=> 'Pos::model()->findByAttributes(array("id_pos"=> "$data->id_pos" ))->denomination',
            				        // ),
                                    array(
                                        'name'=>'id_pos',
                                        'header'=>'Pos',
                                        'value'=>'($data->id_pos >0) ? Pos::model()->findByPk($data->id_pos)->denomination : Shops::model()->findByPk($data->item_code*1)->denomination',
                                        'filter'=>CHtml::listData(Pos::model()->findAll($criteria), 'id_pos', 'denomination'),
                                    ),
            						array(
            				            'name'=>'id_invoice_bps',
            							'type' => 'raw',
            		   					'value' => 'CHtml::link(CHtml::encode($data->id_invoice_bps), Yii::app()->createUrl("transactions/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_transaction)))',
            				        ),
                                    [
                                        'value'=>''
                                        ],

            					)
            				));
            				?>
            			</div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                			<div class="col-md-12">
                				<div class="overview-wrap">
                					<h2 class="title-1">
                                        <form>
                						<a href="<?php echo $printURL;?>" target="_blank" id='print-button'>
                							<button type="button" class="btn btn-secondary">Stampa</button>
                						</a>
                						<a href="<?php echo $exportURL;?>" target="_blank" id='export-button'>
                							<button type="button" class="btn btn-warning">Esporta</button>
                						</a>
                						</form>
                					</h2>
                				</div>
                			</div>
                		</div>
                    </div>
                </div>
			</div>
		</div>

		<?php echo Logo::footer(); ?>
	</div>
</div>
