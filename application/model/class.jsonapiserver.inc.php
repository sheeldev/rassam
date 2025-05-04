<?php
class RestApiServer
{
	protected $sheel;
	private $routes = [];
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	public function get_all_endpoints()
	{
		$endpoints = [];
		foreach ($this->routes as $route) {
			$endpoints[] = [
				'method' => $route['method'],
				'path' => $route['path'],
				'handler' => is_array($route['handler']) ? $route['handler'][1] : $route['handler']
			];
		}
		return $endpoints;
	}
	private function validate_request($method, $input, $schema = null, $fieldPath = '')
	{
		$schemas = RestApiSchemas::$schemas;
		if ($schema === null) {
			if (!isset($schemas[$method])) {
				return "Schema not defined for method: $method";
			}
			$schema = $schemas[$method];
		}
		if (isset($schema['required'])) {
			foreach ($schema['required'] as $field) {
				if (!isset($input[$field])) {
					return "Missing required field: " . ($fieldPath ? "$fieldPath.$field" : $field);
				}
			}
		}
		if (isset($schema['properties'])) {
			foreach ($schema['properties'] as $field => $rules) {
				if (isset($input[$field])) {
					$value = $input[$field];
					if (isset($rules['type'])) {
						if ($rules['type'] === 'array') {
							if (!is_array($value)) {
								return "Invalid type for field: " . ($fieldPath ? "$fieldPath.$field" : $field) . ". Expected array.";
							}
							if (isset($rules['items'])) {
								foreach ($value as $index => $item) {
									$itemValidation = $this->validate_request($method, $item, $rules['items'], ($fieldPath ? "$fieldPath.$field" : $field) . "[$index]");
									if ($itemValidation !== true) {
										return $itemValidation;
									}
								}
							}
						} elseif (gettype($value) !== $rules['type']) {
							return "Invalid type for field: " . ($fieldPath ? "$fieldPath.$field" : $field) . ". Expected " . $rules['type'];
						}
					}
					if (isset($rules['enum']) && !in_array($value, $rules['enum'])) {
						return "Invalid value for field: " . ($fieldPath ? "$fieldPath.$field" : $field) . ". Allowed values are: " . implode(', ', $rules['enum']);
					}
					if (isset($rules['pattern']) && !preg_match('/' . $rules['pattern'] . '/', $value)) {
						return isset($rules['errorMessage'])
							? $rules['errorMessage']
							: "Invalid format for field: " . ($fieldPath ? "$fieldPath.$field" : $field);
					}
				}
			}
		}
		return true;
	}
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
		$input = [];
		if ($requestMethod === 'GET') {
			$input = $_GET;
		} else {
			$rawInput = file_get_contents('php://input');
			$input = json_decode($rawInput, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->send_response(['status' => 'error', 'message' => 'Invalid JSON payload: ' . json_last_error_msg(), 'code' => '400']);
				return;
			}
		}
		foreach ($this->routes as $route) {
			if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
				$token='';
				if ($route['path'] !== '/api/system/connect/') {
					$headers = getallheaders();
					$authHeader = $headers['Authorization'] ?? null;
					if (empty($authHeader) || !is_string($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
						return false;
					}
					$token = substr($authHeader, 7);
					$validtoken = $this->validate_token($token);
					if (!$validtoken) {
						$this->send_response(['status' => 'error', 'message' => 'Unauthorized: Invalid or expired token', 'code' => '401']);
						return;
					}
					$this->update_session($token, $route['path']);
				}
				$this->sheel->template->meta['areatitle'] = 'REST API - /api/system/connect/';
				$validation = $this->validate_request($route['handler'][1], $input);
				if ($validation !== true) {
					$this->send_response(['status' => 'error', 'message' => $validation, 'code' => '422']);
					return;
				}
				try {
					$response = call_user_func($route['handler'], $input);
					$this->send_response($response, 200);
				} catch (Exception $e) {
					$this->send_response(['status' => 'error', 'message' => $e->getMessage(), 'code' => '500']);
				}
				return;
			}
		}
		$this->send_response(['status' => 'error', 'message' => 'Route not found', 'code' => '404']);
	}
	private function send_response($response, $statusCode = 200)
	{
		http_response_code($statusCode);
		header('Content-Type: application/json');
		echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		exit();
	}
	private function validate_token($token)
	{
		$sql = $this->sheel->db->query("
			SELECT sesskey, expiry, token, value
			FROM " . DB_PREFIX . "sessions
			WHERE token = '" . $this->sheel->db->escape_string($token) . "'
			AND expiry > UNIX_TIMESTAMP()
			LIMIT 1
		", 1);
		if ($this->sheel->db->num_rows($sql) === 0) {
			return false;
		}
		return true;
	}
	private function update_session($token = '', $url)
	{
		if (!empty($token)) {
			$sql = $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "sessions
				SET url = '" . $url . "',
				title = 'REST API - " . $url . "'
				WHERE token = '" . $token . "'
				LIMIT 1
			");
		}
	}
}

