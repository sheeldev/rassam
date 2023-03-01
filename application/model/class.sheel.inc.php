<?php

/**
 * sheel class to perform the majority of the main common sheel functions.
 *
 * @package sheel\sheel
 * @version 6.0.0.622
 * @author sheel
 */
class sheel
{

    protected $sheel;

    /**
     * $_GET, $_POST and $_COOKIE array
     *
     * @var $GPC
     */
    var $GPC = array();
    var $ilpage = array();
    var $show = array();
    var $config = array();
    var $curlerrors = array();
    var $ssoproviders = array(
        'twitter',
        'linkedin',
        'facebook',
        'googleplus',
        'ldap'
    );

    var $keywords = '';

    // keywords entered by a user
    /**
     * Will store all plugins currently installed into an array for future processing
     *
     * @var $pluginsxml
     */
    var $plugins;

    /**
     * Constructor
     */

    function __construct($sheel)
    {
        $this->sheel = $sheel;
        if (false) {
            $this->magicquotes = 1;
            $this->strip_slashes_array($_REQUEST);
            $this->strip_slashes_array($_POST);
            $this->strip_slashes_array($_GET);
            $this->strip_slashes_array($_COOKIE);
        }
        @ini_set('magic_quotes_runtime', 0);
        $arrays = array_merge($_GET, $_POST);
        $this->parse_incoming($arrays);
        if (@ini_get('register_globals') or !@ini_get('gpc_order')) {
            $this->unset_globals($_POST);
            $this->unset_globals($_GET);
            $this->unset_globals($_FILES);
        }
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        if (is_array($arr) and count($arr) > 1) {
            $objmain = iL($arr[0], $this);
            $this->{$arr[0]} = $objmain;
        }
        $obj = iL($name, $this);
        if (!empty($obj)) {
            @$this->$name = $obj;
            return $obj;
        }
    }
    /**
     * Function to parse any incoming input and tranform it into our reusable $sheel->GPC array used in the software.
     *
     * @param
     *            array array
     *            
     */
    function parse_incoming($array)
    {
        if (!is_array($array)) {
            return;
        }
        foreach ($array as $key => $val) {
            $this->GPC["$key"] = $val;
        }
    }

    /**
     * Function wrapper for the xx_escape_string function for escaping valid sql input
     *
     * @param
     *            string string to escape
     *            
     * @return string Returns xx_escape_string value
     */
    function escape_string($text = '')
    {
        return $this->db->escape_string($text);
    }

    /**
     * Function to strip any slashes within a regular or recursive array
     *
     * @param
     *            array array
     *            
     */
    function strip_slashes_array(&$array)
    {
        foreach ($array as $key => $val) {
            if (is_array($array[$key])) {
                $this->strip_slashes_array($array[$key]);
            } else {
                $array[$key] = stripslashes($array[$key]);
            }
        }
    }

    /**
     * Function to unset $_GLOBAL's from being set by users via URL manipulation
     *
     * @param
     *            array array value to clean
     *            
     */
    function unset_globals($array)
    {
        if (!is_array($array)) {
            return;
        }
        foreach (array_keys($array) as $key) {
            unset($GLOBALS["$key"]);
        }
    }

