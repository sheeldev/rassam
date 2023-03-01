<?php
if (defined('LOCATION') and LOCATION == 'admin') {
	define('IN_ADMIN_CP', true);
} else {
	define('IN_ADMIN_CP', false);
}
define('SESSIONHOST', mb_substr(IPADDRESS, 0, 15));

/**
 * Session class to perform the majority of session functionality in sheel.
 *
 * @package      sheel\Sessions
 * @version      6.0.0.622
 * @author       sheel
 */
class sessions
{
	protected $sheel;
	private $sessionencrypt = false;
	/**
	 * Constructor
	 *
	 * @param       $registry	sheel registry object
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->start();
		session_set_save_handler(
			array(&$this, 'session_open'),
			array(&$this, 'session_close'),
			array(&$this, 'session_read'),
			array(&$this, 'session_write'),
			array(&$this, 'session_destroy'),
			array(&$this, 'session_gc')
		);
	}
	function start()
	{
		if (!session_id()) {
			if (isset($this->sheel->GPC['sessid']) and !empty($this->sheel->GPC['sessid'])) { // mobile or external app session id (obtained by rpc)
				
				session_start($this->sheel->GPC['sessid']);
			} else { // web visitor
				session_start();
			}
			
			$this->handle_language_style_changes();
			$this->init_remembered_session();
		} else {
			die('Fatal: The application must have ownership of the very first session_start().  A previously created session was detected');
		}
	}
	/**
	 * Encrypt and compress the serailized session data
	 *
	 * @param       array        session data
	 * @return      string       Encrypted session data
	 */
	function encrypt($data = '')
	{
		if ($this->sessionencrypt) {
			return $this->sheel->crypt->encrypt($data);
		}
		return $data;
	}

	/**
	 * Decrypt and return the encrypted or serialized session data
	 *
	 * @param       string       encrypted session data
	 * @return      array        Session data
	 */
	function decrypt($data = '')
	{
		if ($this->sessionencrypt) {
			return $this->sheel->crypt->decrypt($data);
		}
		return $data;
	}

