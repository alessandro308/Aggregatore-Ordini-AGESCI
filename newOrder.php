<?php
	require("menu.php");
	$db = new ArticoliDb();
	//$db -> addItem("camicia", 24.5, "descrizione camicia", ["S", "M", "L"]);
	//$db -> addItem("pantaloni", 27.5, "decrizione Pantaloni", ["XS", "M", "XL"]);
?>
<script>
<?php
	$item = $db -> getItem();
	$rows = array();
	while($row = $item -> fetchArray()){
		$row["taglie"] = convertStringToArray($row["taglie"]);
		$rows[$row["nome"]]=$row;
	}
	echo "var db=".json_encode($rows).";";
?>
var row_count = 0;
var total = 0;
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
	row.append('<td id="td_descr"></td><td id="td_taglie"></td><td id="td_prezzo"></td><td id="td_quantity"></td><td id="td_button"></td>');
	$("tbody").append(row);
}

function item_selected(item){
	//Cerco elemento nel JSON_db
	elemento = item.value;

	var row_id = item.parentNode.parentNode.id;
	//Mostro la descrizione
	var descrizione = $("#"+row_id).find("#td_descr");
	descrizione.html(db[elemento].descrizione);

	//Mostro le taglie
	var td_taglie = $("#"+row_id).find("#td_taglie");
	var select_taglie = $("<select></select>");
	select_taglie.addClass("form-control");
	var taglie = db[elemento].taglie;
	taglie.forEach(function(x){
		var option = $("<option></option>");
		option.append(x);
		select_taglie.append(option);
	});
	td_taglie.html(select_taglie);

	//Mostro il prezzo
	$("#"+row_id).find("#td_prezzo").text(db[item.value].prezzo);

	//Costruisco la lista di scelta quantita'
	var quantity = $("#"+row_id).find("#td_quantity");
	var select_quantity = $("<select></select>");
	select_quantity.addClass("form-control");
	select_quantity.attr("onchange", "showTotal(item)");
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
	total += parseFloat($("#"+row_id).find("#td_prezzo").text());
	$("#total").html(total);
}

function removeItem(btnClicked){
	var row_id = btnClicked.parentNode.parentNode.id;
	total -= parseFloat($("#"+row_id).find("#td_prezzo").text());
	$("#total").html(total);
	$("#"+row_id).remove();
}

function createOrder(){

}
</script>
</head>
<body onload="print_row()">
<div class="container form-group">
	
	<!--<ul class="nav nav-pills">
	  <li role="presentation"><a href="index.php">Home</a></li>
	  <li role="presentation" class="active"><a href="#">Nuovo Ordine</a></li>
	  <li role="presentation"><a href="#">Stato Ordine</a></li>
	  <li role="presentation"><a href="#">Amministrazione</a></li>
	</ul>-->

	  <h2>Ordina i tuoi prodotti</h2>       
	  <table class="table">
		<thead>
		  <tr>
			<th>Articolo</th>
			<th>Descrizione</th>
			<th>Taglie Disponibili</th>
			<th>Prezzo</th>
			<th>Quantità</th>
			<th></th>
		  </tr>
		</thead>
		<tbody>
		</tbody>
	  </table>
			
	
	<div id="total"></div>
	<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" onclick="createOrder()">Concludi Ordine</button>
</div>
<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Modal Header</h4>
	  </div>
	  <div class="modal-body">
		<form role="form">
		  <div class="form-group">
			<label for="email">Email address:</label>
			<input type="email" class="form-control" id="email">
		  </div>
		  <div class="form-group">
			<label for="pwd">Password:</label>
			<input type="password" class="form-control" id="pwd">
		  </div>
		  <div class="checkbox">
			<label><input type="checkbox"> Remember me</label>
		  </div>
		  <button type="submit" class="btn btn-default">Submit</button>
		</form>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	</div>

  </div>
</div>
</body>
</html>