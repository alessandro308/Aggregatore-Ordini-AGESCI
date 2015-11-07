<?php
	require("menu.php");
	require("mail_config.php");
	if($useSMTP)
		require("PHPMailer/PHPMailerAutoload.php");
	$db = new ArticoliDb(".");
	

	$items = json_decode(urldecode($_POST["items"]), true);
	$totale = $_POST["totale"];
	if (isset($_POST['name']))  
		$name = $_POST["name"]; else $name = "undefined";
	if (isset($_POST['mail']))  
		$mail = $_POST["mail"]; else $mail = "undefined";
	if (isset($_POST['phone']))  
		$phone = $_POST["phone"]; else $phone = "undefined";
	if (isset($_POST['pagamento']))   
		$pagamento = $_POST['pagamento']; else $pagamento = "undefined";
	if (isset($_POST['branca']))   
		$branca = $_POST['branca']; else $branca = "undefined";
	//var_dump($items);
	
	$idOrdine = $db -> addOrder($name, $mail, $phone, $items, $totale, $pagamento, $branca);
	$mailBody = "<html><body>Il tuo ordine e' andato a buon fine<br/>";
	$mailBody.= "Il totale dell'ordine (da saldare in anticipo) e' di euro ".$totale;
	$mailBody.= PHP_EOL."<br/>Per vedere la tua ricevuta vai pagina (".$ricevutaDir.'/showReceipt.php?mail='.$mail.'&id='.$idOrdine.')'."<br/><br/>".PHP_EOL.PHP_EOL;
	if($pagamento == "A mano")
		$mailBody.= "Per confermare l'ordine e' necessario il pagamento. Puoi saldare la quota portando i soldi e una copia della ricevuta direttamente ad un capo a fine riunione (o in alternativa, se non si riesce a stampare la pagina, bastera' il numero dell'ordine e l'importo).";
	else
		$mailBody.= "Puoi effettuare il bonifico al seguente IBAN: ".$iban;
	$mailBody .= "<br/> L'ordine sarà valido solo dopo il pagamento</body></html>";
	if($useSMTP){
		$sendmail = new PHPMailer();  // create a new object
		$sendmail->IsSMTP(); // enable SMTP
		$sendmail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
		$sendmail->Mailer = "smtp";
		$sendmail->SMTPAuth = true;  // authentication enabled
		$sendmail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
		$sendmail->Host = $host;
		$sendmail->Port = $port; 
		$sendmail->Username = $username;  
		$sendmail->Password = $password;           
		$sendmail->SetFrom($my_email, "");
		$sendmail->Subject = "Ricevuta Ordine AGESCI";
		$sendmail->Body = $mailBody;
		$sendmail->addCustomHeader("Content-Type: text/html; charset=ISO-8859-1\r\n");
		$to      = $mail;
		$sendmail->AddAddress($to);
		if(!$sendmail->Send())
			echo "<h2>Mail error: ".$sendmail->ErrorInfo.'</h2>'; 
	}
	else{
		$headers  = "From:".$your_name."<".$my_email.">\n";
		$headers .= "X-Sender: Ordini Agesci"."<".$my_email.">\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		$headers .= "X-Priority: 1\n"; // Urgent message!
		$headers .= "Return-Path: ".$my_email."\n"; // Return path for errors
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		mail($mail, "Ricevuta Ordine #".$idOrdine, $mailBody, $headers);
	}
?>
<title> Ricevuta Ordini </title>
<script>
	$(window).load(function(){
		$('#orderModal').modal('show');
	});
</script>
</head>
<body>
<div class="container">
	<div style="
	margin: auto;
	width: 60%;
	padding: 10px;">
	<img src="ricevuta/header.jpg"/></div>
  <h2>Ricevuta Ordine Uniformi #<?php echo $idOrdine; ?></h2>
  <h4><?php echo "Nome: <b>".$name."</b> - eMail: <b>".$mail."</b> <br/> Telefono: <b>".$phone."</b> - Branca: <b>".$branca." </b> <br/> <br/>  Modalità Pagamento: <b>".$pagamento; ?></b></h4>
  <h5>PAGATO: ___ - CONSEGNATO: ___ </h5><br/>
  <table class="table table-hover">
	<thead>
	  <tr>
		<th>Articolo</th>
		<th>Taglia</th>
		<th>Quantità</th>
		<th>Prezzo (unità)</th>
	  </tr>
	</thead>
	<tbody>
	<?php
		for($i=0; $i<count($items); $i++){
			echo "<tr>";
			echo "<td>".$items[$i]["item"]."</td>";
			echo "<td>".$items[$i]["taglia"]."</td>";
			echo "<td>".$items[$i]["quantity"]."</td>";
			$prezzo = $db -> getPrice($items[$i]["item"]);
			echo "<td>€ ".number_format((float)$prezzo, 2, ',', '')."</td>";
			echo "</tr>";
		}
	?>
	<tr>
		<td></td>
		<td></td>
		<td><h4>TOTALE:</h4></td>
		<td><h4><?php echo "€ ".number_format((float)$totale, 2, ',', '');?></h4></td>
	</tr>
	</tbody>
  </table>
</div>

<!-- Modal -->
<div id="orderModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Ordine inviato correttamente</h4>
	  </div>
	  <div class="modal-body">
		<?php if($pagamento == "A mano" || $pagamento == "undefined"){?>
			<h4> Per confermare l'ordine è necessario il pagamento. <br/> Puoi saldare la quota portando i soldi e una copia di questa pagina direttamente ad un capo a fine riunione (o in alternativa, se non si riesce a stampare la pagina, basterà il numero dell'ordine e l'importo). <br/> <br/>Ti è stata inviata una mail a <?php echo $mail;?> con un link per ritrovare questa pagina.</h4>
		<?php } else if($pagamento == "Bonifico Bancario"){?>
			<h4> Sotto questa finestra trovi al ricevuta, puoi salvarla e conservarla (in qualsiasi caso ti è stata inviata una mail per recuperarla se dovessi averne bisogno). 
			<br/> <br/>
			Per il pagamento, necessario per confermare l'ordine, puoi effettuare il bonifico a <?php echo $IBAN; ?> </h4>
		<?php } ?>
			
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	</div>

  </div>
</div>

</body>
</html>