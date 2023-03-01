<?php
/**
 * Membership class to perform the majority of membership functionality in sheel.
 *
 * @package      sheel\Membership
 * @version      6.0.0.622
 * @author       sheel
 */
class subscription
{
        protected $sheel;

        function __construct($sheel)
        {
                $this->sheel = $sheel;
        }
        /**
         * Function for processing a subscription plan payment from a previously generated unpaid subscription transaction.
         *
         * @param       integer      user id
         * @param       integer      invoice id for payment processing
         * @param       string       payment mode (ipn or account)
         * @param       string       payment gateway title which will be processing this payment (optional)
         * @param       string       payment gateway transaction id (from gateway provider) (optional)
         * @param       boolean      is this a refunded invoice payment? (default false)
         * @param       string       payment gateway original transaction id (if payment is/was refunded by gateway)
         * @param       boolean      silent mode? (return only true or false; [default false], when false, final function goals will be refreshed to appropriate pages with notices)
         *
         * @return      mixed        for ipn processing, boolean is used, others will use a print_notice() function to end user.
         */
        function payment($payload = array('invoiceid' => '0', 'invoicetype' => 'debit', 'totalamount' => '0', 'subtotal' => '0', 'tax' => '0', 'shipping' => '0', 'handling' => '0', 'transactionfee' => '0', 'userid' => '0', 'paymethod' => 'account', 'gateway' => '', 'gatewaytxn' => '', 'isrefund' => false, 'originalgatewaytxn' => '', 'bpid' => '', 'number' => '', 'cardtype' => '', 'fullname' => '', 'expiry' => '', 'cvc' => '', 'silentmode' => false, 'sendemail' => true))
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                if ($payload['paymethod'] == 'ipn') {
                        $sql = $this->sheel->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($payload['invoiceid']) . "'
                                        AND (status = 'unpaid' OR status = 'scheduled')
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "invoices
                                        SET paid = '" . $this->sheel->db->escape_string($res['totalamount']) . "',
                                        status = 'paid',
                                        paiddate = '" . DATETIME24H . "',
                                        referer = '" . $this->sheel->db->escape_string('') . "',
                                        custommessage = '" . $this->sheel->db->escape_string($payload['gatewaytxn']) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                                AND invoiceid = '" . intval($payload['invoiceid']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_company
                                        SET paymethod = 'ipn',
                                        recurring_gateway = " . $this->sheel->db->escape_string($payload['gateway']) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                                AND invoiceid = '" . intval($payload['invoiceid']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $this->sheel->accounting_payment->insert_income_spent($res['user_id'], sprintf("%01.2f", $res['totalamount']), 'credit');
                                $this->sheel->referral->update_referral_action('subscription', $res['user_id']);
                                // record transaction ledger
                                $array = array(
                                        'userid' => $res['user_id'],
                                        'credit' => sprintf("%01.2f", $res['totalamount']),
                                        'debit' => 0,
                                        'description' => '{_payment_from} {_' . $payload['gateway'] . '} {_into_account_balance_for} ' . $res['description'],
                                        'invoiceid' => $payload['invoiceid'],
                                        'transactionid' => $res['transactionid'],
                                        'custom' => $payload['gatewaytxn'],
                                        'staffnotes' => '',
                                        'technical' => 'Account balance credit based on [payment(), method=ipn] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                );
                                $this->sheel->accounting->account_balance($array);
                                // Account Debit for Buyer & Provider Membership
                                $array = array(
                                        'userid' => $res['user_id'],
                                        'credit' => 0,
                                        'debit' => sprintf("%01.2f", $res['totalamount']),
                                        'description' => 'Debit for ' . $res['description'],
                                        'invoiceid' => $payload['invoiceid'],
                                        'transactionid' => $res['transactionid'],
                                        'custom' => $payload['gatewaytxn'],
                                        'staffnotes' => '',
                                        'technical' => 'Account balance debit based on [payment(), method=ipn] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                );
                                $this->sheel->accounting->account_balance($array);
                                unset($array);
                                $sql_subscription_plan = $this->sheel->db->query("
                                        SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                                                AND type = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql_subscription_plan) > 0) {
                                        $subscription_plan_result = $this->sheel->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                        $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                        $subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_company
                                                SET active = 'yes',
                                                cancelled = '0',
                                                renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                startdate = '" . DATETIME24H . "',
                                                autopayment = '1',
                                                subscriptionid = '" . $res['subscriptionid'] . "',
                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                roleid = '" . $subscription_plan_result['roleid'] . "'
                                                WHERE user_id = '" . $res['user_id'] . "'
                                                        AND invoiceid = '" . intval($payload['invoiceid']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $this->handle_no_stores_permission($res['user_id']);
                                        $existing = array(
                                                '{{provider}}' => $this->sheel->fetch_user('username', $res['user_id']),
                                                '{{invoice_id}}' => $res['invoiceid'],
                                                '{{invoice_amount}}' => $this->sheel->currency->format($res['totalamount']),
                                        );
                                        $this->sheel->email->mail = $this->sheel->fetch_user('email', $res['user_id']);
                                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res['user_id']);
                                        $this->sheel->email->get('subscription_fee_paid_creditcard');
                                        $this->sheel->email->set($existing);
                                        if ($payload['sendemail']) {
                                                $this->sheel->email->send();
                                        }
                                        $this->sheel->email->mail = SITE_CONTACT;
                                        $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                                        $this->sheel->email->get('subscription_fee_paid_creditcard_admin');
                                        $this->sheel->email->set($existing);
                                        if ($payload['sendemail']) {
                                                $this->sheel->email->send();
                                        }
                                        return true;
                                }
                        }
                        return false;
                } else if ($payload['paymethod'] == 'account') {
                        $sql = $this->sheel->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($payload['invoiceid']) . "'
                                        AND user_id = '" . intval($payload['userid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) == 0) {
                                if ($payload['silentmode']) {
                                        return false;
                                }
                                refresh(HTTPS_SERVER . 'accounting/?note=inv:err');
                                exit();
                        }
                        $res_invoiceprice = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $totalamount = (($res_invoiceprice['istaxable'] > 0 and $res_invoiceprice['totalamount'] > 0) ? $res_invoiceprice['totalamount'] : $res_invoiceprice['amount']);
                        $sel_balance = $this->sheel->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($payload['userid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $res_balance = $this->sheel->db->fetch_array($sel_balance, DB_ASSOC);
                        if ($res_balance['available_balance'] < $totalamount) {
                                if ($payload['silentmode']) {
                                        return false;
                                }
                                refresh(HTTPS_SERVER . 'accounting/?note=inv:nsf');
                                exit();
                        }
                        // pay the membership fee invoice
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET paid = '" . $this->sheel->db->escape_string($totalamount) . "',
                                status = 'paid',
                                paiddate = '" . DATETIME24H . "',
                                paymethod = 'account',
                                referer = '" . $this->sheel->db->escape_string(REFERRER) . "' 
                                WHERE user_id = '" . intval($payload['userid']) . "'
                                        AND invoiceid = '" . intval($payload['invoiceid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->accounting_payment->insert_income_spent(intval($payload['userid']), $totalamount, 'credit');
                        $this->sheel->referral->update_referral_action('subscription', intval($payload['userid']));
                        $paymethod = 'account';
                        $_SESSION['sheeldata']['user']['active'] = 'yes';
                        $sql_subscription_plan = $this->sheel->db->query("
                                SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res_invoiceprice['subscriptionid'] . "'
                                        AND type = 'product'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql_subscription_plan) > 0) {
                                $subscription_plan_result = $this->sheel->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                $subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_company
                                        SET paymethod = 'account',
                                        startdate = '" . DATETIME24H . "',
                                        renewdate = '" . $subscription_renew_date . "',
                                        autopayment = '1',
                                        active = 'yes',
                                        cancelled = '0',
                                        subscriptionid = '" . intval($res_invoiceprice['subscriptionid']) . "',
                                        roleid = '" . $subscription_plan_result['roleid'] . "',
                                        migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                        migratelogic = '" . $subscription_plan_result['migratelogic'] . "'
                                        WHERE user_id = '" . intval($payload['userid']) . "'
                                                AND invoiceid = '" . intval($payload['invoiceid']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $_SESSION['sheeldata']['user']['subscriptionid'] = $res_invoiceprice['subscriptionid'];
                        }
                        $new_total = ($res_balance['total_balance'] - $totalamount);
                        $new_avail = ($res_balance['available_balance'] - $totalamount);
                        // update account minus subscription fee amount
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
                                total_balance = '" . sprintf("%01.2f", $new_total) . "'
                                WHERE user_id = '" . intval($payload['userid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        // record transaction ledger
                        $array = array(
                                'userid' => $payload['userid'],
                                'credit' => 0,
                                'debit' => sprintf("%01.2f", $totalamount),
                                'description' => 'Debit for ' . $res_invoiceprice['description'],
                                'invoiceid' => $payload['invoiceid'],
                                'transactionid' => $res_invoiceprice['transactionid'],
                                'custom' => '',
                                'staffnotes' => '',
                                'technical' => 'Account balance debit based on [payment(), method=account] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                        );
                        $this->sheel->accounting->account_balance($array);
                        unset($array);
                        $this->handle_no_stores_permission(intval($payload['userid']));
                        $existing = array(
                                '{{provider}}' => $this->sheel->fetch_user('username', intval($payload['userid'])),
                                '{{invoice_id}}' => intval($payload['invoiceid']),
                                '{{invoice_amount}}' => $this->sheel->currency->format($totalamount, $res_invoiceprice['currency_id']),
                        );
                        $this->sheel->email->mail = SITE_CONTACT;
                        $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                        $this->sheel->email->get('subscription_paid_online_account_admin');
                        $this->sheel->email->set($existing);
                        if ($payload['sendemail']) {
                                $this->sheel->email->send();
                        }
                        $this->sheel->email->mail = $this->sheel->fetch_user('email', intval($payload['userid']));
                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng(intval($payload['userid']));
                        $this->sheel->email->get('subscription_paid_online_account');
                        $this->sheel->email->set($existing);
                        if ($payload['sendemail']) {
                                $this->sheel->email->send();
                        }
                        if ($payload['silentmode']) {
                                return true;
                        }
                        refresh(HTTPS_SERVER . 'membership/?note=mp:cmpld');
                        exit();
                }
        }
        /**
         * Function used to obtain the time left of a membership plan down to the second.
         *
         * @param       integer        seconds left
         *
         * @return      string         Returns time left
         */
        function subscription_countdown_timeleft($countdown)
        {
                $dif = $countdown;
                $ndays = floor($dif / 86400);
                $dif -= $ndays * 86400;
                $nhours = floor($dif / 3600);
                $dif -= $nhours * 3600;
                $nminutes = floor($dif / 60);
                $dif -= $nminutes * 60;
                $nseconds = $dif;
                $sign = '+';
                if ($countdown < 0) {
                        $countdown = -$countdown;
                        $sign = '-';
                }
                if ($sign == '-') {
                        $subscription_time_left = '{_subscription_expired}';
                } else {
                        if ($ndays != '0') {
                                $subscription_time_left = $ndays . '{_d_shortform}, ';
                                $subscription_time_left .= $nhours . '{_h_shortform}+ ';
                        } elseif ($nhours != '0') {
                                $subscription_time_left = $nhours . '{_h_shortform}, ';
                                $subscription_time_left .= $nminutes . '{_m_shortform}, ';
                                $subscription_time_left .= $nseconds . '{_s_shortform}';
                        } else {
                                $subscription_time_left = $nminutes . '{_m_shortform}, ';
                                $subscription_time_left .= $nseconds . '{_s_shortform}';
                        }
                }
                $subscription_countdown = $subscription_time_left;
                return $subscription_countdown;
        }
        /**
         * Function used to obtain the time left in seconds.
         *
         * @param       integer        user id
         *
         * @return      string         Returns time left in seconds
         */
        function fetch_seconds_left($userid = 0)
        {
                $res = $this->sheel->db->query("
			SELECT UNIX_TIMESTAMP(u.renewdate) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS countdown
			FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
			WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
			LIMIT 1
                ");
                if ($this->sheel->db->num_rows($res) > 0) {
                        $row = $this->sheel->db->fetch_array($res, DB_ASSOC);
                        return $row['countdown'];
                }
                return 0;
        }
        /**
         * Function used to obtain the subscription length (in days) from a supplied unit (D/M/Y) and length (in days)
         *
         * @param       string         unit (D or M or Y)
         * @param       integer        length in days
         *
         * @return      string         Returns time left
         */
        function subscription_length($units, $length)
        {
                $days = ($length < 1 ? 1 : $length);
                switch ($units) {
                        case 'Y': {
                                        $value = 365 * intval($days);
                                        break;
                                }
                        case 'M': {
                                        $value = 30 * intval($days);
                                        break;
                                }
                        case 'D': {
                                        $value = intval($days);
                                        break;
                                }
                }
                return $value;
        }
        /**
         * Function to display any subscription alerts from my account area
         *
         * @param       integer        user id
         *
         * @return      string         Returns HTML formatted text
         */
        function alerts($userid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT u.active, u.cancelled
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        if ($res['cancelled']) {
                                $html = '{_you_have_cancelled_your_subscription_plan_your_subscription_plan_will_remain_active_until_the_expiration_date_your_account_will_not_be_billed}';
                        } else {
                                if ($res['active'] == 'no') {
                                        $html = '{_please_optin_to_a_valid_subscription_plan_to_enable_access_permissions_to_your_online_account_failing_to_optin_to_a_subscription_plan_will_not_allow_you_to_participate} <a href="' . HTTPS_SERVER . 'membership/">{_click_here_to_upgrade_your_subscription}</a>.';
                                } else {
                                        $html = '{_your_subscription_plan_is_active}' . '  <a href="' . HTTPS_SERVER . 'membership/">{_click_here_to_view_other_subscription_plans}</a>.';
                                }
                        }
                } else {
                        $html = '{_the_subscription_plan_system_is_currently_under_maintenance_and_will_be_available_shortly_thank_you_for_your_continued_patience}';
                }
                return $html;
        }
        /**
         * Function to display subscription plans within a pulldown menu element
         *
         * @return      string         Returns HTML pulldown menu element
         */
        function plans_pulldown($class = 'draw-select', $selected = '', $js = '', $formname = 'form[subscriptionid]', $formid = 'subscriptionid', $showpleaseselect = false)
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $html = '<select name="' . $formname . '" id="' . $formid . '" class="' . $class . '"' . ((!empty($js)) ? ' ' . $js : '') . '>';
                $html .= (($showpleaseselect) ? '<option value="">{_select}</option>' : '');
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title, cost, length, units
                        FROM " . DB_PREFIX . "subscription
                        WHERE type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $checked = '';
                                if (isset($selected) and $selected == $res['subscriptionid']) {
                                        $checked = ' selected="selected"';
                                }
                                $html .= '<option value="' . $res['subscriptionid'] . '"' . $checked . '>' . stripslashes(o($res['title'])) . ' (' . $res['length'] . ' ' . $this->print_unit($res['units']) . ' &ndash; ' . $this->sheel->currency->format($res['cost']) . ')</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to display for users any subscription plans within a pulldown menu element
         *
         * @return      string         Returns HTML pulldown menu element
         */
        function pulldown()
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $html = '<div class="draw-select__wrapper w-355"><select name="subscriptionid" id="subscriptionid" class="draw-select"><optgroup label="{_all_subscriptions}">';
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title
                        FROM " . DB_PREFIX . "subscription
                        WHERE type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $html .= '<option value="0">{_please_select}</option>';
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $html .= '<option value="' . $res['subscriptionid'] . '">' . stripslashes(o($res['title'])) . '</option>';
                        }
                }
                $html .= '</optgroup></select></div>';
                return $html;
        }
        /**
         * Function to display for users any subscription plans within a pulldown menu element
         *
         * @return      string         Returns HTML pulldown menu element
         */
        function admincp_plans_radios($formname = 'form_who', $showcounts = 1)
        {
                $html = '';
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
                        FROM " . DB_PREFIX . "subscription
                        WHERE type = 'product'
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $html .= '';
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $sql2 = $this->sheel->db->query("
                                        SELECT COUNT(su.user_id) AS count
                                        FROM " . DB_PREFIX . "subscription_company su
                                        LEFT JOIN " . DB_PREFIX . "users u ON (su.user_id = u.user_id)
                                        WHERE su.active = 'yes'
                                                AND su.subscriptionid = '" . $res['subscriptionid'] . "'
                                                AND u.status = 'active'
                                                AND u.emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $res2 = $this->sheel->db->fetch_array($sql2, DB_ASSOC);
                                $html .= '<div class="sb"><label for="plan_' . $res['subscriptionid'] . '"><input type="radio" value="' . $res['subscriptionid'] . '" name="' . $formname . '" id="plan_' . $res['subscriptionid'] . '"> <span class="badge badge--subdued">' . o(stripslashes($res['title'])) . ' (' . number_format($res2['count']) . ')</span></label></div>';
                        }
                        $html .= '<div class="sb"><label for="plan_orphaned"><input type="radio" value="orphaned" name="' . $formname . '" id="plan_orphaned"> <span class="badge badge--subdued">Assigned &amp; Unpaid or Expired (' . number_format($this->status_count('orphaned')) . ')</span></label></div>';
                }
                return $html;
        }
        function status_count($status = '')
        {
                switch ($status) {
                        case 'everyone': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'orphaned': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(su.user_id) AS count
                                        FROM " . DB_PREFIX . "subscription_company su
                                        LEFT JOIN " . DB_PREFIX . "users u ON (su.user_id = u.user_id)
                                        WHERE su.active = 'no'
                                                AND u.status = 'active'
                                                AND u.emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'active': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'active'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'suspended': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'suspended'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'cancelled': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'cancelled'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'unverified': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'unverified'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'banned': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'banned'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                        case 'moderated': {
                                        $sql = $this->sheel->db->query("
                                        SELECT COUNT(user_id) AS count
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'moderated'
                                                AND emailnotify = '1'
                                ", 0, null, __FILE__, __LINE__);
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        return $res['count'];
                                        break;
                                }
                }
        }
        /**
         * Function to display for users any subscription plans within a pulldown menu element
         *
         * @return      string         Returns HTML pulldown menu element
         */
        function admincp_status_radios($formname = 'form_who', $showcounts = 1)
        {
                $html = '<div class="sb"><label for="everyone"><input type="radio" value="-1" name="' . $formname . '" id="everyone" checked="checked"> <span class="badge badge--success">{_everyone} (' . number_format($this->status_count('everyone')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="active"><input type="radio" value="active" name="' . $formname . '" id="active"> <span class="badge badge--subdued">{_active} (' . number_format($this->status_count('active')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="suspended"><input type="radio" value="suspended" name="' . $formname . '" id="suspended"> <span class="badge badge--subdued">{_suspended} (' . number_format($this->status_count('suspended')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="cancelled"><input type="radio" value="cancelled" name="' . $formname . '" id="cancelled"> <span class="badge badge--subdued">{_cancelled} (' . number_format($this->status_count('cancelled')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="unverified"><input type="radio" value="unverified" name="' . $formname . '" id="unverified"> <span class="badge badge--subdued">{_email} {_unverified} (' . number_format($this->status_count('unverified')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="banned"><input type="radio" value="banned" name="' . $formname . '" id="banned"> <span class="badge badge--subdued">{_banned} (' . number_format($this->status_count('banned')) . ')</span></label></div>';
                $html .= '<div class="sb"><label for="moderated"><input type="radio" value="moderated" name="' . $formname . '" id="moderated"> <span class="badge badge--subdued">{_moderated} (' . number_format($this->status_count('moderated')) . ')</span></label></div>';
                return $html;
        }
        function permission_group_pulldown($id = 'subscriptiongroupid', $fieldname = 'form[subscriptiongroupid]', $selected = '', $class = 'draw-select', $slng = 'eng')
        {
                $html = '';
                $sql = $this->sheel->db->query("
                        SELECT subscriptiongroupid, canremove, title_$slng AS title, description_$slng AS description
                        FROM " . DB_PREFIX . "subscription_group
                        WHERE type = 'product'
                ");
                $html = '<select name="' . $fieldname . '" class="' . $class . '" id="' . $id . '">';
                while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                        $html .= '<option value="' . $res['subscriptiongroupid'] . '"';
                        if (!empty($selected) and $selected == $res['subscriptiongroupid']) {
                                $html .= ' selected="selected"';
                        }
                        $html .= '>' . stripslashes(o($res['title'])) . '</option>';
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to fetch a membership plan title based on a user id.  This function will take the site type (service/product)
         * into consideration before displaying the output.
         *
         * @param       integer        user id
         *
         * @return      string         Returns the subscription plan as requested
         */
        function fetch_subscription_plan($userid = 0)
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $sql = $this->sheel->db->query("
                        SELECT u.subscriptionid, s.title_" . $slng . " AS title
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return stripslashes(o($res['title']));
                } else {
                        return '{_no_plan}';
                }
        }
        /**
         * Function to display any subscription plan exemptions within a pulldown menu element.
         *
         * @return      string         Returns the subscription exemptions as requested
         */
        function exemptions_pulldown()
        {
                $html = '<div class="draw-select__wrapper w-250"><select name="accessname" id="accessname" class="draw-select w-100pct">';
                $sql = $this->sheel->db->query("
                        SELECT accessname, accesstype
                        FROM " . DB_PREFIX . "subscription_permissions
                        WHERE accessmode = 'global' OR accessmode = 'product'
                        GROUP BY accessname
                        ORDER BY accessname ASC
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $html .= '<option value="' . $res['accessname'] . '">' . $res['accessname'] . ' &ndash; {_' . $res['accessname'] . '_text} (' . $res['accesstype'] . ')</option>';
                        }
                }
                $html .= '</select></div>';
                return $html;
        }
        /**
         * Function to handle the subscription exemption upgrade process for end users.
         *
         * @param       integer        user id
         * @param       string         access permission name
         * @param       string         access permission value
         * @param       integer        cost for this exemption
         * @param       integer        days this exemption shall last for
         * @param       string         logic to use for determining what to do
         * @param       string         end user comments
         * @param       boolean        defines if this function should dispatch email once it's finished
         *
         * @return      string         Returns the subscription exemptions as requested
         */
        function construct_subscription_exemption($userid = 0, $accessname = '', $accessvalue = '', $cost = 0, $days = 0, $logic = '', $comments = '', $doemail = '')
        {
                $userid = isset($userid) ? intval($userid) : '';
                $accessname = isset($accessname) ? $accessname : '';
                $accessvalue = isset($accessvalue) ? $accessvalue : '';
                $cost = isset($cost) ? $cost : 0;
                $days = isset($days) ? $days : 7;
                $exemptfrom = DATETIME24H;
                $exemptto = $this->sheel->datetimes->fetch_date_fromnow($days) . ' ' . TIMENOW; 
                $nofunds = 0;
                if ($userid == '' or $accessname == '' or $accessvalue == '') {
                        return 0;
                }
                if (isset($logic)) {
                        $sql = $this->sheel->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription_company_exempt
                                WHERE user_id = '" . intval($userid) . "'
                                        AND accessname = '" . $this->sheel->db->escape_string($accessname) . "'
                                        AND active = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) == 0) {
                                switch ($logic) {
                                        case 'active': {
                                                        // insert permission and waive transaction fee for cost amount
                                                        $transactionid = $this->sheel->accounting_payment->construct_transaction_id();
                                                        $invoiceid = $this->sheel->accounting->insert_transaction(
                                                                array(
                                                                        'user_id' => $userid,
                                                                        'description' => 'Membership Permission Exemption: ' . $accessname . ' ({_from}: ' . $exemptfrom . ' {_to}: ' . $exemptto . ')',
                                                                        'amount' => sprintf("%01.2f", 0),
                                                                        'status' => 'paid',
                                                                        'invoicetype' => 'debit',
                                                                        'paymethod' => 'account',
                                                                        'createdate' => DATETIME24H,
                                                                        'duedate' => DATEINVOICEDUE, 
                                                                        'paiddate' => DATETIME24H,
                                                                        'custommessage' => $comments,
                                                                        'returnid' => 1,
                                                                        'transactionidx' => $transactionid
                                                                )
                                                        );
                                                        break;
                                                }
                                        case 'activepaid': {
                                                        // insert permission and insert new paid transaction for cost amount
                                                        $transactionid = $this->sheel->accounting_payment->construct_transaction_id();
                                                        $invoiceid = $this->sheel->accounting->insert_transaction(
                                                                array(
                                                                        'user_id' => $userid,
                                                                        'description' => 'Membership Permission Exemption: ' . $accessname . ' ({_from}: ' . $exemptfrom . ' {_to}: ' . $exemptto . ')',
                                                                        'amount' => sprintf("%01.2f", $cost),
                                                                        'paid' => sprintf("%01.2f", $cost),
                                                                        'status' => 'paid',
                                                                        'invoicetype' => 'debit',
                                                                        'paymethod' => 'account',
                                                                        'createdate' => DATETIME24H,
                                                                        'duedate' => DATEINVOICEDUE,
                                                                        'paiddate' => DATETIME24H,
                                                                        'custommessage' => $comments,
                                                                        'returnid' => 1,
                                                                        'transactionidx' => $transactionid
                                                                )
                                                        );
                                                        // record transaction ledger
                                                        $array = array(
                                                                'userid' => $userid,
                                                                'credit' => 0,
                                                                'debit' => sprintf("%01.2f", $cost),
                                                                'description' => 'Debit for Membership Permission Exemption: ' . $accessname . ' ({_from}: ' . $exemptfrom . ' {_to}: ' . $exemptto . ')',
                                                                'invoiceid' => $invoiceid,
                                                                'transactionid' => $transactionid,
                                                                'custom' => $comments,
                                                                'staffnotes' => '',
                                                                'technical' => 'Account balance debit based on [construct_subscription_exemption(), logic=activepaid] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                                        );
                                                        $this->sheel->accounting->account_balance($array);
                                                        unset($array);
                                                        break;
                                                }
                                        case 'activedebit': {
                                                        // attempt to debit customers account for payment for permissions
                                                        $sql = $this->sheel->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                        if ($this->sheel->db->num_rows($sql) > 0) {
                                                                $res = $this->sheel->db->fetch_array($sql);
                                                                if ($cost <= $res['available_balance']) {
                                                                        // customer has sufficient funds
                                                                        $transactionid = $this->sheel->accounting_payment->construct_transaction_id();
                                                                        $invoiceid = $this->sheel->accounting->insert_transaction(
                                                                                array(
                                                                                        'user_id' => $userid,
                                                                                        'description' => 'Membership Permission Exemption: ' . $accessname . ' ({_from}: ' . $exemptfrom . ' {_to}: ' . $exemptto . ')',
                                                                                        'amount' => sprintf("%01.2f", $cost),
                                                                                        'paid' => sprintf("%01.2f", $cost),
                                                                                        'status' => 'paid',
                                                                                        'invoicetype' => 'debit',
                                                                                        'paymethod' => 'account',
                                                                                        'createdate' => DATETIME24H,
                                                                                        'duedate' => DATEINVOICEDUE,
                                                                                        'paiddate' => DATETIME24H,
                                                                                        'custommessage' => $comments,
                                                                                        'returnid' => 1,
                                                                                        'transactionidx' => $transactionid
                                                                                )
                                                                        );
                                                                        // debit amount from online account
                                                                        $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET available_balance = available_balance - $cost,
                                                                        total_balance = total_balance - $cost
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                        // record transaction ledger
                                                                        $array = array(
                                                                                'userid' => $userid,
                                                                                'credit' => 0,
                                                                                'debit' => sprintf("%01.2f", $cost),
                                                                                'description' => 'Debit for Membership Permission Exemption: ' . $accessname . ' ({_from}: ' . $exemptfrom . ' {_to}: ' . $exemptto . ')',
                                                                                'invoiceid' => $invoiceid,
                                                                                'transactionid' => $transactionid,
                                                                                'custom' => $comments,
                                                                                'staffnotes' => '',
                                                                                'technical' => 'Account balance debit based on [construct_subscription_exemption(), logic=activedebit] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                                                        );
                                                                        $this->sheel->accounting->account_balance($array);
                                                                        unset($array);
                                                                } else {
                                                                        $nofunds = 1;
                                                                }
                                                        }
                                                        break;
                                                }
                                }
                                if ($nofunds == 0) { // create new exemption
                                        $this->sheel->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_company_exempt
                                                (user_id, accessname, value, exemptfrom, exemptto, comments, invoiceid, active)
                                                VALUES (
                                                '" . intval($userid) . "',
                                                '" . $this->sheel->db->escape_string($accessname) . "',
                                                '" . $this->sheel->db->escape_string($accessvalue) . "',
                                                '" . $this->sheel->db->escape_string($exemptfrom) . "',
                                                '" . $this->sheel->db->escape_string($exemptto) . "',
                                                '" . $this->sheel->db->escape_string($comments) . "',
                                                '" . intval($invoiceid) . "',
                                                '1')
                                        ", 0, null, __FILE__, __LINE__);
                                        return 1;
                                } else {
                                        return 0;
                                }
                        } else {
                                return 0;
                        }
                } else {
                        return 0;
                }
        }
        function remove_pending_transactions($userid = 0)
        {
                if ($userid > 0) {
                        $sql = $this->sheel->db->query("
                                SELECT i.invoiceid
                                FROM " . DB_PREFIX . "invoices i
                                LEFT JOIN " . DB_PREFIX . "subscription s ON i.subscriptionid = s.subscriptionid
                                WHERE i.subscriptionid > 0
                                        AND i.user_id = '" . intval($userid) . "'
                                        AND (i.status = 'scheduled' OR i.status = 'pending')
                                        AND s.type = 'product'
                                ORDER BY i.createdate DESC
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) { // old transaction exists! let's remove it!
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $this->sheel->db->query("
                                        DELETE FROM " . DB_PREFIX . "invoices
                                        WHERE invoiceid = '" . $res['invoiceid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
        }
        /**
         * Function to handle the subscription upgrade process for end users.
         *
         * @param       integer        user id
         * @param       integer        subscription id
         * @param       boolean        end user agreement of terms value (true / false)
         * @param       boolean        defines if the subscription cost is zero or not
         * @param       boolean        defines if the transaction will be using the recurring subscription logic
         * @param       string         payment method chosen by the payment selected [bluepay, authnet, paypal_pro, paypal, skrill, account]
         * @param       boolean        defines if this transaction is a recurring subscription modification or not
         * @param       boolean        defines if this function should automatically delete any previous free or paid subscription transactions to reduce the amount of pending invoices in the admincp
         * @param       string         return url (optional)
         * @param       string         amount to pay now (force this price)
         * @param       string         adjustment (cost in savings due to upgraded plan for pro rate pricing)
         * @param       string         profile id of recurring billing gateway transaction for this user
         * @param       string         profile transaction id of the recurring billing invoice for this user
         * @param       integer        billing profile id (when credit cards are being used for memberships)
         *
         */
        function subscription_upgrade_process($payload = array())
        {
                $userid = isset($payload['userid']) ? intval($payload['userid']) : '0';
                $subscriptionid = isset($payload['subscriptionid']) ? intval($payload['subscriptionid']) : '0';
                $agreecheck = isset($payload['agreecheck']) ? intval($payload['agreecheck']) : '0';
                $nocost = isset($payload['nocost']) ? $payload['nocost'] : '0';
                $recurring = isset($payload['recurring']) ? $payload['recurring'] : '0';
                $paymethod = isset($payload['paymethod']) ? $payload['paymethod'] : 'account';
                $ismodify = isset($payload['ismodify']) ? $payload['ismodify'] : '0';
                $removepending = isset($payload['removepending']) ? $payload['removepending'] : false;
                $returnurl = isset($payload['returnurl']) ? $payload['returnurl'] : '';
                $amount_now = isset($payload['amount_now']) ? $payload['amount_now'] : '0';
                $adjustment = isset($payload['adjustment']) ? $payload['adjustment'] : '0';
                $profileid = isset($payload['profileid']) ? $payload['profileid'] : '';
                $eventid = isset($payload['eventid']) ? $payload['eventid'] : '';
                $otherid = isset($payload['otherid']) ? $payload['otherid'] : '';
                $currency = isset($payload['currency']) ? $payload['currency'] : 'USD';
                $profiletransactionid = isset($payload['profiletransactionid']) ? $payload['profiletransactionid'] : '';
                $paymentgateway = isset($payload['paymentgateway']) ? $payload['paymentgateway'] : '';
                $paymentgatewayold = isset($payload['paymentgatewayold']) ? $payload['paymentgatewayold'] : '';
                $bpid = isset($payload['bpid']) ? $payload['bpid'] : '0';
                $length = isset($payload['length']) ? intval($payload['length']) : '';
                $units = isset($payload['units']) ? $payload['units'] : '';
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                if ($removepending) { // remove any pending membership transactions
                        $this->remove_pending_transactions($userid);
                }
                if ($nocost) { // free plan
                        $sql = $this->sheel->db->query("
                                SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                        AND type = 'product'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $subscription_plan_result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $subscription_plan_cost = $subscription_plan_result['cost'];
                                if ($subscription_plan_cost <= 0) {
                                        if ($agreecheck) {
                                                if (!empty($paymentgateway) and !empty($profileid)) { // stop the recurring billing of old and/or current/active plan if applicable
                                                        $this->sheel->paymentgateway->recurring()->cancel(
                                                                array(
                                                                        'paymentgatewayold' => $paymentgateway,
                                                                        'profileid' => $profileid,
                                                                        'eventid' => $eventid,
                                                                        'otherid' => $otherid
                                                                )
                                                        );
                                                } else if (!empty($paymentgatewayold) and !empty($profileid)) { // stop the recurring billing of old and/or current/active plan if applicable
                                                        $this->sheel->paymentgateway->recurring()->cancel(
                                                                array(
                                                                        'paymentgatewayold' => $paymentgatewayold,
                                                                        'profileid' => $profileid,
                                                                        'eventid' => $eventid,
                                                                        'otherid' => $otherid
                                                                )
                                                        );
                                                }
                                                $transactionid = $this->sheel->accounting_payment->construct_transaction_id();
                                                $subscription_invoice_id = $this->sheel->accounting->insert_transaction(
                                                        array(
                                                                'subscriptionid' => intval($subscriptionid),
                                                                'user_id' => intval($userid),
                                                                'description' => $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . ' ' . $this->print_unit($subscription_plan_result['units']) . ')',
                                                                'amount' => sprintf("%01.2f", $subscription_plan_cost),
                                                                'paid' => sprintf("%01.2f", $subscription_plan_cost),
                                                                'status' => 'paid',
                                                                'invoicetype' => 'subscription',
                                                                'paymethod' => 'account',
                                                                'createdate' => DATETIME24H,
                                                                'duedate' => DATEINVOICEDUE,
                                                                'paiddate' => DATETIME24H,
                                                                'custommessage' => '',
                                                                'returnid' => 1,
                                                                'transactionidx' => $transactionid
                                                        )
                                                );
                                                $subscription_item_name = stripslashes(o($subscription_plan_result['title'])) . ' (' . $subscription_plan_result['length'] . ' ' . $this->print_unit($subscription_plan_result['units']) . ')';
                                                $this->sheel->template->templateregistry['subscription_item_name'] = $subscription_item_name;
                                                $subscription_item_name = $this->sheel->template->parse_template_phrases('subscription_item_name');
                                                $subscription_item_cost = sprintf("%01.2f", $subscription_plan_cost);
                                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                $subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
                                                $sqlcheck = $this->sheel->db->query("
                                                        SELECT u.id, u.subscriptionid
                                                        FROM " . DB_PREFIX . "subscription_company u
                                                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                                        WHERE u.user_id = '" . intval($userid) . "'
                                                                AND s.type = 'product'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($this->sheel->db->num_rows($sqlcheck) > 0) { // update membership with new plan info
                                                        $rescheck = $this->sheel->db->fetch_array($sqlcheck, DB_ASSOC);
                                                        $this->sheel->db->query("
                                                                UPDATE " . DB_PREFIX . "subscription_company
                                                                SET active = 'yes',
                                                                renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                                startdate = '" . DATETIME24H . "',
                                                                subscriptionid = '" . intval($subscriptionid) . "',
                                                                migrateto = '" . $this->sheel->db->escape_string($subscription_plan_result['migrateto']) . "',
                                                                migratelogic = '" . $this->sheel->db->escape_string($subscription_plan_result['migratelogic']) . "',
                                                                invoiceid = '" . intval($subscription_invoice_id) . "',
                                                                roleid = '" . intval($subscription_plan_result['roleid']) . "',
                                                                cancelled = '0'
                                                                WHERE id = '" . $rescheck['id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                } else { // create new free active membership for user
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "subscription_company
                                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, roleid, migrateto, migratelogic, invoiceid)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($subscriptionid) . "',
                                                                '" . intval($userid) . "',
                                                                'account',
                                                                '" . DATETIME24H . "',
                                                                '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                                '1',
                                                                'yes',
                                                                '0',
                                                                '" . intval($subscription_plan_result['roleid']) . "',
                                                                '" . $this->sheel->db->escape_string($subscription_plan_result['migrateto']) . "',
                                                                '" . $this->sheel->db->escape_string($subscription_plan_result['migratelogic']) . "',
                                                                '" . intval($subscription_invoice_id) . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                $_SESSION['sheeldata']['user']['subscriptionid'] = intval($subscriptionid);
                                                if (!empty($_SESSION['sheeldata']['user']['active']) and $_SESSION['sheeldata']['user']['active'] == 'no') { // update membership for user
                                                        $_SESSION['sheeldata']['user']['active'] = 'yes';
                                                }
                                                // remove stores support if the new plan doesn't offer that.. and user agreed to delete store..
                                                $this->handle_no_stores_permission($userid);
                                                refresh(HTTPS_SERVER . 'membership/?note=mp:cmpld');
                                                exit();
                                        } else { // didn't agree to checkbox terms/privacy
                                                refresh(HTTPS_SERVER . 'membership/?note=mp:agchk');
                                                exit();
                                        }
                                } else { // error
                                        refresh(HTTPS_SERVER . 'membership/?note=mp:ivinf');
                                        exit();
                                }
                        } else { // error
                                refresh(HTTPS_SERVER . 'membership/?note=mp:ivinf');
                                exit();
                        }
                } else { // paid plan
                        if ($recurring and $paymethod != 'account') { // recurring subscription via credit card or IPN payments
                                if (empty($paymethod)) {
                                        refresh(HTTPS_SERVER . 'membership/?note=mp:ivinf');
                                        exit();
                                }
                                $this->sheel->template->meta['pagetitle'] = '{_subscription} {_payment} - ' . SITE_NAME;
                                $this->sheel->template->meta['areatitle'] = '{_subscription} {_payment}';
                                $this->sheel->template->meta['navcrumb'] = array();
                                $this->sheel->template->meta['navcrumb'][HTTPS_SERVER . 'membership/'] = '{_subscription}';
                                $this->sheel->template->meta['navcrumb'][""] = '{_payment}';
                                $sql = $this->sheel->db->query("
                                        SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                                AND type = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql) > 0) {
                                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);

                                        $res['cost'] = sprintf("%01.2f", $res['cost']);
                                        $res['amount_now'] = sprintf("%01.2f", ($res['cost'] - $adjustment));
                                        $res['tax'] = $res['taxnow'] = $res['transactionfee'] = $res['transactionfeenow'] = $res['taxpercent'] = 0;
                                        $this->sheel->show['taxes'] = false;
                                        if ($this->sheel->tax->is_taxable(intval($userid), 'subscription')) {
                                                $res['tax'] = sprintf("%01.2f", $this->sheel->tax->fetch_amount(intval($userid), sprintf("%01.2f", $res['cost']), 'subscription', 0));
                                                $res['taxnow'] = sprintf("%01.2f", $this->sheel->tax->fetch_amount(intval($userid), sprintf("%01.2f", $res['amount_now']), 'subscription', 0));
                                                $res['taxbit'] = $this->sheel->tax->fetch_amount(intval($userid), '', 'subscription', 1);
                                                $res['taxbitnow'] = $this->sheel->tax->fetch_amount(intval($userid), '', 'subscription', 1);
                                                $res['taxpercent'] = $this->sheel->tax->fetch_amount(intval($userid), sprintf("%01.2f", $res['cost']), 'subscription', 0, 1);
                                                $this->sheel->show['taxes'] = ($res['taxnow'] > 0) ? true : false;
                                        }
                                        $res['cost_formatted'] = $this->sheel->currency->format($res['cost']);
                                        $res['adjustment_formatted'] = '- ' . $this->sheel->currency->format($adjustment);
                                        $res['costbeforetax'] = sprintf("%01.2f", $res['amount_now']);
                                        $res['costbeforetax_formatted'] = $this->sheel->currency->format(sprintf("%01.2f", $res['amount_now']));
                                        $res['tax_formatted'] = $this->sheel->currency->format($res['tax']);
                                        $res['taxnow_formatted'] = $this->sheel->currency->format($res['taxnow']);

                                        $tfees = $this->transaction_fees($paymethod); // txn fees for selected gateway
                                        $this->sheel->config['cc_transaction_fee'] = $tfees['fee1'];
                                        $this->sheel->config['cc_transaction_fee2'] = $tfees['fee2'];

                                        $res['transactionfee'] = ($res['cost'] > 0) ? sprintf("%01.2f", round((($res['cost']) * $this->sheel->config['cc_transaction_fee']) + $this->sheel->config['cc_transaction_fee2'], 2)) : 0;
                                        $res['transactionfee_formatted'] = $this->sheel->currency->format($res['transactionfee']);
                                        $res['transactionfeenow'] = ($res['amount_now'] > 0) ? sprintf("%01.2f", round(($res['amount_now'] * $this->sheel->config['cc_transaction_fee']) + $this->sheel->config['cc_transaction_fee2'], 2)) : 0;
                                        $res['transactionfeenow_formatted'] = $this->sheel->currency->format($res['transactionfeenow']);

                                        $res['total'] = ($res['cost'] + $res['tax'] + $res['transactionfee']);
                                        $res['totalnow'] = ($res['amount_now'] + $res['taxnow'] + $res['transactionfeenow']);
                                        $res['total_formatted'] = $this->sheel->currency->format($res['cost'] + $res['tax'] + $res['transactionfee']);
                                        $res['totalnow_formatted'] = $this->sheel->currency->format($res['amount_now'] + $res['taxnow'] + $res['transactionfeenow']);

                                        $this->sheel->show['transactionfees'] = (($res['transactionfee'] > 0) ? true : false);
                                        $this->sheel->show['usecardonfile'] = false;
                                        $this->sheel->show['paymentsandbox'] = $this->sheel->paymentgateway->debug;
                                        $user_has_active_and_authorized_card = $this->sheel->accounting_creditcard->fetch_default_creditcard_profile($userid, true);
                                        if ($this->sheel->config['save_credit_cards'] and $this->sheel->config['use_internal_gateway'] != 'none') { // we're letting users save cards to the db
                                                // does user have an active credit card payment profile?
                                                if ($user_has_active_and_authorized_card) { // allow processing of active card in db
                                                        $this->sheel->show['usecardonfile'] = true;
                                                }
                                        } else { // show a card payment form each time
                                                $this->sheel->show['usecardonfile'] = false;
                                        }
                                        $customencrypted = 'RECURRINGSUBSCRIPTION|' . intval($userid) . '|0|0|' . $res['length'] . '|' . $res['units'] . '|' . intval($subscriptionid) . '|' . $res['cost'] . '|' . $res['roleid'] . '|' . $res['tax'] . '|' . $res['transactionfee'] . '|' . $res['amount_now'] . '|' . $res['taxnow'] . '|' . $res['transactionfeenow'] . '|' . $adjustment;
                                        $this->sheel->template->templateregistry['title'] = $res['title'] . ' {_recurring_subscription}';
                                        $recurring = array(
                                                'subscriptionid' => $subscriptionid,
                                                'roleid' => $res['roleid'],
                                                'currency' => $currency,
                                                'total' => $res['total'],
                                                'totalnow' => $res['totalnow'],
                                                'adjustment' => $adjustment,
                                                'units' => $res['units'],
                                                'length' => $res['length'],
                                                'title' => $res['title'],
                                                'description' => $this->sheel->template->parse_template_phrases('title'),
                                                'transactionfee' => $res['transactionfee'],
                                                'transactionfeenow' => $res['transactionfeenow'],
                                                'tax' => $res['tax'],
                                                'taxnow' => $res['taxnow'],
                                                'taxpercent' => $res['taxpercent'],
                                                'profileid' => $profileid,
                                                'eventid' => $eventid,
                                                'otherid' => $otherid,
                                                'profiletransactionid' => $profiletransactionid,
                                                'paymentgateway' => $paymentgateway,
                                                'paymentgatewayold' => $paymentgatewayold,
                                                'customencrypted' => $customencrypted,
                                                'email' => $this->sheel->fetch_user('email', $userid),
                                                'phone' => $this->sheel->fetch_user('phone', $userid),
                                                'countryshort' => $this->sheel->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . intval($this->sheel->fetch_user('country', $userid)) . "'", "cc"),
                                                'userid' => $userid,
                                                'mode' => 'create',
                                                'onsubmit' => 'return validate_card(this)',
                                                'bpid' => $bpid
                                        );
                                        if (in_array($paymethod, $this->sheel->paymentgateway->recurring_gateway_accepted)) { // recurring credit card payments
                                                foreach ($this->sheel->paymentgateway->recurring_gateway_accepted as $gateway) { // authnet, paypal_pro, eway, etc
                                                        if ($paymethod == $gateway) {
                                                                if (!empty($paymentgatewayold) and !empty($profileid)) { // stop the recurring billing of old and/or current/active plan if applicable
                                                                        $this->sheel->paymentgateway->recurring()->cancel(
                                                                                array(
                                                                                        'paymentgatewayold' => $paymentgatewayold,
                                                                                        'profileid' => $profileid,
                                                                                        'eventid' => $eventid,
                                                                                        'otherid' => $otherid
                                                                                )
                                                                        );
                                                                }
                                                                if ($this->sheel->show['usecardonfile']) { // process card for recurring membership
                                                                        $payload = $this->sheel->{$gateway}->print_recurring_payment($recurring, 'array');
                                                                        $this->sheel->accounting_creditcard->process_recurring_creditcard_payment($paymethod, $payload);
                                                                        exit();
                                                                } else { // show form to let user enter his card for recurring membership payment
                                                                        $options = $this->sheel->shipping->fetch_billing_profile_select_options($userid, true);
                                                                        $vars = array(
                                                                                'billingpulldown' => $this->sheel->construct_pulldown('bpid', 'bpid', $options['billing'], '', 'class="draw-select"'),
                                                                                'hidden_form_start' => $this->sheel->{$gateway}->print_recurring_payment($recurring, 'form'),
                                                                                // <-- form submits to subscription.php (cmd=process_recurring_payment)
                                                                                'hidden_form_end' => $this->sheel->{$gateway}->print_recurring_payment_end(),
                                                                                'title' => $res['title'],
                                                                                'paymentgatewayid' => $this->sheel->paymentgateway->gateway,
                                                                                'length' => $length,
                                                                                'units' => $units
                                                                        );
                                                                        $this->sheel->template->meta['jsinclude']['header'][] = 'vendor/jquery_card';
                                                                        $this->sheel->template->meta['cssinclude']['vendor'][] = 'card';
                                                                        $this->sheel->show['slimheader'] = $this->sheel->show['slimfooter'] = true;
                                                                        $this->sheel->template->fetch('main', 'subscription_creditcard_recurring.html');
                                                                        $this->sheel->template->parse_hash('main', array('ilpage' => $this->sheel->ilpage, 'pay' => $res));
                                                                        $this->sheel->template->pprint('main', $vars);
                                                                        exit();
                                                                }
                                                        }
                                                }
                                        }
                                        if (in_array($paymethod, $this->sheel->paymentgateway->recurring_ipn_gateway_accepted)) { // ipn based membership payments
                                                foreach ($this->sheel->paymentgateway->recurring_ipn_gateway_accepted as $gateway) { // paypal, skrill, mollie, etc
                                                        if ($paymethod == $gateway) {
                                                                if (!empty($paymentgatewayold) and !empty($profileid)) { // stop the recurring billing of old and/or current/active plan if applicable
                                                                        $this->sheel->paymentgateway->recurring()->cancel(
                                                                                array(
                                                                                        'paymentgatewayold' => $paymentgatewayold,
                                                                                        'profileid' => $profileid,
                                                                                        'eventid' => $eventid,
                                                                                        'otherid' => $otherid
                                                                                )
                                                                        );
                                                                }
                                                                $recurring['onsubmit'] = 'return validate_ipn_email(this)';
                                                                $vars = array(
                                                                        'ipntype' => $gateway,
                                                                        'hidden_form_start' => $this->sheel->{$gateway}->print_recurring_payment($recurring, 'form'),
                                                                        // <-- form submits to IPN payment gateway, leaving site
                                                                        'hidden_form_end' => $this->sheel->{$gateway}->print_recurring_payment_end(),
                                                                        'ipnoptions' => $this->sheel->{$gateway}->print_recurring_payment_options($this->sheel->fetch_user($gateway . '_profile', $userid)),
                                                                        'title' => o($res['title']),
                                                                        'length' => $length,
                                                                        'units' => $units
                                                                );
                                                                $this->sheel->show['slimheader'] = $this->sheel->show['slimfooter'] = true;
                                                                $this->sheel->template->fetch('main', 'subscription_ipn_recurring.html');
                                                                $this->sheel->template->parse_hash('main', array('ilpage' => $this->sheel->ilpage, 'pay' => $res));
                                                                $this->sheel->template->pprint('main', $vars);
                                                                exit();
                                                        }
                                                }
                                        }
                                }
                        } else { // regular account balance subscription
                                if (empty($paymethod)) {
                                        refresh(HTTPS_SERVER . 'membership/?note=mp:ivinf');
                                        exit();
                                }
                                $subscription_plan_result = array();
                                $sql = $this->sheel->db->query("
                                        SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                                AND type = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql) > 0) {
                                        $subscription_plan_result = $this->sheel->db->fetch_array($sql, DB_ASSOC);

                                        $subscription_plan_cost = $subscription_plan_result['cost'];
                                        $subscription_plan_cost_notax = $subscription_plan_cost;
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);

                                        if ($agreecheck) { // agreed to site terms and conditions

                                                if (!empty($paymentgateway) and !empty($profileid)) { // stop the recurring billing of old and/or current/active plan if applicable
                                                        $this->sheel->paymentgateway->recurring()->cancel(
                                                                array(
                                                                        'paymentgatewayold' => $paymentgateway,
                                                                        'profileid' => $profileid,
                                                                        'eventid' => $eventid,
                                                                        'otherid' => $otherid
                                                                )
                                                        );
                                                }
                                                $extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $subscription_plan_cost_notax) . "',";
                                                if ($this->sheel->tax->is_taxable(intval($userid), 'subscription')) { // is user taxable for this invoice type?
                                                        $subscription_plan_cost = ($subscription_plan_cost_notax + $this->sheel->tax->fetch_amount(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'subscription', 0));
                                                        $taxamount = $this->sheel->tax->fetch_amount(intval($userid), $subscription_plan_cost_notax, 'subscription', 0);
                                                        $totalamount = ($subscription_plan_cost_notax + $taxamount);
                                                        $taxinfo = $this->sheel->tax->fetch_amount(intval($userid), $subscription_plan_cost_notax, 'subscription', 1);
                                                        $extrainvoicesql = "
                                                                istaxable = '1',
                                                                totalamount = '" . sprintf("%01.2f", $totalamount) . "',
                                                                taxamount = '" . sprintf("%01.2f", $taxamount) . "',
                                                                taxinfo = '" . $this->sheel->db->escape_string($taxinfo) . "',
                                                        ";
                                                }
                                                // determine primary payment profile (account balance, credit card profile, etc)
                                                // does customer take advantage of instant payment from online account balance?
                                                $transactionid = $this->sheel->accounting_payment->construct_transaction_id();
                                                $subscription_invoice_id = $this->sheel->accounting->insert_transaction(
                                                        array(
                                                                'subscriptionid' => intval($subscriptionid),
                                                                'user_id' => intval($userid),
                                                                'description' => $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . ' ' . $this->print_unit($subscription_plan_result['units']) . ')',
                                                                'amount' => sprintf("%01.2f", $subscription_plan_cost_notax),
                                                                'status' => 'scheduled',
                                                                'invoicetype' => 'subscription',
                                                                'paymethod' => 'account',
                                                                'createdate' => DATETIME24H,
                                                                'duedate' => $this->print_subscription_renewal_datetime($subscription_length),
                                                                'returnid' => 1,
                                                                'transactionidx' => $transactionid
                                                        )
                                                );
                                                $this->sheel->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET
                                                        $extrainvoicesql
                                                        isfvf = '0'
                                                        WHERE invoiceid = '" . intval($subscription_invoice_id) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $subscription_item_name = stripslashes($subscription_plan_result['title']) . ' (' . $subscription_plan_result['length'] . ' ' . $this->print_unit($subscription_plan_result['units']) . ')';
                                                $this->sheel->template->templateregistry['subscription_item_name'] = $subscription_item_name;
                                                $subscription_item_name = $this->sheel->template->parse_template_phrases('subscription_item_name');
                                                $subscription_item_cost = $subscription_plan_cost;
                                                $resinsorupd = array();
                                                $insorupd = $this->sheel->db->query("
                                                        SELECT u.id
                                                        FROM " . DB_PREFIX . "subscription_company u
                                                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                                        WHERE u.user_id = '" . intval($userid) . "'
                                                                AND s.type = 'product'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($this->sheel->db->num_rows($insorupd) > 0) { // set payment method to online account and auto payments to active
                                                        $resinsorupd = $this->sheel->db->fetch_array($insorupd, DB_ASSOC);
                                                        $this->sheel->db->query("
                                                                UPDATE " . DB_PREFIX . "subscription_company
                                                                SET paymethod = 'account',
                                                                autopayment = '1',
                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                roleid = '" . $subscription_plan_result['roleid'] . "',
                                                                invoiceid = '" . $subscription_invoice_id . "',
                                                                cancelled = '0'
                                                                WHERE id = '" . $resinsorupd['id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                } else {
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "subscription_company
                                                                (id, subscriptionid, user_id, paymethod, autopayment, active, roleid, migrateto, migratelogic, invoiceid)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($subscriptionid) . "',
                                                                '" . intval($userid) . "',
                                                                'account',
                                                                '1',
                                                                'no',
                                                                '" . intval($subscription_plan_result['roleid']) . "',
                                                                '" . $this->sheel->db->escape_string($subscription_plan_result['migrateto']) . "',
                                                                '" . $this->sheel->db->escape_string($subscription_plan_result['migratelogic']) . "',
                                                                '" . $subscription_invoice_id . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $resinsorupd['id'] = $this->sheel->db->insert_id();
                                                }
                                                // calculate subscription renewal date
                                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                $subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
                                                $sqlgetacc = $this->sheel->db->query("
                                                        SELECT total_balance, available_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($this->sheel->db->num_rows($sqlgetacc) > 0) {
                                                        $resgetacc = $this->sheel->db->fetch_array($sqlgetacc, DB_ASSOC);
                                                        if ($resgetacc['available_balance'] >= $subscription_plan_cost) {
                                                                $new_total = sprintf("%01.2f", $resgetacc['total_balance'] - $subscription_plan_cost);
                                                                $new_avail = sprintf("%01.2f", $resgetacc['available_balance'] - $subscription_plan_cost);
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET available_balance = '" . $this->sheel->db->escape_string($new_avail) . "',
                                                                        total_balance = '" . $this->sheel->db->escape_string($new_total) . "'
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // update invoice with payment from online account balance
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET paid = '" . $this->sheel->db->escape_string($subscription_plan_cost) . "',
                                                                        status = 'paid',
                                                                        paiddate = '" . DATETIME24H . "'
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                                AND invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                                AND invoicetype = 'subscription'
                                                                                AND subscriptionid = '" . intval($subscriptionid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // record transaction ledger
                                                                $array = array(
                                                                        'userid' => intval($userid),
                                                                        'credit' => 0,
                                                                        'debit' => $subscription_plan_cost,
                                                                        'description' => $subscription_plan_result['title'],
                                                                        'invoiceid' => $subscription_invoice_id,
                                                                        'transactionid' => $transactionid,
                                                                        'custom' => '',
                                                                        'staffnotes' => '',
                                                                        'technical' => 'Account balance debit based on [subscription_upgrade_process(), type=regularsubscription] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                                                );
                                                                $this->sheel->accounting->account_balance($array);
                                                                unset($array);

                                                                // track income spent
                                                                $this->sheel->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'credit');
                                                                $bidtotal = $this->sheel->permissions->check_access($userid, 'bidlimitperday');
                                                                $bidsleft = ($bidtotal - $this->sheel->bid->fetch_bidcount_today($userid)) * (-1);
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET bidstoday = '" . $this->sheel->db->escape_string($bidsleft) . "'
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);

                                                                // upgrade customers membership plan
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "subscription_company
                                                                        SET active = 'yes',
                                                                        renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                                        startdate = '" . DATETIME24H . "',
                                                                        subscriptionid = '" . intval($subscriptionid) . "',
                                                                        roleid = '" . intval($subscription_plan_result['roleid']) . "',
                                                                        migrateto = '" . $this->sheel->db->escape_string($subscription_plan_result['migrateto']) . "',
                                                                        migratelogic = '" . $this->sheel->db->escape_string($subscription_plan_result['migratelogic']) . "',
                                                                        invoiceid = '" . intval($subscription_invoice_id) . "',
                                                                        cancelled = '0'
                                                                        WHERE id = '" . intval($resinsorupd['id']) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                $_SESSION['sheeldata']['user']['subscriptionid'] = intval($subscriptionid);
                                                                $_SESSION['sheeldata']['user']['active'] = 'yes';
                                                                $this->handle_no_stores_permission(intval($userid));
                                                                $this->sheel->email->mail = $this->sheel->fetch_user('email', intval($userid));
                                                                $this->sheel->email->slng = $this->sheel->language->fetch_user_slng(intval($userid));
                                                                $this->sheel->email->get('subscription_paid_online_account');
                                                                $this->sheel->email->set(
                                                                        array(
                                                                                '{{provider}}' => $this->sheel->fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $this->sheel->currency->format($subscription_item_cost)
                                                                        )
                                                                );
                                                                $this->sheel->email->send();
                                                                $this->sheel->email->mail = SITE_CONTACT;
                                                                $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                                                                $this->sheel->email->get('subscription_paid_online_account_admin');
                                                                $this->sheel->email->set(
                                                                        array(
                                                                                '{{provider}}' => $this->sheel->fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $this->sheel->currency->format($subscription_item_cost)
                                                                        )
                                                                );
                                                                $this->sheel->email->send();

                                                                refresh(HTTPS_SERVER . 'membership/?note=mp:cmpld');
                                                                exit();
                                                        } else { // no funds in account balance
                                                                refresh(HTTPS_SERVER . 'membership/?note=mp:nsf');
                                                                exit();
                                                        }
                                                }
                                        } else { // did not agree to site terms
                                                refresh(HTTPS_SERVER . 'membership/?note=mp:agchk');
                                                exit();
                                        }
                                } else { // error: selected plan doesn't exist
                                        refresh(HTTPS_SERVER . 'membership/?note=mp:ivinf');
                                        exit();
                                }
                        }
                }
        }
        /**
         * Function to handle checking if a seller has a store, if so, determine if his current plan supports stores,
         * otherwise suspend store and un associate inventory by storeid if exists.  This function is run many places but usually
         * just after the user pays for membership plan or the membership plan was up/downgraded.
         *
         * @param       integer      user id
         */
        function handle_no_stores_permission($companyid = 0)
        {
                if ($companyid > 0) {
                        $sql = $this->sheel->db->query("
                                SELECT u.id, u.subscriptionid, u.active, u.cancelled
                                FROM " . DB_PREFIX . "subscription_company u
                                LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                WHERE u.user_id = '" . intval($companyid) . "'
                                        AND s.type = 'product'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                if ($res['id'] > 0 and $res['subscriptionid'] > 0) {
                                        if ($this->sheel->stores->has_store($companyid)) {
                                                $storeid = $this->sheel->stores->get_storeid($companyid);
                                                if ($res['active'] == 'no') { // inactive membership :: suspend store
                                                        if ($storeid > 0) { // suspend sellers store
                                                                $this->sheel->stores->suspend($storeid, 'Membership is currently inactive. Store suspended.');
                                                        }
                                                } else { // active membership..
                                                        if ($this->sheel->permissions->check_access(0, 'canopenstore', $res['subscriptionid']) == 'no') { // current membership does not support stores!
                                                                if ($storeid > 0) { // suspend sellers store, de-associated all items
                                                                        $this->sheel->stores->suspend($storeid, 'Membership selected does not support Stores. Store suspended.');
                                                                }
                                                        } else {
                                                                if ($storeid > 0) { // unsuspend sellers store, re-associate all items
                                                                        $this->sheel->stores->unsuspend($storeid);
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                return true;
        }
        /**
         * Function to update a users subscription plan within the AdminCP
         *
         * @param       integer      user id
         * @param       integer      membership plan id
         * @param       string       transaction description
         * @param       string       action
         */
        function subscription_upgrade_process_admincp($userid = 0, $subscriptionid = 0, $txndescription = '{_no_description}', $action = '')
        {
                $slng = ((isset($_SESSION['sheeldata']['user']['slng'])) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                AND type = 'product'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $subscription_length = $this->subscription_length($res['units'], $res['length']);
                        $subscription_renew_date = $this->print_subscription_renewal_datetime($subscription_length);
                        if ($action == 'active') { // mark invoice active for $0.00
                                $this->sheel->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status, invoicetype, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $this->sheel->db->escape_string($res['title']) . "',
                                        '0.00',
                                        '0.00',
                                        '0.00',
                                        'paid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '" . $this->sheel->db->escape_string('{_subscription_fee_waived_by_administration}') . "',
                                        '" . $this->sheel->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $this->sheel->db->insert_id();
                                $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                $sql = $this->sheel->db->query("
                                        SELECT u.id
                                        FROM " . DB_PREFIX . "subscription_company u
                                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                        WHERE u.user_id = '" . intval($userid) . "'
                                                AND s.type = 'product'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql) > 0) {
                                        $resu = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_company
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE id = '" . $resu['id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                } else {
                                        $this->sheel->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_company
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        } else if ($action == 'activepaid') { // mark invoice active and paid for it's amount
                                $txn = $this->sheel->accounting_payment->construct_transaction_id();
                                $this->sheel->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $this->sheel->db->escape_string($res['title']) . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        'paid',
                                        'subscription',
                                        'check',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '{_subscription_fee_payment_paid_outside_marketplace_thank_you_for_your_business}',
                                        '" . $txn . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $this->sheel->db->insert_id();
                                // record transaction ledger
                                $array = array(
                                        'userid' => $userid,
                                        'credit' => sprintf("%01.2f", $res['cost']),
                                        'debit' => 0,
                                        'description' => 'Credit via Cash/Check for ' . $res['title'] . ' {_subscription}',
                                        'invoiceid' => $newinvoiceid,
                                        'transactionid' => $txn,
                                        'custom' => '{_subscription_fee_payment_paid_outside_marketplace_thank_you_for_your_business}',
                                        'staffnotes' => '',
                                        'technical' => 'Account balance credit based on [subscription_upgrade_process_admincp(), action=activepaid] [URI: ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                );
                                $this->sheel->accounting->account_balance($array);
                                unset($array);
                                // record transaction ledger
                                $array = array(
                                        'userid' => $userid,
                                        'credit' => 0,
                                        'debit' => sprintf("%01.2f", $res['cost']),
                                        'description' => $res['title'] . ' {_subscription}',
                                        'invoiceid' => $newinvoiceid,
                                        'transactionid' => $txn,
                                        'custom' => '{_subscription_fee_payment_paid_outside_marketplace_thank_you_for_your_business}',
                                        'staffnotes' => '',
                                        'technical' => 'Account balance debit based on [subscription_upgrade_process_admincp(), action=activepaid] [URI ' . $_SERVER['REQUEST_URI'] . ', Line: ' . (__LINE__ + 2) . ']'
                                );
                                $this->sheel->accounting->account_balance($array);
                                unset($array);
                                $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                $sql = $this->sheel->db->query("
                                        SELECT u.id
                                        FROM " . DB_PREFIX . "subscription_company u
                                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                        WHERE u.user_id = '" . intval($userid) . "'
                                                AND s.type = 'product'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql) > 0) {
                                        $resu = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_company
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE id = '" . intval($resu['id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                } else {
                                        $this->sheel->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_company
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        } else if ($action == 'inactive') { // mark invoice inactive and unpaid requires payment from customer
                                $this->sheel->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, totalamount, paid, status, invoicetype, createdate, duedate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $this->sheel->db->escape_string($res['title']) . "',
                                        '" . $this->sheel->db->escape_string($res['cost']) . "',
                                        '" . $this->sheel->db->escape_string($res['cost']) . "',
                                        '0.00',
                                        'unpaid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '{_thank_you_for_your_continued_business}',
                                        '" . $this->sheel->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $this->sheel->db->insert_id();
                                $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                $sql = $this->sheel->db->query("
                                        SELECT u.id
                                        FROM " . DB_PREFIX . "subscription_company u
                                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                                        WHERE u.user_id = '" . intval($userid) . "'
                                                AND s.type = 'product'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql) > 0) {
                                        $resu = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_company
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'no',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE id = '" . intval($resu['id']) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                } else {
                                        $this->sheel->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_company
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $this->sheel->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'no',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
        /**
         * Function to internally check if a user has an active subscription plan (paid or free).
         *
         * @param       integer        user id
         *
         * @return      bool           Returns true or false
         */
        function has_active_subscription($userid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT u.active, u.cancelled, u.subscriptionid
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        if ($res['active'] == 'yes') {
                                if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] == $userid) {
                                        $_SESSION['sheeldata']['user']['active'] = 'yes';
                                        $_SESSION['sheeldata']['user']['subscriptionid'] = $res['subscriptionid'];
                                }
                                return true;
                        } else {
                                if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] == $userid) {
                                        $_SESSION['sheeldata']['user']['active'] = 'no';
                                        $_SESSION['sheeldata']['user']['subscriptionid'] = $res['subscriptionid'];
                                }
                        }
                }
                return false;
        }
        /**
         * Function to print a user's subscription title
         *
         * @param        integer     user id
         *
         * @return	string      Returns the subscription title
         */
        function print_subscription_title($userid = 0)
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $sql = $this->sheel->db->query("
                        SELECT u.subscriptionid, s.title_" . $slng . " AS title
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return stripslashes($res['title']);
                }
                return '{_registered_subscriber}';
        }
        /**
         * Function to print a user's subscription icon
         *
         * @param        integer     user id
         *
         * @return	string      Returns the subscription icon
         */
        function print_subscription_icon($userid = 0)
        {
                return '';
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $sql = $this->sheel->db->query("
                        SELECT u.subscriptionid, s.icon, s.title_" . $slng . " AS title
                        FROM " . DB_PREFIX . "subscription_company AS u
			LEFT JOIN " . DB_PREFIX . "subscription AS s ON u.subscriptionid = s.subscriptionid
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res2 = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return '<span title="' . o(stripslashes($res2['title'])) . '"><img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $res2['icon'] . '" border="0" alt="" style="vertical-align: middle;margin-top:-5px" /></span>';
                }
                //return '<span title="{_registered_member}"><img src="' . $this->sheel->config['imguploadscdn'] . 'plan/default.png" border="0" alt="" style="vertical-align: middle;margin-top:-5px" /></span>';
        }
        /**
         * Function to dispatch membership expiry notifications to users "x" days before the membership plan becomes expired
         * This function is run via sheel automation script (cron.dailyrfp.php)
         *
         * @param        integer     days to remind user before expiry (default 7)
         *
         * @return	string      Returns the cron log bit information to append to the cron job log for actions taken within this function
         */
        function send_subscription_expiry_reminders($reminddays = 7)
        {
                if ($this->sheel->config['subscriptions_emailexpiryreminder'] == '0') {
                        return false;
                }
                $sent = 0;
                // since this cron script will run once per day, let fetch
                // upcoming subscriptions in x days and send a friendly reminder
                // informing the user about the subscription renewal
                $remind = $this->sheel->db->query("
                        SELECT u.user_id, u.startdate, u.renewdate, u.paymethod, u.recurring, u.autorenewal, u.autopayment, u.active, s.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, s.cost
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
                        WHERE u.cancelled = '0'
                                AND s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($remind) > 0) {
                        while ($reminds = $this->sheel->db->fetch_array($remind, DB_ASSOC)) {
                                // renew date
                                $date1split = explode(' ', $reminds['renewdate']);
                                $date2split = explode('-', $date1split[0]);
                                // days left for subscription count (ex: reminder in 7 days from now)
                                $reminder = $reminddays;
                                $days = $this->sheel->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
                                if ($days == $reminder) {
                                        $user = $this->sheel->db->query("
                                                SELECT username, first_name, last_name, email
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $reminds['user_id'] . "'
                                                        AND status = 'active'
                                                        AND email != ''
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($this->sheel->db->num_rows($user) > 0) {
                                                $res_user = $this->sheel->db->fetch_array($user, DB_ASSOC);
                                                $sql_emaillog = $this->sheel->db->query("
                                                        SELECT emaillogid
                                                        FROM " . DB_PREFIX . "emaillog
                                                        WHERE logtype = 'subscriptionremind'
                                                                AND user_id = '" . $reminds['user_id'] . "'
                                                                AND date LIKE '%" . DATETODAY . "%'
                                                                AND sent = 'yes'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($this->sheel->db->num_rows($sql_emaillog) <= 0) { // user has not received this email today.. send!
                                                        $this->sheel->db->query("
                                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                                (emaillogid, logtype, user_id, date, sent)
                                                                VALUES(
                                                                NULL,
                                                                'subscriptionremind',
                                                                '" . $reminds['user_id'] . "',
                                                                '" . DATETODAY . "',
                                                                'yes')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $billingcycle = '{_none}';
                                                        if ($reminds['startdate'] != '0000-00-00 00:00:00' and $reminds['renewdate'] != '0000-00-00 00:00:00' and $reminds['active'] == 'yes') {
                                                                $billingcycle = $this->sheel->common->print_date($reminds['startdate'], 'F d, Y', 0, 0) . ' - ' . $this->sheel->common->print_date($reminds['renewdate'], 'F d, Y', 0, 0);
                                                        }
                                                        if ($reminds['paymethod'] == 'account') {
                                                                $paymentmethod = '{_account_balance}';
                                                        } else {
                                                                $paymentmethod = '{_' . $reminds['paymethod'] . '}'; // ipn, creditcard
                                                        }
                                                        $billingtype = '{_manual_billing_and_payment}';
                                                        if ($reminds['recurring']) {
                                                                $billingtype = '{_recurring_payments}';
                                                        } else if ($reminds['autorenewal']) {
                                                                $billingtype = '{_auto_plan_renewal}' . (($reminds['autopayment']) ? ' ({_account_balance_debit_lc})' : ' ({_manual_payment_lc})');
                                                        }
                                                        $this->sheel->email->mail = $res_user['email'];
                                                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($reminds['user_id']);
                                                        $this->sheel->email->get('upcoming_subscription_reminder');
                                                        $this->sheel->email->set(
                                                                array(
                                                                        '{{days}}' => $days,
                                                                        '{{customer}}' => ucfirst($res_user['first_name']),
                                                                        '{{plan}}' => $reminds['title'],
                                                                        '{{cost}}' => $this->sheel->currency->format($reminds['cost']),
                                                                        '{{billingcycle}}' => $billingcycle,
                                                                        '{{billingtype}}' => $billingtype,
                                                                        '{{paymentmethod}}' => $paymentmethod,
                                                                )
                                                        );
                                                        $this->sheel->email->send();
                                                        $sent++;
                                                }
                                        }
                                }
                        }
                }
                return 'subscription->send_subscription_expiry_reminders() [' . $sent . '], ';
        }
        /**
         * Function to dispatch newsletter digest per category based on users category preferences
         *
         * @return       string      Return string with information for cron log
         */
        function send_category_notification_subscriptions()
        {
                $new_projects_array = $seller_array = $emailsDuplicatePrevention = array();
                $sent = 0;
                $newprojects = $this->sheel->db->query("
                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                        FROM " . DB_PREFIX . "projects
                        WHERE date_added LIKE '%" . DATEYESTERDAY . "%' 
                                AND status = 'open'
                                AND project_details != 'invite_only'
                                AND visible = '1'
                ", 0, null, __FILE__, __LINE__);
                while ($row = $this->sheel->db->fetch_array($newprojects, DB_ASSOC)) {
                        $new_projects_array[] = $row;
                }
                if (count($new_projects_array) > 0) { // fetch sellers with active category subscriptions
                        $users = $this->sheel->db->query("
                                SELECT user_id, username, email, notifyproductscats, country, zip_code, city
                                FROM " . DB_PREFIX . "users
                                WHERE status = 'active'
                                        AND notifyproducts = '1'
                                        AND notifyproductscats != ''
                                        AND emailnotify = '1'
                                        AND email != ''
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($users) > 0) {
                                while ($row = $this->sheel->db->fetch_array($users, DB_ASSOC)) {
                                        if (!in_array($row['email'], $emailsDuplicatePrevention)) {
                                                $sellers[] = $row;
                                                $emailsDuplicatePrevention[] = $row['email'];
                                        }
                                }
                                unset($row);
                                if (!empty($sellers) and count($sellers) > 0) {
                                        $sent = 0;
                                        foreach ($sellers as $seller) {
                                                $messagebody = '';
                                                $requested_categories = explode(',', $seller['notifyproductscats']);
                                                $projectsToSend = array();
                                                foreach ($requested_categories as $category) {
                                                        if ($category > 0) {
                                                                $tempchildren = $this->sheel->categories->fetch_children($category, 'product');
                                                                $children = explode(',', $tempchildren);
                                                                unset($tempchildren);
                                                                foreach ($new_projects_array as $new_project) {
                                                                        if (in_array($new_project['cid'], $children)) {
                                                                                $projectsToSend[] = $new_project;
                                                                        }
                                                                }
                                                        }
                                                }
                                                if (count($projectsToSend) > 0) {
                                                        $c = 0;
                                                        foreach ($projectsToSend as $project) {
                                                                $c++;
                                                                if ($c <= 50) {
                                                                        $buyerinfo = $this->sheel->db->query("
                                                                                SELECT username
                                                                                FROM " . DB_PREFIX . "users
                                                                                WHERE user_id = '" . $project['user_id'] . "'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($this->sheel->db->num_rows($buyerinfo) > 0) {
                                                                                $res_buyer_name = $this->sheel->db->fetch_array($buyerinfo, DB_ASSOC);
                                                                                $messagebody .= $this->sheel->censor->strip_vulgar_words(stripslashes($project['project_title'])) . "\n";
                                                                                $url = $this->sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $project['project_id'], 'name' => stripslashes($project['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
                                                                                $messagebody .= $url . "\n";
                                                                                $messagebody .= '{_category}: ' . strip_tags($this->sheel->categories->recursive($project['cid'], 'product', $this->sheel->language->fetch_user_slng($seller['user_id']), 1)) . "\n";
                                                                                $messagebody .= '{_seller}: ' . $res_buyer_name['username'] . "\n";
                                                                                $messagebody .= '{_ends}: ' . $this->sheel->common->print_date($project['date_end']) . "\n";
                                                                                $messagebody .= "************\n";
                                                                        }
                                                                }
                                                        }
                                                        if ($c > 50) {
                                                                $messagebody .= "\n-------- [Plus " . number_format($c - 50) . " other listings] --------\n";
                                                        }
                                                        $messagebody .= "\n";
                                                        $messagebody .= '{_sell_merchandise_via_product_auctions}:' . "\n";
                                                        $messagebody .= HTTPS_SERVER . "sell/\n\n";
                                                        $messagebody .= '{_browse_product_auctions_and_other_merchandise}:' . "\n";
                                                        $messagebody .= HTTPS_SERVER . "catalog/\n\n";
                                                        $messagebody .= "************\n";
                                                        $messagebody .= '{_please_contact_us_if_you_require_any_additional_information_were_always_here_to_help}';
                                                        $sql_emaillog = $this->sheel->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "emaillog
                                                                WHERE logtype = 'dailyproduct'
                                                                        AND user_id = '" . $seller['user_id'] . "'
                                                                        AND date LIKE '%" . DATETODAY . "%'
                                                                        AND sent = 'yes'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($this->sheel->db->num_rows($sql_emaillog) == 0) { // user didn't receive this email today..
                                                                $this->sheel->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "emaillog
                                                                        (emaillogid, logtype, user_id, date, sent)
                                                                        VALUES(
                                                                        NULL,
                                                                        'dailyproduct',
                                                                        '" . $seller['user_id'] . "',
                                                                        '" . DATETODAY . "',
                                                                        'yes')
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // just for reference so we can show the user the exact date we sent last email
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET lastemailproductcats = '" . DATETODAY . "'
                                                                        WHERE user_id = '" . $seller['user_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                $this->sheel->email->mail = $seller['email'];
                                                                $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($seller['user_id']);
                                                                $this->sheel->email->get('cron_daily_auction_newsletter');
                                                                $this->sheel->email->set(
                                                                        array(
                                                                                '{{username}}' => $this->sheel->fetch_user('username', $seller['user_id']),
                                                                                '{{newsletterbody}}' => $messagebody,
                                                                                '{{total}}' => count($projectsToSend),
                                                                        )
                                                                );
                                                                $this->sheel->email->send();
                                                                $sent++;
                                                        }
                                                }
                                        }
                                }
                                unset($sellers);
                        }
                }
                return 'subscription->send_category_notification_subscriptions() [' . $sent . '], ';
        }
        /**
         * Function to cancel any scheduled subscription invoices based on a timer which the admin defines in max days of invoice cancellation
         *
         * @return       string      Return string with information for cron log
         */
        function cancel_scheduled_subscription_invoices()
        {
                $schsub = $this->sheel->db->query("
                        SELECT invoiceid, createdate
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                                AND (status = 'unpaid' OR status = 'scheduled')
                                AND paiddate = '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($schsub) > 0) {
                        while ($unpaid = $this->sheel->db->fetch_array($schsub, DB_ASSOC)) {
                                $date1split = explode(' ', $unpaid['createdate']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldaysunpaid = $this->sheel->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
                                if ($totaldaysunpaid > $this->sheel->config['invoicesystem_maximumpaymentdays']) { // cancel this scheduled membership invoice (admin defined max days)
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET status = 'cancelled',
                                                canceldate = '" . DATETIME24H . "',
                                                custommessage = 'Automatically cancelled invoice due to non-payment over $totaldaysunpaid days.'
                                                WHERE invoiceid = '" . $unpaid['invoiceid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        // remove ID from subscription_company table..
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_company
                                                SET invoiceid = '0'
                                                WHERE invoiceid = '" . $unpaid['invoiceid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        // email customer previous invoice was deleted? manually re-subscribe?
                                        // ..
                                }
                        }
                }
                return 'subscription->cancel_scheduled_subscription_invoices(), ';
        }
        /**
         * Function designed to send out membership reminder notices based on an admin defined email dispatch frequency
         * This function is called from cron.30minute.php
         *
         * @return       string      Return string with information for cron log
         */
        function send_user_subscription_frequency_reminders()
        {
                $cronlog = '';
                $count = 0;
                $remindfrequency = $this->sheel->datetimes->fetch_date_fromnow($this->sheel->config['invoicesystem_resendfrequency']);
                $expiry = $this->sheel->db->query("
                        SELECT user_id, invoiceid, invoicetype, createdate, description, amount, paid, totalamount, invoicetype, duedate, transactionid, istaxable, taxamount, taxinfo
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                                AND (status = 'unpaid' OR status = 'scheduled')
                                AND amount > 0
                                AND totalamount > 0
                                AND archive = '0'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($expiry) > 0) {
                        while ($reminder = $this->sheel->db->fetch_array($expiry, DB_ASSOC)) {
                                $user = $this->sheel->db->query("
                                        SELECT email, first_name, last_name, username
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $reminder['user_id'] . "'
                                                AND status = 'active'
                                                AND email != ''
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($user) > 0) {
                                        $res_user = $this->sheel->db->fetch_array($user, DB_ASSOC);
                                        $logs = $this->sheel->db->query("
                                                SELECT invoicelogid, date_sent, date_remind
                                                FROM " . DB_PREFIX . "invoicelog
                                                WHERE user_id = '" . $reminder['user_id'] . "'
                                                        AND invoiceid = '" . $reminder['invoiceid'] . "'
                                                ORDER BY invoicelogid DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($this->sheel->db->num_rows($logs) == 0) {
                                                $this->sheel->db->query("
                                                        INSERT INTO " . DB_PREFIX . "invoicelog
                                                        (invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
                                                        VALUES(
                                                        NULL,
                                                        '" . $reminder['user_id'] . "',
                                                        '" . $reminder['invoiceid'] . "',
                                                        '" . $reminder['invoicetype'] . "',
                                                        '" . DATETODAY . "',
                                                        '" . $remindfrequency . "')
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($this->sheel->config['invoicesystem_unpaidreminders']) {
                                                        $crypted = array('id' => $reminder['invoiceid']);
                                                        $invoiceurl = HTTPS_SERVER . 'pay/?crypted=' . $this->sheel->encrypt_url($crypted);
                                                        $amounts = '';
                                                        if ($reminder['istaxable'] and $reminder['taxamount'] > 0) {
                                                                $amounts = "{_sub_total}: " . $this->sheel->currency->format($reminder['amount']) . LINEBREAK;
                                                                $amounts .= "{_tax}: " . $this->sheel->currency->format($reminder['taxamount']) . LINEBREAK;
                                                                $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                        } else if ($reminder['amount'] == $reminder['totalamount']) {
                                                                $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                        } else {
                                                                $amounts = "{_sub_total}: " . $this->sheel->currency->format($reminder['amount']) . LINEBREAK;
                                                                $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                        }
                                                        $this->sheel->email->mail = $res_user['email'];
                                                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($reminder['user_id']);
                                                        $this->sheel->email->get('cron_expired_subscription_invoice_reminder');
                                                        $this->sheel->email->set(
                                                                array(
                                                                        '{{username}}' => $res_user['username'],
                                                                        '{{firstname}}' => $res_user['first_name'],
                                                                        '{{description}}' => $reminder['description'],
                                                                        '{{transactionid}}' => $reminder['transactionid'],
                                                                        '{{paid}}' => $this->sheel->currency->format($reminder['paid']),
                                                                        '{{duedate}}' => $this->sheel->common->print_date($reminder['duedate']),
                                                                        '{{invoiceid}}' => $reminder['invoiceid'],
                                                                        '{{reminddate}}' => $remindfrequency,
                                                                        '{{membershipurl}}' => HTTPS_SERVER . 'membership/',
                                                                        '{{invoiceurl}}' => $invoiceurl,
                                                                        '{{amounts}}' => $amounts
                                                                )
                                                        );
                                                        $this->sheel->email->send();
                                                        $count++;
                                                }
                                        } else if ($this->sheel->db->num_rows($logs) > 0) { // it appears we have a log for this invoice id ..
                                                $reslogs = $this->sheel->db->fetch_array($logs, DB_ASSOC);
                                                // time to send an update to this user for this invoice
                                                // make sure we didn't already send one today
                                                if ($reslogs['date_remind'] == DATETODAY and $reslogs['date_sent'] == DATETODAY) { // we've sent a reminder to this user for this invoice today already.. do nothing until next reminder frequency

                                                } else if ($reslogs['date_remind'] == DATETODAY and $reslogs['date_sent'] != DATETODAY) { // time to send a new frequency reminder.. update table with new email sent date as today
                                                        $this->sheel->db->query("
                                                                UPDATE " . DB_PREFIX . "invoicelog
                                                                SET date_sent = '" . DATETODAY . "',
                                                                date_remind = '" . $remindfrequency . "'
                                                                WHERE invoiceid = '" . $reminder['invoiceid'] . "'
                                                                        AND user_id = '" . $reminder['user_id'] . "'
                                                        ");
                                                        if ($this->sheel->config['invoicesystem_unpaidreminders']) {
                                                                $crypted = array('id' => $reminder['invoiceid']);
                                                                $invoiceurl = HTTPS_SERVER . 'pay/?crypted=' . $this->sheel->encrypt_url($crypted);
                                                                $amounts = '';
                                                                if ($reminder['istaxable'] and $reminder['taxamount'] > 0) {
                                                                        $amounts = "{_sub_total}: " . $this->sheel->currency->format($reminder['amount']) . LINEBREAK;
                                                                        $amounts .= "{_tax}: " . $this->sheel->currency->format($reminder['taxamount']) . LINEBREAK;
                                                                        $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                                } else if ($reminder['amount'] == $reminder['totalamount']) {
                                                                        $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                                } else {
                                                                        $amounts = "{_sub_total}: " . $this->sheel->currency->format($reminder['amount']) . LINEBREAK;
                                                                        $amounts .= "{_total_amount}: " . $this->sheel->currency->format($reminder['totalamount']) . LINEBREAK;
                                                                }
                                                                $this->sheel->email->mail = $res_user['email'];
                                                                $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($reminder['user_id']);
                                                                $this->sheel->email->get('cron_expired_subscription_invoice_reminder');
                                                                $this->sheel->email->set(
                                                                        array(
                                                                                '{{username}}' => $res_user['username'],
                                                                                '{{firstname}}' => $res_user['first_name'],
                                                                                '{{description}}' => $reminder['description'],
                                                                                '{{transactionid}}' => $reminder['transactionid'],
                                                                                '{{paid}}' => $this->sheel->currency->format($reminder['paid']),
                                                                                '{{duedate}}' => $this->sheel->common->print_date($reminder['duedate']),
                                                                                '{{invoiceid}}' => $reminder['invoiceid'],
                                                                                '{{reminddate}}' => $remindfrequency,
                                                                                '{{membershipurl}}' => HTTPS_SERVER . 'membership/',
                                                                                '{{invoiceurl}}' => $invoiceurl,
                                                                                '{{amounts}}' => $amounts
                                                                        )
                                                                );
                                                                $this->sheel->email->send();
                                                                $count++;
                                                        }
                                                }
                                        }
                                }
                        }
                }
                if ($count > 0) {
                        $cronlog .= 'subscription->send_user_subscription_frequency_reminders() [' . $count . ' sent], ';
                }
                return $cronlog;
        }
        /**
         * Function to dispatch emails based on users saved searches where they choose to opt-in
         * This function is run via sheel automation script (cron.dailyrfp.php)
         *
         * @param        integer     limit (default 50)
         * @param        boolean     force email to send always when function is called (default false)
         *
         * @return       string      Return string with information for cron log
         */
        function send_saved_search_subscriptions($limit = 50, $forceemail = false)
        {
                if ($this->sheel->config['savedsearches'] == false) {
                        return;
                }
                $limit = intval($limit);
                // select all subscriptions from search_favorites where subscribed = 1 and lastsent != today for products
                $sqlextra = "AND lastsent NOT LIKE '%" . DATETODAY . "%'";
                if ($forceemail) {
                        $sqlextra = "";
                }
                $sql = $this->sheel->db->query("
                        SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                        FROM " . DB_PREFIX . "search_favorites
                        WHERE cattype = 'product'
                                AND subscribed = '1'
                        $sqlextra
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $lastseen = $lastseenids = $last = array();
                                $url = HTTPS_SERVER . stripslashes(urldecode($res['searchoptions'])) . (((strrchr(urldecode($res['searchoptions']), '?') == false)) ? '?do=array&list=list' : '&do=array&list=list');
                                $c = curl_init();
                                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($c, CURLOPT_URL, $url);
                                $results = curl_exec($c);
                                curl_close($c);
                                if (!empty($res['lastseenids']) and $this->sheel->is_serialized($res['lastseenids'])) {
                                        $lastseen = unserialize($res['lastseenids']);
                                }
                                if (!empty($results)) {
                                        $results = urldecode($results);
                                        if ($this->sheel->is_serialized($results)) {
                                                $results = unserialize($results);
                                                $messagebody = '<table border="0" cellspacing="0" cellpadding="12">';
                                                $sent = 0;
                                                foreach ($results as $key => $listing) { // items found
                                                        foreach ($listing as $field => $value) { // fields
                                                                if ($field == 'project_id' and !in_array($value, $lastseen)) { // save item id's so we don't resend duplicates in future (on a different day) for user
                                                                        $lastseenids[] = $value;
                                                                }
                                                        }
                                                        if ($sent <= $limit) {
                                                                $url = $this->sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $listing['project_id'], 'name' => stripslashes($listing['title_plain']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
                                                                $picturedata = array('url' => $url, 'mode' => '108x108', 'projectid' => $listing['project_id'], 'start_from_image' => 0, 'attachtype' => '', 'httponly' => true, 'limit' => 1, 'forcenoribbon' => true, 'forceplainimg' => false, 'forceimgsrc' => false);
                                                                $item_photo = $this->sheel->auction->print_item_photo($picturedata);

                                                                $listing['currentbid_plain'] = isset($listing['currentbid_plain']) ? $listing['currentbid_plain'] : '-';
                                                                $messagebody .= '<tr><td width="50" align="left">' . $item_photo . '</td><td align="left"><span style="font-size:13px;color:#900"><a href="' . $url . '">' . $this->sheel->censor->strip_vulgar_words($this->sheel->common->un_htmlspecialchars(stripslashes($listing['title_plain']))) . '</a></span> <span style="font-size:13px;color:black">(' . $listing['project_id'] . ')</span><br /><span style="font-size:13px;color:black">{_ends}: ' . $listing['endtime'] . '</span></td></tr>';
                                                                $sent++;
                                                        }
                                                }
                                                $messagebody .= '</table>';
                                        }
                                }
                                if (!empty($lastseenids) and is_array($lastseenids)) {
                                        if (!empty($lastseen) and is_array($lastseen)) {
                                                $last = array_merge($lastseenids, $lastseen);
                                        } else {
                                                $last = $lastseenids;
                                        }
                                        $this->sheel->email->mail = $this->sheel->fetch_user('email', $res['user_id']);
                                        $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($res['user_id']);
                                        $this->sheel->email->logtype = 'alert';
                                        $this->sheel->email->get('cron_send_product_saved_searches');
                                        $this->sheel->email->set(
                                                array(
                                                        '{{searchtitle}}' => $this->sheel->common->un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => $this->sheel->common->un_htmlspecialchars($res['searchoptionstext']),
                                                        '{{username}}' => $this->sheel->fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => $messagebody,
                                                )
                                        );
                                        $this->sheel->email->send();
                                        $last = serialize($last);
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "search_favorites
                                                SET lastseenids = '" . $this->sheel->db->escape_string($last) . "',
                                                lastsent = '" . DATETIME24H . "'
                                                WHERE searchid = '" . $res['searchid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                return 'subscription->send_saved_search_subscriptions(), ';
        }
        /**
         * Function to expire the saved search subscriptions after x days defined in the argument
         *
         * @param        integer     days (default 30)
         */
        function expire_saved_search_subscriptions($days = 30)
        {
                if ($this->sheel->config['savedsearches'] == false) {
                        return;
                }
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "search_favorites
                        SET subscribed = '0',
                        lastseenids = ''
                        WHERE added < DATE_SUB(CURDATE(), INTERVAL $days DAY)
                ", 0, null, __FILE__, __LINE__);
                return 'subscription->expire_saved_search_subscriptions(), ';
        }
        /**
         * Function to fetch the roleid of a particular subscription plan id
         *
         * @param        integer     subscription id
         *
         * @return	integer     Returns the role id
         */
        function fetch_subscription_roleid($subscriptionid = 0)
        {
                $roleid = 0;
                $sql = $this->sheel->db->query("
                        SELECT roleid
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                AND type = 'product'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $roleid = $res['roleid'];
                        }
                }
                return $roleid;
        }
        /**
         * Function to determine if any user membership plans need to be removed due to users being deleted from other functions within the user table
         *
         * @return	boolean     Returns true or false
         */
        function remove_unlinked_memberships()
        {
                $sql = $this->sheel->db->query("
                        SELECT u.id, u.user_id
                        FROM " . DB_PREFIX . "subscription_company u
                        LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
                        WHERE s.type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $sql2 = $this->sheel->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $res['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql2) == 0) {
                                        $this->sheel->db->query("
                                                DELETE FROM " . DB_PREFIX . "subscription_company
                                                WHERE id = '" . $res['id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                return 'subscription->remove_unlinked_memberships(), ';
        }
        /**
         * Function to fetch a duration unit
         *
         * @param       string         unit type (D, M or Y)
         *
         * @return      string         Returns the actual unit type phrase in the appropriate language (Day, Month, Year)
         */
        function print_unit($unit = '')
        {
                if (!empty($unit)) {
                        switch ($unit) {
                                case 'D': {
                                                return '{_unit_d}';
                                                break;
                                        }
                                case 'M': {
                                                return '{_unit_m}';
                                                break;
                                        }
                                case 'Y': {
                                                return '{_unit_y}';
                                                break;
                                        }
                        }
                }
                return '';
        }
        function print_subscription_renewal_date($days)
        {
                return date('Y-m-d', (TIMESTAMPNOW + intval($days) * 24 * 3600));
        }
        /**
         * Function to print the subscription renewal date/timestamp based on days.
         *
         * @param       integer        days
         *
         * @return      string         Returns datetime stamp (ie: 2007-02-01 22:00:00)
         */
        function print_subscription_renewal_datetime($days)
        {
                return date('Y-m-d H:i:s', (TIMESTAMPNOW + intval($days) * 24 * 3600));
        }
        function monthly_user_counts_reset()
        {
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET bidsthismonth = '0',
                        auctiondelists = '0',
                        bidretracts = '0',
                        listingsthismonth = '0'
                ", 0, null, __FILE__, __LINE__);
                return 'subscription->monthly_user_counts_reset(), ';
        }
        function transaction_fees($gateway = '')
        {
                $types = array();
                $sql = $this->sheel->db->query("
			SELECT groupname, moduletype
			FROM " . DB_PREFIX . "payment_groups
			WHERE moduletype = 'ipn' OR moduletype = 'gateway'
		");
                while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) { // 'authnet' => 'cc_' vs 'paypal' => 'paypal_',
                        $types[$res['groupname']] = (($res['moduletype'] == 'gateway') ? 'cc_' : $res['groupname'] . '_');
                }
                $prefix = $types[$gateway]; // cc_ or paypal_
                $fee1 = $this->sheel->db->fetch_field(DB_PREFIX . "payment_configuration", "configgroup = '" . $this->sheel->db->escape_string($gateway) . "' AND name = '" . $this->sheel->db->escape_string($prefix) . "transaction_fee'", "value");
                $fee2 = $this->sheel->db->fetch_field(DB_PREFIX . "payment_configuration", "configgroup = '" . $this->sheel->db->escape_string($gateway) . "' AND name = '" . $this->sheel->db->escape_string($prefix) . "transaction_fee2'", "value");
                return array('fee1' => $fee1, 'fee2' => $fee2);
        }
}
?>
