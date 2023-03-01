<?php
define('LOCATION', 'registration');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
    'header' => array(
        'functions',
        'vendor/jquery_' . JQUERYVERSION,
        'vendor/lazyload',
        'vendor/growl',
        'vendor/jquery_mask',
        'inline',
        'register',
        'password_strength',
        'vendor/jquery_tablesaw'
        //'vendor/jquery_ui'
    ),
    'footer' => array(
        'v5',
        'others'

    )
);
$sheel->template->meta['cssinclude'] = array(
    'vendor' => array(
        'bootstrap3.3.5',
        'font-awesome',
        'spinner',
        'tablesaw',
        'growl',
        'breadcrumb',
        'slidein',
        'color'
    ),
    'general',
    'theme',
    'timeline'
);
$sheel->template->meta['area'] = 'registration';
// #### setup default breadcrumb ###############################################
$sheel->template->meta['navcrumb'] = array(HTTPS_SERVER . 'register/' => '{_registration}');
$sheel->template->meta['areatitle'] = '{_user_registration}';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_user_registration}';
$sheel->show['nobreadcrumb'] = true;
$sheel->show['slimheader'] = true;
$sheel->show['slimfooter'] = true;
// #### REDIRECTION HANDLER ####################################################
if (isset($sheel->GPC['redirect']) and !empty($sheel->GPC['redirect'])) {
    $sheel->GPC['redirect'] = strip_tags($sheel->GPC['redirect']);
    $_SESSION['sheeldata']['user']['new_redirect'] = ((!isset($_SESSION['sheeldata']['user']['new_redirect'])) ? $sheel->GPC['redirect'] : '');
}
if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'activate') { // new member email verification process
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'resend') { // member requests that sheel resend their email link code verification
        if (!empty($sheel->GPC['email'])) { // resend email activation code to member
            if ($sheel->registration->send_email_activation($sheel->GPC['email'])) {
                refresh(HTTPS_SERVER . 'signin/?error=checkemail');
                exit();
            } else {
                refresh(HTTPS_SERVER . 'signin/?error=1');
                exit();
            }
        } else {
            refresh(HTTPS_SERVER . 'signin/?error=1');
            exit();
        }
    } else {
        if (!empty($sheel->GPC['u'])) { // member appears to be validating his/her registration
            $sheel->GPC['u'] = $sheel->crypt->decrypt(urldecode($sheel->GPC['u']));
            if (is_numeric($sheel->GPC['u'])) {
                $sql = $sheel->db->query("
					SELECT user_id, email, username, first_name, last_name, phone, status
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($sheel->GPC['u']) . "'
					LIMIT 1
				");
                if ($sheel->db->num_rows($sql) > 0) {
                    $user = $sheel->db->fetch_array($sql, DB_ASSOC);
                    if ($user['status'] == 'unverified') { // user is still unverified
                        // does admin manually verify new members before they can login?
                        $status = (($sheel->config['registrationdisplay_moderation']) ? 'moderated' : 'active');
                        $sheel->db->query("
							UPDATE " . DB_PREFIX . "users
							SET status = '" . $sheel->db->escape_string($status) . "'
							WHERE user_id = '" . intval($sheel->GPC['u']) . "'
							LIMIT 1
						");
                        // if we are active, send new email to user
                        if ($status == 'active') {
                            // if an account credit bonus was active we should dispatch that email to new user now
                            // and update his account balance with new credit accordingly
                            $registerbonus = '0.00';
                            // lets construct a little payment bonus for new member, we will:
                            // - create a transaction and send email to user and admin
                            // - return the bonus amount so we can update the users account
                            // - now handles points system if enabled for signup points bonus
                            $registerbonus = $sheel->accounting->construct_account_bonus(intval($sheel->GPC['u']), $status);
                            if ($registerbonus > 0) { // update income reported
                                $sheel->db->query("
									UPDATE " . DB_PREFIX . "users
									SET income_reported = '" . $sheel->db->escape_string($registerbonus) . "',
									income_spent = '0.00'
									WHERE user_id = '" . intval($sheel->GPC['u']) . "'
								");
                            }
                            $categories = '';
                            $getcats = $sheel->db->query("
								SELECT cid, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
								FROM " . DB_PREFIX . "categories
								WHERE parentid = '0'
									AND cattype = 'product'
									AND visible = '1'
								ORDER BY title_" . $_SESSION['sheeldata']['user']['slng'] . " ASC
								LIMIT 10
							", 0, null, __FILE__, __LINE__);
                            if ($sheel->db->num_rows($getcats) > 0) {
                                while ($res = $sheel->db->fetch_array($getcats, DB_ASSOC)) {
                                    $categories .= "$res[title]\n";
                                }
                            }
                            // admin activates new members after their email link code verification
                            // so in this case, let's dispatch a new welcome email to the member
                            $sheel->email->mail = $user['email'];
                            $sheel->email->slng = $sheel->language->fetch_user_slng($user['user_id']);
                            $sheel->email->get('register_welcome_email');
                            $sheel->email->set(
                                array(
                                    '{{username}}' => $user['username'],
                                    '{{user_id}}' => $user['user_id'],
                                    '{{first_name}}' => $user['first_name'],
                                    '{{last_name}}' => $user['last_name'],
                                    '{{phone}}' => $user['phone'],
                                    '{{categories}}' => $categories
                                )
                            );
                            $sheel->email->send();
                        }
                        // dispatch email to admin
                        $sheel->email->mail = SITE_CONTACT;
                        $sheel->email->slng = $sheel->language->fetch_site_slng();
                        $sheel->email->get('register_welcome_email_admin');
                        $sheel->email->set(
                            array(
                                '{{username}}' => $user['username'],
                                '{{user_id}}' => $user['user_id'],
                                '{{first_name}}' => $user['first_name'],
                                '{{last_name}}' => $user['last_name'],
                                '{{phone}}' => $user['phone'],
                                '{{emailaddress}}' => $user['email'],
                                '{{ipaddress}}' => IPADDRESS,
                                '{{country}}' => (!empty($_SERVER['GEOIP_COUNTRY']) ? $_SERVER['GEOIP_COUNTRY'] : '-'),
                                '{{city}}' => (!empty($_SERVER['GEOIP_CITY']) ? $_SERVER['GEOIP_CITY'] : '-'),
                                '{{state}}' => (!empty($_SERVER['GEOIP_STATE']) ? $_SERVER['GEOIP_STATE'] : '-'),
                                '{{zipcode}}' => (!empty($_SERVER['GEOIP_ZIPCODE']) ? $_SERVER['GEOIP_ZIPCODE'] : '-')
                            )
                        );
                        $sheel->email->send();
                        if ($status == 'active') {
                            $redirect = (isset($sheel->GPC['redirect'])) ? 'signin/?redirect=' . urlencode($sheel->GPC['redirect']) . '&note=complete' : 'signin/?note=complete';
                            refresh(HTTPS_SERVER . $redirect);
                            exit();
                        } else {
                            // display thanks for verifying email, admin will moderate you shortly ..
                            // at this point the user still has not been sent the welcome to the marketplace
                            // nor has he received any "account bonus" credit for signing up
                            // these emails will be dispatched from the admin control panel
                            $redirect = (isset($sheel->GPC['redirect'])) ? 'signin/?redirect=' . urlencode($sheel->GPC['redirect']) . '&error=' . $status : 'signin/?error=' . $status;
                            refresh(HTTPS_SERVER . $redirect);
                            exit();
                        }
                    } else { // user is not unverified refresh url showing the user his/her status
                        refresh(HTTPS_SERVER . 'signin/?error=' . $user['status']); // active, suspended, cancelled, unverified, banned, moderated
                        exit();
                    }
                } else {
                    refresh(HTTPS_SERVER . 'signin/?error=1');
                    exit();
                }
            } else {
                refresh(HTTPS_SERVER . 'signin/?error=crypt');
                exit();
            }

        } else {
            refresh(HTTPS_SERVER . 'signin/?error=1');
            exit();
        }
    }
}
// are we returning to registration from a previous invitation or registration attempt?
if (!empty($_COOKIE[COOKIE_PREFIX . 'invitedid']) and $_COOKIE[COOKIE_PREFIX . 'invitedid'] > 0) {
    $_SESSION['sheeldata']['user']['invited'] = 1;
    $_SESSION['sheeldata']['user']['invitedid'] = intval($_COOKIE[COOKIE_PREFIX . 'invitedid']);
} else { // are we being externally invited?
    if (isset($sheel->GPC['invited']) and $sheel->GPC['invited'] and isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) { // member has clicked link from listing page
        $_SESSION['sheeldata']['user']['invited'] = 1;
        $_SESSION['sheeldata']['user']['invitedid'] = intval($sheel->GPC['id']);
        set_cookie('invitedid', intval($sheel->GPC['id']));
    }
}
// are we a guest trying to register for an auction event?
if (isset($sheel->GPC['eventid']) and $sheel->GPC['eventid'] > 0) { // member has clicked "register for event" link from auctions page as guest
    set_cookie('eventid', intval($sheel->GPC['eventid']));
}
if (isset($sheel->GPC['view']) and !empty($sheel->GPC['view'])) {
    $onclick = 'location.href=\'' . HTTPS_SERVER . 'signin/\'';
    $returnurl = ((isset($sheel->GPC['returnurl'])) ? urldecode($sheel->GPC['returnurl']) : HTTPS_SERVER . 'signin/');
    if ($sheel->GPC['view'] == 'welcome') {
        $sheel->template->meta['navcrumb'][] = "{_completed}";
        $onclick = 'location.href=\'' . $returnurl . '\'';
    } else if ($sheel->GPC['view'] == 'verification') {
        $sheel->template->meta['navcrumb'][] = "{_verification}";
        $onclick = 'location.href=\'' . $returnurl . '\'';
    } else if ($sheel->GPC['view'] == 'moderation') {
        $sheel->template->meta['navcrumb'][] = "{_moderation}";
        $onclick = 'location.href=\'' . $returnurl . '\'';
    }
    unset($returnurl);
    $sheel->template->fetch_popup('main', 'register3.html');
    $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
    $sheel->template->pprint('main', array('onclick' => $onclick, 'header_text' => '{_registration}'));
    exit();
}
if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and !isset($sheel->GPC['step'])) {
    $sheel->print_notice_popup('{_already_registered}', '{_sorry_already_registered}', HTTPS_SERVER, '{_main_menu}');
    exit();
}
if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'register') {
    if (!isset($sheel->GPC['step'])) {
        $sheel->GPC['step'] = 1;
        set_cookie('userid', '', false);
        set_cookie('password', '', false);
    }
    if ($sheel->GPC['step'] == '1') { // step 1

        $sheel->template->meta['navcrumb'] = array();
        $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
        $sheel->template->meta['navcrumb'][""] = '{_account}';
        $sheel->show['nobreadcrumb'] = true;
        $sheel->show['error_username'] = $sheel->show['error_username2'] = $sheel->show['error_email'] = $sheel->show['error_email_blocked'] = $sheel->show['error_turing'] = false;
        $defaultroleid = '';
        if (isset($sheel->GPC['agreement']) and $sheel->GPC['agreement']) {
            $_SESSION['sheeldata']['user']['agreeterms'] = 1;
        }
        if (isset($sheel->GPC['marketing']) and $sheel->GPC['marketing']) {
            $_SESSION['sheeldata']['user']['emailnotify'] = 1;
        } else {
            $_SESSION['sheeldata']['user']['emailnotify'] = 0;
        }
        $_SESSION['sheeldata']['user']['dob'] = ((!empty($sheel->GPC['year']) and !empty($sheel->GPC['month']) and !empty($sheel->GPC['day']) and $sheel->config['registrationdisplay_dob']) ? intval($sheel->GPC['year']) . '-' . intval($sheel->GPC['month']) . '-' . intval($sheel->GPC['day']) : '0000-00-00');
        if ($sheel->config['registrationdisplay_dob']) {
            if ($sheel->config['registrationdisplay_dobunder18'] == 0) {
                if ($sheel->GPC['year'] > (date('Y') - 18) or ($sheel->GPC['year'] == (date('Y') - 18) and $sheel->GPC['month'] < date('m')) or ($sheel->GPC['year'] == (date('Y') - 18) and $sheel->GPC['month'] == date('m') and $sheel->GPC['day'] < date('d'))) {
                    $sheel->print_notice('{_you_must_be_over_18}', '{_were_sorry_you_must_be_over_the_age_of_18_to_register_on_this_marketplace}', HTTPS_SERVER, '{_main_menu}');
                    exit();
                }
            }
        }
        if (!empty($_SESSION['sheeldata']['user']['agreeterms']) and $_SESSION['sheeldata']['user']['agreeterms']) {
            $sqlrolepulldown = $sheel->db->query("
				SELECT roleid, purpose_" . $_SESSION['sheeldata']['user']['slng'] . " AS purpose, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, custom, roletype, roleusertype, active
				FROM " . DB_PREFIX . "roles
			", 0, null, __FILE__, __LINE__);
            $rerole = $sheel->db->fetch_array($sqlrolepulldown, DB_ASSOC);
            $rolesql = $sheel->db->num_rows($sqlrolepulldown);
            if ($rolesql > 1) {
                $sheel->show['rolescount'] = true;
            } else {
                $sheel->show['rolescount'] = false;
                if ($rolesql == 1) { // only 1 visible role available, use it as default since we don't show the pull down if there is only one available
                    $sheel->GPC['roleid'] = $rerole['roleid'];
                    $defaultroleid = $sheel->GPC['roleid'];
                } else { // all plans hidden from registration..
                    if (isset($sheel->config['subscriptions_defaultroleid_product']) and $sheel->config['subscriptions_defaultroleid_product'] > 0) {
                        $sheel->GPC['roleid'] = $sheel->config['subscriptions_defaultroleid_product'];
                        $defaultroleid = $sheel->GPC['roleid'];
                    } else {
                        $sheel->GPC['roleid'] = '-1';
                        $defaultroleid = $sheel->GPC['roleid'];
                    }
                }
            }
            $vars = array(
                'defaultroleid' => $defaultroleid,
                'rolepulldown' => $sheel->role->print_role_pulldown(0, 1, 0, 0, '', $_SESSION['sheeldata']['user']['slng'], 'draw-select', 'roleid', 'roleid'),
                'captcha' => '<img style="background-color:#FDB819; opacity: 0.5;" src="' . HTTPS_SERVER . 'attachment/captcha/" alt="captcha" border="0" align="center" />',
                'password' => '',
                'password2' => '',
                'username' => '',
                'email' => '',
                'header_text' => '{_registration}'
            );
            $sheel->template->fetch_popup('main', 'register1.html');
            $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
            $sheel->template->pprint('main', $vars);
            exit();
        } else {
            $sheel->template->meta['areatitle'] = '{_registration}<div class="type--subdued">{_terms}</div>';
            $sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_registration_terms_and_agreements_review}';
            $sheel->template->meta['navcrumb'] = array();
            $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
            $sheel->template->meta['navcrumb'][""] = '{_terms}';
            $sheel->show['nobreadcrumb'] = true;
            $days = array('' => '{_day}');
            for ($i = 1; $i <= 31; $i++) {
                $val = ($i < 10) ? '0' . $i : $i;
                $days[$val] = $i;
            }
            $vars = array(
                'registration1' => nl2br($sheel->db->fetch_field(DB_PREFIX . "content", "isterms = '1'", "description")),
                'yearpulldown' => $sheel->registration->pulldown_year('', 18),
                'daypulldown' => $sheel->construct_pulldown('day', 'day', $days, '', 'class="draw-select"'),
                'monthpulldown' => $sheel->construct_pulldown('month', 'month', array('' => '{_month}', '01' => '{_january}', '02' => '{_february}', '03' => '{_march}', '04' => '{_april}', '05' => '{_may}', '06' => '{_june}', '07' => '{_july}', '08' => '{_august}', '09' => '{_september}', '10' => '{_october}', '11' => '{_november}', '12' => '{_december}'), '', 'class="draw-select"'),
                'header_text' => '{_registration}'
            );
            $sheel->template->fetch_popup('main', 'registration.html');
            $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
            $sheel->template->pprint('main', $vars);
            exit();
        }
    } else if ($sheel->GPC['step'] == '2') { // step 2
        $sqlrolepulldown = $sheel->db->query("
			SELECT roleid, purpose_" . $_SESSION['sheeldata']['user']['slng'] . " AS purpose, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, custom, roletype, roleusertype, active
			FROM " . DB_PREFIX . "roles
			WHERE active = '1'
		", 0, null, __FILE__, __LINE__);
        $rerole = $sheel->db->fetch_array($sqlrolepulldown, DB_ASSOC);
        $rolesql = $sheel->db->num_rows($sqlrolepulldown);
        if (isset($rolesql) and $rolesql > 1) {
            $sheel->show['rolescount'] = true;
        } else {
            $sheel->show['rolescount'] = false;
            if ($rolesql == 1) { // only 1 visible role available, use it as default since we don't show the pull down if there is only one available
                $sheel->GPC['roleid'] = $rerole['roleid'];
                $defaultroleid = $sheel->GPC['roleid'];
            } else { // all plans hidden from registration..
                if (isset($sheel->config['subscriptions_defaultroleid_product']) and $sheel->config['subscriptions_defaultroleid_product'] > 0) {
                    $sheel->GPC['roleid'] = $sheel->config['subscriptions_defaultroleid_product'];
                    $defaultroleid = $sheel->GPC['roleid'];
                } else {
                    $sheel->GPC['roleid'] = '-1';
                    $defaultroleid = $sheel->GPC['roleid'];
                }
            }
        }
        $sheel->template->meta['navcrumb'] = array();
        $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
        $sheel->template->meta['navcrumb'][""] = '{_contact}';
        $sheel->show['nobreadcrumb'] = true;
        $sheel->show['error_username'] = $sheel->show['error_username2'] = $sheel->show['error_password'] = $sheel->show['error_email'] = $sheel->show['error_email_blocked'] = $sheel->show['error_turing'] = $sheel->show['error_role'] = false;
        $username = $password = $password2 = $email = $username_blocked_note =  $username_exist_note = '';

        if (isset($sheel->GPC['roleid']) and $sheel->GPC['roleid'] > 0) {
            $_SESSION['sheeldata']['user']['roleid'] = intval($sheel->GPC['roleid']);
        } else {
            if (isset($sheel->config['subscriptions_defaultroleid_product']) and $sheel->config['subscriptions_defaultroleid_product'] > 0) {
                $_SESSION['sheeldata']['user']['roleid'] = $sheel->config['subscriptions_defaultroleid_product'];
                $sheel->show['error_role'] = false;
            } else {
                $_SESSION['sheeldata']['user']['roleid'] = $defaultroleid;
                $sheel->show['error_role'] = true;
            }
        }
        // #### username checkup #######################################
        if (isset($sheel->GPC['username']) and $sheel->GPC['username'] != '') { // todo: move to ajax checker so username input can show checkmark or bad icon for issue..
            if ($sheel->common->is_username_banned($sheel->GPC['username'])) {
                $sheel->show['error_username2'] = true;
                $username_blocked_note = ((!empty($sheel->common->username_errors[0])) ? $sheel->common->username_errors[0] : '{_sorry_that_username_has_been_blocked}');
                $username = $sheel->GPC['username'];
            } else {
                $sqlusercheck = $sheel->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "users
                                        WHERE username IN ('" . $sheel->db->escape_string($sheel->GPC['username']) . "')
                                ");
                if ($sheel->db->num_rows($sqlusercheck) > 0) {
                    $sheel->show['error_username'] = true;
                    $username_exist_note = '{_were_sorry_that_username_is_taken}';
                    $username = stripslashes(strip_tags(trim($sheel->GPC['username'])));
                } else {
                    $_SESSION['sheeldata']['user']['username'] = trim($sheel->GPC['username']);
                    $username = $_SESSION['sheeldata']['user']['username'];
                }
            }
        } else {
            $sheel->show['error_username'] = true;
        }
        // #### password checkup #######################################
        if (isset($sheel->GPC['password']) and $sheel->GPC['password'] != '' and isset($sheel->GPC['password2']) and $sheel->GPC['password2'] != '') {
            $password = trim($sheel->GPC['password']);
            $password2 = trim($sheel->GPC['password2']);
            if ($sheel->GPC['password'] != $sheel->GPC['password2']) {
                $sheel->show['error_password'] = true;
            } else {
                $_SESSION['sheeldata']['user']['salt'] = $sheel->construct_password_salt(5);
                $_SESSION['sheeldata']['user']['password_md5'] = md5(md5($sheel->GPC['password']) . $_SESSION['sheeldata']['user']['salt']);
            }
        } else {
            $sheel->show['error_password'] = true;
        }

        // #### email checkup ##########################################
        if (isset($sheel->GPC['email']) and $sheel->GPC['email'] != '') {
            $email = trim($sheel->GPC['email']);
            if ($sheel->common->is_email_banned(trim($sheel->GPC['email']))) {
                $sheel->show['error_email_blocked'] = true;
            }
            // email is good check if it's duplicate
            $sqlemailcheck = $sheel->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
                                WHERE email = '" . $sheel->db->escape_string(trim($sheel->GPC['email'])) . "'
                        ");
            if ($sheel->db->num_rows($sqlemailcheck) > 0) {
                $sheel->show['error_email'] = true;
            } else {
                $_SESSION['sheeldata']['user']['email'] = trim(o($sheel->GPC['email']));
            }
        }
        if ($sheel->config['registrationdisplay_turingimage']) { // admin use captcha
            if (isset($sheel->GPC['turing']) and $sheel->GPC['turing'] != '' and !empty($_SESSION['sheeldata']['user']['captcha'])) { // user supplied turing captcha
                $turing = mb_strtoupper(trim($sheel->GPC['turing']));
                if ($turing != $_SESSION['sheeldata']['user']['captcha']) {
                    $sheel->show['error_turing'] = true;
                }
            } else {
                $sheel->show['error_turing'] = true;
            }
        }
        if ($sheel->show['error_username'] or $sheel->show['error_username2'] or $sheel->show['error_password'] or $sheel->show['error_email'] or $sheel->show['error_email_blocked'] or $sheel->show['error_turing'] or $sheel->show['error_role']) { // errors! back to step 1
            $sheel->show['nobreadcrumb'] = true;
            $sheel->template->meta['navcrumb'] = array();
            $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
            $sheel->template->meta['navcrumb'][''] = '{_account}';

            $roleselected = ((isset($sheel->GPC['roleid']) and $sheel->GPC['roleid'] > 0) ? intval($sheel->GPC['roleid']) : $sheel->config['subscriptions_defaultroleid_product']);
            $vars = array(
                'defaultroleid' => $roleselected,
                'rolepulldown' => $sheel->role->print_role_pulldown($roleselected, 1, 0, 0, '', $_SESSION['sheeldata']['user']['slng'], 'draw-select', 'roleid', 'roleid'),
                'password' => $password,
                'password2' => $password2,
                'captcha' => '<img style="background-color:#FDB819;opacity: 0.5;" src="' . HTTPS_SERVER . 'attachment/captcha/" alt="captcha" border="0" />',
                'username' => $username,
                'email' => $email,
                'header_text' => '{_registration}',
                'username_exist_note' => $username_exist_note,
                'username_blocked_note' => ((!empty($username_blocked_note)) ? $username_blocked_note : '')
            );
            $sheel->template->fetch_popup('main', 'register1.html');
            $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
            $sheel->template->pprint('main', $vars);
            exit();
        } else { // step 2
            $sheel->template->meta['navcrumb'] = array();
            $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
            $sheel->template->meta['navcrumb'][""] = '{_contact}';
            $sheel->show['nobreadcrumb'] = true;
            $geodata = $sheel->common_location->fetch_city_state_country('', '');
            $countryid = $sheel->common_location->fetch_country_id($geodata['country'], $_SESSION['sheeldata']['user']['slng']);
            $vars = array(
                'currency_pulldown' => $sheel->currency->pulldown('registration', '', 'draw-select'),
                'city_js_pulldown' => '<div id="cityid">' . $sheel->common_location->construct_city_pulldown('', 'city', '', false, true, 'draw-select', true, 'w-214', true, '') . '</div>',
                'state_js_pulldown' => '<div id="stateid">' . $sheel->common_location->construct_state_pulldown($countryid, '', 'state', false, true, 0, 'draw-select', 0, 'city', 'cityid') . '</div>',
                'country_js_pulldown' => $sheel->common_location->construct_country_pulldown($countryid, $geodata['country'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid'),
                'cb_gender_undecided' => 'checked="checked"',
                'cb_gender_male' => '',
                'cb_gender_female' => '',
                'address' => '',
                'address2' => '',
                'zipcode' => '',
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'cc' => ((isset($_SERVER['GEOIP_COUNTRYCODE']) and !empty($_SERVER['GEOIP_COUNTRYCODE'])) ? $_SERVER['GEOIP_COUNTRYCODE'] : $sheel->common_location->fetch_country_id($sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng'])),
                'country' => $geodata['country'],
                'header_text' => '{_registration}'
            );


            $sheel->template->fetch_popup('main', 'register2.html');
            $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
            $sheel->template->pprint('main', $vars);
            exit();
        }
    } else if ($sheel->GPC['step'] == '3') { // step 3
        $sheel->template->meta['navcrumb'] = array();
        $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
        $sheel->template->meta['navcrumb'][""] = '{_user_registration}';
        $sheel->show['nobreadcrumb'] = true;
        $_SESSION['sheeldata']['user']['gender'] = (isset($sheel->GPC['gender'])) ? $sheel->GPC['gender'] : '';
        $_SESSION['sheeldata']['preferences']['languageid'] = $_SESSION['sheeldata']['user']['languageid'];
        $_SESSION['sheeldata']['preferences']['notifyproducts'] = 0;
        $error_firstname = $error_lastname = $error_phone = $error_address = $error_zipcode = 0;
        $phone = $city = $country = $state = $first_name = $last_name = $address = $address2 = $zipcode = '';

        if (empty($sheel->GPC['phone'])) {
            if ($sheel->config['requirephonenumber']) {
                $error_phone = 1;
            } else {
                $_SESSION['sheeldata']['user']['phone'] = '';
            }
        } else {
            $_SESSION['sheeldata']['user']['phone'] = trim(o($sheel->GPC['phone']));
            $phone = trim(o($sheel->GPC['phone']));
        }
        if (isset($sheel->GPC['first_name']) and !empty($sheel->GPC['first_name'])) {
            $_SESSION['sheeldata']['user']['firstname'] = ucwords(trim(o($sheel->GPC['first_name'])));
            $first_name = $_SESSION['sheeldata']['user']['firstname'];
        } else {
            $error_firstname = 1;
        }
        if (isset($sheel->GPC['last_name']) and !empty($sheel->GPC['last_name'])) {
            $_SESSION['sheeldata']['user']['lastname'] = ucwords(trim(o($sheel->GPC['last_name'])));
            $last_name = $_SESSION['sheeldata']['user']['lastname'];
        } else {
            $error_lastname = 1;
        }
        if (isset($sheel->GPC['address']) and !empty($sheel->GPC['address'])) {
            $_SESSION['sheeldata']['user']['address'] = ucwords(trim(o($sheel->GPC['address'])));
            $address = $_SESSION['sheeldata']['user']['address'];
        } else {
            $error_address = 1;
        }
        if (isset($sheel->GPC['address2']) and !empty($sheel->GPC['address2'])) {
            $_SESSION['sheeldata']['user']['address2'] = ucwords(trim(o($sheel->GPC['address2'])));
            $address2 = $_SESSION['sheeldata']['user']['address2'];
        } else {
            $_SESSION['sheeldata']['user']['address2'] = '';
        }
        if (isset($sheel->GPC['zipcode']) and !empty($sheel->GPC['zipcode'])) {
            $sheel->common_location->fetch_city_state_country($sheel->GPC['zipcode'], $sheel->GPC['cc']); // <-- $sheel->show['error_zip_code_api']
            if (isset($sheel->show['error_zip_code_api']) and $sheel->show['error_zip_code_api']) {
                $zipcode = trim(o($sheel->GPC['zipcode']));
                $error_zipcode = 1;
            } else {
                $_SESSION['sheeldata']['user']['zipcode'] = trim(o($sheel->GPC['zipcode']));
                $zipcode = $_SESSION['sheeldata']['user']['zipcode'];
            }
        } else {
            $error_zipcode = 1;
        }
        if (empty($sheel->config['googlemapsgeocodingkey'])) { // from input fields
            if (isset($sheel->GPC['city']) and !empty($sheel->GPC['city'])) {
                $_SESSION['sheeldata']['user']['city'] = ucwords(o(trim($sheel->GPC['city'])));
                $city = $_SESSION['sheeldata']['user']['city'];
            }
            if (isset($sheel->GPC['state']) and !empty($sheel->GPC['state'])) {
                $_SESSION['sheeldata']['user']['state'] = o(trim($sheel->GPC['state']));
                $state = $_SESSION['sheeldata']['user']['state'];
            }
            if (isset($sheel->GPC['country']) and !empty($sheel->GPC['country'])) {
                $_SESSION['sheeldata']['user']['country'] = o(trim($sheel->GPC['country']));
                $_SESSION['sheeldata']['user']['countryid'] = $sheel->common_location->fetch_country_id($_SESSION['sheeldata']['user']['country'], $_SESSION['sheeldata']['user']['slng']);
                $country = $_SESSION['sheeldata']['user']['country'];
            }
        } else { // from zipcode field
            $geodata = $sheel->common_location->fetch_city_state_country($zipcode, $sheel->GPC['cc']);
            $_SESSION['sheeldata']['user']['city'] = ((isset($geodata['city']) and isset($sheel->GPC['city']) and !empty($sheel->GPC['city']) and $geodata['city'] != $sheel->GPC['city']) ? ucwords(o(trim($sheel->GPC['city']))) : $geodata['city']);
            $_SESSION['sheeldata']['user']['state'] = ((isset($geodata['state']) and isset($sheel->GPC['state']) and !empty($sheel->GPC['state']) and $geodata['state'] != $sheel->GPC['state']) ? o(trim($sheel->GPC['state'])) : $geodata['state']);
            $_SESSION['sheeldata']['user']['country'] = ((isset($geodata['country']) and isset($sheel->GPC['country']) and !empty($sheel->GPC['country']) and $geodata['country'] != $sheel->GPC['country']) ? o(trim($sheel->GPC['country'])) : $geodata['country']);
            $_SESSION['sheeldata']['user']['countryid'] = $sheel->common_location->fetch_country_id($_SESSION['sheeldata']['user']['country'], $_SESSION['sheeldata']['user']['slng']);
            $city = $_SESSION['sheeldata']['user']['city'];
            $state = $_SESSION['sheeldata']['user']['state'];
            $country = $_SESSION['sheeldata']['user']['country'];
        }
        if (isset($sheel->GPC['currencyid']) and $sheel->GPC['currencyid'] > 0) { // currency
            $_SESSION['sheeldata']['preferences']['currencyid'] = intval($sheel->GPC['currencyid']);
        }

        if (isset($_COOKIE[COOKIE_PREFIX . 'timezone']) and !empty($_COOKIE[COOKIE_PREFIX . 'timezone'])) { // timezone
            $_SESSION['sheeldata']['preferences']['usertimezone'] = trim(o($_COOKIE[COOKIE_PREFIX . 'timezone']));
        } else {
            $_SESSION['sheeldata']['preferences']['usertimezone'] = $sheel->config['globalserverlocale_sitetimezone'];
        }
        if (isset($sheel->GPC['companyname']) and !empty($sheel->GPC['companyname'])) { // company name
            $_SESSION['sheeldata']['preferences']['companyname'] = trim(o($sheel->GPC['companyname']));
        } else {
            $_SESSION['sheeldata']['preferences']['companyname'] = '';
        }
        if ($error_firstname or $error_lastname or $error_address or $error_zipcode or $error_phone) { // errors! back to step 2
            $sheel->template->meta['navcrumb'] = array();
            $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
            $sheel->template->meta['navcrumb'][""] = '{_contact}';
            $sheel->show['nobreadcrumb'] = true;
            $countryid = $sheel->common_location->fetch_country_id($country, $_SESSION['sheeldata']['user']['slng']);
            $cb_gender_male = $cb_gender_female = '';
            if (isset($sheel->GPC['gender']) and $sheel->GPC['gender'] == '') {
                $cb_gender_undecided = 'checked="checked"';
                $cb_gender_male = $cb_gender_female = '';
            } else {
                if (isset($sheel->GPC['gender']) and $sheel->GPC['gender'] == 'male') {
                    $cb_gender_undecided = $cb_gender_female = '';
                    $cb_gender_male = 'checked="checked"';
                } else if (isset($sheel->GPC['gender']) and $sheel->GPC['gender'] == 'female') {
                    $cb_gender_undecided = $cb_gender_male = '';
                    $cb_gender_female = 'checked="checked"';
                }
            }

            $vars = array(
                'cb_gender_undecided' => (isset($cb_gender_undecided) ? $cb_gender_undecided : ''),
                'cb_gender_male' => (isset($cb_gender_male) ? $cb_gender_male : ''),
                'cb_gender_female' => (isset($cb_gender_female) ? $cb_gender_female : ''),
                'address' => $address,
                'address2' => $address2,
                'zipcode' => $zipcode,
                'currency_pulldown' => $sheel->currency->pulldown('registration', ((isset($sheel->GPC['currencyid'])) ? intval($sheel->GPC['currencyid']) : ''), 'draw-select'),
                'city_js_pulldown' => '<div id="cityid">' . $sheel->common_location->construct_city_pulldown($state, 'city', $city, false, true, 'draw-select', true, 'w-355', true, 'w-355') . '</div>',
                'state_js_pulldown' => '<div id="stateid">' . $sheel->common_location->construct_state_pulldown($countryid, $state, 'state', false, true, 0, 'draw-select', 0, 'city', 'cityid') . '</div>',
                'country_js_pulldown' => $sheel->common_location->construct_country_pulldown($countryid, $country, 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'draw-select', false, false, '', 0, 'city', 'cityid'),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => (isset($phone) ? o($phone) : ''),
                'cc' => ((isset($_SERVER['GEOIP_COUNTRYCODE']) and !empty($_SERVER['GEOIP_COUNTRYCODE'])) ? $_SERVER['GEOIP_COUNTRYCODE'] : $sheel->common_location->fetch_country_id($sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng'])),
                'country' => $geodata['country'],
                'header_text' => '{_registration}'
            );

            $sheel->template->fetch_popup('main', 'register2.html');
            $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
            $sheel->template->pprint('main', $vars);
            exit();
        } else { //final step
            set_cookie('userid', '', false);
            set_cookie('password', '', false);
            set_cookie('username', '', false);
            if (!isset($_SESSION['sheeldata']['user']['agreeterms'])) { // session expired
                refresh(HTTPS_SERVER . 'register/?note=expired');
                exit();
            }
            $sheel->template->meta['navcrumb'] = array();
            $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
            $sheel->template->meta['navcrumb'][""] = '{_registration}';
            $sheel->show['nobreadcrumb'] = true;
            if (isset($sheel->GPC['subscriptionid']) and $sheel->GPC['subscriptionid'] > 0) {
                $_SESSION['sheeldata']['subscription']['subscriptionid'] = intval($sheel->GPC['subscriptionid']);
                $_SESSION['sheeldata']['subscription']['subscriptionpaymethod'] = mb_strtolower(trim($sheel->GPC['paymethod']));
            } else {
                $_SESSION['sheeldata']['subscription']['subscriptionid'] = $sheel->config['subscriptions_defaultplanid_product'];
                $_SESSION['sheeldata']['subscription']['subscriptionpaymethod'] = 'account';
            }
            if (!empty($sheel->GPC['promocode'])) { // support promotional code feature
                $_SESSION['sheeldata']['subscription']['promocode'] = trim(o($sheel->GPC['promocode']));
            } else {
                $_SESSION['sheeldata']['subscription']['promocode'] = '';
            }
            $dowhat = 'return_userarray';
            $final = $sheel->registration->build_user_datastore($_SESSION['sheeldata']['user'], $_SESSION['sheeldata']['preferences'], $_SESSION['sheeldata']['subscription'], $dowhat);
            if (!empty($final)) {
                set_cookie('username', $sheel->crypt->encrypt($final['username']));
                set_cookie('lastvisit', DATETIME24H);
                set_cookie('lastactivity', DATETIME24H);
                if (isset($_SESSION['sheeldata']['user']['new_redirect'])) {
                    unset($_SESSION['sheeldata']['user']['new_redirect']);
                }
                switch ($final['status']) {
                    case 'active': { // display final registration information
                            set_cookie('userid', $sheel->crypt->encrypt($final['userid']));
                            set_cookie('password', $sheel->crypt->encrypt($final['password']));
                            if (!empty($_SESSION['sheeldata']['user']['password_md5'])) {
                                $_SESSION['sheeldata']['user']['password'] = $_SESSION['sheeldata']['user']['password_md5'];
                                unset($_SESSION['sheeldata']['user']['password_md5']);
                            }
                            refresh(HTTPS_SERVER . 'register/welcome/' . ((isset($final['redirect'])) ? '?returnurl=' . urlencode($final['redirect']) : '?returnurl=' . urlencode(HTTPS_SERVER . 'account/')));
                            break;
                        }
                    case 'unverified': { // display email link code information
                            session_unset();
                            $sheel->sessions->session_destroy(session_id());
                            session_destroy();
                            refresh(HTTPS_SERVER . 'register/verification/' . ((isset($final['redirect'])) ? '?returnurl=' . urlencode(HTTPS_SERVER . 'signin/?redirect=' . $final['redirect']) : '?returnurl=' . urlencode(HTTPS_SERVER . 'signin/')));
                            break;
                        }
                    case 'moderated': { // display email link code information
                            session_unset();
                            $sheel->sessions->session_destroy(session_id());
                            session_destroy();
                            refresh(HTTPS_SERVER . 'register/moderation/' . ((isset($final['redirect'])) ? '?returnurl=' . urlencode($final['redirect']) : '?returnurl=' . urlencode(HTTP_SERVER)));
                            break;
                        }
                }
                exit();
            } else {
                $sheel->print_notice('{_registration_error_occured}', '{_were_sorry_we_only_allow_forms_to_be_securely_processed_via_our_web_site}', HTTPS_SERVER . 'register/', '{_registration}');
                exit();
            }

  
        }
    } 
} else {
    $sheel->template->meta['areatitle'] = '{_registration}<div class="smaller">{_terms}</div>';
    $sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_registration}';
    $sheel->show['nobreadcrumb'] = true;
    $sheel->template->meta['navcrumb'] = array();
    $sheel->template->meta['navcrumb'][HTTPS_SERVER . 'register/'] = '{_registration}';
    $sheel->template->meta['navcrumb'][""] = '{_terms}';
    $days = array('' => '{_day}');
    for ($i = 1; $i <= 31; $i++) {
        $val = ($i < 10) ? '0' . $i : $i;
        $days[$val] = $i;
    }
    $vars = array(
        'registration1' => nl2br($sheel->db->fetch_field(DB_PREFIX . "content", "isterms = '1'", "description")),
        'yearpulldown' => $sheel->registration->pulldown_year('', 18),
        'daypulldown' => $sheel->construct_pulldown('day', 'day', $days, '', 'class="draw-select"'),
        'monthpulldown' => $sheel->construct_pulldown('month', 'month', array('' => '{_month}', '01' => '{_january}', '02' => '{_february}', '03' => '{_march}', '04' => '{_april}', '05' => '{_may}', '06' => '{_june}', '07' => '{_july}', '08' => '{_august}', '09' => '{_september}', '10' => '{_october}', '11' => '{_november}', '12' => '{_december}'), '', 'class="draw-select"'),
        'header_text' => '{_registration}'

    );
    $sheel->template->fetch_popup('main', 'registration.html');
    $sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
    $sheel->template->pprint('main', $vars);
    exit();
}
?>