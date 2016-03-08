CREATE TABLE `users` (
		`id` bigint(20) NOT NULL AUTO_INCREMENT,
		`username` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
		`password` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
		`password_seed` varchar(8) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
		`full_name` varchar(128) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
		`email` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
		`date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`active` tinyint(1) NOT NULL DEFAULT '0',
		`login_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
