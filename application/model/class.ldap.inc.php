<?php
class ldap
{
	protected $sheel;
	var $errors = array();
	var $response = array();
	var $file_log = LDAPLOG;
	var $log = array();
	var $debug = false;
	/*
	possible attributes
	[
	  ["First Name","givenName"  ],
	  ["Middle Name / Initials","initials"  ],
	  ["Last Name","sn"  ],
	  ["Logon Name","userPrincipalName"  ],
	  ["Logon Name (Pre Windows 2000)","sAMAccountName"  ],
	  ["Display Name","displayName"  ],
	  ["Full  Name","name/cn"  ],
	  ["Description","description"  ],
	  ["Office","physicalDeliveryOfficeName"  ],
	  ["Telephone Number","telephoneNumber"  ],
	  ["Email","mail"  ],
	  ["Web Page","wWWHomePage"  ],
	  ["Password","password"  ],
	  ["Street","streetAddress"  ],
	  ["PO Box","postOfficeBox"  ],
	  ["City","l"  ],
	  ["State/Province","st"  ],
	  ["Zip/Postal Code","postalCode"  ],
	  ["Country","co"  ],
	  ["Country 2 Digit Code - eg. US","c"  ],
	  ["Country code -eg. for US country code is 840","countryCode"  ],
	  ["Group","memberOf"  ],
	  ["Account Expires (use same date format as server)","accountExpires"  ],
	  ["User Account Control","userAccountControl"  ],
	  ["Profile Path","profilePath"  ],
	  ["Login Script","scriptPath"  ],
	  ["Home Folder","homeDirectory"  ],
	  ["Home Drive","homeDrive"  ],
	  ["Log on to","userWorkstations"  ],
	  ["Home","homePhone"  ],
	  ["Pager","pager"  ],
	  ["Mobile","mobile"  ],
	  ["Fax","facsimileTelephoneNumber"  ],
	  ["IP Phone","ipPhone"  ],
	  ["Notes","info"  ],
	  ["Title","title"  ],
	  ["Department","department"  ],
	  ["Company","company"  ],
	  ["Manager","manager"  ],
	  ["Mail Alias","mailNickName"  ],
	  ["Simple Display Name","displayNamePrintable"  ],
	]
	*/
	function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	public function authenticate_openldap($username = '', $password = '')
	{ // openldap v3
		$this->open_log();
		if (empty($username) OR empty($password))
		{
			$error = "The username and/or password cannot be blank.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		if (empty($this->sheel->config['ldap_controllers']))
		{
			$error = "Failure: Host for LDAP server is missing or not found.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		if (empty($this->sheel->config['ldap_port']))
		{
			$error = "Failure: Port for LDAP server is missing or not found.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		if ($connect = ldap_connect($this->sheel->config['ldap_controllers'], $this->sheel->config['ldap_port']))
		{ // if connected to ldap server
			if ($this->sheel->config['ldap_tls'] AND !ldap_start_tls($connect))
			{
				$error = "Secure TLS could not be established while connecting to the LDAP server.";
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
		        }
			ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($connect, LDAP_OPT_NETWORK_TIMEOUT, 10);
			$username = $username . $this->sheel->config['ldap_login_suffix']; // Peter@company.local (example)
			if (!$bind = ldap_bind($connect, $this->sheel->config['ldap_login_attribute'] . $username . ',' . $this->sheel->config['ldap_baserdn'], $password))
			{ // could be uid=Peter or mydomain\\Peter
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			// Look up user and get first name, middle initial, last name, and email address
			if (!$result = ldap_search($connect, $this->sheel->config['ldap_baserdn'], "(" . $this->sheel->config['ldap_login_attribute'] . "$username)"))
			{
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			if (!$info = ldap_get_entries($connect, $result))
			{
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			//print_r($info); exit;
			$user = array();
			if ($info['count'] == 1)
			{
				$user['driver'] = 'Open LDAP v3';
				$user['dn'] = ((isset($info[0]['dn'])) ? $info[0]['dn'] : '');
				$user['firstname'] = ((isset($info[0]['givenname'][0])) ? $info[0]['givenname'][0] : '');
				$user['lastname'] = ((isset($info[0]['sn'][0])) ? $info[0]['sn'][0] : '');
				$user['fullname'] = ((isset($info[0]['cn'][0])) ? $info[0]['cn'][0] : trim($user['firstname'] . ' ' . $user['lastname']));
				$user['firstname'] = ((empty($user['firstname'])) ? explode(' ', $user['fullname'])[0] : '');
				$user['email'] = ((isset($info[0]['mail'][0])) ? $info[0]['mail'][0] : '');
				$user['address'] = ((isset($info[0]['streetaddress'][0])) ? $info[0]['streetaddress'][0] : '');
				$user['city'] = ((isset($info[0]['l'][0])) ? $info[0]['l'][0] : '');
				$user['state'] = ((isset($info[0]['st'][0])) ? $info[0]['st'][0] : '');
				$user['zipcode'] = ((isset($info[0]['postalcode'][0])) ? $info[0]['postalcode'][0] : '');
				$user['country'] = ((isset($info[0]['co'][0])) ? $info[0]['co'][0] : '');
				$user['phone'] = ((isset($info[0]['telephonenumber'][0])) ? $info[0]['telephonenumber'][0] : '');
				$user['groups'] = ((isset($info[0]['memberof'])) ? $info[0]['memberof'] : array());
			}
			ldap_close($connect);
			if (!empty($this->sheel->config['ldap_required_groups']) AND isset($info[0]['memberof']) AND count($info[0]['memberof']) > 0)
			{
				$allowedgroups = explode(';', $this->sheel->config['ldap_required_groups']);
				$access = false;
				$accessgroup = '';
				foreach ($info[0]['memberof'] AS $grps)
				{
					if (in_array($grps, $allowedgroups))
					{
						$accessgroup = $grps;
						$access = true;
					}
				}
				if (!empty($this->sheel->config['ldap_default_membership']) AND !empty($accessgroup))
				{
					$forceplans = explode(';', $this->sheel->config['ldap_required_groups']); // Buyers=1;Sellers=2;Admins=4
					foreach ($forceplans AS $key => $value)
					{ // Buyers=1
						$tmp = explode('=', $value);
						if (isset($tmp[0]) AND strtolower($tmp[0]) == strtolower($accessgroup))
						{
							$user['force_subscriptionid'] = $tmp[1]; // plan id
						}
					}
				}
				if ($access)
				{ // establish session variables
					$this->response = $user;
					$this->set_log('Successfully authenticated with the Open LDAP server.');
					$this->close_log();
					return true;
				}
				else
				{ // user has no rights
					$error = "Invalid access rights [memberof] from Open LDAP server.";
					$this->errors[] = $error;
					$this->set_error($error);
					$this->close_log();
					return false;
				}
			}
			$this->response = $user;
			$this->set_log('Successfully authenticated with the Open LDAP server.');
			$this->close_log();
			return true;
		}
		else
		{ // no conection to ldap server
			$error = ldap_error($connect);
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
		}
		ldap_close($connect);
		return false;
	}
	public function authenticate($username = '', $password = '')
	{ // active directory v3
		$this->open_log();
		if (empty($username) OR empty($password))
		{
			$error = "The username and/or password cannot be blank.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		if (empty($this->sheel->config['ldap_controllers']))
		{
			$error = "Failure: Host for LDAP server is missing or not found.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		if (empty($this->sheel->config['ldap_port']))
		{
			$error = "Failure: Port for LDAP server is missing or not found.";
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
			return false;
		}
		// connect to active directory
		if ($connect = ldap_connect($this->sheel->config['ldap_controllers'], $this->sheel->config['ldap_port']))
		{ // if connected to ldap server
			if ($this->sheel->config['ldap_tls'] AND !ldap_start_tls($connect))
			{
				$error = "Secure TLS could not be established while connecting to the LDAP server.";
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
		        }
			ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($connect, LDAP_OPT_NETWORK_TIMEOUT, 30);
			$username = $username . $this->sheel->config['ldap_login_suffix']; // Peter@company.local (example)
			if (!$bind = ldap_bind($connect, $this->sheel->config['ldap_login_attribute'] . $username, $password))
			{
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			$searchfilter = '';
			if (!empty($this->sheel->config['ldap_login_attribute']) AND strrchr($this->sheel->config['ldap_login_attribute'], '%username'))
			{
				$searchfilter = '(' . str_replace('%username', $username, $this->sheel->config['ldap_login_attribute']) . ')';
			}
			//$attr = array("memberof","givenname","sn","etc..");
			//if (!$result = ldap_search($connect, $this->sheel->config['ldap_baserdn'], $searchfilter, $attr))
			if (!$result = ldap_search($connect, $this->sheel->config['ldap_baserdn'], $searchfilter))
			{
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			ldap_sort($connect, $result, "sn");
			if (!$info = ldap_get_entries($connect, $result))
			{
				$error = ldap_error($connect);
				$this->errors[] = $error;
				$this->set_error($error);
				$this->close_log();
				return false;
			}
			//print_r($info); exit;
			$user = array();
			if (isset($info['count']) AND $info['count'] == 1)
			{
				$user['driver'] = 'LDAP Active Directory';
				$user['dn'] = ((isset($info[0]['dn'])) ? $info[0]['dn'] : '');
				$user['distinguishedname'] = ((isset($info[0]['distinguishedname'][0])) ? $info[0]['distinguishedname'][0] : '');
				$user['firstname'] = ((isset($info[0]['givenname'][0])) ? $info[0]['givenname'][0] : '');
				$user['lastname'] = ((isset($info[0]['sn'][0])) ? $info[0]['sn'][0] : '');
				$user['fullname'] = ((isset($info[0]['cn'][0])) ? $info[0]['cn'][0] : trim($user['firstname'] . ' ' . $user['lastname']));
				$user['firstname'] = ((empty($user['firstname'])) ? explode(' ', $user['fullname'])[0] : '');
				$user['email'] = ((isset($info[0]['mail'][0])) ? $info[0]['mail'][0] : '');
				$user['address'] = ((isset($info[0]['streetaddress'][0])) ? $info[0]['streetaddress'][0] : '');
				$user['city'] = ((isset($info[0]['l'][0])) ? $info[0]['l'][0] : '');
				$user['state'] = ((isset($info[0]['st'][0])) ? $info[0]['st'][0] : '');
				$user['zipcode'] = ((isset($info[0]['postalcode'][0])) ? $info[0]['postalcode'][0] : '');
				$user['country'] = ((isset($info[0]['co'][0])) ? $info[0]['co'][0] : '');
				$user['phone'] = ((isset($info[0]['telephonenumber'][0])) ? $info[0]['telephonenumber'][0] : '');
				$user['groups'] = ((isset($info[0]['memberof'])) ? $info[0]['memberof'] : array());
			}
			ldap_close($connect);
			if (!empty($this->sheel->config['ldap_required_groups']) AND isset($info[0]['memberof']) AND count($info[0]['memberof']) > 0)
			{
				$allowedgroups = explode(';', $this->sheel->config['ldap_required_groups']);
				$access = false;
				$accessgroup = '';
				foreach ($info[0]['memberof'] AS $grps)
				{
					if (in_array($grps, $allowedgroups))
					{
						$accessgroup = $grps;
						$access = true;
					}
				}
				if (!empty($this->sheel->config['ldap_default_membership']) AND !empty($accessgroup))
				{
					$forceplans = explode(';', $this->sheel->config['ldap_required_groups']); // Buyers=1;Sellers=2;Admins=4
					foreach ($forceplans AS $key => $value)
					{ // Buyers=1
						$tmp = explode('=', $value);
						if (isset($tmp[0]) AND strtolower($tmp[0]) == strtolower($accessgroup))
						{
							$user['force_subscriptionid'] = $tmp[1]; // plan id
						}
					}
				}
				if ($access)
				{ // establish session variables
					$this->response = $user;
					$this->set_log('Successfully authenticated with the LDAP server.');
					$this->close_log();
					return true;
				}
				else
				{ // user has no rights
					$error = "Invalid access rights [memberof] from LDAP server.";
					$this->errors[] = $error;
					$this->set_error($error);
					$this->close_log();
					return false;
				}
			}
			$this->response = $user;
			$this->set_log('Successfully authenticated with the LDAP server.');
			$this->close_log();
			return true;
		}
		else
		{
			$error = ldap_error($connect);
			$this->errors[] = $error;
			$this->set_error($error);
			$this->close_log();
		}
		ldap_close($connect);
		return false;
	}
	function admincp_test()
	{
		$this->open_log();
		$response = 'success';
		$message = 'Successfully connected to the LDAP server.';
		if (!$connect = ldap_connect($this->sheel->config['ldap_controllers'], $this->sheel->config['ldap_port']))
		{
			$message = ldap_error($connect);
			$this->set_error($message);
			$response = array('response' => 'failed', 'message' => $message);
			$this->close_log();
			return $response;
		}
		if ($this->sheel->config['ldap_tls'] AND !ldap_start_tls($connect))
		{
			$message = 'Secure TLS could not be established while connecting to the LDAP server.';
			$this->set_error($message);
			$response = array('response' => 'failed', 'message' => $message);
			$this->close_log();
			return $response;
		}
		$this->set_log('Successfully connected to the LDAP server.');
		// authentication
		// ..
		$response = array('response' => $response, 'message' => $message);
		$this->close_log();
		return $response;
	}
	/*
        * Function to set an apache style payment log record from any error array details supplied
        *
        * @param       string         error text message
        *
        * @return      nothing
        */
        function set_error($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] [error] ' . $text;
		$this->save_log();
	}
        /*
        * Function to set an apache style debug log record from any error array details supplied
        *
        * @param       string         error text message
        *
        * @return      nothing
        */
        function set_debug($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] [debug] ' . $text;
		$this->save_log();
	}
        function open_log()
        {
                $this->log[] = "----------------------------------------------------------------------------------------------------
=> Log opened from ILance LDAP (ldapiL) at " . vdate('D M d Y H:i:s O', time());
		$this->save_log();
        }
        function close_log()
        {
                $this->log[] = "=> Log closed " . vdate('D M d Y H:i:s O', time());
		$this->save_log();
        }
	/*
        * Function to set an apache style log record from text being sent from the upgrader
        *
        * @param       string         text message to log
        *
        * @return      nothing
        */
        function set_log($text = '')
	{
		$this->log[] = '[' . vdate('d/M/Y:H:i:s O', time()) . '] ' . $text;
		$this->save_log();
	}
	/*
        * Function to save the log response to the log file
        *
        * @return      nothing
        */
        function save_log()
	{
		if (!empty($this->file_log) AND file_exists($this->file_log))
		{
			if (isset($this->log) AND count($this->log) > 0)
			{
				if ($fp = fopen($this->file_log, 'a'))
				{
					foreach ($this->log AS $line)
					{
						fwrite($fp, $line . LINEBREAK);
					}
					fclose($fp);
				}
			}
		}
		$this->log = array();
	}
	function clean_logs()
        { // runs monthly
                if (!empty($this->file_log) AND file_exists($this->file_log))
		{
                        $fp = fopen($this->file_log, "w");
                        fclose($fp);
                }
                return 'ldap->clean_logs(), ';
        }
}
?>
