<?php
	//ordini (id INTEGER PRIMARY KEY UNIQUE, data INT, nome TEXT, email TEXT, branca TEXT, telefono INT, totale REAL, pagamento TEXT, saldato INT, consegnato INT)
	require("../menu.php");
	require("../mail_config.php");
	$db = new ArticoliDb("..");
	$orderedItems = $db -> getAllOrderItem();
	$ordiniInSospeso = $db -> query("SELECT * FROM ordini WHERE saldato = 0 AND consegnato = 0 ORDER BY id");
	$ordiniDaConsegnare = $db -> query("SELECT * FROM ordini WHERE saldato = 1 AND consegnato = 0 ORDER by id");
	?>
	<script>
		function a_onClick(obj) {
		 
		 $orderId = obj.attr('id');
		 console.log($orderId)
		 $("#TitoloModale").html("Dettagli ordine #".concat($orderId));
		 $.ajax(
		 	{url: "getItems.php?id=".concat($orderId), 
			 	success: function(result){
			 	$("#tabella").html(result);
		 		}
		 	}
		 );
		 
		 $('#modale').modal('show');
		}
	</script>
</head>
		<div class="container">
		  <h2>Ordini Da Saldare</h2>
		  <p>Ordini in sospeso in attesa di essere saldati</p>            
		  <table class="table table-striped">
			<thead>
			  <tr>
				<th>ID</th>
				<th>Data</th>
				<th>Nome</th>
				<th>eMail</th>
				<th>Branca</th>
				<th>Telefono</th>
				<th>Totale</th>
				<th>Pagamento</th>
				<th></th>
				<th>Dettagli</td>
			  </tr>
			</thead>
			<tbody>
				<form action="setPagato.php" method="POST">
	<?
	while($row = $ordiniInSospeso -> fetchArray()){
		?>
		
			  <tr id="<?php echo $row['id']?>">
				<td><?php echo $row["id"]?></td>
				<td><?php echo $row["data"]?></td>
				<td><?php echo $row["nome"]?></td>
				<td><?php echo $row["email"]?></td>
				<td><?php echo $row["branca"]?></td>
				<td><?php echo $row["telefono"]?></td>
				<td><?php echo $row["totale"]?></td>
				<td><?php echo $row["pagamento"]?></td>

				<td>
					<div class="checkbox" style="position:relative; top:-10px;">
						<?php echo '<input type="checkbox" name="id-'.$row["id"].'" value="'.$row["id"].'">';?>
					</div>
				</td>
				<td id="<?php echo $row['id']?>">
					<a id="<?php echo $row['id']?>" onclick="a_onClick($(this))">Dettagli</a>
				</td>
			  </tr>
	<?
	}
	?>
			<input type="submit" value="IMPOSTA SELEZIONATI COME PAGATI"/>
				</form>
			</tbody>
		  </table>
		  
		  <h2>Ordini da consegnare</h2>
		  	  <p>Ordini pagati in attesa di essere consegnati</p>            
		  	  <table class="table table-striped">
		  		<thead>
		  		  <tr>
		  			<th>ID</th>
		  			<th>Data</th>
		  			<th>Nome</th>
		  			<th>eMail</th>
		  			<th>Branca</th>
		  			<th>Telefono</th>
		  			<th>Totale</th>
		  			<th>Pagamento</th>
		  			<th></th>
		  			<th>Dettaglio</th>
		  		  </tr>
		  		</thead>
		  		<tbody>
		  			<form action="setConsegnato.php" method="POST">
		  <?
		  while($row = $ordiniDaConsegnare -> fetchArray()){
		  	?>
		  	
		  		  <tr>
		  			<td><?php echo $row["id"]?></td>
		  			<td><?php echo $row["data"]?></td>
		  			<td><?php echo $row["nome"]?></td>
		  			<td><?php echo $row["email"]?></td>
		  			<td><?php echo $row["branca"]?></td>
		  			<td><?php echo $row["telefono"]?></td>
		  			<td><?php echo $row["totale"] + $costoGestioneOrdine?></td>
		  			<td><?php echo $row["pagamento"]?></td>
		  
		  			<td>
		  				<div class="checkbox" style="position:relative; top:-10px;">
		  					<?php echo '<input type="checkbox" name="id-'.$row["id"].'" value="'.$row["id"].'">';?>
		  				</div>
		  			</td>
		  			<td id="<?php echo $row['id']?>">
		  				<a id="<?php echo $row['id']?>" onclick="a_onClick($(this))">Dettagli</a>
		  			</td>
		  		  </tr>
		  <?
		  }
		  ?>
		  		<input type="submit" value="IMPOSTA SELEZIONATI COME CONSEGNATI"/>
		  			</form>
		  		</tbody>
		  	  </table>
		<h2><a href="closedOrders.php">Vedi ordini passati</a></h2>
		</div>

<div id="modale" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 id="TitoloModale" class="modal-title">Dettagli ordine</h4>
	  </div>
	  <div id="tabella" class="modal-body">
		<p>Dettagli ordine</p>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	</div>

  </div>
</div>