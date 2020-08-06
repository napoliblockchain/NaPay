<?php
/* @var $this MerchantsController */
/* @var $dataProvider CActiveDataProvider */
$active = ['No','Si'];

// se sei admin visualizzi
$visible = (Yii::app()->user->objUser['privilegi'] == 20) ? true : false;
$showPasswordURL = Yii::app()->createUrl('stores/showpassword');
 $actionURL = Yii::app()->createUrl('merchants/create');

$myWalletScript = <<<JS

	var ajax_loader_url = 'css/images/loading.gif';

	function showPassword(id_merchant){
		//console.log('show password',id_merchant);
		$.ajax({
			url:'{$showPasswordURL}',
			type: "POST",
			data: {'id_merchant': id_merchant},
			dataType: "json",
			beforeSend: function() {
				$('#password').hide();
				$('#password').after('<div class="__loading"><center><img width=20 src="'+ajax_loader_url+'" alt="loading..."></center></div>');
			},
			success:function(data){
				//console.log(data);
				$('#password').show().text(data.password);
				$('.__loading').remove();
			},
			error: function(j){
				console.log('error');
			}
		});
	}

JS;
Yii::app()->clientScript->registerScript('myWalletScript', $myWalletScript,CClientScript::POS_HEAD );

?>

<div class='section__content section__content--p30'>
	<div class='container-fluid'>

		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-industry"></i>
						<span class="card-title">Lista commercianti</span>
						<?php if (Yii::app()->user->objUser['privilegi'] > 5){ ?>
							<div class="float-right">
								<a href="<?php echo $actionURL;?>">
									<button class="btn alert-primary text-light img-cir" style="padding:2.5px; width:30px; height:30px;">
										<i class="fa fa-plus"></i></button>
								</a>
							</div>
						<?php } ?>

					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card m-b-40">
							<?php
							$this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-striped table-earning'),
								// 'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								'htmlOptions' => array('class' => 'table table-wallet'),
							    'dataProvider'=>$dataProvider,
								'columns' => array(
									// array(
							        //     'name'=>'id_association',
									// 	'value'=>'getAssociation($data->id_association)',
									// 	'visible' => $visible
							        // ),
									array(
							            'name'=>'denomination',
										'type'=>'raw',
										'value' => 'CHtml::link(CHtml::encode($data->denomination), Yii::app()->createUrl("merchants/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_merchant)))',
							        ),
									array(
							            'name'=>'Nome e Cognome',
							            'value'=>'(isset(Users::model()->findByAttributes(array("id_user"=>$data->id_user))->name) ? Users::model()->findByAttributes(array("id_user"=>$data->id_user))->name : "")  . " " .
												 (isset(Users::model()->findByAttributes(array("id_user"=>$data->id_user))->surname) ? Users::model()->findByAttributes(array("id_user"=>$data->id_user))->surname : "")'
							        ),
									array(
							            'name'=>'Email',
							            'value'=>'(isset(Users::model()->findByAttributes(array("id_user"=>$data->id_user))->email) ? Users::model()->findByAttributes(array("id_user"=>$data->id_user))->email : Yii::t("lang","Not found"))'
							        ),
									array(
										'type'=>'raw',
										'name'=>'id_merchant',
										'header'=>'',
										'value'=> 'WebApp::showPasswordButton($data->id_merchant)',
										'visible' => $visible,
									),

									// array(
							        //     'name'=>'address',
							        //     'value'=>'$data->address',
							        // ),
									// array(
									// 	'type'=>'raw',
							        //     'name'=>'Città',
							        //     'value'=>'ComuniItaliani::model()->findByPk($data->city)->citta.
									// 				" (". ComuniItaliani::model()->findByPk($data->city)->sigla.")"'
							        // ),
									array(
										'type'=> 'raw',
							            'name'=>'iscrizione',
										'value'=>'WebApp::StatoPagamenti($data->id_user)',
							        ),
                                    [
                                        'name' =>''
                                        ]
								)
							));

							?>
						</div>
					</div>
				</div>

			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
<!-- SHOW PASSWORD MODAL -->

<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="passwordModalLabel">Password BTCPay Server</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<p><span id='password'></span></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
			</div>
		</div>
	</div>
</div>
