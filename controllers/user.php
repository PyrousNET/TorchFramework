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

		// Get the User's IP
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		switch(@$params['type']) {
			case 'login':
				if ($data['username'] && $data['password']) {
					$user = Users::find(array('username' => $data['username']));

					if (isset($user)) {
						if (!password_verify($data['password'], $user->password)) {
							header('HTTP/1.0 422 Unprocessable Entity');
							return;
						}

						if (!$user->active) {
							header('HTTP/1.0 423 User Not Active');
							return;
						}

						$user->login_date = date();
						$user->ip_address = $ip;
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
			case 'create_user':
				try {
					$existing_user = Users::first(array('username' => $data['username']));
					if (!is_null($existing_user)) {
						header('HTTP/1.0 400 User already exists with that username.');
						return;
					}
				} catch (Exception $e) {
					header('HTTP/1.0 500 ' . $e->getMessage());
					exit();
				}

				$user = new Users();
				$user->username = $data['username'];
				$user->password = password_hash($data['password'], PASSWORD_DEFAULT, array(
							"salt" => $this->get_salt(),
							"cost" => 12
							));

				$user->full_name = $data['full_name'];
				$user->email = $data['email'];
				$user->date_created = date();
				$user->ip_address = $ip;
				$user->save();
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
		$user = (object)array();
		$user->username = $this->_memcache->get($this->_key . "_user")->username;
		$user->full_name = $this->_memcache->get($this->_key . "_user")->full_name;
		$user->email = $this->_memcache->get($this->_key . "_user")->email;
		$user->date_created = $this->_memcache->get($this->_key . "_user")->date_created;

		$this->_response = $user;
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

	/*
	 * This function will generate a salt using a CSPRNG.
	 *
	 * @return String
	 */
	protected function get_salt() {
		$f = fopen("/dev/urandom", "r");
		$salt = hash("sha256", fread($f, 4 * 256));
		fclose($f);

		return $salt;
	}

}
