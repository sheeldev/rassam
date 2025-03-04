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
	function fetch($what = '', $period = 'last7days', $do = '', $company = '', $country = '')
	{
		if ($what == 'home') {
			if ($do == 'visitors' or $do == 'uniquevisitors' or $do == 'pageviews') {
				$sql = $this->sheel->db->query("
					SELECT COUNT(DISTINCT ipaddress) AS uniquevisitors, COUNT(*) AS visitors, SUM(pageviews) AS pageviews
					FROM " . DB_PREFIX . "visits
					WHERE " . $this->period_to_sql('`date`', $period) . "
				");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($do == 'visitors') {
					return number_format($res['visitors']);
				} else if ($do == 'uniquevisitors') {
					return number_format($res['uniquevisitors']);
				} else if ($do == 'pageviews') {
					return number_format($res['pageviews']);
				}
			} else if ($do == 'visitorlabel') {
				return $this->period_to_label($period);
			} else if ($do == 'visitorseries') {
				return $this->period_to_series('visitors', $period);
			} else if ($do == 'mostactive') // Morning, Afternoon, Evening, Night
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
				while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
					$loop[] = $res;
				}
				return $loop;
			} else if ($do == 'topcountries') {
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
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topcountries'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topcountriesx[] = $res;
					}
					// $sum = 30
					foreach ($topcountriesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcountries[$key]['icon'] = $this->sheel->common_location->print_country_flag($array['locationid']);
						$topcountries[$key]['title'] = $array['country'];
						$topcountries[$key]['percent'] = $percent;
						$topcountries[$key]['count'] = $array['count'];
					}
				}
				return $topcountries;
			} else if ($do == 'topdevices') {
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
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topdevices'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topdevicesx[] = $res;
					}
					// $sum = 30
					foreach ($topdevicesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topdevices[$key]['title'] = ((empty($array['device'])) ? '{_other}' : '{_' . $array['device'] . '}');
						$topdevices[$key]['percent'] = $percent;
						$topdevices[$key]['count'] = $array['count'];
					}
				}
				return $topdevices;
			} else if ($do == 'topbrowsers') {
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
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topbrowsers'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topbrowsersx[] = $res;
					}
					// $sum = 30
					foreach ($topbrowsersx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topbrowsers[$key]['icon'] = $this->sheel->common->fetch_browser_name(1, $array['browser']);
						$topbrowsers[$key]['title'] = $this->sheel->common->fetch_browser_name(0, $array['browser']);
						$topbrowsers[$key]['percent'] = $percent;
						$topbrowsers[$key]['count'] = $array['count'];
					}
				}
				return $topbrowsers;
			} else if ($do == 'trafficsources') {
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
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['trafficsources'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$trafficsourcesx[] = $res;
					}
					// $sum = 30
					foreach ($trafficsourcesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$trafficsources[$key]['title'] = '{_' . $array['sources'] . '}';
						$trafficsources[$key]['percent'] = $percent;
						$trafficsources[$key]['count'] = $array['count'];
					}
				}
				return $trafficsources;
			} else if ($do == 'toplandingpages') {
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
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['toplandingpages'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$toplandingpagesx[] = $res;
					}
					// $sum = 30
					foreach ($toplandingpagesx as $key => $array) {
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
		if ($what == 'dashboard') {
			$sqltotal = $this->sheel->db->query("
							SELECT DISTINCT analysisreference
							FROM " . DB_PREFIX . "analysis a
							LEFT JOIN " . DB_PREFIX . "customers c ON a.analysisidentifier = c.customer_ref
							WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
				$this->field_to_sql($company, $country) . "
						");

			$sum = $this->sheel->db->num_rows($sqltotal);
			$sqlassemblytotal = $this->sheel->db->query("
				SELECT SUM(totalquantity) AS totalquantity
				FROM " . DB_PREFIX . "analysis_records
				WHERE recordidentifier in (SELECT analysisreference FROM " . DB_PREFIX . "analysis where " . $this->period_to_sql('`createdtime`', $period, '', true) . $this->field_to_sql($company, $country) . ") 
				AND lastcheckpoint NOT IN (SELECT checkpointid FROM " . DB_PREFIX . "checkpoints WHERE type = 'Assembly' and triggeredon='0-Out')
			");

			$ressum = $this->sheel->db->fetch_array($sqlassemblytotal, DB_ASSOC);
			$sumassemblies = $ressum['totalquantity'];

			if ($do == 'totalorders' or $do == 'totalquantity' or $do == 'invoiced' or $do == 'archived' or $do == 'smallorders' or $do == 'mediumorders' or $do == 'largeorders') {
				$sql = $this->sheel->db->query("
							SELECT COUNT(analysisreference) AS totalorders , SUM(totalquantity) AS totalquantity,  SUM(isfinished) AS invoiced, SUM(isarchived) AS archived, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
							FROM " . DB_PREFIX . "analysis
							WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
						");
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($do == 'totalorders') {
					return number_format($res['totalorders']);
				} else if ($do == 'totalquantity') {
					return number_format($res['totalquantity']);
				} else if ($do == 'invoiced') {
					return number_format($res['invoiced']);
				} else if ($do == 'archived') {
					return number_format($res['archived']);
				} else if ($do == 'smallorders') {
					return number_format($res['smallorders']);
				} else if ($do == 'mediumorders') {
					return number_format($res['mediumorders']);
				} else if ($do == 'largeorders') {
					return number_format($res['largeorders']);
				}
			} else if ($do == 'orderlabel') {
				return $this->period_to_label($period);
			} else if ($do == 'orderseries') {
				return $this->period_to_series('orders', $period, $company, $country);
			} else if ($do == 'topdestinations') {
				$this->sheel->show['topdestinations'] = false;
				$topdestinations = array();
				$sql = $this->sheel->db->query("
					SELECT a.countrycode, l.cc, l.locationid, l.location_" . $_SESSION['sheeldata']['user']['slng'] . ", COUNT(countrycode) AS count
					FROM " . DB_PREFIX . "analysis a
					LEFT JOIN " . DB_PREFIX . "locations l ON (a.countrycode = l.cc)
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
					GROUP BY a.countrycode
					ORDER BY count DESC
					LIMIT 7
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topdestinations'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$topdestinationsx[] = $res;
					}
					foreach ($topdestinationsx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topdestinations[$key]['icon'] = $this->sheel->common_location->print_country_flag($array['locationid']);
						$topdestinations[$key]['title'] = $array['location_' . $_SESSION['sheeldata']['user']['slng']];
						$topdestinations[$key]['code'] = $array['countrycode'];
						$topdestinations[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=topdestinations&code=' . $array['countrycode'] . '&period=' . $period;
						if (!empty($company)) {
							$topdestinations[$key]['url'] .= '&company=' . $company;
						}

						if (!empty($country)) {
							$topdestinations[$key]['url'] .= '&country=' . $country;
						}
						$topdestinations[$key]['percent'] = $percent;
						$topdestinations[$key]['count'] = $array['count'];
					}
				}
				return $topdestinations;
			} else if ($do == 'deliveries') {
				$this->sheel->show['deliveries'] = false;
				$deliveries = array();
				$today = new DateTime();
				$week = $today->format('W');
				$year = $today->format('Y');
				$sql = $this->sheel->db->query("
					SELECT deliveryweek, deliveryyear, COUNT(analysisreference) AS count, sum(totalquantity) as totalquantity
					FROM " . DB_PREFIX . "analysis
					WHERE deliveryweek >= '" . $week . "' AND deliveryyear >= '" . $year . "' AND (isfinished = '0' AND isarchived = '0')
					" . $this->field_to_sql($company, $country) . "
					GROUP BY deliveryweek, deliveryyear
					ORDER BY deliveryweek, deliveryyear asc
					LIMIT 15
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['deliveries'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$deliveriesx[] = $res;
					}
					foreach ($deliveriesx as $key => $array) {
						$deliveries[$key]['title'] = $array['deliveryweek'] . '-' . $array['deliveryyear'];
						$deliveries[$key]['count'] = $array['count'];
						$deliveries[$key]['totalquantity'] = $array['totalquantity'];
						$deliveries[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=deliveries&week=' . $array['deliveryweek'] . '&year=' . $array['deliveryyear'];
						if (!empty($company)) {
							$deliveries[$key]['url'] .= '&company=' . $company;
						}

						if (!empty($country)) {
							$deliveries[$key]['url'] .= '&country=' . $country;
						}
					}
				}
				return $deliveries;
			} else if ($do == 'topcustomers') {
				$this->sheel->show['topcustomers'] = false;
				$topcustomers = array();
				$sql = $this->sheel->db->query("
					SELECT a.analysisidentifier, c.customername, c.logo, COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis a
					LEFT JOIN " . DB_PREFIX . "customers c ON a.analysisidentifier = c.customer_ref
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
					GROUP BY a.analysisidentifier
					ORDER BY count DESC
					LIMIT 7
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topcustomers'] = true;

					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$topcustomersx[] = $res;
					}
					foreach ($topcustomersx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topcustomers[$key]['icon'] = $this->sheel->admincp_customers->print_customer_logo($array['logo']);
						$topcustomers[$key]['title'] = $array['customername'] . ' (' . $array['analysisidentifier'] . ')';
						$topcustomers[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=topcustomers&code=' . $array['analysisidentifier'] . '&period=' . $period;
						if (!empty($company)) {
							$topcustomers[$key]['url'] .= '&company=' . $company;
						}

						if (!empty($country)) {
							$topcustomers[$key]['url'] .= '&country=' . $country;
						}
						$topcustomers[$key]['percent'] = $percent;
						$topcustomers[$key]['count'] = $array['count'];
					}
				}
				return $topcustomers;
			} else if ($do == 'topentities') {
				$this->sheel->show['topentities'] = false;
				$topentities = array();
				$sql = $this->sheel->db->query("
					SELECT a.entityid, c.name, COUNT(DISTINCT analysisid) AS count
					FROM " . DB_PREFIX . "analysis a
					LEFT JOIN " . DB_PREFIX . "companies c ON a.entityid = c.company_id
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
					GROUP BY a.entityid
					ORDER BY count DESC
					LIMIT 7
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['topentities'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$topentitiesx[] = $res;
					}
					foreach ($topentitiesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$topentities[$key]['icon'] = $this->sheel->common->fetch_company_logo();
						$topentities[$key]['code'] = $array['entityid'];
						$topentities[$key]['name'] = $array['name'];
						$topentities[$key]['title'] = $array['name'];
						$topentities[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=topentities&code=' . $array['entityid'] . '&period=' . $period;
						if (!empty($company)) {
							$topentities[$key]['url'] .= '&company=' . $company;
						}

						if (!empty($country)) {
							$topentities[$key]['url'] .= '&country=' . $country;
						}
						$topentities[$key]['percent'] = $percent;
						$topentities[$key]['count'] = $array['count'];
					}
				}
				return $topentities;
			} else if ($do == 'assembliescategories') {
				$this->sheel->show['assembliescategories'] = false;
				$assembliescategories = array();
				$sql = $this->sheel->db->query("
					SELECT category as name, SUM(totalquantity) AS count
					FROM " . DB_PREFIX . "analysis_records
					WHERE recordidentifier in (SELECT analysisreference FROM " . DB_PREFIX . "analysis where " . $this->period_to_sql('`createdtime`', $period, '', true) . $this->field_to_sql($company, $country) . ")
					AND lastcheckpoint NOT IN (SELECT checkpointid FROM " . DB_PREFIX . "checkpoints WHERE type = 'Assembly' and triggeredon='0-Out')
					GROUP BY category
					ORDER BY count DESC
					LIMIT 15
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['assembliescategories'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$assembliescategoriesx[] = $res;
					}
					foreach ($assembliescategoriesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sumassemblies) * 100);
						$assembliescategories[$key]['icon'] = $this->sheel->common->fetch_company_logo();
						$assembliescategories[$key]['title'] = $array['name'];
						$assembliescategories[$key]['percent'] = $percent;
						$assembliescategories[$key]['count'] = $array['count'];
					}
				}
				return $assembliescategories;
			} else if ($do == 'assembliesevents') {
				$this->sheel->show['assembliesevents'] = false;
				$assembliescategories = array();
				$sql = $this->sheel->db->query("
					SELECT ar.lastcheckpoint, SUM(ar.totalquantity) AS count, c.message as name
					FROM " . DB_PREFIX . "analysis_records ar
					LEFT JOIN " . DB_PREFIX . "checkpoints c ON ar.lastcheckpoint = c.checkpointid
					LEFT JOIN " . DB_PREFIX . "checkpoints_sequence cs ON c.checkpointid = cs.checkpointid and ar.companyid = cs.fromid
					WHERE ar.recordidentifier in (SELECT analysisreference FROM " . DB_PREFIX . "analysis where " . $this->period_to_sql('`createdtime`', $period, '', true) . $this->field_to_sql($company, $country) . ")
					AND ar.lastcheckpoint NOT IN (SELECT checkpointid FROM " . DB_PREFIX . "checkpoints WHERE type = 'Assembly' and triggeredon='0-Out')
					GROUP BY ar.lastcheckpoint
					ORDER BY cs.sequence ASC
					LIMIT 15
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['assembliesevents'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$assemblieseventsx[] = $res;
					}
					foreach ($assemblieseventsx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sumassemblies) * 100);
						$assembliesevents[$key]['icon'] = $this->sheel->common->fetch_company_logo();
						$assembliesevents[$key]['title'] = $array['name'];
						$assembliesevents[$key]['percent'] = $percent;
						$assembliesevents[$key]['count'] = $array['count'];
					}
				}
				return $assembliesevents;
			} else if ($do == 'ordersizes') {
				$this->sheel->show['ordersizes'] = false;
				$ordersizes = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `issmall` = '1' THEN 'small'
					WHEN `ismedium` = '1' THEN 'medium'
					WHEN `islarge` = '1' THEN 'large'
					END AS sizes,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
					GROUP BY sizes
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['ordersizes'] = true;
					$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$sum += $res['count'];
						$res['percent'] = '0.0';
						$ordersizesx[] = $res;
					}
					// $sum = 30
					foreach ($ordersizesx as $key => $array) {
						$percent = sprintf("%01.1f", ($array['count'] / $sum) * 100);
						$ordersizes[$key]['title'] = '{_' . $array['sizes'] . '}';
						$ordersizes[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=' . $array['sizes'] . '&period=' . $period;
						if (!empty($company)) {
							$ordersizes[$key]['url'] .= '&company=' . $company;
						}

						if (!empty($country)) {
							$ordersizes[$key]['url'] .= '&country=' . $country;
						}
						$ordersizes[$key]['percent'] = $percent;
						$ordersizes[$key]['count'] = $array['count'];
					}
				}
				return $ordersizes;
			} else if ($do == 'analysis') {
				$this->sheel->show['analysis'] = false;
				$analysis = array();
				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `isactive` = '0' THEN 'inactive'
					WHEN `isactive` = '1' THEN 'active'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) .
					$this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				}

				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `hasquote` = '0' THEN 'noquote'
					ELSE 'unknown'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) . " and hasquote='0'
					" . $this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					//$sum = 0;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				} else {
					$this->sheel->show['analysis'] = true;
					$analysisx[] = array('analysis' => 'noquote', 'count' => 0);
				}

				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `isfinished` = '1' AND `isarchived` = '0' THEN 'invoicednotarchived'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) . " AND isfinished = '1' AND isarchived = '0'"
					. $this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				} else {
					$this->sheel->show['analysis'] = true;
					$analysisx[] = array('analysis' => 'invoicednotarchived', 'count' => 0);
				}

				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `isfinished` = '0' AND `isarchived` = '1' THEN 'deleted'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) . " AND isfinished = '0' AND isarchived = '1'"
					. $this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				} else {
					$this->sheel->show['analysis'] = true;
					$analysisx[] = array('analysis' => 'deleted', 'count' => 0);
				}


				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `isfinished` = '1' AND `isarchived` = '1' THEN 'closed'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) . " AND isfinished = '1' AND isarchived = '1'"
					. $this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				} else {
					$this->sheel->show['analysis'] = true;
					$analysisx[] = array('analysis' => 'closed', 'count' => 0);
				}

				$sql = $this->sheel->db->query("
					SELECT
					CASE
					WHEN `isfinished` = '1' AND `isarchived` = '1' AND isontime = '1' THEN 'ontime'
					WHEN `isfinished` = '1' AND `isarchived` = '1' AND isontime = '0' THEN 'notontime'
					END AS analysis,
					COUNT(DISTINCT analysisreference) AS count
					FROM " . DB_PREFIX . "analysis
					WHERE " . $this->period_to_sql('`createdtime`', $period, '', true) . " AND isfinished = '1' AND isarchived = '1'"
					. $this->field_to_sql($company, $country) . "
					GROUP BY analysis
					ORDER BY count DESC
				");
				if ($this->sheel->db->num_rows($sql) > 0) {
					$this->sheel->show['analysis'] = true;
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$res['percent'] = '0.0';
						$analysisx[] = $res;
					}
				}

				foreach ($analysisx as $key => $value) {
					if ($value['count'] > 0) {
						$percent = sprintf("%01.1f", ($value['count'] / $sum) * 100);
					} else {
						$percent = sprintf("%01.1f", 0);
					}
					$analysis[$key]['title'] = '{_' . $value['analysis'] . '}';
					$analysis[$key]['url'] = HTTPS_SERVER_ADMIN . 'customers/orders/-1/?analysis=' . $value['analysis'] . '&period=' . $period;
					if (!empty($company)) {
						$analysis[$key]['url'] .= '&company=' . $company;
					}

					if (!empty($country)) {
						$analysis[$key]['url'] .= '&country=' . $country;
					}
					$analysis[$key]['percent'] = $percent;
					$analysis[$key]['count'] = $value['count'];
				}



				return $analysis;
			}
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
		switch ($period) {
			case 'yesterday':
			case 'today': {
				$return = "'12am','4am','8am','12pm','4pm','8pm','11pm'";
				break;
			}
			case 'last7days': {
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++) {
					$array[] = "'" . vdate('M. j', $timestamp) . "',";
					$timestamp -= 24 * 3600;
				}
				$array = array_reverse($array);
				foreach ($array as $value) {
					$return .= "$value";
				}
				break;
			}
			case 'last30days': {
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++) {
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 5;
				}
				$array = array_reverse($array);
				foreach ($array as $value) {
					$return .= "$value";
				}
				break;
			}
			case 'last60days': {
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++) {
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 10;
				}
				$array = array_reverse($array);
				foreach ($array as $value) {
					$return .= "$value";
				}
				break;
			}
			case 'last90days': {
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++) {
					$array[] = "'" . vdate('M. j', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 15;
				}
				$array = array_reverse($array);
				foreach ($array as $value) {
					$return .= "$value";
				}
				break;
			}
			case 'last365days': {
				$array = array();
				$timestamp = TIMESTAMPNOW;
				$return = '';
				$x = 0;
				for ($i = 0; $i < 7; $i++) {
					$array[] = "'" . vdate('M.', $timestamp) . "', ";
					$timestamp -= 24 * 3600 * 60;
				}
				$array = array_reverse($array);
				foreach ($array as $value) {
					$return .= "$value";
				}
				break;
			}
		}
		return $return;
	}
	private function period_to_series($what = '', $period = '', $company = '', $country = '')
	{
		$return = '';
		if ($what == 'visitors') {
			switch ($period) {
				case 'today':
				case 'yesterday': {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['hour']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last7days': {
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++) {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days': {
					$dayin = '';
					$case = '';
					$otimestamp = $timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last60days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last90days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
				case 'last365days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
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
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('uniquevisitors' => $res['uniquevisitors'], 'visitors' => $res['visitors']);
					}
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			foreach ($series as $hour => $array) {
				$seriesa .= "'" . $array['visitors'] . "',";
				$seriesb .= "'" . $array['uniquevisitors'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesa .= '],';
			$seriesb .= ']';
			$return = $seriesa . $seriesb;
		} else if ($what == 'orders') {
			$addition='';
			if (isset($company) and !empty($company)) {
                $addition .= " AND companyid = '" . $company . "'";
            }
            if (isset($country) and !empty($country)) {
                $addition .= " AND countrycode = '" . $country . "'";
            }
			switch ($period) {
				case 'today':
				case 'yesterday': {

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
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 0  AND 3  AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '00:00:00' AND '03:59:59' THEN '0'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 4  AND 7  AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '04:00:00' AND '07:59:59' THEN '4'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 8  AND 11 AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '08:00:00' AND '11:59:59' THEN '8'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 12 AND 15 AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '12:00:00' AND '15:59:59' THEN '12'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 16 AND 19 AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '16:00:00' AND '19:59:59' THEN '16'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) BETWEEN 20 AND 22 AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '20:00:00' AND '22:59:59' THEN '20'
							WHEN HOUR(FROM_UNIXTIME(createdtime)) = 23 AND TIME(FROM_UNIXTIME(createdtime)) BETWEEN '23:00:00' AND '23:59:59' THEN '23'
						END AS hour, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '', true) . "
						$addition
						GROUP BY hour
						HAVING hour IS NOT NULL
						ORDER BY HOUR(FROM_UNIXTIME(createdtime)) ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['hour']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}
					break;
				}
				case 'last7days': {
					$series = array();
					$timestamp = TIMESTAMPNOW;
					for ($i = 0; $i < 7; $i++) {
						$series[date('j', $timestamp)] = array('small' => 0, 'medium' => 0, 'large' => 0);
						$timestamp -= 24 * 3600;
					}
					$sql = $this->sheel->db->query("
						SELECT DAY(FROM_UNIXTIME(createdtime)) AS day, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '', true) . "
						$addition
						GROUP BY day
						ORDER BY createdtime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}
					$series = array_reverse($series);
					break;
				}
				case 'last30days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN FROM_UNIXTIME(createdtime) BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 5)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('small' => 0, 'medium' => 0, 'large' => 0);
						$timestamp -= 24 * 3600 * 5;
					}
					$dayin = substr($dayin, 0, -1);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '35', true) . "
						$addition
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdtime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}
					$series = array_reverse($series);
					break;
				}
				case 'last60days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$case .= "WHEN FROM_UNIXTIME(createdtime) BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 10)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$series[date('Y-m-d', $timestamp)] = array('small' => 0, 'medium' => 0, 'large' => 0);
						$timestamp -= 24 * 3600 * 10;
					}
					$dayin = substr($dayin, 0, -1);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '65', true) . "
						$addition
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdtime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}

					$series = array_reverse($series);
					break;
				}
				case 'last90days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('small' => 0, 'medium' => 0, 'large' => 0);
						$case .= "WHEN FROM_UNIXTIME(createdtime) BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 15)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 15;
					}
					$dayin = substr($dayin, 0, -1);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '95', true) . "
						$addition
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdtime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}
					$series = array_reverse($series);
					break;
				}
				case 'last365days': {
					$dayin = '';
					$case = '';
					$timestamp = TIMESTAMPNOW;
					$x = 0;
					for ($i = 0; $i < 7; $i++) {
						$dayin .= "'" . date('Y-m-d', $timestamp) . "',";
						$series[date('Y-m-d', $timestamp)] = array('small' => 0, 'medium' => 0, 'large' => 0);
						$case .= "WHEN FROM_UNIXTIME(createdtime) BETWEEN '" . date('Y-m-d', ($timestamp - 24 * 3600 * 60)) . " 00:00:00' AND '" . date('Y-m-d', $timestamp) . " 23:59:59' THEN '" . date('Y-m-d', $timestamp) . "' ";
						$timestamp -= 24 * 3600 * 60;
					}
					$dayin = substr($dayin, 0, -1);
					$sql = $this->sheel->db->query("
						SELECT
						CASE
						$case
						END AS day, SUM(issmall) AS smallorders, SUM(ismedium) AS mediumorders, SUM(islarge) AS largeorders
						FROM " . DB_PREFIX . "analysis
						WHERE " . $this->period_to_sql('createdtime', $period, '375', true) . "
						$addition
						GROUP BY day
						HAVING day IS NOT NULL
						ORDER BY createdtime ASC
					");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$series[$res['day']] = array('small' => $res['smallorders'], 'medium' => $res['mediumorders'], 'large' => $res['largeorders']);
					}
					$series = array_reverse($series);
					break;
				}
			}
			$seriesa = '[';
			$seriesb = '[';
			$seriesc = '[';
			foreach ($series as $day => $array) {
				$seriesa .= "'" . $array['small'] . "',";
				$seriesb .= "'" . $array['medium'] . "',";
				$seriesc .= "'" . $array['large'] . "',";
			}
			$seriesa = substr($seriesa, 0, -1);
			$seriesb = substr($seriesb, 0, -1);
			$seriesc = substr($seriesc, 0, -1);
			$seriesa .= '],';
			$seriesb .= '],';
			$seriesc .= ']';
			$return = $seriesa . $seriesb . $seriesc;

		}
		return $return;
	}
	public function period_to_sql($column = '', $period = '', $overide = '', $istime = false)
	{
		switch ($period) {
			case 'today': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') = CURDATE()";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") = CURDATE()";
				}
			}
			case 'yesterday': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') = (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '1') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") = (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '1') . " DAY)";
				}
			}
			case 'last7days': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '7') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '7') . " DAY)";
				}
			}
			case 'last30days': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '30') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '30') . " DAY)";
				}
			}
			case 'last60days': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '60') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '60') . " DAY)";
				}
			}
			case 'last90days': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '90') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '90') . " DAY)";
				}
			}
			case 'last365days': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '365') . " DAY)";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") > (CURDATE() - INTERVAL " . ((!empty($overide)) ? $overide : '365') . " DAY)";
				}
			}
			case 'alltime': {
				if ($istime) {
					return "DATE_FORMAT(FROM_UNIXTIME(" . $this->sheel->db->escape_string($column) . "), '%Y-%m-%e') != ''";
				} else {
					return "DATE(" . $this->sheel->db->escape_string($column) . ") != ''";
				}
			}
		}
	}
	public function field_to_sql($company = '', $country = '')
	{
		if (!empty($company) && !empty($country)) {
			return " AND (companyid = '" . $this->sheel->db->escape_string($company) . "' AND countrycode = '" . $this->sheel->db->escape_string($country) . "')";
		} else if (!empty($company)) {
			return " AND companyid = '" . $this->sheel->db->escape_string($company) . "'";
		} else if (!empty($country)) {
			return " AND countrycode = '" . $this->sheel->db->escape_string($country) . "'";
		} else {
			return '';
		}
	}
}
?>