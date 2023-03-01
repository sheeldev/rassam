<?php
define('LOCATION', 'ajax');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array('header' => array('functions'));
$sheel->template->meta['cssinclude'] = array(
	'header',
	'thumbnail',
	'footer',
	'common',
	'vendor' => array(
		'balloon',
		'font-awesome',
		'bootstrap'
	)
);
// only skip session if method doesn't change active session in any way
$methods = array(
	'atc' => array('skipsession' => true),
	'dfc' => array('skipsession' => true),
	'avr' => array('skipsession' => true),
	'cardselect' => array('skipsession' => false),
	// changes bpid
	'previewbid' => array('skipsession' => true),
	'submitproductbid' => array('skipsession' => true),
	'previewpm' => array('skipsession' => true),
	'submitpm' => array('skipsession' => true),
	'pmbploader' => array('skipsession' => true),
	'pminfo' => array('skipsession' => true),
	'vminfo' => array('skipsession' => true),
	'pmremove' => array('skipsession' => true),
	'inlineedit' => array('skipsession' => true),
	'watchlist' => array('skipsession' => true),
	'addwatchlist' => array('skipsession' => true),
	'unfollow' => array('skipsession' => true),
	'follow' => array('skipsession' => true),
	'check_email' => array('skipsession' => true),
	'searchfavorites' => array('skipsession' => true),
	'acpenhancements' => array('skipsession' => true),
	'categories' => array('skipsession' => true),
	'cbcountries' => array('skipsession' => true),
	'quickregister' => array('skipsession' => false),
	'autocomplete' => array('skipsession' => true),
	'iteminfo' => array('skipsession' => true),
	'showstates' => array('skipsession' => true),
	'showcities' => array('skipsession' => true),
	'showshippers' => array('skipsession' => true),
	'showshippackages' => array('skipsession' => true),
	'showshippickupdropoff' => array('skipsession' => true),
	'showduration' => array('skipsession' => true),
	'shipcalculator' => array('skipsession' => true),
	'showshipservicerows' => array('skipsession' => true),
	'calculate' => array('skipsession' => true),
	'calculateinsertionfees' => array('skipsession' => true),
	'search_category_keyword' => array('skipsession' => true),
	'search_brand_keyword' => array('skipsession' => true),
	'heropicture' => array('skipsession' => true),
	'fileuploader' => array('skipsession' => true),
	'fileuploaderform' => array('skipsession' => true),
	'fileuploadreorder' => array('skipsession' => true),
	'fileuploadviaurl' => array('skipsession' => true),
	'recentlyvieweditems' => array('skipsession' => true),
	'rvfooter' => array('skipsession' => true),
	'favourites' => array('skipsession' => true),
	'categoryquestionspulldown' => array('skipsession' => true),
	'searchresult' => array('skipsession' => true),
	'build' => array('skipsession' => true),
	'version' => array('skipsession' => true),
	'licensekey' => array('skipsession' => true),
	'sessiontimeout' => array('skipsession' => true),
	'pmbcheckup' => array('skipsession' => true),
	'pmbpcheckup' => array('skipsession' => true),
	'viewpermission' => array('skipsession' => true),
	'abusereport' => array('skipsession' => true),
	'bitcoin-confirmations' => array('skipsession' => true),
	'updateitemqty' => array('skipsession' => true),
	'updateitemvqty' => array('skipsession' => true),
	'ordercheckout' => array('skipsession' => true),
	'shoppingcart' => array('skipsession' => true),
	'thumbdragdropupload' => array('skipsession' => true),
	'hpadragdropupload' => array('skipsession' => true),
	'savesearch' => array('skipsession' => true),
	'paymentreminder' => array('skipsession' => true),
	'paymentreleasereminder' => array('skipsession' => true),
	'submitpl' => array('skipsession' => true),
	'submitsf' => array('skipsession' => true),
	'submitud' => array('skipsession' => true),
	'updateblocking' => array('skipsession' => true),
	'fetchblocking' => array('skipsession' => true),
	'updateshippingpromotion' => array('skipsession' => true),
	'fetchshippromotions' => array('skipsession' => true),
	'bulkmailer' => array('skipsession' => true),
	'upgradelog' => array('skipsession' => true),
	'paymentlog' => array('skipsession' => true),
	'ldaplog' => array('skipsession' => true),
	'shippinglog' => array('skipsession' => true),
	'smtplog' => array('skipsession' => true),
	'forceautoupdate' => array('skipsession' => true),
	'testldap' => array('skipsession' => true),
	'testdomain' => array('skipsession' => true),
	'sefurlcollide' => array('skipsession' => true),
	'acploadtemplate' => array('skipsession' => true),
	'acpsavetemplate' => array('skipsession' => true),
	'acpclosetemplate' => array('skipsession' => true),
	'acpswitchtemplate' => array('skipsession' => true),
	'acpcomparetemplate' => array('skipsession' => true),
	'ddu' => array('skipsession' => true),
	'dpu' => array('skipsession' => true),
	'searchfacets' => array('skipsession' => true),
	'acpcheckusername' => array('skipsession' => true),
	'acpcheckemail' => array('skipsession' => true),
	'acproleoptions' => array('skipsession' => true),
	'acpfsr' => array('skipsession' => true),
	'fetchvariants' => array('skipsession' => true),
	'itemvariants' => array('skipsession' => true),
	'addauctionstream' => array('skipsession' => true),
	'goingonce' => array('skipsession' => true),
	'outputauctionstream' => array('skipsession' => true),
	'jswidget' => array('skipsession' => true),
	'wysiwygupload' => array('skipsession' => true),
	'wysiwygdelete' => array('skipsession' => true),
	'wysiwygmanage' => array('skipsession' => true),
	'consent' => array('skipsession' => false),
	'brandsearch' => array('skipsession' => true),
	'sellersearch' => array('skipsession' => true),
	'eventsearch' => array('skipsession' => true),
	'productsearch' => array('skipsession' => true),
	'catsearch' => array('skipsession' => true),
	'checkstorename' => array('skipsession' => true),
	'checkbrandname' => array('skipsession' => true),
	'storenewsletter' => array('skipsession' => true),
	'deleteitemquestion' => array('skipsession' => true),
	'voteitemanswer' => array('skipsession' => true),
	'eventinfo' => array('skipsession' => true),
	'importupc' => array('skipsession' => true),
	'refreshupctr' => array('skipsession' => true),
	'lollipopgetimage' => array('skipsession' => true),
	'lollipopsaveimage' => array('skipsession' => true),
	'eventnotification' => array('skipsession' => true),
	'acpimporttheme' => array('skipsession' => true),
	'acpimportskin' => array('skipsession' => true),
	'tzoffset' => array('skipsession' => true),
	'requestdetails_orig' => array('skipsession' => true),
	'requestdetails' => array('skipsession' => true)
);

