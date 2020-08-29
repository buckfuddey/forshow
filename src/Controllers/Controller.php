<?php 

namespace App\Controllers;

use App\Request\Request;
use App\DB\DB;


class Controller
{
	protected $request;

	private $db = null;

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	protected function db()
	{
		if (!$this->db) {

			$this->db = new DB;
		}

		return $this->db;
	}

	protected function incorrectRequest()
	{
		Request::respond(
			[
				"message" 	=> "Missing or incorrect data in postfields, refer to Documentation @ /docs",
				"status" 	=> "error"
			],
			400
		);
	}

	public function docs()
	{
		$docs = json_decode(file_get_contents("/var/www/docs.json"), true);

		Request::respond(
			[
				"message" 	=> "Docs fetched",
				"status" 	=> "success",
				"data"		=> $docs
			],
			200
		);
	}
	
}