<?php
/**
 * AdminCP class to perform the majority of functions within the sheel Admin Control Panel
 *
 * @package      Sheel\AdminCP
 * @version      1.0.0.0
 * @author       sheel
 */
class admincp
{
        protected $sheel;
        var $acceptedips = array();

        public function __construct($sheel)
        {
                $this->sheel = $sheel;
                $this->acceptedips = ((!defined('ACCEPTEDIPS')) ? array('192.187.114.178', '192.187.114.181') : ACCEPTEDIPS);
        }
        /**
         * Function to calculate the sum of the total users logged into the marketplace
         *
         * @param       integer        user id
         *
         * @return      string         Returns total members online count in phrase format (ie: 3 members online)
         */
        function members_online($verbose = true)
        {
                $sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "token
			FROM " . DB_PREFIX . "sessions
			GROUP BY token
		", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        if ($this->sheel->db->num_rows($sql) == 1) {
                                if ($verbose) {
                                        return '<span id="usersonlinecount">' . (int) $this->sheel->db->num_rows($sql) . ' {_member_online}</span>';
                                }
                                return (int) $this->sheel->db->num_rows($sql);
                        } else {
                                if ($verbose) {
                                        return '<span id="usersonlinecount">' . (int) $this->sheel->db->num_rows($sql) . ' {_members_online}</span>';
                                }
                                return (int) $this->sheel->db->num_rows($sql);
                        }
                }
                if ($verbose) {
                        return '<span id="usersonlinecount">{_one_member_online}</span>';
                }
                return 1;
        }
        /**
         * Function to fetch sites/users limit
         *
         * @return      string        Returns formatted number (3,201) or string (Unlimited)
         */
        function usersitelimits()
        {
                $userlimit = $this->sheel->config['user_limit'];
                $sitelimit = $this->sheel->config['site_limit'];
                $ul = 'WypddXNlcmxpbWl0Wypd';
                $sl = 'Wypdc2l0ZWxpbWl0Wypd';
                if ($userlimit == base64_decode($ul)) {
                        $userlimit = 5000;
                }
                if ($sitelimit == base64_decode($sl)) {
                        $sitelimit = 1;
                }
                if ($userlimit == -1 or $userlimit == '-1') {
                        $userlimit = '{_unlimited}';
                } else {
                        $userlimit = number_format($userlimit);
                }
                if ($sitelimit == -1 or $sitelimit == '-1') {
                        $sitelimit = '{_unlimited}';
                } else {
                        $sitelimit = number_format($sitelimit);
                }
                return array('userlimit' => $userlimit, 'sitelimit' => $sitelimit);
        }
        /**
         * Function to print an action was successful used mainly within the AdminCP
         *
         * @param        string      success message to display
         * @param        string      redirect to url location
         *
         * @return	string      Returns the HTML representation of the action success template
         */
        function print_action_success($notice = '', $admurl = '')
        {
                if (($sidenav = $this->sheel->cache->fetch("sidenav_actionsuccess")) === false) {
                        $sidenav = $this->sheel->admincp_nav->print('actionsuccess');
                        $this->sheel->cache->store("sidenav_actionsuccess", $sidenav);
                }
                $userid = isset($_SESSION['sheeldata']['user']['userid']) ? $_SESSION['sheeldata']['user']['userid'] : 0;
                $details = "success\n" . $this->sheel->array2string($this->sheel->GPC);
                $this->sheel->log_event($userid, basename(__FILE__), $details);
                $this->sheel->template->meta['jsinclude'] = array('header' => array('functions', 'admin'), 'footer' => array());
                $this->sheel->template->fetch('main', 'action_success.html', 1);
                $this->sheel->template->parse_hash('main', array('ilpage' => $this->sheel->ilpage));
                $this->sheel->template->pprint('main', array('notice' => $notice, 'admurl' => $admurl, 'sidenav' => $sidenav));
                exit();
        }
        /**
         * Function to print an action failed used mainly within the AdminCP
         *
         * @param        string      error message to display
         * @param        string      url that error action occured
         *
         * @return	string      Returns the HTML representation of the action failed template
         */
        function print_action_failed($error = '', $admurl = '')
        {
                if (($sidenav = $this->sheel->cache->fetch("sidenav_actionfailed")) === false) {
                        $sidenav = $this->sheel->admincp_nav->print('actionfailed');
                        $this->sheel->cache->store("sidenav_actionfailed", $sidenav);
                }
                $userid = isset($_SESSION['sheeldata']['user']['userid']) ? $_SESSION['sheeldata']['user']['userid'] : 0;
                $details = "failure\n" . $this->sheel->array2string($this->sheel->GPC);
                $this->sheel->log_event($userid, basename(__FILE__), $details);
                $this->sheel->template->meta['jsinclude'] = array('header' => array('functions', 'admin'), 'footer' => array());
                $this->sheel->template->fetch('main', 'action_failed.html', 1);
                $this->sheel->template->parse_hash('main', array('ilpage' => $this->sheel->ilpage));
                $this->sheel->template->pprint('main', array('error' => $error, 'admurl' => $admurl, 'sidenav' => $sidenav));
                exit();
        }
        /**
         * Function for inserting a new bid increment in the database.
         *
         * @param       string       increment from
         * @param       string       increment to
         * @param       string       increment amount
         * @param       integer      category id (optional)
         * @param       integer      display sort order in the admincp
         * @param       string       bid increment group name
         */
        function insert_bid_increment($from = 0, $to = 0, $amount = 0, $cid = 0, $sort = 0, $groupname = '')
        {
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "increments
                        (incrementid, groupname, increment_from, increment_to, amount, sort, cid)
                        VALUES(
                        NULL,
                        '" . $this->sheel->db->escape_string($groupname) . "',
                        '" . $this->sheel->db->escape_string($from) . "',
                        '" . $this->sheel->db->escape_string($to) . "',
                        '" . $this->sheel->db->escape_string($amount) . "',
                        '" . intval($sort) . "',
                        '" . intval($cid) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function for inserting a new insertion group.
         *
         * @param       string       insertion group
         * @param       string       state
         * @param       string       description
         */
        function insert_insertion_group($groupname = '', $state = '', $description = '')
        {
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "insertion_groups
                        (groupid, groupname, description, state)
                        VALUES(
                        NULL,
                        '" . $this->sheel->db->escape_string($groupname) . "',
                        '" . $this->sheel->db->escape_string($description) . "',
                        '" . $this->sheel->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function for inserting a new final value group.
         *
         * @param       string       final value group
         * @param       string       state
         * @param       string       description
         */
        function insert_fv_group($groupname = '', $state = '', $description = '')
        {
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "finalvalue_groups
                        (groupid, groupname, description, state)
                        VALUES(
                        NULL,
                        '" . $this->sheel->db->escape_string($groupname) . "',
                        '" . $this->sheel->db->escape_string($description) . "',
                        '" . $this->sheel->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function for inserting a new buyer premium fee group.
         *
         * @param       string       buyer premium fee group
         * @param       string       state
         * @param       string       description
         */
        function insert_pf_group($groupname = '', $state = '', $description = '')
        {
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "buyerpremium_groups
                        (groupid, groupname, description, state)
                        VALUES(
                        NULL,
                        '" . $this->sheel->db->escape_string($groupname) . "',
                        '" . $this->sheel->db->escape_string($description) . "',
                        '" . $this->sheel->db->escape_string($state) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function for removing an existing bid increment group.  This function will additionally deassociate any categories using this group.
         *
         * @param       integer      group id
         */
        function remove_increment_group($groupid = 0)
        {
                $groupname = $this->sheel->db->fetch_field(DB_PREFIX . "increments_groups", "groupid = '" . intval($groupid) . "'", "groupname");
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "increments_groups
                        WHERE groupid = '" . intval($groupid) . "'
                ", 0, null, __FILE__, __LINE__);
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "increments
                        WHERE groupname = '" . $this->sheel->db->escape_string($groupname) . "'
                ", 0, null, __FILE__, __LINE__);
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "categories
                        SET incrementgroup = ''
                        WHERE incrementgroup = '" . $this->sheel->db->escape_string($groupname) . "'
                                AND cattype = 'product'
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function for inserting a new bid increment group.  Additionally, the groupname will be replaced with underscores if the string contains spaces.
         *
         * @param       string       group name
         * @param       string       description
         */
        function insert_increment_group($groupname = '', $description = '')
        {
                $groupname = str_replace(' ', '_', $groupname);
                $groupname = mb_strtolower($groupname);
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "increments_groups
                        (groupid, groupname, description)
                        VALUES(
                        NULL,
                        '" . $this->sheel->db->escape_string($groupname) . "',
                        '" . $this->sheel->db->escape_string($description) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to update an existing increment group
         *
         * @param       integer      group id
         * @param       string       group name
         * @param       string       description
         */
        function update_increment_group($groupid = 0, $newgroupname = '', $newdescription = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "increments_groups
                        WHERE groupid = '" . intval($groupid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET incrementgroup = '" . $this->sheel->db->escape_string($newgroupname) . "'
                                WHERE incrementgroup = '" . $this->sheel->db->escape_string($res['groupname']) . "'
                                    AND cattype = 'product'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "increments
                                SET groupname = '" . $this->sheel->db->escape_string($newgroupname) . "'
                                WHERE groupname = '" . $this->sheel->db->escape_string($res['groupname']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "increments_groups
                                SET groupname = '" . $this->sheel->db->escape_string($newgroupname) . "',
                                description = '" . $this->sheel->db->escape_string($newdescription) . "'
                                WHERE groupid = '" . intval($groupid) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        /**
         * Function to remove an existing bid increment.
         *
         * @param       integer      increment id
         */
        function remove_bid_increment($id = 0)
        {
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "increments
                        WHERE incrementid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to remove an existing insertion fee.
         *
         * @param       integer      insertion fee id
         */
        function remove_insertion_fee($id = 0)
        {
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "insertion_fees
                        WHERE insertionid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to remove an existing final value fee.
         *
         * @param       integer      final value fee id
         */
        function remove_fv_fee($id = 0)
        {
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "finalvalue
                        WHERE tierid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to remove an existing buyer premium fee.
         *
         * @param       integer      buyer premium fee id
         */
        function remove_pf_fee($id = 0)
        {
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "buyerpremium
                        WHERE tierid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to remove an existing insertion group.
         *
         * @param       integer      insertion group id
         */
        function remove_insertion_group($id = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT groupname, state
                        FROM " . DB_PREFIX . "insertion_groups
                        WHERE groupid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND state = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "insertion_groups
                                WHERE groupid = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET insertiongroup = '0'
                                WHERE insertiongroup = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND cattype = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "subscription_permissions
                                SET value = '0'
                                WHERE accessname = '{$result['state']}insgroup'
                                        AND value = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        /**
         * Function to remove an existing final value group
         *
         * @param       integer      final value group id
         */
        function remove_fv_group($id = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT groupname, state
                        FROM " . DB_PREFIX . "finalvalue_groups
                        WHERE groupid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "finalvalue
                                WHERE groupname = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND state = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "finalvalue_groups
                                WHERE groupid = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET finalvaluegroup = '0'
                                WHERE finalvaluegroup = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND cattype = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "subscription_permissions
                                SET value = '0'
                                WHERE accessname = '{$result['state']}fvfgroup'
                                        AND value = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
        /**
         * Function to remove an existing buyer premium fee group and all associated fee ranges
         *
         * @param       integer      buyer premium fee group id
         */
        function remove_pf_group($id = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT groupname, state
                        FROM " . DB_PREFIX . "buyerpremium_groups
                        WHERE groupid = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "buyerpremium
                                WHERE groupname = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND state = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "buyerpremium_groups
                                WHERE groupid = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET premiumgroup = '0'
                                WHERE premiumgroup = '" . $this->sheel->db->escape_string($result['groupname']) . "'
                                        AND cattype = '" . $this->sheel->db->escape_string($result['state']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        /*$this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "subscription_permissions
                        SET value = '0'
                        WHERE accessname = '{$result['state']}buyerpremiumgroup'
                        AND value = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);*/
                }
        }
        /**
         * Function to remove a registration question from the database.
         *
         * @param       integer      registration question id
         */
        function remove_registration_question($id = 0)
        {
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "register_answers
                        WHERE questionid = '" . intval($id) . "'
                ", 0, null, __FILE__, __LINE__);
                $this->sheel->db->query("
                        DELETE FROM " . DB_PREFIX . "register_questions
                        WHERE questionid = '" . intval($id) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to update a registration question form to the database.
         *
         * @param       integer      registration form array
         */
        function update_registration_question($form = array())
        {

                $visible = isset($form['form']['visible']) ? intval($form['form']['visible']) : 0;
                $required = isset($form['form']['required']) ? intval($form['form']['required']) : 0;
                $profile = isset($form['form']['public']) ? intval($form['form']['public']) : 0;
                $guests = isset($form['form']['guests']) ? intval($form['form']['guests']) : 0;
                $displayvalues = isset($form['form']['multiplechoice']) ? $form['form']['multiplechoice'] : '';
                $sort = isset($form['form']['sort']) ? intval($form['form']['sort']) : 0;
                $formdefault = isset($form['form']['formdefault']) ? $form['form']['formdefault'] : '';
                $roleid = $query1 = $query2 = '';
                if (isset($form['form']['roleid'])) {
                        if (is_array($form['form']['roleid'])) {
                                foreach ($form['form']['roleid'] as $key => $value) {
                                        $roleid .= !empty($roleid) ? '|' . $value : $value;
                                }
                        } else {
                                $roleid = $form['form']['roleid'];
                        }
                }
                if (!empty($form['form']['question']) and !empty($form['form']['description'])) {
                        foreach ($form['form']['question'] as $slng => $value) {
                                $query1 .= "question_" . mb_strtolower($slng) . " = '" . $this->sheel->db->escape_string($value) . "',";
                        }
                        foreach ($form['form']['description'] as $slng => $value) {
                                $query2 .= "description_" . mb_strtolower($slng) . " = '" . $this->sheel->db->escape_string($value) . "',";
                        }
                }
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "register_questions
                        SET pageid = '" . intval($form['form']['pageid']) . "',
                        $query1
                        $query2
                        inputtype = '" . $this->sheel->db->escape_string($form['form']['inputtype']) . "',
                        formname = '" . $this->sheel->db->escape_string($form['form']['formname']) . "',
                        formdefault = '" . $this->sheel->db->escape_string($form['form']['default']) . "',
                        sort = '" . intval($form['form']['sort']) . "',
                        visible = '" . intval($visible) . "',
                        required = '" . intval($required) . "',
                        profile = '" . intval($profile) . "',
                        multiplechoice = '" . $this->sheel->db->escape_string($displayvalues) . "',
                        guests = '" . intval($guests) . "',
                        roleid = '" . $this->sheel->db->escape_string($roleid) . "'
                        WHERE questionid = '" . intval($form['id']) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to insert a registration question form to the database.
         *
         * @param       integer      registration form array
         */
        function insert_registration_question($form = array())
        {
                $visible = isset($form['form']['visible']) ? intval($form['form']['visible']) : 0;
                $required = isset($form['form']['required']) ? intval($form['form']['required']) : 0;
                $profile = isset($form['form']['public']) ? intval($form['form']['public']) : 0;
                $guests = isset($form['form']['guests']) ? intval($form['form']['guests']) : 0;
                $displayvalues = isset($form['form']['multiplechoice']) ? $form['form']['multiplechoice'] : '';
                $sort = isset($form['form']['sort']) ? intval($form['form']['sort']) : 0;
                $formdefault = isset($form['form']['formdefault']) ? $form['form']['formdefault'] : '';
                $roleid = '';
                if (isset($form['form']['roleid'])) {
                        if (is_array($form['form']['roleid'])) {
                                foreach ($form['form']['roleid'] as $key => $value) {
                                        $roleid .= !empty($roleid) ? '|' . $value : $value;
                                }
                        } else {
                                $roleid = intval($form['form']['roleid']);
                        }
                }
                $this->sheel->db->query("
			INSERT INTO " . DB_PREFIX . "register_questions
			(questionid, pageid, formname, formdefault, inputtype, multiplechoice, sort, required, profile, guests, roleid)
			VALUES(
			NULL,
			'" . intval($form['form']['pageid']) . "',
			'" . $this->sheel->db->escape_string($form['form']['formname']) . "',
			'" . $this->sheel->db->escape_string($formdefault) . "',
			'" . $this->sheel->db->escape_string($form['form']['inputtype']) . "',
			'" . $this->sheel->db->escape_string($displayvalues) . "',
			'" . intval($sort) . "',
			'" . intval($required) . "',
			'" . intval($profile) . "',
			'" . intval($guests) . "',
			'" . $this->sheel->db->escape_string($roleid) . "')
		", 0, null, __FILE__, __LINE__);
                $insid = $this->sheel->db->insert_id();
                $query1 = $query2 = '';
                if (!empty($form['form']['question']) and !empty($form['form']['description'])) {
                        foreach ($form['form']['question'] as $slng => $value) {
                                $query1 .= "question_" . mb_strtolower($slng) . " = '" . $this->sheel->db->escape_string($value) . "',";
                        }
                        foreach ($form['form']['description'] as $slng => $value) {
                                $query2 .= "description_" . mb_strtolower($slng) . " = '" . $this->sheel->db->escape_string($value) . "',";
                        }
                }
                $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "register_questions
			SET
			$query1
			$query2
			visible = '" . $visible . "'
			WHERE questionid = '" . $insid . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
        }
        /**
         * Function to print the products or add-ons installed pulldown menu.
         *
         * @return      string       HTML representation of the pulldown menu
         */
        function products_pulldown($selected = 'sheel', $fieldname = 'product')
        {

                if (isset($html)) {
                        return $html;
                } else {
                        $html = '<select name="' . $fieldname . '" class="draw-select">';
                        $html .= '<option value="sheel" selected="selected">Sheel</option>';
                        $html .= '</select>';
                }
                return $html;
        }
        /**
         * Function to fetch how many categories are currently associated with this particular insertion group.
         *
         * @param       string       category type (service or product)
         * @param       string       insertion group name
         *
         * @return      integer      category count
         */
        function fetch_insertion_catcount($cattype = '', $group = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE insertiongroup = '" . $this->sheel->db->escape_string($group) . "'
				AND cattype = '" . $this->sheel->db->escape_string($cattype) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return number_format($result['count']);
                }
                return 0;
        }
        /**
         * Function to fetch how many permission groups are currently associated with this particular insertion group.
         *
         * @param       string       category type (service or product)
         * @param       string       insertion group name
         *
         * @return      integer      category count
         */
        function fetch_insertion_permcount($cattype = '', $groupid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "subscription_permissions
                        WHERE value = '" . intval($groupid) . "'
				AND accessname = '" . $this->sheel->db->escape_string("{$cattype}insgroup") . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return number_format($result['count']);
                }
                return 0;
        }
        /**
         * Function to fetch how many categories are currently associated with this particular bid increment group.
         *
         * @param       string       increment group name
         *
         * @return      integer      category count
         */
        function fetch_increment_catcount($group = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE incrementgroup = '" . $this->sheel->db->escape_string($group) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $result['count'];
                }
                return 0;
        }
        /**
         * Function to fetch how many categories are currently associated with this particular final value group.
         *
         * @param       string       category type (service or product)
         * @param       string       final value group name
         *
         * @return      integer      category count
         */
        function fetch_fv_catcount($cattype = '', $group = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE finalvaluegroup = '" . $this->sheel->db->escape_string($group) . "'
                                AND cattype = '" . $this->sheel->db->escape_string($cattype) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return number_format($result['count']);
                }
                return 0;
        }
        /**
         * Function to fetch how many categories are currently associated with this particular buyer premium fee group.
         *
         * @param       string       category type (service or product)
         * @param       string       buyer premium fee group name
         *
         * @return      integer      category count
         */
        function fetch_pf_catcount($cattype = '', $group = '')
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                        WHERE premiumgroup = '" . $this->sheel->db->escape_string($group) . "'
                                AND cattype = '" . $this->sheel->db->escape_string($cattype) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return number_format($result['count']);
                }
                return 0;
        }
        /**
         * Function to fetch how many permission groups are currently associated with this particular insertion group.
         *
         * @param       string       category type (service or product)
         * @param       string       insertion group name
         *
         * @return      integer      category count
         */
        function fetch_fv_permcount($cattype = '', $groupid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "subscription_permissions
                        WHERE value = '" . intval($groupid) . "'
                                AND accessname = '" . $this->sheel->db->escape_string("{$cattype}fvfgroup") . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $result = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return number_format($result['count']);
                }
                return 0;
        }
        /**
         * Function to construct and print out the insertion group pulldown menu
         *
         * @param       string        insertion group name
         * @param       string        category type (service or product)
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function construct_insertion_group_pulldown($insertiongroup, $cattype)
        {
                $html = '<select name="form[insertiongroup]" class="draw-select">';
                $html .= '<option value="0">{_no_insertion_group}</option>';
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
                        FROM " . DB_PREFIX . "insertion_groups
                        WHERE state = '" . $this->sheel->db->escape_string($cattype) . "'
                        GROUP BY groupname
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                if (isset($insertiongroup) and $insertiongroup == $res['groupname']) {
                                        $html .= '<option value="' . o($res['groupname']) . '" selected="selected">' . o($res['groupname']) . '</option>';
                                } else {
                                        $html .= '<option value="' . o($res['groupname']) . '">' . o($res['groupname']) . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to construct and print out the final value group pulldown menu
         *
         * @param       string        final value group name
         * @param       string        category type (service or product)
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function construct_finalvalue_group_pulldown($finalvaluegroup = '', $cattype = '')
        {
                $html = '<select name="form[finalvaluegroup]" class="draw-select">';
                $html .= '<option value="0">{_no_final_value_group}</option>';
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
                        FROM " . DB_PREFIX . "finalvalue_groups
                        WHERE state = '" . $this->sheel->db->escape_string($cattype) . "'
                        GROUP BY groupname
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                if (isset($finalvaluegroup) and $finalvaluegroup == $res['groupname']) {
                                        $html .= '<option value="' . $res['groupname'] . '" selected="selected">' . o($res['groupname']) . '</option>';
                                } else {
                                        $html .= '<option value="' . $res['groupname'] . '">' . o($res['groupname']) . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to construct and print out the buyer premium fee group pulldown menu
         *
         * @param       string        buyer premium group name
         * @param       string        category type (service or product)
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function construct_buyerpremium_group_pulldown($finalvaluegroup = '', $cattype = '')
        {
                $html = '<select name="form[premiumgroup]" class="draw-select">';
                $html .= '<option value="0">No buyer\'s premium group</option>';
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
                        FROM " . DB_PREFIX . "buyerpremium_groups
                        WHERE state = '" . $this->sheel->db->escape_string($cattype) . "'
                        GROUP BY groupname
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                if (isset($finalvaluegroup) and $finalvaluegroup == $res['groupname']) {
                                        $html .= '<option value="' . $res['groupname'] . '" selected="selected">' . o($res['groupname']) . '</option>';
                                } else {
                                        $html .= '<option value="' . $res['groupname'] . '">' . o($res['groupname']) . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to construct and print out the increment group pulldown menu
         *
         * @param       string        increment group name
         * @param       string        category type (service or product)
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function construct_increment_group_pulldown($incrementgroup, $cattype)
        {
                $html = '<select name="form[incrementgroup]" class="draw-select">';
                $html .= '<option value="0">{_no_bid_increment_group}</option>';
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
                        FROM " . DB_PREFIX . "increments_groups
                        GROUP BY groupname
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                if (isset($incrementgroup) and $incrementgroup == $res['groupname']) {
                                        $html .= '<option value="' . $res['groupname'] . '" selected="selected">' . o($res['groupname']) . '</option>';
                                } else {
                                        $html .= '<option value="' . $res['groupname'] . '">' . o($res['groupname']) . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                return $html;
        }
        /**
         * Function to construct and print out the role type pulldown menu
         *
         * @param       string        selected role type
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function print_roletype_pulldown($selected = '')
        {
                $roletypes = array('product' => '{_product}');
                return $this->sheel->construct_pulldown('roletype', 'roletype', $roletypes, $selected, 'class="select"');
        }
        /**
         * Function to construct and print out the role user type pulldown menu
         *
         * @param       string        selected role user type
         *
         * @return      string        HTML representation of the pulldown menu
         */
        function print_roleusertype_pulldown($selected = '', $textonly = false)
        {
                $roleusertypes = array('customer' => '{_customer}', 'admin' => '{_admin}');
                if ($textonly) {
                        return $roleusertypes["$selected"];
                } else {
                        return $this->sheel->construct_pulldown('form_roleusertype', 'form[roleusertype]', $roleusertypes, $selected, 'class="draw-select"');
                }
        }
        /**
         * Function to print the admininstration configuration input template menus.
         *
         * @param       string       config group
         * @param       string       return url
         * @param       string       varname to search
         * @param       string       button html
         * @param       string       config group table
         * @param       string       config table
         * @param       boolean      show mini form (for admin search panel)
         *
         * @return      string       HTML representation of the configuration template
         */
        function construct_admin_input($configgroup = '', $returnurl = '', $varname = '', $buttons = '', $grouptable = 'configuration_groups', $configtable = 'configuration', $miniform = false, $isapp = false)
        {
                $html = '';
                if ($isapp) {
                        $html .= '<div class="section">
                        <div class="layout-content">
                        <div class="layout-content__sidebar layout-content__first">
                        <div class="section-summary">
                        <h1>{_settings}</h1>
                        <p>{_manage_update_settings_for_app_here}</p>
                        ' . $buttons . '
                        </div>
                        </div>
                        <div class="layout-content__main">
                        <div class="draw-card">
                                <div class="section-content">';

                        $sql = $this->sheel->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "inputtype, name, value, comment, description, sort
                                FROM " . DB_PREFIX . $configtable . "
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $rowcount = 0;
                                while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                        $res['class'] = '';
                                        $rowcount++;
                                        if ($res['inputtype'] == 'yesno') {
                                                $html .= $this->construct_parent_choice_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['comment']);
                                        } else if ($res['inputtype'] == 'textarea' or $res['inputtype'] == 'textareatags' or $res['inputtype'] == 'text' or $res['inputtype'] == 'texttags' or $res['inputtype'] == 'pass' or $res['inputtype'] == 'int') {
                                                $html .= $this->construct_parent_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], $res['comment']);
                                        } else if ($res['inputtype'] == 'pulldown') {
                                                $html .= $this->construct_parent_pulldown_input($res['name'], $res['value'], $res['description'], $res['inputtype'], $res['class'], $res['sort'], '', $res['comment']);
                                        }
                                        $html .= '<input type="hidden" name="sort[' . $res['name'] . ']" value="' . $res['sort'] . '" />';
                                }
                        }
                        $html .= '</div>
                                        </div>
                                </div>
                                </div>
                                </div>';
                } else {
                        $option = ((empty($varname)) ? "c.configgroup = '" . $this->sheel->db->escape_string($configgroup) . "'" : "c.name IN (" . $varname . ")");
                        $sqlgrp = $this->sheel->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "g.groupname
                                FROM " . DB_PREFIX . $grouptable . " g
                                " . ((!empty($varname)) ? "LEFT JOIN " . DB_PREFIX . $configtable . " c ON (g.groupname = c.configgroup)" : "") . "
                                WHERE (g.parentgroupname = '" . $this->sheel->db->escape_string($configgroup) . "' OR g.groupname = '" . $this->sheel->db->escape_string($configgroup) . "')
                                " . ((!empty($varname)) ? "AND $option" : "") . "
                                " . ((!empty($varname)) ? "GROUP BY g.groupname" : "") . "
                                ORDER BY g.sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sqlgrp) > 0) {
                                $i = 0;
                                while ($resgrpties = $this->sheel->db->fetch_array($sqlgrp, DB_ASSOC)) {
                                        $i++;
                                        $html .= (($miniform) ? '<form autocomplete="off" action="' . HTTPS_SERVER_ADMIN . 'settings/globalupdate/" accept-charset="UTF-8" method="post">' : '');
                                        $html .= (($miniform) ? '<input type="hidden" name="return" value="' . urlencode($returnurl) . '">' : '');
                                        $html .= (($miniform and $configtable == 'configuration') ? '<input type="hidden" name="subcmd" value="_update-config-settings">' : '');
                                        $html .= (($miniform and $configtable == 'payment_configuration') ? '<input type="hidden" name="subcmd" value="_update-payment-settings">' : '');
                                        $html .= (($miniform and $configtable == 'payment_configuration') ? '<input type="hidden" name="module" value="' . $resgrpties['groupname'] . '">' : '');
                                        $html .= '<div class="section">
                                                        <div class="layout-content">
                                                                <div class="layout-content__sidebar layout-content__first">
                                                                        <div class="section-summary">
                                                                        <h1>{_confgroup_' . stripslashes($resgrpties['groupname']) . '_desc}</h1>
                                                                        <p>{_confgroup_' . stripslashes($resgrpties['groupname']) . '_help}</p>
                                                                        ' . $buttons . '
                                                                        </div>
                                                                </div>
                                                                <div class="layout-content__main">
                                                                        <div class="draw-card">
                                                                                <div class="section-content">';
                                        if ($configtable == 'payment_configuration' and !empty($varname)) {
                                                $option = "configgroup = '" . $resgrpties['groupname'] . "' AND name IN (" . $varname . ")";
                                        } else {
                                                $option = empty($varname) ? "configgroup = '" . $resgrpties['groupname'] . "'" : "name IN (" . $varname . ")";
                                        }
                                        $sql = $this->sheel->db->query("
                                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "inputtype, name, value, inputcode, sort
                                                FROM " . DB_PREFIX . $configtable . "
                                                WHERE $option
                                                        AND visible = '1'
                                                ORDER BY sort ASC
                                        ", 0, null, __FILE__, __LINE__);

                                        if ($this->sheel->db->num_rows($sql) > 0) {
                                                $rowcount = 0;
                                                while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                                        $res['class'] = '';
                                                        $rowcount++;
                                                        if ($res['inputtype'] == 'yesno') {
                                                                $html .= $this->construct_parent_choice_input($res['name'], $res['value'], '{_' . $res['name'] . '_desc}', $res['inputtype'], $res['class'], $res['sort'], '{_' . $res['name'] . '_help}');
                                                        } else if ($res['inputtype'] == 'textarea' or $res['inputtype'] == 'textareatags' or $res['inputtype'] == 'text' or $res['inputtype'] == 'texttags' or $res['inputtype'] == 'pass' or $res['inputtype'] == 'int') {
                                                                $html .= $this->construct_parent_input($res['name'], $res['value'], '{_' . $res['name'] . '_desc}', $res['inputtype'], $res['class'], $res['sort'], '{_' . $res['name'] . '_help}');
                                                        } else if ($res['inputtype'] == 'pulldown') {
                                                                $html .= $this->construct_parent_pulldown_input($res['name'], $res['value'], '{_' . $res['name'] . '_desc}', $res['inputtype'], $res['class'], $res['sort'], $res['inputcode'], '{_' . $res['name'] . '_help}');
                                                        }

                                                        $html .= '<input type="hidden" name="sort[' . $res['name'] . ']" value="' . $res['sort'] . '" />';

                                                }


                                        }

                                        $html .= '</div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>';
                                        $html .= (($miniform)
                                                ? '<div class="section"><div class="draw-grid draw-grid--right-aligned"><div class="draw-grid__cell draw-grid__cell--no-flex">
                        ' . '<button type="submit" class="btn js-btn-primary js-btn-loadable has-loading btn-primary">{_save}</button>' .
                                                '</div></div></div>'
                                                : '');
                                        $html .= (($miniform) ? '</form>' : '');
                                }
                        }
                }
                return $html;
        }
        /**
         * Function to print out a parents "yes or no" input field (based on radio buttons)
         *
         * @param       integer       configuration varname
         * @param       string        value
         * @param       string        description
         * @param       string        input type
         * @param       string        class (default alt1)
         *
         * @return      string        HTML representation of the Yes/No radio button input selection
         */
        function construct_parent_choice_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $help = '')
        {
                $html = '<div class="draw-grid draw-grid--outside-padding draw-grid--inner-grid">
	<div class="draw-grid__cell">
		<div class="draw-input-wrapper">
			<label class="draw-label sb"><strong>' . stripslashes($description) . '</strong></label>
			<ul class="unstyled choicelist">
			<li>
				<input class="fl" bind-event-click="" type="radio" name="config[' . $variableinfo . ']" value="1" id="rb_1[' . $variableinfo . ']"' . (($value == 1) ? ' checked="checked"' : '') . '>
				<label class="draw-label" for="rb_1[' . $variableinfo . ']">{_enabled}</label>
			</li>
			<li>
				<input class="fl" bind-event-click="" type="radio" name="config[' . $variableinfo . ']" value="0" id="rb_0[' . $variableinfo . ']"' . (($value == 0) ? ' checked="checked"' : '') . '>
				<label class="draw-label" for="rb_0[' . $variableinfo . ']">{_disabled}</label>
			</li>
			</ul>
			<p class="draw-input__help-text st">' . stripslashes($help) . '</p>
			</div>
		</div>
	</div>';
                return $html;
        }
        /**
         * Function to print out a parents textarea text input field
         *
         * @param       integer       configuration varname
         * @param       string        value
         * @param       string        description
         * @param       string        input type
         * @param       string        class (default alt1)
         *
         * @return      string        HTML representation of the integer textarea input selection
         */
        function construct_parent_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $help = '')
        {
                $html = '<div class="draw-grid draw-grid--outside-padding draw-grid--inner-grid">
	<div class="draw-grid__cell">
		<div class="draw-input-wrapper">
			<label class="draw-label sb" for="config_' . $variableinfo . '"><strong>' . o($description) . '</strong></label>';
                if ($inputtype == 'text' or $inputtype == 'int') {
                        $html .= '<input class="draw-input" size="30" type="text" value="' . sheel_htmlentities($value) . '" name="config[' . $variableinfo . ']" id="config_' . $variableinfo . '" autocomplete="off">';
                } else if ($inputtype == 'texttags') {
                        $html .= '<input class="draw-input" size="30" type="text" value="' . sheel_htmlentities($value) . '" name="config[' . $variableinfo . ']" id="config_' . $variableinfo . '" autocomplete="off">';
                } else if ($inputtype == 'pass') {
                        $html .= '<input class="draw-input" size="30" type="password" name="config[' . $variableinfo . ']" id="config_' . $variableinfo . '" placeholder="' . (($value == '') ? '{_please_set_password}' : '{_enter_new_password_change_current_one}') . '" autocomplete="off">';
                        $html .= ((!empty($value)) ? '<input type="hidden" name="pass[' . $variableinfo . ']" value="set" id="pass_config_' . $variableinfo . '">' : '<input type="hidden" name="pass[' . $variableinfo . ']" value="notset" id="pass_config_' . $variableinfo . '">');
                } else if ($inputtype == 'textarea') {
                        $html .= '<textarea class="draw-input" value="" name="config[' . $variableinfo . ']" id="config_' . $variableinfo . '">' . sheel_htmlentities($value) . '</textarea>';
                } else if ($inputtype == 'textareatags') {
                        $html .= '<style>div.inputTags-list input.inputTags-field{float:none;width:80px;font-size:14px}div.inputTags-list {width: 99.6%;height: 64px;padding-left:4px;border: 1px solid #d3dbe2;border-radius: 3px;font-size: 14px;}</style>';
                        $html .= '<textarea class="draw-input" value="" name="config[' . $variableinfo . ']" id="config_' . $variableinfo . '">' . sheel_htmlentities($value) . '</textarea>';
                        $html .= '<script>jQuery(\'#config_' . $variableinfo . '\').inputTags({max:25});</script>';
                }
                $html .= '<p class="draw-input__help-text st">' . stripslashes($help) . '</p>
			</div>
		</div>
	</div>';
                return $html;
        }
        /**
         * Function to print out a parents pulldown menu input field
         *
         * @param       integer       configuration varname
         * @param       string        value
         * @param       string        description
         * @param       string        input type
         * @param       string        class (default alt1)
         *
         * @return      string        HTML representation of the integer textarea input selection
         */
        function construct_parent_pulldown_input($variableinfo = '', $value = '', $description = '', $inputtype = '', $class = 'alt1', $sort = '0', $inputcode = '', $help = '')
        {
                $html = '<div class="draw-grid draw-grid--outside-padding draw-grid--inner-grid"><div class="draw-grid__cell"><div class="draw-input-wrapper"><label class="draw-label sb" for="config_' . $variableinfo . '"><strong>' . stripslashes($description) . '</strong></label><div class="draw-select__wrapper draw-input--has-content">';
                if ($variableinfo == 'globalserverlocale_defaultcurrency') {
                        $html .= $this->sheel->currency->pulldown('admin', $variableinfo, 'draw-select');
                } else if ($variableinfo == 'registrationdisplay_defaultcountry') {
                        $countryid = $this->sheel->common_location->fetch_country_id($this->sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']);
                        $html .= $this->sheel->common_location->construct_country_pulldown($countryid, $this->sheel->config['registrationdisplay_defaultcountry'], 'config[' . $variableinfo . ']', false, 'config[registrationdisplay_defaultstate]', false, false, false, 'stateid', false, '', '', '', 'draw-select');
                } else if ($variableinfo == 'registrationdisplay_defaultstate') {
                        $countryid = $this->sheel->common_location->fetch_country_id($this->sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']);
                        $html .= '<div id="stateid">' . $this->sheel->common_location->construct_state_pulldown($countryid, $this->sheel->config['registrationdisplay_defaultstate'], 'config[' . $variableinfo . ']', false, false, 0, 'draw-select') . '</div>';
                } else if ($variableinfo == 'globalserverlocale_sitetimezone') {
                        $html .= $this->sheel->datetimes->timezone_pulldown($variableinfo, $this->sheel->config['globalserverlocale_sitetimezone'], true, true, 'draw-select', 'config[' . $variableinfo . ']');
                } else if ($variableinfo == 'default_wysiwyg') {
                        $html .= '<select name="config[' . $variableinfo . ']" class="draw-select"><option value="textarea" ' . ($this->sheel->config[$variableinfo] == 'textarea' ? 'selected="selected"' : '') . '>Text area</option><option value="ckeditor" ' . ($this->sheel->config[$variableinfo] == 'ckeditor' ? 'selected="selected"' : '') . '>CKEditor 4.1.2</option><option value="froala" ' . ($this->sheel->config[$variableinfo] == 'froala' ? 'selected="selected"' : '') . '>Froala ' . FROALAVERSION . '</option></select>';
                } else if ($variableinfo == 'shipping_regions') {
                        $regions = array('europe', 'africa', 'antarctica', 'asia', 'north_america', 'oceania', 'south_america');
                        $html .= '<select name="config[' . $variableinfo . '][]" multiple="multiple" class="draw-select">';
                        $sel_regions = unserialize($this->sheel->config[$variableinfo]);
                        foreach ($regions as $key => $value) {
                                $sel = (in_array($value, $sel_regions)) ? 'selected="selected"' : '';
                                $html .= '<option value="' . $value . '" ' . $sel . '>{_' . $value . '}</option>';
                        }
                        $html .= '</select>';
                } else if ($variableinfo == 'defaultstyle') {
                        $html .= $this->sheel->styles->print_styles_pulldown($this->sheel->config['defaultstyle'], '', 'config[defaultstyle]', 'draw-select');
                } else if ($variableinfo == 'globalauctionsettings_endsoondays') {
                        $html .= $this->sheel->construct_pulldown("config[$variableinfo]", "config[$variableinfo]", array('-1' => '{_any_date}', '1' => '1 {_hour}', '2' => '2 {_hours}', '3' => '3 {_hours}', '4' => '4 {_hours}', '5' => '5 {_hours}', '6' => '12 {_hours}', '7' => '24 {_hours}', '8' => '2 {_days}', '9' => '3 {_days}', '10' => '4 {_days}', '11' => '5 {_days}', '12' => '6 {_days}', '13' => '7 {_days}', '14' => '2 {_weeks}', '15' => '1 {_month}'), $this->sheel->config[$variableinfo], 'class="draw-select"');
                } else if ($variableinfo == 'subscriptions_defaultroleid_product') {
                        $html .= $this->sheel->role->print_role_pulldown($this->sheel->config['subscriptions_defaultroleid_product'], 0, 0, 0, '', $_SESSION['sheeldata']['user']['slng'], 'draw-select', 'config_subscriptions_defaultroleid_product', 'config[subscriptions_defaultroleid_product]');
                } else if ($variableinfo == 'subscriptions_defaultplanid_product') {
                        $html .= $this->sheel->subscription->plans_pulldown('draw-select', $this->sheel->config['subscriptions_defaultplanid_product'], '', 'config[subscriptions_defaultplanid_product]', 'config_subscriptions_defaultplanid_product');
                } else {
                        $html .= $inputcode;
                }
                $html .= '</div><p class="draw-input__help-text st">' . stripslashes($help) . '</p></div></div></div>';
                return $html;
        }
        /**
         * Function to remove & allow rebuild of the javascript phrase cache file
         *
         */
        function rebuild_language_cache()
        {
                $this->sheel->language->clean_cache();
        }
        function ordinal($number)
        {
                $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
                if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                        return $number . 'th';
                } else {
                        return $number . $ends[$number % 10];
                }
        }
        /**
         * Function to fetch the schedule details of a task used in the self-cron automation system
         *
         * @param       array         array data of schedule
         *
         * @return      string
         */
        function fetch_cron_schedule($cron = array())
        {
                $t = array(
                        'hour' => $cron['hour'],
                        'day' => $cron['day'],
                        'month' => $cron['month'],
                        'weekday' => $cron['weekday']
                );
                foreach ($t as $field => $value) {
                        $t["$field"] = iif($value == -1, '*', $value);
                }
                if (is_numeric($cron['minute'])) {
                        $cron['minute'] = array(0 => $cron['minute']);
                } else {
                        $cron['minute'] = unserialize($cron['minute']);
                        if (!is_array($cron['minute'])) {
                                $cron['minute'] = array(-1);
                        }
                }
                if ($cron['minute'][0] == -1) {
                        $t['minute'] = '*';
                } else {
                        $minutes = array();
                        foreach ($cron['minute'] as $nextminute) {
                                $minutes[] = str_pad(intval($nextminute), 2, 0, STR_PAD_LEFT);
                        }
                        $t['minute'] = implode(', ', $minutes);
                }
                if ($t['weekday'] != '*') {
                        $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                        $day = $days[intval($t['weekday'])];
                        $t['weekday'] = '{_' . $day . '}';
                        $t['day'] = '*';
                }
                if ($t['month'] != '*') {
                        $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
                        $month = $months[intval($t['month']) - 1];
                        $t['month'] = '{_' . $month . '}';
                }
                return $t;
        }
        /**
         * Function to fetch the task variable name for a given cron job within the automation system
         *
         * @param       integer       cron job id
         *
         * @return      string        HTML representation of the string
         */
        function fetch_task_varname($cronid)
        {
                $value = '';
                $sql = $this->sheel->db->query("
			SELECT varname
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . intval($cronid) . "'
		", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        $value = $res['varname'];
                }
                return $value;
        }
        /**
         * Function to print out a phrase based on a text string for the scheduled task
         *
         * @param       string        selected task
         *
         * @return      string        HTML representation of the string
         */
        function scheduled_task_phrase($selected)
        {
                switch ($selected) {
                        case 'subscriptions':
                                $phrase = '{_subscriptions}';
                                break;
                        case 'store_subscriptions':
                                $phrase = '{_store_subscriptions}';
                                break;
                        case 'rfp':
                        case 'auctions':
                                $phrase = '{_auctions}';
                                break;
                        case 'reminders':
                                $phrase = '{_reminders}';
                                break;
                        case 'currency':
                                $phrase = '{_currencies}';
                                break;
                        case 'dailyreports':
                                $phrase = '{_dailyreports}';
                                break;
                        case 'dailyrfp':
                                $phrase = '{_daily_newsletters}';
                                break;
                        case 'creditcards':
                                $phrase = '{_credit_card_cleanup}';
                                break;
                        case 'warnings':
                                $phrase = '{_warnings}';
                                break;
                        case 'monthly':
                                $phrase = '{_monthly_cleanup}';
                                break;
                        case 'watchlist':
                                $phrase = '{_watchlist}';
                                break;
                        default:
                                $phrase = str_replace('_', ' ', $selected);
                                $phrase = ucwords($phrase);
                                break;
                }
                return $phrase;
        }
        /**
         * Function to print out the scheduled tasks pulldown menu
         *
         * @param       string        selected option
         *
         * @return      string        HTML representation of the pulldown
         */
        function print_scheduled_tasks_pulldown($selected = '0')
        {
                $values['0'] = '{_all_tasks}';
                $sql = $this->sheel->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cron
			ORDER BY cronid ASC
		", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $values[$res['cronid']] = $this->scheduled_task_phrase($res['varname']);
                        }
                }
                return $this->sheel->construct_pulldown('cronid', 'cronid', $values, $selected, 'tabindex="1" class="select"');
        }
        /**
         * Function to print the "migrate to" pulldown menu for subscription plans within the AdminCP
         *
         * @param       string       selected option
         *
         * @return      string
         */
        function print_migrate_to_pulldown($selected = '', $slng = 'eng', $shownone = false)
        {
                if (isset($selected) and !empty($selected)) {
                        $sql_migrate = $this->sheel->db->query("
	                        SELECT subscriptionid, migrateto, title_$slng AS title, length, units
	                        FROM " . DB_PREFIX . "subscription
	                        WHERE subscriptionid = '" . intval($selected) . "'
	                ", 0, null, __FILE__, __LINE__);
                        $res_migrate = $this->sheel->db->fetch_array($sql_migrate, DB_ASSOC);
                }
                $sql = $this->sheel->db->query("
                        SELECT subscriptionid, migrateto, title_$slng AS title, length, units
                        FROM " . DB_PREFIX . "subscription
                        WHERE type = 'product'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $html = '<select name="form[migratetoid]" class="draw-select">';
                        $html .= (($shownone) ? '<option value="none">{_no_migration_subscription_logic}</option>' : '');
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $html .= '<option value="' . $res['subscriptionid'] . '"';
                                if (isset($selected) and !empty($selected) and $res['subscriptionid'] == $res_migrate['migrateto']) {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . stripslashes($res['title']) . ' (' . $res['length'] . ' ' . $this->sheel->subscription->print_unit($res['units']) . ')</option>';
                        }
                        $html .= '</select>';
                } else {
                        $html = '{_no_subscription_plans_to_migrate_users}';
                }
                return $html;
        }
        /**
         * Function to print the migration billing pulldown menu
         *
         * @param       string       selected option
         *
         * @return      string
         */
        function print_migrate_billing_pulldown($selected = '')
        {
                if (isset($selected) and !empty($selected)) {
                        $sql_migrate_logic = $this->sheel->db->query("
                                SELECT migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = " . $selected . "
                        ", 0, null, __FILE__, __LINE__);
                        $res_migrate_logic = $this->sheel->db->fetch_array($sql_migrate_logic, DB_ASSOC);
                }
                $s1 = '';
                if (isset($res_migrate_logic['migratelogic']) and $res_migrate_logic['migratelogic'] == 'waived') {
                        $s1 = 'selected="selected"';
                }
                $s2 = '';
                if (isset($res_migrate_logic['migratelogic']) and $res_migrate_logic['migratelogic'] == 'unpaid') {
                        $s2 = 'selected="selected"';
                }
                $s3 = '';
                if (isset($res_migrate_logic['migratelogic']) and $res_migrate_logic['migratelogic'] == 'paid') {
                        $s3 = 'selected="selected"';
                }
                $html = '<select name="form[migratelogic]" class="draw-select">';
                $html .= '<option value="none">No billing changes</option>';
                $html .= '<optgroup label="Billing Migration">';
                $html .= '<option value="waived" ' . $s1 . '>Move plan > create invoice > mark paid (for $0.00) [Instant access]</option>';
                $html .= '<option value="unpaid" ' . $s2 . '>Move plan > create invoice > mark unpaid (for amount of new plan) [Requires payment before access]</option>';
                $html .= '<option value="paid" ' . $s3 . '>Move plan > create invoice > mark paid (for amount of new plan) [Instant access]</option>';
                $html .= '</optgroup>';
                $html .= '</select>';
                return $html;
        }
        function fetch_master_phrases_count()
        {
                $count = 0;
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE ismaster = '1'
                ", 0, null, __FILE__, __LINE__);
                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                $count = (int) $res['count'];
                return $count;
        }
        function fetch_custom_phrases_count()
        {
                $count = 0;
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE ismaster = '0'
                ", 0, null, __FILE__, __LINE__);
                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                $count = (int) $res['count'];
                return $count;
        }
        function fetch_moved_phrases_count()
        {
                $count = 0;
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE (isupdated = '1' OR ismoved = '1')
                ", 0, null, __FILE__, __LINE__);
                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                $count = (int) $res['count'];
                return $count;
        }
        function fetch_total_phrases_count()
        {
                $count = 0;
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                ", 0, null, __FILE__, __LINE__);
                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                $count = (int) $res['count'];
                return $count;
        }
        function construct_revenue_balance()
        {
                $array = array(
                        'subscription1' => '',
                        'subscription2' => '',
                        'subscription3' => '',
                        'subscription4' => '',
                        'credential1' => '',
                        'credential2' => '',
                        'credential3' => '',
                        'credential4' => '',
                        'portfolio1' => '',
                        'portfolio2' => '',
                        'portfolio3' => '',
                        'portfolio4' => '',
                        'listing1' => '',
                        'listing2' => '',
                        'listing3' => '',
                        'listing4' => '',
                        'fvf1' => '',
                        'fvf2' => '',
                        'fvf3' => '',
                        'fvf4' => '',
                        'if1' => '',
                        'if2' => '',
                        'if3' => '',
                        'if4' => '',
                        'escrowfees1' => '',
                        'escrowfees2' => '',
                        'escrowfees3' => '',
                        'escrowfees4' => '',
                        'withdraw1' => '',
                        'withdraw2' => '',
                        'withdraw3' => '',
                        'withdraw4' => '',
                        'p2b1' => '',
                        'p2b2' => '',
                        'p2b3' => '',
                        'p2b4' => '',
                        'totalsgenerated' => '',
                        'paidgenerated' => '',
                        'owinggenerated' => '',
                        'overduegenerated' => ''
                );
                return $array;
        }
        function stats($what = '', $period = 'today')
        {
                if ($what == 'home') {
                        $stats = array(

                                'visitors' => array(
                                        'visitors' => $this->sheel->admincp_stats->fetch($what, $period, 'visitors'),
                                        'uniquevisitors' => $this->sheel->admincp_stats->fetch($what, $period, 'uniquevisitors'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorseries'),
                                        'pageviews' => $this->sheel->admincp_stats->fetch($what, $period, 'pageviews'),
                                        'mostactive' => $this->sheel->admincp_stats->fetch($what, $period, 'mostactive'),
                                ),
                                'stats' => array(
                                        'topcountries' => $this->sheel->admincp_stats->fetch($what, $period, 'topcountries'),
                                        'topdevices' => $this->sheel->admincp_stats->fetch($what, $period, 'topdevices'),
                                        'topbrowsers' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrowsers'),
                                        'trafficsources' => $this->sheel->admincp_stats->fetch($what, $period, 'trafficsources'),
                                        'toplandingpages' => $this->sheel->admincp_stats->fetch($what, $period, 'toplandingpages')
                                )
                        );
                }

                if ($what == 'dashboard') {
                        $stats = array(
                                'revenue' => array(
                                        // multi-vendor
                                        'revenue7days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue7days'),
                                        'revenuecount7days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount7days'),
                                        'revenuetoday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuetoday'),
                                        'revenuecounttoday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecounttoday'),
                                        'revenueyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenueyesterday'),
                                        'revenuecountyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecountyesterday'),
                                        'revenue30days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue30days'),
                                        'revenuecount30days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount30days'),
                                        'revenue90days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue90days'),
                                        'revenuecount90days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount90days'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuelabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'revenueseries'),
                                ),
                                'sales' => array(
                                        // single seller
                                        'sellersales7days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales7days'),
                                        'sellersalescount7days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount7days'),
                                        'sellersalestoday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalestoday'),
                                        'sellersalescounttoday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescounttoday'),
                                        'sellersalesyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalesyesterday'),
                                        'sellersalescountyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescountyesterday'),
                                        'sellersales30days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales30days'),
                                        'sellersalescount30days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount30days'),
                                        'sellersales90days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales90days'),
                                        'sellersalescount90days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount90days'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersaleslabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalesseries')
                                ),
                                'visitors' => array(
                                        'visitors' => $this->sheel->admincp_stats->fetch($what, $period, 'visitors'),
                                        'uniquevisitors' => $this->sheel->admincp_stats->fetch($what, $period, 'uniquevisitors'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorseries'),
                                        'pageviews' => $this->sheel->admincp_stats->fetch($what, $period, 'pageviews'),
                                        'mostactive' => $this->sheel->admincp_stats->fetch($what, $period, 'mostactive'),
                                ),
                                'percent' => array(
                                        'buyerpercent' => $this->sheel->admincp_stats->fetch($what, $period, 'buyerpercent'),
                                        'buyercount' => $this->sheel->admincp_stats->fetch($what, $period, 'buyercount'),
                                        'sellerpercent' => $this->sheel->admincp_stats->fetch($what, $period, 'sellerpercent'),
                                        'sellercount' => $this->sheel->admincp_stats->fetch($what, $period, 'sellercount'),
                                        'bothpercent' => $this->sheel->admincp_stats->fetch($what, $period, 'bothpercent'),
                                        'bothcount' => $this->sheel->admincp_stats->fetch($what, $period, 'bothcount'),
                                ),
                                'stats' => array(
                                        'topcategories' => $this->sheel->admincp_stats->fetch($what, $period, 'topcategories'),
                                        'topcountries' => $this->sheel->admincp_stats->fetch($what, $period, 'topcountries'),
                                        'topdevices' => $this->sheel->admincp_stats->fetch($what, $period, 'topdevices'),
                                        'topbrowsers' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrowsers'),
                                        'trafficsources' => $this->sheel->admincp_stats->fetch($what, $period, 'trafficsources'),
                                        'socialreferrers' => $this->sheel->admincp_stats->fetch($what, $period, 'socialreferrers'),
                                        'topreferrers' => $this->sheel->admincp_stats->fetch($what, $period, 'topreferrers'),
                                        'topsearchterms' => $this->sheel->admincp_stats->fetch($what, $period, 'topsearchterms'),
                                        'toplandingpages' => $this->sheel->admincp_stats->fetch($what, $period, 'toplandingpages'),
                                        'utmcampaigns' => $this->sheel->admincp_stats->fetch($what, $period, 'utmcampaigns')
                                )
                        );
                } else if ($what == 'marketplace') {
                        $stats = array(
                                'stats' => array(
                                        'topbuyers' => '',
                                        'topsellers' => '',
                                        'topcountries' => '',
                                        'topdevices' => '',
                                        'topbrowsers' => '',
                                        'trafficsources' => '',
                                        'socialreferrers' => '',
                                        'topreferrers' => '',
                                        'topsearchterms' => '',
                                        'toplandingpages' => '',
                                        'utmcampaigns' => ''
                                ),
                                'sales' => array(
                                        'sellersales7days' => '0.00',
                                        'sellersalescount7days' => '0',
                                        'sellersalestoday' => '0.00',
                                        'sellersalescounttoday' => '0',
                                        'sellersalesyesterday' => '0.00',
                                        'sellersalescountyesterday' => '0',
                                        'sellersales30days' => '0.00',
                                        'sellersalescount30days' => '0',
                                        'sellersales90days' => '0.00',
                                        'sellersalescount90days' => '0',
                                        'label' => "",
                                        //"'Dec 12', '', 'Dec 14', '', 'Dec 16', '', 'Dec 18'",
                                        'series' => "",
                                        //"['10', '20', '10', '20', '0', '10', '30']"
                                ),
                                'listings' => array(
                                        'listingcount' => '0',
                                        'listingcountpast' => '0',
                                        'label' => "",
                                        // "'Dec 12', '', 'Dec 14', '', 'Dec 16', '', 'Dec 18'",
                                        'series' => "",
                                        //"['37931', '33110', '7312', '6532', '33441', '7422', '2993'],['29361', '1230', '5423', '1233', '3441', '5551', '349']"
                                ),
                                'percent' => array(
                                        'bidpercent' => '0.00',
                                        'bidcheckoutpercent' => '0.00',
                                        'bidpurchasepercent' => '0.00',
                                        'bidcount' => '0',
                                        'bidcheckoutcount' => '0',
                                        'bidpurchasecount' => '0',
                                        'cartpercent' => '0.00',
                                        'checkoutpercent' => '0.00',
                                        'purchasedpercent' => '0.00',
                                        'cartcount' => '0',
                                        'checkoutcount' => '0',
                                        'purchasedcount' => '0'
                                )
                        );
                } else if ($what == 'accounting') {
                        $stats = array(
                                'balance' => array(
                                        'totaldepositspending' => $this->sheel->admincp_stats->fetch($what, '', 'totaldepositspending'),
                                        'totaldepositsamount' => $this->sheel->admincp_stats->fetch($what, '', 'totaldepositsamount'),
                                        'totaldepositscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'totaldepositscancelled'),
                                        'totalwithdrawsamount' => $this->sheel->admincp_stats->fetch($what, '', 'totalwithdrawsamount'),
                                        'totalwithdrawspending' => $this->sheel->admincp_stats->fetch($what, '', 'totalwithdrawspending'),
                                        'totalwithdrawscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'totalwithdrawscancelled'),
                                        'totalescrowfunding' => $this->sheel->admincp_stats->fetch($what, '', 'totalescrowfunding'),
                                        'totalescrowreleases' => $this->sheel->admincp_stats->fetch($what, '', 'totalescrowreleases'),
                                        'totalescrowreversals' => $this->sheel->admincp_stats->fetch($what, '', 'totalescrowreversals'),
                                        'totaluserbalances' => $this->sheel->admincp_stats->fetch($what, '', 'totaluserbalances'),
                                        'todaydepositsamount' => $this->sheel->admincp_stats->fetch($what, 'today', 'todaydepositsamount'),
                                        'todaydepositspending' => $this->sheel->admincp_stats->fetch($what, '', 'todaydepositspending'),
                                        'todaydepositscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'todaydepositscancelled'),
                                        'todaywithdrawsamount' => $this->sheel->admincp_stats->fetch($what, 'today', 'todaywithdrawsamount'),
                                        'todaywithdrawspending' => $this->sheel->admincp_stats->fetch($what, '', 'todaywithdrawspending'),
                                        'todaywithdrawscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'todaywithdrawscancelled'),
                                        'todayescrowfunding' => $this->sheel->admincp_stats->fetch($what, 'today', 'todayescrowfunding'),
                                        'todayescrowreleases' => $this->sheel->admincp_stats->fetch($what, 'today', 'todayescrowreleases'),
                                        'todayescrowreversals' => $this->sheel->admincp_stats->fetch($what, 'today', 'todayescrowreversals'),
                                        'last7depositsamount' => $this->sheel->admincp_stats->fetch($what, 'last7days', 'last7depositsamount'),
                                        'last7depositspending' => $this->sheel->admincp_stats->fetch($what, '', 'last7depositspending'),
                                        'last7depositscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last7depositscancelled'),
                                        'last7withdrawsamount' => $this->sheel->admincp_stats->fetch($what, 'last7days', 'last7withdrawsamount'),
                                        'last7withdrawspending' => $this->sheel->admincp_stats->fetch($what, '', 'last7withdrawspending'),
                                        'last7withdrawscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last7withdrawscancelled'),
                                        'last7escrowfunding' => $this->sheel->admincp_stats->fetch($what, 'last7days', 'last7escrowfunding'),
                                        'last7escrowreleases' => $this->sheel->admincp_stats->fetch($what, 'last7days', 'last7escrowreleases'),
                                        'last7escrowreversals' => $this->sheel->admincp_stats->fetch($what, 'last7days', 'last7escrowreversals'),
                                        'last30depositsamount' => $this->sheel->admincp_stats->fetch($what, 'last30days', 'last30depositsamount'),
                                        'last30depositspending' => $this->sheel->admincp_stats->fetch($what, '', 'last30depositspending'),
                                        'last30depositscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last30depositscancelled'),
                                        'last30withdrawsamount' => $this->sheel->admincp_stats->fetch($what, 'last30days', 'last30withdrawsamount'),
                                        'last30withdrawspending' => $this->sheel->admincp_stats->fetch($what, '', 'last30withdrawspending'),
                                        'last30withdrawscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last30withdrawscancelled'),
                                        'last30escrowfunding' => $this->sheel->admincp_stats->fetch($what, 'last30days', 'last30escrowfunding'),
                                        'last30escrowreleases' => $this->sheel->admincp_stats->fetch($what, 'last30days', 'last30escrowreleases'),
                                        'last30escrowreversals' => $this->sheel->admincp_stats->fetch($what, 'last30days', 'last30escrowreversals'),
                                        'last90depositsamount' => $this->sheel->admincp_stats->fetch($what, 'last90days', 'last90depositsamount'),
                                        'last90depositspending' => $this->sheel->admincp_stats->fetch($what, '', 'last90depositspending'),
                                        'last90depositscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last90depositscancelled'),
                                        'last90withdrawsamount' => $this->sheel->admincp_stats->fetch($what, 'last90days', 'last90withdrawsamount'),
                                        'last90withdrawspending' => $this->sheel->admincp_stats->fetch($what, '', 'last90withdrawspending'),
                                        'last90withdrawscancelled' => $this->sheel->admincp_stats->fetch($what, '', 'last90withdrawscancelled'),
                                        'last90escrowfunding' => $this->sheel->admincp_stats->fetch($what, 'last90days', 'last90escrowfunding'),
                                        'last90escrowreleases' => $this->sheel->admincp_stats->fetch($what, 'last90days', 'last90escrowreleases'),
                                        'last90escrowreversals' => $this->sheel->admincp_stats->fetch($what, 'last90days', 'last90escrowreversals'),
                                ),
                                'transactions' => array(
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'transactionlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'transactionseries'),
                                ),
                                'escrow' => array(
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'escrowlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'escrowseries'),
                                ),
                                'deposits' => array(
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'depositslabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'depositsseries'),
                                ),
                                'withdraws' => array(
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'withdrawslabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'withdrawsseries'),
                                ),
                                'percent' => array(
                                        'invoicepercent' => $this->sheel->admincp_stats->fetch($what, $period, 'invoicepercent'),
                                        'invoicecount' => $this->sheel->admincp_stats->fetch($what, $period, 'invoicecount'),
                                        'checkoutpercent' => $this->sheel->admincp_stats->fetch($what, $period, 'checkoutpercent'),
                                        'checkoutcount' => $this->sheel->admincp_stats->fetch($what, $period, 'checkoutcount'),
                                        'purchasedpercent' => $this->sheel->admincp_stats->fetch($what, $period, 'purchasedpercent'),
                                        'purchasedcount' => $this->sheel->admincp_stats->fetch($what, $period, 'purchasedcount')
                                ),
                                'stats' => array(
                                        'topinvoicetypes' => $this->sheel->admincp_stats->fetch($what, $period, 'topinvoicetypes'),
                                        'topaccountbalances' => $this->sheel->admincp_stats->fetch($what, $period, 'topaccountbalances'),
                                        'toppaymethods' => $this->sheel->admincp_stats->fetch($what, $period, 'toppaymethods'),
                                        'topgateways' => $this->sheel->admincp_stats->fetch($what, $period, 'topgateways'),
                                        'topcurrencies' => $this->sheel->admincp_stats->fetch($what, $period, 'topcurrencies'),
                                        'topdaypart' => $this->sheel->admincp_stats->fetch($what, $period, 'topdaypart'),
                                        'topbalancesowing' => $this->sheel->admincp_stats->fetch($what, $period, 'topbalancesowing')
                                )
                        );
                } else if ($what == 'stores') {
                        $stats = array(
                                'revenue' => array(
                                        // multi-vendor
                                        'revenue7days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue7days'),
                                        'revenuecount7days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount7days'),
                                        'revenuetoday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuetoday'),
                                        'revenuecounttoday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecounttoday'),
                                        'revenueyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenueyesterday'),
                                        'revenuecountyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecountyesterday'),
                                        'revenue30days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue30days'),
                                        'revenuecount30days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount30days'),
                                        'revenue90days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenue90days'),
                                        'revenuecount90days' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuecount90days'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'revenuelabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'revenueseries')
                                ),
                                'sales' => array(
                                        // single seller
                                        'sellersales7days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales7days'),
                                        'sellersalescount7days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount7days'),
                                        'sellersalestoday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalestoday'),
                                        'sellersalescounttoday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescounttoday'),
                                        'sellersalesyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalesyesterday'),
                                        'sellersalescountyesterday' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescountyesterday'),
                                        'sellersales30days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales30days'),
                                        'sellersalescount30days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount30days'),
                                        'sellersales90days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersales90days'),
                                        'sellersalescount90days' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalescount90days'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersaleslabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'sellersalesseries')
                                ),
                                'listings' => array(
                                        'listingcount' => $this->sheel->admincp_stats->fetch($what, $period, 'listingcount'),
                                        'listingcountpast' => $this->sheel->admincp_stats->fetch($what, $period, 'listingcountpast'),
                                        'listingcountmoderated' => $this->sheel->admincp_stats->fetch($what, $period, 'listingcountmoderated'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'listingcountlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'listingcountseries')
                                ),
                                'percent' => array(
                                        'addedtocart' => $this->sheel->admincp_stats->fetch($what, $period, 'addedtocart'),
                                        'addedtocartcount' => $this->sheel->admincp_stats->fetch($what, $period, 'addedtocartcount'),
                                        'reachedcheckout' => $this->sheel->admincp_stats->fetch($what, $period, 'reachedcheckout'),
                                        'reachedcheckoutcount' => $this->sheel->admincp_stats->fetch($what, $period, 'reachedcheckoutcount'),
                                        'purchased' => $this->sheel->admincp_stats->fetch($what, $period, 'purchased'),
                                        'purchasedcount' => $this->sheel->admincp_stats->fetch($what, $period, 'purchasedcount')
                                ),
                                'stats' => array(
                                        'topstoresbyorders' => $this->sheel->admincp_stats->fetch($what, $period, 'topstoresbyorders'),
                                        'topproductsbyorders' => $this->sheel->admincp_stats->fetch($what, $period, 'topproductsbyorders'),
                                        'topstoresbytraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topstoresbytraffic'),
                                        'topstorescategorybytraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topstorescategorybytraffic'),
                                        'topproductsbytraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topproductsbytraffic'),
                                        'topproductsbyreturns' => $this->sheel->admincp_stats->fetch($what, $period, 'topproductsbyreturns'),
                                        'topstoresbyreturns' => $this->sheel->admincp_stats->fetch($what, $period, 'topstoresbyreturns'),
                                        'topcountries' => $this->sheel->admincp_stats->fetch($what, $period, 'topcountries'),
                                        'topdevices' => $this->sheel->admincp_stats->fetch($what, $period, 'topdevices'),
                                        'topbrowsers' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrowsers'),
                                        'trafficsources' => $this->sheel->admincp_stats->fetch($what, $period, 'trafficsources'),
                                        'socialreferrers' => $this->sheel->admincp_stats->fetch($what, $period, 'socialreferrers'),
                                        'topreferrers' => $this->sheel->admincp_stats->fetch($what, $period, 'topreferrers'),
                                        'topsearchterms' => $this->sheel->admincp_stats->fetch($what, $period, 'topsearchterms'),
                                        'toplandingpages' => $this->sheel->admincp_stats->fetch($what, $period, 'toplandingpages'),
                                        'utmcampaigns' => $this->sheel->admincp_stats->fetch($what, $period, 'utmcampaigns')
                                )
                        );
                } else if ($what == 'brands') {
                        $stats = array(
                                'listings' => array(
                                        'brandcount' => $this->sheel->admincp_stats->fetch($what, $period, 'brandcount'),
                                        'productcount' => $this->sheel->admincp_stats->fetch($what, $period, 'productcount'),
                                        'brandsmoderated' => $this->sheel->admincp_stats->fetch($what, $period, 'brandsmoderated'),
                                        'label' => $this->sheel->admincp_stats->fetch($what, $period, 'brandcountlabel'),
                                        'series' => $this->sheel->admincp_stats->fetch($what, $period, 'brandcountseries')
                                ),
                                'stats' => array(
                                        'topbrandsbyorders' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandsbyorders'),
                                        'topbrandownersbyorders' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandownersbyorders'),
                                        'topbrandsbyitemtraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandsbyitemtraffic'),
                                        'topbrandownersbyitemtraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandownersbyitemtraffic'),
                                        'topbrandsbytraffic' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandsbytraffic'),
                                        'topbrandsbyorderreturns' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandsbyorderreturns'),
                                        'topbrandsbyordercancels' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrandsbyordercancels'),
                                        'topsearchterms' => $this->sheel->admincp_stats->fetch($what, $period, 'topsearchterms'),
                                        'toplandingpages' => $this->sheel->admincp_stats->fetch($what, $period, 'toplandingpages'),
                                        'utmcampaigns' => $this->sheel->admincp_stats->fetch($what, $period, 'utmcampaigns')
                                )
                        );
                } else if ($what == 'memberships') {
                }
                return $stats;
        }
        /**
         * Function for printing the prev and next links to allow users to navigate through result listings.
         *
         * @param       integer        total number of rows
         * @param       integer        row limit (per page)
         * @param       integer        current page number
         * @param       string         current page url
         * @param       string         custom &page= name
         *
         * @return      string         HTML representation of the page navigator
         */
        function pagination($number = 0, $rowlimit = 10, $page = 0, $scriptpage = '', $custompagename = 'page')
        {
                $html = '<ul class="segmented" context="adjacent">';
                if (empty($custompagename)) {
                        $custompagename = 'page';
                }
                $startend = $this->sheel->construct_start_end_array($page, $rowlimit, $number);
                $totalpages = ceil(($number / $rowlimit));
                if ($totalpages == 0) {
                        $totalpages = 1;
                }
                if ($number <= $rowlimit) {
                        return false;
                }
                $scriptpage = $this->sheel->seo->remove_querystring_var($scriptpage, '?page');
                $scriptpage = $this->sheel->seo->remove_querystring_var($scriptpage, 'pp');
                if (substr($scriptpage, -1) == '?') {
                        $scriptpage = substr($scriptpage, 0, -1); // removing ending ?
                } else if (substr($scriptpage, -1) == '&') {
                        $scriptpage = substr($scriptpage, 0, -1); // removing ending &
                }
                $url1 = (($page <= 1) ? 'javascript:;' : $scriptpage . ((strrchr($scriptpage, '?') == false) ? '?' : '&') . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit);
                $url2 = (($page > 1 and $totalpages == $page) ? 'javascript:;' : $scriptpage . ((strrchr($scriptpage, '?') == false) ? '?' : '&') . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit);

                $html .= '<li>
                        <a class="btn tooltip tooltip-bottom js-prev-btn' . (($page <= 1) ? ' disabled' : '') . '" href="' . $url1 . '">
                            <span class="tooltip-container"></span>
                            <span class="page-prev"></span>
                        </a>
                    </li>';

                $html .= '<li>
                        <a class="btn tooltip tooltip-bottom js-draw-btn' . (($page > 1 and $totalpages == $page) ? ' disabled' : '') . '" href="' . $url2 . '">
                            <span class="tooltip-container"></span>
                            <span class="page-next"></span>
                        </a>
                    </li></ul>';

                return $html;
        }



        function custom_number_format($n, $precision = 1, $leftsymbol = '', $rightsymbol = '')
        {
                if ($n < 1000) {
                        $n_format = number_format($n); // 1,000
                } else if ($n < 1000000) {
                        $n_format = number_format($n / 1000, $precision) . 'k';
                } else if ($n < 1000000000) {
                        $n_format = number_format($n / 1000000, $precision) . 'm';
                } else {
                        $n_format = number_format($n / 1000000000, $precision) . 'b';
                }
                return $leftsymbol . $n_format . $rightsymbol;
        }
        /**
         * Function to generate a random form field name based on a supplied character length limit
         *
         * @param        integer       length (default 10)
         *
         * @return	string        Returns the random form field name
         */
        function construct_form_name($length = 10)
        {
                $formname = mb_substr(mb_ereg_replace("[^a-zA-Z]", "", password_hash(time(), PASSWORD_DEFAULT)) . mb_ereg_replace("[^0-9]", "", password_hash(time(), PASSWORD_DEFAULT)) . mb_ereg_replace("[^a-zA-Z]", "", password_hash(time(), PASSWORD_DEFAULT)), 0, $length);
                return $formname;
        }
}
?>
