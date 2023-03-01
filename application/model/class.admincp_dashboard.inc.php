<?php
/**
* AdminCP Dashboard class to fetch the information bits on the Admin Control Panel Dashboard
*
* @package      Sheel\AdminCP\Dashboard
* @version      1.0.0.0
* @author       Sheel
*/
class admincp_dashboard extends admincp
{
	/**
	* Function to fetch important dashboard template variables for information and overview
	*
	* @return      array
	*/
	function fetch()
	{
		$dashboard = array();
		// members moderation count
		$members = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users
			WHERE status = 'moderated'
		");
		$dashboard['userspending'] = (int)$members['count'];
		// auction moderation count
		$auctions = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "projects
			WHERE visible = '0'
		");
		$dashboard['itemspending'] = (int)$auctions['count'];
		$disputes = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices
			WHERE indispute = '1'
				AND indisputeresponse = ''
		");
		$dashboard['paymentdisputes'] = (int)$disputes['count'];
		// attachment moderation count
		$attach = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '0'
		");
		$dashboard['attachmentspending'] = (int)$attach['count'];
		// withdraws pending count
		$withdraws = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid > 0
				AND iswithdraw = '1'
				AND status = 'scheduled'
		");
		$dashboard['withdrawspending'] = (int)$withdraws['count'];
		// unpaid invoices count
		$unpaid = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users AS c
			LEFT JOIN " . DB_PREFIX . "invoices AS invoices ON (c.user_id = invoices.user_id)
			WHERE invoices.status = 'unpaid'
				AND invoices.archive = '0'
				AND invoices.invoicetype != 'escrow'
				AND invoices.invoicetype != 'p2b'
				AND invoices.invoicetype != 'credit'
				AND invoices.invoicetype != 'buynow'
				AND invoices.invoiceid > 0
				AND invoices.iswithdraw = '0'
				AND invoices.isdeposit = '0'
				AND invoices.totalamount > 0

		");
		$dashboard['unpaidinvoices'] = (int)$unpaid['count'];
		// unpaid scheduled transactions count
		$scheduled = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices AS invoices
			WHERE (invoices.status = 'scheduled')
				AND invoices.invoiceid > 0
				AND invoices.iswithdraw = '0'
				AND invoices.isdeposit = '0'
				AND invoicetype != 'escrow'
				AND invoicetype != 'p2b'
		");
		$dashboard['scheduledtransactions'] = (int)$scheduled['count'];
		// 24 hour information preview
		$members24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users
			WHERE date_added LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newmemberstoday'] = intval($members24h['count']);
		$product24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_added LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newitemstoday'] = intval($product24h['count']);
		$productbids24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "project_bids
			WHERE date_added LIKE ('%" . DATETODAY . "%')
				AND state = 'product'
		");
		$dashboard['newbidstoday'] = intval($productbids24h['count']);
		$productexpired24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_end LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['expireditemstoday'] = intval($productexpired24h['count']);
		$productdelisted24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE close_date LIKE ('%" . DATETODAY . "%')
				AND status = 'delisted'
		");
		$dashboard['delisteditemstoday'] = intval($productdelisted24h['count']);
		return $dashboard;
	}
	function print_keyword_searched_in_categories($keyword = '')
	{
		$html = '';
		$sql = $this->sheel->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "search
			WHERE keyword = '" . $this->sheel->db->escape_string($keyword) . "'
				AND cid > 0
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$count = 0;
			$html .= '<div class="type--subdued">';
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$c = $this->sheel->categories->title($_SESSION['sheeldata']['user']['slng'], $res['cid']);
				if ($c != 'Unknown' AND $c != '{_unknown}' AND !empty($c))
				{
					$html .= trim($this->sheel->categories->title($_SESSION['sheeldata']['user']['slng'], $res['cid'])) . ', ';
					$count++;
				}
			}
			if ($count > 0)
			{
				$html = substr($html, 0, -2);
				$html .= '</div>';
			}
			else
			{
				return '';
			}
		}
		return $html;
	}
	function fetch_diskspace()
	{
		if ($this->sheel->config['license_type'] == 'leased')
		{ // leased
			$return = array();
			$content = $this->sheel->fetch_curl_string($this->sheel->config['billing_endpoint'] . LICENSEKEY . '/freespace/');
			if ($content['response']['code'] == 200)
			{
				$xml = simpleXML_load_string($content['body']);
				$return['used'] = $xml->data[0]->_count; // used mb 1437.24
				$return['max'] = $xml->data[0]->_max; // max mb 5096.00
				$return['free'] = ($return['max'] - $return['used']); // free mb 3658.76
				$return['percent'] = $xml->data[0]->percent;
				$return['percentfree'] = (100 - $return['percent']);
			}
			return $return;
		}
		return false;
	}
}
?>
