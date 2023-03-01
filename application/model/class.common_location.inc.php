<?php
/**
* common_location.
*
* @package      sheel\Common\Location
* @version      1.0.0.0
* @author       sheel
*/
class common_location extends common
{
	/**
	* Function to print the user location bit based on a particular user id.
	*
	* @param       integer        user id
	* @param       integer        user short language identifier (i.e.: eng)
	* @param       string         supplied country name
	* @param       string         supplied state name
	* @param       string         supplied city name
	* @param       string         supplied zip code name
	*
	* @return      string         Returns HTML representation of the user location bit
	*/
	function print_user_location($uid = 0, $slng = 'eng', $country = '', $state = '', $city = '', $zip = '')
	{
		if (empty($slng))
		{
			$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		}
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.address, u.address2, u.city, u.state, u.zip_code, l.location_" . $slng . " AS country
			FROM " . DB_PREFIX . "users u
			LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = u.country)
			WHERE u.user_id = '" . intval($uid) . "'
				AND u.status = 'active'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$country = empty($country) ? $res['country'] : $country;
			$state = empty($state) ? $res['state'] : $state;
			$city = empty($city) ? $res['city'] : $city;
			$zip = empty($zip) ? $res['zip_code'] : $zip;
			$zip = (mb_strtolower($zip) == '{_unknown}') ? mb_strtolower($zip) : $zip;
			$address = $res['address'];
			$address2 = $res['address2'];
			$search = array('[address]', '[address2]', '[country]', '[state]', '[city]', '[zip]');
			$replace = array($address, $address2, $country, $state, $city, $zip);
			$output = str_replace($search, $replace, $this->sheel->config['globalfilters_locationformat']);
			$trim_output = trim($this->sheel->config['globalfilters_locationformat']);
			$collecting_special_charcter = substr($trim_output, (stripos($trim_output, ']') + 1), 1);
			if (!empty($collecting_special_charcter))
			{
				$out = explode($collecting_special_charcter, $output);
				$out1 = array();
				foreach ($out AS $key => $value)
				{
					if (!empty($value))
					{
						$out1[] = trim($value);
					}
				}
				return implode($collecting_special_charcter . ' ', $out1);
			}
			else
			{
				return $output;
			}
		}
		return '';
	}

	/**
	* Function to print cities in a column table format
	*
	* @param       string         state/provice name
	* @param       integer        number of columns to display (default 4)
	*
	* @return      string         Returns HTML representation of the listing location bit
	*/
	function print_cities($country = '', $state = '', $columns = 4)
	{
		$html = '';
		$count = 0;
		$locationid = $this->fetch_country_id($country, $_SESSION['sheeldata']['user']['slng']);
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "c.city
			FROM " . DB_PREFIX . "locations_cities c
			WHERE c.state = '" . $this->sheel->db->escape_string($state) . "'
				AND c.locationid = '" . intval($locationid) . "'
			GROUP BY c.city
			ORDER by c.city ASC
		");
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$count++;
				$res['itemcount'] = 0;
				$cities[] = $res;
			}
		}
		$cols = 0;
		$counter = $count;
		$divideby = ceil($count / $columns);
		for ($i = 0; $i < $count; $i++)
		{
			$html .= '<td><div class="blue"><a href="' . HTTP_SERVER . $this->sheel->ilpage['search'] . '?mode=product&amp;sort=01&amp;country=' . o($country) . '&amp;state=' . o($state) . '&amp;city=' . o($cities[$i]['city']) . '&amp;classifieds=1" nofollow="nofollow">' . o($cities[$i]['city']) . '</a>&nbsp;<span class="litegray">(' . $cities[$i]['itemcount'] . ')</span></div></td>';
			if (($counter % $columns) == $divideby)
			{
				$html .= '</tr>';
			}
			$cols++;
			$counter++;
			if ($cols == $columns)
			{
				$html .= '</tr>';
				$cols = 0;
			}
		}
		return $html;
	}

	/**
	* Function to construct a country pull down menu
	*
	* @param       integer      country id
	* @param       string       country title
	* @param       string       country fieldname
	* @param       boolean      disable states pulldown (default false)
	* @param       string       states field name
	* @param       boolean      show worldwide as an option (default false)
	* @param       boolean      show usa/canada at top of list (default false)
	* @param       boolean      output option code as regions instead of countries (default false)
	* @param       string       states pull down container id
	* @param       boolean      only output states ISO codes (default false)
	* @param       string
	* @param       string
	* @param       string
	* @param       string       extra or custom css classes (default .select css class)
	* @param       boolean      show please select country name (default false)
	* @param       string       only show countries from this region (default all)
	* @param       integer      region id (if applicable)
	* @param       boolean      disable cities pulldown (default true)
	* @param       string       cities field name
	* @param       string       cities div id
	*
	* @return      string       HTML formatted country pulldown menu
	*/
	function construct_country_pulldown($countryid = 0, $countryname = '', $fieldname = 'country', $disablestates = false, $statesfieldname = 'state', $showworldwide = false, $usacanadafirst = false, $regionsonly = false, $statesdivid = 'stateid', $onlyiso = false, $statesfieldname2 = '', $fieldname2 = '', $statesdivid2 = '', $extracss = '', $showpleaseselect = false, $groupbyregion = false, $regionid = '', $disablecities = 1, $citiesfieldname = 'city', $citiesdivid = 'cityid')
	{
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disablestates == false)
			? ' onchange="print_states(\'' . $statesfieldname . '\', \'' . $fieldname . '\', \'' . $statesdivid . '\', \'' . intval($onlyiso) . '\', \'' . $extracss . '\', \'' . intval($disablecities) . '\', \'' . $citiesfieldname . '\', \'' . $citiesdivid . '\');' . ((!empty($statesfieldname2) AND !empty($fieldname2) AND !empty($statesdivid2)) ? 'print_states(\'' . $statesfieldname2 . '\', \'' . $fieldname2 . '\', \'' . $statesdivid2 . '\', \'' . intval($onlyiso) . '\', \'' . $extracss . '\', \'' . intval($disablecities) . '\', \'' . $citiesfieldname . '\', \'' . $citiesdivid . '\');' // this changes tax state pull down also when new country is selected
			: '') . '"' : '') . ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '">';
		$extraquery = ($usacanadafirst) ? " AND locationid != '500' AND locationid != '330'" : '';
		$extraquery = ($regionsonly) ? "" : $extraquery;
		$regionquery = "";
		if ($groupbyregion)
		{
			$regionquery = "AND r.region_" . $_SESSION['sheeldata']['user']['slng'] . " = '" . $this->sheel->db->escape_string($regionid) . "'";
		}
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "l.locationid, l.location_" . $_SESSION['sheeldata']['user']['slng'] . " AS location, r.region_" . $_SESSION['sheeldata']['user']['slng'] . " AS region, l.cc
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.visible = '1'
			ORDER BY location_eng
			$extraquery
			$regionquery
		", 0, null, __FILE__, __LINE__);

