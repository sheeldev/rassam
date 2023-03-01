<?php
/**
* Subscription class to perform the majority of subscription functionality in sheel.
*
* @package      sheel\Membership\Plan
* @version      6.0.0.622
* @author       sheel
*/
class subscription_plan extends subscription
{
	/**
	* Function to deactivate a particular subscription plan for a specific user id
	*
	* @param       string         user id
	* @param       boolean        notify admin via email? (default true)
	*
	* @return      void
	*/
	function deactivate_subscription_plan($userid = 0, $notifyadmin = true, $cancelpaymentatgateway = false)
	{
		$sql = $this->sheel->db->query("
			SELECT u.id, u.recurring_profileid, u.recurring_eventid, u.recurring_gateway, u.recurring, u.recurring_other
			FROM " . DB_PREFIX . "subscription_user u
			LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND s.type = 'product'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($res['recurring'] AND !empty($res['recurring_gateway']) AND !empty($res['recurring_profileid']) AND $cancelpaymentatgateway)
			{ // cancel recurring billing at the gateway
				$this->sheel->paymentgateway->recurring()->cancel(array(
					'paymentgatewayold' => $res['recurring_gateway'],
					'profileid' => $res['recurring_profileid'],
					'eventid' => $res['recurring_eventid'],
					'otherid' => $res['recurring_other']
				));
			}
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET active = 'no'
				WHERE id = '" . $res['id'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			// move all active carts for customers of this seller as frozen and disable items from search
			$this->sheel->auction->set_frozen_flag($userid, '1');
			$this->handle_no_stores_permission($userid);
			if ($notifyadmin)
			{
				$existing = array (
					'{{user}}' => $this->sheel->fetch_user('username', $userid)
				);
				$this->sheel->email->mail = SITE_CONTACT;
				$this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
				$this->sheel->email->get('member_subscription_deactivated_admin');
				$this->sheel->email->set($existing);
				$this->sheel->email->send();
			}
			return true;
		}
		return false;
	}
	/**
	* Function to activate a particular subscription plan for a specific user id
	*
	* @param       array          payload array()
	* @param       boolean        notify admin via email? (default true)
	* 			      payload:
	* @param       integer        user id
	* @param       string         start date
	* @param       string         renew date
	* @param       boolean        is recurring? (default false)
	* @param       integer        invoice id
	* @param       integer        subscription id
	* @param       string         payment method
	* @param       integer        role id
	* @param       string         cost
	* @param       string         payment method identifier (i.e: VISA#4111)
	*
	* @return      void
	*/
	function activate_subscription_plan($payload = array(), $notifyadmin = true)
	{
		$userid              = isset($payload['userid'])                     ? intval($payload['userid'])                   : '0';
		$startdate           = isset($payload['startdate'])                  ? $payload['startdate']                        : DATETIME24H;
                $renewdate           = isset($payload['renewdate'])                  ? $payload['renewdate']                        : '';
		$recurring           = isset($payload['recurring'])                  ? intval($payload['recurring'])                : '0';
		$recurring_profileid = isset($payload['recurring_profileid'])        ? $payload['recurring_profileid']              : '';
		$recurring_eventid   = isset($payload['recurring_eventid'])          ? $payload['recurring_eventid']                : '';
		$recurring_other     = isset($payload['recurring_other'])            ? $payload['recurring_other']                  : '';
		$recurring_gateway   = isset($payload['recurring_gateway'])          ? $payload['recurring_gateway']                : '';
		$invoiceid           = isset($payload['invoiceid'])                  ? intval($payload['invoiceid'])                : '0';
		$invoiceidpublic     = isset($payload['invoiceidpublic'])            ? $payload['invoiceidpublic']                  : '';
                $subscriptionid      = isset($payload['subscriptionid'])             ? intval($payload['subscriptionid'])           : '0';
		$paymethod           = isset($payload['paymethod'])                  ? $payload['paymethod']                        : '';
                $roleid              = isset($payload['roleid'])                     ? intval($payload['roleid'])                   : '0';
                $cost                = isset($payload['cost'])                       ? $payload['cost']                             : '0';
		$ccnum_hidden        = isset($payload['ccnum_hidden'])               ? $payload['ccnum_hidden']                     : '';
		$tax                 = isset($payload['tax'])                        ? $payload['tax']                              : '0';
		$taxinfo             = isset($payload['taxinfo'])                    ? $payload['taxinfo']                          : '';
		$transactionfee      = isset($payload['transactionfee'])             ? $payload['transactionfee']                   : '0';
		// do we already have a membership in the database for this member?
		$sql = $this->sheel->db->query("
			SELECT u.id
			FROM " . DB_PREFIX . "subscription_user u
			LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND s.type = 'product'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{ // we do ! let's change and activate it with the invoice just generated from the calling script..
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET active = 'yes',
				cancelled = '0',
				startdate = '" . $this->sheel->db->escape_string($startdate) . "',
				renewdate = '" . $this->sheel->db->escape_string($renewdate) . "',
				recurring = '" . intval($recurring) . "',
				recurring_profileid = '" . $this->sheel->db->escape_string($recurring_profileid) . "',
				recurring_eventid = '" . $this->sheel->db->escape_string($recurring_eventid) . "',
				recurring_other = '" . $this->sheel->db->escape_string($recurring_other) . "',
				recurring_gateway = '" . $this->sheel->db->escape_string($recurring_gateway) . "',
				roleid = '" . intval($roleid) . "',
				subscriptionid = '" . intval($subscriptionid) . "',
				invoiceid = '" . intval($invoiceid) . "',
				autopayment = '1',
				autorenewal = '1',
				paymethod = '" . $this->sheel->db->escape_string($paymethod) . "'
				WHERE id = '" . $res['id'] . "'
			", 0, null, __FILE__, __LINE__);
		}
		else
		{ // we will create a new membership entry for this user
			$this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "subscription_user
				(id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, autorenewal, active, cancelled, recurring, recurring_profileid, recurring_eventid, recurring_other, recurring_gateway, invoiceid, roleid)
				VALUES(
				NULL,
				'" . intval($subscriptionid) . "',
				'" . intval($userid) . "',
				'" . $this->sheel->db->escape_string($paymethod) . "',
				'" . $this->sheel->db->escape_string($startdate) . "',
				'" . $this->sheel->db->escape_string($renewdate) . "',
				'1',
				'1',
				'yes',
				'0',
				'" . intval($recurring) . "',
				'" . $this->sheel->db->escape_string($recurring_profileid) . "',
				'" . $this->sheel->db->escape_string($recurring_eventid) . "',
				'" . $this->sheel->db->escape_string($recurring_other) . "',
				'" . $this->sheel->db->escape_string($recurring_gateway) . "',
				'" . intval($invoiceid) . "',
				'" . intval($roleid) . "')
			", 0, null, __FILE__, __LINE__);
		}
		// unset frozen membership status for items in cart and search
		$this->sheel->auction->set_frozen_flag($userid, '0');
		$this->handle_no_stores_permission($userid);
		$existing = array(
			'{{provider}}' => $this->sheel->fetch_user('username', intval($userid)),
			'{{invoice_id}}' => $invoiceidpublic,
			'{{invoice_amount}}' => $this->sheel->currency->format($cost),
			'{{paymethod}}' => ((!empty($ccnum_hidden)) ? $ccnum_hidden : $paymethod),
			'{{startdate}}' => $startdate,
			'{{renewdate}}' => $renewdate,
			'{{subscriptionid}}' => $subscriptionid,
			'{{roleid}}' => $roleid,
			'{{plantitle}}' => $this->sheel->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($subscriptionid) . "'", "title_" . $_SESSION['sheeldata']['user']['slng']),
			'{{billcycle}}' => $this->sheel->common->print_date($startdate, 'm/d/Y') . ' - ' . $this->sheel->common->print_date($renewdate, 'm/d/Y'),
			'{{nextbilldate}}' => $this->sheel->common->print_date($renewdate, 'm/d/Y'),
			'{{subtotal}}' => $this->sheel->currency->format($cost - $tax - $transactionfee),
			'{{tax}}' => $this->sheel->currency->format($tax),
			'{{taxbit}}' => $taxinfo,
			'{{transactionfee}}' => $this->sheel->currency->format($transactionfee),
			'{{total}}' => $this->sheel->currency->format($cost),
			'{{paid}}' => $this->sheel->currency->format($cost),
			'{{gateway}}' => $recurring_gateway,
			'{{profileid}}' => $recurring_profileid,
			'{{eventid}}' => $recurring_eventid
		);
		$this->sheel->email->mail = $this->sheel->fetch_user('email', intval($userid));
		$this->sheel->email->slng = $this->sheel->language->fetch_user_slng(intval($userid));
		$this->sheel->email->get('membership_payment_completed');
		$this->sheel->email->set($existing);
		$this->sheel->email->send();
		if ($notifyadmin)
		{
			$this->sheel->email->mail = SITE_CONTACT;
			$this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
			$this->sheel->email->get('membership_payment_completed_admin');
			$this->sheel->email->set($existing);
			$this->sheel->email->send();
		}
		return true;
	}
	/**
	* Function to cancel a particular membership plan for a specific user id
	*
	* @param       integer        user id
	* @param       boolean        notify admin via email? (default true)
	*
	* @return      void
	*/
	function cancel_subscription_plan($userid = 0, $notifyadmin = true)
	{
		$sql = $this->sheel->db->query("
			SELECT u.id, u.invoiceid, u.recurring_profileid, u.recurring_eventid, u.recurring_gateway, u.recurring, u.recurring_other
			FROM " . DB_PREFIX . "subscription_user u
			LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND s.type = 'product'
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET cancelled = '1',
				autopayment = '0'
				WHERE id = '" . $res['id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($res['recurring'] AND !empty($res['recurring_gateway']) AND !empty($res['recurring_profileid']))
			{ // cancel recurring billing at the gateway
				$this->sheel->paymentgateway->recurring()->cancel(array(
					'paymentgatewayold' => $res['recurring_gateway'],
					'profileid' => $res['recurring_profileid'],
					'eventid' => $res['recurring_eventid'],
					'otherid' => $res['recurring_other']
				));
			}
			if ($res['recurring'] AND (empty($res['recurring_gateway']) AND empty($res['recurring_profileid'])))
			{
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "subscription_user
					SET recurring = '0'
					WHERE id = '" . $res['id'] . "'
				", 0, null, __FILE__, __LINE__);
			}
			$this->handle_no_stores_permission($userid);
			$existing = array (
				'{{user}}' => $_SESSION['sheeldata']['user']['username'],
				'{{comment}}' => $this->sheel->GPC['comment']
			);
			$this->sheel->email->mail = $_SESSION['sheeldata']['user']['email'];
			$this->sheel->email->slng = $_SESSION['sheeldata']['user']['slng'];
			$this->sheel->email->get('member_cancelled_subscription');
			$this->sheel->email->set($existing);
			$this->sheel->email->send();
			if ($notifyadmin)
			{
				$this->sheel->email->mail = SITE_CONTACT;
				$this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
				$this->sheel->email->get('member_cancelled_subscription_admin');
				$this->sheel->email->set($existing);
				$this->sheel->email->send();
			}
			return true;
		}
		return false;
	}
	/**
	* Function to determine if a user's subscription is cancelled based on a supplied user id
	*
	* @param       string         user id
	*
	* @return      boolean        Returns true if cancelled, false if not
	*/
	function is_subscription_cancelled($userid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT u.cancelled
			FROM " . DB_PREFIX . "subscription_user u
			LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
			WHERE u.user_id = '" . intval($userid) . "'
				AND s.type = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return intval($res['cancelled']);
		}
		return 0;
	}
	/**
	* Function to determine if a subscription plan's permission is setup or not
	*
	* @param       string         subscription id
	*
	* @return      boolean        Returns true if ready, false if not
	*/
	function is_subscription_permissions_ready($subscriptiongroupid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT COUNT(*) AS permissioncount
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($res['permissioncount'] > 0)
			{
				return true;
			}
		}
		return false;
	}
	/**
	* Function to add valid subscription permissions into the subscription datastore
	*
	* @param       string         access group
	* @param       string         access name
	* @param       string         access type
	* @param       string         access mode
	* @param       string         access default value
	* @param       boolean        can access permission be removed? (default true)
	*
	*/
	function add_subscription_permissions($accessgroup = '', $accessname = '', $accesstype = '', $accessmode = '', $value = '', $canremove = 1, $visible = 1)
	{
		$sql = $this->sheel->db->query("
			SELECT id
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE accessname = '" . $this->sheel->db->escape_string($accessname) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			return false;
		}
		else
		{
			$sqlcreate = $this->sheel->db->query("
				SELECT subscriptiongroupid, canremove
				FROM " . DB_PREFIX . "subscription_group
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sqlcreate) > 0)
			{
				while ($res = $this->sheel->db->fetch_array($sqlcreate, DB_ASSOC))
				{
					if ($this->is_subscription_permissions_ready($res['subscriptiongroupid']))
					{
						$this->sheel->db->query("
							INSERT INTO " . DB_PREFIX . "subscription_permissions
							(`id`, `subscriptiongroupid`, `accessgroup`, `accessname`, `accesstype`, `accessmode`, `value`, `canremove`, `original`, `iscustom`, `visible`)
							VALUES(
							NULL,
							'" . $res['subscriptiongroupid'] . "',
							'" . $this->sheel->db->escape_string($accessgroup) . "',
							'" . $this->sheel->db->escape_string($accessname) . "',
							'" . $this->sheel->db->escape_string($accesstype) . "',
							'" . $this->sheel->db->escape_string($accessmode) . "',
							'" . $this->sheel->db->escape_string($value) . "',
							'" . intval($canremove) . "',
							'1',
							'0',
							'" . intval($visible) . "')
						", 0, null, __FILE__, __LINE__);
					}
				}
				return true;
			}
		}
	}
	/**
        * Function to display subscription plans within a pulldown menu element
        *
        * @return      string         Returns HTML pulldown menu element
        */
        function options()
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$array = array();
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title, cost, length, units
                        FROM " . DB_PREFIX . "subscription
                        WHERE type = 'product'
			ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
			$array[''] = '{_select_plan} &ndash;';
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
                        {
                                $array[$res['subscriptionid']] = stripslashes(o($res['title']));
                        }
                }
                return $array;
        }
}
?>
