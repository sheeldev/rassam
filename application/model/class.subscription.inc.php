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
			FROM " . DB_PREFIX . "subscription_customer u
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
                        FROM " . DB_PREFIX . "subscription_customer u
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

        function getname($subscriptionid)
        {
                $slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
                $name = '';
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . $subscriptionid . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $name = stripslashes(o($res['title']));
                        }
                }
                return $name;
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
}
?>