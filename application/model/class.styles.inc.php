<?php
/**
 * Styles class to perform the majority of skinning and template functions in sheel
 *
 * @package      sheel\Styles
 * @version	     1.0.0.0
 * @author       sheel
 */
class styles
{
    protected $sheel;
    public $computed_style;
    public $css_raw_file;
    public $css_output_file;
    public $css_output_filepath;
    public $css_output_filename;
    public $css_output_url;
    public $styleid;
    public $filehash;
    public $filehash_current;
    public $templatevars = array();

    function __construct($sheel)
    {
        $this->sheel = $sheel;
        $this->init();
    }
    function init()
    {
        if (empty($_SESSION['sheeldata']['user']['styleid']) or !$this->is_styleid_valid($_SESSION['sheeldata']['user']['styleid'])) {
            $_SESSION['sheeldata']['user']['styleid'] = $this->sheel->config['defaultstyle'];
        }
        $this->styleid = $_SESSION['sheeldata']['user']['styleid'];
        $this->init_css_replacements();

    }
    /*
     * Function to init our css template {variables}
     *
     * @return      array         Returns array with available CSS template variables
     */
    function init_css_replacements()
    {
        $this->templatevars = array();
        if (($this->templatevars = $this->sheel->cachecore->fetch('templatevars_' . $this->styleid)) === false) {
            $this->templatevars['imgrel'] = $this->sheel->config['imgrel']; // /application/
            $this->templatevars['img'] = $this->sheel->config['img']; // /application/assets/images/
            $this->templatevars['imgcdn'] = $this->sheel->config['imgcdn']; // /application/assets/images/
            $this->templatevars['css'] = $this->sheel->config['css']; // /application/assets/css/x/
            $this->templatevars['csscdn'] = $this->sheel->config['csscdn']; // /application/assets/css/x/
            $this->templatevars['js'] = $this->sheel->config['js']; // /application/assets/javascript/
            $this->templatevars['jscdn'] = $this->sheel->config['jscdn']; // /application/assets/javascript/
            $this->templatevars['fonts'] = $this->sheel->config['fonts']; // /application/assets/fonts/
            $this->templatevars['fontscdn'] = $this->sheel->config['fontscdn']; // /application/assets/fonts/
            $this->templatevars['imguploads'] = $this->sheel->config['imguploads']; // /application/uploads/attachments/
            $this->templatevars['imguploadscdn'] = $this->sheel->config['imguploadscdn']; // /application/uploads/attachments/
            $this->templatevars['template_charset'] = $this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset'];
            $this->templatevars['template_languagecode'] = $this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['languageiso'];
            $this->templatevars['template_textdirection'] = $this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['textdirection'];
            $this->templatevars['template_textalignment'] = (($this->templatevars['template_textdirection'] == 'ltr') ? 'left' : 'right');
            $this->templatevars['template_textalignment_alt'] = (($this->templatevars['template_textalignment'] == 'left') ? 'right' : 'left');
            $this->templatevars['template_textalignment_r'] = (($this->templatevars['template_textdirection'] == 'ltr') ? '' : '_r');
            $this->templatevars['table_cellpadding'] = '12';
            $this->templatevars['table_cellspacing'] = '0';
            $sql = $this->sheel->db->query("
				SELECT herodimension, box, box2
				FROM " . DB_PREFIX . "styles
				WHERE styleid = '" . intval($this->styleid) . "'
			");
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $this->templatevars['herodimension'] = $res['herodimension'];
            $this->templatevars['box'] = $res['box'];
            $this->templatevars['box2'] = $res['box2'];
            $tmp = explode('x', $res['herodimension']);
            $this->templatevars['herowidth'] = trim($tmp[0]);
            $this->templatevars['heroheight'] = trim($tmp[1]);
            $this->sheel->cachecore->store('templatevars_' . $this->styleid, $this->templatevars);
            unset($tmp);
        }
        $this->sheel->config = array_merge($this->sheel->config, $this->templatevars);
        return $this->templatevars;
    }
    /*
     * Function to determine if the selected style id is valid within the datastore
     *
     * @param       integer     style id
     *
     * @return      bool        Returns true or false
     */
    function is_styleid_valid($styleid = 0)
    {
        if (!$stylevisible = $this->sheel->cachecore->fetch('is_styleid_valid_' . $this->styleid)) {
            $stylevisible = false;
            $sql = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "visible
				FROM " . DB_PREFIX . "styles
				WHERE styleid = '" . intval($styleid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
            if ($this->sheel->db->num_rows($sql) > 0) {
                $stylevisible = true;
            }
            $this->sheel->cachecore->store('is_styleid_valid_' . $this->styleid, $stylevisible);
        }
        return $stylevisible;
    }
    /*
     * Function to return site style selection / theme pulldown menu on footer pages
     *
     * @return      string       HTML formatted style pulldown menu
     */
    function print_styles_pulldown($selected = '', $autosubmit = '', $name = 'styleid', $extracss = 'draw-select')
    {
        $onchange = (isset($autosubmit) and $autosubmit) ? 'onchange="urlswitch(this, \'dostyle\')"' : '';
        $html = '<select name="' . $name . '" id="form_styleid" ' . $onchange . ' class="' . $extracss . '">';
        $html .= '<optgroup label="{_choose_style}">';
        $sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid, name
			FROM " . DB_PREFIX . "styles
			WHERE visible = '1'
			ORDER BY styleid ASC
		", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $stylecount = 0;
            while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                $sel = (isset($selected) and $res['styleid'] == $selected) ? 'selected="selected"' : '';
                $html .= '<option value="' . $res['styleid'] . '" ' . $sel . '>' . o($res['name']) . '</option>';
                $stylecount++;
            }
        }
        $html .= '</optgroup></select>';
        if (isset($autosubmit) and $autosubmit and $stylecount <= 1 and defined('LOCATION') and LOCATION != 'admin') {
            return false;
        }
        return $html;
    }
    /*
     * Function to parse Javascript array to string
     *
     * @return      string	    inline Javascript
     */
    function jsarr2txt($arr = array())
    {
        $bit = '';
        foreach ($arr as $k => $v) {
            if (stripos(strtolower($v), 'vendor/jquery_' . JQUERYVERSION) !== false) { // loading jquery, include additional required libraries
                $bit .= "vendor/jquery_" . JQUERYVERSION . "+vendor/jquery_ui+vendor/jquery_easing+vendor/jquery_modal+vendor/jquery_mousewheel+vendor/jquery_carousel+vendor/jquery_menuaim+vendor/bootstrap+";
            } else {
                switch ($v) {
                    default: {
                            $bit .= $v . '+';
                            break;
                        }
                }
            }
        }
        return (!empty($bit) ? substr($bit, 0, -1) : $bit);
    }
    /*
     * Function to init our <head> Javascript
     *
     * @return      nothing
     */
    function init_head_js()
    {
        // move to external
        $js = '';
        $js .= "<script>\nvar iL = {IMG: \"" . $this->sheel->config['img'] . "\", ";
        $js .= "CDNIMG: \"" . $this->sheel->config['imgcdn'] . "\", ";
        $js .= "CDNCSS: \"" . $this->sheel->config['csscdn'] . "\", ";
        $js .= "CDNJS: \"" . $this->sheel->config['jscdn'] . "\", ";
        $js .= "IMGUPLOADS: \"" . $this->sheel->config['imguploads'] . "\", ";
        $js .= "IMGUPLOADSCDN: \"" . $this->sheel->config['imguploadscdn'] . "\", ";
        $js .= "BASEURL: \"" . HTTPS_SERVER . "\", ";
        $js .= "SWFBASE: \"" . $this->sheel->config['imgrel'] . DIR_ASSETS_NAME . '/' . DIR_SWF_NAME . "/\", ";
        $js .= "MP3BASE: \"" . $this->sheel->config['imgrel'] . DIR_ASSETS_NAME . '/' . DIR_SOUNDS_NAME . "/\", ";
        $js .= "COOKIENAME: \"" . $this->sheel->config['globalsecurity_cookiename'] . "\", ";
        $js .= "TOKEN: \"" . TOKEN . "\", ";
        $js .= "PAGEURL: \"" . sheel_htmlentities($this->sheel->common->un_htmlspecialchars(PAGEURL)) . "\", ";
        $js .= "URI: \"" . sheel_htmlentities($this->sheel->common->un_htmlspecialchars($_SERVER['REQUEST_URI'])) . "\", ";
        $js .= "AJAXURL: \"" . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . "ajax" . "\", ";
        $js .= "ESCROW: \"" . (int) $this->sheel->config['escrowsystem_enabled'] . "\", ";
        $js .= "DISTANCE: \"" . (int) $this->sheel->config['globalserver_enabledistanceradius'] . "\", ";
        $js .= "UID: \"" . (!empty($_SESSION['sheeldata']['user']['userid']) ? $_SESSION['sheeldata']['user']['userid'] : 0) . "\", ";
        $js .= "MINIFY: \"" . (int) $this->sheel->config['globalfilters_jsminify'] . "\", ";
        $js .= "SITEID: \"" . SITE_ID . "\", ";
        $js .= "SESSION: \"" . session_id() . "\", ";
        $js .= "CURRENCY: \"" . $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['currency_abbrev'] . "\", ";
        $js .= "CURRENCYSYMBOL: \"" . $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'] . "\", ";
        $js .= "CURRENCYSYMBOLRIGHT: \"" . $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right'] . "\", ";
        $js .= "TM: \"" . (($this->sheel->show['ADMINCP_TEST_MODE']) ? "1" : "0") . "\", ";
        $js .= "MSS: \"" . (int) $this->sheel->config['maxshipservices'] . "\", ";
        $js .= "FP: \"" . (int) $this->sheel->config['enablefixedpricetab'] . "\", ";
        $js .= "MV: \"" . (int) $this->sheel->config['maxvariants'] . "\", ";
        $js .= "MVO: \"" . (int) $this->sheel->config['maxvariantoptions'] . "\", ";
        $js .= "LOC: \"" . ((defined('LOCATION') and !empty(LOCATION)) ? o(LOCATION) : 'unknown') . "\", ";
        $js .= "SE: \"" . ((isset($this->sheel->show['searchengine']) and $this->sheel->show['searchengine']) ? '1' : '0') . "\", ";
        $js .= "BOX: \"" . ((isset($this->sheel->config['box'])) ? $this->sheel->config['box'] : '1224') . "\", ";
        $js .= "BOX2: \"" . ((isset($this->sheel->config['box2'])) ? $this->sheel->config['box2'] : '1500') . "\", ";
        $js .= "STZ: \"" . $this->sheel->config['globalserverlocale_sitetimezone'] . "\", "; // America/Toronto
        $js .= "CTZ: Intl.DateTimeFormat().resolvedOptions().timeZone, "; // America/New York
        $js .= "CTZO: \"" . ((isset($_COOKIE[COOKIE_PREFIX . 'tzoffset']) and $_COOKIE[COOKIE_PREFIX . 'tzoffset'] > 0) ? $_COOKIE[COOKIE_PREFIX . 'tzoffset'] : 0) . "\", "; // 10800 (seconds)
        $js .= "LTR: \"" . (($this->sheel->config['template_textalignment'] == 'left') ? "1" : "0") . "\"";


        $js .= "}\n" . (($this->sheel->config['default_wysiwyg'] == 'ckeditor') ? "window.CKEDITOR_BASEPATH = '" . $this->sheel->config['jscdn'] . "vendor/ckeditor/';\n" : "") . "\n</script>";



        if (is_array($this->sheel->template->meta['jsinclude']['header']) and count($this->sheel->template->meta['jsinclude']['header']) > 0) {
            // Enable JS Cache
            //			 $js .=  "\n<script src=\"" . SUB_FOLDER_ROOT . "javascript/?dojs=" . $this->jsarr2txt($this->sheel->template->meta['jsinclude']['header']) . "\" charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\" data-turbolinks-track=\"reload\"></script>";

            // Disable JS Cache (following 2 lines)
            $js .= LINEBREAK;
            $js .= file_get_contents(HTTPS_SERVER . "javascript/?dojs=" . $this->jsarr2txt($this->sheel->template->meta['jsinclude']['header']));
        }



        // echo $this->sheel->template->meta['jsinclude']['header'];
        $this->sheel->template->meta['headinclude'] = $js . $this->sheel->template->meta['headinclude'];
    }
    /*
     * Function to init Javascript just before our </body>
     *
     * @return      nothing
     */
    function init_foot_js()
    {
        $js = '';
        if (isset($this->sheel->template->meta['jsinclude']['footer']) and is_array($this->sheel->template->meta['jsinclude']['footer']) and count($this->sheel->template->meta['jsinclude']['footer']) > 0) {
            // Enable JS Cache
            //			 $js .=  "<script src=\"" . SUB_FOLDER_ROOT . 'javascript/?dojs=' . $this->jsarr2txt($this->sheel->template->meta['jsinclude']['footer']) . "\" charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\"></script>";

            // Disable JS Cache (following 2 lines)
            $js .= LINEBREAK;
            $js .= file_get_contents(HTTPS_SERVER . "javascript/?dojs=" . $this->jsarr2txt($this->sheel->template->meta['jsinclude']['footer']));

            $js .= in_array('menu', $this->sheel->template->meta['jsinclude']['footer']) ? "\n<script>var d = new v3lib();</script>\n" : '';
        }
        $this->sheel->template->meta['footinclude'] = $js . (isset($this->sheel->template->meta['footinclude']) ? $this->sheel->template->meta['footinclude'] : '');
    }
    /*
     * Function to init our <head> CSS
     *
     * @return      nothing
     */
    function init_head_css()
    {
        $this->computed_style = '';
        if (DEBUG_FOOTER) {
            $this->sheel->template->meta['cssinclude']['vendor'][] = 'debug';
        }
        if (isset($this->sheel->template->meta['cssinclude']) and is_array($this->sheel->template->meta['cssinclude']) and count($this->sheel->template->meta['cssinclude']) > 0) {
            foreach ($this->sheel->template->meta['cssinclude'] as $cssfile) {
                if (is_array($cssfile)) { // vendors
                    foreach ($cssfile as $vendorcssfile) {
                        $file = DIR_CSS . $this->styleid . '/vendor/' . $vendorcssfile . '/' . $vendorcssfile . '.css';
                        $filem = DIR_CSS . $this->styleid . '/vendor/' . $vendorcssfile . '/' . $vendorcssfile . '.min.css';
                        if (file_exists($file . 'x')) { // .cssx
                            $this->computed_style .= file_get_contents($file . 'x');
                        } else if (file_exists($filem . 'x') and $this->sheel->config['globalfilters_jsminify']) { // .min.cssx
                            $this->computed_style .= file_get_contents($filem . 'x');
                        } else if (file_exists($filem) and $this->sheel->config['globalfilters_jsminify']) { // .min.css
                            $this->computed_style .= file_get_contents($filem);
                        } else if (file_exists($file)) { // .css
                            $this->computed_style .= file_get_contents($file);
                        }
                    }
                } else {
                    $file = DIR_CSS . $this->styleid . '/' . ((defined('LOCATION') and LOCATION == 'admin') ? 'admin' : 'client') . '/' . $cssfile . '.css';
                    $filem = DIR_CSS . $this->styleid . '/' . ((defined('LOCATION') and LOCATION == 'admin') ? 'admin' : 'client') . '/' . $cssfile . '.min.css';
                    if (file_exists($file . 'x')) { // .cssx
                        $this->computed_style .= file_get_contents($file . 'x');
                    } else if (file_exists($filem . 'x') and $this->sheel->config['globalfilters_jsminify']) { // .min.cssx
                        $this->computed_style .= file_get_contents($filem . 'x');
                    } else if (file_exists($filem) and $this->sheel->config['globalfilters_jsminify']) { // .min.css
                        $this->computed_style .= file_get_contents($filem);
                    } else if (file_exists($file)) { // .css
                        $this->computed_style .= file_get_contents($file);
                    }
                }
            }
            $this->preview();
            $hash = md5($this->computed_style);
            $this->css_output_filename = ((defined('LOCATION') and LOCATION == 'admin') ? 'admin' : 'client') . '-' . $this->styleid . '-' . $_SESSION['sheeldata']['user']['slng'] . '-' . $hash . '.min.css';
            $this->css_output_file = $this->css_output_filename;
            $this->css_output_filepath = DIR_TMP_CSS . $this->css_output_file;
            unset($file);
            if (!file_exists($this->css_output_filepath)) {
                $this->save();
                $this->css_output_url = HTTP_TMP_CSS . $this->css_output_file . '?' . filemtime($this->css_output_filepath);
            } else {
                $this->css_output_url = HTTP_TMP_CSS . $this->css_output_file . '?' . filemtime($this->css_output_filepath);
                $comparehash = md5_file($this->css_output_filepath);
                if ($hash != $comparehash) { // new updates detected
                    $this->clean_cache();
                    $this->save();
                    $this->css_output_url = HTTP_TMP_CSS . $this->css_output_file . '?' . filemtime($this->css_output_filepath);
                }
            }
            $this->sheel->template->meta['headinclude'] = LINEBREAK . '<link type="text/css" rel="stylesheet" href="' . $this->css_output_url . '" async integrity="' . $this->sheel->security->generate_sri_checksum($this->computed_style) . '" crossorigin="anonymous" media="screen, print" id="css-v5"' . ((defined('LOCATION') and LOCATION == 'admin') ? ' data-turbolinks-track="reload"' : '') . '>' . (isset($this->sheel->template->meta['headinclude']) ? $this->sheel->template->meta['headinclude'] : '');
            unset($this->computed_style, $hash, $comparehash);
        }
    }
    /*
     * Function to preview our CSS cache file
     *
     * @return      nothing
     */
    function preview()
    {
        $this->parse_css_variables();
        $this->minify_css();
    }
    /*
     * Function to save our CSS cache file
     *
     * @return      nothing
     */
    function save()
    {
        file_put_contents($this->css_output_filepath, $this->computed_style);
    }
    /*
     * Function to parse our CSS {variables}
     *
     * @return      nothing
     */
    function parse_css_variables()
    {
        if (!empty($this->computed_style)) {
            // YUI minifier hack
            $pattern = '/([a-zA-Z:)])\{(?!:)\w+\b\}([a-zA-Z(;\-\d)])/';
            if (preg_match_all($pattern, $this->computed_style, $matches) !== false) {
                $matches = array_values(array_unique($matches[0]));
                $replaceable = array();
                foreach ($matches as $key) {
                    if (!empty($key)) // :{template_textalignment}c, :{box}p
                    {
                        $first = substr($key, 0, 1); // :
                        $last = substr($key, -1); // p
                        $lasttwo = substr($key, -2); // }p
                        if ($first == ':') {
                            if ($lasttwo == '}p') {
                                $replaceable[$key] = str_replace(array('}' . $last), array('}' . $last), $key);
                            } else {
                                $replaceable[$key] = str_replace(array('}' . $last), array('} ' . $last), $key);
                            }
                        } else {
                            if ($lasttwo == '}p') {
                                $replaceable[$key] = str_replace(array($first . '{', '}' . $last), array($first . ' {', '}' . $last), $key);
                            } else {
                                $replaceable[$key] = str_replace(array($first . '{', '}' . $last), array($first . ' {', '} ' . $last), $key);
                            }
                        }
                    }
                }
                $this->computed_style = str_replace(array_keys($replaceable), array_values($replaceable), $this->computed_style);
                unset($replaceable, $matches);
            }
            // finally replace css variables
            $pattern = '/{([\w\d_]+)}/';
            if (preg_match_all($pattern, $this->computed_style, $matches) !== false) {
                $matches = array_values(array_unique($matches[1]));
                $replaceable = array();
                foreach ($matches as $key) {
                    if (isset($key) and isset($this->templatevars["$key"])) {
                        $replaceable['{' . $key . '}'] = $this->templatevars["$key"];
                    }
                }
                $this->computed_style = str_replace(array_keys($replaceable), array_values($replaceable), $this->computed_style);
                unset($replaceable, $matches);
            }
        }
    }
    /*
     * Function to minify and remove extra white space or line breaks within our final parsed CSS
     *
     * @return      nothing
     */
    function minify_css()
    {
        if (!empty($this->computed_style) and $this->sheel->config['globalfilters_jsminify']) {
            $this->computed_style = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->computed_style);
            $this->computed_style = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $this->computed_style);
            // YUI hack for calc()
            $this->computed_style = str_replace('calc(100%+', 'calc(100% + ', $this->computed_style);
            $this->computed_style = str_replace('calc(100%-', 'calc(100% - ', $this->computed_style);
            $this->computed_style = str_replace('calc(75%-', 'calc(75% - ', $this->computed_style);
            $this->computed_style = str_replace('calc(50%-', 'calc(50% - ', $this->computed_style);
            $this->computed_style = str_replace('calc(33.33333%-', 'calc(33.33333% - ', $this->computed_style);
            $this->computed_style = str_replace('calc(100vh-', 'calc(100vh - ', $this->computed_style);
            $this->computed_style = str_replace('calc(100vw-', 'calc(100vw - ', $this->computed_style);
            $this->computed_style = str_replace('calc(0.77778em-', 'calc(0.77778em - ', $this->computed_style);
            $this->computed_style = str_replace('calc(0.5625em-', 'calc(0.5625em - ', $this->computed_style);
        }
    }
    /*
     * Function to clean out the CSS cache folder
     * Runs from weekly cron job and after a live update
     *
     * @return      nothing
     */
    function clean_cache()
    {
        $files = glob(DIR_TMP_CSS . '*.css');
        if (!empty($files) and is_array($files) and count($files) > 0) {
            foreach ($files as $file) {
                if ($file != '' and file_exists($file)) {
                    @unlink($file);
                }
            }
        }
        return 'styles->clean_cache(), ';
    }
    function delete($styleid = 0)
    {
        if ($styleid <= 0) {
            die(json_encode(array('response' => 0, 'message' => 'Sorry there was a problem deleting this theme.')));
        }
        $sql = $this->sheel->db->query("SELECT styleid FROM " . DB_PREFIX . "styles");
        $num = $this->sheel->db->num_rows($sql);
        if ($styleid != $this->sheel->config['defaultstyle'] and $num > 1) {
            if ($styleid == '1') {
                die(json_encode(array('response' => 0, 'message' => 'Sorry you cannot delete the default style.  Instead, make not visible to hide this theme from being selected by users.')));
            }
            $this->sheel->db->query("
				DELETE FROM " . DB_PREFIX . "styles
				WHERE styleid = '" . intval($styleid) . "'
				LIMIT 1
			");
            $this->sheel->attachment->recursive_remove_directory(DIR_CSS . $styleid . '/');
            $this->sheel->attachment->recursive_remove_directory(DIR_VIEWS . $styleid . '/');
        } else {
            die(json_encode(array('response' => 0, 'message' => '{_were_sorry_there_seems_to_be_only_1_available_style}')));
        }
        die(json_encode(array('response' => 1, 'message' => 'Successfully deleted theme ID ' . $styleid)));
    }
    /**
     * Function to return site language links header page
     *
     */
    function print_links($returnhtml = false)
    {
        $count = 0;
        $currenturl = PAGEURL;
        $styleslinks = array();
        $sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid, name, visible
			FROM " . DB_PREFIX . "styles
		", 0, null, __FILE__, __LINE__);
        $html = '';
        $html .= '<ul>';
        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
            if ($res['visible']) {
                $count++;
                //$currenturl = $this->sheel->seo->rewrite_url($currenturl, 'styleid=' . $res['styleid']);
                if (strrchr(urldecode($currenturl), '?') == false) {
                    $currenturlx = $currenturl . '?styleid=' . $res['styleid'];
                } else {
                    $currenturlx = $currenturl . '&styleid=' . $res['styleid'];
                }
                if (!$returnhtml) {
                    $styleslinks['styleid'] = ($res['styleid']);
                    $styleslinks['name'] = ($res['name']);
                    $styleslinks['url'] = $currenturlx;
                } else {
                    $html .= '<li><a href="' . $currenturlx . '">' . o($res['name']) . '</a></li>';
                }
            }
        }
        $html .= '</ul>';
        if ($count == 0) {
            $html = '';
        }
        if ($returnhtml) {
            return $html;
        }
        return $styleslinks;
    }
    function styles_count_visible()
    {
        $count = 1;
        $sql = $this->sheel->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid
			FROM " . DB_PREFIX . "styles
			WHERE visible = '1'
		", 0, null, __FILE__, __LINE__);
        $count = $this->sheel->db->num_rows($sql);
        return $count;
    }
}
?>