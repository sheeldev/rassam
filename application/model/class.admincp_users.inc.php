<?php
class admincp_users extends admincp
{
	/**
	* Function to remove single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for removal
	* @param        bool            remove invoices for user? (default no)
	* @param        bool            remove listings for user? (default no)
	* @param        bool            remove escrow for user? (default no)
	* @param        bool            remove buynow orders for user? (default no)
	* @param        bool            remove bids for user? (default no)
	* @param        bool            slient mode (send email to user default false)
	*
	* @return       string          Returns HTML string of users removed separated by a comma for display purposes
	*/
	function remove_user($ids = array(), $silentmode = false)
	{
		$errors = $allerrors = $removedusers = $successids = $failedids = '';
		$removeduserscount = 0;
		$status = '{_removed}';
		foreach ($ids AS $inc => $userid)
		{
			$errors = '';
			$sql = $this->sheel->db->query("
				SELECT isadmin, email, username, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($res['isadmin'] == '1')
				{
					$sql2 = $this->sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE user_id != '" . intval($userid) . "'
							AND isadmin = '1'
					", 0, null, __FILE__, __LINE__);
					if ($this->sheel->db->num_rows($sql2) == 0)
					{
						$errors .= '1';
						$failedids .= "$userid~";
						$allerrors .= '{_could_not_delete_x_at_least_1_admin::' . $res['username'] . '}|';
					}
				}
				if (empty($errors))
				{
					$removedusers .= $res['username'] . ', ';
					$removeduserscount++;
					
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "audit WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "emaillog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "sessions WHERE userid = '" . intval($userid) . "' AND isuser = '1'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "users WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "email_optout WHERE email = '" . $this->sheel->db->escape_string($res['email']) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "bulk_sessions WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp_measurements WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp_sizes WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$this->sheel->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp_staffs WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$successids .= "$userid~";
					if (!$silentmode)
					{
						$this->sheel->email->mail = $res['email'];
						$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
						$this->sheel->email->get('admin_changed_user_status');
						$this->sheel->email->set(array(
							'{{username}}' => $res['username'],
							'{{user_id}}' => $userid,
							'{{first_name}}' => $res['first_name'],
							'{{last_name}}' => $res['last_name'],
							'{{status}}' => $status
						));
						$this->sheel->email->send();
					}
				}
			}
		}
		$action = '{_removed_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$actionplural = (($removeduserscount == 1) ? '{_users_lc}' : '{_users_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $removeduserscount . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($removeduserscount > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($removeduserscount > 0) ? 'User(s) removed successfully' : 'Failure removing user(s)'), (($removeduserscount > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
		return array('success' => (($removeduserscount > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
	}
	/**
	* Function to suspend single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for suspension
	*
	* @return       string          Returns HTML string of users suspended separated by a comma for display purposes
	*/
	function suspend_user($ids = array())
	{
		$suspendusers = $successids = $failedids = '';
		$status = '{_suspended}';
		$count = 0;
		foreach ($ids AS $inc => $userid)
		{
			$sql = $this->sheel->db->query("
				SELECT username, email, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$suspendusers .= $res['username'] . ', ';
				$count++;

				// suspend the user
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'suspended'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);

				$successids .= "$userid~";

				// remove the session (in case user is logged in)
				$this->sheel->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				$this->sheel->email->mail = $res['email'];
				$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
				$this->sheel->email->get('admin_changed_user_status');
				$this->sheel->email->set(array(
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$this->sheel->email->send();
			}
			else
			{
				$failedids .= "$userid~";
			}
		}
		$action = '{_suspended_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$this->sheel->template->templateregistry['errors'] = (($count <= 0) ? '{_no_users_were_selected_for_removal_please_try_again}' : '');
		$actionplural = (($count == 1) ? '{_users_lc}' : '{_users_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'User(s) suspended successfully' : 'Failure suspending user(s)'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $this->sheel->template->parse_template_phrases('errors')));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => (($count <= 0) ? $this->sheel->template->parse_template_phrases('errors') : ''), 'successids' => $successids, 'failedids' => $failedids);
	}
	/**
	* Function to unsuspend single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for unsuspension
	*
	* @return       string          Returns HTML string of users unsuspended separated by a comma for display purposes
	*/
	function unsuspend_user($ids = array())
	{
		$unsuspendusers = $successids = $failedids = '';
		$status = '{_unsuspended}';
		$count = 0;
		foreach ($ids AS $inc => $userid)
		{
			$sql = $this->sheel->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$unsuspendusers .= $res['username'] . ', ';
				$successids .= "$userid~";
				$count++;

				// suspend the user
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'active'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);

				$this->sheel->email->mail = $res['email'];
				$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
				$this->sheel->email->get('admin_changed_user_status');
				$this->sheel->email->set(array(
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$this->sheel->email->send();

				// remove the session (in case user is logged in)
				$this->sheel->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				$failedids .= "$userid~";
			}
		}
		$action = '{_unsuspended_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$this->sheel->template->templateregistry['errors'] = (($count <= 0) ? '{_no_users_were_selected_for_removal_please_try_again}' : '');
		$actionplural = (($count == 1) ? '{_customer_lc}' : '{_customers_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Users unsuspended successfully' : 'Failure unsuspending users'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $this->sheel->template->parse_template_phrases('errors')));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => (($count <= 0) ? $this->sheel->template->parse_template_phrases('errors') : ''), 'successids' => $successids, 'failedids' => $failedids);
	}

	function activate_user($ids = array(), $givesignupbonus = false)
	{
		$activatedusers = $successids = $failedids = '';
		$count = 0;
		foreach ($ids AS $inc => $userid)
		{
			$sql = $this->sheel->db->query("
				SELECT status, email, username, first_name, last_name, phone
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($res['status'] == 'moderated')
				{
					$categories = '';
					$getcats = $this->sheel->db->query("
						SELECT cid, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
						FROM " . DB_PREFIX . "categories
						WHERE parentid = '0'
							AND cattype = 'product'
							AND visible = '1'
						ORDER BY title_" . $_SESSION['sheeldata']['user']['slng'] . " ASC
						LIMIT 10
					", 0, null, __FILE__, __LINE__);
					if ($this->sheel->db->num_rows($getcats) > 0)
					{
						while ($res_p = $this->sheel->db->fetch_array($getcats, DB_ASSOC))
						{
							$categories .= $res_p['title'] . LINEBREAK;
						}
					}
					// user is moderated and admin is now validating him
					$this->sheel->email->mail = $res['email'];
					$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
					$this->sheel->email->get('register_welcome_email');
					$this->sheel->email->set(array(
						'{{username}}' => $res['username'],
						'{{user_id}}' => $userid,
						'{{first_name}}' => $res['first_name'],
						'{{last_name}}' => $res['last_name'],
						'{{phone}}' => $res['phone'],
						'{{categories}}' => $categories
					));
					$this->sheel->email->send();
					// additionally, we'll run our account bonus function so this email is also dispatched
					$registerbonus = '0.00';
					if ($this->sheel->config['registrationupsell_bonusactive'] AND $givesignupbonus)
					{
						// lets construct a little payment bonus for new member, we will:
						// - create a transaction and send email to user and admin
						$registerbonus = $this->sheel->accounting->construct_account_bonus($userid, 'active');
						if ($registerbonus > 0)
						{ // update income reported
							$this->sheel->db->query("
								UPDATE " . DB_PREFIX . "users
								SET income_reported = '" . $this->sheel->db->escape_string($registerbonus) . "',
								income_spent = '0.00'
								WHERE user_id = '" . intval($userid) . "'
							");
						}
					}
				}

				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'active'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);

				$activatedusers .= $res['username'] . ', ';
				$successids .= "$userid~";
				$count++;
			}
			else
			{
				$failedids .= "$userid~";
			}
		}
		$action = '{_activated_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$this->sheel->template->templateregistry['errors'] = (($count <= 0) ? '{_no_users_were_selected_for_removal_please_try_again}' : '');
		$actionplural = (($count == 1) ? '{_users_lc}' : '{_users_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Users activated successfully' : 'Failure activating users'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $this->sheel->template->parse_template_phrases('errors')));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => (($count <= 0) ? $this->sheel->template->parse_template_phrases('errors') : ''), 'successids' => $successids, 'failedids' => $failedids);
	}
	/**
	* Function to ban single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
	function ban_user($ids = array ())
	{
		$bannedusers = $successids = $failedids = '';
		$status = '{_banned}';
		$count = 0;
		foreach ($ids AS $inc => $userid)
		{
			$sql = $this->sheel->db->query("
				SELECT username, email, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$bannedusers .= $res['username'] . ', ';
				$successids .= "$userid~";
				$count++;

				// ban this user
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'banned'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);

				// remove the session (in case user is logged in)
				$this->sheel->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				$this->sheel->email->mail = $res['email'];
				$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
				$this->sheel->email->get('admin_changed_user_status');
				$this->sheel->email->set(array(
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$this->sheel->email->send();
			}
			else
			{
				$failedids .= "$userid~";
			}
		}
		$action = '{_banned_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$this->sheel->template->templateregistry['errors'] = (($count <= 0) ? '{_no_users_were_selected_for_removal_please_try_again}' : '');
		$actionplural = (($count == 1) ? '{_users_lc}' : '{_users_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Users banned successfully' : 'Failure banning users'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $this->sheel->template->parse_template_phrases('errors')));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => (($count <= 0) ? $this->sheel->template->parse_template_phrases('errors') : ''), 'successids' => $successids, 'failedids' => $failedids);
	}
	/**
	* Function to unban single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
	function unban_user($ids = array())
	{
		$bannedusers = $successids = $failedids = '';
		$status = '{_unbanned}';
		$count = 0;
		foreach ($ids AS $inc => $userid)
		{
			$sql = $this->sheel->db->query("
				SELECT username, email, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$bannedusers .= $res['username'] . ', ';
				$successids .= "$userid~";
				$count++;

				// unban this user
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'active'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);

				// remove the session (in case user is logged in)
				$this->sheel->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				$this->sheel->email->mail = $res['email'];
				$this->sheel->email->slng = $this->sheel->language->fetch_user_slng($userid);
				$this->sheel->email->get('admin_changed_user_status');
				$this->sheel->email->set(array(
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$this->sheel->email->send();
			}
			else
			{
				$failedids .= "$userid~";
			}
		}
		$action = '{_unbanned_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$this->sheel->template->templateregistry['errors'] = (($count <= 0) ? '{_no_users_were_selected_for_removal_please_try_again}' : '');
		$actionplural = (($count == 1) ? '{_users_lc}' : '{_users_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Users unbanned successfully' : 'Failure unbanning users'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $this->sheel->template->parse_template_phrases('errors')));
		return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => (($count <= 0) ? $this->sheel->template->parse_template_phrases('errors') : ''), 'successids' => $successids, 'failedids' => $failedids);
	}

	function construct_new_member($username = '', $customerid, $roleid, $password = '', $salt = '', $email = '', $first = '', $last = '', $address = '', $address2 = '', $city = '', $state = '', $zipcode = '', $phone = '', $country = '', $dob = '',  $languageid = '', $currencyid = '', $usertimezone = '', $isadmin = 0)
	{

       
		$ipaddress = IPADDRESS;
		$unh = $this->sheel->crypt->encrypt($username);
		$usernameslug = $this->sheel->seo->construct_seo_url_name($username);
		$this->sheel->db->query("
			INSERT INTO " . DB_PREFIX . "users
			(user_id, ipaddress, customerid, roleid, username, usernameslug, usernamehash, password, salt,  email, first_name, last_name, address, address2, city, state, zip_code, phone, country, date_added, status, dob, languageid, currencyid, timezone,  emailnotify, isadmin)
			VALUES(
			NULL,
			'" . $this->sheel->db->escape_string($ipaddress) . "',
            '" . $this->sheel->db->escape_string($customerid) . "',
            '" . $this->sheel->db->escape_string($roleid) . "',
			'" . $this->sheel->db->escape_string($username) . "',
			'" . $this->sheel->db->escape_string($usernameslug) . "',
			'" . $this->sheel->db->escape_string($unh) . "',
			'" . $this->sheel->db->escape_string($password) . "',
			'" . $this->sheel->db->escape_string($salt) . "',
			'" . $this->sheel->db->escape_string($email) . "',
			'" . $this->sheel->db->escape_string($first) . "',
			'" . $this->sheel->db->escape_string($last) . "',
			'" . $this->sheel->db->escape_string($address) . "',
			'" . $this->sheel->db->escape_string($address2) . "',
			'" . $this->sheel->db->escape_string($city) . "',
			'" . $this->sheel->db->escape_string($state) . "',
			'" . $this->sheel->db->escape_string($zipcode) . "',
			'" . $this->sheel->db->escape_string($phone) . "',
			'" . intval($country) . "',
			'" . DATETIME24H . "',
			'active',
			'" . $this->sheel->db->escape_string($dob) . "',
			'" . intval($languageid) . "',
			'" . intval($currencyid) . "',
			'" . $this->sheel->db->escape_string($usertimezone) . "',
			'1',
			'" . intval($isadmin) . "')
		", 0, null, __FILE__, __LINE__);
		unset($unh);
		$user_id = $this->sheel->db->insert_id();
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), 'User created successfully', "A new user '$first $last' was created successfully.");
		return $user_id;
	}
	/**
	* Function to remove single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for action
	* @param        bool            slient mode (send email to user default false)
	*
	* @return       string          Returns HTML string of users removed separated by a comma for display purposes
	*/
	function reject_user_delete_request($ids = array(), $silentmode = false)
	{
		$errors = $allerrors = $successids = $failedids = '';
		$rejecteduserscount = 0;
		$status = '{_rejected}';
		foreach ($ids AS $inc => $userid)
		{
			$errors = '';
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "users
				SET requestdeletion = '0'
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$rejecteduserscount++;
		}
		$action = '{_rejected_lc}';
		$this->sheel->template->templateregistry['action'] = $action;
		$actionplural = (($rejecteduserscount == 1) ? '{_customer_deletion_request_lc}' : '{_customer_deletion_requests_lc}');
		$this->sheel->template->templateregistry['actionplural'] = $actionplural;
		$success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $rejecteduserscount . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
		$this->sheel->template->templateregistry['success'] = $success;
		$this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($rejecteduserscount > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($rejecteduserscount > 0) ? 'User deletion requests rejected successfully' : 'Failure rejecting user deletion requests'), (($rejecteduserscount > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
		return array('success' => (($rejecteduserscount > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
	}
	/**
	* Function to generate a unique user account number for the billing and payments system
	*
	* @return	string        Returns a formatted account number
	*/
	function construct_account_number()
	{
	        $first = rand(100, 999);
	        $second = rand(100, 999);
	        $third = rand(100, 999);
	        $fourth = rand(100, 999);
	        $fifth = rand(0, 9);
	        return $first . $second . $third . $fourth . $fifth;
	}
	
	function get_user_id($accountnumber)
	{
	    $uid=0;
	    $sql = $this->sheel->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE account_number = '" . $accountnumber . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
	        if ($this->sheel->db->num_rows($sql) > 0)
	        {
	            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
	            $uid = $res['user_id'];
	        }
	        else
	        {
	            $uid=0;
	        }
	    return $uid;
	 }
	
	 function get_user_profile_id($userid)
	 {
	     $spid=0;
	     $sql = $this->sheel->db->query("
				SELECT id
				FROM " . DB_PREFIX . "shipping_profiles
				WHERE user_id = '" . $userid . "' AND type='shipping' AND isdefault='1'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
	     if ($this->sheel->db->num_rows($sql) > 0)
	     {
	         $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
	         $spid = $res['id'];
	     }
	     else
	     {
	         $spid=0;
	     }
	     return $spid;
	 }
	 function get_user_account($uid)
	 {
	     $acc='';
	     $sql = $this->sheel->db->query("
				SELECT account_number
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $uid . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
	     if ($this->sheel->db->num_rows($sql) > 0)
	     {
	         $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
	         $acc = $res['account_number'];
	     }
	     else
	     {
	         $acc='';
	     }
	     return $acc;
	 }
}
?>
