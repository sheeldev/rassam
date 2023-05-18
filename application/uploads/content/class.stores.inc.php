<?php
/**
 * Stores class to perform the majority of store handling functions within Sheel
 *
 * @package      Sheel\Stores
 * @version      1.0.0.0
 * @author       Sheel
 */
class stores
{
        protected $sheel;

        function __construct($sheel)
        {
                $this->sheel = $sheel;
        }
        /*
         * Function to expire featured store statuses (parsed from cron.1minute.php)
         */
        function unsuspend_active_membership_stores()
        {
                $sql = $this->sheel->db->query("
                        SELECT companyid
                        FROM " . DB_PREFIX . "stores
                        WHERE issuspended = '1'
                                AND storesuspendby = '-1'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($stores = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $this->sheel->subscription->handle_no_stores_permission($stores['companyid']);
                        }
                }
                return 'stores->unsuspend_active_membership_stores(), ';
        }
        /**
         * Function to print the total inventory amount for a particular store
         *
         * @param       integer       store id
         *
         * @return      string        HTML formatted display
         */
        function print_total_inventory_amount($storeid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT buynow_price, buynow_qty
                        FROM " . DB_PREFIX . "projects
                        WHERE storeid = '" . intval($storeid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $total = 0;
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $total += ($res['buynow_price'] * $res['buynow_qty']);
                        }
                        return number_format($total, 2);
                }
                return '0.00';
        }

        /**
         * Function to print the total inventory count for a particular store
         *
         * @param       integer       store id
         *
         * @return      string        HTML formatted display
         */
        function print_total_inventory_count($storeid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS total
                        FROM " . DB_PREFIX . "projects
                        WHERE storeid = '" . intval($storeid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['total'];
                }
                return '0';
        }
        /**
         * Function to determine if a seller has an active store
         *
         * @param       integer        seller id
         *
         * @return      boolean        Returns true or false
         */
        function has_active_store($sellerid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT storeid
                        FROM " . DB_PREFIX . "stores
                        WHERE companyid = '" . intval($sellerid) . "'
				AND visible = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        return true;
                }
                return false;
        }
        /**
         * Function to determine if a seller has an active store
         *
         * @param       integer        seller id
         *
         * @return      boolean        Returns true or false
         */
        function has_store($sellerid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT storeid
                        FROM " . DB_PREFIX . "stores
                        WHERE companyid = '" . intval($sellerid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        return true;
                }
                return false;
        }
        /**
         * Function to get a storeid for a seller
         *
         * @param       integer        seller id
         *
         * @return      boolean        Returns true or false
         */
        function get_storeid($sellerid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT storeid
                        FROM " . DB_PREFIX . "stores
                        WHERE companyid = '" . intval($sellerid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['storeid'];
                }
                return 0;
        }
        /**
         * Function to fetch all information related to a specific storefront order
         *
         * @param       string        information request type
         * @param       integer       storefront id
         *
         */
        function info($infotype = '', $storeid = 0)
        {
                $link = 0;
                if ($infotype == 'username') {
                        $sql = $this->sheel->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "stores
                                WHERE storeid = '" . intval($storeid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $results = $this->sheel->fetch_user('username', $res['user_id']);
                                return $results;
                        }
                        return '';
                } else if ($infotype == 'visitorcount') {
                        $sql = $this->sheel->db->query("
                                SELECT COUNT(*) AS visitors
                                FROM " . DB_PREFIX . "sessions
                                WHERE storeid = '" . intval($storeid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                if ($res['visitors'] <= 1) {
                                        return '1';
                                }
                                return number_format($res['visitors']);
                        }
                        return '1';
                } else if ($infotype == 'sellerid') {
                        $sql = $this->sheel->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "stores
                                WHERE storeid = '" . intval($storeid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                return $res['user_id'];
                        }
                        return 0;
                } else if ($infotype == 'email') {
                        $sql = $this->sheel->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "stores
                                WHERE storeid = '" . intval($storeid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $results = $this->sheel->fetch_user('email', $res['user_id']);
                                return $results;
                        }
                        return '';
                } else if ($infotype == 'totalitems') {
                        $sql = $this->sheel->db->query("
                                SELECT COUNT(*) AS count
                                FROM " . DB_PREFIX . "projects
                                WHERE storeid = '" . intval($storeid) . "'
					AND buynow_qty > 0
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                return (int) $res['count'];
                        }
                        return '0';
                } else if ($infotype == 'title') {
                        $field = 'storename';
                } else if ($infotype == 'titleahref') {
                        $field = 'storename';
                        $link = 1;
                } else if ($infotype == 'description') {
                        $field = 'description';
                } else if ($infotype == 'started') {
                        $field = 'started';
                } else if ($infotype == 'storetype') {
                        $field = 'storetype';
                } else if ($infotype == 'salescount') {
                        $field = 'salescount';
                } else if ($infotype == 'views') {
                        $field = 'views';
                } else if ($infotype == 'url') {
                        $field = 'seourl';
                } else if (substr($infotype, 0, 5) == 'email') {
                        $field = 'email' . substr($infotype, 5, strlen($infotype) - 5);
                } else {
                        $field = 'title';
                }
                if ($field != '') {
                        $sql = $this->sheel->db->query("
                                SELECT " . $this->sheel->db->escape_string($field) . " AS field, seourl
                                FROM " . DB_PREFIX . "stores
                                WHERE storeid = '" . intval($storeid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $results = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                if ($link) {
                                        return '<a href="' . HTTPS_SERVER . 'stores/' . $results['seourl'] . '/">' . stripslashes(o($results['field'])) . '</a>';
                                }
                                return stripslashes(o($results['field']));
                        }
                        return '';
                }
        }
        /**
         * Function to return template array data for latest storefronts with inventory posted
         *
         * @param       integer       stores limit (default 5)
         *
         * @return      string        Returns template array data for use with parse_loop() function
         */
        function fetch_latest_storefronts($limit = 5, $cid = 0)
        {
                $cidextra = 'AND s.cid > 0';
                if ($cid > 0) {
                        $cidextra = "AND s.cid = '" . intval($cid) . "'";
                }
                $this->sheel->timer->start();
                $this->sheel->show['lateststorefronts'] = 0;
                $latestauctions = array();
                $sql = $this->sheel->db->query("
                        SELECT s.*, COUNT(i.project_id) AS items
                        FROM " . DB_PREFIX . "stores s
                        LEFT OUTER JOIN  " . DB_PREFIX . "projects i ON (s.storeid = i.storeid)
                        WHERE s.visible = '1'
				$cidextra
                        GROUP BY s.storeid
                        ORDER BY items DESC, started ASC, views DESC, buynow_purchases DESC
                        LIMIT $limit
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $this->sheel->show['lateststorefronts'] = 1;
                        $this->sheel->show['nourlbit'] = true;
                        while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $row['storename'] = $this->sheel->common->url(array('type' => 'store', 'catid' => 0, 'auctionid' => $row['storeid'], 'seourl' => '', 'name' => stripslashes($row['storename']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => 'qid', 'extrahref' => '', 'cutoffname' => ''));
                                $row['description'] = $this->sheel->short_string(stripslashes($row['description']), 100);
                                $row['description'] = $this->sheel->censor->strip_vulgar_words($row['description']);
                                $category = stripslashes($this->sheel->db->fetch_field(DB_PREFIX . "stores_category", "cid = '" . $row['cid'] . "'", "category_name"));
                                $row['category'] = $this->sheel->common->url(array('type' => 'store', 'catid' => 0, 'auctionid' => $row['storeid'], 'seourl' => '', 'name' => $this->sheel->shorten(stripslashes($category), 20), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => 'qid', 'extrahref' => '', 'cutoffname' => ''));
                                $row['started'] = $this->sheel->common->print_date($this->sheel->db->fetch_field(DB_PREFIX . "stores", "storeid = '" . intval($row['storeid']) . "'", "started"));
                                $row['views'] = number_format($row['views']);
                                $row['items'] = number_format($row['items']);
                                $latestauctions[] = $row;
                        }
                }
                $this->sheel->timer->stop();
                DEBUG("fetch_latest_storefronts()", 'FUNCTION', $this->sheel->timer->get(), '');
                return $latestauctions;
        }
        /**
         * Function to return template array data for latest storefront items
         *
         * @param       integer       items limit (default 5)
         *
         * @return      string        Returns template array data for use with parse_loop() function
         */
        function fetch_latest_storefront_items($limit = 5)
        {
                $this->sheel->timer->start();
                $this->sheel->show['lateststoreitems'] = false;
                $latestauctions = array();
                $sql = $this->sheel->db->query("
                        SELECT i.project_id, i.storeid, i.cid, i.buynow_qty, i.project_title, i.description, i.imageurl, i.buynow_price, s.storename, u.user_id, u.username AS merchant, i.imageurl_attachid, i.download_attachid, s.storetype
                        FROM " . DB_PREFIX . "projects AS i,
                        " . DB_PREFIX . "stores AS s,
                        " . DB_PREFIX . "users AS u
                        WHERE i.storeid = s.storeid
                                AND s.user_id = u.user_id
                                AND s.visible = '1'
                                AND i.buynow_qty > 0
                                AND i.buynow_price > 0
                                AND (i.imageurl_attachid > 0 OR i.hasimage > 0)
			ORDER BY RAND()
			LIMIT $limit
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $this->sheel->show['lateststoreitems'] = true;
                        $this->sheel->show['nourlbit'] = true;
                        while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $row['merchant'] = $this->sheel->common->print_username($row['user_id'], 'href', 0, '&amp;view=store-statistics', '?&amp;view=store-statistics');
                                $row['cid'] = $this->sheel->db->fetch_field(DB_PREFIX . "stores", "storeid = '" . $row['storeid'] . "'", "cid");
                                $row['subcategory'] = stripslashes($this->sheel->db->fetch_field(DB_PREFIX . "stores_category", "cid = '" . $row['cid'] . "'", "category_name"));
                                $row['price'] = $this->sheel->currency->format($row['buynow_price']);
                                $row['buynow_price'] = $this->sheel->currency->format($row['buynow_price']);
                                $row['action'] = '<input type="button" class="buttons" onclick="location.href=\'' . $this->sheel->ilpage['stores'] . '?cmd=addtocart&amp;itemid=' . $row['project_id'] . '&amp;id=' . $row['storeid'] . '&amp;qty=1\'" value="{_add_to_cart}" />';
                                if (isset($this->sheel->modules->stores->config['storeitemshomepagestorelink']) and $this->sheel->modules->stores->config['storeitemshomepagestorelink'] and isset($this->sheel->modules->stores->config['enablestores']) and $this->sheel->modules->stores->config['enablestores']) {
                                        $this->sheel->show['nourlbit'] = true;
                                        $row['title'] = $this->sheel->common->url(array('type' => 'storelisting', 'catid' => $row['project_id'], 'auctionid' => $row['storeid'], 'seourl' => '', 'name' => stripslashes($row['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => 'qid', 'extrahref' => '', 'cutoffname' => ''));
                                }
                                if (isset($this->sheel->modules->stores->config['storeitemshomepagecategory']) and $this->sheel->modules->stores->config['storeitemshomepagecategory'] and isset($this->sheel->modules->stores->config['enablestores']) and $this->sheel->modules->stores->config['enablestores']) {
                                        $this->sheel->show['nourlbit'] = true;
                                        $row['storename'] = $this->sheel->common->url(array('type' => 'store', 'catid' => 0, 'auctionid' => $row['storeid'], 'seourl' => '', 'name' => stripslashes($row['storename']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => 'qid', 'extrahref' => '', 'cutoffname' => ''));
                                }
                                if (isset($row['imageurl_attachid']) and $row['imageurl_attachid'] > 0) {
                                        $itemphoto = 1;
                                        $row['sample'] = $this->print_item_photo($row['project_id'], 'thumb', '1', '#ffffff', true);
                                        $row['photoplain'] = $this->print_item_photo($row['project_id'], 'thumb', '1', '#ffffff', true, true);
                                } else {
                                        if (!empty($row['imageurl'])) {
                                                $row['sample'] = '<a href="' . $this->sheel->ilpage['stores'] . '?cmd=viewitem&amp;itemid=' . $row['project_id'] . '"><img src="' . $row['imageurl'] . '" border="0" alt="" style="max-width:108px;max-height:108px" id="" /></a>';
                                                $row['photoplain'] = '<a href="' . $this->sheel->ilpage['stores'] . '?cmd=viewitem&amp;itemid=' . $row['project_id'] . '"><span class="imgb"><img src="' . $row['imageurl'] . '" border="0" alt="" style="max-width:108px;max-height:108px" id="" /></span></a>';
                                                $itemphoto = 1;
                                        } else {
                                                $row['sample'] = '<a href="' . $this->sheel->ilpage['stores'] . '?cmd=viewitem&amp;itemid=' . $row['project_id'] . '"><img src="' . $this->sheel->ilconfig['template_relativeimagepath_cdn'] . $this->sheel->ilconfig['template_imagesfolder'] . 'v5/img_nophoto.gif' . '" border="0" alt="" style="max-width:108px;max-height:108px" /></a>';
                                                $row['photoplain'] = '<a href="' . $this->sheel->ilpage['stores'] . '?cmd=viewitem&amp;itemid=' . $row['project_id'] . '"><span class="imgb"><img src="' . $this->sheel->ilconfig['template_relativeimagepath_cdn'] . $this->sheel->ilconfig['template_imagesfolder'] . 'v5/img_nophoto.gif" border="0" alt="" style="max-width:108px;max-height:108px" /></span></a>';
                                                $itemphoto = 0;
                                        }
                                }
                                if ($row['storetype'] == 'product') {
                                        $shipsto = $this->sheel->stores->fetch_item_shipping_destinations($row['project_id'], $row['download_attachid']);
                                        $row['shipsto'] = '<div style="padding-top:9px" class="smaller gray">{_ships_to}: <span class="blue">' . $shipsto . '</span></div>';
                                } else {
                                        $row['shipsto'] = '';
                                }
                                $row['qty'] = number_format($row['buynow_qty']);
                                $row['class'] = 'alt1';
                                $latestauctions[] = $row;
                        }
                }
                $this->sheel->timer->stop();
                DEBUG("fetch_latest_storefront_items()", 'FUNCTION', $this->sheel->timer->get(), '');
                return $latestauctions;
        }
        function link_item_to_storeids()
        {
                $sql = $this->sheel->db->query("
                        SELECT storeid, user_id
                        FROM " . DB_PREFIX . "stores
                        WHERE visible = '1'
                                AND storeisclosed = '0'
                ");
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) { // for every store, update storeid for items from this seller
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET storeid = '" . $row['storeid'] . "'
                                        WHERE user_id = '" . $row['user_id'] . "'
                                ");
                        }
                }
                return 'stores->link_item_to_storeids(), ';
        }
        function link_store_cid_to_items()
        {
                $sql = $this->sheel->db->query("
                        SELECT id, storeid
                        FROM " . DB_PREFIX . "projects
                        WHERE storeid > 0
                                AND storecid <= 0
                ");
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) { // for every item, update storecid for items from this seller
                                $storecid = $this->sheel->db->fetch_field(DB_PREFIX . "stores_category", "storeid = '" . $row['storeid'] . "' AND canremove = '0'", "cid");
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET storecid = '" . intval($storecid) . "'
                                        WHERE id = '" . $row['id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
                return 'stores->link_store_cid_to_items(), ';
        }
        function print_store_categories($cid = '', $storeid = '', $baseurl = '')
        {
                $this->sheel->show['leftnavcategories'] = false;
                $html = '<ul>';
                $sql = $this->sheel->db->query("
                        SELECT s.cid, s.category_name, s.seourl, COUNT(p.id) AS items
                        FROM " . DB_PREFIX . "stores_category s
                        LEFT JOIN " . DB_PREFIX . "projects p ON (p.storecid = s.cid AND p.status = 'open' AND p.visible = '1')
                        WHERE s.visible = '1'
                                AND s.storeid = '" . intval($storeid) . "'
                                " . $this->sheel->search->sqlquery['keywords'] . "
                        GROUP BY s.cid
                        HAVING COUNT(p.id) > 0
                        ORDER BY category_name ASC
                ");
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $this->sheel->show['leftnavcategories'] = true;
                        while ($row = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $hf = $this->sheel->seo->print_hidden_fields(true, array('id', 'searchid', 'sef', 'scid', 'cid', 'page', 'sort'), true);
                                $html .= (($cid == $row['cid'])
                                        ? '<li class="pb-6 fs-13 black bold">' . o($row['category_name']) . ' <span class="bidi litegray normal">(' . $row['items'] . ')</span></li>'
                                        : '<li class="pb-6 fs-13"><a href="' . $baseurl . $row['seourl'] . '/' . $hf . '">' . o($row['category_name']) . '</a> <span class="bidi litegray">(' . $row['items'] . ')</span></li>');
                        }
                }
                $html .= '</ul>';
                return $html;
        }
        function sync_seller_product_feeds($syncmode = 'daily')
        {
                $sql = $this->sheel->db->query("
			SELECT storeid, sync_url, sync_auth, sync_user, sync_pass, sync_skufield, sync_qtyfield, sync_pricefield
			FROM " . DB_PREFIX . "stores
			WHERE sync_url != ''
				AND sync_frequency = '" . $this->sheel->db->escape_string($syncmode) . "'
                                AND visible = '1'
                                AND ispaused = '0'
                                AND issuspended = '0'
                                AND storeisclosed = '0'
		", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) { // for each store's data feed....
                                /*$googledrive = $res['sync_gdurl'];
                                if ($googledrive == 'yes')
                                {
                                $host = 'https://drive.google.com/uc?export=download&id='.$res['sync_gdurl'];
                                }
                                else
                                {
                                $host = $res['sync_url'];
                                }*/
                                $host = $res['sync_url'];
                                $ch = curl_init();
                                if ($res['sync_auth']) { // check if the csv is on ftp
                                        if (substr($host, 0, 6) === "ftp://") { // set ftp username and password
                                                $login = '$_FTP[' . $res['sync_user'] . ']:$_FTP[' . $res['sync_pass'] . ']';
                                        } else {
                                                $login = $res['sync_user'] . ':' . $res['sync_pass'];
                                        }
                                        $curl_config = [
                                                CURLOPT_URL => $host,
                                                CURLOPT_USERPWD => $login,
                                                CURLOPT_VERBOSE => 1,
                                                CURLOPT_RETURNTRANSFER => 1,
                                                CURLOPT_AUTOREFERER => false,
                                                CURLOPT_REFERER => HTTPS_SERVER,
                                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                CURLOPT_HEADER => 0,
                                                CURLOPT_SSL_VERIFYHOST => 0,
                                                // do not verify that host matches one in certificate
                                                CURLOPT_SSL_VERIFYPEER => 0,
                                                // do not verify certificate's meta
                                                CURLOPT_FOLLOWLOCATION => true,
                                        ];
                                } else { // no credentials needed
                                        $curl_config = [
                                                CURLOPT_URL => $host,
                                                CURLOPT_VERBOSE => 1,
                                                CURLOPT_RETURNTRANSFER => 1,
                                                CURLOPT_AUTOREFERER => false,
                                                CURLOPT_REFERER => HTTPS_SERVER,
                                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                CURLOPT_HEADER => 0,
                                                CURLOPT_SSL_VERIFYHOST => 0,
                                                CURLOPT_SSL_VERIFYPEER => 0,
                                                CURLOPT_FOLLOWLOCATION => true,
                                        ];
                                }
                                curl_setopt_array($ch, $curl_config);
                                $result = curl_exec($ch);
                                // if ,
                                if (empty($result)) { // something went wrong
                                        // update db with problem & continue next store
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "stores
                                                SET sync_laststatus = '0',
                                                sync_lastcheck = '" . DATETIME24H . "'
                                                WHERE storeid = '" . $res['storeid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        continue;
                                }
                                $destination = DIR_TMP . 'data_raw.csv';
                                $file = fopen($destination, "w+");
                                fputs($file, $result);
                                fclose($file);
                                unset($result);
                                curl_close($ch);

                                // get settings for the csv fields
                                $sku = $res['sync_skufield'];
                                $quantity = $res['sync_qtyfield'];
                                $price = $res['sync_pricefield'];
                                $pricesync = false;
                                if (!empty($price)) {
                                        $pricesync = true;
                                }
                                $delimiter = $res['sync_delimiter'];
                                if ($delimiter == 't') {
                                        $delimiter = '\t';
                                }
                                // the clean version of our data_raw.csv
                                $output = DIR_TMP . 'data_raw_clean.csv';
                                if (false !== ($i = fopen($destination, 'r'))) { // delete columns we don't need
                                        $o = fopen($output, 'w+');
                                        $c = 1; // current line
                                        while (false !== ($data = fgetcsv($i, 0, $delimiter))) { // get the ids of the columns we need
                                                if ($c == 1) {
                                                        $id_sku = array_search($sku, $data);
                                                        $id_qty = array_search($quantity, $data);
                                                        if ($pricesync) {
                                                                $id_price = array_search($price, $data);
                                                        }
                                                } else { // build new row with only the columns that we need
                                                        if ($pricesync) {
                                                                $outputData = array($data[$id_sku], $data[$id_qty], $data[$id_price]);
                                                        } else {
                                                                $outputData = array($data[$id_sku], $data[$id_qty]);
                                                        }
                                                        // write minimied csv-file
                                                        fputcsv($o, $outputData);
                                                }
                                                $c++;
                                        }
                                        // close files
                                        fclose($i);
                                        fclose($o);
                                        // clear raw file
                                        file_put_contents($destination, '');
                                        unlink($destination);
                                }
                                // open clean file
                                $fileHandle = fopen($output, "r");
                                if ($fileHandle === false) {
                                        $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "stores
                                                SET sync_laststatus = '0',
                                                sync_lastcheck = '" . DATETIME24H . "'
                                                WHERE storeid = '" . $res['storeid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        continue;
                                }
                                while (!feof($fileHandle)) {
                                        // $product[0] = Product SKU
                                        // $product[1] = Quantity
                                        // $product[2] = Price (optional)
                                        while (($product = fgetcsv($fileHandle)) !== false) {
                                                if ($product[0] != '' and $product[1] != '') {
                                                        if ($pricesync and $product[2] != '') { // price & qty..
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects
                                                                        SET buynow_qty = '" . intval($product[1]) . "',
                                                                        buynow_price = '" . $this->sheel->db->escape_string((float) $product[2]) . "',
                                                                        startprice = '" . $this->sheel->db->escape_string((float) $product[2]) . "',
                                                                        currentprice = '" . $this->sheel->db->escape_string((float) $product[2]) . "'
                                                                        WHERE storeid = '" . $res['storeid'] . "'
                                                                                AND sku = '" . $this->sheel->db->escape_string($product[0]) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // variants
                                                                $sql2 = $this->sheel->db->query("
                                                                        SELECT project_id
                                                                        FROM " . DB_PREFIX . "projects
                                                                        WHERE storeid = '" . $res['storeid'] . "'
                                                                                AND variants = '1'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($this->sheel->db->num_rows($sql2) > 0) {
                                                                        while ($res2 = $this->sheel->db->fetch_array($sql2, DB_ASSOC)) {
                                                                                $this->sheel->db->query("
                                                                                        UPDATE " . DB_PREFIX . "variants
                                                                                        SET qty = '" . intval($product[1]) . "',
                                                                                        price = '" . $this->sheel->db->escape_string((float) $product[2]) . "'
                                                                                        WHERE project_id = '" . $res2['project_id'] . "'
                                                                                                AND sku = '" . $this->sheel->db->escape_string($product[0]) . "'
                                                                                        LIMIT 1
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                        }
                                                                }
                                                        } else { // qty only
                                                                $this->sheel->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects
                                                                        SET buynow_qty = '" . intval($product[1]) . "'
                                                                        WHERE storeid = '" . $res['storeid'] . "'
                                                                                AND sku = '" . $this->sheel->db->escape_string($product[0]) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // variants
                                                                $sql2 = $this->sheel->db->query("
                                                                        SELECT project_id
                                                                        FROM " . DB_PREFIX . "projects
                                                                        WHERE storeid = '" . $res['storeid'] . "'
                                                                                AND variants = '1'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($this->sheel->db->num_rows($sql2) > 0) {
                                                                        while ($res2 = $this->sheel->db->fetch_array($sql2, DB_ASSOC)) {
                                                                                $this->sheel->db->query("
                                                                                        UPDATE " . DB_PREFIX . "variants
                                                                                        SET qty = '" . intval($product[1]) . "'
                                                                                        WHERE project_id = '" . $res2['project_id'] . "'
                                                                                                AND sku = '" . $this->sheel->db->escape_string($product[0]) . "'
                                                                                        LIMIT 1
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                                // close the file
                                fclose($fileHandle);
                                file_put_contents($output, '');
                                unlink($output);

                                // all done for this store!
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "stores
                                        SET sync_laststatus = '1',
                                        sync_lastsync = '" . DATETIME24H . "',
                                        sync_lastcheck = '" . DATETIME24H . "'
                                        WHERE storeid = '" . $res['storeid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
                return 'stores->sync_seller_product_feeds(' . $syncmode . '), ';
        }
        function create_store($payload = array(), $notifyadmin = true)
        {
                $fileid = (isset($payload['fileid']) and !empty($payload['fileid'])) ? intval($payload['fileid']) : '0';
                $bgfileid = (isset($payload['bgfileid']) and !empty($payload['bgfileid'])) ? intval($payload['bgfileid']) : '0';
                $seourl = $this->sheel->seo->construct_seo_url_name(o($payload['storename']));
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "stores
                        (storeid, user_id, storename, seourl, description, logo_attachid, bg_attachid, started, storetype, visible)
                        VALUES(
                        NULL,
                        '" . $payload['userid'] . "',
                        '" . $this->sheel->db->escape_string($payload['storename']) . "',
                        '" . $this->sheel->db->escape_string($seourl) . "',
                        '" . $this->sheel->db->escape_string($payload['description']) . "',
                        '" . intval($fileid) . "',
                        '" . intval($bgfileid) . "',
                        '" . DATETIME24H . "',
                        'product',
                        '" . (($this->sheel->config['storesmoderation']) ? '0' : '1') . "')
                ", 0, null, __FILE__, __LINE__);
                $newstoreid = $this->sheel->db->insert_id();
                $other1 = '{_other}';
                $other2 = '{_other_lc}';
                $this->sheel->template->templateregistry['other1'] = $other1;
                $other1 = $this->sheel->template->parse_template_phrases('other1');
                $this->sheel->template->templateregistry['other2'] = $other2;
                $other2 = $this->sheel->template->parse_template_phrases('other2');
                $this->sheel->db->query("
                        INSERT INTO " . DB_PREFIX . "stores_category
                        (cid, storeid, category_name, seourl, parentid, level, canpost, canremove, visible, sort)
                        VALUES(
                        NULL,
                        '" . $newstoreid . "',
                        '" . $this->sheel->db->escape_string($other1) . "',
                        '" . $this->sheel->db->escape_string($other2) . "',
                        '0',
                        '1',
                        '1',
                        '0',
                        '1',
                        '100')
                ", 0, null, __FILE__, __LINE__);
                $newcatid = $this->sheel->db->insert_id();
                // merge all sellers listings into new store (except classified ads)
                $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "projects
                        SET storeid = '" . intval($newstoreid) . "',
                        storecid = '" . intval($newcatid) . "'
                        WHERE user_id = '" . intval($payload['userid']) . "'
                ", 0, null, __FILE__, __LINE__);
                $items = $this->sheel->db->affected_rows();
                $existing = array(
                        '{{storetitle}}' => o($payload['storename']),
                        '{{storedescription}}' => o($payload['description']),
                        '{{username}}' => $payload['username'],
                        '{{url}}' => HTTPS_SERVER . 'stores/' . $seourl . '/',
                        '{{storestatus}}' => (($this->sheel->config['storesmoderation']) ? '{_pending_approval_lc}' : '{_active_lc}'),
                        '{{items}}' => $items
                );
                $this->sheel->email->mail = $payload['email'];
                $this->sheel->email->slng = $payload['slng'];
                $this->sheel->email->get('new_storefront_created');
                $this->sheel->email->set($existing);
                $this->sheel->email->send();
                if ($notifyadmin) {
                        $this->sheel->email->mail = SITE_CONTACT;
                        $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                        $this->sheel->email->get('new_storefront_created_admin');
                        $this->sheel->email->set($existing);
                        $this->sheel->email->send();
                }
        }
        function suspend($storeid = 0, $reason = '')
        {
                $issuspended = $this->sheel->db->fetch_field(DB_PREFIX . "stores", "storeid = '" . intval($storeid) . "'", "issuspended");
                if ($storeid > 0 and $reason != '' and $issuspended <= 0) {
                        $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "stores
				SET visible = '0',
				issuspended = '1',
				reasonforsuspend = '" . $this->sheel->db->escape_string($reason) . "',
				storesuspendby = '-1',
				suspenddate = '" . DATETIME24H . "'
				WHERE storeid = '" . intval($storeid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET storeid = '0'
				WHERE storeid = '" . intval($storeid) . "'
			", 0, null, __FILE__, __LINE__);
                        return true;
                }
                return false;
        }
        function unsuspend($storeid = 0)
        {
                if ($storeid > 0) {
                        $userid = $this->sheel->db->fetch_field(DB_PREFIX . "stores", "storeid = '" . intval($storeid) . "'", "user_id");
                        $issuspended = $this->sheel->db->fetch_field(DB_PREFIX . "stores", "storeid = '" . intval($storeid) . "'", "issuspended");
                        if ($userid > 0 and $issuspended > 0) {
                                $this->sheel->db->query("
					UPDATE " . DB_PREFIX . "stores
					SET visible = '1',
					issuspended = '0',
					reasonforsuspend = '',
					storesuspendby = '0',
					suspenddate = '0000-00-00 00:00:00'
					WHERE storeid = '" . intval($storeid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
                                $this->sheel->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET storeid = '" . intval($storeid) . "'
					WHERE user_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                }
                return false;
        }

        function pulldown($cssextra = 'draw-select', $formfieldname = 'storeid', $fieldname = 'storeid', $selected = '')
        {
                $pulldown = '';
                $stores = array();
                $values = array();
                $store = array();
                $query = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "storeid, storename
				FROM " . DB_PREFIX . "stores where user_id in (select user_id from " . DB_PREFIX . "users where isadmin='1')
			", 0, null, __FILE__, __LINE__);



                while ($store = $this->sheel->db->fetch_array($query, DB_ASSOC)) {
                        // generate string type values (ie: USD)
                        $stores[$store['storename']] = array(
                                'storename' => $store['storename']
                        );
                        // generate integer type values (ie: 1)
                        $stores[$store['storeid']] = array(
                                'storename' => $store['storename']
                        );

                }

                $selected = ((empty($selected)) ? '-1' : $selected);
                $values['-1'] = '{_select}';

                foreach ($stores as $key => $val) {
                        if (is_int($key)) {
                                $values[$key] = $val['storename'];
                        }
                }




                $pulldown = $this->sheel->construct_pulldown($fieldname, $formfieldname, $values, $selected, ' class="' . $cssextra . '"');

                return $pulldown;
        }

}
?>