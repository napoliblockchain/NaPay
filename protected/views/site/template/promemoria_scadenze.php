<?php
$criteriaPromemoria=new CDbCriteria();
$criteriaPromemoria->compare('status_activation_code',1,false);

//creo la lista degli utenti abilitati
$usersPromemoria = Users::model()->findAll($criteriaPromemoria);
$remindersPromemoria = array();



// per ciascun utente verifico i pagamenti
foreach($usersPromemoria as $itemPromemoria) {
	$timelapsePromemoria = WebApp::StatoPagamenti($itemPromemoria->id_user,true);
	if ($timelapsePromemoria > -45 && $timelapsePromemoria < 365){
		$remindersPromemoria[] = $itemPromemoria;
	}
}

$dataPromemoria=new CActiveDataProvider('Users', array(
	'data' => $remindersPromemoria,
));


if (count($remindersPromemoria) > 0){
?>
<div class="row m-t-25">
	<div class="col col-lg-12">
		<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
			<div class="card-header ">
				<i class="fa fa-exclamation-triangle"></i>
				<span class="card-title">Lista utenti con pagamenti in scadenza</span>
			</div>
			<div class="card-body">
				<div class="table-responsive table--no-card ">
					<?php
					$this->widget('zii.widgets.grid.CGridView', array(
						'id'=>'users-grid',
						//'htmlOptions' => array('class' => 'table table table-borderless table-data3'),
						//'htmlOptions' => array('class' => 'table table-borderless table-data3 table-earning '),
						'dataProvider'=>$dataPromemoria,
						'columns' => array(

							array(
								'type' => 'raw',
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
								'type'=> 'raw',
								'header'=>'Iscrizione',
								'name'=>'id_user',
								'value'=>'WebApp::StatoPagamenti($data->id_user)',
							),
							array(
								'type'=> 'raw',
								'name'=>'id_user',
								'header'=>'Solleciti',
								'value'=>'WebApp::ContaSollecitiPagamenti($data->id_user)',
							),
						)
					));
					?>

				</div>
			</div>
			<div class="card-footer">
				<a class="js-arrow" href="<?php echo Yii::app()->createUrl('users/reminder'); ?>">
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
