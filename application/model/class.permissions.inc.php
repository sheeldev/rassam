<?php
class permissions
{
	protected $sheel;
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	function check_access($customerid = 0, $accessname = '', $subscriptionid = 0)
	{
		$value = 'no';
		$customerid = intval($customerid);
		$subscriptionid = intval($subscriptionid);
		if ($customerid > 0 and !empty($accessname) and $subscriptionid <= 0) {
			$sql = $this->sheel->db->query("
				SELECT c.subscriptionid, c.customerid, perm.value
				FROM " . DB_PREFIX . "subscription_customer c
				LEFT JOIN " . DB_PREFIX . "subscription sub ON (c.subscriptionid = sub.subscriptionid)
				LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (c.subscriptionid = perm.subscriptionid)
				WHERE c.customerid = '" . intval($customerid) . "'
					AND sub.active = 'yes'
					AND sub.type = 'product'
					AND c.active = 'yes'
					AND perm.subscriptionid = sub.subscriptionid
					AND perm.accessname = '" . $this->sheel->db->escape_string($accessname) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$sql2 = $this->sheel->db->query("
					SELECT value
					FROM " . DB_PREFIX . "subscription_customer_exempt
					WHERE customerid = '" . intval($customerid) . "'
						AND accessname = '" . $this->sheel->db->escape_string($accessname) . "'
						AND active = '1'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($sql2) > 0) { // does admin force a permission exemption?
					$res2 = $this->sheel->db->fetch_array($sql2, DB_ASSOC);
					if ($accessname == 'bidlimitperday') { // allows admin to offer bidder extra bids on a per (day/month) basis
						$value = ($res['value'] + $res2['value']);
					} else {
						$value = $res2['value'];
					}
				} else { // if there is no exemption for this user for this permission resource
					$value = $res['value'];
				}
			}
		} else if ($customerid <= 0 and !empty($accessname) and $subscriptionid > 0) {
			$sql = $this->sheel->db->query("
				SELECT perm.value
				FROM " . DB_PREFIX . "subscription sub
				LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (sub.subscriptionid = perm.subscriptionid)
				WHERE sub.subscriptionid = '" . intval($subscriptionid) . "'
					AND sub.active = 'yes'
					AND sub.type = 'product'
					AND perm.subscriptionid = sub.subscriptionid
					AND perm.accessname = '" . $this->sheel->db->escape_string($accessname) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$value = $res['value'];
			}
		}
		return $value;
	}
	function can_post_html($userid)
	{
		return (($this->check_access($userid, 'posthtml') == "yes") or $this->sheel->fetch_user('posthtml', $userid)) ? true : false;
	}
	/**
	 * Function to fetch and print a subscription's permission name
	 *
	 * @param        string      permission variable to process
	 *
	 * @return	string      Returns the membership permission name and description
	 */
	function fetch_permission_name($variable = '')
	{
		$arr['_' . $variable . '_text'] = $arr['_' . $variable . '_desc'] = '';
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : $this->sheel->language->fetch_site_slng();
		$sql = $this->sheel->db->query("
                        SELECT text_" . $slng . " AS text, varname
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE varname LIKE '%" . $this->sheel->db->escape_string($variable) . "%'
                        LIMIT 2
                ");
		while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
			$arr[$res['varname']] = $res['text'];
		}
		return array('text' => $arr['_' . $variable . '_text'], 'description' => $arr['_' . $variable . '_desc']);
	}
}
?>