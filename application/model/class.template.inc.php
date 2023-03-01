<?php
/**
 * Template class to perform the majority of custom template operations in sheel
 *
 * @package      sheel\Template
 * @version		 1.0.0.0
 * @author       sheel
 */
class template
{
	protected $sheel;
	/*
	 * The sheel registry object
	 *
	 * @var	    $sheel
	 */
	var $registry = null;
	/**
	 * This will store the current template into the registry
	 *
	 * @var array
	 */
	var $templateregistry = array();
	/**
	 * This will store the variable modifier pipe action
	 *
	 * @var string
	 */
	var $modifierpipe = '|';
	/**
	 * This will store the opening template variable
	 *
	 * @var string
	 */
	var $start = '{';
	/**
	 * This will store the closing template variable
	 *
	 * @var string
	 */
	var $end = '}';
	/**
	 * This will store the opening template variable for language phrases
	 *
	 * @var string
	 */
	var $phrasestart = '{_';
	/**
	 * This will store the closing template variable for language phrases
	 *
	 * @var string
	 */
	var $phraseend = '}';
	/**
	 * This matches {_some_phrase} {_some_phrase::var1::var2::var3}
	 *
	 * @var string
	 */
	var $phraseregexp = '([^}]*)'; //'([\w\d\:]+)'; //'([\w\d\:(\S)^\{\}]+)'; // '([\w\d\:(\S)]+)'; //'([\w\d\:]+)';
	/**
	 * This will store the tags used to prevent a template from parsing phrase variables
	 * within the html templates (admincp templates area, <text areas>'s etc.)
	 *
	 * @usage <noparse>......</noparse>
	 * @var string
	 */
	var $noparse = 'noparse';
	/**
	 * This will store all current {var_names} used in a template registry
	 *
	 * @var array
	 */
	var $var_names_assoc = array();
	var $regexp = null;
	var $js_phrases_file = null;
	var $template_load_count = 0;
	var $template_views = array();
	var $template_loops = 0;
	var $template_vars = 0;
	var $forcenodebugbar = false;
	/**
	 * This array will store all permitted functions allowed to pass through
	 * the template's <if condition=""> conditionals
	 *
	 * @var array
	 */
	var $safe_functions = array(
		'in_array',
		'is_array',
		'is_numeric',
		'function_exists',
		'isset',
		'empty',
		'defined',
		'array',
		'extension_loaded',
		'can_display_financials',
		'check_access',
		'is_subscription_permissions_ready',
		'has_winning_bidder',
		'has_highest_bidder',
		'has_active_store',
		'has_store',
		'can_display_element',
		'can_post_html',
		'count',
		'strpos',
		'strtolower',
		'is_notification_unsubscribed',
		'card_count',
		'lang_count_canselect',
		'styles_count_visible'
	);
	/**
	 * This array will store all template bits for the templates
	 *
	 * @var array
	 */
	var $meta = array();
	var $templatebits = array();
	var $leftnav = '';
	var $headerfooter = true;
	var $dynamic_phrases = true;
	var $node = null;
	var $cache_key = null;
	/**
	 * This information will determine if the pmb modal widget is loaded
	 */
	var $pmb_modal_loaded = false;
	var $pmb_modal_wysiwyg = null;
	var $report_if_error = false;
	public $isadmincp = false;
	public $findregxp = array();
	var $nothing_to_parse = '';
	var $else_error = '';
	var $runtime = 0;

	var $sheelpages = array('main_PRODUCT1.html', 'nonprofits.html', 'brands.html');
	/*
	 * Constructor
	 *
	 * @param       $registry	    sheel registry object
	 */
	public function __construct($sheel)
	{
		$this->sheel = $sheel;
		$this->sheel->timer->start();
	}
	/*
	 * Loads a template popup into the class (does not use template skinning)
	 *
	 * @param       string       node
	 * @param       string       filename
	 */
	function load_popup($node, $filename, $conditionals = false)
	{
		if (file_exists(DIR_TEMPLATES . $filename)) {
			$this->forcenodebugbar = true;
			$this->templateregistry["$node"] = $this->fgc(DIR_TEMPLATES . $filename);
			if ($conditionals) {
				$this->parse_if_blocks($node);
			}
			$this->template_load_count++;
			$this->template_views[] = $filename;
			$this->templateregistry['currentview'] = $filename;
		}
	}
	/*
	 * Loads an AdminCP template popup into the class (does not use template skinning)
	 *
	 * @param       string       node
	 * @param       string       filename
	 */
	function load_admincp_popup($node, $filename)
	{
		if (file_exists(DIR_TEMPLATES_ADMIN . $filename)) {
			$this->templateregistry["$node"] = $this->fgc(DIR_TEMPLATES_ADMIN . $filename);
			$this->template_load_count++;
			$this->template_views[] = $filename;
		}
	}
	function fgc($file = '')
	{
		if (!file_exists($file . 'x')) { // use stock template
			$html = file_get_contents($file);
		} else { // loads custom template that won't be overwritten on update
			$html = file_get_contents($file . 'x');
		}
		return $html;
	}

