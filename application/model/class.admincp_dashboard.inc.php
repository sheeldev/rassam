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
		$dashboard['userspending'] = (int) $members['count'];

		$members24h = $this->sheel->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users
			WHERE date_added LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newmemberstoday'] = intval($members24h['count']);

		return $dashboard;
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
				'orders' => array(
					'totalorders' => $this->sheel->admincp_stats->fetch($what, $period, 'totalorders'),
					'totalquantity' => $this->sheel->admincp_stats->fetch($what, $period, 'totalquantity'),
					'invoiced' => $this->sheel->admincp_stats->fetch($what, $period, 'invoiced'),
					'archived' => $this->sheel->admincp_stats->fetch($what, $period, 'archived'),
					'smallorders' => $this->sheel->admincp_stats->fetch($what, $period, 'smallorders'),
					'mediumorders' => $this->sheel->admincp_stats->fetch($what, $period, 'mediumorders'),
					'largeorders' => $this->sheel->admincp_stats->fetch($what, $period, 'largeorders'),
					'label' => $this->sheel->admincp_stats->fetch($what, $period, 'orderlabel'),
					'series' => $this->sheel->admincp_stats->fetch($what, $period, 'orderseries')
				),
				'stats' => array(
					'assembliescategories' => $this->sheel->admincp_stats->fetch($what, $period, 'assembliescategories'),
					'assembliesevents' => $this->sheel->admincp_stats->fetch($what, $period, 'assembliesevents'),
					'topdestinations' => $this->sheel->admincp_stats->fetch($what, $period, 'topdestinations'),
					'topcustomers' => $this->sheel->admincp_stats->fetch($what, $period, 'topcustomers'),
					'topentities' => $this->sheel->admincp_stats->fetch($what, $period, 'topentities'),
					'ordersizes' => $this->sheel->admincp_stats->fetch($what, $period, 'ordersizes'),
					'analysis' => $this->sheel->admincp_stats->fetch($what, $period, 'analysis'),
				)
			);
		}
		return $stats;
	}
	function fetch_diskspace()
	{
		$return = array();
		$content = $this->sheel->fetch_curl_string($this->sheel->config['system_endpoint'] . '/freespace/');
		if ($content['response']['code'] == 200) {
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