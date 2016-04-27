<?php
require_once('controller_interface.php');

abstract class abstract_controller implements icontroller {
	protected $_data;
	protected $_method;
	protected $_memcache;
	protected $_key;
	protected $_user;

	function __construct($memcache, $key) {
		$this->_memcache = $memcache;
		$this->_key = $key;

		$post_data = file_get_contents("php://input");
		$json_input = json_decode($post_data);

		if (!empty($post_data) && empty($json_input)) {
			header("HTTP/1.0 400 Bad Request, Unable to parse input.");
			return;
		}

		$this->_method = $_SERVER['REQUEST_METHOD'];
		$this->_data = $json_input;
		$this->_raw_data = $post_data;
		$this->_user = $this->_memcache->get($this->_key."_user");

	}

	/*
	 * handle_request
	 * params - Query Parameters from the request
	 *
	 * Should always return a json encodable object or array.
	 */
	function handle_request($params) {

		switch($this->_method) {
			case 'POST': // Create
				$this->post_method($params);
				break;
			case 'GET': // Read
				$this->get_method($params);
				break;
			case 'PUT': // Update
				$this->put_method($params);
				break;
			case 'DELETE': // Delete
				$this->delete_method($params);
				break;
            		default:
			    header("HTTP/1.0 405 Method Not Allowed");
		}

		return $this->_response;
	}
	// =======================================================

	
	protected abstract function post_method($params);
	protected abstract function get_method($params);
	protected abstract function put_method($params);
	protected abstract function delete_method($params);
}

?>