	/**
	 * Fetch session first click if applicable
	 *
	 * @param       string       session key
	 *
	 * @return      string       Returns first click timestamp
	 */
	function session_firstclick($sessionkey = '')
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "firstclick
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sessionkey) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['firstclick'];
		}
		return TIMESTAMPNOW;
	}

	/**
	 * Session open handler
	 *
	 * @return      bool         true if session data could be opened
	 */
	function session_open($savepath = '', $sessioname = '')
	{
		return true;
	}

	/**
	 * Session close handler
	 *
	 * @return      bool         true if session data could be closed
	 */
	function session_close()
	{
		$this->session_gc();
		return true;
	}

	/**
	 * Session read handler is called once the script is loaded
	 *
	 * @param       string       session key
	 *
	 * @return      string       value from the session table
	 */


	function session_read($sessionkey)
	{
		$result = $this->sheel->db->query("
			SELECT value
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sessionkey) . "'
				AND expiry > " . TIMESTAMPNOW . "
		", 0, null, __FILE__, __LINE__);
		if (list($value) = $this->sheel->db->fetch_row($result)) {
			return $value;
		}
		return '';
	}
	function session_get_key($sessionkey)
	{
		$result = $this->sheel->db->query("
			SELECT sesskey
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sessionkey) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Session write handler is called once the script is finished executing
	 *
	 * @param       string       session key
	 * @param       string       session data we would like to update
	 */
	function session_write($sessionkey = '', $sessiondata = '')
	{
		$session = array();
		$skipsession = array('cron', 'javascript');
		if (defined('SKIP_SESSION') and SKIP_SESSION or defined('LOCATION') and in_array(LOCATION, $skipsession)) {
			return true;
		}
		if (empty($_COOKIE[COOKIE_PREFIX . 'lastvisit'])) { // if we've never been here before, we'll create a "last visit" cookie to remember the user
			set_cookie('lastvisit', DATETIME24H);
		}
		
		// we will continue to update our last activity cookie on each page hit
		set_cookie('lastactivity', DATETIME24H);
		
		$scriptname = ((!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '');
		
		$firstclick = $this->session_firstclick($sessionkey);
		$lastclick = $this->session_firstclick($sessionkey);
		$session['sheeldata'] = unserialize(str_replace('sheeldata|', '', $sessiondata));
		$session['sheeldata']['user']['url'] = $scriptname; // . $querystring;

		$session['sheeldata']['user']['area_title'] = !empty($this->sheel->template->meta['areatitle']) ? $this->sheel->template->meta['areatitle'] : '{_unknown}';
		$storeid = "'" . ((!empty($this->sheel->template->meta['area']) and $this->sheel->template->meta['area'] == 'main_store' and isset($this->sheel->template->meta['storeid'])) ? intval($this->sheel->template->meta['storeid']) : '0') . "',";
		
		if (isset($this->sheel->show['searchengine']) and $this->sheel->show['searchengine']) { // search bots and crawlers
			$expiry = "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_crawlertimeout'] * 60)) . "',";
			$userid = "'0',";
			$isuser = "'0',";
			$isadmin = "'0',";
			$isrobot = "'1',";
			$iserror = "'0',";
		} else if (!empty($session['sheeldata']['user']['userid'])) { // user and staff
			if ($session['sheeldata']['user']['ismobile'] == '2') {
				$expiry = "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_mobileusertimeout'] * 60)) . "',";
			} else if ($session['sheeldata']['user']['ismobile'] == '1') {
				$expiry = "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_mobileguesttimeout'] * 60)) . "',";
			} else {
				$expiry = ((IN_ADMIN_CP and $session['sheeldata']['user']['isadmin']) ? "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_admintimeout'] * 60)) . "'," : "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_membertimeout'] * 60)) . "',");
			}
			$userid = "'" . $session['sheeldata']['user']['userid'] . "',";
			$isuser = ((IN_ADMIN_CP and $session['sheeldata']['user']['isadmin']) ? "'0'," : "'1',");
			$isadmin = ((IN_ADMIN_CP and $session['sheeldata']['user']['isadmin']) ? "'1'," : "'0',");
			$isrobot = "'0',";
			$iserror = "'0',";
		} else { // guests
			if (defined('LOCATION') and LOCATION == 'registration') { // allow more time for guests during registration
				$expiry = "'" . (TIMESTAMPNOW + (($this->sheel->config['globalserversession_guesttimeout'] + 100) * 60)) . "',";
			} else {
				if ($session['sheeldata']['user']['ismobile'] == '1') {
					$expiry = "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_mobileguesttimeout'] * 60)) . "',";
				} else {
					$expiry = "'" . (TIMESTAMPNOW + ($this->sheel->config['globalserversession_guesttimeout'] * 60)) . "',";
				}

			}
			$userid = "'0',";
			$isuser = "'0',";
			$isadmin = "'0',";
			$isrobot = "'0',";
			$iserror = "'0',";
		}
		if (defined('SKIP_SESSION_TITLE') and SKIP_SESSION_TITLE) {
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "sessions
				SET expiry = $expiry
				value = '" . $this->sheel->db->escape_string($sessiondata) . "',
				userid = $userid
				isuser = $isuser
				isadmin = $isadmin
				isrobot = $isrobot
				iserror = $iserror
				storeid = $storeid
				languageid = '" . intval($session['sheeldata']['user']['languageid']) . "',
				styleid = '" . intval($session['sheeldata']['user']['styleid']) . "',
				agent = '" . $this->sheel->db->escape_string(USERAGENT) . "',
				lastclick = '" . $this->sheel->db->escape_string($lastclick) . "',
				ipaddress = '" . $this->sheel->db->escape_string(IPADDRESS) . "',
				firstclick = '" . $this->sheel->db->escape_string($firstclick) . "',

				browser ='" . ($session['sheeldata']['user']['ismobile'] == '1' ? $session['sheeldata']['user']['devicetoken'] : $this->sheel->db->escape_string($this->sheel->common->fetch_browser_name())) . "',
				token = '" . $this->sheel->db->escape_string(TOKEN) . "',
				siteid = '" . $this->sheel->db->escape_string(SITE_ID) . "'
				WHERE sesskey = '" . $this->sheel->db->escape_string($sessionkey) . "'
			", 0, null, __FILE__, __LINE__);
		} else {
			$this->sheel->db->query("
				REPLACE " . DB_PREFIX . "sessions
				(sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, storeid, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
				VALUES(
				'" . $this->sheel->db->escape_string($sessionkey) . "',
				$expiry
				'" . $this->sheel->db->escape_string($sessiondata) . "',
				$userid
				$isuser
				$isadmin
				$isrobot
				$iserror
				$storeid
				'" . intval($session['sheeldata']['user']['languageid']) . "',
				'" . intval($session['sheeldata']['user']['styleid']) . "',
				'" . $this->sheel->db->escape_string(USERAGENT) . "',
				'" . TIMESTAMPNOW . "',
				'" . $this->sheel->db->escape_string(IPADDRESS) . "',
				'" . $this->sheel->db->escape_string($session['sheeldata']['user']['url']) . "',
				'" . $this->sheel->db->escape_string($session['sheeldata']['user']['area_title']) . "',
				'" . $this->sheel->db->escape_string($firstclick) . "',
				'" . ($session['sheeldata']['user']['ismobile'] == '1' || $session['sheeldata']['user']['ismobile'] == '2' ? $session['sheeldata']['user']['devicetoken'] : $this->sheel->db->escape_string($this->sheel->common->fetch_browser_name())) . "',
				'" . $this->sheel->db->escape_string(TOKEN) . "',
				'" . $this->sheel->db->escape_string(SITE_ID) . "')
			", 0, null, __FILE__, __LINE__);
		}
		return true;
	}

	/**
	 * Session destroy handler
	 *
	 * @param       string       session key
	 * @return      void
	 */
	function session_destroy($sessionkey = '')
	{
		$this->sheel->db->query("
			DELETE FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sessionkey) . "'
		", 0, null, __FILE__, __LINE__);
		return true;
	}

	/**
	 * Session garbage collection handler
	 *
	 * @return      void
	 */
	function session_gc($maxlifetime = '')
	{
		$this->sheel->db->query("
			DELETE
			FROM " . DB_PREFIX . "sessions
			WHERE expiry < " . TIMESTAMPNOW
			,
			0,
			null,
			__FILE__,
			__LINE__
		);
		return true;
	}

	/**
	 * Function to handle remembering a user by automatically initializing their session based on valid cookies and them wanting to be remembered.
	 *
	 * @return      void
	 */
	function init_remembered_session()
	{
		$session = array();
		$noremember = array('registration', 'attachment', 'login', 'admin', 'cron', 'ipn', 'ajax');
		if (empty($_SESSION['sheeldata']['user']['userid']) and !empty($_COOKIE[COOKIE_PREFIX . 'password']) and !empty($_COOKIE[COOKIE_PREFIX . 'username']) and !empty($_COOKIE[COOKIE_PREFIX . 'userid']) and defined('LOCATION') and !in_array(LOCATION, $noremember)) {
			$sql = $this->sheel->db->query("
				SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE u.username = '" . $this->sheel->db->escape_string($this->sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username'])) . "'
					AND u.password = '" . $this->sheel->db->escape_string($this->sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'password'])) . "'
					AND u.user_id = '" . intval($this->sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'userid'])) . "'
					AND u.status = 'active'
				GROUP BY username
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$userinfo['zip_code'] = (mb_strtolower($userinfo['zip_code']) != '{_unknown}') ? mb_strtoupper($userinfo['zip_code']) : mb_strtolower($userinfo['zip_code']);
				// default shipping & billing profile
				$userinfo['shipprofileid'] = $this->sheel->shipping->fetch_default_ship_profileid($userinfo['user_id']);
				$userinfo['billprofileid'] = $this->sheel->shipping->fetch_default_bill_profileid($userinfo['user_id']);
				$session['sheeldata'] = $this->build_user_session($userinfo, true);
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET lastseen = '" . DATETIME24H . "'
					WHERE user_id = '" . $userinfo['user_id'] . "'
				", 0, null, __FILE__, __LINE__);
				set_cookie('radiuszip', o($this->sheel->format_zipcode($userinfo['zip_code'])));

			}
		}
		if (!empty($session['sheeldata']['user']) and is_array($session['sheeldata']['user'])) {
			foreach ($session as $key => $value) {
				$_SESSION["$key"] = $value;
			}
		}
	}
	/**
	 * Function to handle a user language or style switch within the marketplace.  Additionally, will update their account within the db if the user is active and logged in.  This is called from global.php.
	 * Additionally, this function is responsible for setting the user's initial languageid and styleid for the active session.
	 *
	 * @return      void
	 */
	function handle_language_style_changes()
	{
		if (isset($this->sheel->GPC['language']) and !empty($this->sheel->GPC['language'])) {
			$this->sheel->config['langcode'] = urldecode(mb_strtolower(trim($this->sheel->GPC['language'])));
			$langdata = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, languagecode, languageiso
				FROM " . DB_PREFIX . "language
				WHERE languagecode = '" . $this->sheel->db->escape_string($this->sheel->config['langcode']) . "'
					AND canselect = '1'
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($langdata) > 0) {
				$langinfo = $this->sheel->db->fetch_array($langdata, DB_ASSOC);
				if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
					$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET languageid = '" . $langinfo['languageid'] . "'
						WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				$_SESSION['sheeldata']['user']['languageid'] = intval($langinfo['languageid']);
				$_SESSION['sheeldata']['user']['languagecode'] = $langinfo['languagecode'];
				$_SESSION['sheeldata']['user']['languageiso'] = strtoupper($langinfo['languageiso']);
				$_SESSION['sheeldata']['user']['slng'] = mb_substr($_SESSION['sheeldata']['user']['languagecode'], 0, 3);
				unset($langinfo);
			}
		}
		if (isset($this->sheel->GPC['styleid']) and $this->sheel->GPC['styleid'] > 0 and ((defined('LOCATION') and LOCATION != 'admin') or (!defined('LOCATION')))) {
			$styledata = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid
				FROM " . DB_PREFIX . "styles
				WHERE styleid = '" . intval($this->sheel->GPC['styleid']) . "'
					AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($styledata) > 0) {
				if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
					$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "users
						SET styleid = '" . intval($this->sheel->GPC['styleid']) . "'
						WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				$_SESSION['sheeldata']['user']['styleid'] = intval($this->sheel->GPC['styleid']);
			}
		}
		
		if (empty($_SESSION['sheeldata']['user']['languageid']) or empty($_SESSION['sheeldata']['user']['slng'])) {
			$_SESSION['sheeldata']['user']['languageid'] = $this->sheel->config['globalserverlanguage_defaultlanguage'];
			$_SESSION['sheeldata']['user']['languagecode'] = $this->sheel->language->print_language_code($this->sheel->config['globalserverlanguage_defaultlanguage']);
			$_SESSION['sheeldata']['user']['languageiso'] = strtoupper($this->sheel->language->print_language_iso($this->sheel->config['globalserverlanguage_defaultlanguage']));
			$_SESSION['sheeldata']['user']['slng'] = $this->sheel->language->print_short_language_code();
		}
		if (empty($_SESSION['sheeldata']['user']['styleid'])) {
			$_SESSION['sheeldata']['user']['styleid'] = $this->sheel->config['defaultstyle'];
		}
		if (empty($_SESSION['sheeldata']['user']['currencyid'])) {
			$_SESSION['sheeldata']['user']['currencyid'] = $this->sheel->config['globalserverlocale_defaultcurrency'];
		}
		if (empty($_SESSION['sheeldata']['user']['csrf'])) {
			$_SESSION['sheeldata']['user']['csrf'] = md5(uniqid(mt_rand(), true));
		}
	}

	/**
	 * Function to build a valid user session after successful sign-in.  This function was created because we've implemented the new admin user switcher
	 * and it's pointless to handle 2 large pieces of code for session building- so this was created.
	 *
	 * @param       array          $userinfo array of user from the database
	 * @param       boolean        only return array (default false, builds $_SESSION['sheeldata'])
	 * @param       boolean        only return valid csrf token for RPC sessions
	 *
	 */
	function build_user_session($userinfo = array(), $returnonlyarray = false, $returnonlycsrf = false, $forcebuildsession = false, $rememberuser = false, $ismobile = false, $devicetoken = '0')
	{
		// #### empty inline cookie ############################################
		set_cookie('inlineproduct', '', false);

		// #### build user session #############################################
		$csrf = md5(uniqid(mt_rand(), true));
		$session = array(
			'user' => array(

				'ismobile' => $ismobile ? $rememberuser ? '2' : '1' : '0',
				'devicetoken' => $ismobile ? $devicetoken : '0',
				'isadmin' => $userinfo['isadmin'],
				'status' => $userinfo['status'],
				'userid' => $userinfo['user_id'],
				'companyid' => $userinfo['companyid'],
				'username' => $userinfo['username'],
				'usernameslug' => $userinfo['usernameslug'],
				'usernamehash' => $userinfo['usernamehash'],
				'password' => $userinfo['password'],
				'salt' => $userinfo['salt'],
				'email' => o($userinfo['email']),
				'phone' => o($userinfo['phone']),
				'firstname' => o($userinfo['first_name']),
				'lastname' => o($userinfo['last_name']),
				'fullname' => o($userinfo['first_name'] . ' ' . $userinfo['last_name']),
				'address' => ucwords(o($userinfo['address'])),
				'address2' => ucwords(o($userinfo['address2'])),
				'fulladdress' => ucwords(stripslashes($userinfo['address'])) . ' ' . ucwords(o($userinfo['address2'])),
				'city' => ucwords(o($userinfo['city'])),
				'state' => ucwords(o($userinfo['state'])),
				'postalzip' => o(mb_strtoupper(trim($userinfo['zip_code']))),
				'countryid' => intval($userinfo['country']),
				'countryids' => $this->sheel->user->fetch_shipping_profile_countries_array($userinfo['user_id'], true, $userinfo['country']),
				'country' => o($this->sheel->common_location->print_country_name($userinfo['country'])),
				'countries' => $this->sheel->user->print_shipping_profile_countries($userinfo['user_id']),
				'countryshort' => $this->sheel->common_location->print_country_name($userinfo['country'], mb_substr($userinfo['languagecode'], 0, 3), true),
				'shipprofileid' => $userinfo['shipprofileid'],
				'billprofileid' => $userinfo['billprofileid'],
				'lastseen' => $userinfo['lastseen'],
				'ipaddress' => $userinfo['ipaddress'],
				'iprestrict' => isset($userinfo['iprestrict']) ? $userinfo['iprestrict'] : 0,
				'auctiondelists' => isset($userinfo['auctiondelists']) ? intval($userinfo['auctiondelists']) : 0,
				'bidretracts' => isset($userinfo['bidretracts']) ? intval($userinfo['bidretracts']) : 0,
				'ridcode' => isset($userinfo['rid']) ? $userinfo['rid'] : '',
				'dob' => o($userinfo['dob']),
				'browseragent' => o(USERAGENT),
				'browserhistory' => intval($userinfo['browserhistory']),
				'failedlogins' => isset($userinfo['failedlogins']) ? intval($userinfo['failedlogins']) : 0,
				'productawards' => isset($userinfo['productawards']) ? intval($userinfo['productawards']) : 0,
				'rewardpoints' => isset($userinfo['rewardpoints']) ? intval($userinfo['rewardpoints']) : 0,
				'productsold' => isset($userinfo['productsold']) ? intval($userinfo['productsold']) : 0,
				'rating' => isset($userinfo['rating']) ? $userinfo['rating'] : 0,
				'languageid' => intval($userinfo['languageid']),
				'languagecode' => $userinfo['languagecode'],
				'languageiso' => strtoupper($userinfo['languageiso']),
				'slng' => mb_substr($userinfo['languagecode'], 0, 3),
				'styleid' => intval($userinfo['styleid']),
				'timezone' => isset($userinfo['timezone']) ? o($userinfo['timezone']) : 'America/New_York',
				'distance' => $userinfo['project_distance'],
				'emailnotify' => intval($userinfo['emailnotify']),
				'companyname' => o(stripslashes($userinfo['companyname'])),
				'roleid' => intval($userinfo['roleid']),
				'subscriptionid' => intval($userinfo['subscriptionid']),
				'cost' => isset($userinfo['cost']) ? $userinfo['cost'] : '0.00',
				'active' => $userinfo['active'],
				'currencyid' => intval($userinfo['currencyid']),
				'currencyname' => o(stripslashes($userinfo['currency_name'])),
				'currencysymbol' => (isset($userinfo['currencyid']) and !empty($userinfo['currencyid'])) ? $this->sheel->currency->currencies[$userinfo['currencyid']]['symbol_left'] : '$',
				'currencyabbrev' => o(mb_strtoupper($userinfo['currency_abbrev'])),
				'searchoptions' => isset($userinfo['searchoptions']) ? $userinfo['searchoptions'] : '',
				'storeid' => isset($userinfo['storeid']) ? $userinfo['storeid'] : '',
				'storeseourl' => isset($userinfo['seourl']) ? $userinfo['seourl'] : '',
				// <-- json_encoded()
				'token' => TOKEN,
				'siteid' => SITE_ID,
				'csrf' => $csrf
			)
		);

		if ($returnonlyarray) {
			if ($forcebuildsession) {
				$_SESSION['sheeldata'] = $session;
			}
			return $session;
		} else if ($returnonlycsrf) {
			if ($forcebuildsession) {
				$_SESSION['sheeldata'] = $session;
			}
			return $csrf; // works with init_remembered_session()
		} else {
			$_SESSION['sheeldata'] = $session;
		}
	}
	function set_primary_address($res = array(), $update = false, $forceupdate = false)
	{
		$res['countryid'] = $this->sheel->common_location->fetch_country_id($res['country'], $_SESSION['sheeldata']['user']['slng']);
		$_SESSION['sheeldata']['user']['postalzip'] = $res['zipcode'];
		$_SESSION['sheeldata']['user']['state'] = $res['state'];
		$_SESSION['sheeldata']['user']['city'] = $res['city'];
		$_SESSION['sheeldata']['user']['country'] = $res['country'];
		$_SESSION['sheeldata']['user']['countryshort'] = $this->sheel->common_location->print_country_name(0, $_SESSION['sheeldata']['user']['slng'], true, $res['country']); // CA
		$_SESSION['sheeldata']['user']['countryid'] = $res['countryid'];
		$_SESSION['sheeldata']['user']['countryids'] = $this->sheel->shipping->fetch_shipping_profile_countries_array($_SESSION['sheeldata']['user']['userid'], true, $res['countryid']);
		$_SESSION['sheeldata']['user']['countries'] = $this->sheel->shipping->print_shipping_profile_countries($_SESSION['sheeldata']['user']['userid']);
		$_SESSION['sheeldata']['user']['phone'] = $res['phone'];
		$_SESSION['sheeldata']['user']['address'] = $res['address'];
		$_SESSION['sheeldata']['user']['address2'] = $res['address2'];
		$_SESSION['sheeldata']['user']['fulladdress'] = $res['address'] . ' ' . $res['address2'];
		if ($update) {
			if ($forceupdate) {
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET address = '" . $this->sheel->db->escape_string($res['address']) . "',
					address2 = '" . $this->sheel->db->escape_string($res['address2']) . "',
					city = '" . $this->sheel->db->escape_string($res['city']) . "',
					state = '" . $this->sheel->db->escape_string($res['state']) . "',
					zip_code = '" . $this->sheel->db->escape_string($res['zipcode']) . "',
					phone = '" . $this->sheel->db->escape_string($res['phone']) . "',
					country = '" . intval($res['countryid']) . "'
					WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					LIMIT 1
				");
			} else {
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET address = '" . $this->sheel->db->escape_string($res['address']) . "',
					address2 = '" . $this->sheel->db->escape_string($res['address2']) . "',
					city = '" . $this->sheel->db->escape_string($res['city']) . "',
					state = '" . $this->sheel->db->escape_string($res['state']) . "',
					zip_code = '" . $this->sheel->db->escape_string($res['zipcode']) . "',
					phone = '" . $this->sheel->db->escape_string($res['phone']) . "',
					country = '" . intval($res['countryid']) . "'
					WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						AND (address = '' OR address = '{_unknown}' OR
						city = '' OR city = '{_unknown}' OR
						state = '' OR state = '{_unknown}' OR
						zip_code = '' OR zip_code = '{_unknown}')
					LIMIT 1
				");
			}
		}
		return true;
	}
	function seconds_until_expiry($sesskey = '')
	{
		$sql = $this->sheel->db->query("
			SELECT expiry, userid, isuser, isrobot, lastclick
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $this->sheel->db->escape_string($sesskey) . "'
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				return array(
					'secondsleft' => $this->sheel->common->sec2text($res['expiry'] - TIMESTAMPNOW, false),
					'lastclick' => $res['lastclick'],
					'userid' => $res['userid'],
					'isuser' => $res['isuser'],
					'isrobot' => $res['isrobot']
				);
			}
		}
		return 0;
	}

	function generateToken() {
		$characters = '0123456789abcdef';
		$charactersLength = strlen($characters);
		$token = '';
		for ($i = 0; $i < 32; ++$i) {
			$token .= $characters[rand(0, $charactersLength - 1)];
		}
		$_SESSION['token'] = $token;
	}

	/**
	 * Ensure session data is written out before classes are destroyed
	 *
	 * @return      void
	 */
	function __destruct()
	{
		@session_write_close();
	}
}
?>