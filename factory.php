<?php
require_once("controller_interface.php");

class controller_factory {
	static function get_controller($memcache, $key, $type = 'user') {
		$controller_file = $type .'.php';
		$_SERVER['DOCUMENT_ROOT'] = chop(`pwd`);

		if (file_exists($_SERVER['DOCUMENT_ROOT'] .'/controllers/'.  $controller_file)) {
			$controller_location = $_SERVER['DOCUMENT_ROOT'] .'/controllers/'.  $controller_file;
			require_once($controller_location);
			$controller_name = $type."_controller";
			$controller = new $controller_name($memcache, $key);

			if (!$controller instanceof icontroller) {
				header("HTTP/1.0 500 Server Error");
				return;
			}

			return $controller;
		}
		else
			header('HTTP/1.0 404 Controller not found');
	}
}

?>
