<?php
class xmlrpcserver
{
	private $server_handler;
	private $external_functions;
	public function __construct()
	{
		$this->server_handler = xmlrpc_server_create();
		$this->external_functions = array();
	}
	public function register_method($external_name, $function, $parameter_names)
	{
		if ($function == null)
		{
			$function = $external_name;
		}
		xmlrpc_server_register_method($this->server_handler, $external_name, array(&$this, 'call_method'));
		$this->external_functions[$external_name] = array('function' => $function, 'parameter_names' => $parameter_names);
	}
	public function call_method($function_name, $parameters_from_request)
	{
		$function = $this->external_functions[$function_name]['function'];
		$parameter_names = $this->external_functions[$function_name]['parameter_names'];
		$parameters = array();
		if (!empty($parameter_names) AND count($parameter_names) > 0)
		{
			foreach ($parameter_names AS $parameter_name)
			{
				$parameters[] = (isset($parameters_from_request[0][$parameter_name]) ? $parameters_from_request[0][$parameter_name] : '');
			}
		}
		return call_user_func_array($function, $parameters);
	}
	public function send_reponse()
	{
		$options = ['output_type' => 'xml', 'verbosity' => 'pretty', 'escaping' => ['markup'], 'version' => 'xmlrpc', 'encoding' => 'utf-8'];
		return xmlrpc_server_call_method($this->server_handler, file_get_contents('php://input'), null, $options);
	}
}
class sheel_xmlrpcserver
{
	protected $sheel;
	private $xmlrpcserver;
	var $csrftoken = '';
	public function __construct($sheel, $xmlrpcserver)
	{
		$this->sheel = $sheel;
		$this->xmlrpcserver = $xmlrpcserver;
		$this->xmlrpcserver->register_method('system.officialtime', array(&$this, 'system_getofficialtime'), array('sessid'));
		$this->xmlrpcserver->register_method('system.officialtime.formatted', array(&$this, 'system_getofficialtimeformatted'), array('sessid'));
		
		$this->xmlrpcserver->register_method('system.connect', array(&$this, 'system_connect'), array('devicetoken')); // <-- retrieves a session id & csrf token for subsequent connections
		$this->xmlrpcserver->register_method('user.signin', array(&$this, 'user_signin'), array('username', 'password', 'apikey', 'csrftoken','rememberuser','devicetoken'));
		$this->xmlrpcserver->register_method('user.signout', array(&$this, 'user_signout'), array('sessid'));
		$this->xmlrpcserver->register_method('user.register', array(&$this, 'user_register'), array('username', 'password', 'email', 'firstname', 'lastname', 'address', 'address2', 'phone', 'country', 'city', 'state', 'zipcode', 'currency', 'acceptsmarketing',  'secretquestion', 'secretanswer', 'dob', 'csrftoken'));
		
	}
	public function system_getofficialtime($sessid)
	{ // retrieves the official system time
		return array('error' => '0', 'message' => '', 'datetime' => DATETIME24H);
	}
	public function system_getofficialtimeformatted($sessid)
	{ // retrieves the official system time
		return array('error' => '0', 'message' => '', 'datetime' => $this->sheel->common->print_date(DATETIME24H));
	}
    public function system_connect($devicetoken = '0')
	{ // retrieves a session id & csrf token for subsequent connections
		$this->sheel->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->sheel->db->escape_string('system.connect') . "'
		LIMIT 1
		");
		if (!isset($_SESSION['sheeldata']['user']['userid']) OR (isset($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] <= 0) OR (!isset($_SESSION['sheeldata']['user']['csrf']))) {
			$_SESSION['sheeldata']['user']['ismobile'] = '1';
			if ($devicetoken=='') {
				$_SESSION['sheeldata']['user']['devicetoken'] = '0';
			}
			else {
				$_SESSION['sheeldata']['user']['devicetoken'] = $devicetoken;
			}
			
			return array('error' => '0', 'message' => 'Connected.', 'sessid' => session_id(), 'csrftoken' => $_SESSION['sheeldata']['user']['csrf'], 'expiry' => $this->getsessionexpiry(session_id()), 'prefix' => COOKIE_PREFIX);
		}
		else {
			
			return array('error' => '0', 'message' => 'Connected.', 'sessid' => '0', 'csrftoken' => $_SESSION['sheeldata']['user']['csrf'], 'expiry' => $this->getsessionexpiry(session_id()) , 'prefix' => COOKIE_PREFIX);
		}
		
	}
	public function user_signin($username, $password, $apikey, $csrftoken, $rememberuser, $devicetoken)
	{ 

		$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string('user.signin') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['sheeldata']['user']['csrf']){
			$this->api_failed('user.signin');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->sheel->db->query("
		SELECT status
		FROM " . DB_PREFIX . "users 
		WHERE (username = '" . $this->sheel->db->escape_string($username) . "' OR email = '" . $this->sheel->db->escape_string($username) . "' OR companyname = '" . $this->sheel->db->escape_string($username) . "')
			" . ((!empty($apikey)) ? "AND apikey = '" . $this->sheel->db->escape_string($apikey) . "' AND useapi = '1'" : "") . "
		LIMIT 1
		");

		if ($this->sheel->db->num_rows($sql) > 0) {
			$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);

			if ($userinfo['status'] == 'unverified') {
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_you_have_not_activated_your_account_via_email', $_SESSION['sheeldata']['user']['languageid']));
			}

			if ($userinfo['status'] == 'banned') {
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_this_account_has_been_banned', $_SESSION['sheeldata']['user']['languageid']));
			}
		}
		if ($this->authorize($username, $password, $apikey)) {
			$usertoken = $this->sheel->common->login($username, $password, $apikey, $csrftoken, true, $rememberuser, true, $devicetoken);

			if ($usertoken == '' )
			{
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_user_password_incorrect', $_SESSION['sheeldata']['user']['languageid']));
			}
			$this->api_success('user.signin');
			return array('error' => '0', 'message' => 'Authentication successful.', 'sessid' => session_id() , 'csrftoken' => $usertoken);
		}
		else {
			$this->api_failed('user.signin');
			return array('error' => '1', 'message' => $this->getmessage('_app_user_password_incorrect', $_SESSION['sheeldata']['user']['languageid']));
		}

		$this->api_failed('user.signin');
		return array('error' => '1', 'message' => 'Could not authenticate. General Error');
	}

