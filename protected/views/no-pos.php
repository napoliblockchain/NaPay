<?php
	$posURL = Yii::app()->createUrl('pos/create');
?>
<div class="card">
	<div class="card-header">
		<h2 class="card-title text-primary">Manca ancora un passo...</h2>
	</div>
	<div class="card-body">
		<div class="typo-articles">
			<p class="text-dark">
				Hai creato il Negozio, ma devi ancora completare l'operazione di creazione del POS.<br>
			</p>
			</div>

			<p class="text-dark">Clicca sul pulsante per crearlo.</p>
			<p><a href="<?php echo $posURL;?>">
				<button type="button" class="btn btn-primary">Nuovo POS</button>
			</a></p>
		</div>
	</div>
</div>