    /**
     * Function to clean $_GLOBAL, $_POST and $_COOKIE input
     *
     * @param
     *            string g, p or c values
     * @param
     *            array array or value to clean
     * @param
     *            string variable clean type selector (ie: TYPE_INT, TYPE_NUM, etc)
     *            
     */
    function clean_gpc($gpc, $variable, $type = '')
    {
        $boolmethods = array(
            '1',
            'yes',
            'y',
            'true'
        );
        if (empty($type)) { // handling input in main scripts (abuse.php, etc)
            foreach ($variable as $fieldname => $type) {
                switch ($type) {
                    case 'TYPE_INT': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = intval($_GET["$fieldname"]);
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = intval($_POST["$fieldname"]);
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = intval($_COOKIE["$fieldname"]);
                            }
                            break;
                        }
                    case 'TYPE_NUM': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = strval($_GET["$fieldname"]) + 0;
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = strval($_POST["$fieldname"]) + 0;
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]) + 0;
                            }
                            break;
                        }
                    case 'TYPE_STR': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = trim(strval($_GET["$fieldname"]));
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = trim(strval($_POST["$fieldname"]));
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = trim(strval($_COOKIE["$fieldname"]));
                            }
                            break;
                        }
                    case 'TYPE_NOTRIM': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = strval($_GET["$fieldname"]);
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = strval($_POST["$fieldname"]);
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]);
                            }
                            break;
                        }
                    case 'TYPE_NOHTML': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_GET["$fieldname"])));
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_POST["$fieldname"])));
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_COOKIE["$fieldname"])));
                            }
                            break;
                        }
                    case 'TYPE_BOOL': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = in_array(mb_strtolower($_GET["$fieldname"]), $boolmethods) ? 1 : 0;
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = in_array(mb_strtolower($_POST["$fieldname"]), $boolmethods) ? 1 : 0;
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = in_array(mb_strtolower($_COOKIE["$fieldname"]), $boolmethods) ? 1 : 0;
                            }
                            break;
                        }
                    case 'TYPE_ARRAY': {
                            if ($gpc == 'g') {
                                $this->GPC["$fieldname"] = (is_array($_GET["$fieldname"])) ? $fieldname : array();
                            } else if ($gpc == 'p') {
                                $this->GPC["$fieldname"] = (is_array($_POST["$fieldname"])) ? $fieldname : array();
                            } else if ($gpc == 'c') {
                                $this->GPC["$fieldname"] = (is_array($_COOKIE["$fieldname"])) ? $fieldname : array();
                            }
                            break;
                        }
                }
            }
        } else { // handling input in datamanger scripts (class.datamanager_xxx.inc.php, etc)
            switch ($type) {
                case 'TYPE_INT': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = intval($_GET["$variable"]);
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = intval($_POST["$variable"]);
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = intval($_COOKIE["$variable"]);
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = intval($variable);
                        }
                        break;
                    }
                case 'TYPE_NUM': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = strval($_GET["$variable"]) + 0;
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = strval($_POST["$variable"]) + 0;
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = strval($_COOKIE["$variable"]) + 0;
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = strval($variable) + 0;
                        }
                        break;
                    }
                case 'TYPE_STR': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = trim(strval($_GET["$variable"]));
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = trim(strval($_POST["$variable"]));
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = trim(strval($_COOKIE["$variable"]));
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = trim(strval($variable));
                        }
                        break;
                    }
                case 'TYPE_NOTRIM': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = strval($_GET["$variable"]);
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = strval($_POST["$variable"]);
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = strval($_COOKIE["$variable"]);
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = strval($variable);
                        }
                        break;
                    }
                case 'TYPE_NOHTML': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_GET["$variable"])));
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_POST["$variable"])));
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_COOKIE["$variable"])));
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($variable)));
                        }
                        break;
                    }
                case 'TYPE_BOOL': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = in_array(mb_strtolower($_GET["$variable"]), $boolmethods) ? 1 : 0;
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = in_array(mb_strtolower($_POST["$variable"]), $boolmethods) ? 1 : 0;
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = in_array(mb_strtolower($_COOKIE["$variable"]), $boolmethods) ? 1 : 0;
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = in_array(mb_strtolower($variable), $boolmethods) ? 1 : 0;
                        }
                        break;
                    }
                case 'TYPE_ARRAY': {
                        if ($gpc == 'g') {
                            $this->GPC["$variable"] = (is_array($_GET["$variable"])) ? $variable : array();
                        } else if ($gpc == 'p') {
                            $this->GPC["$variable"] = (is_array($_POST["$variable"])) ? $variable : array();
                        } else if ($gpc == 'c') {
                            $this->GPC["$variable"] = (is_array($_COOKIE["$variable"])) ? $variable : array();
                        } else if ($gpc == 's') {
                            $this->GPC["$variable"] = (is_array($variable)) ? $variable : array();
                        }
                        break;
                    }
            }
            if ($gpc == 's') {
                return $this->GPC["$variable"];
            }
        }
    }

    /**
     * Function to connect to the sheel.com web site to fetch the latest version.
     *
     * @param
     *            string version checkup url (ie: http://www.sheel.com/apps/icommunity/versioncheck)
     *            
     * @return string Returns formatted HTML or PHP code to be parsed inline as called.
     */
    function latest_version($versioncheckurl = '')
    {
        $version = '-';
        if (defined('LOCATION') and LOCATION == 'admin' and !empty($versioncheckurl) and defined('VERSIONCHECK') and VERSIONCHECK) { // may cause slight delay for 1 or 2 seconds to grab latest version
            $fp = @fopen($versioncheckurl, 'r');
            $version = trim(@fread($fp, 16));
            @fclose($fp);
            if (mb_strlen($version) > 10) {
                $version = '-';
            }
        }
        return $version;
    }

    /**
     * Function to fetch the language locale settings to setup our environment
     *
     * @param
     *            integer language id
     * @param
     *            string (optional) short form language identifier (if specified, will override language id argument). i.e: eng
     *            
     * @return integer Returns an array like $res['locale] which would equal 'en_US' or 'en_US.utf8', 'pl_PL.utf8', etc.
     */
    function fetch_language_locale($languageid = 1, $slng = '')
    {
        $res['locale'] = 'en_US';
        if (!empty($slng)) {
            $sql = $this->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locale
				FROM " . DB_PREFIX . "language
				WHERE CONCAT(SUBSTRING(languagecode, 1, 3)) = '" . $this->db->escape_string($slng) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        } else {
            $sql = $this->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locale
				FROM " . DB_PREFIX . "language
				WHERE languageid = '" . intval($languageid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        }
        if ($this->db->num_rows($sql) > 0) {
            $res = $this->db->fetch_array($sql, DB_ASSOC);
        }
        return $res;
    }

    /**
     * Function to fetch the user count
     *
     * @return integer Returns number (i.e.: 3201)
     */
    function usercount()
    {
        $count = 0;
        $sql = $this->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "users
		");
        $res = $this->db->fetch_array($sql, DB_ASSOC);
        if ($res['count'] > 0) {
            $count = $res['count'];
        }
        return $count;
    }

    /**
     * Function to fetch the order count
     *
     * @return integer Returns number (i.e.: 3201)
     */
    function ordercount()
    {
        $count = 0;
        $sql = $this->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "buynow_orders
			WHERE parentid = '0'
		");
        $res = $this->db->fetch_array($sql, DB_ASSOC);
        if ($res['count'] > 0) {
            $count = $res['count'];
        }
        return $count;
    }

    /**
     * Function to fetch the request count
     *
     * @return integer Returns number (i.e.: 3201)
     */
    function companiescount()
    {
        $count = 0;
        $sql = $this->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "companies where status='active' and requestdeletion='0'");
        $res = $this->db->fetch_array($sql, DB_ASSOC);
        if ($res['count'] > 0) {
            $count = $res['count'];
        }
        return $count;
    }



    /**
     * Function for determining the entire size used within the database for sheel operations.
     *
     * @return string size in bytes
     */
    function fetch_database_size()
    {
        $total = 0;
        $result = $this->db->query("SHOW TABLE STATUS", 0, null, __FILE__, __LINE__);
        while ($row = $this->db->fetch_array($result)) {
            $total += ($row['Data_length'] + $row['Index_length']);
        }
        return $total;
    }

    /**
     * Function to generate a unique password salt string mainly used for password hashing
     *
     * @param
     *            integer length of salt to generate
     *            
     * @return string Salt string
     */
    function construct_password_salt($length = 5)
    {
        $salt = '';
        for ($i = 0; $i < $length; $i++) {
            $salt .= chr(rand(33, 126));
        }
        $salt = str_replace(",", "_", $salt);
        $salt = str_replace("'", "^", $salt);
        $salt = str_replace('"', '*', $salt);
        $salt = str_replace("\\", '+', $salt);
        $salt = str_replace("\\\\", '-', $salt);
        return $salt;
    }

    /**
     * Function to generate a human-readable password where the password length is based on a supplied argument
     *
     * @param
     *            integer password character length
     *            
     * @return string Generated human-readable password
     */
    function construct_password($len = 8)
    {
        error_reporting(0);
        $vocali = array(
            'a',
            'e',
            'i',
            'o',
            'u'
        );
        $dittonghi = array(
            'ae',
            'ai',
            'ao',
            'au',
            'ea',
            'ei',
            'eo',
            'eu',
            'ia',
            'ie',
            'io',
            'iu',
            'ua',
            'ue',
            'ui',
            'uo'
        );
        $cons = array(
            'b',
            'c',
            'd',
            'f',
            'g',
            'h',
            'k',
            'l',
            'n',
            'm',
            'p',
            'r',
            's',
            't',
            'v',
            'z'
        );
        $consdoppie = array(
            'bb',
            'cc',
            'dd',
            'ff',
            'gg',
            'll',
            'nn',
            'mm',
            'pp',
            'rr',
            'ss',
            'tt',
            'vv',
            'zz'
        );
        $consamiche = array(
            'bl',
            'br',
            'ch',
            'cl',
            'cr',
            'dl',
            'dm',
            'dr',
            'fl',
            'fr',
            'gh',
            'gl',
            'gn',
            'gr',
            'lb',
            'lp',
            'ld',
            'lf',
            'lg',
            'lm',
            'lt',
            'lv',
            'lz',
            'mb',
            'mp',
            'nd',
            'nf',
            'ng',
            'nt',
            'nv',
            'nz',
            'pl',
            'pr',
            'ps',
            'qu',
            'rb',
            'rc',
            'rd',
            'rf',
            'rg',
            'rl',
            'rm',
            'rn',
            'rp',
            'rs',
            'rt',
            'rv',
            'rz',
            'sb',
            'sc',
            'sd',
            'sf',
            'sg',
            'sl',
            'sm',
            'sn',
            'sp',
            'sr',
            'st',
            'sv',
            'tl',
            'tr',
            'vl',
            'vr'
        );
        $listavocali = array_merge($vocali, $dittonghi);
        $listacons = array_merge($cons, $consdoppie, $consamiche);
        $nrvocali = sizeof($listavocali);
        $nrconsonanti = sizeof($listacons);
        $loop = $len;
        $password = '';
        if (rand(1, 10) > 5) {
            $password = $cons[rand(1, sizeof($cons))];
            $password .= $listavocali[rand(1, $nrvocali)];
            $inizioc = true;
            $loop--;
        }
        for ($i = 0; $i < $loop; $i++) {
            $qualev = $listavocali[rand(1, $nrvocali)];
            $qualec = $listacons[rand(1, $nrconsonanti)];
            if (isset($inizioc)) {
                $password .= $qualec . $qualev;
            } else {
                $password .= $qualev . $qualec;
            }
        }
        $password = mb_substr($password, 0, $len);
        if (in_array(mb_substr($password, ($len - 2), $len), $consdoppie)) {
            $password = mb_substr($password, 0, ($len - 1)) . $listavocali[rand(1, $nrvocali)];
        }
        return $password;
    }

    /**
     * Function to verify a password and show a prompt or an error message
     *
     * @param
     *            string password
     * @param
     *            string return url
     * @param
     *            string key
     * @param
     *            string value
     * @param
     *            integer timeout value
     *            
     * @return string HTML output
     */
    function verify_password_prompt($password = '', $returnurl = '', $key = 'cardupdate', $value = '', $timeout = 120)
    {
        $badpassword = false;
        if ($_SESSION['sheeldata']['user']['password'] != iif($password, md5(md5($password) . $_SESSION['sheeldata']['user']['salt']), '') and $_SESSION['sheeldata']['user']['password'] != md5(md5($password) . $_SESSION['sheeldata']['user']['salt'])) {
            $badpassword = true;
        }
        if ($badpassword) { // check for cookie so we don't need to re-enter password
            $this->print_notice('{_account_password_incorrect}', '{_there_was_a_problem_verifying_the_password}', urldecode($returnurl), '{_retry}');
            exit();
        } else {
            $_SESSION['sheeldata']['user'][$key . '_' . $value] = TIMESTAMPNOW + $timeout;
            refresh(urldecode($returnurl));
            exit();
        }
    }

    /**
     * Function to verify an item listing password and show a prompt or an error message
     *
     * @param
     *            string password
     * @param
     *            string return url
     * @param
     *            string key
     * @param
     *            string value
     * @param
     *            integer timeout value
     *            
     * @return string HTML output
     */
    function verify_item_password_prompt($password = '', $listingpassword = '', $returnurl = '', $key = '', $value = '', $timeout = 120)
    {
        $badpassword = false;
        if ($password != $listingpassword) {
            $badpassword = true;
        }
        if ($badpassword) { // check for cookie so we don't need to re-enter password
            $this->show['slimheader'] = true;
            $this->print_notice('{_account_password_incorrect}', '{_there_was_a_problem_verifying_the_password}', urldecode($returnurl), '{_retry}');
            exit();
        } else {
            set_cookie($key . '_' . $value, TIMESTAMPNOW + $timeout);
            refresh(urldecode($returnurl));
            exit();
        }
    }
    /**
     * Function to print a viewable notice template to the web browser using the regular sheel template parsed with the header and footer
     *
     * @param
     *            string header text
     * @param
     *            string body text
     * @param
     *            string return url
     * @param
     *            string return url title
     * @param
     *            array custom message (more specific)
     *            
     * @return string Message with domain phrases blocked
     */
    function print_notice($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '')
    {
        $this->show['widescreen'] = false;
        $this->show['fluidscreen'] = false;
        $this->show['slimheader'] = true;
        $this->show['slimfooter'] = true;
        $this->show['returnname'] = false;
        $this->show['nobreadcrumb'] = true;
        $this->template->meta['areatitle'] = $header_text;
        $this->template->meta['pagetitle'] = SITE_NAME . ' - ' . $header_text;
        $this->template->meta['jsinclude'] = array(
            'header' => array(
                'functions',
                'vendor/jquery_' . JQUERYVERSION,
                'vendor/lazyload'
            ),
            'footer' => array(
                'v5',
                'autocomplete'
            )
        );
        if (is_array($custom) and !empty($custom['text']) and !empty($custom['description'])) {
            $header_text = '{_something_went_wrong_x::' . $custom['text'] . '}<sup class="fs-10">âœ�</sup>';
            $text = '<div class="pt-12 fs-11 litegray">âœ� ' . $custom['description'] . '</div>';
            $body_text .= $text;
        }
        if (!empty($return_name)) {
            $this->show['returnname'] = true;
        }


        $this->template->fetch('main', 'print_notice.html');
        $this->template->parse_hash('main', array(
            'ilpage' => $this->ilpage
        )
        );
        $this->template->pprint('main', array(
            'body_text' => $body_text,
            'header_text' => $header_text,
            'return_url' => $return_url,
            'return_name' => $return_name
        )
        );
        exit();
    }

    /**
     * Function to print a viewable notice template to the web browser using the regular sheel template parsed with the header and footer
     *
     * @param
     *            string header text
     * @param
     *            string body text
     * @param
     *            string return url
     * @param
     *            string return url title
     * @param
     *            array custom message (more specific)
     *            
     * @return string Message with domain phrases blocked
     */
    function print_notice_popup($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '')
    {
        $this->show['widescreen'] = false;
        $this->show['fluidscreen'] = false;
        $this->show['slimheader'] = true;
        $this->show['slimfooter'] = true;
        $this->show['returnname'] = false;
        $this->show['nobreadcrumb'] = true;
        $this->template->meta['areatitle'] = $header_text;
        $this->template->meta['pagetitle'] = SITE_NAME . ' - ' . $header_text;
        $this->template->meta['jsinclude'] = array(
            'header' => array(
                'functions',
                'vendor/jquery_' . JQUERYVERSION,
                'vendor/lazyload'
            ),
            'footer' => array(
                'v5',
                'autocomplete'
            )
        );
        if (is_array($custom) and !empty($custom['text']) and !empty($custom['description'])) {
            $header_text = '{_something_went_wrong_x::' . $custom['text'] . '}<sup class="fs-10">âœ�</sup>';
            $text = '<div class="pt-12 fs-11 litegray">âœ� ' . $custom['description'] . '</div>';
            $body_text .= $text;
        }
        if (!empty($return_name)) {
            $this->show['returnname'] = true;
        }


        $this->template->fetch_popup('main', 'print_notice_popup.html');
        $this->template->parse_hash('main', array(
            'ilpage' => $this->ilpage
        )
        );
        $this->template->meta['navcrumb'] = array();
        $this->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = $header_text;
        $this->template->pprint('main', array(
            'body_text' => $body_text,
            'header_text' => $header_text,
            'return_url' => $return_url,
            'return_name' => $return_name
        )
        );
        exit();
    }

    function print_notice_popup_login($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '')
    {
        $this->show['widescreen'] = false;
        $this->show['fluidscreen'] = false;
        $this->show['slimheader'] = true;
        $this->show['slimfooter'] = true;
        $this->show['returnname'] = false;
        $this->show['nobreadcrumb'] = true;
        $this->template->meta['areatitle'] = $header_text;
        $this->template->meta['pagetitle'] = SITE_NAME . ' - ' . $header_text;
        $this->template->meta['jsinclude'] = array(
            'header' => array(
                'functions',
                'vendor/jquery_' . JQUERYVERSION,
                'vendor/lazyload'
            ),
            'footer' => array(
                'v5',
                'autocomplete'
            )
        );
        if (is_array($custom) and !empty($custom['text']) and !empty($custom['description'])) {
            $header_text = '{_something_went_wrong_x::' . $custom['text'] . '}';
            $text = $custom['description'];
            $body_text .= $text;
        }
        if (!empty($return_name)) {
            $this->show['returnname'] = true;
        }


        $this->template->fetch_popup('main', 'print_notice_popup_login.html');
        $this->template->parse_hash('main', array(
            'ilpage' => $this->ilpage
        )
        );
        $this->template->meta['navcrumb'] = array();
        $this->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = $header_text;
        $this->template->pprint('main', array(
            'body_text' => $body_text,
            'header_text' => $header_text,
            'return_url' => $return_url,
            'return_name' => $return_name
        )
        );
        exit();
    }

    /**
     * Function to log an event based on a particular action engaged by a user data mined within the AdminCP > Audit Manager
     *
     * @param
     *            integer user id
     * @param
     *            string script filename
     * @param
     *            string response[success/failure] + $_post + $_get actions performed
     * @param
     *            string title of action performed (for admin growls)
     * @param
     *            string message of action performed (for admin growls)
     *            
     */
    function log_event($userid = 0, $script = '', $postget = '', $title = '', $message = '', $type = 'audit')
    {
        $this->GPC['cmd'] = ((isset($this->GPC['cmd'])) ? $this->GPC['cmd'] : '');
        $this->GPC['subcmd'] = ((isset($this->GPC['subcmd'])) ? $this->GPC['subcmd'] : '');
        $this->GPC['do'] = ((isset($this->GPC['do'])) ? $this->GPC['do'] : '');
        $this->GPC['uri'] = ((isset($_SERVER['REQUEST_URI']) and !empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '');
        $this->db->query("
			INSERT INTO " . DB_PREFIX . "audit
			(logid, user_id, script, cmd, subcmd, do, uri, otherinfo, title, message, datetime, ipaddress, type)
			VALUES
			(NULL,
			'" . intval($userid) . "',
			'" . $this->db->escape_string($script) . "',
			'" . $this->db->escape_string($this->GPC['cmd']) . "',
			'" . $this->db->escape_string($this->GPC['subcmd']) . "',
			'" . $this->db->escape_string($this->GPC['do']) . "',
			'" . $this->db->escape_string($this->GPC['uri']) . "',
			'" . $this->db->escape_string($postget) . "',
			'" . $this->db->escape_string($title) . "',
			'" . $this->db->escape_string($message) . "',
			'" . TIMESTAMPNOW . "',
			'" . $this->db->escape_string(IPADDRESS) . "',
			'" . $this->db->escape_string($type) . "')
		", 0, null, __FILE__, __LINE__);
    }

    /**
     * Function to encrypt a url
     *
     * @param
     *            array url array
     *            
     * @return string encoded url
     */
    function encrypt_url($array = array())
    {
        $encoded = $this->crypt->encrypt($array);
        return urlencode($encoded);
    }

    /**
     * Function to decrypt a url
     *
     * @param
     *            string encoded url
     *            
     * @return array decoded url array
     */
    function decrypt_url($encrypted = '')
    {
        if (empty($encrypted)) {
            $gpc = array();
            if (isset($this->GPC)) {
                foreach ($this->GPC as $key => $value) {
                    $gpc["$key"] = $value;
                }
            }
            return $gpc;
        } else {
            $gpc = $this->crypt->decrypt(urldecode($encrypted));
            return $gpc;
        }
    }

    function picture_factory($userid = 0)
    {
        $factory = array(
            'profile' => array(
                'settings' => array(
                    'watermark' => $this->config['watermark_profiles'],
                    'extensions' => $this->config['attachmentlimit_profileextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => 1,
                    'folder' => DIR_PROFILE_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'itemphoto' => array(
                'settings' => array(
                    'watermark' => $this->config['watermark_itemphoto'],
                    'extensions' => $this->config['attachmentlimit_productphotoextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => $this->config['attachmentlimit_slideshowmaxfiles'],
                    'folder' => DIR_AUCTION_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'eventphoto' => array(
                'settings' => array(
                    'watermark' => 0,
                    'extensions' => $this->config['attachmentlimit_productphotoextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => 1,
                    'folder' => DIR_AUCTION_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'stores' => array(
                'settings' => array(
                    'watermark' => 0,
                    'extensions' => $this->config['attachmentlimit_productphotoextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => 1,
                    'folder' => DIR_STORE_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'storesbackground' => array(
                'settings' => array(
                    'watermark' => 0,
                    'extensions' => $this->config['attachmentlimit_productphotoextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => 1,
                    'folder' => DIR_STORE_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'pmb' => array(
                'settings' => array(
                    'watermark' => 0,
                    'extensions' => $this->config['attachmentlimit_defaultextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => $this->permissions->check_access($userid, 'maxpmbattachments'),
                    'folder' => DIR_PMB_ATTACHMENTS
                ),
                'dimensions' => array_map('trim', explode(',', $this->config['attachmentlimit_productphotodimensions']))
            ),
            'digital' => array(
                'settings' => array(
                    'watermark' => 0,
                    'extensions' => $this->config['attachmentlimit_digitalfileextensions'],
                    'maxuploadfilesize' => $this->permissions->check_access($userid, 'uploadlimit'),
                    'maxfiles' => 1,
                    'folder' => DIR_AUCTION_ATTACHMENTS
                ),
                'dimensions' => array()
            )
        );

        return $factory;
    }

    /**
     * Function to fetch any field from the user table based on a number of access methods such as by user id, username or email address
     *
     * @param
     *            string field to fetch from the user table
     * @param
     *            integer user id (optional; default)
     * @param
     *            string username (optional)
     * @param
     *            string email (optional)
     * @param
     *            boolean show unknown phrase (default true)
     *            
     * @return string Returns field value from the user table
     */
    function fetch_user($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
    {
        $paymentfields = array();
        foreach ($this->paymentgateway->ipn_gateway_accepted as $gatewayipn) {
            $paymentfields[] = $gatewayipn . '_profile';
        }
        $validfields = array(
            'user_id',
            'companyid',
            'ipaddress',
            'iprestrict',
            'username',
            'usernamehash',
            'usernameslug',
            'password',
            'salt',
            'email',
            'first_name',
            'last_name',
            'address',
            'address2',
            'city',
            'state',
            'zip_code',
            'phone',
            'country',
            'date_added',
            'subcategories',
            'status',
            'failedlogins',
            'productawards',
            'rewardpoints',
            'productsold',
            'rating',
            'feedback',
            'score',
            'bidstoday',
            'bidsthismonth',
            'auctiondelists',
            'bidretracts',
            'lastseen',
            'warnings',
            'warning_level',
            'warning_bans',
            'dob',
            'rid',
            'account_number',
            'available_balance',
            'total_balance',
            'income_reported',
            'income_spent',
            'styleid',
            'project_distance',
            'currency_calculation',
            'languageid',
            'currencyid',
            'timezone',
            'notifyproducts',
            'apikey',
            'displayprofile',
            'emailnotify',
            'displayfinancials',
            'vatnumber',
            'regnumber',
            'dnbnumber',
            'companyname',
            'usecompanyname',
            'companyabout',
            'companydescription',
            'infractions',
            'profilevideourl',
            'buyerprofileintro',
            'profileintro',
            'autopayment',
            'gender',
            'password_lastchanged',
            'username_history',
            'posthtml',
            'isadmin'
        );

        $validfields = array_merge($validfields, $paymentfields);


        if (!empty($whereusername)) {
            $sql = $this->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                                FROM " . DB_PREFIX . "users
                                WHERE username = '" . $this->db->escape_string($whereusername) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
            if ($this->db->num_rows($sql) > 0) {
                $res = $this->db->fetch_array($sql, DB_ASSOC);
                return o($res[$field]);
            }
        } else if (!empty($whereemail)) {
            $sql = $this->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                                FROM " . DB_PREFIX . "users
                                WHERE email = '" . $this->db->escape_string($whereemail) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
            if ($this->db->num_rows($sql) > 0) {
                $res = $this->db->fetch_array($sql, DB_ASSOC);
                return o($res[$field]);
            }
        } else if ($field == 'fullname') {
            $sql = $this->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "first_name, last_name
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
            if ($this->db->num_rows($sql) > 0) {
                $res = $this->db->fetch_array($sql, DB_ASSOC);
                return o(stripslashes($res['first_name']) . ' ' . stripslashes($res['last_name']));
            }
        } else {
            if (in_array($field, $validfields)) {
                $sql = $this->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                if ($this->db->num_rows($sql) > 0) {
                    $res = $this->db->fetch_array($sql, DB_ASSOC);
                    if ($field == 'username_history') {
                        return $res["$field"];
                    }
                    return o($res[$field]);
                }
            }
        }
        if ($showunknown) {
            return 'N/A';
        }
        return '';
    }

    /**
     * Function to fetch a user's id from the datastore based on an actual username
     *
     * @param
     *            string user name
     *            
     * @return integer Returns the user id
     */
    function fetch_userid($username = '')
    {
        $sql = $this->db->query("
	                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
	                FROM " . DB_PREFIX . "users
	                WHERE username = '" . $this->db->escape_string($username) . "'
	        ", 0, null, __FILE__, __LINE__);
        if ($this->db->num_rows($sql) > 0) {
            $user = $this->db->fetch_array($sql, DB_ASSOC);
            return $user['user_id'];
        }
        return 0;
    }

    /**
     * Function to show page results within the pagnation function like showing results [first] to [last] of [total] pages emulation
     *
     * @param
     *            integer page number we are currently viewing
     * @param
     *            string per page limit
     * @param
     *            string total pages
     *            
     * @return array Returns an array with [first] and [last] page number results
     */
    function construct_start_end_array($pagenum = 0, $perpage = 0, $total = 0)
    {
        $first = $perpage * ($pagenum - 1);
        $last = $first + $perpage;
        if ($last > $total) {
            $last = $total;
        }
        $first++;
        return array(
            'first' => number_format($first),
            'last' => number_format($last)
        );
    }

    /**
     * Function for printing a smart pagination handler to let users navigate small and large data sets.
     *
     * @param
     *            integer total number of rows
     * @param
     *            integer row limit (per page)
     * @param
     *            integer current page number
     * @param
     *            integer total selectable links (7 default)
     * @param
     *            string current page url
     * @param
     *            string custom &page= name
     * @param
     *            boolean include a question mark ? after the $scriptpage url?
     *            
     * @return string HTML representation of the page navigator
     */
    function pagination_old($number = 0, $rowlimit = 10, $page = 0, $selectable = 7, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
    {
        $this->show['hideall'] = false;
        $custompagename = (empty($custompagename) ? 'page' : $custompagename);
        if ($number < $rowlimit) {
            $this->show['hideall'] = true;
            return array(
                '',
                ''
            );
        }
        $this->GPC['page'] = ((isset($this->GPC['page'])) ? intval($this->GPC['page']) : 1);
        $hasqm = ((strrchr($scriptpage, '?') == false) ? false : true); // add ? to url if we don't have one
        $sort = (isset($this->GPC['sort']) ? o($this->GPC['sort']) : '01');
        $startend = $this->construct_start_end_array($page, $rowlimit, $number);
        $scriptpage = (strrchr($scriptpage, '?') == false) ? $scriptpage . '?sort=' . $sort : $scriptpage;
        $scriptpage = $this->seo->rewrite_url($scriptpage, 'page=' . $this->GPC['page']);
        $totalpages = ceil(($number / $rowlimit));
        $totalpages = (($totalpages <= 0) ? 1 : $totalpages);

        $outputmini = '<!-- start mini navigator --><div class="numb">
	' . ($page == 1 ? '' : '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" class="prev" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow">{_prev}</a>') . '
	<span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span>
	' . ($page == $totalpages ? '' : '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="{_next_page}: ' . number_format(($page + 1)) . '" rel="next" class="next">{_next}</a>') . '
</div><!-- end mini navigator -->';

        $output = '';
        $output .= '<!-- start product-control --><div class="product-control"><div class="txt-left" title="' . $this->language->construct_phrase('{_showing_results_x_to_x_of_x}', array(
            '' . $startend['first'] . '',
            '' . $startend['last'] . '',
            '' . number_format($number) . ''
        )
        ) . '">{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</div>';
        $output .= '<!-- start control-box --><div class="control-box"><!-- start pagination --><div class="pagination">';
        $output .= ($page == 1 ? '' : '<a class="prev" href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '">{_prev}</a>') . ($page == $totalpages ? '' : '<a class="next" href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '">{_next}</a>') . '<ul>';
        if ($totalpages <= $selectable) { // the total number of pages is less than the number of selectable pages
            for ($i = 1; $i <= $totalpages; $i++) { // iterate ascendingly
                $output .= '<li' . ($page == $i ? ' class="active"' : '') . '><a href="' . ($page == $i ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit) . '">' . $i . '</a></li>';
            }
        } else { // the total number of pages is greater than the number of selectable pages
            // start with a link to the first page & highlight the page currently selected
            $output .= '<li' . ($page == 1 ? ' class="active"' : '') . '><a href="' . ($page == 1 ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit) . '">1</a></li>';
            // compute the number of adjacent pages to display to the left and right of the currently selected page so the selected [page] is always centered
            $adjacent = floor(($selectable - 3) / 2);
            if ($adjacent == 0) { // this number must be at least 1
                $adjacent = 1;
            }
            // find the page number after we need to show the first "..."
            $scroll_from = ($selectable - $adjacent);
            // get the page number from where we should start rendering
            // if displaying links in natural order, then it's "2" because we have already rendered the first page
            $starting_page = 2;
            // if the currently selected page is past the point from where we need to scroll,
            // we need to adjust the value of $starting_page
            if ($page >= $scroll_from) {
                $starting_page = $page + -$adjacent;
                if ($totalpages - $starting_page < ($selectable - 2)) { // adjust the value of $starting_page again
                    $starting_page -= ($selectable - 2) - ($totalpages - $starting_page);
                }
                // put the "..." after the link to the first page
                $output .= '<li><span>&hellip;</span></li>';
            }
            // get the page number where we should stop rendering
            $ending_page = $starting_page + ((1) * ($selectable - 3));
            // if ending page is greater than the total number of pages minus 1 adjust the ending page
            $ending_page = (($ending_page > $totalpages - 1) ? ($totalpages - 1) : $ending_page);
            // render pagination links
            for ($i = $starting_page; $i <= $ending_page; $i++) { // also highlight the currently selected page
                $output .= '<li' . ($page == $i ? ' class="active"' : '') . '><a href="' . ($page == $i ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit) . '">' . $i . '</a></li>';
            }
            // if we have to, place another "..." at the end, before the link to the last page
            if ($totalpages - $ending_page > 1) {
                $output .= '<li><span>&hellip;</span></li>';
            }
            // put a link to the last page
            $output .= '<li' . ($page == $i ? ' class="active"' : '') . '><a href="' . ($page == $i ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=' . $totalpages . '&amp;pp=' . $rowlimit) . '">' . $totalpages . '</a></li>';
        }
        $output .= '</ul></div><!-- end pagination -->
		</div><!-- end control-box -->
	</div><!-- end product-control -->';
        // return the resulting string
        return array(
            $output,
            $outputmini,
            $startend['first'],
            $startend['last'],
            number_format($number)
        );
    }

    /**
     * Function for printing a smart pagination handler to let users navigate small and large data sets.
     *
     * @param
     *            integer total number of rows
     * @param
     *            integer row limit (per page)
     * @param
     *            integer current page number
     * @param
     *            integer total selectable links (7 default)
     * @param
     *            string current page url
     * @param
     *            string custom &page= name
     * @param
     *            boolean include a question mark ? after the $scriptpage url?
     *            
     * @return string HTML representation of the page navigator
     */
    function pagination($number = 0, $rowlimit = 10, $page = 0, $selectable = 7, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
    {
        $this->show['hideall'] = false;
        $custompagename = (empty($custompagename) ? 'page' : $custompagename);
        if ($number < $rowlimit) {
            $this->show['hideall'] = true;
            return array(
                '',
                ''
            );
        }
        $this->GPC['page'] = ((isset($this->GPC['page'])) ? intval($this->GPC['page']) : 1);
        $hasqm = ((strrchr($scriptpage, '?') == false) ? false : true);
        $sort = (isset($this->GPC['sort']) ? o($this->GPC['sort']) : '01');
        $startend = $this->construct_start_end_array($page, $rowlimit, $number);
        $scriptpage = (strrchr($scriptpage, '?') == false) ? $scriptpage . '?sort=' . $sort : $scriptpage;
        $scriptpage = $this->seo->rewrite_url($scriptpage, 'page=' . $this->GPC['page']);
        $totalpages = ceil(($number / $rowlimit));
        $totalpages = (($totalpages <= 0) ? 1 : $totalpages);

        $outputmini = '<!-- start mini navigator --><div class="numb">
	' . ($page == 1 ? '' : '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" class="prev" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow">{_prev}</a>') . '
	<span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span>
	' . ($page == $totalpages ? '' : '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="{_next_page}: ' . number_format(($page + 1)) . '" rel="next" class="next">{_next}</a>') . '
</div><!-- end mini navigator -->';

        $output = '';
        $output .= '<div class="i-row spacing-top-small"><div class="i-row spacing-micro"></div><div id="pagination" class="tac"><ul class="i-pagination">';
        $output .= ($page == 1 ? '' : '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '">â†�&nbsp;&nbsp;{_prev}</a></li>');
        if ($totalpages <= $selectable) { // the total number of pages is less than the number of selectable pages
            for ($i = 1; $i <= $totalpages; $i++) { // iterate ascendingly
                $output .= '<li' . ($page == $i ? ' class="i-selected"' : '') . ' data-page="' . $i . '"><a href="' . ($page == $i ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit) . '">' . $i . '</a></li>';
            }
        } else { // the total number of pages is greater than the number of selectable pages
            // start with a link to the first page & highlight the page currently selected
            $output .= '<li' . ($page == 1 ? ' class="i-selected"' : '') . ' data-page="1"><a href="' . ($page == 1 ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit) . '">1</a></li>';
            // compute the number of adjacent pages to display to the left and right of the currently selected page so the selected [page] is always centered
            $adjacent = floor(($selectable - 3) / 2);
            if ($adjacent == 0) { // this number must be at least 1
                $adjacent = 1;
            }
            // find the page number after we need to show the first "..."
            $scroll_from = ($selectable - $adjacent);
            // get the page number from where we should start rendering
            // if displaying links in natural order, then it's "2" because we have already rendered the first page
            $starting_page = 2;
            // if the currently selected page is past the point from where we need to scroll,
            // we need to adjust the value of $starting_page
            if ($page >= $scroll_from) {
                $starting_page = $page + -$adjacent;
                if ($totalpages - $starting_page < ($selectable - 2)) { // adjust the value of $starting_page again
                    $starting_page -= ($selectable - 2) - ($totalpages - $starting_page);
                }
                // put the "..." after the link to the first page
                $output .= '<li class="i-disabled">&hellip;</li>';
            }
            // get the page number where we should stop rendering
            $ending_page = $starting_page + ((1) * ($selectable - 3));
            // if ending page is greater than the total number of pages minus 1 adjust the ending page
            $ending_page = (($ending_page > $totalpages - 1) ? ($totalpages - 1) : $ending_page);
            for ($i = $starting_page; $i <= $ending_page; $i++) { // render pagination links : also highlight the currently selected page
                $output .= '<li' . ($page == $i ? ' class="i-selected"' : '') . ' data-page="' . $i . '"><a href="' . ($page == $i ? 'javascript:;' : $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit) . '">' . $i . '</a></li>';
            }
            if ($totalpages - $ending_page > 1) { // if we have to, place another "..." at the end, before the link to the last page
                $output .= '<li class="i-disabled">&hellip;</li>';
            }
            // put a link to the last page
            $output .= '<li class="i-disabled" data-page="' . $i . '">' . $totalpages . '</li>';
        }
        $output .= ($page == $totalpages ? '' : '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '">{_next}&nbsp;&nbsp;â†’</a></li>');
        $output .= '</ul></div></div><!-- end pagination -->';
        return array(
            $output,
            $outputmini,
            $startend['first'],
            $startend['last'],
            number_format($number)
        );
    }

    private function sso_facebook($id = '')
    {
        $userinfo['roleid'] = -1;
        $userinfo['subscriptionid'] = $userinfo['cost'] = 0;
        $userinfo['active'] = 'no';
        $sql = $this->db->query("
			SELECT u.*,c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			WHERE u.facebook_id = '" . $this->db->escape_string($id) . "'
			GROUP BY u.facebook_id
			LIMIT 1
		");
        if ($this->db->num_rows($sql) > 0) {
            $userinfo = $this->db->fetch_array($sql, DB_ASSOC);
            $this->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
				LIMIT 1
			");
            if ($userinfo['status'] == 'active') {
                if ($userinfo['iprestrict'] and !empty($userinfo['ipaddress'])) {
                    if (IPADDRESS != $userinfo['ipaddress']) {
                        refresh(HTTPS_SERVER . 'signin/?error=iprestrict');
                        exit();
                    }
                }
                // default shipping & billing profile
                $userinfo['shipprofileid'] = $this->shipping->fetch_default_ship_profileid($userinfo['user_id']);
                $userinfo['billprofileid'] = $this->shipping->fetch_default_bill_profileid($userinfo['user_id']);
                $this->sessions->build_user_session($userinfo);
                set_cookie('userid', $this->crypt->encrypt($userinfo['user_id']));
                set_cookie('password', $this->crypt->encrypt($userinfo['password']));
                set_cookie('username', $this->crypt->encrypt($userinfo['username']));
                set_cookie('lastvisit', DATETIME24H);
                set_cookie('lastactivity', DATETIME24H);
            }
            return true;
        }
        return false;
    }

    private function sso_twitter($id = '')
    {
        $userinfo['roleid'] = -1;
        $userinfo['subscriptionid'] = $userinfo['cost'] = 0;
        $userinfo['active'] = 'no';
        $sql = $this->db->query("
			SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			LEFT JOIN " . DB_PREFIX . "stores st ON su.user_id = st.user_id
			WHERE u.twitter_id = '" . $this->db->escape_string($id) . "'
			GROUP BY u.twitter_id
			LIMIT 1
		");
        if ($this->db->num_rows($sql) > 0) {
            $userinfo = $this->db->fetch_array($sql, DB_ASSOC);
            $this->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
				LIMIT 1
			");
            if ($userinfo['status'] == 'active') {
                if ($userinfo['iprestrict'] and !empty($userinfo['ipaddress'])) {
                    if (IPADDRESS != $userinfo['ipaddress']) {
                        refresh(HTTPS_SERVER . 'signin/?error=iprestrict');
                        exit();
                    }
                }
                // default shipping & billing profile
                $userinfo['shipprofileid'] = $this->shipping->fetch_default_ship_profileid($userinfo['user_id']);
                $userinfo['billprofileid'] = $this->shipping->fetch_default_bill_profileid($userinfo['user_id']);
                $this->sessions->build_user_session($userinfo);
                set_cookie('userid', $this->crypt->encrypt($userinfo['user_id']));
                set_cookie('password', $this->crypt->encrypt($userinfo['password']));
                set_cookie('username', $this->crypt->encrypt($userinfo['username']));
                set_cookie('lastvisit', DATETIME24H);
                set_cookie('lastactivity', DATETIME24H);
            }
            return true;
        }
        return false;
    }

    private function sso_linkedin($id = '')
    {
        $userinfo['roleid'] = -1;
        $userinfo['subscriptionid'] = $userinfo['cost'] = 0;
        $userinfo['active'] = 'no';
        $sql = $this->db->query("
			SELECT u.*, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			LEFT JOIN " . DB_PREFIX . "stores st ON su.user_id = st.user_id
			WHERE u.linkedin_id = '" . $this->db->escape_string($id) . "'
			GROUP BY u.linkedin_id
			LIMIT 1
		");
        if ($this->db->num_rows($sql) > 0) {
            $userinfo = $this->db->fetch_array($sql, DB_ASSOC);
            $this->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
				LIMIT 1
			");
            if ($userinfo['status'] == 'active') {
                if ($userinfo['iprestrict'] and !empty($userinfo['ipaddress'])) {
                    if (IPADDRESS != $userinfo['ipaddress']) {
                        refresh(HTTPS_SERVER . 'signin/?error=iprestrict');
                        exit();
                    }
                }
                // default shipping & billing profile
                $userinfo['shipprofileid'] = $this->shipping->fetch_default_ship_profileid($userinfo['user_id']);
                $userinfo['billprofileid'] = $this->shipping->fetch_default_bill_profileid($userinfo['user_id']);
                $this->sessions->build_user_session($userinfo);
                set_cookie('userid', $this->crypt->encrypt($userinfo['user_id']));
                set_cookie('password', $this->crypt->encrypt($userinfo['password']));
                set_cookie('username', $this->crypt->encrypt($userinfo['username']));
                set_cookie('lastvisit', DATETIME24H);
                set_cookie('lastactivity', DATETIME24H);
            }
            return true;
        }
        return false;
    }

    private function sso_ldap($id = '')
    {
        $userinfo['roleid'] = -1;
        $userinfo['subscriptionid'] = $userinfo['cost'] = 0;
        $userinfo['active'] = 'no';
        $sql = $this->db->query("
			SELECT u.*,c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "currency c ON (u.currencyid = c.currency_id)
			LEFT JOIN " . DB_PREFIX . "language l ON (u.languageid = l.languageid)
			LEFT JOIN " . DB_PREFIX . "stores st ON (su.user_id = st.user_id)
			WHERE u.ldap_id = '" . $this->db->escape_string($id) . "'
			GROUP BY u.ldap_id
			LIMIT 1
		");
        if ($this->db->num_rows($sql) > 0) {
            $userinfo = $this->db->fetch_array($sql, DB_ASSOC);
            $this->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
				LIMIT 1
			");
            if ($userinfo['status'] == 'active') {
                if ($userinfo['iprestrict'] and !empty($userinfo['ipaddress'])) {
                    if (IPADDRESS != $userinfo['ipaddress']) {
                        refresh(HTTPS_SERVER . 'signin/?error=iprestrict');
                        exit();
                    }
                }
                // default shipping & billing profile
                $userinfo['shipprofileid'] = $this->shipping->fetch_default_ship_profileid($userinfo['user_id']);
                $userinfo['billprofileid'] = $this->shipping->fetch_default_bill_profileid($userinfo['user_id']);
                $this->sessions->build_user_session($userinfo);
                set_cookie('userid', $this->crypt->encrypt($userinfo['user_id']));
                set_cookie('password', $this->crypt->encrypt($userinfo['password']));
                set_cookie('username', $this->crypt->encrypt($userinfo['username']));
                set_cookie('lastvisit', DATETIME24H);
                set_cookie('lastactivity', DATETIME24H);
            }
            return true;
        }
        return false;
    }

    private function sso_signin($provider = '', $id = '')
    {
        $provider = 'sso_' . $provider;
        $response = $this->$provider($id);
        unset($provider);
        return $response;
    }

    public function sso($provider = '', $id = '')
    {
        if (in_array($provider, $this->ssoproviders)) {
            $response = $this->sso_signin($provider, $id);
            return $response;
        }
        return false;
    }

    /**
     * Function to scan various settings to ensure integrity and health of the marketplace
     *
     * @return string Plain text string of what was completed for cron automation
     */
    function potentalconflicts()
    {
        if (!is_dir(DIR_ATTACHMENTS . 'auctions')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'auctions', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'ax')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'ax', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'blocks')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'blocks', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . DIR_TMP_NAME)) { // cache folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . DIR_TMP_NAME, 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'categoryheros')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'categoryheros', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'categoryicons')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'categoryicons', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'categorysearch')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'categorysearch', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'categorysponsor')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'categorysponsor', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'categorythumbs')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'categorythumbs', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'content')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'content', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'heros')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'heros', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'nonprofit')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'nonprofit', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'plan')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'plan', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'pmbs')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'pmbs', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'product')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'product', 0755, true);
            mkdir(DIR_ATTACHMENTS . 'product/brand', 0755, true);
            mkdir(DIR_ATTACHMENTS . 'product/gtin', 0755, true);
            mkdir(DIR_ATTACHMENTS . 'product/owner', 0755, true);
            mkdir(DIR_ATTACHMENTS . 'product/upc', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'product/brand')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'product/brand', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'product/gtin')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'product/gtin', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'product/owner')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'product/owner', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'product/upc')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'product/upc', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'profiles')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'profiles', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'stores')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'stores', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'vendor')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'vendor', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_ATTACHMENTS . 'watermarks')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_ATTACHMENTS . 'watermarks', 0755, true);
            umask($oldumask);
        }
        // cache folders
        if (!is_dir(DIR_TMP . 'app')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'app', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'applog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'applog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'backup')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'backup', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'checkup')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'checkup', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'css')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'css', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'datastore')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'datastore', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'didyoumean')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'didyoumean', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'javascript')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'javascript', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'ldaplog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'ldaplog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'liveauction')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'liveauction', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'paymentlog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'paymentlog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'rotatelog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'rotatelog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'rtbf')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'rtbf', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'shippinglog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'shippinglog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'smtplog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'smtplog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'upgradelog')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'upgradelog', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'upgrader')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'upgrader', 0755, true);
            umask($oldumask);
        }
        if (!is_dir(DIR_TMP . 'vendor')) { // folder doesn't exist! make the folder!
            $oldumask = umask(0);
            mkdir(DIR_TMP . 'vendor', 0755, true);
            umask($oldumask);
        }
        return 'sheel->potentalconflicts(), ';
    }

    /**
     * Function to convert an array into a readable string
     *
     * @param
     *            array array
     *            
     * @return string HTML representation of the supplied array
     */
    function array2string($data)
    {
        $log_a = "";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $log_a .= "[" . $key . "] => (" . $this->array2string($value) . ")\n";
            } else {
                $log_a .= "[" . $key . "] => " . $value . "\n";
            }
        }
        return $log_a;
    }

    /**
     * Function for fetching data from a url based on the curl library extention in php
     *
     * @param
     *            string url
     *            
     * @return string HTML representation of the data requested
     */
    function fetch_curl_string($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            $this->curlerrors[] = curl_error($ch);
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // <- As of PHP 5.5.0 and cURL 7.10.8, this is a legacy alias of CURLINFO_RESPONSE_CODE
        curl_close($ch);
        $return = array();
        $return['response']['code'] = $httpcode;
        $return['body'] = $response;
        return $return;
    }

    /**
     * Function for fetching data from a url with sessions based on the curl library extention in php
     *
     * @param
     *            string url
     *            
     * @return string HTML representation of the data requested
     */
    function fetch_curl_string_session($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);
        $header = curl_getinfo($ch);
        if (curl_error($ch)) {
            $this->curlerrors[] = curl_error($ch);
        }
        curl_close($ch);
        $header_content = substr($result, 0, $header['header_size']);
        $pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m";
        preg_match_all($pattern, $header_content, $matches);
        $cookies = implode("; ", $matches['cookie']);
        // Then, once we have the cookie, let's use it in the next request:
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            $this->curlerrors[] = curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    /**
     * Function to format a zipcode for sheel by removing spaces and dashes
     *
     * @param
     *            string zip code to format
     *            
     * @return string Returns HTML formatted string
     */
    function format_zipcode($zipcode = '')
    {
        $zipcode = str_replace(' ', '', $zipcode);
        $zipcode = strtoupper($zipcode);
        return $zipcode;
    }

    /**
     * Function to replace test as t**t and profanity as p******y, etc
     *
     * @param
     *            string string to asterisk
     *            
     * @return string Returns HTML formatted string
     */
    function asterisks($str = '')
    {
        $maxlen = 25;
        $len = mb_strlen($str); // 50
        if ($len <= $maxlen) {
            return substr($str, 0, 1) . str_repeat('*', $len - 2) . substr($str, $len - 1, 1);
        }
        return substr($str, 0, 1) . str_repeat('*', $maxlen - 2) . substr($str, $len - 1, 1);
    }

    /**
     * Function to shorten a string based on a supplied argument length to cut off as well as a custom symbol
     * to represent at the end of the string (ie: .....)
     *
     * @param
     *            string text
     * @param
     *            integer limiter length
     * @param
     *            string limiter symbol (ie: .....)
     *            
     * @return string Returns the formatted text with the ending limiter symbol to represent more text is available
     */
    function short_string($text = '', $length, $symbol = ' .....')
    {
        $length_text = mb_strlen($text);
        $length_symbol = mb_strlen($symbol);
        if ($length_text <= $length or $length_text <= $length_symbol or $length <= $length_symbol) {
            return ($text);
        } else {
            if ((mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") > mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 10) && (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 10)) {
                return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
            } else if ((mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 10) && (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") > 0)) {
                return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".")) . $symbol);
            } else if ((mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 10) && (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") > 0)) {
                return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",")) . $symbol);
            } else {
                return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
            }
        }
    }

    /**
     * Function to shorten a string of characters using an argument limiter as the amount of characters to reveal
     *
     * @param
     *            string html string
     * @param
     *            integer limiter amount (ie: 50)
     *            
     * @return string HTML representation of the shortened string
     */
    function shorten($string = '', $limit = 0, $showdots = true)
    {
        if (mb_strlen($string) > $limit) {
            $string = mb_substr($string, 0, $limit);
            if (($pos = mb_strrpos($string, ' ')) !== false) {
                $string = mb_substr($string, 0, $pos);
            }
            return $string . (($showdots) ? '...' : '');
        }
        return $string;
    }

    /**
     * Function helper for print_string_wrap()
     *
     * @param
     *            string text
     * @param
     *            integer chracter limit to break up
     * @param
     *            string break character (default \r\n)
     *            
     * @return string Formatted text
     */
    function mb_chunk_split($str, $l = 65, $e = "\r\n")
    {
        $tmp = array_chunk(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $l);
        $str = "";
        foreach ($tmp as $t) {
            $str .= join("", $t) . $e;
        }
        return $str;
    }

    /**
     * Function to break up a long string with no spaces based on a supplied character limit
     *
     * @param
     *            string text
     * @param
     *            integer chracter limit to break up
     *            
     * @return string Formatted text
     */
    function print_string_wrap($text = '', $width = 65, $break = ' ')
    {
        $thewidth = $width;
        $thebreak = $break;
        global $thewidth, $thebreak;
        $thewidth = $width;
        $thebreak = $break;
        global $thewidth, $thebreak;
        return preg_replace_callback('#(\S{' . $width . ',})#', function ($matches) {
            global $thewidth, $thebreak;
            return $this->mb_chunk_split($matches[1], $thewidth, $thebreak);
        }, $text);
    }
    function is_serialized($data = '')
    {
        return (@unserialize($data) !== false);
    }
    function construct_pulldown($id = '', $name = '', $values = array(), $default = '', $extra = '', $wrapperstart = '', $wrapperend = '', $attr = array())
    {
        $html = $wrapperstart . '<select id="' . $id . '" name="' . $name . '" ' . $extra . '>';
        foreach ($values as $key => $value) {
            if (strtolower($key) == "optgroupstart") {
                $html .= '<optgroup label="' . $value . '">';
            } else if (strtolower($key) == "optgroupend") {
                $html .= '</optgroup>';
            } else {
                $attrs = '';
                if (isset($attr[$key]) and count($attr[$key]) > 0) {
                    foreach ($attr[$key] as $key2 => $value2) {
                        $attrs .= $key2 . '="' . $value2 . '" ';
                    }
                }
                if ($default != '' and $this->is_serialized($default)) { // multiple selection
                    $defaultx = unserialize($default);
                    $sel = ((in_array($key, $defaultx)) ? ' selected="selected"' : ''); // todo!!
                } else { // single
                    $sel = ($key == $default) ? ' selected="selected"' : '';
                }
                $html .= '<option ' . $attrs . ' value="' . $key . '"' . $sel . '>' . $value . '</option>';
            }
        }
        $html .= '</select>' . $wrapperend;
        return $html;
    }

    function pagelinks($location = 'footer', $lis = false)
    {
        $html = '';

        $sql = ($location == 'foorter-home') ? $this->db->query("
			SELECT title, seourl
			FROM " . DB_PREFIX . "content
			WHERE ispublished = '1'
				AND visible = '1'
				AND (location = '" . $this->db->escape_string('footer') . "' OR location = 'both')
			ORDER BY sort ASC
		") : ($location == 'header-home') ? $this->db->query("
			SELECT title, seourl
			FROM " . DB_PREFIX . "content
			WHERE ispublished = '1'
				AND visible = '1'
				AND (location = '" . $this->db->escape_string('header') . "' OR location = 'both')
			ORDER BY sort ASC
		") : $this->db->query("
			SELECT title, seourl
			FROM " . DB_PREFIX . "content
			WHERE ispublished = '1'
				AND visible = '1'
				AND (location = '" . $this->db->escape_string($location) . "' OR location = 'both')
			ORDER BY sort ASC
		");


        if ($location == 'foorter-home' || $location == 'header-home') {


            while ($res = $this->db->fetch_array($sql, DB_ASSOC)) {
                if ($lis) { // <li><p><a href="{https_server}membership/"><span class="left_icon"><i class="fa fa-user"></i></span>{_subscription} <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>
                    $html .= (($location == 'footer-home') ? '<li><p><a href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><span class="left_icon"><i class="fa fa-question-circle"></i></span>' . o($res['title']) . ' <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>' : '<li><p><a href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><span class="left_icon"><i class="fa fa-question-circle"></i></span>' . o($res['title']) . '  <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>');
                } else {
                    $html .= (($location == 'footer-home') ? '<li><a class="color-ccc" href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><i class="fa fa-caret-right"></i>' . o($res['title']) . '</a></li>' : '<li><a class="text-uppercase" href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html">' . o($res['title']) . '</a></li>');
                }
            }
        } else {
            while ($res = $this->db->fetch_array($sql, DB_ASSOC)) {
                if ($lis) { // <li><p><a href="{https_server}membership/"><span class="left_icon"><i class="fa fa-user"></i></span>{_subscription} <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>
                    $html .= (($location == 'footer') ? '<li><p><a href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><span class="left_icon"><i class="fa fa-question-circle"></i></span>' . o($res['title']) . ' <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>' : '<li><p><a href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><span class="left_icon"><i class="fa fa-question-circle"></i></span>' . o($res['title']) . '  <span class="right_arrow"><i class="fa fa-angle-right"></i></span></a></p></li>');
                } else {
                    $html .= (($location == 'footer') ? '<li><a class="color-ccc" href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html"><i class="fa fa-caret-right"></i>&nbsp;' . o($res['title']) . '</a></li>' : '<li><a class="pt-16" href="' . HTTPS_SERVER . 'content/' . trim($res['seourl']) . '.html">' . o($res['title']) . '</a></li>');
                }
            }

        }
        return $html;
    }

    /**
     * Function to fetch the first admin's email address.
     * We'll revert to the primary from: email if we can't find a valid administrator.
     *
     * @return string Returns admin email address
     */
    function fetch_staff_email()
    {
        $sql = $this->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "email
			FROM " . DB_PREFIX . "users
			WHERE isadmin = '1'
				AND status = 'active'
			ORDER BY user_id DESC
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
        if ($this->db->num_rows($sql) > 0) {
            $res = $this->db->fetch_array($sql, DB_ASSOC);
            return $res['email'];
        }
        return SITE_EMAIL;
    }

    function api_request_submit($requestmd5 = '')
    {
        $sql = $this->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, response
			FROM " . DB_PREFIX . "api_requests
			WHERE hash = '" . $this->db->escape_string($requestmd5) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
        if ($this->db->num_rows($sql) > 0) {
            $res = $this->db->fetch_array($sql, DB_ASSOC);
            $this->db->query("
				UPDATE " . DB_PREFIX . "api_requests
				SET hits = hits + 1
				WHERE id = '" . $res['id'] . "'
				LIMIT 1
			");
            $detect = json_decode($res['response']);
            if ($detect != '' and json_last_error() === JSON_ERROR_NONE) {
                $res['response'] = json_decode($res['response'], true);
            }
            return $res['response'];
        }
        return false;
    }

    function clean_api_requests()
    { // runs monthly
        /*
         * $this->db->query("
         * TRUNCATE TABLE " . DB_PREFIX . "api_requests
         * ", 0, null, __FILE__, __LINE__);
         */
    }

    /**
     * Function to log an api request and response for later audit
     *
     * @param
     *            string api name
     * @param
     *            string api request
     * @param
     *            string api response
     * @param
     *            integer api response time
     */
    function api_request($apiname = '', $request = '', $response = '', $time = 0)
    {
        $userid = ((!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? $_SESSION['sheeldata']['user']['userid'] : 0);
        $hash = md5($request);
        $this->db->query("
			INSERT INTO " . DB_PREFIX . "api_requests
			(id, datetime, name, request, response, hash, userid, ipaddress, time)
			VALUES (
			NULL,
			'" . DATETIME24H . "',
			'" . $this->db->escape_string($apiname) . "',
			'" . $this->db->escape_string($request) . "',
			'" . $this->db->escape_string($response) . "',
			'" . $this->db->escape_string($hash) . "',
			'" . intval($userid) . "',
			'" . IPADDRESS . "',
			'" . $this->db->escape_string($time) . "'
			)
		");
    }

    function bot($url = '')
    {
        $header = array();
        $header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
        $header[] = 'Cache-Control: max-age=0';
        $header[] = 'Content-Type: text/html; charset=utf-8';
        $header[] = 'Transfer-Encoding: chunked';
        $header[] = 'Connection: keep-alive';
        $header[] = 'Keep-Alive: 300';
        $header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
        $header[] = 'Accept-Language: en-us,en;q=0.5';
        $header[] = 'Pragma:';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; sheelbot/1.0; +http://www.sheel.com/bot.html)');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_REFERER, 'http://www.sheel.com');
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($curl);
        curl_close($curl);
        return $body;
    }

    function fetch_earn_points($price = 0, $sellerid = 0, $formatted = true)
    {
        $this->show['canearnpoints'] = false;
        $earnpoints = '';
        if ($this->config['pointsystem'] and $this->config['pointsystemuserids'] != '' and $price != '') {
            $pnttmp = explode(',', $this->config['pointsystemuserids']);
            foreach ($pnttmp as $accepteduserid) {
                $accepteduserid = intval(trim($accepteduserid));
                if ($accepteduserid == $sellerid and $this->config['pointsystemlimit'] != '') {
                    $this->show['canearnpoints'] = true;
                    $earnpoints = (($formatted) ? number_format(floor(($price / $this->config['pointsystemlimit']))) : floor(($price / $this->config['pointsystemlimit'])));
                    break;
                }
            }
        }
        return $earnpoints;
    }

    function is_valid_csrf_token($token = '', $uid = 0, $strict = false)
    {
        if (!$strict) {
            $sql = $this->db->query("
				SELECT sesskey
				FROM " . DB_PREFIX . "sessions
				WHERE token = '" . $this->db->escape_string($token) . "'
			");
            if ($this->db->num_rows($sql) > 0) {
                return true;
            }
        } else {
            $sql = $this->db->query("
				SELECT sesskey
				FROM " . DB_PREFIX . "sessions
				WHERE token = '" . $this->db->escape_string($token) . "'
					AND userid = '" . intval($uid) . "'
					AND (isuser = '1' OR isadmin = '1')
			");
            if ($this->db->num_rows($sql) > 0) {
                return true;
            }
        }
        return false;
    }
}
?>
