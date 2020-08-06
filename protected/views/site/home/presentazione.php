<style>
	#contenitore-logo{
		width: 95px;
		height: 95px;
		left:0px;
		padding: 5px;
	}

	#div-yellow {
		position: relative;
		width: 100%; height: 100%; top: 50%; left: 0;
		background : red;
		border: 4px solid black;
	    -moz-border-radius:6px;
		-khtml-border-radius: 6px;
		-webkit-border-radius: 6px;
		border-radius:6px;
	}
	#div-red {
		position: relative;
		width: 100%; height: 100%; top: -90%; left: 40%;

		background : rgba(255,220,116,1);
	 	border: 4px solid black;
		-moz-border-radius:6px;
		-khtml-border-radius: 6px;
		-webkit-border-radius: 6px;
		border-radius:6px;
	}
	#nome-associazione{
		position:absolute;
		top:25px;
		left:160px;
		font-size:28px;
		font-family: Arial;
		/* font-weight: bold; */
		color: #eee;
	}
	.img-comune{
		max-width: 150px;
	}
</style>
<div class="row">&nbsp;</div>
<div class="row">
	<div class="col-md-4">
		<div class="card border border-primary ">
			<div class="card-body">
				<p class="card-text text-primary">
					<center>
						<img class='img-comune' src="css/images/logo_comune_trasparente.png" alt="comune di napoli">
					</center>
				</p>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="card border border-primary bg-dark">
			<div class="card-body">
				<p class="card-text text-primary">
					<div id='contenitore-logo'>
						<div id='div-yellow'></div>
						<div id='div-red'></div>
						<div id='nome-associazione'>
							<p>Associazione</p>
							<p>Napoli</p>
							<p>Blockchain</p>
						</div>
					</div>
					<div class="row">&nbsp;</div>
					<div class="row">&nbsp;</div>
				</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card border border-primary ">
			<div class="card-body">
				<p class="card-text text-primary">
					<center>
						<a href="<?php echo Yii::app()->createUrl('site/login');?>">
							<img src="css/images/ngatepay-trasparente.png" alt="ngatepay" title="Clicca per collegarti" >
						</a>
					</center>
				</p>
			</div>
		</div>
	</div>
</div>
<div class="row">&nbsp;</div>
<div class="row">
	<div class="col-md-4">
		<div class="card border border-primary bg-light">
			<div class="card-header bg-dark">
				<strong class="card-title text-light">Progetto Blockchain Napoli</strong>
			</div>
			<div class="card-body">
				<p class="card-text text-dark">
					Nel mese di aprile 2018, il Comune di Napoli ha effettuato una chiamata pubblica
					per il reclutamento di volontari al fine di realizzare alcuni progetti in ambito blockchain
					e cryptovalute. A questa chiamata hanno risposto pi&uacute; di 300 persone. Per quanto riguarda
					i pagamenti in criptovaluta, si &egrave; formato un ristretto gruppo di tecnici il cui obiettivo &egrave;
					stato la realizzazione di un sistema di pagamento virtuale che permetta agli esercenti di accettare
					<strong>Bitcoin</strong> presso la propria attivit&agrave;
				</p>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="card border border-primary bg-dark">
			<div class="card-header text-light">
				<strong class="card-title">Associazione Blockchain Napoli</strong>
			</div>
			<div class="card-body bg-secondary" >
				<p class="card-text text-light">
				L'Associazione Blockchain Napoli si propone di:<br>
				</p>
				<p class="card-text text-light">
					<i class="fa fa-check"></i> diffondere la conoscenza della Blockchain e delle cryptovalute
				</p>
				<p class="card-text text-light">
					<i class="fa fa-check"></i> aiutare i soci a creare un senso di consapevolezza sull'utilit&agrave; di tali tecnologie nella vita
				</p>
				<p class="card-text text-light">
					<i class="fa fa-check"></i> promuovere la tecnologia Blockchain come veicolo per l'inclusione sociale, per il rafforzamento dell partecipazione civica
				</p>
				<p class="card-text text-light">
					<i class="fa fa-check"></i> supportare il Comune di Napoli nello sviluppo, diffusione ed implementazione dell'utilizzo della Blockchain
				</p>
			</div>
		</div>
	</div>


	<div class="col-md-4">
		<div class="card border border-primary bg-teal">
			<div class="card-header text-primary">
				<strong class="card-title">Napoli Gateway of Payments, cos'&egrave;?</strong>
			</div>
			<div class="card-body">
				<p class="card-text text-primary">
					E' un software sviluppato all'interno del progetto Napoli Blockchain da volontari e appassionati, di
					consolidata esperienza nei vari ambiti tecnologici (programmatori, sistemisti, ecc.), la cui sinergia
					ha fornito le basi per la creazione di un nuovo sistema di pagamento per le attivit&agrave; commerciali
					ed i professionisti.
				</p>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="card border border-primary bg-info">
			<div class="card-header">
				<strong class="card-title text-light">Shop</strong>
			</div>
			<div class="card-body">
				<p class="card-text text-light">
					Accettare pagamenti presso il tuo negozio o ristorante
				</p>
			</div>
		</div>

	</div>
	<div class="col-md-4">
		<div class="card border border-primary bg-warning">
			<div class="card-header">
				<strong class="card-title">E-Commerce</strong>
			</div>
			<div class="card-body">
				<p class="card-text">
					Accettare pagamenti on line con Bitcoin
				</p>
			</div>
		</div>

	</div>
	<div class="col-md-4">
		<div class="card border border-primary bg-success">
			<div class="card-header">
				<strong class="card-title text-light">Donazioni</strong>
			</div>
			<div class="card-body">
				<p class="card-text text-light">
					Accettare donazioni on line in Bitcoin e altre 50 cryptovalute
				</p>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="card bg-dark">
			<div class="card-body">
				<center>
					<p class="card-text text-light">
						Guarda un video di dimostrazione del funzionamento del software
					</p>
					<video width="320" height="240" controls>
						<source src="css/video/Pagamento_con_Token.mp4" type="video/mp4">
					  	<!-- <source src="movie.ogg" type="video/ogg"> -->
						  Your browser does not support the video tag.
					</video>
				</center>
			</div>
		</div>

	</div>
	<div class="col-md-4">
		<div class="card bg-white">
			<div class="card-header">
				<strong class="card-title text-dark">Cosa vuoi fare?</strong>
			</div>
			<img class="card-img-top" src="themes/cool/images/bg-title-01.jpg">
			<div class="card-body">
				<center>
					<p><a href="<?php echo Yii::app()->createUrl('site/login'); ?>">
						<button class="au-btn au-btn--block au-btn--blue m-b-20">
							Accedi</button></a></p>
					<p><a href="<?php echo Yii::app()->createUrl('site/register'); ?>">
						<button class="au-btn au-btn--block au-btn--green m-b-20">
							Iscriviti</button></a></p>
				</center>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="card">
			<img class="card-img-top" src="themes/cool/images/bg-title-02.jpg">
			<div class="card-body">
				<center>
					<p><a href="<?php echo Yii::app()->createUrl('site/contact'); ?>">
						<button class="au-btn au-btn--block au-btn--blue m-b-20">
							Contattaci</button></a></p>
				</center>
			</div>
		</div>
	</div>

</div>
