<?php

namespace App\Controllers;

use App\Request\Request;


class CustomerController extends Controller
{
	
	public function show($customer)
	{

		$customer = $this->db()->getCustomer($customer);

		if (empty($customer)) {
			
			Request::respond(
				[
					"message" 	=> "Resource not found",
					"status" 	=> "error"
				],
				404
			);
		}

		Request::respond(
			[
				"message" 	=> "Customer fetched",
				"status" 	=> "success",
				"data"		=> [
					"customer_number" 	=> $customer[0]["customer_number"],
					"name" 				=> $customer[0]["name"],
					"customer_type"		=> $customer[0]["type"]
				]
			],
			200
		);
	}

	public function create()
	{
		$requestData = $this->request->getPostFields();

		if (!isset($requestData["name"]) || !$requestData["type"]) {

			$this->incorrectRequest();
		}

		$customerType = $this->db()->getCustomerType($requestData["type"]);

		if (empty($customerType)) {

			$this->incorrectRequest();
		}

		$customer = $this->db()->createCustomer($requestData["name"], $customerType[0]["id"]);

		Request::respond(
			[
				"message" 	=> "Customer created",
				"status" 	=> "success",
				"data"		=> [
					"customer_number" => $customer["customer_number"]
				]
			],
			201
		);
	}
}