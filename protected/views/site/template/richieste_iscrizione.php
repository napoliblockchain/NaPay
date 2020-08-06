<?php

$criteriaIscrizione=new CDbCriteria();
$criteriaIscrizione->compare('status_activation_code',0,false);

$dataIscrizione=new CActiveDataProvider('Users', array(
	'criteria'=>$criteriaIscrizione,
));

if ($dataIscrizione->totalItemCount > 0){
?>

<div class="row m-t-25">
	<div class="col col-lg-12">
		<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
			<div class="card-header ">
				<i class="fa fa-eye"></i>
				<span class="card-title">Richieste di iscrizione</span>
			</div>
			<div class="card-body">
				<div class="table-responsive table--no-card ">
					<?php
					$this->widget('zii.widgets.grid.CGridView', array(
						'id'=>'users-grid',
						//'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
						//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
						'dataProvider'=>$dataIscrizione,
						'columns' => array(

							array(
								'type'=> 'raw',
								'name'=>'name',
								'value' => 'strtoupper(Users::model()->findByPk($data->id_user)->surname).chr(32).Users::model()->findByPk($data->id_user)->name',
							),
							array(
								'name'=>'email',
								'type'=>'raw',
								'value' => '$data->email',
							),
							array(
								'name'=>'id_carica',
								'value'=>'Cariche::model()->findByPk($data->id_carica)->description',
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
						)
					));
					?>
				</div>
			</div>
			<div class="card-footer">
				<a class="js-arrow" href="<?php echo Yii::app()->createUrl('users/approve'); ?>">
					<button class="btn btn-primary">
						 Gestisci
					</button>
				</a>
			</div>
		</div>


	</div>
</div>

<?php
}
?>