class SheelRestApiServer
{
	protected $sheel;
	private $restApiServer;
	private $endpoints = [];
	private $mysqlErrorCodes = [
		1062 => ['message' => 'Duplicate entry detected', 'status' => 409], 
		1452 => ['message' => 'Foreign key constraint violation', 'status' => 422], 
		1048 => ['message' => 'A required field is missing', 'status' => 400], 
		1146 => ['message' => 'Table does not exist', 'status' => 500], 
		1054 => ['message' => 'Unknown column in the query', 'status' => 400], 
		1064 => ['message' => 'Syntax error in the SQL query', 'status' => 400], 
		1364 => ['message' => 'Field does not have a default value', 'status' => 400],
		1049 => ['message' => 'Unknown database', 'status' => 500], 
		2006 => ['message' => 'MySQL server has gone away', 'status' => 500], 
		2013 => ['message' => 'Lost connection to MySQL server during query', 'status' => 500],
	];
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->restApiServer = new RestApiServer($sheel);

		$this->restApiServer->register_route('GET', '/api/system/endpoints/', [$this, 'system_list_endpoints']);
		$this->restApiServer->register_route('POST', '/api/system/connect/', [$this, 'system_connect']);
		$this->restApiServer->register_route('GET', '/api/system/officialtime/', [$this, 'system_getofficialtime']);
		$this->restApiServer->register_route('GET', '/api/core/scan/', [$this, 'core_scan_get']);
		$this->restApiServer->register_route('POST', '/api/core/scan/', [$this, 'core_scan_post']);
		$this->endpoints = $this->restApiServer->get_all_endpoints();
	}
	public function handle_request()
	{
		$this->restApiServer->handle_request();
	}
	private function success_response($data = [], $message = 'Success')
	{
		return json_encode([
			'status' => 'success',
			'message' => $message,
			'data' => $data
		], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	}
	private function error_response($message, $code = 400)
	{
		return json_encode([
			'status' => 'error',
			'message' => $message,
			'code' => $code
		], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	}
	public function system_connect($input)
	{
		$hascredentials = false;
		$this->api_hit('system_connect');
		if ($input['grant_type'] == 'client_credentials') {
			$sql = $this->sheel->db->query("
				SELECT status
				FROM " . DB_PREFIX . "users 
				WHERE (username = '" . $this->sheel->db->escape_string($input['user_id']) . "' OR email = '" . $this->sheel->db->escape_string($input['user_id']) . "')
				" . ((!empty($input['apikey'])) ? " AND apikey = '" . $this->sheel->db->escape_string($input['apikey']) . "' AND useapi = '1'" : "") . "
				LIMIT 1
			", 1);
		} else {
			return $this->error_response('Invalid grant type', 422);
		}
		if ($this->sheel->db->num_rows($sql) > 0) {
			$hascredentials = true;
			$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($userinfo['status'] == 'unverified') {
				$this->api_failed('system_connect');
				return $this->error_response($this->getmessage('_you_have_not_activated_your_account_via_email', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
			if ($userinfo['status'] == 'banned') {
				$this->api_failed('system_connect');
				return $this->error_response($this->getmessage('_this_account_has_been_banned', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
		}
		if ($hascredentials) {
			if ($this->authenticate($input['user_id'], $input['user_secret'], $input['apikey'])) {
				$result = $this->validate_session($input['user_id'], $input['user_secret'], $input['apikey']);
				$token = '';
				if ($result['status']) {
					$token = $result['token'];
				} else {
					$this->sheel->sessions->start();
					$token = $this->login($input['user_id'], $input['user_secret'], $input['apikey'], true, true, true, $input['apikey']);
				}
				$this->api_success('system_connect');
				return $this->success_response(array('token' => $token), 'Connected.');
			} else {
				$this->api_failed('system_connect');
				return $this->error_response($this->getmessage('_you_have_provided_incorrect_login_credentials', $_SESSION['sheeldata']['user']['languageid']), 422);
			}
		} else {
			return $this->error_response($this->getmessage('_you_have_provided_incorrect_login_credentials', $_SESSION['sheeldata']['user']['languageid']), 422);
		}
	}
	public function system_list_endpoints()
	{
		$this->api_hit('system_list_endpoints');
	
		$endpoints = [];
		foreach ($this->restApiServer->get_all_endpoints() as $route) {
			$handler = $route['handler'];
			$schema = RestApiSchemas::$schemas[$handler] ?? null;
	
			$endpoints[] = [
				'method' => $route['method'],
				'path' => $route['path'],
				'description' => $schema['description'] ?? 'No description available.',
				'schema' => $schema
			];
		}
	
		return $this->success_response($endpoints, 'List of all API endpoints with details');
	}
	public function system_getofficialtime($input)
	{
		$this->api_hit('system_getofficialtime');
		$data = ['datetime' => date('Y-m-d H:i:s')];
		return $this->success_response($data, 'System time retrieved successfully');
	}

	public function core_scan_get($input)
	{
		$this->api_hit('core_scan_get');
		$filter = $input['filter'];
		$filterSql = $this->parse_filter($filter);
		if ($filterSql === false) {
			$this->api_failed('core_scan_get');
			return $this->error_response('Invalid filter format', 400);
		}
		$orderbySql = '';
		if (!empty($input['orderby'])) {
			$orderby = $input['orderby'];
			$orderbySql = $this->parse_orderby($orderby);
			if ($orderbySql === false) {
				$this->api_failed('core_scan_get');
				return $this->error_response('Invalid orderby format', 400);
			}
		}
		$sql = "
			SELECT *
			FROM " . DB_PREFIX . "scan_activities
			WHERE $filterSql
		";
		if (!empty($orderbySql)) {
			$sql .= " ORDER BY $orderbySql";
		}
		$sql .= " LIMIT 1000";
		$result = $this->sheel->db->query($sql, 1);
		$data = [];
		while ($row = $this->sheel->db->fetch_array($result, DB_ASSOC)) {
			$data[] = $row;
		}
		if (empty($data)) {
			return $this->success_response([], 'No records found');
		}
		$this->api_success('core_scan_get');
		return $this->success_response($data, 'Data retrieved successfully');
	}

	public function core_scan_post($input)
	{
		$this->api_hit('core_scan_post');
		if (!isset($input['data']) || !is_array($input['data'])) {
			return $this->error_response('Invalid input format. Expected "data" to be an array of records.', 400);
		}
		$records = $input['data'];
		$this->sheel->db->beginTransaction();

		foreach ($records as $index => $record) {
			try {
				$this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "scan_activities (
						sales_line_unit_id,
						sales_line_id,
						assembly_no,
						mo_no,
						item_code,
						design_code,
						variant_code,
						so_no,
						activity_code,
						activity_name,
						activity_remark,
						activity_type,
						activity_date,
						activity_time
					) VALUES (
						'" . $this->sheel->db->escape_string($record['sales_line_unit_id']) . "',
						'" . $this->sheel->db->escape_string($record['sales_line_id']) . "',
						'" . $this->sheel->db->escape_string($record['assembly_no']) . "',
						'" . $this->sheel->db->escape_string($record['mo_no']) . "',
						'" . $this->sheel->db->escape_string($record['item_code']) . "',
						'" . $this->sheel->db->escape_string($record['design_code']) . "',
						'" . $this->sheel->db->escape_string($record['variant_code']) . "',
						'" . $this->sheel->db->escape_string($record['so_no']) . "',
						'" . $this->sheel->db->escape_string($record['activity_code']) . "',
						'" . $this->sheel->db->escape_string($record['activity_name']) . "',
						" . (isset($record['activity_remark']) ? "'" . $this->sheel->db->escape_string($record['activity_remark']) . "'" : 'NULL') . ",
						'" . $this->sheel->db->escape_string($record['activity_type']) . "',
						'" . $this->sheel->db->escape_string($record['activity_date']) . "',
						" . (int) $record['activity_time'] . "
					)
				", 1);
			} catch (Exception $e) {
				$this->sheel->db->rollback();
				$this->api_failed('core_scan_post');
				return $this->error_response('Transaction failed: ' . $this->mysqlErrorCodes[$e->getMessage()]['message'] . ' On: ' . $record['sales_line_unit_id'] . '|' . $record['activity_code'] . '|' . $record['activity_type'], $this->mysqlErrorCodes[$e->getMessage()]['status']);
			}
		}
		$this->sheel->db->commit();
		$this->api_success('core_scan_post');
		return $this->success_response([], 'All records inserted successfully');
	}

	private function parse_filter($filter)
	{
		$conditions = preg_split('/\s+(AND|OR)\s+/i', $filter, -1, PREG_SPLIT_DELIM_CAPTURE);
		if (!$conditions || count($conditions) === 0) {
			return false;
		}
		$parsedConditions = [];
		$logicalOperator = null;
		foreach ($conditions as $condition) {
			$condition = trim($condition);
			if (strtoupper($condition) === 'AND' || strtoupper($condition) === 'OR') {
				$logicalOperator = strtoupper($condition);
				$parsedConditions[] = $logicalOperator;
				continue;
			}
			if (!preg_match('/^([a-zA-Z0-9_]+) (eq|like|between) (.+)$/', $condition, $matches)) {
				return false;
			}
			$column = $matches[1];
			$operator = $matches[2];
			$value = $matches[3];
			$operatorMap = [
				'eq' => '=',
				'like' => 'LIKE',
				'between' => 'BETWEEN'
			];
			if (!isset($operatorMap[$operator])) {
				return false;
			}
			$sqlOperator = $operatorMap[$operator];
			if ($operator === 'between') {
				$values = explode(' and ', strtolower($value));
				if (count($values) !== 2) {
					return false;
				}
				$value = "'" . $this->sheel->db->escape_string(trim($values[0], "'")) . "' AND '" . $this->sheel->db->escape_string(trim($values[1], "'")) . "'";
			} else {
				$value = trim($value, "'");
				$value = "'" . $this->sheel->db->escape_string($value) . "'";
			}
			$parsedConditions[] = "$column $sqlOperator $value";
		}
		return implode(' ', $parsedConditions);
	}

	private function parse_orderby($orderby)
	{
		$columns = explode(',', $orderby);
		$parsedColumns = [];
		foreach ($columns as $column) {
			$column = trim($column);
			if (!preg_match('/^([a-zA-Z0-9_]+) (asc|desc)$/i', $column, $matches)) {
				return false;
			}
			$columnName = $matches[1];
			$direction = strtoupper($matches[2]);

			$parsedColumns[] = "$columnName $direction";
		}
		return implode(', ', $parsedColumns);
	}
	private function getmessage($var, $lng)
	{
		if ($lng == '') {
			$slng = 'eng';
		}
		else {
			$sqllang = $this->sheel->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language
			where languageid ='" . $lng . "'
		");
		if ($this->sheel->db->num_rows($sqllang) > 0) {
			$reslang = $this->sheel->db->fetch_array($sqllang, DB_ASSOC);
			$slng = substr($reslang['languagecode'], 0, 3);
		}
		}
		$finalmessage = '';
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
	private function api_hit($method)
	{
		if (!empty($method)) {
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
		}
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
	private function validate_session($userid = '', $usersecret = '', $apikey = '')
	{
		if (empty($apikey) || empty($userid) || empty($usersecret)) {
			return ['status' => false, 'token' => null];
		}
		$sql = $this->sheel->db->query("
			SELECT sesskey, expiry, token, value
			FROM " . DB_PREFIX . "sessions
			WHERE sesskeyapi = '" . $this->sheel->db->escape_string($apikey) . "'
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			$session = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$expiry = $session['expiry'];
			$currentTime = time();

			if ($expiry > $currentTime) {
				$this->sheel->GPC['sessid'] = $session['sesskey'];
				$this->sheel->sessions->start();
				$this->login($userid, $usersecret, $apikey, true, true, true, $apikey);
				$this->sheel->sessions->session_write($session['sesskey'], $session['value']);
				return ['status' => true, 'token' => $session['token']];
			}
		}
		return ['status' => false, 'token' => null];
	}
}