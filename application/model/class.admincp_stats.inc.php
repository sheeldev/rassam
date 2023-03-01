<?php
/**
* AdminCP stats fetcher
*
* @package      Sheel\AdminCP\Stats
* @version      1.0.0.0
* @author       Sheel
*/
class admincp_stats extends admincp
{
	function fetch($what = '', $period = 'last7days', $do = '')
	{
		if ($what == 'home')
		{
			if ($do == 'visitors' OR $do == 'uniquevisitors' OR $do == 'pageviews')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($do == 'visitors')
				{
					return number_format($res['visitors']);
				}
				else if ($do == 'uniquevisitors')
				{
					return number_format($res['uniquevisitors']);
				}
				else if ($do == 'pageviews')
				{
					return number_format($res['pageviews']);
				}
			}
			else if ($do == 'visitorlabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'visitorseries')
			{
				return $this->period_to_series('visitors', $period);
			}
			else if ($do == 'mostactive') // Morning, Afternoon, Evening, Night
			{
				$loop = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
				 	WHEN HOUR(firsthit) BETWEEN 5 AND 12 THEN 'Morning'
					WHEN HOUR(firsthit) BETWEEN 12 AND 13 THEN 'Daytime'
					WHEN HOUR(firsthit) BETWEEN 13 AND 17 THEN 'Evening'
					ELSE 'Nighttime'
					END AS PartOfDay,
					COUNT(DISTINCT ipaddress) AS `rows`
					FROM " . DB_PREFIX . "visits
					GROUP BY PartOfDay
				");
				while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
				{
					$loop[] = $res;
				}
				return $loop;
			}
			else if ($do == 'topcountries')
			{
				$this->sheel->show['topcountries'] = false;
				$topcountries = array();
				$sql = $this->sheel->db->query("
					SELECT v.country, l.cc, l.locationid, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits v
					LEFT JOIN " . DB_PREFIX . "locations l ON (v.country LIKE CONCAT('%', l.location_" . $_SESSION['sheeldata']['user']['slng'] . ", '%'))
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND v.country != ''
					GROUP BY v.country
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topcountries'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcountriesx[] = $res;
					}
					// $sum = 30
					foreach ($topcountriesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcountries[$key]['icon'] = $this->sheel->common_location->print_country_flag($array['locationid']);
						$topcountries[$key]['title'] = $array['country'];
						$topcountries[$key]['percent'] = $percent;
						$topcountries[$key]['count'] = $array['count'];
					}
				}
				return $topcountries;
			}
			else if ($do == 'topdevices')
			{
				$this->sheel->show['topdevices'] = false;
				$topdevices = array();
				$sql = $this->sheel->db->query("
					SELECT device, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
					GROUP BY device
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topdevices'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topdevicesx[] = $res;
					}
					// $sum = 30
					foreach ($topdevicesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topdevices[$key]['title'] = ((empty($array['device'])) ? '{_other}' : '{_' . $array['device'] . '}');
						$topdevices[$key]['percent'] = $percent;
						$topdevices[$key]['count'] = $array['count'];
					}
				}
				return $topdevices;
			}
			else if ($do == 'topbrowsers')
			{
				$this->sheel->show['topbrowsers'] = false;
				$topbrowsers = array();
				$sql = $this->sheel->db->query("
					SELECT browser, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND browser NOT LIKE '%bot%'
					GROUP BY browser
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrowsers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrowsersx[] = $res;
					}
					// $sum = 30
					foreach ($topbrowsersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrowsers[$key]['icon'] = $this->sheel->common->fetch_browser_name(1, $array['browser']);
						$topbrowsers[$key]['title'] = $this->sheel->common->fetch_browser_name(0, $array['browser']);
						$topbrowsers[$key]['percent'] = $percent;
						$topbrowsers[$key]['count'] = $array['count'];
					}
				}
				return $topbrowsers;
			}
			else if ($do == 'trafficsources')
			{
				$this->sheel->show['trafficsources'] = false;
				$trafficsources = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `browser` LIKE '%bot%' THEN 'search'
					WHEN `referrer` = '{_direct}' THEN 'direct'
					WHEN `referrer` != '{_direct}' AND `referrer` != '' THEN 'referrals'
					END AS sources,
					COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
					GROUP BY sources
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['trafficsources'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$trafficsourcesx[] = $res;
					}
					// $sum = 30
					foreach ($trafficsourcesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$trafficsources[$key]['title'] = '{_' . $array['sources'] . '}';
						$trafficsources[$key]['percent'] = $percent;
						$trafficsources[$key]['count'] = $array['count'];
					}
				}
				return $trafficsources;
			}
			else if ($do == 'toplandingpages')
			{
				$this->sheel->show['toplandingpages'] = false;
				$toplandingpages = array();
				$sql = $this->sheel->db->query("
					SELECT landingpage, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND landingpage != ''
					GROUP BY landingpage
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['toplandingpages'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toplandingpagesx[] = $res;
					}
					// $sum = 30
					foreach ($toplandingpagesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$toplandingpages[$key]['url'] = substr(HTTPS_SERVER, 0, -1) . $array['landingpage'];
						$toplandingpages[$key]['title'] = o($array['landingpage']);
						$toplandingpages[$key]['percent'] = $percent;
						$toplandingpages[$key]['count'] = $array['count'];
					}
				}
				return $toplandingpages;
			}
		}
		if ($what == 'dashboard')
		{
			if ($do == 'visitors' OR $do == 'uniquevisitors' OR $do == 'pageviews')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($do == 'visitors')
				{
					return number_format($res['visitors']);
				}
				else if ($do == 'uniquevisitors')
				{
					return number_format($res['uniquevisitors']);
				}
				else if ($do == 'pageviews')
				{
					return number_format($res['pageviews']);
				}
			}
			else if ($do == 'visitorlabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'visitorseries')
			{
				return $this->period_to_series('visitors', $period);
			}
			else if ($do == 'mostactive') // Morning, Afternoon, Evening, Night
			{
				$loop = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
				 	WHEN HOUR(firsthit) BETWEEN 5 AND 12 THEN 'Morning'
					WHEN HOUR(firsthit) BETWEEN 12 AND 13 THEN 'Daytime'
					WHEN HOUR(firsthit) BETWEEN 13 AND 17 THEN 'Evening'
					ELSE 'Nighttime'
					END AS PartOfDay,
					COUNT(DISTINCT ipaddress) AS `rows`
					FROM " . DB_PREFIX . "visits
					GROUP BY PartOfDay
				");
				while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
				{
					$loop[] = $res;
				}
				return $loop;
			}
			// multi-vendor
			else if ($do == 'revenue7days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']);
			}
			else if ($do == 'revenuecount7days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenuetoday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecounttoday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenueyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'yesterday') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecountyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'yesterday') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenue30days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecount30days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenue90days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecount90days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenuelabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'revenueseries')
			{
				return $this->period_to_series('revenue', $period);
			}
			// single seller
			// sales (single seller)
			else if ($do == 'sellersales7days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last7days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']);
			}
			else if ($do == 'sellersalescount7days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last7days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersalestoday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'today') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescounttoday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'today') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersalesyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'yesterday') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescountyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'yesterday') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersales30days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last30days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescount30days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last30days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersales90days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last90days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescount90days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last90days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersaleslabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'sellersalesseries')
			{
				return $this->period_to_series('sales', $period);
			}
			else if ($do == 'buyerpercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productbuyer'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$buyers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productseller'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$sellers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'all'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$both = $res['count'];
				$all = $buyers + $sellers + $both;
				if ($all > 0)
				{
					$percent = ($buyers/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'buyercount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productbuyer'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellerpercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productbuyer'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$buyers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productseller'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$sellers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'all'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$both = $res['count'];
				$all = $buyers + $sellers + $both;
				if ($all > 0)
				{
					$percent = ($sellers/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'sellercount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productseller'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'bothpercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productbuyer'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$buyers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'productseller'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$sellers = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'all'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$both = $res['count'];
				$all = $buyers + $sellers + $both;
				if ($all > 0)
				{
					$percent = ($both/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'bothcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "subscription_user s
					LEFT JOIN " . DB_PREFIX . "users u ON (s.user_id = u.user_id)
					LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (s.roleid = r.roleid)
					WHERE " . $this->period_to_sql('u.date_added', $period) . "
						AND r.roleusertype = 'all'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'topcategories')
			{
				$this->sheel->show['topcategories'] = false;
				$topcategories = array();
				$sql = $this->sheel->db->query("
					SELECT c.title_" . $_SESSION['sheeldata']['user']['slng'] . " AS title, c.seourl_" . $_SESSION['sheeldata']['user']['slng'] . " AS seourl, COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders b
					LEFT JOIN " . DB_PREFIX . "categories c ON (b.cid = c.cid)
					WHERE " . $this->period_to_sql('b.orderdate', $period) . "
						AND b.iscancelled = '0'
						AND b.isreturned = '0'
					GROUP BY c.cid
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topcategories'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcategoriesx[] = $res;
					}
					// $sum = 30
					foreach ($topcategoriesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcategories[$key]['title'] = $array['title'];
						$topcategories[$key]['count'] = $array['count'];
						$topcategories[$key]['seourl'] = $array['seourl'];
						$topcategories[$key]['percent'] = $percent;
					}
				}
				return $topcategories;
			}
			else if ($do == 'topcountries')
			{
				$this->sheel->show['topcountries'] = false;
				$topcountries = array();
				$sql = $this->sheel->db->query("
					SELECT v.country, l.cc, l.locationid, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits v
					LEFT JOIN " . DB_PREFIX . "locations l ON (v.country LIKE CONCAT('%', l.location_" . $_SESSION['sheeldata']['user']['slng'] . ", '%'))
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND v.country != ''
					GROUP BY v.country
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topcountries'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcountriesx[] = $res;
					}
					// $sum = 30
					foreach ($topcountriesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcountries[$key]['icon'] = $this->sheel->common_location->print_country_flag($array['locationid']);
						$topcountries[$key]['title'] = $array['country'];
						$topcountries[$key]['percent'] = $percent;
						$topcountries[$key]['count'] = $array['count'];
					}
				}
				return $topcountries;
			}
			else if ($do == 'topdevices')
			{
				$this->sheel->show['topdevices'] = false;
				$topdevices = array();
				$sql = $this->sheel->db->query("
					SELECT device, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
					GROUP BY device
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topdevices'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topdevicesx[] = $res;
					}
					// $sum = 30
					foreach ($topdevicesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topdevices[$key]['title'] = ((empty($array['device'])) ? '{_other}' : '{_' . $array['device'] . '}');
						$topdevices[$key]['percent'] = $percent;
						$topdevices[$key]['count'] = $array['count'];
					}
				}
				return $topdevices;
			}
			else if ($do == 'topbrowsers')
			{
				$this->sheel->show['topbrowsers'] = false;
				$topbrowsers = array();
				$sql = $this->sheel->db->query("
					SELECT browser, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND browser NOT LIKE '%bot%'
					GROUP BY browser
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrowsers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrowsersx[] = $res;
					}
					// $sum = 30
					foreach ($topbrowsersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrowsers[$key]['icon'] = $this->sheel->common->fetch_browser_name(1, $array['browser']);
						$topbrowsers[$key]['title'] = $this->sheel->common->fetch_browser_name(0, $array['browser']);
						$topbrowsers[$key]['percent'] = $percent;
						$topbrowsers[$key]['count'] = $array['count'];
					}
				}
				return $topbrowsers;
			}
			else if ($do == 'trafficsources')
			{
				$this->sheel->show['trafficsources'] = false;
				$trafficsources = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `browser` LIKE '%bot%' THEN 'search'
					WHEN `referrer` = '{_direct}' THEN 'direct'
					WHEN `referrer` != '{_direct}' AND `referrer` != '' THEN 'referrals'
					END AS sources,
					COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
					GROUP BY sources
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['trafficsources'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$trafficsourcesx[] = $res;
					}
					// $sum = 30
					foreach ($trafficsourcesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$trafficsources[$key]['title'] = '{_' . $array['sources'] . '}';
						$trafficsources[$key]['percent'] = $percent;
						$trafficsources[$key]['count'] = $array['count'];
					}
				}
				return $trafficsources;
			}
			else if ($do == 'socialreferrers')
			{
				$this->sheel->show['socialreferrers'] = false;
				$socialreferrers = array();
				$sql = $this->sheel->db->query("
					SELECT social, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND social != ''
					GROUP BY social
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['socialreferrers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$socialreferrersx[] = $res;
					}
					// $sum = 30
					foreach ($socialreferrersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$socialreferrers[$key]['title'] = '{_' . $array['social'] . '}';
						$socialreferrers[$key]['percent'] = $percent;
						$socialreferrers[$key]['count'] = $array['count'];
					}
				}
				return $socialreferrers;
			}
			else if ($do == 'topreferrers')
			{
				$this->sheel->show['topreferrers'] = false;
				$topreferrers = array();
				$sql = $this->sheel->db->query("
					SELECT referrer, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND referrer NOT LIKE '%" . $this->sheel->db->escape_string($_SERVER['SERVER_NAME']) . "%'
					GROUP BY referrer
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topreferrers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topreferrersx[] = $res;
					}
					// $sum = 30
					foreach ($topreferrersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topreferrers[$key]['url'] = (($array['referrer'] == '{_direct}') ? HTTPS_SERVER : 'http://' . $array['referrer']);
						$topreferrers[$key]['title'] = o($array['referrer']);
						$topreferrers[$key]['percent'] = $percent;
						$topreferrers[$key]['count'] = $array['count'];
					}
				}
				return $topreferrers;
			}
			else if ($do == 'topsearchterms')
			{
				$this->sheel->show['topsearchterms'] = false;
				$topsearchterms = array();
				$sql = $this->sheel->db->query("
					SELECT keyword, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "search_users
					WHERE " . $this->period_to_sql('`added`', $period) . "
						AND keyword != ''
					GROUP BY keyword
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topsearchterms'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topsearchtermsx[] = $res;
					}
					// $sum = 30
					foreach ($topsearchtermsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topsearchterms[$key]['url'] = HTTPS_SERVER . 'search/' . str_replace(' ', '+', $array['keyword']) . '.html';
						$topsearchterms[$key]['title'] = o($array['keyword']);
						$topsearchterms[$key]['percent'] = $percent;
						$topsearchterms[$key]['count'] = $array['count'];
					}
				}
				return $topsearchterms;
			}
			else if ($do == 'toplandingpages')
			{
				$this->sheel->show['toplandingpages'] = false;
				$toplandingpages = array();
				$sql = $this->sheel->db->query("
					SELECT landingpage, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND landingpage != ''
					GROUP BY landingpage
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['toplandingpages'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toplandingpagesx[] = $res;
					}
					// $sum = 30
					foreach ($toplandingpagesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$toplandingpages[$key]['url'] = substr(HTTPS_SERVER, 0, -1) . $array['landingpage'];
						$toplandingpages[$key]['title'] = o($array['landingpage']);
						$toplandingpages[$key]['percent'] = $percent;
						$toplandingpages[$key]['count'] = $array['count'];
					}
				}
				return $toplandingpages;
			}
			else if ($do == 'utmcampaigns')
			{
				$this->sheel->show['utmcampaigns'] = false;
				$utmcampaigns = array();
				$sql = $this->sheel->db->query("
					SELECT utmcampaign, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND utmcampaign != ''
					GROUP BY utmcampaign
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['utmcampaigns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$utmcampaignsx[] = $res;
					}
					// $sum = 30
					foreach ($utmcampaignsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$utmcampaigns[$key]['url'] = HTTPS_SERVER;
						$utmcampaigns[$key]['title'] = o('/?utm_campaign=' . $array['utmcampaign']);
						$utmcampaigns[$key]['percent'] = $percent;
						$utmcampaigns[$key]['count'] = $array['count'];
					}
				}
				return $utmcampaigns;
			}
		}
		else if ($what == 'marketplace')
		{
			//
		}
		else if ($what == 'accounting')
		{
			if ($do == 'totaldepositspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'scheduled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totaldepositsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'paid'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totaldepositscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'cancelled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
						AND canceldate != '0000-00-00 00:00:00'
						AND canceluserid > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalwithdrawsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'paid'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalwithdrawspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'scheduled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalwithdrawscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE status = 'cancelled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalescrowfunding')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE date_funded != '0000-00-00 00:00:00'
						AND status = 'funded'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalescrowreleases')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE status = 'released'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totalescrowreversals')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE status = 'reversed'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'totaluserbalances')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(available_balance) AS amount
					FROM " . DB_PREFIX . "users
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']) . '</span>';
			}
			else if ($do == 'todaydepositsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'todaydepositspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'today') . "
						AND status = 'scheduled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'todaydepositscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'today') . "
						AND status = 'cancelled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
						AND canceldate != '0000-00-00 00:00:00'
						AND canceluserid > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'todaywithdrawsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'todaywithdrawspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'today') . "
						AND status = 'scheduled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'todaywithdrawscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'today') . "
						AND status = 'cancelled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'todayescrowfunding')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE date_funded != '0000-00-00 00:00:00'
						AND " . $this->period_to_sql('date_funded', 'today') . "
						AND status = 'funded'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'todayescrowreleases')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE " . $this->period_to_sql('date_released', 'today') . "
						AND status = 'released'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'todayescrowreversals')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE (" . $this->period_to_sql('date_fundsreversal', 'today') . " OR " . $this->period_to_sql('date_postfundsreversal', 'today') . ")
						AND status = 'reversed'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last7depositsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last7depositspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last7days') . "
						AND status = 'scheduled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last7depositscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last7days') . "
						AND status = 'cancelled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
						AND canceldate != '0000-00-00 00:00:00'
						AND canceluserid > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last7withdrawsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last7withdrawspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last7days') . "
						AND status = 'scheduled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last7withdrawscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last7days') . "
						AND status = 'cancelled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last7escrowfunding')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE date_funded != '0000-00-00 00:00:00'
						AND " . $this->period_to_sql('date_funded', 'last7days') . "
						AND status = 'funded'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last7escrowreleases')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE " . $this->period_to_sql('date_released', 'last7days') . "
						AND status = 'released'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last7escrowreversals')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE (" . $this->period_to_sql('date_fundsreversal', 'last7days') . " OR " . $this->period_to_sql('date_postfundsreversal', 'last7days') . ")
						AND status = 'reversed'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last30depositsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last30depositspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last30days') . "
						AND status = 'scheduled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last30depositscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last30days') . "
						AND status = 'cancelled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
						AND canceldate != '0000-00-00 00:00:00'
						AND canceluserid > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last30withdrawsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last30withdrawspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last30days') . "
						AND status = 'scheduled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last30withdrawscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last30days') . "
						AND status = 'cancelled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last30escrowfunding')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE date_funded != '0000-00-00 00:00:00'
						AND " . $this->period_to_sql('date_funded', 'last30days') . "
						AND status = 'funded'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last30escrowreleases')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE " . $this->period_to_sql('date_released', 'last30days') . "
						AND (status = 'funded' OR status = 'released')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last30escrowreversals')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE (" . $this->period_to_sql('date_fundsreversal', 'last30days') . " OR " . $this->period_to_sql('date_postfundsreversal', 'last30days') . ")
						AND status = 'reversed'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last90depositsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last90depositspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last90days') . "
						AND status = 'scheduled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last90depositscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(depositcreditamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last90days') . "
						AND status = 'cancelled'
						AND invoicetype = 'credit'
						AND depositcreditamount > 0
						AND isdeposit = '1'
						AND canceldate != '0000-00-00 00:00:00'
						AND canceluserid > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last90withdrawsamount')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last90withdrawspending')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', 'last90days') . "
						AND status = 'scheduled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last90withdrawscancelled')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(totalamount) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('canceldate', 'last90days') . "
						AND status = 'cancelled'
						AND invoicetype = 'debit'
						AND totalamount > 0
						AND iswithdraw = '1'
						AND iswithdrawfee = '0'
						AND parentid = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return '<span title="' . strip_tags($this->sheel->currency->format($res['amount'])) . '">' . $this->sheel->currency->format($res['amount']) . '</span>';
			}
			else if ($do == 'last90escrowfunding')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE date_funded != '0000-00-00 00:00:00'
						AND " . $this->period_to_sql('date_funded', 'last90days') . "
						AND status = 'funded'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last90escrowreleases')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE " . $this->period_to_sql('date_released', 'last90days') . "
						AND status = 'released'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'last90escrowreversals')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(total_converted) AS amount
					FROM " . DB_PREFIX . "escrow
					WHERE (" . $this->period_to_sql('date_fundsreversal', 'last90days') . " OR " . $this->period_to_sql('date_postfundsreversal', 'last90days') . ")
						AND status = 'reversed'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'transactionlabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'transactionseries')
			{
				return $this->period_to_series('accounting', $period);
			}
			else if ($do == 'escrowlabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'escrowseries')
			{
				return $this->period_to_series('escrow', $period);
			}
			else if ($do == 'depositslabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'depositsseries')
			{
				return $this->period_to_series('deposits', $period);
			}
			else if ($do == 'withdrawslabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'withdrawsseries')
			{
				return $this->period_to_series('withdraws', $period);
			}
			else if ($do == 'invoicepercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$generated = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('previewed_date', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND previewed = '1'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$previewed = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND status = 'paid'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $generated + $previewed + $purchased;
				if ($all > 0)
				{
					$percent = ($generated/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'invoicecount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'checkoutpercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$generated = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('previewed_date', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND previewed = '1'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$previewed = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND status = 'paid'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $generated + $previewed + $purchased;
				if ($all > 0)
				{
					$percent = ($previewed/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'checkoutcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('previewed_date', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND previewed = '1'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'purchasedpercent')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$generated = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('previewed_date', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND previewed = '1'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$previewed = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND status = 'paid'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $generated + $previewed + $purchased;
				if ($all > 0)
				{
					$percent = ($purchased/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'purchasedcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', $period) . "
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						AND status = 'paid'
					GROUP BY user_id
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
                        else if ($do == 'topinvoicetypes')
			{
				$this->sheel->show['topinvoicetypes'] = false;
				$topinvoicetypes = array();
				$sql = $this->sheel->db->query("
					SELECT CASE
						WHEN istransactionfee = '1' THEN 'Transaction fees'
						WHEN iswithdrawfee = '1' THEN 'Withdraw fees'
						WHEN isescrowfee = '1' THEN 'Escrow fees'
						WHEN isenhancementfee = '1' THEN 'Enhancement fees'
						WHEN isif = '1' THEN 'Insertion fees'
						WHEN isfvf = '1' THEN 'Final value fees'
						WHEN invoicetype = 'subscription' AND amount > 0 THEN 'Membership fees'
					END AS invoicetypes, COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND (invoicetype = 'debit' OR invoicetype = 'subscription')
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
					GROUP BY invoicetypes
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topinvoicetypes'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topinvoicetypesx[] = $res;
					}
					foreach ($topinvoicetypesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topinvoicetypes[$key]['title'] = $array['invoicetypes'];
						$topinvoicetypes[$key]['count'] = $array['count'];
						$topinvoicetypes[$key]['percent'] = $percent;
					}
				}
				return $topinvoicetypes;
			}
        		else if ($do == 'topaccountbalances')
			{
				$this->sheel->show['topaccountbalances'] = false;
				$topaccountbalances = array();
				$sql = $this->sheel->db->query("
					SELECT user_id, first_name, last_name, username, available_balance
					FROM " . DB_PREFIX . "users
					WHERE available_balance > 0
					ORDER BY available_balance DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topaccountbalances'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$topaccountbalancesx[] = $res;
					}
					foreach ($topaccountbalancesx AS $key => $array)
					{
						$topaccountbalances[$key]['title'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $array['user_id'] . '/">' . $array['first_name'] . ' ' . $array['last_name'] . ' (' . $array['username'] . ')</a>';
						$topaccountbalances[$key]['available_balance'] = $this->sheel->currency->format($array['available_balance']);
					}
				}
				return $topaccountbalances;
			}
        		else if ($do == 'toppaymethods')
			{
				$this->sheel->show['toppaymethods'] = false;
				$toppaymethods = array();
				$paymentfields = '';
				foreach ($this->sheel->paymentgateway->ipn_gateway_accepted AS $gatewayipn)
				{
					$paymentfields .= "WHEN paymethod = 'ipn' AND paymentgateway = '$gatewayipn' THEN '{_$gatewayipn}'
";
				}
				foreach ($this->sheel->paymentgateway->ipn_gateway_accepted AS $gatewayipn)
				{
					$paymentfields .= "WHEN paymethod = '$gatewayipn' THEN '{_$gatewayipn}'
";
				}
				$sql = $this->sheel->db->query("
					SELECT CASE
						WHEN paymethod = 'creditcard' OR paymethod = 'visa' OR paymethod = 'amex' OR paymethod = 'mc' OR paymethod = 'disc' OR paymethod = 'paypal_pro' THEN '{_credit_card}'
						WHEN paymethod = 'account' THEN '{_account_balance}'
						WHEN paymethod = 'bank' THEN '{_bank_account}'
						WHEN paymethod = 'check' THEN '{_check}'
						$paymentfields
					END AS paymethod, COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
					GROUP BY paymethod
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['toppaymethods'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toppaymethodsx[] = $res;
					}
					foreach ($toppaymethodsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$toppaymethods[$key]['title'] = $array['paymethod'];
						$toppaymethods[$key]['count'] = $array['count'];
						$toppaymethods[$key]['percent'] = $percent;
					}
				}
				return $toppaymethods;
			}
        		else if ($do == 'topgateways')
			{
				$this->sheel->show['topgateways'] = false;
				$topgateways = array();
				$sql = $this->sheel->db->query("
					SELECT paymentgateway, COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('createdate', $period) . "
						AND paymentgateway != ''
						AND status != 'scheduled'
						AND paymethod != 'bank'
					GROUP BY paymentgateway
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topgateways'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topgatewaysx[] = $res;
					}
					foreach ($topgatewaysx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topgateways[$key]['title'] = '{_' . $array['paymentgateway'] . '}';
						$topgateways[$key]['count'] = $array['count'];
						$topgateways[$key]['percent'] = $percent;
					}
				}
				return $topgateways;
			}
        		else if ($do == 'topcurrencies')
			{
				$this->sheel->show['topcurrencies'] = false;
				$topcurrencies = array();
				$sql = $this->sheel->db->query("
					SELECT c.currency_abbrev, COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders i
					LEFT JOIN " . DB_PREFIX . "currency c ON (i.originalcurrencyid = c.currency_id)
					WHERE " . $this->period_to_sql('i.orderdate', $period) . "
						AND c.currency_abbrev != ''
					GROUP BY c.currency_abbrev
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topcurrencies'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcurrenciesx[] = $res;
					}
					foreach ($topcurrenciesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcurrencies[$key]['title'] = $array['currency_abbrev'];
						$topcurrencies[$key]['count'] = $array['count'];
						$topcurrencies[$key]['percent'] = $percent;
					}
				}
				return $topcurrencies;
			}
        		else if ($do == 'topdaypart')
			{
				$this->sheel->show['topdaypart'] = false;
				$loop = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
				 	WHEN HOUR(orderdate) BETWEEN 5 AND 12 THEN 'Morning'
					WHEN HOUR(orderdate) BETWEEN 12 AND 17 THEN 'Day'
					WHEN HOUR(orderdate) BETWEEN 17 AND 23 THEN 'Evening'
					ELSE 'Night'
					END AS title,
					COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					GROUP BY title
					ORDER BY count DESC
				");
				$sum = 0;
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topdaypart'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$loop[] = $res;
					}
					foreach ($loop AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$loop[$key]['title'] = $array['title'];
						$loop[$key]['count'] = $array['count'];
						$loop[$key]['percent'] = $percent;
					}
				}
				return $loop;
			}
        		else if ($do == 'topbalancesowing')
			{
				$this->sheel->show['topbalancesowing'] = false;
				$topbalancesowing = array();
				$sql = $this->sheel->db->query("
					SELECT u.user_id, u.first_name, u.last_name, u.username, SUM(totalamount) AS allowing, COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices i
					LEFT JOIN " . DB_PREFIX . "users u ON (i.user_id = u.user_id)
					WHERE i.totalamount > 0
						AND (i.isportfoliofee = '1' OR i.isfvf = '1' OR i.isif = '1' OR i.isenhancementfee = '1' OR i.isescrowfee = '1' OR i.iswithdrawfee = '1' OR i.isfvfbuyer = '1' OR i.isdonationfee = '1')
						AND i.status = 'unpaid'
					GROUP BY i.user_id
					ORDER BY allowing DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbalancesowing'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$topbalancesowingx[] = $res;
					}
					foreach ($topbalancesowingx AS $key => $array)
					{
						$topbalancesowing[$key]['title'] = '<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $array['user_id'] . '/">' . $array['first_name'] . ' ' . $array['last_name'] . ' (' . $array['username'] . ')</a>';
						$topbalancesowing[$key]['allowing'] = $this->sheel->currency->format($array['allowing']);
					}
				}
				return $topbalancesowing;
			}
		}
		else if ($what == 'stores')
		{
			if ($do == 'listingcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS listingcount
					FROM " . DB_PREFIX . "projects
					WHERE " . $this->period_to_sql('`date_added`', $period) . "
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['listingcount']);
			}
			else if ($do == 'listingcountpast')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS listingcountpast
					FROM " . DB_PREFIX . "projects
					WHERE " . $this->period_to_sql('date_end', $period) . "
						AND date_end <= CURDATE()
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['listingcountpast']);
			}
			else if ($do == 'listingcountmoderated')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS listingcountmoderated
					FROM " . DB_PREFIX . "projects
					WHERE " . $this->period_to_sql('date_added', $period) . "
						AND visible = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['listingcountmoderated']);
			}
			else if ($do == 'listingcountlabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'listingcountseries')
			{
				return $this->period_to_series('listings', $period);
			}
			// revenue (multi-vendor)
			else if ($do == 'revenue7days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']); //$this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecount7days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last7days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenuetoday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecounttoday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'today') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenueyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'yesterday') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecountyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'yesterday') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenue30days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecount30days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last30days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenue90days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(paid) AS amount
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'revenuecount90days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "invoices
					WHERE " . $this->period_to_sql('paiddate', 'last90days') . "
						AND status = 'paid'
						AND paid > 0
						AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'revenuelabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'revenueseries')
			{
				return $this->period_to_series('revenue', $period);
			}
			// sales (single seller)
			else if ($do == 'sellersales7days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last7days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->custom_number_format($res['amount'], 1, $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'], $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right']);
			}
			else if ($do == 'sellersalescount7days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last7days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersalestoday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'today') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescounttoday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'today') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersalesyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'yesterday') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescountyesterday')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'yesterday') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersales30days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last30days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescount30days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last30days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersales90days')
			{
				$sql = $this->sheel->db->query("
					SELECT SUM(amount) AS amount
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last90days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return $this->sheel->currency->format($res['amount']);
			}
			else if ($do == 'sellersalescount90days')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders
					WHERE " . $this->period_to_sql('orderdate', 'last90days') . "
						AND amount > 0
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'sellersaleslabel')
			{
				return $this->period_to_label($period);
			}
			else if ($do == 'sellersalesseries')
			{
				return $this->period_to_series('sales', $period);
			}
			// percent
			else if ($do == 'addedtocart')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('added', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$addedtocart = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('reachedcheckoutdate', $period) . "
						AND reachedcheckout = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$reachedcheckout = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('purchasedate', $period) . "
						AND purchased = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $addedtocart + $reachedcheckout + $purchased;
				if ($all > 0)
				{
					$percent = ($addedtocart/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'addedtocartcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('added', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'reachedcheckout')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('added', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$addedtocart = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('reachedcheckoutdate', $period) . "
						AND reachedcheckout = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$reachedcheckout = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('purchasedate', $period) . "
						AND purchased = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $addedtocart + $reachedcheckout + $purchased;
				if ($all > 0)
				{
					$percent = ($reachedcheckout/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'reachedcheckoutcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('reachedcheckoutdate', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'purchased')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('added', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$addedtocart = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('reachedcheckoutdate', $period) . "
						AND reachedcheckout = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$reachedcheckout = $res['count'];
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('purchasedate', $period) . "
						AND purchased = '1'
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$purchased = $res['count'];
				$all = $addedtocart + $reachedcheckout + $purchased;
				if ($all > 0)
				{
					$percent = ($purchased/$all) * 100;
				}
				else
				{
					$percent = '0.00';
				}
				return sprintf("%01.1f", $percent);
			}
			else if ($do == 'purchasedcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "carts
					WHERE " . $this->period_to_sql('purchasedate', $period) . "
					GROUP BY userid
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			// stats
			else if ($do == 'topstoresbyorders')
			{
				$this->sheel->show['topstoresbyorders'] = false;
				$topstoresbyorders = array();
				$sql = $this->sheel->db->query("
					SELECT s.storename, s.seourl, a.filehash, COUNT(*) AS count
					FROM " . DB_PREFIX . "stores s
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (s.user_id = o.owner_id)
					LEFT JOIN " . DB_PREFIX . "attachment a ON (s.logo_attachid = a.attachid)
					WHERE " . $this->period_to_sql('`orderdate`', $period) . "
						 AND o.parentid = 0
						 AND o.isreturned = '0'
						 AND o.iscancelled = '0'
					GROUP BY s.storeid
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topstoresbyorders'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topstoresbyordersx[] = $res;
					}
					// $sum = 30
					foreach ($topstoresbyordersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topstoresbyorders[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'stores/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topstoresbyorders[$key]['title'] = '<a href="' . HTTPS_SERVER . 'stores/' . $array['seourl'] . '/" target="_blank">' . o($array['storename']) . '</a>'; //$array['country'];
						$topstoresbyorders[$key]['percent'] = $percent;
						$topstoresbyorders[$key]['count'] = $array['count'];
					}
				}
				return $topstoresbyorders;
			}
			else if ($do == 'topproductsbyorders')
			{
				$this->sheel->show['topproductsbyorders'] = false;
				$topproductsbyorders = array();
				$sql = $this->sheel->db->query("
					SELECT o.sku, o.project_title, o.project_id, a.filehash, COUNT(*) AS count
					FROM " . DB_PREFIX . "buynow_orders o
					LEFT JOIN " . DB_PREFIX . "attachment a ON (o.project_id = a.project_id)
					WHERE " . $this->period_to_sql('`orderdate`', $period) . "
						AND a.attachtype = 'itemphoto'
						AND o.isreturned = '0'
						AND o.iscancelled = '0'
					GROUP BY o.project_id
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topproductsbyorders'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topproductsbyordersx[] = $res;
					}
					// $sum = 30
					foreach ($topproductsbyordersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$seourl = $this->sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $array['project_id'], 'name' => stripslashes($array['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
						$topproductsbyorders[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'auctions/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topproductsbyorders[$key]['title'] = '<a href="' . $seourl . ((!empty($array['sku'])) ? '?sku=' . $array['sku'] : '') . '" target="_blank">' . o($array['project_title']) . ' (#' . $array['project_id'] . ')' . ((!empty($array['sku'])) ? ' (SKU: ' . $array['sku'] . ')' : '') . '</a>';
						$topproductsbyorders[$key]['percent'] = $percent;
						$topproductsbyorders[$key]['count'] = $array['count'];
					}
				}
				return $topproductsbyorders;
			}
			else if ($do == 'topstoresbytraffic')
			{
				$this->sheel->show['topstoresbytraffic'] = false;
				$topstoresbytraffic = array();
				$sql = $this->sheel->db->query("
					SELECT s.storename, s.seourl, a.filehash, COUNT(su.id) AS count
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "stores s ON (p.user_id = s.user_id)
					LEFT JOIN " . DB_PREFIX . "attachment a ON (s.logo_attachid = a.attachid)
					LEFT JOIN " . DB_PREFIX . "search_users su ON (p.project_id = su.project_id)
					WHERE " . $this->period_to_sql('su.`added`', $period) . "
						AND p.storeid > 0
					GROUP BY s.storeid
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topstoresbytraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topstoresbytrafficx[] = $res;
					}
					foreach ($topstoresbytrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topstoresbytraffic[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'stores/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topstoresbytraffic[$key]['title'] = '<a href="' . HTTPS_SERVER . 'stores/' . $array['seourl'] . '/" target="_blank">' . o($array['storename']) . '</a>'; //$array['country'];
						$topstoresbytraffic[$key]['percent'] = $percent;
						$topstoresbytraffic[$key]['count'] = $array['count'];
					}
				}
				return $topstoresbytraffic;
			}
			else if ($do == 'topstorescategorybytraffic')
			{
				$this->sheel->show['topstorescategorybytraffic'] = false;
				$topstorescategorybytraffic = array();
				$sql = $this->sheel->db->query("
					SELECT s.storename, s.seourl AS storeseourl, a.filehash, c.category_name, c.seourl, COUNT(h.id) AS count
					FROM " . DB_PREFIX . "stores_category c
					LEFT JOIN " . DB_PREFIX . "stores s ON (c.storeid = s.storeid)
					LEFT JOIN " . DB_PREFIX . "attachment a ON (s.logo_attachid = a.attachid)
					LEFT JOIN " . DB_PREFIX . "hits h ON (c.cid = h.cid)
					WHERE " . $this->period_to_sql('h.`datetime`', $period) . "
						AND c.storeid > 0
						AND s.storename != ''
					GROUP BY s.storeid
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topstorescategorybytraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topstorescategorybytrafficx[] = $res;
					}
					foreach ($topstorescategorybytrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topstorescategorybytraffic[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'stores/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topstorescategorybytraffic[$key]['title'] = '<a href="' . HTTPS_SERVER . 'stores/' . $array['storeseourl'] . '/' . $array['seourl'] . '/" target="_blank">' . o($array['storename']) . '</a>'; //$array['country'];
						$topstorescategorybytraffic[$key]['ctitle'] = o($array['category_name']);
						$topstorescategorybytraffic[$key]['percent'] = $percent;
						$topstorescategorybytraffic[$key]['count'] = $array['count'];
					}
				}
				return $topstorescategorybytraffic;
			}
			else if ($do == 'topproductsbytraffic')
			{
				$this->sheel->show['topproductsbytraffic'] = false;
				$topproductsbytraffic = array();
				$sql = $this->sheel->db->query("
					SELECT p.project_title, p.project_id, a.filehash, COUNT(su.id) AS count
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "attachment a ON (p.project_id = a.project_id)
					LEFT JOIN " . DB_PREFIX . "search_users su ON (p.project_id = su.project_id)
					WHERE " . $this->period_to_sql('su.`added`', $period) . "
						AND a.attachtype = 'itemphoto'
					GROUP BY p.project_id
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topproductsbytraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topproductsbytrafficx[] = $res;
					}
					foreach ($topproductsbytrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$seourl = $this->sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $array['project_id'], 'name' => stripslashes($array['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
						$topproductsbytraffic[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'auctions/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topproductsbytraffic[$key]['title'] = '<a href="' . $seourl . '" target="_blank">' . o($array['project_title']) . ' (#' . $array['project_id'] . ')</a>';
						$topproductsbytraffic[$key]['percent'] = $percent;
						$topproductsbytraffic[$key]['count'] = $array['count'];
					}
				}
				return $topproductsbytraffic;
			}
			else if ($do == 'topproductsbyreturns')
			{
				$this->sheel->show['topproductsbyreturns'] = false;
				$topproductsbyreturns = array();
				$sql = $this->sheel->db->query("
					SELECT p.project_title, p.project_id, a.filehash, COUNT(*) AS count
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (p.project_id = o.project_id)
					LEFT JOIN " . DB_PREFIX . "attachment a ON (p.project_id = a.project_id)
					WHERE " . $this->period_to_sql('`returnrequestdate`', $period) . "
						 AND (o.isreturned = '1' OR o.`return` = '1')
						 AND a.attachtype = 'itemphoto'
					GROUP BY p.project_id
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topproductsbyreturns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topproductsbyreturnsx[] = $res;
					}
					foreach ($topproductsbyreturnsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$seourl = $this->sheel->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $array['project_id'], 'name' => stripslashes($array['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
						$topproductsbyreturns[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'auctions/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topproductsbyreturns[$key]['title'] = '<a href="' . $seourl . '" target="_blank">' . o($array['project_title']) . ' (#' . $array['project_id'] . ')</a>';
						$topproductsbyreturns[$key]['percent'] = $percent;
						$topproductsbyreturns[$key]['count'] = $array['count'];
					}
				}
				return $topproductsbyreturns;
			}
			else if ($do == 'topstoresbyreturns')
			{
				$this->sheel->show['topstoresbyreturns'] = false;
				$topstoresbyreturns = array();
				$sql = $this->sheel->db->query("
					SELECT s.storename, s.seourl, a.filehash, COUNT(*) AS count
					FROM " . DB_PREFIX . "stores s
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (s.user_id = o.owner_id)
					LEFT JOIN " . DB_PREFIX . "attachment a ON (s.logo_attachid = a.attachid)
					WHERE " . $this->period_to_sql('`returnrequestdate`', $period) . "
						 AND (o.isreturned = '1' OR o.`return` = '1')
					GROUP BY s.storeid
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topstoresbyreturns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topstoresbyreturnsx[] = $res;
					}
					foreach ($topstoresbyreturnsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topstoresbyreturns[$key]['icon'] = '<img src="' . ((!empty($array['filehash'])) ? HTTP_ATTACHMENTS . 'stores/' . $array['filehash'] . '/50x50.jpg' : $this->sheel->config['imgcdn'] . 'v5/img_nophoto.gif') . '" width="50">';
						$topstoresbyreturns[$key]['title'] = '<a href="' . HTTPS_SERVER . 'stores/' . $array['seourl'] . '/" target="_blank">' . o($array['storename']) . '</a>'; //$array['country'];
						$topstoresbyreturns[$key]['percent'] = $percent;
						$topstoresbyreturns[$key]['count'] = $array['count'];
					}
				}
				return $topstoresbyreturns;
			}
			else if ($do == 'topcountries')
			{
				$this->sheel->show['topcountries'] = false;
				$topcountries = array();
				$sql = $this->sheel->db->query("
					SELECT v.country, l.cc, l.locationid, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits v
					LEFT JOIN " . DB_PREFIX . "locations l ON (v.country LIKE CONCAT('%', l.location_" . $_SESSION['sheeldata']['user']['slng'] . ", '%'))
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND v.country != ''
						AND v.storeid > 0
					GROUP BY v.country
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topcountries'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcountriesx[] = $res;
					}
					// $sum = 30
					foreach ($topcountriesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcountries[$key]['icon'] = $this->sheel->common_location->print_country_flag($array['locationid']);
						$topcountries[$key]['title'] = $array['country'];
						$topcountries[$key]['percent'] = $percent;
						$topcountries[$key]['count'] = $array['count'];
					}
				}
				return $topcountries;
			}
			else if ($do == 'topdevices')
			{
				$this->sheel->show['topdevices'] = false;
				$topdevices = array();
				$sql = $this->sheel->db->query("
					SELECT device, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND storeid > 0
					GROUP BY device
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topdevices'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topdevicesx[] = $res;
					}
					// $sum = 30
					foreach ($topdevicesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topdevices[$key]['title'] = ((empty($array['device'])) ? '{_other}' : '{_' . $array['device'] . '}');
						$topdevices[$key]['percent'] = $percent;
						$topdevices[$key]['count'] = $array['count'];
					}
				}
				return $topdevices;
			}
			else if ($do == 'topbrowsers')
			{
				$this->sheel->show['topbrowsers'] = false;
				$topbrowsers = array();
				$sql = $this->sheel->db->query("
					SELECT browser, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND browser NOT LIKE '%bot%'
						AND storeid > 0
					GROUP BY browser
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrowsers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrowsersx[] = $res;
					}
					// $sum = 30
					foreach ($topbrowsersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrowsers[$key]['icon'] = $this->sheel->common->fetch_browser_name(1, $array['browser']);
						$topbrowsers[$key]['title'] = $this->sheel->common->fetch_browser_name(0, $array['browser']);
						$topbrowsers[$key]['percent'] = $percent;
						$topbrowsers[$key]['count'] = $array['count'];
					}
				}
				return $topbrowsers;
			}
			else if ($do == 'trafficsources')
			{
				$this->sheel->show['trafficsources'] = false;
				$trafficsources = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `browser` LIKE '%bot%' THEN 'search'
					WHEN `referrer` = '{_direct}' THEN 'direct'
					WHEN `referrer` != '{_direct}' AND `referrer` != '' THEN 'referrals'
					END AS sources,
					COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND storeid > 0
					GROUP BY sources
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['trafficsources'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$trafficsourcesx[] = $res;
					}
					// $sum = 30
					foreach ($trafficsourcesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$trafficsources[$key]['title'] = '{_' . $array['sources'] . '}';
						$trafficsources[$key]['percent'] = $percent;
						$trafficsources[$key]['count'] = $array['count'];
					}
				}
				return $trafficsources;
			}
			else if ($do == 'socialreferrers')
			{
				$this->sheel->show['socialreferrers'] = false;
				$socialreferrers = array();
				$sql = $this->sheel->db->query("
					SELECT social, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND social != ''
						AND storeid > 0
					GROUP BY social
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['socialreferrers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$socialreferrersx[] = $res;
					}
					// $sum = 30
					foreach ($socialreferrersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$socialreferrers[$key]['title'] = '{_' . $array['social'] . '}';
						$socialreferrers[$key]['percent'] = $percent;
						$socialreferrers[$key]['count'] = $array['count'];
					}
				}
				return $socialreferrers;
			}
			else if ($do == 'topreferrers')
			{
				$this->sheel->show['topreferrers'] = false;
				$topreferrers = array();
				$sql = $this->sheel->db->query("
					SELECT referrer, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND referrer NOT LIKE '%" . $this->sheel->db->escape_string($_SERVER['SERVER_NAME']) . "%'
						AND storeid > 0
					GROUP BY referrer
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topreferrers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topreferrersx[] = $res;
					}
					// $sum = 30
					foreach ($topreferrersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topreferrers[$key]['url'] = (($array['referrer'] == '{_direct}') ? HTTPS_SERVER : 'http://' . $array['referrer']);
						$topreferrers[$key]['title'] = o($array['referrer']);
						$topreferrers[$key]['percent'] = $percent;
						$topreferrers[$key]['count'] = $array['count'];
					}
				}
				return $topreferrers;
			}
			else if ($do == 'topsearchterms')
			{
				$this->sheel->show['topsearchterms'] = false;
				$topsearchterms = array();
				$sql = $this->sheel->db->query("
					SELECT keyword, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "search_users
					WHERE " . $this->period_to_sql('`added`', $period) . "
						AND keyword != ''
						AND searchmode != 'brand'
						AND searchmode != 'brandowner'
					GROUP BY keyword
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topsearchterms'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topsearchtermsx[] = $res;
					}
					// $sum = 30
					foreach ($topsearchtermsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topsearchterms[$key]['url'] = HTTPS_SERVER . 'search/' . str_replace(' ', '+', $array['keyword']) . '.html';
						$topsearchterms[$key]['title'] = o($array['keyword']);
						$topsearchterms[$key]['percent'] = $percent;
						$topsearchterms[$key]['count'] = $array['count'];
					}
				}
				return $topsearchterms;
			}
			else if ($do == 'toplandingpages')
			{
				$this->sheel->show['toplandingpages'] = false;
				$toplandingpages = array();
				$sql = $this->sheel->db->query("
					SELECT landingpage, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND landingpage != ''
						AND storeid > 0
					GROUP BY landingpage
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['toplandingpages'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toplandingpagesx[] = $res;
					}
					// $sum = 30
					foreach ($toplandingpagesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$toplandingpages[$key]['url'] = substr(HTTPS_SERVER, 0, -1) . $array['landingpage'];
						$toplandingpages[$key]['title'] = o($array['landingpage']);
						$toplandingpages[$key]['percent'] = $percent;
						$toplandingpages[$key]['count'] = $array['count'];
					}
				}
				return $toplandingpages;
			}
			else if ($do == 'utmcampaigns')
			{
				$this->sheel->show['utmcampaigns'] = false;
				$utmcampaigns = array();
				$sql = $this->sheel->db->query("
					SELECT utmcampaign, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND utmcampaign != ''
						AND storeid > 0
					GROUP BY utmcampaign
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['utmcampaigns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$utmcampaignsx[] = $res;
					}
					// $sum = 30
					foreach ($utmcampaignsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$utmcampaigns[$key]['url'] = HTTPS_SERVER;
						$utmcampaigns[$key]['title'] = o('/?utm_campaign=' . $array['utmcampaign']);
						$utmcampaigns[$key]['percent'] = $percent;
						$utmcampaigns[$key]['count'] = $array['count'];
					}
				}
				return $utmcampaigns;
			}
		}
		else if ($what == 'brands')
		{
			if ($do == 'brandcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "brand
					WHERE VISIBLE = '1'
						AND MODERATED = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'productcount')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "brand_gtin
					WHERE VISIBLE = '1'
						AND MODERATED = '0'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'brandsmoderated')
			{
				$sql = $this->sheel->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "brand
					WHERE MODERATED = '1'
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return number_format($res['count']);
			}
			else if ($do == 'brandcountlabel')
			{
				return $this->period_to_label_brands();
			}
			else if ($do == 'brandcountseries')
			{
				return $this->period_to_series_brands();
			}
			// stats
			else if ($do == 'topbrandsbyorders')
			{
				$this->sheel->show['topbrandsbyorders'] = false;
				$topbrandsbyorders = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG, COUNT(*) AS count
					FROM " . DB_PREFIX . "brand b
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (b.BSIN = o.bsin)
					WHERE " . $this->period_to_sql('`orderdate`', $period) . "
						 AND o.parentid = 0
						 AND o.isreturned = '0'
						 AND o.iscancelled = '0'
						 AND o.bsin != ''
						 AND b.VISIBLE = '1'
	 						AND b.MODERATED = '0'
					GROUP BY b.BRAND_NM
					ORDER BY count DESC
					LIMIT 8
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandsbyorders'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandsbyordersx[] = $res;
					}
					foreach ($topbrandsbyordersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandsbyorders[$key]['icon'] = '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">';
						$topbrandsbyorders[$key]['title'] = '<a href="' . HTTPS_SERVER . $array['SLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>';
						$topbrandsbyorders[$key]['percent'] = $percent;
						$topbrandsbyorders[$key]['count'] = $array['count'];
					}
				}
				return $topbrandsbyorders;
			}
			else if ($do == 'topbrandownersbyorders')
			{
				$this->sheel->show['topbrandownersbyorders'] = false;
				$topbrandownersbyorders = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG AS BSLUG, w.OWNER_CD, n.OWNER_NM, n.SLUG AS NSLUG, COUNT(*) AS count
					FROM " . DB_PREFIX . "brand b
					LEFT JOIN " . DB_PREFIX . "brand_owner_bsin w ON (b.BSIN = w.BSIN)
					LEFT JOIN " . DB_PREFIX . "brand_owner n ON (w.OWNER_CD = n.OWNER_CD)
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (b.BSIN = o.bsin)
					WHERE " . $this->period_to_sql('`orderdate`', $period) . "
						 AND o.parentid = 0
						 AND o.isreturned = '0'
						 AND o.iscancelled = '0'
						 AND o.bsin != ''
						 AND n.OWNER_NM != ''
						 AND b.VISIBLE = '1'
				 		 AND b.MODERATED = '0'
						 AND n.VISIBLE = '1'
						 AND n.MODERATED = '0'
					GROUP BY n.OWNER_NM
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandownersbyorders'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandownersbyordersx[] = $res;
					}
					// $sum = 30
					foreach ($topbrandownersbyordersx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandownersbyorders[$key]['icon'] = ((empty($array['OWNER_NM'])) ? '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">' : '<img src="' . HTTP_BRAND_OWNER . $array['OWNER_CD'] . '.jpg" width="50">');
						$topbrandownersbyorders[$key]['title'] = ((empty($array['OWNER_NM'])) ? '<a href="' . HTTPS_SERVER  . $array['BSLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>' : '<a href="' . HTTPS_SERVER . 'b/' . $array['OSLUG'] . '/" target="_blank">' . o($array['OWNER_NM']) . '</a>');
						$topbrandownersbyorders[$key]['percent'] = $percent;
						$topbrandownersbyorders[$key]['count'] = $array['count'];
					}
				}
				return $topbrandownersbyorders;
			}
			else if ($do == 'topbrandsbyitemtraffic')
			{
				$this->sheel->show['topbrandsbyitemtraffic'] = false;
				$topbrandsbyitemtraffic = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG, COUNT(su.id) AS count
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "brand b ON (p.bsin = b.BSIN)
					LEFT JOIN " . DB_PREFIX . "search_users su ON (p.project_id = su.project_id)
					WHERE " . $this->period_to_sql('su.`added`', $period) . "
						AND p.bsin != ''
						AND b.BRAND_NM != ''
						AND b.VISIBLE = '1'
						AND b.MODERATED = '0'
					GROUP BY b.BSIN
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandsbyitemtraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandsbyitemtrafficx[] = $res;
					}
					foreach ($topbrandsbyitemtrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandsbyitemtraffic[$key]['icon'] = '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">';
						$topbrandsbyitemtraffic[$key]['title'] = '<a href="' . HTTPS_SERVER . $array['SLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>';
						$topbrandsbyitemtraffic[$key]['percent'] = $percent;
						$topbrandsbyitemtraffic[$key]['count'] = $array['count'];
					}
				}
				return $topbrandsbyitemtraffic;
			}
			else if ($do == 'topbrandownersbyitemtraffic')
			{
				$this->sheel->show['topbrandownersbyitemtraffic'] = false;
				$topbrandownersbyitemtraffic = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG AS BSLUG, w.OWNER_CD, n.OWNER_NM, n.SLUG AS NSLUG, COUNT(*) AS count
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "brand b ON (p.bsin = b.BSIN)
					LEFT JOIN " . DB_PREFIX . "brand_owner_bsin w ON (b.BSIN = w.BSIN)
					LEFT JOIN " . DB_PREFIX . "brand_owner n ON (w.OWNER_CD = n.OWNER_CD)
					LEFT JOIN " . DB_PREFIX . "search_users su ON (p.project_id = su.project_id)
					WHERE " . $this->period_to_sql('su.`added`', $period) . "
						 AND p.bsin != ''
						 AND n.OWNER_NM != ''
						 AND b.VISIBLE = '1'
	 						AND b.MODERATED = '0'
					GROUP BY n.OWNER_NM
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandownersbyitemtraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandownersbyitemtrafficx[] = $res;
					}
					foreach ($topbrandownersbyitemtrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandownersbyitemtraffic[$key]['icon'] = ((empty($array['OWNER_NM'])) ? '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">' : '<img src="' . HTTP_BRAND_OWNER . $array['OWNER_CD'] . '.jpg" width="50">');
						$topbrandownersbyitemtraffic[$key]['title'] = ((empty($array['OWNER_NM'])) ? '<a href="' . HTTPS_SERVER  . $array['BSLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>' : '<a href="' . HTTPS_SERVER . 'b/' . $array['NSLUG'] . '/" target="_blank">' . o($array['OWNER_NM']) . '</a>');
						$topbrandownersbyitemtraffic[$key]['percent'] = $percent;
						$topbrandownersbyitemtraffic[$key]['count'] = $array['count'];
					}
				}
				return $topbrandownersbyitemtraffic;
			}
			else if ($do == 'topbrandsbytraffic')
			{
				$this->sheel->show['topbrandsbytraffic'] = false;
				$topbrandsbytraffic = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG, COUNT(su.id) AS count
					FROM " . DB_PREFIX . "brand b
					LEFT JOIN " . DB_PREFIX . "hits su ON (b.BSIN = su.bsin)
					WHERE " . $this->period_to_sql('su.`datetime`', $period) . "
						AND b.BSIN != ''
						AND b.BRAND_NM != ''
						AND b.VISIBLE = '1'
						AND b.MODERATED = '0'
						AND su.bsin != ''
					GROUP BY b.BSIN
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandsbytraffic'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandsbytrafficx[] = $res;
					}
					foreach ($topbrandsbytrafficx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandsbytraffic[$key]['icon'] = '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">';
						$topbrandsbytraffic[$key]['title'] = '<a href="' . HTTPS_SERVER . $array['SLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>';
						$topbrandsbytraffic[$key]['percent'] = $percent;
						$topbrandsbytraffic[$key]['count'] = $array['count'];
					}
				}
				return $topbrandsbytraffic;
			}
			else if ($do == 'topbrandsbyorderreturns')
			{
				$this->sheel->show['topbrandsbyorderreturns'] = false;
				$topbrandsbyorderreturns = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG, COUNT(*) AS count
					FROM " . DB_PREFIX . "brand b
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (b.BSIN = o.bsin)
					WHERE " . $this->period_to_sql('`returnrequestdate`', $period) . "
						 AND (o.isreturned = '1' OR o.`return` = '1')
						 AND o.bsin != ''
						 AND b.BSIN != ''
						 AND b.VISIBLE = '1'
						 AND b.MODERATED = '0'
					GROUP BY b.BSIN
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandsbyorderreturns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandsbyorderreturnsx[] = $res;
					}
					foreach ($topbrandsbyorderreturnsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandsbyorderreturns[$key]['icon'] = '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">';
						$topbrandsbyorderreturns[$key]['title'] = '<a href="' . HTTPS_SERVER . $array['SLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>';
						$topbrandsbyorderreturns[$key]['percent'] = $percent;
						$topbrandsbyorderreturns[$key]['count'] = $array['count'];
					}
				}
				return $topbrandsbyorderreturns;
			}
			else if ($do == 'topbrandsbyordercancels')
			{
				$this->sheel->show['topbrandsbyordercancels'] = false;
				$topbrandsbyordercancels = array();
				$sql = $this->sheel->db->query("
					SELECT b.BSIN, b.BRAND_NM, b.SLUG, COUNT(*) AS count
					FROM " . DB_PREFIX . "brand b
					LEFT JOIN " . DB_PREFIX . "buynow_orders o ON (b.BSIN = o.bsin)
					WHERE " . $this->period_to_sql('`buyerrequestordercanceldate`', $period) . "
						 AND (o.iscancelled = '1' OR o.buyerrequestordercancel = '1')
						 AND o.bsin != ''
						 AND b.BSIN != ''
						 AND b.VISIBLE = '1'
						 AND b.MODERATED = '0'
					GROUP BY b.BSIN
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topbrandsbyordercancels'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrandsbyordercancelsx[] = $res;
					}
					foreach ($topbrandsbyordercancelsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrandsbyordercancels[$key]['icon'] = '<img src="' . HTTP_BRANDS . $array['BSIN'] . '.jpg" width="50">';
						$topbrandsbyordercancels[$key]['title'] = '<a href="' . HTTPS_SERVER . $array['SLUG'] . '/b/?bsin=' . $array['BSIN'] . '" target="_blank">' . o($array['BRAND_NM']) . '</a>';
						$topbrandsbyordercancels[$key]['percent'] = $percent;
						$topbrandsbyordercancels[$key]['count'] = $array['count'];
					}
				}
				return $topbrandsbyordercancels;
			}
			else if ($do == 'topsearchterms')
			{
				$this->sheel->show['topsearchterms'] = false;
				$topsearchterms = array();
				$sql = $this->sheel->db->query("
					SELECT keyword, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "search_users
					WHERE " . $this->period_to_sql('`added`', $period) . "
						AND keyword != ''
						AND (searchmode = 'brand' OR searchmode = 'brandowner')
					GROUP BY keyword
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['topsearchterms'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topsearchtermsx[] = $res;
					}
					foreach ($topsearchtermsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topsearchterms[$key]['url'] = HTTPS_SERVER . 'b/?q=' . str_replace(' ', '+', $array['keyword']);
						$topsearchterms[$key]['title'] = o($array['keyword']);
						$topsearchterms[$key]['percent'] = $percent;
						$topsearchterms[$key]['count'] = $array['count'];
					}
				}
				return $topsearchterms;
			}
			else if ($do == 'toplandingpages')
			{
				$this->sheel->show['toplandingpages'] = false;
				$toplandingpages = array();
				$sql = $this->sheel->db->query("
					SELECT landingpage, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND landingpage != ''
						AND (bsin != '' OR owner_cd > 0)
					GROUP BY landingpage
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['toplandingpages'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toplandingpagesx[] = $res;
					}
					// $sum = 30
					foreach ($toplandingpagesx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$toplandingpages[$key]['url'] = substr(HTTPS_SERVER, 0, -1) . $array['landingpage'];
						$toplandingpages[$key]['title'] = o($array['landingpage']);
						$toplandingpages[$key]['percent'] = $percent;
						$toplandingpages[$key]['count'] = $array['count'];
					}
				}
				return $toplandingpages;
			}
			else if ($do == 'utmcampaigns')
			{
				$this->sheel->show['utmcampaigns'] = false;
				$utmcampaigns = array();
				$sql = $this->sheel->db->query("
					SELECT utmcampaign, COUNT(DISTINCT ipaddress) AS count
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
						AND utmcampaign != ''
						AND (bsin != '' OR owner_cd > 0)
					GROUP BY utmcampaign
					ORDER BY count DESC
					LIMIT 5
				");
				if ($this->sheel->db->num_rows($sql) > 0)
				{
					$this->sheel->show['utmcampaigns'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$utmcampaignsx[] = $res;
					}
					// $sum = 30
					foreach ($utmcampaignsx AS $key => $array)
					{
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$utmcampaigns[$key]['url'] = HTTPS_SERVER;
						$utmcampaigns[$key]['title'] = o('/?utm_campaign=' . $array['utmcampaign']);
						$utmcampaigns[$key]['percent'] = $percent;
						$utmcampaigns[$key]['count'] = $array['count'];
					}
				}
				return $utmcampaigns;
			}
		}
		else if ($what == 'memberships')
		{
			//
		}
	}
	private function period_to_label_brands()
	{
		$return = ''; //"'Apple', 'Heinz', 'Nino Cerruti', 'Paco Rabanne', 'Carolina', 'AB Pieno', 'Old Spice'";
		return $return;
	}
	private function period_to_series_brands()
	{
		$return = ''; //"[5, 4, 3, 7, 5, 10, 3]";
		return $return;
	}
	private function period_to_label($period = '')
	{
		switch ($period)
		{
			case 'yesterday':
			case 'today':
			{
				$return = "'12am','4am','8am','12pm','4pm','8pm','11pm'";
				break;
			}
			case 'last7days':
			{
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++)
				{
					$array[] = "'" . vdate('M. j', $timestamp) . "',";
					$timestamp -= 24 * 3600;
				}
				$array = array_reverse($array);
				foreach ($array AS $value)
				{
					$return .= "$value";
				}
				break;
			}
			case 'last30days':
			{
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++)
				{
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 5;
				}
				$array = array_reverse($array);
				foreach ($array AS $value)
				{
					$return .= "$value";
				}
				break;
			}
			case 'last60days':
			{
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++)
				{
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 10;
				}
				$array = array_reverse($array);
				foreach ($array AS $value)
				{
					$return .= "$value";
				}
				break;
			}
			case 'last90days':
			{
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++)
				{
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 15;
				}
				$array = array_reverse($array);
				foreach ($array AS $value)
				{
					$return .= "$value";
				}
				break;
			}
			case 'last365days':
			{
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++)
				{
					$array[] = "'" . vdate('M.', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 60;
				}
				$array = array_reverse($array);
				foreach ($array AS $value)
				{
					$return .= "$value";
				}
				break;
			}
		}
		return $return;
	}
	private function period_to_series($what = '', $period = '')
	{
		$return = '';
		if ($what == 'visitors')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'4' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'8' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'12' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'16' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'20' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0),
						'23' => array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						WHEN HOUR(firsthit) BETWEEN 0 AND 3 AND TIME(firsthit) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
						WHEN HOUR(firsthit) BETWEEN 4 AND 7 AND TIME(firsthit) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
						WHEN HOUR(firsthit) BETWEEN 8 AND 11 AND TIME(firsthit) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
						WHEN HOUR(firsthit) BETWEEN 12 AND 15 AND TIME(firsthit) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
						WHEN HOUR(firsthit) BETWEEN 16 AND 19 AND TIME(firsthit) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
						WHEN HOUR(firsthit) BETWEEN 20 AND 22 AND TIME(firsthit) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
						WHEN HOUR(firsthit) = 23 AND TIME(firsthit) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period) . "
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(firsthit) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('j', $timestamp)] = array('uniquevisitors' => 0, 'visitors' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DAY(`date`) AS day, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period) . "
						GROUP BY day
						ORDER BY `date` ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$otimestamp = $timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN `date` BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period, '35') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY `date` ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN `date` BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period, '65') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY `date` ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN `date` BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0);
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period, '95') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY `date` ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN `date` BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "'" . LINEBREAK;
						$series[date('Y-m-d', $timestamp)] = array('uniquevisitors' => 0, 'visitors' => 0, 'pageviews' => 0);
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
						FROM " . DB_PREFIX . "visits
						WHERE " . $this->period_to_sql('`date`', $period, '375') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY `date` ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $hour => $array)
			{
				$seriesa .= "'" . $array['visitors'] . "',";
				$seriesb .= "'" . $array['uniquevisitors'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		else if ($what == 'listings')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('open' => 0, 'ended' => 0),
						'4' => array('open' => 0, 'ended' => 0),
						'8' => array('open' => 0, 'ended' => 0),
						'12' => array('open' => 0, 'ended' => 0),
						'16' => array('open' => 0, 'ended' => 0),
						'20' => array('open' => 0, 'ended' => 0),
						'23' => array('open' => 0, 'ended' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(date_added) BETWEEN 0  AND 3  AND TIME(date_added) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
							WHEN HOUR(date_added) BETWEEN 4  AND 7  AND TIME(date_added) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
							WHEN HOUR(date_added) BETWEEN 8  AND 11 AND TIME(date_added) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
							WHEN HOUR(date_added) BETWEEN 12 AND 15 AND TIME(date_added) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
							WHEN HOUR(date_added) BETWEEN 16 AND 19 AND TIME(date_added) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
							WHEN HOUR(date_added) BETWEEN 20 AND 22 AND TIME(date_added) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
							WHEN HOUR(date_added) = 23 AND TIME(date_added) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period) . "
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(date_added) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('open' => $res['open'], 'ended' => 0);
					}
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(date_end) BETWEEN 0  AND 3  AND TIME(date_end) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
							WHEN HOUR(date_end) BETWEEN 4  AND 7  AND TIME(date_end) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
							WHEN HOUR(date_end) BETWEEN 8  AND 11 AND TIME(date_end) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
							WHEN HOUR(date_end) BETWEEN 12 AND 15 AND TIME(date_end) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
							WHEN HOUR(date_end) BETWEEN 16 AND 19 AND TIME(date_end) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
							WHEN HOUR(date_end) BETWEEN 20 AND 22 AND TIME(date_end) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
							WHEN HOUR(date_end) = 23 AND TIME(date_end) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period) . "
							AND date_end <= CURDATE()
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(date_end) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']]['ended'] = $res['ended'];
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('open' => 0, 'ended' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(date_added) AS day, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period) . "
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['open'] = $res['open'];
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(date_end) AS day, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period) . "
							AND date_end <= CURDATE()
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['ended'] = $res['ended'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_added BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('open' => 0, 'ended' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period, '35') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_added ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['open'] = $res['open'];
					}
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					$case = '';
					$dayin = '';
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_end BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['ended'] = 0;
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period, '35') . "
							AND date_end <= CURDATE()
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_end ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['ended'] = $res['ended'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_added BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('open' => 0, 'ended' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period, '65') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_added ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['open'] = $res['open'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_end BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['ended'] = 0;
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period, '65') . "
							AND date_end <= CURDATE()
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_end ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['ended'] = $res['ended'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['open'] = 0;
						$case .= "WHEN date_added BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period, '65') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_added ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['open'] = $res['open'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['ended'] = 0;
						$case .= "WHEN date_end BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period, '65') . "
							AND date_end <= CURDATE()
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_end ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['ended'] = $res['ended'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('open' => 0, 'ended' => 0);
						$case .= "WHEN date_added BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS open
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_added', $period, '375') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_added ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['open'] = $res['open'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['ended'] = 0;
						$case .= "WHEN date_end BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, COUNT(*) AS ended
						FROM " . DB_PREFIX . "projects
						WHERE " . $this->period_to_sql('date_end', $period, '375') . "
							AND date_end <= CURDATE()
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_end ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['ended'] = $res['ended'];
					}
					$series = array_reverse($series);
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $day => $array)
			{
				$seriesa .= "'" . $array['open'] . "',";
				$seriesb .= "'" . $array['ended'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		else if ($what == 'revenue')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('amount' => 0),
						'4' => array('amount' => 0),
						'8' => array('amount' => 0),
						'12' => array('amount' => 0),
						'16' => array('amount' => 0),
						'20' => array('amount' => 0),
						'23' => array('amount' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						WHEN HOUR(paiddate) BETWEEN 0 AND 3 AND TIME(paiddate) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
						WHEN HOUR(paiddate) BETWEEN 4 AND 7 AND TIME(paiddate) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
						WHEN HOUR(paiddate) BETWEEN 8 AND 11 AND TIME(paiddate) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
						WHEN HOUR(paiddate) BETWEEN 12 AND 15 AND TIME(paiddate) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
						WHEN HOUR(paiddate) BETWEEN 16 AND 19 AND TIME(paiddate) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
						WHEN HOUR(paiddate) BETWEEN 20 AND 22 AND TIME(paiddate) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
						WHEN HOUR(paiddate) = 23 AND TIME(paiddate) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(paiddate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(paiddate) AS day, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '35') . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(paid) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '375') . "
							AND status = 'paid'
							AND paid > 0
							AND (istransactionfee = '1' OR isfvfbuyer = '1' OR iswithdrawfee = '1' OR isescrowfee = '1' OR isenhancementfee = '1' OR isportfoliofee = '1' OR isif = '1' OR isfvf = '1')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
			}
			$seriesa = '[';
			foreach ($series AS $hour => $array)
			{
				$seriesa .= "'" . $array['amount'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesa .= ']';
			$return = $seriesa;
		}
		else if ($what == 'sales')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('amount' => 0),
						'4' => array('amount' => 0),
						'8' => array('amount' => 0),
						'12' => array('amount' => 0),
						'16' => array('amount' => 0),
						'20' => array('amount' => 0),
						'23' => array('amount' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						WHEN HOUR(orderdate) BETWEEN 0 AND 3 AND TIME(orderdate) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
						WHEN HOUR(orderdate) BETWEEN 4 AND 7 AND TIME(orderdate) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
						WHEN HOUR(orderdate) BETWEEN 8 AND 11 AND TIME(orderdate) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
						WHEN HOUR(orderdate) BETWEEN 12 AND 15 AND TIME(orderdate) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
						WHEN HOUR(orderdate) BETWEEN 16 AND 19 AND TIME(orderdate) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
						WHEN HOUR(orderdate) BETWEEN 20 AND 22 AND TIME(orderdate) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
						WHEN HOUR(orderdate) = 23 AND TIME(orderdate) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period) . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(orderdate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(orderdate) AS day, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period) . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN orderdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period, '35') . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY orderdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN orderdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period, '65') . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$case .= "WHEN orderdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period, '65') . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY orderdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('amount' => 0);
						$case .= "WHEN orderdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS amount
						FROM " . DB_PREFIX . "buynow_orders
						WHERE " . $this->period_to_sql('orderdate', $period, '375') . "
							AND amount > 0
							AND iscancelled = '0'
							AND isreturned = '0'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY orderdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('amount' => $res['amount']);
					}
					break;
				}
			}
			$seriesa = '[';
			foreach ($series AS $hour => $array)
			{
				$seriesa .= "'" . $array['amount'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesa .= ']';
			$return = $seriesa;
		}
		else if ($what == 'accounting')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('debit' => 0, 'credit' => 0),
						'4' => array('debit' => 0, 'credit' => 0),
						'8' => array('debit' => 0, 'credit' => 0),
						'12' => array('debit' => 0, 'credit' => 0),
						'16' => array('debit' => 0, 'credit' => 0),
						'20' => array('debit' => 0, 'credit' => 0),
						'23' => array('debit' => 0, 'credit' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						WHEN HOUR(datetime) BETWEEN 0 AND 3 AND TIME(datetime) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
						WHEN HOUR(datetime) BETWEEN 4 AND 7 AND TIME(datetime) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
						WHEN HOUR(datetime) BETWEEN 8 AND 11 AND TIME(datetime) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
						WHEN HOUR(datetime) BETWEEN 12 AND 15 AND TIME(datetime) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
						WHEN HOUR(datetime) BETWEEN 16 AND 19 AND TIME(datetime) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
						WHEN HOUR(datetime) BETWEEN 20 AND 22 AND TIME(datetime) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
						WHEN HOUR(datetime) = 23 AND TIME(datetime) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period) . "
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(datetime) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('debit' => 0, 'credit' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(datetime) AS day, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period) . "
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN datetime BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('debit' => 0, 'credit' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period, '35') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY datetime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN datetime BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('debit' => 0, 'credit' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period, '65') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY datetime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('debit' => 0, 'credit' => 0);
						$case .= "WHEN datetime BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period, '65') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY datetime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('debit' => 0, 'credit' => 0);
						$case .= "WHEN datetime BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(debit) AS debit, SUM(credit) AS credit
						FROM " . DB_PREFIX . "transactions
						WHERE " . $this->period_to_sql('datetime', $period, '375') . "
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY datetime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']] = array('debit' => $res['debit'], 'credit' => $res['credit']);
					}
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $hour => $array)
			{
				$seriesa .= "'" . $array['credit'] . "',";
				$seriesb .= "'" . $array['debit'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		else if ($what == 'escrow')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('funded' => 0, 'released' => 0),
						'4' => array('funded' => 0, 'released' => 0),
						'8' => array('funded' => 0, 'released' => 0),
						'12' => array('funded' => 0, 'released' => 0),
						'16' => array('funded' => 0, 'released' => 0),
						'20' => array('funded' => 0, 'released' => 0),
						'23' => array('funded' => 0, 'released' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(date_funded) BETWEEN 0  AND 3  AND TIME(date_funded) BETWEEN '00:00:00' AND '03:59:59' AND (status = 'funded' OR status = 'released') THEN '0'
							WHEN HOUR(date_funded) BETWEEN 4  AND 7  AND TIME(date_funded) BETWEEN '04:00:00' AND '07:59:59' AND (status = 'funded' OR status = 'released') THEN '4'
							WHEN HOUR(date_funded) BETWEEN 8  AND 11 AND TIME(date_funded) BETWEEN '08:00:00' AND '11:59:59' AND (status = 'funded' OR status = 'released') THEN '8'
							WHEN HOUR(date_funded) BETWEEN 12 AND 15 AND TIME(date_funded) BETWEEN '12:00:00' AND '15:59:59' AND (status = 'funded' OR status = 'released') THEN '12'
							WHEN HOUR(date_funded) BETWEEN 16 AND 19 AND TIME(date_funded) BETWEEN '16:00:00' AND '19:59:59' AND (status = 'funded' OR status = 'released') THEN '16'
							WHEN HOUR(date_funded) BETWEEN 20 AND 22 AND TIME(date_funded) BETWEEN '20:00:00' AND '22:59:59' AND (status = 'funded' OR status = 'released') THEN '20'
							WHEN HOUR(date_funded) = 23 AND TIME(date_funded) BETWEEN '23:00:00' AND '23:59:59' AND (status = 'funded' OR status = 'released') THEN '23'
						END AS hour,
						CASE
							WHEN (status = 'funded' OR status = 'released') THEN SUM(total_converted)
						END AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period) . "
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(date_funded) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('funded' => $res['funded'], 'released' => 0);
					}
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(date_released) BETWEEN 0  AND 3  AND TIME(date_released) BETWEEN '00:00:00' AND '03:59:59' AND status = 'released' THEN '0'
							WHEN HOUR(date_released) BETWEEN 4  AND 7  AND TIME(date_released) BETWEEN '04:00:00' AND '07:59:59' AND status = 'released' THEN '4'
							WHEN HOUR(date_released) BETWEEN 8  AND 11 AND TIME(date_released) BETWEEN '08:00:00' AND '11:59:59' AND status = 'released' THEN '8'
							WHEN HOUR(date_released) BETWEEN 12 AND 15 AND TIME(date_released) BETWEEN '12:00:00' AND '15:59:59' AND status = 'released' THEN '12'
							WHEN HOUR(date_released) BETWEEN 16 AND 19 AND TIME(date_released) BETWEEN '16:00:00' AND '19:59:59' AND status = 'released' THEN '16'
							WHEN HOUR(date_released) BETWEEN 20 AND 22 AND TIME(date_released) BETWEEN '20:00:00' AND '22:59:59' AND status = 'released' THEN '20'
							WHEN HOUR(date_released) = 23 AND TIME(date_released) BETWEEN '23:00:00' AND '23:59:59' AND status = 'released' THEN '23'
						END AS hour,
						CASE
							WHEN status = 'released' THEN SUM(total_converted)
						END AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period) . "
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(date_released) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']]['released'] = $res['released'];
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('funded' => 0, 'released' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(date_funded) AS day, SUM(total_converted) AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period) . "
							AND (status = 'funded' OR status = 'released')
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['funded'] = $res['funded'];
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(date_released) AS day, SUM(total_converted) AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period) . "
							AND status = 'released'
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['released'] = $res['released'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_funded BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('funded' => 0, 'released' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period, '35') . "
							AND (status = 'funded' OR status = 'released')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_funded ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['funded'] = $res['funded'];
					}
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					$case = '';
					$dayin = '';
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_released BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period, '35') . "
							AND status = 'released'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_released ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['released'] = $res['released'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_funded BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('funded' => 0, 'released' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period, '65') . "
							AND (status = 'funded' OR status = 'released')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_funded ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['funded'] = $res['funded'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN date_released BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period, '65') . "
							AND status = 'released'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_funded ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['released'] = $res['released'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['funded'] = 0;
						$case .= "WHEN date_funded BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period, '65') . "
							AND (status = 'funded' OR status = 'released')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_funded ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['funded'] = $res['funded'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$case .= "WHEN date_released BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period, '65') . "
							AND status = 'released'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_released ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['released'] = $res['released'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('funded' => 0, 'released' => 0);
						$case .= "WHEN date_funded BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS funded
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_funded', $period, '375') . "
							AND (status = 'funded' OR status = 'released')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_funded ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['funded'] = $res['funded'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$case .= "WHEN date_released BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(total_converted) AS released
						FROM " . DB_PREFIX . "escrow
						WHERE " . $this->period_to_sql('date_released', $period, '375') . "
							AND status = 'released'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY date_released ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['released'] = $res['released'];
					}
					$series = array_reverse($series);
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $day => $array)
			{
				$seriesa .= "'" . $array['funded'] . "',";
				$seriesb .= "'" . $array['released'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		else if ($what == 'deposits')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('pending' => 0, 'completed' => 0),
						'4' => array('pending' => 0, 'completed' => 0),
						'8' => array('pending' => 0, 'completed' => 0),
						'12' => array('pending' => 0, 'completed' => 0),
						'16' => array('pending' => 0, 'completed' => 0),
						'20' => array('pending' => 0, 'completed' => 0),
						'23' => array('pending' => 0, 'completed' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(createdate) BETWEEN 0  AND 3  AND TIME(createdate) BETWEEN '00:00:00' AND '03:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '0'
							WHEN HOUR(createdate) BETWEEN 4  AND 7  AND TIME(createdate) BETWEEN '04:00:00' AND '07:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '4'
							WHEN HOUR(createdate) BETWEEN 8  AND 11 AND TIME(createdate) BETWEEN '08:00:00' AND '11:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '8'
							WHEN HOUR(createdate) BETWEEN 12 AND 15 AND TIME(createdate) BETWEEN '12:00:00' AND '15:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '12'
							WHEN HOUR(createdate) BETWEEN 16 AND 19 AND TIME(createdate) BETWEEN '16:00:00' AND '19:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '16'
							WHEN HOUR(createdate) BETWEEN 20 AND 22 AND TIME(createdate) BETWEEN '20:00:00' AND '22:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '20'
							WHEN HOUR(createdate) = 23 AND TIME(createdate) BETWEEN '23:00:00' AND '23:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '23'
						END AS hour,
						CASE
							WHEN (status = 'scheduled' OR status = 'paid') THEN SUM(depositcreditamount)
						END AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period) . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(createdate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('pending' => $res['pending'], 'completed' => 0);
					}
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(paiddate) BETWEEN 0  AND 3  AND TIME(paiddate) BETWEEN '00:00:00' AND '03:59:59' AND status = 'paid' THEN '0'
							WHEN HOUR(paiddate) BETWEEN 4  AND 7  AND TIME(paiddate) BETWEEN '04:00:00' AND '07:59:59' AND status = 'paid' THEN '4'
							WHEN HOUR(paiddate) BETWEEN 8  AND 11 AND TIME(paiddate) BETWEEN '08:00:00' AND '11:59:59' AND status = 'paid' THEN '8'
							WHEN HOUR(paiddate) BETWEEN 12 AND 15 AND TIME(paiddate) BETWEEN '12:00:00' AND '15:59:59' AND status = 'paid' THEN '12'
							WHEN HOUR(paiddate) BETWEEN 16 AND 19 AND TIME(paiddate) BETWEEN '16:00:00' AND '19:59:59' AND status = 'paid' THEN '16'
							WHEN HOUR(paiddate) BETWEEN 20 AND 22 AND TIME(paiddate) BETWEEN '20:00:00' AND '22:59:59' AND status = 'paid' THEN '20'
							WHEN HOUR(paiddate) = 23 AND TIME(paiddate) BETWEEN '23:00:00' AND '23:59:59' AND status = 'paid' THEN '23'
						END AS hour,
						CASE
							WHEN status = 'paid' THEN SUM(depositcreditamount)
						END AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(paiddate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']]['completed'] = $res['completed'];
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(createdate) AS day, SUM(depositcreditamount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period) . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(paiddate) AS day, SUM(depositcreditamount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '35') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					$case = '';
					$dayin = '';
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '35') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '65') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['pending'] = 0;
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '65') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '375') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(depositcreditamount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '375') . "
							AND invoicetype = 'credit'
							AND depositcreditamount > 0
							AND isdeposit = '1'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $day => $array)
			{
				$seriesa .= "'" . $array['pending'] . "',";
				$seriesb .= "'" . $array['completed'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		else if ($what == 'withdraws')
		{
			switch ($period)
			{
				case 'today':
				case 'yesterday':
				{
					$series = array(
						'0' => array('pending' => 0, 'completed' => 0),
						'4' => array('pending' => 0, 'completed' => 0),
						'8' => array('pending' => 0, 'completed' => 0),
						'12' => array('pending' => 0, 'completed' => 0),
						'16' => array('pending' => 0, 'completed' => 0),
						'20' => array('pending' => 0, 'completed' => 0),
						'23' => array('pending' => 0, 'completed' => 0)
					);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(createdate) BETWEEN 0  AND 3  AND TIME(createdate) BETWEEN '00:00:00' AND '03:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '0'
							WHEN HOUR(createdate) BETWEEN 4  AND 7  AND TIME(createdate) BETWEEN '04:00:00' AND '07:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '4'
							WHEN HOUR(createdate) BETWEEN 8  AND 11 AND TIME(createdate) BETWEEN '08:00:00' AND '11:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '8'
							WHEN HOUR(createdate) BETWEEN 12 AND 15 AND TIME(createdate) BETWEEN '12:00:00' AND '15:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '12'
							WHEN HOUR(createdate) BETWEEN 16 AND 19 AND TIME(createdate) BETWEEN '16:00:00' AND '19:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '16'
							WHEN HOUR(createdate) BETWEEN 20 AND 22 AND TIME(createdate) BETWEEN '20:00:00' AND '22:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '20'
							WHEN HOUR(createdate) = 23 AND TIME(createdate) BETWEEN '23:00:00' AND '23:59:59' AND (status = 'scheduled' OR status = 'paid') THEN '23'
						END AS hour,
						CASE
							WHEN (status = 'scheduled' OR status = 'paid') THEN SUM(amount)
						END AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period) . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(createdate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']] = array('pending' => $res['pending'], 'completed' => 0);
					}
					$sql = $this->sheel->db->query("
						SELECT
						CASE
							WHEN HOUR(paiddate) BETWEEN 0  AND 3  AND TIME(paiddate) BETWEEN '00:00:00' AND '03:59:59' AND status = 'paid' THEN '0'
							WHEN HOUR(paiddate) BETWEEN 4  AND 7  AND TIME(paiddate) BETWEEN '04:00:00' AND '07:59:59' AND status = 'paid' THEN '4'
							WHEN HOUR(paiddate) BETWEEN 8  AND 11 AND TIME(paiddate) BETWEEN '08:00:00' AND '11:59:59' AND status = 'paid' THEN '8'
							WHEN HOUR(paiddate) BETWEEN 12 AND 15 AND TIME(paiddate) BETWEEN '12:00:00' AND '15:59:59' AND status = 'paid' THEN '12'
							WHEN HOUR(paiddate) BETWEEN 16 AND 19 AND TIME(paiddate) BETWEEN '16:00:00' AND '19:59:59' AND status = 'paid' THEN '16'
							WHEN HOUR(paiddate) BETWEEN 20 AND 22 AND TIME(paiddate) BETWEEN '20:00:00' AND '22:59:59' AND status = 'paid' THEN '20'
							WHEN HOUR(paiddate) = 23 AND TIME(paiddate) BETWEEN '23:00:00' AND '23:59:59' AND status = 'paid' THEN '23'
						END AS hour,
						CASE
							WHEN status = 'paid' THEN SUM(amount)
						END AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(paiddate) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['hour']]['completed'] = $res['completed'];
					}
					break;
				}
				case 'last7days':
				{
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++)
					{
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(createdate) AS day, SUM(amount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period) . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$sql = $this->sheel->db->query("
						SELECT DATE(paiddate) AS day, SUM(amount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period) . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY day
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '35') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					$case = '';
					$dayin = '';
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '35') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last60days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '65') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last90days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['pending'] = 0;
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '65') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['completed'] = 0;
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '65') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
				case 'last365days':
				{
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('pending' => 0, 'completed' => 0);
						$case .= "WHEN createdate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS pending
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('createdate', $period, '375') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND (status = 'scheduled' OR status = 'paid')
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['pending'] = $res['pending'];
					}
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++)
					{
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)]['released'] = 0;
						$case .= "WHEN paiddate BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$series = array_reverse($series);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(amount) AS completed
						FROM " . DB_PREFIX . "invoices
						WHERE " . $this->period_to_sql('paiddate', $period, '375') . "
							AND invoicetype = 'debit'
							AND amount > 0
							AND iswithdraw = '1'
							AND iswithdrawfee = '0'
							AND status = 'paid'
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY paiddate ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
					{
						$series[$res['day']]['completed'] = $res['completed'];
					}
					$series = array_reverse($series);
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series AS $day => $array)
			{
				$seriesa .= "'" . $array['pending'] . "',";
				$seriesb .= "'" . $array['completed'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		}
		return $return;
	}
	private function period_to_sql($column = '', $period = '', $overide = '')
	{
		switch ($period)
		{
			case 'today':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") = CURDATE()";
			}
			case 'yesterday':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") = (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '1') . " DAY)";
			}
			case 'last7days':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '7') . " DAY)";
			}
			case 'last30days':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '30') . " DAY)";
			}
			case 'last60days':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '60') . " DAY)";
			}
			case 'last90days':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '90') . " DAY)";
			}
			case 'last365days':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '365') . " DAY)";
			}
			case 'alltime':
			{
				return "DATE(" . $this->sheel->db->escape_string($column) . ") != ''";
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Sun, Jun 16th, 2019
|| ####################################################################
\*======================================================================*/
?>
