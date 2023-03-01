<?php
/**
 * Common class which holds the majority of common sheel functions in the system
 *
 * @package      sheel\Common
 * @version      1.0.0.0
 * @author       sheel
 */
class common
{
	protected $sheel;
	var $valid_files = array();
	var $username_errors = array();
	var $socialdomains = array(
		'facebook',
		'twitter',
		'instagram',
		'pinterest',
		'linkedin',
		'github',
		'youtube',
		'vimeo',
		'google',
		'flickr'
	);
	var $crawlers = array(
		'Google' => 'googlebot',
		'MSN' => 'msnbot',
		'Rambler' => 'ramblerbot',
		'Yahoo' => 'yahoobot',
		'AbachoBOT' => 'abachobot',
		'accoona' => 'accoonabot',
		'AcoiRobot' => 'acoirobot',
		'ASPSeek' => 'aspseekbot',
		'CrocCrawler' => 'croccrawlerbot',
		'Dumbot' => 'dumbot',
		'FAST-WebCrawler' => 'fastwebcrawlerbot',
		'GeonaBot' => 'geonabot',
		'Gigabot' => 'gigabot',
		'Lycos spider' => 'lycosbot',
		'MSRBOT' => 'msrbot',
		'AltaVista robot' => 'altavistabot',
		'ID-Search Bot' => 'idbot',
		'eStyle Bot' => 'estylebot',
		'Scrubby robot' => 'scrubbybot',
		'Facebook' => 'facebookexternalhitbot',
		'YandexBot' => 'yandexbot',
		'Baiduspider' => 'baiduspiderbot'
	);
	/**
	 * Constructor
	 */
	function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	/**
	 * Function to determine what the visiting users web browser is
	 *
	 * @param       string        browser
	 *
	 * @return      string        Returns browser info
	 */
	function is_webbrowser($browser)
	{
		static $is;
		$agent = mb_strtolower(USERAGENT); // ie 11: Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko
		if (!is_array($is)) {
			$useragent = $agent;
			$is = array(
				'iphone' => 0,
				'ipad' => 0,
				'android' => 0,
				'blackberry' => 0,
				'opera' => 0,
				'ie' => 0,
				'mozilla' => 0,
				'firebird' => 0,
				'firefox' => 0,
				'camino' => 0,
				'konqueror' => 0,
				'safari' => 0,
				'webtv' => 0,
				'netscape' => 0,
				'chrome' => 0,
				'aol' => 0,
				'lynx' => 0,
				'phoenix' => 0,
				'omniweb' => 0,
				'icab' => 0,
				'mspie' => 0,
				'netpositive' => 0,
				'galeon' => 0,
				'maxthon' => 0,
				'edge' => 0,
				'ucbrowser' => 0,
				'vivaldi' => 0,
				'aviator' => 0,
				'coc_' => 0,
				'dragon' => 0,
				'flock' => 0,
				'iron' => 0,
				'kinza' => 0,
				'mxnitro' => 0,
				'nichrome' => 0,
				'perk' => 0,
				'rockmelt' => 0,
				'seznam' => 0,
				'sleipnir' => 0,
				'spark' => 0,
				'webexplorer' => 0,
				'yabrowser' => 0
			);
			preg_match('/msie (.*?);/', $useragent, $regs);
			if (count($regs) < 2) {
				preg_match('/trident\/\d{1,2}.\d{1,2};(.*)rv:([0-9]*)/', $useragent, $regs);
			}
			if (count($regs) > 1) {
				$is['ie'] = 1;
			} else if (mb_strpos($useragent, 'edge') !== false) {
				$is['edge'] = 1;
			} else if (mb_strpos($useragent, 'ucweb') !== false) {
				$is['ucbrowser'] = 1;
			} else if (mb_strpos($useragent, 'vivaldi') !== false) {
				$is['vivaldi'] = 1;
			} else if (mb_strpos($useragent, 'aviator') !== false) {
				$is['aviator'] = 1;
			} else if (mb_strpos($useragent, 'coc_') !== false) {
				$is['coc_'] = 1;
			} else if (mb_strpos($useragent, 'dragon') !== false) {
				$is['dragon'] = 1;
			} else if (mb_strpos($useragent, 'flock') !== false) {
				$is['flock'] = 1;
			} else if (mb_strpos($useragent, 'iron') !== false) {
				$is['iron'] = 1;
			} else if (mb_strpos($useragent, 'kinza') !== false) {
				$is['kinza'] = 1;
			} else if (mb_strpos($useragent, 'mxnitro') !== false) {
				$is['mxnitro'] = 1;
			} else if (mb_strpos($useragent, 'nichrome') !== false) {
				$is['nichrome'] = 1;
			} else if (mb_strpos($useragent, 'perk') !== false) {
				$is['perk'] = 1;
			} else if (mb_strpos($useragent, 'rockmelt') !== false) {
				$is['rockmelt'] = 1;
			} else if (mb_strpos($useragent, 'seznam') !== false) {
				$is['seznam'] = 1;
			} else if (mb_strpos($useragent, 'sleipnir') !== false) {
				$is['sleipnir'] = 1;
			} else if (mb_strpos($useragent, 'spark') !== false) {
				$is['spark'] = 1;
			} else if (mb_strpos($useragent, 'webexplorer') !== false) {
				$is['webexplorer'] = 1;
			} else if (mb_strpos($useragent, 'yabrowser') !== false) {
				$is['yabrowser'] = 1;
			} else if (mb_strpos($useragent, 'iphone') !== false) {
				$is['iphone'] = 1;
			} else if (mb_strpos($useragent, 'ipad') !== false) {
				$is['ipad'] = 1;
			} else if (mb_strpos($useragent, 'android') !== false) {
				$is['android'] = 1;
			} else if (mb_strpos($useragent, 'blackberry') !== false) {
				$is['blackberry'] = 1;
			} else if (mb_strpos($useragent, 'opera') !== false) {
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = (isset($regs[2]) and !empty($regs[2]) ? 1 : 0);
			} else if (mb_strpos($useragent, 'camino') !== false) {
				$is['camino'] = 1;
			} else if (mb_strpos($useragent, 'chrome') !== false) {
				$is['chrome'] = 1;
			} else if (mb_strpos($useragent, 'safari') !== false) {
				preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
				$is['safari'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
			} else if (mb_strpos($useragent, 'konqueror') !== false) {
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
			} else if (mb_strpos($useragent, 'gecko') !== false) {
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
				if (mb_strpos($useragent, 'firefox') !== false or mb_strpos($useragent, 'firebird') !== false or mb_strpos($useragent, 'phoenix') !== false) {
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = (isset($regs[3]) and !empty($regs[3]) ? 1 : 0);
					if (isset($regs[1]) and $regs[1] == 'firefox') {
						$is['firefox'] = (isset($regs[3]) and !empty($regs[3]) ? 1 : 0);
					}
				}
				if (mb_strpos($useragent, 'chimera') !== false or mb_strpos($useragent, 'camino') !== false) {
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = (isset($regs[2]) and !empty($regs[2]) ? 1 : 0);
				}
			} else if (mb_strpos($useragent, 'webtv') !== false) {
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
			} else if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs)) {
				$is['netscape'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
			} else if (mb_strpos($useragent, 'aol') !== false) {
				$is['aol'] = 1;
			} else if (mb_strpos($useragent, 'lynx') !== false) {
				$is['lynx'] = 1;
			} else if (mb_strpos($useragent, 'phoenix') !== false) {
				$is['phoenix'] = 1;
			} else if (mb_strpos($useragent, 'firebird') !== false) {
				$is['firebird'] = 1;
			} else if (mb_strpos($useragent, 'omniweb') !== false) {
				$is['omniweb'] = 1;
			} else if (mb_strpos($useragent, 'icab') !== false) {
				$is['icab'] = 1;
			} else if (mb_strpos($useragent, 'mspie') !== false) {
				$is['mspie'] = 1;
			} else if (mb_strpos($useragent, 'netpositive') !== false) {
				$is['netpositive'] = 1;
			} else if (mb_strpos($useragent, 'galeon') !== false) {
				$is['galeon'] = 1;
			} else if (mb_strpos($useragent, 'maxthon') !== false) {
				$is['maxthon'] = 1;
			} else if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs)) {
				$is['netscape'] = (isset($regs[1]) and !empty($regs[1]) ? 1 : 0);
			}
		}
		$browser = mb_strtolower($browser);
		if (mb_substr($browser, 0, 3) == 'is_') {
			$browser = mb_substr($browser, 3);
		}
		if (isset($is["$browser"]) and $is["$browser"]) {
			return $is["$browser"];
		}
		return 0;
	}
	/**
	 * Function to return a utf-8 string based on a numeric entity string
	 *
	 * @param       string        numeric entity character eg: &320;
	 *
	 * @return      string        Returns utf-8 character based on numeric entities supplied
	 */
	function numeric_to_utf8($t = '')
	{
		$convmap = array(0x0, 0x2FFFF, 0, 0xFFFF);
		return mb_decode_numericentity($t, $convmap, 'UTF-8');
	}
	/**
	 * Function to return numeric entities from htmlentity characters
	 *
	 * @param string
	 *
	 * @return      string
	 */
	function entities_to_numeric($text = '', $flip = 0, $skip = '')
	{
		$to_ncr = array(
			'Ã‚Ë‡' => '&#161;',
			'Ã‚Ë˜' => '&#162;',
			'Ã‚Å�' => '&#163;',
			'Ã‚Â¤' => '&#164;',
			'Ã‚Ä„' => '&#165;',
			'Ã‚Â¦' => '&#166;',
			'Ã‚Â§' => '&#167;',
			'Ã‚Â¨' => '&#168;',
			'Ã‚Â©' => '&#169;',
			'Ã‚Åž' => '&#170;',
			'Ã‚Â«' => '&#171;',
			'Ã‚Â¬' => '&#172;',
			'Ã‚Â®' => '&#174;',
			'Ã‚Å»' => '&#175;',
			'Ã‚Â°' => '&#176;',
			'Ã‚Â±' => '&#177;',
			'Ã‚Ë›' => '&#178;',
			'Ã‚Å‚' => '&#179;',
			'Ã‚Â´' => '&#180;',
			'Ã‚Âµ' => '&#181;',
			'Ã‚Â¶' => '&#182;',
			'Ã‚Â·' => '&#183;',
			'Ã‚Â¸' => '&#184;',
			'Ã‚Ä…' => '&#185;',
			'Ã‚ÅŸ' => '&#186;',
			'Ã‚Â»' => '&#187;',
			'Ã‚Ä½' => '&#188;',
			'Ã‚Ë�' => '&#189;',
			'Ã‚Ä¾' => '&#190;',
			'Ã‚Å¼' => '&#191;',
			'Ä‚â‚¬' => '&#192;',
			'Ä‚?' => '&#193;',
			'Ä‚â€š' => '&#194;',
			'Ä‚?' => '&#195;',
			'Ä‚â€ž' => '&#196;',
			'Ä‚â€¦' => '&#197;',
			'Ä‚â€ ' => '&#198;',
			'Ä‚â€¡' => '&#199;',
			'Ä‚?' => '&#200;',
			'Ä‚â€°' => '&#201;',
			'Ä‚Å ' => '&#202;',
			'Ä‚â€¹' => '&#203;',
			'Ä‚Åš' => '&#204;',
			'Ä‚Å¤' => '&#205;',
			'Ä‚Å½' => '&#206;',
			'Ä‚Å¹' => '&#207;',
			'Ä‚?' => '&#208;',
			'Ä‚â€˜' => '&#209;',
			'Ä‚â€™' => '&#210;',
			'Ä‚â€œ' => '&#211;',
			'Ä‚â€�' => '&#212;',
			'Ä‚â€¢' => '&#213;',
			'Ä‚â€“' => '&#214;',
			'Ä‚â€”' => '&#215;',
			'Ä‚?' => '&#216;',
			'Ä‚â„¢' => '&#217;',
			'Ä‚Å¡' => '&#218;',
			'Ä‚â€º' => '&#219;',
			'Ä‚Å›' => '&#220;',
			'Ä‚Å¥' => '&#221;',
			'Ä‚Å¾' => '&#222;',
			'Ä‚Åº' => '&#223;',
			'Ä‚Â ' => '&#224;',
			'Ä‚Ë‡' => '&#225;',
			'Ä‚Ë˜' => '&#226;',
			'Ä‚Å�' => '&#227;',
			'Ä‚Â¤' => '&#228;',
			'Ä‚Ä„' => '&#229;',
			'Ä‚Â¦' => '&#230;',
			'Ä‚Â§' => '&#231;',
			'Ä‚Â¨' => '&#232;',
			'Ä‚Â©' => '&#233;',
			'Ä‚Åž' => '&#234;',
			'Ä‚Â«' => '&#235;',
			'Ä‚Â¬' => '&#236;',
			'Ä‚Â­' => '&#237;',
			'Ä‚Â®' => '&#238;',
			'Ä‚Å»' => '&#239;',
			'Ä‚Â°' => '&#240;',
			'Ä‚Â±' => '&#241;',
			'Ä‚Ë›' => '&#242;',
			'Ä‚Å‚' => '&#243;',
			'Ä‚Â´' => '&#244;',
			'Ä‚Âµ' => '&#245;',
			'Ä‚Â¶' => '&#246;',
			'Ä‚Â·' => '&#247;',
			'Ä‚Â¸' => '&#248;',
			'Ä‚Ä…' => '&#249;',
			'Ä‚ÅŸ' => '&#250;',
			'Ä‚Â»' => '&#251;',
			'Ä‚Ä½' => '&#252;',
			'Ä‚Ë�' => '&#253;',
			'Ä‚Ä¾' => '&#254;',
			'Ä‚Å¼' => '&#255;',
			'&quot;' => '&#34;',
			'&amp;' => '&#38;',
			'&frasl;' => '&#47;',
			'&lt;' => '&#60;',
			'&gt;' => '&#62;',
			'|' => '&#124;',
			'&nbsp;' => '&#160;',
			'&iexcl;' => '&#161;',
			'&cent;' => '&#162;',
			'&pound;' => '&#163;',
			'&curren;' => '&#164;',
			'&yen;' => '&#165;',
			'&brvbar;' => '&#166;',
			'&brkbar;' => '&#166;',
			'&sect;' => '&#167;',
			'&uml;' => '&#168;',
			'&die;' => '&#168;',
			'&copy;' => '&#169;',
			'&ordf;' => '&#170;',
			'&laquo;' => '&#171;',
			'&not;' => '&#172;',
			'&shy;' => '&#173;',
			'&reg;' => '&#174;',
			'&macr;' => '&#175;',
			'&hibar;' => '&#175;',
			'&deg;' => '&#176;',
			'&plusmn;' => '&#177;',
			'&sup2;' => '&#178;',
			'&sup3;' => '&#179;',
			'&acute;' => '&#180;',
			'&micro;' => '&#181;',
			'&para;' => '&#182;',
			'&middot;' => '&#183;',
			'&cedil;' => '&#184;',
			'&sup1;' => '&#185;',
			'&ordm;' => '&#186;',
			'&raquo;' => '&#187;',
			'&frac14;' => '&#188;',
			'&frac12;' => '&#189;',
			'&frac34;' => '&#190;',
			'&iquest;' => '&#191;',
			'&Agrave;' => '&#192;',
			'&Aacute;' => '&#193;',
			'&Acirc;' => '&#194;',
			'&Atilde;' => '&#195;',
			'&Auml;' => '&#196;',
			'&Aring;' => '&#197;',
			'&AElig;' => '&#198;',
			'&Ccedil;' => '&#199;',
			'&Egrave;' => '&#200;',
			'&Eacute;' => '&#201;',
			'&Ecirc;' => '&#202;',
			'&Euml;' => '&#203;',
			'&Igrave;' => '&#204;',
			'&Iacute;' => '&#205;',
			'&Icirc;' => '&#206;',
			'&Iuml;' => '&#207;',
			'&ETH;' => '&#208;',
			'&Ntilde;' => '&#209;',
			'&Ograve;' => '&#210;',
			'&Oacute;' => '&#211;',
			'&Ocirc;' => '&#212;',
			'&Otilde;' => '&#213;',
			'&Ouml;' => '&#214;',
			'&times;' => '&#215;',
			'&Oslash;' => '&#216;',
			'&Ugrave;' => '&#217;',
			'&Uacute;' => '&#218;',
			'&Ucirc;' => '&#219;',
			'&Uuml;' => '&#220;',
			'&Yacute;' => '&#221;',
			'&THORN;' => '&#222;',
			'&szlig;' => '&#223;',
			'&agrave;' => '&#224;',
			'&aacute;' => '&#225;',
			'&acirc;' => '&#226;',
			'&atilde;' => '&#227;',
			'&auml;' => '&#228;',
			'&aring;' => '&#229;',
			'&aelig;' => '&#230;',
			'&ccedil;' => '&#231;',
			'&egrave;' => '&#232;',
			'&eacute;' => '&#233;',
			'&ecirc;' => '&#234;',
			'&euml;' => '&#235;',
			'&igrave;' => '&#236;',
			'&iacute;' => '&#237;',
			'&icirc;' => '&#238;',
			'&iuml;' => '&#239;',
			'&eth;' => '&#240;',
			'&ntilde;' => '&#241;',
			'&ograve;' => '&#242;',
			'&oacute;' => '&#243;',
			'&ocirc;' => '&#244;',
			'&otilde;' => '&#245;',
			'&ouml;' => '&#246;',
			'&divide;' => '&#247;',
			'&oslash;' => '&#248;',
			'&ugrave;' => '&#249;',
			'&uacute;' => '&#250;',
			'&ucirc;' => '&#251;',
			'&uuml;' => '&#252;',
			'&yacute;' => '&#253;',
			'&thorn;' => '&#254;',
			'&yuml;' => '&#255;',
			'&OElig;' => '&#338;',
			'&oelig;' => '&#339;',
			'&Scaron;' => '&#352;',
			'&scaron;' => '&#353;',
			'&Yuml;' => '&#376;',
			'&fnof;' => '&#402;',
			'&circ;' => '&#710;',
			'&tilde;' => '&#732;',
			'&Alpha;' => '&#913;',
			'&Beta;' => '&#914;',
			'&Gamma;' => '&#915;',
			'&Delta;' => '&#916;',
			'&Epsilon;' => '&#917;',
			'&Zeta;' => '&#918;',
			'&Eta;' => '&#919;',
			'&Theta;' => '&#920;',
			'&Iota;' => '&#921;',
			'&Kappa;' => '&#922;',
			'&Lambda;' => '&#923;',
			'&Mu;' => '&#924;',
			'&Nu;' => '&#925;',
			'&Xi;' => '&#926;',
			'&Omicron;' => '&#927;',
			'&Pi;' => '&#928;',
			'&Rho;' => '&#929;',
			'&Sigma;' => '&#931;',
			'&Tau;' => '&#932;',
			'&Upsilon;' => '&#933;',
			'&Phi;' => '&#934;',
			'&Chi;' => '&#935;',
			'&Psi;' => '&#936;',
			'&Omega;' => '&#937;',
			'&alpha;' => '&#945;',
			'&beta;' => '&#946;',
			'&gamma;' => '&#947;',
			'&delta;' => '&#948;',
			'&epsilon;' => '&#949;',
			'&zeta;' => '&#950;',
			'&eta;' => '&#951;',
			'&theta;' => '&#952;',
			'&iota;' => '&#953;',
			'&kappa;' => '&#954;',
			'&lambda;' => '&#955;',
			'&mu;' => '&#956;',
			'&nu;' => '&#957;',
			'&xi;' => '&#958;',
			'&omicron;' => '&#959;',
			'&pi;' => '&#960;',
			'&rho;' => '&#961;',
			'&sigmaf;' => '&#962;',
			'&sigma;' => '&#963;',
			'&tau;' => '&#964;',
			'&upsilon;' => '&#965;',
			'&phi;' => '&#966;',
			'&chi;' => '&#967;',
			'&psi;' => '&#968;',
			'&omega;' => '&#969;',
			'&thetasym;' => '&#977;',
			'&upsih;' => '&#978;',
			'&piv;' => '&#982;',
			'&ensp;' => '&#8194;',
			'&emsp;' => '&#8195;',
			'&thinsp;' => '&#8201;',
			'&zwnj;' => '&#8204;',
			'&zwj;' => '&#8205;',
			'&lrm;' => '&#8206;',
			'&rlm;' => '&#8207;',
			'&ndash;' => '&#8211;',
			'&mdash;' => '&#8212;',
			'&lsquo;' => '&#8216;',
			'&rsquo;' => '&#8217;',
			'&sbquo;' => '&#8218;',
			'&ldquo;' => '&#8220;',
			'&rdquo;' => '&#8221;',
			'&bdquo;' => '&#8222;',
			'&dagger;' => '&#8224;',
			'&Dagger;' => '&#8225;',
			'&bull;' => '&#8226;',
			'&hellip;' => '&#8230;',
			'&permil;' => '&#8240;',
			'&prime;' => '&#8242;',
			'&Prime;' => '&#8243;',
			'&lsaquo;' => '&#8249;',
			'&rsaquo;' => '&#8250;',
			'&oline;' => '&#8254;',
			'&frasl;' => '&#8260;',
			'&euro;' => '&#8364;',
			'&image;' => '&#8465;',
			'&weierp;' => '&#8472;',
			'&real;' => '&#8476;',
			'&trade;' => '&#8482;',
			'&alefsym;' => '&#8501;',
			'&larr;' => '&#8592;',
			'&uarr;' => '&#8593;',
			'&rarr;' => '&#8594;',
			'&darr;' => '&#8595;',
			'&harr;' => '&#8596;',
			'&crarr;' => '&#8629;',
			'&lArr;' => '&#8656;',
			'&uArr;' => '&#8657;',
			'&rArr;' => '&#8658;',
			'&dArr;' => '&#8659;',
			'&hArr;' => '&#8660;',
			'&forall;' => '&#8704;',
			'&part;' => '&#8706;',
			'&exist;' => '&#8707;',
			'&empty;' => '&#8709;',
			'&nabla;' => '&#8711;',
			'&isin;' => '&#8712;',
			'&notin;' => '&#8713;',
			'&ni;' => '&#8715;',
			'&prod;' => '&#8719;',
			'&sum;' => '&#8721;',
			'&minus;' => '&#8722;',
			'&lowast;' => '&#8727;',
			'&radic;' => '&#8730;',
			'&prop;' => '&#8733;',
			'&infin;' => '&#8734;',
			'&ang;' => '&#8736;',
			'&and;' => '&#8743;',
			'&or;' => '&#8744;',
			'&cap;' => '&#8745;',
			'&cup;' => '&#8746;',
			'&int;' => '&#8747;',
			'&there4;' => '&#8756;',
			'&sim;' => '&#8764;',
			'&cong;' => '&#8773;',
			'&asymp;' => '&#8776;',
			'&ne;' => '&#8800;',
			'&equiv;' => '&#8801;',
			'&le;' => '&#8804;',
			'&ge;' => '&#8805;',
			'&sub;' => '&#8834;',
			'&sup;' => '&#8835;',
			'&nsub;' => '&#8836;',
			'&sube;' => '&#8838;',
			'&supe;' => '&#8839;',
			'&oplus;' => '&#8853;',
			'&otimes;' => '&#8855;',
			'&perp;' => '&#8869;',
			'&sdot;' => '&#8901;',
			'&lceil;' => '&#8968;',
			'&rceil;' => '&#8969;',
			'&lfloor;' => '&#8970;',
			'&rfloor;' => '&#8971;',
			'&lang;' => '&#9001;',
			'&rang;' => '&#9002;',
			'&loz;' => '&#9674;',
			'&spades;' => '&#9824;',
			'&clubs;' => '&#9827;',
			'&hearts;' => '&#9829;',
			'&diams;' => '&#9830;'
		);
		if (isset($flip) and $flip) {
			$to_ncr = array_flip($to_ncr);
		}
		foreach ($to_ncr as $entity => $ncr) {
			if (isset($skip) and $skip != '') {
				if ($skip != $entity) {
					$text = str_replace($entity, $ncr, $text);
				}
			} else {
				$text = str_replace($entity, $ncr, $text);
			}

		}
		return $text;
	}
	/**
	 * Function to
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function xhtml_entities_to_numeric_entities($text = '')
	{
		if (preg_match('~&#x([0-9a-fA-F]+);~', $text, $matches)) {
			$text = str_replace('&#x' . $matches[1] . ';', '&#' . hexdec('0x' . $matches[1]) . ';', $text);
			$text = str_replace(array('&#62;', '&#60;'), array('&gt;', '&lt;'), $text);
		}
		return $text;
	}
	/**
	 * Function to
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function js_escaped_to_xhtml_entities($text = '')
	{
		$text = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($text));
		return $text;
	}
	/**
	 * Function to strips invalid html such as javascript code
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function xss_clean(&$var)
	{
		static $find = array('#javascript#i', '#sheelscript#i'), $replace = array('java script', 'sheel script');
		$var = preg_replace($find, $replace, htmlspecialchars_uni($var));
		return $var;
	}
	/**
	 * Function to display the login information bar for members
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function login_include()
	{
		if (!empty($_SESSION['sheeldata']['user']['userid']) and !empty($_SESSION['sheeldata']['user']['username']) and !empty($_SESSION['sheeldata']['user']['password'])) {
			//$html = '<div class="slim-login lh-14">{_hello}, ' . $_SESSION['sheeldata']['user']['username'] . '<br /><a href="' . HTTPS_SERVER . 'signout/?nc=' . time() . '"><span class="bold fs-14">{_log_out}</span></a></div>';
		} else {
			if (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) {
				//$html = '<div class="slim-login lh-14">{_hello}, ' . $this->sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']) . '<br /><a href="' . HTTPS_SERVER . 'signin/?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '"><span class="bold fs-14">{_not_you}?</span></a></div>';
			} else {
				//$html = '<div class="slim-login lh-14">{_hello}. {_sign_in}<br /><a href="' . HTTPS_SERVER . 'signin/?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '"><span class="bold fs-14">{_your_account}</span></a></div>';
			}
		}
		if (!empty($html)) {
			return $html;
		}
		return false;
	}
	/**
	 * Function to display a date when a supplied user id was last seen
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function last_seen($userid, $location = false)
	{
		$lastseen = '{_never}';
		$sql = $this->sheel->db->query("
                        SELECT lastseen FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			if ($res['lastseen'] == "0000-00-00 00:00:00") {
				$lastseen = '{_more_than_a_month_ago}';
			} else {
				$lastseen = $this->print_date($res['lastseen'], $this->sheel->config['globalserverlocale_globaltimeformat'], 0, 0);
			}
		}
		return $lastseen;
	}
	/**
	 * Function to determine if an email address is valid.
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function is_email_valid($email = '')
	{
		return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,}))$#si', $email);
	}
	/**
	 * Function to determine if an email address is banned.
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function is_email_banned($email = '')
	{
		if (!empty($this->sheel->config['registrationdisplay_emailban'])) {
			$bans = preg_split('/\s+/', $this->sheel->config['registrationdisplay_emailban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans as $banned) {
				if ($this->is_email_valid($banned)) {
					$regex = '^' . preg_quote($banned, '#') . '$';
				} else {
					$regex = preg_quote($banned, '#');
				}
				if (preg_match("#$regex#i", $email)) {
					return 1;
				}
			}
		}
		if (!empty($email)) {
			if (!$this->is_email_valid($email)) {
				return 1;
			}
		}
		return 0;
	}
	/**
	 * Function to determine if a username is banned.
	 *
	 * @param       string        username
	 * @param       boolean       force allow spaces (used in quick registration due to full name requirement containing a space)
	 *
	 * @return      string
	 */
	function is_username_banned($username = '', $forceallowspace = false)
	{
		$isbanned = false;
		if (!empty($this->sheel->config['registrationdisplay_userban'])) {
			$bans = preg_split('/\s+/', $this->sheel->config['registrationdisplay_userban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans as $banned) {
				$regex = '^' . preg_quote($banned, '#') . '$';
				if (preg_match("#$regex#i", $username)) {
					$this->username_errors[] = "{_x_is_banned_username::$username}";
					$isbanned = true;
				}
			}
		}
		if ($this->sheel->config['registration_allow_special'] == 0) {
			if ($forceallowspace) { // reset to safe default allowing spaces
				$this->sheel->config['usernameregex'] = '/[^a-zA-Z0-9@ _.-]+/';
			}
			if (preg_match($this->sheel->config['usernameregex'], $username)) { // marketplace regex pattern detected error
				$this->username_errors[] = "{_x_contains_invalid_chars::$username}";
				$isbanned = true;
			}
		}
		return $isbanned;
	}
	/**
	 * Function to download a file to a web browser.
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function download_file($filestring = '', $filename = '', $filetype = '')
	{
		if (!isset($isIE)) {
			static $isIE;
			$isIE = iif($this->is_webbrowser('ie') or $this->is_webbrowser('opera'), true, false);
		}
		if ($isIE) {
			$filetype = 'application/octetstream';
		} else {
			$filetype = 'application/octet-stream';
		}
		header('Content-Type: ' . $filetype);
		header('Expires: ' . date('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		//header('Content-Length: ' . strlen($filestring));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		echo $filestring;
		exit();
	}
	/**
	 * Function to generate a random string based on a supplied number of characters.
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function construct_random_value($num)
	{
		switch ($num) {
			case "1":
				$rand = "A";
				break;
			case "2":
				$rand = "B";
				break;
			case "3":
				$rand = "C";
				break;
			case "4":
				$rand = "D";
				break;
			case "5":
				$rand = "E";
				break;
			case "6":
				$rand = "F";
				break;
			case "7":
				$rand = "G";
				break;
			case "8":
				$rand = "H";
				break;
			case "9":
				$rand = "I";
				break;
			case "10":
				$rand = "J";
				break;
			case "11":
				$rand = "K";
				break;
			case "12":
				$rand = "L";
				break;
			case "13":
				$rand = "M";
				break;
			case "14":
				$rand = "N";
				break;
			case "15":
				$rand = "O";
				break;
			case "16":
				$rand = "P";
				break;
			case "17":
				$rand = "Q";
				break;
			case "18":
				$rand = "R";
				break;
			case "19":
				$rand = "S";
				break;
			case "20":
				$rand = "T";
				break;
			case "21":
				$rand = "U";
				break;
			case "22":
				$rand = "V";
				break;
			case "23":
				$rand = "W";
				break;
			case "24":
				$rand = "X";
				break;
			case "25":
				$rand = "Y";
				break;
			case "26":
				$rand = "Z";
				break;
			case "27":
				$rand = "0";
				break;
			case "28":
				$rand = "1";
				break;
			case "29":
				$rand = "2";
				break;
			case "30":
				$rand = "3";
				break;
			case "31":
				$rand = "4";
				break;
			case "32":
				$rand = "5";
				break;
			case "33":
				$rand = "6";
				break;
			case "34":
				$rand = "7";
				break;
			case "35":
				$rand = "8";
				break;
			case "36":
				$rand = "9";
				break;
		}
		return $rand;
	}
	/**
	 * Function to fetch the active web browser name.
	 *
	 * @param       string        text
	 *
	 * @return      string
	 */
	function fetch_browser_name($showicon = 0, $readname = '')
	{
		if (isset($readname) and $readname != '') {
			if ($readname == 'ie') {
				$name = 'Internet Explorer';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ie.gif';
			} else if ($readname == 'opera') {
				$name = 'Opera';
				$icon = $this->sheel->config['imgcdn'] . 'browser/opera.gif';
			} else if ($readname == 'firefox' or $readname == 'firebird' or $readname == 'phoenix') {
				$name = 'FireFox';
				$icon = $this->sheel->config['imgcdn'] . 'browser/firefox.gif';
			} else if ($readname == 'camino') {
				$name = 'Camino';
				$icon = $this->sheel->config['imgcdn'] . 'browser/camino.gif';
			} else if ($readname == 'konqueror') {
				$name = 'Konqueror';
				$icon = $this->sheel->config['imgcdn'] . 'browser/konqueror.gif';
			} else if ($readname == 'chrome') {
				$name = 'Chrome';
				$icon = $this->sheel->config['imgcdn'] . 'browser/chrome.gif';
			} else if ($readname == 'safari') {
				$name = 'Safari';
				$icon = $this->sheel->config['imgcdn'] . 'browser/safari.gif';
			} else if ($readname == 'netscape') {
				$name = 'Netscape';
				$icon = $this->sheel->config['imgcdn'] . 'browser/netscape.gif';
			} else if ($readname == 'maxthon') {
				$name = 'Maxthon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/maxthon.png';
			} else if ($readname == 'webtv') {
				$name = 'WebTV';
				$icon = $this->sheel->config['imgcdn'] . 'browser/webtv.gif';
			} else if ($readname == 'lynx') {
				$name = 'Lynx';
				$icon = $this->sheel->config['imgcdn'] . 'browser/lynx.gif';
			} else if ($readname == 'omniweb') {
				$name = 'Omniweb';
				$icon = $this->sheel->config['imgcdn'] . 'browser/omniweb.gif';
			} else if ($readname == 'icab') {
				$name = 'iCab';
				$icon = $this->sheel->config['imgcdn'] . 'browser/icab.gif';
			} else if ($readname == 'mspie') {
				$name = 'mspie';
				$icon = $this->sheel->config['imgcdn'] . 'browser/mspie.gif';
			} else if ($readname == 'netpositive') {
				$name = 'NetPositive';
				$icon = $this->sheel->config['imgcdn'] . 'browser/netpositive.gif';
			} else if ($readname == 'galeon') {
				$name = 'Galeon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/galeon.gif';
			} else if ($readname == 'iphone') {
				$name = 'iPhone';
				$icon = $this->sheel->config['imgcdn'] . 'browser/iphone.gif';
			} else if ($readname == 'ipad') {
				$name = 'iPad';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ipad.gif';
			} else if ($readname == 'blackberry') {
				$name = 'BlackBerry';
				$icon = $this->sheel->config['imgcdn'] . 'browser/blackberry.gif';
			} else if ($readname == 'android') {
				$name = 'Android';
				$icon = $this->sheel->config['imgcdn'] . 'browser/android.gif';
			} else if ($readname == 'tor') {
				$name = 'Tor';
				$icon = $this->sheel->config['imgcdn'] . 'browser/tor.png';
			} else if ($readname == 'edge') {
				$name = 'Edge';
				$icon = $this->sheel->config['imgcdn'] . 'browser/edge.png';
			} else if ($readname == 'ucbrowser') {
				$name = 'UC Browser';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ucbrowser.png';
			} else if ($readname == 'vivaldi') {
				$name = 'Vivaldi';
				$icon = $this->sheel->config['imgcdn'] . 'browser/vivaldi.png';
			} else if ($readname == 'aviator') {
				$name = 'Aviator';
				$icon = $this->sheel->config['imgcdn'] . 'browser/aviator.png';
			} else if ($readname == 'aviator') {
				$name = 'Aviator';
				$icon = $this->sheel->config['imgcdn'] . 'browser/aviator.png';
			} else if ($readname == 'coc_') {
				$name = 'coc_';
				$icon = $this->sheel->config['imgcdn'] . 'browser/coc_.png';
			} else if ($readname == 'dragon') {
				$name = 'Dragon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/dragon.png';
			} else if ($readname == 'flock') {
				$name = 'Flock';
				$icon = $this->sheel->config['imgcdn'] . 'browser/flock.png';
			} else if ($readname == 'iron') {
				$name = 'Iron';
				$icon = $this->sheel->config['imgcdn'] . 'browser/iron.png';
			} else if ($readname == 'kinza') {
				$name = 'Kinza';
				$icon = $this->sheel->config['imgcdn'] . 'browser/kinza.png';
			} else if ($readname == 'mxnitro') {
				$name = 'MXnitro';
				$icon = $this->sheel->config['imgcdn'] . 'browser/mxnitro.png';
			} else if ($readname == 'nichrome') {
				$name = 'Nichrome';
				$icon = $this->sheel->config['imgcdn'] . 'browser/nichrome.png';
			} else if ($readname == 'perk') {
				$name = 'Perk';
				$icon = $this->sheel->config['imgcdn'] . 'browser/perk.png';
			} else if ($readname == 'rockmelt') {
				$name = 'Rockmelt';
				$icon = $this->sheel->config['imgcdn'] . 'browser/rockmelt.png';
			} else if ($readname == 'seznam') {
				$name = 'Seznam';
				$icon = $this->sheel->config['imgcdn'] . 'browser/seznam.png';
			} else if ($readname == 'sleipnir') {
				$name = 'Sleipnir';
				$icon = $this->sheel->config['imgcdn'] . 'browser/sleipnir.png';
			} else if ($readname == 'sparkr') {
				$name = 'Spark';
				$icon = $this->sheel->config['imgcdn'] . 'browser/spark.png';
			} else if ($readname == 'webexplorer') {
				$name = 'Webexplorer';
				$icon = $this->sheel->config['imgcdn'] . 'browser/webexplorer.png';
			} else if ($readname == 'yabrowser') {
				$name = 'Yandex Browser';
				$icon = $this->sheel->config['imgcdn'] . 'browser/yabrowser.png';
			} else {
				$name = '{_unknown}';
				$icon = $this->sheel->config['imgcdn'] . 'browser/unknown.gif';
			}
		} else {
			if ($this->is_webbrowser('ie')) {
				$name = 'ie';
				$real = 'Internet Explorer';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ie.gif';
			} else if ($this->is_webbrowser('opera')) {
				$name = 'opera';
				$real = 'Opera';
				$icon = $this->sheel->config['imgcdn'] . 'browser/opera.gif';
			} else if ($this->is_webbrowser('firefox') or $this->is_webbrowser('firebird') or $this->is_webbrowser('phoenix')) {
				$name = 'firefox';
				$real = 'FireFox';
				$icon = $this->sheel->config['imgcdn'] . 'browser/firefox.gif';
			} else if ($this->is_webbrowser('camino')) {
				$name = 'camino';
				$real = 'Camino';
				$icon = $this->sheel->config['imgcdn'] . 'browser/camino.gif';
			} else if ($this->is_webbrowser('konqueror')) {
				$name = 'konqueror';
				$real = 'Konqueror';
				$icon = $this->sheel->config['imgcdn'] . 'browser/konqueror.gif';
			} else if ($this->is_webbrowser('chrome')) {
				$name = 'chrome';
				$real = 'Chrome';
				$icon = $this->sheel->config['imgcdn'] . 'browser/chrome.gif';
			} else if ($this->is_webbrowser('safari')) {
				$name = 'safari';
				$real = 'Safari';
				$icon = $this->sheel->config['imgcdn'] . 'browser/safari.gif';
			} else if ($this->is_webbrowser('netscape')) {
				$name = 'netscape';
				$real = 'Netscape';
				$icon = $this->sheel->config['imgcdn'] . 'browser/netscape.gif';
			} else if ($this->is_webbrowser('maxthon')) {
				$name = 'maxthon';
				$real = 'Maxthon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/maxthon.png';
			} else if ($this->is_webbrowser('webtv')) {
				$name = 'webtv';
				$real = 'WebTV';
				$icon = $this->sheel->config['imgcdn'] . 'browser/webtv.gif';
			} else if ($this->is_webbrowser('lynx')) {
				$name = 'lynx';
				$real = 'Lynx';
				$icon = $this->sheel->config['imgcdn'] . 'browser/lynx.gif';
			} else if ($this->is_webbrowser('omniweb')) {
				$name = 'omniweb';
				$real = 'Omniweb';
				$icon = $this->sheel->config['imgcdn'] . 'browser/omniweb.gif';
			} else if ($this->is_webbrowser('icab')) {
				$name = 'icab';
				$real = 'iCab';
				$icon = $this->sheel->config['imgcdn'] . 'browser/icab.gif';
			} else if ($this->is_webbrowser('mspie')) {
				$name = 'mspie';
				$real = 'mspie';
				$icon = $this->sheel->config['imgcdn'] . 'browser/mspie.gif';
			} else if ($this->is_webbrowser('netpositive')) {
				$name = 'netpositive';
				$real = 'NetPositive';
				$icon = $this->sheel->config['imgcdn'] . 'browser/netpositive.gif';
			} else if ($this->is_webbrowser('galeon')) {
				$name = 'galeon';
				$real = 'Galeon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/galeon.gif';
			} else if ($this->is_webbrowser('iphone')) {
				$name = 'iphone';
				$real = 'iPhone';
				$icon = $this->sheel->config['imgcdn'] . 'browser/iphone.gif';
			} else if ($this->is_webbrowser('ipad')) {
				$name = 'ipad';
				$real = 'iPad';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ipad.gif';
			} else if ($this->is_webbrowser('blackberry')) {
				$name = 'blackberry';
				$real = 'BlackBerry';
				$icon = $this->sheel->config['imgcdn'] . 'browser/blackberry.gif';
			} else if ($this->is_webbrowser('android')) {
				$name = 'android';
				$real = 'Android';
				$icon = $this->sheel->config['imgcdn'] . 'browser/android.gif';
			} else if ($this->is_webbrowser('tor')) {
				$name = 'tor';
				$real = 'Tor';
				$icon = $this->sheel->config['imgcdn'] . 'browser/tor.png';
			} else if ($this->is_webbrowser('edge')) {
				$name = 'edge';
				$real = 'Edge';
				$icon = $this->sheel->config['imgcdn'] . 'browser/edge.png';
			} else if ($this->is_webbrowser('ucbrowser')) {
				$name = 'ucbrowser';
				$real = 'UC Browser';
				$icon = $this->sheel->config['imgcdn'] . 'browser/ucbrowser.png';
			} else if ($this->is_webbrowser('vivaldi')) {
				$name = 'vivaldi';
				$real = 'Vivaldi';
				$icon = $this->sheel->config['imgcdn'] . 'browser/vivaldi.png';
			} else if ($this->is_webbrowser('aviator')) {
				$name = 'aviator';
				$real = 'Aviator';
				$icon = $this->sheel->config['imgcdn'] . 'browser/aviator.png';
			} else if ($this->is_webbrowser('coc_')) {
				$name = 'coc_';
				$real = 'coc_';
				$icon = $this->sheel->config['imgcdn'] . 'browser/coc_.png';
			} else if ($this->is_webbrowser('dragon')) {
				$name = 'dragon';
				$real = 'Dragon';
				$icon = $this->sheel->config['imgcdn'] . 'browser/dragon.png';
			} else if ($this->is_webbrowser('flock')) {
				$name = 'flock';
				$real = 'Flock';
				$icon = $this->sheel->config['imgcdn'] . 'browser/flock.png';
			} else if ($this->is_webbrowser('iron')) {
				$name = 'iron';
				$real = 'Iron';
				$icon = $this->sheel->config['imgcdn'] . 'browser/iron.png';
			} else if ($this->is_webbrowser('kinza')) {
				$name = 'kinza';
				$real = 'Kinza';
				$icon = $this->sheel->config['imgcdn'] . 'browser/kinza.png';
			} else if ($this->is_webbrowser('mxnitro')) {
				$name = 'mxnitro';
				$real = 'MXnitro';
				$icon = $this->sheel->config['imgcdn'] . 'browser/mxnitro.png';
			} else if ($this->is_webbrowser('nichrome')) {
				$name = 'nichrome';
				$real = 'Nichrome';
				$icon = $this->sheel->config['imgcdn'] . 'browser/nichrome.png';
			} else if ($this->is_webbrowser('perk')) {
				$name = 'perk';
				$real = 'Perk';
				$icon = $this->sheel->config['imgcdn'] . 'browser/perk.png';
			} else if ($this->is_webbrowser('rockmelt')) {
				$name = 'rockmelt';
				$real = 'Rockmelt';
				$icon = $this->sheel->config['imgcdn'] . 'browser/rockmelt.png';
			} else if ($this->is_webbrowser('seznam')) {
				$name = 'seznam';
				$real = 'Seznam';
				$icon = $this->sheel->config['imgcdn'] . 'browser/seznam.png';
			} else if ($this->is_webbrowser('sleipnir')) {
				$name = 'sleipnir';
				$real = 'Sleipnir';
				$icon = $this->sheel->config['imgcdn'] . 'browser/sleipnir.png';
			} else if ($this->is_webbrowser('spark')) {
				$name = 'spark';
				$real = 'Spark';
				$icon = $this->sheel->config['imgcdn'] . 'browser/spark.png';
			} else if ($this->is_webbrowser('webexplorer')) {
				$name = 'webexplorer';
				$real = 'Webexplorer';
				$icon = $this->sheel->config['imgcdn'] . 'browser/webexplorer.png';
			} else if ($this->is_webbrowser('yabrowser')) {
				$name = 'yabrowser';
				$real = 'Yandex Browser';
				$icon = $this->sheel->config['imgcdn'] . 'browser/yabrowser.png';
			} else {
				$name = 'unknown';
				$real = '{_unknown}';
				$icon = $this->sheel->config['imgcdn'] . 'browser/unknown.gif';
			}
		}
		if (isset($showicon) and $showicon) {
			return $icon;
		}
		return $name;
	}
	function fetch_search_keywords($referrer = '')
	{
		$keywords = '';
		if (!empty($referrer)) {
			$referringPage = parse_url($referrer);
			if (!empty($referringPage['host']) and (stristr($referringPage['host'], 'google.') or stristr($referringPage['host'], 'bing.') or stristr($referringPage['host'], 'yahoo.'))) { // big 3 detected
				$referringPage['query'] = ((isset($referringPage['query']) and !empty($referringPage['query'])) ? $referringPage['query'] : '');
				parse_str($referringPage['query'], $queryVars);
				if (stristr($referringPage['host'], 'google.') or stristr($referringPage['host'], 'bing.')) {
					$keywords = ((isset($queryVars['q']) and !empty($queryVars['q'])) ? $queryVars['q'] : '');
				} else if (stristr($referringPage['host'], 'yahoo.')) {
					$keywords = ((isset($queryVars['p']) and !empty($queryVars['p'])) ? $queryVars['p'] : '');
				}
				if (!empty($keywords)) {
					$keywords = str_replace('+', ' ', $keywords);
				}
			}
		}
		return $keywords;
	}
	function init_pageview_tracker()
	{
		if (defined('LOCATION') and LOCATION == 'ajax') {
			return false;
		}
		$remote = ((isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '');
		$ragent = (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown');
		$browser = $this->fetch_browser_name();
		if ($browser == 'unknown') { // find out if we're a search bot
			foreach ($this->crawlers as $crawlerfull => $crawler) {
				if (isset($crawlerfull) and isset($crawler) and stristr($ragent, $crawlerfull)) {
					$browser = $crawler;
					break;
				}
			}
		}
		$rrefer = $orrefer = (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
		$keywords = $this->fetch_search_keywords($orrefer);
		$utmcampaign = ((isset($this->sheel->GPC['utm_campaign']) and !empty($this->sheel->GPC['utm_campaign'])) ? $this->sheel->GPC['utm_campaign'] : '');
		if (!empty($rrefer)) {
			$rrefer = parse_url($rrefer);
			$rrefer = ((isset($rrefer['host']) and !empty($rrefer['host'])) ? $rrefer['host'] : '{_direct}');
		} else {
			$rrefer = '{_direct}';
		}
		$uid = (isset($_SESSION['sheeldata']['user']['userid']) ? $_SESSION['sheeldata']['user']['userid'] : '0');
		$uri = (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
		$urit = (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/') . '~~~' . TIMESTAMPNOW;
		$datenow = date('Y') . '-' . date('m') . '-' . date('d');
		$device = (($this->is_mobile()) ? 'mobile' : 'browser'); // tablet, game console, api?
		$social = '';
		if (!empty($orrefer) and is_array($this->socialdomains)) {
			foreach ($this->socialdomains as $socialdomain) {
				if (isset($socialdomain) and strpos($socialdomain, $orrefer) !== false) {
					$social = $socialdomain;
					break;
				}
			}
		}
		if (!empty($remote)) { // record views for category, items and stores
			$countryid = $this->sheel->common_location->fetch_country_id($_SERVER['GEOIP_COUNTRY'], $this->sheel->language->fetch_site_slng());
			$regionid = $this->sheel->common_location->fetch_region_by_countryid($countryid, false, true);
			unset($countryid);
			$this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "hits
				(id, userid, url, datetime, agent, ipaddress, regionid)
				VALUES (
				NULL,
				'" . $this->sheel->db->escape_string($uid) . "',
				'" . $this->sheel->db->escape_string($uri) . "',
				'" . DATETIME24H . "',
				'" . $this->sheel->db->escape_string($ragent) . "',
				'" . $this->sheel->db->escape_string($remote) . "',
				'" . intval($regionid) . "'
				)
			", 0, null, __FILE__, __LINE__);
			$sql = $this->sheel->db->query("
				SELECT id, url, url_time, pageviews
				FROM " . DB_PREFIX . "visits
				WHERE ipaddress = '" . $this->sheel->db->escape_string($remote) . "'
					AND agent = '" . $this->sheel->db->escape_string($ragent) . "'
					AND browser = '" . $this->sheel->db->escape_string($browser) . "'
					AND date = '" . $this->sheel->db->escape_string($datenow) . "'
					AND device = '" . $this->sheel->db->escape_string($device) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				if ($res['pageviews'] <= 100) {
					if (defined('LOCATION') and LOCATION != 'cron' or !defined('LOCATION')) {
						$uri = $res['url'] . '; ' . $uri;
						$urit = $res['url_time'] . '; ' . $urit;
					}
					$pageviews = $res['pageviews'] + 1;
					$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "visits
						SET url = '" . $this->sheel->db->escape_string($uri) . "',
						url_time = '" . $this->sheel->db->escape_string($urit) . "',
						lasthit = '" . DATETIME24H . "',
						pageviews = '" . intval($pageviews) . "'
						WHERE id = '" . $res['id'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				} else { // prevent large field row in table
					$pageviews = $res['pageviews'] + 1;
					$this->sheel->db->query("
						UPDATE " . DB_PREFIX . "visits
						SET lasthit = '" . DATETIME24H . "',
						pageviews = '" . intval($pageviews) . "'
						WHERE id = '" . $res['id'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			} else {
				$this->sheel->db->query("
					INSERT INTO " . DB_PREFIX . "visits
					(id, url, url_time, ipaddress, userid, pageviews, date, firsthit, lasthit, referrer, referrer_url, keywords, social, utmcampaign, browser, agent, siteid, country, landingpage, device)
					VALUES (
					NULL,
					'" . $this->sheel->db->escape_string($uri) . "',
					'" . $this->sheel->db->escape_string($urit) . "',
					'" . $this->sheel->db->escape_string($remote) . "',
					'" . $this->sheel->db->escape_string($uid) . "',
					'1',
					CURDATE(),
					'" . DATETIME24H . "',
					'" . DATETIME24H . "',
					'" . $this->sheel->db->escape_string($rrefer) . "',
					'" . $this->sheel->db->escape_string($orrefer) . "',
					'" . $this->sheel->db->escape_string($keywords) . "',
					'" . $this->sheel->db->escape_string($social) . "',
					'" . $this->sheel->db->escape_string($utmcampaign) . "',
					'" . $this->sheel->db->escape_string($browser) . "',
					'" . $this->sheel->db->escape_string($ragent) . "',
					'" . SITE_ID . "',
					'" . $this->sheel->db->escape_string($_SERVER['GEOIP_COUNTRY']) . "',
					'" . $this->sheel->db->escape_string($uri) . "',
					'" . $this->sheel->db->escape_string($device) . "'
					)
				", 0, null, __FILE__, __LINE__);
			}
		}
	}


	/**
	 * Function to handle displaying the date and time within sheel.  This function has been enhanced to display the actual
	 * time in any given timezone id passed to it.  If no time zone id is supplied the default time display will be that
	 * of the site marketplace.
	 *
	 * @param        string      date and time string
	 * @param        string      format of string (optional)
	 * @param        bool        should we show the time zone abbreviation in the string?
	 * @param        bool        should we treat the date display with "Yesterday and Today" instead of the actual date?
	 * @param        string      force time zone (ie: America/New_York)
	 *
	 * @return	string      Returns the formatted strftime() date and time string including a timezone identifier if requested
	 */
	function print_date($datetime = '', $format = '', $showtimezone = false, $yesterdaytoday = false, $forcetimezone = '')
	{
		if (empty($format)) {
			$format = $this->sheel->config['globalserverlocale_globaltimeformat']; //D, M d, Y h:i A
		}
		if (empty($forcetimezone) and isset($_SESSION['sheeldata']['user']['timezone']) and !empty($_SESSION['sheeldata']['user']['timezone'])) {
			$forcetimezone = $_SESSION['sheeldata']['user']['timezone'];
		}
		if ($yesterdaytoday and $this->sheel->config['globalserverlocale_yesterdaytodayformat']) {
			$tempdate = date('Y-m-d', $this->sheel->datetimes->fetch_timestamp_from_datetime($datetime));
			$difference = $this->sheel->datetimes->fetch_timestamp_from_datetime(DATETIME24H) - $this->sheel->datetimes->fetch_timestamp_from_datetime($datetime);
			if ($difference < 3600) {
				$result = ($difference < 120) ? '{_less_an_a_minute_ago}' : $this->sheel->language->construct_phrase("{_x_minutes_ago}", intval($difference / 60));
			} else if ($difference < 7200) {
				$result = '{_one_hour_ago}';
			} else if ($difference < 86400) {
				$result = $this->sheel->language->construct_phrase("{_x_hours_ago}", intval($difference / 3600));
			}
		}
		if (empty($result)) {
			$datetime = empty($datetime) ? null : $datetime;
			$datetimezone = new DateTimeZone($this->sheel->config['globalserverlocale_sitetimezone']);
			$date = new DateTime($datetime, $datetimezone);
			if (!empty($forcetimezone)) {
				$datetimezone = new DateTimeZone($forcetimezone);
				$date->setTimezone($datetimezone);
			}
			$result = vdate($format, $date->getTimestamp()); // multi-language
			if ($showtimezone) {
				$result .= ' ' . $date->format('T');
			}
		}
		return $result;
	}
	function print_date_verbose($datetime = '')
	{
		$tempdate = date('Y-m-d', $this->sheel->datetimes->fetch_timestamp_from_datetime($datetime));
		$ago = $this->sheel->datetimes->fetch_timestamp_from_datetime(DATETIME24H) - $this->sheel->datetimes->fetch_timestamp_from_datetime($datetime);
		if ($ago < 60) {
			$when = round($ago);
			$t = ($when == 1) ? "{_second_lower}" : "{_seconds_lower}";
		} else if ($ago < 3600) {
			$when = round($ago / 60);
			$t = ($when == 1) ? "{_minute_lower}" : "{_minutes_lower}";
		} else if ($ago >= 3600 && $ago < 86400) {
			$when = round($ago / 60 / 60);
			$t = ($when == 1) ? "{_hour_lower}" : "{_hours_lower}";
		} else if ($ago >= 86400 && $ago < 2629743.83) {
			$when = round($ago / 60 / 60 / 24);
			$t = ($when == 1) ? "{_day_lower}" : "{_days_lower}";
		} else if ($ago >= 2629743.83 && $ago < 31556926) {
			$when = round($ago / 60 / 60 / 24 / 30.4375);
			$t = ($when == 1) ? "{_month_lower}" : "{_months_lower}";
		} else {
			$when = round($ago / 60 / 60 / 24 / 365);
			$t = ($when == 1) ? "{_year_lower}" : "{_years_lower}";
		}
		$this->sheel->template->templateregistry['t'] = $t;
		$t = $this->sheel->template->parse_template_phrases('t');
		$phrase = "{_x_x_ago::$when::$t}";
		$this->sheel->template->templateregistry['verbose'] = $phrase;
		$verbose = $this->sheel->template->parse_template_phrases('verbose');
		return $verbose;
	}
	/**
	 * Function to print the online status of a particular user.  This function is also LanceAlert ready
	 * where if the user is online and logged into the app it will show the online status of the IM user status
	 * (away, busy, online, dnd, etc) vs the status of online or offline
	 *
	 * @param        integer       user id
	 * @param        string        offline user color (example: gray)
	 * @param        string        online user color (example: green)
	 * @param        boolean       icon output only (default false)
	 *
	 * @return	string        Returns the HTML representation of the online status
	 */
	function print_online_status($userid = 0, $offlinecolor = 'litegray', $onlinecolor = 'green', $icononly = false, $showofflineicon = false)
	{
		$isonline = (($icononly) ? (($showofflineicon) ? $this->sheel->config['imgcdn'] . 'v5/ico_dot_red.gif' : '') : '{_offline}');
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.user_id
			FROM " . DB_PREFIX . "sessions s,
			" . DB_PREFIX . "users u
			WHERE u.user_id = '" . intval($userid) . "'
				AND u.user_id = s.userid
				AND isuser = '1'
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$isonline = (($icononly) ? $this->sheel->config['imgcdn'] . 'v5/ico_dot_green.gif' : '{_online}');
		}
		return $isonline;
	}
	function print_online_indicator($userid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.user_id
			FROM " . DB_PREFIX . "sessions s,
			" . DB_PREFIX . "users u
			WHERE u.user_id = '" . intval($userid) . "'
				AND u.user_id = s.userid
				AND (s.isuser = '1' OR s.isadmin = '1')
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			return true;
		}
		return false;
	}
	/**
	 * Function to handle parsing PHP code internally for add-on and product support in sheel
	 * and accepts code with or without <?php and ?> tags
	 *
	 * @param        string      php code to parse
	 *
	 * @return	mixed       Returns mixed output
	 */
	function parse_php_in_html($html_str = '')
	{
		preg_match_all("/(<\?php|<\?)(.*?)\?>/si", $html_str, $raw_php_matches);
		$php_idx = 0;
		while (isset($raw_php_matches[0][$php_idx])) {
			$raw_php_str = $raw_php_matches[0][$php_idx];
			$raw_php_str = str_replace("<?php", "", $raw_php_str);
			$raw_php_str = str_replace("?>", "", $raw_php_str);
			ob_start();
			eval("$raw_php_str;");
			$exec_php_str = ob_get_contents();
			ob_end_clean();
			$exec_php_str = str_replace("\$", "\\$", $exec_php_str);
			$html_str = preg_replace("/(<\?php|<\?)(.*?)\?>/si", $exec_php_str, $html_str, 1);
			$php_idx++;
		}
		return $html_str;
	}
	/**
	 * Function to process and print out a username bit with icons based on various bits of information
	 *
	 * @param       integer        user id
	 *
	 * @return      string         Formatted text
	 */
	function construct_username_bits($userid = 0)
	{
		$html = $pattern = $extraleftjoin = $extrafields = '';
		if (($html = $this->sheel->cache->fetch('construct_username_bits_' . $userid)) === false) {
			$roles = $this->sheel->db->query("
				SELECT r.custom, u.active
				FROM " . DB_PREFIX . "subscription_user u
				LEFT JOIN " . DB_PREFIX . "subscription s ON u.subscriptionid = s.subscriptionid
				LEFT JOIN " . DB_PREFIX . "roles r ON (u.roleid = r.roleid)
				WHERE u.user_id = '" . intval($userid) . "'
					AND s.type = 'product'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($roles) > 0) {
				$role = $this->sheel->db->fetch_array($roles, DB_ASSOC);
				if (!empty($role['custom'])) {
					$pattern = $role['custom'];
				}
			}
			if (!empty($pattern)) {
				$feedback_memberinfo = array();
				$feedback_memberinfo = $this->sheel->feedback->datastore(intval($userid));
				$username_url = $this->print_username(intval($userid), 'url', 0, '', '');
				$pattern = str_replace('[fbscore]', $feedback_memberinfo['score'], $pattern);
				//$pattern = str_replace('[fbpercent]', '<a href="' . $username_url . '" title="{_total_positive_feedback_percentile}">' . $feedback_memberinfo['pcnt'] . '%</a>', $pattern);
				//$pattern = str_replace('[rating]', '<a href="' . $username_url . '" title="{_total_feedback_rating_out_of_500}">' . $feedback_memberinfo['rating'] . '</a>', $pattern);
				$pattern = str_replace('[fbpercent]', $feedback_memberinfo['pcnt'], $pattern);
				$pattern = str_replace('[rating]', $feedback_memberinfo['rating'], $pattern);
				if ($role['active'] == 'yes') {
					$pattern = str_replace('[stars]', $this->sheel->feedback->print_feedback_icon($feedback_memberinfo['score']), $pattern);
					$pattern = str_replace('[verified]', '', $pattern);
					$pattern = str_replace('[subscription]', $this->sheel->subscription->print_subscription_icon(intval($userid)), $pattern);
					$pattern = str_replace('[fbimport]', $this->sheel->feedback_import->print_imported_feedback(intval($userid), 'userbit'), $pattern);
				} else {
					$pattern = str_replace('[stars]', '', $pattern);
					$pattern = str_replace('[verified]', '', $pattern);
					$pattern = str_replace('[subscription]', '', $pattern);
					$pattern = str_replace('[fbimport]', '', $pattern);
				}

				$html .= $pattern;
				unset($feedback_memberinfo);
			}
			$this->sheel->cache->store('construct_username_bits_' . $userid, $html);
		}
		return $html;
	}
	/**
	 * Function to print a user's username based on seo and other elements such as icons, subscription info, etc
	 *
	 * @param       integer        user id
	 * @param       string         mode
	 * @param       boolean        is bold? (default false)
	 * @param       string         extra info
	 * @param       string         extra seo info
	 * @param       string         display name (if we have one before calling this function, use this.)
	 * @param       boolean        use company name?
	 * @param       string         company name
	 *
	 * @return      string         Returns a formatted version of the user's username
	 */
	function print_username($userid = 0, $mode = 'plain', $bold = 0, $extra = '', $extraseo = '', $displayname = '', $usecompanyname = 0, $companyname = '')
	{
		$html = '';
		$sql = $this->sheel->db->query("
			SELECT username, usernameslug
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
			LIMIT 1
		");
		$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
		$displayname = (($displayname != '') ? $displayname : $res['username']); // admin
		$displayname = (($companyname != '' and $usecompanyname) ? $companyname : $displayname);
		$displayname = (($bold) ? '<strong>' . $displayname . '</strong>' : $displayname);
		if ($mode == 'href') {
			$html = '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'members/' . $res['usernameslug'] . '/' . $extraseo . '">' . $displayname . '</a> ' . $this->construct_username_bits(intval($userid));
		} else if ($mode == 'href_blank') {
			$html = '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'members/' . $res['usernameslug'] . '/' . $extraseo . '" target="_blank">' . $displayname . '</a> ' . $this->construct_username_bits(intval($userid));
		} else if ($mode == 'href_nobit') {
			$html = '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'members/' . $res['usernameslug'] . '/' . $extraseo . '">' . $displayname . '</a>';
		} else if ($mode == 'plain') {
			$html = $displayname;
		} else if ($mode == 'plain_fb') {
			$html = $displayname . ' ' . $this->construct_username_bits(intval($userid));
		} else if ($mode == 'custom') {
			$html = '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'members/' . $res['usernameslug'] . '/' . $extraseo . '">' . $displayname . '</a>';
		} else if ($mode == 'url') {
			$html = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'members/' . $res['usernameslug'] . '/' . $extraseo;
		}
		return $html;
	}

	function fetch_payment_profiles($userid = 0)
	{
		$sql = $this->sheel->db->query("
			SELECT payment_profile, payment_profile_backup
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res;
		}
		return false;
	}
	/**
	 * Function to fetch the business numbers for a user (VAT or Business Reg #)
	 *
	 * @param       integer        user id
	 * @param       bool           force no formatting
	 *
	 * @return      array         Returns business number(s) for display
	 */
	function fetch_business_numbers($userid = 0, $noformatting = '')
	{
		$sql = $this->sheel->db->query("
                        SELECT regnumber, vatnumber, companyname, dnbnumber
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$bn = array();
			if (!empty($res['companyname'])) {
				$bn['companyname'] = stripslashes(o($res['companyname']));
			}
			if (!empty($res['regnumber'])) {
				$bn['regnumber'] = stripslashes(o($res['regnumber']));
			}
			if (!empty($res['vatnumber'])) {
				$bn['vatnumber'] = stripslashes(o($res['vatnumber']));
			}
			if (empty($html)) {
				$bn['companyname'] = '{_no_company_registration_numbers_submitted_to_marketplace}';
			}
		}
		return $bn;
	}
	function fetch_bought_count($userid = 0, $what = 'service')
	{
		$count = 0;
		if ($what == 'product' and $userid > 0) {
			$count = $this->sheel->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productawards");
		}
		if ($count < 0) {
			$count = 0;
		}
		return $count;
	}
	function fetch_sold_count($userid = 0, $what = 'service')
	{
		$count = 0;
		if ($what == 'product' and $userid > 0) {
			$count = $this->sheel->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productsold");
		}
		if ($count < 0) {
			$count = 0;
		}
		return $count;
	}
	/**
	 * Function to fetch specific information about a charity based on a supplied charity id
	 *
	 * @return      array        returns an array of stats
	 */
	function fetch_charity_details($charityid = 0)
	{
		$array = array();
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title, description, url, visible
                        FROM " . DB_PREFIX . "charities
                        WHERE visible = '1'
                                AND charityid = '" . intval($charityid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$array['title'] = stripslashes(o($res['title']));
			$array['description'] = stripslashes(o($res['description']));
			$array['url'] = $res['url'];
		} else {
			$array['title'] = '{_unknown}';
			$array['description'] = 'n/a';
			$array['url'] = $this->sheel->ilpage['nonprofits'];
		}
		return $array;
	}
	/**
	 * Function to fetch the title of the operating system found from the crawler agent ident
	 *
	 * @param        string       browser agent string
	 * @param        boolean      force icon mode (default false)
	 *
	 * @return	string       Returns name of connected crawler
	 */
	function fetch_os_name($agent = '', $forceicon = false)
	{
		if ($forceicon) {
			$oses = array(
				$this->sheel->config['imgcdn'] . 'device/android.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windowsvista.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windowsxp.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows95.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/windows.png',
				$this->sheel->config['imgcdn'] . 'device/openbsd.png',
				$this->sheel->config['imgcdn'] . 'device/sunos.png',
				$this->sheel->config['imgcdn'] . 'device/ubuntu.png',
				$this->sheel->config['imgcdn'] . 'device/linux.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/ios.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/mac.png',
				$this->sheel->config['imgcdn'] . 'device/qnx.png',
				$this->sheel->config['imgcdn'] . 'device/beos.png',
				$this->sheel->config['imgcdn'] . 'device/os2.png'
			);
		} else {
			$oses = array(
				'Android' => 'android',
				'Windows 7' => 'windows nt 6.1',
				'Windows Vista' => 'windows nt 6.0',
				'Windows Server 2003' => 'windows nt 5.2',
				'Windows XP' => '(windows nt 5.1)|(windows xp)',
				'Windows 2000 sp1' => 'windows nt 5.01',
				'Windows 2000' => '(windows nt 5.0)|(windows 2000)',
				'Windows NT' => '(windows nt 4.0)|(winnt4.0)|(winnt)|(windows nt)',
				'Windows Me' => '(windows 98)|(win 9x 4.90)|(windows me)',
				'Windows 98' => '(windows 98)|(win98)',
				'Windows 95' => '(windows 95)|(win95)|(windows_95)',
				'Windows CE' => 'windows ce',
				'Windows 3.11' => 'win16',
				'Windows (version unknown)' => 'windows',
				'OpenBSD' => 'openbsd',
				'SunOS' => 'sunos',
				'Ubuntu' => 'ubuntu',
				'Linux' => '(linux)|(x11)|(red hat)',
				'Mac OS X Beta (Kodiak)' => 'mac os x beta',
				'Mac OS X Yosemite' => 'mac os x 10.10',
				'Mac OS X El Captain' => 'mac os x 10.11',
				'Mac OS X Sierra' => 'mac os x 10.12',
				'Mac OS X Cheetah' => 'mac os x 10.0',
				'Mac OS X Puma' => 'mac os x 10.1',
				'Mac OS X Jaguar' => 'mac os x 10.2',
				'Mac OS X Panther' => 'mac os x 10.3',
				'Mac OS X Tiger' => 'mac os x 10.4',
				'Mac OS X Leopard' => 'mac os x 10.5',
				'Mac OS X Snow Leopard' => 'mac os x 10.6',
				'Mac OS X Lion' => 'mac os x 10.7',
				'Mac OS X Mountain Lion' => 'mac os x 10.8',
				'Mac OS X Mavericks' => 'mac os x 10.9',
				'iPhone iOS' => 'iphone os',
				'Mac OS X (version unknown)' => 'mac os x',
				'Mac OS (classic)' => '(mac_powerpc)|(macintosh)',
				'QNX' => 'qnx',
				'BeOS' => 'beos',
				'OS2' => 'os/2'
			);
		}
		$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
		foreach ($oses as $os => $pattern) {
			if (preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $agent)) {
				return $os;
			}
		}
		return (($forceicon) ? $this->sheel->config['imgcdn'] . 'browser/unknown.gif' : 'Unknown');
	}
	/**
	 * Function to fetch the title of the crawler found within the robot file
	 *
	 * @return	string     Returns name of connected crawler
	 */
	function fetch_search_crawler_title($agent)
	{
		if (($xml = $this->sheel->cachecore->fetch("crawlers_xml")) === false) {
			$xml = array();
			$handle = opendir(DIR_XML);
			while (($file = readdir($handle)) !== false) {
				if (!preg_match('#^crawlers.xml$#i', $file, $matches)) {
					continue;
				}
				$xml = $this->sheel->xml->construct_xml_array('UTF-8', 1, $file);
			}
			ksort($xml);
			$this->sheel->cachecore->store("crawlers_xml", $xml);
		}
		if (is_array($xml['crawler']) and isset($agent) and $agent != '') {
			foreach ($xml['crawler'] as $crawler) {
				if (preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", $agent)) {
					return o($crawler['title']);
				}
			}
		}
		unset($handle, $xml);
		return 'Crawler';
	}
	/**
	 * Function to convert seconds into text format based on a supplied number of seconds argument
	 *
	 * @param        integer      number of seconds
	 *
	 * @return	string       Returns text
	 */
	function sec2text($num_secs, $format = true)
	{
		$htmlx = '';
		if ($format) {
			$days = intval(intval($num_secs) / 86400);
			if ($days >= 1) {
				$htmlx .= $days;
				$htmlx .= '{_d_shortform}, ';
				$num_secs = $num_secs - ($days * 86400);
			}
			$hours = intval(intval($num_secs) / 3600);
			if ($hours >= 1) {
				$htmlx .= $hours;
				$htmlx .= '{_h_shortform}, ';
				$num_secs = $num_secs - ($hours * 3600);
			}
			$minutes = intval(intval($num_secs) / 60);
			if ($minutes >= 1) {
				$htmlx .= $minutes;
				$htmlx .= '{_m_shortform}, ';
				$num_secs = $num_secs - ($minutes * 60);
			}
			$htmlx .= $num_secs . '{_s_shortform}';
		} else {
			return ($num_secs);
		}
		return $htmlx;
	}
	/**
	 * Function to prevent a string from containing words passed to it through the argument
	 *
	 * @param       string       string
	 * @param       array       stop words array
	 */
	function stop_words($text = '', $stopwords)
	{
		$mb_trim = function ($string) {
			$string = preg_replace("/(^\s+)|(\s+$)/us", "", $string);
			return $string;
		};
		$stopwords = array_map("mb_strtolower", $stopwords);
		$stopwords = array_map($mb_trim, $stopwords);
		$pattern = '/[^\w]/u';
		$text = preg_replace($pattern, ',', $text);
		$text = mb_strtolower($text);
		$text_array = explode(",", $text);
		$text_array = array_map($mb_trim, $text_array);
		$html = '';
		foreach ($text_array as $term) {
			if (!empty($term)) {
				if (!in_array($term, $stopwords)) {
					$html .= "$term ";
				}
			}
		}
		return preg_replace("/(^\s+)|(\s+$)/us", "", $html);
	}
	function is_mobile()
	{
		$uaFull = ((isset($_SERVER['HTTP_USER_AGENT']) and !empty($_SERVER['HTTP_USER_AGENT'])) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown');
		$uaStart = substr($uaFull, 0, 4);
		$uaPhone = array(
			'(android|bb\d+|meego).+mobile',
			'avantgo',
			'bada\/',
			'blackberry',
			'blazer',
			'compal',
			'elaine',
			'fennec',
			'hiptop',
			'iemobile',
			'ip(hone|od)',
			'iris',
			'kindle',
			'lge ',
			'maemo',
			'midp',
			'mmp',
			'mobile.+firefox',
			'netfront',
			'opera m(ob|in)i',
			'palm( os)?',
			'phone',
			'p(ixi|re)\/',
			'plucker',
			'pocket',
			'psp',
			'series(4|6)0',
			'symbian',
			'treo',
			'up\.(browser|link)',
			'vodafone',
			'wap',
			'windows ce',
			'xda',
			'xiino'
		);
		$uaMobile = array(
			'1207',
			'6310',
			'6590',
			'3gso',
			'4thp',
			'50[1-6]i',
			'770s',
			'802s',
			'a wa',
			'abac|ac(er|oo|s\-)',
			'ai(ko|rn)',
			'al(av|ca|co)',
			'amoi',
			'an(ex|ny|yw)',
			'aptu',
			'ar(ch|go)',
			'as(te|us)',
			'attw',
			'au(di|\-m|r |s )',
			'avan',
			'be(ck|ll|nq)',
			'bi(lb|rd)',
			'bl(ac|az)',
			'br(e|v)w',
			'bumb',
			'bw\-(n|u)',
			'c55\/',
			'capi',
			'ccwa',
			'cdm\-',
			'cell',
			'chtm',
			'cldc',
			'cmd\-',
			'co(mp|nd)',
			'craw',
			'da(it|ll|ng)',
			'dbte',
			'dc\-s',
			'devi',
			'dica',
			'dmob',
			'do(c|p)o',
			'ds(12|\-d)',
			'el(49|ai)',
			'em(l2|ul)',
			'er(ic|k0)',
			'esl8',
			'ez([4-7]0|os|wa|ze)',
			'fetc',
			'fly(\-|_)',
			'g1 u',
			'g560',
			'gene',
			'gf\-5',
			'g\-mo',
			'go(\.w|od)',
			'gr(ad|un)',
			'haie',
			'hcit',
			'hd\-(m|p|t)',
			'hei\-',
			'hi(pt|ta)',
			'hp( i|ip)',
			'hs\-c',
			'ht(c(\-| |_|a|g|p|s|t)|tp)',
			'hu(aw|tc)',
			'i\-(20|go|ma)',
			'i230',
			'iac( |\-|\/)',
			'ibro',
			'idea',
			'ig01',
			'ikom',
			'im1k',
			'inno',
			'ipaq',
			'iris',
			'ja(t|v)a',
			'jbro',
			'jemu',
			'jigs',
			'kddi',
			'keji',
			'kgt( |\/)',
			'klon',
			'kpt ',
			'kwc\-',
			'kyo(c|k)',
			'le(no|xi)',
			'lg( g|\/(k|l|u)|50|54|\-[a-w])',
			'libw',
			'lynx',
			'm1\-w',
			'm3ga',
			'm50\/',
			'ma(te|ui|xo)',
			'mc(01|21|ca)',
			'm\-cr',
			'me(rc|ri)',
			'mi(o8|oa|ts)',
			'mmef',
			'mo(01|02|bi|de|do|t(\-| |o|v)|zz)',
			'mt(50|p1|v )',
			'mwbp',
			'mywa',
			'n10[0-2]',
			'n20[2-3]',
			'n30(0|2)',
			'n50(0|2|5)',
			'n7(0(0|1)|10)',
			'ne((c|m)\-|on|tf|wf|wg|wt)',
			'nok(6|i)',
			'nzph',
			'o2im',
			'op(ti|wv)',
			'oran',
			'owg1',
			'p800',
			'pan(a|d|t)',
			'pdxg',
			'pg(13|\-([1-8]|c))',
			'phil',
			'pire',
			'pl(ay|uc)',
			'pn\-2',
			'po(ck|rt|se)',
			'prox',
			'psio',
			'pt\-g',
			'qa\-a',
			'qc(07|12|21|32|60|\-[2-7]|i\-)',
			'qtek',
			'r380',
			'r600',
			'raks',
			'rim9',
			'ro(ve|zo)',
			's55\/',
			'sa(ge|ma|mm|ms|ny|va)',
			'sc(01|h\-|oo|p\-)',
			'sdk\/',
			'se(c(\-|0|1)|47|mc|nd|ri)',
			'sgh\-',
			'shar',
			'sie(\-|m)',
			'sk\-0',
			'sl(45|id)',
			'sm(al|ar|b3|it|t5)',
			'so(ft|ny)',
			'sp(01|h\-|v\-|v )',
			'sy(01|mb)',
			't2(18|50)',
			't6(00|10|18)',
			'ta(gt|lk)',
			'tcl\-',
			'tdg\-',
			'tel(i|m)',
			'tim\-',
			't\-mo',
			'to(pl|sh)',
			'ts(70|m\-|m3|m5)',
			'tx\-9',
			'up(\.b|g1|si)',
			'utst',
			'v400',
			'v750',
			'veri',
			'vi(rg|te)',
			'vk(40|5[0-3]|\-v)',
			'vm40',
			'voda',
			'vulc',
			'vx(52|53|60|61|70|80|81|83|85|98)',
			'w3c(\-| )',
			'webc',
			'whit',
			'wi(g |nc|nw)',
			'wmlb',
			'wonu',
			'x700',
			'yas\-',
			'your',
			'zeto',
			'zte\-'
		);
		$isPhone = preg_match('/' . implode($uaPhone, '|') . '/i', $uaFull);
		$isMobile = preg_match('/' . implode($uaMobile, '|') . '/i', $uaStart);
		if ($isPhone or $isMobile) {
			return true;
		}
		return false;
	}
	/**
	 * Function to cut a string of characters apart using an argument limiter as the amount of characters to cut between
	 *
	 * @param	string	     html string
	 * @param	integer      limiter amount (ie: 50)
	 *
	 * @return	string       HTML representation of the string which has been cut
	 */
	function cutstring($string = '', $limit)
	{
		if (mb_strlen($string) > $limit) {
			$string = mb_substr($string, 0, $limit);
			if (($pos = mb_strrpos($string, ' ')) !== false) {
				$string = mb_substr($string, 0, $pos);
			}
			return $string;
		}
		return $string;
	}
	function convert_integer_to_words($x)
	{
		$nwords = array(
			'zero',
			'one',
			'two',
			'three',
			'four',
			'five',
			'six',
			'seven',
			'eight',
			'nine',
			'ten',
			'eleven',
			'twelve',
			'thirteen',
			'fourteen',
			'fifteen',
			'sixteen',
			'seventeen',
			'eighteen',
			'nineteen',
			'twenty',
			'01' => 'one',
			'02' => 'two',
			'03' => 'three',
			'04' => 'four',
			'05' => 'five',
			'06' => 'six',
			'07' => 'seven',
			'08' => 'eight',
			'09' => 'nine',
			'10' => 'ten',
			'11' => 'eleven',
			'12' => 'twelve',
			'13' => 'thirteen',
			'14' => 'fourteen',
			'15' => 'fifteen',
			'16' => 'sixteen',
			'17' => 'seventeen',
			'18' => 'eighteen',
			'19' => 'nineteen',
			'20' => 'twenty',
			30 => 'thirty',
			40 => 'forty',
			50 => 'fifty',
			60 => 'sixty',
			70 => 'seventy',
			80 => 'eighty',
			90 => 'ninety'
		);
		if (!is_numeric($x)) {
			$w = '#';
		} else if (fmod($x, 1) != 0) {
			$w = '#';
		} else {
			if ($x < 0) {
				$w = 'minus ';
				$x = -$x;
			} else {
				$w = '';
			}
			if ($x < 21) {
				$w .= ((isset($nwords[$x])) ? $nwords[$x] : '');
			} else if ($x < 100) {
				$w .= $nwords[10 * floor($x / 10)];
				$r = fmod($x, 10);
				if ($r > 0) {
					$w .= '-' . $nwords[$r];
				}
			} else if ($x < 1000) {
				$w .= $nwords[floor($x / 100)] . ' hundred';
				$r = fmod($x, 100);
				if ($r > 0) {
					$w .= ' and ' . $this->convert_integer_to_words($r);
				}
			} else if ($x < 1000000) {
				$w .= $this->convert_integer_to_words(floor($x / 1000)) . ' thousand';
				$r = fmod($x, 1000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$w .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			} else if ($x < 1000000000) {
				$w .= $this->convert_integer_to_words(floor($x / 1000000)) . ' million';
				$r = fmod($x, 1000000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$word .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			} else if ($x < 1000000000000) {
				$w .= $this->convert_integer_to_words(floor($x / 1000000000)) . ' billion';
				$r = fmod($x, 1000000000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$word .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			} else if ($x < 1000000000000000) {
				$w .= $this->convert_integer_to_words(floor($x / 1000000000000)) . ' trillion';
				$r = fmod($x, 1000000000000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$word .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			} else if ($x < 1000000000000000000) {
				$w .= $this->convert_integer_to_words(floor($x / 1000000000000000)) . ' quadrillion';
				$r = fmod($x, 1000000000000000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$word .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			} else {
				$w .= $this->convert_integer_to_words(floor($x / 1000000000000000000)) . ' quintillion';
				$r = fmod($x, 1000000000000000000);
				if ($r > 0) {
					$w .= ' ';
					if ($r < 100) {
						$word .= 'and ';
					}
					$w .= $this->convert_integer_to_words($r);
				}
			}
		}
		return $w;
	}
	function convert_number_to_words($number, $currencysymbol = '', $currencyid = 0)
	{
		if ($currencyid > 0) {
			if (isset($this->sheel->currency->currencies[$currencyid]['iscrypto']) and $this->sheel->currency->currencies[$currencyid]['iscrypto']) {
				$number = sprintf("%01.2f", $this->sheel->currency->convert_currency($this->sheel->config['globalserverlocale_defaultcurrency'], $number, $currencyid));
			}
		}
		$currencylabelsarray = array('dollars' => 'dollars', 'cents' => 'cents');
		if (!is_numeric($number)) {
			return false;
		}
		$number = sprintf("%01.2f", $number);
		$nums = explode('.', $number);
		$out = $this->convert_integer_to_words($nums[0]) . ' dollars';
		if (isset($nums[1]) and $nums[1] > 0) {
			$out .= ' and ' . $this->convert_integer_to_words($nums[1]) . ' cents';
		}
		$this->sheel->template->templateregistry['out'] = $out;
		$out = $this->sheel->template->parse_template_phrases('out');
		return $out;
	}
	/**
	 * Function to return a string where HTML entities have been converted to their original characters
	 *
	 * @param	string	     html string to parse
	 * @param	bool         convert unicode string back from HTML entities?
	 *
	 * @return	string
	 */
	function un_htmlspecialchars($text = '')
	{
		return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), $text);
	}
	function logout()
	{
		set_cookie('lastvisit', DATETIME24H);
		set_cookie('lastactivity', DATETIME24H);
		set_cookie('userid', '', false);
		set_cookie('password', '', false);
		set_cookie('inlineproduct', '', false);
		set_cookie('inlineservice', '', false);
		set_cookie('inlineprovider', '', false);
		set_cookie('collapse', '', false);
		$this->sheel->sessions->session_destroy(session_id());
		session_unset();
		session_destroy();
		session_write_close();
		setcookie(session_name(), '', 0, '/');
	}
	/**
	 * Function to return a session token for login calls to the XMLRPC
	 *
	 * @param	string	     username
	 * @param	string       password
	 * @param        string       api key for user
	 * @param        string       guest csrf token
	 * @param        boolean      force building of $_SESSION (default false, used for XML-RPC sessions when true)
	 *
	 * @return	string       Returns 0 for false/error
	 */
	function login($username = '', $password = '', $apikey = '', $guestcsrftoken = '', $forcebuildsession = false, $rememberuser = false, $ismobile = false, $devicetoken = '0')
	{
		$badusername = $badpassword = true;
		if (!empty($username)) {
			$sql = $this->sheel->db->query("
				SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode, l.languageiso, st.storeid, st.seourl
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
				LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				LEFT JOIN " . DB_PREFIX . "stores st ON su.user_id = st.user_id
				WHERE (u.username = '" . $this->sheel->db->escape_string($username) . "' OR u.email = '" . $this->sheel->db->escape_string($username) . "' OR u.companyname = '" . $this->sheel->db->escape_string($username) . "')
					AND sp.type = 'product'
					" . ((!empty($apikey)) ? "AND u.apikey = '" . $this->sheel->db->escape_string($apikey) . "' AND u.useapi = '1'" : "") . "
				GROUP BY u.username
				LIMIT 1
			");
			if ($this->sheel->db->num_rows($sql) > 0) {
				$userinfo = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				$md5pass = $md5pass_utf = md5($password);
				$badusername = $badpassword = false;
				if (
					$userinfo['password'] != iif($password and !$md5pass, md5(md5($password) . $userinfo['salt']), '') and
					$userinfo['password'] != md5($md5pass . $userinfo['salt']) and
					$userinfo['password'] != iif($md5pass_utf, md5($md5pass_utf . $userinfo['salt']), '')
				) {
					$badpassword = true;
				}
			} else {
			}
			if (!$badusername and !$badpassword) {
				// default shipping & billing profile
				$userinfo['shipprofileid'] = $this->sheel->shipping->fetch_default_ship_profileid($userinfo['user_id']);
				$userinfo['billprofileid'] = $this->sheel->shipping->fetch_default_bill_profileid($userinfo['user_id']);
				$csrf = $this->sheel->sessions->build_user_session($userinfo, false, true, $forcebuildsession, $rememberuser, $ismobile, $devicetoken);
				$this->sheel->log_event($userinfo['user_id'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), $userinfo['username'] . ' just signed in (XMLRPC)', $userinfo['username'] . ' just signed into the marketplace (XMLRPC).');
				return $csrf;
			} else {
				$this->sheel->log_event(0, basename(__FILE__), "failure\n", $username . ' could not sign-in (XMLRPC).', $username . ' could not sign-in (XMLRPC).');
			}
		}
		if (isset($this->username_errors) and count($this->username_errors) > 0) {
			$this->sheel->log_event(0, basename(__FILE__), "failure\n" . $this->sheel->array2string($this->username_errors), $username . ' could not sign-in (XMLRPC).', $username . ' could not sign-in (XMLRPC).');
		}
		return 0;
	}

	function get_files($folder)
	{
		$this->valid_files = array();
		$files = scandir($folder);
		foreach ($files as $file) {
			if (substr($file, 0, 1) == "." || !is_readable($folder . '/' . $file)) {
				continue;
			}
			if (is_dir($file)) {
				array_merge($this->valid_files, $this->get_files($folder . '/' . $file));
			} else if ($file == 'attachments') {
				$this->valid_files[] = $folder . '/' . $file;
				$this->valid_files[] = $folder . '/' . $file . '/images';
				//array_merge($this->valid_files, $this->get_files($folder . '/' . $file . '/images'));
			} else {
				$this->valid_files[] = $folder . '/' . $file;
			}
		}
		return $this->valid_files;
	}
	function create_gdpr_archive($userid = 0, $data = array())
	{
		if ($userid > 0) {
			$foldername = DIR_TMP . $userid;
			if (is_dir($foldername)) { // remove folder first
				$this->sheel->attachment->recursive_remove_directory($foldername);
			}
			$oldumask = umask(0);
			mkdir($foldername, 0755);
			mkdir($foldername . '/data', 0755);
			foreach ($data as $folder) {
				mkdir($foldername . '/data/' . $folder, 0755);
				// fetch data for this folder
				$userdata = $this->fetch_user_gdpr_data($userid, $folder);
				// write user data and move to next folder
				if (!empty($userdata['xml'])) { // write file..
					$fp = fopen($foldername . '/data/' . $folder . '/data.xml', 'w+');
					fwrite($fp, $userdata['xml']);
					fclose($fp);
					if ($folder == 'attachments') {
						mkdir($foldername . '/data/' . $folder . '/images', 0755);
						// copy all original images into folder
					}
				}
			}
			umask($oldumask);
			// zip the file...
			$valid_files = $this->get_files($foldername . '/data');
			$zipfile = 'uid-' . $userid . '-gdpr-download.zip';
			if (count($valid_files)) {
				$zip = new ZipArchive();
				if ($zip->open(DIR_TMP . $zipfile, ZIPARCHIVE::CREATE)) {
					foreach ($valid_files as $file) { // add the files
						if (basename($file) == 'images') {
							// loop through images folder
							/*$valid_imgfiles = $this->get_files($file);
							if (count($valid_imgfiles))
							{
							foreach ($valid_imgfiles AS $imgfile)
							{
							$zip->addFile($imgfile, basename($imgfile) . '/' . basename($imgfile));
							}
							}*/
						} else {
							$zip->addFile($file . '/data.xml', basename($file) . '/' . basename($file) . '.xml');
						}
					}
					$zip->close();
				}
			}
			if (is_dir($foldername)) { // remove folder
				$this->sheel->attachment->recursive_remove_directory($foldername);
			}
			return DIR_TMP . $zipfile;
		}
		return false;
	}
	function fetch_user_gdpr_data($userid = 0, $item = '')
	{
		$data['xml'] = ''; // working
		$data['csv'] = '';
		$data['html'] = '';
		switch ($item) {
			case 'attachments': {
					$data['xml'] .= $this->sheel->xml->add_group('attachments');
					$sql = $this->sheel->db->query("
					SELECT filename, filesize, date, ipaddress, filehash, attachtype, category_id, project_id, width, height
					FROM " . DB_PREFIX . "attachment
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('attachment', array('type' => $res['attachtype']));
						$data['xml'] .= $this->sheel->xml->add_tag('filehash', $res['filehash']);
						$data['xml'] .= $this->sheel->xml->add_tag('filename', $res['filename']);
						$data['xml'] .= $this->sheel->xml->add_tag('filesize', $res['filesize']);
						$data['xml'] .= $this->sheel->xml->add_tag('width', $res['width']);
						$data['xml'] .= $this->sheel->xml->add_tag('height', $res['height']);
						$data['xml'] .= $this->sheel->xml->add_tag('date', $res['date']);
						$data['xml'] .= $this->sheel->xml->add_tag('url', '#');
						$data['xml'] .= $this->sheel->xml->add_tag('categoryid', $res['category_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('itemid', $res['project_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('ipaddress', $res['ipaddress']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'emails': {
					$data['xml'] .= $this->sheel->xml->add_group('emails');
					$sql = $this->sheel->db->query("
					SELECT email, subject, body, date
					FROM " . DB_PREFIX . "emaillog
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('email');
						$data['xml'] .= $this->sheel->xml->add_tag('to', $res['email']);
						$data['xml'] .= $this->sheel->xml->add_tag('subject', $res['subject']);
						$data['xml'] .= $this->sheel->xml->add_tag('body', $res['body']);
						$data['xml'] .= $this->sheel->xml->add_tag('date', $res['date']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'feedback': {
					$data['xml'] .= $this->sheel->xml->add_group('feedback');
					$sql = $this->sheel->db->query("
					SELECT title, buynoworderid, project_id, comments, response, date_added, type
					FROM " . DB_PREFIX . "feedback
					WHERE for_user_id = '" . intval($userid) . "' OR from_user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('result', array('fortype' => $res['type']));
						$data['xml'] .= $this->sheel->xml->add_tag('orderid', $res['buynoworderid']);
						$data['xml'] .= $this->sheel->xml->add_tag('title', $res['title']);
						$data['xml'] .= $this->sheel->xml->add_tag('itemid', $res['project_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('comments', $res['comments']);
						$data['xml'] .= $this->sheel->xml->add_tag('response', $res['response']);
						$data['xml'] .= $this->sheel->xml->add_tag('date', $res['date_added']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'items': {
					$data['xml'] .= $this->sheel->xml->add_group('items');
					$sql = $this->sheel->db->query("
					SELECT project_title, description, project_id, date_added, date_starts, date_end, status, bids, views, buynow_qty, buynow_price, buynow_purchases, currencyid, reserve_price, currentprice
					FROM " . DB_PREFIX . "projects
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('item');
						$data['xml'] .= $this->sheel->xml->add_tag('title', o($res['project_title']));
						$data['xml'] .= $this->sheel->xml->add_tag('description', o($res['description']));
						$data['xml'] .= $this->sheel->xml->add_tag('itemid', $res['project_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('currencyid', $res['currencyid']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateadded', $res['date_added']);
						$data['xml'] .= $this->sheel->xml->add_tag('datestart', $res['date_starts']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateend', $res['date_end']);
						$data['xml'] .= $this->sheel->xml->add_tag('price', $res['buynow_price']);
						$data['xml'] .= $this->sheel->xml->add_tag('currentbid', $res['currentprice']);
						$data['xml'] .= $this->sheel->xml->add_tag('reserveprice', $res['reserve_price']);
						$data['xml'] .= $this->sheel->xml->add_tag('bids', $res['bids']);
						$data['xml'] .= $this->sheel->xml->add_tag('sold', $res['buynow_purchases']);
						$data['xml'] .= $this->sheel->xml->add_tag('qty', $res['buynow_qty']);
						$data['xml'] .= $this->sheel->xml->add_tag('views', $res['views']);
						$data['xml'] .= $this->sheel->xml->add_tag('status', $res['status']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'messages': {
					$data['xml'] .= $this->sheel->xml->add_group('messages');
					$sql = $this->sheel->db->query("
					SELECT id, project_id, message, subject, datetime
					FROM " . DB_PREFIX . "pmb
					WHERE from_id = '" . intval($userid) . "' OR to_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('message');
						$data['xml'] .= $this->sheel->xml->add_tag('id', $res['id']);
						$data['xml'] .= $this->sheel->xml->add_tag('itemid', $res['project_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('subject', $res['subject']);
						$data['xml'] .= $this->sheel->xml->add_tag('message', $res['message']);
						$data['xml'] .= $this->sheel->xml->add_tag('date', $res['datetime']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'personal_profile': {
					$data['xml'] .= $this->sheel->xml->add_group('personalprofiles');
					$data['xml'] .= $this->sheel->xml->add_group('profile', array('type' => 'primary'));
					$data['xml'] .= $this->sheel->xml->add_tag('userid', $_SESSION['sheeldata']['user']['userid']);
					$data['xml'] .= $this->sheel->xml->add_tag('username', $_SESSION['sheeldata']['user']['username']);
					$data['xml'] .= $this->sheel->xml->add_tag('email', $_SESSION['sheeldata']['user']['email']);
					$data['xml'] .= $this->sheel->xml->add_tag('dateofbirth', $_SESSION['sheeldata']['user']['dob']);
					$data['xml'] .= $this->sheel->xml->close_group();
					$sql = $this->sheel->db->query("
					SELECT ipaddress, first_name, last_name, address, address2, city, state, country, phone, email, zipcode, dateadded, type
					FROM " . DB_PREFIX . "shipping_profiles
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('profile', array('type' => $res['type']));
						$data['xml'] .= $this->sheel->xml->add_tag('firstname', $res['first_name']);
						$data['xml'] .= $this->sheel->xml->add_tag('lastname', $res['last_name']);
						$data['xml'] .= $this->sheel->xml->add_tag('address', $res['address']);
						$data['xml'] .= $this->sheel->xml->add_tag('address2', $res['address2']);
						$data['xml'] .= $this->sheel->xml->add_tag('city', $res['city']);
						$data['xml'] .= $this->sheel->xml->add_tag('state', $res['state']);
						$data['xml'] .= $this->sheel->xml->add_tag('zipcode', $res['zipcode']);
						$data['xml'] .= $this->sheel->xml->add_tag('country', $res['country']);
						$data['xml'] .= $this->sheel->xml->add_tag('phone', $res['phone']);
						$data['xml'] .= $this->sheel->xml->add_tag('email', $res['email']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateadded', $res['dateadded']);
						$data['xml'] .= $this->sheel->xml->add_tag('ipaddress', $res['ipaddress']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'public_profile': {
					$data['xml'] .= $this->sheel->xml->add_group('publicprofile');
					$data['xml'] .= $this->sheel->xml->add_group('store');
					$sql = $this->sheel->db->query("
					SELECT s.storename, s.seourl, s.started, a.filename, a.ipaddress
					FROM " . DB_PREFIX . "stores s
					LEFT JOIN " . DB_PREFIX . "attachment a ON (s.logo_attachid = a.attachid)
					WHERE s.user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_tag('name', $res['storename']);
						$data['xml'] .= $this->sheel->xml->add_tag('url', HTTPS_SERVER . $res['seourl'] . '/');
						$data['xml'] .= $this->sheel->xml->add_tag('logo', $res['filename']);
						$data['xml'] .= $this->sheel->xml->add_tag('opensince', $res['started']);
						$data['xml'] .= $this->sheel->xml->add_tag('ipaddress', $res['ipaddress']);
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->add_group('selling');
					$sql = $this->sheel->db->query("
					SELECT username, profilevideourl, profileintro, date_added, registersource, ipaddress
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_tag('joindate', $res['date_added']);
						$data['xml'] .= $this->sheel->xml->add_tag('avatar', '');
						$data['xml'] .= $this->sheel->xml->add_tag('username', $res['username']);
						$data['xml'] .= $this->sheel->xml->add_tag('videourl', $res['profilevideourl']);
						$data['xml'] .= $this->sheel->xml->add_tag('aboutme', $res['profileintro']);
						$data['xml'] .= $this->sheel->xml->add_tag('ipaddress', $res['ipaddress']);
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'saved_lists': {
					$data['xml'] .= $this->sheel->xml->add_group('saved');
					// categories
					$data['xml'] .= $this->sheel->xml->add_group('category');
					$sql = $this->sheel->db->query("
					SELECT notifyproductscats
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_tag('ids', $res['notifyproductscats']);
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					// saved searches
					$data['xml'] .= $this->sheel->xml->add_group('search');
					$sql = $this->sheel->db->query("
					SELECT searchid, searchoptions, searchoptionstext, title, added, lastsent, subscribed, cattype
					FROM " . DB_PREFIX . "search_favorites
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('query', array('type' => $res['cattype']));
						$data['xml'] .= $this->sheel->xml->add_tag('searchoptions', $res['searchoptions']);
						$data['xml'] .= $this->sheel->xml->add_tag('searchoptionstext', $res['searchoptionstext']);
						$data['xml'] .= $this->sheel->xml->add_tag('title', $res['title']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateadded', $res['added']);
						$data['xml'] .= $this->sheel->xml->add_tag('lastsent', $res['lastsent']);
						$data['xml'] .= $this->sheel->xml->add_tag('subscribed', $res['subscribed']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					// sellers
					$data['xml'] .= $this->sheel->xml->add_group('sellers');
					$sql = $this->sheel->db->query("
					SELECT w.watching_user_id, w.comment, w.dateadded, u.username
					FROM " . DB_PREFIX . "watchlist w
					LEFT JOIN " . DB_PREFIX . "users u ON (w.watching_user_id = u.user_id)
					WHERE w.user_id = '" . intval($userid) . "'
						AND w.state = 'mprovider'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('seller');
						$data['xml'] .= $this->sheel->xml->add_tag('sellerid', $res['watching_user_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('username', $res['username']);
						$data['xml'] .= $this->sheel->xml->add_tag('comment', $res['comment']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateadded', $res['dateadded']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					// items
					$data['xml'] .= $this->sheel->xml->add_group('items');
					$sql = $this->sheel->db->query("
					SELECT w.watching_project_id, w.comment, w.dateadded, p.project_title
					FROM " . DB_PREFIX . "watchlist w
					LEFT JOIN " . DB_PREFIX . "projects p ON (w.watching_project_id = p.project_id)
					WHERE w.user_id = '" . intval($userid) . "'
						AND w.state = 'auction'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('item');
						$data['xml'] .= $this->sheel->xml->add_tag('itemtitle', $res['project_title']);
						$data['xml'] .= $this->sheel->xml->add_tag('itemid', $res['watching_project_id']);
						$data['xml'] .= $this->sheel->xml->add_tag('comment', $res['comment']);
						$data['xml'] .= $this->sheel->xml->add_tag('dateadded', $res['dateadded']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group(); // items
					$data['xml'] .= $this->sheel->xml->close_group(); // saved
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'tax_profiles': {
					$data['xml'] .= $this->sheel->xml->add_group('taxprofiles');
					$sql = $this->sheel->db->query("
					SELECT country, state, label, rate, currencyid
					FROM " . DB_PREFIX . "tax_profiles
					WHERE user_id = '" . intval($userid) . "'
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('profile');
						$data['xml'] .= $this->sheel->xml->add_tag('country', $res['country']);
						$data['xml'] .= $this->sheel->xml->add_tag('state', $res['state']);
						$data['xml'] .= $this->sheel->xml->add_tag('label', $res['label']);
						$data['xml'] .= $this->sheel->xml->add_tag('rate', $res['rate']);
						$data['xml'] .= $this->sheel->xml->add_tag('currencyid', $res['currencyid']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
			case 'transactions': {
					$data['xml'] .= $this->sheel->xml->add_group('transactions');
					$sql = $this->sheel->db->query("
					SELECT debit, credit, currencyid, currencyrate, datetime, description
					FROM " . DB_PREFIX . "transactions
					WHERE userid = '" . intval($userid) . "'
					ORDER BY id ASC
				");
					while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
						$data['xml'] .= $this->sheel->xml->add_group('transaction');
						$data['xml'] .= $this->sheel->xml->add_tag('debit', $res['debit']);
						$data['xml'] .= $this->sheel->xml->add_tag('credit', $res['credit']);
						$data['xml'] .= $this->sheel->xml->add_tag('currencyid', $res['currencyid']);
						$data['xml'] .= $this->sheel->xml->add_tag('currencyrate', $res['currencyrate']);
						$data['xml'] .= $this->sheel->xml->add_tag('date', $res['datetime']);
						$data['xml'] .= $this->sheel->xml->add_tag('description', $res['description']);
						$data['xml'] .= $this->sheel->xml->close_group();
					}
					$data['xml'] .= $this->sheel->xml->close_group();
					$data['xml'] .= $this->sheel->xml->print_xml_inline();
					break;
				}
		}
		return $data;
	}
}
?>
