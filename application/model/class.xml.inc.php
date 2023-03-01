<?php
/**
 * XML class to perform the majority of xml tasks within sheel
 *
 * @package      sheel\XML
 * @version      1.0.0.0
 * @author       sheel
 */
class xml
{
        protected $sheel;
        var $charset = 'UTF-8';
        var $content_type = 'text/xml';
        var $xmldata = '';
        var $tag_count = '';
        var $tabs = '';
        var $doc = '';
        var $data = '';
        var $xml_parser = '';
        var $cdata = '';
        var $stack = array();
        var $open_tags = array();
        var $parsedxmldata = array();

        /**
         * Constructor
         */
        public function __construct($sheel, $content_type = null, $charset = null)
        {
                $this->sheel = $sheel;
                if ($content_type) {
                        $this->content_type = $content_type;
                }
                if ($charset == null) {
                        $charset = (!empty($this->sheel->styles->templatevars['template_charset']) ? $this->sheel->styles->templatevars['template_charset'] : 'UTF-8');
                }
			
                $this->charset = $charset;
        }
        /*
         * Function to send the content-type header
         *
         * @return
         */
        function send_content_type_header()
        {
                @header('Content-Type: ' . $this->content_type . ($this->charset == '' ? '' : '; charset=' . $this->charset));
        }
        /*
         * Fetch to fetch the XML tag
         *
         * @return
         */
        function fetch_xml_tag()
        {
                return '<?xml version="1.0" encoding="' . $this->charset . '"?>' . "\n";
        }
        /*
         * Function to add an XML tag to a group
         *
         * @return
         */
        function add_group($tag, $attr = array())
        {
                $this->open_tags[] = $tag;
                $this->doc .= $this->tabs . $this->build_tag($tag, $attr) . "\n";
                $this->tabs .= "\t";
        }
        /*
         * Function to close the xml group
         *
         * @return
         */
        function close_group()
        {
                $tag = array_pop($this->open_tags);
                $this->tabs = mb_substr($this->tabs, 0, -1);
                $this->doc .= $this->tabs . "</$tag>\n";
        }
        /*
         * Function to add a tag with specific content
         *
         * @return
         */
        function add_tag($tag, $content = '', $attr = array(), $cdata = false, $htmlspecialchars = false)
        {
                $this->doc .= $this->tabs . $this->build_tag($tag, $attr, ($content === ''));
                if ($content !== '') {
                        if ($htmlspecialchars) {
                                $this->doc .= htmlspecialchars_uni($content);
                        } else if ($cdata or preg_match('/[\<\>\&\'\"\[\]]/', $content)) {
                                $this->doc .= '<![CDATA[' . $this->escape_cdata($content) . ']]>';
                        } else {
                                $this->doc .= $content;
                        }
                        $this->doc .= "</$tag>\n";
                }
        }
        /*
         * Function to build an XML tag
         *
         * @return
         */
        function build_tag($tag, $attr, $closing = false)
        {
                $tmp = "<$tag";
                if (!empty($attr)) {
                        foreach ($attr as $attr_name => $attr_key) {
                                if (mb_strpos($attr_key, '"') !== false) {
                                        $attr_key = htmlspecialchars_uni($attr_key);
                                }
                                $tmp .= " $attr_name=\"$attr_key\"";
                        }
                }
                $tmp .= ($closing ? " />\n" : '>');
                return $tmp;
        }
        /*
         * Function to escape XML CDATA
         *
         * @return
         */
        function escape_cdata($xml)
        {
                $xml = preg_replace('#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $xml);
                return str_replace(array('<![CDATA[', ']]>'), array('«![CDATA[', ']]»'), $xml);
        }
        /*
         * Function to output the XML document
         *
         * @return
         */
        function output()
        {
                if (!empty($this->open_tags)) {
                        return false;
                }
                return $this->doc;
        }
        /*
         * Function to print the XML
         *
         * @return
         */
        function print_xml()
        {
                $this->send_content_type_header();
                echo $this->fetch_xml_tag() . $this->output();
        }
        function print_xml_inline()
        {
                $output = $this->fetch_xml_tag() . $this->output();
                $this->doc = '';
                return $output;
        }
        /*
         * Function to handle cdata
         *
         * @return
         */
        function handle_cdata(&$parser, $data)
        {
                $this->cdata .= $data;
        }
        /*
         * Function to add a node
         *
         * @return
         */
        function add_node(&$child, $name, $value)
        {
                if (!is_array($child) or !in_array($name, array_keys($child))) {
                        $child[$name] = $value;
                } else if (is_array($child[$name]) and isset($child[$name][0])) {
                        $child[$name][] = $value;
                } else {
                        $child[$name] = array($child[$name]);
                        $child[$name][] = $value;
                }
        }
        /*
         * Function to unescape cdata
         *
         * @return
         */
        function unescape_cdata($xml)
        {
                static $find, $replace;
                if (!is_array($find)) {
                        $find = array('«![CDATA[', ']]»', "\r\n", "\n");
                        $replace = array('<![CDATA[', ']]>', "\n", "\r\n");
                }
                return str_replace($find, $replace, $xml);
        }
        /*
         * ...
         *
         * @return
         */
        function handle_element_start(&$parser, $name, $attribs)
        {
                $this->cdata = '';
                foreach ($attribs as $key => $val) {
                        if (preg_match('#&[a-z]+;#i', $val)) {
                                $attribs["$key"] = $this->sheel->common->un_htmlspecialchars($val);
                        }
                }
                array_unshift($this->stack, array('name' => $name, 'attribs' => $attribs, 'tag_count' => ++$this->tag_count));
        }
        /*
         * ...
         *
         * @return
         */
        function handle_element_end(&$parser, $name)
        {
                $tag = array_shift($this->stack);
                if ($tag['name'] != $name) {
                        return;
                }
                $output = $tag['attribs'];
                if (trim($this->cdata) !== '' or $tag['tag_count'] == $this->tag_count) {
                        if (sizeof($output) == 0) {
                                $output = $this->unescape_cdata($this->cdata);
                        } else {
                                $this->add_node($output, 'value', $this->unescape_cdata($this->cdata));
                        }
                }
                if (isset($this->stack[0])) {
                        $this->add_node($this->stack[0]['attribs'], $name, $output);
                } else {
                        $this->parsedxmldata = $output;
                }
                $this->cdata = '';
        }
        /*
         * Function to break down xml tags into usable arrays
         *
         * @param       string        encoding character set (default ISO-8859-1)
         * @param       bool 	     decide if we should empty the xml data being held in memory after processing
         * @param       string        xml filename to process
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function construct_xml_array($encoding = 'UTF-8', $emptyafter = true, $xmlfile)
        {
                if (!empty($xmlfile)) {
                        $this->xmldata = @file_get_contents(DIR_XML . $xmlfile);
                }
                if (empty($this->xmldata)) {
                        return false;
                }
                if (!($this->xml_parser = xml_parser_create($encoding))) {
                        return false;
                }
                xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 0);
                xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
                xml_set_character_data_handler($this->xml_parser, array(&$this, 'handle_cdata'));
                xml_set_element_handler($this->xml_parser, array(&$this, 'handle_element_start'), array(&$this, 'handle_element_end'));
                xml_parse($this->xml_parser, $this->xmldata);
                $err = xml_get_error_code($this->xml_parser);
                if ($emptyafter) {
                        $this->xmldata = '';
                        $this->stack = array();
                        $this->cdata = '';
                }
                if ($err) {
                        return false;
                }
                xml_parser_free($this->xml_parser);
                return $this->parsedxmldata;
        }
        /*
         * Function to process a valid sheel XML Configuration data
         *
         * @param       array 	     xml data
         * @param       string        error level (unused)
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_configuration_xml($a = array(), $e)
        {
                $site_name = $sheel_version = $sheel_build = $date = $main_configuration = '';
                $elementcount = count($a);
                for ($i = 0; $i < $elementcount; $i++) {
                        if ($a[$i]['tag'] == 'CONFIGURATION') {
                                if ($a[$i]['type'] == 'open') {
                                        $sheel_version = $a[$i]['attributes']['ILVERSION'];
                                        $sheel_build = $a[$i]['attributes']['ILBUILD'];
                                }
                        } else if ($a[$i]['tag'] == 'SITENAME') {
                                if ($a[$i]['type'] == 'complete') {
                                        $site_name = $a[$i]['value'];
                                }
                        } else if ($a[$i]['tag'] == 'DATE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $date = $a[$i]['value'];
                                }
                        } else if ($a[$i]['tag'] == 'MAIN_CONFIGURATION') {
                                if ($a[$i]['type'] == 'open') {
                                        $main_configuration = array();
                                }
                        } else if ($a[$i]['tag'] == 'CONFIGURATION_GROUP') {
                                if ($a[$i]['type'] == 'open') {
                                        $current_groupname = $a[$i]['attributes']['GROUPNAME'];
                                }
                        } else if ($a[$i]['tag'] == 'OPTION') {
                                if ($a[$i]['type'] == 'complete') {
                                        $main_configuration[$current_groupname][$a[$i]['attributes']['NAME']] = trim($a[$i]['value']);
                                }
                        }
                }
                $result = array(
                        'sheel_version' => $sheel_version,
                        'sheel_build' => $sheel_build,
                        'site_name' => $site_name,
                        'date' => $date,
                        'main_configuration' => $main_configuration
                );
                return $result;
        }
        /*
         * Function to process a valid sheel XML Phrases Template data to convert all xml tags into usable arrays
         *
         * @param       array 	     xml data
         * @param       string        error level (unused)
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_lang_xml($a = array(), $e)
        {
                $illang_version = $title = $author = $lang_code = $charset = $locale = $languageiso = $textdirection = $current_phrase_group = $replacements = $alphabet = $canselect = '';
                $phrasearray = $phrase_group_data = array();
                $elementcount = count($a);
                for ($i = 0; $i < $elementcount; $i++) {
                        if ($a[$i]['tag'] == 'LANGUAGE') {
                                if (empty($illang_version) and $a[$i]['type'] == 'open') {
                                        $illang_version = $a[$i]['attributes']['ILVERSION'];
                                }
                        } else if ($a[$i]['tag'] == 'TITLE') {
                                if (empty($title) and $a[$i]['type'] == 'complete') {
                                        $title = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'AUTHOR') {
                                if (empty($author) and $a[$i]['type'] == 'complete') {
                                        $author = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'LANGUAGECODE') {
                                if (empty($lang_code) and $a[$i]['type'] == 'complete') {
                                        $lang_code = $a[$i]['value'];
                                }
                        } else if ($a[$i]['tag'] == 'CHARSET') {
                                if (empty($charset) and $a[$i]['type'] == 'complete') {
                                        $charset = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'LOCALE') {
                                if (empty($locale) and $a[$i]['type'] == 'complete') {
                                        $locale = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'LANGUAGEISO') {
                                if (empty($languageiso) and $a[$i]['type'] == 'complete') {
                                        $languageiso = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'TEXTDIRECTION') {
                                if (empty($textdirection) and $a[$i]['type'] == 'complete') {
                                        $textdirection = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'REPLACEMENTS') {
                                if (empty($replacements) and $a[$i]['type'] == 'complete' and isset($a[$i]['value'])) {
                                        $replacements = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'ALPHABET') {
                                if (empty($alphabet) and $a[$i]['type'] == 'complete' and isset($a[$i]['value'])) {
                                        $alphabet = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'CANSELECT') {
                                if (empty($canselect) and $a[$i]['type'] == 'complete') {
                                        $canselect = (int) trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'PHRASEGROUP') {
                                if ($a[$i]['type'] == 'open' or $a[$i]['type'] == 'complete') {
                                        $current_phrase_group = preg_replace("/[^a-zA-Z0-9_]+/", '', $a[$i]['attributes']['NAME']);
                                        $phrase_group_data[] = array(
                                                $current_phrase_group,
                                                preg_replace("/[^a-zA-Z_]+/", '', $a[$i]['attributes']['NAME']),
                                                preg_replace("/[^a-zA-Z]+/", '', $a[$i]['attributes']['DESCRIPTION']),
                                                preg_replace("/[^a-zA-Z]+/", '', $a[$i]['attributes']['PRODUCT'])
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'PHRASE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $phrasearray[] = array(
                                                $current_phrase_group,
                                                preg_replace("/[^a-zA-Z0-9_]+/", '', mb_strtolower(trim($a[$i]['attributes']['VARNAME']))),
                                                trim($a[$i]['value'])
                                        );
                                }
                        }
                }
                $result = array(
                        'illang_version' => $illang_version,
                        'title' => $title,
                        'author' => $author,
                        'lang_code' => $lang_code,
                        'charset' => $charset,
                        'phrasearray' => $phrasearray,
                        'phrase_group_data' => $phrase_group_data,
                        'locale' => $locale,
                        'languageiso' => $languageiso,
                        'textdirection' => $textdirection,
                        'replacements' => $replacements,
                        'alphabet' => $alphabet,
                        'canselect' => $canselect
                );
                return $result;
        }
        /*
         * Function to process a valid sheel XML Email Template data to convert all xml tags into usable arrays
         *
         * @param       array 	     xml data
         * @param       string        error level
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_email_xml($a = array(), $e)
        {
                $ilversion = $langcode = $charset = $author = $emailname = $emailsubject = $emailbody = $emailhtmlbody = $emailtype = $emailvarname = $emailproduct = $emailgroup = $emailbuyer = $emailseller = $emailadmin = '';
                $emailarray = array();
                $arraycount = count($a);
                for ($i = 0; $i < $arraycount; $i++) {
                        if ($a[$i]['tag'] == 'LANGUAGE') {
                                if (empty($ilversion) and $a[$i]['type'] == 'open') {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        } else if ($a[$i]['tag'] == 'AUTHOR') {
                                if (empty($author) and $a[$i]['type'] == 'complete') {
                                        $author = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'LANGUAGECODE') {
                                if (empty($langcode) and $a[$i]['type'] == 'complete') {
                                        $langcode = preg_replace("/[^a-zA-Z0-9_]+/", '', $a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'CHARSET') {
                                if (empty($charset) and $a[$i]['type'] == 'complete') {
                                        $charset = trim($a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'NAME') {
                                $emailname = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'SUBJECT') {
                                $emailsubject = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'MESSAGE') {
                                $emailbody = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        } else if ($a[$i]['tag'] == 'MESSAGEHTML') {
                                $emailhtmlbody = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        } else if ($a[$i]['tag'] == 'TYPE') {
                                $accepted = array('global', 'service', 'product');
                                $emailtype = preg_replace("/[^a-zA-Z]+/", '', mb_strtolower($a[$i]['value']));
                                if (!in_array($emailtype, $accepted)) {
                                        $emailtype = 'global';
                                }
                        } else if ($a[$i]['tag'] == 'VARNAME') {
                                $emailvarname = preg_replace("/[^a-zA-Z0-9_]+/", '', mb_strtolower($a[$i]['value']));
                        } else if ($a[$i]['tag'] == 'PRODUCT') {
                                $emailproduct = preg_replace("/[^a-zA-Z_]+/", '', mb_strtolower($a[$i]['value']));
                        } else if ($a[$i]['tag'] == 'GROUP') {
                                $accepted = array('account', 'accounting', 'bidding', 'digest', 'feedback', 'listings', 'membership', 'messages', 'order', 'payments', 'register', 'selling', 'staff');
                                $emailgroup = preg_replace("/[^a-zA-Z_]+/", '', mb_strtolower($a[$i]['value']));
                                if (!in_array($emailgroup, $accepted)) {
                                        $emailgroup = 'account';
                                }
                        } else if ($a[$i]['tag'] == 'BUYER') {
                                $emailbuyer = (int) $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'SELLER') {
                                $emailseller = (int) $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'ADMIN') {
                                $emailadmin = (int) $a[$i]['value'];
                        }
                        if (!empty($emailvarname) and !empty($emailname) and !empty($emailsubject) and !empty($emailbody) and !empty($emailtype) and !empty($emailproduct) and !empty($emailgroup)) {
                                $emailarray[] = array(
                                        $emailname,
                                        $emailsubject,
                                        $emailbody,
                                        $emailtype,
                                        $emailvarname,
                                        (isset($emailproduct) ? $emailproduct : 'sheel'),
                                        $emailgroup,
                                        $emailbuyer,
                                        $emailseller,
                                        $emailadmin,
                                        $emailhtmlbody
                                );
                                $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailproduct = $emailgroup = $emailbuyer = $emailseller = $emailadmin = $emailhtmlbody = '';
                        }
                }
                $result = array(
                        'ilversion' => $ilversion,
                        'langcode' => $langcode,
                        'author' => $author,
                        'charset' => $charset,
                        'emailarray' => $emailarray
                );
                return $result;
        }
        /*
         * Function to process a valid sheel XML Category Template data to convert all xml tags into usable arrays
         *
         * @param       array 	     xml data
         * @param       string        error level
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_category_xml($a = array(), $e)
        {
                $ilversion = $langcode = $cid = $title = $description = $keywords = $seourl = '';
                $catsarray = array();
                $arraycount = count($a);
                for ($i = 0; $i < $arraycount; $i++) {
                        if ($a[$i]['tag'] == 'LANGUAGE') {
                                if (empty($ilversion) and $a[$i]['type'] == 'open') {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        } else if ($a[$i]['tag'] == 'LANGUAGECODE') {
                                if (empty($langcode) and $a[$i]['type'] == 'complete') {
                                        $langcode = preg_replace("/[^a-zA-Z0-9_]+/", '', $a[$i]['value']);
                                }
                        } else if ($a[$i]['tag'] == 'CATEGORY') {
                                if (empty($cid) and $a[$i]['type'] == 'open') {
                                        $cid = $a[$i]['attributes']['CID'];
                                }
                        } else if ($a[$i]['tag'] == 'TITLE') {
                                $title = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        } else if ($a[$i]['tag'] == 'DESCRIPTION') {
                                $description = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        } else if ($a[$i]['tag'] == 'KEYWORDS') {
                                $keywords = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        } else if ($a[$i]['tag'] == 'SEOURL') {
                                $seourl = ((isset($a[$i]['value'])) ? $a[$i]['value'] : '');
                        }
                        if ($cid != '' and !empty($title) and !empty($description) and !empty($seourl)) {
                                $catsarray[] = array(
                                        $cid,
                                        $title,
                                        $description,
                                        $keywords,
                                        $seourl
                                );
                                $cid = $title = $description = $keywords = $seourl = '';
                        }
                }
                $result = array(
                        'ilversion' => $ilversion,
                        'langcode' => $langcode,
                        'catsarray' => $catsarray
                );
                return $result;
        }
        /*
         * Function to process a valid sheel XML Add-on Installer Package to convert all xml tags into usable arrays
         *
         * @param       array 	     xml data
         * @param       string        error level
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_addon_xml($a = array(), $e)
        {
                $filestructure = $installcode = $uninstallcode = $upgradecode = $installnotifyurl = $updatenotifyurl = $uninstallnotifyurl = $developer = $developeremail = $developerweb = $newsfeedurl = $iconurl = $product = $modulearray = $modulegroup = $setting = $configgroup = $phrasegroup = $taskgroup = $taskarray = $emailgroup = array();
                $emailname = $emailsubject = $emailbody = $emailhtmlbody = $emailtype = $emailvarname = $emailbuyer = $emailseller = $emailadmin = $version = $minbuild = $maxbuild = '';
                $current_module_group = 0;
                $count = count($a);
                for ($i = 0; $i < $count; $i++) {
                        if ($a[$i]['tag'] == 'VERSION') {
                                $version = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'VERSIONCHECKURL') {
                                $versioncheckurl = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'URL') {
                                $url = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'MINVERSION') {
                                $minversion = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'MAXVERSION') {
                                $maxversion = !empty($a[$i]['value']) ? $a[$i]['value'] : '';
                        } else if ($a[$i]['tag'] == 'MINBUILD') {
                                $minbuild = !empty($a[$i]['value']) ? $a[$i]['value'] : '';
                        } else if ($a[$i]['tag'] == 'MAXBUILD') {
                                $maxbuild = !empty($a[$i]['value']) ? $a[$i]['value'] : '';
                        } else if ($a[$i]['tag'] == 'DEVELOPER') {
                                $developer = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'DEVELOPEREMAIL') {
                                $developeremail = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'DEVELOPERWEB') {
                                $developerweb = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'NEWSFEEDURL') {
                                $newsfeedurl = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'ICONURL') {
                                $iconurl = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'INSTALLNOTIFYURL') {
                                $installnotifyurl = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'UPDATENOTIFYURL') {
                                $updatenotifyurl = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'UNINSTALLNOTIFYURL') {
                                $uninstallnotifyurl = $a[$i]['value'];
                        }
                        // #### SETTINGS #######################################
                        else if ($a[$i]['tag'] == 'CONFIGGROUP') {
                                if ($a[$i]['type'] == 'open' or $a[$i]['type'] == 'complete') {
                                        $current_config_group = $a[$i]['attributes']['GROUPNAME'];
                                        $current_config_table = $a[$i]['attributes']['TABLE'];
                                        $configgroup[] = array(
                                                $a[$i]['attributes']['GROUPNAME'],
                                                $a[$i]['attributes']['PARENTGROUPNAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['TABLE']
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'SETTING') {
                                if ($a[$i]['type'] == 'open' or $a[$i]['type'] == 'complete') {
                                        $setting[] = array(
                                                $current_config_group = isset($current_config_group) ? $current_config_group : '',
                                                $current_config_table = isset($current_config_table) ? $current_config_table : '',
                                                $a[$i]['attributes']['NAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['VALUE'],
                                                $a[$i]['attributes']['INPUTTYPE'],
                                                $a[$i]['attributes']['SORT'],
                                                htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e)
                                        );
                                }
                        }
                        // #### PRODUCT DATA ###################################
                        else if ($a[$i]['tag'] == 'MODULEGROUP') {
                                if ($a[$i]['type'] == 'open' or $a[$i]['type'] == 'complete') {
                                        $current_module_group = $a[$i]['attributes']['NAME'];
                                        $modulegroup[] = array(
                                                $a[$i]['attributes']['NAME'], // 0
                                                $a[$i]['attributes']['MODULENAME'], // 1
                                                $a[$i]['attributes']['FOLDER'], // 2
                                                $current_config_table = isset($current_config_table) ? $current_config_table : '', // 3
                                                $a[$i]['attributes']['DESCRIPTION'], // 4
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'MODULE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $modulearray[] = array(
                                                $current_module_group,
                                                // 0
                                                trim($a[$i]['attributes']['TAB']),
                                                // 1
                                                trim($a[$i]['attributes']['SORT']),
                                                // 2
                                                trim($a[$i]['value']) // 3
                                        );
                                }
                        }
                        // #### PHRASES ########################################
                        else if ($a[$i]['tag'] == 'PHRASEGROUP') {
                                if ($a[$i]['type'] == 'open' || $a[$i]['type'] == 'complete') {
                                        $productname = !empty($a[$i]['attributes']['PRODUCT']) ? $a[$i]['attributes']['PRODUCT'] : mb_strtolower($a[$i]['attributes']['NAME']);
                                        $current_phrase_group = !empty($a[$i]['attributes']['NAME']) ? $a[$i]['attributes']['NAME'] : '';
                                        $phrasegroup[] = array(
                                                $a[$i]['attributes']['NAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $productname
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'PHRASE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $phrasearray[] = array(
                                                $current_phrase_group,
                                                trim($a[$i]['attributes']['VARNAME']),
                                                htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e)
                                        );
                                }
                        }
                        // #### FILE STRUCTURE #################################
                        else if ($a[$i]['tag'] == 'FILE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $filestructure[] = array(
                                                ((!empty($a[$i]['attributes']['MD5']) and mb_strlen($a[$i]['attributes']['MD5']) == 32) ? $a[$i]['attributes']['MD5'] : ''),
                                                (!empty($a[$i]['value']) ? htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e) : '')
                                        );
                                }
                        }
                        // #### SCHEDULED TASKS ################################
                        else if ($a[$i]['tag'] == 'TASK') {
                                if ($a[$i]['type'] == 'open' || $a[$i]['type'] == 'complete') {
                                        $current_task_group = !empty($a[$i]['attributes']['VARNAME']) ? $a[$i]['attributes']['VARNAME'] : '';
                                        $taskgroup[] = array(
                                                $a[$i]['attributes']['VARNAME'],
                                                $a[$i]['attributes']['FILENAME'],
                                                $a[$i]['attributes']['ACTIVE'],
                                                $a[$i]['attributes']['LOGLEVEL'],
                                                $a[$i]['attributes']['PRODUCT']
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'SCHEDULE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $taskarray[] = array(
                                                $current_task_group,
                                                trim($a[$i]['attributes']['WEEKDAY']),
                                                trim($a[$i]['attributes']['DAY']),
                                                trim($a[$i]['attributes']['HOUR']),
                                                trim($a[$i]['attributes']['MINUTE'])
                                        );
                                }
                        }
                        // #### EMAIL TEMPLATES ################################
                        else if ($a[$i]['tag'] == 'NAME') {
                                $emailname = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'SUBJECT') {
                                $emailsubject = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'MESSAGE') {
                                $emailbody = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'MESSAGEHTML') {
                                $emailhtmlbody = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'TYPE') {
                                $emailtype = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'VARNAME') {
                                $emailvarname = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'BUYER') {
                                $emailbuyer = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'SELLER') {
                                $emailseller = $a[$i]['value'];
                        } else if ($a[$i]['tag'] == 'ADMIN') {
                                $emailadmin = $a[$i]['value'];
                        }
                        if (!empty($emailvarname) and !empty($emailname) and !empty($emailsubject) and !empty($emailtype) and !empty($emailbody)) {
                                $emailgroup[] = array(
                                        $emailvarname,
                                        $emailname,
                                        $emailsubject,
                                        $emailtype,
                                        $emailbody,
                                        $emailbuyer,
                                        $emailseller,
                                        $emailadmin,
                                        $emailhtmlbody
                                );
                                // reset for next email
                                $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailbuyer = $emailseller = $emailadmin = $emailhtmlbody = '';
                        }
                        // #### INSTALLATION CODE ##############################
                        else if ($a[$i]['tag'] == 'INSTALLCODE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $installcode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                        // #### UNINSTALLATION CODE ############################
                        else if ($a[$i]['tag'] == 'UNINSTALLCODE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $uninstallcode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                        // #### UPGRADE CODE ###################################
                        else if ($a[$i]['tag'] == 'UPGRADECODE') {
                                if ($a[$i]['type'] == 'complete') {
                                        $upgradecode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                }
                $product[] = array($version, $versioncheckurl, $url, $minversion, $maxversion, $minbuild, $maxbuild, $developer, $developeremail, $developerweb, $newsfeedurl, $iconurl, $installnotifyurl, $updatenotifyurl, $uninstallnotifyurl);
                $result = array(
                        'product' => $product,
                        'configgroup' => isset($configgroup) ? $configgroup : '',
                        'setting' => isset($setting) ? $setting : '',
                        'modulearray' => $modulearray,
                        'modulegroup' => $modulegroup,
                        'phrasearray' => isset($phrasearray) ? $phrasearray : '',
                        'phrasegroup' => isset($phrasegroup) ? $phrasegroup : '',
                        'taskarray' => isset($taskarray) ? $taskarray : '',
                        'taskgroup' => isset($taskgroup) ? $taskgroup : '',
                        'emailgroup' => isset($emailgroup) ? $emailgroup : '',
                        'installcode' => isset($installcode) ? $installcode : '',
                        'uninstallcode' => isset($uninstallcode) ? $uninstallcode : '',
                        'upgradecode' => isset($upgradecode) ? $upgradecode : '',
                        'developer' => isset($developer) ? $developer : '',
                        'developeremail' => isset($developeremail) ? $developeremail : '',
                        'developerweb' => isset($developerweb) ? $developerweb : '',
                        'newsfeedurl' => isset($newsfeedurl) ? $newsfeedurl : '',
                        'iconurl' => isset($iconurl) ? $iconurl : '',
                        'installnotifyurl' => isset($installnotifyurl) ? $installnotifyurl : '',
                        'updatenotifyurl' => isset($updatenotifyurl) ? $updatenotifyurl : '',
                        'uninstallnotifyurl' => isset($uninstallnotifyurl) ? $uninstallnotifyurl : '',
                        'filestructure' => isset($filestructure) ? $filestructure : ''
                );
                return $result;
        }
        /*
         * Function to process a valid sheel XML configuration data to convert all xml tags into usable arrays
         *
         * @param       array 	     xml data
         * @param       string        error level (unused)
         *
         * @return      array         Returns formatted array of xml tag data
         */
        function process_config_xml($a = array(), $e)
        {
                $ilversion = $current_setting_group = '';
                $settingarray = array();
                $elementcount = count($a);
                for ($i = 0; $i < $elementcount; $i++) {
                        if ($a[$i]['tag'] == 'CONFIG') {
                                if (empty($ilversion) and $a[$i]['type'] == 'open') {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        } else if ($a[$i]['tag'] == 'CONFIGGROUP') {
                                if ($a[$i]['type'] == 'open' or $a[$i]['type'] == 'complete') {
                                        $current_setting_group = $a[$i]['attributes']['GROUPNAME'];
                                        $settinggrouparray[] = array(
                                                $current_setting_group,
                                                $a[$i]['attributes']['PARENTGROUPNAME'],
                                                $a[$i]['attributes']['GROUPNAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['HELP'],
                                                $a[$i]['attributes']['CLASS'],
                                                $a[$i]['attributes']['SORT']
                                        );
                                }
                        } else if ($a[$i]['tag'] == 'SETTING') {
                                if ($a[$i]['type'] == 'complete') {
                                        $settingarray[] = array(
                                                $current_setting_group,
                                                trim($a[$i]['attributes']['NAME']),
                                                trim($a[$i]['attributes']['DESCRIPTION']),
                                                trim($a[$i]['attributes']['VALUE']),
                                                trim($a[$i]['attributes']['CONFIGGROUP']),
                                                trim($a[$i]['attributes']['INPUTTYPE']),
                                                trim($a[$i]['attributes']['INPUTCODE']),
                                                trim($a[$i]['attributes']['INPUTNAME']),
                                                trim($a[$i]['attributes']['HELP']),
                                                trim($a[$i]['attributes']['SORT']),
                                                trim($a[$i]['attributes']['VISIBLE'])
                                        );
                                }
                        }
                }
                $result = array(
                        'ilversion' => $ilversion,
                        'settingarray' => $settingarray,
                        'settinggrouparray' => $settinggrouparray,
                );
                return $result;
        }
        function search_to_xml($array = array(), $doheaders = true)
        {
                $skiptags = array();
                $xml = "";
                if ($doheaders) {
                        header("Content-type: text/xml; charset=" . $this->sheel->config['template_charset'] . "");
                        $xml = "<?xml version=\"1.0\" encoding=\"" . $this->sheel->config['template_charset'] . "\"?>" . LINEBREAK;
                }
                if (!empty($array) and is_array($array)) {
                        $xml .= "<search>" . LINEBREAK;
                        foreach ($array as $key => $value) {
                                $xml .= "\t<result>" . LINEBREAK;
                                if (isset($value) and !empty($value) and is_array($value)) {
                                        foreach ($value as $field => $data) {
                                                $xml .= "\t\t<$field><![CDATA[" . sheel_htmlentities($data) . "]]></$field>" . LINEBREAK;
                                        }
                                }
                                $xml .= "\t</result>" . LINEBREAK;
                        }
                        $xml .= "</search>";
                }
                return $xml;
        }
        function xml_to_array($xml = '')
        {
                $xmlary = array();
                $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
                $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
                preg_match_all($reels, $xml, $elements);
                foreach ($elements[1] as $ie => $xx) {
                        $xmlary[$ie]["name"] = $elements[1][$ie];
                        if ($attributes = trim($elements[2][$ie])) {
                                preg_match_all($reattrs, $attributes, $att);
                                foreach ($att[1] as $ia => $xx) {
                                        $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                                }
                        }
                        $cdend = mb_strpos($elements[3][$ie], "<");
                        if ($cdend > 0) {
                                $xmlary[$ie]["text"] = mb_substr($elements[3][$ie], 0, $cdend - 1);
                        }
                        if (preg_match($reels, $elements[3][$ie])) {
                                $xmlary[$ie]["elements"] = $this->xml_to_array($elements[3][$ie]);
                        } else if ($elements[3][$ie]) {
                                $xmlary[$ie]["text"] = $elements[3][$ie];
                        }
                }
                return $xmlary;
        }
        function closetags($html)
        {
                preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
                $openedtags = $result[1];
                preg_match_all('#</([a-z]+)>#iU', $html, $result);
                $closedtags = $result[1];
                $len_opened = count($openedtags);
                if (count($closedtags) == $len_opened) {
                        return $html;
                }
                $openedtags = array_reverse($openedtags);
                for ($i = 0; $i < $len_opened; $i++) {
                        if (!in_array($openedtags[$i], $closedtags)) {
                                $html .= '</' . $openedtags[$i] . '>';
                        } else {
                                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
                        }
                }
                return $html;
        }
}
?>