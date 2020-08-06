<?php
	$storeURL = Yii::app()->createUrl('stores/create');
?>
<div class="card">
	<div class="card-header">
		<h2 class="card-title text-primary">Benvenuto!</h2>
	</div>
	<div class="card-body">
		<div class="typo-articles">
			<p class="text-dark">
				Se vuoi accettare pagamenti in Bitcoin devi creare un Negozio ed attivare il POS.<br>
				Segui le semplici indicazioni della guida che ti forniremo passo dopo passo.
			</p>
			<p><b><i>Crea tutti i negozi di cui hai bisogno. Ad ognuno di essi puoi associare uno o più POS.</i></b></p>
			<p>Per creare il tuo primo negozio clicca il pulsante seguente: </p>
				<p><a href="<?php echo $storeURL;?>">
					<button type="button" class="btn btn-primary">Nuovo Negozio</button>
			</a></p>
		</div>
	</div>
</div>
