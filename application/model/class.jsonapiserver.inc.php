<?php

class RestApiServer
{

	private $routes = [];
	public function register_route($method, $path, $handler)
	{
		$this->routes[] = [
			'method' => strtoupper($method),
			'path' => $path,
			'handler' => $handler
		];
	}
	public function handle_request()
	{
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$rawInput = file_get_contents('php://input');
		$input = json_decode($rawInput, true);
		// Check if the request is for system_connect
		if ($requestMethod === 'POST' && $requestUri === '/api/system/connect/') {
			foreach ($this->routes as $route) {
				if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
					try {
						$response = call_user_func($route['handler'], $input);
						$this->send_response($response, 200);
					} catch (Exception $e) {
						$this->send_response(['error' => $e->getMessage()], 500);
					}
					return;
				}
			}
		}
		// Match the route and handle the request
		foreach ($this->routes as $route) {
			if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
				try {
					$response = call_user_func($route['handler'], $input);
					$this->send_response($response, 200);
				} catch (Exception $e) {
					$this->send_response(['error' => $e->getMessage()], 500);
				}
				return;
			}
		}
		$this->send_response(['error' => 'Route not found'], 404);
	}
	private function send_response($response, $statusCode = 200)
	{
		http_response_code($statusCode);
		header('Content-Type: application/json');
		echo json_encode($response);
		exit();
	}
}

class SheelRestApiServer
{
	protected $sheel;
	private $restApiServer;