//		var_dump($this->sheel->db->num_rows($sql)); die();

		if ($this->sheel->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="" readonly="readonly">{_country}</option><!--<option value="">{_any_country}</option>-->';
			}
			if ($regionsonly == false)
			{
				$html .= ($showworldwide) ? '<option value=""></option><option value="{_worldwide}">{_worldwide}</option><option value="{_worldwide}">-------------------------------</option>' : '';
				$html .= ($usacanadafirst) ? '<option value="' . (($onlyiso == false) ? 'Canada' : 'CA') . '">Canada</option><option value="' . (($onlyiso == false) ? 'United States' : 'US') . '">United States</option><option value="" readonly="readonly">-------------------------------</option>' : '';
			}
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				if ($onlyiso == false)
				{
					$html .= ($regionsonly) ? '<option value="' . mb_strtolower(str_replace(' ', '_', $res['region'])) . '.' . $res['locationid'] . '"' : '<option value="' . $res['location'] . '"';
					$html .= (mb_strtolower(str_replace(' ', '_', $res['region']) . '.' . $res['locationid']) == $countryname) ? ' selected="selected"' : '';
					$html .= ($res['locationid'] == $countryid) ? ' selected="selected"' : '';
				}
				else
				{
					$html .= '<option value="' . $res['cc'] . '"';
					$html .= ($res['locationid'] == $countryid) ? ' selected="selected"' : '';
				}
				$html .= '>' . o($res['location']) . '</option>';
			}
		}
		unset($sql);
		$html .= '</select>';

