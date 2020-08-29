<?php 

namespace App;

use App\Request\Request;


class Mainframe {

	private $request;

	private $router;

	function __construct()
	{
		$this->request = new Request();
	}

	public function handle()
	{
		$this->request->handle();

		(new Router($this->request))->executeController();
		
	}

}