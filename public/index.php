<?php

use Dotenv\Dotenv;
use App\Mainframe;

use App\DB\DB;

require_once realpath("../vendor/autoload.php");

Dotenv::createImmutable(realpath("../"))->load();

try {

	$mainframe = (new Mainframe())->handle();	

} catch (Error $e) {

	$db = new DB;

	$db->saveError(
		$e->getMessage(),
		$e->getLine(),
		$e->getFile()
	);
	var_dump($e->getMessage(),$e->getLine(),
		$e->getFile() );

	/*

	DOES NOT WORK
	try {	

	} catch (Error $fatal) {

		http_response_code(500);

		echo json_encode([
			"system failure, contact admin"
		]);		
	}
	*/ 

	
	http_response_code(500);

	echo json_encode([
		"something went wrong, contact admin"
	]);

}