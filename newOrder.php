<?php
	require("menu.php");
	require("mail_config.php");
	$db = new ArticoliDb(".");
?>
<script>
<?php
	$item = $db -> getItem();
	$rows = array();
	while($row = $item -> fetchArray()){
		$rows[$row["nome"]]=$row;
		$rows[$row["nome"]]["taglie"] = $db -> getTagliePrezzi($row["nome"]);
	}
	echo "var db=".json_encode($rows).";";
	echo "var costoGestione = ".$costoGestioneOrdine.";";
?>
var row_count = 0;
var total = 0;
var order_items = [];

function print_row(){
	var row = $("<tr></tr>");
	row.attr("id", "n"+row_count);
	row_count++;
	var tdnome = $("<td></td>");
	tdnome.attr("id", "td_nome");
	select_nome = $("<select></select>");
	select_nome.addClass("form-control");
	select_nome.attr("onchange", "item_selected(this)");
	select_nome.append($("<option disabled selected></option>").text("Scegli un articolo"));
	for(var i in db){
		var option = $("<option></option>");
		option.append(db[i].nome);
		select_nome.append(option);
	}
	tdnome.append(select_nome);
	row.append(tdnome);
	row.append('<td id="td_descr"></td><td id="td_taglie" ></td><td id="td_prezzo"></td><td id="td_quantity"></td><td id="td_button"></td>');
	$("tbody").append(row);
}

function item_selected(item){
	//Cerco elemento nel JSON_db
	elemento = item.value;

	var row_id = item.parentNode.parentNode.id;
	//Mostro la descrizione
	var descrizione = $("#"+row_id).find("#td_descr");
	descrizione.html(db[elemento].descrizione.replace("%27", "'"));

	//Mostro le taglie
	var td_taglie = $("#"+row_id).find("#td_taglie");
	var select_taglie = $("<select></select>");
	select_taglie.addClass("form-control");
	var taglie = db[elemento].taglie;
	for(var i in taglie){
		var option = $("<option></option>");
		option.append(i);
		select_taglie.append(option);
	};
	select_taglie.on("change", function () {
		taglia_selezionata = select_taglie.find(":selected").text();
		var prezzo = taglie[taglia_selezionata];
		$("#"+row_id).find("#td_prezzo").text(prezzo.toFixed(2));
	});
	td_taglie.html(select_taglie);

	//Mostro il prezzo
	taglia_selezionata = select_taglie.children().first().text();
	var prezzo = taglie[taglia_selezionata];
	$("#"+row_id).find("#td_prezzo").text(prezzo.toFixed(2));

	//Costruisco la lista di scelta quantita'
	var quantity = $("#"+row_id).find("#td_quantity");
	var select_quantity = $("<select></select>");
	select_quantity.addClass("form-control");
	//select_quantity.attr("onchange", "showTotal(item)");
	for(var i = 1; i<10; i++){
		var option = $("<option></option>");
		option.append(i);
		select_quantity.append(option);
	}
	quantity.html(select_quantity);

	//Mostro pulsante 
	$("#"+row_id).find("#td_button").html('<button type="button" class="btn btn-success" onclick="addItem(this)">Aggiungi</button>');

}

function addItem(btnClicked) {
	print_row();
	var row_id = btnClicked.parentNode.parentNode.id;
	var button = $("#"+row_id).find("#td_button");
	$("#"+row_id).find("select").attr("disabled", "disabled");
	$("#"+row_id).find("#td_taglia").attr("disabled", "disabled");
	$("#"+row_id).find("#td_quantity").attr("disabled", "disabled");
	button.html('<button type="button" class="btn btn-danger" onclick="removeItem(this)">Rimuovi</button>');
	if(total == 0)
		total = costoGestione;
	total += parseFloat($("#"+row_id).find("#td_prezzo").text())*parseFloat($("#"+row_id).find("#td_quantity option:selected").text());
	$("#total").html("<h6> Totale: "+total.toFixed(2)+"€* <br/> *compreso di spese di gestione ordine</h6>");
	
	//Aggiungo elemento a JSON Obj
	var item = new Object();
	item.row_id = row_id;
	item.item = $("#"+row_id).find("#td_nome option:selected").text();
	item.taglia = $("#"+row_id).find("#td_taglie option:selected").text();
	item.quantity = parseFloat($("#"+row_id).find("#td_quantity option:selected").text());
	order_items.push(item);
	//console.dir(order_items);
}

