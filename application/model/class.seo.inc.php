<?php
/**
 * Search Engine Friendly URL class to perform the majority of seo/sef functionality in sheel.
 *
 * @package      sheel\Seo
 * @version      6.0.0.622
 * @author       sheel
 */
class seo
{
	protected $sheel;

	function __construct($sheel)
	{
		$this->sheel = $sheel;
	}
	/**
	 * Function
	 *
	 * @param       integer
	 * @param       string
	 *
	 * @return      bool           Returns
	 */
	function remove_querystring_var($url, $key)
	{
		$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		$url = substr($url, 0, -1);
		return $url;
	}
	/**
	 * Function to print any hidden $sheel->GPC elements into a url string or hidden input fields.  All fields values will be wrapped in
	 * urlencode.
	 *
	 * @param       bool           use input fields (default true)
	 * @param       array          excluded array keys (ie: 'cmd','cid','project_id')
	 * @param       bool           print a ? question mark before any url text (default false)
	 * @param       string         prepend text to hidden input field names (example: old[)
	 * @param       string         append text to hidden input field names (example: ])
	 * @param       boolean        convert text using htmlentities() (default true)
	 * @param       boolean        return urldecoded() string? (default false & urlencoded())
	 * @param       boolean        show sid[x]=true in url bit? (default false)
	 *
	 * @return      integer        Returns HTML representation of the url string or hidden input fields.
	 */
	function print_hidden_fields($string = false, $excluded = array(), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = false, $showsid = false)
	{
		if ($showsid == false) {
			$excludedtmp = array('sid');
			$excluded = array_merge($excludedtmp, $excluded);
		}
		$unaccepted = array('select', 'search', 'submit', 'pp', 'token', 'sef', 'do', 'searchid', 'radiuszip', 'list', 'language', 'mode');
		$unaccepted = array_merge($unaccepted, $excluded);
		$unaccepted = array_unique($unaccepted);
		$html = '';
		foreach ($this->sheel->GPC as $key => $value) {
			if (!in_array($key, $unaccepted)) {
				if (is_array($value)) {
					if ($string) {
						foreach ($value as $key2 => $value2) {
							if (empty($html) and $questionmarkfirst) {
								if (isset($value2) and $value2 != '') {
									if (is_array($value2)) {
										foreach ($value2 as $key3 => $value3) {
											if (isset($value3) and $value3 != '') {
												if (is_array($value3)) {
													foreach ($value3 as $key4 => $value4) {
														if (!empty($value4)) {
															$html .= '?' . $key . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']=' . urlencode(o(str_replace(' ', '+', $value4)));
														}
													}
												} else {
													if (!empty($value3)) {
														$html .= '?' . $key . '[' . $key2 . '][' . $key3 . ']=' . urlencode(o(str_replace(' ', '+', $value3)));
													}
												}
											}
										}
									} else {
										if (!empty($value2)) {
											$html .= '?' . $key . '[' . $key2 . ']=' . urlencode(o(str_replace(' ', '+', $value2)));
										}
									}
								}
							} else {
								if (isset($value2) and $value2 != '') {
									if (is_array($value2)) {
										foreach ($value2 as $key3 => $value3) {
											if (isset($value3) and $value3 != '') {
												if (is_array($value3)) {
													foreach ($value3 as $key4 => $value4) {
														if (!empty($value4)) {
															$html .= '&' . $key . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']=' . urlencode(o(str_replace(' ', '+', $value4)));
														}
													}
												} else {
													if (!empty($value3)) {
														$html .= '&' . $key . '[' . $key2 . '][' . $key3 . ']=' . urlencode(o(str_replace(' ', '+', $value3)));
													}
												}
											}
										}
									} else {
										if (!empty($value2)) {
											$html .= '&' . $key . '[' . $key2 . ']=' . urlencode(o(str_replace(' ', '+', $value2)));
										}
									}
								}
							}
						}
					} else {
						foreach ($value as $key2 => $value2) {
							if (isset($value2) and $value2 != '') {
								if (is_array($value2)) {
									foreach ($value2 as $key3 => $value3) {
										if (isset($value3) and $value3 != '') {
											if (is_array($value3)) {
												foreach ($value3 as $key4 => $value4) {
													if (!empty($value4)) {
														$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']" id="' . $key . '_' . $key2 . '_' . $key3 . '_' . $key4 . '" value="' . (($htmlentities) ? sheel_htmlentities($value4) : urlencode(o(str_replace(' ', '+', $value4)))) . '" />' . "\n";
													}
												}
											} else {
												if (!empty($value3)) {
													$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . '][' . $key3 . ']" id="' . $key . '_' . $key2 . '_' . $key3 . '" value="' . (($htmlentities) ? sheel_htmlentities($value3) : urlencode(o(str_replace(' ', '+', $value3)))) . '" />' . "\n";
												}
											}
										}
									}
								} else {
									if (!empty($value2)) {
										$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . ']" id="' . $key . '_' . $key2 . '" value="' . (($htmlentities) ? sheel_htmlentities($value2) : urlencode(o(str_replace(' ', '+', $value2)))) . '" />' . "\n";
									}
								}
							}
						}
					}
				} else {
					if ($string) {
						if (empty($html) and $questionmarkfirst) {
							if (isset($value) and $value != '') {
								$html .= '?' . $key . '=' . urlencode(o(str_replace(' ', '+', $value)));
							}
						} else {
							if (isset($value) and $value != '') {
								$html .= '&' . $key . '=' . urlencode(o(str_replace(' ', '+', $value)));
							}
						}
					} else {
						if (isset($value) and $value != '') {
							$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '" id="hidden_' . $key . '" value="' . o($value, false) . '" />' . "\n";
						}
					}
				}
			}
		}
		if ($urldecode) {
			$html = urldecode($html);
		}
		return $html;
	}
	/**
	 * Function to rewrite a url by providing a text to remove out of the url
	 *
	 * @param       string         search string text
	 * @param       string         replace string text
	 * @param       array          array holding multiple vars to be removed
	 *
	 * @return      string         Returns the replaced text
	 */
	function rewrite_url($string = '', $removetext = '', $removearray = array())
	{
		$unaccept = array('select', 'search', 'submit', 'pp', 'token', 'sef', 'do', 'cmd', 'ld', 'rl', 'language', 'mode');
		$unaccepted = array_merge($unaccept, $removearray);
		$unaccepted = array_unique($unaccepted);
		$removetext = str_replace(' ', '+', $removetext);
		if (!empty($removetext)) {
			$find1 = "?$removetext&";
			$repl1 = "?";
			$string = str_replace($find1, $repl1, $string);

			$find2 = "?$removetext";
			$repl2 = "";
			$string = str_replace($find2, $repl2, $string);

			$find3 = "&$removetext";
			$repl3 = "";
			$string = str_replace($find3, $repl3, $string);

			$find4 = "$removetext";
			$repl4 = "";
			$string = str_replace($find4, $repl4, $string);
		}
		foreach ($unaccepted as $removetext) {
			if (isset($this->sheel->GPC["$removetext"]) and $this->sheel->GPC["$removetext"] != '') {
				$find1 = "?$removetext=" . $this->sheel->GPC["$removetext"] . "&";
				$repl1 = "?";
				$string = str_replace($find1, $repl1, $string);

				$find2 = "?$removetext=" . $this->sheel->GPC["$removetext"] . "";
				$repl2 = "";
				$string = str_replace($find2, $repl2, $string);

				$find3 = "&$removetext=" . $this->sheel->GPC["$removetext"] . "";
				$repl3 = "";
				$string = str_replace($find3, $repl3, $string);

				$find4 = "$removetext=" . $this->sheel->GPC["$removetext"] . "";
				$repl4 = "";
				$string = str_replace($find4, $repl4, $string);
			} else {
				$parsed = parse_url($string);
				if (isset($parsed['query']) and isset($parsed['scheme']) and isset($parsed['host']) and isset($parsed['path'])) {
					$query = $parsed['query'];
					parse_str($query, $params);
					unset($params["$removetext"]);
					$string = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] . '?' . http_build_query($params);
				}
			}
		}
		$string = urldecode($string);
		$string = str_replace(' ', '+', $string);
		return $string;
	}
	/**
	 * Function to generate a valid search engine friendly url name (replaces spaces with underscores, etc)
	 *
	 * @param       string         text
	 * @param       boolean        force preventing text from being converted to lower case? (default false)
	 * @param       boolean        force accents replacements (default false, example: é becomes e)
	 *
	 * @return      integer        Returns the url text formatted for any web browser url bar
	 */
	function construct_seo_url_name($text = '', $forcenolowercase = false, $forceaccentconvert = false)
	{
		$text = & $text;
		$replacements = $this->sheel->language->fetch_seo_replacements($_SESSION['sheeldata']['user']['languageid']);
		if (!empty($replacements)) {
			$replacement = explode(', ', $replacements);
			foreach ($replacement as $set) {
				if (!empty($set)) {
					$value = explode('|', $set);
					if (!empty($value[0]) and !empty($value[1])) {
						$text = str_replace($value[0], $value[1], $text);
					}
				}
			}
		}
		if ($forceaccentconvert) {
			$text = $this->convert_accents($text);
		}
		if ($this->sheel->config['seourls_utf8']) {
			$text = str_replace(' - ', '-', $text);
			$text = str_replace('quot', '', $text);
			$text = str_replace(' ', '-', $text);
			$text = str_replace('?', '', $text);
			$text = str_replace('%', '', $text);
			$text = str_replace('/', '-', $text);
			$text = str_replace('\\', '', $text);
			$text = str_replace('|', '-', $text);
			$text = str_replace('~', '-', $text);
			$text = str_replace(']', '', $text);
			$text = str_replace('[', '', $text);
			$text = str_replace('}', '', $text);
			$text = str_replace('{', '', $text);
			$text = str_replace('*', '', $text);
			$text = str_replace('$', '', $text);
			$text = str_replace('!', '', $text);
			$text = str_replace('~', '', $text);
			$text = str_replace('#', '', $text);
			$text = str_replace('@', '', $text);
			$text = str_replace('^', '', $text);
			$text = str_replace('(', '', $text);
			$text = str_replace(')', '', $text);
			$text = str_replace('+', '', $text);
			$text = str_replace('"', '', $text);
			$text = str_replace("'", '', $text);
			$text = str_replace("’", '', $text);
			$text = str_replace(',', '', $text);
			$text = str_replace('.', '', $text);
			$text = str_replace('=', '', $text);
			$text = str_replace('>', '', $text);
			$text = str_replace('<', '', $text);
			$text = str_replace('&amp;', '', $text);
			$text = str_replace('&', '', $text);
			$text = str_replace(';', '', $text);
			$text = str_replace(':', '', $text);
			$text = str_replace('--', '-', $text);
		} else {
			$text = str_replace('quot', '', $text);
			$text = str_replace('&amp;', '', $text);
			$text = str_replace('&', '', $text);
			$text = str_replace("’", '', $text);
			$text = $this->utf8_seo_url($text);
			if (empty($text)) {
				$text = '--';
			}
		}
		$last = substr($text, -1);
		if ($last == '-') {
			$text = substr($text, 0, -1);
		}
		if (empty($text)) {
			$this->sheel->show['emptyurltext'] = true;
		} else {
			$this->sheel->show['emptyurltext'] = false;
		}
		if ($this->sheel->config['seourls_lowercase'] and $forcenolowercase == false) {
			$text = mb_strtolower($text);
		}
		return $text;
	}
	function utf8_seo_url($string, $separator = '-', $extra = null)
	{
		return trim(preg_replace('~[^0-9a-zA-Z.' . preg_quote($extra, '~') . ']+~i', $separator, html_entity_decode(preg_replace('~&([a-zA-Z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $separator);
	}
	/**
	 * Function to generate a valid search engine friendly url
	 *
	 * @param       array
	 *
	 * @return      integer        Returns search engine friendly url
	 */
	function url($args = array('cleanaccents' => false, 'type' => '', 'catid' => 0, 'seourl' => '', 'auctionid' => 0, 'name' => '', 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''))
	{
		$url = '';
		$cleanaccents = ((isset($args['cleanaccents']) and $args['cleanaccents']) ? true : false);
		if (isset($args['type'])) {
			if (!empty($args['customlink'])) {
				if (isset($args['bold']) and $args['bold'] > 0) {
					$urlname = $args['name'];
					$urlname = preg_replace("/[\n\r]/", "", $urlname);
					if ($args['cutoffname'] != '') {
						$lnkname = '<strong>' . $args['cutoffname'] . '</strong>';
					} else {
						$lnkname = '<strong>' . $args['customlink'] . '</strong>';
					}
				} else {
					$urlname = $args['name'];
					$urlname = preg_replace("/[\n\r]/", "", $urlname);
					if ($args['cutoffname'] != '') {
						$lnkname = $args['cutoffname'];
					} else {
						$lnkname = $args['customlink'];
					}
				}
			} else {
				if (isset($args['bold']) and $args['bold'] > 0) {
					$urlname = $args['name'];
					$urlname = preg_replace("/[\n\r]/", "", $urlname);
					if ($args['cutoffname'] != '') {
						$lnkname = '<strong>' . $args['cutoffname'] . '</strong>';
					} else {
						$lnkname = '<strong>' . $args['name'] . '</strong>';
					}
				} else {
					$urlname = $args['name'];
					$urlname = preg_replace("/[\n\r]/", "", $urlname);
					if ($args['cutoffname'] != '') {
						$lnkname = $args['cutoffname'];
					} else {
						$lnkname = $args['name'];
					}
				}
			}
			$args['name'] = o($args['name']);
			$keywords = ((!empty($this->sheel->GPC['q'])) ? o($this->sheel->GPC['q']) : '');
			$excludeurlbit = array('do', 'page', 'mode', 'cid', 'cmd', 'state', 'id', 'q', 'subject', 'message', 'note');
			if (!empty($args['removevar'])) {
				if (is_array($args['removevar'])) {
					foreach ($args['removevar'] as $key) {
						if ($key != '') {
							$excludeurlbit[] = $key;
						}
					}
				} else {
					$excludeurlbit[] = $args['removevar'];
				}
			}
			switch ($args['type']) {
				case 'productcat': {
						if (!empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						} else if ((empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) or (empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid']))) {
							$schema = '{HTTP_SERVER}{CIDSEO}/{URLBIT}';
						} else if (!empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						}
						$schema = '<a href="' . $schema . '" ' . $args['extrahref'] . ' title="' . $args['name'] . '">{LINKNAME}</a>';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', $keywords, $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') . $this->print_hidden_fields(true, $excludeurlbit, true, '', '', true, true);
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{LINKNAME}', $lnkname, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productcatplain': {
						if (!empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						} else if ((empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) or (empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid']))) {
							$schema = '{HTTP_SERVER}{CIDSEO}/{URLBIT}';
						} else if (!empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						}
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', $keywords, $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') . $this->print_hidden_fields(true, $excludeurlbit, true, '', '', true, true);
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productcatmap': {
						if (!empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						} else if ((empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) or (empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid']))) {
							$schema = '{HTTP_SERVER}{CIDSEO}/{URLBIT}';
						} else if (!empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						}
						$schema = '<a href="' . $schema . '" ' . $args['extrahref'] . ' title="' . $args['name'] . '">{LINKNAME}</a>';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', $keywords, $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{LINKNAME}', $lnkname, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productcatmapplain': {
						if (!empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}/{CIDSEO}.html{URLBIT}';
						} else if ((empty($this->sheel->GPC['q']) and isset($this->sheel->GPC['cid'])) or (empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid']))) {
							$schema = '{HTTP_SERVER}{CIDSEO}/{URLBIT}';
						} else if (!empty($this->sheel->GPC['q']) and !isset($this->sheel->GPC['cid'])) {
							$schema = '{HTTP_SERVER}search/{KEYWORDS}.html{URLBIT}';
						}
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', $keywords, $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{CATEGORYLOWERCASE}', $this->construct_seo_url_name(mb_strtolower($urlname), false, $cleanaccents), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productsearchquestion': {
						if (strrchr($args['seourl'], '?')) {
							$tmp = explode('?', $args['seourl']);
							$args['seourl'] = $tmp[0];
						}
						$schema = '{CIDSEO}{URLBIT}'; // {HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}
						$schema = '<a href="' . $schema . '" ' . $args['extrahref'] . '>{LINKNAME}</a>';
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (((isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit'])) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : $this->print_hidden_fields(true, $excludeurlbit, true, '', '', true, true));
						$qidbit = ((isset($this->sheel->GPC['qid']) and !empty($this->sheel->GPC['qid'])) ? $this->sheel->GPC['qid'] . ',' . $args['questionid'] . '.' . $args['answerid'] : $args['questionid'] . '.' . $args['answerid']);
						if (empty($urlbit)) {
							$urlbit = '?qid=' . $qidbit;
						} else {
							$urlbit .= '&amp;qid=' . $qidbit;
						}
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{LINKNAME}', $args['searchquestion'], $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productsearchquestionplain': {
						if (strrchr($args['seourl'], '?')) {
							$tmp = explode('?', $args['seourl']);
							$args['seourl'] = $tmp[0];
						}
						$schema = '{CIDSEO}{URLBIT}';
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$urlbit = (((isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit'])) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : $this->print_hidden_fields(true, $excludeurlbit, true, '', '', true, true));
						$qidbit = ((isset($this->sheel->GPC['qid']) and !empty($this->sheel->GPC['qid'])) ? $this->sheel->GPC['qid'] . ',' . $args['questionid'] . '.' . $args['answerid'] : $args['questionid'] . '.' . $args['answerid']);
						if (empty($urlbit)) {
							$urlbit = '?qid=' . $qidbit;
						} else {
							$urlbit .= '&amp;qid=' . $qidbit;
						}
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{LINKNAME}', $args['searchquestion'], $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productauction': {
						$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{CATEGORY}{URLBIT}{KEYWORDS}" ' . $args['extrahref'] . ' title="' . o($args['name']) . '">{LINKNAME}</a>';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('item'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['auctionid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{KEYWORDS}', ((empty($urlbit) and isset($this->sheel->GPC['q']) and !empty($this->sheel->GPC['q']) and isset($this->sheel->show['nourlbit']) and !$this->sheel->show['nourlbit']) ? '?q=' . o(str_replace('%2B', '+', trim(str_replace('/', '', urlencode($this->sheel->GPC['q']))))) : ''), $schema);
						$schema = str_replace('{LINKNAME}', $lnkname, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'productauctionplain': {
						$schema = '{HTTP_SERVER}{IDENTIFIER}/{ID}/{CATEGORY}{URLBIT}{KEYWORDS}';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('item'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['auctionid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{KEYWORDS}', ((empty($urlbit) and isset($this->sheel->GPC['q']) and !empty($this->sheel->GPC['q']) and isset($this->sheel->show['nourlbit']) and !$this->sheel->show['nourlbit']) ? '?q=' . o(str_replace('%2B', '+', trim(str_replace('/', '', urlencode($this->sheel->GPC['q']))))) : ''), $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'auctionevent': {
						$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{URLBIT}" ' . $args['extrahref'] . ' title="' . $args['name'] . '">{LINKNAME}</a>';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', '', $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('auctions/lots'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['auctionid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{LINKNAME}', $lnkname, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'auctioneventplain': {
						$schema = '{HTTP_SERVER}{IDENTIFIER}/{ID}/{URLBIT}';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{KEYWORDS}', '', $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('auctions/lots'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['auctionid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$url = $schema;
						unset($schema);
						break;
					}

				case 'salvage': {
						$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{CATEGORY}{URLBIT}{KEYWORDS}" ' . $args['extrahref'] . ' title="' . o($args['name']) . '">{LINKNAME}</a>';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('salvage'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['requestid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{KEYWORDS}', ((empty($urlbit) and isset($this->sheel->GPC['q']) and !empty($this->sheel->GPC['q']) and isset($this->sheel->show['nourlbit']) and !$this->sheel->show['nourlbit']) ? '?q=' . o(str_replace('%2B', '+', trim(str_replace('/', '', urlencode($this->sheel->GPC['q']))))) : ''), $schema);
						$schema = str_replace('{LINKNAME}', $lnkname, $schema);
						$url = $schema;
						unset($schema);
						break;
					}
				case 'salvageplain': {
						$schema = '{HTTP_SERVER}{IDENTIFIER}/{ID}/{CATEGORY}{URLBIT}{KEYWORDS}';
						$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
						$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
						$schema = str_replace('{CATEGORY}', $this->construct_seo_url_name($urlname, false, $cleanaccents), $schema);
						$schema = str_replace('{IDENTIFIER}', $this->print_seo_url('salvage'), $schema);
						$schema = str_replace('{CID}', $args['catid'], $schema);
						$schema = str_replace('{CIDSEO}', $args['seourl'], $schema);
						$schema = str_replace('{ID}', $args['auctionid'], $schema);
						$urlbit = (isset($this->sheel->show['nourlbit']) and $this->sheel->show['nourlbit']) ? ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '') : ((isset($args['seourlextra']) and !empty($args['seourlextra'])) ? $args['seourlextra'] : '');
						$schema = str_replace('{URLBIT}', $urlbit, $schema);
						$schema = str_replace('{KEYWORDS}', ((empty($urlbit) and isset($this->sheel->GPC['q']) and !empty($this->sheel->GPC['q']) and isset($this->sheel->show['nourlbit']) and !$this->sheel->show['nourlbit']) ? '?q=' . o(str_replace('%2B', '+', trim(str_replace('/', '', urlencode($this->sheel->GPC['q']))))) : ''), $schema);
						$url = $schema;
						unset($schema);
						break;
					}
			}
		}
		return str_replace(LINEBREAK, '', $url);
	}
	/**
	 * Function to parse a valid SEO (search engine optimized) url
	 *
	 * @param       string         text
	 *
	 * @return      integer        Returns the url
	 */
	function print_seo_url($string = '', $cleanaccents = false)
	{
		if ($this->sheel->config['seourls_lowercase']) {
			$string = mb_strtolower($string);
		}
		if ($cleanaccents) {
			$string = $this->convert_accents($string);
		}
		return $string;
	}

	private function convert_accents($string = '')
	{
		$unwanted_array = array(
			'Š' => 'S',
			'š' => 's',
			'Ž' => 'Z',
			'ž' => 'z',
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'Æ' => 'A',
			'Ç' => 'C',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ñ' => 'N',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ø' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Þ' => 'B',
			'ß' => 'Ss',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'æ' => 'a',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ð' => 'o',
			'ñ' => 'n',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ø' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ý' => 'y',
			'ý' => 'y',
			'þ' => 'b',
			'ÿ' => 'y'
		);
		return mb_strtolower(strtr($string, $unwanted_array));
	}
}
?>