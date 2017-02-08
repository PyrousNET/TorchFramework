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
		$connection = ActiveRecord\ConnectionManager::get_connection();

		if (!file_exists('reports/'.$params['type'].'.php')) {
			header('HTTP/1.0 404 Report Not Found');
			return;
		}

		// The following block of code checks authorization to the report.
		// Get the group_ids from the database without loading up a bulky model.
		GLOBAL $group_ids;
		$connection->query_and_fetch("select group_id from user_groups where user_id=".$this->_user->id,function ($result) {
			GLOBAL $group_ids;
			$group_ids[] = $result['group_id'];
		});

		$authorized = false;
		if ($params['query']) {
			$group_reports = GroupReports::all(array('conditions'=>array('group_id in (?) and report_name=? and query_name=?',$group_ids,$params['type'],$params['query'])));

			if (count($group_reports)) $authorized = true;
		} else {
			$group_reports = GroupReports::all(array('conditions'=>array('group_id in (?) and report_name=?',$group_ids,$params['type'])));

			foreach ($group_reports as $report) {
				if (is_null($report->query_name)) $authorized = true;
				else if ($report->query_name == "default") $authorized = true;
			}
		}

		if (!$authorized) {
				header("HTTP/1.0 401");
				die("Unauthorized access to report.");
		}
		// End Authorization

		require_once('reports/'.$params['type'].'.php');
		if ($params['query'] && $reports[$params['query']]) {
			$query = $reports[$params['query']];
		} else if (empty($params['query']) && $reports['default']) {
			$query = $reports['default'];
		} else {
				header("HTTP/1.0 404 Not Found");
				die("Report Not Found.");
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
