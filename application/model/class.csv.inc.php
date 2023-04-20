<?php
/**
* CSV class to perform the majority of importing and exporting functions within sheel.
*
* @package      sheel\CSV
* @version      1.0.0.0
* @author       sheel
*/
class csv
{
	protected $sheel;
	var $fields_bulk = '';
	var $fields_table1 = '';
	var $fields_table2 = '';
	var $fields_table3 = '';
        function __construct($sheel)
        {
		$this->sheel = $sheel;
		$this->fields_bulk = 'project_title, description, startprice, buynow_price, reserve_price, buynow_qty, buynow_qty_lot, project_details, filtered_auctiontype, cid, sample, ' . (($this->sheel->config['globalserverlocale_currencyselector']) ? "currency, " : '') . 'city, state, zipcode, country, attributes, sku, partnumber, modelnumber, storecid, password, keywords, description_videourl, ship_method, ship_options, ship_service, ship_fee, ship_packagetype, ship_pickuptype, ship_handlingtime, ship_handlingfee, ship_dimensions, ship_weight, returnaccepted, returnwithin, returngivenas, returnshippaidby, returnpolicy, variants, feature1, feature2, feature3, feature4, feature5' . (($this->sheel->config['brands']) ? ", bsin" : '') . ', product_id, product_id_type, eventid, project_id, action, requestid, storeid, customerid';
		$this->fields_table1 = 'p.project_id, p.date_starts, p.project_title, p.description, p.startprice, p.buynow_price, p.reserve_price, p.buynow_qty, p.buynow_qty_lot, p.project_details, p.filtered_auctiontype, p.cid, ' . (($this->sheel->config['globalserverlocale_currencyselector']) ? "p.currencyid, " : '') . 'p.city, p.state, p.zipcode, p.country, p.sku, p.partnumber, p.modelnumber, p.storecid, p.password, p.keywords, p.description_videourl, p.returnaccepted, p.returnwithin, p.returngivenas, p.returnshippaidby, p.returnpolicy, p.feature1, p.feature2, p.feature3, p.feature4, p.feature5, p.eventid' . (($this->sheel->config['brands']) ? ", p.bsin" : '') . ', p.product_id, p.product_id_type, p.eventid, p.project_id';
		$this->fields_table2 = 's.ship_method, s.ship_handlingtime, s.ship_handlingfee, s.ship_length, s.ship_width, s.ship_height, s.ship_weightlbs, s.ship_weightoz';
		$tmp = '';
		for ($i = 1; $i <= $this->sheel->config['maxshipservices']; $i++)
		{
			$tmp .= "i.ship_options_$i, i.ship_service_$i, i.ship_fee_$i, i.ship_fee_next_$i, i.freeshipping_$i, i.ship_packagetype_$i, i.ship_pickuptype_$i, ";
		}
		$tmp = substr($tmp, 0, -2);
		$this->fields_table3 = $tmp;
		unset($tmp);
	}
        function csv_to_db($file = '', $userid = 0, $bulk_id = 0, $eventid = 0, $containsheader = false)
        {
		$this->sheel->db->query('
			LOAD DATA LOCAL INFILE "' . $this->sheel->db->escape_string($file) . '"
			INTO TABLE ' . DB_PREFIX . 'bulk_tmp
			CHARACTER SET UTF8
			FIELDS TERMINATED BY "' . $this->sheel->config['globalfilters_bulkuploadcolsep'] . '"
			OPTIONALLY ENCLOSED BY "' . $this->sheel->db->escape_string($this->sheel->config['globalfilters_bulkuploadcolencap']) . '"
			LINES TERMINATED BY "' . LINEBREAK . '"
			' . (($containsheader) ? 'IGNORE 1 LINES' : '') . '
			(' . $this->fields_bulk . ')
			SET user_id = "' . intval($userid) . '",
			bulk_id = "' . intval($bulk_id) . '",
			' . (($eventid > 0) ? 'eventid = "' . intval($eventid) . '",' : '') . '
			dateupload = "' . DATETODAY . '"
		', 0, null, __FILE__, __LINE__);
        }
	function db_to_csv($containsheader = true, $sellerid = 0, $view = '', $status = '', $filter = '', $filter2 = '', $keywords = '', $eventid = 0)
	{
		$csv = $this->fields_bulk . LINEBREAK;
		$extra = '';
		if ($sellerid > 0)
		{
			$extra = "WHERE p.user_id = '" . intval($sellerid) . "'";
			if ($eventid > 0)
			{
				$extra .= " AND p.eventid = '" . intval($eventid) . "'";
			}
		}
		else
		{
			if (!empty($view) OR !empty($status) OR !empty($filter2) OR (!empty($filter) AND !empty($keywords)))
			{
				$extra .= "WHERE p.project_id != '0' ";
			}
			if (!empty($view))
			{
				if ($view == 'auctions')
				{
					$extra .= "AND p.filtered_auctiontype = 'regular' AND p.buynow = '0' ";
				}
				else if ($view == 'fixed')
				{
					$extra .= "AND p.filtered_auctiontype = 'fixed' AND p.buynow = '1' ";
				}
				else if ($view == 'auctionfixed')
				{
					$extra .= "AND p.filtered_auctiontype = 'regular' AND p.buynow = '1' ";
				}
				else if ($view == 'classifieds')
				{
					$extra .= "AND p.filtered_auctiontype = 'classified' ";
				}
				else if ($view == 'moderated')
				{
					$extra .= "AND p.visible = '0' ";
				}
			}
			if (!empty($status))
			{
				if ($status == 'open')
				{
					$extra .= "AND p.status = 'open' ";
				}
				if ($status == 'expired') // Bidder username or full name
				{
					$extra .= "AND (p.status = 'expired' OR p.status = 'closed') ";
				}
				if ($status == 'draft')
				{
					$extra .= "AND p.status = 'draft' ";
				}
				if ($status == 'delisted')
				{
					$extra .= "AND p.status = 'delisted' ";
				}
				if ($status == 'archived')
				{
					$extra .= "AND p.status = 'archived' ";
				}
			}
			if (!empty($filter) AND !empty($keywords))
			{
				switch ($filter)
				{
					case 'itemid':
					{
						$extra .= "AND p.project_id = '" . intval($keywords) . "' ";
						break;
					}
					case 'seller': // Bidder username or full name
					{
						$q1 = $q2 = '';
						if (strrchr($keywords, ' '))
						{
							$tmp = explode(' ', trim($keywords));
							$q1 = trim($tmp[0]);
							$q2 = trim($tmp[1]);
							$extra .= "AND (u.first_name LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR u.last_name LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR u.username LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR (u.first_name LIKE '%" . $this->sheel->db->escape_string($q1) . "%' AND u.last_name LIKE '%" . $this->sheel->db->escape_string($q2) . "%')) ";
						}
						else
						{
							$extra .= "AND (u.first_name LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR u.last_name LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR u.username LIKE '%" . $this->sheel->db->escape_string($keywords) . "%') ";
						}
						break;
					}
					case 'title':
					{
						$extra .= "AND p.project_title LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' ";
						break;
					}
					case 'sku':
					{
						$extra .= "AND (p.sku LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.product_id LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.partnumber LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.modelnumber LIKE '%" . $this->sheel->db->escape_string($keywords) . "%') ";
						break;
					}
					case 'category':
					{
						$extra .= "AND c.title_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' ";
						break;
					}
					case 'location': // city, state or country or zipcode
					{
						$extra .= "AND (l.location_" . $_SESSION['sheeldata']['user']['slng'] . " LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.city LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.state LIKE '%" . $this->sheel->db->escape_string($keywords) . "%' OR p.zipcode LIKE '%" . $this->sheel->db->escape_string($keywords) . "%') ";
						break;
					}
					case 'bidsgt':
					{
						$extra .= "AND p.bids > '" . intval($keywords) . "' ";
						break;
					}
					case 'bidslt':
					{
						$extra .= "AND (p.bids < '" . intval($keywords) . "' AND p.bids > 0) ";
						break;
					}
					case 'pricegt':
					{
						$extra .= "AND ((p.buynow_price > '" . intval($keywords) . "' AND p.buynow_price > 0 AND p.buynow = '1') OR (p.classified_price > '" . intval($keywords) . "' AND p.classified_price > 0)) ";
						break;
					}
					case 'pricelt':
					{
						$extra .= "AND ((p.buynow_price < '" . intval($keywords) . "' AND p.buynow_price > 0 AND p.buynow = '1') OR (p.classified_price < '" . intval($keywords) . "' AND p.classified_price > 0)) ";
						break;
					}
					case 'currentpricegt':
					{
						$extra .= "AND (p.currentprice > '" . intval($keywords) . "' AND p.currentprice > 0) ";
						break;
					}
					case 'currentpricelt':
					{
						$extra .= "AND (p.currentprice < '" . intval($keywords) . "' AND p.currentprice > 0) ";
						break;
					}
					case 'purchasesgt':
					{
						$extra .= "AND p.buynow_purchases > '" . intval($keywords) . "' ";
						break;
					}
					case 'purchaseslt':
					{
						$extra .= "AND p.buynow_purchases < '" . intval($keywords) . "' ";
						break;
					}
					case 'viewsgt':
					{
						$extra .= "AND p.views > '" . intval($keywords) . "' ";
						break;
					}
					case 'viewslt':
					{
						$extra .= "AND (p.views < '" . intval($keywords) . "' AND p.views > 0) ";
						break;
					}
					case 'withpictures':
					{
						$extra .= "AND p.hasimage = '1' ";
						break;
					}
					case 'withoutpictures':
					{
						$extra .= "AND p.hasimage = '0' ";
						break;
					}
					case 'bulkid':
					{
						$extra .= "AND p.bulkid = '" . intval($keywords) . "' ";
						break;
					}
				}
			}
			if (!empty($filter2))
			{
				switch ($filter2)
				{
					case 'withpictures':
					{
						$extra .= "AND p.hasimage = '1' ";
						break;
					}
					case 'withoutpictures':
					{
						$extra .= "AND p.hasimage = '0' ";
						break;
					}
					case 'withwinningbid':
					{
						$extra .= "AND p.haswinner = '1' ";
						break;
					}
					case 'withoutwinningbid':
					{
						$extra .= "AND p.haswinner = '0' ";
						break;
					}
					case 'withpurchase':
					{
						$extra .= "AND p.buynow_purchases > 0 ";
						break;
					}
					case 'withoutpurchase':
					{
						$extra .= "AND p.buynow_purchases <= 0 ";
						break;
					}
					case 'withgtc':
					{
						$extra .= "AND p.gtc = '1' ";
						break;
					}
				}
			}
		}
		$sql = $this->sheel->db->query("
			SELECT
			$this->fields_table1,
			$this->fields_table2,
			$this->fields_table3
			FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "users u ON (p.user_id = u.user_id)
			LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
			LEFT JOIN " . DB_PREFIX . "categories c ON (p.cid = c.cid)
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (s.project_id = p.project_id)
			LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations i ON (i.project_id = p.project_id)
			$extra
			ORDER BY p.id DESC
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			while ($res = $this->sheel->db->fetch_array($sql))
			{
				$ship_options = $ship_service = $ship_fee = $ship_packagetype = $ship_pickuptype = $sample = $attributes = '';
				$csv .= '"' . str_replace('"', '""', $res['project_title']) . '",'; // 1
				$csv .= '"' . str_replace('"', '""', $res['description']) . '",'; // 2
				$csv .= '"' . o($res['startprice']) . '",'; // 3
				$csv .= '"' . o($res['buynow_price']) . '",'; // 4
				$csv .= '"' . o($res['reserve_price']) . '",'; // 5
				$csv .= '"' . o($res['buynow_qty']) . '",'; // 6
				$csv .= '"' . o($res['buynow_qty_lot']) . '",'; // 7
				$csv .= '"' . (($res['project_details'] == 'realtime') ? 'realtime|' . $res['date_starts'] : $res['project_details']) . '",'; // 8
				$csv .= '"' . o($res['filtered_auctiontype']) . '",'; // 9
				$csv .= '"' . o($res['cid']) . '",'; // 10
				$sqlimg = $this->sheel->db->query("
					SELECT filename, filehash
					FROM " . DB_PREFIX . "attachment
					WHERE project_id = '" . $res['project_id'] . "'
					ORDER BY attachid ASC
				");
				if ($this->sheel->db->num_rows($sqlimg) > 0)
				{
					while ($resimg = $this->sheel->db->fetch_array($sqlimg, DB_ASSOC))
					{
						if (!empty($resimg['filehash']) AND !empty($resimg['filename']))
						{
							$sample .= HTTPS_SERVER . 'application/uploads/attachments/auctions/' . $resimg['filehash'] . '/' . $resimg['filename'] . '|';
						}
					}
					if (!empty($sample))
					{
						$sample = substr($sample, 0, -1);
					}
				}
				$csv .= '"' . $sample . '",'; // 11
				$csv .= (($this->sheel->config['globalserverlocale_currencyselector']) ? '"' . $this->sheel->currency->currencies[$res['currencyid']]['currency_abbrev'] . '",' : ''); // 12
				$csv .= '"' . o($res['city']) . '",'; // 13
				$csv .= '"' . o($res['state']) . '",'; // 14
				$csv .= '"' . o($res['zipcode']) . '",'; // 15
				$csv .= '"' . o($res['country']) . '",'; // 16
				$sqlatt = $this->sheel->db->query("
					SELECT questionid, answer
					FROM " . DB_PREFIX . "product_answers
					WHERE project_id = '" . $res['project_id'] . "'
				");
				if ($this->sheel->db->num_rows($sqlatt) > 0)
				{
					while ($resatt = $this->sheel->db->fetch_array($sqlatt, DB_ASSOC))
					{
						$attributes .= $resatt['questionid'] . '=' . $resatt['answer'] . '|';
					}
					if (!empty($attributes))
					{
						$attributes = substr($attributes, 0, -1);
					}
				}
				$csv .= '"' . $attributes . '",'; // 17
				$csv .= '"' . o($res['sku']) . '",'; // 18
				$csv .= '"' . o($res['partnumber']) . '",'; // 19
				$csv .= '"' . o($res['modelnumber']) . '",'; // 20
				$csv .= '"' . o($res['storecid']) . '",'; // 21
				$csv .= '"' . o(str_replace('"', '""', $res['password'])) . '",'; // 22
				$csv .= '"' . o(str_replace('"', '""', $res['keywords'])) . '",'; // 23
				$csv .= '"' . o($res['description_videourl']) . '",'; // 24
				// shipping
				$csv .= '"' . o($res['ship_method']) . '",'; // 25
				for ($i = 1; $i <= $this->sheel->config['maxshipservices']; $i++)
				{
					if (!empty($res['ship_options_' . $i]))
					{
						if ($res['ship_options_' . $i] == 'custom')
						{
							$ship_options .= 'domestic|'; // need to fix when destinations table is normalized
						}
						else
						{
							$ship_options .= $res['ship_options_' . $i] . '|';
						}
						if ($res['ship_service_' . $i] > 0)
						{
							$ship_service .= $res['ship_service_' . $i] . '|';
							$ship_packagetype .= $res['ship_packagetype_' . $i] . '|';
							$ship_pickuptype .= $res['ship_pickuptype_' . $i] . '|';
							if ($res['ship_method'] != 'calculated')
							{
								if ($res['ship_fee_' . $i] > 0 AND $res['freeshipping_' . $i] <= 0)
								{
									if ($res['ship_fee_next_' . $i] > 0)
									{
										$ship_fee .= $res['ship_fee_' . $i] . ',' . $res['ship_fee_next_' . $i] . '|';
									}
									else
									{
										$ship_fee .= $res['ship_fee_' . $i] . '|';
									}
								}
								else if ($res['ship_fee_' . $i] <= 0 OR $res['freeshipping_' . $i] > 0)
								{
									$ship_fee .= 'FREE|';
								}
							}
						}
					}
				}
				if (!empty($ship_options))
				{
					$ship_options = substr($ship_options, 0, -1);
				}
				if (!empty($ship_service))
				{
					$ship_service = substr($ship_service, 0, -1);
				}
				if (!empty($ship_fee))
				{
					$ship_fee = substr($ship_fee, 0, -1);
				}
				if (!empty($ship_packagetype))
				{
					$ship_packagetype = substr($ship_packagetype, 0, -1);
				}
				if (!empty($ship_pickuptype))
				{
					$ship_pickuptype = substr($ship_pickuptype, 0, -1);
				}
				$csv .= '"' . $ship_options . '",'; // 26
				$csv .= '"' . $ship_service . '",'; // 27
				$csv .= '"' . $ship_fee . '",'; // 28
				$csv .= '"' . $ship_packagetype . '",'; // 29
				$csv .= '"' . $ship_pickuptype . '",'; // 30
				$csv .= '"' . o($res['ship_handlingtime']) . '",'; // 31
				$csv .= '"' . o($res['ship_handlingfee']) . '",'; // 32
				$csv .= (($res['ship_length'] <= 0 OR $res['ship_width'] <= 0 OR $res['ship_height'] <= 0) ? '"4x4x4",' : '"' . intval($res['ship_length']) . 'x' . intval($res['ship_width']) . 'x' . intval($res['ship_height']) . '",'); // 33
				$csv .= (($res['ship_weightlbs'] <= 0) ? '"0.5",' : '"' . intval($res['ship_weightlbs']) . '.' . intval($res['ship_weightoz']) . '",'); // 34
				$csv .= '"' . intval($res['returnaccepted']) . '",'; // 35
				$csv .= '"' . o($res['returnwithin']) . '",'; // 36
				$csv .= '"' . o($res['returngivenas']) . '",'; // 37
				$csv .= '"' . o($res['returnshippaidby']) . '",'; // 38
				$csv .= '"' . o(str_replace('"', '""', $res['returnpolicy'])) . '",'; // 39
				// variants
				$variants = '';
				$sqlv = $this->sheel->db->query("
					SELECT sku, price, weight, dimensions, qty, attachid
					FROM " . DB_PREFIX . "variants
					WHERE project_id = '" . $res['project_id'] . "'
					ORDER BY id ASC
				");
				if ($this->sheel->db->num_rows($sqlv) > 0)
				{
					while ($resv = $this->sheel->db->fetch_array($sqlv, DB_ASSOC))
					{
						$options = '';
						$sqlvc = $this->sheel->db->query("
							SELECT question, answer
							FROM " . DB_PREFIX . "variants_choices
							WHERE project_id = '" . $res['project_id'] . "'
							ORDER BY id ASC
						");
						if ($this->sheel->db->num_rows($sqlvc) > 0)
						{
							while ($resvc = $this->sheel->db->fetch_array($sqlvc, DB_ASSOC))
							{
								$options .= $resvc['question'] . '=' . $resvc['answer'] . ',';
							}
							if (!empty($options))
							{
								$options = substr($options, 0, -1);
							}
						}
						$variants .= 'SKU=' . $resv['sku'] . '|' . $options . '|Price=' . $resv['price'] . '|Weight=' . $resv['weight'] . '|Dimension=' . $resv['dimensions'] . '|Quantity=' . $resv['qty'] . '|AttachID=' . $resv['attachid'] . ';';
					}
					if (!empty($variants))
					{
						$variants = substr($variants, 0, -1);
					}
				}
				$csv .= '"' . o(str_replace('"', '""', $variants)) . '",'; // 40
				$csv .= '"' . o($res['feature1']) . '",'; // 41
				$csv .= '"' . o($res['feature2']) . '",'; // 42
				$csv .= '"' . o($res['feature3']) . '",'; // 43
				$csv .= '"' . o($res['feature4']) . '",'; // 44
				$csv .= '"' . o($res['feature5']) . '",'; // 45
				$csv .= (($this->sheel->config['brands']) ? '"' . o($res['bsin']) . '",' : ''); // 46
				$csv .= '"' . o($res['product_id']) . '",'; // 47
				$csv .= '"' . o($res['product_id_type']) . '",'; // 48
				// csv re upload association
				$csv .= '"' . intval($res['eventid']) . '",'; // 49
				$csv .= '"' . intval($res['project_id']) . '",'; // 50
				$csv .= '"update"' . LINEBREAK; // 51 <-- possible methods: update, delete, relist, delist, archive
			}
		}
		return $csv;
	}
	function analyse_csv_file($file, $capture_limit_in_kb = 10)
	{
		// capture starting memory usage
		$output['peak_mem']['start'] = memory_get_peak_usage(true);
		// log the limit how much of the file was sampled (in Kb)
		$output['read_kb'] = $capture_limit_in_kb;
		// read in file
		$fh = fopen($file, 'r');
		$contents = fread($fh, ($capture_limit_in_kb * 1024)); // in KB
		fclose($fh);
		// specify allowed field delimiters
		$delimiters = array(
			'comma' => ',',
			'semicolon' => ';',
			'tab' => "\t",
			'pipe' => '|',
			'colon' => ':'
		);
		// specify allowed line endings
		$line_endings = array(
			'rn' => "\r\n",
			'n' => "\n",
			'r' => "\r",
			'nr' => "\n\r"
		);
		// loop and count each line ending instance
		foreach ($line_endings AS $key => $value)
		{
			$line_result[$key] = substr_count($contents, $value);
		}
		// sort by largest array value
		asort($line_result);
		// log to output array
		$output['line_ending']['results'] = $line_result;
		$output['line_ending']['count'] = end($line_result);
		$output['line_ending']['key'] = key($line_result);
		$output['line_ending']['value'] = $line_endings[$output['line_ending']['key']];
		$lines = explode($output['line_ending']['value'], $contents);
		// remove last line of array, as this maybe incomplete?
		array_pop($lines);
		// create a string from the legal lines
		$complete_lines = implode(' ', $lines);
		// log statistics to output array
		$output['lines']['count'] = count($lines);
		$output['lines']['length'] = strlen($complete_lines);
		// loop and count each delimiter instance
		foreach ($delimiters as $delimiter_key => $delimiter)
		{
			$delimiter_result[$delimiter_key] = substr_count($complete_lines, $delimiter);
		}
		// sort by largest array value
		asort($delimiter_result);
		// log statistics to output array with largest counts as the value
		$output['delimiter']['results'] = $delimiter_result;
		$output['delimiter']['count'] = end($delimiter_result);
		$output['delimiter']['key'] = key($delimiter_result);
		$output['delimiter']['value'] = $delimiters[$output['delimiter']['key']];
		// capture ending memory usage
		$output['peak_mem']['end'] = memory_get_peak_usage(true);
		return $output;
	}
}
?>
