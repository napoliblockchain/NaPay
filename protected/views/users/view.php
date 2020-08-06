<?php
$viewName = 'Utente';

$criteria=new CDbCriteria();
$criteria->compare('id_user',$model->id_user,false);
$pagamenti = Pagamenti::model()->findAll($criteria);
$totalePagamenti=count($pagamenti);

$idUserCrypted = crypt::Encrypt($model->id_user);

$resetPwdURL = Yii::app()->createUrl('users/resetpwd'); //
$sollecitoURL = Yii::app()->createUrl('users/sollecito');
$listaPagamentiURL = Yii::app()->createUrl('pagamenti/index').'&id='.$idUserCrypted;
$modifyURL = Yii::app()->createUrl('users/update').'&id='.$idUserCrypted;
$changeURL = Yii::app()->createUrl('users/changepwd').'&id='.$idUserCrypted;
$google2faURL = Yii::app()->createUrl('users/2fa').'&id='.$idUserCrypted;
$google2faRemoveURL = Yii::app()->createUrl('users/2faRemove').'&id='.$idUserCrypted;
$deleteURL = Yii::app()->createUrl('users/delete').'&id='.$idUserCrypted;
$modifyConsensus = Yii::app()->createUrl('users/consensus');

//per effettuare la sottoscrizione notifiche push
$urlSavesubscription = Yii::app()->createUrl('users/saveSubscription'); //save subscription for push messages
$settingsWebapp = Settings::load();
$vapidPublicKey = $settingsWebapp->VapidPublic;

include ('js_view.php');

#echo "<pre>".print_r($userSettings,true)."</pre>";
#exit;
function consenso($t){
	if ($t <> 0){
		echo date("d/m/Y h:i",$t);
	}else{
		echo 'Non concesso';
	}
}

