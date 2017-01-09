<?php

require_once('../vendor/autoload.php');

// Set the API key
$apiKey = 'YOUR-API-KEY';

// Instantiate the client
$zipTax = new supplyhog\ZipTax($apiKey);

//  Valid Call
try{
	$response = $zipTax->request('37402', 'Chattanooga', 'TN');
	var_dump($response);
}
catch(\Exception $e) {
	var_dump($e->getMessage());
}

//  Throws an Error: Invalid State
try{
	$response = $zipTax->request('37402', 'Chattanooga', 'Tennessee');
	var_dump($response);
}
catch(\Exception $e) {
	var_dump($e->getMessage());
}

//  Returns false.  Invalid City.
try{
	$response = $zipTax->request('37402', 'Somewhere Outthere', 'TN');
	var_dump($response);
}
catch(\Exception $e) {
	var_dump($e->getMessage());
}

//  Postal Code only.  This may return more than one valid result.
try{
	$response = $zipTax->request('90210');
	var_dump($response);
}
catch(\Exception $e) {
	var_dump($e->getMessage());
}