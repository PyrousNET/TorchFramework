<?php
require_once 'abstract_controller.php';

class user_controller extends abstract_controller {
	function __construct($memcache, $key) {
		parent::__construct($memcache, $key);
	}

	/*
	 * post_method
	 */
	protected function post_method($params) {
		$data = (array) $this->_data;

		switch(@$params['type']) {
			case 'login':
				if ($data['username'] && $data['password']) {
					$user = Users::find(array('username' => $data['username']));

					if (isset($user)) {
						// Validate against password
						$sha_password = hash('sha256',$user->password_seed . $data['password']);

						if (!$user->active) {
							header('HTTP/1.0 423 User Not Active');
							return;
						}

						$user->login_date = date();
						$user->save();

						$this->_memcache->set($this->_key . "_user", $user);
					} else {
						header('HTTP/1.0 400 Incorrect Data');
						return;
					}
				} else {
					header('HTTP/1.0 400 Please include a username and password.');
					return;
				}

				break;
			default:
				header('HTTP/1.0 400 Incorrect Data');
		}
	}
	// =======================================================

	/*
	 * get_method
	 */
	protected function get_method($params) {
		var_dump($this->_memcache->get($this->_key . "_user"));
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
		switch(@$params['type']) {
			case 'logout':
				unset($_COOKIE['key']);
				setcookie('key','',time()-3600);
				break;
			default:
				header('HTTP/1.0 400 Bad Request');
		}
	}
	// =======================================================
}
