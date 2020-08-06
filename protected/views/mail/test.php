<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900italic,900' rel='stylesheet' type='text/css'>
<table border='0' cellpadding='0' cellspacing='0' style="font-family:Roboto;
															padding: 3px 3px 3px 3px;
															outline: none;
															background-color: #f0f0f0;
															border: none;
															border-radius: 5px;
															box-shadow: 0 3px #999;">
  <tbody>
	  <tr>
		  <td>
			  <div class="row">
                  <div class="col" style="text-align:left;">
                      <img width="250" src="<?php echo $logo; ?>" alt="" >
                  </div>
              </div>
		  </td>
  </tr>
  <tr>

	<td>
	  <h1>Iscrizione</h1>
	  <br>
	  Complimenti!
	  <br>
      <p>
          La tua iscrizione a <strong><?php echo Yii::app()->params['nomeAssociazione']; ?></strong> è stata inoltrata!
          Riceverai una mail con la conferma della registrazione non appena sarà approvata.<br>
		  Successivamente, riceverai le istruzioni per effettuare il pagamento della Quota Associativa necessaria per attivare il tuo account.
      </p>

	</td>
</tr>
<tr>
	<td>
		<br>
		Conserva questi dati, ti serviranno per effettuare il collegamento:
		<br>
		<p style="padding: 3px 3px 3px 3px;
					outline: none;
					background-color: #9Defa0;
					border: none;
					border-radius: 5px;
					box-shadow: 0 3px #555;
					min-width: 100px;
					text-shadow: 1px 1px 2px black;">
			Username: <?php echo $email; ?>
			<br>
			Password: <?php echo $password; ?>
		</p>
		<p>
		<br>
		<?php echo Yii::app()->params['adminName']; ?> Team
		<br><br></p>
	</td>
</tr>
</tbody>
</table>


<?php
exit;
?>
