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
						// Validate against password
						$sha_password = hash('sha256',$user->password_seed . $data['password']);

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
				$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

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
				$user->password_seed = $this->get_random_string($chars, 8);
				$user->password = hash('sha256',$user->password_seed . $data['password']);
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
	 * get_random_string
	 */
	protected function get_random_string($valid_chars, $length)
	{
		// start with an empty random string
		$random_string = "";

		// count the number of chars in the valid chars string so we know how many choices we have
		$num_valid_chars = strlen($valid_chars);

		// repeat the steps until we've created a string of the right length
		for ($i = 0; $i < $length; $i++)
		{
			// pick a random number from 1 up to the number of valid chars
			$random_pick = mt_rand(1, $num_valid_chars);

			// take the random character out of the string of valid chars
			// subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
			$random_char = $valid_chars[$random_pick-1];

			// add the randomly-chosen char onto the end of our string so far
			$random_string .= $random_char;
		}

		// return our finished random string
		return $random_string;
	}
}