//		var_dump($html); die();

		return $html;
	}

	/**
	* Function to construct a state or province pull down menu
	*
	* @param       integer      country id
	* @param       string       state or province
	* @param       string       fieldname and/or id name
	* @param       boolean      disabled (default false)
	* @param       boolean      show please select as an option (default false)
	* @param       boolean      short form state codes only (default false)
	* @param       string       extra or custom css classes (default .select css class)
	* @param       boolean      disable cities pulldown (default true)
	* @param       string       cities field name
	* @param       string       cities div id
	*
	* @return      string       HTML formatted state pulldown menu
	*/
	function construct_state_pulldown($locationid = '', $statename = '', $fieldname = 'state', $disabled = false, $showpleaseselect = false, $shortformonly = 0, $extracss = '', $disablecities = 1, $citiesfieldname = 'city', $citiesdivid = 'cityid')
	{
		$html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disablecities == 0) ? ' onchange="print_cities(\'' . $citiesfieldname . '\', \'' . $fieldname . '\', \'' . $citiesdivid . '\', \'' . $extracss . '\');"' : '') . ' class="' . ((empty($extracss)) ? 'draw-select' : $extracss) . '"' . ($disabled ? ' readonly="readonly"' : '') . '>';
		$defaultstate = '';
		if (!empty($locationid) AND !empty($statename))
		{
			$sql = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid, state, sc
				FROM " . DB_PREFIX . "locations_states
				WHERE locationid = '" . intval($locationid) . "'
					AND (state = '" . $this->sheel->db->escape_string($statename) . "' OR sc = '" . $this->sheel->db->escape_string($statename) . "')
					AND visible = '1'
				ORDER BY state ASC
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0)
			{
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$defaultstate = (($shortformonly AND !empty($res['sc'])) ? $res['sc'] : $res['state']);
			}
			unset($res);
		}
		else
		{
			if (defined('LOCATION') AND LOCATION == 'admin')
			{
				$defaultstate = (isset($statename) AND !empty($statename)) ? $statename : $this->sheel->config['registrationdisplay_defaultstate'];
			}
			else
			{
				$defaultstate = (isset($statename) AND !empty($statename)) ? $statename : '';
			}
		}
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "state, sc
			FROM " . DB_PREFIX . "locations_states
			WHERE locationid = '" . intval($locationid) . "'
			    AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="">{_state_or_province}</option>';
			}
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<option value="' . (($shortformonly AND !empty($res['sc'])) ? $res['sc'] : $res['state']) . '"';
				$html .= (isset($defaultstate) AND !empty($defaultstate) AND ($res['state'] == $defaultstate OR $res['sc'] == $defaultstate)) ? ' selected="selected"' : '';
				$html .= '>' . stripslashes(o($res['state'])) . '</option>';
			}
		}
		else
		{
			$html .= '<option value="" readonly="readonly">{_state_or_province}</option>';
		}
		$html .= '</select>';
		return $html;
	}
	/**
	* Function to construct a city pulldown menu
	*
	* @param       string       state or province
	* @param       string       city fieldname and/or id name
	* @param       string       currently selected city name (optional)
	* @param       boolean      disabled select menu (default false)
	* @param       boolean      show please select as an option (default false)
	* @param       string       extra css to apply to pull down menu
	* @param       boolean      switch to input field if pull down has no values (default true)
	*
	* @return      string       HTML formatted city pull down menu
	*/
	function construct_city_pulldown($statename = '', $fieldname = 'city', $selected = '', $disabled = false, $showpleaseselect = false, $cssclass = 'draw-select', $switchtoinputfield = true, $fieldwidth = 'w-200', $usewrapper = true, $citiesinputwidth = '')
	{
		if (mb_strlen($statename) == 2)
		{
			$statename = $this->fetch_state_from_abbreviation($statename);
		}
		$html = (($usewrapper) ? '<div class="draw-select__wrapper ' . $fieldwidth . '" id="city-wrapper">' : '');
		$html .= '<select name="' . $fieldname . '" id="' . $fieldname . '" class="' . $cssclass . '"' . ($disabled ? ' readonly="readonly"' : '') . '>';
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "city
			FROM " . DB_PREFIX . "locations_cities
			WHERE state = '" . $this->sheel->db->escape_string($statename) . "'
				AND state != ''
				AND visible = '1'
			ORDER BY city ASC
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="">{_select}</option>';
			}
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<option value="' . o($res['city']) . '"';
				$html .= (($res['city'] == $selected)) ? ' selected="selected"' : '';
				$html .= '>' . o($res['city']) . '</option>';
			}
		}
		else
		{
			if ($switchtoinputfield)
			{
				$html = '<input type="text" name="' . $fieldname . '" value="' . o($selected) . '" id="' . $fieldname . '" class="input-orig requiredfield' . ((stristr($citiesinputwidth, 'draw-')) ? ' ' . (($fieldwidth == 'w-355') ? 'w-350' : $fieldwidth) : ' ' . (($citiesinputwidth == 'w-355') ? 'w-350' : $citiesinputwidth)) . '" placeholder="{_city_or_town}" />';
				return $html;
			}
			else
			{
				$html .= '<option readonly="readonly">{_city_or_village}</option>';
			}
		}
		$html .= '</select>';
		$html .= (($usewrapper) ? '</div>' : '');
		return $html;
	}

	/**
	* Function to print a user's country based on a supplied user id and a short language identifier to display the proper country name in the appropriate language
	*
	* @param       integer        user id
	* @param       string         short language identifier (default eng)
	*
	* @return      string         Returns the user's country name
	*/
	function print_user_country($userid, $slng = 'eng')
	{
		$countryid = $this->sheel->fetch_user('country', intval($userid));
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "location_$slng AS countryname
			FROM " . DB_PREFIX . "locations
			WHERE locationid = '" . $countryid . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return stripslashes(o($res['countryname']));
		}
		return '{_unknown}';
	}

	/**
	* Function to print a country name based on a supplied country id and a short language identifier to display the proper country name in the appropriate language
	*
	* @param       integer        country id
	* @param       string         short language identifier (default eng)
	* @param       boolean        short form output? (default false)
	*
	* @return      string         Returns the user's country name
	*/
	function print_country_name($countryid, $slng = 'eng', $shortform = false, $countryname = '')
	{
		if (empty($slng))
		{
			$slng = 'eng';
		}
		$condition = "locationid = '" . intval($countryid) . "'";
		if (!empty($countryname))
		{
			$condition = "location_$slng = '" . $this->sheel->db->escape_string($countryname) . "'";
		}
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "location_$slng AS countryname, cc
			FROM " . DB_PREFIX . "locations
			WHERE $condition
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($shortform)
			{
				return $res['cc'];
			}
			return $res['countryname'];
		}
		return '{_unknown}';
	}
	/**
	* Function to print a country flag based on a supplied country id
	*
	* @param       integer        country id
	*
	* @return      string         Returns the user's country flag in <img>
	*/
	function print_country_flag($countryid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cc, location_" . $_SESSION['sheeldata']['user']['slng'] . " AS country
			FROM " . DB_PREFIX . "locations
			WHERE locationid = '" . intval($countryid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return '<span title="' . $res['country'] . '"><img src="' . $this->sheel->config['imgcdn'] . 'flags/' . strtolower($res['cc']) . '.png" border="0" alt="" /></span>';
		}
		return '';
	}

	/**
	* Function to fetch a valid country id from the datastore based on a country code
	*
	* @param       string         country code
	*
	* @return      integer        Returns the country id
	*/
	function fetch_country_id_by_code($code = '')
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
			FROM " . DB_PREFIX . "locations
			WHERE cc = '" . $this->sheel->db->escape_string(strtoupper($code)) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['locationid'];
		}
		return '500';
	}

	/**
	* Function to convert a valid state or province into it's short form abbreviation
	*
	* @param       string         state name (ie: Florida)
	*
	* @return      string         Returns the short form abbreviation of a state or province
	*/
	function fetch_state_abbreviation($state = '')
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "sc
			FROM " . DB_PREFIX . "locations_states
			WHERE state = '" . $this->sheel->db->escape_string($state) . "'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['sc'];
		}
		return false;
	}

	/**
	* Function to convert a valid state or province into it's short form abbreviation
	*
	* @param       string         state name (ie: Florida)
	*
	* @return      string         Returns the short form abbreviation of a state or province
	*/
	function fetch_state_from_abbreviation($state = '')
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "state
			FROM " . DB_PREFIX . "locations_states
			WHERE sc = '" . $this->sheel->db->escape_string(mb_strtoupper($state)) . "'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['state'];
		}
		return false;
	}
	function print_local_time($timezone = 'America/Toronto')
	{
		$timestamp = time();
		date_default_timezone_set($timezone);
		return date('g:i a', $timestamp);
	}
	/**
        * Function to fetch a valid country id from the datastore based on an actual country name along with a short language identifier
        *
        * @param       string         country name
        * @param       string         short language identifier
        *
        * @return      integer        Returns the country id
        */
        function fetch_country_id($countryname = '', $slng = 'eng')
        {
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
                        FROM " . DB_PREFIX . "locations
                        WHERE location_" . $this->sheel->db->escape_string($slng) . " = '" . $this->sheel->db->escape_string($countryname) . "' OR cc = '" . $this->sheel->db->escape_string($countryname) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['locationid'];
                }
                return '500';
        }
	function fetch_country_id_from_spid($spid = '', $slng = 'eng')
        {
                $sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
                        FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "shipping_profiles s ON (s.country = l.location_" . $this->sheel->db->escape_string($slng) . ")
                        WHERE s.id = '" . intval($spid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['locationid'];
                }
                return '500';
        }
	function is_valid_country_title($countryname = '', $slng = 'eng')
	{
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
                        FROM " . DB_PREFIX . "locations
                        WHERE location_" . $this->sheel->db->escape_string($slng) . " = '" . $this->sheel->db->escape_string($countryname) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
                        return true;
                }
                return false;
	}
	/*
	* Function to print the regions (continents)
	*
	* @param       string         fieldname
	* @param       string         selected option value (if applicable)
	* @param       string         short form language code (eng, ger, pol, etc)
	* @param       string         element object id (id="")
	* @param       string         display type to print (pulldown or links)
	* @param       boolean        determine if we want to handle onchange on pulldowns to disable distance bit when only a region contains no country id.
	* @param       integer        search form id (<form id="xx">..)
	*
	* @return      string         Returns HTML representation of the pull down menu
	*/
	function print_regions($fieldname = '', $selected = '', $slng = 'eng', $id = '', $displaytype = 'pulldown', $onchange = false, $searchformid = '0')
	{
		$html = '';
		$regioncount = 0;
		$showonlycountryid = $this->fetch_country_id($this->sheel->config['registrationdisplay_defaultcountry'], $_SESSION['sheeldata']['user']['slng']);
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "region_" . $slng . " AS region
			FROM " . DB_PREFIX . "locations_regions
			GROUP BY region_" . $slng . "
			ORDER BY region_" . $slng . " ASC
		", 0, null, __FILE__, __LINE__);
		if ($displaytype == 'pulldown')
		{
			if ($onchange AND $this->sheel->config['globalserver_enabledistanceradius'])
			{
				if ($searchformid == '1')
				{
					$distancediv1 = 'if (iL[\'DISTANCE\'] == 1){toggle_show(\'toggleradiusservice\');}';
					$distancediv2 = 'if (iL[\'DISTANCE\'] == 1){toggle_hide(\'toggleradiusservice\');}';
				}
				else if ($searchformid == '2')
				{
					$distancediv1 = 'if (iL[\'DISTANCE\'] == 1){toggle_show(\'toggleradiusproduct\');}';
					$distancediv2 = 'if (iL[\'DISTANCE\'] == 1){toggle_hide(\'toggleradiusproduct\');}';
				}
				else if ($searchformid == '3')
				{
					$distancediv1 = 'if (iL[\'DISTANCE\'] == 1){toggle_show(\'toggleradiusexperts\');}';
					$distancediv2 = 'if (iL[\'DISTANCE\'] == 1){toggle_hide(\'toggleradiusexperts\');}';
				}
			}
			$onchangejs = ($onchange AND $this->sheel->config['globalserver_enabledistanceradius'])
				? ' onchange="javascript:
				if (iL[\'DISTANCE\'] == 1)
				{
					var idselected = fetch_js_object(\'' . $id . '\').value
					if (idselected.indexOf(\'.\') == \'-1\')
					{
						' . $distancediv2 . '
					}
					else
					{
						' . $distancediv1 . '
					}
				}"'
				: '';

			$html .= '<div class="draw-select__wrapper inlineblock w-250"><select name="' . $fieldname . '" id="' . $id . '"' . $onchangejs . ' class="draw-select">';
			// #### show option to only show country of installed site #####
			//$html .= ($showonlycountryid > 0) ? '<option value="' . $this->sheel->shipping->fetch_region_by_countryid($showonlycountryid) . '.' . $showonlycountryid . '">{_only} ' . o($this->sheel->config['registrationdisplay_defaultcountry']) . '</option>' : '';
			// #### show option to show results worldwide ##################
			$html .= ($this->sheel->config['worldwideshipping'] == '1') ? '<option value="">-</option><option value="worldwide">{_worldwide}</option><option value="" readonly="readonly">-----------------</option>' : '';
			// #### loop through accepted regions of the installed site ####
			while ($crow = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$region = strtolower(str_replace(' ', '_', $crow['region']));
				if (isset($this->sheel->regions["$region"]) AND $this->sheel->regions["$region"])
				{
					$html .= '<option value="' . $region . '">' . o($crow['region']) . '</option>';
				}
			}
			$html .= '</select></div>';
		}
		else if ($displaytype == 'links')
		{
			$html .= '';
			$selected2 = $countryid = '';
			if (!empty($selected) AND strrchr($selected, '.'))
			{
				$regtemp = explode('.', $selected);
				if (!empty($regtemp[0]))
				{
					$selected = $regtemp[0];
				}
				if (!empty($regtemp[1]))
				{
					$selected2 = '.' . $regtemp[1];
					$countryid = $regtemp[1];
				}
				unset($regtemp);
			}
			else if (!empty($selected))
			{
				$regionname = $selected;
			}
			// make sure our php_self string contains a ?
			$php_self = (strrchr($this->sheel->search->php_self, '?') == false) ? $this->sheel->search->php_self . '?sort=' . o($this->sheel->GPC['sort']) : $this->sheel->search->php_self;
			$removeurl = $this->sheel->seo->rewrite_url($php_self, 'region=' . $selected);
			$removeurl = ($countryid > 0) ? $this->sheel->seo->rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
			$removeurl = (isset($this->sheel->GPC['country'])) ? $this->sheel->seo->rewrite_url($removeurl, 'country=' . urlencode($this->sheel->GPC['country'])) : $removeurl;
			$removeurl = (isset($this->sheel->GPC['state'])) ? $this->sheel->seo->rewrite_url($removeurl, 'state=' . urlencode($this->sheel->GPC['state'])) : $removeurl;
			$removeurl = (isset($this->sheel->GPC['city'])) ? $this->sheel->seo->rewrite_url($removeurl, 'city=' . urlencode($this->sheel->GPC['city'])) : $removeurl;
			$removeurl = (isset($this->sheel->GPC['radiuszip'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radiuszip=' . urlencode($this->sheel->GPC['radiuszip'])) : $removeurl;
			$removeurl = (isset($this->sheel->GPC['radius'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radius=' . $this->sheel->GPC['radius']) : $removeurl;
			$removeurl = (strrchr($removeurl, '?') == false) ? $removeurl . '?sort=' . o($this->sheel->GPC['sort']) : $removeurl;
			unset($removeurl);
			$foundmatch = false;
			while ($crow = $this->sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$removeurl = $this->sheel->seo->rewrite_url($php_self, 'region=' . $selected);
				$removeurl = (strrchr($removeurl, '?') == false) ? $removeurl . '?sort=' . o($this->sheel->GPC['sort']) : $removeurl;
				$currentregion = strtolower(str_replace(' ', '_', $crow['region']));
				if ($currentregion == $selected)
				{
					if (isset($this->sheel->regions["$currentregion"]) AND $this->sheel->regions["$currentregion"])
					{
						$foundmatch = true;
						$regioncount++;
						$removeurl = $this->sheel->seo->rewrite_url($php_self, 'region=' . $selected);
						$removeurl = ($countryid > 0) ? $this->sheel->seo->rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['country'])) ? $this->sheel->seo->rewrite_url($removeurl, 'country=' . urlencode($this->sheel->GPC['country'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['state'])) ? $this->sheel->seo->rewrite_url($removeurl, 'state=' . urlencode($this->sheel->GPC['state'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['city'])) ? $this->sheel->seo->rewrite_url($removeurl, 'city=' . urlencode($this->sheel->GPC['city'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['radiuszip'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radiuszip=' . urlencode($this->sheel->GPC['radiuszip'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['radius'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radius=' . $this->sheel->GPC['radius']) : $removeurl;
						$removeurl = (strrchr($removeurl, '?') == false) ? $removeurl . '?sort=' . o($this->sheel->GPC['sort']) : $removeurl;
						$html .= '<div class="search-filter"><a href="' . $removeurl . '" class="selected">' . $crow['region'] . '</a></div>';
						if (!empty($countryid) AND !empty($selected2))
						{
							$removeurl = $this->sheel->seo->rewrite_url($php_self, $selected2);
							$removeurl = ($countryid > 0) ? $this->sheel->seo->rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
							$removeurl = (isset($this->sheel->GPC['country'])) ? $this->sheel->seo->rewrite_url($removeurl, 'country=' . urlencode($this->sheel->GPC['country'])) : $removeurl;
							$removeurl = (isset($this->sheel->GPC['state'])) ? $this->sheel->seo->rewrite_url($removeurl, 'state=' . urlencode($this->sheel->GPC['state'])) : $removeurl;
							$removeurl = (isset($this->sheel->GPC['city'])) ? $this->sheel->seo->rewrite_url($removeurl, 'city=' . urlencode($this->sheel->GPC['city'])) : $removeurl;
							$removeurl = (isset($this->sheel->GPC['radiuszip'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radiuszip=' . urlencode($this->sheel->GPC['radiuszip'])) : $removeurl;
							$removeurl = (isset($this->sheel->GPC['radius'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radius=' . $this->sheel->GPC['radius']) : $removeurl;
							$removeurl = (strrchr($removeurl, '?') == false) ? $removeurl . '?sort=' . o($this->sheel->GPC['sort']) : $removeurl;
						}
						break;
					}
				}
				else
				{
					if (isset($this->sheel->regions["$currentregion"]) AND $this->sheel->regions["$currentregion"] AND empty($selected))
					{
						$regioncount++;
						$removeurl = $this->sheel->seo->rewrite_url($php_self, 'region=' . $selected);
						$removeurl = ($countryid > 0) ? $this->sheel->seo->rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['country'])) ? $this->sheel->seo->rewrite_url($removeurl, 'country=' . urlencode($this->sheel->GPC['country'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['state'])) ? $this->sheel->seo->rewrite_url($removeurl, 'state=' . urlencode($this->sheel->GPC['state'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['city'])) ? $this->sheel->seo->rewrite_url($removeurl, 'city=' . urlencode($this->sheel->GPC['city'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['radiuszip'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radiuszip=' . urlencode($this->sheel->GPC['radiuszip'])) : $removeurl;
						$removeurl = (isset($this->sheel->GPC['radius'])) ? $this->sheel->seo->rewrite_url($removeurl, 'radius=' . $this->sheel->GPC['radius']) : $removeurl;
						$removeurl = (strrchr($removeurl, '?') == false) ? $removeurl . '?sort=' . o($this->sheel->GPC['sort']) : $removeurl;
						$html .= '<div class="search-filter"><a href="' . $removeurl . '&amp;region=' . strtolower(str_replace(' ', '_', $crow['region'])). '">' . $crow['region'] . '</a></div>';
					}
				}
				unset($removeurl);
			}
		}
		if ($regioncount == 1 AND !$foundmatch)
		{
			$html = '';
			$this->sheel->config['search_location_tab'] = 0;
		}
		return $html;
	}
	function filter_component($components, $type)
	{
		return array_filter($components, function($component) use ($type) {
			return array_filter($component["types"], function($data) use ($type) {
				return $data == $type;
			});
		});
	}
	/*
	* Function to fetch a valid city, state and country based on a supplied zip code and short form country code.
	*
	* @param       string         zipcode
	* @param       string         country code (2 letter ISO)
	*
	* @return      string         Returns array with city, state and country
	*/
	function fetch_city_state_country($zipcode = '', $cc = '')
	{
		$this->sheel->show['error_zip_code_api'] = false;
		if (isset($this->sheel->config['googlemapsgeocodingkey']) AND !empty($this->sheel->config['googlemapsgeocodingkey']) AND !empty($zipcode) AND !empty($cc))
		{
			$request = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode(trim(o($zipcode))) . '&components=country:' . trim(o($cc)) . '&key=' . $this->sheel->config['googlemapsgeocodingkey'];
			$data = $this->sheel->api_request_submit(md5($request));
			if ($data == false)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $request);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = json_decode(curl_exec($ch), true);
				curl_close($ch);
				$this->sheel->api_request('Google Geocoding API', $request, json_encode($data));
			}
			if (is_array($data) AND isset($data['status']) AND strtoupper($data['status']) == 'OK' AND isset($data['results'][0]['address_components']))
			{
				$components = $data['results'][0]['address_components'];
				$response['city'] = ((isset(array_values($this->filter_component($components, 'locality'))[0]['long_name'])) ? array_values($this->filter_component($components, 'locality'))[0]['long_name'] : '');
				$response['state'] = ((isset(array_values($this->filter_component($components, 'administrative_area_level_1'))[0]['long_name'])) ? array_values($this->filter_component($components, 'administrative_area_level_1'))[0]['long_name'] : '');
				$response['country'] = ((isset(array_values($this->filter_component($components, 'country'))[0]['long_name'])) ? array_values($this->filter_component($components, 'country'))[0]['long_name'] : '');
				$response['countryshort'] = ((isset(array_values($this->filter_component($components, 'country'))[0]['short_name'])) ? array_values($this->filter_component($components, 'country'))[0]['short_name'] : $cc);
				$response['zipcode'] = ((isset(array_values($this->filter_component($components, 'postal_code'))[0]['long_name'])) ? array_values($this->filter_component($components, 'postal_code'))[0]['long_name'] : $zipcode);
				$response['formatted'] = ((isset($data['results'][0]['formatted_address'])) ? $data['results'][0]['formatted_address'] : ''); // North York, ON M3N 2J9, Canada
				if ($this->sheel->distance->has_zipcode_data($this->fetch_country_id_by_code($cc)) AND (empty($response['state']) OR empty($response['city'])))
				{
					if (empty($response['state']))
					{
						$countryid = $this->fetch_country_id_by_code($cc);
						$response['state'] = $this->sheel->distance->fetch_state_from_zipcode($countryid, $zipcode);
					}
					if (empty($response['city']) AND !empty($response['state']) AND isset($countryid) AND $countryid > 0)
					{
						$response['city'] = $this->sheel->distance->fetch_city_from_zipcode($countryid, $response['state'], $zipcode);
					}
					if (empty($response['state']) OR empty($response['city']))
					{
						$this->sheel->show['error_zip_code_api'] = true;
					}
				}
			}
			else if (is_array($data) AND isset($data['status']) AND strtoupper($data['status']) == 'ZERO_RESULTS' AND $this->sheel->distance->has_zipcode_data($this->fetch_country_id_by_code($cc)))
			{ // from distance dbs (if applicable)
				$countryid = $this->fetch_country_id_by_code($cc);
				$response['country'] = $this->print_country_name($countryid, $_SESSION['sheeldata']['user']['slng'], false, '');
				$response['countryshort'] = $cc;
				$response['state'] = $this->sheel->distance->fetch_state_from_zipcode($countryid, $zipcode);
				$response['city'] = $this->sheel->distance->fetch_city_from_zipcode($countryid, $response['state'], $zipcode);
				$response['zipcode'] = $zipcode;
				$response['formatted'] = "$response[city], $response[state] $response[zipcode], $response[country]";
				if (empty($response['state']) OR empty($response['city']))
				{ // bad zip code
					$this->sheel->show['error_zip_code_api'] = true;
				}
			}
			else
			{ // from ip address (geoip)
				$response['city'] = ((!empty($_SERVER['GEOIP_CITY'])) ? $_SERVER['GEOIP_CITY'] : $this->sheel->config['registrationdisplay_defaultcity']);
				$response['state'] = ((!empty($_SERVER['GEOIP_STATE'])) ? $_SERVER['GEOIP_STATE'] : $this->sheel->config['registrationdisplay_defaultstate']);
				$response['country'] = ((!empty($_SERVER['GEOIP_COUNTRY'])) ? $_SERVER['GEOIP_COUNTRY'] : $this->sheel->config['registrationdisplay_defaultcountry']);
				$response['countryshort'] = ((isset($_SERVER['GEOIP_COUNTRYCODE']) AND !empty($_SERVER['GEOIP_COUNTRYCODE'])) ? $_SERVER['GEOIP_COUNTRYCODE'] : $cc);
				$response['zipcode'] = $zipcode;
				$response['formatted'] = "$response[city], $response[state] $response[zipcode], $response[country]";
				if (isset($data['status']) AND strtoupper($data['status']) != 'OK')
				{ // bad zip code
					$this->sheel->show['error_zip_code_api'] = true;
				}
			}
		}
		else
		{
			$response['city'] = ((!empty($_SERVER['GEOIP_CITY'])) ? $_SERVER['GEOIP_CITY'] : $this->sheel->config['registrationdisplay_defaultcity']);
			$response['state'] = ((!empty($_SERVER['GEOIP_STATE'])) ? $_SERVER['GEOIP_STATE'] : $this->sheel->config['registrationdisplay_defaultstate']);
			$response['country'] = ((!empty($_SERVER['GEOIP_COUNTRY'])) ? $_SERVER['GEOIP_COUNTRY'] : $this->sheel->config['registrationdisplay_defaultcountry']);
			$response['countryshort'] = ((isset($_SERVER['GEOIP_COUNTRYCODE']) AND !empty($_SERVER['GEOIP_COUNTRYCODE'])) ? $_SERVER['GEOIP_COUNTRYCODE'] : $cc);
			$response['zipcode'] = $zipcode;
			$response['formatted'] = "$response[city], $response[state] $response[zipcode], $response[country]";
		}
		return $response;
	}

		/**
	* Function to fetch region title/name by a country id
	*
	* This function
	*
	* @param       integer        country id
	* @param       boolean        convert full title (North America) to (north_america) (default true)
	*
	* @return      string         Returns HTML formatted string
	*/
	function fetch_region_by_countryid($countryid = 0, $doformatting = true, $regionidonly = false)
	{
		$html = '';
		$slng = ((isset($_SESSION['sheeldata']['user']['slng'])) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "r.regionid, r.region_$slng AS region
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.locationid = '" . intval($countryid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$html = $res['region'];
			if ($doformatting)
			{
				$html = str_replace(' ', '_', $html);
				$html = strtolower($html);
			}
			if ($regionidonly)
			{
				$html = $res['regionid'];
			}
		}
		else
		{
			if ($regionidonly)
			{
				$html = 0;
			}
		}
		return $html;
	}
}
?>
