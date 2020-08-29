<?php

namespace App\Controllers;


use App\Request\Request;
/**
 * 
 */
class OrderController extends Controller
{

	private $messageOverRide = false;
	
	public function show($order)
	{
		$order = $this->db()->getOrder($order);

		if (empty($order)) {
			Request::respond(
				[
					"message" 	=> "Resource not found",
					"status" 	=> "error",
				],
				404
			);
		}

		$order = $order[0];

		$customer = $this->db()->getCustomerById($order["customer_id"])[0];

		$orderWithArticles = $this->db()->getOrderWithArticles($order["id"]);

		$totalPrice = 0;

		$totalDiscount = 0;

		$returnArr = [
			"customer_name" 	=> $customer["name"],
			"customer_number"	=> $customer["customer_number"],
			"order_number"		=> $order["order_number"],
			"requested_at" 		=> $order["requested_at"]
		];

		foreach ($orderWithArticles as $index => $article) {

			$articlePrice = ($article["price"] * $article["article_amount"]);

			$returnArr["articles"][$index] = [
				"article_name" 		=> $article["article_name"],
				"article_amount" 	=> $article["article_amount"],
				"price" 			=> $article["price"],
				"total_price"		=> $articlePrice,
				"reduced_price" 	=> 0,
				"discount"			=> 0,
				"requested_at"	=> $article["requested_at"],
				"updated_at"	=> $article["updated_at"]
			];

			if ($customer["type"] === "small_company" || $customer["type"] === "large_company") {

				$returnArr["articles"][$index]["reduced_price"] = ($articlePrice * 0.9);
			}

			if ($customer["type"] === "large_company" && ($article["article_name"] === "pen" || $article["article_name"] === "paper") ) {
				$returnArr["articles"][$index]["reduced_price"] = ($returnArr["articles"][$index]["reduced_price"] * 0.8);
			}

			if ($returnArr["articles"][$index]["reduced_price"] > 0) {

				$discount = ($articlePrice - $returnArr["articles"][$index]["reduced_price"]);

				$returnArr["articles"][$index]["discount"] = $discount;

				$totalDiscount += $discount;
			}


			$totalPrice += $articlePrice;
		}

		$returnArr["total_price"] = $totalPrice;

		$returnArr["reduced_price"] = ($totalPrice - $totalDiscount);

		$returnArr["total_discount"] = $totalDiscount;

		if ($totalPrice >= 10000) {
			$returnArr["special_gift"] = "https://www.youtube.com/watch?v=fg2gLapBFow";
		}
		

		Request::respond(
			[
				"message" 	=> $this->messageOverRide ? $this->messageOverRide : "Order fetched",
				"status" 	=> "success",
				"data"		=> [
					"order" => $returnArr
				]
			],
			200
		);
	}


	public function create()
	{
		$requestData = $this->request->getPostFields();

		if (!isset($requestData["customer_number"])) {

			$this->incorrectRequest();
		}

		$customer = $this->db()->getCustomer($requestData["customer_number"]);

		if (empty($customer)) {
			
			Request::respond(
				[
					"message" 	=> "Invalid customer",
					"status" 	=> "error"
				],
				400
			);
		}

		if (!isset($requestData["articles"]) || empty($requestData["articles"])) {

			$this->incorrectRequest();
		}

		$articles = $this->db()->getArticles();

		$fixedArticles = array_reduce($articles, function($fixing, $article){

			$fixing[$article["article_name"]] = $article;
			
			return $fixing;
		});

		$createdOrder = $this->db()->createOrder($customer[0]["id"]);

		foreach ($requestData["articles"] as $articleName => $articleAmount) {

			$this->db()->createArticleOrder($createdOrder["id"], $fixedArticles[$articleName]["id"], $articleAmount); 	
		} 


		$this->messageOverRide = "Order created";


		return $this->show($createdOrder["order_number"]);
	}

	public function update($order)
	{

		$requestData = $this->request->getPostFields();

		$order = $this->db()->getOrder($order);

		if (empty($order)) {
			Request::respond(
				[
					"message" 	=> "Resource not found",
					"status" 	=> "error",
				],
				404
			);
		}

		if (isset($requestData["articles"]) && !empty($requestData["articles"])) {

			$order = $order[0];


			$articles = $this->db()->getArticles();


			$articleById = array_reduce($articles, function($fixing, $article){

				$fixing[$article["id"]] = $article;
				
				return $fixing;
			});

			$articleByName = array_reduce($articles, function($fixing, $article){

				$fixing[$article["article_name"]] = $article;
				
				return $fixing;
			});
	

			$articleOrders = $this->db()->getArticleOrders($order["id"]);

			$fixedArticleOrders = array_reduce($articleOrders, function($fixing, $articleOrder) use ($articleById) {

				$fixing[$articleById[$articleOrder["article_id"]]["article_name"]] = [

					"article_amount" 	=> $articleOrder["article_amount"]
				];

				return $fixing;
				
			});

		

			foreach ($requestData["articles"] as $articleName => $articleAmount) {


				// input validation 
				if (
					// if trying to order something that doesnt exist
					!isset($articleByName[$articleName]) ||

					// if amount ordered is type other than int
					gettype($articleAmount) !== "integer" || 

					// if trying to order a negative number
					$articleAmount < 0
				) {

					$this->incorrectRequest();
				}


				if (isset($fixedArticleOrders[$articleName])) {

					if ($fixedArticleOrders[$articleName]["article_amount"] !== $articleAmount) {
						
						$this->db()->updateArticleOrder(
							$order["id"],
							$articleByName[$articleName]["id"],
							$articleAmount
						);
					}

				} else {
					$this->db()->createArticleOrder(
						$order["id"],
						$articleByName[$articleName]["id"],
						$articleAmount
					);
				}
			}

			$this->messageOverRide = "Order updated";

			$this->show($order["order_number"]);
			
		} else {

			Request::respond(
				[
					"message" 	=> "Order unaltered",
					"status" 	=> "success"
				],
				204
			);	
		}
	}

	public function articles()
	{
		$articles = $this->db()->getArticles();

		$clean = [];

		foreach ($articles as $article) {
			$clean[] = [
				"price" 		=> $article["price"],
				"article_name" 	=> $article["article_name"]
			];
		}
		
		Request::respond(
			[
				"message" 	=> "Articles fetched",
				"status"	=> "success",
				"data"		=> $clean
			],
			200
		);

	}
}