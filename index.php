<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SQL Injection</title>
	<style>
span.input {
	background-color: yellow;
	border: solid 1px;
	padding: 3px;
	margin: 3px;
}

span.prawda, span.falsz {
	color: white;
	padding: 3px;
	margin: 3px;
	font-weight: bold;
}

span.prawda {
	background-color: green;
}

span.falsz {
	background-color: red;
}
	</style>
</head>
<body>

<?php

require 'vendor/autoload.php';

function spanuj($tekst) {
	return '<span class="input">'.$tekst.'</span>';
}

function pisz_bool($bool) {
	if($bool) {
		echo '<span class="prawda">PRAWDA</span>';
	} else {
		echo '<span class="falsz">FAŁSZ</span>';
	}
}

$endl = "";//\n";

if(isset($_POST['login'])) {
	$pol = new mysqli('localhost', 'root', '', 'sql_injection');

	$login = $_POST['login'];
	$haslo = $_POST['haslo'];
	if(@$_POST['escape'] == 'tak') {
		$login = $pol->escape_string($login);
		$haslo = $pol->escape_string($haslo);
	}

	echo '<h2>Dane z formularza</h2>';
	$sql1 = 'SELECT id FROM userzy WHERE login ='.$endl.'"';
	$sql2 = '"'.$endl.' AND haslo = '.$endl.'"';
	$sql3 = '"'.$endl.';';

	$zapytanie = $sql1.$login.$sql2.$haslo.$sql3;
	$spanowane = $sql1.spanuj($login).$sql2.spanuj($haslo).$sql3;
?>

<p>Zapytanie oczami programisty: <?php echo $spanowane; ?></p>

<p>Kolorowanie składni: <?php echo SqlFormatter::highlight($zapytanie); ?></p>

<p>Zapytanie oczami bazy danych: <?php echo SqlFormatter::format($zapytanie); ?></p>

<h3>Wynik z bazy</h3>

<?php

	$wynik = $pol->query($zapytanie);
	$l_wierszy = $wynik->num_rows;
	if($l_wierszy > 0) {
		$pierwsze_id = $wynik->fetch_array()[0];
	}

	echo '<p>Liczba wyników: '.$l_wierszy.'</p>';

	echo '<ul>';

	if(isset($pierwsze_id)) {
		echo '<li>'.$pierwsze_id.'</li>';
	}
	while($wiersz = $wynik->fetch_array()) {
		echo '<li>'.$wiersz[0].'</li>';
	}
?>

</ul>

<h3>Sprawdzenie warunków</h3>
<p>wiersze=1: <?php echo pisz_bool($l_wierszy == 1) ?></p>
<p>wiersze>0: <?php echo pisz_bool($l_wierszy > 0) ?></p>

<p>Zalogowano: <?php echo @$pierwsze_id; ?></p>

<?php
	$pol->close();
}

?>

<h2>Formularz</h2>
<form method="post">
<p>Login: <input type="text" name="login" id="login"></p>
<p>Hasło: <input type="text" name="haslo" id="haslo"></p>
<p><input type="checkbox" name="escape" id="escape" value="tak"> mysqli_escape_string</p>
<p><input type="submit" value="Loguj"></p>
</form>
</body>
</html>