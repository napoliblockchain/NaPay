<?php
(Yii::app()->user->objUser['privilegi'] == 20) ? $visible = true : $visible = false;

if ($id == null){
	$actionURL = Yii::app()->createUrl('pagamenti/create');
	$nome = '';
}else{
	$actionURL = Yii::app()->createUrl('pagamenti/create').'&id='.$id;
	$nome =  '('. $users->name.' '.$users->surname.')';
}
$userPayment = Yii::app()->createUrl('payfee/index');

$url = Yii::app()->createUrl('pagamenti/index');
$printURL = Yii::app()->createUrl('pagamenti/print',['typelist'=>0]);
$exportURL = Yii::app()->createUrl('pagamenti/export',['typelist'=>0]);

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
            $.fn.yiiGridView.update('pagamenti-grid', {
                type: 'GET',
                url: url,
                success: function() {
                    $('#pagamenti-grid').yiiGridView('update',{
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
						<i class="fas fa-credit-card"></i>
						<span class="card-title">Lista Pagamenti <?php echo $nome; ?></span>
						<div class="float-right">
							<?php
							if (Yii::app()->user->objUser['privilegi'] == 20){
							?>
							<a href="<?php echo $actionURL;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						<?php }else{ ?>
							<a href="<?php echo $userPayment;?>">
								<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
									<i class="fa fa-plus"></i></button>
							</a>
						<?php } ?>
						</div>

					</div>
					<div class="card-body">
						<span>
                            <button title='Pagamenti confermati' type='button' class='btn-0 btn btn-outline-info btn-sm <?php echo $activeButton[0]; ?>' onclick='lista.cambia(0);'>Completati</button>
                            <button title='Tutti i pagamenti' type='button' class='btn-3 btn btn-outline-info btn-sm <?php echo $activeButton[3]; ?>' onclick='lista.cambia(3);'>Tutti</button>
                        </span>
						<div class="table-responsive table--no-card m-t-40">
							<?php $this->widget('zii.widgets.grid.CGridView', array(
								 'id' => 'pagamenti-grid',

								'htmlOptions' => array('class' => 'table table-wallet'),
			                    'dataProvider'=>$modelc->search(),
			                    'filter'=>$modelc,
			                    'enablePagination'  => true,

								'columns' => array(
									//'id_pagamento',
									//'anno',
									array(
										'type'=>'raw',
										'name'=>'progressivo',
										'value' => 'CHtml::link(CHtml::encode($data->anno."/".$data->progressivo), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento)))',
									),
									array(
							            'name'=>'status',
										'type' => 'raw',
										'value'=>'CHtml::link(WebApp::walletStatus($data->status), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento)))',
										'filter'=>WebApp::statusList('pagamenti'),
							        ),

									array(
										'type'=> 'raw',
							            'name'=>'id_user',
										'value' => 'isset(Users::model()->findByPk($data->id_user)->surname) ? CHtml::link(CHtml::encode(Users::model()->findByPk($data->id_user)->surname.chr(32).Users::model()->findByPk($data->id_user)->name), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento))) : ""',
										'visible'=>$visible,
							        ),
									array(
							            'name'=>'importo',
										'value'=> '$data->importo',
							        ),
									array(
										'type'=> 'raw',
							            'name'=>'id_tipo_pagamento',
										'value' => 'CHtml::link(CHtml::encode(TipoPagamenti::model()->findByPk($data->id_tipo_pagamento)->description), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento)))',
										'filter'=>CHtml::listData(TipoPagamenti::model()->findAll(), 'id_tipo_pagamento', 'description'),
							        ),
									// array(
							        //     'name'=>'data_inizio',
									// 	'value'=> 'WebApp::data_it($data->data_inizio)',
							        // ),
									array(
							            'name'=>'data_scadenza',
										'value'=> 'WebApp::data_it($data->data_scadenza)',
							        ),
									array(
							            'name'=>'id_invoice_bps',
									 	'type' => 'raw',
					   			 		'value' => '$data->id_invoice_bps != "0" ? CHtml::link(CHtml::encode($data->id_invoice_bps), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento))) : CHtml::link(CHtml::encode(crypt::Encrypt($data->id_invoice_bps)), Yii::app()->createUrl("pagamenti/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_pagamento)))',
							        ),

									array(
							            'name'=>'id_invoice_bps',
										'header'=>'<i class="fa fa-download"></i>',
										'type' => 'raw',
										'value'=> '($data->status == "paid") ? (CHtml::link("<img src=\'css/images/pdf-icon.png\' width=30 />",Yii::app()->createUrl(\'pagamenti/print\',array(\'id\'=>crypt::Encrypt($data->id_pagamento))),array("target"=>"_blank"))) : ($data->status == "complete" ? (CHtml::link("<img src=\'css/images/pdf-icon.png\' width=30 />",Yii::app()->createUrl(\'pagamenti/print\',array(\'id\'=>crypt::Encrypt($data->id_pagamento))),array("target"=>"_blank"))) : "---")',
										'filter'=>"",
							        ),
									[
										'value' => '',
										]
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
										<?php $printURL = Yii::app()->createUrl('pagamenti/printlist'); ?>
										<?php $exportURL = Yii::app()->createUrl('pagamenti/export'); ?>
										<form>
										<a href="<?php echo $printURL;?>" target="_blank">
											<button type="button" class="btn btn-secondary">Stampa</button>
										</a>
										<a href="<?php echo $exportURL;?>" target="_blank">
											<button type="button" class="btn btn-warning">Esporta</button>
										</a>
										<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>


												<?php $actionChart = Yii::app()->createUrl('pagamenti/chart'); ?>
												<a href="<?php echo $actionChart;?>">
													<button type='button' class="btn btn-primary">
														<i class="fa fa-bar-chart-o"></i> Grafico</button>
												</a>


										<?php } ?>
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
