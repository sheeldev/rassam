<?php
/**
 * Configuration class.
 *
 * @package      sheel\Language
 * @version      1.0.0.0
 * @author       sheel
 */
class configuration
{
	var $buildversion = '';
	var $scrumbs = array();
	var $scollapse = array();
	var $regions = array();
	var $loadaverage = null;
	protected $sheel;
	/**
	 * Constructor
	 */
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->factory($this->sheel);
	}
	function factory($factory)
	{
		$caller = get_calling_location();
		$this->sheel->timer->start();
		$sconfig = $sregions = array();
		if (
			!$sconfig = $this->sheel->cachecore->fetch('sconfig', array(
				'uid' => false,
				'sid' => false,
				'rid' => false,
				'styleid' => false,
				'slng' => false
			)
			)
		) {
			$config = $this->sheel->db->query("
				(SELECT name, value
				FROM " . DB_PREFIX . "configuration)
				UNION
				(SELECT name, value
				FROM " . DB_PREFIX . "payment_configuration)
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($config) > 0) {
				while ($res = $this->sheel->db->fetch_array($config, DB_ASSOC)) {
					$sconfig[$res['name']] = $res['value'];
				}
			}
			unset($config, $res);
			$paygroups = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
				FROM " . DB_PREFIX . "payment_groups
				WHERE moduletype = 'gateway'
			", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($paygroups) > 0) {
				while ($res = $this->sheel->db->fetch_array($paygroups, DB_ASSOC)) {
					if ($res['groupname'] == $sconfig['use_internal_gateway']) {
						$v3pay['selectedmodule'] = $res['groupname'];
						break;
					} else {
						$v3pay['selectedmodule'] = 'none';
					}
				}
				unset($res);
				if ($v3pay['selectedmodule'] != 'none') {
					$sql = $this->sheel->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "name, value
						FROM " . DB_PREFIX . "payment_configuration
						WHERE configgroup = '" . $this->sheel->db->escape_string($v3pay['selectedmodule']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($this->sheel->db->num_rows($sql) > 0) {
						while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
							$sconfig[$res['name']] = $res['value'];
						}
						unset($res);
					}
					unset($sql);
				}
				unset($v3pay);
			}
			unset($paygroups);

			// important asset URLs and paths
			$sconfig['defaultstyle'] = '1';
			$sconfig['imgrel'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/';
			$sconfig['img'] = $sconfig['imgcdn'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_IMAGES_NAME . '/';
			$sconfig['imguploads'] = $sconfig['imguploadscdn'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_UPLOADS_NAME . '/' . DIR_ATTACHMENTS_NAME . '/';
			$sconfig['css'] = $sconfig['csscdn'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_CSS_NAME . '/';
			$sconfig['js'] = $sconfig['jscdn'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/';
			$sconfig['fonts'] = $sconfig['fontscdn'] = SUB_FOLDER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_FONTS_NAME . '/';
			if (isset($sconfig['usecdn']) and $sconfig['usecdn']) { // begin cdn content delivery static asset urls
				$sconfig['imgcdn'] = ((!empty($sconfig['usecdn_imgcdn'])) ? $sconfig['usecdn_imgcdn'] : substr(HTTPS_SERVER, 0, -1) . $sconfig['img']);
				$sconfig['csscdn'] = ((!empty($sconfig['usecdn_csscdn'])) ? $sconfig['usecdn_csscdn'] : substr(HTTPS_SERVER, 0, -1) . $sconfig['css']);
				$sconfig['jscdn'] = ((!empty($sconfig['usecdn_jscdn'])) ? $sconfig['usecdn_jscdn'] : substr(HTTPS_SERVER, 0, -1) . $sconfig['js']);
				$sconfig['fontscdn'] = ((!empty($sconfig['usecdn_fontscdn'])) ? $sconfig['usecdn_fontscdn'] : substr(HTTPS_SERVER, 0, -1) . $sconfig['fonts']);
			}
			$sconfig['globalserverlocale_sitetimezone'] = (empty($sconfig['globalserverlocale_sitetimezone']) ? 'America/Los_Angeles' : $sconfig['globalserverlocale_sitetimezone']);
			$this->sheel->cachecore->store("sconfig", $sconfig, CACHE_EXPIRY, array(
				'uid' => false,
				'sid' => false,
				'rid' => false,
				'styleid' => false,
				'slng' => false
			)
			);
		}
		$regions = array(
			'europe',
			'africa',
			'antarctica',
			'asia',
			'north_america',
			'oceania',
			'south_america'
		);
		$sel_regions = $this->is_serialized($sconfig['shipping_regions']) ? unserialize($sconfig['shipping_regions']) : array();
		foreach ($regions as $value) {
			$sregions[$value] = (in_array($value, $sel_regions)) ? true : false;
		}
		$sregions['worldwide'] = ($sconfig['worldwideshipping'] == '1') ? true : false;
		$this->regions = $sregions;
		if (function_exists('date_default_timezone_set')) {
			date_default_timezone_set($sconfig['globalserverlocale_sitetimezone']);
		}
		define('SITE_NAME', $sconfig['globalserversettings_sitename']);
		define('SITE_ADDRESS', $sconfig['globalserversettings_siteaddress']);
		define('SITE_EMAIL', $sconfig['globalserversettings_siteemail']);
		define('SITE_CONTACT', $sconfig['globalserversettings_staffemail']);
		define('SITE_PHONE', $sconfig['globalserversettings_sitephone']);
		define('COMPANY_NAME', $sconfig['globalserversettings_companyname']);
		define('COOKIE_PREFIX', (empty($sconfig['globalsecurity_cookiename']) ? 'sheel_' : $sconfig['globalsecurity_cookiename']));
		$this->spage = array(
			'login' => 'signin/',
			'payment' => 'payment/',
			'buying' => 'buying/',
			'feedback' => 'feedback/',
			'members' => 'members/',
			'portfolio' => 'p/',
			'merch' => 'merch/',
			'main' => '/',
			'watchlist' => 'watchlist/',
			'preferences' => 'preferences/',
			'subscription' => 'membership/',
			'accounting' => 'accounting/',
			'messages' => 'messages/',
			'notify' => 'notify/',
			'search' => 'search/',
			'upload' => 'upload/',
			'registration' => 'register/',
			'selling' => 'selling/',
			'campaign' => 'campaign/',
			'nonprofits' => 'nonprofits/',
			'escrow' => 'escrow/',
			'bulk' => 'bulk/',
			// admin
			'components' => 'apps/',
			'connections' => 'sessions/',
			'language' => 'language/',
			'locations' => 'locations/',
			'settings' => 'settings/',
			'subscribers' => 'customers/',
			'dashboard' => 'dashboard/',
			'compare' => 'compare/',
			'styles' => 'styles/',
			'tools' => 'tools/'
		);


		$this->sheel->config = array_merge($this->sheel->config, $sconfig);
		$this->sheel->config['version'] = VERSION;
		$this->sheel->config['build'] = $this->buildversion = SVNVERSION;
		$this->fetch_geoip_server_vars(IPADDRESS);
		$this->post_request_protection();
		$this->sheel->show['searchengine'] = $this->is_search_crawler();
		$this->sheel->show['serveroverloaded'] = $this->init_server_overload_checkup();

		$this->fetch_breadcrumb_titles();
		$this->sheel->timer->stop();
		unset($sconfig, $sregions);
		DEBUG("factory()", 'FUNCTION', $this->sheel->timer->get(), $caller);
	}
    function fetch_breadcrumb_titles()
    {
        $this->scrumbs = array(
            'pay/' => '{_invoicing}',
            'signin/' => '{_login}',
            'payment/' => '{_payments}',
            'attachment/' => '{_attachment}',
            'buying' => '{_buying}',
            'rfp' => 'RFP',
            'feedback/' => '{_feedback}',
            'members/' => '{_members}',
            'p/' => '{_portfolios}',
            'merch' => '{_products}',
            '/' => '{_main}',
            'watchlist/' => '{_watchlist}',
            'upload/' => '{_upload}',
            'preferences/' => '{_preferences}',
            'membership/' => '{_subscription}',
            'accounting/' => '{_accounting}',
            'messages/' => '{_messages}',
            'notify/' => '{_notify}',
            'search/' => '{_search}',
            'register/' => '{_registration}',
            'selling/' => '{_selling}',
            'compare/' => '{_compare}',
            'campaign/' => 'Campaign',
            'ajax/' => 'Ajax',
            'nonprofits/' => '{_nonprofits}',
            'escrow/' => '{_escrow}',
            'bulk/' => '{_bulk}',
            // admin control panel
            'apps/' => '{_products}',
            'sessions/' => '{_connections}',
            'language/' => '{_languages}',
            'locations/' => '{_locations}',
            'settings/' => '{_settings}',
            'customers/' => '{_customers}',
            'dashboard/' => '{_dashboard}',
            'styles/' => '{_styles}',
            'tools/' => '{_tools}'
        );

        return $this->scrumbs;
    }
    function init_server_overload_checkup($returnbool = false)
    {
        $serveroverloaded = false;
        $this->loadaverage = '';
        if (PHP_OS == 'Linux') {
            $loadaverageArray = @sys_getloadavg();
            $this->loadaverage = $loadaverageArray[0];
            if (isset($this->sheel->config['serveroverloadlimit']) and $this->sheel->config['serveroverloadlimit'] > 0 and $this->loadaverage > $this->sheel->config['serveroverloadlimit']) {
                $serveroverloaded = true;
            }
        }
        if (empty($this->loadaverage)) {
            $this->loadaverage = 'n/a';
        }
        if ($returnbool) {
            return $serveroverloaded;
        }
        if ($serveroverloaded) {
			$message = 'This application has too many connections or is currently overloaded. Please try again in a few minutes. Thank you.';
            echo $message;
            exit();
        }
    }

    function is_search_crawler()
    {
        $this->sheel->timer->start();
        $caller = get_calling_location();
        if (($xml = $this->sheel->cachecore->fetch("crawlers_xml")) === false) {
            $xml = array();
            $handle = opendir(DIR_XML);
            while (($file = readdir($handle)) !== false) {
                if (! preg_match('#^crawlers.xml$#i', $file, $matches)) {
                    continue;
                }
                $xml = $this->sheel->xml->construct_xml_array('UTF-8', 1, $file);
            }
            ksort($xml);
            $this->sheel->cachecore->store("crawlers_xml", $xml);
        }
        if (is_array($xml['crawler'])) {
            foreach ($xml['crawler'] as $crawler) {
                if (defined('USERAGENT') and USERAGENT != '' and preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", USERAGENT)) {
                    $this->sheel->show['searchenginename'] = $crawler['title'];
                    return true;
                }
            }
        }
        unset($handle, $xml);
        $this->sheel->timer->stop();
        DEBUG("is_search_crawler()", 'FUNCTION', $this->sheel->timer->get(), $caller);
        return false;
    }
    function is_serialized($data = '')
    {
        return (@unserialize($data) !== false);
    }
    function fetch_geoip_server_vars($ipaddress = '')
    {
        $this->sheel->timer->start();
        $caller = get_calling_location();
        $_SERVER['GEOIP_COUNTRYCODE'] = '';
        $_SERVER['GEOIP_COUNTRY'] = '';
        $_SERVER['GEOIP_STATECODE'] = '';
        $_SERVER['GEOIP_STATE'] = '';
        $_SERVER['GEOIP_CITY'] = '';
        $_SERVER['GEOIP_ZIPCODE'] = '';
        if (file_exists(DIR_GEOIP . 'GeoLiteCity.dat')) {
            if (! function_exists('geoip_open')) {
                require_once (DIR_CLASSES . 'class.geoip.inc.php');
            }
            $geoip = geoip_open(DIR_GEOIP . 'GeoLiteCity.dat', GEOIP_STANDARD);
            $geo = geoip_record_by_addr($geoip, ((empty($ipaddress)) ? IPADDRESS : $ipaddress));
            $_SERVER['GEOIP_COUNTRYCODE'] = (! empty($geo->country_code) ? $geo->country_code : '');
            $_SERVER['GEOIP_COUNTRY'] = (! empty($geo->country_name) ? $geo->country_name : '');
            $_SERVER['GEOIP_STATECODE'] = (! empty($geo->region) ? $geo->region : '');
            $_SERVER['GEOIP_STATE'] = (! empty($geo->region) ? (! empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : '') : '');
            $_SERVER['GEOIP_CITY'] = (! empty($geo->city) ? $geo->city : '');
            $_SERVER['GEOIP_ZIPCODE'] = (! empty($geo->postal_code) ? $geo->postal_code : '');
            geoip_close($geoip);
            unset($geoip, $geo);
        }
        $this->sheel->timer->stop();
        DEBUG("fetch_geoip_server_vars()", 'FUNCTION', $this->sheel->timer->get(), $caller);
    }
    function post_request_protection()
    {
        $this->sheel->timer->start();
        $caller = get_calling_location();
        if (isset($_SERVER['REQUEST_METHOD']) and ! empty($_SERVER['REQUEST_METHOD']) and mb_strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            // default referrers should be: paypal.com skrill.com authorize.net cashu.com plugnpay.com
            // please see AdminCP > Global Security Settings to update this list
            $acceptedreferrers = $this->sheel->config['post_request_whitelist'];
            if (! empty($_ENV['HTTP_HOST']) or ! empty($_SERVER['HTTP_HOST'])) {
                $httphost = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
            } else if (! empty($_SERVER['SERVER_NAME']) or ! empty($_ENV['SERVER_NAME'])) {
                $httphost = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
            }
            if (! empty($httphost) and ! empty($_SERVER['HTTP_REFERER'])) {
                $httphost = preg_replace('#:80$#', '', trim($httphost));
                $parts = @parse_url($_SERVER['HTTP_REFERER']);
                $port = ! empty($parts['port']) ? intval($parts['port']) : '80';
                $host = $parts['host'] . ((! empty($port) and $port != '80') ? ":$port" : '');
                $allowdomains = preg_split('#\s+#', $acceptedreferrers, - 1, PREG_SPLIT_NO_EMPTY);
                $allowdomains[] = preg_replace('#^www\.#i', '', $httphost);
                $passcheck = false;
                foreach ($allowdomains as $allowhost) {
                    if (preg_match('#' . preg_quote($allowhost, '#') . '$#siU', $host)) {
                        $passcheck = true;
                        break;
                    }
                }
                unset($allowdomains);
                if ($passcheck == false) {
                    $message = 'POST request could not find your domain in the whitelist. Please contact support for further information.';
                    die($message);
                }
            }
        }
        $this->sheel->timer->stop();
        DEBUG("post_request_protection()", 'FUNCTION', $this->sheel->timer->get(), $caller);
    }

}
?>