	public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->restApiServer = new RestApiServer();
		$this->restApiServer->register_route('POST', '/api/system/connect/', [$this, 'system_connect']);
		$this->restApiServer->register_route('GET', '/api/system/officialtime/', [$this, 'system_getofficialtime']);
	}
	public function handle_request()
	{
		$this->restApiServer->handle_request();
	}
	private function success_response($data = [], $message = 'Success')
	{
		return [
			'status' => 'success',
			'message' => $message,
			'data' => $data
		];
	}
	private function error_response($message, $code = 400)
	{
		return [
			'status' => 'error',
			'message' => $message,
			'code' => $code
		];
	}
	public function system_connect($input)
	{ // retrieves a session id & csrf token for subsequent connections
		$hascredentials = false;
		$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string('system.connect') . "'
				LIMIT 1
			");
		if ($input['grant_type'] == 'client_credentials') {
			$sql = $this->sheel->db->query("
				SELECT status
				FROM " . DB_PREFIX . "users 
				WHERE (username = '" . $this->sheel->db->escape_string($input['user_id']) . "' OR email = '" . $this->sheel->db->escape_string($input['user_id']) . "')
				LIMIT 1
			");
		} else if ($input['grant_type'] == 'api_key') {
			$sql = $this->sheel->db->query("
				SELECT status
				FROM " . DB_PREFIX . "users 
				WHERE apikey = '" . $this->sheel->db->escape_string($input['apikey']) . "' AND useapi = '1'
				LIMIT 1
			");
		} else {
			return $this->error_response('Invalid grant type', 422);
		}
		if ($this->sheel->db->num_rows($sql) > 0) {
			$hascredentials = true;
			$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($userinfo['status'] == 'unverified') {
				$this->api_failed('system.connect');
				return $this->error_response($this->getmessage('_you_have_not_activated_your_account_via_email', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
			if ($userinfo['status'] == 'banned') {
				$this->api_failed('system.connect');
				return $this->error_response($this->getmessage('_this_account_has_been_banned', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
		}
		if ($hascredentials) {
			if ($this->authenticate($input['user_id'], $input['user_secret'], $input['apikey'])) {
				$csrftoken = $this->login($input['user_id'], $input['user_secret'], $input['apikey'], true, true, true, $input['apikey']);
				$this->api_success('system.connect');
				return $this->success_response(array('token' => $csrftoken), 'Connected.');
			} else {
				$this->api_failed('system.connect');
				return $this->error_response($this->getmessage('_you_have_provided_incorrect_login_credentials', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
		} else {
			return $this->error_response($this->getmessage('_you_have_provided_incorrect_login_credentials', $_SESSION['sheeldata']['user']['languageid']), 422);
		}
	}
	public function system_getofficialtime($input)
	{
		$data = ['datetime' => date('Y-m-d H:i:s')];
		return $this->success_response($data, 'System time retrieved successfully');
	}
	private function validate_token()
	{
		// Get the Authorization header
		$headers = getallheaders();
		$authHeader = $headers['Authorization'] ?? null;

		if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
			$this->error_response(['error' => 'Unauthorized: Missing or invalid Authorization header'], 401);
			exit();
		}

		// Extract the token
		$token = substr($authHeader, 7); // Remove "Bearer " prefix

		// Validate the token in the database
		$sql = $this->sheel->db->query("
			SELECT userid, username, token_expiry
			FROM " . DB_PREFIX . "api_users
			WHERE token = '" . $this->sheel->db->escape_string($token) . "'
			AND token_expiry > NOW()
			AND is_active = 1
			LIMIT 1
		");

		if ($this->sheel->db->num_rows($sql) === 0) {
			$this->error_response(['error' => 'Unauthorized: Invalid or expired token'], 401);
			exit();
		}

		// Fetch user details and store them in the session or class property
		$user = $this->sheel->db->fetch_array($sql, DB_ASSOC);
		$_SESSION['api_user'] = $user; // Store user details in the session
	}
	private function getmessage($var, $lng)
	{
		$finalmessage = '';
		$sqllang = $this->sheel->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language
			where languageid ='" . $lng . "'
		");
		if ($this->sheel->db->num_rows($sqllang) > 0) {
			$reslang = $this->sheel->db->fetch_array($sqllang, DB_ASSOC);
			$slng = substr($reslang['languagecode'], 0, 3);
		}
		$sql = $this->sheel->db->query("
			SELECT text_$slng AS text
			FROM " . DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			$message = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$finalmessage = $message['text'];

		}
		return $finalmessage;
	}
	private function api_failed($method)
	{
		if (!empty($method)) {
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET failed = failed + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
		}
	}
	private function api_success($method)
	{
		if (!empty($method)) {
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET success = success + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
		}
	}

	private function is_session_valid($sessid = '')
	{
		$sql = $this->sheel->db->query("
			SELECT expiry
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sessid) . "'
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			$sess = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$expiry = strtotime($sess['expiry']);
			$currentTime = time();
			if ($expiry > $currentTime) {
				return true;
			} else {
				return false;
			}
		}
		return false; // Session not found
	}
	private function authenticate($username = '', $password = '', $apikey = '')
	{
		$badusername = true;
		$badpassword = true;
		if (!empty($username)) {
			$sql = $this->sheel->db->query("
				SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE (u.username = '" . $this->sheel->db->escape_string($username) . "' OR u.email = '" . $this->sheel->db->escape_string($username) . "')
					" . ((!empty($apikey)) ? "AND u.apikey = '" . $this->sheel->db->escape_string($apikey) . "' AND u.useapi = '1'" : "") . "
				GROUP BY u.username
				LIMIT 1
			");
			if ($this->sheel->db->num_rows($sql) > 0) {
				$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$badusername = false;
				$md5pass = md5($password);
				$md5pass_utf = md5($password);
				if (
					$userinfo['password'] != md5(md5($password) . $userinfo['salt']) &&
					$userinfo['password'] != md5($md5pass . $userinfo['salt']) &&
					$userinfo['password'] != md5($md5pass_utf . $userinfo['salt'])
				) {
					$badpassword = true;
				} else {
					$badpassword = false;
				}
			}
		}
		return !$badusername && !$badpassword;
	}
	private function login($username = '', $password = '', $apikey = '', $forcebuildsession = false, $rememberuser = false, $ismobile = false, $sesskeyapi = '0')
	{
		$badusername = $badpassword = true;
		if (!empty($username)) {
			$sql = $this->sheel->db->query("
				SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE (u.username = '" . $this->sheel->db->escape_string($username) . "' OR u.email = '" . $this->sheel->db->escape_string($username) . "')
					" . ((!empty($apikey)) ? "AND u.apikey = '" . $this->sheel->db->escape_string($apikey) . "' AND u.useapi = '1'" : "") . "
				GROUP BY u.username
				LIMIT 1
			");
			if ($this->sheel->db->num_rows($sql) > 0) {
				$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$md5pass = $md5pass_utf = md5($password);
				$badusername = $badpassword = false;
				if (
					$userinfo['password'] != iif($password and !$md5pass, md5(md5($password) . $userinfo['salt']), '') and
					$userinfo['password'] != md5($md5pass . $userinfo['salt']) and
					$userinfo['password'] != iif($md5pass_utf, md5($md5pass_utf . $userinfo['salt']), '')
				) {
					$badpassword = true;
				}
			} else {
			}
			if (!$badusername and !$badpassword) {
				$userinfo['shipprofileid'] = $this->sheel->user->fetch_default_ship_profileid($userinfo['user_id']);
				$userinfo['billprofileid'] = $this->sheel->user->fetch_default_bill_profileid($userinfo['user_id']);
				$customer = $this->sheel->customers->get_customer_details($userinfo['customerid']);
				$userinfo['subscriptionid'] = $customer['subscriptionid'];
				$csrf = $this->sheel->sessions->build_user_session($userinfo, false, true, $forcebuildsession, $rememberuser, $ismobile, $sesskeyapi);
				return $csrf;
			}
		}
		return 0;
	}
}