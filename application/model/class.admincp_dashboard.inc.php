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

	function stats($what = '', $period = 'today', $company='', $country)
	{
		if ($what == 'home') {
			$stats = array(

				'visitors' => array(
					'visitors' => $this->sheel->admincp_stats->fetch($what, $period, 'visitors', '', ''),
					'uniquevisitors' => $this->sheel->admincp_stats->fetch($what, $period, 'uniquevisitors', '', ''),
					'label' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorlabel', '', ''),
					'series' => $this->sheel->admincp_stats->fetch($what, $period, 'visitorseries', '', ''),
					'pageviews' => $this->sheel->admincp_stats->fetch($what, $period, 'pageviews', '', ''),
					'mostactive' => $this->sheel->admincp_stats->fetch($what, $period, 'mostactive', '', ''),
				),
				'stats' => array(
					'topcountries' => $this->sheel->admincp_stats->fetch($what, $period, 'topcountries', '', ''),
					'topdevices' => $this->sheel->admincp_stats->fetch($what, $period, 'topdevices', '', ''),
					'topbrowsers' => $this->sheel->admincp_stats->fetch($what, $period, 'topbrowsers', '', ''),
					'trafficsources' => $this->sheel->admincp_stats->fetch($what, $period, 'trafficsources', '', ''),
					'toplandingpages' => $this->sheel->admincp_stats->fetch($what, $period, 'toplandingpages', '', '')
				)
			);
		}
		if ($what == 'dashboard') {
			$stats = array(
				'orders' => array(
					'totalorders' => $this->sheel->admincp_stats->fetch($what, $period, 'totalorders', $company, $country),
					'totalquantity' => $this->sheel->admincp_stats->fetch($what, $period, 'totalquantity', $company, $country),
					'invoiced' => $this->sheel->admincp_stats->fetch($what, $period, 'invoiced', $company, $country),
					'archived' => $this->sheel->admincp_stats->fetch($what, $period, 'archived', $company, $country),
					'smallorders' => $this->sheel->admincp_stats->fetch($what, $period, 'smallorders', $company, $country),
					'mediumorders' => $this->sheel->admincp_stats->fetch($what, $period, 'mediumorders', $company, $country),
					'largeorders' => $this->sheel->admincp_stats->fetch($what, $period, 'largeorders', $company, $country),
					'label' => $this->sheel->admincp_stats->fetch($what, $period, 'orderlabel', $company, $country),
					'series' => $this->sheel->admincp_stats->fetch($what, $period, 'orderseries', $company, $country)
				),
				'stats' => array(
					'assembliescategories' => $this->sheel->admincp_stats->fetch($what, $period, 'assembliescategories', $company, $country),
					'assembliesevents' => $this->sheel->admincp_stats->fetch($what, $period, 'assembliesevents', $company, $country),
					'topdestinations' => $this->sheel->admincp_stats->fetch($what, $period, 'topdestinations', $company, $country),
					'deliveries' => $this->sheel->admincp_stats->fetch($what, $period, 'deliveries', $company, $country),
					'topcustomers' => $this->sheel->admincp_stats->fetch($what, $period, 'topcustomers', $company, $country),
					'topentities' => $this->sheel->admincp_stats->fetch($what, $period, 'topentities', $company, $country),
					'ordersizes' => $this->sheel->admincp_stats->fetch($what, $period, 'ordersizes', $company, $country),
					'analysis' => $this->sheel->admincp_stats->fetch($what, $period, 'analysis', $company, $country),
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