if (isset($sheel->GPC['do'])) {
	if (isset($methods[$sheel->GPC['do']]['skipsession'])) {
		define('SKIP_SESSION', $methods[$sheel->GPC['do']]['skipsession']);
	} else {
		define('SKIP_SESSION', false);
	}
	if ($sheel->GPC['do'] == 'inlineedit') { // inline text input editor
		if (isset($sheel->GPC['action']) and !empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			switch ($sheel->GPC['action']) {
				case 'permission_accesstext': { // subscription permissions title
						break;
					}
				case 'permission_description': { // subscription permissions description
						break;
					}
				case 'favsearchtitle': { // favorite search title
						$sheel->GPC['text'] = $sheel->common->js_escaped_to_xhtml_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = $sheel->common->xhtml_entities_to_numeric_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = mb_convert_encoding($sheel->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "search_favorites
						SET title = '" . $sheel->db->escape_string($sheel->GPC['text']) . "'
						WHERE searchid = '" . intval($sheel->GPC['id']) . "'
					", 0, null, __FILE__, __LINE__);
						echo $sheel->GPC['text'];
						break;
					}
				case 'watchlistcomment': { // favorite search title
						$sheel->GPC['text'] = $sheel->common->js_escaped_to_xhtml_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = $sheel->common->xhtml_entities_to_numeric_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = mb_convert_encoding($sheel->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "watchlist
						SET comment = '" . $sheel->db->escape_string($sheel->GPC['text']) . "'
						WHERE watchlistid = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						echo $sheel->GPC['text'];
						break;
					}
				case 'sellerpaymethod': { // seller updating pay method
						$sheel->GPC['text'] = $sheel->common->js_escaped_to_xhtml_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = $sheel->common->xhtml_entities_to_numeric_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = mb_convert_encoding($sheel->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
						echo $sheel->GPC['text'];
						break;
					}
				case 'sellershiptracking': { // seller updating shipment tracking number
						$sheel->GPC['text'] = $sheel->common->js_escaped_to_xhtml_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = $sheel->common->xhtml_entities_to_numeric_entities($sheel->GPC['text']);
						$sheel->GPC['text'] = mb_convert_encoding($sheel->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
						echo $sheel->GPC['text'];
						break;
					}
			}
			exit();
		}
	} else if ($sheel->GPC['do'] == 'unfollow') { // remove item or seller from watchlist


		if (isset($sheel->GPC['userid']) and $sheel->GPC['userid'] > 0 and isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['itemid']) and $sheel->GPC['itemid'] > 0) {
				$success = $sheel->watchlist->delete_item('item', intval($sheel->GPC['userid']), 0, intval($sheel->GPC['itemid']));
				if ($success) {
					$sheel->xml->add_tag('status', 'removeditem');
				}
			} else if (isset($sheel->GPC['sellerid']) and $sheel->GPC['sellerid'] > 0) {
				$success = $sheel->watchlist->delete_item('seller', intval($sheel->GPC['userid']), intval($sheel->GPC['sellerid']), 0);
				if ($success) {
					$sheel->xml->add_tag('status', 'removedseller');
				}
			} else {
				$sheel->xml->add_tag('status', 'error');
			}
			$sheel->xml->print_xml();
			exit();
		}
	} else if ($sheel->GPC['do'] == 'follow') { // add item or seller from watchlist


		if (isset($sheel->GPC['userid']) and $sheel->GPC['userid'] > 0 and isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['itemid']) and $sheel->GPC['itemid'] > 0) {
				$success = $sheel->watchlist->insert_item(intval($sheel->GPC['userid']), intval($sheel->GPC['itemid']), 'auction', '{_added_from_listing_page}', 0, 0, 0, 0);
				if ($success) {
					$sheel->xml->add_tag('status', 'addeditem');
				}
			} else if (isset($sheel->GPC['sellerid']) and $sheel->GPC['sellerid'] > 0) {
				$success = $sheel->watchlist->insert_item(intval($sheel->GPC['userid']), intval($sheel->GPC['sellerid']), 'mprovider', '{_added_from_listing_page}', 0, 0, 0, 0);
				if ($success) {
					$sheel->xml->add_tag('status', 'addedseller');
				}
			} else {
				$sheel->xml->add_tag('status', 'error');
			}
			$sheel->xml->print_xml();
			exit();
		}
	} else if ($sheel->GPC['do'] == 'check_email') {
		if (isset($sheel->GPC['email_user'])) {
			$add_customer['status'] = $add_customer['status1'] = true;
			$sql = $sheel->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE email = '" . $sheel->db->escape_string($sheel->GPC['email']) . "'
			", 0, null, __FILE__, __LINE__);
			$html = " ";
			if ($sheel->db->num_rows($sql) > 0) {
				$add_customer['status1'] = false;
				$html = "0";
			}
			$sql1 = $sheel->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql1) > 0) {
				$add_customer['status'] = false;
				$html = "1";
			}
			if ($sheel->db->num_rows($sql) > 0 and $sheel->db->num_rows($sql1) > 0) {
				$html = "2";
			}
			if ($sheel->db->num_rows($sql) == 0 and $sheel->db->num_rows($sql1) == 0) {
				$html = "3";
			}
			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'searchfavorites') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {

			$sheel->GPC['searchid'] = intval($sheel->GPC['searchid']);
			$sheel->GPC['value'] = ($sheel->GPC['value'] == 'on' ? 1 : 0);
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "search_favorites
				SET subscribed = '" . intval($sheel->GPC['value']) . "',
				added = '" . DATETIME24H . "'
				WHERE searchid = '" . intval($sheel->GPC['searchid']) . "'
					AND user_id = '" . intval($_SESSION['sheeldata']['user']['userid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->GPC['value']) {
				$sheel->xml->add_tag('status', 'on');
			} else {
				$sheel->xml->add_tag('status', 'off');
			}
			$sheel->xml->print_xml();
			exit();
		}
	} else if ($sheel->GPC['do'] == 'acpenhancements') {
		if (isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {

			$sheel->GPC['id'] = intval($sheel->GPC['id']);
			$sheel->GPC['value'] = ($sheel->GPC['value'] == 'on' ? 1 : 0);
			$sheel->GPC['type'] = strip_tags($sheel->GPC['type']);
			switch ($sheel->GPC['type']) {
				case 'featured': {
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured = '" . intval($sheel->GPC['value']) . "'
						WHERE project_id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						break;
					}
				case 'featured_searchresults': {
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured_searchresults = '" . intval($sheel->GPC['value']) . "'
						WHERE project_id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						break;
					}
				case 'bold': {
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET bold = '" . intval($sheel->GPC['value']) . "'
						WHERE project_id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						break;
					}
				case 'highlite': {
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET highlite = '" . intval($sheel->GPC['value']) . "'
						WHERE project_id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						break;
					}
				case 'autorelist': {
						$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET autorelist = '" . intval($sheel->GPC['value']) . "'
						WHERE project_id = '" . intval($sheel->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
						break;
					}
			}
			if ($sheel->GPC['value']) {
				$sheel->xml->add_tag('status', 'on');
			} else {
				$sheel->xml->add_tag('status', 'off');
			}
			$sheel->xml->print_xml();
			exit();
		}
	} else if ($sheel->GPC['do'] == 'categories') { // ajax category selector
		require_once(DIR_CONTROLLERS . 'client/ajax_categories.php');
	} else if ($sheel->GPC['do'] == 'cbcountries') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$sql = $sheel->db->query("
				SELECT locationid, location_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "locations
				WHERE visible = '1'
				ORDER BY location_" . $_SESSION['sheeldata']['user']['slng'] . " ASC
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				$rc = 0;
				while ($res = $sheel->db->fetch_array($sql)) {
					$res['class'] = ($rc % 2) ? 'alt2' : 'alt1';
					$res['cb'] = '<input type="checkbox" name="locationid[]" value="' . $res['locationid'] . '" />';
					$res['title'] = stripslashes($res['title']);
					$countries[] = $res;
					$rc++;
				}
				$sheel->template->load_popup('head', 'popup_header.html');
				$sheel->template->load_popup('main', 'ajax_countries.html');
				$sheel->template->load_popup('foot', 'popup_footer.html');
				$sheel->template->parse_loop('main', array('countries' => $countries));
				$sheel->template->pprint('head', array('headinclude' => $sheel->template->meta['headinclude'], 'onbeforeunload' => '', 'onload' => $sheel->template->meta['onload']));
				$sheel->template->pprint('main', array());
				$sheel->template->pprint('foot', array('footinclude' => $sheel->template->meta['footinclude']));
				exit();
			} else {
				echo 'Could not fetch country list at this time.';
				exit();
			}
		}
	} else if ($sheel->GPC['do'] == 'quickregister') {
		if (isset($sheel->GPC['qusername']) and isset($sheel->GPC['qpassword']) and isset($sheel->GPC['qemail'])) {
			$sheel->GPC['source'] = 'Quick Registration';
			$sheel->GPC['output'] = 'return_userarray';
			$response = $sheel->registration->quick($sheel->GPC);
			if (!$response) {
				if (count($sheel->registration->quickregistererrors) > 0) {
					foreach ($sheel->registration->quickregistererrors as $error) {
						echo $error;
						exit();
					}
				}
				$sheel->template->templateregistry['quickregister_notice'] = '{_unknown_error}';
				echo $sheel->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			die($response); // 1 = active, 2 = unverified, 3 = moderated
		} else {
			$sheel->template->templateregistry['quickregister_notice'] = '{_please_answer_all_required_fields}';
			echo $sheel->template->parse_template_phrases('quickregister_notice');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'autocomplete') { // autocomplete search bar
		$xmlDoc = '<?xml version="1.0" encoding="utf-8"?><root>';
		if (isset($sheel->GPC['q']) and !empty($sheel->GPC['q'])) {
			$keyword_text = $sheel->GPC['q'];
			// keywords
			$sqlquery['keywords'] = "AND keyword LIKE '" . $sheel->db->escape_string($keyword_text) . "%'";
			$available = $available2 = array();
			$sql = $sheel->db->query("
				SELECT keyword
				FROM " . DB_PREFIX . "search
				WHERE keyword != ''
					$sqlquery[keywords]
					AND count > 1
				GROUP BY keyword
				ORDER BY keyword ASC
				LIMIT 10
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$res['keyword'] = mb_strtolower($res['keyword']);
					$available[] = $res['keyword'];
					$availablemode[$res['keyword']] = 'product';
				}
			}
			$sqlquery['keywords'] = "AND project_title LIKE '" . $sheel->db->escape_string($keyword_text) . "%'";
			$sql = $sheel->db->query("
				SELECT project_title AS keyword
				FROM " . DB_PREFIX . "projects
				WHERE project_title != ''
					$sqlquery[keywords]
					AND status = 'open'
				GROUP BY project_title
				ORDER BY project_title ASC
				LIMIT 10
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$res['keyword'] = mb_strtolower($res['keyword']);
					$available2[] = $res['keyword'];
					$availablemode[$res['keyword']] = 'product';
				}
			}
			$results = array_merge($available2, $available);
			if (count($results) > 0) {
				$i = 0;
				foreach ($results as $key => $label) {
					if ($i <= 10) {
						$label = o($label);
						$labelformatted = str_replace(mb_strtolower($sheel->GPC['q']), '<span class="black normal">' . mb_strtolower($sheel->GPC['q']) . '</span>', $label);
						$labelformatted = '<div class="search_autocomplete_label bold">' . $labelformatted . '</div>';
						$xmlDoc .= '<item id="' . $i . '" label="' . o($labelformatted) . '" text="' . o($label) . '" searchmode="' . $availablemode[o($label)] . '"></item>';
					}
					$i++;
				}
			}
		}
		$xmlDoc .= '</root>';
		header('Content-type: application/xml; charset="' . $sheel->config['template_charset'] . '"');
		echo $xmlDoc;
		exit();
	} else if ($sheel->GPC['do'] == 'iteminfo') { // refresh item page
		if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0 and isset($sheel->GPC['type']) and !empty($sheel->GPC['type'])) {
			$sheel->GPC['invited'] = ((isset($sheel->GPC['invited'])) ? intval($sheel->GPC['invited']) : 0);
			$sheel->GPC['sku'] = ((isset($sheel->GPC['sku']) and !empty($sheel->GPC['sku'])) ? o($sheel->GPC['sku']) : '');
			$sheel->GPC['isended'] = ((isset($sheel->GPC['isended']) and $sheel->GPC['isended'] != '') ? intval($sheel->GPC['isended']) : '0');
			$payload = array(
				'itemid' => $sheel->GPC['id'],
				'sku' => $sheel->GPC['sku'],
				'type' => $sheel->GPC['type'],
				'invited' => $sheel->GPC['invited'],
				'isended' => $sheel->GPC['isended']
			);
			echo $sheel->auction_product->info($payload);
			$sheel->bid_proxy->do_proxy_bidder($sheel->GPC['id'], 0, 1);
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showstates') { // show states based on selected country
		if (isset($sheel->GPC['countryname']) and !empty($sheel->GPC['countryname']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			if ($sheel->GPC['countryname'] > 0) {
				$locationid = intval($sheel->GPC['countryname']);
			} else {
				$locationid = $sheel->common_location->fetch_country_id($sheel->GPC['countryname'], $_SESSION['sheeldata']['user']['slng']);
			}
			$shortform = isset($sheel->GPC['shortform']) ? intval($sheel->GPC['shortform']) : 0;
			$extracss = ((isset($sheel->GPC['extracss']) and !empty($sheel->GPC['extracss'])) ? $sheel->GPC['extracss'] : '');
			$disablecities = isset($sheel->GPC['disablecities']) ? intval($sheel->GPC['disablecities']) : 1;
			$citiesfieldname = isset($sheel->GPC['citiesfieldname']) ? $sheel->GPC['citiesfieldname'] : 'city';
			$citiesdivid = isset($sheel->GPC['citiesdivid']) ? $sheel->GPC['citiesdivid'] : 'cityid';
			$html = $sheel->common_location->construct_state_pulldown($locationid, '', $sheel->GPC['fieldname'], false, true, $shortform, $extracss, $disablecities, $citiesfieldname, $citiesdivid);


			$sheel->template->templateregistry['showstates'] = $html;
			echo $sheel->template->parse_template_phrases('showstates');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showcities') { // show cities based on selected state
		if (isset($sheel->GPC['state']) and !empty($sheel->GPC['state']) and isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$extracss = ((isset($sheel->GPC['extracss']) and !empty($sheel->GPC['extracss'])) ? $sheel->GPC['extracss'] : '');
			$currentcitiesclass = ((isset($sheel->GPC['currentcitiesclass'])) ? $sheel->GPC['currentcitiesclass'] : ''); // input w-350
			$currentstateclasswidth = ((isset($sheel->GPC['currentstateclasswidth'])) ? $sheel->GPC['currentstateclasswidth'] : 'w-170'); // w-350
			$html = $sheel->common_location->construct_city_pulldown($sheel->GPC['state'], $sheel->GPC['fieldname'], '', false, true, $extracss, true, $currentstateclasswidth, true, $currentcitiesclass);


			$sheel->template->templateregistry['showcities'] = $html;
			echo $sheel->template->parse_template_phrases('showcities');
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showshippers') { // show ship services based on shop to option selected
		if (isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			if (($html = $sheel->cache->fetch('showshippers_' . $sheel->GPC['fieldname'])) === false) {
				$sheel->GPC['domestic'] = isset($sheel->GPC['domestic']) ? $sheel->GPC['domestic'] : 'false';
				$sheel->GPC['international'] = isset($sheel->GPC['international']) ? $sheel->GPC['international'] : 'false';
				$sheel->GPC['shipperid'] = isset($sheel->GPC['shipperid']) ? intval($sheel->GPC['shipperid']) : 0;
				$sheel->GPC['disabled'] = isset($sheel->GPC['disabled']) ? $sheel->GPC['disabled'] : false;
				$sheel->GPC['jspackagetype'] = isset($sheel->GPC['jspackagetype']) ? intval($sheel->GPC['jspackagetype']) : 0;
				$sheel->GPC['jspackagedivcontent'] = isset($sheel->GPC['jspackagedivcontent']) ? $sheel->GPC['jspackagedivcontent'] : '';
				$sheel->GPC['jspackagefieldname'] = isset($sheel->GPC['jspackagefieldname']) ? $sheel->GPC['jspackagefieldname'] : '';
				$sheel->GPC['jspackagevalue'] = isset($sheel->GPC['jspackagevalue']) ? $sheel->GPC['jspackagevalue'] : '';
				$sheel->GPC['jspickupdivcontent'] = isset($sheel->GPC['jspickupdivcontent']) ? $sheel->GPC['jspickupdivcontent'] : '';
				$sheel->GPC['jspickupfieldname'] = isset($sheel->GPC['jspickupfieldname']) ? $sheel->GPC['jspickupfieldname'] : '';
				$sheel->GPC['jspickupvalue'] = isset($sheel->GPC['jspickupvalue']) ? $sheel->GPC['jspickupvalue'] : '';
				$sheel->GPC['ship_method'] = isset($sheel->GPC['ship_method']) ? $sheel->GPC['ship_method'] : '';
				$sheel->GPC['position'] = isset($sheel->GPC['position']) ? intval($sheel->GPC['position']) : 1;
				$html = $sheel->shipping->print_shipping_partners($sheel->GPC['fieldname'], false, $sheel->GPC['domestic'], $sheel->GPC['international'], $sheel->GPC['shipperid'], $sheel->GPC['disabled'], $sheel->GPC['jspackagetype'], $sheel->GPC['jspackagedivcontent'], $sheel->GPC['jspackagefieldname'], $sheel->GPC['jspackagevalue'], $sheel->GPC['jspickupdivcontent'], $sheel->GPC['jspickupfieldname'], $sheel->GPC['jspickupvalue'], '150', $sheel->GPC['ship_method'], $sheel->GPC['position']);
				$sheel->cache->store('showshippers', $html);
			}


			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showshippackages') { // show ship package types based on ship service
		if (isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$sheel->GPC['shipperid'] = isset($sheel->GPC['shipperid']) ? intval($sheel->GPC['shipperid']) : 0;
			$sheel->GPC['packageid'] = isset($sheel->GPC['packageid']) ? $sheel->GPC['packageid'] : '';
			$sheel->GPC['disabled'] = isset($sheel->GPC['disabled']) ? $sheel->GPC['disabled'] : false;
			$sheel->GPC['position'] = isset($sheel->GPC['position']) ? intval($sheel->GPC['position']) : 1;
			$html = $sheel->shipping->print_shipping_packages($sheel->GPC['fieldname'], $sheel->GPC['packageid'], $sheel->GPC['disabled'], $sheel->GPC['shipperid'], '350', $sheel->GPC['position']);


			$sheel->template->templateregistry['output'] = $html;
			$html = $sheel->template->parse_template_phrases('output');
			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showshippickupdropoff') { // sell item: show shipping pickup types based on ship service
		if (isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$sheel->GPC['shipperid'] = isset($sheel->GPC['shipperid']) ? intval($sheel->GPC['shipperid']) : 0;
			$sheel->GPC['pickupid'] = isset($sheel->GPC['pickupid']) ? $sheel->GPC['pickupid'] : '';
			$sheel->GPC['disabled'] = isset($sheel->GPC['disabled']) ? $sheel->GPC['disabled'] : false;
			$sheel->GPC['position'] = isset($sheel->GPC['position']) ? intval($sheel->GPC['position']) : 1;
			$html = $sheel->shipping->print_shipping_pickupdropoff($sheel->GPC['fieldname'], $sheel->GPC['pickupid'], $sheel->GPC['disabled'], $sheel->GPC['shipperid'], '350', $sheel->GPC['position']);


			$sheel->template->templateregistry['output'] = $html;
			$html = $sheel->template->parse_template_phrases('output');
			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'showduration') { // show dynamic duration pull down
		if (isset($sheel->GPC['fieldname']) and !empty($sheel->GPC['fieldname'])) {
			$sheel->GPC['unittype'] = isset($sheel->GPC['unittype']) ? $sheel->GPC['unittype'] : 'D';
			$sheel->GPC['showprices'] = isset($sheel->GPC['showprices']) ? $sheel->GPC['showprices'] : true;
			$sheel->GPC['cid'] = isset($sheel->GPC['cid']) ? $sheel->GPC['cid'] : 0;
			$sheel->GPC['bulkitemscount'] = isset($sheel->GPC['bulkitemscount']) ? $sheel->GPC['bulkitemscount'] : 1;
			$sheel->GPC['disabled'] = isset($sheel->GPC['disabled']) ? $sheel->GPC['disabled'] : false;
			$sheel->GPC['duration'] = isset($sheel->GPC['duration']) ? intval($sheel->GPC['duration']) : '';
			if ($sheel->GPC['mode'] == 'new') {
				$sheel->GPC['cmd'] = 'new-item';
			} else {
				$sheel->GPC['cmd'] = 'product-management';
			}
			$html = $sheel->auction_post->duration($sheel->GPC['duration'], $sheel->GPC['fieldname'], $sheel->GPC['disabled'], $sheel->GPC['unittype'], $sheel->GPC['showprices'], $sheel->GPC['cid'], true, $sheel->GPC['bulkitemscount']);
			$sheel->template->templateregistry['output'] = $html;
			$html = $sheel->template->parse_template_phrases('output');


			echo $html;
			exit();
		}
	} else if ($sheel->GPC['do'] == 'shipcalculator') { // show ship services based on ship to option selected
		if (isset($sheel->GPC['shipperid']) and isset($sheel->GPC['weightlbs']) and isset($sheel->GPC['country_from']) and isset($sheel->GPC['zipcode_from']) and isset($sheel->GPC['country_to']) and isset($sheel->GPC['zipcode_to'])) {
			$sheel->GPC['state_from'] = isset($sheel->GPC['state_from']) ? $sheel->GPC['state_from'] : ''; // required for fedex only
			$sheel->GPC['state_to'] = isset($sheel->GPC['state_to']) ? $sheel->GPC['state_to'] : ''; // required for fedex only
			$sheel->GPC['city_to'] = isset($sheel->GPC['city_to']) ? $sheel->GPC['city_to'] : ''; // required for fedex only
			$sheel->GPC['city_from'] = isset($sheel->GPC['city_from']) ? $sheel->GPC['city_from'] : ''; // required for fedex only
			$sheel->GPC['carrier'] = $sheel->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($sheel->GPC['shipperid']) . "'", "carrier");
			$sheel->GPC['shipcode'] = $sheel->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($sheel->GPC['shipperid']) . "'", "shipcode");
			$sheel->GPC['length'] = isset($sheel->GPC['length']) ? $sheel->GPC['length'] : 4;
			$sheel->GPC['width'] = isset($sheel->GPC['width']) ? $sheel->GPC['width'] : 4;
			$sheel->GPC['height'] = isset($sheel->GPC['height']) ? $sheel->GPC['height'] : 4;
			$sheel->GPC['weightlbs'] = isset($sheel->GPC['weightlbs']) ? intval($sheel->GPC['weightlbs']) : 1;
			$sheel->GPC['weightoz'] = isset($sheel->GPC['weightoz']) ? intval($sheel->GPC['weightoz']) : 0;
			$sheel->GPC['weight'] = $sheel->GPC['weightlbs'] . '.' . $sheel->GPC['weightoz'];
			$sheel->GPC['pickuptype'] = isset($sheel->GPC['pickuptype']) ? $sheel->GPC['pickuptype'] : $sheel->shipcalculator->pickuptypes($sheel->GPC['carrier'], true);
			$sheel->GPC['packagetype'] = isset($sheel->GPC['packagetype']) ? $sheel->GPC['packagetype'] : $sheel->shipcalculator->packagetypes($sheel->GPC['carrier'], $sheel->GPC['shipcode'], true);
			$sheel->GPC['weightunit'] = isset($sheel->GPC['weightunit']) ? $sheel->GPC['weightunit'] : $sheel->shipcalculator->weightunits($sheel->GPC['carrier'], true);
			$sheel->GPC['dimensionunit'] = isset($sheel->GPC['dimensionunit']) ? $sheel->GPC['dimensionunit'] : $sheel->shipcalculator->dimensionunits($sheel->GPC['carrier'], true);
			$sheel->GPC['sizecode'] = isset($sheel->GPC['sizecode']) ? $sheel->GPC['sizecode'] : $sheel->shipcalculator->sizeunits($sheel->GPC['carrier'], $sheel->GPC['length'], $sheel->GPC['width'], $sheel->GPC['height'], true);
			$carriers[$sheel->GPC['carrier']] = true;
			$shipinfo = array(
				'weight' => o(trim($sheel->GPC['weight'])),
				'destination_zipcode' => o($sheel->format_zipcode(trim($sheel->GPC['zipcode_to']))),
				'destination_state' => o(trim($sheel->GPC['state_to'])),
				'destination_city' => o(trim($sheel->GPC['city_to'])),
				'destination_country' => o(trim($sheel->GPC['country_to'])),
				'origin_zipcode' => o($sheel->format_zipcode(trim($sheel->GPC['zipcode_from']))),
				'origin_state' => o(trim($sheel->GPC['state_from'])),
				'origin_city' => o(trim($sheel->GPC['city_from'])),
				'origin_country' => o(trim($sheel->GPC['country_from'])),
				'carriers' => $carriers,
				'shipcode' => o(trim($sheel->GPC['shipcode'])),
				'length' => o(trim($sheel->GPC['length'])),
				'width' => o(trim($sheel->GPC['width'])),
				'height' => o(trim($sheel->GPC['height'])),
				'pickuptype' => o(trim($sheel->GPC['pickuptype'])),
				'packagingtype' => o(trim($sheel->GPC['packagetype'])),
				'weightunit' => o(trim($sheel->GPC['weightunit'])),
				'dimensionunit' => o(trim($sheel->GPC['dimensionunit'])),
				'sizecode' => o(trim($sheel->GPC['sizecode']))
			);
			$rates = $sheel->shipcalculator->get_rates($shipinfo);
			if (isset($rates['price'][0])) {
				$currencyid = (isset($rates['currency'][0]) and isset($sheel->currency->currencies[$rates['currency'][0]]['currency_id'])) ? $sheel->currency->currencies[$rates['currency'][0]]['currency_id'] : $sheel->config['globalserverlocale_defaultcurrency'];
				echo '<h1><strong>' . $sheel->currency->format($rates['price'][0], $currencyid) . '</strong></h1>';
			} else {
				if (isset($rates['errordesc'])) {
					echo '<div class="smaller red" style="max-width:415px">' . o($rates['errordesc']) . '</div>';
				} else {
					$sheel->template->templateregistry['output'] = '{_out_of_region_try_again}';
					echo $sheel->template->parse_template_phrases('output');
				}
				exit();
			}


			unset($test);
			exit();
		} else {
			die('Shipping Rates Research Calculator not available.');
		}
	} else if ($sheel->GPC['do'] == 'showshipservicerows') { // item shipping calculation (based on country and zipcode)
		if (isset($sheel->GPC['countryid']) and isset($sheel->GPC['pid']) and isset($sheel->GPC['radiuszip'])) {
			$output = '';
			$rows = 0;
			$sheel->GPC['qty'] = isset($sheel->GPC['qty']) ? intval($sheel->GPC['qty']) : 1;
			$sheel->GPC['radiuszip'] = isset($sheel->GPC['radiuszip']) ? o(trim($sheel->GPC['radiuszip'])) : '';
			$sheel->GPC['state'] = isset($sheel->GPC['state']) ? o(trim($sheel->GPC['state'])) : '';
			$sheel->GPC['city'] = isset($sheel->GPC['city']) ? o(trim($sheel->GPC['city'])) : '';
			$sheel->GPC['vqty'] = isset($sheel->GPC['vqty']) ? intval($sheel->GPC['vqty']) : 1;
			$result = $sheel->db->query("
				SELECT p.row, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS countrytitle, l.cc, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region
				FROM " . DB_PREFIX . "projects_shipping_regions p
				LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
				LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
				WHERE p.project_id = '" . intval($sheel->GPC['pid']) . "'
					AND p.countryid = '" . intval($sheel->GPC['countryid']) . "'
					AND l.visible = '1'
					AND l.visible_shipping = '1'
				ORDER BY p.row ASC
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($result) > 0) {
				while ($res = $sheel->db->fetch_array($result, DB_ASSOC)) {
					$location = $sheel->common_location->fetch_city_state_country($sheel->GPC['radiuszip'], $res['cc']);
					if (!empty($location) and is_array($location) and (empty($sheel->GPC['city']) or empty($sheel->GPC['state']))) {
						$sheel->GPC['city'] = $location['city'];
						$sheel->GPC['state'] = $location['state'];
					} else if (!empty($location) and is_array($location) and !empty($sheel->GPC['city']) and !empty($sheel->GPC['state']) and (($sheel->GPC['city'] != $location['city']) or ($sheel->GPC['state'] != $location['state'])) and $location['country'] == $res['countrytitle']) {
						$sheel->GPC['city'] = $location['city'];
						$sheel->GPC['state'] = $location['state'];
					}
					$sheel->GPC['country'] = $res['countrytitle'];
					$ship_service_row = $sheel->shipping->fetch_ajax_ship_service_row($res['row'], $sheel->GPC['pid'], $res['countrytitle'], $res['region'], $sheel->GPC['qty'], $sheel->GPC['vqty'], $sheel->GPC['radiuszip'], $sheel->GPC['state'], $sheel->GPC['city']);
					$output .= '|' . $ship_service_row;
					$rows++;
				}
			}
			if (!empty($sheel->GPC['country'])) { // check if user supplied us with a country
				set_cookie('country', o($sheel->GPC['country']));
			}
			if (!empty($sheel->GPC['state'])) { // check if user supplied us with a state
				set_cookie('state', o($sheel->GPC['state']));
			}
			if (!empty($sheel->GPC['city'])) { // check if user supplied us with a city
				set_cookie('city', o($sheel->GPC['city']));
			}
			if (!empty($sheel->GPC['radiuszip'])) { // check if user supplied us with a zip code
				set_cookie('radiuszip', o($sheel->format_zipcode($sheel->GPC['radiuszip'])));
			}
			echo $rows . $output;


			exit();
		}
	} else if ($sheel->GPC['do'] == 'calculate') {
		$mode = isset($sheel->GPC['mode']) ? $sheel->GPC['mode'] : 'fvf';
		$groupid = isset($sheel->GPC['groupid']) ? intval($sheel->GPC['groupid']) : 0;
		$cid = isset($sheel->GPC['cid']) ? intval($sheel->GPC['cid']) : 0;
		$fee = isset($sheel->GPC['fee']) ? $sheel->GPC['fee'] : 0;
		$noformat = isset($sheel->GPC['noformat']) ? true : false;
		$iscrypto = ((isset($sheel->GPC['iscrypto']) and $sheel->GPC['iscrypto']) ? true : false);
		if ($mode == 'fvf') {
			$result = $sheel->accounting_fees->calculate_final_value_fee($fee, $cid, 0, $groupid, $iscrypto);
		} else if ($mode == 'if') {
			$result = $sheel->accounting_fees->calculate_insertion_fee($cid, $fee, 0, 0, false, $groupid, $iscrypto);
		} else if ($mode == 'bp') {
			$result = $sheel->accounting_fees->calculate_buyer_premium_fee($fee, $cid, 0, $groupid, $iscrypto);
		}
		if ($noformat) {
			echo $result;
			exit();
		}
		echo $sheel->currency->format($result);
		exit();
	} else if ($sheel->GPC['do'] == 'calculateinsertionfees') { // calculate insertion and fv fees
		$cid = intval($sheel->GPC['cid']);
		$htmlinsertionfees = '';
		$currencyid = isset($sheel->GPC['currencyid']) ? $sheel->GPC['currencyid'] : $sheel->config['globalserverlocale_defaultcurrency'];
		$iscrypto = ((isset($sheel->currency->currencies[$currencyid]['iscrypto']) and $sheel->currency->currencies[$currencyid]['iscrypto']) ? true : false);
		$startprice = isset($sheel->GPC['startprice']) ? $sheel->GPC['startprice'] : 0.00;
		$reserve_price = isset($sheel->GPC['reserve_price']) ? $sheel->GPC['reserve_price'] : 0.00;
		$buynow_price = isset($sheel->GPC['buynow_price']) ? $sheel->GPC['buynow_price'] : 0.00;
		$classified_price = isset($sheel->GPC['classified_price']) ? $sheel->GPC['classified_price'] : 0.00;
		$price = $startprice;
		$plainfee = 0;
		if ($classified_price > 0) {
			$price = $classified_price;
		} else if ($reserve_price > $startprice) {
			$price = $reserve_price;
			if ($buynow_price > $reserve_price) {
				$price = $buynow_price;
			}
		} else if ($buynow_price > $startprice) {
			$price = $buynow_price;
		}
		$price = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcurrency'], $price, $currencyid);
		$sql = $sheel->db->query("
			SELECT insertiongroup, finalvaluegroup, cattype
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $sheel->db->fetch_array($sql, DB_ASSOC);
		$sql_fees = $sheel->db->query("
			SELECT insertionid
			FROM " . DB_PREFIX . "insertion_fees
			WHERE groupname = '" . $res['insertiongroup'] . "'
		", 0, null, __FILE__, __LINE__);
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $sheel->permissions->check_access($_SESSION['sheeldata']['user']['userid'], 'insexempt') == 'yes') {
			$htmlinsertionfees = '<div class="pb-6"><span class="' . (($sheel->config['template_textdirection'] == 'ltr') ? 'right' : 'left') . ' mlr-6">{_exempt}</span>{_insertion_fee}</div>';
		} else {
			if ($res['insertiongroup'] != '' and $res['insertiongroup'] != '0' and $sheel->db->num_rows($sql_fees) > 0 and !empty($_SESSION['sheeldata']['user']['userid'])) {
				$ifgroupname = $res['insertiongroup'];
				$forceifgroupid = $sheel->permissions->check_access($_SESSION['sheeldata']['user']['userid'], "{$res['cattype']}insgroup");
				if ($forceifgroupid > 0) {
					$ifgroupname = $sheel->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
				}
				$sqlinsertions = $sheel->db->query("
					SELECT amount
					FROM " . DB_PREFIX . "insertion_fees
					WHERE groupname = '" . $sheel->db->escape_string($ifgroupname) . "'
						AND state = '" . $sheel->db->escape_string($res['cattype']) . "'
						AND (insertion_from < '" . $sheel->db->escape_string($price) . "' OR insertion_from = '" . $sheel->db->escape_string($price) . "')
						AND (insertion_to > '" . $sheel->db->escape_string($price) . "' OR insertion_to = '" . $sheel->db->escape_string($price) . "' OR insertion_to = '-1')
					ORDER BY sort ASC
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sqlinsertions) > 0) {
					$res_ins = $sheel->db->fetch_array($sqlinsertions, DB_ASSOC);
					$fee = $sheel->currency->format($res_ins['amount']);
					$sheel->show['insertionfeeamount'] = $res_ins['amount'];
					$plainfee = $res_ins['amount'];
					$htmlinsertionfees .= '<div class="pb-6"><span class="' . (($sheel->config['template_textdirection'] == 'ltr') ? 'right' : 'left') . ' mlr-6">' . $fee . '</span>{_insertion_fee}</div>';
				}
			} else {
				$sheel->show['insertionfees'] = $sheel->show['insertionfeeamount'] = 0;
				$htmlinsertionfees .= '<div class="pb-6"><span class="' . (($sheel->config['template_textdirection'] == 'ltr') ? 'right' : 'left') . ' mlr-6">{_none}</span>{_insertion_fee}</div>';
			}
		}
		$listingfees = $htmlinsertionfees;
		$sheel->template->templateregistry['listingfees'] = $listingfees;
		echo $sheel->template->parse_template_phrases('listingfees') . '|' . $plainfee;
		exit();
	} else if ($sheel->GPC['do'] == 'search_category_keyword') { // search categories (client)
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$var = isset($sheel->GPC['var']) ? $sheel->db->escape_string($sheel->GPC['var']) : '';
		$results = '';
		if (!empty($var) and strlen($var) > 2 and $var != 'x') {
			$sql = $sheel->db->query("
				SELECT cid
				FROM " . DB_PREFIX . "categories
				WHERE (title_$slng LIKE '%$var%' OR keywords_$slng LIKE '%$var%' OR description_$slng LIKE '%$var%')
					AND cattype = 'product'
					AND canpost = '1'
				LIMIT 20
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$results .= '<div class="pb-6 fs-13 lh-14"><a href="' . HTTPS_SERVER . 'selling/new-item/?cid=' . $res['cid'] . '&ckw=' . $var . '">' . strip_tags($sheel->categories->recursive($res['cid'], 'product', $slng, 1)) . '</a> <span class="litegray">(#' . $res['cid'] . ')</span></div>';
				}
			}
		}
		die($results);
	} else if ($sheel->GPC['do'] == 'search_brand_keyword') { // search categories (client)
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$var = isset($sheel->GPC['var']) ? $sheel->db->escape_string($sheel->GPC['var']) : '';
		$results = '';
		if (!empty($var) and strlen($var) > 2 and $var != 'x') {
			$sql = $sheel->db->query("
				SELECT BSIN, BRAND_NM
				FROM " . DB_PREFIX . "brand
				WHERE (BRAND_NM LIKE '%$var%')
					AND VISIBLE = '1'
				LIMIT 20
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$results .= '<div class="pb-6 fs-13 lh-14">' . strip_tags($res['BRAND_NM']) . ' <span class="litegray">(' . $res['BSIN'] . ')</span></div>';
				}
			}
		}
		die($results);
	} else if ($sheel->GPC['do'] == 'heropicture') { // admin hero picture info
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
			$filename = ((isset($sheel->GPC['filename'])) ? $sheel->GPC['filename'] : '');
			$mode = ((isset($sheel->GPC['mode'])) ? o($sheel->GPC['mode']) : 'homepage');
			$folder = ((isset($sheel->GPC['folder'])) ? o($sheel->GPC['folder']) : 'heros');
			$cid = ((isset($sheel->GPC['cid'])) ? intval($sheel->GPC['cid']) : 0);
			$id = ((isset($sheel->GPC['id'])) ? intval($sheel->GPC['id']) : 0);
			if (!empty($filename)) {
				if ($mode == 'load' and $id > 0) {
					$sql = $sheel->db->query("
						SELECT imagemap, mode, filename, sort, cid, width, height, styleid
						FROM " . DB_PREFIX . "hero
						WHERE filename = '" . $sheel->db->escape_string($filename) . "'
							AND id = '" . intval($id) . "'
							AND cid = '" . intval($cid) . "'
						LIMIT 1
					");
					if ($sheel->db->num_rows($sql) > 0) {
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$themeselect = '<div class="draw-select__wrapper draw-input--has-content">' . $sheel->styles->print_styles_pulldown($res['styleid'], '', 'src_styleid', 'draw-select') . '</div>';
						$pulldown = '<div class="draw-select__wrapper draw-input--has-content"><select name="src_location" id="src_location" class="draw-select" onchange="((jQuery(\'#src_location option:selected\').attr(\'type\').length > 0) ? jQuery(\'#src_mode\').val(jQuery(\'#src_location option:selected\').attr(\'type\')) : jQuery(\'#src_mode\').val(\'\'))"><optgroup label="{_location}"><option value="homepage" id="0" cid="0"' . (($res['mode'] == 'homepage') ? ' selected="selected"' : '') . ' type="homepage">{_homepage}</option><option value="landingpage" id="0" cid="0"' . (($res['mode'] == 'landingpage') ? ' selected="selected"' : '') . ' type="landingpage">Landing Page</option><option value="storeshomepage" id="0" cid="0"' . (($res['mode'] == 'storeshomepage') ? ' selected="selected"' : '') . ' type="storeshomepage">Stores Homepage</option><option value="auctionevents" id="0" cid="0"' . (($res['mode'] == 'auctionevents') ? ' selected="selected"' : '') . ' type="auctionevents">Auction Events Homepage</option><option value="nonprofits" id="0" cid="0"' . (($res['mode'] == 'nonprofits') ? ' selected="selected"' : '') . ' type="nonprofits">Nonprofits Homepage</option></optgroup>';
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options($cid, 'categoryflyout', 'Shop By Category Flyouts', $res['mode']);
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options($cid, 'categorymap', 'Marketplace Category', $res['mode']);
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options($cid, 'storescategorymap', 'Stores Category', $res['mode']);
						//$pulldown .= $sheel->admincp->fetch_parent_hero_category_options($cid, 'auctioneventscategorymap', 'Auction Events Category', $res['mode']);
						$pulldown .= '</select><input type="hidden" name="src_mode" id="src_mode" value="' . $res['mode'] . '" /></div>';
						$sheel->template->templateregistry['results'] = "$res[sort]|$res[imagemap]|$res[cid]|$pulldown|$res[width]|$res[height]|$themeselect";
						die($sheel->template->parse_template_phrases('results'));
					}
				} else if ($mode == 'insert') {
					$sql = $sheel->db->query("
						SELECT imagemap, mode, filename, sort, cid
						FROM " . DB_PREFIX . "hero
						ORDER BY sort DESC
						LIMIT 1
					");
					if ($sheel->db->num_rows($sql) > 0) {
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$themeselect = '<div class="draw-select__wrapper draw-input--has-content">' . $sheel->styles->print_styles_pulldown($sheel->config['defaultstyle'], '', 'src_styleid', 'draw-select') . '</div>';
						$pulldown = '<div class="draw-select__wrapper draw-input--has-content"><select name="src_location" id="src_location" class="draw-select" onchange="((jQuery(\'#src_location option:selected\').attr(\'type\').length > 0) ? jQuery(\'#src_mode\').val(jQuery(\'#src_location option:selected\').attr(\'type\')) : jQuery(\'#src_mode\').val(\'\'))"><option value="">{_select_a_location}</option><optgroup label="{_location}"><option value="homepage" id="0" type="homepage">{_homepage}</option><option value="landingpage" id="0" type="landingpage">Landing Page</option><option value="storeshomepage" id="0" type="storeshomepage">Stores Homepage</option><option value="auctionevents" id="0" type="auctionevents">Auction Events Homepage</option><option value="nonprofits" id="0" cid="0" type="nonprofits">Nonprofits Homepage</option></optgroup>';
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options(0, 'categoryflyout', 'Shop By Category Flyouts');
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options(0, 'categorymap', 'Marketplace Category');
						$pulldown .= $sheel->admincp->fetch_parent_hero_category_options(0, 'storescategorymap', 'Stores Category');
						$pulldown .= '</select><input type="hidden" name="src_mode" id="src_mode" /></div>';
						$width = $height = '';
						$targetpath = DIR_ATTACHMENTS . $folder . '/' . $filename;
						if (file_exists($targetpath)) {
							list($width, $height, $type, $attr) = getimagesize($targetpath);
						}
						$sheel->template->templateregistry['results'] = "$res[sort]|$res[imagemap]|$res[cid]|$pulldown|$width|$height|$themeselect";
						echo $sheel->template->parse_template_phrases('results');
						exit();
					} else {
						echo "10|";
						exit();
					}
				}
			}
		}
		echo '';
		exit();
	} else if ($sheel->GPC['do'] == 'fileuploader') { // file uploader
		if (isset($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['userid'])) {
			if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'remove' and isset($sheel->GPC['aid']) and $sheel->GPC['aid'] > 0) {
				$sheel->fileuploaderhandler->delete();
			} else {
				$sheel->fileuploaderhandler->attachtype = ((isset($sheel->GPC['attachtype']) and in_array($sheel->GPC['attachtype'], array('itemphoto'))) ? $sheel->GPC['attachtype'] : 'itemphoto');
				$sheel->fileuploaderhandler->init(); // also loads existing photos for update item mode
			}
		}
		exit();
	} else if ($sheel->GPC['do'] == 'fileuploaderform-sheel') { // form
		if (isset($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['userid'])) {
			$_SESSION['sheeldata']['tmp']['newitemid'] = ((isset($_SESSION['sheeldata']['tmp']['newitemid']) and !empty($_SESSION['sheeldata']['tmp']['newitemid'])) ? $_SESSION['sheeldata']['tmp']['newitemid'] : 0);
			$sheel->GPC['pid'] = ((isset($sheel->GPC['pid'])) ? $sheel->GPC['pid'] : 0);
			$pid = ($sheel->GPC['pid'] == '1') ? $_SESSION['sheeldata']['tmp']['newitemid'] : $sheel->GPC['pid'];
			$attachtype = isset($sheel->GPC['attachtype']) ? $sheel->GPC['attachtype'] : 'itemphoto';
			$maximum_files = $freefiles = $attach_usage_total = $attach_user_max = $max_size = $attach_usage_left = $max_width = $max_height = $extensions = '-';
			$slideshowcost = '';
			$accepted_attachtypes = array('itemphoto');


			if (in_array($attachtype, $accepted_attachtypes)) {
				$res_file_sum['attach_usage_total'] = 0;
				$sql_file_sum = $sheel->db->query("
					SELECT SUM(filesize) AS attach_usage_total
					FROM " . DB_PREFIX . "attachment
					WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				");
				if ($sheel->db->num_rows($sql_file_sum) > 0) {
					$res_file_sum = $sheel->db->fetch_array($sql_file_sum, DB_ASSOC);
					$attach_usage_total = $sheel->attachment->print_filesize($res_file_sum['attach_usage_total']);
				}
				$attach_user_max = $sheel->permissions->check_access($_SESSION['sheeldata']['user']['userid'], 'attachlimit');
				$attach_usage_left = ($attach_user_max - $res_file_sum['attach_usage_total']);
				$attach_usage_left = ($attach_usage_left <= 0) ? $sheel->attachment->print_filesize(0) : $sheel->attachment->print_filesize($attach_usage_left);
				$condition = $sheel->attachment->handle_attachtype_rebuild_settings($attachtype, $_SESSION['sheeldata']['user']['userid'], $pid, 0, '', '');
				$attach_user_max = $sheel->attachment->print_filesize($attach_user_max);
				$maximum_files = $condition['maximum_files'];
				$max_width = $condition['max_width'];
				$max_height = $condition['max_height'];
				$min_width = $condition['min_width'];
				$min_height = $condition['min_width'];
				$max_filesize = $condition['max_filesize'];
				$max_size = $condition['max_size'];
				$extensions = $condition['extensions'];
				if ($attachtype == 'itemphoto') {
					$slideshowcost = ($sheel->config['productupsell_slideshowcost'] > 0) ? strip_tags($sheel->currency->format($sheel->config['productupsell_slideshowcost'])) : '{_free_lower}';
				}
				$sheel->template->templateregistry['slideshowcost'] = $slideshowcost;
				$slideshowcost = $sheel->template->parse_template_phrases('slideshowcost');
			}
			if ($sheel->config['attachmentlimit_slideshowfreelimit'] <= 0) {
				$limits = '{_you_can_upload_x_pictures::' . $maximum_files . '::' . $slideshowcost . '}';
			} else {
				if ($sheel->config['attachmentlimit_slideshowfreelimit'] == $maximum_files) {
					$limits = '{_you_can_upload_x_free_pictures::' . $sheel->config['attachmentlimit_slideshowfreelimit'] . '::' . $maximum_files . '}';
				} else {
					$limits = '{_you_can_upload_x_free_pictures_each_additional::' . $sheel->config['attachmentlimit_slideshowfreelimit'] . '::' . $maximum_files . '::' . $slideshowcost . '}';
				}
			}
			$sheel->template->templateregistry['limits'] = $limits;
			$newlimits = $sheel->template->parse_template_phrases('limits');
			unset($limits);
			$sheel->template->load_popup('fileuploader', 'ajax_pictureupload-sheel.html');
			$sheel->template->init_js_phrase_array('fileuploader');
			$vars = array(
				'pid' => $pid,
				'min' => (($sheel->config['globalfilters_jsminify'] and $sheel->config['globalfilters_jsminify']) ? '.min' : ''),
				'attachtype' => $attachtype,
				'csspath' => $sheel->config['imgrel'] . DIR_ASSETS_NAME . '/' . DIR_CSS_NAME . '/' . $_SESSION['sheeldata']['user']['styleid'] . '/vendor/fileuploader/',
				'phrases' => HTTP_TMP_JS . $sheel->template->js_phrases_file,
				'maximum_files' => $maximum_files,
				'freefiles' => $sheel->config['attachmentlimit_slideshowfreelimit'],
				'free_files' => $sheel->config['attachmentlimit_slideshowfreelimit'],
				'costperupload' => $sheel->config['productupsell_slideshowcost'],
				'newlimits' => $newlimits,
				'attach_usage_total' => $attach_usage_total,
				'attach_user_max' => $attach_user_max,
				'max_size' => $max_size,
				'attach_usage_left' => $attach_usage_left,
				'max_width' => $max_width,
				'max_height' => $max_height,
				'min_width' => $min_width,
				'min_height' => $min_height,
				'extensions' => $extensions,
				'slideshowcost' => $slideshowcost,
				'img' => $sheel->config['img'],
				'imgcdn' => $sheel->config['imgcdn'],
				'jscdn' => $sheel->config['jscdn'],
				'http_server' => HTTPS_SERVER,
				'https_server' => HTTPS_SERVER
			);
			$sheel->template->parse_hash('fileuploader', array('vars' => $vars, 'ilpage' => $sheel->ilpage));
			echo $sheel->template->parse_template_phrases('fileuploader');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'fileuploaderform') { // form
		if (isset($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['userid'])) {
			$_SESSION['sheeldata']['tmp']['newitemid'] = ((isset($_SESSION['sheeldata']['tmp']['newitemid']) and !empty($_SESSION['sheeldata']['tmp']['newitemid'])) ? $_SESSION['sheeldata']['tmp']['newitemid'] : 0);
			$sheel->GPC['pid'] = ((isset($sheel->GPC['pid'])) ? $sheel->GPC['pid'] : 0);
			$pid = ($sheel->GPC['pid'] == '1') ? $_SESSION['sheeldata']['tmp']['newitemid'] : $sheel->GPC['pid'];
			$attachtype = isset($sheel->GPC['attachtype']) ? $sheel->GPC['attachtype'] : 'itemphoto';
			$maximum_files = $freefiles = $attach_usage_total = $attach_user_max = $max_size = $attach_usage_left = $max_width = $max_height = $extensions = '-';
			$slideshowcost = '';
			$accepted_attachtypes = array('itemphoto');


			if (in_array($attachtype, $accepted_attachtypes)) {
				$res_file_sum['attach_usage_total'] = 0;
				$sql_file_sum = $sheel->db->query("
					SELECT SUM(filesize) AS attach_usage_total
					FROM " . DB_PREFIX . "attachment
					WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				");
				if ($sheel->db->num_rows($sql_file_sum) > 0) {
					$res_file_sum = $sheel->db->fetch_array($sql_file_sum, DB_ASSOC);
					$attach_usage_total = $sheel->attachment->print_filesize($res_file_sum['attach_usage_total']);
				}
				$attach_user_max = $sheel->permissions->check_access($_SESSION['sheeldata']['user']['userid'], 'attachlimit');
				$attach_usage_left = ($attach_user_max - $res_file_sum['attach_usage_total']);
				$attach_usage_left = ($attach_usage_left <= 0) ? $sheel->attachment->print_filesize(0) : $sheel->attachment->print_filesize($attach_usage_left);
				$condition = $sheel->attachment->handle_attachtype_rebuild_settings($attachtype, $_SESSION['sheeldata']['user']['userid'], $pid, 0, '', '');
				$attach_user_max = $sheel->attachment->print_filesize($attach_user_max);
				$maximum_files = $condition['maximum_files'];
				$max_width = $condition['max_width'];
				$max_height = $condition['max_height'];
				$min_width = $condition['min_width'];
				$min_height = $condition['min_width'];
				$max_filesize = $condition['max_filesize'];
				$max_size = $condition['max_size'];
				$extensions = $condition['extensions'];
				if ($attachtype == 'itemphoto') {
					$slideshowcost = ($sheel->config['productupsell_slideshowcost'] > 0) ? strip_tags($sheel->currency->format($sheel->config['productupsell_slideshowcost'])) : '{_free_lower}';
				}
				$sheel->template->templateregistry['slideshowcost'] = $slideshowcost;
				$slideshowcost = $sheel->template->parse_template_phrases('slideshowcost');
			}
			if ($sheel->config['attachmentlimit_slideshowfreelimit'] <= 0) {
				$limits = '{_you_can_upload_x_pictures::' . $maximum_files . '::' . $slideshowcost . '}';
			} else {
				if ($sheel->config['attachmentlimit_slideshowfreelimit'] == $maximum_files) {
					$limits = '{_you_can_upload_x_free_pictures::' . $sheel->config['attachmentlimit_slideshowfreelimit'] . '::' . $maximum_files . '}';
				} else {
					$limits = '{_you_can_upload_x_free_pictures_each_additional::' . $sheel->config['attachmentlimit_slideshowfreelimit'] . '::' . $maximum_files . '::' . $slideshowcost . '}';
				}
			}
			$sheel->template->templateregistry['limits'] = $limits;
			$newlimits = $sheel->template->parse_template_phrases('limits');
			unset($limits);
			$sheel->template->load_popup('fileuploader', 'ajax_pictureupload.html');
			$sheel->template->init_js_phrase_array('fileuploader');
			$vars = array(
				'pid' => $pid,
				'min' => (($sheel->config['globalfilters_jsminify'] and $sheel->config['globalfilters_jsminify']) ? '.min' : ''),
				'attachtype' => $attachtype,
				'csspath' => $sheel->config['imgrel'] . DIR_ASSETS_NAME . '/' . DIR_CSS_NAME . '/' . $_SESSION['sheeldata']['user']['styleid'] . '/vendor/fileuploader/',
				'phrases' => HTTP_TMP_JS . $sheel->template->js_phrases_file,
				'maximum_files' => $maximum_files,
				'freefiles' => $sheel->config['attachmentlimit_slideshowfreelimit'],
				'free_files' => $sheel->config['attachmentlimit_slideshowfreelimit'],
				'costperupload' => $sheel->config['productupsell_slideshowcost'],
				'newlimits' => $newlimits,
				'attach_usage_total' => $attach_usage_total,
				'attach_user_max' => $attach_user_max,
				'max_size' => $max_size,
				'attach_usage_left' => $attach_usage_left,
				'max_width' => $max_width,
				'max_height' => $max_height,
				'min_width' => $min_width,
				'min_height' => $min_height,
				'extensions' => $extensions,
				'slideshowcost' => $slideshowcost,
				'img' => $sheel->config['img'],
				'imgcdn' => $sheel->config['imgcdn'],
				'jscdn' => $sheel->config['jscdn'],
				'http_server' => HTTPS_SERVER,
				'https_server' => HTTPS_SERVER
			);
			$sheel->template->parse_hash('fileuploader', array('vars' => $vars, 'ilpage' => $sheel->ilpage));
			echo $sheel->template->parse_template_phrases('fileuploader');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'fileuploadreorder') { // file upload picture reordering
		if (isset($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['userid'])) {
			$sheel->GPC['image_li'] = ((isset($sheel->GPC['image_li'])) ? $sheel->GPC['image_li'] : array());
			if (is_array($sheel->GPC['image_li']) and count($sheel->GPC['image_li']) > 0) {
				foreach ($sheel->GPC['image_li'] as $key => $value) {
					if ($value != '') {
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET user_sort = '" . intval($key + 1) . "'
							WHERE filehash = '" . $sheel->db->escape_string($value) . "'
								AND user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						");
					}
				}
			}
		}
	} else if ($sheel->GPC['do'] == 'fileuploadviaurl') { // file upload picture rotator + resizer
		if (isset($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['userid'])) {
			if (isset($sheel->GPC['url'])) {
				$url = urldecode($sheel->GPC['url']);
				$file = file_get_contents($url);
				if (imagecreatefromstring($file)) {
					$size = getimagesizefromstring($file);
					$type = $size['mime'];
					$width = $size[0];
					$height = $size[1];
					$data = "data:" . $type . ";base64," . base64_encode($file);
					$extension = str_replace('/', '.', mb_strrchr($url, '.')); // .jpg?t=1531154223
					$filename = substr(str_replace('/', '.', mb_strrchr($url, '/')), 1); // 1024x768.jpg?t=1531154223
					if (mb_strrchr($extension, '?')) {
						$tmp = explode('?', $extension);
						$extension = trim($tmp[0]);
						unset($tmp);
					}
					if (mb_strrchr($filename, '?')) {
						$tmp = explode('?', $filename);
						$filename = trim($tmp[0]);
						unset($tmp);
					}
					$return = array(
						'width' => $width,
						'height' => $height,
						'mime' => $type,
						'filename' => $filename,
						// image1.jpg
						'extension' => $extension,
						// .jpg
						'data' => $data
					);
					$return_val = json_encode($return);
					if (isset($sheel->GPC['callback'])) {
						$return_val = $sheel->GPC['callback'] . '(' . $return_val . ');';
						header('Cache-Control: no-cache, must-revalidate');
						header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
						header('Content-type: application/json');
						die($return_val);
					} else {
						header('HTTP/1.0 400 Bad Request');
						die('No callback specified');
					}
				} else {
					header('HTTP/1.0 400 Bad Request');
					die('Invalid image or url specified');
				}
			} else {
				header('HTTP/1.0 400 Bad Request');
				die('No URL was specified');
			}
		}
	} else if ($sheel->GPC['do'] == 'recentlyvieweditems') {
		$sheel->show['type'] = isset($sheel->GPC['type']) ? $sheel->GPC['type'] : 'load';
		$sheel->show['norecentitems'] = false;
		$returnurl = isset($sheel->GPC['returnurl']) ? urlencode($sheel->GPC['returnurl']) : urlencode(HTTPS_SERVER);
		$columns = isset($sheel->GPC['columns']) ? intval($sheel->GPC['columns']) : 3;
		$recentlyviewedtopitems = array();
		if (($recentlyviewedtopitems = $sheel->cache->fetch('recentreviewedproductauctions_col_' . $columns)) === false) {
			$recentlyviewedtopitems = $sheel->auction_listing->fetch_recently_viewed_auctions('product', 12, 1, 0, '', '108x108');
			$sheel->cache->store('recentreviewedproductauctions_col_' . $columns, $recentlyviewedtopitems);
		}
		if (count($recentlyviewedtopitems) <= 0) {
			$sheel->show['norecentitems'] = true;
		}
		$sheel->template->load_popup('main', 'inline_recentlyvieweditems_' . $columns . 'col.html');
		$sheel->template->parse_loop('main', array('recentlyviewedtopitems' => $recentlyviewedtopitems));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', array('returnurl' => $returnurl));
		exit();
	} else if ($sheel->GPC['do'] == 'rvfooter') {
		$sheel->show['limit'] = isset($sheel->GPC['limit']) ? intval($sheel->GPC['limit']) : 25;
		$sheel->show['norecentitems'] = false;
		$returnurl = isset($sheel->GPC['returnurl']) ? urlencode($sheel->GPC['returnurl']) : urlencode(HTTPS_SERVER);
		$recentlyviewedfooter = array();
		if (isset($_COOKIE[COOKIE_PREFIX . 'history']) and $_COOKIE[COOKIE_PREFIX . 'history'] == '1') {
			if (($recentlyviewedfooter = $sheel->cache->fetch('recentreviewedfooter')) === false) {
				$recentlyviewedfooter = $sheel->auction_listing->fetch_recently_viewed_auctions('product', $sheel->show['limit'], 1, 0, '', '50x50');
				$sheel->cache->store('recentreviewedfooter', $recentlyviewedfooter);
			}
		}
		if (count($recentlyviewedfooter) <= 0) {
			$sheel->show['norecentitems'] = true;
		}
		$sheel->template->load_popup('main', 'ajax_recentlyviewedfooter.html');
		$sheel->template->parse_loop('main', array('recentlyviewedfooter' => $recentlyviewedfooter));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', array('returnurl' => $returnurl));
		exit();
	} else if ($sheel->GPC['do'] == 'favourites') {
		$html1 = $html2 = $html3 = '';
		$sheel->GPC['limit'] = isset($sheel->GPC['limit']) ? intval($sheel->GPC['limit']) : 10;
		$sheel->show['noresults'] = true;
		$favouriteitems = array();
		if (($favouriteitems = $sheel->cache->fetch('favouriteitems')) === false) {
			$favouriteitems = $sheel->watchlist->fetch_watching_items($sheel->GPC['limit']);
			$sheel->cache->store('favouriteitems', $favouriteitems);
		}
		if (is_array($favouriteitems) and count($favouriteitems) > 0) {
			$sheel->show['noresults'] = false;
		}
		$sheel->template->load_popup('main', 'inline_favouriteitems.html');
		$sheel->template->parse_loop('main', array('favouriteitems' => $favouriteitems));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html1 = $sheel->template->pprint('main', array(), false);
		$sheel->show['noresults'] = true;
		$favouritesellers = array();
		if (($favouritesellers = $sheel->cache->fetch('favouritesellers')) === false) {
			$favouritesellers = $sheel->watchlist->fetch_watching_sellers($sheel->GPC['limit']);
			$sheel->cache->store('favouritesellers', $favouritesellers);
		}
		if (is_array($favouritesellers)) {
			$sheel->show['noresults'] = false;
		}
		$sheel->template->load_popup('main', 'inline_favouritesellers.html');
		$sheel->template->parse_loop('main', array('favouritesellers' => $favouritesellers));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html2 = $sheel->template->pprint('main', array(), false);
		$sheel->show['noresults'] = true;
		$favouritesearches = array();
		if (($favouritesearches = $sheel->cache->fetch('favouritesearches')) === false) {
			$favouritesearches = $sheel->watchlist->fetch_favourite_searches($sheel->GPC['limit'], true);
			$sheel->cache->store('favouritesearches', $favouritesearches);
		}
		if (is_array($favouritesearches)) {
			$sheel->show['noresults'] = false;
		}
		$sheel->template->load_popup('main', 'inline_favouritesearches.html');
		$sheel->template->parse_loop('main', array('favouritesearches' => $favouritesearches));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html3 = $sheel->template->pprint('main', array(), false);
		$sheel->show['noresults'] = true;
		$favouriteauctionevents = array();
		if (($favouriteauctionevents = $sheel->cache->fetch('favouriteauctionevents')) === false) {
			$favouriteauctionevents = $sheel->watchlist->fetch_watching_events($sheel->GPC['limit']);
			$sheel->cache->store('favouriteauctionevents', $favouriteauctionevents);
		}
		if (is_array($favouriteauctionevents) and count($favouriteauctionevents) > 0) {
			$sheel->show['noresults'] = false;
		}
		$sheel->template->load_popup('main', 'inline_auctionevents.html');
		$sheel->template->parse_loop('main', array('favouriteauctionevents' => $favouriteauctionevents));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html4 = $sheel->template->pprint('main', array(), false);
		echo json_encode(array('favouriteitems' => $html1, 'favouritesellers' => $html2, 'favouritesearches' => $html3, 'favouriteauctionevents' => $html4));
		unset($html1, $html2, $html3, $html4);
		exit();
	} else if ($sheel->GPC['do'] == 'requestdetails') {
		$html2 = '';
		$sheel->GPC['rid'] = isset($sheel->GPC['rid']) ? intval($sheel->GPC['rid']) : 0;
		$rid = $sheel->GPC['rid'];
		$sheel->show['noresults'] = true;
		if (isset($sheel->GPC['dview'])) {
			$estimated_revenue = $sheel->requests->getProjectedEarnings($sheel->GPC['rid']);
			$reqsql = $sheel->db->query("
			SELECT r.request_id,r.project_id, r.cid, r.vehicle_registration, r.vehicle_c_number, r.vehicle_make, r.vehicle_model, r.vehicle_year, r.date_added, r.vehicle_value, r.condition, r.status, r.total_parts, r.used_for, r.used_for_unit, r.customer_id, s.storename, s.seourl
			FROM " . DB_PREFIX . "requests r
			LEFT JOIN " . DB_PREFIX . "users u ON (r.customer_id = u.user_id)
			LEFT JOIN " . DB_PREFIX . "subscription_user su ON (r.customer_id = su.user_id)
			LEFT JOIN " . DB_PREFIX . "stores s ON (r.storeid = s.storeid)
			WHERE r.request_id = '" . intval($sheel->GPC['rid']) . "'
				AND u.status = 'active'
				AND su.active = 'yes'
				", 0, null, __FILE__, __LINE__);
			$requests = array();
			$rpictures = array();
			if ($sheel->db->num_rows($reqsql) > 0) {
				$sheel->show['noresults'] = false;
				while ($res = $sheel->db->fetch_array($reqsql, DB_ASSOC)) {
					$res['rid'] = $res['request_id'];
					$res['url'] = HTTPS_SERVER . 'startselling/' . $res['request_id'] . '/';
					$res['title'] = o($res['vehicle_make'] . '-' . $res['vehicle_model']);
					$res['date'] = $sheel->common->print_date($res['date_added'], 'M j, Y @ g:i A T');
					$res['month'] = $sheel->common->print_date($res['date_added'], 'M', 0, 0); // 'Jul';
					$res['day'] = $sheel->common->print_date($res['date_added'], 'j', 0, 0); // '10';
					$res['time'] = $sheel->common->print_date($res['date_added'], 'g:i A', 0, 0); // 12:00 PM';
					$res['tz'] = $sheel->common->print_date($res['date_added'], 'T', 0, 0); // 'EST';
					$res['seller'] = $sheel->fetch_user('first_name', $res['customer_id']) . ' ' . $sheel->fetch_user('last_name', $res['customer_id']);
					$res['location'] = $res['storename'];
					$res['usage'] = o($res['used_for'] . '-' . $res['used_for_unit']);
					$res['storeurl'] = $res['seourl'];
					$stars = 'wp-00';
					if ($res['condition'] == 'bad') {
						$stars = "wp-20";
					} else if ($res['condition'] == 'fair') {
						$stars = "wp-40";
					} else if ($res['condition'] == 'good') {
						$stars = "wp-60";
					} else if ($res['condition'] == 'verygood') {
						$stars = "wp-80";
					} else if ($res['condition'] == 'excellent') {
						$stars = "wp-100";
					}

					$res['stars'] = $stars;

					$sql1 = $sheel->db->query("
										SELECT a.filehash, r.vehicle_make, r.cid
										FROM " . DB_PREFIX . "attachment a
										LEFT JOIN " . DB_PREFIX . "requests r ON (a.project_id = r.project_id)
										WHERE a.attachtype = 'itemphoto'
											AND r.project_id = '" . $res['project_id'] . "'
										ORDER BY RAND()
										LIMIT 6
								");

					$res['numberofpictures'] = $sheel->db->num_rows($sql1);
					if ($sheel->db->num_rows($sql1) > 0) {

						while ($res1 = $sheel->db->fetch_array($sql1, DB_ASSOC)) {

							$rpicturessrc .= '<img src="' . HTTP_ATTACHMENTS . 'auctions/' . $res1['filehash'] . '/150x150.jpg" alt="instagram">';
							$res1['picture1'] = $rpicturessrc;
							$rpictures[] = $res1;
						}
					}

					$requests[] = $res;
				}


				if ($sheel->GPC['dview'] == 'accepted') {

					$soldquery = $sheel->db->query("
                    SELECT b.orderid
					FROM " . DB_PREFIX . "buynow_orders b
					LEFT JOIN " . DB_PREFIX . "projects p ON (b.project_id = p.project_id)
					LEFT JOIN " . DB_PREFIX . "payment_profiles AS pp ON (p.user_id = pp.user_id)
					WHERE b.owner_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						
						AND p.visible = '1'
						AND p.requestid = '" . intval($rid) . "'
						AND pp.mode = 'product'
						AND pp.type = 'seller'
						");

					$soldsumquery = $sheel->db->query("
                    SELECT SUM(amount) as total
					FROM " . DB_PREFIX . "buynow_orders b
					LEFT JOIN " . DB_PREFIX . "projects p ON (b.project_id = p.project_id)
					LEFT JOIN " . DB_PREFIX . "payment_profiles AS pp ON (p.user_id = pp.user_id)
					WHERE b.owner_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						
						AND p.visible = '1'
						AND p.requestid = '" . intval($rid) . "'
						AND pp.mode = 'product'
						AND pp.type = 'seller'
						");

					if ($sheel->db->num_rows($soldsumquery) > 0) {
						while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
							$soldsum = $res['total'];
						}
					} else {
						$soldsum = 0;
					}
					$sold = $sheel->db->num_rows($soldquery);

					$pendingquery = $sheel->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects AS p
					WHERE p.requestid = '" . intval($rid) . "'
						AND p.status != 'delisted'
						AND p.status != 'archived'
						AND p.status != 'expired'
						AND p.status != 'draft'
						AND p.status != 'wait_approval'
						" . (($sheel->config['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . "
						");

					$pendingcheckout = $sheel->db->num_rows($pendingquery);

					$archivedquery = $sheel->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects AS p
					WHERE p.visible = '1'
							AND p.status = 'archived'
							AND p.requestid = '" . intval($rid) . "'
							AND p.eventid = '0'
							");
					$archived = $sheel->db->num_rows($archivedquery);


					$delistedquery = $sheel->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects AS p
					WHERE p.visible = '1'
							AND p.status = 'delisted'
							AND p.requestid = '" . intval($rid) . "'
							AND p.eventid = '0'
							");
					$delisted = $sheel->db->num_rows($delistedquery);

					$expiredquery = $sheel->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects AS p
					WHERE p.visible = '1'
							AND (p.status = 'expired' OR p.status = 'finished')
							AND p.requestid = '" . intval($rid) . "'
							AND p.eventid = '0'
							");
					$ended = $sheel->db->num_rows($expiredquery);


					$activequery = $sheel->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects AS p
					WHERE p.visible = '1'
							AND p.status != 'archived'
							AND p.status != 'delisted'
							AND p.status != 'expired'
							AND p.status != 'finished'
							AND p.status != 'draft'
							AND p.status != 'wait_approval'
							AND p.requestid = '" . intval($rid) . "'
							AND p.eventid = '0'
							" . (($sheel->config['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0')) AND ((p.enhancementfee > 0 AND p.isenhancementfeepaid = '1') OR (p.enhancementfeeinvoiceid = '0'))" : "") . "
							");
					$active = $sheel->db->num_rows($activequery);

				}
			} else {
				$sheel->show['noresults'] = true;
			}

		}

		$vars = array(
			'rid' => $sheel->GPC['rid'],
			'title' => $requests[0]['title'],
			'status' => $requests[0]['status'],
			'stars' => $requests[0]['stars'],
			'registration' => $requests[0]['vehicle_registration'],
			'number' => $requests[0]['vehicle_c_number'],
			'year' => $requests[0]['vehicle_year'],
			'usedfor' => $requests[0]['used_for'],
			'usedforunit' => $requests[0]['used_for_unit'],
			'value' => $requests[0]['vehicle_value'],
			'estimatedrevenue' => $estimated_revenue,
			'numberofpictures' => $requests[0]['numberofpictures'],
			'totalparts' => $requests[0]['total_parts'],
			'cpcount' => $sheel->requests->getCataloguedPartsCount($sheel->GPC['rid']),
			'aspcount' => $sheel->requests->getAuctionSellPartsCount($sheel->GPC['rid']),
			'dspcount' => $sheel->requests->getDirectSellPartsCount($sheel->GPC['rid']),
			'upcount' => $sheel->requests->getUnsellablePartsCount($sheel->GPC['rid']),
			'rpcount' => $sheel->requests->getRecyclablePartsCount($sheel->GPC['rid']),
			'pecount' => $sheel->requests->getProjectedEarnings($sheel->GPC['rid']),
			'sold' => $sold,
			'soldsum' => $sheel->currency->format($soldsum),
			'archived' => $archived,
			'delisted' => $delisted,
			'ended' => $ended,
			'pendingcheckout' => $pendingcheckout,
			'active' => $active
		);
		$sheel->template->load_popup('main', 'inline_requestdetails.html');
		$sheel->template->parse_loop('main', array('rpictures' => $rpictures));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html2 = $sheel->template->pprint('main', $vars, false);
		echo json_encode(array('requestdetails' => $html2));
		unset($html2);
		exit();
	} else if ($sheel->GPC['do'] == 'requestdetails_orig') {
		$html2 = '';
		$sheel->template->load_popup('main', 'inline_requestdetails_orig.html');
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$html2 = $sheel->template->pprint('main', array(), false);
		echo json_encode(array('emptyrequestdetails' => $html2));
		unset($html2);
		exit();
	} else if ($sheel->GPC['do'] == 'categoryquestionspulldown') {
		if (isset($sheel->GPC['cid']) and $sheel->GPC['cid'] > 0 and isset($sheel->GPC['qid']) and isset($sheel->GPC['cattype']) and isset($sheel->GPC['mode']) and isset($sheel->GPC['counter'])) {
			$languages = $sheel->db->query("
				SELECT languagecode, title, languageiso, textdirection
				FROM " . DB_PREFIX . "language
			", 0, null, __FILE__, __LINE__);
			$lc = $sheel->db->num_rows($languages);
			$lcc = 1;
			$html = '<div class="draw-card__section" style="padding:10px 0 20px 0"><div class="clearfix"></div>';
			while ($language = $sheel->db->fetch_array($languages, DB_ASSOC)) {
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				if ($lc > 1) {
					if ($lcc == 1) {
						$html .= '<div class="sb span16 inner-left">
							<input class="draw-input with-add-on" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($sheel->GPC['counter']) . '" title="' . o($language['title']) . '" placeholder="' . $language['language'] . '" dir="' . $language['textdirection'] . '" />
							<span class="add-on-plain after fr type--subdued fr-textinput-icon">' . mb_strtoupper($language['languageiso']) . '</span>
						</div>
						<div class="sb span8 inner-right">
							' . ((isset($sheel->GPC['hidedelete']) and $sheel->GPC['hidedelete']) ? '' : '<ul class="segmented fr">
								<li><a href="javascript:;" onclick="jQuery(\'#pdmdiv_' . intval($sheel->GPC['counter']) . '\').remove()" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li>
							</ul>') . '
							<input title="{_display_order}" class="draw-input" style="display:inline-block;width:85%" name="newmultiplechoiceorder[]" id="newmultiplechoiceorder_' . intval($sheel->GPC['counter']) . '" value="" style="width:50%" placeholder="{_display_order}" />
						</div>';
					} else {
						$html .= '<div class="sb span16 inner-left">
							<input class="draw-input with-add-on" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($sheel->GPC['counter']) . '" title="' . o($language['title']) . '" placeholder="' . $language['language'] . '" dir="' . $language['textdirection'] . '" />
							<span class="add-on-plain after fr type--subdued fr-textinput-icon">' . mb_strtoupper($language['languageiso']) . '</span>
						</div>
						<div class="sb span8 inner-right"></div>';
					}
				} else {
					$html .= '<div class="sb span16 inner-left">
						<input class="draw-input with-add-on" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($sheel->GPC['counter']) . '" title="' . o($language['title']) . '" placeholder="' . $language['language'] . '" dir="' . $language['textdirection'] . '" />
						<span class="add-on-plain after fr type--subdued fr-textinput-icon">' . mb_strtoupper($language['languageiso']) . '</span>
					</div>
					<div class="sb span8 inner-right">
						' . ((isset($sheel->GPC['hidedelete']) and $sheel->GPC['hidedelete']) ? '' : '<ul class="segmented fr">
							<li><a href="javascript:;" onclick="jQuery(\'#pdmdiv_' . intval($sheel->GPC['counter']) . '\').remove()" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li>
						</ul>') . '
						<input title="{_display_order}" class="draw-input" style="display:inline-block;width:85%" name="newmultiplechoiceorder[]" id="newmultiplechoiceorder_' . intval($sheel->GPC['counter']) . '" value="" style="width:50%" placeholder="{_display_order}" />
					</div>';
				}
				$lcc++;
			}
			$html .= '</div><div class="clearfix"></div>';
			$sheel->template->templateregistry['categoryquestionspulldown'] = $html;
			echo $sheel->template->parse_template_phrases('categoryquestionspulldown');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'searchresult') {
		if (isset($sheel->GPC['itemid']) and $sheel->GPC['itemid'] > 0) {
			$title = $sheel->auction->fetch_auction('project_title', $sheel->GPC['itemid']);
			$cid = $sheel->auction->fetch_auction('cid', $sheel->GPC['itemid']);
			$url = $sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $sheel->GPC['itemid'], 'name' => stripslashes($title), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
			$picturedata = array('url' => $url, 'mode' => '208x208', 'projectid' => $sheel->GPC['itemid'], 'start_from_image' => 0, 'attachtype' => '', 'httponly' => false, 'limit' => 1, 'forcenoribbon' => false, 'forceplainimg' => false, 'forceimgsrc' => false);
			$t['bigphoto'] = $sheel->auction->print_item_photo($picturedata);
			if (($specifics = $sheel->cache->fetch("specifics_" . $sheel->GPC['itemid'] . "_outputmini_cid_" . $cid . "_5")) === false) {
				$specifics = $sheel->auction_questions->construct_auction_questions($cid, $sheel->GPC['itemid'], 'outputmini', 'product', 0, false, 5);
				$sheel->cache->store("specifics_" . $sheel->GPC['itemid'] . "_outputmini_cid_" . $cid . "_5", $specifics);
			}
			$t['specifics'] = $specifics;
			unset($title, $url, $specifics, $cid, $picturedata);
			$sheel->template->templateregistry['output'] = "$t[bigphoto]|{_price}|<strong>$0.00</strong>|$t[specifics]|timeleft|0";
			echo $sheel->template->parse_template_phrases('output');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'build') {
		die('622');
	} else if ($sheel->GPC['do'] == 'version') {
		die(VERSION . '.' . ((SVNVERSION == '622') ? '0' : SVNVERSION));
	} else if ($sheel->GPC['do'] == 'pmbcheckup') {
		die(json_encode(array('unreadpm' => 0)));
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$newpmbsql = $sheel->db->query("
				SELECT id, orderidpublic, project_id, event_id, from_id, message, subject, datetime
				FROM " . DB_PREFIX . "pmb
				WHERE to_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					AND to_status = 'new'
					AND track_popup = '0'
				ORDER BY id DESC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($newpmbsql) > 0) {
				$newpmb = $sheel->db->fetch_array($newpmbsql, DB_ASSOC);
				$crypted = array(
					'event_id' => $newpmb['event_id'],
					'orderidpublic' => $newpmb['orderidpublic'],
					'project_id' => $newpmb['project_id'],
					'from_id' => $_SESSION['sheeldata']['user']['userid'],
					'to_id' => $newpmb['from_id']
				);
				// if user x sends 5 pms to user y track popup for all messages sent to avoid user seeing 5 modal pm popups
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "pmb
					SET track_popup = '1',
					to_status = 'active'
					WHERE to_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						AND from_id = '" . $sheel->db->escape_string($newpmb['from_id']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->pmb->private_message_user_blacklist('ignored', $_SESSION['sheeldata']['user']['userid'], $newpmb['from_id'])) {
					die(json_encode(array('unreadpm' => 0)));
				}
				if ($sheel->pmb->private_message_user_blacklist('blocked', $_SESSION['sheeldata']['user']['userid'], $newpmb['from_id'])) {
					die(json_encode(array('unreadpm' => 0)));
				}
				$newpmb['message'] = $sheel->censor->strip_vulgar_words($newpmb['message']);
				$newpmb['message'] = $sheel->censor->strip_email_words($newpmb['message']);
				$newpmb['message'] = $sheel->censor->strip_domain_words($newpmb['message']);
				$newpmb['subject'] = $sheel->common->un_htmlspecialchars($newpmb['subject']);
				$newpmb['subject'] = $sheel->censor->strip_vulgar_words($newpmb['subject']);
				$newpmb['subject'] = $sheel->censor->strip_email_words($newpmb['subject']);
				$newpmb['subject'] = $sheel->censor->strip_domain_words($newpmb['subject']);
				$newpmb['username'] = $sheel->common->un_htmlspecialchars($sheel->fetch_user('username', $newpmb['from_id']));
				$newpmb['crypted'] = $sheel->encrypt_url($crypted);
				$sheel->template->templateregistry['output'] = '{_you_have_a_new_private_message_from::' . $newpmb['username'] . '}';
				$title = $sheel->template->parse_template_phrases('output');
				die(json_encode(array('unreadpm' => 1, 'title' => $title, 'description' => '"' . $sheel->shorten($newpmb['message'], 150) . '"', 'date' => $sheel->common->print_date($newpmb['datetime'], 'M-d-Y h:i:s', 1, 0), 'username' => $newpmb['username'], 'crypted' => $newpmb['crypted'])));
			}
		}
		die(json_encode(array('unreadpm' => 0)));
	} else if ($sheel->GPC['do'] == 'pmbpcheckup') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$newpmbsql = $sheel->db->query("
				SELECT id, orderidpublic, project_id, event_id, from_id, message, subject, datetime
				FROM " . DB_PREFIX . "pmb
				WHERE to_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					AND to_status = 'new'
					AND track_popup = '0'
				ORDER BY id DESC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($newpmbsql) > 0) {
				$newpmb = $sheel->db->fetch_array($newpmbsql, DB_ASSOC);
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "pmb
					SET track_popup = '1',
					to_status = 'active'
					WHERE to_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						AND from_id = '" . $sheel->db->escape_string($newpmb['from_id']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->pmb->private_message_user_blacklist('ignored', $_SESSION['sheeldata']['user']['userid'], $newpmb['from_id'])) {
					die(json_encode(array('unreadpmbp' => 0)));
				}
				if ($sheel->pmb->private_message_user_blacklist('blocked', $_SESSION['sheeldata']['user']['userid'], $newpmb['from_id'])) {
					die(json_encode(array('unreadpmbp' => 0)));
				}
				die(json_encode(array('unreadpmbp' => 1)));
			}
		}
		die(json_encode(array('unreadpmbp' => 0)));
	} else if ($sheel->GPC['do'] == 'viewpermission') {
		if (isset($sheel->GPC['id']) and isset($sheel->GPC['gid'])) {
			$html = '';
			$extrasql1 = (($sheel->config['use_internal_gateway'] == 'none') ? "AND (accessname != 'addcreditcard' AND accessname != 'delcreditcard' AND accessname != 'usecreditcard' AND accessname != 'acpaccess')" : "AND accessname != 'acpaccess'");
			$extrasql2 = "AND (accessmode = 'product' OR accessmode = 'global')";
			$sql = $sheel->db->query("
				SELECT id, accessname, subscriptiongroupid, value, visible
				FROM " . DB_PREFIX . "subscription_permissions
				WHERE subscriptiongroupid = '" . intval($sheel->GPC['gid']) . "'
				$extrasql1
				$extrasql2
				ORDER BY accessname ASC
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$html .= '<tbody>';
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					if ($res['visible'] != 0) {
						$html .= '<tr class="alt1">';
						if ($res['value'] == 'yes' or $res['value'] == 'no') {
							$userinput = ((($res['value'] == 'yes')) ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" />' : '');
						} else {
							$userinput = ((($res['accessname'] == 'attachlimit' or $res['accessname'] == 'uploadlimit')) ? $sheel->attachment->print_filesize($res['value']) : $res['value']);
						}
						$res['accesstext'] = stripslashes("{_" . $res['accessname'] . "_text}");
						$res['userinput'] = $userinput;
						$html .= '<td>' . $res['accesstext'] . '</td><td nowrap="nowrap">' . $res['userinput'] . '</td>';
						$html .= '</tr>';
					}
				}
				$html .= '</tbody>';
			}
			$title = $description = '{_unknown}';
			$sql = $sheel->db->query("
				SELECT title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, description_" . $_SESSION['sheeldata']['user']['slng'] . " AS description
				FROM " . DB_PREFIX . "subscription
				WHERE subscriptionid = '" . intval($sheel->GPC['id']) . "'
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$title = o(stripslashes($res['title']));
				$description = o(stripslashes($res['description']));
			}
			$info = $title . '|' . $description . '|' . $html;
			$sheel->template->templateregistry['info'] = $info;
			echo $sheel->template->parse_template_phrases('info');
		}
		exit();
	} else if ($sheel->GPC['do'] == 'abusereport') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'submit-abuse') {
				$sheel->GPC['abusereason'] = ((isset($sheel->GPC['abusereason']) and !empty($sheel->GPC['abusereason'])) ? $sheel->GPC['abusereason'] : '');
				$sheel->GPC['abusemessage'] = ((isset($sheel->GPC['abusemessage']) and !empty($sheel->GPC['abusemessage'])) ? $sheel->censor->strip_vulgar_words($sheel->GPC['abusemessage']) : '');
				if ($sheel->GPC['abusereason'] != 'Other') {
					$sheel->GPC['abusemessage'] = $sheel->GPC['abusereason'];
				}
				$sheel->GPC['memberstart'] = $sheel->common->print_date($sheel->fetch_user('date_added', $_SESSION['sheeldata']['user']['userid']), $sheel->config['globalserverlocale_globaldateformat']);
				$sheel->GPC['countryname'] = $sheel->common_location->print_user_country($_SESSION['sheeldata']['user']['userid'], $_SESSION['sheeldata']['user']['slng']);
				if (empty($sheel->GPC['abusemessage'])) {
					$sheel->GPC['abusemessage'] = '{_none}';
				}
				$sheel->GPC['buyerid'] = ((isset($sheel->GPC['buyerid'])) ? intval($sheel->GPC['buyerid']) : 0);
				$sheel->GPC['sellerid'] = ((isset($sheel->GPC['sellerid'])) ? intval($sheel->GPC['sellerid']) : 0);
				$sheel->GPC['otherid'] = ((isset($sheel->GPC['otherid'])) ? intval($sheel->GPC['otherid']) : 0);


				// #### insert abuse report into database ##############################
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "abuse_reports
					(abuseid, regarding, username, email, itemid, otherid, abusetype, type, pageurl, status, dateadded, buyerid, sellerid)
					VALUES (
					NULL,
					'" . $sheel->db->escape_string($sheel->GPC['abusemessage']) . "',
					'" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['username']) . "',
					'" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['email']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['id']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['otherid']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['abusetype']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['type']) . "',
					'" . $sheel->db->escape_string($sheel->GPC['pageurl']) . "',
					'1',
					'" . DATETIME24H . "',
					'" . intval($sheel->GPC['buyerid']) . "',
					'" . intval($sheel->GPC['sellerid']) . "')
				");
				$sheel->email->mail = SITE_CONTACT;
				$sheel->email->slng = $sheel->language->fetch_site_slng();
				$sheel->email->get('submit_abuse');
				$sheel->email->set(
					array(
						'{{abusetype}}' => $sheel->GPC['abusetype'],
						'{{abuseurl}}' => urldecode($sheel->GPC['pageurl']),
						'{{reporter}}' => $_SESSION['sheeldata']['user']['username'],
						'{{reporteremail}}' => $_SESSION['sheeldata']['user']['email'],
						'{{abusemessage}}' => $sheel->GPC['abusemessage'],
						'{{memberstart}}' => $sheel->GPC['memberstart'],
						'{{countryname}}' => $sheel->GPC['countryname'],
					)
				);
				$sheel->email->send();


				$sql = $sheel->db->query("
					SELECT abuseid, regarding, dateadded, pageurl
					FROM " . DB_PREFIX . "abuse_reports
					WHERE email = '" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['email']) . "'
						AND type = 'product'
						AND status = '1'
					ORDER BY abuseid DESC
				");
				if ($sheel->db->num_rows($sql) > 0) {
					while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['regarding'] = '<a href="' . urldecode($res['pageurl']) . '" target="_parent">' . o($res['regarding']) . '</a>';
						$res['date'] = $sheel->common->print_date($res['dateadded'], $sheel->config['globalserverlocale_globaldateformat']);
						$reports[] = $res;
					}
				}
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Violation reported', 'A new violation report has been submitted by ' . $_SESSION['sheeldata']['user']['username'] . '.');
				$sheel->template->load_popup('head', 'popup_header.html');
				$sheel->template->load_popup('main', 'ajax_abuse.html');
				$sheel->template->load_popup('foot', 'popup_footer.html');
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
				$sheel->template->parse_loop('main', array('reports' => $reports));
				$sheel->template->pprint('head', array('headinclude' => $sheel->template->meta['headinclude'], 'onbeforeunload' => '', 'onload' => $sheel->template->meta['onload']));
				$sheel->template->pprint('main', array());
				$sheel->template->pprint('foot', array('footinclude' => $sheel->template->meta['footinclude']));
				exit();
			}
			$abusetypes = array('listing', 'bid', 'message', 'customerimage', 'profile', 'bpublic', 'feedback', 'pmb', 'payment');
			$sheel->GPC['cmd'] = ((isset($sheel->GPC['cmd'])) ? $sheel->GPC['cmd'] : '');
			$sheel->GPC['id'] = ((isset($sheel->GPC['id'])) ? $sheel->GPC['id'] : '');
			$sheel->GPC['otherid'] = ((isset($sheel->GPC['otherid'])) ? $sheel->GPC['otherid'] : ''); // messageid from message board on item page
			$id = $sheel->GPC['id'];
			$otherid = $sheel->GPC['otherid'];
			$type = ((isset($sheel->GPC['type'])) ? $sheel->GPC['type'] : '');
			$pageurl = ((isset($sheel->GPC['pageurl'])) ? $sheel->GPC['pageurl'] : '');
			if (!in_array($sheel->GPC['cmd'], $abusetypes)) {
				echo 'Invalid violation type.';
				exit();
			}
			$abusetype_pulldown = $sheel->profile->print_abuse_type_pulldown($sheel->GPC['cmd'], $sheel->GPC['id']);
			$abusetype_pulldown .= '<div class="draw-select__wrapper w-100pct mb-4" id="wrapper-reason"><select id="abusereason" name="abusereason" class="draw-select" onchange="if (fetch_js_object(\'abusereason\').options[fetch_js_object(\'abusereason\').selectedIndex].value == \'{_other}\'){toggle_show(\'showabusemessage\');}else{toggle_hide(\'showabusemessage\')}"><option value="">{_select_reason} &ndash;</option>' . (($sheel->GPC['cmd'] == 'payment') ? '<option value="{_unpaid_order_number}' . $sheel->GPC['id'] . '">{_unpaid_order_number}' . $sheel->GPC['id'] . '</option><option value="{_other}">{_other} ({_enter_comment_lc})</option>' : '<option value="{_fake_product}">{_fake_product}</option><option value="{_prohibited_product}">{_prohibited_product}</option><option value="{_contact_posted_within_description}">{_contact_posted_within_description}</option><option value="{_product_posted_several_times}">{_product_posted_several_times}</option><option value="{_product_violates_ip_rights}">{_product_violates_ip_rights}</option><option value="{_incorrect_category}">{_incorrect_category}</option><option value="{_inappropriate_title}">{_inappropriate_title}</option><option value="{_inappropriate_video}">{_inappropriate_video}</option><option value="{_inappropriate_feedback_comment}">{_inappropriate_feedback_comment}</option><option value="{_inappropriate_feedback_response}">{_inappropriate_feedback_response}</option><option value="{_inappropriate_question_or_answer}' . (($otherid > 0) ? ' (Post ID: ' . $otherid . ')' : '') . '"' . (($otherid > 0) ? ' selected="selected"' : '') . '>{_inappropriate_question_or_answer}' . (($otherid > 0) ? ' (Post ID: ' . $otherid . ')' : '') . '</option><option value="{_multiple_prices_within_description}">{_multiple_prices_within_description}</option><option value="{_pirated}">{_pirated}</option><option value="{_other}">{_other} ({_enter_comment_lc})</option>') . '</select></div>';
			// #### form elements ##################################################
			$_SESSION['sheeldata']['user']['csrf'] = md5(uniqid(mt_rand(), true));
			$form['start'] = '<form action="' . HTTPS_SERVER . 'ajax" method="post" name="ilform" accept-charset="UTF-8" onsubmit="return submit_violation()"><input type="hidden" name="do" value="abusereport" /><input type="hidden" name="cmd" value="submit-abuse" /><input type="hidden" name="id" value="' . o($id) . '" /><input type="hidden" name="type" value="' . o($type) . '" /><input type="hidden" name="token" value="' . $_SESSION['sheeldata']['user']['csrf'] . '" /><input type="hidden" name="pageurl" value="' . o(urlencode($pageurl)) . '" />';
			$form['start'] .= ((isset($sheel->GPC['buyerid']) and $sheel->GPC['buyerid'] > 0) ? '<input type="hidden" name="buyerid" value="' . intval($sheel->GPC['buyerid']) . '" />' : '');
			$form['start'] .= (($sheel->GPC['cmd'] == 'listing') ? '<input type="hidden" name="sellerid" value="' . $sheel->auction->fetch_auction('user_id', $id) . '" />' : '');
			$form['start'] .= (($otherid > 0) ? '<input type="hidden" name="otherid" value="' . intval($otherid) . '" />' : '');
			$form['end'] = '</form>';
			$sheel->show['alreadyreported'] = false;
			$reports = array();
			$sql = $sheel->db->query("
				SELECT abuseid, regarding, dateadded, pageurl, itemid
				FROM " . DB_PREFIX . "abuse_reports
				WHERE email = '" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['email']) . "'
					AND type = 'product'
					AND status = '1'
					AND otherid = '" . $sheel->db->escape_string($otherid) . "'
				ORDER BY abuseid DESC
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					if ($res['itemid'] > 0 and $id > 0 and $res['itemid'] == $id) {
						$sheel->show['alreadyreported'] = true;
					}
					$res['regarding'] = '<a href="' . urldecode($res['pageurl']) . '" target="_parent">' . o($res['regarding']) . '</a>';
					$res['date'] = $sheel->common->print_date($res['dateadded'], $sheel->config['globalserverlocale_globaldateformat']);
					$reports[] = $res;
				}
			}
			$vars = array(
				'abusetype_pulldown' => $abusetype_pulldown,
				'type' => $type
			);
			$sheel->template->meta['jsinclude']['header'][] = 'vendor/jquery_' . JQUERYVERSION;
			$sheel->template->meta['jsinclude']['header'][] = 'vendor/growl';
			$sheel->template->meta['cssinclude']['vendor'][] = 'growl';
			$sheel->template->load_popup('head', 'popup_header.html');
			$sheel->template->load_popup('main', 'ajax_abuse.html');
			$sheel->template->load_popup('foot', 'popup_footer.html');
			$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => $form));
			$sheel->template->parse_loop('main', array('reports' => $reports));
			$sheel->template->pprint('head', array('headinclude' => $sheel->template->meta['headinclude'], 'onbeforeunload' => '', 'onload' => $sheel->template->meta['onload']));
			$sheel->template->pprint('main', $vars);
			$sheel->template->pprint('foot', array('footinclude' => $sheel->template->meta['footinclude']));
			exit();
		}
		header('Location:' . HTTPS_SERVER . 'signin/?redirect=' . urlencode('ajax?do=abusereport&type=' . ((isset($sheel->GPC['type'])) ? $sheel->GPC['type'] : '') . '&cmd=' . ((isset($sheel->GPC['cmd'])) ? $sheel->GPC['cmd'] : '') . '&id=' . ((isset($sheel->GPC['id'])) ? $sheel->GPC['id'] : '') . '&pageurl=' . ((isset($sheel->GPC['pageurl'])) ? o(urlencode($sheel->GPC['pageurl'])) : '')));
		exit();
	} else if ($sheel->GPC['do'] == 'bitcoin-confirmations' and isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0) {
		$sql = $sheel->db->query("
			SELECT confirmations
			FROM " . DB_PREFIX . "invoices_bitcoin
			WHERE invoice_id = '" . intval($sheel->GPC['id']) . "'
		");
		if ($sheel->db->num_rows($sql) > 0) {
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			die($res['confirmations']);
		}
		die('0');
	} else if ($sheel->GPC['do'] == 'updateitemqty') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['pid']) and $sheel->GPC['pid'] > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET buynow_qty = '" . intval($sheel->GPC['qty']) . "'
					WHERE project_id = '" . intval($sheel->GPC['pid']) . "'
						AND user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					LIMIT 1
				");


				echo intval($sheel->GPC['qty']);
				exit();
			}
		}
		echo '0';
		exit();
	} else if ($sheel->GPC['do'] == 'updateitemvqty') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['pid']) and $sheel->GPC['pid'] > 0 and isset($sheel->GPC['vqty']) and is_array($sheel->GPC['vqty'])) {
				$totalqty = 0;
				foreach ($sheel->GPC['vqty'] as $vid => $qty) {
					if ($vid > 0) {
						$totalqty += $qty;
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "variants
							SET qty = '" . intval($qty) . "'
							WHERE project_id = '" . intval($sheel->GPC['pid']) . "'
								AND id = '" . intval($vid) . "'
							LIMIT 1
						");
					}
				}
				if (isset($sheel->GPC['pid']) and $sheel->GPC['pid'] > 0) {
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET buynow_qty = '" . intval($totalqty) . "'
						WHERE project_id = '" . intval($sheel->GPC['pid']) . "'
							AND user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
						LIMIT 1
					");

				}
				echo $totalqty;
				exit();
			}
		}
		echo '0';
		exit();
	} else if ($sheel->GPC['do'] == 'ordercheckout') {
		if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'updateqty' and isset($sheel->GPC['qty']) and isset($sheel->GPC['cartid'])) {
			if ($sheel->GPC['qty'] <= 0 and $sheel->GPC['cartid'] > 0) {
				$sheel->cart->set_deleted(0, intval($sheel->GPC['cartid']));
			} else if ($sheel->GPC['qty'] > 0 and $sheel->GPC['cartid'] > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "carts
					SET qty = '" . intval($sheel->GPC['qty']) . "'
					WHERE cartid = '" . intval($sheel->GPC['cartid']) . "'
				");
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'applypromocode' and isset($sheel->GPC['promocode'])) { // apply promo code on review your order page
			if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
				// find out what store ids are in our cart to compare promo codes toward
				$storeids = $sheel->buynow->fetch_storeids_array_in_cart($_SESSION['sheeldata']['user']['userid'], session_id());
				$sheel->show['promocoderedeemed'] = false;
				$sheel->show['promocoderedeemederror'] = false;
				$sheel->show['promocoderedeemederrormaxpercustomer'] = false;
				$sheel->show['promocoderedeemederrormaxusetotal'] = false;
				$sheel->show['promocoderedeemederrorexpired'] = false;
				$sql = $sheel->db->query("
					SELECT storeid, used, maxpercustomer, maxtotal
					FROM " . DB_PREFIX . "stores_promocode
					WHERE promocode = '" . $sheel->db->escape_string($sheel->GPC['promocode']) . "'
				");
				if ($sheel->db->num_rows($sql) > 0 and count($storeids) > 0) {
					while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
						// check if the promo code can be used based on the storeids in the buyers cart matching storeid in this loop
						if (in_array($res['storeid'], $storeids)) { // we're adding a promo code for items in our cart for only stores that accept those promo codes
							$canusepromocode = true;
							if ($sheel->buynow->has_promocode_expired($res['storeid'], $sheel->GPC['promocode'])) { // promo code can or has expired
								$sheel->show['promocoderedeemederrorexpired'] = true;
								$canusepromocode = false;
							}
							if ($res['maxtotal'] > 0 and $res['used'] >= $res['maxtotal']) { // max uses of promo code met
								$sheel->show['promocoderedeemederrormaxusetotal'] = true;
								$canusepromocode = false;
							} else {
								$usedpromocodecount = $sheel->buynow->get_used_promocode_count($_SESSION['sheeldata']['user']['userid'], $res['storeid'], $sheel->GPC['promocode']);
								if ($usedpromocodecount >= $res['maxpercustomer']) { // max uses for this buyer for this store promo code met
									$sheel->show['promocoderedeemederrormaxpercustomer'] = true;
									$canusepromocode = false;
								}
							}
							if ($canusepromocode) {
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "carts
									SET promocode = '" . $sheel->db->escape_string($sheel->GPC['promocode']) . "'
									WHERE userid = '" . $_SESSION['sheeldata']['user']['userid'] . "'
										AND wishlist = '0'
										AND auctionorderid = '0'
										AND purchased = '0'
										AND isdeleted = '0'
										AND storeid = '" . $res['storeid'] . "'
										AND sessionid = '" . $sheel->db->escape_string(session_id()) . "'
								");
								if ($sheel->db->affected_rows() > 0) {
									$sheel->show['promocoderedeemed'] = true;
									$sheel->show['promocoderedeemederror'] = false;
								}
							}
						} else {
							$sheel->show['promocoderedeemederror'] = true;
						}
					}
				} else { // invalid code, not found by any store..
					$sheel->show['promocoderedeemederror'] = true;
				}
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'updateshipper' and isset($sheel->GPC['id']) and isset($sheel->GPC['cartid'])) {
			if ($sheel->GPC['id'] > 0 and $sheel->GPC['cartid'] > 0) {
				$calculatedshipping = ((isset($sheel->GPC['cshipping']) and $sheel->GPC['cshipping'] > 0) ? o($sheel->GPC['cshipping']) : '0.00');
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "carts
					SET shipperid = '" . intval($sheel->GPC['id']) . "',
					calculatedshipping = '" . $sheel->db->escape_string($calculatedshipping) . "'
					WHERE cartid = '" . intval($sheel->GPC['cartid']) . "'
					LIMIT 1
				");
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'updateshipto' and isset($sheel->GPC['spid']) and isset($sheel->GPC['cartid'])) {
			if ($sheel->GPC['spid'] > 0 and $sheel->GPC['cartid'] > 0) {
				$sql = $sheel->db->query("
					SELECT d.ship_packagetype_1 AS packagetype, d.ship_pickuptype_1 AS pickuptype, d.ship_fallbackfee_1 AS fallbackcost, c.calculatedshipping, c.shipperid, c.qty, p.countryid, p.currencyid, p.zipcode, p.state, p.city, p.user_id, s.ship_method, s.ship_handlingtime, s.ship_handlingfee, s.ship_length, s.ship_width, s.ship_height, s.ship_weightlbs, s.ship_weightoz
					FROM " . DB_PREFIX . "carts c
					LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (c.itemid = s.project_id)
					LEFT JOIN " . DB_PREFIX . "projects p ON (c.itemid = p.project_id)
					LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations d ON (p.project_id = d.project_id)
					WHERE c.cartid = '" . intval($sheel->GPC['cartid']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) > 0) {
					$res = $sheel->db->fetch_array($sql, DB_ASSOC);
					if ($res['ship_method'] == 'calculated') { // using calculated shipping let's get new rates for the new location being selected
						$sql2 = $sheel->db->query("
							SELECT carrier, shipcode
							FROM " . DB_PREFIX . "shippers
							WHERE shipperid = '" . $res['shipperid'] . "'
						");
						$res2 = $sheel->db->fetch_array($sql2, DB_ASSOC);
						$sql3 = $sheel->db->query("
							SELECT cc
							FROM " . DB_PREFIX . "locations
							WHERE locationid = '" . $res['countryid'] . "'
						");
						$res3 = $sheel->db->fetch_array($sql3, DB_ASSOC);
						$sizecode = $sheel->shipcalculator->sizeunits($res2['carrier'], $res['ship_length'], $res['ship_width'], $res['ship_height'], true);
						$carriers[$res2['carrier']] = true;
						$sqlx = $sheel->db->query("
							SELECT city, state, country, zipcode
							FROM " . DB_PREFIX . "shipping_profiles
							WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
								AND type = 'shipping'
								AND id = '" . intval($sheel->GPC['spid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($sheel->db->num_rows($sqlx) > 0) {
							while ($resx = $sheel->db->fetch_array($sqlx, DB_ASSOC)) {
								$postalzip = $resx['zipcode'];
								$state = $resx['state'];
								$city = $resx['city'];
								$countryshort = $sheel->common_location->print_country_name(0, $_SESSION['sheeldata']['user']['slng'], true, $resx['country']);
							}
						}
						$shipinfo = array(
							'weight' => $res['ship_weightlbs'],
							'destination_zipcode' => $postalzip,
							'destination_state' => $state,
							'destination_city' => $city,
							'destination_country' => $countryshort,
							'origin_zipcode' => $res['zipcode'],
							'origin_state' => $res['state'],
							'origin_city' => $res['city'],
							'origin_country' => $res3['cc'],
							'carriers' => $carriers,
							'shipcode' => $res2['shipcode'],
							'length' => $res['ship_length'],
							'width' => $res['ship_width'],
							'height' => $res['ship_height'],
							'weightunit' => 'LBS',
							'dimensionunit' => 'IN',
							'sizecode' => $sizecode,
							'pickuptype' => $res['pickuptype'],
							'packagingtype' => $res['packagetype']
						);
						$rates = $sheel->shipcalculator->get_rates($shipinfo);
						$price = $crypto_price = '';
						if ((isset($rates['price'][0]) and $rates['price'][0] > 0) or (isset($rates['price'][1]) and $rates['price'][1] > 0)) {
							if ($res3['cc'] == 'CA') {
								$res['cost'] = $res['cost_next'] = ((!empty($rates['price'][1])) ? $rates['price'][1] : $rates['price'][0]); // 0 USD 1 CAD
								$res['crypto_cost'] = $res['crypto_cost_next'] = 0;
								if (isset($sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcryptocurrency']]['rate'])) {
									$res['crypto_cost'] = $res['crypto_cost_next'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcryptocurrency'], $rates['price'][1], $sheel->currency->currencies['CAD']['currency_id']);
								}
							} else {
								$res['cost'] = $res['cost_next'] = ((!empty($rates['price'][0])) ? $rates['price'][0] : $rates['price'][1]); // 0 USD 1 CAD
								$res['crypto_cost'] = $res['crypto_cost_next'] = 0;
								if (isset($sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcryptocurrency']]['rate'])) {
									$res['crypto_cost'] = $res['crypto_cost_next'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcryptocurrency'], $rates['price'][0], $res['currencyid']);
								}
							}
							$price = $res['cost'];
							$crypto_price = $res['crypto_cost'];
						} else {
							if (isset($res['fallbackcost']) and $res['fallbackcost'] > 0) {
								$res['cost'] = $res['cost_next'] = $res['fallbackcost']; // 25.50
								$res['crypto_cost'] = $res['crypto_cost_next'] = 0;
								if (isset($sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcryptocurrency']]['rate'])) {
									$res['crypto_cost'] = $res['crypto_cost_next'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcryptocurrency'], $res['cost'], $res['currencyid']);
								}
								$price = $res['cost'];
								$crypto_price = $res['crypto_cost'];
							}
						}
						if ($price != '') {
							$priceraw = ($price * $res['qty']);
							$crypto_priceraw = ($crypto_price * $res['qty']);
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "carts
								SET calculatedshipping = '" . $sheel->db->escape_string($priceraw) . "',
								crypto_calculatedshipping = '" . $sheel->db->escape_string($crypto_priceraw) . "'
								WHERE cartid = '" . intval($sheel->GPC['cartid']) . "'
								LIMIT 1
							");
						}
					}
					$sheel->db->query("
						UPDATE " . DB_PREFIX . "carts
						SET spid = '" . intval($sheel->GPC['spid']) . "',
						shipperid = '0'
						WHERE cartid = '" . intval($sheel->GPC['cartid']) . "'
						LIMIT 1
					");
				}
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'updatepaymethod' and isset($sheel->GPC['id']) and isset($sheel->GPC['sellerid'])) { // updates payment method for all items in buyers cart only with this particular seller
			if (!empty($sheel->GPC['id']) and $sheel->GPC['sellerid'] > 0 and !empty($_SESSION['sheeldata']['user']['userid'])) {
				$sheel->GPC['lastfour'] = ((isset($sheel->GPC['lastfour']) and !empty($sheel->GPC['lastfour'])) ? $sheel->GPC['lastfour'] : '');
				$sheel->GPC['carddata'] = ((isset($sheel->GPC['carddata']) and !empty($sheel->GPC['carddata'])) ? $sheel->crypt->encrypt(base64_encode($sheel->GPC['carddata'])) : '');
				$sheel->GPC['ccid'] = ((isset($sheel->GPC['ccid']) and $sheel->GPC['ccid'] > 0) ? intval($sheel->GPC['ccid']) : '0');
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "carts
					SET paymethod = '" . $sheel->db->escape_string($sheel->GPC['id']) . "',
					ccid = '" . intval($sheel->GPC['ccid']) . "',
					carddata = '" . $sheel->db->escape_string($sheel->GPC['carddata']) . "',
					lastfour = '" . $sheel->db->escape_string($sheel->GPC['lastfour']) . "'
					WHERE sellerid = '" . intval($sheel->GPC['sellerid']) . "'
						AND (userid = '" . $_SESSION['sheeldata']['user']['userid'] . "' AND browsertoken = '" . $sheel->db->escape_string($_COOKIE[COOKIE_PREFIX . 'token']) . "' AND sessionid = '" . $sheel->db->escape_string(session_id()) . "' OR (userid = '" . $_SESSION['sheeldata']['user']['userid'] . "' AND ipaddress = ''))
						AND isdeleted = '0'
						AND saveforlater = '0'
						AND wishlist = '0'
						AND purchased = '0'
				");
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'pointsspend' and isset($sheel->GPC['points']) and isset($sheel->GPC['cartid']) and isset($sheel->GPC['peritemraw']) and isset($sheel->GPC['qty'])) {
			if ($sheel->config['pointsystem'] and $sheel->GPC['cartid'] > 0 and isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
				$sheel->GPC['points'] = intval($sheel->GPC['points']); // 1000
				$sheel->GPC['points'] = (($sheel->GPC['points'] < 0) ? 0 : $sheel->GPC['points']);
				$sheel->GPC['oldpoints'] = intval($sheel->GPC['oldpoints']); // 695
				$sheel->GPC['cartid'] = intval($sheel->GPC['cartid']);
				$sheel->GPC['currencyid'] = $sheel->db->fetch_field(DB_PREFIX . "carts", "cartid = '" . $sheel->GPC['cartid'] . "'", "currencyid");
				$sheel->GPC['iscrypto'] = ((isset($sheel->currency->currencies[$sheel->GPC['currencyid']]['iscrypto']) and $sheel->currency->currencies[$sheel->GPC['currencyid']]['iscrypto']) ? true : false);
				$sheel->GPC['qty'] = intval($sheel->GPC['qty']); // 1
				$sheel->GPC['peritemraw'] = sprintf("%." . $sheel->currency->currencies[$sheel->GPC['currencyid']]['decimal_places'] . "f", $sheel->GPC['peritemraw']);

				if ($sheel->GPC['iscrypto']) { // x.xxxxxxxx -> x.xx
					$sheel->GPC['peritemraw'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcurrency'], $sheel->GPC['peritemraw'], $sheel->GPC['currencyid']);
				}

				if ($sheel->GPC['peritemraw'] > 0 and $sheel->config['pointsystemrate'] > 0) {
					$sheel->GPC['maxitempoints'] = ceil(((($sheel->GPC['peritemraw'] * $sheel->GPC['qty']) * $sheel->config['pointsystemrate']) * 100) + $sheel->GPC['oldpoints']);
					if ($sheel->GPC['points'] > $sheel->GPC['maxitempoints']) { // apply only points that we need and avoid points overspend
						$sheel->GPC['points'] = $sheel->GPC['maxitempoints'];
					}
				} else if ($sheel->GPC['peritemraw'] <= 0 and $sheel->GPC['oldpoints'] > 0 and $sheel->config['pointsystemrate'] > 0) {
					if ($sheel->GPC['points'] > $sheel->GPC['oldpoints']) { // apply only points that we need and avoid points overspend
						$sheel->GPC['points'] = $sheel->GPC['oldpoints'];
					}
				}
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "carts
					SET pointsspend = '" . intval($sheel->GPC['points']) . "'
					WHERE cartid = '" . intval($sheel->GPC['cartid']) . "'
						AND (userid = '" . $_SESSION['sheeldata']['user']['userid'] . "' AND browsertoken = '" . $sheel->db->escape_string($_COOKIE[COOKIE_PREFIX . 'token']) . "' AND sessionid = '" . $sheel->db->escape_string(session_id()) . "' OR (userid = '" . $_SESSION['sheeldata']['user']['userid'] . "' AND ipaddress = ''))
					LIMIT 1
				");
			}
			$html = $sheel->buynow->print_order_review($_SESSION);
			die(json_encode(array('response' => $html)));
		} else if (isset($sheel->GPC['action']) and $sheel->GPC['action'] == 'refresh') { // refresher
			die(json_encode(array('response' => $sheel->buynow->print_order_review($_SESSION))));
		}
		$sheel->buynow->print_order_review($_SESSION, true);
		exit();
	} else if ($sheel->GPC['do'] == 'shoppingcart') {
		$sheel->GPC['limit'] = isset($sheel->GPC['limit']) ? intval($sheel->GPC['limit']) : 10;
		$sheel->show['noresults'] = true;
		$inlinecart = array();
		$inlinecart = $sheel->cart->fetch($sheel->GPC['limit']);
		$sheel->cache->store('inlinecart', $inlinecart);
		if (is_array($inlinecart) and count($inlinecart) > 0) {
			$sheel->show['noresults'] = false;
		}
		$sheel->template->load_popup('main', 'inline_viewcart.html');
		$sheel->template->parse_loop('main', array('inlinecart' => $inlinecart));
		$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
		$sheel->template->pprint('main', array());
		exit();
	} else if ($sheel->GPC['do'] == 'thumbdragdropupload' and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] and isset($sheel->GPC['cid']) and $sheel->GPC['cid'] > 0) {
		if (is_array($_FILES) and isset($_FILES['userImage']['tmp_name'])) {
			if (is_uploaded_file($_FILES['userImage']['tmp_name'])) {
				if ($fileinfo = getimagesize($_FILES['userImage']['tmp_name'])) { // only accept images
					if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) {
						if (filesize($_FILES['userImage']['tmp_name']) > 65536) { // check file size to keep sites running fast
							$array = array(
								'error' => 1,
								'note' => 'The file size is to big for a category thumbnail. For best results, no more than 65KB.',
							);
							$response = json_encode($array);
							echo $response;
							exit();
						}
						if ($fileinfo[0] < 150 or $fileinfo[1] < 150) { // check width / height limits
							$array = array(
								'error' => 1,
								'note' => 'The width and height for this thumbnail should be at least 150x150 pixels.  For best results, at least 170x170.',
							);
							$response = json_encode($array);
							echo $response;
							exit();
						}
						if ($fileinfo[2] != 1 and $fileinfo[2] != 2 and $fileinfo[2] != 3) { // check if .jpg, .gif or .png
							$array = array(
								'error' => 1,
								'note' => 'The image type must be jpg, gif or png only.'
							);
							$response = json_encode($array);
							echo $response;
							exit();
						}
						// check for file extension
						$ext = pathinfo($_FILES['userImage']['name'], PATHINFO_EXTENSION);
						if (empty($ext)) // jpg, jpeg, gif, png
						{
							$array = array(
								'error' => 1,
								'note' => 'The filename must contain a .jpg, .gif or .png extension.'
							);
							$response = json_encode($array);
							echo $response;
							exit();
						}
						$sourcePath = $_FILES['userImage']['tmp_name'];
						$filename = intval($sheel->GPC['cid']) . '.' . $ext; // 3019.jpg
						$targetPath = DIR_ATTACHMENTS . 'categorythumbs/' . $filename;
						if (file_exists($targetPath)) {
							unlink($targetPath);
						}
						if (move_uploaded_file($sourcePath, $targetPath)) {
							// todo: push move to cdn function?
							$sheel->db->query("
								UPDATE " . DB_PREFIX . "categories
								SET catthumb = '" . $sheel->db->escape_string($filename) . "'
								WHERE cid = '" . intval($sheel->GPC['cid']) . "'
								LIMIT 1
							");
							$array = array(
								'error' => 0,
								'note' => '<div class="photo"><img src="' . $sheel->config['imguploadscdn'] . 'categorythumbs/' . $filename . '?t=' . time() . '" border="0" /></div>'
							);
							$response = json_encode($array);
							die($response);
						}
					}
				}
			}
		}
		$array = array(
			'error' => 1,
			'note' => 'Something went wrong.  Check folder permissions for /application/uploads/attachments/categorythumbs/'
		);
		$response = json_encode($array);
		die($response);
	} else if ($sheel->GPC['do'] == 'hpadragdropupload' and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] and isset($sheel->GPC['id']) and !empty($sheel->GPC['id']) and isset($sheel->GPC['url']) and !empty($sheel->GPC['url'])) {
		if (is_array($_FILES) and isset($_FILES['userImage']['tmp_name'])) {
			if (is_uploaded_file($_FILES['userImage']['tmp_name'])) {
				if ($fileinfo = getimagesize($_FILES['userImage']['tmp_name'])) { // only accept image
					if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) { // check filesize to keep sites running fast
						if (filesize($_FILES['userImage']['tmp_name']) > 131072) {
							$array = array(
								'error' => 1,
								'note' => 'The filesize is to big for this ad. For best results, no more than 130KB.',
								'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . (($sheel->GPC['id'] == 3) ? '.gif' : (($sheel->GPC['id'] == 9) ? '.png' : '.jpg')) . '" border="0" />'
							);
							$response = json_encode($array);
							die($response);
						}
						switch ($sheel->GPC['id']) { // check width / height limits
							case '1': { // 300x?? & check if only jpg
									if ($fileinfo[0] > 300) {
										$array = array(
											'error' => 1,
											'note' => 'The maximum width for this ad must be 300 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '2': { // 280x390 & check if only jpg
									if ($fileinfo[0] != 280 or $fileinfo[1] != 390) {
										$array = array(
											'error' => 1,
											'note' => 'The width and height of this ad must be exactly 280x390 pixels.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '3': { // 400x39 245x400 & check if only gif
									if ($fileinfo[0] != 400 or $fileinfo[1] != 39) {
										$array = array(
											'error' => 1,
											'note' => 'The width and height of this ad must be exactly 400x39 pixels.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.gif" width="400" height="39" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 1) {
										// error not .gif
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .gif file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.gif" width="400" height="39" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '4': { // 1110 x 290
									if ($fileinfo[0] > 1110) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 1110 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '5': { // 440 x 200
									if ($fileinfo[0] > 440) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 440 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '6': { // 440 x 200
									if ($fileinfo[0] > 440) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 440 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '7': { // 440 x 200
									if ($fileinfo[0] > 440) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 440 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '8': { // 440 x 200
									if ($fileinfo[0] > 440) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 440 pixels or less.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 2) {
										// error not .jpg
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .jpg file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.jpg" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
							case '9': { // 1920 x 55 & check only png
									if ($fileinfo[0] < 1920) {
										$array = array(
											'error' => 1,
											'note' => 'The width should be 1920 pixels or more.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.png" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[1] != 55) {
										$array = array(
											'error' => 1,
											'note' => 'The height should be exactly 55 pixels.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.png" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									if ($fileinfo[2] != 3) {
										$array = array(
											'error' => 1,
											'note' => 'The image type for this ad must be a .png file.',
											'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . '.png" border="0" />'
										);
										$response = json_encode($array);
										die($response);
									}
									break;
								}
						}
						$sourcePath = $_FILES['userImage']['tmp_name'];
						$targetPath = DIR_ATTACHMENTS . 'ax/hpa' . intval($sheel->GPC['id']) . (($sheel->GPC['id'] == 3) ? '.gif' : (($sheel->GPC['id'] == 9) ? '.png' : '.jpg'));
						if (file_exists($targetPath)) {
							@unlink($targetPath);
						}
						if (move_uploaded_file($sourcePath, $targetPath)) {
							$setting['1'] = 'globalserversettings_homepageadurl';
							$setting['2'] = 'globalserversettings_homepageadurl2';
							$setting['3'] = 'globalserversettings_homepageadurl3';
							$setting['4'] = 'globalserversettings_homepageadurl4';
							$setting['5'] = 'globalserversettings_homepageadurl5';
							$setting['6'] = 'globalserversettings_homepageadurl6';
							$setting['7'] = 'globalserversettings_homepageadurl7';
							$setting['8'] = 'globalserversettings_homepageadurl8';
							$setting['9'] = 'globalserversettings_homepageadurl9';
							if (isset($sheel->GPC['id']) and $sheel->GPC['id'] <= 9) {
								$sheel->db->query("
									UPDATE " . DB_PREFIX . "configuration
									SET value = '" . $sheel->db->escape_string($sheel->GPC['url']) . "'
									WHERE name = '" . $sheel->db->escape_string($setting[intval($sheel->GPC['id'])]) . "'
									LIMIT 1
								");
								$array = array(
									'error' => 0,
									'ad' => '<img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa' . intval($sheel->GPC['id']) . (($sheel->GPC['id'] == 3) ? '.gif' : (($sheel->GPC['id'] == 9) ? '.png' : '.jpg')) . '?t=' . time() . '" border="0" />',
								);
								$response = json_encode($array);
								die($response);
							}
						}
					}
				}
			}
		}
		$array = array(
			'error' => 1,
			'note' => 'Something went wrong.  Check folder permissions for ' . $sheel->config['imguploadscdn'] . 'ax/',
			'ad' => ''
		);
		$response = json_encode($array);
		die($response);
	} else if ($sheel->GPC['do'] == 'savesearch') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'submit-save') {
				$sheel->GPC['title'] = ((isset($sheel->GPC['title'])) ? $sheel->GPC['title'] : '');
				$sheel->GPC['fav'] = ((isset($sheel->GPC['fav'])) ? $sheel->GPC['fav'] : '');
				$sheel->GPC['verbose'] = ((isset($sheel->GPC['verbose'])) ? $sheel->GPC['verbose'] : '');


				$unc = urldecode($sheel->GPC['fav']);
				$unc = unserialize($unc);
				if (!empty($unc) and is_array($unc)) {
					$url = '';
					foreach ($unc as $value) {
						if (is_array($value)) {
							foreach ($value as $search => $option) {
								if ($search == 'sid') {
									if (is_array($option)) {
										foreach ($option as $searchkey => $searchsel) {
											if (!empty($searchsel)) {
												$url .= '&amp;sid[' . $searchkey . ']=' . $searchsel;
											}
										}
									}
								} else {
									if (!empty($search) and !empty($option)) {
										if ($search == 'q') {
											$unc['keywords'] = $option;
										} else if ($search == 'mode') {
											$unc['cattype'] = $option;
										}
										if ($search == 'url') {
											$url .= $option;
										}
									}
								}
							}
						}
					}
					if (empty($sheel->GPC['title'])) {
						$unc['keywords'] = '{_custom_search}';
					} else {
						$unc['keywords'] = $sheel->GPC['title'];
					}
					if (empty($unc['cattype'])) {
						$unc['keywords'] = 'product';
					}
					$sheel->db->query("
	                                        INSERT INTO " . DB_PREFIX . "search_favorites
						(searchid, user_id, searchoptions, searchoptionstext, title, cattype, subscribed, added)
						VALUES
						(NULL,
						'" . $_SESSION['sheeldata']['user']['userid'] . "',
						'" . $sheel->db->escape_string($url) . "',
						'" . $sheel->db->escape_string($sheel->GPC['verbose']) . "',
						'" . $sheel->db->escape_string($unc['keywords']) . "',
						'" . $sheel->db->escape_string($unc['cattype']) . "',
						'1',
						'" . DATETIME24H . "')
	                                ", 0, null, __FILE__, __LINE__);
				}
				$sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $sheel->array2string($sheel->GPC), 'Saved search', 'A new search has been saved by ' . $_SESSION['sheeldata']['user']['username'] . '.');
			}
			$favorites = array();
			$sql = $sheel->db->query("
				SELECT searchid, user_id, searchoptions, searchoptionstext, title, cattype, subscribed, added, lastsent, lastseenids
				FROM " . DB_PREFIX . "search_favorites
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					AND cattype = 'product'
				ORDER BY searchid DESC
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$searchoptions = urldecode($res['searchoptions']);
					$searchoptions = stripslashes($searchoptions);
					if (strrchr($searchoptions, "?") == false) {
						$searchoptions = $searchoptions . '?searchid=' . $res['searchid'];
					} else {
						$searchoptions = $searchoptions . '&amp;searchid=' . $res['searchid'];
					}
					$res['searchoptionstext'] = '<span>' . stripslashes($res['searchoptionstext']) . '</span>';
					$res['action'] = '<input type="checkbox" name="searchid[]" value="' . $res['searchid'] . '" />';
					$res['cattype'] = '{_product}';
					$date1split = explode(' ', $res['added']);
					$date2split = explode('-', $date1split[0]);
					$totaldays = 30;
					$elapsed = $sheel->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
					$days = ($totaldays - $elapsed);
					if ($days < 0) { // somehow the cron job did not expire the save search subscription for this member
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "search_favorites
							SET subscribed = '0'
							WHERE searchid = '" . $res['searchid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($res['lastsent'] == '0000-00-00 00:00:00') {
							$res['lastsent'] = '{_never}';
						} else {
							$res['lastsent'] = $sheel->common->print_date_verbose($res['lastsent']);
						}
					} else {
						if ($res['subscribed']) {
							if ($res['lastsent'] == '0000-00-00 00:00:00') {
								$res['lastsent'] = '{_never}';
							} else {
								$res['lastsent'] = $sheel->common->print_date_verbose($res['lastsent']);
							}
						} else {
							if ($res['lastsent'] == '0000-00-00 00:00:00') {
								$res['lastsent'] = '{_never}';
							} else {
								$res['lastsent'] = $sheel->common->print_date_verbose($res['lastsent']);
							}
						}
					}
					$res['title'] = str_replace('"', "&#34;", $res['title']);
					$res['title'] = str_replace("'", "&#39;", $res['title']);
					$res['title'] = str_replace("<", "&#60;", $res['title']);
					$res['title'] = str_replace(">", "&#61;", $res['title']);
					$res['title'] = '<div class="bold">' . o($res['title']) . '</div>';
					$res['edit'] = '<div class="smaller gray pt-4">{_added} ' . $sheel->common->print_date($res['added'], 'F j, Y', 0, 0) . '</div>';
					$res['goto'] = '<a href="' . HTTPS_SERVER . $searchoptions . '" target="_parent">{_go_to_search_results}</a>';
					$favorites[] = $res;
				}
			} else {
				$sheel->show['no_favorites'] = true;
			}

			$sheel->GPC['cmd'] = ((isset($sheel->GPC['cmd'])) ? $sheel->GPC['cmd'] : '');
			$type = ((isset($sheel->GPC['type'])) ? $sheel->GPC['type'] : '');
			$pageurl = ((isset($sheel->GPC['pageurl'])) ? $sheel->GPC['pageurl'] : '');
			// #### form elements ##################################################
			$_SESSION['sheeldata']['user']['csrf'] = md5(uniqid(mt_rand(), true));
			$form['start'] = '<form action="' . HTTPS_SERVER . 'ajax" method="post" name="ilform" accept-charset="UTF-8"><input type="hidden" name="do" value="savesearch" /><input type="hidden" name="cmd" value="submit-save" /><input type="hidden" name="type" value="' . o($type) . '" /><input type="hidden" name="token" value="' . $_SESSION['sheeldata']['user']['csrf'] . '" /><input type="hidden" name="pageurl" value="' . o(urlencode($pageurl)) . '" />';
			$form['end'] = '</form>';
			$form['q'] = ((isset($sheel->GPC['q'])) ? urldecode($sheel->GPC['q']) : '');
			$sheel->template->load_popup('head', 'popup_header.html');
			$sheel->template->load_popup('main', 'ajax_savesearch.html');
			$sheel->template->load_popup('foot', 'popup_footer.html');
			$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'form' => $form));
			$sheel->template->parse_loop('main', array('favorites' => $favorites));
			$sheel->template->pprint('head', array('headinclude' => $sheel->template->meta['headinclude'], 'onbeforeunload' => '', 'onload' => $sheel->template->meta['onload']));
			$sheel->template->pprint('main', array());
			$sheel->template->pprint('foot', array('footinclude' => $sheel->template->meta['footinclude']));
			exit();
		}
		//echo 'Please register or sign-in before saving a search.';
		header('Location:' . HTTPS_SERVER . 'signin/?redirect=' . urlencode('ajax?do=savesearch&type=product&pageurl=' . ((isset($sheel->GPC['pageurl'])) ? o(urlencode($sheel->GPC['pageurl'])) : '')));
		exit();
	} else if ($sheel->GPC['do'] == 'paymentreminder') { // seller to buyer payment reminder for order
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($sheel->GPC['oid']) and !empty($sheel->GPC['oid']) and isset($sheel->GPC['comment']) and !empty($sheel->GPC['comment'])) {
			$sql = $sheel->db->query("
				SELECT b.buyer_id, b.orderdate, u.username, u.email
				FROM " . DB_PREFIX . "buynow_orders b
				LEFT JOIN " . DB_PREFIX . "users u ON (b.buyer_id = u.user_id)
				WHERE b.orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['oid']) . "'
					AND b.parentid = '0'
					AND b.iscancelled = '0'
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET payremind = '1',
					payreminddate = '" . DATETIME24H . "'
					WHERE orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['oid']) . "'
				");
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$sheel->email->mail = $res['email'];
				$sheel->email->slng = $sheel->language->fetch_user_slng($res['buyer_id']);
				$sheel->email->get('seller_to_buyer_payment_reminder');
				$sheel->email->set(array('{{buyer}}' => $res['username'], '{{oid}}' => $sheel->GPC['oid'], '{{orderdate}}' => $sheel->common->print_date($res['orderdate'], 'j M Y', 0, 0), '{{comments}}' => o($sheel->GPC['comment'])));
				$sheel->email->send();
				die('1');
			}
			exit();
		}
	} else if ($sheel->GPC['do'] == 'eventnotification') { // seller to winning auction event lot winners email notification
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($sheel->GPC['eventid']) and !empty($sheel->GPC['eventid']) and isset($sheel->GPC['comment']) and !empty($sheel->GPC['comment'])) {
			$sql = $sheel->db->query("
				SELECT winner_user_id, user_id
				FROM " . DB_PREFIX . "projects
				WHERE eventid = '" . intval($sheel->GPC['eventid']) . "'
					AND winner_user_id > 0
				GROUP BY winner_user_id
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$sheel->email->mail = $sheel->fetch_user('email', $res['winner_user_id']);
					$sheel->email->slng = $sheel->language->fetch_user_slng($res['winner_user_id']);
					$sheel->email->get('auction_event_bidder_notification');
					$sheel->email->set(
						array(
							'{{buyer}}' => $sheel->fetch_user('username', $res['winner_user_id']),
							'{{title}}' => $sheel->db->fetch_field(DB_PREFIX . "events", "eventid = '" . intval($sheel->GPC['eventid']) . "'", "title"),
							'{{seller}}' => $sheel->fetch_user('username', $res['user_id']),
							'{{url}}' => HTTPS_SERVER . 'auctions/lots/' . $sheel->GPC['eventid'] . '/',
							'{{comments}}' => o($sheel->GPC['comment'])
						)
					);
					$sheel->email->send();
				}
				die('1');
			}
			exit();
		}
	} else if ($sheel->GPC['do'] == 'paymentreleasereminder') { // seller to buyer payment rekease reminder for order via escrow payments
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($sheel->GPC['oid']) and !empty($sheel->GPC['oid']) and isset($sheel->GPC['comment']) and !empty($sheel->GPC['comment'])) {
			$sql = $sheel->db->query("
				SELECT b.buyer_id, b.orderdate, u.username, u.email
				FROM " . DB_PREFIX . "buynow_orders b
				LEFT JOIN " . DB_PREFIX . "users u ON (b.buyer_id = u.user_id)
				WHERE b.orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['oid']) . "'
					AND b.parentid = '0'
					AND b.iscancelled = '0'
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET releaseremind = '1',
					releasereminddate = '" . DATETIME24H . "'
					WHERE orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['oid']) . "'
				");
				$res = $sheel->db->fetch_array($sql, DB_ASSOC);
				$sheel->email->mail = $res['email'];
				$sheel->email->slng = $sheel->language->fetch_user_slng($res['buyer_id']);
				$sheel->email->get('seller_to_buyer_payment_release_reminder');
				$sheel->email->set(array('{{buyer}}' => $res['username'], '{{oid}}' => $sheel->GPC['oid'], '{{orderdate}}' => $sheel->common->print_date($res['orderdate'], 'j M Y', 0, 0), '{{comments}}' => o($sheel->GPC['comment'])));
				$sheel->email->send();
				die('1');
			}
			exit();
		}
	} else if ($sheel->GPC['do'] == 'submitpl') { // pickup location update for customer
		if (isset($sheel->GPC['message']) and isset($sheel->GPC['orderid']) and isset($sheel->GPC['month']) and isset($sheel->GPC['day']) and isset($sheel->GPC['time'])) {
			if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
				$datetime = date('Y') . '-' . $sheel->GPC['month'] . '-' . $sheel->GPC['day'] . ' ' . $sheel->GPC['time'];
				$sheel->GPC['message'] = urldecode($sheel->GPC['message']);
				$sheel->GPC['message'] = $sheel->common->js_escaped_to_xhtml_entities($sheel->GPC['message']);
				$sheel->GPC['message'] = $sheel->common->xhtml_entities_to_numeric_entities($sheel->GPC['message']);
				$sheel->GPC['message'] = mb_convert_encoding($sheel->GPC['message'], 'UTF-8', 'HTML-ENTITIES');
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET pickup_location = '" . $sheel->db->escape_string($sheel->GPC['message']) . "',
					pickup_datetime = '" . $sheel->db->escape_string($datetime) . "'
					WHERE orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['orderid']) . "'
						AND owner_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				");
				$returndate = o($sheel->GPC['message']) . ' {_on} ' . $sheel->common->print_date($datetime, 'M j', 0, 0) . ' {_at_lower} ' . $sheel->common->print_date($datetime, 'g:i a', 0, 0);
				$sheel->template->templateregistry['returndate'] = $returndate;
				echo $sheel->template->parse_template_phrases('returndate');
				exit();
				// notify buyer via email?
			}
		}
	} else if ($sheel->GPC['do'] == 'submitsf') { // seller mark order shipment as fulfilled
		if (isset($sheel->GPC['message']) and isset($sheel->GPC['orderid']) and !empty($sheel->GPC['orderid']) and isset($sheel->GPC['service']) and !empty($sheel->GPC['service'])) {
			if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
				$sheel->GPC['message'] = urldecode($sheel->GPC['message']);
				$sheel->GPC['service'] = urldecode($sheel->GPC['service']);
				$response = $sheel->buynow->markshipped($sheel->GPC['orderid'], $sheel->GPC['message'], $sheel->GPC['service']);
				if ($response) {
					$sellermarkedasshippeddate = $sheel->db->fetch_field(DB_PREFIX . "buynow_orders", "orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['orderid']) . "' AND parentid = '0'", "sellermarkedasshippeddate");
					$buyershipperid = $sheel->db->fetch_field(DB_PREFIX . "buynow_orders", "orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['orderid']) . "' AND parentid = '0'", "buyershipperid");
					$sheel->template->templateregistry['response'] = 'success|{_shipped} ' . (($sellermarkedasshippeddate == '0000-00-00 00:00:00') ? $sheel->common->print_date(DATETIME24H, 'l F j, Y', 0, 0) : $sheel->common->print_date($sellermarkedasshippeddate, 'l F j, Y', 0, 0)) . '.|' . ((!empty($sheel->GPC['message'])) ? '{_tracking_number}: ' . o($sheel->GPC['message']) : '') . '|' . $sheel->shipping->print_tracking_url($buyershipperid, o(strip_tags($sheel->GPC['message'])), true);
					echo $sheel->template->parse_template_phrases('response');
				} else {
					$sheel->template->templateregistry['response'] = 'failed|' . $response;
					echo $sheel->template->parse_template_phrases('response');
				}
				exit();
			}
		}
	} else if ($sheel->GPC['do'] == 'submitud') { // seller mark download as approved
		if (isset($sheel->GPC['orderid']) and !empty($sheel->GPC['orderid'])) {
			if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
				$sheel->GPC['orderid'] = urldecode($sheel->GPC['orderid']);
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET sellerapprovedownload = '1',
					sellermarkedasshippeddate = '" . DATETIME24H . "'
					WHERE orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['orderid']) . "'
						AND owner_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				");
				$buyerid = $sheel->db->fetch_field(DB_PREFIX . "buynow_orders", "orderidpublic = '" . $sheel->db->escape_string($sheel->GPC['orderid']) . "' AND parentid = '0' AND owner_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'", "buyer_id");
				if ($buyerid > 0) {
					$existing = array(
						'{{buyer}}' => $sheel->fetch_user('username', $buyerid),
						'{{buyerfullname}}' => $sheel->fetch_user('fullname', $buyerid),
						'{{seller}}' => $sheel->fetch_user('username', $_SESSION['sheeldata']['user']['userid']),
						'{{sellerfullname}}' => $sheel->fetch_user('fullname', $_SESSION['sheeldata']['user']['userid']),
						'{{orderid}}' => $sheel->GPC['orderid'],
						'{{approvedate}}' => $sheel->common->print_date(DATETIME24H, 'l F j, Y', 0, 0),
					);
					$sheel->email->mail = $sheel->fetch_user('email', $buyerid);
					$sheel->email->slng = $sheel->language->fetch_user_slng($buyerid);
					$sheel->email->get('seller_marked_order_download_unlocked');
					$sheel->email->set($existing);
					$sheel->email->send();
				}
				$returndate = '{_digital_download_unlocked} ' . $sheel->common->print_date(DATETIME24H, 'l F j, Y', 0, 0);
				$sheel->template->templateregistry['returndate'] = $returndate;
				echo $sheel->template->parse_template_phrases('returndate');
				exit();
			}
		}
	} else if ($sheel->GPC['do'] == 'updateblocking') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and !empty($sheel->GPC['data'])) {
			$response = json_decode($sheel->GPC['data'], true);
			$sql = $sheel->db->query("
				SELECT id
				FROM " . DB_PREFIX . "blocking
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
			");
			if ($sheel->db->num_rows($sql) == 0) {
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "blocking
					(id, user_id, require_limitshipto, require_limitmaxunpaidstrikes, maxunpaidstrikescount, maxunpaidstrikesduration, require_limitminfbscore, minfbscore, require_limitmaxviolationreports, maxviolationreportscount, maxviolationreportsduration, require_limitmaxnumberpurchases, maxnumberpurchases, require_limitblocked, require_limitpaymentprofile, require_limitcancelledorders, maxnumbercancelledorders, maxnumbercancelledordersduration)
					VALUES (
					NULL,
					'" . $_SESSION['sheeldata']['user']['userid'] . "',
					'" . intval($response['require_limitshipto']) . "',
					'" . intval($response['require_limitmaxunpaidstrikes']) . "',
					'" . intval($response['maxunpaidstrikescount']) . "',
					'" . intval($response['maxunpaidstrikesduration']) . "',
					'" . intval($response['require_limitminfbscore']) . "',
					'" . $sheel->db->escape_string($response['minfbscore']) . "',
					'" . intval($response['require_limitmaxviolationreports']) . "',
					'" . intval($response['maxviolationreportscount']) . "',
					'" . intval($response['maxviolationreportsduration']) . "',
					'" . intval($response['require_limitmaxnumberpurchases']) . "',
					'" . intval($response['maxnumberpurchases']) . "',
					'" . intval($response['require_limitblocked']) . "',
					'" . intval($response['require_limitpaymentprofile']) . "',
					'" . intval($response['require_limitcancelledorders']) . "',
					'" . intval($response['maxnumbercancelledorders']) . "',
					'" . intval($response['maxnumbercancelledordersduration']) . "')
				");
				die('1');
			}
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "blocking
				SET require_limitshipto = '" . intval($response['require_limitshipto']) . "',
				require_limitmaxunpaidstrikes = '" . intval($response['require_limitmaxunpaidstrikes']) . "',
				maxunpaidstrikescount = '" . intval($response['maxunpaidstrikescount']) . "',
				maxunpaidstrikesduration = '" . intval($response['maxunpaidstrikesduration']) . "',
				require_limitminfbscore = '" . intval($response['require_limitminfbscore']) . "',
				minfbscore = '" . $sheel->db->escape_string($response['minfbscore']) . "',
				require_limitmaxviolationreports = '" . intval($response['require_limitmaxviolationreports']) . "',
				maxviolationreportscount = '" . intval($response['maxviolationreportscount']) . "',
				maxviolationreportsduration = '" . intval($response['maxviolationreportsduration']) . "',
				require_limitmaxnumberpurchases = '" . intval($response['require_limitmaxnumberpurchases']) . "',
				maxnumberpurchases = '" . intval($response['maxnumberpurchases']) . "',
				require_limitblocked = '" . intval($response['require_limitblocked']) . "',
				require_limitpaymentprofile = '" . intval($response['require_limitpaymentprofile']) . "',
				require_limitcancelledorders = '" . intval($response['require_limitcancelledorders']) . "',
				maxnumbercancelledorders = '" . intval($response['maxnumbercancelledorders']) . "',
				maxnumbercancelledordersduration = '" . intval($response['maxnumbercancelledordersduration']) . "'
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					AND id = '" . $res['id'] . "'
			");
			die('1');
		}
	} else if ($sheel->GPC['do'] == 'fetchblocking') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$sql = $sheel->db->query("
				SELECT require_limitshipto, require_limitmaxunpaidstrikes, maxunpaidstrikescount, maxunpaidstrikesduration, require_limitminfbscore, minfbscore, require_limitmaxviolationreports, maxviolationreportscount, maxviolationreportsduration, require_limitmaxnumberpurchases, maxnumberpurchases, require_limitblocked, require_limitpaymentprofile, require_limitcancelledorders, maxnumbercancelledorders, maxnumbercancelledordersduration
				FROM " . DB_PREFIX . "blocking
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$response = $sheel->db->fetch_array($sql, DB_ASSOC);
				die(json_encode(array('response' => $response)));
			}
		}
		die(json_encode(array('response' => '')));
	} else if ($sheel->GPC['do'] == 'updateshippingpromotion') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and !empty($sheel->GPC['data'])) {
			$response = json_decode($sheel->GPC['data'], true);
			$iscrypto = ((isset($sheel->currency->currencies[$response['currencyid']]['iscrypto']) and $sheel->currency->currencies[$response['currencyid']]['iscrypto']) ? true : false);
			$responsex = '';
			if ($response['type'] == '0') {
				$responsex = '{_not_offered}';
			} else if ($response['type'] == '1') {
				$responsex = '{_spend_x_on_2_items_shipping_x::' . $sheel->currency->format($response['spendlimit'], $response['currencyid']) . '::' . $response['spendlimitdiscount'] . '}';
			} else if ($response['type'] == '2') {
				$responsex = '{_spend_x_on_1_or_more_items_shipping_free::' . $sheel->currency->format($response['spendcap'], $response['currencyid']) . '}';
			} else if ($response['type'] == '3') {
				$responsex = '{_spend_no_more_than_x_on_single_order::' . $sheel->currency->format($response['shippaycap'], $response['currencyid']) . '}';
			} else if ($response['type'] == '4') {
				$responsex = '{_buy_x_or_more_items_shipping_is_x::' . $response['buylimit'] . '::' . $response['buylimitdiscount'] . '}';
			} else if ($response['type'] == '5') {
				$responsex = '{_buy_multiple_items_and_pay_the_x_ship_price::' . $response['shippaymode'] . '}';
			} else if ($response['type'] == '6') {
				$responsex = '{_buy_multiple_items_and_pay_x_additional_price::' . $sheel->currency->format($response['additionalfeeshippaycap'], $response['currencyid']) . '}';
				$response['shippaycap'] = $response['additionalfeeshippaycap'];
			}
			$sheel->template->templateregistry['responsex'] = $responsex;
			$responsex = $sheel->template->parse_template_phrases('responsex');
			$response['crypto_spendlimit'] = $response['spendlimit'];
			$response['crypto_spendcap'] = $response['spendcap'];
			$response['crypto_shippaycap'] = $response['shippaycap'];
			if ($iscrypto and isset($sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcryptocurrency']]['rate'])) { // x.xxxxxxxx -> x.xx
				$response['spendlimit'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcurrency'], $response['spendlimit'], $sheel->config['globalserverlocale_defaultcryptocurrency']);
				$response['spendcap'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcurrency'], $response['spendcap'], $sheel->config['globalserverlocale_defaultcryptocurrency']);
				$response['shippaycap'] = $sheel->currency->convert_currency($sheel->config['globalserverlocale_defaultcurrency'], $response['shippaycap'], $sheel->config['globalserverlocale_defaultcryptocurrency']);
			} else {
				$response['crypto_spendlimit'] = 0;
				$response['crypto_spendcap'] = 0;
				$response['crypto_shippaycap'] = 0;
			}
			$sql = $sheel->db->query("
				SELECT id
				FROM " . DB_PREFIX . "shipping_promotion
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
			");
			if ($sheel->db->num_rows($sql) <= 0) {
				$sheel->db->query("
					INSERT INTO " . DB_PREFIX . "shipping_promotion
					(id, user_id, type, crypto_spendlimit, spendlimit, spendlimitdiscount, crypto_spendcap, spendcap, crypto_shippaycap, shippaycap, buylimit, buylimitdiscount, shippaymode)
					VALUES (
					NULL,
					'" . $_SESSION['sheeldata']['user']['userid'] . "',
					'" . intval($response['type']) . "',
					'" . $sheel->db->escape_string($response['crypto_spendlimit']) . "',
					'" . $sheel->db->escape_string($response['spendlimit']) . "',
					'" . $sheel->db->escape_string($response['spendlimitdiscount']) . "',
					'" . $sheel->db->escape_string($response['crypto_spendcap']) . "',
					'" . $sheel->db->escape_string($response['spendcap']) . "',
					'" . $sheel->db->escape_string($response['crypto_shippaycap']) . "',
					'" . $sheel->db->escape_string($response['shippaycap']) . "',
					'" . intval($response['buylimit']) . "',
					'" . $sheel->db->escape_string($response['buylimitdiscount']) . "',
					'" . $sheel->db->escape_string($response['shippaymode']) . "')
				");
				die(json_encode(array('response' => $responsex)));
			}
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "shipping_promotion
				SET type = '" . intval($response['type']) . "',
				crypto_spendlimit = '" . $sheel->db->escape_string($response['crypto_spendlimit']) . "',
				spendlimit = '" . $sheel->db->escape_string($response['spendlimit']) . "',
				spendlimitdiscount = '" . $sheel->db->escape_string($response['spendlimitdiscount']) . "',
				crypto_spendcap = '" . $sheel->db->escape_string($response['crypto_spendcap']) . "',
				spendcap = '" . $sheel->db->escape_string($response['spendcap']) . "',
				crypto_shippaycap = '" . $sheel->db->escape_string($response['crypto_shippaycap']) . "',
				shippaycap = '" . $sheel->db->escape_string($response['shippaycap']) . "',
				buylimit = '" . intval($response['buylimit']) . "',
				buylimitdiscount = '" . $sheel->db->escape_string($response['buylimitdiscount']) . "',
				shippaymode = '" . $sheel->db->escape_string($response['shippaymode']) . "'
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
					AND id = '" . $res['id'] . "'
			");
			die(json_encode(array('response' => $responsex)));
		}
		die(json_encode(array('response' => '')));
	} else if ($sheel->GPC['do'] == 'fetchshippromotions') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			$sql = $sheel->db->query("
				SELECT type, crypto_spendlimit, spendlimit, spendlimitdiscount, crypto_spendcap, spendcap, crypto_shippaycap, shippaycap, buylimit, buylimitdiscount, shippaymode
				FROM " . DB_PREFIX . "shipping_promotion
				WHERE user_id = '" . $_SESSION['sheeldata']['user']['userid'] . "'
				LIMIT 1
			");
			if ($sheel->db->num_rows($sql) > 0) {
				$response = $sheel->db->fetch_array($sql, DB_ASSOC);
				$iscrypto = ((isset($sheel->currency->currencies[$sheel->GPC['currencyid']]['iscrypto']) and $sheel->currency->currencies[$sheel->GPC['currencyid']]['iscrypto']) ? true : false);
				$responsex = '';
				if ($response['type'] == '0') {
					$responsex = '{_not_offered}';
				} else if ($response['type'] == '1') {
					$responsex = '{_spend_x_on_2_items_shipping_x::' . (($iscrypto) ? $response['crypto_spendlimit'] : $response['spendlimit']) . '::' . $response['spendlimitdiscount'] . '}';
				} else if ($response['type'] == '2') {
					$responsex = '{_spend_x_on_1_or_more_items_shipping_free::' . (($iscrypto) ? $response['crypto_spendcap'] : $response['spendcap']) . '}';
				} else if ($response['type'] == '3') {
					$responsex = '{_spend_no_more_than_x_on_single_order::' . (($iscrypto) ? $response['crypto_shippaycap'] : $response['shippaycap']) . '}';
				} else if ($response['type'] == '4') {
					$responsex = '{_buy_x_or_more_items_shipping_is_x::' . $response['buylimit'] . '::' . $response['buylimitdiscount'] . '}';
				} else if ($response['type'] == '5') {
					$responsex = '{_buy_multiple_items_and_pay_the_x_ship_price::' . $response['shippaymode'] . '}';
				} else if ($response['type'] == '6') {
					$responsex = '{_buy_multiple_items_and_pay_x_additional_price::' . $response['shippaycap'] . '}';
				}
				$sheel->template->templateregistry['responsex'] = $responsex;
				$responsex = $sheel->template->parse_template_phrases('responsex');
				$response['response'] = $responsex;
				die(json_encode(array('response' => $response)));
			} else {
				$responsex = '{_not_offered}';
				$sheel->template->templateregistry['responsex'] = $responsex;
				$responsex = $sheel->template->parse_template_phrases('responsex');
				$response['response'] = $responsex;
				$response['type'] = '0';
				$response['spendlimit'] = '';
				$response['spendlimitdiscount'] = '';
				$response['spendcap'] = '';
				$response['shippaycap'] = '';
				$response['buylimit'] = '';
				$response['buylimitdiscount'] = '';
				$response['shippaymode'] = '';
				die(json_encode(array('response' => $response)));
			}
		}
		die(json_encode(array('response' => '')));
	} else if ($sheel->GPC['do'] == 'bulkmailer') { // admin panel bulk mailer
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if ($sheel->show['ADMINCP_TEST_MODE']) {
				die(json_encode(array('response' => '0')));
			}
			$sheel->template->meta['areatitle'] = '{_sending_bulk_email}';
			$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_sending_bulk_email}';
			$from = trim($sheel->GPC['from']);
			$subject = o(urldecode($sheel->GPC['subject']));
			$message = urldecode($sheel->GPC['body']);
			$messagehtml = urldecode($sheel->GPC['bodyhtml']);
			$plan = false;
			$subscriptionid = '';
			if ($sheel->GPC['subscriptionid'] != '-1') {
				$plan = true;
				$subscriptionid = $sheel->GPC['subscriptionid'];
			}
			if (isset($sheel->GPC['testmode']) and $sheel->GPC['testmode'] == 'true' and isset($sheel->GPC['testemail']) and !empty($sheel->GPC['testemail'])) { // staff sending test email
				$sheel->template->meta['areatitle'] = '{_bulk_email_test_message}';
				$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_bulk_email_test_message}';
				$find = array(
					'{{username}}' => $_SESSION['sheeldata']['user']['username'],
					'{{firstname}}' => $_SESSION['sheeldata']['user']['firstname'],
					'{{lastname}}' => $_SESSION['sheeldata']['user']['lastname'],
					'{{plantitle}}' => 'Test Plan Title',
					'{{planprice}}' => $sheel->currency->format(0),
					'{{planlinkpayment}}' => HTTPS_SERVER . 'accounting/billing-payments/',
					'{{planbillingcycle}}' => '',
					'{{twitter}}' => $sheel->config['globalserversettings_twitterurl'],
					'{{facebook}}' => $sheel->config['globalserversettings_facebookurl'],
					'{{linkedin}}' => $sheel->config['globalserversettings_linkedin'],
					'{{youtube}}' => $sheel->config['globalserversettings_youtubeurl'],
					'{{instagram}}' => $sheel->config['globalserversettings_instaurl'],
					'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/',
					'{{https_server}}' => HTTPS_SERVER
				);
				$message = str_replace(array_keys($find), $find, $message);
				$messagehtml = str_replace(array_keys($find), $find, $messagehtml);
				$subject = str_replace(array_keys($find), $find, $subject);
				$sheel->email->mail = o($sheel->GPC['testemail']);
				$sheel->email->from = $from;
				$sheel->email->subject = $subject;
				$sheel->email->message = $message;
				$sheel->email->messagehtml = $messagehtml;
				$sheel->email->type = 'global';
				$sheel->email->dohtml = ((!empty($messagehtml)) ? 1 : 0);
				$sheel->email->send();
				die(json_encode(array('response' => '1')));
			} else { // admin dispatching bulk mail to customers
				if ($subscriptionid == 'active') { // sending to only active
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'active'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'suspended') { // sending to only suspended
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'suspended'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'cancelled') { // sending to only cancelled users
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'cancelled'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'unverified') { // sending to only unverified email
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'unverified'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'banned') { // sending to only banned
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'banned'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'moderated') { // sending to only moderated/unapproved
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE status = 'moderated'
					", 0, null, __FILE__, __LINE__);
				} else if ($subscriptionid == 'orphaned') { // in a specific membership..
					$sql = $sheel->db->query("
						SELECT u.user_id, s.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, s.cost
						FROM " . DB_PREFIX . "subscription_user u
						LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
						WHERE u.active = 'no'
							AND s.type = 'product'
					", 0, null, __FILE__, __LINE__);
				} else if ($plan and $subscriptionid > 0) { // in a specific membership..
					$sql = $sheel->db->query("
						SELECT u.user_id, s.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, s.cost
						FROM " . DB_PREFIX . "subscription_user u
						LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
						WHERE u.subscriptionid = '" . intval($subscriptionid) . "'
							AND u.active = 'yes'
							AND s.type = 'product'
					", 0, null, __FILE__, __LINE__);
				} else { // sending to everyone
					$sql = $sheel->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
					", 0, null, __FILE__, __LINE__);
				}
				// send email..
				if ($sheel->db->num_rows($sql) > 0) {
					while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) { // to each customer
						$res['title'] = ((isset($res['title'])) ? $res['title'] : '');
						$res['cost'] = ((isset($res['cost'])) ? $sheel->currency->format($res['cost']) : '');
						$sql2 = $sheel->db->query("
							SELECT username, email, first_name, last_name
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res['user_id'] . "'
								AND emailnotify = '1'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($sheel->db->num_rows($sql2) > 0) {
							$res2 = $sheel->db->fetch_array($sql2, DB_ASSOC);
							$find = array(
								'{{username}}' => $res2['username'],
								'{{firstname}}' => ucfirst($res2['first_name']),
								'{{lastname}}' => ucfirst($res2['last_name']),
								'{{plantitle}}' => $res['title'],
								'{{planprice}}' => $res['cost'],
								'{{planlinkpayment}}' => HTTPS_SERVER . 'accounting/billing-payments/',
								'{{planbillingcycle}}' => '',
								'{{twitter}}' => $sheel->config['globalserversettings_twitterurl'],
								'{{facebook}}' => $sheel->config['globalserversettings_facebookurl'],
								'{{linkedin}}' => $sheel->config['globalserversettings_linkedin'],
								'{{youtube}}' => $sheel->config['globalserversettings_youtubeurl'],
								'{{instagram}}' => $sheel->config['globalserversettings_instaurl'],
								'{{imagefolder}}' => HTTPS_SERVER . 'application/uploads/attachments/meta/',
								'{{https_server}}' => HTTPS_SERVER
							);
							$message = str_replace(array_keys($find), $find, $message);
							$messagehtml = str_replace(array_keys($find), $find, $messagehtml);
							$subject = str_replace(array_keys($find), $find, $subject);
							$sheel->email->mail = $res2['email'];
							$sheel->email->from = $from;
							$sheel->email->subject = $subject;
							$sheel->email->message = $message;
							$sheel->email->messagehtml = $messagehtml;
							$sheel->email->type = 'global';
							$sheel->email->dohtml = ((!empty($messagehtml)) ? 1 : 0);
							$sheel->email->send();
						}
					}
					die(json_encode(array('response' => '1')));
				}
			}
		}
		die(json_encode(array('response' => '0')));
	} else if ($sheel->GPC['do'] == 'upgradelog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = UPGRADELOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'paymentlog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = PAYMENTLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'ldaplog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = LDAPLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'shippinglog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = SHIPPINGLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'smtplog') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$filename = SMTPLOG;
			$response = array();
			$response['log'] = file_get_contents($filename);
			die(json_encode($response));
		}
	} else if ($sheel->GPC['do'] == 'forceautoupdate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$sheel->liveupdate->activate_cron();
			die('success|');
		}
	} else if ($sheel->GPC['do'] == 'testldap') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$response = $sheel->ldap->admincp_test();
			die($response['response'] . '|' . $response['message']);
		}
	} else if ($sheel->GPC['do'] == 'testdomain') {
		$domain = isset($sheel->GPC['domain']) ? $sheel->GPC['domain'] : $_SERVER['SERVER_NAME'];
		$ns = dns_get_record($domain, DNS_NS);
		$nscorrect1 = $nscorrect2 = false;
		if (is_array($ns) and count($ns) > 0) {
			foreach ($ns as $key => $info) {
				if (isset($info['type']) and $info['type'] == 'NS') {
					if (isset($info['target']) and $info['target'] == 'ns1.serverxo.com') {
						$nscorrect1 = true;
					} else if (isset($info['target']) and $info['target'] == 'ns2.serverxo.com') {
						$nscorrect2 = true;
					}
				}
			}
		}
		if ($nscorrect1 and $nscorrect2) {
			echo '<h2 class="type--success sst">Congratulations! Your domain &quot;' . o($domain) . '&quot; is connected to the sheel Cloud!</h2>';
			echo '<p class="st type--subdued">Now that you\'re connected it\'s time to approve and get your domain talking to your marketplace.  Click approve to begin this process.</p>';
			echo '<p class="st type--subdued"><button type="button" class="btn js-btn-primary js-btn-loadable has-loading btn-primary" onclick="connect_domain(\'' . LICENSEKEY . '\', \'' . o($domain) . '\', \'' . $sheel->config['billing_endpoint'] . '\')">Approve ' . o($domain) . '</button>&nbsp;<span id="connectdomainresult"></span></p>';
		} else {
			echo '<h2 class="type--danger sst">DNS server not connected for ' . o($domain) . '</h2><p class="st type--subdued">To connect, point your ' . o($domain) . ' domain nameservers to the following and try again: <br /><br />1. NS1.SERVERXO.COM<br />2. NS2.SERVERXO.COM</p>';
			echo '<p class="sst type--subdued"><a href="https://community.sheel.com" target="_blank">How do I change my domain nameservers?</p>';
		}
		exit();
	} else if ($sheel->GPC['do'] == 'sefurlcollide') {
		if (isset($sheel->GPC['slng']) and isset($sheel->GPC['sefurl']) and isset($sheel->GPC['cid'])) {
			if ($sheel->GPC['cid'] <= 0) { // new category
				$sql = $sheel->db->query("
					SELECT cid
					FROM " . DB_PREFIX . "categories
					WHERE seourl_" . $sheel->db->escape_string($sheel->GPC['slng']) . " = '" . $sheel->db->escape_string($sheel->GPC['sefurl']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) > 0) {
					die('0');
				}
			} else { // updating category
				$sql = $sheel->db->query("
					SELECT cid
					FROM " . DB_PREFIX . "categories
					WHERE seourl_" . $sheel->db->escape_string($sheel->GPC['slng']) . " = '" . $sheel->db->escape_string($sheel->GPC['sefurl']) . "'
						AND cid != '" . intval($sheel->GPC['cid']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) > 0) {
					die('0');
				}
			}
			die('1'); // pass
		}
		die('0');
	} else if ($sheel->GPC['do'] == 'acploadtemplate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$html = $tabshtml = '';
			if ($sheel->GPC['view'] == 'client') {
				if ($sheel->GPC['type'] == 'css') {
					$html = file_get_contents(DIR_CSS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$html = file_get_contents(DIR_VIEWS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'js') {
					$html = '';
				}
			} else if ($sheel->GPC['view'] == 'admin') {
				if ($sheel->GPC['type'] == 'css') {
					$html = file_get_contents(DIR_CSS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$html = file_get_contents(DIR_VIEWS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'js') {
					$html = '';
				}
			}
			$newtab[] = array(
				'styleid' => $sheel->GPC['styleidx'],
				'type' => $sheel->GPC['type'],
				'view' => $sheel->GPC['view'],
				'filename' => $sheel->GPC['filename'],
				'class' => 'active'
			);
			$existingtabs = ((isset($_COOKIE[COOKIE_PREFIX . 'acpeditor'])) ? unserialize(base64_decode($_COOKIE[COOKIE_PREFIX . 'acpeditor'])) : array());
			if (is_array($existingtabs) and count($existingtabs) > 0) {
				$existingtabs = array_reverse($existingtabs);
				foreach ($existingtabs as $key => $array) { // disable active class on other tabs
					$existingtabs[$key]['class'] = (($array['styleid'] == $sheel->GPC['styleidx']) ? '' : $array['class']);
					$existingtabs[$key]['tab'] = $array['tab'];
				}
				$newtab[0]['tab'] = $sheel->GPC['tab'];
				$existingtabs = array_reverse($existingtabs);
				$tabs = array_merge($newtab, $existingtabs);
			} else {
				$newtab[0]['tab'] = $sheel->GPC['tab'];
				$tabs = $newtab;
			}
			$existingtabs = $tabs;
			if (is_array($existingtabs) and count($existingtabs) > 0) {
				$existingtabs = array_reverse($existingtabs);
				foreach ($existingtabs as $key => $array) {
					if ($array['styleid'] == $sheel->GPC['styleidx']) {
						$tabshtml .= '<li class="template-editor-tab ' . $array['class'] . '" id="editor-tab-' . $array['tab'] . '"><a class="template-editor-tab-filename" onclick="switch_tab(\'' . $array['tab'] . '\', \'' . $array['type'] . '\', \'' . $array['view'] . '\', \'' . $array['styleid'] . '\', \'' . $array['filename'] . '\')" id="editor-tab-filename-' . $array['tab'] . '">' . $array['view'] . '/' . $array['filename'] . '</a><a class="template-editor-close-tab" onclick="close_tab(\'' . $array['tab'] . '\', \'' . $array['type'] . '\', \'' . $array['view'] . '\', \'' . $array['styleid'] . '\', \'' . $array['filename'] . '\')">x</a></li>';
					}
				}
			}
			set_cookie('acpeditor', base64_encode(serialize($tabs)), true);
			die(json_encode(array('response' => 1, 'template' => $html, 'tabs' => $tabshtml)));
		} else {
			die(json_encode(array('response' => -1)));
		}
	} else if ($sheel->GPC['do'] == 'acpsavetemplate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if ($sheel->GPC['view'] == 'client') {
				if ($sheel->GPC['type'] == 'css') {
					$file = DIR_CSS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename'];
					$file2 = DIR_CSS . $sheel->GPC['styleidx'] . '/client/' . str_replace('.css', '.min.css', $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$file = DIR_VIEWS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename'];
					$file2 = "";
				} else if ($sheel->GPC['type'] == 'js') {
					$file = "";
					$file2 = "";
				}
			} else if ($sheel->GPC['view'] == 'admin') {
				if ($sheel->GPC['type'] == 'css') {
					$file = DIR_CSS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename'];
					$file2 = DIR_CSS . $sheel->GPC['styleidx'] . '/admin/' . str_replace('.css', '.min.css', $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$file = DIR_VIEWS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename'];
					$file2 = "";
				} else if ($sheel->GPC['type'] == 'js') {
					$file = "";
					$file2 = "";
				}
			}
			$filelink = fopen($file, 'w+') or die(json_encode(array('response' => 0)));
			$sheel->GPC['content'] = str_replace(array('<else/>', '<else>'), '<else />', $sheel->GPC['content']);
			fwrite($filelink, $sheel->GPC['content']);
			fclose($filelink);
			if (!empty($file2)) { // minify document
				if ($sheel->GPC['type'] == 'css') {
					$sheel->GPC['content'] = $sheel->minify->minify_css($sheel->GPC['content']);
				} else if ($sheel->GPC['type'] == 'js') {
					$sheel->GPC['content'] = $sheel->minify->minify_js($sheel->GPC['content']);
				}
				$filelink = fopen($file2, 'w+') or die(json_encode(array('response' => 0)));
				fwrite($filelink, $sheel->GPC['content']);
				fclose($filelink);
			}
			die(json_encode(array('response' => 1)));
		} else {
			die(json_encode(array('response' => -1)));
		}
	} else if ($sheel->GPC['do'] == 'acpclosetemplate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$n = 0;
			$tabshtml = '';
			$switchtotab = '';
			$existingtabs = $tabs = array();
			$existingtabsx = ((isset($_COOKIE[COOKIE_PREFIX . 'acpeditor'])) ? unserialize(base64_decode($_COOKIE[COOKIE_PREFIX . 'acpeditor'])) : array());
			if (is_array($existingtabsx) and count($existingtabsx) > 0) {
				$t = 1;
				$cp = 0;
				$closeposition = 0;
				$totaltabs = 0;
				$existingtabsx = array_reverse($existingtabsx);
				foreach ($existingtabsx as $key => $array) {
					$cp++;
					if ($array['tab'] == $sheel->GPC['tab']) {
						$closeposition = $cp;
					}
					if ($array['tab'] != $sheel->GPC['tab']) {
						if ($array['styleid'] == $sheel->GPC['styleidx']) {
							$totaltabs++;
						}
					}
				}
				if ($closeposition > 1 and $totaltabs > 0) {
					$closeposition = ($closeposition - 1);
				} else {
					$closeposition = 1;
				}
				foreach ($existingtabsx as $key => $array) {
					if ($array['tab'] != $sheel->GPC['tab']) {
						if ($array['styleid'] == $sheel->GPC['styleidx']) {
							$existingtabs[$key]['class'] = (($t == $closeposition) ? 'active' : '');
							$existingtabs[$key]['tab'] = $array['tab'];
							$existingtabs[$key]['type'] = $array['type'];
							$existingtabs[$key]['view'] = $array['view'];
							$existingtabs[$key]['styleid'] = $array['styleid'];
							$existingtabs[$key]['filename'] = $array['filename'];
							if ($t == $closeposition) { // we'll switch to this tab..
								$switchtotab = $array['tab'];
							}
							$t++;
						} else {
							$existingtabs[$key]['class'] = $array['class'];
							$existingtabs[$key]['tab'] = $array['tab'];
							$existingtabs[$key]['type'] = $array['type'];
							$existingtabs[$key]['view'] = $array['view'];
							$existingtabs[$key]['styleid'] = $array['styleid'];
							$existingtabs[$key]['filename'] = $array['filename'];
						}
					}
				}
				$n = $t;
				$existingtabs = array_reverse($existingtabs);
				$tabs = $existingtabs;
			}
			if (count($existingtabs) > 0) {
				$existingtabs = array_reverse($existingtabs);
				foreach ($existingtabs as $key => $array) {
					if ($array['styleid'] == $sheel->GPC['styleidx']) {
						$tabshtml .= '<li class="template-editor-tab ' . $array['class'] . '" id="editor-tab-' . $array['tab'] . '"><a class="template-editor-tab-filename" onclick="switch_tab(\'' . $array['tab'] . '\', \'' . $array['type'] . '\', \'' . $array['view'] . '\', \'' . $array['styleid'] . '\', \'' . $array['filename'] . '\')" id="editor-tab-filename-' . $array['tab'] . '">' . $array['view'] . '/' . $array['filename'] . '</a><a class="template-editor-close-tab" onclick="close_tab(\'' . $array['tab'] . '\', \'' . $array['type'] . '\', \'' . $array['view'] . '\', \'' . $array['styleid'] . '\', \'' . $array['filename'] . '\')">x</a></li>';
					}
				}
				set_cookie('acpeditor', base64_encode(serialize($tabs)), true);
			} else {
				set_cookie('acpeditor', '', false);
			}
			die(json_encode(array('response' => 1, 'tabs' => $tabshtml, 'switchto' => ((!empty($switchtotab)) ? $switchtotab : ''))));
		} else {
			die(json_encode(array('response' => -1)));
		}
	} else if ($sheel->GPC['do'] == 'acpswitchtemplate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			die(json_encode(array('response' => 1, 'switchto' => $sheel->GPC['tab'])));
		} else {
			die(json_encode(array('response' => -1)));
		}
	} else if ($sheel->GPC['do'] == 'acpcomparetemplate') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if ($sheel->GPC['view'] == 'client') {
				if ($sheel->GPC['type'] == 'css') {
					$left_template = file_get_contents(DIR_CSS . '/1/client/' . $sheel->GPC['filename']);
					$right_template = file_get_contents(DIR_CSS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$left_template = file_get_contents(DIR_VIEWS . '/1/client/' . $sheel->GPC['filename']);
					$right_template = file_get_contents(DIR_VIEWS . $sheel->GPC['styleidx'] . '/client/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'js') {
					$left_template = $right_template = '';
				}
			} else if ($sheel->GPC['view'] == 'admin') {
				if ($sheel->GPC['type'] == 'css') {
					$left_template = file_get_contents(DIR_CSS . '/1/admin/' . $sheel->GPC['filename']);
					$right_template = file_get_contents(DIR_CSS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'html') {
					$left_template = file_get_contents(DIR_VIEWS . '/1/admin/' . $sheel->GPC['filename']);
					$right_template = file_get_contents(DIR_VIEWS . $sheel->GPC['styleidx'] . '/admin/' . $sheel->GPC['filename']);
				} else if ($sheel->GPC['type'] == 'js') {
					$left_template = $right_template = '';
				}
			}
			$entries = & $sheel->diff->fetch_diff($left_template, $right_template);
			$html = '<table width="100%" cellpadding="12" cellspacing="0">';
			$html .= "<tr><td><h3><strong>Original</strong></h3></td><td><h3><strong>Current</strong> (" . $sheel->GPC['filename'] . ")</h3></td></tr>";
			foreach ($entries as $diff_entry) {
				// possible classes: unchanged, notext, deleted, added, changed
				$html .= "<tr>\n\t";
				$html .= '<td width="50%" valign="top" class="diff-' . $diff_entry->fetch_data_old_class() . '" dir="ltr">' . $diff_entry->prep_diff_text($diff_entry->fetch_data_old(), 1) . "</td>\n\t";
				$html .= '<td width="50%" valign="top" class="diff-' . $diff_entry->fetch_data_new_class() . '" dir="ltr">' . $diff_entry->prep_diff_text($diff_entry->fetch_data_new(), 1) . "</td>\n";
				$html .= "</tr>\n\n";
			}
			$html .= "<tr><td class=\"diff-deleted\" align=\"center\" width=\"50%\">Removed from current</td><td class=\"diff-notext\">&nbsp;</td></tr>\n";
			$html .= "<tr><td class=\"diff-changed\" colspan=\"2\" align=\"center\">Changed from original</td></tr>\n";
			$html .= "<tr><td class=\"diff-notext\" width=\"50%\">&nbsp;</td><td class=\"diff-added\" align=\"center\">New</td></tr></table>\n";
			die(json_encode(array('response' => 1, 'html' => $html)));
		} else {
			die(json_encode(array('response' => -1)));
		}
	} else if ($sheel->GPC['do'] == 'ddu') { // delete digital upload attachment (post/update new item)
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if ($sheel->attachment->remove_attachment(intval($sheel->GPC['aid']), $_SESSION['sheeldata']['user']['userid'])) {
				die(json_encode(array('response' => 1)));
			}
		}
		die(json_encode(array('response' => 0)));
	} else if ($sheel->GPC['do'] == 'dpu') { // delete digital upload attachment (post/update new item)
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if ($sheel->attachment->remove_attachment(intval($sheel->GPC['aid']), $_SESSION['sheeldata']['user']['userid'])) {
				die(json_encode(array('response' => 1)));
			}
		}
		die(json_encode(array('response' => 0)));
	} else if ($sheel->GPC['do'] == 'searchfacets') {
		$html = '';
		$sql = $sheel->db->query("
			SELECT questionid, question_" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['slng']) . " AS question
			FROM " . DB_PREFIX . "product_questions
			WHERE cid = '" . intval($sheel->GPC['cid']) . "'
				AND inputtype = 'pulldown' OR inputtype = 'multiplechoice'
			GROUP BY question_" . $sheel->db->escape_string($_SESSION['sheeldata']['user']['slng']) . "
		");
		if ($sheel->db->num_rows($sql) > 0) {
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$sql2 = $sheel->db->query("
					SELECT answerid, questionid, answer, optionid
					FROM " . DB_PREFIX . "product_answers
					WHERE questionid = '" . $res['questionid'] . "'
					GROUP BY optionid
				");
				if ($sheel->db->num_rows($sql2) > 0) {
					$html .= '<div class="pb-5 bold">' . o($res['question']) . '</div><div class="pb-14">';
					while ($res2 = $sheel->db->fetch_array($sql2, DB_ASSOC)) {
						$html .= '<div class="pb-4"><label for="qidx-' . $res2['questionid'] . '-' . $res2['optionid'] . '"><input onclick="append_qid(\'' . $res2['questionid'] . '.' . $res2['optionid'] . '\')" type="checkbox" id="qidx-' . $res2['questionid'] . '-' . $res2['optionid'] . '" value="' . $res2['questionid'] . '.' . $res2['optionid'] . '"> ' . o($res2['answer']) . '</label></div>';
					}
					$html .= '</div>';
				}
			}
			$html .= '<input type="hidden" name="qid" value="" id="qid-output">';
		}
		die(json_encode(array('response' => 1, 'html' => $html)));
	} else if ($sheel->GPC['do'] == 'acpcheckusername') {
		$sheel->template->templateregistry['error'] = '';
		$response = '0';
		if ($sheel->common->is_username_banned($sheel->GPC['username'])) {
			$sheel->template->templateregistry['error'] = ((!empty($sheel->common->username_errors[0])) ? $sheel->common->username_errors[0] : '{_sorry_that_username_has_been_blocked}');
			$response = '1';
		} else if (empty($sheel->GPC['username']) or !isset($sheel->GPC['username'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_username}';
			$response = '1';
		}
		// make sure username doesn't conflict with another user
		$sqlusercheck = $sheel->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE username IN ('" . $sheel->db->escape_string($sheel->GPC['username']) . "')
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlusercheck) > 0) { // woops! change username for new user automatically.
			$sheel->template->templateregistry['error'] = '{_that_username_already_exists_in_our_system}';
			$response = '1';
		}
		$html = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'error' => $html)));
	} else if ($sheel->GPC['do'] == 'acpcheckemail') {
		$sheel->template->templateregistry['error'] = '';
		$response = '0';
		if (!isset($sheel->GPC['email']) or empty($sheel->GPC['email'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_email}';
			$response = '1';
		}
		if (!$sheel->common->is_email_valid($sheel->GPC['email'])) {
			$sheel->template->templateregistry['error'] = '{_please_enter_correct_email}';
			$response = '1';
		}
		// make sure email doesn't conflict with another user
		$sqlusercheck = $sheel->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE email IN ('" . $sheel->db->escape_string($sheel->GPC['email']) . "')
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sqlusercheck) > 0) { // woops! can't use same email!
			$sheel->template->templateregistry['error'] = '{_that_email_address_already_exists_in_our_system}';
			$response = '1';
		}
		$html = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		die(json_encode(array('response' => $response, 'error' => $html)));
	} else if ($sheel->GPC['do'] == 'acproleoptions') {
		$html = $error = $sheel->template->templateregistry['error'] = $sheel->template->templateregistry['html'] = '';
		$response = '0';
		if (!isset($sheel->GPC['subscriptionid']) or empty($sheel->GPC['subscriptionid'])) {
			$sheel->template->templateregistry['error'] = 'Please choose a valid plan.';
			$response = '1';
			$error = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
			die(json_encode(array('response' => $response, 'error' => $error, 'html' => '')));
		}
		$html = $sheel->role->print_subscription_roles_pulldown($sheel->GPC['subscriptionid'], '', 1, '', '', 'draw-select', 'form_roleid', 'form[roleid]', false);
		$sheel->template->templateregistry['html'] = $html;
		$html = ((!empty($sheel->template->parse_template_phrases('html'))) ? $sheel->template->parse_template_phrases('html') : '');
		die(json_encode(array('response' => $response, 'error' => '', 'html' => $html)));
	} else if ($sheel->GPC['do'] == 'fetchvariants') {
		$sheel->template->templateregistry['error'] = '';
		$response = '0';
		if (!isset($sheel->GPC['variants']) or empty($sheel->GPC['variants'])) {
			$sheel->template->templateregistry['error'] = 'Invalid variant configuration.  Contact seller.';
			$response = '1';
		}
		if (!isset($sheel->GPC['itemid']) or (empty($sheel->GPC['itemid']) or (isset($sheel->GPC['itemid']) and $sheel->GPC['itemid'] <= 0))) {
			$sheel->template->templateregistry['error'] = 'Invalid item.';
			$response = '1';
		}
		$join = $condition = '';
		$variants = $sheel->GPC['variants'];
		$variants = json_decode($variants, true);
		$i = 1;
		foreach ($variants as $key => $value) {
			if ($key != '' and $value != '') {
				if ($i == 1) {
					$join .= "RIGHT JOIN " . DB_PREFIX . "variants_choices v$i ON (c.sku = v$i.sku) ";
				} else {
					$join .= "RIGHT JOIN " . DB_PREFIX . "variants_choices v$i ON (v" . ($i - 1) . ".sku = v$i.sku) ";
				}
				$condition .= "AND v$i.project_id = '" . $sheel->db->escape_string($sheel->GPC['itemid']) . "' AND (v$i.question = '" . $sheel->db->escape_string($key) . "' AND v$i.answer = '" . $sheel->db->escape_string($value) . "') ";
				$i++;
			}
		}
		// let's find a match based on provided variants
		$sql = $sheel->db->query("
			SELECT c.sku, c.crypto_price, c.price, c.qty, c.attachid, p.currencyid, p.user_id
			FROM " . DB_PREFIX . "variants c
			LEFT JOIN " . DB_PREFIX . "projects p ON (c.project_id = p.project_id)
			$join
			WHERE c.project_id = '" . $sheel->db->escape_string($sheel->GPC['itemid']) . "'
				$condition
			GROUP BY c.sku
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sql) <= 0) { // woops!
			$sheel->template->templateregistry['error'] = 'Invalid variant selection.  Please retry.';
			$response = '1';
		} else if ($sheel->db->num_rows($sql) > 1) {
			$sheel->template->templateregistry['error'] = 'Invalid variant configuration.  Contact seller.';
			$response = '1';
		}
		$html = ((!empty($sheel->template->parse_template_phrases('error'))) ? $sheel->template->parse_template_phrases('error') : '');
		$res = $sheel->db->fetch_array($sql, DB_ASSOC);
		$iscrypto = ((isset($sheel->currency->currencies[$res['currencyid']]['iscrypto']) and $sheel->currency->currencies[$res['currencyid']]['iscrypto']) ? true : false);
		$purchases = $sheel->auction_product->fetch_buynow_ordercount($sheel->GPC['itemid'], $res['sku']);
		if (!empty($res['sku'])) {
			$sheel->db->query("
				UPDATE " . DB_PREFIX . "variants
				SET hits = hits + 1
				WHERE project_id = '" . $sheel->db->escape_string($sheel->GPC['itemid']) . "'
					AND sku = '" . $sheel->db->escape_string($res['sku']) . "'
				LIMIT 1
			");
		}
		// points
		$earnpoints = $sheel->fetch_earn_points($res['price'], $res['user_id'], true);


		die(json_encode(
			array(
				'response' => $response,
				'error' => $html,
				'variants' => json_encode($variants),
				'sku' => $res['sku'],
				'earnpoints' => $earnpoints,
				'price' => $sheel->currency->format((($iscrypto) ? $res['crypto_price'] : $res['price']), $res['currencyid']),
				'qty' => $res['qty'],
				'purchases' => $purchases,
				'attachid' => $res['attachid']
			)
		)
		);
	} else if ($sheel->GPC['do'] == 'itemvariants') {
		$variantcount = 0;
		if (!isset($sheel->GPC['variants'])) {
			$sheel->GPC['variants'] = array();
		}
		if (!isset($sheel->GPC['itemid']) or !isset($sheel->GPC['question'])) {
			die();
		}
		foreach ($sheel->GPC['variants'] as $question => $value) {
			if ($value != '' and $question != '') {
				$variantcount++;
			}
		}
		$leftjoins = $condition = $items = $questions = '';
		$a = 2;
		foreach ($sheel->GPC['variants'] as $question => $value) {
			if ($question != '') {
				$items .= "AND v$a.project_id = '" . intval($sheel->GPC['itemid']) . "'" . LINEBREAK;
				$a++;
			}
		}
		$q = 1;
		foreach ($sheel->GPC['variants'] as $question => $value) {
			if ($question != '') {
				if ($q == 1) {
					$questions .= "AND v$q.question = '" . $sheel->db->escape_string($sheel->GPC['question']) . "'" . LINEBREAK;
					$questions .= "AND v" . ($q + 1) . ".question = '" . $sheel->db->escape_string($question) . "' ";
				} else {
					$questions .= "AND v" . ($q + 1) . ".question = '" . $sheel->db->escape_string($question) . "' ";
				}
				$q++;
			}
		}
		$c = 1;
		foreach ($sheel->GPC['variants'] as $question => $value) {
			if ($question != '') {
				$leftjoins .= "LEFT JOIN " . DB_PREFIX . "variants_choices v" . ($c + 1) . " ON (v$c.sku = v" . ($c + 1) . ".sku)" . LINEBREAK;
				if ($c == 1) {
					$condition .= "AND v" . ($c + 1) . ".answer = '" . $sheel->db->escape_string($value) . "' ";
				} else {
					$condition .= "AND v" . ($c + 1) . ".answer = '" . $sheel->db->escape_string($value) . "' ";
				}
				$c++;
			}
		}
		$response[] = array("" => "&ndash;");
		$sql = $sheel->db->query("
			SELECT v1.answer, v.qty
			FROM " . DB_PREFIX . "variants_choices v1
			$leftjoins
			LEFT JOIN " . DB_PREFIX . "variants v ON (v1.sku = v.sku)
			WHERE v.project_id = '" . intval($sheel->GPC['itemid']) . "'
				AND v1.sku != ''
				AND v1.project_id = '" . intval($sheel->GPC['itemid']) . "'
				$items
				$questions
				$condition
			GROUP BY v1.answer
		", 0, null, __FILE__, __LINE__);
		if ($sheel->db->num_rows($sql) > 0) {
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$response[] = (object) array($res['answer'] => o($res['answer']) . (($res['qty'] <= 0) ? ' (Out of stock)' : ''));
			}
		}
		die(json_encode($response));
	} else if ($sheel->GPC['do'] == 'goingonce') {
		$sheel->GPC['itemid'] = ((isset($sheel->GPC['itemid'])) ? intval($sheel->GPC['itemid']) : 0);
		$sheel->GPC['ttl'] = ((isset($sheel->GPC['ttl'])) ? intval($sheel->GPC['ttl']) : 0);
		$sheel->GPC['uid'] = ((isset($sheel->GPC['uid'])) ? intval($sheel->GPC['uid']) : 0); // seller id requesting
		$sheel->GPC['token'] = ((isset($sheel->GPC['token'])) ? $sheel->GPC['token'] : '');
		$sheel->GPC['sessuid'] = ((isset($_SESSION['sheeldata']['user']['userid'])) ? $_SESSION['sheeldata']['user']['userid'] : 0);
		if ($sheel->GPC['itemid'] <= 0 or $sheel->GPC['uid'] <= 0 or $sheel->GPC['token'] == '' or $sheel->GPC['sessuid'] <= 0 or $sheel->GPC['ttl'] <= 0) {
			exit();
		}
		$sheel->auction->goingonce($sheel->GPC['itemid'], $sheel->GPC['ttl'], $sheel->GPC['uid'], $sheel->GPC['sessuid'], $sheel->GPC['token']);
		exit();
	} else if ($sheel->GPC['do'] == 'addauctionstream') {
		$types = array('system', 'seller', 'bid', 'private', 'error', 'draft', 'upcoming');
		$sheel->GPC['itemid'] = ((isset($sheel->GPC['itemid'])) ? intval($sheel->GPC['itemid']) : 0);
		$sheel->GPC['message'] = ((isset($sheel->GPC['message'])) ? o($sheel->GPC['message']) : '');
		$sheel->GPC['mode'] = ((isset($sheel->GPC['mode'])) ? o($sheel->GPC['mode']) : 'system');
		$sheel->GPC['touid'] = ((isset($sheel->GPC['uid'])) ? intval($sheel->GPC['uid']) : 0);
		$sheel->GPC['token'] = ((isset($sheel->GPC['token'])) ? $sheel->GPC['token'] : '');
		if (!in_array($sheel->GPC['mode'], $types)) {
			exit();
		}
		$sheel->auction->message($sheel->GPC['itemid'], $sheel->GPC['message'], $sheel->GPC['mode'], $sheel->GPC['touid'], $sheel->GPC['token']);
		exit();
	} else if ($sheel->GPC['do'] == 'outputauctionstream') {
		ignore_user_abort(0);
		set_time_limit(30);

		$sheel->GPC['id'] = ((isset($sheel->GPC['id'])) ? intval($sheel->GPC['id']) : 0);
		if ($sheel->GPC['id'] <= 0) {
			http_response_code(401);
			die();
		}
		$data_source_file = DIR_LIVEAUCTION . 'liveauction_' . $sheel->GPC['id'];
		if (!file_exists($data_source_file)) {
			http_response_code(410);
			die();
		}
		$starttime = time();
		while (1) { // main loop
			$now = time() - $starttime;
			if ($now > 30) {
				$result = array(
					'error' => '1',
					'title' => 'Timer',
					'message' => 'Checking client connection'
				);
				break;
			}
			if (connection_status() != CONNECTION_NORMAL) {
				$result = array(
					'error' => '1',
					'title' => 'Network connection',
					'message' => 'Connection to server has been disconnected.'
				);
				break;
			}
			if (connection_aborted()) {
				$result = array(
					'error' => '1',
					'title' => 'Connection aborted',
					'message' => 'Connection to server has been aborted.'
				);
				break;
			}
			$last_ajax_call = isset($sheel->GPC['ts']) ? (int) $sheel->GPC['ts'] : null;
			clearstatcache();
			$last_change_in_data_file = filemtime($data_source_file);
			if ($last_ajax_call == null or $last_change_in_data_file > $last_ajax_call) {
				$data = file_get_contents($data_source_file);
				$result = array(
					'error' => '0',
					'html' => $data,
					'timestamp' => $last_change_in_data_file
				);
				$json = json_encode($result);
				http_response_code(200);
				echo $json;
				die();
			} else { // wait for 3 secs (this blocks the PHP/Apache process but that's how it goes)
				usleep(3000000);
				continue;
			}
		}
		$json = json_encode($result);
		http_response_code(200);
		echo $json;
		die();
	} else if ($sheel->GPC['do'] == 'jswidget') {
		$array = array();
		$options = ((isset($sheel->GPC['options'])) ? json_decode($sheel->GPC['options'], true) : array());
		if (isset($sheel->GPC['widget']) and $sheel->GPC['widget'] == 'block') {
			if ($options['mode'] == 'googleanalytics') {
				$html = '';
				$js = "jQuery(document).ready(function(){(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '" . $sheel->config['globalserversettings_ga'] . "', 'auto');ga('send', 'pageview');});";
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'googleadsense') {
				$html = '';
				$js = "jQuery(document).ready(function(){var externalScript = document.createElement('script');externalScript.type = 'text/javascript';externalScript.setAttribute('async','async');externalScript.src = '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';document.getElementsByTagName('body')[0].appendChild(externalScript);var ins = document.createElement('ins');ins.setAttribute('class','adsbygoogle');ins.setAttribute('style','display:block;max-width:300px;width:300px');ins.setAttribute('data-ad-client','" . $options['pubid'] . "');ins.setAttribute('data-ad-slot','" . $options['slotid'] . "');ins.setAttribute('data-ad-format','auto');document.getElementById('" . $options['container'] . "').appendChild(ins);var inlineScript = document.createElement('script');inlineScript.type = 'text/javascript';inlineScript.text = '(adsbygoogle = window.adsbygoogle || []).push({});';document.getElementsByTagName('body')[0].appendChild(inlineScript);});";
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'privatemessagepopup') { // private message popup widget
				$vars = array();
				$sheel->template->load_popup('main', 'ajax_widget_privatemessagepopup.html');
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_privatemessagepopup.js.html');
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'rv-footer') {
				$vars = array();
				$sheel->template->load_popup('main', 'ajax_widget_footerrecentviewed.html');
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_footerrecentviewed.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'recentkeywords') {
				$options['returnurl'] = ((isset($options['returnurl'])) ? urlencode($options['returnurl']) : '');
				$userid = ((!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? $_SESSION['sheeldata']['user']['userid'] : 0);
				if (($recentkeywords = $sheel->cache->fetch('recentkeywords_' . TOKEN . '_' . $userid . '_' . str_replace('.', '_', IPADDRESS))) === false) { // recent keywords entered by user or guest (based on IP address)
					$recentkeywords = $sheel->auction->fetch_recently_used_keywords($userid, 1, $options['limit'], 0, '', 'product');
					$sheel->cache->store('recentkeywords_' . TOKEN . '_' . $userid . '_' . str_replace('.', '_', IPADDRESS), $recentkeywords);
				}
				if (count($recentkeywords) >= 1) {
					$sheel->show['recentkeywords'] = true;
				}
				$vars = array('clearkeywordsurl' => HTTPS_SERVER . 'preferences/favorites/recentlyreviewed/clearkeywordlist/');
				$loops = array(
					'recentkeywords' => $recentkeywords
				);
				$sheel->template->load_popup('main', 'ajax_widget_recentkeywords.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_recentkeywords.js.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 0);
				unset($js, $html);
			} else if ($options['mode'] == 'anchorstores') {
				if (($anchorstores = $sheel->cache->fetch('anchorstores_200x200')) === false) {
					$anchorstores = $sheel->auction_listing->fetch_stores(4, $options['cid'], 1, 0, 0, '', 0, '208x208', '60x60');
					$sheel->cache->store('anchorstores_208x208', $anchorstores);
				}
				if (count($anchorstores) >= 1) {
					$sheel->show['anchorstores'] = true;
				}
				$vars = array();
				$loops = array(
					'anchorstores' => $anchorstores
				);
				$sheel->template->load_popup('main', 'ajax_widget_anchorstores.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_anchorstores.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'auctionevents') {
				if (($auctionevents = $sheel->cache->fetch('auctionevents_150x150')) === false) {
					$auctionevents = $sheel->auction_listing->fetch_auctions(6, 50, $options['cid'], '', 0, '30x30', '300x280'); // $columns = 4, $cid = 0, $keywords = '', $singlelots = 0, $picturedim = '150x150', $logodim = '60x60'
					$sheel->cache->store('auctionevents_150x150', $auctionevents);
				}
				if (count($auctionevents) >= 1) {
					$sheel->show['auctionevents'] = true;
				}
				$vars = array();
				$loops = array(
					'auctionevents' => $auctionevents
				);
				$sheel->template->load_popup('main', 'ajax_widget_auctionevents.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_auctionevents.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'auctionevents_sheel') {
				if (($auctionevents = $sheel->cache->fetch('auctionevents_150x150')) === false) {
					$auctionevents = $sheel->auction_listing->fetch_auctions(6, 50, $options['cid'], '', 0, '30x30', '300x280'); // $columns = 4, $cid = 0, $keywords = '', $singlelots = 0, $picturedim = '150x150', $logodim = '60x60'
					$sheel->cache->store('auctionevents_150x150', $auctionevents);
				}
				if (count($auctionevents) >= 1) {
					$sheel->show['auctionevents'] = true;
				}
				$vars = array();
				$loops = array(
					'auctionevents' => $auctionevents
				);
				$sheel->template->load_popup('main', 'ajax_widget_auctionevents_sheel.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_auctionevents.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'featuredstores') {
				$featuredstores = $sheel->auction_listing->fetch_stores(4, $options['cid'], 0, 1, 0, '', 0, '', '60x60');
				$sheel->show['featuredstores'] = ((count($featuredstores) > 0) ? true : false);
				$vars = array();
				$loops = array(
					'featuredstores' => $featuredstores
				);
				$sheel->template->load_popup('main', 'ajax_widget_featuredstores.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_featuredstores.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'popularbrands') {
				$popularbrands = $sheel->brands->fetch_popular_brands($options['limit']);
				$sheel->show['popularbrands'] = ((count($popularbrands) > 0) ? true : false);
				$vars = array();
				$loops = array(
					'popularbrands' => $popularbrands
				);
				$sheel->template->load_popup('main', 'ajax_widget_popularbrands.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_popularbrands.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'hpax') {

			} else if ($options['mode'] == 'hpa') { // drag & drop ads
				if (isset($options['id']) and $options['id'] > 0) {
					$html = '';
					$js = '(function($){$.fn.dragdrophpa' . $options['id'] . ' = function(){jQuery("#drop-area-hpa' . $options['id'] . '.drop-area-active").on(\'dragenter, dragleave, dragover\', function(e) {e.preventDefault();});jQuery("#drop-area-hpa' . $options['id'] . '.drop-area-active").on(\'drop\', function(e) {e.preventDefault();e.stopPropagation();var image = e.originalEvent.dataTransfer.files;var prompttext = sheel_prompt(\'<div class="pb-4 bold">Enter Ad URL</div>\');if (prompttext != null && prompttext != false && prompttext != \'\'){var hpaid = jQuery(this).attr("data-hpaid");var url = prompttext;jQuery().hpaupload(image, hpaid, url);}else{if (prompttext == null || prompttext == false){alert_js(\'Warning: Ad not saved.  Please specify URL.\');}}});};})(jQuery);jQuery(document).ready(function () {(function(){jQuery().dragdrophpa' . $options['id'] . '();}());});';
					if ($options['id'] == 1) { // homepage sidebar right
						$html = '<a href="' . $sheel->config['globalserversettings_homepageadurl'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa10" data-hpaid="10"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa10.jpg" width="' . $options['width'] . '" class="img-responsive" alt="hpa 1" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					}
					if ($options['id'] == 10) { // homepage sidebar right 2
						$html = '<a href="' . $sheel->config['globalserversettings_homepageadurl10'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa1" data-hpaid="1"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa1.jpg" width="' . $options['width'] . '" class="img-responsive" alt="hpa 1" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					}
					if ($options['id'] == 11) { // homepage sidebar right 2
						$html = '<a href="' . $sheel->config['globalserversettings_homepageadurl11'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa1" data-hpaid="11"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa11.jpg" width="' . $options['width'] . '" class="img-responsive" alt="hpa 1" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 2) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl2'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa2" data-hpaid="2"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa2.jpg" width="' . $options['width'] . '" height="' . $options['height'] . '" alt="" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 3) {
						$html = '<div id="top-nav-right-ad"><a href="' . $sheel->config['globalserversettings_homepageadurl3'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa3" data-hpaid="3"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa3.gif" alt="hpa 3" width="' . $options['width'] . '" height="' . $options['height'] . '" /></span></a></div>';
					} else if ($options['id'] == 4) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl4'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa4" data-hpaid="4"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa4.jpg" class="img-responsive mw-1110" width="' . $options['width'] . '" height="' . $options['height'] . '" alt="hpa 4" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 5) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl5'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa5" data-hpaid="5"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa5.jpg" class="mw-440" width="' . $options['width'] . '" height="' . $options['height'] . '" alt="hpa 5" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 6) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl6'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa6" data-hpaid="6"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa6.jpg" class="mw-440" width="' . $options['width'] . '" height="' . $options['height'] . '" alt="hpa 6" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 7) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl7'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa7" data-hpaid="7"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa7.jpg" class="mw-440" width="100%" height="100%" alt="hpa 7" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 8) {
						$html = '<a rel="nofollow" href="' . $sheel->config['globalserversettings_homepageadurl8'] . '"><span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa8" data-hpaid="8"><img src="' . $sheel->config['imguploadscdn'] . 'ax/hpa8.jpg" class="mw-440" width="100%" height="100%" alt="hpa 8" />' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</span></a>';
					} else if ($options['id'] == 9) {
						$html = '<span class="drop-area' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '-active' : '') . '" id="drop-area-hpa9" data-hpaid="9" style="width:' . $options['width'] . 'px;height:' . $options['height'] . 'px"><a href="' . $sheel->config['globalserversettings_homepageadurl9'] . '" target="_blank" class="billboard-promo">' . ((isset($options['alttext'])) ? '<span class="alt-text">' . o($options['alttext']) . '</span>' : '') . '' . ((isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) ? '<span class="dropnote"></span>' : '') . '</a></span>';
					}
					if (isset($options['divider']) and $options['divider']) {
						$options['dividersize'] = ((isset($options['dividersize'])) ? intval($options['dividersize']) : 12);
						$html .= '<div class="hr-' . $options['dividersize'] . '-0-' . $options['dividersize'] . '-0"></div>';
					}
					$sheel->template->templateregistry['html'] = $html;
					$html = $sheel->template->parse_template_phrases('html');
					$array = array('js' => $js, 'html' => $html, 'eval' => 1);
					unset($js, $html);
				}
			} else if ($options['mode'] == 'newarrivals') { // new arrivals by department
				if (($newarrivals = $sheel->cache->fetch('newarrivals')) === false) {
					$newarrivals = $sheel->auction_listing->fetch_latest_categories(8, 0);
					$sheel->cache->store('newarrivals', $newarrivals);
				}
				$vars = array();
				$loops = array(
					'newarrivals' => $newarrivals
				);
				$sheel->template->load_popup('main', 'ajax_widget_newarrivals.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_newarrivals.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'bestsellers') {

			} else if ($options['mode'] == 'bestnewsellers') {

			} else if ($options['mode'] == 'relateditemsfromcart') {

			} else if ($options['mode'] == 'recommendedcategories') {
				$userid = ((!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? $_SESSION['sheeldata']['user']['userid'] : 0);
				if (($t['favoritecategories'] = $sheel->cache->fetch('favoritecategories_' . TOKEN . '_' . $userid . '_' . str_replace('.', '_', IPADDRESS))) === false) { // recent keywords entered by user or guest (based on IP address)
					$t['favoritecategories'] = $sheel->auction->fetch_favorite_categories($userid, 25);
					$sheel->cache->store('favoritecategories_' . TOKEN . '_' . $userid . '_' . str_replace('.', '_', IPADDRESS), $t['favoritecategories']);
				}
				if (count($t['favoritecategories']) >= 1) {
					$sheel->show['favoritecategories'] = true;
				}
				$loops = array(
					'favoritecategories' => $t['favoritecategories']
				);
				$vars = array();
				$sheel->template->load_popup('main', 'ajax_widget_recommendedcategories.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_recommendedcategories.js.html');
				$sheel->template->parse_loop('main', $loops);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 0);
				unset($js, $html);
			} else if ($options['mode'] == 'iax') {

			} else if ($options['mode'] == 'footermobileapp') {
				$vars = array();
				$sheel->template->load_popup('main', 'ajax_widget_footermobileapp.html');
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$html = $sheel->template->pprint('main', $vars, false);
				$sheel->template->load_popup('main', 'ajax_widget_footermobileapp.js.html', false);
				$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'options' => $options));
				$js = $sheel->template->pprint('main', $vars, false);
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			} else if ($options['mode'] == 'custom') {
				$html = '';
				$js = '';
				if (!empty($options['html'])) {
					$html = $options['html']; // uses html provided
				}
				if (!empty($options['js'])) {
					$js = $options['js']; // uses js provided
				}
				$js = "jQuery(document).ready(function(){" . $js . "});";
				$array = array('js' => $js, 'html' => $html, 'eval' => 1);
				unset($js, $html);
			}
		} else if (isset($sheel->GPC['widget']) and $sheel->GPC['widget'] == 'slider') {
			if ($options['mode'] == 'categorymap') { // ending soon

			} else if ($options['mode'] == 'nonprofits') { // newly listed

			} else if ($options['mode'] == 'storeshomepage') { // newly listed

			} else if ($options['mode'] == 'auctionshomepage') { // newly listed

			} else if ($options['mode'] == 'homepage') { // newly listed

			}
		} else if (isset($sheel->GPC['widget']) and $sheel->GPC['widget'] == 'carousel') {
			if ($options['mode'] == 'endingsoon') { // ending soon

			} else if ($options['mode'] == 'latest') { // newly listed

			} else if ($options['mode'] == 'itemwatchlists') { // items from seller watchlist

			} else if ($options['mode'] == 'relatedtoviewed') { // related to viewed

			} else if ($options['mode'] == 'featured') { // featured

			} else if ($options['mode'] == 'bestsellers') { // Best sellers in [x] (vertical)

			} else if ($options['mode'] == 'bestsellingnewitems') { // Best-selling new items in [x] (vertical)

			} else if ($options['mode'] == 'categorybestsellers') { // [x] best sellers

			} else if ($options['mode'] == 'recommended') { // Recommendations for you in [x]

			} else if ($options['mode'] == 'categorythumb') {

			} else if ($options['mode'] == 'featuredstores') {

			} else if ($options['mode'] == 'anchorstores') { // premium anchor stores

			} else if ($options['mode'] == 'browserhistory') { // Inspired by your browsing history

			}
		}
		/*
		Fun gift ideas for $10 and under
		Accessories for $10 or less with free shipping
		Low-price fashion with free shipping
		Low-price home décor with free shipping
		Additional items to explore
		More items to consider
		Movers and shakers
		Most wished for in Books
		*/
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$array = json_encode($array);
		die($array);
	} else if ($sheel->GPC['do'] == 'wysiwygupload') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			try {
				$fileRoute = DIR_ATTACHMENTS . 'content/';
				$fieldname = "file";
				$filename = explode(".", $_FILES[$fieldname]["name"]);
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$tmpName = $_FILES[$fieldname]["tmp_name"];
				$mimeType = finfo_file($finfo, $tmpName);
				$extension = end($filename);
				$allowedExts = array("jpeg", "jpg", "png");
				$allowedMimeTypes = array("image/jpeg", "image/pjpeg", "image/x-png", "image/png");
				if (!in_array(strtolower($mimeType), $allowedMimeTypes) || !in_array(strtolower($extension), $allowedExts)) {
					throw new \Exception("File does not meet the validation.  Please upload .jpg, .jpeg or .png only.");
				}
				$name = sha1(microtime()) . "." . $extension;
				$fullNamePath = $fileRoute . $name;
				if (file_exists($fullNamePath)) {
					@unlink($fullNamePath);
				}
				move_uploaded_file($tmpName, $fullNamePath);
				$response = new \StdClass;
				$response->link = HTTP_ATTACHMENTS . 'content/' . $name;
				echo stripslashes(json_encode($response));
			} catch (Exception $e) {
				echo $e->getMessage();
				http_response_code(404);
			}
		}
	} else if ($sheel->GPC['do'] == 'wysiwygdelete') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$name = ((isset($sheel->GPC['name']) and !empty($sheel->GPC['name'])) ? $sheel->GPC['name'] : '');
			if (file_exists(DIR_ATTACHMENTS . 'content/' . $name)) {
				@unlink(DIR_ATTACHMENTS . 'content/' . $name);
				echo stripslashes(json_encode('Success'));
				exit();
			} else {
				$response = new StdClass;
				$response->error = "Image does not exist!";
			}
		}
	} else if ($sheel->GPC['do'] == 'wysiwygmanage') {
		if (isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$response = array();
			$image_types = array(
				"image/gif",
				"image/jpeg",
				"image/pjpeg",
				"image/jpeg",
				"image/pjpeg",
				"image/png",
				"image/x-png"
			);
			$fnames = scandir(DIR_ATTACHMENTS . 'content/');
			if ($fnames) {
				foreach ($fnames as $name) { // go through all the filenames in the folder
					if (!is_dir($name)) { // filename must not be a folder
						if (in_array(mime_content_type(DIR_ATTACHMENTS . 'content/' . $name), $image_types)) { // build the image.
							$img = new \StdClass;
							$img->url = HTTP_ATTACHMENTS . 'content/' . $name;
							$img->thumb = HTTP_ATTACHMENTS . 'content/' . $name;
							$img->name = $name;
							array_push($response, $img);
						}
					}
				}
			} else { // folder does not exist, respond with a JSON to throw error
				$response = new StdClass;
				$response->error = "Images folder does not exist!";
			}
			echo stripslashes(json_encode($response));
			exit();
		}
	} else if ($sheel->GPC['do'] == 'consent') {
		$types = array('cookieconsent', 'termsconsent', 'privacyconsent');
		if (isset($sheel->GPC['type']) and !empty($sheel->GPC['type']) and in_array($sheel->GPC['type'], $types)) {
			set_cookie($sheel->GPC['type'], 1);
		}
	} else if ($sheel->GPC['do'] == 'brandsearch') {
		$return_arr = array();
		$sql = $sheel->db->query("
			SELECT BRAND_NM, BSIN
			FROM " . DB_PREFIX . "brand
			WHERE BRAND_NM LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%'
				AND VISIBLE = '1'
			GROUP BY BRAND_NM
			LIMIT 12
		");
		if ($sheel->db->num_rows($sql) > 0) {
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$return_arr[] = array(
					'name' => trim($res['BRAND_NM']),
					'bsin' => o(trim($res['BSIN']))
				);
			}
		}
		$return_val = json_encode($return_arr);
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		die($return_val);
	} else if ($sheel->GPC['do'] == 'sellersearch') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$return_arr = array();
			$sql = $sheel->db->query("
				SELECT user_id, username, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE (username LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR user_id = '" . intval($sheel->GPC['q']) . "' OR first_name LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR last_name LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%')
					AND status = 'active'
				LIMIT 12
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$return_arr[] = array(
						'username' => trim($res['username']) . ' (' . trim($res['first_name']) . ' ' . trim($res['last_name']) . ' / User ID: ' . $res['user_id'] . ')',
						'plainusername' => trim($res['username']),
						'userid' => $res['user_id']
					);
				}
			}
			$return_val = json_encode($return_arr);
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');
			die($return_val);
		}
	} else if ($sheel->GPC['do'] == 'eventsearch') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$return_arr = array();
			$sql = $sheel->db->query("
				SELECT eventid, title
				FROM " . DB_PREFIX . "events
				WHERE (title LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR eventid = '" . intval($sheel->GPC['q']) . "')
					AND ended = '0'
				LIMIT 12
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$return_arr[] = array(
						'name' => trim($res['title']),
						'title' => trim($res['title']) . ' (#' . $res['eventid'] . ')',
						'eventid' => $res['eventid']
					);
				}
			}
			$return_val = json_encode($return_arr);
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');
			die($return_val);
		}
	} else if ($sheel->GPC['do'] == 'productsearch') {
		$return_arr = array();
		$sql = $sheel->db->query("
			SELECT g.GTIN_CD, g.UPC, g.GTIN_NM, g.GTIN_DESC, g.FEATURES, g.ATTRIBUTES, g.PRICE, g.IMG, g.IMG_UPC, g.CRAWLED, g.BSIN, b.BRAND_NM
			FROM " . DB_PREFIX . "brand_gtin g
			LEFT JOIN " . DB_PREFIX . "brand b ON (g.BSIN = b.BSIN)
			WHERE (g.GTIN_NM LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR g.GTIN_NM LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')
				AND g.VISIBLE = '1'
			GROUP BY g.GTIN_NM
			LIMIT 12
		");
		if ($sheel->db->num_rows($sql) > 0) {
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				$res['imgsrc'] = $sheel->config['imgcdn'] . 'v5/img_nophoto.png';
				if (!empty($res['UPC'])) {
					$upcfolder = 'upc-' . substr($res['UPC'], 0, 3);
					if ($res['IMG_UPC'] != '') {
						if ($res['CRAWLED'] and file_exists(DIR_UPC . $upcfolder . '/' . $res['UPC'] . '.jpg')) { // saved locally or to cdn
							$res['imgsrc'] = HTTP_UPC . $upcfolder . '/' . $res['UPC'] . '.jpg';
						} else { // load from remote url for preview before crawling
							$res['imgsrc'] = $res['IMG_UPC'];
						}
					}
				} else {
					if ($res['IMG'] == '1') {
						$gtinfolder = 'gtin-' . substr($res['GTIN_CD'], 0, 3);
						$res['imgsrc'] = HTTP_GTIN . $gtinfolder . '/' . $res['GTIN_CD'] . '.jpg';
					}
				}
				$fcount = 1;
				$fcountmax = 5;
				$feature['1'] = '';
				$feature['2'] = '';
				$feature['3'] = '';
				$feature['4'] = '';
				$feature['5'] = '';
				$features = array();
				$allfeatures = '';
				$modelnumber = '';
				$partnumber = '';
				$weight = '';
				$width = '';
				$height = '';
				$length = '';
				if (!empty($res['FEATURES'])) {
					$features = unserialize($res['FEATURES']);
					foreach ($features as $key => $value) {
						if ($key == 'blob') {
							$allfeatures .= "<br /><strong>{_additional_information}:</strong><br />$value<br />";
						} else {
							$allfeatures .= ((!is_numeric($key)) ? "$key: $value<br />" : "$value<br />");
							// capture additional fields for sell item page..
							if (containsword(strtolower($key), 'model number')) {
								$modelnumber = trim(o($value));
							}
							if (containsword(strtolower($key), 'part number')) {
								$partnumber = trim(o($value));
							}
							if (containsword(strtolower($key), 'weight')) {
								$weight = trim(o(intval($value)));
							}
							if (containsword(strtolower($key), 'width')) {
								$width = trim(o(intval($value)));
							}
							if (containsword(strtolower($key), 'height')) {
								$height = trim(o(intval($value)));
							}
							if (containsword(strtolower($key), 'length')) {
								$length = trim(o(intval($value)));
							}
						}
					}
					if (!empty($allfeatures)) {
						$allfeaturesx = '<br /><br /><strong>{_product_features}:</strong><br />' . $allfeatures;
						$sheel->template->templateregistry['allfeatures'] = $allfeaturesx;
						$allfeatures = $sheel->template->parse_template_phrases('allfeatures');
					}
					foreach ($features as $key => $value) {
						if ($fcount == $fcountmax) {
							break;
						}
						if (!containsword(strtolower($key), 'blob')) {
							if (!containsword(strtolower($key), 'model number') and !containsword(strtolower($key), 'part number') and !containsword(strtolower($key), 'weight') and !containsword(strtolower($key), 'width') and !containsword(strtolower($key), 'height') and !containsword(strtolower($key), 'length')) {
								$feature[$fcount] = ((!is_numeric($key)) ? "$key: $value" : $value);
								$fcount++;
							}
						} else {
							$tmp = explode(';', $value);
							foreach ($tmp as $blobfeature) {
								if ($fcount == $fcountmax) {
									break;
								}
								$feature[$fcount] = "$blobfeature"; // Chipset: GeForce 8400GS Engine Clock: 520 MHz Video Memory: 512MB DDR2;
								$fcount++;
							}
						}
					}
				}
				$return_arr[] = array(
					'gtin' => $res['GTIN_CD'],
					'upc' => $res['UPC'],
					'name' => trim($res['GTIN_NM']),
					'desc' => trim($res['GTIN_DESC']),
					'brand' => trim($res['BRAND_NM']),
					'bsin' => o(trim($res['BSIN'])),
					'feature1' => $feature['1'],
					'feature2' => $feature['2'],
					'feature3' => $feature['3'],
					'feature4' => $feature['4'],
					'feature5' => $feature['5'],
					'allfeatures' => $allfeatures,
					'attributes' => '',
					'price' => $res['PRICE'],
					'picture' => $res['imgsrc'],
					'modelnumber' => $modelnumber,
					'partnumber' => $partnumber,
					'weight' => $weight,
					'width' => $width,
					'height' => $height,
					'length' => $length,
				);
			}
		}
		$return_val = json_encode($return_arr);
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		die($return_val);
	} else if ($sheel->GPC['do'] == 'catsearch') {
		if (isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] == 1) {
			$return_arr = array();
			$sql = $sheel->db->query("
				SELECT cid, title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "categories
				WHERE (title_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '" . $sheel->db->escape_string($sheel->GPC['q']) . "%' OR cid LIKE '%" . $sheel->db->escape_string($sheel->GPC['q']) . "%')
					AND visible = '1'
				LIMIT 12
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$return_arr[] = array(
						'cid' => $res['cid'],
						'name' => str_replace('&amp;', '&', strip_tags($sheel->categories->recursive($res['cid'], 'product', $_SESSION['sheeldata']['user']['slng'], 1))) . ' (#' . $res['cid'] . ')',
						'nameshort' => $res['title']
					);
				}
			}
			$return_val = json_encode($return_arr);
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');
			die($return_val);
		}
	} else if ($sheel->GPC['do'] == 'checkstorename') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['title']) and !empty($sheel->GPC['title'])) {
				$response = '1';
				$sql = $sheel->db->query("
					SELECT storeid
					FROM " . DB_PREFIX . "stores
					WHERE storename = '" . $sheel->db->escape_string($sheel->GPC['title']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($sheel->db->num_rows($sql) > 0) {
					$response = '0';
				}
				die($response);
			}
			die('0');
		}
	} else if ($sheel->GPC['do'] == 'checkbrandname') {
		if (isset($sheel->GPC['title']) and !empty($sheel->GPC['title'])) {
			$response = '0';
			$sql = $sheel->db->query("
				SELECT BSIN
				FROM " . DB_PREFIX . "brand
				WHERE BRAND_NM = '" . $sheel->db->escape_string($sheel->GPC['title']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				$response = '1';
			}
			die($response);
		}
		die('1');
	} else if ($sheel->GPC['do'] == 'storenewsletter') {
		if (isset($sheel->GPC['name']) and isset($sheel->GPC['email']) and isset($sheel->GPC['turing']) and isset($sheel->GPC['sid'])) {
			if ($_SESSION['sheeldata']['user']['captcha'] != mb_strtoupper(trim($sheel->GPC['turing']))) { // turing
				echo json_encode(array('error' => 1));
				exit();
			}
			$storeid = $sheel->db->fetch_field(DB_PREFIX . "stores", "seourl = '" . $sheel->db->escape_string($sheel->GPC['sid']) . "'", "storeid");
			$storename = $sheel->db->fetch_field(DB_PREFIX . "stores", "seourl = '" . $sheel->db->escape_string($sheel->GPC['sid']) . "'", "storename");
			$sellerid = $sheel->db->fetch_field(DB_PREFIX . "stores", "seourl = '" . $sheel->db->escape_string($sheel->GPC['sid']) . "'", "user_id");
			// check if subscribed
			$sql = $sheel->db->query("
				SELECT id
				FROM " . DB_PREFIX . "stores_newsletter
				WHERE storeid = '" . intval($storeid) . "'
					AND email = '" . $sheel->db->escape_string($sheel->GPC['email']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($sheel->db->num_rows($sql) > 0) {
				echo json_encode(array('error' => 2));
				exit();
			}
			$sheel->db->query("
				INSERT INTO " . DB_PREFIX . "stores_newsletter
				(id, storeid, firstname, email, userid, verified, ipaddress, dateadded)
				VALUES (
				NULL,
				'" . intval($storeid) . "',
				'" . $sheel->db->escape_string($sheel->GPC['name']) . "',
				'" . $sheel->db->escape_string($sheel->GPC['email']) . "',
				'" . ((isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? $_SESSION['sheeldata']['user']['userid'] : '0') . "',
				'0',
				'" . $sheel->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
				'" . DATETIME24H . "'
				)
			");
			$sheel->pmb->compose_private_message($sellerid, -1, 'New Store Newsletter Subscriber', $sheel->GPC['name'] . ' just signed up for the ' . $storename . ' store newsletter. This new subscriber ' . ((isset($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) ? 'is a <b>verified user</b>.' : 'is a <b>unverified</b> user.'));
			echo json_encode(array('error' => 0));
			exit();
		}
	} else if ($sheel->GPC['do'] == 'deleteitemquestion') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['qid']) and $sheel->GPC['qid'] > 0 and isset($sheel->GPC['sellerid']) and $sheel->GPC['sellerid'] > 0 and ($sheel->GPC['sellerid'] == $_SESSION['sheeldata']['user']['userid'] or (isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] == 1))) {
				// remove question & answers
				$sheel->db->query("
					DELETE FROM " . DB_PREFIX . "messages
					WHERE messageid = '" . $sheel->db->escape_string($sheel->GPC['qid']) . "'
						AND replyid = '0'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$sheel->db->query("
					DELETE FROM " . DB_PREFIX . "messages
					WHERE replyid = '" . $sheel->db->escape_string($sheel->GPC['qid']) . "'
				", 0, null, __FILE__, __LINE__);
				die('1');
			}
		}
		die('0');
	} else if ($sheel->GPC['do'] == 'voteitemanswer') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['id']) and $sheel->GPC['id'] > 0 and isset($sheel->GPC['mode']) and !empty($sheel->GPC['mode']) and in_array($sheel->GPC['mode'], array('up', 'down'))) {
				if ($sheel->GPC['mode'] == 'up') {
					$query = "SET votesup = votesup + 1";
				} else if ($sheel->GPC['mode'] == 'down') {
					$query = "SET votesdown = votesdown + 1";
				}
				// remove question & answers
				$sheel->db->query("
					UPDATE " . DB_PREFIX . "messages
					$query
					WHERE messageid = '" . $sheel->db->escape_string($sheel->GPC['id']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				die('1');
			}
		}
		die('0');
	} else if ($sheel->GPC['do'] == 'eventinfo') {
		$sheel->auction_expiry->listings();
		$eventinfo = array('published' => 0, 'totallots' => 0, 'bids' => 0, 'bidders' => 0, 'winners' => 0, 'views' => 0, 'eventstatus' => '', 'currentlot' => 0, 'ended' => '', 'lotduration' => '', 'lots' => array());
		$sql = $sheel->db->query("
			SELECT e.lots, e.bidders, e.winners, e.datetime, e.enddatetime, e.currentlot, e.ended, e.secondsperlot, e.published, e.visible, e.views, p.bids, p.currentprice, p.currencyid, p.status, p.date_starts, p.haswinner, p.hasbuynowwinner, p.close_date
			FROM " . DB_PREFIX . "events e
			LEFT JOIN " . DB_PREFIX . "projects p ON (e.currentlot = p.lotid AND p.eventid = e.eventid)
			WHERE e.eventid = '" . intval($sheel->GPC['eventid']) . "'
				AND e.visible = '1'
				AND p.visible = '1'
			LIMIT 1
		");
		if ($sheel->db->num_rows($sql) > 0) {
			$ended = $published = 0;
			$lotduration = '';
			$lots = array();
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
				if ($res['currentlot'] <= 0) {
					$res['currentlot'] = 1;
				}
				// event lot duration
				if ($res['secondsperlot'] > 0) {
					if ($res['secondsperlot'] == 30) {
						$sheel->template->templateregistry['lotduration'] = '{_x_secs::30}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 60) {
						$sheel->template->templateregistry['lotduration'] = '{_x_min::1}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 120) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::2}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 180) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::3}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 240) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::4}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 300) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::5}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 600) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::10}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 900) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::15}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 1200) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::20}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					} else if ($res['secondsperlot'] == 1800) {
						$sheel->template->templateregistry['lotduration'] = '{_x_mins::30}';
						$sheel->template->templateregistry['lotdurationtext'] = '{_lot_time_uc}';
					}
				} else {
					$sheel->template->templateregistry['lotduration'] = $sheel->common->print_date($res['enddatetime'], 'M d, Y g:i:s T');
					$sheel->template->templateregistry['lotdurationtext'] = '{_lots_end_uc}';
					$res['currentlot'] = '-1';
				}
				// fetch all lots for viewing page
				$maxrowsdisplay = (isset($sheel->GPC['pp']) and is_numeric($sheel->GPC['pp'])) ? intval($sheel->GPC['pp']) : $sheel->config['globalfilters_maxrowsdisplay'];
				$limit = ' ORDER BY lotid ASC LIMIT ' . (($sheel->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
				$sqllots = $sheel->db->query("
					SELECT project_id, lotid, bids, status, date_starts, date_end, haswinner, hasbuynowwinner, currentprice, currencyid, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
					FROM " . DB_PREFIX . "projects
					WHERE eventid = '" . intval($sheel->GPC['eventid']) . "'
					$limit
				");
				if ($sheel->db->num_rows($sqllots) > 0) {
					while ($reslots = $sheel->db->fetch_array($sqllots, DB_ASSOC)) {
						$lotended = $lotstarted = 0;
						if ($res['published'] == 0) {
							$sheel->template->templateregistry['lotstatus'] = '<span class="bold redbright">{_unpublished}</span>';
						} else {
							if ($reslots['status'] == 'delisted' or $reslots['status'] == 'frozen' or $reslots['status'] == 'archived' or $reslots['status'] == 'draft') {
								$lotended = $lotstarted = 1;
								if ($reslots['haswinner']) {
									$sheel->template->templateregistry['lotstatus'] = '<span class="bold a_active">{_sold}</span>';
								} else {
									$sheel->template->templateregistry['lotstatus'] = '<span class="bold black">{_ended}</span>';
								}
							} else {
								if ($reslots['status'] == 'expired') {
									$lotended = $lotstarted = 1;
									if ($reslots['haswinner']) {
										$sheel->template->templateregistry['lotstatus'] = '<span class="bold a_active">{_sold}</span>';
									} else {
										$sheel->template->templateregistry['lotstatus'] = '<span class="bold black">{_ended}</span>';
									}
								} else if (strtotime($reslots['date_starts']) > strtotime(DATETIME24H)) {
									$sheel->template->templateregistry['lotstatus'] = '<span class="bold a_active">{_upcoming}</span>';
								} else {
									$lotstarted = 1;
									$sheel->template->templateregistry['lotstatus'] = '<span class="bold green">{_live}</span>';
								}
							}
						}
						$sheel->template->templateregistry['timeleft'] = $sheel->auction->auction_timeleft(false, $reslots['date_starts'], $reslots['mytime'], $reslots['starttime'], true);
						$sheel->template->templateregistry['starts'] = $sheel->auction->auction_timeleft(true, $reslots['date_starts'], $reslots['mytime'], $reslots['starttime'], false);
						$lots[$reslots['lotid']] = array(
							'itemid' => $reslots['project_id'],
							'bids' => $reslots['bids'],
							'bid' => $sheel->currency->format_local($reslots['currentprice'], $reslots['currencyid']),
							'buys' => $reslots['hasbuynowwinner'],
							'sold' => $reslots['haswinner'],
							'status' => $sheel->template->parse_template_phrases('lotstatus'),
							'timeleft' => $sheel->template->parse_template_phrases('timeleft'),
							'starts' => $sheel->template->parse_template_phrases('starts'),
							'started' => $lotstarted,
							'ended' => $lotended
						);
					}
				}
				// event status
				if ($res['published'] == 0) {
					$sheel->template->templateregistry['eventstatus'] = '<span class="bold redbright">{_unpublished}</span>';
				} else {
					$published = 1;
					if ($res['ended']) {
						$ended = 1;
						$sheel->template->templateregistry['eventstatus'] = '<span class="bold red">{_ended}</span>';
					} else if ($res['datetime'] > DATETIME24H) {
						$sheel->template->templateregistry['eventstatus'] = '<span class="bold green">{_upcoming}</span>';
					} else {
						$sheel->template->templateregistry['eventstatus'] = '<span class="bold green">{_live_now}</span>';
					}
				}
				$eventinfo = array(
					'published' => $published,
					// 0 or 1
					'totallots' => $res['lots'],
					// 2
					'bids' => $sheel->db->fetch_field(DB_PREFIX . "projects", "eventid = '" . intval($sheel->GPC['eventid']) . "'", "SUM(bids)"),
					// 21
					'bidders' => $res['bidders'],
					// 4, 5, 6
					'winners' => $res['winners'],
					// 0, 1, 2
					'views' => $res['views'],
					'lotduration' => strtoupper($sheel->template->parse_template_phrases('lotduration')),
					// ALL LOTS END X
					'lotdurationtext' => strtoupper($sheel->template->parse_template_phrases('lotdurationtext')),
					// LOTS END or LOT TIME
					'eventstatus' => $sheel->template->parse_template_phrases('eventstatus'),
					// <span class="bold green">{_live_now}</span>
					'currentlot' => $res['currentlot'],
					// -1 or 1,2,3,4,5
					'ended' => $ended,
					// 0 or 1
					'lots' => $lots
				);
			}
		}
		die(json_encode($eventinfo));
	} else if ($sheel->GPC['do'] == 'importupc') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if (isset($sheel->GPC['upc']) and $sheel->GPC['upc'] != '') { // check
				$sheel->GPC['upc'] = trim($sheel->GPC['upc']);
				$sheel->GPC['gtin'] = str_pad($sheel->GPC['upc'], 13, '0', STR_PAD_LEFT); // 37000962496 = 0037000962496
				$sql = $sheel->db->query("
					SELECT UPC, GTIN_NM, GTIN_DESC, IMG
					FROM " . DB_PREFIX . "brand_gtin
					WHERE UPC = '" . $sheel->db->escape_string($sheel->GPC['upc']) . "' OR GTIN_CD = '" . $sheel->db->escape_string($sheel->GPC['gtin']) . "'
					LIMIT 1
				");
				if ($sheel->db->num_rows($sql) <= 0) { // upc doesn't exist yet
					$sheel->db->query("
						INSERT INTO " . DB_PREFIX . "brand_gtin
						(GTIN_CD, UPC, CRAWLED, VISIBLE, IMG, SOURCE, QTY, COUNTRY, STATE, CITY, ZIP)
						VALUES (
						'" . $sheel->db->escape_string($sheel->GPC['gtin']) . "',
						'" . $sheel->db->escape_string($sheel->GPC['upc']) . "',
						'0',
						'0',
						'0',
						'SCANNER',
						'1',
						'" . $sheel->db->escape_string($sheel->GPC['country']) . "',
						'" . $sheel->db->escape_string($sheel->GPC['state']) . "',
						'" . $sheel->db->escape_string($sheel->GPC['city']) . "',
						'" . $sheel->db->escape_string($sheel->GPC['zipcode']) . "'
						)
					");
					$response = $sheel->crawler->fetch_upc_info($sheel->GPC['upc']);
					if (isset($response['error']) and !empty($response['error'])) { // hard error from the api
						die($response['error']);
					}
					die('1'); // <-- added
				} else { // update existing upc add +1 qty
					$res = $sheel->db->fetch_array($sql, DB_ASSOC);
					if (empty($res['UPC'])) {
						$sqltype = "+ 1";
						if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'subtract') {
							$sqltype = "- 1";
						}
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "brand_gtin
							SET UPC = '" . $sheel->db->escape_string($sheel->GPC['upc']) . "',
							SOURCE = 'SCANNER',
							QTY = QTY $sqltype,
							COUNTRY = '" . $sheel->db->escape_string($sheel->GPC['country']) . "',
							STATE = '" . $sheel->db->escape_string($sheel->GPC['state']) . "',
							CITY = '" . $sheel->db->escape_string($sheel->GPC['city']) . "',
							ZIP = '" . $sheel->db->escape_string($sheel->GPC['zipcode']) . "'
							WHERE GTIN_CD = '" . $sheel->db->escape_string($sheel->GPC['gtin']) . "'
							LIMIT 1
						");
					} else {
						$sqltype = "+ 1";
						if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'subtract') {
							$sqltype = "- 1";
						}
						$sheel->db->query("
							UPDATE " . DB_PREFIX . "brand_gtin
							SET QTY = QTY $sqltype,
							SOURCE = 'SCANNER',
							COUNTRY = '" . $sheel->db->escape_string($sheel->GPC['country']) . "',
							STATE = '" . $sheel->db->escape_string($sheel->GPC['state']) . "',
							CITY = '" . $sheel->db->escape_string($sheel->GPC['city']) . "',
							ZIP = '" . $sheel->db->escape_string($sheel->GPC['zipcode']) . "'
							WHERE UPC = '" . $sheel->db->escape_string($sheel->GPC['upc']) . "'
							LIMIT 1
						");
					}
					if (empty($res['GTIN_NM']) or empty($res['GTIN_DESC']) or $res['IMG'] <= 0) {
						$response = $sheel->crawler->fetch_upc_info($sheel->GPC['upc']);
						if (isset($response['error']) and !empty($response['error'])) { // hard error from the api
							die($response['error']);
						}
					}
					die('2'); // <-- updated
				}
			}
		}
		die('0'); // <-- noauth
	} else if ($sheel->GPC['do'] == 'refreshupctr') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			$html = $js = '';
			$sql = $sheel->db->query("
				SELECT GTIN_CD, CID, UPC, SKU, PRICE, QTY, GTIN_DEPT, FEATURES, ATTRIBUTES, BSIN, GTIN_NM, GTIN_DESC, SOURCE, IMG, IMG_UPC, CRAWLED, COUNTRY, STATE, CITY, ZIP
				FROM " . DB_PREFIX . "brand_gtin
				WHERE SOURCE = 'SCANNER'
				ORDER BY CRAWLED_DATE DESC
				LIMIT 250
			");
			if ($sheel->db->num_rows($sql) > 0) {
				while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
					$upcfolder = 'upc-' . substr($res['UPC'], 0, 3);
					if ($res['IMG_UPC'] != '') {
						if ($res['CRAWLED'] and file_exists(DIR_UPC . $upcfolder . '/' . $res['UPC'] . '.jpg')) { // saved locally or to cdn
							$res['icon'] = '<img src="' . HTTP_UPC . $upcfolder . '/' . $res['UPC'] . '.jpg" width="50">';
						} else { // load from remote url for preview before crawling
							$res['icon'] = '<img src="' . $res['IMG_UPC'] . '" width="50">';
						}
					} else {
						$res['icon'] = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.png" width="50">';
					}
					$res['CIDNAME'] = '';
					if ($res['CID'] > 0) {
						$res['CIDNAME'] = $sheel->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($res['CID']) . "'", "title_" . $_SESSION['sheeldata']['user']['slng']);
					}
					$res['GTIN_DEPT'] = ((mb_strlen($res['GTIN_DEPT']) < 5 or $res['GTIN_DEPT'] == '?') ? '' : o($sheel->print_string_wrap($res['GTIN_DEPT'], 40)) . '?');
					$res['crawled'] = (($res['CRAWLED']) ? 'Yes' : 'No');

					$js .= 'var options = {url: function(phrase) {return iL[\'AJAXURL\'] + \'?do=catsearch\';},getValue: function(element) {return element.name;},list: {maxNumberOfElements: 12,onClickEvent: function() {var namex = jQuery("#cidname_' . $res['UPC'] . '").getSelectedItemData().nameshort;var cid = jQuery("#cidname_' . $res['UPC'] . '").getSelectedItemData().cid;jQuery("#cid_' . $res['UPC'] . '").val(cid);jQuery("#cidname_' . $res['UPC'] . '").val(namex);},onSelectItemEvent: function() {var name = jQuery("#cidname_' . $res['UPC'] . '").getSelectedItemData().name;var cid = jQuery("#cidname_' . $res['UPC'] . '").getSelectedItemData().cid;jQuery("#cid_' . $res['UPC'] . '").val(cid);}},ajaxSettings: {dataType: "json",method: "POST",data: {dataType: "json"}},preparePostData: function(data) {data.q = jQuery("#cidname_' . $res['UPC'] . '").val();if (data.q == \'\') {jQuery("#cidname_' . $res['UPC'] . '").val(\'\');jQuery("#cid_' . $res['UPC'] . '").val(\'0\');}return data;},requestDelay: 500};jQuery("#cidname_' . $res['UPC'] . '").easyAutocomplete(options);';

					$html .= '<tr id="tr_selected_' . $res['UPC'] . '" valign="top"><td class="select"><div class="draw-input-wrapper draw-input-wrapper--inline"><input type="checkbox" name="produ_' . $res['UPC'] . '" id="producz_' . $res['UPC'] . '" value="' . $res['UPC'] . '" class="draw-checkbox" onchange="inlineCB.toggle(this)"><span class="draw-checkbox--styled"></span></div></td><td class="photo no-wrap" width="50"><a href="' . HTTPS_SERVER_ADMIN . 'brands/products/view/' . $res['GTIN_CD'] . '/"><span id="icon_' . $res['UPC'] . '">' . $res['icon'] . '</span></a></td><td class="name"><input type="text" class="draw-input-small required" value="' . o($res['GTIN_NM']) . '" name="product[' . $res['UPC'] . '][GTIN_NM]" id="title_' . $res['UPC'] . '"><div class="st" style="min-width:300px"><textarea class="draw-textarea textarea required" name="product[' . $res['UPC'] . '][GTIN_DESC]" id="desc_' . $res['UPC'] . '" placeholder="{_description}">' . o($res['GTIN_DESC']) . '</textarea></div><div class="type--subdued st">' . $res['COUNTRY'] . ' ' . $res['STATE'] . ' ' . $res['CITY'] . ' ' . $res['ZIP'] . '<input type="hidden" name="product[' . $res['UPC'] . '][COUNTRY]" value="' . $res['COUNTRY'] . '"><input type="hidden" name="product[' . $res['UPC'] . '][STATE]" value="' . $res['STATE'] . '"><input type="hidden" name="product[' . $res['UPC'] . '][CITY]" value="' . $res['CITY'] . '"><input type="hidden" name="product[' . $res['UPC'] . '][ZIP]" value="' . $res['ZIP'] . '"></div><div class="st"><a href="' . HTTPS_SERVER_ADMIN . 'brands/products/view/' . $res['GTIN_CD'] . '/" class="btn">{_edit}</a> ' . ((isset($sheel->show['ADMINCP_TEST_MODE']) and $sheel->show['ADMINCP_TEST_MODE']) ? '<button type="button" class="btn js-btn-loadable has-loading" bind-event-click="jQuery.growl.error({ title: \'{_demo_version}\', message: \'{_demo_mode_only}\' })">{_save}</button>' : '<button type="submit" class="btn js-btn-loadable has-loading" name="save">{_save}</button>') . '</div></td><td class="orders"><span class="type--subdued"><input type="text" class="draw-input-small" value="' . $res['PRICE'] . '" name="product[' . $res['UPC'] . '][PRICE]" id="price_' . $res['UPC'] . '"></span></td><td class="orders"><span class="type--subdued"><input type="text" class="draw-input-small" value="' . $res['QTY'] . '" name="product[' . $res['UPC'] . '][QTY]" id="qty_' . $res['UPC'] . '"></span></td><td class="orders"><input type="text" class="draw-input-small" value="' . $res['SKU'] . '" name="product[' . $res['UPC'] . '][SKU]" id="sku_' . $res['UPC'] . '" placeholder="SKU"></td><td class="orders"><input type="text" class="draw-input-small required" value="' . $res['CIDNAME'] . '" id="cidname_' . $res['UPC'] . '" placeholder="{_category}"><input type="hidden" name="product[' . $res['UPC'] . '][CID]" value="' . $res['CID'] . '" id="cid_' . $res['UPC'] . '"><div class="type--subdued smallest st" style="max-width:200px" title="Category title suggestion">' . $res['GTIN_DEPT'] . '</div></td><td class="orders"><span class="type--subdued">' . $res['UPC'] . '</span></td><td class="orders tc"><span id="crawled_' . $res['UPC'] . '" class="type--subdued">' . $res['crawled'] . '</span></td></tr>';
				}
			}
			$sheel->template->templateregistry['htmlx'] = $html;
			$html = $sheel->template->parse_template_phrases('htmlx');
			$array = array('js' => $js, 'html' => $html, 'eval' => 1);
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$array = json_encode($array);
			die($array);
		}
		die();
	} else if ($sheel->GPC['do'] == 'acpfsr') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin']) {
			if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'fixed') {
				$html = '<table><tr><th></th><th>{_price}</th><th>Compared</th><th>{_quantity}</th><th>{_duration}</th><th>{_shipping}</th><th>{_returns}</th></tr>';
				if (isset($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]) and !empty($_COOKIE['sheel_inline' . $sheel->GPC['prefix']])) {
					$tmp = explode('~', urldecode($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]));
					foreach ($tmp as $gtin_cd) {
						$sql = $sheel->db->query("
							SELECT UPC, GTIN_NM, GTIN_DESC, FEATURES, PRICE, IMG_UPC, CRAWLED, CID, QTY, SKU, BSIN
							FROM " . DB_PREFIX . "brand_gtin
							WHERE GTIN_CD = '" . $sheel->db->escape_string($gtin_cd) . "'
							LIMIT 1
						");
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$upc = $res['UPC'];
						$title = $res['GTIN_NM'];
						$description = $res['GTIN_DESC'];
						$sku = $res['SKU'];
						$bsin = $res['BSIN'];
						$qty = $res['QTY'];
						$price = $res['PRICE'];
						$img_upc = $res['IMG_UPC'];
						$crawled = $res['CRAWLED'];
						$cid = $res['CID'];
						$icon = '';
						$image = '';
						$upcfolder = 'upc-' . substr($upc, 0, 3);
						if ($img_upc != '') {
							if ($crawled and file_exists(DIR_UPC . $upcfolder . '/' . $upc . '.jpg')) { // saved locally or to cdn
								$icon = '<img src="' . HTTP_UPC . $upcfolder . '/' . $upc . '.jpg" width="50">';
								$image = HTTP_UPC . $upcfolder . '/' . $upc . '.jpg';
							} else { // load from remote url for preview before crawling
								$icon = '<img src="' . $img_upc . '" width="50">';
								$image = $img_upc;
							}
						} else {
							$icon = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.png" width="50">';
							$image = $sheel->config['imgcdn'] . 'v5/img_nophoto.png';
						}
						$features = '';
						$featurestmp = $res['FEATURES'];
						if (strlen($featurestmp) > 5) {
							$maxf = 5;
							$ftmp = unserialize($featurestmp);
							if (is_array($ftmp) and count($ftmp) > 0) {
								$c = 0;
								foreach ($ftmp as $key => $feature) {
									$c++;
									$features .= '<input type="hidden" name="product[' . $gtin_cd . '][feature' . $c . ']" value="' . ((is_numeric($key)) ? o(trim($feature)) : o(trim($key)) . ': ' . o(trim($feature))) . '" id="feature' . $c . '_' . $gtin_cd . '" class="acp-hidden-input">';
									if ($c == $maxf) {
										break;
									}
								}
							}
						}
						$duration = $sheel->auction_post->duration('7', 'product[' . $gtin_cd . '][duration]', false, 'D', false, $cid, true, 1, 'duration_' . $gtin_cd, 'draw-select acp-required acp-input', false);
						$shipping = $sheel->shipping->print_seller_shipping_profiles($sheel->GPC['userid'], '', 'product[' . $gtin_cd . '][sspid]', 'sspid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$returns = $sheel->shipping->print_seller_returnpolicy_profiles($sheel->GPC['userid'], '', 'product[' . $gtin_cd . '][srpid]', 'srpid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$html .= '<tr><td title="' . o($title) . '" style="min-width:50px;width:50px">' . $icon . '</td><td>' . $features . '<input type="hidden" name="product[' . $gtin_cd . '][img]" value="' . $image . '" id="img_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][title]" value="' . o($title) . '" id="title_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][description]" value="' . o($description) . '" id="desc_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][cid]" value="' . $cid . '" id="cid_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][bsin]" value="' . $bsin . '" id="bsin_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][sku]" value="' . $sku . '" id="sku_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][upc]" value="' . $upc . '" id="upc_' . $gtin_cd . '" class="acp-hidden-input"><input type="text" name="product[' . $gtin_cd . '][price]" id="price_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="{_price}" autocomplete="off" value="' . sprintf("%01.2f", $price) . '"></td><td><input type="text" name="product[' . $gtin_cd . '][comparedprice]" id="comparedprice_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Compared at price" autocomplete="off" value="' . sprintf("%01.2f", ($price + 10)) . '"></td><td><input type="text" name="product[' . $gtin_cd . '][qty]" id="qty_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="{_quantity}" autocomplete="off" value="' . $qty . '"></td><td>' . $duration . '</td><td>' . $shipping . '</td><td>' . $returns . '</td></tr>';
					}
				}
				$html .= '</table>';
			} else if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'auction') {
				$html = '<table><tr><th></th><th>Start Price</th><th>Reserve</th><th>{_duration}</th><th>{_shipping}</th><th>{_returns}</th></tr>';
				if (isset($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]) and !empty($_COOKIE['sheel_inline' . $sheel->GPC['prefix']])) {
					$tmp = explode('~', urldecode($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]));
					foreach ($tmp as $gtin_cd) {
						$sql = $sheel->db->query("
							SELECT UPC, GTIN_NM, GTIN_DESC, FEATURES, PRICE, IMG_UPC, CRAWLED, CID, SKU, BSIN
							FROM " . DB_PREFIX . "brand_gtin
							WHERE GTIN_CD = '" . $sheel->db->escape_string($gtin_cd) . "'
							LIMIT 1
						");
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$upc = $res['UPC'];
						$title = $res['GTIN_NM'];
						$description = $res['GTIN_DESC'];
						$sku = $res['SKU'];
						$bsin = $res['BSIN'];
						$price = $res['PRICE'];
						$img_upc = $res['IMG_UPC'];
						$crawled = $res['CRAWLED'];
						$cid = $res['CID'];
						$icon = '';
						$image = '';
						$upcfolder = 'upc-' . substr($upc, 0, 3);
						if ($img_upc != '') {
							if ($crawled and file_exists(DIR_UPC . $upcfolder . '/' . $upc . '.jpg')) { // saved locally or to cdn
								$icon = '<img src="' . HTTP_UPC . $upcfolder . '/' . $upc . '.jpg" width="50">';
								$image = HTTP_UPC . $upcfolder . '/' . $upc . '.jpg';
							} else { // load from remote url for preview before crawling
								$icon = '<img src="' . $img_upc . '" width="50">';
								$image = $img_upc;
							}
						} else {
							$icon = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.png" width="50">';
							$image = $sheel->config['imgcdn'] . 'v5/img_nophoto.png';
						}
						$features = '';
						$featurestmp = $res['FEATURES'];
						if (strlen($featurestmp) > 5) {
							$maxf = 5;
							$ftmp = unserialize($featurestmp);
							if (is_array($ftmp) and count($ftmp) > 0) {
								$c = 0;
								foreach ($ftmp as $key => $feature) {
									$c++;
									$features .= '<input type="hidden" name="product[' . $gtin_cd . '][feature' . $c . ']" value="' . ((is_numeric($key)) ? o(trim($feature)) : o(trim($key)) . ': ' . o(trim($feature))) . '" id="feature' . $c . '_' . $gtin_cd . '" class="acp-hidden-input">';
									if ($c == $maxf) {
										break;
									}
								}
							}
						}
						$duration = $sheel->auction_post->duration('7', 'product[' . $gtin_cd . '][duration]', false, 'D', false, $cid, true, 1, 'duration_' . $gtin_cd, 'draw-select acp-required acp-input', false);
						$shipping = $sheel->shipping->print_seller_shipping_profiles($sheel->GPC['userid'], '', 'product[' . $gtin_cd . '][sspid]', 'sspid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$returns = $sheel->shipping->print_seller_returnpolicy_profiles($sheel->GPC['userid'], '', 'product[' . $gtin_cd . '][srpid]', 'srpid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$html .= '<tr><td title="' . o($title) . '" style="min-width:50px;width:50px">' . $icon . '</td><td>' . $features . '<input type="hidden" name="product[' . $gtin_cd . '][img]" value="' . $image . '" id="img_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][title]" value="' . o($title) . '" id="title_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][description]" value="' . o($description) . '" id="desc_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][cid]" value="' . $cid . '" id="cid_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][bsin]" value="' . $bsin . '" id="bsin_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][sku]" value="' . $sku . '" id="sku_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][upc]" value="' . $upc . '" id="upc_' . $gtin_cd . '" class="acp-hidden-input"><input type="text" name="product[' . $gtin_cd . '][startprice]" id="startprice_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Starting Price" autocomplete="off" value="' . sprintf("%01.2f", $price) . '"></td><td><input type="text" name="product[' . $gtin_cd . '][reserveprice]" id="reserveprice_' . $gtin_cd . '" class="draw-input acp-input" placeholder="{_optional}" autocomplete="off" value=""></td><td>' . $duration . '</td><td>' . $shipping . '</td><td>' . $returns . '</td></tr>';
					}
				}
				$html .= '</table>';
			} else if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'classified') {
				$html = '<table><tr><th></th><th>{_price}</th><th>Compared</th><th>{_phone_number}</th><th>{_duration}</th></tr>';
				if (isset($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]) and !empty($_COOKIE['sheel_inline' . $sheel->GPC['prefix']])) {
					$tmp = explode('~', urldecode($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]));
					foreach ($tmp as $gtin_cd) {
						$sql = $sheel->db->query("
							SELECT UPC, GTIN_NM, GTIN_DESC, FEATURES, PRICE, IMG_UPC, CRAWLED, CID, SKU, BSIN
							FROM " . DB_PREFIX . "brand_gtin
							WHERE GTIN_CD = '" . $sheel->db->escape_string($gtin_cd) . "'
							LIMIT 1
						");
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$upc = $res['UPC'];
						$title = $res['GTIN_NM'];
						$description = $res['GTIN_DESC'];
						$sku = $res['SKU'];
						$bsin = $res['BSIN'];
						$price = $res['PRICE'];
						$img_upc = $res['IMG_UPC'];
						$crawled = $res['CRAWLED'];
						$cid = $res['CID'];
						$icon = '';
						$image = '';
						$upcfolder = 'upc-' . substr($upc, 0, 3);
						if ($img_upc != '') {
							if ($crawled and file_exists(DIR_UPC . $upcfolder . '/' . $upc . '.jpg')) { // saved locally or to cdn
								$icon = '<img src="' . HTTP_UPC . $upcfolder . '/' . $upc . '.jpg" width="50">';
								$image = HTTP_UPC . $upcfolder . '/' . $upc . '.jpg';
							} else { // load from remote url for preview before crawling
								$icon = '<img src="' . $img_upc . '" width="50">';
								$image = $img_upc;
							}
						} else {
							$icon = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.png" width="50">';
							$image = $sheel->config['imgcdn'] . 'v5/img_nophoto.png';
						}
						$features = '';
						$featurestmp = $res['FEATURES'];
						if (strlen($featurestmp) > 5) {
							$maxf = 5;
							$ftmp = unserialize($featurestmp);
							if (is_array($ftmp) and count($ftmp) > 0) {
								$c = 0;
								foreach ($ftmp as $key => $feature) {
									$c++;
									$features .= '<input type="hidden" name="product[' . $gtin_cd . '][feature' . $c . ']" value="' . ((is_numeric($key)) ? o(trim($feature)) : o(trim($key)) . ': ' . o(trim($feature))) . '" id="feature' . $c . '_' . $gtin_cd . '" class="acp-hidden-input">';
									if ($c == $maxf) {
										break;
									}
								}
							}
						}
						$duration = $sheel->auction_post->duration('7', 'product[' . $gtin_cd . '][duration]', false, 'D', false, $cid, true, 1, 'duration_' . $gtin_cd, 'draw-select acp-required acp-input', false);
						$phone = $sheel->fetch_user('phone', $sheel->GPC['userid']);
						$html .= '<tr><td title="' . o($title) . '" style="min-width:50px;width:50px">' . $icon . '</td><td>' . $features . '<input type="hidden" name="product[' . $gtin_cd . '][img]" value="' . $image . '" id="img_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][title]" value="' . o($title) . '" id="title_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][description]" value="' . o($description) . '" id="desc_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][cid]" value="' . $cid . '" id="cid_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][bsin]" value="' . $bsin . '" id="bsin_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][sku]" value="' . $sku . '" id="sku_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][upc]" value="' . $upc . '" id="upc_' . $gtin_cd . '" class="acp-hidden-input"><input type="text" name="product[' . $gtin_cd . '][price]" id="price_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Price" autocomplete="off" value="' . sprintf("%01.2f", $price) . '"></td><td><input type="text" name="product[' . $gtin_cd . '][comparedprice]" id="comparedprice_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Compared at price" autocomplete="off" value="' . sprintf("%01.2f", ($price + 10)) . '"></td><td><input type="text" name="product[' . $gtin_cd . '][phone]" id="phone_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Phone Number" autocomplete="off" value="' . o($phone) . '"></td><td>' . $duration . '</td></tr>';
					}
				}
				$html .= '</table>';
			} else if (isset($sheel->GPC['type']) and $sheel->GPC['type'] == 'event') {
				$html = '<table><tr><th></th><th>Start Price</th><th>{_shipping}</th><th>{_returns}</th></tr>';
				if (isset($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]) and !empty($_COOKIE['sheel_inline' . $sheel->GPC['prefix']])) {
					$tmp = explode('~', urldecode($_COOKIE['sheel_inline' . $sheel->GPC['prefix']]));
					foreach ($tmp as $gtin_cd) {
						$sql = $sheel->db->query("
							SELECT UPC, GTIN_NM, GTIN_DESC, FEATURES, PRICE, IMG_UPC, CRAWLED, CID, SKU, BSIN
							FROM " . DB_PREFIX . "brand_gtin
							WHERE GTIN_CD = '" . $sheel->db->escape_string($gtin_cd) . "'
							LIMIT 1
						");
						$res = $sheel->db->fetch_array($sql, DB_ASSOC);
						$upc = $res['UPC'];
						$title = $res['GTIN_NM'];
						$description = $res['GTIN_DESC'];
						$sku = $res['SKU'];
						$bsin = $res['BSIN'];
						$price = $res['PRICE'];
						$img_upc = $res['IMG_UPC'];
						$crawled = $res['CRAWLED'];
						$cid = $res['CID'];
						$icon = '';
						$image = '';
						$upcfolder = 'upc-' . substr($upc, 0, 3);
						if ($img_upc != '') {
							if ($crawled and file_exists(DIR_UPC . $upcfolder . '/' . $upc . '.jpg')) { // saved locally or to cdn
								$icon = '<img src="' . HTTP_UPC . $upcfolder . '/' . $upc . '.jpg" width="50">';
								$image = HTTP_UPC . $upcfolder . '/' . $upc . '.jpg';
							} else { // load from remote url for preview before crawling
								$icon = '<img src="' . $img_upc . '" width="50">';
								$image = $img_upc;
							}
						} else {
							$icon = '<img src="' . $sheel->config['imgcdn'] . 'v5/img_nophoto.png" width="50">';
							$image = $sheel->config['imgcdn'] . 'v5/img_nophoto.png';
						}
						$features = '';
						$featurestmp = $res['FEATURES'];
						if (strlen($featurestmp) > 5) {
							$maxf = 5;
							$ftmp = unserialize($featurestmp);
							if (is_array($ftmp) and count($ftmp) > 0) {
								$c = 0;
								foreach ($ftmp as $key => $feature) {
									$c++;
									$features .= '<input type="hidden" name="product[' . $gtin_cd . '][feature' . $c . ']" value="' . ((is_numeric($key)) ? o(trim($feature)) : o(trim($key)) . ': ' . o(trim($feature))) . '" id="feature' . $c . '_' . $gtin_cd . '" class="acp-hidden-input">';
									if ($c == $maxf) {
										break;
									}
								}
							}
						}
						$userid = $sheel->db->fetch_field(DB_PREFIX . "events", "eventid = '" . intval($sheel->GPC['userid']) . "'", "userid");
						$shipping = $sheel->shipping->print_seller_shipping_profiles($userid, '', 'product[' . $gtin_cd . '][sspid]', 'sspid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$returns = $sheel->shipping->print_seller_returnpolicy_profiles($userid, '', 'product[' . $gtin_cd . '][srpid]', 'srpid_' . $gtin_cd, 'draw-select acp-required acp-input');
						$html .= '<tr><td title="' . o($title) . '" style="min-width:50px;width:50px">' . $icon . '</td><td>' . $features . '<input type="hidden" name="product[' . $gtin_cd . '][img]" value="' . $image . '" id="img_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][title]" value="' . o($title) . '" id="title_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][description]" value="' . o($description) . '" id="desc_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][cid]" value="' . $cid . '" id="cid_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][bsin]" value="' . $bsin . '" id="bsin_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][sku]" value="' . $sku . '" id="sku_' . $gtin_cd . '" class="acp-hidden-input"><input type="hidden" name="product[' . $gtin_cd . '][upc]" value="' . $upc . '" id="upc_' . $gtin_cd . '" class="acp-hidden-input"><input type="text" name="product[' . $gtin_cd . '][startprice]" id="startprice_' . $gtin_cd . '" class="draw-input acp-required acp-input" placeholder="Starting Price" autocomplete="off" value="' . sprintf("%01.2f", $price) . '"></td><td>' . $shipping . '</td><td>' . $returns . '</td></tr>';
					}
				}
				$html .= '</table>';
			}
			$sheel->template->templateregistry['htmlx'] = $html;
			$html = $sheel->template->parse_template_phrases('htmlx');
			die($html);
		}
	} else if ($sheel->GPC['do'] == 'lollipopgetimage') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['url'])) {
				$url = $sheel->GPC['url'];
				$mime = pathinfo($url, PATHINFO_EXTENSION);
				if (function_exists('curl_version')) {
					$handle = curl_init();
					curl_setopt($handle, CURLOPT_URL, $url);
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
					$data = curl_exec($handle);
					curl_close($handle);
				} else {
					$data = file_get_contents($url);
				}
				$imageData = base64_encode($data);
				$formatted = 'data: ' . $mime . ';base64,' . $imageData;
				die($formatted);
			}
		}
	} else if ($sheel->GPC['do'] == 'lollipopsaveimage') {
		if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0) {
			if (isset($sheel->GPC['imgData']) and !empty($sheel->GPC['imgData']) and isset($sheel->GPC['hash']) and !empty($sheel->GPC['hash']) and isset($sheel->GPC['filename']) and !empty($sheel->GPC['filename'])) {
				$base_to_php = explode(',', $sheel->GPC['imgData']);
				$data = base64_decode($base_to_php[1]);
				$path = DIR_AUCTION_ATTACHMENTS . $sheel->GPC['hash'] . '/' . $sheel->GPC['filename'];
				file_put_contents($path, $data);
				$attachid = $sheel->db->fetch_field(DB_PREFIX . "attachment", "filehash = '" . $sheel->db->escape_string($sheel->GPC['hash']) . "' AND user_id = '" . intval($_SESSION['sheeldata']['user']['userid']) . "'", "attachid");
				if ($attachid > 0) { // rebuild and resize saved image
					$sheel->auction_pictures_rebuilder->process_picture_rebuilder($attachid);
				}
				die('1');
			}
		}
		die('0');
	} else if ($sheel->GPC['do'] == 'acpimporttheme') { // acp import theme

	} else if ($sheel->GPC['do'] == 'acpimportskin') { // acp import skin

	} else if ($sheel->GPC['do'] == 'tzoffset') { // set tzoffset in seconds
		if (isset($sheel->GPC['ctz']) and !empty($sheel->GPC['ctz'])) {
			set_cookie('tzoffset', $sheel->datetimes->fetch_timezone_offset($sheel->config['globalserverlocale_sitetimezone'], $sheel->GPC['ctz']));
			set_cookie('timezone', $sheel->GPC['ctz']);
		}
	}
}
?>
