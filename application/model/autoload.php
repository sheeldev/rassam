<?php
/**
* @package      sheel\General
* @version		1.0.0.0
* @author       sheel
*/


/**
 * Function to create a cookie entry
 *
 * @param	string	        cookie name
 * @param	mixed	        cookie value
 * @param	boolean	        is permanent for 1 year? (default true)
 * @param	boolean	        enable secure cookies over SSL
 * @param	boolean	        enable httponly cookies in supported browsers? (default true)
 * @param   integer         (optional) force cookie to expiry in x days (default 365)
 */
function set_cookie($name = '', $value = '', $permanent = true, $allowsecure = true, $httponly = true, $expiredays = 365)
{
	if (empty($name) or (!empty($name) and stristr($name, 'COOKIE_PREFIX'))) {
		return false;
	}
	$expire = (($permanent) ? TIMESTAMPNOW + 60 * 60 * 24 * $expiredays : 0);
	if ($expire <= 0 and $expiredays > 0) {
		$expire = TIMESTAMPNOW + 60 * 60 * 24 * $expiredays;
	}
	$secure = ((PROTOCOL_REQUEST == 'https' and $allowsecure) ? true : false);
	do_set_cookie(COOKIE_PREFIX . $name, $value, $expire, '/', '', $secure, $httponly);
}
/**
 * Callback function to set the cookie called from set_cookie()
 *
 * @param	string	        cookie name
 * @param	string	        cookie value
 * @param	int				cookie expire time
 * @param	string	        cookie path
 * @param	string	        cookie domain
 * @param	boolean	        cookie secure via SSL
 * @param	boolean	        cookie is http only
 *
 * @return	boolean	        Returns true on success
 */
function do_set_cookie($name, $value, $expires, $path = '', $domain = '', $secure = true, $httponly = true)
{
	if ($value and $httponly) {
		foreach (array("\014", "\013", ",", ";", " ", "\t", "\r", "\n") as $badcharacter) {
			if (mb_strpos($name, $badcharacter) !== false or mb_strpos($value, $badcharacter) !== false) {
				return false;
			}
		}
		$setcookie = "Set-Cookie: $name=" . urlencode($value);
		$setcookie .= ($expires > 0 ? '; expires=' . date('D, d-M-Y H:i:s', $expires) . ' GMT' : '');
		$setcookie .= ($path ? "; path=$path" : '');
		$setcookie .= ($domain ? "; domain=$domain" : '');
		$setcookie .= ($secure ? '; secure' : '');
		$setcookie .= ($httponly ? '; HttpOnly' : '');
		header($setcookie, false);
		return true;
	} else {
		return setcookie($name, $value, $expires, $path, $domain, $secure);
	}
}
/**
 * Function to hard refresh a page and to show a please wait while we direct you to the specified location message
 *
 * @param        string        url to send user
 * @param        string        custom argument (unused)
 *
 * @return	void
 */
function refresh($url = '', $custom = '')
{
	if (!empty($custom)) {
		$url = $custom;
	}
	header("Location: " . urldecode($url));
	exit();
}
/**
 * Function to handle the construction of the registry object datastore
 *
 * @param       string       class filename to load
 * @param       string       class argument set 1 (optional)
 * @param       string       class argument set 2 (optional)
 * @param       string       class argument set 3 (optional)
 * @param       string       class argument set 4 (optional)
 * @param       string       class argument set 5 (optional)
 *
 * @return      object       Returns our registry object
 */
function iL($classname, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
{
	global $sheel;
	$caller = get_calling_location();
	if (empty($classname)) {
		return false;
	}
	$function = new stdClass();
	$function->timer = new timer;
	$function->timer->start();
	$arr = explode('_', $classname);
	if (is_array($arr) and count($arr) > 1) {
		$objmain = iL($arr[0]);
		if ($objmain) {
			$sheel = ((!isset($sheel)) ? new stdClass() : $sheel);
			$sheel->{$arr[0]} = $objmain;
		}
	}
	if (!isset($sheel->$classname)) {
		if (class_exists($classname)) {
			$obj = new $classname($param1, $param2, $param3, $param4, $param5);
		} else if (file_exists(DIR_CLASSES . 'class.' . $classname . '.inc.phpx')) {
			include_once(DIR_CLASSES . 'class.' . $classname . '.inc.phpx');
			$obj = new $classname($param1, $param2, $param3, $param4, $param5);
		} else if (file_exists(DIR_CLASSES . 'class.' . $classname . '.inc.php')) {
			include_once(DIR_CLASSES . 'class.' . $classname . '.inc.php');
			$obj = new $classname($param1, $param2, $param3, $param4, $param5);
		} else {
			$obj = false;
		}
	} else {
		$obj = $sheel->$classname;
	}
	$function->timer->stop();
	return $obj;
}
/**
 * Function to convert html tags for safe output
 *
 * @return      string       HTML formatted string
 */
function o($text = '', $entities = false, $stripemoji = false)
{
	$text = htmlspecialchars_uni($text, $entities);
	$text = (($stripemoji) ? remoji($text) : $text);
	return $text;
}
/**
 * Function to emulate a unicode version of htmlspecialchars()
 *
 * @param	string	     text to be converted into unicode
 * @param   bool         (optional) force numeric entities only? (default true)
 *
 * @return	string
 */
function htmlspecialchars_uni($text, $entities = true)
{
	return str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), preg_replace('/&(?!' . ($entities ? '#[0-9]+' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $text));
}
/**
 * Function to emulate an expression where the values for true and false are predefined
 *
 * @param	string	     expression
 * @param   string       value to return if expression is true
 * @param	string       value to return if expression is false
 *
 * @return	string
 */
