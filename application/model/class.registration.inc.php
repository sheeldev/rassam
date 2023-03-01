<?php
/**
 * Registration class to perform the majority of registration handling tasks
 *
 * @package      sheel\Registration
 * @version      1.0.0.0
 * @author       sheel
 */
class registration
{
        protected $sheel;
        var $quickregistererrors = array();
        function __construct($sheel)
        {
                $this->sheel = $sheel;
        }
        /**
         * Function for creating a valid sheel member using the registration datastore.
         *
         * @param       array        user information
         * @param       array        user preferences information
         * @param       array        user subscription information
         * @param       string       tells this function what data to return when completed: return_userid OR return_userstatus OR return_userarray
         * @param       bool         tells this function if it should skip sessions (for api calls from other applications if required)
         * @param       string       tells the site the exact source where the user registering is from (api calls like Facebook apis, external applications, etc)
         * @param       boolean      tells the function if we're creating a new user based on a guest checkout to auto sign-in without authentication
         *
         * @return      mixed        returns integers, strings and arrays
         */
        function build_user_datastore(&$user, &$preferences, &$subscription, $custom = 'return_userarray', $skipsessions = 0, $registersource = 'Full Registration', $isguestcheckout = 0)
        {
                if (!empty($user) and is_array($user)) {
                        if (!isset($user['emailnotify'])) {
                                $user['emailnotify'] = 0;
                        }
                        if (!isset($user['agreeterms'])) { // assumes the customer has accepted the terms before being passed to this function
                                $user['agreeterms'] = 1;
                        }
                        if (!empty($user['password_md5']) and !empty($user['salt'])) { // we're sending an salted md5 password ready to store in the user table
                                $user['password'] = $user['password_md5'];
                        } else {
                                if (empty($user['salt']) and !empty($user['password'])) { // no salt found! just a clear text password! encode password!
                                        $user['password_raw'] = $user['password'];
                                        $user['salt'] = $this->sheel->construct_password_salt(5);
                                        $user['password'] = md5(md5($user['password']) . $user['salt']);
                                } else if (!empty($user['salt']) and !empty($user['password'])) { // clear text password and salt found! encode password!
                                        $user['password'] = md5(md5($user['password']) . $user['salt']);
                                }
                        }
                        if (empty($user['address2'])) {
                                $user['address2'] = '';
                        }
                        if (empty($user['phone'])) {
                                $user['phone'] = '{_unknown}';
                        }
                        if (empty($user['dob'])) {
                                $user['dob'] = '0000-00-00';
                        }
                        if (empty($user['ipaddress'])) {
                                $user['ipaddress'] = IPADDRESS;
                        }
                        if (empty($user['roleid'])) {
                                $user['roleid'] = $this->sheel->config['subscriptions_defaultroleid_product'];
                        }
                        if ($this->sheel->config['guest_checkouts'] == 0) { // guest checkouts disabled..
                                if ($this->sheel->config['registrationdisplay_emailverification']) { // require email verification
                                        $user['status'] = 'unverified';
                                        $skipsessions = 1;
                                } else {
                                        if ($this->sheel->config['registrationdisplay_moderation']) { // require staff moderation approval
                                                $user['status'] = 'moderated';
                                                $skipsessions = 1;
                                        } else {
                                                $user['status'] = 'active';
                                        }
                                }
                        } else { // guest checkouts enabled
                                
                                if ($isguestcheckout) { // visitor signing up via order (real guest checkout from shipping location page)
                                        $user['status'] = 'active';
                                } else { // visitor signing up via registration (allow moderation rules)
                                        if ($this->sheel->config['registrationdisplay_emailverification']) { // require email verification
                                                $user['status'] = 'unverified';
                                                $skipsessions = 1;
                                        } else {
                                                if ($this->sheel->config['registrationdisplay_moderation']) { // require staff moderation approval
                                                        $user['status'] = 'moderated';
                                                        $skipsessions = 1;
                                                } else {
                                                        $user['status'] = 'active';
                                                }
                                        }
                                }

                        }
                        

                        require_once(__DIR__ . '/class.geoip.inc.php');
                        $g = array();
                        $g['country'] = ((!empty($_SERVER['GEOIP_COUNTRY']) and $_SERVER['GEOIP_COUNTRY'] != '') ? $_SERVER['GEOIP_COUNTRY'] : $this->sheel->config['registrationdisplay_defaultcountry']);
                        $g['countryid'] = ((!empty($_SERVER['GEOIP_COUNTRYCODE']) and $_SERVER['GEOIP_COUNTRYCODE'] != '') ? $this->sheel->common_location->fetch_country_id_by_code($_SERVER['GEOIP_COUNTRYCODE']) : $this->sheel->common_location->fetch_country_id($g['country']));
                        $g['state'] = ((!empty($GEOIP_REGION_NAME[$_SERVER['GEOIP_COUNTRYCODE']][$_SERVER['GEOIP_STATECODE']]) and $GEOIP_REGION_NAME[$_SERVER['GEOIP_COUNTRYCODE']][$_SERVER['GEOIP_STATECODE']] != '') ? $GEOIP_REGION_NAME[$_SERVER['GEOIP_COUNTRYCODE']][$_SERVER['GEOIP_STATECODE']] : $this->sheel->config['registrationdisplay_defaultstate']);
                        $g['city'] = ((!empty($_SERVER['GEOIP_CITY']) and $_SERVER['GEOIP_CITY'] != '') ? $_SERVER['GEOIP_CITY'] : $this->sheel->config['registrationdisplay_defaultcity']);
                        $g['zipcode'] = (!empty($_SERVER['GEOIP_ZIPCODE']) ? $_SERVER['GEOIP_ZIPCODE'] : '{_unknown}');
                        if (empty($user['state'])) {
                                $user['state'] = $g['state'];
                        }
                        if (empty($user['city'])) {
                                $user['city'] = $g['city'];
                        }
                        if (empty($user['zipcode'])) {
                                $user['zipcode'] = $g['zipcode'];
                        }
                        if (empty($user['country'])) {
                                $user['country'] = $g['country'];
                                $user['countryid'] = $g['countryid'];
                        }
                        if (empty($user['countryid']) and !empty($user['country'])) {
                                $user['countryid'] = $this->sheel->common_location->fetch_country_id($user['country']);
                        }
                        unset($g);
                       
                        $user['styleid'] = ((isset($user['styleid']) and $user['styleid'] > 0) ? intval($user['styleid']) : $this->sheel->config['defaultstyle']);
                        $user['slng'] = ((isset($user['slng']) and !empty($user['styleid'])) ? $user['slng'] : $_SESSION['sheeldata']['user']['slng']);
                        $user['gender'] = ((isset($user['gender']) and $user['gender'] != '') ? $user['gender'] : '');
                        
                        if (!empty($user['username']) and !empty($user['password']) and !empty($user['salt']) and !empty($user['email']) and !empty($user['firstname']) and !empty($user['lastname']) and !empty($user['address']) and !empty($user['city']) and !empty($user['state']) and !empty($user['countryid']) and !empty($user['zipcode'])) {
                                if (!isset($preferences['languageid']) or isset($preferences['languageid']) and $preferences['languageid'] <= 0) {
                                        $preferences['languageid'] = ((isset($user['languageid']) and $user['languageid'] > 0) ? $user['languageid'] : $this->sheel->config['globalserverlanguage_defaultlanguage']);
                                }
                                if (empty($user['currencyid'])) {
                                        $preferences['currencyid'] = $this->sheel->config['globalserverlocale_defaultcurrency'];
                                }
                                if (empty($preferences['usertimezone'])) {
                                        $preferences['usertimezone'] = $this->fetch_default_timezone();
                                }
                                if (empty($user['notifyproducts'])) {
                                        $preferences['notifyproducts'] = '0';
                                }

                                $user['usernamehash'] = $this->sheel->crypt->encrypt($user['username']);
                                $user['usernameslug'] = $this->sheel->seo->construct_seo_url_name($user['username']);
                                $this->sheel->db->query("
                                        INSERT INTO " . DB_PREFIX . "users
                                        (user_id, ipaddress, username, usernameslug, usernamehash, password, salt, email, first_name, last_name, address, address2, city, state, zip_code, phone, country, date_added, status, lastseen, dob, roleid, styleid, languageid, currencyid, timezone, notifyproducts, displayprofile, emailnotify, gender, useapi, registersource)
                                        VALUES(
                                        NULL,
                                        '" . $this->sheel->db->escape_string($user['ipaddress']) . "',
                                        '" . $this->sheel->db->escape_string($user['username']) . "',
                                        '" . $this->sheel->db->escape_string($user['usernameslug']) . "',
                                        '" . $this->sheel->db->escape_string($user['usernamehash']) . "',
                                        '" . $this->sheel->db->escape_string($user['password']) . "',
                                        '" . $this->sheel->db->escape_string($user['salt']) . "',
                                        '" . $this->sheel->db->escape_string($user['email']) . "',
                                        '" . $this->sheel->db->escape_string($user['firstname']) . "',
                                        '" . $this->sheel->db->escape_string($user['lastname']) . "',
                                        '" . $this->sheel->db->escape_string($user['address']) . "',
                                        '" . $this->sheel->db->escape_string($user['address2']) . "',
                                        '" . $this->sheel->db->escape_string($user['city']) . "',
                                        '" . $this->sheel->db->escape_string($user['state']) . "',
                                        '" . $this->sheel->db->escape_string($user['zipcode']) . "',
                                        '" . $this->sheel->db->escape_string($user['phone']) . "',
                                        '" . intval($user['countryid']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $this->sheel->db->escape_string($user['status']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $this->sheel->db->escape_string($user['dob']) . "',
                                        '" . $this->sheel->db->escape_string($user['roleid']) . "',
                                        '" . intval($user['styleid']) . "',
                                        '" . intval($preferences['languageid']) . "',
                                        '" . intval($preferences['currencyid']) . "',
                                        '" . $this->sheel->db->escape_string($preferences['usertimezone']) . "',
                                        '" . intval($preferences['notifyproducts']) . "',
                                        '1',
                                        '" . intval($user['emailnotify']) . "',
					'" . $this->sheel->db->escape_string($user['gender']) . "',
                                        '1',
                                        '" . $this->sheel->db->escape_string($registersource) . "')
                                ", 0, null, __FILE__, __LINE__);
                                $member['userid'] = $this->sheel->db->insert_id();
                                $postget = "success\n" . $this->sheel->array2string($user);
                                $message = 'Accepted terms';
                                $this->sheel->log_event($member['userid'], basename(__FILE__), $postget, 'Consent', $message, 'consent');
                                $message = 'Accepted privacy policy';
                                $this->sheel->log_event($member['userid'], basename(__FILE__), $postget, 'Consent', $message, 'consent');
                                $message = (($user['emailnotify'] > 0) ? 'Accepted' : 'Rejected') . ' receive news and updates from us by email';
                                $this->sheel->log_event($member['userid'], basename(__FILE__), $postget, 'Consent', $message, 'consent');
                                if (isset($user['new_redirect']) and !empty($user['new_redirect'])) {
                                        $member['redirect'] = $user['new_redirect'];
                                }
                        } else { // one or more elements within the $user array is missing
                                return false;
                        }
                } else {
                        return false;
                }
                if (!empty($member['userid']) and $member['userid'] > 0) {
                        $sqlcurrencies = $this->sheel->db->query("
                                SELECT currency_id, currency_abbrev, currency_name, rate, time, isdefault, symbol_left, symbol_right, symbol_local, decimal_point, thousands_point, decimal_places, decimal_places_local
                                FROM " . DB_PREFIX . "currency
                                WHERE currency_id = '" . intval($preferences['currencyid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sqlcurrencies) > 0) {
                                $res_currencies = $this->sheel->db->fetch_array($sqlcurrencies, DB_ASSOC);
                        }
                        unset($sqlcurrencies);
                        if ($skipsessions == 0) { // build session
                                $user['user_id'] = $member['userid'];
                                $user['first_name'] = $user['firstname'];
                                $user['last_name'] = $user['lastname'];
                                $user['zip_code'] = $user['zipcode'];
                                $user['rid'] = $user['ridcode'];
                                $user['timezone'] = $preferences['usertimezone'];
                                $user['lastseen'] = DATETIME24H;
                                $user['languagecode'] = $this->sheel->language->print_language_code($preferences['languageid']);
                                $user['languageiso'] = $this->sheel->language->print_language_iso($preferences['languageid']);
                                $user['country'] = intval($user['countryid']);
                                $user['emailnotify'] = 1;
                                $user['active'] = (($isguestcheckout and $this->sheel->config['guest_checkouts']) ? 'yes' : 'no');
                                $user['iprestrict'] = 0;
                                $user['isadmin'] = 0;
                                $user['cost'] = 0;
                                $user['storeid'] = 0;
                                $user['seourl'] = '';
                                $user['browserhistory'] = 1;
                                $userinfo = array_merge($user, $preferences, $subscription, $res_currencies);
                                $this->sheel->sessions->build_user_session($userinfo);
                                unset($userinfo);
                        }

                        if ($user['status'] == 'active') {
                                $this->sheel->email->mail = $user['email'];
                                $this->sheel->email->slng = $user['slng'];
                                $this->sheel->email->get('register_welcome_email');
                                $this->sheel->email->set(
                                        array(
                                                '{{username}}' => $user['username'],
                                                '{{user_id}}' => $member['userid'],
                                                '{{first_name}}' => $user['firstname'],
                                                '{{last_name}}' => $user['lastname'],
                                                '{{phone}}' => $user['phone'],
                                                '{{categories}}' => $categories
                                        )
                                );
                                $this->sheel->email->send();
                                $this->sheel->email->mail = SITE_CONTACT;
                                $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                                $this->sheel->email->get('register_welcome_email_admin');
                                $this->sheel->email->set(
                                        array(
                                                '{{username}}' => $user['username'],
                                                '{{user_id}}' => $member['userid'],
                                                '{{first_name}}' => $user['firstname'],
                                                '{{last_name}}' => $user['lastname'],
                                                '{{phone}}' => $user['phone'],
                                                '{{emailaddress}}' => $user['email'],
                                                '{{ipaddress}}' => IPADDRESS,
                                                '{{country}}' => (!empty($user['country']) ? $user['country'] : $_SERVER['GEOIP_COUNTRY']),
                                                '{{city}}' => (!empty($user['city']) ? $user['city'] : $_SERVER['GEOIP_CITY']),
                                                '{{state}}' => (!empty($user['state']) ? $user['state'] : $_SERVER['GEOIP_STATE']),
                                                '{{zipcode}}' => (!empty($user['zipcode']) ? $user['zipcode'] : $_SERVER['GEOIP_ZIPCODE']),
                                                '{{registersource}}' => $registersource
                                        )
                                );
                                $this->sheel->email->send();
                        } else {
                                if ($user['status'] == 'unverified') { // send link code verification email
                                        if (isset($member['redirect'])) {
                                                $this->send_email_activation_with_redirect_link($user['email'], $member['redirect']);
                                        } else {
                                                $this->send_email_activation($user['email']);
                                        }
                                } else if ($user['status'] == 'moderated') {
                                        $this->sheel->email->mail = $user['email'];
                                        $this->sheel->email->slng = $user['slng'];
                                        $this->sheel->email->get('register_moderation_email');
                                        $this->sheel->email->set(array());
                                        $this->sheel->email->send();
                                }
                        }
                        
                } else {
                        return false;
                }
                switch ($custom) { // handle custom arguments to send valid response back
                        case 'return_userid': { // let's return the new member ID to the script
                                        return intval($member['userid']);
                                }
                        case 'return_userstatus': { // let's return the new member user / login status
                                        return $user['status'];
                                }
                        case 'default':
                        case 'return_userarray': { // let's return the new member array
                                        $user['userid'] = intval($member['userid']);
                                        if (isset($member['redirect']) and !empty($member['redirect'])) {
                                                $user['redirect'] = $member['redirect'];
                                        }
                                        return $user;
                                }
                        case 'return_userarray_json': { // let's return the new member array json encoded
                                        $user['userid'] = intval($member['userid']);
                                        if (isset($member['redirect']) and !empty($member['redirect'])) {
                                                $user['redirect'] = $member['redirect'];
                                        }
                                        return json_encode($user);
                                }
                }
        }
        /**
         * Function for dispatching the activation email to new clients with a redirection link after sign-in.
         *
         * @param       string       user email address
         *
         */
        private function send_email_activation_with_redirect_link($useremail = '', $redirect = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $this->sheel->db->escape_string($useremail) . "'
                                AND status = 'unverified'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $member['userid'] = $res['user_id'];
                        $link = (isset($redirect)) ? HTTPS_SERVER . 'register/activate/?redirect=' . urlencode($redirect) . '&u=' . $this->sheel->crypt->encrypt($member['userid']) : HTTPS_SERVER . 'register/activate/?u=' . $this->sheel->crypt->encrypt($member['userid']);
                        $this->sheel->email->mail = $res['email'];
                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res['user_id']);
                        $this->sheel->email->get('registration_email');
                        $this->sheel->email->set(
                                array(
                                        '{{username}}' => $res['username'],
                                        '{{user_id}}' => $res['user_id'],
                                        '{{first_name}}' => $res['first_name'],
                                        '{{last_name}}' => $res['last_name'],
                                        '{{phone}}' => $res['phone'],
                                        '{{http_server}}' => HTTPS_SERVER,
                                        '{{site_name}}' => SITE_NAME,
                                        '{{staff}}' => SITE_CONTACT,
                                        '{{link}}' => $link,
                                )
                        );
                        $this->sheel->email->send();
                        return true;
                }
                return false;
        }

        /**
         * Function for checking a referral code
         *
         * @param       integer      user id
         * @param       string       referral code
         *
         */
        private function referral_check($userid, $referralcode)
        {
                $sql = $this->sheel->db->query("
                        SELECT rid, user_id
                        FROM " . DB_PREFIX . "users
                        WHERE rid = '" . $this->sheel->db->escape_string($referralcode) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql)) {
                                $sql2 = $this->sheel->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql2) > 0) {
                                        $this->sheel->db->query("
                                                INSERT INTO " . DB_PREFIX . "referral_data
                                                (id, user_id, referred_by, date)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($res['user_id']) . "',
                                                '" . DATETIME24H . "')
                                        ", 0, null, __FILE__, __LINE__);
                                        $this->sheel->email->mail = $this->sheel->fetch_user('email', $res['user_id']);
                                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng(intval($userid));
                                        $this->sheel->email->get('referral_registered_referrer');
                                        $this->sheel->email->set(
                                                array(
                                                        '{{username}}' => $this->sheel->fetch_user('username', $res['user_id']),
                                                        '{{rid}}' => $referralcode,
                                                        '{{payout_amount}}' => $this->sheel->currency->format($this->sheel->config['referalsystem_payout'])
                                                )
                                        );
                                        $this->sheel->email->send();
                                }
                        }
                }
        }
        /**
         * Function for creating a new user account number used in the sheel accounting system.
         *
         * @return      mixed         unique online account balance number
         */
        private function construct_account_number()
        {
                do {
                        $rand1 = rand(100, 999);
                        $rand2 = rand(100, 999);
                        $rand3 = rand(100, 999);
                        $rand4 = rand(100, 999);
                        $rand5 = rand(1, 9);
                        $account_number = $rand1 . $rand2 . $rand3 . $rand4 . $rand5;
                        $sql = $this->sheel->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
                                WHERE account_number = '" . $this->sheel->db->escape_string($account_number) . "'
                                LIMIT 1
                        ");
                }
                while ($this->sheel->db->num_rows($sql) > 0);
                return $account_number;
        }

        /**
         * Function for returning the default time zone
         *
         * @return      string        Returns default time zone
         */
        private function fetch_default_timezone()
        {
                return (!empty($this->sheel->config['globalserverlocale_sitetimezone']) ? $this->sheel->config['globalserverlocale_sitetimezone'] : '');
        }
        /**
         * Function for dispatching the email to new clients who signup as a guest for checkout via shipping page.
         *
         * @param       array        user array
         *
         * @return      boolean      Returns true or false
         */
        function send_email_guest_checkout($user = array())
        {
                $this->sheel->email->mail = $user['email'];
                $this->sheel->email->slng = $user['slng'];
                $this->sheel->email->get('password_renewed');
                $this->sheel->email->set(
                        array(
                                '{{username}}' => $user['username'],
                                '{{password}}' => $user['password_raw']
                        )
                );
                $this->sheel->email->send();
                return true;
        }
        /**
         * Function for dispatching the activation email to new clients who signup as a guest for checkout via shipping page.
         *
         * @param       array        user array
         *
         * @return      boolean      Returns true or false
         */
        function send_email_activation_guest_checkout($user = array())
        {
                $sql = $this->sheel->db->query("
                        SELECT user_id, first_name, last_name, phone
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $this->sheel->db->escape_string($user['email']) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->email->mail = $user['email'];
                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res['user_id']);
                        $this->sheel->email->get('registration_email_guest_checkout');
                        $this->sheel->email->set(
                                array(
                                        '{{username}}' => $user['username'],
                                        '{{password}}' => $user['password_raw'],
                                        '{{user_id}}' => $res['user_id'],
                                        '{{first_name}}' => $res['first_name'],
                                        '{{last_name}}' => $res['last_name'],
                                        '{{phone}}' => $res['phone'],
                                        '{{http_server}}' => HTTP_SERVER,
                                        '{{site_name}}' => SITE_NAME,
                                        '{{staff}}' => SITE_CONTACT,
                                        '{{link}}' => HTTPS_SERVER . 'register/activate/?u=' . urlencode($this->sheel->crypt->encrypt($res['user_id'])),
                                )
                        );
                        $this->sheel->email->send();
                        return true;
                }
                return false;
        }
        /**
         * Function for dispatching the activation email to new clients.
         *
         * @param       string       user email address
         *
         */
        function send_email_activation($useremail = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $this->sheel->db->escape_string($useremail) . "'
                                AND status = 'unverified'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->email->mail = $res['email'];
                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res['user_id']);
                        $this->sheel->email->get('registration_email');
                        $this->sheel->email->set(
                                array(
                                        '{{username}}' => $res['username'],
                                        '{{user_id}}' => $res['user_id'],
                                        '{{first_name}}' => $res['first_name'],
                                        '{{last_name}}' => $res['last_name'],
                                        '{{phone}}' => $res['phone'],
                                        '{{http_server}}' => HTTP_SERVER,
                                        '{{site_name}}' => SITE_NAME,
                                        '{{staff}}' => SITE_CONTACT,
                                        '{{link}}' => HTTPS_SERVER . 'register/activate/?u=' . urlencode($this->sheel->crypt->encrypt($res['user_id'])),
                                )
                        );
                        $this->sheel->email->send();
                        return true;
                }
                return false;
        }
        function pulldown_year($selected = '', $subtract = 0)
        {
                $html = '<select name="year" id="year" class="draw-select"><option value="">{_year}</option>';
                $years = range(date("Y"), 1900);
                $cyear = date("Y");
                foreach ($years as $value) {
                        $sel = '';
                        if (!empty($selected) and $selected == $value) {
                                $sel = ' selected="selected"';
                        }
                        if ($subtract > 0) {
                                if (($value + $subtract) > $cyear) {
                                        continue;
                                } else {
                                        $html .= '<option value="' . $value . '"' . $sel . '>' . $value . '</option>';
                                }
                        } else {
                                $html .= '<option value="' . $value . '"' . $sel . '>' . $value . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to register a new user via quick registration
         *
         * @param       array         key value pair
         *
         * @return      mixed         true/false,  1, 2 or 3
         */
        function quick($payload = array())
        {
                if ($this->sheel->common->is_username_banned($payload['qusername'], true)) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = ((!empty($this->sheel->common->username_errors[0])) ? $this->sheel->common->username_errors[0] : '{_sorry_that_username_has_been_blocked}');
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                $sqlusercheck = $this->sheel->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                        WHERE username IN ('" . $this->sheel->db->escape_string($payload['qusername']) . "')
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sqlusercheck) > 0) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_that_username_already_exists_in_our_system}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                $sqlemailcheck = $this->sheel->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                        WHERE email IN ('" . $this->sheel->db->escape_string($payload['qemail']) . "')
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sqlemailcheck) > 0) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_that_email_address_already_exists_in_our_system}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                if ($this->sheel->common->is_email_banned(trim($payload['qemail'])) or $this->sheel->common->is_email_valid(trim($payload['qemail'])) == false) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                // set defaults
                $user = array();
                $subscription = array();
                $preferences = array();
                $preferences['usertimezone'] = trim(o($_COOKIE[COOKIE_PREFIX . 'timezone']));
                $user = $this->sheel->common_location->fetch_city_state_country($payload['qzip'], $payload['qcc']);
                $user['roleid'] = ((isset($payload['roleid']) and $payload['roleid'] > 0) ? intval($payload['roleid']) : $this->sheel->config['subscriptions_defaultroleid_product']);
                $user['username'] = trim($payload['qusername']);
                $user['password'] = $payload['qpassword'];
                $user['email'] = trim(o($payload['qemail']));
                $user['address'] = ((isset($payload['qaddress'])) ? trim(o($payload['qaddress'])) : '{_unknown}');
                $user['country'] = ((isset($payload['qcc'])) ? trim(o($payload['qcc'])) : '');
                $user['city'] = ((isset($payload['qcity'])) ? trim(o($payload['qcity'])) : '');
                $user['state'] = ((isset($payload['qstate'])) ? trim(o($payload['qstate'])) : '');
                $user['zipcode'] = ((isset($payload['qzip'])) ? trim(o($payload['qzip'])) : '');
                $user['phone'] = ((empty($payload['qphone']) or !isset($payload['qphone'])) ? '{_unknown}' : trim(o($payload['qphone'])));
                $user['emailnotify'] = ((isset($payload['emailnotify'])) ? intval($payload['emailnotify']) : 0);
                if ((empty($payload['qfirstname']) or !isset($payload['qfirstname'])) or (empty($payload['qlastname']) or !isset($payload['qlastname']))) { // get first/last from username
                        $tmp = explode(' ', $payload['qfullname']);
                        if (isset($tmp[0]) and isset($tmp[1])) {
                                $user['firstname'] = trim(o($tmp[0]));
                                $user['lastname'] = (isset($tmp[2])?trim(o($tmp[1].' '.$tmp[2])):trim(o($tmp[1])));
                        } else {
                                $this->sheel->template->templateregistry['quickregister_notice'] = '{_please_enter_first_last_quick_register}';
                                $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                                return false;
                        }
                } else {
                        $user['firstname'] = trim(o($payload['qfirstname']));
                        $user['lastname'] = trim(o($payload['qlastname']));
                }


                $final = $this->build_user_datastore($user, $preferences, $subscription, $payload['output'], 0, $payload['source']);
                if (!empty($final)) {
                        set_cookie('userid', $this->sheel->crypt->encrypt($final['userid']));
                        set_cookie('username', $this->sheel->crypt->encrypt($final['username']));
                        set_cookie('password', $this->sheel->crypt->encrypt($final['password']));
                        set_cookie('lastvisit', DATETIME24H);
                        set_cookie('lastactivity', DATETIME24H);
                        switch ($final['status']) {
                                case 'active': {
                                                if (!empty($_SESSION['sheeldata']['user']['password_md5'])) {
                                                        $_SESSION['sheeldata']['user']['password'] = $_SESSION['sheeldata']['user']['password_md5'];
                                                        unset($_SESSION['sheeldata']['user']['password_md5']);
                                                }
                                                return '1';
                                        }
                                case 'unverified': {
                                                return '2';
                                        }
                                case 'moderated': {
                                                return '3';
                                        }
                        }
                }
                return false;
        }



        /**
         * Function to register a new user via mobile app registration
         *
         * @param       array         key value pair
         *
         * @return      mixed         true/false,  1, 2 or 3
         */
        function mobilequick($payload = array())
        {
                if ($this->sheel->common->is_username_banned($payload['qusername'], true)) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = ((!empty($this->sheel->common->username_errors[0])) ? $this->sheel->common->username_errors[0] : '{_sorry_that_username_has_been_blocked}');
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                $sqlusercheck = $this->sheel->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                        WHERE username IN ('" . $this->sheel->db->escape_string($payload['qusername']) . "')
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sqlusercheck) > 0) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_that_username_already_exists_in_our_system}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                $sqlemailcheck = $this->sheel->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                        WHERE email IN ('" . $this->sheel->db->escape_string($payload['qemail']) . "')
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sqlemailcheck) > 0) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_that_email_address_already_exists_in_our_system}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                if ($this->sheel->common->is_email_banned(trim($payload['qemail'])) or $this->sheel->common->is_email_valid(trim($payload['qemail'])) == false) {
                        $this->sheel->template->templateregistry['quickregister_notice'] = '{_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address}';
                        $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                        return false;
                }
                //                 if (!isset($payload['qzip']) OR (isset($payload['qzip']) AND empty($payload['qzip'])))
