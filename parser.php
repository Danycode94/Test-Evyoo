<?php
if (!isset($_SESSION)) {
	session_start();
}
//include("../Configuration/config.php");
//include('../Configuration/config1.php');

// Now we check if the data from the login form was submitted, isset() will check if the data exists.

function test_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function siExist1($con, $nomfichier, $nomchamp, $valeurchamp){
	$valeur = "";
	$query = "select * from $nomfichier where $nomchamp = '$valeurchamp'";
	//echo $query;
	$results = mysqli_query($con, $query);

	if (!$results) {
		die("SQL query failed: " . mysqli_error($con));
	}
	if (mysqli_num_rows($results) > 0) {
		// Process the results
		//echo '<script>alert("found")</script>';
		$valeur = 'Y';
	} else {
		//echo '<script>alert("Not found")</script>';
		$valeur = 'N';
	}
	return $valeur;
}

function fsearchCustCode($con, $nomchamp){
	$valeur = "";
	$query = "select * from cptnumber";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row[$nomchamp];
	}

	$query = mysqli_query($con, "update cptnumber set $nomchamp = ($valeur + 1)");
	if ($query) {
		return $valeur;
	} else {
		die("SQL query failed: " . mysqli_error($con));
	}
}

function fsearchCustCodeInvoice($con, $nomchamp)
{
	$valeur = "";
	$query = "select * from cptnumber";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row[$nomchamp];
	}

	$query = mysqli_query($con, "update cptnumber set $nomchamp = ($valeur + 1)");
	if ($query) {
		return $valeur;
	} else {
		die("SQL query failed: " . mysqli_error($con));
	}
}

function fsearchQuoteIDHD($con, $quoteidDT)
{
	$valeur = "";
	$query = "SELECT * FROM quotedt WHERE quoteidDT=$quoteidDT";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row['quoteidHD'];
	}

	return $valeur;
}

function fsearchProduct_idOnQuoteDT($con, $quoteidDT)
{
	$valeur = "";
	$query = "SELECT * FROM quotedt WHERE quoteidDT=$quoteidDT";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row['produit_id'];
	}

	return $valeur;
}

function testnew()
{
	echo '<script>alert("Add new")</script>';
}

function fsearchUnitDescription($con, $nomTable, $nomchamp, $unit_id)
{
	$valeur1 = "";
	$query_sql = "select * from $nomTable WHERE unit_id =$unit_id";
	//echo $query;
	$results1 = mysqli_query($con, $query_sql);
	while ($row_sql = mysqli_fetch_array($results1)) {
		$valeur1 = $row_sql[$nomchamp];
	}
	return $valeur1;
}


function returnQuantityProductById($con, $produit_id){
	$valeur = "";
	$query = "SELECT * FROM produit WHERE produit_id=$produit_id";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row['quantitetotal'];
	}
	$results->close();

	return $valeur;
}

function returnBaseUnitProductById($con, $produit_id){
	$valeur = 0;
	$query = "SELECT * FROM produit WHERE produit_id=$produit_id";
	//echo $query;
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row['baseunit'];
	}
	$results->close();

	return $valeur;
}

function returnAcountBalanceCustomerById($con, $customers_id){
	$valeur = 0;
	
	$query = "SELECT * FROM customers WHERE customers_id=$customers_id";
	$results = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($results)) {
		$valeur = $row['account_balance'];
	}
	$results->close();

	return $valeur;
}

function saveDataQuoteHdPartTwo($con, $quoteIDHD){
		
}


?>
