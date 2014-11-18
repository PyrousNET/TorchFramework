<?php
require_once '../ActiveRecord.php';
require_once '../config.php';

class executive {
}

function main() {
	throw new Exception("This is a test failure.");
}

try {
	$default_timezone = @date_default_timezone_get();
	if (!isset($default_timezone))
		date_default_timezone_set($default_timezone);
	else
		date_default_timezone_set('America/Denver');

	ActiveRecord\Config::initialize(function($cfg)
	{
		GLOBAL $connections, $config;

		$cfg->set_model_directory('../models/');
		$cfg->set_connections((array)$connections);

		$cfg->set_default_connection($config['default_connection']);
	});

	main();
} catch (Exception $e) {
	header('HTTP/1.0 500 Server Error');
	var_dump($e->getMessage());

	// Email the Admin
	require '../PHPMailer/PHPMailerAutoload.php';
	$config = Array(
			"protocol"=>"smtp",
			"smtp_host"=>"", //Host
			"smtp_port"=>"25",
			"smtp_user"=>"",
			"smtp_pass"=>""
			); 

	// Email the Admin
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = $config['smtp_host'];
	$mail->Port = $config['smtp_port'];
	$mail->SMTPAuth = true;
	$mail->Username = $config['smtp_user'];
	$mail->Password = $config['smtp_pass'];


	$mail->setFrom('<from email address>', '<from name>');

	$mail->addAddress('<to address>');
	$mail->Subject = 'ERROR';
	$mail->msgHTML('<!DOCTYPE html><html><body><h1>' .$e->getMessage() . '</h1><p>' .$e->getTraceAsString() . '</p>'
				.'<p>The exception happened on line '.$e->getLine().' of '.$e->getFile().'</p></body></html>');
	$mail->AltBody = $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n"
				.'The exception happened on line '.$e->getLine().' of '.$e->getFile();

	if (!$mail->send()) {
		file_put_contents("error_log", "Mailer error: " .$mail->ErrorInfo);
	}

	die();
}

?>
