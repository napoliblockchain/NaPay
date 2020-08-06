<?php
$visible =  WebApp::isMobileDevice();


$criteria=new CDbCriteria;
$criteria->compare('deleted',0,false);

$url = Yii::app()->createUrl('tokens/index');
$printURL = Yii::app()->createUrl('tokens/print',['typelist'=>0]);
$exportURL = Yii::app()->createUrl('tokens/export',['typelist'=>0]);

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
            $.fn.yiiGridView.update('tokens-grid', {
                type: 'GET',
                url: url,
                success: function() {
                    $('#tokens-grid').yiiGridView('update',{
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
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-12">
                <div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-star"></i>
						<span class="card-title">Transazioni token</span>

					</div>
					<div class="card-body">
                        <span>
                            <button title='Pagamenti che hanno 6 o più conferme' type='button' class='btn-0 btn btn-outline-info btn-sm <?php echo $activeButton[0]; ?>' onclick='lista.cambia(0);'>Completati</button>
                            <!-- <button title='Pagamenti che hanno da 0 a 5 conferme' type='button' class='btn-1 btn btn-outline-info <?php echo $activeButton[1]; ?>' onclick='lista.cambia(1);'>Pagati</button> -->
                            <button title='Pagamenti in attesa di pagamento' type='button' class='btn-2 btn btn-outline-info btn-sm <?php echo $activeButton[2]; ?>' onclick='lista.cambia(2);'>In corso...</button>
                            <button title='Tutti i pagamenti' type='button' class='btn-3 btn btn-outline-info btn-sm <?php echo $activeButton[3]; ?>' onclick='lista.cambia(3);'>Tutti</button>
                        </span>
        				<div class="table-responsive table--no-card m-t-40">

        				<?php

                            $this->widget('zii.widgets.grid.CGridView', array(
                              'id' => 'tokens-grid',
                              //'hideHeader' => true,
                              // 'htmlOptions' => array('class' => 'table table-borderless  table-data4 table-wallet text-light'),
                              //'htmlOptions' => array('class' => 'table table-borderless table-data2 table-earning '),
                             // 'htmlOptions' => array('class' => 'table grid-view text-dark  table-wallet'),
                             'htmlOptions' => array('class' => 'table table-wallet'),
                             'dataProvider'=>$modelc->search(),
                             'id'=>'tokens-grid',
                             'filter'=>$modelc,
                                // 'to_address'   => $to_address,          // your special parameter
                                'pager'=>array(
                                      //'header'=>'Go to page:',
                                      //'cssFile'=>Yii::app()->theme->baseUrl
                                  'cssFile'=>Yii::app()->request->baseUrl."/css/yiipager.css",
                                      'prevPageLabel'=>'<',
                                      'nextPageLabel'=>'>',
                                      'firstPageLabel'=>'<<',
                                      'lastPageLabel'=>'>>',
                                  ),

                              'columns' => array(

                                array(
                                  'name'=>'invoice_timestamp',
                                  'type'=>'raw',
                                  'value' => 'CHtml::link(WebApp::dateLN($data->invoice_timestamp,$data->id_token), Yii::app()->createUrl("tokens/view",["id"=>crypt::Encrypt($data->id_token)]) )',
                                ),
                                array(
                                  'type'=>'raw',
                                  'name'=>'status',
                                  'value'=>'CHtml::link(WebApp::walletStatus($data->status), Yii::app()->createUrl("tokens/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
                                  'cssClassExpression' => '( $data->status == "sent" ) ? "denied" : (( $data->status == "complete" ) ? "process" : "desc incorso")',
                                  'filter'=>WebApp::statusList('token'),
                                    ),
                                array(
                                  'type'=>'raw',
                                  'name'=>'token_price',
                                  // 'value'=>'Yii::app()->controller->typePrice($data->token_price,(($data->to_address == $this->grid->to_address) ? "received" : "sent"))',
                                  'value'=>'"<h5 class=\"text-success\">+".$data->token_price."</h5>"',
                                  // 'htmlOptions'=>array('style'=>'text-align:center;'),
                                    ),
                                array(
                                    'name'=>'id_pos',
                                    'header'=>'Pos',
                                    'value'=>'(Pos::model()->findByPk($data->id_pos) === null) ? "" : Pos::model()->findByPk($data->id_pos)->denomination',
                                    'filter'=>CHtml::listData(Pos::model()->findAll($criteria), 'id_pos', 'denomination'),
                                ),

                                [
                                  'type'=>'raw',
                                  'name'=>'from_address',
                                  'value'=>'CHtml::link($data->from_address, Yii::app()->createUrl("tokens/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
                                  'visible'=>!$visible,
                                ],
                                [
                                    'value'=>'',
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
