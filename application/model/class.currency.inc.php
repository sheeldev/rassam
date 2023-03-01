<?php
/**
 * Currency class to perform the majority of currency related functions in sheel
 *
 * @package      sheel\Currency
 * @version      6.0.0.622
 * @author       sheel
 */
class currency
{
	protected $sheel;
	public $currencies;
	public $conversion = false;
	/**
	* Constructor
	*
	*/
	function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->init_currencies();
	}
	function init_currencies()
	{
		if (($this->currencies = $this->sheel->cachecore->fetch("currencies")) === false)
		{
			$this->currencies = array();
			$query = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "currency_id, currency_abbrev AS code, symbol_left, symbol_right, symbol_local, decimal_point, thousands_point, decimal_places, decimal_places_local, rate, currency_name, currency_abbrev, currency_subunit, iscrypto
				FROM " . DB_PREFIX . "currency
			", 0, null, __FILE__, __LINE__);
			while ($currencies = $this->sheel->db->fetch_array($query, DB_ASSOC))
			{
				// generate string type values (ie: USD)
				$this->currencies[$currencies['code']] = array(
					'symbol_left' => $currencies['symbol_left'],
					'symbol_right' => $currencies['symbol_right'],
					'symbol_local' => $currencies['symbol_local'],
					'decimal_point' => $currencies['decimal_point'],
					'thousands_point' => $currencies['thousands_point'],
					'decimal_places' => $currencies['decimal_places'],
					'decimal_places_local' => $currencies['decimal_places_local'],
					'rate' => $currencies['rate'],
					'currency_id' => $currencies['currency_id'],
					'currency_name' => $currencies['currency_name'],
					'currency_abbrev' => $currencies['currency_abbrev'],
					'currency_subunit' => $currencies['currency_subunit'],
					'iscrypto' => $currencies['iscrypto']
				);
				// generate integer type values (ie: 1)
				$this->currencies[$currencies['currency_id']] = array(
					'symbol_left' => $currencies['symbol_left'],
					'symbol_right' => $currencies['symbol_right'],
					'symbol_local' => $currencies['symbol_local'],
					'decimal_point' => $currencies['decimal_point'],
					'thousands_point' => $currencies['thousands_point'],
					'decimal_places' => $currencies['decimal_places'],
					'decimal_places_local' => $currencies['decimal_places_local'],
					'rate' => $currencies['rate'],
					'code' => $currencies['code'],
					'currency_name' => $currencies['currency_name'],
					'currency_abbrev' => $currencies['currency_abbrev'],
					'currency_subunit' => $currencies['currency_subunit'],
					'iscrypto' => $currencies['iscrypto']
				);
			}
			//$this->sheel->cachecore->store("currencies", $this->currencies);
		}
		
	}
	/**
	* Function to mimik number_format() to ensure the output is based on the viewing users thousands point, decimal places and decimal point.
	*
	*/
	function number_format($number = 0, $userid = 0)
	{
		if ($userid <= 0)
		{
			$currencyid = $this->fetch_default_currencyid();
			$html = number_format($number, 0, $this->currencies[$currencyid]['decimal_point'], $this->currencies[$currencyid]['thousands_point']);
		}
		else
		{
			$currencyid = $this->sheel->fetch_user('currencyid', $userid);
			$html = number_format($number, 0, $this->currencies[$currencyid]['decimal_point'], $this->currencies[$currencyid]['thousands_point']);
		}
		return $html;
	}
	function numbers_to_k($number)
	{
		if ($number >= 1000)
		{
			return round($number/1000) . "k";
		}
		else if ($number >= 1000000)
		{
			return round($number/1000000) . "M";
		}
		else if ($number >= 1000000)
		{
			return round($number/1000000) . "M";
		}
		else if ($number >= 1000000000)
		{
			return round($number/1000000000) . "B";
		}
		else
		{
			return number_format($number);
		}
	}
	function format_local($number = 0, $currencyid = 0, $regulardecimal = false)
	{
		if ($currencyid <= 0)
		{
			$currencyid = $this->sheel->config['globalserverlocale_defaultcurrency'];
		}
		$iscrypto = ((isset($this->currencies[$currencyid]['iscrypto']) AND $this->currencies[$currencyid]['iscrypto']) ? true : false);
		if ($iscrypto AND $this->number_of_decimal_places($number) <= 2)
		{ // number being passed is regular x.xx so format to cryptocurrency equiv x.xxxxxxxx..
			$number = $this->convert_currency($currencyid, $number, $this->sheel->config['globalserverlocale_defaultcurrency']);  // 15.00 = 0.00194258
		}
		if ($regulardecimal OR fmod($number, 1) !== 0.00)
		{
			return ((empty(!$this->currencies[$currencyid]['symbol_left'])) ? $this->currencies[$currencyid]['symbol_local'] : '') . number_format($number, $this->sheel->currency->currencies[$currencyid]['decimal_places'], $this->currencies[$currencyid]['decimal_point'], $this->currencies[$currencyid]['thousands_point']) . ((empty(!$this->currencies[$currencyid]['symbol_right'])) ? $this->currencies[$currencyid]['symbol_local'] : '');
		}
		return ((!empty($this->currencies[$currencyid]['symbol_left'])) ? $this->currencies[$currencyid]['symbol_local'] : '') . number_format($number, $this->sheel->currency->currencies[$currencyid]['decimal_places_local']) . ((!empty($this->currencies[$currencyid]['symbol_right'])) ? $this->currencies[$currencyid]['symbol_local'] : '');
	}
	function number_of_decimal_places($value = '')
	{
		$num = strlen(preg_replace("/.*\./", "", $value)); // 0.00194258 = 7, 33.00 = 2
		return $num;
	}
	/**
	* Function to properly format a dollar value based on the database currency settings (symbols, decimal places, thousands place, etc)
	*
	*/
	function format($number = 0, $currencyid = 0, $hidesymbols = false, $forcedecimalhide = false, $conversion = true, $numberstok = false)
	{
		$html = '';
		if ($currencyid <= 0)
		{
			$currencyid = $this->sheel->config['globalserverlocale_defaultcurrency'];
		}

		$iscrypto = ((isset($this->currencies[$currencyid]['iscrypto']) AND $this->currencies[$currencyid]['iscrypto']) ? true : false);
		if ($iscrypto AND $this->number_of_decimal_places($number) <= 2)
		{ // number being passed is regular x.xx so format to cryptocurrency equiv x.xxxxxxxx..
			$number = $this->convert_currency($currencyid, $number, $this->sheel->config['globalserverlocale_defaultcurrency']);  // 15.00 = 0.00194258
		}

		$number = $this->string_to_number($number) * 1;
		if (!$hidesymbols)
		{
			$html .= $this->currencies["$currencyid"]['symbol_left'];
			if ($forcedecimalhide)
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number);
				}
			}
			else
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
				}
			}
			$html .= $this->currencies["$currencyid"]['symbol_right'];
			if ($conversion OR $this->conversion)
			{
				if (isset($_SESSION['sheeldata']['user']['currencyid']) AND $_SESSION['sheeldata']['user']['currencyid'] != $currencyid)
				{
					$html = '<span class="currency-hover" title="' . $html . ' ~ ' . $this->currencies[$_SESSION['sheeldata']['user']['currencyid']]['symbol_left'] . number_format($this->convert_currency($_SESSION['sheeldata']['user']['currencyid'], $number, $currencyid), $this->currencies[$_SESSION['sheeldata']['user']['currencyid']]['decimal_places'], $this->currencies[$_SESSION['sheeldata']['user']['currencyid']]['decimal_point'], $this->currencies[$_SESSION['sheeldata']['user']['currencyid']]['thousands_point']) . $this->currencies[$_SESSION['sheeldata']['user']['currencyid']]['symbol_right'] . '">' . $html . '</span>';
				}
			}
		}
		else
		{
			if ($forcedecimalhide)
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number);
				}
			}
			else
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
				}
			}
		}
		return $html;
	}
	/**
	* Function to fetch the default currency id installed for the marketplace
	*
	*/
	function fetch_default_currencyid()
	{
		return (isset($this->sheel->config['globalserverlocale_defaultcurrency']) ? $this->sheel->config['globalserverlocale_defaultcurrency'] : '1');
	}
	/**
	* Function to fetch a user's default currency setup when they registered or edited their profile
	*
	*/
	function fetch_user_currency($userid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "currencyid
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0)
		{
			$cur = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $cur['currencyid'];
		}
		else
		{
			return $this->fetch_default_currencyid();
		}
	}
	/**
	* Function to build a currency selector pulldown menu element
	*
	*/
	function pulldown($inputtype = '', $variableinfo = '', $cssextra = 'draw-select', $formfieldname = 'currencyid', $fieldname = 'currencyid', $selected = '')
	{
		$pulldown = '';
		foreach ($this->currencies AS $key => $val)
		{
			if (is_int($key))
			{
				$values[$key] = $val['currency_abbrev'] . ' - ' . stripslashes($val['currency_name']) . ' (' . (empty($val['symbol_left']) ? $val['symbol_right'] : $val['symbol_left']) . ')';
			}
		}
		if ($inputtype == 'admin')
		{
			$disabled = '';
			$sql_rfp = $this->sheel->db->query("
				SELECT orderid
				FROM " . DB_PREFIX . "buynow_orders
				LIMIT 1
			");
			if ($this->sheel->db->num_rows($sql_rfp) > 0)
			{
				$disabled = 'disabled="disabled"';
				$sql_conf = $this->sheel->db->query("SELECT configgroup FROM " . DB_PREFIX . "configuration WHERE name = '" . $this->sheel->db->escape_string($variableinfo) . "'");
				$res_conf = $this->sheel->db->fetch_array($sql_conf, DB_ASSOC);
				$this->sheel->template->meta['headinclude'] .= '<script>jQuery(\'document\').ready(function(){ jQuery(\'#formid_' . $res_conf['configgroup'] . '\').submit(function() {alert_js(phrase[\'_active_listings_and_or_invoices_have_been_recorded_in_the_database_and_processed_in_your_previous_default_site_currency\']); }); });</script>';
			}
			$pulldown = $this->sheel->construct_pulldown('config_' . $variableinfo, 'config[' . $variableinfo . ']', $values, $this->sheel->config['globalserverlocale_defaultcurrency'], ' class="' . $cssextra . '" ' . $disabled);
		}
		else
		{
			if (!empty($selected))
			{
				$default_user_currency = $selected;
			}
			else
			{
				$default_user_currency = (!empty($_SESSION['sheeldata']['user']['userid'])) ? ($this->sheel->fetch_user('currencyid', intval($_SESSION['sheeldata']['user']['userid']))) : ((isset($this->sheel->GPC['currencyid']) AND $inputtype == 'registration') ? $this->sheel->GPC['currencyid'] : $this->sheel->config['globalserverlocale_defaultcurrency']);
			}
			$pulldown = $this->sheel->construct_pulldown($fieldname, $formfieldname, $values, $default_user_currency, ' class="' . $cssextra . '"');
		}
		return $pulldown;
	}

	/**
	* Function to take a string inputted by a user based on a dollar amount to be converted into 2 decimal places.
	* Example: 1,002.23 = 1002.23 or 12 = 12.00, etc.
	*
	* @param        integer         input price to be evaluated
	*
	* @credit       developer       ratherodd.com
	* @return       integer         return 2 decimal place dollar amount ready for storing into database
	*/
	function string_to_number($price)
	{
		$price = stripslashes(preg_replace('/^\s+|\s+$/', '', $price));
		$decPoint = strrpos($price, '.');
		$decComma = strrpos($price, ',');
		$thous = "' ";
		$first = $second = '';
		if ($decPoint > -1 && $decComma > -1)
		{
			if ($decPoint > $decComma)
			{
				$thous .= ',';
			}
			else
			{
				$thous .= '.';
			}
			$decMark = ',';
		}
		if ((strpos($price, ' ') OR strpos($price, "'")) AND $decComma)
		{
			$decMark = ',';
		}
		if (strlen(substr($price, $decPoint + 1)) === 3 AND $decComma === false AND strpos($price, '.') < $decPoint)
		{
			$thous .= '.';
		}
		if (strlen(substr($price, $decComma + 1)) === 3 AND $decPoint === false AND strpos($price, ',') < $decComma)
		{
			$thous .= ',';
		}
		preg_match('/^(?:(\d{1,3}(?:(?:(?:[' . $thous . ']\d{3})+)?)?|\d+)?([,.]\d{1,})?|\d+)$/', $price, $matches);
		if (!isset($matches))
		{
			//return false;
			return $price;
		}
		if (!isset($matches[1]) AND !isset($matches[2]) AND isset($matches[0]))
		{
			$matches[1] = $matches[0];
		}
		$dec = ((isset($matches[2]) AND $matches[2] AND strlen($matches[2]) === 4 AND !isset($decMark) AND $matches[1] !== '0') ? '' : '.');
		if (isset($matches[1]))
		{
			$first = preg_replace("/[,' .]/", '', $matches[1]);
		}
		if (isset($matches[2]))
		{
			$second = str_replace(',', $dec, $matches[2]);
		}
		return (float) ($first . $second);
	}
	/**
	* Function to print the currency pull down selector
	*
	* @param       integer        currency id
	* @param       integer        use javascript onchange? (default false)
	* @param       boolean        disabled (default false)
	*
	* @return      string         Returns the formatted currency pull down menu
	*/
	function print_currency_pulldown($currencyid = 0, $jsonchange = false, $disabled = false, $class = 'draw-select')
	{
		$attr = array();
		$extra = ' class="' . $class . '"' . (($disabled) ? ' disabled="disabled"' : '');
		if ($jsonchange AND $this->sheel->config['globalserverlocale_currencyselector'])
		{
			$this->fetch_currency_symbols_js();
			$extra .= ' onchange="currency_switcher()" ';
		}
		foreach ($this->currencies AS $key => $val)
		{
			if (is_int($key))
			{
				$symbol = (!empty($val['symbol_left'])) ? $val['symbol_left'] : $val['symbol_right'];
				$values[$key] = o($val['currency_abbrev']) . ' &ndash; ' . o($val['currency_name']);
				$attr[$key] = array(
					'data-cryptocurrency' => $val['iscrypto'],
					'data-dir' => ((!empty($val['symbol_left'])) ? 'left' : 'right'),
					'data-dec' => $val['decimal_places']
				);
			}
		}
		return $this->sheel->construct_pulldown('currencyoptions', 'currencyid', $values, $currencyid, $extra, '', '', $attr);
	}
	function fetch_currency_symbols_js()
	{
		$this->sheel->template->meta['headinclude'] .= '<script>function currency_switcher(){var currencyid = fetch_js_object(\'currencyoptions\').options[fetch_js_object(\'currencyoptions\').selectedIndex].value;var direction = jQuery(\'option:selected\', \'#currencyoptions\').attr(\'data-dir\');if (direction == \'right\'){jQuery(".add-on.before").addClass("hide-ni");jQuery(".add-on.after").removeClass("hide-ni");}else{jQuery(".add-on.before").removeClass("hide-ni");jQuery(".add-on.after").addClass("hide-ni");}if (jQuery(\'#ship_handlingfee_currency\').length){fetch_js_object(\'ship_handlingfee_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'ship_handlingfee_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#spendlimit_currency_left\').length){fetch_js_object(\'spendlimit_currency_left\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'spendlimit_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#spendcap_currency_left\').length){fetch_js_object(\'spendcap_currency_left\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'spendcap_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#shippaycap_currency_left\').length){fetch_js_object(\'shippaycap_currency_left\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'shippaycap_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#additionalfee_shippaycap_currency_left\').length){fetch_js_object(\'additionalfee_shippaycap_currency_left\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'additionalfee_shippaycap_currency_right\').innerHTML = currencysymbols2[currencyid];}';

		$this->sheel->template->meta['headinclude'] .= $this->sheel->config['enableauctiontab'] ? 'if (jQuery(\'#startprice_currency\').length){fetch_js_object(\'startprice_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'startprice_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#buynowprice_currency\').length){fetch_js_object(\'buynowprice_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'buynowprice_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#reserveprice_currency\').length){fetch_js_object(\'reserveprice_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'reserveprice_currency_right\').innerHTML = currencysymbols2[currencyid];}' : '';
		$this->sheel->template->meta['headinclude'] .= $this->sheel->config['enablefixedpricetab'] ? 'if (jQuery(\'#buynowpricefixed_currency\').length){fetch_js_object(\'buynowpricefixed_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'buynowpricefixed_currency_right\').innerHTML = currencysymbols2[currencyid];}if (jQuery(\'#msrpprice_currency\').length){fetch_js_object(\'msrpprice_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'msrpprice_currency_right\').innerHTML = currencysymbols2[currencyid];}' : '';
		$this->sheel->template->meta['headinclude'] .= $this->sheel->config['enableclassifiedtab'] ? 'if (jQuery(\'#classifiedprice_currency\').length){fetch_js_object(\'classifiedprice_currency\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'classifiedprice_currency_right\').innerHTML = currencysymbols2[currencyid];}' : '';

		if ($this->sheel->config['globalserverlocale_currencyselector'])
		{
			for ($i = 1; $i <= $this->sheel->config['maxshipservices']; $i++)
			{
				$this->sheel->template->meta['headinclude'] .= 'if (jQuery(\'#ship_service_' . $i . '_css_costsymbol\').length){fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'ship_service_' . $i . '_css_costsymbol_right\').innerHTML = currencysymbols2[currencyid];}';
				$this->sheel->template->meta['headinclude'] .= 'if (jQuery(\'#next_ship_service_' . $i . '_css_costsymbol\').length){fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol_right\').innerHTML = currencysymbols2[currencyid];}';
				$this->sheel->template->meta['headinclude'] .= 'if (jQuery(\'#ship_service_' . $i . '_css_fallbackcostsymbol\').length){fetch_js_object(\'ship_service_' . $i . '_css_fallbackcostsymbol\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'ship_service_' . $i . '_css_fallbackcostsymbol_right\').innerHTML = currencysymbols2[currencyid];}';
			}
			for ($x = 1; $x <= $this->sheel->config['maxvariants']; $x++)
			{
				$this->sheel->template->meta['headinclude'] .= 'if (jQuery(\'#v_' . $x . '_price_currency_left\').length){fetch_js_object(\'v_' . $x . '_price_currency_left\').innerHTML = currencysymbols[currencyid];fetch_js_object(\'v_' . $x . '_price_currency_right\').innerHTML = currencysymbols2[currencyid];}';
			}
		}
		$this->sheel->template->meta['headinclude'] .= '}var currencysymbols = [];var currencysymbols2 = [];';
		foreach ($this->currencies AS $key => $val)
		{
			if (is_int($key))
			{
				$this->sheel->template->meta['headinclude'] .= 'currencysymbols[' . $key . '] = \'' . $val['symbol_left'] . '\';currencysymbols2[' . $key . '] = \'' . $val['symbol_right'] . '\';';
			}
		}
		$this->sheel->template->meta['headinclude'] .= '</script>';
	}
	/**
	* Function to print the left currency symbol
	*
	* @return      string         Returns left currency symbol (US$, $, etc)
	*/
	function print_left_currency_symbol()
	{
		return $this->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'];
	}
	/**
	* Function to print the currency conversion based on a supplied currency id
	*
	* @param       integer        viewing user's currency id
	* @param       integer        amount to process (x.xx or x.xxxxxxxx)
	* @param       integer        item currency id
	* @param       boolean        flip output
	*
	* @return      string         Returns the formatted amount based on a particular currency id
	*/
	function print_currency_conversion($currencyid = 0, $amount = 0, $currencyid_item = 0, $flipoutput = false)
	{
		$html = '';
		$iscrypto = ((isset($this->currencies[$currencyid_item]['iscrypto']) AND $this->currencies[$currencyid_item]['iscrypto']) ? true : false);
		if ($iscrypto AND $this->number_of_decimal_places($amount) <= 2)
		{ // number being passed is regular xxx.xx so format to cryptocurrency equiv x.xxxxxxxx..
			$amount = $this->convert_currency($currencyid_item, $amount, $this->sheel->config['globalserverlocale_defaultcurrency']);  // 15.00 = 0.00194258
		}

		$default_rate = ($currencyid_item == 0) ? $this->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['rate'] : $this->currencies[$currencyid_item]['rate'];
		$amount = (($iscrypto AND $this->number_of_decimal_places($amount) == 8) ? $amount : $this->string_to_number($amount));

		$customer_rate = $default_rate;
		$customer_rate = (($currencyid > 0) ? $this->currencies[$currencyid]['rate'] : $default_rate);

		$price_conversion_rate = ($amount * $customer_rate / $default_rate);

		$convert_currencyid = ($currencyid == 0) ? $this->sheel->config['globalserverlocale_defaultcurrency'] : $currencyid;
		$convert2_currencyid = ($currencyid_item == 0) ? $this->sheel->config['globalserverlocale_defaultcurrency'] : $currencyid_item;

		$converted1 = $this->format($price_conversion_rate, $convert_currencyid, false, false, false);
		$converted2 = $this->format($amount, $convert2_currencyid, false, false, false);
		if ($flipoutput)
		{
			$html = ($default_rate == $customer_rate) ? $converted2 : '<span title="' . $converted1 . ' ~ ' . $converted2 . '">' . $converted1 . '</span>';
		}
		else
		{
			$html = ($default_rate == $customer_rate) ? $converted1 : '<span title="' . $converted2 . ' ~ ' . $converted1 . '">' . $converted2 . '</span>';
		}
		return $html;
	}
	/**
	* Function to print the currency conversion based on a pruction auction currency id with global currency
	*
	* @param       integer        viewing user's currency id
	* @param       integer        dollar amount to process
	* @param       integer        listing currency id
	*
	* @return      string         Returns the formatted amount based on a particular currency id
	*/
	function print_currency_conversion_invoice($currencyid = 0, $amount = 0, $currencyid_item = 0)
	{
		$html = '';
		$default_rate = ($currencyid_item == 0) ? $this->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['rate'] : $this->currencies[$currencyid_item]['rate'];
		$amount = $this->string_to_number($amount);
		$customer_rate = $default_rate;
		$customer_rate = (($currencyid > 0) ? $this->currencies[$currencyid]['rate'] : $default_rate);
		$price_conversion_rate = ($amount * $customer_rate / $default_rate);
		$price_conversion_rate = sprintf("%01.2f", $price_conversion_rate);
		$convert_currencyid = ($currencyid == 0) ? $this->sheel->config['globalserverlocale_defaultcurrency'] : $currencyid;
		$convert2_currencyid = ($currencyid_item == 0) ? $this->sheel->config['globalserverlocale_defaultcurrency'] : $currencyid_item;
		$converted1 = $this->format($price_conversion_rate, $convert_currencyid, false, false, false);
		$converted2 = $this->format($amount, $convert2_currencyid, false, false, false);
		$html = ($default_rate == $customer_rate OR $amount <= 0) ? $converted1 : $converted1 . '<span style="text-decoration:none">&nbsp;&nbsp;<span class="smaller gray">(<span class="blueonly"><a href="' . HTTPS_SERVER . 'currency-converter/?subcmd=process&amp;amount=' . sprintf("%01.2f", $amount) . '&amp;transfer_from=' . $currencyid_item . '&amp;transfer_to=' . $currencyid . '&amp;returnurl=' . urlencode(PAGEURL) . '">' . $converted2  . '</a></span>)</span>';
		return $html;
	}
	/**
	* Function to convert an amount from one currency to another outputing only the raw amount (no formatting)
	*
	* @param       integer        viewing currency id
	* @param       integer        amount to process
	* @param       integer        convert to currency id
	*
	* @return      string         Returns the converted currency
	*/
	function convert_currency($currencyid = 0, $amount = 0, $currencyid_item = 0)
	{
		$default_rate = ($currencyid_item == 0) ? $this->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['rate'] : $this->currencies[$currencyid_item]['rate'];
		$default_decimalplaces = ($currencyid_item == 0) ? $this->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['decimal_places'] : $this->currencies[$currencyid_item]['decimal_places'];
		$amount = $this->string_to_number($amount);
		$customer_rate = $default_rate;
		$customer_rate = (($currencyid > 0) ? $this->currencies[$currencyid]['rate'] : $default_rate);
		$decimalplaces = (($currencyid > 0) ? $this->currencies[$currencyid]['decimal_places'] : $default_decimalplaces);
		$price_conversion_rate = number_format(($amount * $customer_rate / $default_rate), $decimalplaces, '.', ''); // 1,200.00 becomes 1200.00
		return $price_conversion_rate;
	}
	function fetch_live_rates()
	{
		$cronlog = '';
		$searchfor = '<Cube currency';
		if (($fcontents = @file($this->sheel->config['globalserverlocale_defaultcurrencyxml'])))
		{
		        $i = 0;
		        foreach ($fcontents AS $line)
		        {
		                if ($sp = mb_strpos($line, $searchfor))
		                {
		                        $xmlarray = explode("'", $line);
		                        $xmlabbrev = trim($xmlarray[3]);
		                        $xmlrate[$i]['abbv'] = mb_strtoupper(trim($xmlarray[1]));
		                        $xmlrate[$i]['rate'] = $xmlabbrev;
		                        $i++;
		                }
		        }
		        for ($x = 0; $x < $i; $x++)
		        {
		                $this->sheel->db->query("
		                        UPDATE " . DB_PREFIX . "currency
		                        SET rate = '" . $this->sheel->db->escape_string($xmlrate[$x]['rate']) . "',
		                        `time` = '" . DATETIME24H . "'
		                        WHERE currency_abbrev = '" . $this->sheel->db->escape_string($xmlrate[$x]['abbv']) . "'
					LIMIT 1
		                ", 0, null, __FILE__, __LINE__);
		                $cronlog .= $xmlrate[$x]['abbv'] . ' = ' . $xmlrate[$x]['rate'] . ', ';
		        }
			// BTC - Bitcoin from blockchain.info
			if (!empty($this->sheel->config['globalserverlocale_defaultcryptocurrencyxml']))
			{
				$btc = @file_get_contents($this->sheel->config['globalserverlocale_defaultcryptocurrencyxml']);
				if (!empty($btc))
				{
					$btc = floatval($btc);
					if ($btc > 0)
					{
						$cronlog .= 'BTC = ' . $btc . ', ';
						$this->sheel->db->query("
							UPDATE " . DB_PREFIX . "currency
							SET rate = '" . $this->sheel->db->escape_string($btc) . "',
							`time` = '" . DATETIME24H . "'
							WHERE currency_abbrev = 'BTC'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
					}
					else
					{
						$cronlog .= 'BTC = (ERROR - blockchain.com down?), ';
					}
				}
			}

		        $this->sheel->db->query("
		                UPDATE " . DB_PREFIX . "currency
		                SET rate = '1.0000',
		                `time` = '" . DATETIME24H . "'
		                WHERE currency_abbrev = 'EUR'
				LIMIT 1
		        ", 0, null, __FILE__, __LINE__);
		        if (!empty($cronlog))
		        {
		                $cronlog = mb_substr($cronlog, 0, -2);
		        }
		}
		return 'currency->fetch_live_rates() [' . $cronlog . '], ';
	}
}
?>
