CREATE TABLE `customers` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128),
	`customer_number` VARCHAR(36),
	`type_id` INT unsigned,
	KEY `type` (`type_id`) USING BTREE,
	PRIMARY KEY (`id`)
);

CREATE TABLE `customer_types` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(128) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

INSERT INTO customer_types (type) VALUES ("private");
INSERT INTO customer_types (type) VALUES ("small_company");
INSERT INTO customer_types (type) VALUES ("large_company");

CREATE TABLE `orders` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`customer_id` INT unsigned,
	`order_number` VARCHAR(36),
	`requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	KEY `customer` (`customer_id`) USING BTREE,
	PRIMARY KEY (`id`)
);

CREATE TABLE `articles` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`article_name` VARCHAR(128),
	`price` INT unsigned,
	PRIMARY KEY (`id`)
);

INSERT INTO articles (article_name, price) VALUES ("pen", 5);
INSERT INTO articles (article_name, price) VALUES ("notepad", 20);
INSERT INTO articles (article_name, price) VALUES ("paper", 1);
INSERT INTO articles (article_name, price) VALUES ("eraser", 10);


CREATE TABLE `article_orders` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`order_id` INT unsigned,
	`article_id` INT unsigned,
	`article_amount` INT unsigned,
	`requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	KEY `pivot` (`order_id`,`article_id`) USING BTREE,
	PRIMARY KEY (`id`)
);


CREATE TABLE `errors` (
	`id` INT unsigned NOT NULL AUTO_INCREMENT,
	`message` VARCHAR(2048),
	`line` INT unsigned,
	`file` VARCHAR(1024),
	PRIMARY KEY (`id`)
);