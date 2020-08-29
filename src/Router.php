<?php

namespace App;

use App\Request\Request;

/**
 * 
 */
class Router
{

	private $routes;

	private $request;

	function __construct(Request $request)
	{
		$this->request = $request;

		$this->loadRoutes();
	}
	
	
	private function loadRoutes()
	{
		$routeArray = json_decode(
			file_get_contents(__DIR__."/Config/routes.json"),
			true
		);
		
		foreach ($routeArray as $index => $route) {
			
			$clean = ltrim($route["endpoint"], "/");

			$routeArray[$index]["endpoint_data"] = explode("/", $clean);

			$regexify = str_replace("/", "\/", preg_replace("/\{([^}]+)\}/", "(.{36})", $route["endpoint"]));

			$fixed = "/^".$regexify."$/";

			$routeArray[$index]["regex"] = $fixed;
		}

		$this->routes = $routeArray;
	}

	private function matchRoute()
	{
		foreach ($this->routes as $index => $route) {
			
			if (
				preg_match($route["regex"], $this->request->getUri())
				&& 
				$this->request->getMethod() === $route["method"]
			) {
				return $this->routes[$index];
			}
		}

		Request::respond(
			[
				"message" => "This is not the route you are looking for.",
				"status" => "error"
			],
			404
		);
	}

	private function getControllerVars($routeVars)
	{
		$vars = [];

		$givenUriData = explode("/", ltrim($this->request->getUri(), "/"));

		foreach ($routeVars as $index => $routeVar) {

			if (preg_match("/\{([^}]+)\}/", $routeVar)) {

				$clean = trim($routeVar, "{}");

				$vars[/*$clean*/] = $givenUriData[$index]; //if there is time, use reflection class to make sure that the variable name in route matches the variable name in controller $clean is the name in route, should correspond to name in controller function 
			}
		}

		return $vars;
	}

	private function fetchController()
	{
		$route = $this->matchRoute();

		if ($route["protect"]) {
			if(!$this->request->passes()){
				Request::respond(
					[
						"message" => "Unauthorized",
						"status" => "error"
					],
					401
				);
			}
		}

		$controllerVars = $this->getControllerVars($route["endpoint_data"]);

		list($controllerClass, $controllerMethod) = explode("@", $route["controller"]); 

		return [
			"\\App\\Controllers\\$controllerClass",
			$controllerMethod,
			$controllerVars
		];
	}

	public function executeController()
	{

		list($controllerPath, $method, $params) = $this->fetchController();

		$controller = new $controllerPath;

		$controller->setRequest($this->request);

		call_user_func_array([$controller, $method], $params);
	}
}
