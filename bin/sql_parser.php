<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once '../ActiveRecord.php';
require_once '../config.php';

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

	$connection = ActiveRecord\ConnectionManager::get_connection();

} catch (Exception $e) {
	header('HTTP/1.0 500 Server Error');
	var_dump($e->getMessage());

	// Email the Admin
	$to      = 'benjamin.payne@pyrous.net';
	$subject = 'Food Service Rebates ERROR';
	$boundary = uniqid('np');
	$message = $e->getMessage() . "\r\n" . $e->getTraceAsString() . "\r\n" 
				.'The exception happened on line '.$e->getLine().' of '.$e->getFile();
	$message .= "\r\n\r\n--" . $boundary . "\r\n";
	$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";
	$message .= '<!DOCTYPE html><html><body><h1>' .$e->getMessage() . '</h1><p>' .$e->getTraceAsString() . '</p>'
				.'<p>The exception happened on line '.$e->getLine().' of '.$e->getFile().'</p></body></html>';

	$headers = 'From: bugs@foodservicerebates.com' . "\r\n" .
			'To: ' . $to . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

	mail('', $subject, $message, $headers);

	die();
}


class sql_parser {

	public static function takeOffComments($query)
	{
		$sqlComments = '@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms';

		$query = trim( preg_replace( $sqlComments, '$1', $query ) );

		if(strrpos($query, ";") === strlen($query) - 1) {
			$query = substr($query, 0, strlen($query) - 1);
		}

		return $query;
	}


	public static function parse($content) {

		$sqlList = array();

		$lines = explode("\n", $content);

		$query = "";

		foreach ($lines as $sql_line):
			$sql_line = trim($sql_line);
		if($sql_line === "") continue;
		else if(strpos($sql_line, "--") === 0) continue;
		else if(strpos($sql_line, "#") === 0) continue;

		$query .= $sql_line;
		if (preg_match("/(.*);/", $sql_line)) {
			$query = trim($query);
			$query = substr($query, 0, strlen($query) - 1);

			$query = sql_parser::takeOffComments($query);

			$sqlList[] = $query;
			$query = "";
		}

		endforeach;

		return $sqlList;
	}
}

function scan_dir($dir) {
	$ignored = array('.', '..', '.svn', '.htaccess');

	$files = array();
	foreach (scandir($dir) as $file) {
		if (in_array($file, $ignored)) continue;
		$files[$file] = filemtime($dir . '/' . $file);
	}

	arsort($files);
	$files = array_keys($files);

	return ($files) ? $files : false;
}

// Load file names from the migrations table
$migrations = Migrations::all();

$connection->query("start transaction");

$complete_migrations = array();
foreach ($migrations as $record) {
	array_push($complete_migrations, $record->name);
}
$migrations_to_run = array_values(preg_grep('/^([^.])/', scan_dir(__DIR__ .'/../migrations')));
$migrations = array_diff($migrations_to_run, $complete_migrations);

try {
	foreach ($migrations as $migration) {
		$sqlLists = sql_parser::parse(file_get_contents(__DIR__ ."/../migrations/".$migration));

		foreach($sqlLists as $sql) {
			$connection->query($sql);

			$migration_container = new Migrations();
			$migration_container->name = $migration;
			$migration_container->save();
		}
	}

	$connection->query("commit");
} catch (Exception $e) {
	$connection->query("rollback");
	echo "Failed: " . $e->getMessage();
}
?>
