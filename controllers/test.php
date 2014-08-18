<?php
require_once 'abstract_controller.php';

class test_controller extends abstract_controller {
	function __construct() {
		parent::__construct();
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
		$this->_response = array("response"=>"Hello World!");
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
