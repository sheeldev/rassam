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

		$members24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users
			WHERE date_added LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newmemberstoday'] = intval($members24h['count']);

		return $dashboard;
	}
	function fetch_diskspace()
	{
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
}
?>