	public function user_signout($sessid)
	{ 
		$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string('user.signout') . "'
				LIMIT 1
			");
		if ($sessid == session_id())
		{
			$this->sheel->common->logout($sessid);
			$this->api_success('user.signout');
			return array('error' => '0', 'message' => 'Sign-out successful.');
		}
		$this->api_failed('user.signout');
		return array('error' => '1', 'message' => 'Sign-out requires a valid sessid.');
	}

	public function user_register($username, $password, $email, $firstname, $lastname, $address, $address2, $phone, $country, $city, $state, $zipcode, $currency, $acceptsmarketing, $secretquestion, $secretanswer, $dob, $gender, $csrftoken)
	{ 
		$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string('user.register') . "'
				LIMIT 1
			");
		$ccode='';
		$sql = $this->sheel->db->query("
			SELECT cc
			FROM " . DB_PREFIX . "locations 
			WHERE visible = '1' AND locationid ='".$country."'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);

		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$ccode= $res['cc'];
			}
		unset($sql);
		
		$status = $this->sheel->registration->register($username, $password, $email, $firstname, $lastname, $address, $address2, $phone, $ccode, $city, $state, $zipcode, $currency, 'XML-RPC', $acceptsmarketing, $secretquestion,  $secretanswer, $dob, $gender);
		// 1 = active, 2 = unverified, 3 = moderated or string for error output message
		$message = '';
		if ($status == '1' || $status == '2' || $status == '3')
		{
			
			switch ($status)
                        {
                                case '1':
                                {
								   $message = 'Account Successfully Registered.';
								   break;
                                }
                                case '2':
                                {
									$message = 'Account Successfully Registered. Please Verify Your Email Before Logging In.';
									break;
                                }
                                case '3':
                                {
									$message = 'Account Successfully Registered. Please Hold For Activation By Our Staff.';
									break;
								}
								default:
								{
									$message = 'Error Creating Account.';
									break;
								} 

							
                        }

		}
		else
		{
			$this->api_failed('user.register');
			return array('error' => '1', 'message' => 'Registration Failed due to Unkonwn Status.');
		}
		//$timezone = geoip_time_zone_by_country_and_region('CA', 'QC');
		$this->api_success('user.register');
		return array('error' => (($status == '1'|| $status == '2' || $status == '3') ? '1' : '1'), 'message' => $message, 'status' => $status);
	}








	public function build_md5_filelist()
	{
		$this->sheel->security->build_md5_filelist();
		return 'success';
	}
	private function api_failed($method)
	{
		if (!empty($method))
		{
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
		if (!empty($method))
		{
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET success = success + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
		}
	}
	public function authenticate_staff($username, $password, $apikey, $guestcsrftoken, $method)
	{ // verify username, password, api key & csrf token for any request
		if (
			!isset($_SESSION['sheeldata']['user']['userid']) OR
			(isset($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] <= 0) OR
			(isset($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['isadmin'] <= 0))
		{
			$this->csrftoken = $this->sheel->common->login($username, $password, $apikey, $guestcsrftoken, true); // true denotes force building of $_SESSION
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
			if ($this->csrftoken != '' AND $this->csrftoken <= 0)
			{
				return false;
			}
			if (isset($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] > 0 AND isset($_SESSION['sheeldata']['user']['isadmin']) AND $_SESSION['sheeldata']['user']['isadmin'] > 0)
			{
				return true;
			}
			return false;
		}
		else
		{
			$this->csrftoken = $_SESSION['sheeldata']['user']['csrf'];
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
		}
		return true;
	}

	public function getphrase($var, $lng) {
		$slng = substr($lng,0,3);
		$sql = $this->sheel->db->query("
			SELECT text_$slng AS text
			FROM " .DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$phrase = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return  $phrase['text'];
		}
		return 'Could not find Any Phrase.';
	}

	public function getsessionexpiry($sessid='') {
		$sql = $this->sheel->db->query("
		SELECT expiry
		FROM " . DB_PREFIX . "sessions
		WHERE sesskey = '" . $sessid . "'
		LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			$sess = $this->sheel->db->fetch_array($sql, DB_ASSOC);	
			
			return $sess['expiry'];
		}
		return '0';
	}
	public function authorize($username, $password, $apikey) {
		$badpassword = true;
		if (!empty($username))
		{
			$sql = $this->sheel->db->query("
				SELECT u.username, u.password, u.apikey, u.salt
				FROM " . DB_PREFIX . "users AS u
				WHERE (u.username = '" . $this->sheel->db->escape_string($username) . "' OR u.email = '" . $this->sheel->db->escape_string($username) . "' OR u.companyname = '" . $this->sheel->db->escape_string($username) . "')
					AND u.status='active'
					" . ((!empty($apikey)) ? "AND u.apikey = '" . $this->sheel->db->escape_string($apikey) . "' AND u.useapi = '1'" : "") . "
				LIMIT 1
			");
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$md5pass = $md5pass_utf = md5($password);
				$badpassword = false;
				if (
					$userinfo['password'] != iif($password AND !$md5pass, md5(md5($password) . $userinfo['salt']), '') AND
					$userinfo['password'] != md5($md5pass . $userinfo['salt']) AND
					$userinfo['password'] != iif($md5pass_utf, md5($md5pass_utf . $userinfo['salt']), '')
				)
				{
					$badpassword = true;
				}
			}
			else
			{
				return false;
			}
			if ($badpassword)
			{
				return false;
			}
		}	
		return true;
	}

	public function authenticate($username, $password, $apikey, $guestcsrftoken, $method, $rememberuser)
	{ // verify username, password, api key & csrf token for any request
		if (!isset($_SESSION['sheeldata']['user']['userid']) OR (isset($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] <= 0) OR (!isset($_SESSION['sheeldata']['user']['csrf'])))
		{
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->sheel->db->escape_string($method) . "'
				LIMIT 1
			");
			if ($this->authorize($username, $password, $apikey)) {
				$this->csrftoken = $this->sheel->common->login($username, $password, $apikey, $guestcsrftoken, true, $rememberuser, true); // true denotes force building of $_SESSION
				if ($this->csrftoken != '' AND $this->csrftoken <= 0)
				{
					return false;
				}

				if ($this->csrftoken == '' )
				{
					return false;
				}
				return true;
			}
			else {
				return false;
			}
		}
		else
		{
			if ($this->authorize($username, $password, $apikey)) 
			{
				$this->csrftoken = $_SESSION['sheeldata']['user']['csrf'];
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "api
					SET hits = hits + 1
					WHERE name = '" . $this->sheel->db->escape_string($method) . "'
					LIMIT 1
				");
			}

			else {
				return false;
			}
			
		}
		return true;
	}
    public function getmessage($var, $lng) 
	{
		$finalmessage='';
		$sqllang = $this->sheel->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language
			where languageid ='".$lng."'
		");
		if ($this->sheel->db->num_rows($sqllang) > 0) {
			$reslang = $this->sheel->db->fetch_array($sqllang, DB_ASSOC);
			$slng = substr($reslang['languagecode'],0,3);
		}
		$sql = $this->sheel->db->query("
			SELECT text_$slng AS text
			FROM " .DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$message = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$finalmessage = $message['text'];
	
		}
		return $finalmessage;
	}
}
?>
