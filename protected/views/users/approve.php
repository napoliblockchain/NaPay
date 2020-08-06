<?php
$gateways=Gateways::model()->findAll();
$listaGateways = CHtml::listData( $gateways, 'id_gateway' , 'denomination');
ksort($listaGateways);

if (empty($preferredCoinList))
	$preferredCoinList = [];

if ($settings->blockchainAsset == '')
	$settings->blockchainAsset = "{'BTC':'BTC'}";

$enabledAssets = CJSON::decode($settings->blockchainAsset);
$listaServerBlockchain = CHtml::listData(Blockchains::model()->findAll(), 'url', 'denomination');
?>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'iscrizioni-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));

$script = <<<JS
    var disclaim = document.querySelector('#disclaim-button');

	disclaim.addEventListener('click', function(){
		$('#rifiuto').val(1);
		$('form').submit();
    });

JS;
Yii::app()->clientScript->registerScript('script', $script);
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>

		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fa fa-eye"></i>
						<span class="card-title">Richieste di iscrizione</span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card ">
							<?php
							$this->widget('ext.selgridview.SelGridView', array(
								'id'=>'users-grid',
								'selectableRows' => 2,
								//'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
								// 'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								'htmlOptions' => array('class' => 'table table-wallet'),
							    'dataProvider'=>$dataProvider,
								'columns' => array(
									array(
									   'id'=>'selectedUsers',
									   'class'=>'CCheckBoxColumn',
								    ),
									array(
										'type'=> 'raw',
							            'name'=>'name',
										'value' => 'CHtml::link(CHtml::encode(strtoupper(Users::model()->findByPk($data->id_user)->surname).chr(32).Users::model()->findByPk($data->id_user)->name), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
							        ),
									array(
							            'name'=>'email',
										'type'=>'raw',
										'value' => 'CHtml::link(CHtml::encode($data->email), Yii::app()->createUrl("users/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_user)))',
							        ),
									array(
							            'name'=>'corporate',
										'value'=>'($data->corporate == 0 ? "No" : "Si")',
							        ),
									array(
							            'name'=>'id_users_type',
										'value'=>'UsersType::model()->findByPk($data->id_users_type)->desc',
							        ),
									array(
										'type'=>'raw',
							            'name'=>'Mail inviate',
										'value'=>'isset(Settings::loadUser($data->id_user)->numero_mail_approvazione) ? Settings::loadUser($data->id_user)->numero_mail_approvazione : "0"',
							        ),
									array(
							      		'name'=>'',
										'value' => '',
							    	),
								)
							));
							?>
						</div>
					</div>
					<div class="card-footer">
						<?php if ($dataProvider->totalItemCount >0) { ?>
							<div class="row">
								<div class="col-md-12">
									<div class="overview-wrap">
										<h2 class="title-1">
											<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#scrollmodalAccetta">Accetta</button>
											<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#scrollmodalModello">Rifiuta</button>
										</h2>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo Logo::footer(); ?>
	</div>
</div>
<!-- modal accetta -->
<div class="modal fade" id="scrollmodalAccetta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabelAccetta" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="scrollmodalLabelAccetta">Accetta Iscrizione</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body bg-primary" >
				<div class="au-card au-card--no-shadow au-card--no-pad bg-transparent text-light">
					<div class="card-header ">
						<i class="fas fa-rocket"></i>
						<span class="card-title">Impostazioni generali per tutti i soci selezionati</span><br/>
						<i>N.B. Queste impostazioni saranno applicate esclusivamente ai soci con personalità giuridica</i>
					</div>

					<div class="card-body">
							<div class="form-group">
									<?php echo $form->labelEx($settings,'Seleziona il Server Blockchain'); ?>
									<?php echo $form->dropDownList($settings,'blockchainAddress',$listaServerBlockchain,array('class'=>'form-control'));	?>
									<?php echo $form->error($settings,'blockchainAddress',array('class'=>'alert alert-danger')); ?>
							</div>

							<div class="row form-group ">
								<div class="col col-md-3">
									<label class=" form-control-label"><?php echo $form->labelEx($settings,'blockchainAsset'); ?></label>
								</div>

								<div class="col col-md-9">
									<div class="form-check">
										<?php
										foreach ($preferredCoinList as $key => $value) {
											$checked = '';
											if (array_key_exists($key,$enabledAssets))
												$checked = 'checked="checked"';
											?>

											<div class="checkbox">
												<label for="checkbox_<?php echo $key;?>" class="form-check-label ">
													<input <?php echo $checked;?> type="checkbox" name="blockchainAsset[<?php echo $key;?>]" value="<?php echo $key;?>" class="form-check-input"><?php echo $value;?>
												</label>

											</div>
										<?php } ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<?php echo $form->labelEx($settings,'Seleziona Gateway'); ?>
								<?php echo $form->dropDownList($settings,'id_gateway',$listaGateways,array('class'=>'form-control'));	?>
							</div>
							<?php echo $form->hiddenField($settings,'id_exchange',array('value'=>1)); ?>

					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<?php echo CHtml::submitButton('Approva', array('class' => 'btn btn-primary ')); ?>
			</div>
		</div>
	</div>
</div>

<!-- modal rifiuto -->
<div class="modal fade" id="scrollmodalModello" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabelModello" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="scrollmodalLabelModello">Motivazioni Rifiuto Iscrizione</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<input type="hidden" name='rifiuto' id='rifiuto' value='0'>
						<textarea name="motivazione" rows="5" cols="40" class="form-control">
						</textarea>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<?php echo CHtml::Button('Conferma', array('class' => 'btn btn-primary ','id'=>'disclaim-button')); ?>
			</div>
		</div>
	</div>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->
