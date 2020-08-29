<?php

namespace App\DB;

use PDO;

class DBConnection
{
	protected $PDO;

	function __construct()
	{
		$this->PDO = new PDO("mysql:host=".env("DB_HOST").";dbname=".env("DB_NAME"), env("DB_USER"), env("DB_PASSWORD"));
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$this->PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}


	protected function execute($sql, $params = [])
	{
		// this will do for now, make errors table if time exists to save errors but not display outwards with try catch
		
		$stmt = $this->PDO->prepare($sql);

		!empty($params) ? $stmt->execute($params) : $stmt->execute();

		if (substr($sql, 0, 6) === "SELECT") {

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		if (substr($sql, 0, 6) === "INSERT") {
			
			return $this->PDO->lastInsertId();
		}

		return true;
	}
} 