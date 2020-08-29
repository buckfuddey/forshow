<?php

namespace App\Request;


use \Exception;
use App\Routes;

/**
 * 
 */
class Request
{
	
	private $uri;

	private $method;

	private $query = [];

	private $postfields = [];

	private $securityHeader = null;

	private $securityKey = "WITHME";

	function __construct()
	{
		if (!empty($_GET)) {

			$this->query = $_GET;

			$this->uri = explode("?", $_SERVER["REQUEST_URI"])[0];

		} else {

			$this->uri = $_SERVER["REQUEST_URI"];	
		}

		$this->method = $_SERVER["REQUEST_METHOD"];

		if(isset($_SERVER["HTTP_PLAYMUSIC"])){
			$this->securityHeader = $_SERVER["HTTP_PLAYMUSIC"];
		} 

	}

	public function handle()
	{
		if ($this->method === "POST" || $this->method === "PATCH" || $this->method === "PUT") {
			$this->setPostfields();
		}

	}

	public function getUri()
	{
		return $this->uri;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getPostFields()
	{
		return $this->postfields;
	}

	public function passes() : bool
	{
		return $this->securityHeader === $this->securityKey;
	}


	private function setPostfields()
	{
		$postfields = file_get_contents('php://input');


		if ($postfields !== "") {
			
			$data = json_decode($postfields, true);

			if (!$data) {
				self::respond(
					[
						"message" => "This is a REST API, please use correct JSON.",
						"status" => "error"
					],
					400
				);
			} else {
				$this->postfields = $data;
			}
		}
	}

	public static function respond(array $message, int $code)
	{
		http_response_code($code);

		header('Content-Type: application/json');

		if (!isset($message["data"])) {
			$message["data"] = [];
		}

		echo json_encode($message);

		exit;
	}
}