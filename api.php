<?php
require_once 'ActiveRecord.php';
require_once 'config.php';

try {
	$default_timezone = @date_default_timezone_get();
	if (!isset($default_timezone))
		date_default_timezone_set($default_timezone);
	else
		date_default_timezone_set('America/Denver');

	ActiveRecord\Config::initialize(function($cfg)
	{
		GLOBAL $connections, $config;

		$cfg->set_model_directory('models/');
		$cfg->set_connections((array)$connections);

		$cfg->set_default_connection($config['default_connection']);
	});

	$site_active = SiteActive::first();
	if (isset($site_active) && !$site_active->active) {
		header("HTTP/1.0 503 Site Inactive");
		exit();
	}

	$path = $_SERVER['DOCUMENT_ROOT'] . '/controllers/';
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);

	include_once("bootstrapper.php");

	$url = parse_url($_SERVER['REQUEST_URI']);
	$validate_message = bootstrapper::validate_request($url['path']);

	if (!empty($validate_message)) {
		header("HTTP/1.0 400 Incorrect API Request: ". $validate_message . ".");
	} else {
		$session_id = session_id();
		if (empty($session_id)) session_start();

		if ($_SESSION['step_1'] && $_GET['shutdown'] == $site_active->key2) {
			unset($_SESSION['step_1']);

			$site_active->active = false;
			$site_active->save();

			exit();
		} else if ($_SESSION['step_1']) {
			unset($_SESSION['step_1']);
		}

		if ($_GET['shutdown'] && $_GET['shutdown'] == $site_active->key1) {
			$_SESSION['step_1'] = true;
			exit('initiated');
		}

		$bootstrapper = new bootstrapper($url['path'], $_GET);
		$result = $bootstrapper->handle_request();

		if ($result) {
			header("HTTP/1.0 200 OK");
			echo json_encode($result);
		}
	}
} catch (Exception $e) {
	header('HTTP/1.0 500 Server Error');
	var_dump($e->getMessage());

	// Email the Admin
	$to      = $config['site_email'];
	$subject = $config['site'] . ' ERROR';
	$boundary = uniqid('np');
	$message = $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n" 
				.'The exception happened on line '.$e->getLine().' of '.$e->getFile();
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";
	$message .= '<!DOCTYPE html><html><body><h1>' .$e->getMessage() . '</h1><p>' .$e->getTraceAsString() . '</p>'
				.'<p>The exception happened on line '.$e->getLine().' of '.$e->getFile().'</p></body></html>';

	$headers = 'From: ' . $config['site_email'] . "\r\n" .
			'To: ' . $to . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

	mail('', $subject, $message, $headers);

	die();
}
?>
