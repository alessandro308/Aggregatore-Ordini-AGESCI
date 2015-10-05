<?php
	require("menu.php");
	$db = new ArticoliDb();
	//$db -> addItem("camicia", 24.5, "descrizione camicia", ["S", "M", "L"]);
	//$db -> addItem("pantaloni", 27.5, "pantaloni camicia", ["XS", "M", "XL"]);
?>
<body>
<div class="container">
	
	<ul class="nav nav-pills">
	  <li role="presentation"><a href="index.php">Home</a></li>
	  <li role="presentation" class="active"><a href="#">Nuovo Ordine</a></li>
	  <li role="presentation"><a href="#">Stato Ordine</a></li>
	  <li role="presentation"><a href="#">Amministrazione</a></li>
	</ul>
	
	<form method="post" name="modulo" class="form-inline" role="form">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th data-field="nome">Oggetto</th>
				<th data-field="descrizione">Descrizione</th>
				<th data-field="taglie">Taglia</th>
				<th data-field="prezzo">Prezzo Unitario</th>
				<th data-field="quantity">Quantità</th>
			</tr>
			<?
				$result = $db -> getItem();
				while($row = $result -> fetchArray()){
					
					echo "<tr>";
						echo "<th>".$row[1]."</th>"; //Nome
						echo "<th>".$row[2]."</th>"; //Descrizione
						$taglie =  convertStringToArray($row[3]);
						echo '<th> <select class="form-control" id="sel1" name="'.$row['id'].'-taglia">';
						for($i=0; $i < count($taglie)-1; $i++){
								echo "<option>".$taglie[$i]."</option>";
						}
						echo "</select></th>";//Taglia
						echo "<th>".$row[4]."</th>"; //Prezzo
						//echo "<th>".$row[4]."</th>";
						echo '<th><select class="form-control" id="sel1" name="'.$row['id'].'-quantity" onchange="updateTotal()">';
						for($i=0; $i < 9; $i++){
								echo "<option>".$i."</option>";
						}
					echo "</tr>";
				}	
			?>
		</thead>
	</table>
	
	<input type="button" value="Invia" onClick="Modulo()">
</form>
<div id="total"></div>
</div>
</body>
</html>

<script>
function updateTotal() {
	var total = 0;
	<?php
		$items = $db -> getItem();
		while($row = $items -> fetchArray()){
			echo "if(document.modulo.".$row['id']."-quantity.value != 0){".PHP_EOL;
			echo "	total +=".$row["prezzo"]."* document.modulo.".$row["id"]."-quantity.value;}".PHP_EOL;
		}
	?>
	document.getElementById("content").innerHTML = total;
}
</script>