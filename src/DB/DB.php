<?php

namespace App\DB;

use Ramsey\Uuid\Uuid;


class DB extends DBConnection
{
	
	function __construct()
	{
		parent::__construct();
	}

	

	public function getCustomer($customer_number)
	{
		$sql = "SELECT customers.id, customer_number, name, type FROM customers LEFT JOIN customer_types ON customers.type_id = customer_types.id WHERE customers.customer_number = :customer_number";

		$params = [
			"customer_number" => $customer_number
		];


		return $this->execute($sql, $params);
	}

	public function getCustomerById($customer_id)
	{
		$sql = "SELECT * FROM customers LEFT JOIN customer_types ON customers.type_id = customer_types.id WHERE customers.id = :customer_id";

		$params = [
			"customer_id" => $customer_id
		];


		return $this->execute($sql, $params);
	}	

	public function getCustomerTypes()
	{
		$sql = "SELECT * FROM customer_types";

		return $this->execute($sql);
	}

	public function getCustomerType($type)
	{
		$sql = "SELECT * FROM customer_types WHERE type = :type";

		$params = [
			"type" => $type
		];

		return $this->execute($sql, $params);
	}

	public function getCustomerTypeById($id)
	{
		$sql = "SELECT * FROM customer_types WHERE id = :id";

		$params = [
			"id" => $id
		];

		return $this->execute($sql, $params);
	}

	public function getArticles()
	{
		$sql = "SELECT * FROM articles";

		return $this->execute($sql);
	}

	public function getOrder($order_number)
	{	

		$orderSql = "SELECT * FROM orders WHERE order_number = :order_number";

		$orderParams = [
			"order_number" => $order_number
		];

		
		return $this->execute($orderSql, $orderParams);

	}

	public function getArticleOrders($order_id)
	{
		$sql = "SELECT * FROM article_orders WHERE order_id = :order_id";

		$params = [
			"order_id" => $order_id
		];

		return $this->execute($sql, $params);
	}

	public function getOrderWithArticles($order_id)
	{
		$sql = "SELECT * FROM article_orders LEFT JOIN articles ON article_orders.article_id = articles.id WHERE order_id =:order_id AND article_amount > 0";

		$params = [
			"order_id" => $order_id
		];

		return $this->execute($sql, $params);
	}



	public function createCustomer($name, $type_id)
	{
		$sql = "INSERT INTO customers (name, customer_number, type_id) VALUES (:name, :customer_number, :type_id)";

		$number = Uuid::uuid4()->toString();

		$params = [
			"name" 				=> $name, 
			"customer_number" 	=> $number,
			"type_id" 			=> $type_id
		];

		$id = $this->execute($sql, $params);

		return [
			"id" 				=> $id,
			"customer_number" 	=> $number
		];
	}

	public function createOrder($customer_id)
	{

		$sql = "INSERT INTO orders (customer_id, order_number) VALUES (:customer_id, :order_number)";

		$number = Uuid::uuid4()->toString();

		$params = [
			"customer_id" => $customer_id,
			"order_number" => $number
		]; 

		$id = $this->execute($sql, $params);

		return [
			"id" 			=> $id, 
			"order_number" 	=> $number
		];
	}

	public function createArticleOrder($order_id, $article_id, $article_amount)
	{
		$sql = "INSERT INTO article_orders (order_id, article_id, article_amount) VALUES (:order_id, :article_id, :article_amount)";

		$params = [
			"order_id" 			=> $order_id,
			"article_id" 		=> $article_id,
			"article_amount" 	=> $article_amount
		];

		return $this->execute($sql, $params);
	}

	public function updateArticleOrder($order_id, $article_id, $article_amount)
	{
		$sql = "UPDATE article_orders SET article_amount = :article_amount WHERE article_id = :article_id AND order_id = :order_id";

		$params = [
			"article_amount" 	=> $article_amount,
			"article_id"		=> $article_id,
			"order_id"			=> $order_id
		];

		$this->execute($sql, $params);
	}

	public function saveError($message, $line, $file)
	{
		$sql = "INSERT INTO errors (message, line, file) VALUES (:message, :line, :file)";

		$params = [
			"message" 	=> $message,
			"line" 		=> $line,
			"file" 		=> $file
		];

		$this->execute($sql, $params);
	}



}



/*

BEWARE INTREST ON INTREST FOR COMPANY CUSTOMERS CHECK SUBSECTION "YTTERLIGARE FUKTIONALITET"

BIKE FOR FREE AT 10K IS EXCLUDING DISCOUNTS! CALCULATE BOTH TOTAL PRICE AND DISCOUNTED PRICE



*/