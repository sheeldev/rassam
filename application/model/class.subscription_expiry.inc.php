<?php
if (!class_exists('subscription')) {
	exit;
}

/**
 * Subscription expiry class to perform the majority of subscription expiration functionality in sheel.
 *
 * @package      sheel\Membership\Expiry
 * @version      1.0.0.0
 * @author       sheel
 */
class subscription_expiry extends subscription
{
	/**
	 * Function to expire user membership plans as required (parsed from cron.1minute.php)
	 */
	function user_subscription_plans()
	{
		$notice = $cancelledplans = $recurringplans = $all = 0;
		$slng = $this->sheel->language->fetch_site_slng();
		$subscriptioncheck = $this->sheel->db->query("
                        SELECT c.customer_id, c.customer_ref, c.customername, s.cost, s.title_" . $slng . " AS title, s.description_" . $slng . " AS description, s.length, s.units, sc.id, sc.subscriptionid, sc.paymethod, sc.autopayment AS subscription_autopayment, sc.active,  sc.autorenewal, sc.recurring, sc.cancelled
                        FROM " . DB_PREFIX . "subscription_customer AS sc
						LEFT JOIN " . DB_PREFIX . "customers c ON (sc.customerid = c.customer_id)
						LEFT JOIN " . DB_PREFIX . "subscription s ON (sc.subscriptionid = s.subscriptionid)
								WHERE sc.renewdate <= '" . DATETODAY . " " . TIMENOW . "'
						AND c.status = 'active'
						AND sc.active = 'yes'
						AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($subscriptioncheck) > 0) {
			while ($res_subscription_check = $this->sheel->db->fetch_array($subscriptioncheck, DB_ASSOC)) { // we have active plans that need to EXPIRE
				if ($res_subscription_check['cancelled'] == '1') { // users who have cancelled and don't want to be rebilled
					$this->sheel->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id'], false, true);
					$cancelledusernames .= $res_subscription_check['username'] . ', ';
					$cancelledplans++;
					$all++;
				} else if ($res_subscription_check['recurring'] == '1' or $res_subscription_check['paymethod'] != 'account') { // users who are on a recurring billing via credit card
					$this->sheel->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id'], false, false);
					$recurringusernames .= $res_subscription_check['username'] . ', ';
					$recurringplans++;
					$all++;
				}
			}
			$notice = 'subscription_expiry->user_subscription_plans(), ';
		}
		return $notice;
	}
	/**
	 * Function to expire user membership plan exemptions (parsed from cron.1minute.php)
	 */
	function user_subscription_exemptions()
	{
		$cronlog = '';
		$expiredexemptions = 0;
		$exemptionscheck = $this->sheel->db->query("
                        SELECT exemptid, customerid, accessname, value, exemptfrom, exemptto, comments, invoiceid, active
                        FROM " . DB_PREFIX . "subscription_customer_exempt
                        WHERE exemptto <= '" . DATETODAY . " " . TIMENOW . "'
				AND active = '1'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($exemptionscheck) > 0) {
			while ($exemptions = $this->sheel->db->fetch_array($exemptionscheck, DB_ASSOC)) {
				// expire subscription exemptions
				$this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_customer_exempt
                                        SET active = '0'
                                        WHERE exemptid = '" . $exemptions['exemptid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
				$expiredexemptions++;
				// send email to notify about subscription permission exemption expiry and renewal details
				// >> this will be added at a later date <<
			}
			if ($expiredexemptions > 0) {
				$cronlog .= "subscription_expiry->user_subscription_exemptions() [$expiredexemptions], ";
			}
		}
		return $cronlog;
	}
}
?>