//                 {
//                         $this->sheel->template->templateregistry['quickregister_notice'] = '{_please_enter_shipping_to_or_from_zip}';
//                         $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
//                         return false;
//                 }
//                 if (!isset($payload['qaddress']) OR (isset($payload['qaddress']) AND empty($payload['qaddress'])))
//                 {
//                         $this->sheel->template->templateregistry['quickregister_notice'] = '{_please_enter_shipping_address}';
//                         $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
//                         return false;
//                 }
                // set defaults
                $user = array();
                $subscription = array();
                $preferences = array();
                $user = $this->sheel->common_location->fetch_city_state_country($payload['qzip'], $payload['qcc']);
                $user['roleid'] = ((isset($payload['roleid']) and $payload['roleid'] > 0) ? intval($payload['roleid']) : $this->sheel->config['subscriptions_defaultroleid_product']);
                $user['username'] = trim($payload['qusername']);
                $user['password'] = $payload['qpassword'];
                $user['dob'] = ((isset($payload['dob'])) ? trim(o($payload['dob'])) : '0000-00-00');
                $user['gender'] = ((isset($payload['gender'])) ? trim(o($payload['gender'])) : '');
                $user['email'] = trim(o($payload['qemail']));
                $user['address'] = ((isset($payload['qaddress'])) ? trim(o($payload['qaddress'])) : '{_unknown}');
                $user['address2'] = ((isset($payload['qaddress2'])) ? trim(o($payload['qaddress2'])) : '{_unknown}');
                $user['country'] = ((isset($payload['qcc'])) ? trim(o($payload['qcc'])) : '');
                $user['city'] = ((isset($payload['qcity'])) ? trim(o($payload['qcity'])) : '');
                $user['state'] = ((isset($payload['qstate'])) ? trim(o($payload['qstate'])) : '');
                $user['zipcode'] = ((isset($payload['qzip'])) ? trim(o($payload['qzip'])) : '');
                $user['currencyid'] = ((isset($payload['qcurrencyid'])) ? trim(o($payload['qcurrencyid'])) : '');
                $preferences['currencyid'] = ((isset($payload['qcurrencyid'])) ? trim(o($payload['qcurrencyid'])) : '');
                $preferences['languageid'] = $_SESSION['sheeldata']['user']['languageid'];
                $user['phone'] = ((empty($payload['qphone']) or !isset($payload['qphone'])) ? '{_unknown}' : trim(o($payload['qphone'])));
                $user['emailnotify'] = ((isset($payload['emailnotify'])) ? intval($payload['emailnotify']) : 0);
                if ((empty($payload['qfirstname']) or !isset($payload['qfirstname'])) or (empty($payload['qlastname']) or !isset($payload['qlastname']))) { // get first/last from username
                        $tmp = explode(' ', $payload['qfullname']);
                        if (isset($tmp[0]) and isset($tmp[1])) {
                                $user['firstname'] = trim(o($tmp[0]));
                                $user['lastname'] = trim(o($tmp[1]));
                        } else {
                                $this->sheel->template->templateregistry['quickregister_notice'] = '{_please_enter_first_last_quick_register}';
                                $this->quickregistererrors[] = $this->sheel->template->parse_template_phrases('quickregister_notice');
                                return false;
                        }
                } else {
                        $user['firstname'] = trim(o($payload['qfirstname']));
                        $user['lastname'] = trim(o($payload['qlastname']));
                }


                $final = $this->build_user_datastore($user, $preferences, $subscription, $payload['output'], 0, $payload['source']);

                if (!empty($final)) {
                        set_cookie('userid', $this->sheel->crypt->encrypt($final['userid']));
                        set_cookie('username', $this->sheel->crypt->encrypt($final['username']));
                        set_cookie('password', $this->sheel->crypt->encrypt($final['password']));
                        set_cookie('lastvisit', DATETIME24H);
                        set_cookie('lastactivity', DATETIME24H);
                        switch ($final['status']) {
                                case 'active': {
                                                if (!empty($_SESSION['sheeldata']['user']['password_md5'])) {
                                                        $_SESSION['sheeldata']['user']['password'] = $_SESSION['sheeldata']['user']['password_md5'];
                                                        unset($_SESSION['sheeldata']['user']['password_md5']);
                                                }
                                                return '1';
                                        }
                                case 'unverified': {
                                                return '2';
                                        }
                                case 'moderated': {
                                                return '3';
                                        }
                        }
                }
                return false;
        }

        /**
         * Function to register a new user via quick registration from xml-rpc
         *
         * @param       array         key value pair
         *
         * @return      mixed         true/false,  1, 2 or 3
         */
        function register($username = '', $password = '', $email = '', $firstname = '', $lastname = '', $address = '', $address2 = '', $phone = '', $country = '', $city = '', $state = '', $zipcode = '', $currency = '', $source = 'XML-RPC', $acceptsmarketing = 0, $dob, $gender)
        {
                $payload = array(
                        'qusername' => $username,
                        'qpassword' => $password,
                        // plain-text only
                        'qemail' => $email,

                        'dob' => $dob,
                        'gender' => $gender,
                        'qfirstname' => $firstname,
                        'qlastname' => $lastname,
                        'qaddress' => $address,
                        'qaddress2' => $address2,
                        'qphone' => $phone,
                        'qcountry' => $country,
                        'qcity' => $city,
                        'qstate' => $state,
                        'qzip' => $zipcode,
                        'qcurrencyid' => $currency,
                        'qcc' => $country,
                        'roleid' => '',
                        'source' => $source,
                        'emailnotify' => $acceptsmarketing,
                        'output' => 'return_userarray'
                );
                $response = $this->mobilequick($payload);
                if (!$response) {
                        if (count($this->quickregistererrors) > 0) {
                                foreach ($this->quickregistererrors as $error) {
                                        return $error;
                                }
                        }

                }
                return $response; // 1 = active, 2 = unverified, 3 = moderated
        }
}
?>