	/*
	 * Function to fetch popup and load a template (client or admin) into a specific node
	 *
	 * @param       string       node
	 * @param       string       filename
	 * @param       boolean      is admin cp template
	 * @param       integer      use file path only
	 * @param       string       custom argument
	 */
	function fetch_popup($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		$this->cache_key = 'fetch_' . $node . '_' . $filename . '_' . $admin . '_' . $filepathonly . '_' . $custom;
		$this->isadmincp = ($admin) ? true : false;
		$this->node = $node;
		$this->templateregistry['currentview'] = $filename;
		$shell = 'TEMPLATE_POPUP_SHELL';
		if ($admin) {
			$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_SHELL.html');
			$this->templateregistry['TEMPLATE_header'] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_header.html');
			$this->templateregistry['TEMPLATE_footer'] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_footer.html');
			if (!file_exists(DIR_TEMPLATES_ADMIN . $filename)) {
				$this->templateregistry['template'] = "[$filename does not exist]";
			} else {
				$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES_ADMIN . $filename);
			}
			$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
			$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_header'], $this->templateregistry["$shell"]);
			$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footer'], $this->templateregistry["$shell"]);
			$this->templateregistry["$node"] = $this->templateregistry["$shell"];
			$this->template_views[] = 'TEMPLATE_SHELL.html';
			$this->template_views[] = 'TEMPLATE_header.html';
			$this->template_views[] = $filename;
			$this->template_views[] = 'TEMPLATE_footer.html';
			$this->template_load_count += $this->template_load_count + 4;
		} else {
			//			You can load two different master layouts for cms and home pages.
			//			var_dump($this->templateregistry["currentview"]); // die();

			if ($this->templateregistry["currentview"] == "print_notice_popup.html") {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_POPUP_SHELL.html');
				$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_popup_headerbit.html');
				$this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
				$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
				$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_popup_footerbit.html');
				$this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
				$this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_SHELL.html';
				$this->template_views[] = 'TEMPLATE_popup_headerbit.html';
				$this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
				$this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_popup_footerbit.html';
				$this->template_load_count += $this->template_load_count + 6;
			} else {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_POPUP_SHELL.html');
				$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_popup_headerbit.html');
				$this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
				$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
				$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_popup_footerbit.html');
				$this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
				$this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_SHELL.html';
				$this->template_views[] = 'TEMPLATE_popup_headerbit.html';
				$this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
				$this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_popup_footerbit.html';
				$this->template_load_count += $this->template_load_count + 6;
			}
		}
		$this->sheel->timer->stop();
	}

	/*
	 * Function to fetch and load a template (client or admin) into a specific node
	 *
	 * @param       string       node
	 * @param       string       filename
	 * @param       boolean      is admin cp template
	 * @param       integer      use file path only
	 * @param       string       custom argument
	 */
	function fetch($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		$this->cache_key = 'fetch_' . $node . '_' . $filename . '_' . $admin . '_' . $filepathonly . '_' . $custom;
		$this->isadmincp = ($admin) ? true : false;
		$this->node = $node;
		$this->templateregistry['currentview'] = $filename;
		$shell = 'TEMPLATE_SHELL';

		if ($admin) {
			$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_SHELL.html');
			$this->templateregistry['TEMPLATE_header'] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_header.html');
			$this->templateregistry['TEMPLATE_footer'] = $this->fgc(DIR_TEMPLATES_ADMIN . 'TEMPLATE_footer.html');
			if (!file_exists(DIR_TEMPLATES_ADMIN . $filename)) {
				$this->templateregistry['template'] = "[$filename does not exist]";
			} else {
				$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES_ADMIN . $filename);
			}
			$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
			$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_header'], $this->templateregistry["$shell"]);
			$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footer'], $this->templateregistry["$shell"]);
			$this->templateregistry["$node"] = $this->templateregistry["$shell"];
			$this->template_views[] = 'TEMPLATE_SHELL.html';
			$this->template_views[] = 'TEMPLATE_header.html';
			$this->template_views[] = $filename;
			$this->template_views[] = 'TEMPLATE_footer.html';
			$this->template_load_count += $this->template_load_count + 4;
		} else {
			if ($this->templateregistry["currentview"] == "main_landing_page.html") {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_LANDING_PAGE.html');
				$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_headerbit.html');
				//                $this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_breadcrumbbit.html');
				$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
				$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_footerbit.html');
				//                $this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
//                $this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				//                $this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
//                $this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_LANDING_PAGE.html';
				$this->template_views[] = 'TEMPLATE_headerbit.html';
				//                $this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
//                $this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_footerbit.html';
				$this->template_load_count += $this->template_load_count + 6;
			} else if ($this->templateregistry["currentview"] == "main_custom_landing_page.html") {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_LANDING_PAGE.html');
				if (!isset($this->meta['mobile']) and $this->meta['mobile'] != '1') {
					$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_headerbit.html');
					$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
					$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_footerbit.html');
				}

				//                $this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_landing_breadcrumbbit.html');  
				//                $this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
				//                $this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				//$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				//$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_LANDING_PAGE.html';
				$this->template_views[] = 'TEMPLATE_headerbit.html';
				//                $this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
				//                $this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_footerbit.html';
				$this->template_load_count += $this->template_load_count + 6;
			} else if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_SHELL.html');
				$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_headerbit1.html');
				$this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
				$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
				$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_footerbit1.html');
				$this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
				$this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_SHELL.html';
				$this->template_views[] = 'TEMPLATE_headerbit1.html';
				$this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
				$this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_footerbit1.html';
				$this->template_load_count += $this->template_load_count + 6;
			} else {
				$this->templateregistry["$shell"] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_SHELL.html');
				$this->templateregistry['TEMPLATE_headerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_headerbit.html');
				$this->templateregistry['TEMPLATE_breadcrumbbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
				$this->templateregistry['TEMPLATE_infobar'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
				$this->templateregistry['TEMPLATE_footerbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_footerbit.html');
				$this->templateregistry['TEMPLATE_pluginheaderbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
				$this->templateregistry['TEMPLATE_pluginfooterbit'] = $this->fgc(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
				if (!file_exists(DIR_TEMPLATES . $filename)) {
					$this->templateregistry['template'] = "[$filename does not exist]";
				} else {
					$this->templateregistry['template'] = $this->fgc(DIR_TEMPLATES . $filename);
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
				$this->template_views[] = 'TEMPLATE_SHELL.html';
				$this->template_views[] = 'TEMPLATE_headerbit.html';
				$this->template_views[] = 'TEMPLATE_breadcrumbbit.html';
				$this->template_views[] = 'TEMPLATE_infobar.html';
				$this->template_views[] = $filename;
				$this->template_views[] = 'TEMPLATE_footerbit.html';
				$this->template_load_count += $this->template_load_count + 6;
			}
		}

		$this->sheel->timer->stop();
	}

	/*
	 * Function for parsing {hash[key]} style tags for links throughout the templates by Dexter Tad-y
	 *
	 * @param       string       node
	 * @param       array        hash names ( array('ilpage' => $this->sheel->ilpage) )
	 * @param       integer      parse globals
	 * @param       string       custom template data (optional)
	 */
	function parse_hash($node = '', $hashes, $parseglobals = 0, $data = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		$contents = (isset($this->templateregistry["$node"]) and (empty($data) or $data == '')) ? $this->templateregistry["$node"] : $data;
		if (!empty($contents)) {
			foreach ($hashes as $hname => $hash) {
				$pattern = '/' . $this->start . $hname . '\[([\w\d_]+)\]' . $this->end . '/';
				if (preg_match_all($pattern, $contents, $m) > 0) {
					$replaceable = array();
					$m[1] = array_unique($m[1]);
					foreach ($m[1] as $key) {
						if (isset($hash["$key"])) {
							$replaceable[$this->start . $hname . '[' . $key . ']' . $this->end] = $hash["$key"];
						}
					}
					$contents = str_replace(array_keys($replaceable), array_values($replaceable), $contents);
				}
			}
			$this->templateregistry["$node"] = $contents;
			$this->sheel->timer->stop();
			DEBUG("parse_hash(\$node = $node)", 'FUNCTION', $this->sheel->timer->get(), $caller);
			return $this->templateregistry["$node"];
		}
		$this->sheel->timer->stop();
		DEBUG("parse_hash(\$node = $node)", 'FUNCTION', $this->sheel->timer->get(), $caller);
		return false;
	}
	function hash($hashes, $parseglobals = 0, $data = '')
	{
		$this->parse_hash($this->node, $hashes, $parseglobals, $data);
	}
	/*
	 * Function to load template from the file system.
	 *
	 * @param       string           filename
	 * @param       integer          use template filename commenting
	 */
	function fetch_template($filename = '', $htmlcomments = false)
	{
		$cachekey = "fetch_template_$filename";
		if (file_exists(DIR_TEMPLATES . $filename)) {
			$template = $this->fgc(DIR_TEMPLATES . $filename);
			$this->templateregistry[$cachekey] = addslashes($template);
			if ($htmlcomments) {
				$this->templateregistry[$cachekey] = "<!-- BEGIN TEMPLATE: " . $filename . " -->\n" . $this->templateregistry[$cachekey] . "\n<!-- END TEMPLATE: " . $filename . "-->";
			}
			$this->noparse_cut($cachekey);
			$replaceable = array();
			foreach ($this->sheel->styles->templatevars as $name => $value) {
				if (is_int(mb_strpos($this->templateregistry[$cachekey], $this->start . $name . $this->end)) == true) {
					$replaceable[$this->start . $name . $this->end] = $value;
				}
			}
			$this->templateregistry[$cachekey] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry[$cachekey]);
			$this->noparse_paste($cachekey);
			$template = $this->templateregistry[$cachekey];
			unset($this->templateregistry[$cachekey]);
		}
		return $template;
	}
	/*
	 * Function to set template variable identifiers such as "{" and "}"
	 *
	 * @param       string           starting tag
	 * @param       string           ending tag
	 */
	function set_identifiers($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}
	/*
	 * Function for parsing a <loop name="xxx">yyy</loop name="xxx"> HTML template tag
	 *
	 * @param       string           node
	 * @param       array            loop identifier variable (arrays only)
	 * @param       boolean          force no cache on this loop (default true)
	 */
	function parse_loop($node, $array_name, $nocache = true)
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		if (!$this->is_assoc($array_name)) {
			echo 'Fatal: parse_loop(): Registering template loop variables now require an associative array.<br />';
			return false;
		}
		foreach ($array_name as $key => $value) {
			$lastPos = 0;
			$positions = array();
			if (isset($this->templateregistry["$node"]) and !empty($this->templateregistry["$node"])) {
				while (($lastPos = mb_strpos($this->templateregistry["$node"], '<loop name="' . $key . '">', $lastPos)) !== false) {
					$new_code = '';
					$start_tag = mb_substr($this->templateregistry["$node"], mb_strpos(mb_strtolower($this->templateregistry["$node"]), '<loop name="' . $key . '">'), mb_strlen('<loop name="' . $key . '">'));
					$end_tag = mb_substr($this->templateregistry["$node"], mb_strpos(mb_strtolower($this->templateregistry["$node"]), '</loop name="' . $key . '">'), mb_strlen('</loop name="' . $key . '">'));
					$start = mb_strpos($this->templateregistry["$node"], '<loop name="' . $key . '">') + mb_strlen('<loop name="' . $key . '">');
					$end = mb_strpos($this->templateregistry["$node"], '</loop name="' . $key . '">');
					$code = mb_substr($this->templateregistry["$node"], $start, ($end - $start));
					$positions[] = array('start' => $start, 'end' => $end, 'code' => $code);
					if (preg_match_all('/' . $this->start . '([\w\d_]+)' . $this->end . '/', $code, $variablematches)) {
						$num = count($value);
						for ($i = 0; $i < $num; $i++) {
							if ((!empty($value[$i]) and is_array($value[$i])) or (!empty($value[$i]) and is_object($value[$i]))) {
								$replaceable = array();
								foreach ($variablematches[1] as $keyv) {
									if (isset($value[$i][$keyv]) and !is_array($value[$i][$keyv])) {
										$replaceable[$this->start . $keyv . $this->end] = $value[$i][$keyv];
									}
								}
								$new_code .= str_replace(array_keys($replaceable), array_values($replaceable), $code);
							}
						}
						$this->templateregistry["$node"] = str_replace($start_tag . $code . $end_tag, $new_code, $this->templateregistry["$node"]);
					}
				}
			}
		}
		$this->sheel->timer->stop();
	}
	/*
	 * Function to display error message based on unaccepted functions used within a if condition in a template
	 *
	 * @param       string           function name being used
	 */
	function unsafe_precedence($fn = '')
	{
		$message = 'Template function <strong>' . $fn . '()</strong> is not in the safe functions list. Please remove this expression from the HTML template.';
		$template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html>
					<head>
					<title>The template has encountered a problem.</title>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<style type="text/css">
					body { background-color: white; color: black; }
					#container { width: 400px; }
					#message   { width: 400px; color: black; background-color: #FFFFCC; }
					#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
					.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
					a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
					a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
					</style>
					</head>
					<body>
					<table cellpadding="3" cellspacing="5" id="container">
					<tr>
							<td id="bodytitle" width="100%">Template error</td>
					</tr>
					<tr>
							<td class="bodytext" colspan="2">The HTML template renderer has encountered a problem.</td>
					</tr>
					<tr>
							<td colspan="2"><hr /></td>
					</tr>
					<tr>
							<td class="bodytext" colspan="2">
									Please try the following:
									<ul>
											<li>Load the page again by clicking the <a href="#" onclick="window.location = window.location;">Refresh</a> button in your web browser.</li>
											<li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
									</ul>
							</td>
					</tr>
					<tr>
							<td class="bodytext" colspan="2">The technical staff have been notified of the error.  We apologise for any inconvenience.</td>
					</tr>
					<tbody style="display:none">
					<tr>
							<td colspan="2"><hr /></td>
					</tr>
					<tr>
							<td class="bodytext" colspan="2">' . $message . '</td>
					</tr>
					</tbody>
					</table>
					</body>
					</html>';
		// tell the search engines that our service is temporarily unavailable to prevent indexing db errors
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');
		echo $template;
		exit();
	}
	/*
	 * Function to handle the regular expressions used within the if condition parser
	 *
	 * @param       string           template content
	 */
	function pr_callback($string = '')
	{
		global $sheel; // <-- required for html templates referencing $sheel->xxx
		$this->else_error = $this->nothing_to_parse = 0;
		$sheel = $this->sheel; // <-- allows html templates conditionals to use $sheel->config[xxx]


		$string = substr($string, strpos($string, 'condition'));
		preg_match("/condition=([\"'])((?:(?!\\1).)*)\\1/is", $string, $condition);
		$quotepos = $quotepos2 = $pos = 0;
		while (true) {
			$endpos = strpos($string, '>', $pos);
			if ($quotepos !== false) {
				$quotepos = strpos($string, '"', $pos);
			}
			if ($quotepos2 !== false) {
				$quotepos2 = strpos($string, "'", $pos);
			}
			if (($quotepos < $endpos and $quotepos !== false) or ($quotepos2 < $endpos and $quotepos2 !== false)) {
				if (($quotepos < $quotepos2 or $quotepos2 === false) and $quotepos !== false) {
					// we have " - quotes here
					$quotepos = strpos($string, '"', $quotepos + 1);

					if ($quotepos !== false)
						$pos = $quotepos + 1;

					// back to top of the loop and search for endpos again
					continue;
				}
				if (($quotepos2 < $quotepos or $quotepos === false) and $quotepos2 !== false) {
					// we have ' - quotes here
					$quotepos2 = strpos($string, "'", $quotepos2 + 1);

					if ($quotepos2 !== false)
						$pos = $quotepos2 + 1;

					// back to top of the loop and search for endpos again
					continue;
				}
			}
			if (($quotepos === false or $quotepos > $endpos) and ($quotepos2 === false or $quotepos2 > $endpos)) {
				$pos = $endpos;
				break;
			}
			if ($endpos === false) {
				$pos = $endpos;
			}
		}
		$string = substr($string, $pos + 1);
		$string = substr($string, 0, strrpos($string, '<'));
		$iels = strpos($string, '<else />');
		$no_start = $yes_end = false;
		$pos = -1;
		$level = 0;
		while ($iels !== false) {
			$is = strpos($string, '<if ', $pos + 1);
			$ie = strpos($string, '</if>', $pos + 1);
			$iels = strpos($string, '<else />', $pos + 1);
			if (($is > $iels or $is === false) and ($ie > $iels or $ie === false) and $level == 0) {
				if ($iels !== false) {
					$yes_end = strpos($string, '<else />', max($pos, 0));
					$no_start = $yes_end + strlen('<else />');
				}
				break;
			}
			if (($is < $ie and $is !== false) or ($is !== false and $ie === false)) {
				$level++;
				$pos = $is;
			}
			if (($is > $ie and $ie !== false) or ($is === false and $ie !== false)) {
				$level--;
				$pos = $ie;
			}
		}
		if ($yes_end === false) {
			$no_start = false;
			$yes_end = strlen($string);
		}
		$yes_code = substr($string, 0, $yes_end);
		$no_code = '';
		if ($no_start !== false) {
			$no_end = strlen($string);
			$no_code = substr($string, $no_start, ($no_end - $no_start));
		}
		$condition = ((isset($condition[2])) ? $condition[2] : '');
		$condition = preg_replace_callback(
			'/(([a-z_][a-z_0-9]*)\\(.*?\\))/i',
			function ($matches) {
				return (in_array(strtolower($matches[2]), $this->safe_functions) ? $matches[1] : $this->unsafe_precedence($matches[2]));
			},
			$condition
		);
		$condition = preg_replace(
			"/\\$([a-z][a-z_0-9]*)/is",
			"\$GLOBALS['\\1']",
			$condition
		);


		if (eval("return ($condition);")) {
			return $yes_code;
		} else {
			return $no_code;
		}
	}
	/*
	 * Functions for returning <if condition=""> errors
	 *
	 * @param       void
	 */
	private function report_if_error($html = '', $if_pos = 0, $ending = false)
	{
		$start = $if_pos;
		$end = strpos($html, '>', $if_pos);
		if ($ending == false) {
			$start = $if_pos;
			$end = strpos($html, '>', $if_pos);
			if ($end === false) {
				$start = strpos($html, '"', $if_pos);
				$end = strpos($html, '"', $start + 1);
				$start2 = strpos($html, "'", $if_pos);
				$end2 = strpos($html, "'", $start + 1);
				if (($start2 < $start and $start2 !== false) or $start === false) {
					$start = $start2;
					$end = $end2;
				}
			}
		}
		$if_cond = '';
		if ($start !== false and $end !== false) {
			$if_cond = substr($html, $start, $end - $start + 1);
		} else {
			$if_cond = 'unknown';
		}
		if ($ending) {
			$message = 'No ending &lt;/if&gt; found for: ' . sheel_htmlentities(stripslashes($if_cond)) . '<br><br>HTML code: <pre class="codex">' . sheel_htmlentities(substr(stripslashes($html), $if_pos, 150)) . ' ...</pre>';
		} else {
			$message = sheel_htmlentities(stripslashes($if_cond)) . ' is missing an ending &lt;/if&gt; tag!<br><br>HTML code: <pre class="codex">' . sheel_htmlentities(substr(stripslashes($html), $if_pos, 150)) . ' ...</pre>';
		}
		$template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html>
						<head>
						<title>The template has encountered a problem.</title>
						<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
						<style type="text/css">
						body { background-color: white; color: black; }
						#container { width: 400px; }
						#message   { width: 400px; color: black; background-color: #FFFFCC; }
						#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
						.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
						a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
						a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
						.codex     { margin: 10px 0px 0px 0px;padding:4px;width: 100%;font-family: monospace;font-size: 13px;color:#000000;background-color:#ccc; cursor: crosshair;}
						</style>
						</head>
						<body>
						<table cellpadding="3" cellspacing="5" id="container">
						<tr>
								<td id="bodytitle" width="100%">Template error</td>
						</tr>
						<tr>
								<td class="bodytext" colspan="2">The HTML template has encountered a problem.</td>
						</tr>
						<tr>
								<td colspan="2"><hr /></td>
						</tr>
						<tr>
								<td class="bodytext" colspan="2">
										Please try the following:
										<ul>
												<li>Load the page again by clicking the <a href="#" onclick="window.location = window.location;">Refresh</a> button in your web browser.</li>
												<li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
										</ul>
								</td>
						</tr>
						<tr>
								<td class="bodytext" colspan="2">The technical staff have been notified of the error.  We apologise for any inconvenience.</td>
						</tr>
						<tr>
								<td colspan="2"><hr /></td>
						</tr>
						<tr>
								<td class="bodytext" colspan="2">' . $message . '</td>
						</tr>
						</table>
						</body>
						</html>';
		// tell the search engines that our service is temporarily unavailable to prevent indexing db errors
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');
		echo $template;
		exit();
	}
	/*
	 * Functions for parsing <if condition="">xxx<else />yyy</if> template conditionals
	 *
	 * @param       string       node
	 * @param       string       template data (optional)
	 * @param       boolean      apply slashes to template string/data (default false)
	 */
	private function parse_if_blocks($node = '', $content = '', $addslashes = false)
	{
		$this->sheel->timer->start();
		$caller = get_calling_location();
		$template_str = ((isset($this->templateregistry["$node"]) and empty($content)) ? $this->templateregistry["$node"] : $content);
		$pos = $opening_tags = $level = 0;
		$start = $start2 = -1;
		while (true) {
			$pos = strpos($template_str, '</if ');
			$end = strpos($template_str, '>', $pos);
			if ($end === false and !empty($template_str) and $pos > 0) {
				echo '<strong>Warning:</strong> &lt;/if&gt; tag not closed within template!';
				break;
			}
			if ($pos === false) {
				break;
			}
			$template_str = substr($template_str, 0, $pos) . '</if>' . substr($template_str, $end + 1);
		}
		while (true) {
			$start2 = strpos($template_str, '<if ', $start + 1);
			if ($start2 !== false) {
				$end = strpos($template_str, '</if>', $start + 1);
			} else {
				break;
			}
			$start = $start2;
			if ($end === false) {
				$this->report_if_error($template_str, $start, true);
			}
			if ($start > $end) {
				$this->report_if_error($template_str, $start);
			}
			// start processing if conditional block!
			$end = $start - 1;
			while (true) {
				$is = strpos($template_str, '<if ', $end + 1);
				$ie = strpos($template_str, '</if>', $end + 1);
				if (($is < $ie and $is !== false) or ($is !== false and $ie === false)) {
					$level++;
					$end = $is;
				}
				if (($is > $ie and $ie !== false) or ($is === false and $ie !== false)) {
					$level--;
					$end = $ie;
				}
				if ($ie === false and $is === false and $level != 0) {
					$end = false;
					break;
				}
				if ($level == 0 and ($ie < $is or $is === false)) {
					$end = $ie;
					break;
				}
			}
			if ($start < $end) {
				$a = substr($template_str, 0, $start);
				$b = substr($template_str, $end + 5);
				$c = $this->pr_callback(stripslashes(substr($template_str, $start, $end - $start + 5)));
				$template_str = ($addslashes) ? $a . addslashes($c) . $b : $a . $c . $b;
				$start = -1;
			}
		}
		if (empty($content)) {
			$this->templateregistry["$node"] = $template_str;
		} else {
			return $template_str;
		}
		$this->sheel->timer->stop();
	}
	/*
	 * Function is used only by the register_template_variables() method, for going through arrays and extracting the values.
	 *
	 * @param       string           node
	 * @param       array            array of variable names (key => value)
	 */
	private function traverse_array_assoc($node = '', $array)
	{
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$this->traverse_array_assoc($node, $value);
			} else {
				$this->var_names_assoc["$node"]["$key"] = $value;
			}
		}
	}
	private function is_assoc($arr = array())
	{
		if (empty($arr) or count($arr) <= 0 or !is_array($arr)) {
			return false;
		}
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	/*
	 * Function to register template variables and assigns them to $this->var_names
	 *
	 * @param       string           node
	 * @param       array            variable names (key => value)
	 */
	private function register_template_variables($node = '', $vars)
	{
		if (!empty($vars) and is_array($vars)) {
			$this->traverse_array_assoc($node, $vars); // builds $this->var_names_assoc
		}
	}
	/*
	 * Function to remove duplicate values in an array
	 *
	 * @param       array           array of values
	 */
	function remove_duplicate_template_variables($array)
	{
		$newarray = array();
		if (is_array($array)) {
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$val2 = $this->remove_duplicate_template_variables($val);
				} else {
					$val2 = $val;
					$newarray = array_unique($array);
					break;
				}
				if (!empty($val2)) {
					$newarray["$key"] = $val2;
				}
			}
		}
		return $newarray;
	}
	function noparse_cut($node = '')
	{
		// let's search template for <noparse></noparse> tags
		// so this function can rip those blocks out if required (before we do all phrases in template in next step)
		preg_match_all("'\<$this->noparse\>(.*)\</$this->noparse\>'isU", $this->templateregistry["$node"], $this->findregxp);
		if (!empty($this->findregxp[0]) and $this->findregxp[0] > 0) {
			for ($i = 0; $i < count($this->findregxp[0]); $i++) {
				$this->templateregistry["$node"] = str_replace($this->findregxp[0]["$i"], "~~$this->noparse~~$i~~$this->noparse~~", $this->templateregistry["$node"]);
			}
		}
	}
	function noparse_paste($node = '')
	{
		if (!empty($this->findregxp[0]) and $this->findregxp[0] > 0) { // let's piece back together the template tags used to filter out parsing of phrases
			for ($i = 0; $i < count($this->findregxp[0]); $i++) {
				$this->findregxp[0]["$i"] = str_replace("<$this->noparse>", '', $this->findregxp[0]["$i"]);
				$this->findregxp[0]["$i"] = str_replace("</$this->noparse>", '', $this->findregxp[0]["$i"]);
			}
			for ($i = 0; $i < count($this->findregxp[0]); $i++) {
				$this->templateregistry["$node"] = str_replace("~~$this->noparse~~$i~~$this->noparse~~", $this->findregxp[0]["$i"], $this->templateregistry["$node"]);
			}
		}
		$this->findregxp = array();
	}
	/*
	 * Function to parse template variables within a template
	 *
	 * @param       node           template node
	 */
	function parse_template_variables($node = '')
	{
		$accountdata = $temp = $limits = array();
		$membershipstatus = $attachgauge = '';
		$this->sheel->timer->start();
		$caller = get_calling_function();

		$this->noparse_cut($node);

		//		var_dump($this->sheel->GPC); exit();

		$cid = ((isset($this->sheel->GPC['cid'])) ? intval($this->sheel->GPC['cid']) : 0);
		if (defined('LOCATION') and LOCATION != 'admin') {
			if (defined('LOCATION') and (LOCATION != 'cron' and LOCATION != 'attachment' and LOCATION != 'pmb' and LOCATION != 'stylesheet' and LOCATION != 'upload' and LOCATION != 'ipn')) {
				//if (!$this->sheel->categories->buildarray)
				//{
				//	$this->sheel->categories->build_array('product', $_SESSION['sheeldata']['user']['slng'], 0, true, '', '', 0, -1, 2, 0);
				//}
				if (isset($this->meta['area']) and $this->meta['area'] == 'search_advanced') {
					if (($search_category_pulldown_v4 = $this->sheel->cache->fetch("searchcategorypulldown_big_" . $cid)) === false) { // pulldown select
						if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {
							//$search_category_pulldown_v4 = $this->sheel->categories_pulldown->print_root_category_pulldown_sheel($cid, 'product', 'cid', $_SESSION['sheeldata']['user']['slng'], '', true, false, false, 'cidfield', 'form-group setting-select form-control');
							//$this->sheel->cache->store("searchcategorypulldown_big_" . $cid, $search_category_pulldown_v4);
						} else {
							//$search_category_pulldown_v4 = $this->sheel->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['sheeldata']['user']['slng'], '', true, false, false, 'cidfield', 'draw-select');
							//$this->sheel->cache->store("searchcategorypulldown_big_" . $cid, $search_category_pulldown_v4);
						}


					}
				} else {
					if (($search_category_pulldown_v4 = $this->sheel->cache->fetch("searchcategorypulldown_" . $cid)) === false) { // pulldown select

						if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {
							//$search_category_pulldown_v4 = $this->sheel->categories_pulldown->print_root_category_pulldown_sheel($cid, 'product', 'cid', $_SESSION['sheeldata']['user']['slng'], '', true, false, false, 'cidfield', 'form-group setting-select form-control');
							//$this->sheel->cache->store("searchcategorypulldown_big_" . $cid, $search_category_pulldown_v4);
						} else {
							//$search_category_pulldown_v4 = (((!isset($this->sheel->show['slimheader']) OR (isset($this->sheel->show['slimheader']) AND $this->sheel->show['slimheader'] == false)))
							//    ? $this->sheel->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['sheeldata']['user']['slng'], '', true, false, false, 'cidfield', 'draw-select-small')
							//    : '');
							//$this->sheel->cache->store("searchcategorypulldown_" . $cid, $search_category_pulldown_v4);
						}


					}
				}
				if (($categorypulldownpopup = $this->sheel->cache->fetch("categorypulldownpopup_1col_" . $cid)) === false) { // search dropdown categories
					if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {
						//$categorypulldownpopup = (((!isset($this->sheel->show['slimheader']) OR (isset($this->sheel->show['slimheader']) AND $this->sheel->show['slimheader'] == false)) OR (isset($this->meta['area']) AND ($this->meta['area'] == 'main_stores' OR $this->meta['area'] == 'main_stores_category'))) ? $this->sheel->categories_parser_v5->print_search_category_list_sheel('product', $_SESSION['sheeldata']['user']['slng'], $cid) : '');
						//$this->sheel->cache->store("categorypulldownpopup_1col_" . $cid, $categorypulldownpopup); 
					} else {
						//$categorypulldownpopup = (((!isset($this->sheel->show['slimheader']) OR (isset($this->sheel->show['slimheader']) AND $this->sheel->show['slimheader'] == false)) OR (isset($this->meta['area']) AND ($this->meta['area'] == 'main_stores' OR $this->meta['area'] == 'main_stores_category'))) ? $this->sheel->categories_parser_v5->print_search_category_list('product', $_SESSION['sheeldata']['user']['slng'], $cid) : '');
						//$this->sheel->cache->store("categorypulldownpopup_1col_" . $cid, $categorypulldownpopup); 
					}


				}
				if (($shopbycategory = $this->sheel->cache->fetch("shopbycategory_product")) === false) { // new shop by category full menus
					if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {
						//$shopbycategory = ((!isset($this->sheel->show['slimheader']) OR (isset($this->sheel->show['slimheader']) AND $this->sheel->show['slimheader'] == false)) ? $this->sheel->categories_parser_v5->print_category_navigation_sheel() : '');
						$this->sheel->cache->store("shopbycategory_product", $shopbycategory);
					} else {
						//$shopbycategory = ((!isset($this->sheel->show['slimheader']) OR (isset($this->sheel->show['slimheader']) AND $this->sheel->show['slimheader'] == false)) ? $this->sheel->categories_parser_v5->print_category_navigation() : '');
						$this->sheel->cache->store("shopbycategory_product", $shopbycategory);
					}
				}
			}
		}

		//$accountdata = $this->sheel->accounting->fetch_user_balance($_SESSION['sheeldata']['user']['userid']);
		$accountbalance = $accountdata['available_balance'];

		$accountbalance = $this->sheel->currency->format($accountbalance);

		//$membershipstatus = (($this->sheel->subscription->has_active_subscription($_SESSION['sheeldata']['user']['userid'])) ? '{_active}' : '{_inactive}');
		$pointsavailable = ((isset($accountdata['rewardpoints'])) ? $accountdata['rewardpoints'] : '');
		//$attachgauge = $this->sheel->attachment->print_spaceleft_gauge($_SESSION['sheeldata']['user']['userid'], true);
		//$temp = $this->sheel->accounting->fetch_user_balance_owing($_SESSION['sheeldata']['user']['userid']);
		$balanceowing = $this->sheel->currency->format($temp['balanceowing']);
		unset($temp);

		$this->templatebits = array(
			'headinclude' => ((isset($this->meta['headinclude'])) ? $this->meta['headinclude'] : ''),
			'footinclude' => ((isset($this->meta['footinclude'])) ? $this->meta['footinclude'] : ''),
			'onload' => ((isset($this->meta['onload']) and !empty($this->meta['onload'])) ? ' onload="' . $this->meta['onload'] . '"' : ''),
		);

		if ($this->isadmincp and defined('LOCATION') and LOCATION == 'admin') {

			//$limits = $this->sheel->admincp->usersitelimits();
			$this->templatebits['loadaverage'] = $this->sheel->loadaverage;
			$this->templatebits['userlimit'] = $limits['userlimit'];
			$this->templatebits['sitelimit'] = $limits['sitelimit'];
			unset($limits);
		} else {

			$this->templatebits['v3left_nav'] = (!empty($this->leftnav) ? $this->leftnav : '');
			$this->templatebits['search_category_pulldown_v4'] = isset($search_category_pulldown_v4) ? $search_category_pulldown_v4 : '';
			$this->templatebits['categorypulldownpopup'] = isset($categorypulldownpopup) ? $categorypulldownpopup : '';
			$this->templatebits['shopbycategory'] = isset($shopbycategory) ? $shopbycategory : '';
			$this->templatebits['languagelinks'] = $this->sheel->language->print_links(true);
			$this->templatebits['themelinks'] = $this->sheel->styles->print_links();
			$this->templatebits['login_include'] = ((isset($this->meta['loginclient'])) ? $this->meta['loginclient'] : '');
			$this->templatebits['q'] = (isset($this->sheel->GPC['q']) ? o(urldecode($this->sheel->GPC['q'])) : '');
			$this->templatebits['cid'] = $cid;
			//$this->templatebits['cartcount'] = $this->sheel->cart->count();

			if (in_array($this->templateregistry["currentview"], $this->sheelpages)) {

				$this->templatebits['headerpagelinks'] = $this->sheel->pagelinks('header-home');
				$this->templatebits['footerpagelinks'] = $this->sheel->pagelinks('footer');
				$this->templatebits['mobilefooterpagelinks'] = $this->sheel->pagelinks('footer', true);
			} else {

				$this->templatebits['headerpagelinks'] = $this->sheel->pagelinks('header');
				$this->templatebits['footerpagelinks'] = $this->sheel->pagelinks('footer');
				$this->templatebits['mobilefooterpagelinks'] = $this->sheel->pagelinks('footer', true);

			}

			$this->templatebits['topdynamic'] = ((isset($this->meta['topdynamic']) and !empty($this->meta['topdynamic'])) ? $this->meta['topdynamic'] : '');
			$this->templatebits['topsubnav'] = ((isset($this->meta['topsubnav']) and !empty($this->meta['topsubnav'])) ? $this->meta['topsubnav'] : '');
		}

		$this->templatebits['template_metatitle'] = (!empty($metatitle) ? $metatitle : '{_template_metatitle}');
		$this->templatebits['template_metadescription'] = (!empty($this->meta['description']) ? $this->meta['description'] : '');
		$this->templatebits['template_metakeywords'] = (!empty($this->meta['keywords']) ? $this->meta['keywords'] : '');
		$this->templatebits['official_time'] = $this->sheel->config['official_time'];
		$this->templatebits['template_charset'] = $this->sheel->config['template_charset'];
		$this->templatebits['template_languagecode'] = $this->sheel->config['template_languagecode'];
		$this->templatebits['area_title'] = (isset($this->meta['areatitle']) ? $this->meta['areatitle'] : '');
		$this->templatebits['page_title'] = (isset($this->meta['pagetitle']) ? $this->meta['pagetitle'] : '');
		$this->templatebits['company_name'] = COMPANY_NAME;
		$this->templatebits['site_name'] = SITE_NAME;
		$this->templatebits['site_email'] = SITE_EMAIL;
		$this->templatebits['site_contact'] = SITE_CONTACT;
		$this->templatebits['site_phone'] = SITE_PHONE;
		$this->templatebits['site_address'] = SITE_ADDRESS;
		$this->templatebits['https_server'] = HTTPS_SERVER;
		$this->templatebits['http_server'] = HTTP_SERVER;
		$this->templatebits['https_server_admin'] = HTTPS_SERVER_ADMIN;
		$this->templatebits['http_server_admin'] = HTTP_SERVER_ADMIN;
		$this->templatebits['http_server_cdn'] = ((defined('HTTP_CDN_SERVER')) ? HTTP_CDN_SERVER : HTTP_SERVER);
		$this->templatebits['https_server_cdn'] = ((defined('HTTPS_CDN_SERVER')) ? HTTPS_CDN_SERVER : HTTPS_SERVER);
		$this->templatebits['dir_server_root'] = DIR_SERVER_ROOT;
		$this->templatebits['rand()'] = rand(1, 999999);
		$this->templatebits['time()'] = time();
		$this->templatebits['keywords'] = (!empty($this->sheel->keywords) ? $this->sheel->keywords : '');
		$this->templatebits['s'] = (!empty($_COOKIE['s']) ? $_COOKIE['s'] : session_id());
		$this->templatebits['token'] = TOKEN;
		//$this->templatebits['pageurl'] = $this->sheel->seo->remove_querystring_var(PAGEURL, 'note');
		//$this->templatebits['pageslug'] = preg_replace("/[\-]/", " ", strtolower(trim(explode('<div', $this->meta['areatitle'])[0])));
		$this->templatebits['pageslug'] = $this->createPageSlug($this->meta['areatitle']);
		$this->templatebits['pageurl_urlencoded'] = urlencode($this->templatebits['pageurl']);
		$this->templatebits['request_uri'] = ((isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '');
		$this->templatebits['request_uriencoded'] = ((isset($_SERVER['REQUEST_URI'])) ? urlencode($_SERVER['REQUEST_URI']) : '');
		$this->templatebits['ajaxurl'] = AJAXURL;
		$this->templatebits['buildversion'] = $this->sheel->buildversion;
		$this->templatebits['year'] = date('Y');
		$this->templatebits['csrf'] = (!empty($_SESSION['sheeldata']['user']['csrf']) ? $_SESSION['sheeldata']['user']['csrf'] : md5(uniqid(mt_rand(), true)));
		$this->templatebits['site_id'] = SITE_ID;
		$this->templatebits['template_ilversion'] = $this->sheel->config['current_version'];
		$this->templatebits['imgrel'] = $this->sheel->config['imgrel'];
		$this->templatebits['img'] = $this->sheel->config['img'];
		$this->templatebits['imgcdn'] = $this->sheel->config['imgcdn'];
		$this->templatebits['imguploads'] = $this->sheel->config['imguploads'];
		$this->templatebits['imguploadscdn'] = $this->sheel->config['imguploadscdn'];
		$this->templatebits['css'] = $this->sheel->config['css'];
		$this->templatebits['csscdn'] = $this->sheel->config['csscdn'];
		$this->templatebits['js'] = $this->sheel->config['js'];
		$this->templatebits['jscdn'] = $this->sheel->config['jscdn'];
		$this->templatebits['fonts'] = $this->sheel->config['fonts'];
		$this->templatebits['fontscdn'] = $this->sheel->config['fontscdn'];
		$this->templatebits['currencysymbollocal'] = $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_local'];
		$this->templatebits['currencysymbol'] = $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'];
		$this->templatebits['currencysymbolright'] = $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['symbol_right'];
		$this->templatebits['currencyabbrev'] = $this->sheel->currency->currencies[$this->sheel->config['globalserverlocale_defaultcurrency']]['currency_abbrev'];
		$this->templatebits['sheelaid'] = $this->sheel->config['globalserversettings_sheelaid'];
		$this->templatebits['facebookurl'] = $this->sheel->config['globalserversettings_facebookurl'];
		$this->templatebits['twitterurl'] = $this->sheel->config['globalserversettings_twitterurl'];
		$this->templatebits['googleplusurl'] = $this->sheel->config['globalserversettings_googleplusurl'];
		$this->templatebits['hpadurl1'] = $this->sheel->config['globalserversettings_homepageadurl'];
		$this->templatebits['hpadurl2'] = $this->sheel->config['globalserversettings_homepageadurl2'];
		$this->templatebits['hpadurl3'] = $this->sheel->config['globalserversettings_homepageadurl3'];
		$this->templatebits['hpadurl4'] = $this->sheel->config['globalserversettings_homepageadurl4'];
		$this->templatebits['hpadurl5'] = $this->sheel->config['globalserversettings_homepageadurl5'];
		$this->templatebits['hpadurl6'] = $this->sheel->config['globalserversettings_homepageadurl6'];
		$this->templatebits['hpadurl7'] = $this->sheel->config['globalserversettings_homepageadurl7'];
		$this->templatebits['hpadurl8'] = $this->sheel->config['globalserversettings_homepageadurl8'];
		$this->templatebits['section'] = ((!empty($this->meta['area'])) ? $this->meta['area'] : '');
		$this->templatebits['footerdebug'] = (((!$this->forcenodebugbar)) ? $this->sheel->template_debug->print($node) : '');
		$this->templatebits['js_phrases_content'] = $this->init_js_phrase_array($node);
		$this->init_js_phrase_array($node);

		$this->templatebits['accountbalance'] = $accountbalance;
		$this->templatebits['balanceowing'] = $balanceowing;
		$this->templatebits['membershipstatus'] = $membershipstatus;
		$this->templatebits['pointsavailable'] = $pointsavailable;
		$this->templatebits['attachgauge'] = $attachgauge;



		// merge our new template bits into existing template variable array
		$iltemplate = array_merge($this->templatebits, $this->sheel->styles->templatevars);
		foreach ($iltemplate as $name => $value) {
			// find all occurrences of {template_variables}
			if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $name . $this->end)) == true) {
				$this->templateregistry["$node"] = str_replace($this->start . $name . $this->end, $value, $this->templateregistry["$node"]);
			}
		}
		unset($iltemplate, $this->templatebits);
		$this->noparse_paste($node);


		$this->sheel->timer->stop();
	}

	/*
	 * Create the page slug out of the title of the
	 * existing page, if its homepage then it will
	 * return 'home'. The output of this value is
	 * passed in $this->templatebits['pageslug']
	 * and is being used as class on body tag.
	 */
	function createPageSlug($title)
	{
		$title = urlencode($title);
		$first = "%7B_";
		$last = "%7D";
		$slug = explode($first, $title);

		if (isset($slug[1])) {
			$slug = explode($last, $slug[1]);

			if ($slug[0] == "main_menu") {
				return 'home';
			} else {
				return $slug[0];
			}
		}
	}

	function parse_template_phrases($node = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_function();
		$phrasepattern = '/' . $this->phrasestart . $this->phraseregexp . $this->phraseend . '/'; // matches {_some_phrase} and {_some_phrase::var1::var2::var3}
		$this->noparse_cut($node);
		if (isset($this->templateregistry[$node]) and preg_match_all($phrasepattern, $this->templateregistry[$node], $phrasematches) == true) {
			$varnames = array_values(array_unique($phrasematches[1]));
			$replaceable = array();
			$querystr = '';
			foreach ($varnames as $key => $value) {
				if (!empty($value)) {
					if (stristr($value, '::')) {
						$tmp = explode('::', $value);
						$querystr .= empty($querystr) ? "'_" . $this->sheel->db->escape_string($tmp[0]) . "'" : ", '_" . $this->sheel->db->escape_string($tmp[0]) . "'";
						unset($tmp);
					} else {
						$querystr .= empty($querystr) ? "'_" . $this->sheel->db->escape_string($value) . "'" : ", '_" . $this->sheel->db->escape_string($value) . "'";
					}
				}
			}
			if (!empty($querystr)) {
				$querystr = 'p.varname IN (' . $querystr . ')';
				$query = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.varname, p.text_" . $_SESSION['sheeldata']['user']['slng'] . " AS text
					FROM " . DB_PREFIX . "language_phrases p
					WHERE $querystr
				", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($query) > 0) {
					while ($cache = $this->sheel->db->fetch_array($query, DB_ASSOC)) {
						$cache['text'] = str_replace(array("\r\n", "\n", "\r"), '', $cache['text']);
						$phrase[$cache['varname']] = stripslashes($this->sheel->common->un_htmlspecialchars($cache['text']));
					}
					unset($cache);
				}
				unset($query, $querystr, $phrasesearch, $phrasereplace);
			}
			foreach ($varnames as $key => $value) {
				if (!empty($value)) // congrats_youve_sold_x_items:13500 & payment_to_seller_completed_using:{buyerpaymethodplain
				{
					if (stristr($value, '::')) // congrats_youve_sold_x_items::13500
					{
						$tmp = explode('::', $value); // congrats_youve_sold_x_items
						$c = 0;
						foreach ($tmp as $var) {
							if ($c > 0) {
								$replacements[] = $var;
							}
							$c++;
						}
						$replaceable[$this->phrasestart . $value . $this->phraseend] = ((isset($phrase["_$tmp[0]"])) ? $this->sheel->language->construct_phrase("_$tmp[0]", $replacements) : $this->phrasestart . $tmp[0] . $this->phraseend);
						unset($tmp, $replacements);
					} else {
						$replaceable[$this->phrasestart . $value . $this->phraseend] = ((isset($phrase["_$value"])) ? $phrase["_$value"] : $this->phrasestart . $value . $this->phraseend);
					}
				}
			}
			$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
			unset($replaceable, $phrasematches, $key, $value, $varnames);
		}
		unset($phrase);
		$this->noparse_paste($node);
		$this->sheel->timer->stop();
		return ((isset($this->templateregistry[$node])) ? $this->templateregistry[$node] : '');
	}
	/*
	 * Function for reading and parsing the template's special tags/variables.
	 *
	 * @param       string
	 */
	function parse_template($node = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_function();
		$nodes = explode(',', $node);
		$this->parse_session_globals($node); // parses {session[xx]}
		$this->parse_config_globals($node); // parses {config[xx]}
		$this->parse_htmlblocks_globals($node); // parses {htmlblock[xx]}
		$accepted_nodes = array('head', 'main', 'foot');

		if (in_array($node, $accepted_nodes)) {
			$this->parse_template_variables($node);
		}
		$this->parse_template_varnames_v5($node);
		$this->parse_template_phrases($node);
		unset($node, $nodes);
		$this->sheel->timer->stop();
		DEBUG("parse_template()", 'FUNCTION', $this->sheel->timer->get(), $caller);
	}
	private function parse_template_varnames_v5($node = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_function();
		$y = 0;
		if (isset($this->var_names_assoc["$node"]) or !empty($this->var_names_assoc["$node"])) {
			if (!$this->is_assoc($this->var_names_assoc["$node"])) {
				echo 'Fatal: pprint(): Registering template variables now require an associative array.<br />';
				print_r($this->var_names_assoc["$node"]);
				return false;
			}
			$this->noparse_cut($node);
			foreach ($this->var_names_assoc["$node"] as $key => $value) {
				preg_match_all("/" . $this->start . $key . $this->end . "/", $this->templateregistry["$node"], $m);
				if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $key . $this->end))) {
					if (!is_array($value)) {
						$this->templateregistry["$node"] = str_replace($this->start . $key . $this->end, $value, $this->templateregistry["$node"]);
						$y++;
					}
				}
			}
			$this->noparse_paste($node);
		}
		$this->sheel->timer->stop();
	}
	/*
	 * Function to parse template collapsables
	 *
	 * @param       node            template node
	 */
	function parse_template_collapsables($node = '')
	{
		/*
		 * Usage:
		 * <a href="javascript:void(0)" onclick="return toggle('expert_{user_id}');"><img id="collapseimg_expert_{user_id}" src="{imgcdn}expand{collapse[collapseimg_expert_{user_id}]}.gif" border="0" alt=""></a>
		 * <tbody id="collapseobj_expert_{user_id}" style="{collapse[collapseobj_expert_{user_id}]}">
		 */
		//print_r($this->sheel->ilcollapse);
		if (!empty($this->sheel->ilcollapse)) {
			foreach ($this->sheel->ilcollapse as $key => $value) {
				$replaceable = array();
				$replaceable[$this->start . 'collapse[' . $key . ']' . $this->end] = $value;
				$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
			}
		}
		// find all occurrences of {collapse[XXXXX]}
		$cname = 'collapse';
		$pattern = '/' . $this->start . $cname . '\[([\w\d_]+)\]' . $this->end . '/';
		if (isset($this->templateregistry[$node]) and preg_match_all($pattern, $this->templateregistry[$node], $m) !== false) {
			$replaceable = array();
			foreach ($m[1] as $key) {
				$replaceable[$this->start . $cname . '[' . $key . ']' . $this->end] = '';
			}
			$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
		}
	}
	/*
	 * Function lookup in source for js phrases and construct phrase array.
	 *
	 * @param       string
	 */
	function init_js_phrase_array($node = '')
	{
		$this->sheel->timer->start();
		$caller = get_calling_function();
		$slng = isset($_SESSION['sheeldata']['user']['slng']) ? $_SESSION['sheeldata']['user']['slng'] : 'eng';
		$include_js_phrases = $where = '';
		$charsearch = array("'", '"');
		$charreplace = array('\x27', '\x22');
		$nojsphrases = array('vendor/jquery_' . JQUERYVERSION, 'vendor/jquery_carousel', 'vendor/jquery_easing', 'vendor/jquery_slides', 'vendor/jquery_ui');


		$source[] = $this->templateregistry["$node"];
		// vendor javascripts that contain custom sheel phrases: phrase['_xxx']
		$source[] = file_get_contents(DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/vendor/fileuploader/jquery.fileupload-ui.js');
		$source[] = file_get_contents(DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/vendor/fileuploader/main.js');
		$source[] = file_get_contents(DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/vendor/tock/tock.js');
		if (is_array($this->meta['jsinclude'])) {
			if (isset($this->meta['jsinclude']['header'])) {
				foreach ($this->meta['jsinclude']['header'] as $key => $value) {
					if (!in_array($value, $nojsphrases)) {
						$path = ($value == 'functions') ? DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $value . '.js' : DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/functions_' . $value . '.js';
						if (file_exists($path . 'x')) {
							$source[] = file_get_contents($path . 'x');
						} else if (file_exists($path)) {
							$source[] = file_get_contents($path);
						}
					}
				}
			}
			if (isset($this->meta['jsinclude']['footer'])) {
				foreach ($this->meta['jsinclude']['footer'] as $key => $value) {
					if (!in_array($value, $nojsphrases)) {
						$path = ($value == 'functions') ? DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $value . '.js' : DIR_SERVER_ROOT . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/functions_' . $value . '.js';
						if (file_exists($path . 'x')) {
							$source[] = file_get_contents($path . 'x');
						} else if (file_exists($path)) {
							$source[] = file_get_contents($path);
						}
					}
				}
			}
		}
		foreach ($source as $key => $value) {
			if (preg_match_all("/phrase\['(.*)\']/U", $value, $phrasematches) == true) {
				$varnames = array_values(array_unique($phrasematches[1]));
				foreach ($varnames as $key2 => $value2) {
					$where .= (empty($where)) ? "'$value2'" : ", '$value2'";
				}
			}
		}
		unset($phrasematches, $source, $value);
		$hash = md5($where . $slng);
		$this->js_phrases_file = 'phrases-' . $hash . '.js';
		$js_phrases_filepath = DIR_TMP_JS . $this->js_phrases_file;
		$js_phrases_url = HTTP_TMP . DIR_JS_NAME . '/' . $this->js_phrases_file;
		$js_phrases_content = ((file_exists($js_phrases_filepath))
			? "<script type=\"text/javascript\" src=\"" . $js_phrases_url . "\" async integrity=\"" . $this->sheel->security->generate_sri_checksum(file_get_contents($js_phrases_filepath)) . "\" crossorigin=\"anonymous\" charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\" data-turbolinks-track=\"reload\"></script>"
			: "<script type=\"text/javascript\" src=\"" . $js_phrases_url . "\" async charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\" data-turbolinks-track=\"reload\"></script>");
		$filetime = (file_exists($js_phrases_filepath)) ? filemtime($js_phrases_filepath) : 0;
		if (!empty($where) and $filetime < (TIMESTAMPNOW - 300)) {
			$where = 'varname IN (' . $where . ')';
			$sql = $this->sheel->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "varname, text_$slng AS text
				FROM " . DB_PREFIX . "language_phrases
				WHERE $where
			");
			if ($this->sheel->db->num_rows($sql) > 0) {
				while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
					$jsphrase = str_replace($charsearch, $charreplace, $res['text']);
					$jsphrase = html_entity_decode($jsphrase);
					$jsphrase = str_replace(array("\r\n", "\n", "\r"), '', $jsphrase);
					$include_js_phrases .= "'" . trim($res['varname']) . "':'$jsphrase', ";
				}
				$include_js_phrases = substr($include_js_phrases, 0, -2);
				$include_js_phrases = 'var phrase = {' . $include_js_phrases . '};';
			}
			if (@file_put_contents($js_phrases_filepath, $include_js_phrases) === false) { // can't write to cache/javascript/
				$js_phrases_content = "<script type=\"text/javascript\" charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\">" . LINEBREAK . $include_js_phrases . LINEBREAK . "</script>" . LINEBREAK;
			} else {
				$js_phrases_content = "<script type=\"text/javascript\" src=\"" . $js_phrases_url . "\" async integrity=\"" . $this->sheel->security->generate_sri_checksum($include_js_phrases) . "\" crossorigin=\"anonymous\" charset=\"" . mb_strtolower($this->sheel->language->cache[$_SESSION['sheeldata']['user']['languageid']]['charset']) . "\" data-turbolinks-track=\"reload\"></script>";
			}
		}
		$this->templateregistry["$node"] = str_replace('{js_phrases_content}', $js_phrases_content, $this->templateregistry["$node"]);

		$this->sheel->timer->stop();
	}
	function minify_output($node = '')
	{
		if ($this->sheel->config['globalfilters_whitespacestripper']) {
			$this->templateregistry["$node"] = $this->sheel->minify->minify_html($this->templateregistry["$node"]);
		}
	}
	/*
	 * Function for printing the compiled templates to the web browser.
	 *
	 * @param       string
	 */
	function print_parsed_template($node = '', $echo = true)
	{

		$html = '';
		$this->parse_template_collapsables($node);
		$this->minify_output($node);

		$html .= $this->templateregistry["$node"];
		$this->sheel->common->init_pageview_tracker();
		$this->sheel->timer->stop();
		$this->runtime = $this->sheel->timer->get();
		if (!$echo) {
			return $html;
		}
		echo $html;
	}
	private function register_breadcrumbs($node)
	{
		$navcrumb = (isset($this->meta['navcrumb']) ? $this->construct_breadcrumb($this->meta['navcrumb']) : array());
		if (defined('LOCATION') and LOCATION != 'admin' and isset($navcrumb['breadcrumbfinal']) and isset($navcrumb['breadcrumbtrail'])) {
			$navcrumb['breadcrumbfinal'] = str_replace('$', '\$', $navcrumb['breadcrumbfinal']);
			$navcrumb['breadcrumbtrail'] = str_replace('$', '\$', $navcrumb['breadcrumbtrail']);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbtrail}/si", "$navcrumb[breadcrumbtrail]", $this->templateregistry["$node"]);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbfinal}/si", "$navcrumb[breadcrumbfinal]", $this->templateregistry["$node"]);
		}
		unset($navcrumb);
	}
	private function register_javascripts()
	{
		$this->sheel->styles->init_head_css();
		$this->sheel->styles->init_head_js();
		$this->sheel->styles->init_foot_js();
	}
	/*
	 * Parses and then immediately prints the file.  Function will be depreciated soon as the name of this function is outdated and will be replaced with ->draw()
	 *
	 * @param       string
	 */
	function pprint($node = '', $variablearray = array(), $echo = true)
	{
		
		$this->parse_if_blocks($node);
		$this->register_javascripts();
		$this->register_breadcrumbs($node);
		$this->register_template_variables($node, $variablearray); // builds $this->var_names_assoc
		$this->parse_template($node);

		$html = $this->print_parsed_template($node, $echo);
		if (!$echo) {
			// clear node container
			$this->templateregistry["$node"] = '';
			return $html;
		}
	}
	/*
	 * Function to handle drawing the final parsed template for browser output
	 *
	 * @param       string           list of variables to allow for parsing
	 */
	function draw($variablearray = array(), $echo = true)
	{
		$this->pprint($this->node, $variablearray, $echo);
	}
	/*
	 * Function for parsing $_SESSION['sheeldata'] tags throughout the templates
	 *
	 * @notes       $_SESSION['sheeldata']['user']['XXXX'] = {user[XXXX]}
	 * @usage       {user[username]} would be sheel
	 * @param       string
	 */
	function parse_session_globals($node = '')
	{
		if (!empty($_SESSION['sheeldata']) and is_array($_SESSION['sheeldata'])) {
			foreach ($_SESSION['sheeldata'] as $name => $value) {
				$pattern = '/' . $this->start . $name . '\[([\w\d_]+)\]' . $this->end . '/';
				if (preg_match_all($pattern, $this->templateregistry[$node], $matches) > 0) {
					$matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
					$replaceable = array();
					foreach ($matches as $key) {
						if (isset($key) and $key != '') {
							$replaceable[$this->start . $name . "[$key]" . $this->end] = (isset($value["$key"]) ? $value["$key"] : '');
						}
					}
					$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
					unset($replaceable, $matches);
				}
			}
		}
	}
	/*
	 * Function for parsing $sheel->config['xxx'] tags throughout the templates
	 *
	 * @notes       $sheel->config['XXXX'] = {config[XXXX]}
	 * @usage       {config[site_email]} would be sites email
	 * @param       string
	 */
	function parse_config_globals($node = '')
	{
		if (!empty($this->sheel->config) and is_array($this->sheel->config)) {
			foreach ($this->sheel->config as $nname => $value) {
				$name = 'config';
				$pattern = '/' . $this->start . $name . '\[([\w\d_]+)\]' . $this->end . '/';
				if (isset($this->templateregistry[$node]) and preg_match_all($pattern, $this->templateregistry[$node], $matches) > 0) {
					$matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
					$replaceable = array();
					foreach ($matches as $key) {
						if (isset($key) and $key != '') {
							$replaceable[$this->start . $name . "[$key]" . $this->end] = ((isset($this->sheel->config["$key"])) ? $this->sheel->config["$key"] : '');
						}
					}
					$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
					unset($replaceable, $matches);
				}
			}
		}
	}
	/*
	 * Function for HTML blockbit templates
	 *
	 * @usage       {htmlblock[1]} would be html for id = 1
	 * @param       string
	 */
	function parse_htmlblocks_globals($node = '')
	{
		$sql = $this->sheel->db->query("
			SELECT id, html
			FROM " . DB_PREFIX . "html_blocks
			WHERE visible = '1'
		");
		if ($this->sheel->db->num_rows($sql) > 0) {
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$name = 'htmlblock';
				$pattern = '/' . $this->start . $name . '\[([\w\d_]+)\]' . $this->end . '/';
				if (isset($this->templateregistry[$node]) and preg_match_all($pattern, $this->templateregistry[$node], $matches) > 0) {
					$matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
					$replaceable = array();
					foreach ($matches as $key) {
						if (isset($key) and $key != '') {
							$replaceable[$this->start . $name . "[$key]" . $this->end] = $res['html'];
						}
					}
					$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
					unset($replaceable, $matches);
				}
			}
		}
	}
	/*
	 * Function to construct the breadcrumb trail for the client cp template (just under the top nav)
	 *
	 * @param	string
	 */
	function construct_breadcrumb($navcrumb)
	{
		$elements = array('breadcrumbtrail' => '', 'breadcrumbfinal' => '');
		$current = sizeof($navcrumb);
		$count = 0;
		if (isset($navcrumb) and is_array($navcrumb)) {
			foreach ($navcrumb as $navurl => $navtitle) {
				$type = iif(++$count == $current, 'breadcrumbfinal', 'breadcrumbtrail');
				$dotrail = iif($type == 'breadcrumbtrail', true, false);
				if (empty($navtitle)) {
					continue;
				}
				if ($dotrail == 1) {
					eval('$elements["$type"] .= "' . $this->fetch_template('TEMPLATE_breadcrumb_trail.html', false) . '";');
				} else {
					eval('$elements["$type"] .= "' . $this->fetch_template('TEMPLATE_breadcrumb.html', false) . '";');
				}
			}
		}
		return $elements;
	}
	/**
	 * Function to fetch all $_POST and $_GET recursively
	 *
	 * @param	array	     array
	 */
	function array_recursive($array)
	{
		$html = '';
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = $this->array_recursive($value);
				$html .= "$key=$value&amp;";
			} else {
				$html .= "$key=$value&amp;";
			}
		}
		return $html;
	}
}
?>