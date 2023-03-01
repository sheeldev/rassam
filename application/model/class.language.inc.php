<?php
/**
 * Language class to perform the majority of language functions in sheel.
 *
 * @package      sheel\Language
 * @version      1.0.0.0
 * @author       sheel
 */
class language
{
	protected $sheel;
	var $languages = array(
		'English' => 'English',
		'Arabic' => 'Arabic',
		'Argentinian' => 'Argentinian',
		'Basque' => 'Basque',
		'Belarusian' => 'Belarusian',
		'Bulgarian' => 'Bulgarian',
		'Catalan' => 'Catalan',
		'Chinese' => 'Chinese',
		'Croatian' => 'Croatian',
		'Czech' => 'Czech',
		'Danish' => 'Danish',
		'Estonian' => 'Estonian',
		'Finnish' => 'Finnish',
		'French' => 'French',
		'German' => 'German',
		'Georgian' => 'Georgian',
		'Greek' => 'Greek',
		'Hebrew' => 'Hebrew',
		'Hindi' => 'Hindi',
		'Hungarian' => 'Hungarian',
		'Indonesian' => 'Indonesian',
		'Italian' => 'Italian',
		'Japanese' => 'Japanese',
		'Kurdish' => 'Kurdish',
		'Korean' => 'Korean',
		'Lithuanian' => 'Lithuanian',
		'Latvian' => 'Latvian',
		'Macedonian' => 'Macedonian',
		'Mandarin Chinese' => 'Mandarin Chinese',
		'Netherlands' => 'Netherlands',
		'Norwegian' => 'Norwegian',
		'Persian' => 'Persian',
		'Polish' => 'Polish',
		'Portuguese' => 'Portuguese',
		'Romanian' => 'Romanian',
		'Russian' => 'Russian',
		'Serbian' => 'Serbian',
		'Slovak' => 'Slovak',
		'Slovenian' => 'Slovenian',
		'Spanish' => 'Spanish',
		'Swedish' => 'Swedish',
		'Tatar' => 'Tatar',
		'Thai' => 'Thai',
		'Turkish' => 'Turkish',
		'Ukrainian' => 'Ukrainian',
		'Urdu' => 'Urdu',
		'Vietnamese' => 'Vietnamese'
	);
	var $charactersets = array(
		'ASMO-708' => 'Arabic (ASMO 708)',
		'DOS-720' => 'Arabic (DOS)',
		'iso-8859-6' => 'Arabic (ISO)',
		'x-mac-arabic' => 'Arabic (Mac)',
		'windows-1256' => 'Arabic (Windows)',
		'ibm775' => 'Baltic (DOS)',
		'iso-8859-4' => 'Baltic (ISO)',
		'windows-1257' => 'Baltic (Windows)',
		'ibm852' => 'Central European (DOS)',
		'iso-8859-2' => 'Central European (ISO)',
		'x-mac-ce' => 'Central European (Mac)',
		'windows-1250' => 'Central European (Windows)',
		'EUC-CN' => 'Chinese Simplified (EUC)',
		'gb2312' => 'Chinese Simplified (GB2312)',
		'hz-gb-2312' => 'Chinese Simplified (HZ)',
		'x-mac-chinesesimp' => 'Chinese Simplified (Mac)',
		'big5' => 'Chinese Traditional (Big5)',
		'x-Chinese-CNS' => 'Chinese Traditional (CNS)',
		'x-Chinese-Eten' => 'Chinese Traditional (Eten)',
		'x-mac-chinesetrad' => 'Chinese Traditional (Mac)',
		'cp866' => 'Cyrillic (DOS)',
		'iso-8859-5' => 'Cyrillic (ISO)',
		'koi8-r' => 'Cyrillic (KOI8-R)',
		'koi8-u' => 'Cyrillic (KOI8-U)',
		'x-mac-cyrillic' => 'Cyrillic (Mac)',
		'windows-1251' => 'Cyrillic (Windows)',
		'x-Europa' => 'Europa',
		'x-IA5-German' => 'German (IA5)',
		'ibm737' => 'Greek (DOS)',
		'iso-8859-7' => 'Greek (ISO)',
		'x-mac-greek' => 'Greek (Mac)',
		'windows-1253' => 'Greek (Windows)',
		'ibm869' => 'Greek, Modern (DOS)',
		'DOS-862' => 'Hebrew (DOS)',
		'iso-8859-8-i' => 'Hebrew (ISO-Logical)',
		'iso-8859-8' => 'Hebrew (ISO-Visual)',
		'x-mac-hebrew' => 'Hebrew (Mac)',
		'windows-1255' => 'Hebrew (Windows)',
		'x-EBCDIC-Arabic' => 'IBM EBCDIC (Arabic) ',
		'x-EBCDIC-CyrillicRussian' => 'IBM EBCDIC (Cyrillic Russian)',
		'x-EBCDIC-CyrillicSerbianBulgarian' => 'IBM EBCDIC (Cyrillic Serbian-Bulgarian)',
		'x-EBCDIC-DenmarkNorway' => 'IBM EBCDIC (Denmark-Norway)',
		'x-ebcdic-denmarknorway-euro' => 'IBM EBCDIC (Denmark-Norway-Euro)',
		'x-EBCDIC-FinlandSweden' => 'IBM EBCDIC (Finland-Sweden)',
		'x-ebcdic-finlandsweden-euro' => 'IBM EBCDIC (Finland-Sweden-Euro)',
		'x-ebcdic-finlandsweden-euro' => 'IBM EBCDIC (Finland-Sweden-Euro)',
		'x-ebcdic-france-euro' => 'IBM EBCDIC (France-Euro)',
		'x-EBCDIC-Germany' => 'IBM EBCDIC (Germany) ',
		'x-ebcdic-germany-euro' => 'IBM EBCDIC (Germany-Euro) ',
		'x-EBCDIC-GreekModern' => 'IBM EBCDIC (Greek Modern) ',
		'x-EBCDIC-Greek' => 'IBM EBCDIC (Greek) ',
		'x-EBCDIC-Hebrew' => 'IBM EBCDIC (Hebrew) ',
		'x-EBCDIC-Icelandic' => 'IBM EBCDIC (Icelandic) ',
		'x-ebcdic-icelandic-euro' => 'IBM EBCDIC (Icelandic-Euro)',
		'x-ebcdic-international-euro' => 'IBM EBCDIC (International-Euro) ',
		'x-EBCDIC-Italy' => 'IBM EBCDIC (Italy) ',
		'x-ebcdic-italy-euro' => 'IBM EBCDIC (Italy-Euro) ',
		'x-EBCDIC-JapaneseAndKana' => 'IBM EBCDIC (Japanese and Japanese Katakana) ',
		'x-EBCDIC-JapaneseAndJapaneseLatin' => 'IBM EBCDIC (Japanese and Japanese-Latin) ',
		'x-EBCDIC-JapaneseAndUSCanada' => 'IBM EBCDIC (Japanese and US-Canada) ',
		'x-EBCDIC-JapaneseKatakana' => 'IBM EBCDIC (Japanese katakana)',
		'x-EBCDIC-KoreanAndKoreanExtended' => 'IBM EBCDIC (Korean and Korean Extended)',
		'x-EBCDIC-KoreanExtended' => 'IBM EBCDIC (Korean Extended) ',
		'CP870' => 'IBM EBCDIC (Multilingual Latin-2)',
		'x-EBCDIC-SimplifiedChinese' => 'IBM EBCDIC (Simplified Chinese) ',
		'X-EBCDIC-Spain' => 'IBM EBCDIC (Spain) ',
		'x-ebcdic-spain-euro' => 'IBM EBCDIC (Spain-Euro) ',
		'x-EBCDIC-Thai' => 'IBM EBCDIC (Thai)',
		'x-EBCDIC-TraditionalChinese' => 'IBM EBCDIC (Traditional Chinese) ',
		'CP1026' => 'IBM EBCDIC (Turkish Latin-5) ',
		'x-EBCDIC-Turkish' => 'IBM EBCDIC (Turkish)',
		'x-EBCDIC-UK' => 'IBM EBCDIC (UK)',
		'x-ebcdic-uk-euro' => 'IBM EBCDIC (UK-Euro) ',
		'ebcdic-cp-us' => 'IBM EBCDIC (US-Canada) ',
		'x-ebcdic-cp-us-euro' => 'IBM EBCDIC (US-Canada-Euro) ',
		'ibm861' => 'Icelandic (DOS) ',
		'x-mac-icelandic' => 'Icelandic (Mac) ',
		'x-iscii-as' => 'ISCII Assamese',
		'x-iscii-be' => 'ISCII Bengali',
		'x-iscii-de' => 'ISCII Devanagari',
		'x-iscii-gu' => 'ISCII Gujarathi',
		'x-iscii-ka' => 'ISCII Kannada',
		'x-iscii-ma' => 'ISCII Malayalam ',
		'x-iscii-or' => 'ISCII Oriya',
		'x-iscii-pa' => 'ISCII Panjabi',
		'x-iscii-ta' => 'ISCII Tamil ',
		'x-iscii-te' => 'ISCII Telugu',
		'euc-jp' => 'Japanese (EUC)',
		'iso-2022-jp' => 'Japanese (JIS)',
		'iso-2022-jp' => 'Japanese (JIS-Allow 1 byte Kana - SO/SI)',
		'csISO2022JP' => 'Japanese (JIS-Allow 1 byte Kana)',
		'x-mac-japanese' => 'Japanese (Mac)',
		'shift_jis' => 'Japanese (Shift-JIS) ',
		'ks_c_5601-1987' => 'Korean',
		'euc-kr' => 'Korean (EUC)',
		'iso-2022-kr' => 'Korean (ISO)',
		'Johab' => 'Korean (Johab)',
		'x-mac-korean' => 'Korean (Mac)',
		'iso-8859-3' => 'Latin 3 (ISO)',
		'iso-8859-15' => 'Latin 9 (ISO)',
		'x-IA5-Norwegian' => 'Norwegian (IA5)',
		'IBM437' => 'OEM United States ',
		'x-IA5-Swedish' => 'Swedish (IA5) ',
		'windows-874' => 'Thai (Windows)',
		'ibm857' => 'Turkish (DOS)',
		'iso-8859-9' => 'Turkish (ISO)',
		'x-mac-turkish' => 'Turkish (Mac)',
		'windows-1254' => 'Turkish (Windows)',
		'utf-8" selected="selected' => 'Unicode (UTF-8)',
		'utf-7' => 'Unicode (UTF-7)',
		'unicodeFFFE' => 'Unicode (Big-Endian)',
		'unicode' => 'Unicode',
		'us-ascii' => 'US-ASCII',
		'ibm850' => 'Western European (DOS)',
		'x-IA5' => 'Western European (IA5)',
		'iso-8859-1' => 'Western European (ISO)',
		'macintosh' => 'Western European (Mac)',
		'Windows-1252' => 'Western European (Windows)',
		'windows-1258' => 'Vietnamese (Windows)',
	);
	/**
	 * array holding language cache
	 */
	var $cache = array();
	/**
	 * Constructor
	 */
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements, alphabet
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$this->cache[$res['languageid']] = $res;
			}
			unset($res);
		}
	}
	/**
	 * Function to return the language phrases cache array from the datastore.
	 * This function is called just after session_start() within global.php
	 *
	 * @return      array       $phrase array
	 */
	function init_phrases()
	{
		$this->sheel->timer->start();
		$phrase = array();
		$varnamein = $query = '';
		$slng = (isset($_SESSION['sheeldata']['user']['slng']) and !empty($_SESSION['sheeldata']['user']['slng'])) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$phrasesearch = array('{{site_name}}', '{{max_payment_days}}');
		$phrasereplace = array(SITE_NAME, $this->sheel->config['invoicesystem_maximumpaymentdays']);
		$ajax_phrases = array('_continue', '_you_have_selected_the_following_category', '_youve_selected_a_category_click_continue_button', '_no_category_specifics_exist_in_this_category', '_no_parent_category', '_assign_to_all_categories', '_remove', '_you_can', '_add_another_category_to_your_list');
		$watchlist_phrases = array('_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first', '_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first');
		$payment_phrases = array('_paypal', '_master_card', '_money_order', '_personal_check', '_visa', '_see_description_for_my_accepted_payment_methods');
		$varnames = array_merge($ajax_phrases, $watchlist_phrases, $payment_phrases);
		$varnames = array_values(array_unique($varnames));
		foreach ($varnames as $key => $value) {
			$varnamein .= "'" . $this->sheel->db->escape_string($value) . "', ";
		}
		$varnamein = !empty($varnamein) ? 'varname IN (' . substr($varnamein, 0, strlen($varnamein) - 2) . ')' : '';
		if (!empty($varnamein)) {
			$query = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.varname, p.text_" . $slng . " AS text
				FROM " . DB_PREFIX . "language_phrases p
				WHERE " . $varnamein . "
			", 0, null, __FILE__, __LINE__);
		}
		if ($this->sheel->db->num_rows($query) > 0) {
			while ($cache = $this->sheel->db->fetch_array($query, DB_ASSOC)) {
				$phrase[$cache['varname']] = str_replace($phrasesearch, $phrasereplace, stripslashes($this->sheel->common->un_htmlspecialchars($cache['text'])));
			}
			unset($cache);
		}
		unset($query, $varnamein, $queryextra, $cacheid);
		$this->sheel->timer->stop();
		DEBUG("init_phrases()", 'FUNCTION', $this->sheel->timer->get(), '');
		return $phrase;
	}
	/**
	 * Function to construct a phrase using replacement phrases
	 *
	 * @param       string      phrase string containing [x]'s (Example: {_some_phrase_x})
	 * @param       mixed       array or string containing our replacements
	 *
	 * @return      array       $phrase array
	 */
	function construct_phrase($var, $replacements)
	{
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$var = str_replace(array('{', '}'), array('', ''), $var); // _some_phrase_x
		$var = $this->sheel->db->fetch_field(DB_PREFIX . "language_phrases", "varname = '" . $this->sheel->db->escape_string($var) . "'", "text_" . $this->sheel->db->escape_string($slng) . "", "1");
		$result = $result2 = '';
		if (is_array($replacements)) {
			$k = 0;
			$max = count($replacements); // 2
			for ($i = 0; $i < mb_strlen($var); $i++) {
				if (mb_substr($var, $i, 3) == '[x]') {
					$result .= $replacements[$k++];
					if ($k > $max) {
						return '{_incorrect_number_of_replacements_provided_to_construct_phrase_function}';
					}
					$i += 2;
				} else {
					$result .= mb_substr($var, $i, 1);
				}
			}
		} else {
			for ($i = 0; $i < mb_strlen($var); $i++) {
				if (mb_substr($var, $i, 3) == '[x]') {
					$result .= $replacements;
					$i += 2;
				} else {
					$result .= mb_substr($var, $i, 1);
				}
			}
		}
		return $result;
	}

	/**
	 * @return      array
	 */
	function print_links($returnhtml = false)
	{
		$languagecount = 0;
		$currenturl = PAGEURL;
		$languageslinks = array();
		$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, languagecode, title, canselect, languageiso
					FROM " . DB_PREFIX . "language
			", 0, null, __FILE__, __LINE__);
		$html = '';
		$html .= '<ul>';
		while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
			if ($res['canselect']) {
				$languagecount++;
				$currenturl = $this->sheel->seo->rewrite_url($currenturl, 'language=' . $res['languagecode']);
				if (strrchr(urldecode($currenturl), '?') == false) {
					$currenturlx = $currenturl . '?language=' . $res['languagecode'];
				} else {
					$currenturlx = $currenturl . '&language=' . $res['languagecode'];
				}
				if (!$returnhtml) {
					$languageslinks['title'] = ($res['title']);
					$languageslinks['languageiso'] = ($res['languageiso']);
					$languageslinks['url'] = $currenturlx;
				}
				else {
					$html .= '<li><a href="' . $currenturlx . '" rel="nofollow">' . o($res['title']) . ' &ndash; ' . strtoupper($res['languageiso']) . '</a></li>';
				}
				
			}
		}
		$html .= '</ul>';
		if ($languagecount == 0) {
			$html = '';
		}
		if ($returnhtml) {
			return $html;
		}
		return $languageslinks;
	}
	/**
	 * Function to print a language code like english or german, etc
	 *
	 * @param       integer      (optional) language id
	 * @return      string       HTML formatted language pulldown menu
	 */
	function print_language_code($languageid = '')
	{
		$langid = !empty($languageid) ? intval($languageid) : $this->sheel->config['globalserverlanguage_defaultlanguage'];
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($langid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['languagecode'];
		}
		return 'english';
	}
	/**
	 * Function to print a language code like english or german, etc
	 *
	 * @param       integer      (optional) language id
	 * @return      string       HTML formatted language pulldown menu
	 */
	function print_language_iso($languageid = '')
	{
		$langid = !empty($languageid) ? intval($languageid) : $this->sheel->config['globalserverlanguage_defaultlanguage'];
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageiso
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($langid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return $res['languageiso'];
		}
		return 'en';
	}
	/**
	 * Function to print a short version of the language code like eng or ger, etc
	 *
	 * @return      string       HTML formatted default language pulldown menu
	 */
	function print_short_language_code()
	{
		if (!empty($this->sheel->config['globalserverlanguage_defaultlanguage']) and $this->sheel->config['globalserverlanguage_defaultlanguage'] > 0) {
			$sql = $this->sheel->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                                FROM " . DB_PREFIX . "language
                                WHERE languageid = '" . $this->sheel->config['globalserverlanguage_defaultlanguage'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return mb_substr($res['languagecode'], 0, 3);
			}
		}
		return 'eng';
	}
	/**
	 * Function to count the number of phrases within a particular phrase group
	 *
	 * @param       integer      phrase group
	 *
	 * @return      integer      Returns the number of phrases in the phrasegroup
	 */
	function count_phrases_in_phrasegroup($phrasegroup = '')
	{
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE phrasegroup = '" . $this->sheel->db->escape_string($phrasegroup) . "'
                ", 0, null, __FILE__, __LINE__);
		$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
		return (int) $res['count'];
	}
	/**
	 * Function to count the number of un-phrased phrases within a particular phrase group
	 *
	 * @param       integer      phrase group
	 * @param       string       short language code
	 *
	 * @return      integer      Returns the number of un-phrased phrases in the phrasegroup
	 */
	function count_unphrased_in_phrasegroup($phrasegroup = '', $slng)
	{
		if (isset($slng) and $slng != 'eng') {
			$sql = $this->sheel->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
                                FROM " . DB_PREFIX . "language_phrases
                                WHERE phrasegroup = '" . $this->sheel->db->escape_string($phrasegroup) . "'
                                    AND text_$slng = text_eng
                        ", 0, null, __FILE__, __LINE__);
			$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			return ', ' . (int) $res['count'] . ' untranslated';
		}
		return '';
	}
	/**
	 * Function to print the site's default language id
	 *
	 * @return      integer       Returns default language id
	 */
	function fetch_default_languageid()
	{
		if ($this->sheel->config['globalserverlanguage_defaultlanguage'] > 0) {
			return intval($this->sheel->config['globalserverlanguage_defaultlanguage']);
		}
		return 'eng';
	}
	function lang_count_canselect()
	{
		$count = 1;
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid
                        FROM " . DB_PREFIX . "language
			WHERE canselect = '1'
                ", 0, null, __FILE__, __LINE__);
		$count = $this->sheel->db->num_rows($sql);
		return $count;
	}
	/**
	 * Function to fetch the seo replacement characters for the seo urls based on the currently selected viewing language
	 *
	 * @param       integer       language id
	 *
	 * @return      integer       Returns the phrase group id number
	 */
	function fetch_seo_replacements($languageid = 0)
	{
		return $this->cache[$languageid]['replacements'];
	}
	/*
	 * Function to clean out the Javascript cache folder
	 * Runs from weekly cron job
	 *
	 * @return      nothing
	 */
	function clean_cache()
	{
		$files = glob(DIR_TMP_JS . '*.js');
		if (!empty($files) and is_array($files) and count($files) > 0) {
			foreach ($files as $file) {
				if (!empty($file) and $file != '' and file_exists($file)) {
					@unlink($file);
				}
			}
		}
		return 'language->clean_cache(), ';
	}
	/**
	 * Function to fetch the short form language identifier used by the marketplace as default (english = eng)
	 *
	 * @return      string       Short form language identifier
	 */
	function fetch_site_slng()
	{
		if (isset($this->sheel->config['globalserverlanguage_defaultlanguage'])) {
			$sql = $this->sheel->db->query("
	                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
	                        FROM " . DB_PREFIX . "language
	                        WHERE languageid = '" . intval($this->sheel->config['globalserverlanguage_defaultlanguage']) . "'
	                ", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql) > 0) {
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				return mb_substr($res['languagecode'], 0, 3); // english = eng, spanish = spa, etc.
			}
		}
		return 'eng';
	}
	/**
	 * Function to fetch the short form language identifier used by the marketplace as default (english = eng)
	 *
	 * @param       integer      user id
	 *
	 * @return      string       Short form language identifier
	 */
	function fetch_user_slng($userid = 0)
	{
		$sql = $this->sheel->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$lang = $this->sheel->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $this->sheel->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                                FROM " . DB_PREFIX . "language
                                WHERE languageid = '" . $lang['languageid'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			if ($this->sheel->db->num_rows($sql2) > 0) {
				$lcode = $this->sheel->db->fetch_array($sql2, DB_ASSOC);
				return mb_substr($lcode['languagecode'], 0, 3);
			}
		}
		return 'eng';
	}
	/**
	 * Function to add the database tables alterations that support multiple languages for the language being added.
	 *
	 * @param       integer      language id to add
	 * @param       integer      language id to make default (1 = English)
	 *
	 * @return      boolean      True on success, false on failure
	 */
	function add_language_schema($payload = array())
	{
		$title = ucfirst(mb_strtolower(trim($payload['lng'])));
		$this->sheel->db->query("
			INSERT INTO " . DB_PREFIX . "language
			(languageid, title, languagecode, charset, author, locale, textdirection, languageiso, installdate, lastimport, replacements, alphabet)
			VALUES(
			NULL,
			'" . $this->sheel->db->escape_string($title) . "',
			'" . $this->sheel->db->escape_string(mb_strtolower(trim($payload['lng']))) . "',
			'" . $this->sheel->db->escape_string(mb_strtoupper($payload['charset'])) . "',
			'" . $this->sheel->db->escape_string($payload['author']) . "',
			'" . $this->sheel->db->escape_string($payload['locale']) . "',
			'" . $this->sheel->db->escape_string($payload['textdirection']) . "',
			'" . $this->sheel->db->escape_string($payload['languageiso']) . "',
			'" . DATETIME24H . "',
			'" . DATETIME24H . "',
			'" . $this->sheel->db->escape_string($payload['replacements']) . "',
			'" . $this->sheel->db->escape_string($payload['alphabet']) . "')
		");
		$newlangid = $this->sheel->db->insert_id();
		$sql_blang = $this->sheel->db->query("
			SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . intval($payload['baselanguage']) . "'
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql_blang) > 0) {
			$res_blang = $this->sheel->db->fetch_array($sql_blang, DB_ASSOC);
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD subject_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "email
				SET subject_" . strtolower(substr($payload['lng'], 0, 3)) . " = subject_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD message_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "email
				SET message_" . strtolower(substr($payload['lng'], 0, 3)) . " = message_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD messagehtml_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "email
				SET messagehtml_" . strtolower(substr($payload['lng'], 0, 3)) . " = messagehtml_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD name_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "email
				SET name_" . strtolower(substr($payload['lng'], 0, 3)) . " = name_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "locations
				ADD location_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `locationid`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "locations
				SET location_" . strtolower(substr($payload['lng'], 0, 3)) . " = location_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "locations_regions
				ADD region_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `regionid`
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "locations_regions
				SET region_" . strtolower(substr($payload['lng'], 0, 3)) . " = region_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$this->sheel->db->query("
				ALTER TABLE " . DB_PREFIX . "language_phrases
				ADD text_" . strtolower(substr($payload['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `text_original`
			");

			if (isset($payload['defaultlanguage']) and $payload['defaultlanguage']) {
				$this->sheel->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . $newlangid . "'
					WHERE name = 'globalserverlanguage_defaultlanguage'
				");
			}
		}
		return true;
	}
	/**
	 * Function to remove the database table alterations that support multiple languages for the language being removed.
	 *
	 * @param       integer      language id to remove
	 * @param       integer      language id to make default (1 = English)
	 *
	 * @return      boolean      True on success, false on failure
	 */
	function remove_language_schema($languageid = 0, $baselanguage = 1)
	{
		$success = true;
		$sql_lang = $this->sheel->db->query("
			SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . intval($languageid) . "'
			LIMIT 1
		");
		if ($this->sheel->db->num_rows($sql_lang) > 0) {
			$res_lang = $this->sheel->db->fetch_array($sql_lang, DB_ASSOC);
			$this->sheel->db->query("
				DELETE FROM " . DB_PREFIX . "language
				WHERE languageid = '" . intval($languageid) . "'
				LIMIT 1
			");
			if ($this->sheel->db->field_exists("text_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "language_phrases") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "language_phrases
					DROP text_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("subject_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP subject_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("message_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP message_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("messagehtml_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP messagehtml_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("name_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP name_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("location_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "locations") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "locations
					DROP location_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($this->sheel->db->field_exists("region_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "locations_regions") == 1) {
				$this->sheel->db->query("
					ALTER TABLE " . DB_PREFIX . "locations_regions
					DROP region_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "users
				SET languageid = '" . intval($baselanguage) . "'
				WHERE languageid = '" . $res_lang['languageid'] . "'
			");
			$this->sheel->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($baselanguage) . "'
				WHERE name = 'globalserverlanguage_defaultlanguage'
			");
			// if we are viewing the page in the language we are attempting to remove
			// let's ensure we switch back to the default language so no db phrase errors occur
			if ($_SESSION['sheeldata']['user']['languageid'] == $languageid) {
				$_SESSION['sheeldata']['user']['languageid'] = intval($this->sheel->config['globalserverlanguage_defaultlanguage']);
				$_SESSION['sheeldata']['user']['languagecode'] = $this->print_language_code($this->sheel->config['globalserverlanguage_defaultlanguage']);
				$_SESSION['sheeldata']['user']['slng'] = $this->print_short_language_code();
			}
		} else {
			$success = false;
		}
		return $success;
	}
	function import_from_xml_folder($what = 'phrase')
	{
		if ($what == 'phrase') {
			if (file_exists(__DIR__ . 'installer/xml/master-phrases-english.xml')) {
				$xml = file_get_contents(__DIR__ . 'installer/xml/master-phrases-english.xml');
				$this->sheel->admincp_importexport->import('phrase', 'admincp', $xml, true, 0, 1);
			}
		} else if ($what == 'email') {
			if (file_exists(__DIR__ . 'installer/xml/master-emails-english.xml')) {
				$xml = file_get_contents(__DIR__ . 'installer/xml/master-emails-english.xml');
				$this->sheel->admincp_importexport->import('email', 'admincp', $xml, true, 0, 1);
			}
		}
	}
}
?>