<?php

if (Yii::app()->user->objUser['privilegi'] == 10){
    //$tabList['Token'] = array('id'=>'token','content'=>$this->renderPartial('user/_token',array('model'=>$model,'wallets'=>$wallets,'walletsProvider'=>$walletsProvider),TRUE));

    $tabList['Gateway']   = array('id'=>'gateway','content'=>$this->renderPartial('user/_gateway',array('model'=>$model, ),TRUE));
    $tabList['Exchange']  = array('id'=>'exchange','content'=>$this->renderPartial('user/_exchange',array('model'=>$model),TRUE));
    $tabList['Banca']  = array('id'=>'bank','content'=>$this->renderPartial('user/_bank',array('model'=>$model),TRUE));
}

//$tabList['BTCPay Server']   = array('id'=>'btcpayserver','content'=>$this->renderPartial('user/_btcpayserver',array('model'=>$model),TRUE));

?>
<div class='section__content section__content--p30'>
	<div class='container-fluid'>
		<div class="row">
			<div class="col-lg-7">
				<div class="card">
					<div class="card-header">
						<h2 class='title-1 m-b-25'><small>Impostazioni</small></h2>
					</div>
					<div class="card-body card-block">

						<?php $this->widget('zii.widgets.jui.CJuiTabs',array(

							'tabs' => $tabList,
							'options'=>array(
								'collapsible'=>true,
							),
							'id'=>'MyTab-Menu',
						));
						?>


					</div>
				</div>
			</div>
		</div>
		<?php echo Logo::footer(); ?>
	</div>
</div>
