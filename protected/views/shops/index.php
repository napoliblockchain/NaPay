<?php
/* @var $this PosController */
/* @var $dataProvider CActiveDataProvider */
$actionURL = Yii::app()->createUrl('shops/create');
(Yii::app()->user->objUser['privilegi'] == 20) ? $visible = true : $visible = false;
?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>

		<div class="row">
			<div class="col-lg-12">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<i class="fas fa-globe"></i>
						<span class="card-title">Lista Self POS</span>

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
						<div class="table-responsive table--no-card ">
							<?php $this->widget('zii.widgets.grid.CGridView', array(
								//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
								'htmlOptions' => array('class' => 'table table-striped text-dark table-data4 table-wallet'),
							    'dataProvider'=>$dataProvider,
								'columns' => array(
									array(
							            'name'=>'id_merchant',
										'value'=> '(Merchants::model()->findByPk($data->id_merchant) === null)
														? null : isset(Merchants::model()->findByPk($data->id_merchant)->denomination)
														? CHtml::link(CHtml::encode(Merchants::model()->findByPk($data->id_merchant)->denomination), Yii::app()->createUrl("shops/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_shop))) : ""',
										'visible' => $visible,
										'type'=>'raw',
							        ),
									array(
							            'name'=>'id_store',
										'value'=>'isset(Stores::model()->findByPk($data->id_store)->denomination)
													? CHtml::link(CHtml::encode(Stores::model()->findByPk($data->id_store)->denomination), Yii::app()->createUrl("shops/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_shop))) : ""',
										'type'=>'raw',

							        ),
									array(
							            'name'=>'denomination',
										'type'=>'raw',
										'value' => '(isset($data->denomination) ? CHtml::link(CHtml::encode($data->denomination), Yii::app()->createUrl("shops/view")."&id=".CHtml::encode(crypt::Encrypt($data->id_shop))) : "")',
							        ),
									// array(
							        //     'name'=>'mpk',
									// 	'type'=>'raw',
									// 	'value'=> 'substr($data->mpk,0,15)."..."',
									// 	'visible'=>$visible,
							        // ),
									array(
										'name'=>'',
										'value'=> '',
									)

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
