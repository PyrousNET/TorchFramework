<?php
require_once '../ActiveRecord.php';
require_once '../config.php';

function main() {
	require_once("../auto_interface.php");

	$auto_script = AutoActions::first(array("start_time" => "0000-00-00 00:00:00","error"=>NULL));
	if (is_null($auto_script)) die("Nothing to do.\n");
	echo "Processing script " . $auto_script->auto_action_id . ".\n";
	$auto_script_name = $auto_script->script_name .'.php';

	if (file_exists(__DIR__ . "/../auto_modules/" . $auto_script_name)) {
		$auto_script_location = __DIR__ . "/../auto_modules/" . $auto_script_name;
		require_once($auto_script_location);
		$auto_class = new $auto_script->script_name();

		if (!$auto_class instanceof iauto) {
			$auto_script->error = "$auto_script->script_name is not of type iauto.";
			$auto_script->save();
			die();
		}

		$auto_script->start_time = date("Y-m-d H:i:s", time());
		$auto_class->run_action($auto_script);
	} else
		$auto_script->error = "$auto_script->script_name was not found.";

	$auto_script->end_time = date("Y-m-d H:i:s", time());
	$auto_script->save();
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
	file_put_contents("error_log", array(date("Y-m-d H:i:s", time()), $e->getMessage()));

	// Email the Admin
	require '../PHPMailer/PHPMailerAutoload.php';
	$config = Array(
			"protocol"=>"smtp",
			"smtp_host"=>"",
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
		file_put_contents("error_log", array(date("Y-m-d H:i:s",time()),"Mailer error: " .$mail->ErrorInfo));
	}

	die();
}

?>