//Remove element from JSON obj
function findAndRemove(array, property, value) {
	var i = 0;
	for(i = 0; i<array.length; i++){
		if (array[i][property] == value)
			array.splice(i, 1);
	}
}

function removeItem(btnClicked){
	var row_id = btnClicked.parentNode.parentNode.id;
	total -= parseFloat($("#"+row_id).find("#td_prezzo").text()) * parseFloat($("#"+row_id).find("#td_quantity option:selected").text());
	if(total == costoGestione)
		total = 0;
	$("#total").html("<h6> Totale: "+total.toFixed(2)+"€ <br/>*compreso di spese di gestione ordine</h6>");;
	$("#"+row_id).remove();
	//console.dir(row_id);
	findAndRemove(order_items, "row_id", row_id);
	//console.dir(order_items);
}

function prepareDataForm(){
	$("#items").attr("value", encodeURIComponent(JSON.stringify(order_items)));
	$("#totaleOrdine").attr("value", total.toString());
	if(total != 0){
		$("#warning").html(`<form role="form" action="getIdOrder.php" method="POST">
		  <div class="form-group">
			<label for="nome">Nome e cognome:</label>
			<input type="text" class="form-control" id="nome" name="name" required>
		  </div>
		  <div class="form-group">
			<label for="email">Email:</label>
			<input type="email" class="form-control" id="email" name="mail" required>
		  </div>
		  <div class="form-group">
		  	<label for="phone">Telefono:</label>
		  	<input type="phone" class="form-control" id="phone" name="phone" required>
		  </div>
		  <div class="form-group">
		   	<label for="pagamento">Modalità di pagamento:</label>
		   	<label class="radio-inline"><input type="radio" name="pagamento" value="A mano" required checked>A mano a riunione</label>
		   	<label class="radio-inline"><input type="radio" name="pagamento" value="Bonifico">Bonifico bancario</label>
		  </div>
		  <div class="form-group">
		    	<label for="branca">Branca:</label>
		    	<label class="radio-inline"><input type="radio" name="branca" value="LC" required>L/C (Coccinelle)</label>
		    	<label class="radio-inline"><input type="radio" name="branca" value="EG">E/G (Reparto)</label>
		    	<label class="radio-inline"><input type="radio" name="branca" value="RS">R/S (Clan)</label>
		   </div>
		<input type="hidden" name="items" id="items" value="">
		<input type="hidden" name="totale" id="totaleOrdine" value="">

		  <button id="saveOrder" type="submit" class="btn btn-success">Invia</button>
		  <div style="clear: both"></div>
		</form>`);
	} else {
		$("#warning").html("<h2>Attenzione, l'ordine risulta vuoto. Aggiungi almeno un oggetto!");
	}
}
</script>
</head>
<body onload="print_row()">
	<header>
		<h1>Pisa 3</h1>
	</header>
<div class="container form-group">
	
	<!--<ul class="nav nav-pills">
	  <li role="presentation"><a href="index.php">Home</a></li>
	  <li role="presentation" class="active"><a href="#">Nuovo Ordine</a></li>
	  <li role="presentation"><a href="#">Stato Ordine</a></li>
	  <li role="presentation"><a href="#">Amministrazione</a></li>
	</ul>-->

	  <h2>Ordina i tuoi prodotti</h2>
	  <div class="alert alert-info" role="alert">Serve aiuto con le taglie o con il listino? Consulta la <a href="http://www.fiordaliso.it/fileadmin/fiordaliso.it/utility/tabella_taglie.pdf">guida alle taglie</a> e il <a href="http://www.fiordaliso.it/fileadmin/fiordaliso.it/listini/listino-uniforme-agesci.pdf">listino completo</a>.</div>      
	  <table class="table">
		<thead>
		  <tr>
			<th class="col-sm-3">Articolo</th>
			<th class="col-sm-3">Descrizione</th>
			<th class="col-sm-1">Taglia</th>
			<th class="col-sm-1">Prezzo (€)</th>
			<th class="col-sm-1">Quantità</th>
			<th class="col-sm-2"></th>
		  </tr>
		</thead>
		<tbody>
		</tbody>
	  </table>
			
	
	<div id="total"></div>
	<button id="insertData" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" onclick="prepareDataForm()">Concludi Ordine</button>
</div>
<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Inserisci i dati per concludere il tuo ordine</h4>
	  </div>
	  <div id="warning" class="modal-body">
		
	  </div>
	</div>

  </div>
</div>
</body>
</html>