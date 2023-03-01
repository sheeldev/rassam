<?php
/**
 * Language class to perform the majority of User functions in sheel.
 *
 * @package      sheel\User
 * @version      1.0.0.0
 * @author       sheel
 */
class user
{
	protected $sheel;
	/**
	 * Constructor
	 */
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
	}

	function fetch_default_bill_profileid($userid = 0)
	{
		$profileid = 0;
		$sql = $this->sheel->db->query("
			SELECT cp.id
			FROM " . DB_PREFIX . "company_profiles cp
			LEFT JOIN " . DB_PREFIX . "users u ON (cp.company_id = u.companyid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND cp.type = 'billing'
				AND cp.isdefault = '1'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['id'];
		}
		return $profileid;
	}

	function fetch_default_ship_profileid($userid = 0)
	{
		$profileid = 0;
		$sql = $this->sheel->db->query("
			SELECT cp.id
			FROM " . DB_PREFIX . "company_profiles cp
			LEFT JOIN " . DB_PREFIX . "users u ON (cp.company_id = u.companyid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND cp.type = 'shipping'
				AND cp.isdefault = '1'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['id'];
		}
		return $profileid;
	}

	function fetch_shipping_profile_countries_array($userid = 0, $serialized = false, $fallback_countryid = 0)
	{
		$array = array();
		$sql = $this->sheel->db->query("
			SELECT cp.country
			FROM " . DB_PREFIX . "company_profiles cp
			LEFT JOIN " . DB_PREFIX . "users u ON (cp.company_id = u.companyid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND cp.type = 'shipping'
				AND cp.status = '1'
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$array[] = $this->sheel->common_location->fetch_country_id($res['country'], $this->sheel->language->fetch_site_slng());
			}
		} else if ($fallback_countryid > 0) {
			$array[] = intval($fallback_countryid);
		}
		$array = array_unique($array); // remove duplicate country profiles
		if ($serialized) {
			$array = serialize($array);
		}
		return $array;
	}

	function print_shipping_profile_countries($userid = 0)
	{
		$html = '';
		$array = array();
		$sql = $this->sheel->db->query("
			SELECT cp.country
			FROM " . DB_PREFIX . "company_profiles cp
			LEFT JOIN " . DB_PREFIX . "users u ON (cp.company_id = u.companyid)
			WHERE u.user_id = '" . intval($userid) . "'
				AND cp.type = 'shipping'
				AND cp.status = '1'
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$array[] = $res['country'];
			}
		}
		$array = array_unique($array); // remove duplicate country profiles
		if (count($array) == 1) {
			foreach ($array as $country) {
				$html .= $country;
			}
		} else if (count($array) == 2) {
			$c = 0;
			foreach ($array as $country) {
				$c++;
				if ($c == 1) { // first one
					$html .= $country . ' {_or} ';
				} else {
					$html .= $country;
				}
			}
		} else {
			$t = count($array); // 3
			$c = 0;
			foreach ($array as $country) {
				$c++;
				if ($c == $t) { // last one!
					$html .= ' {_or} ' . $country;
				} else if ($c == 1) { // first one
					$html .= $country;
				} else { // 1st, 2nd
					$html .= ', ' . $country;
				}
			}
		}
		unset($array);
		$this->sheel->template->templateregistry['countries'] = $html;
		$html = $this->sheel->template->parse_template_phrases('countries');
		return $html;
	}
}
?>