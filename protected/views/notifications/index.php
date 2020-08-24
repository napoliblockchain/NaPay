<div class="form">
<?php
Yii::import('libs.NaPacks.Logo');
Yii::import('libs.NaPacks.WebApp');
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'notifications-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
));


?>
<div class='section__content section__content--p30'>
	<div class='container-fluid container-wallet'>
		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-ove rlay--semitransparent">
					<div class="card-header ">
						<i class="zmdi zmdi-comment-text"></i>
						<span class="card-title"><?php echo Yii::t('lang','Messages');?></span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card ">
							<?php
							$this->widget('ext.selgridview.SelGridView', array(
								'id'=>'notifications-grid',
								'selectableRows' => 2, // valori sono 0,1,2
								//'hideHeader' => true,
								// 'htmlOptions' => array('class' => 'table table-borderless  table-data4 table-wallet ',
								// 						'style' => 'border: 0px;'
								// 					),
								// 'htmlOptions' => array('class' => 'table table-borderless  table-data4 table-wallet'),
								'htmlOptions' => array('class' => 'table table-wallet'),
							    'dataProvider'=>$dataProvider,
								// 'pager'=>array(
								// 	//'header'=>'Go to page:',
								// 	//'cssFile'=>Yii::app()->theme->baseUrl
								// 	'cssFile'=>Yii::app()->request->baseUrl."/css/yiipager.css",
								// 	'prevPageLabel'=>'<',
								// 	'nextPageLabel'=>'>',
								// 	'firstPageLabel'=>'<<',
								// 	'lastPageLabel'=>'>>',
								// ),
								'columns' => array(
									array(
									   'id'=>'selectedNotifications',
									   'class'=>'CCheckBoxColumn',
									   'htmlOptions'=>array('style'=>'padding:0px 0px 0px 0px; margin:0px 0px 0px 0px;vertical-align:middle;'),
								    ),
									array(
										'name'=>Yii::t('lang','Select all'),
										'type'=>'raw',
										//'value' => 'CHtml::link(CHtml::encode(date("d/m/Y H:i:s",$data->invoice_timestamp)), Yii::app()->createUrl("wallet/details")."&id=".CHtml::encode(crypt::Encrypt($data->id_token)))',
										'value' => 'CHtml::link(WebApp::dateLN($data->timestamp), Yii::app()->createUrl("tokens/view",["id"=>crypt::Encrypt($data->id_tocheck)]) )',
										'htmlOptions'=>array('style'=>'vertical-align:middle;'),
									),

									array(
							            'name'=>'',
									   'type' => 'raw',
										'value'=>'CHtml::link(WebApp::walletStatus($data->status), $data->url)',
										'htmlOptions'=>array('style'=>'vertical-align:middle;'),
										'cssClassExpression' => '($data->type_notification == "help" ||  $data->type_notification == "contact" ? "is-hidden" : ( $data->status == "sent" ) ? "denied" : (( $data->status == "complete" ) ? "process" : "desc incorso"))',
							        ),

									array(
							            'name'=>'',
										'type' => 'raw',
										'value' => 'CHtml::link(WebApp::translateMSG($data->description), $data->url)',
										'htmlOptions'=>array('style'=>'vertical-align:middle;'),
							        ),

									array(
							            'name'=>'',
										'type' => 'raw',
							            'value'=>'($data->type_notification == "help" ||  $data->type_notification == "contact" ? "" : $data->price)',
										'htmlOptions'=>array('style'=>'vertical-align:middle;'),
										'cssClassExpression' => '($data->type_notification == "help" ||  $data->type_notification == "contact" ? "is-hidden" : "")',
							        ),
								)
							));
							?>

						</div>

					</div>
					<div class="card-footer">
						<?php if ($dataProvider->totalItemCount >0) { ?>
						<div class="form-group">
							<?php echo CHtml::submitButton(Yii::t('lang','delete messages'), array('class' => 'btn btn-primary ')); ?>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
			<?php echo Logo::footer(); ?>
	</div>
</div>






<?php $this->endWidget(); ?>
</div><!-- form -->
