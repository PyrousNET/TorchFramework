<?php
require_once 'abstract_controller.php';

class report_controller extends abstract_controller {
	function __construct($memcache, $key) {
		parent::__construct($memcache, $key);
	}

	/*
	 * post_method
	 */
	protected function post_method($params) {
		header('HTTP/1.0 501 Not Implemented');
	}
	// =======================================================

	/*
	 * get_method
	 */
	protected function get_method($params) {
		if (!file_exists('reports/'.$params['type'].'.php')) {
			header('HTTP/1.0 404 Report Not Found');
			return;
		}

		$connection = ActiveRecord\ConnectionManager::get_connection();

		require_once('reports/'.$params['type'].'.php');
		if ($params['query']) {
			$query = $reports[$params['query']];
		} else {
			$query = $reports['default'];
		}

		GLOBAL $response;
		$connection->query_and_fetch($query,function ($result) {
			GLOBAL $response;

			$response[] = $result;
		});

		$this->_response = $response;
	}
	// =======================================================

	/*
	 * put_method
	 */
	protected function put_method($params) {
		header('HTTP/1.0 501 Not Implemented');
	}
	// =======================================================

	/*
	 * delete_method
	 */
	protected function delete_method($params) {
		header('HTTP/1.0 501 Not Implemented');
	}
	// =======================================================
}