function iif($exp, $rettrue, $retfalse = '')
{
	return ($exp ? $rettrue : $retfalse);
}
/**
 * Fetches a proxy IP address of visitor, will use regular ip if proxy cannot be detected.
 *
 * @return	string
 */
function fetch_proxy_ip_address()
{
	$ip = ((isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '');
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $match)) {
		foreach ($match[0] as $ipaddress) {
			if (!preg_match("#^(10|172\.16|192\.168)\.#", $ipaddress)) {
				$ip = $ipaddress;
				break;
			}
		}
	} else if (isset($_SERVER['HTTP_FROM'])) {
		$ip = $_SERVER['HTTP_FROM'];
	}
	return $ip;
}
/**
 * Encodes HTML safely for UTF-8. Use instead of htmlentities.
 *
 * @param        string          $var
 * @return       string          Returns a valid UTF-8 string
 */
function sheel_htmlentities($text = '')
{
	return htmlentities($text, ENT_QUOTES, 'UTF-8');
}
function get_calling_function()
{
	$caller = debug_backtrace();
	$caller = ((isset($caller[2])) ? $caller[2] : array('function' => '', 'class' => ''));
	$r = $caller['function'] . '()';
	if (isset($caller['class'])) {
		$r .= ' in ' . $caller['class'];
	}
	if (isset($caller['object']) and isset($caller['class']) and get_class($caller['object']) != $caller['class']) {
		$r .= ' (' . get_class($caller['object']) . ')';
	}
	if (isset($caller['file']) and isset($caller['line'])) {
		$r .= " $caller[file] ($caller[line])";
	}
	return $r;
}
function get_calling_location()
{
	$trace = debug_backtrace();
	$caller = ((isset($trace[1])) ? $trace[1] : array('function' => '', 'class' => ''));
	$caller = "$caller[file] ($caller[line])";
	return $caller;
}
/**
 * Function to build an array of information used in sheel for debugging purposes
 *
 * @param       string       message
 * @param       string       debug type (FUNCTION, CLASS, NOTICE, OTHER)
 * @param       integer      timer
 * @param       string       caller function or script
 *
 */
function debug($text = '', $type = 'OTHER', $timer = 0, $caller = '')
{
	if (DEBUG_FOOTER) {
		$functions[] = $text;
		$GLOBALS['DEBUG']["$type"][] = array(
			'caller' => $caller,
			'text' => $text,
			'timer' => $timer,
			'memoryusage' => memory_get_usage(true),
			'memorypeak' => memory_get_peak_usage(true)
		);
	}
}
/**
 * Function to compare two dates and subtract date 1 from date 2
 *
 * @param       array        timestamp 1
 * @param       array        timestamp 2
 *
 * @return      integer     Returns subtracted timestamp
 */
function compare_func($a = array(), $b = array())
{
	$t1 = strtotime($a["datetime"]);
	$t2 = strtotime($b["datetime"]);
	return ($t2 - $t1);
}
function translate($string, array $inserts = null)
{
	$translated = $string;
	$translated = empty($translated) ? $string : $translated;
	if (is_array($inserts) and !empty($inserts)) {
		$translated = vsprintf($translated, $inserts);
	}
	return $translated;
}
function __($string, array $inserts = null)
{
	return translate($string, $inserts);
}
function vdate($format = '', $ts = 0)
{ // multi-language equivalent for date()
	$ts = (($ts <= 0) ? time() : $ts);
	$replace = array(
		// date() -> strfttime()
		'M' => '%b',
		'Y' => '%Y',
		'm' => '%m',
		'd' => '%d',
		'D' => '%a',
		'H' => '%H',
		'h' => '%I',
		'i' => '%M',
		'g' => '%l',
		'a' => '%P',
		'A' => '%p',
		's' => '%S',
		'O' => '%z',
		'T' => '%Z',
		'F' => '%B',
		'l' => '%A',
		'N' => '%u',
		'w' => '%w',
		'z' => '%j',
		'W' => '%V',
		'o' => '%G',
		'y' => '%y',
		'U' => '%s',
		'j' => (((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) ? '%#d' : '%e')
	);
	$format = strtr((string) $format, $replace);
	return strftime("$format", $ts);
}
function remoji($string = '')
{ // https://medium.com/coding-cheatsheet/remove-emoji-characters-in-php-236034946f51
	$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u'; // emoticons
	$clear_string = preg_replace($regex_emoticons, '', $string);
	$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u'; // symbols and pictographs
	$clear_string = preg_replace($regex_symbols, '', $clear_string);
	$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u'; // transport / map symbols
	$clear_string = preg_replace($regex_transport, '', $clear_string);
	$regex_misc = '/[\x{2600}-\x{26FF}]/u'; // miscellaneous symbols
	$clear_string = preg_replace($regex_misc, '', $clear_string);
	$regex_dingbats = '/[\x{2700}-\x{27BF}]/u'; // dingbats
	$clear_string = preg_replace($regex_dingbats, '', $clear_string);
	return $clear_string;
}
function containsword($str, $word)
{
	return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
}
?>