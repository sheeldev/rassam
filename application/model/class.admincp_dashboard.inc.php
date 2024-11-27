<?php
class admincp_dashboard extends admincp
{
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
		$content = $this->sheel->fetch_curl_string($this->sheel->config['system_endpoint'] . '/freespace/');
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