?>
<div class='section__content section__content--p30'>
<div class='container-fluid'>
	<div class="row">
		<div class="col-lg-12">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-header ">
					<i class="fas fa-users"></i>
					<span class="card-title">Dettagli Utente</span>
				</div>
				<div class="card-body">

					<div class="table-responsive table--no-card ">
						<?php $this->widget('zii.widgets.CDetailView', array(
							//'htmlOptions' => array('class' => 'table table-borderless table-striped '),
							'data'=>$model,
							'attributes'=>array(
								'surname',
								'name',
								'email',
								array(
									'type'=> 'raw',
									'name'=>'telefono',
									'value'=>(isset($userSettings->telefono) ? $userSettings->telefono : 'Non inserito'),
								),

								array(
						            'label'=>'Carica',
						            'value'=>Cariche::model()->findByPk($model->id_carica)->description
						        ),
								array(
									'type'=> 'raw',
									'name'=>'iscrizione',
									'value'=>WebApp::StatoPagamenti($model->id_user),
								),
								array(
									'type'=> 'raw',
									'name'=>'denomination',
									'value'=>$model->denomination,
									'visible'=> $model->corporate == 1 ? true : false,
								),
								'vat',
								'address',
								'cap',
								array(
									'name'=>'Citt&agrave;',
									'type'=> 'raw',
									'value'=>ComuniItaliani::model()->findByPk($model->city)->citta
								),
								'country',
							),
						));
						?>
					</div>
				</div>
				<div class="card-footer">
					<div class="row">
						<div class="col-md-6">
							<a href="<?php echo $modifyURL;?>">
								<button type="button" class="btn btn-secondary">Modifica</button>
							</a>

							<?php if (Yii::app()->user->objUser['privilegi'] == 20){ ?>
									<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#resetpwdModal"><span class="resetpwd__content">Reset password</span></button>

									<?php if ($model->id_user != 1){ ?>
										<a href="<?php echo $listaPagamentiURL;?>">
											<button type="button" class="btn btn-primary">Pagamenti
			                      				<span class="badge badge-light"><?php echo $totalePagamenti; ?></span>
			              					</button>
										</a>
										<?php if ($model->status_activation_code == 0){ ?>
												<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#scrollmodalModello">Elimina</button>
										<?php } ?>
										<button type="button" class="btn btn-dark" data-toggle="modal" data-target="#sollecitoModal">Sollecito</button>

										<div class="row div_resetpwd__text" style="display:none;">
											<div class="col-md-12">
												</br>
												<div class="alert alert-warning resetpwd__text" ></div>
											</div>
										</div>
										<div class="row div_sollecito__text" style="display:none;">
											<div class="col-md-12">
												</br>
												<div class="alert alert-info sollecito__text" ></div>
											</div>
										</div>
									<?php } ?>
							<?php }else{ ?>
								<a href="<?php echo $changeURL;?>">
									<button type="button" class="btn btn-warning">Cambio password</button>
								</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if (Yii::app()->user->objUser['privilegi'] != 20){ ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-body">
						<strong class="card-title">Sicurezza a 2 Fattori per il Wallet TTS</strong>
						<span class="float-right">
							<!-- sei un socio corporate, e hai creato il tuo account merchant -->
							<?php if ($model->ga_secret_key == '' && $model->corporate ==1 && Yii::app()->user->objUser['privilegi'] > 5){ ?>
									<a href="<?php echo $google2faURL;?>">
										<button type="button" class="btn btn-success btn-sm">Abilita Google 2FA</button>
									</a>
							<?php }
								if ($model->ga_secret_key != ''){ ?>
									<a href="<?php echo $google2faRemoveURL;?>">
										<button type="button" class="btn btn-danger btn-sm">Disabilita 2FA</button>
									</a>
							<?php } ?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-9">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-body">
						<strong class="card-title">Notifiche PUSH</strong>
						<button disabled type='button' class="js-push-btn-modal btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#pushEnableModal">Abilita</button>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
	<div class="row">
		<div class="col-lg-9">
			<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
				<div class="card-body">
					<strong class="card-title">Download Recovery SHEET</strong>
					<a href="documenti/RECOVERY_SHEET.pdf" class="float-right" target="_blank"/>
						<button type='button' class="btn btn-primary btn-sm" >Download</button>
					</a>
				</div>
			</div>
		</div>
	</div>



	<?php
	// se non sei amministratore
	if ($model->id_users_type <> 3){
		?>
		<div class="row">
			<div class="col-lg-9">
				<div class="au-card au-card--no-shadow au-card--no-pad m-b-40 bg-overlay--semitransparent">
					<div class="card-header ">
						<span class="card-title">Consensi per il trattamento dei dati e condizioni di utilizzo del software</span>
					</div>
					<div class="card-body">
						<div class="table-responsive table--no-card ">

							<table class="detail-view">
								<tbody>
									<tr class="odd">
										<th>Statuto</th>
										<td><?php (isset($userSettings->timestamp_consenso_statuto) ? consenso($userSettings->timestamp_consenso_statuto) : consenso(0))?></td>
									</tr>
									<tr class="even">
										<th>Privacy</th>
										<td><?php (isset($userSettings->timestamp_consenso_privacy) ? consenso($userSettings->timestamp_consenso_privacy) : consenso(0))?></td>
									</tr>
									<tr class="odd">
										<th>Termini di servizio</th>
										<td><?php (isset($userSettings->timestamp_consenso_termini) ? consenso($userSettings->timestamp_consenso_termini) : consenso(0))?></td>
									</tr>

									<tr class="even">
										<th>Marketing</th>
										<td>
											<span id="marketing_text">
												<?php (isset($userSettings->timestamp_consenso_marketing) ? consenso($userSettings->timestamp_consenso_marketing) : consenso(0))?>
											</span>
											<!-- VERIFICO CHE IL SOCIO VISUALIZZATO SIA LO STESSO DI CHI MODIFICA PER MOSTRARE IL PULSANTE -->
											<?php if (Yii::app()->user->objUser['id_user'] == $model->id_user) { ?>
												<button type='button' class="js-consenso-btn-modal btn btn-secondary btn-sm float-right" data-toggle="modal" data-target="#consensoModal">Modifica</button>
											<?php } ?>
										</td>
									</tr>

									<?php if (isset($userSettings->timestamp_consenso_pos) && $userSettings->timestamp_consenso_pos <> 0){ ?>
									<tr class="odd">
										<th>Utilizzo del POS</th>
										<td><?php (isset($userSettings->timestamp_consenso_pos) ? consenso($userSettings->timestamp_consenso_pos) : consenso(0))?></td>
									</tr>
									<?php } ?>

								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
<?php echo Logo::footer(); ?>
</div>


<!-- MODIFICA CONSENSO -->
<div class="modal fade" id="consensoModal" tabindex="-1" role="dialog" aria-labelledby="consensoModalLabel" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="consensoModalLabel">Modifica consenso</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">

					<?php if (isset($userSettings->timestamp_consenso_marketing) && $userSettings->timestamp_consenso_marketing <> 0) { ?>
						<p>Vuoi rimuovere il consenso?</p>
					<?php }else{ ?>
						<p>Vuoi confermare il consenso?</p>
					<?php } ?>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<button type="button"  class="btn btn-primary" data-dismiss="modal" id="consenso-button">Conferma</button>
			</div>
		</div>
	</div>
</div>

<!-- ELIMINA USER -->
<div class="modal fade" id="scrollmodalModello" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabelModello" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="scrollmodalLabelModello">Elimina utente</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">


						<p>
						Un utente di cui non sia stata approvata l'iscrizione è l'unico che può essere eliminato dall'archivio.
						Sei sicuro di voler cancellare questo Utente?
					</p>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<a href="<?php echo $deleteURL;?>">
					<?php echo CHtml::Button('Conferma', array('class' => 'btn btn-primary ')); ?>
				</a>
			</div>
		</div>
	</div>
</div>
<!-- SOLLECITO -->
<div class="modal fade" id="sollecitoModal" tabindex="-1" role="dialog" aria-labelledby="sollecitoModalLabel" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="sollecitoModalLabel">Invia sollecito di pagamento</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">


						<p>
							Vuoi inviare un sollecito di pagamento per questo Utente?
						</p>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<button type="button"  class="btn btn-primary" data-dismiss="modal" id="sollecito-button">Conferma</button>
			</div>
		</div>
	</div>
</div>
<!-- ABILITA PUSH -->
<div class="modal fade " id="pushEnableModal" tabindex="-1" role="dialog" aria-labelledby="pushEnableModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content  ">
			<div class="modal-header">
				<h5 class="modal-title" id="pushEnableModalLabel">Notifiche Push</h5>
			</div>
			<div class="modal-body ">
                <p><b>Abilitazione:</b>
                    <br>Abilitando questa impostazione riceverai notifiche <b>push</b> quando ci saranno nuove transazioni.</b><br><br>
                    <i>Le notifiche vengono abilitate per singolo dispositivo. Per ricevere notifiche su altri dispositivi
                    bisogna effettuare il login da ciascuno, effettuare l'abilitazione ed assicurarsi di essere online.
                    Assicurati di rispondere <b>Consenti</b> dopo aver cliccato sul pulsante di conferma.</i>
                </p>
                <p>
                </p>
                <p><b>Disabilitazione:</b>
                    <br>Disabilitando le notifiche <b>push</b> non riceverai più messaggi istantanei per le transazioni.</b><br><br>
                    <i>Disabilitare le notifiche push da questo dispositivo potrebbe eliminare la sottoscrizione anche di eventuali
                    altri dispositivi collegati.</i>
                </p>
			</div>
			<div class="modal-footer">
                <div class="form-group">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" >Annulla</button>
				</div>
				<div class="form-group">
					<button type="button" class="js-push-btn btn btn-primary" data-dismiss="modal">Conferma</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- RESET PASSWORD -->
<div class="modal fade" id="resetpwdModal" tabindex="-1" role="dialog" aria-labelledby="resetpwdModalLabel" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title" id="resetpwdModalLabel">Reset password amministratore</h3>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">


						<p>
							Questa operazione invierà nella casella di posta dell'Associazione un link per effettuare il cambio della password.<br>
							Vuoi proseguire?
						</p>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
				<button type="button"  class="btn btn-primary" data-dismiss="modal" id="resetpwd-button">Conferma</button>
			</div>
		</div>
	</div>
</div>
