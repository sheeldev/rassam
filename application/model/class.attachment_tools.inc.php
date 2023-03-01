<?php
/**
 * Attachment Tools class to perform the majority of uploading and attachment handling operations within sheel.
 *
 * @package      sheel\Attachment\Tools
 * @version      1.0.0.0
 * @author       sheel
 */
class attachment_tools extends attachment
{
	/*
	 * Function to fetch the relative path name for a particular attachment type in the filesystem
	 *
	 * @param	string		attachment type (profile, itemphoto, bid, pmb, ws)
	 *
	 * @return	string          attachment folder (example: bids, auctions, ws, pmbs)
	 */
	function fetch_physical_folder_name($attachtype = '')
	{
		if ($attachtype == 'profile') {
			$filedata = 'profiles';
		} else if ($attachtype == 'itemphoto' or $attachtype == 'digital' or $attachtype == 'eventphoto') {
			$filedata = 'auctions';
		} else if ($attachtype == 'bid') {
			$filedata = 'bids';
		} else if ($attachtype == 'pmb') {
			$filedata = 'pmbs';
		} else if ($attachtype == 'stores' or $attachtype == 'storesbackground') {
			$filedata = 'stores';
		} else {
		}
		if (!empty($filedata)) {
			return $filedata;
		}
		return false;
	}
	/*
	 * Function to fetch the full attachment path for the specified attachment type
	 *
	 * @param	string		attachment type (profile, itemphoto, bid, pmb, stores)
	 *
	 * @return	string          attachment folder
	 */
	function fetch_attachment_path($attachtype = '')
	{
		if ($attachtype == 'profile') {
			$filedata = DIR_PROFILE_ATTACHMENTS;
		} else if ($attachtype == 'itemphoto' or $attachtype == 'digital' or $attachtype == 'eventphoto') {
			$filedata = DIR_AUCTION_ATTACHMENTS;
		} else if ($attachtype == 'bid') {
			$filedata = DIR_BID_ATTACHMENTS;
		} else if ($attachtype == 'pmb') {
			$filedata = DIR_PMB_ATTACHMENTS;
		} else if ($attachtype == 'stores' or $attachtype == 'storesbackground') {
			$filedata = DIR_STORE_ATTACHMENTS;
		} else {
		}
		if (!empty($filedata)) {
			return $filedata;
		}
		return false;
	}
	/*
	 * Function fetch the full attachment path and actual file(name) for the specified attachment type
	 *
	 * @param	string		attachment type (profile, portfolio, project, itemphoto, bid, pmb, ws, stores, storesbackground)
	 * @param        boolean         attachment file hash
	 * @param        string          attachment file name
	 *
	 * @return	string          full path to the attachment
	 */
	function fetch_attachment_file($attachtype = '', $filehash = '', $filename = '')
	{
		$filedata = '';
		if ($attachtype == 'profile') {
			$filedata = DIR_PROFILE_ATTACHMENTS . $filehash . '/' . $filename;
		} else if ($attachtype == 'itemphoto' or $attachtype == 'digital' or $attachtype == 'eventphoto') {
			$filedata = DIR_AUCTION_ATTACHMENTS . $filehash . '/' . $filename;
		} else if ($attachtype == 'bid') {
			$filedata = DIR_BID_ATTACHMENTS . $filehash . '/' . $filename;
		} else if ($attachtype == 'pmb') {
			$filedata = DIR_PMB_ATTACHMENTS . $filehash . '/' . $filename;
		} else if ($attachtype == 'stores' or $attachtype == 'storesbackground') {
			$filedata = DIR_STORE_ATTACHMENTS . $filehash . '/' . $filename;
		} else {
		}
		return $filedata;
	}
	/*
	 * Function returns back required raw attachment data for a specific attachment
	 *
	 * @param	array 	        attachment array from the DB table
	 *
	 * @return	string          Returns raw attachment file data from the filesystem
	 */
	function fetch_attachment_rawdata($attachment)
	{
		$attachment['filedata'] = false;
		if (isset($attachment['isexternal']) and $attachment['isexternal'] <= 0) {
			$rawdata = $this->fetch_attachment_file($attachment['attachtype'], $attachment['filehash'], $attachment['filename']);
			if (file_exists($rawdata)) {
				$attachment['filedata'] = file_get_contents($rawdata);
			}
			unset($rawdata);
		}
		return $attachment['filedata'];
	}
	/*
	 * Function to print out a security code captcha stored in $_SESSION also using imagecreate() and imagettftext().
	 * Common letters have been eliminated from view such as "i", "l", "o" and "0"
	 *
	 * @param	integer 	length of captcha phrase (default 5 characters)
	 *
	 * @return	string          Returns image data
	 */
	function print_captcha($length = 5)
	{
		$src = 'abcdefghjkmnpqrstuvwxyz23456789';
		if (mt_rand(0, 1) == 0) {
			$src = mb_strtoupper($src);
		}
		$srclen = mb_strlen($src) - 1;
		$font = DIR_FONTS . 'AppleGaramond.ttf';
		$output_type = 'png';
		$min_font_size = 15;
		$max_font_size = 55;
		$min_angle = -25;
		$max_angle = 25;
		$char_padding = 1;
		$data = array();
		$image_width = $image_height = 0;
		$_SESSION['sheeldata']['user']['captcha'] = '';
		for ($i = 0; $i < $length; $i++) {
			$char = mb_strtoupper(mb_substr($src, mt_rand(0, $srclen), 1));
			$_SESSION['sheeldata']['user']['captcha'] .= "$char";
			$size = mt_rand($min_font_size, $max_font_size);
			$angle = mt_rand($min_angle, $max_angle);
			$bbox = imagettfbbox($size, $angle, $font, $char);
			$char_width = max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]);
			$char_height = max($bbox[1], $bbox[3]) - min($bbox[7], $bbox[5]);
			$image_width += $char_width + $char_padding;
			$image_height = max($image_height, $char_height);
			$data[] = array('char' => $char, 'size' => $size, 'angle' => $angle, 'height' => $char_height, 'width' => $char_width);
		}

		$x_padding = 6;
		$image_width += ($x_padding * 2);
		$image_height = ($image_height * 1.5) + 2;
		$im = imagecreatetruecolor($image_width, $image_height);
		// Transparent Background
		imagealphablending($im, false);
		$transparency = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefill($im, 0, 0, $transparency);
		imagesavealpha($im, true);
		$color_text = imagecolorallocate($im, 50, 50, 50);
		$color_border = imagecolorallocate($im, 255, 255, 255);
		$pos_x = $x_padding + ($char_padding / 2);
		foreach ($data as $d) {
			$pos_y = (($image_height + $d['height']) / 2);
			imagettftext($im, $d['size'], $d['angle'], $pos_x, $pos_y, $color_text, $font, $d['char']);
			$pos_x += $d['width'] + $char_padding;
		}
		//imagerectangle($im, 0, 0, $image_width - 1, $image_height - 1, $color_border);
		switch ($output_type) {
			case 'jpg':
			case 'jpeg': {
					header('Content-type: image/jpeg');
					imagejpeg($im, null, 100);
					break;
				}
			case 'png': {
					header('Content-type: image/png');
					imagepng($im);
					break;
				}
			case 'gif': {
					header('Content-type: image/gif');
					imagegif($im);
					break;
				}
		}
		imagedestroy($im);
	}
	/*
	 * Function to fetch the attachment type
	 *
	 * @param       string         attach type
	 * @param       integer        project id
	 * @param       integer        custom id for custom apps
	 *
	 * @return      string         Returns the attachment type
	 */
	function fetch_attachment_type($type = '', $projectid = '', $otherid = '')
	{
		if (!empty($type)) {
			$html = '';
			if ($type == 'profile') {
				$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "a.user_id, u.username
					FROM " . DB_PREFIX . "attachment a
					LEFT JOIN " . DB_PREFIX . "users u ON (a.user_id = u.user_id)
					WHERE a.attachid = '" . intval($otherid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				//todo: convert member url into seo url if enabled...
				$html = '{_profile_logo}';
			} else if ($type == 'itemphoto') {
				$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '{_item_photo} (#' . $projectid . ')';
			} else if ($type == 'eventphoto') {
				$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title
					FROM " . DB_PREFIX . "events
					WHERE eventid = '" . intval($projectid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '{_auction_event} (#' . $projectid . ')';
			} else if ($type == 'bid') {
				$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '{_bid_attachment}';
			} else if ($type == 'pmb') {
				$html = '{_pmb_attachment}';
			} else if ($type == 'digital') {
				$sql = $this->sheel->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '{_digital_download}';
			} else if ($type == 'stores') {
				$html = '{_store_logo}';
			} else if ($type == 'storesbackground') {
				$html = '{_store_billboard}';
			} else {
			}
			return $html;
		}
	}
	/*
	 * Function to fetch the attachment dimensions
	 *
	 * @param       array          attachment array
	 *
	 * @return      string         Returns the attachment type
	 */
	function fetch_attachment_dimensions($res = array())
	{
		$html = '';
		$tmp = $this->sheel->picture_factory($res['user_id']);
		$dimensions = $tmp[$res['attachtype']]['dimensions'];
		if (count($dimensions) > 0) {
			foreach ($dimensions as $dimension) {
				$html .= $dimension . ', ';
			}
			if (!empty($html)) {
				$html = substr($html, 0, -2);
			}
		}
		return $html;
	}
	function get_image($path)
	{
		switch (mime_content_type($path)) {
			case 'image/png': {
					$img = imagecreatefrompng($path);
					break;
				}
			case 'image/gif': {
					$img = imagecreatefromgif($path);
					break;
				}
			case 'image/jpeg': {
					$img = imagecreatefromjpeg($path);
					break;
				}
			case 'image/bmp': {
					$img = imagecreatefrombmp($path);
					break;
				}
			default: {
					$img = null;
				}

		}
		return $img;
	}
	/*
	 * Function to download and save a UPC picture.  If the picture is too small based on min width requirement
	 * the picture is redrawn on a 1024x768 canvas with the original centered.
	 *
	 * @param        string      image url (example: http://server.com/image.gif)
	 * @param        string      method (default curl)
	 * @param        integer     upc number
	 *
	 * @return	boolean     returns true or false
	 */
	function save_upc_image($img = '', $method = 'curl', $upc = 0, $imagecount = 0)
	{
		if ($img != '' and $upc != '') {
			$agents = [
				'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4 like Mac OS X) AppleWebKit/604.1.34 (KHTML, like Gecko) CriOS/67.0.3396.69 Mobile/15F79 Safari/604.1',
				'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1',
				'Mozilla/5.0 (Linux; Android 7.0; Pixel C Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/52.0.2743.98 Safari/537.36',
				'Mozilla/5.0 (Linux; Android 6.0.1; SGP771 Build/32.2.A.0.253; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/52.0.2743.98 Safari/537.36',
				'Mozilla/5.0 (Linux; Android 6.0.1; SHIELD Tablet K1 Build/MRA58K; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Safari/537.36',
				'Mozilla/5.0 (Linux; Android 7.0; SM-T827R4 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.116 Safari/537.36',
				'Mozilla/5.0 (Linux; Android 5.0.2; SAMSUNG SM-T550 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.3 Chrome/38.0.2125.102 Safari/537.36',
				'Mozilla/5.0 (Linux; Android 5.0.2; LG-V410/V41020c Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/34.0.1847.118 Safari/537.36',
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
				'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
				'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1',
				'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/602.2.14 (KHTML, like Gecko) Version/10.0.1 Safari/602.2.14'
			];
			$agent = $agents[mt_rand(0, count($agents) - 1)];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_URL, $img);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if ($rawdata == '') {
				return false;
			}
			$upcfolder = 'upc-' . substr($upc, 0, 3);
			$fullpath = DIR_UPC . $upcfolder;
			if ($imagecount <= 1) { // first upc image
				$filename = $upc . '.jpg';
			} else { // additional upc images
				$filename = ($imagecount - 1) . '.jpg';
			}
			$newfilename = $fullpath . '/' . $filename;
			$foldername = $fullpath . '/';
			if (!is_dir($foldername)) {
				$oldumask = umask(0);
				mkdir($foldername, 0755);
				umask($oldumask);
			}
			if (file_exists($newfilename)) { // overwrite the image capability
				@unlink($newfilename);
			}
			if ($fp = fopen($newfilename, 'x')) { // save image
				fwrite($fp, $rawdata);
				fclose($fp);

				// check if image is valid
				$fileinfo = getimagesize($newfilename);
				if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) { // valid
					// check if image meet width and height restriction
					if ($this->sheel->config['minwidth'] > 0) { // width
						if ($fileinfo[0] < $this->sheel->config['minwidth']) { // redraw as 1024x768
							$oldimage = $this->get_image($newfilename);
							$oldw = imagesx($oldimage);
							$oldh = imagesy($oldimage);
							$newimage = imagecreatetruecolor(800, 600); // Creates a black image
							$white = imagecolorallocate($newimage, 255, 255, 255);
							imagefill($newimage, 0, 0, $white);
							imagecopy($newimage, $oldimage, (800 - $oldw) / 2, (600 - $oldh) / 2, 0, 0, $oldw, $oldh);
							imagejpeg($newimage, $newfilename, 100);
							imagedestroy($newimage);
							// reset fileinfo array
							$fileinfo = getimagesize($newfilename);
							if (isset($fileinfo[0]) and isset($fileinfo[1]) and $fileinfo[0] > 0 and $fileinfo[1] > 0) {
								return true;
							}
						}
					}
					return true;
				} else { // invalid!
					@unlink($newfilename);
				}
			}
		}
		return false;
	}
	/*
	 * Function to download and save an attachment from a remote url
	 *
	 * @param        string      image url (example: http://server.com/image.gif)
	 * @param        integer     user id
	 * @param        string      attachment type (itemphoto default)
	 * @param        string      remote connection method (curl, fgc)
	 * @param        integer     bulk id
	 * @param        boolean     restrict image width/height based on admin width/height limitation (default true)
	 *
	 * @return	boolean     returns true or false
	 */
	function save_url_image($img = '', $userid = 0, $attachtype = 'itemphoto', $method = 'curl', $bulkid = 0, $restrictwh = true)
	{
		if (!empty($img) and $img != '') {
			if ($method == 'curl' and !extension_loaded('curl')) {
				$method = 'fgc';
			}
			if ($method == 'fgc') {
				$context = array(
					'ssl' => array(
						'verify_peer' => false,
						'verify_peer_name' => false
					)
				);
				$rawdata = file_get_contents($img, false, stream_context_create($context));
				if (empty($rawdata) or $rawdata == '') {
					if ($bulkid > 0) {
						$this->sheel->db->query("
							UPDATE " . DB_PREFIX . "bulk_sessions
							SET errors = CONCAT(errors, 'Invalid file data [fgc]: " . $this->sheel->db->escape_string($img) . ", ')
							WHERE id = '" . intval($bulkid) . "'
						", 0, null, __FILE__, __LINE__);
					}
					return false;
				}
			} else if ($method == 'curl') {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $img);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$rawdata = curl_exec($ch);
				curl_close($ch);
				if (empty($rawdata) or $rawdata == '') {
					if ($bulkid > 0) {
						$this->sheel->db->query("
							UPDATE " . DB_PREFIX . "bulk_sessions
							SET errors = CONCAT(errors, 'Invalid file data [curl]: " . $this->sheel->db->escape_string($img) . ", ')
							WHERE id = '" . intval($bulkid) . "'
						", 0, null, __FILE__, __LINE__);
					}
					return false;
				}
			}
			$fullpath = DIR_AUCTION_ATTACHMENTS;
			$filehash = md5(uniqid(microtime()));
			$picturehash = '';
			$filetype = '';
			$filename = $this->fetch_url_image_filename($img, $filehash . '.attach');
			$extension = mb_strtolower(mb_strrchr($filename, '.'));
			$newfilename = $fullpath . $filehash . '/' . $filename;
			$foldername = $fullpath . $filehash . '/';
			if (is_dir($foldername)) { // remove folder hash if exists (so we can re-create new content)
				$this->recursive_remove_directory($foldername);
				$oldumask = umask(0);
				mkdir($foldername, 0755);
				umask($oldumask);
			} else {
				$oldumask = umask(0);
				mkdir($foldername, 0755);
				umask($oldumask);
			}
			if ($fp = fopen($newfilename, 'x')) { // save original
				fwrite($fp, $rawdata);
				fclose($fp);
				$picturehash = md5_file($newfilename);
				$filesize = filesize($newfilename);
				$tmp = $this->sheel->picture_factory($userid);
				$dimensions = $tmp[$attachtype]['dimensions'];
				$valid_minwidth = $valid_minheight = true;
				if (count($dimensions) > 0) {
					if ($fileinfo = getimagesize($newfilename)) { // valid picture file
						if ($attachtype == 'itemphoto') {
							if ($this->sheel->config['minwidth'] > 0 and $restrictwh) { // min width requirement
								if ($fileinfo[0] < $this->sheel->config['minwidth']) {
									$valid_minwidth = false;
								}
							}
							if ($this->sheel->config['minheight'] > 0 and $restrictwh) { // min height requirement
								if ($fileinfo[1] < $this->sheel->config['minheight']) {
									$valid_minheight = false;
								}
							}
						}
						if ($valid_minwidth and $valid_minheight) {
							$degree = 0;
							if (function_exists('exif_read_data')) {
								$exifdata = @exif_read_data($newfilename, 0, true);
								if (!empty($exifdata)) {
									$degree = $this->fetch_orientation_degree($exifdata);
								}
							}
							foreach ($dimensions as $dimension) {
								$mwh = explode('x', $dimension);
								$rfn = $foldername . $dimension . '.jpg';
								$this->picture_resizer($newfilename, $mwh[0], $mwh[1], $extension, $fileinfo[0], $fileinfo[1], $rfn, $this->sheel->config['resizequality'], $degree);
								$this->watermark($attachtype, $rfn, $extension, '');
							}
						}
					}
				}
				unset($tmp, $dimensions, $dimension, $mwh, $rfn, $rawdata);
				if ($valid_minwidth and $valid_minheight) {
					if (isset($fileinfo) and is_array($fileinfo)) { // multiple dimensions
						if (!empty($fileinfo['mime'])) {
							$filetype = $fileinfo['mime'];
						}
						return array(
							'fullpath' => $fullpath . $filehash . '/' . $filename,
							'foldername' => $foldername,
							'filename' => $filename,
							'filehash' => $filehash,
							'picturehash' => $picturehash,
							'filesize' => $filesize,
							'filetype' => $filetype,
							'width' => $fileinfo[0],
							'height' => $fileinfo[1],
							'watermarked' => $this->watermarked
						);
					} else { // single or no dimensions defined
						if ($data = getimagesize($newfilename)) { // return array with original upload info
							if (!empty($data['mime'])) {
								$filetype = $data['mime'];
							}
							return array(
								'fullpath' => $fullpath . $filehash . '/' . $filename,
								'foldername' => $foldername,
								'filename' => $filename,
								'filehash' => $filehash,
								'picturehash' => $picturehash,
								'filesize' => $filesize,
								'filetype' => $filetype,
								'width' => $data[0],
								'height' => $data[1],
								'watermarked' => $this->watermarked
							);
						}
					}
				} else {
					if (is_dir($foldername)) { // remove folder hash as something went wrong
						$this->recursive_remove_directory($foldername);
						$oldumask = umask(0);
						mkdir($foldername, 0755);
						umask($oldumask);
					}
					if ($bulkid > 0) {
						$this->sheel->db->query("
							UPDATE " . DB_PREFIX . "bulk_sessions
							SET errors = CONCAT(errors, 'Invalid min. w/h: " . $this->sheel->db->escape_string($img) . ", ')
							WHERE id = '" . intval($bulkid) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
		return false;
	}
	function url_exists($url = '')
	{
		$headers = get_headers($url);
		return ((stripos($headers[0], "200 OK")) ? true : false);
	}
	/*
	 * Function to fetch a remote image and determine it's physical file size using curl or fsockopen as the engine
	 *
	 * @param        string      image url (example: http://server.com/image.gif)
	 * @param        boolean     return human readable output version (ie: 10KB vs 10000)? default true
	 * @param        string      engine type to use (curl or fsock)
	 *
	 * @return	string      Returns remote file size if applicable
	 */
	function get_remote_file_size($url = '', $readable = true, $engine = 'fsock')
	{
		if ($engine == 'curl') {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_exec($ch);
			$return = curl_getinfo($ch);
			curl_close($ch);
			if (is_array($return) and $readable and isset($return['download_content_length']) and $return['download_content_length'] > 0) {
				$size = round($return['download_content_length'] / 1024, 2);
				$sz = "KB";
				if ($size > 1024) {
					$size = round($size / 1024, 2);
					$sz = "MB";
				}
				$html = "$size $sz";
				return $html;
			}
			return ((isset($return['download_content_length']) and $return['download_content_length'] > 0) ? $return['download_content_length'] : false);
		} else if ($engine == 'fsock') {
			$parsed = parse_url($url);
			if (isset($parsed["host"]) and !empty($parsed["host"])) {
				$host = $parsed["host"];
				$port = (($parsed["scheme"] == 'http') ? 80 : 443);
				if ($port == 80) {
					$fp = fsockopen($host, (($parsed['scheme'] == 'http') ? 80 : 443), $errno, $errstr, 5);
				} else {
					$context = stream_context_create();
					$result = stream_context_set_option($context, 'ssl', 'verify_peer', false);
					$result = stream_context_set_option($context, 'ssl', 'verify_host', false);
					$result = stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
					$fp = @stream_socket_client('ssl://' . $parsed["host"] . ':443' . $parsed["path"], $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
				}
				if (!$fp) {
					return false;
				} else {
					@fputs($fp, "HEAD $url HTTP/1.1\r\n");
					@fputs($fp, "HOST: $host\r\n");
					@fputs($fp, "Connection: close\r\n\r\n");
					$headers = "";
					while (!@feof($fp)) {
						$headers .= @fgets($fp, 1024);
					}
				}
				@fclose($fp);
				$return = false;
				$arr_headers = explode("\n", $headers);
				foreach ($arr_headers as $header) {
					$s = 'Location: '; // follow redirect
					if (substr(strtolower($header), 0, strlen($s)) == strtolower($s)) {
						$url = trim(substr($header, strlen($s)));
						return $this->get_remote_file_size($url, $readable, $engine);
					}
					$s = "Content-Length: "; // parse for content length
					if (substr(strtolower($header), 0, strlen($s)) == strtolower($s)) {
						$return = trim(substr($header, strlen($s)));
						break;
					}
				}
				/*if ($return == false OR $return == 0 OR $return == '')
				{ // cloudflare and other cache methods? check to see if we get a 200 OK at least
				if ($this->url_exists($url))
				{ // we're valid!
				$return = 100000;
				}
				}*/
				if ($return != false and $return > 0 and $readable) {
					$size = round($return / 1024, 2);
					$sz = "KB";
					if ($size > 1024) {
						$size = round($size / 1024, 2);
						$sz = "MB";
					}
					$return = "$size $sz";
				}
				return $return; // false, 0 or 398481
			}
		}
		return false;
	}
	/**
	 * Function to fetch the actual filename of an image called over http: or https:// protocol.
	 *
	 * @param       string        full url including image filename (ex: http://domain.com/image1.jpg)
	 * @param       string        backup image name to use if we cannot process the url version
	 *
	 * @return      string        Returns image filename (ex: image1.jpg)
	 */
	function fetch_url_image_filename($img = '', $backupimg = '')
	{
		if (!empty($img)) {
			$ar = explode('/', $img);
			$filename = $ar[count($ar) - 1];
			return $filename;
		}
		return $backupimg;
	}
	function remove_exif($old = '', $new = '')
	{
		$f1 = fopen($old, 'rb');
		$f2 = fopen($new, 'wb');
		while (($s = fread($f1, 2))) { // find EXIF marker
			$word = unpack('ni', $s)['i'];
			if ($word == 0xFFE1) { // read length (includes the word used for the length)
				$s = fread($f1, 2);
				$len = unpack('ni', $s)['i'];
				// skip the EXIF info
				fread($f1, $len - 2);
				break;
			} else {
				fwrite($f2, $s, 2);
			}
		}
		while (($s = fread($f1, 4096))) { // write the rest of the file
			fwrite($f2, $s, strlen($s));
		}
		fclose($f1);
		fclose($f2);
	}
	function resize_image($img, $maxwidth, $maxheight)
	{
		//This function will return the specified dimension(width,height)
		//dimension[0] - width
		//dimension[1] - height
		$dimension = array();
		$imginfo = getimagesize($img);
		$imgwidth = $imginfo[0];
		$imgheight = $imginfo[1];
		if ($imgwidth > $maxwidth) {
			$ratio = $maxwidth / $imgwidth;
			$newwidth = round($imgwidth * $ratio);
			$newheight = round($imgheight * $ratio);
			if ($newheight > $maxheight) {
				$ratio = $maxheight / $newheight;
				$dimension[] = round($newwidth * $ratio);
				$dimension[] = round($newheight * $ratio);
				return $dimension;
			} else {
				$dimension[] = $newwidth;
				$dimension[] = $newheight;
				return $dimension;
			}
		} else if ($imgheight > $maxheight) {
			$ratio = $maxheight / $imgheight;
			$newwidth = round($imgwidth * $ratio);
			$newheight = round($imgheight * $ratio);
			if ($newwidth > $maxwidth) {
				$ratio = $maxwidth / $newwidth;
				$dimension[] = round($newwidth * $ratio);
				$dimension[] = round($newheight * $ratio);
				return $dimension;
			} else {
				$dimension[] = $newwidth;
				$dimension[] = $newheight;
				return $dimension;
			}
		} else {
			$dimension[] = $imgwidth;
			$dimension[] = $imgheight;
			return $dimension;
		}
	}
	function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80)
	{
		$imgsize = getimagesize($source_file);
		$width = $old_width = $imgsize[0]; // 108
		$height = $old_height = $imgsize[1]; // 108
		$mime = $imgsize['mime'];
		switch ($mime) {
			case 'image/gif': {
					$image_create = "imagecreatefromgif";
					$image = "imagegif";
					break;
				}
			case 'image/png': {
					$image_create = "imagecreatefrompng";
					$image = "imagepng";
					$quality = 7;
					break;
				}
			case 'image/jpeg': {
					$image_create = "imagecreatefromjpeg";
					$image = "imagejpeg";
					$quality = 80;
					break;
				}
			default: {
					return false;
					break;
				}
		}
		$dst_img = imagecreatetruecolor($max_width, $max_height);
		imagealphablending($dst_img, false);
		imagesavealpha($dst_img, true);
		$transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
		imagefilledrectangle($dst_img, 0, 0, $max_width, $max_height, $transparent);
		$src_img = $image_create($source_file);

		$dimension = $this->resize_image($source_file, $max_width, $max_height);
		$new_width = $dimension[0];
		$new_height = $dimension[1];

		// Resize old image into new
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
		$image($dst_img, $dst_dir, $quality);
		if ($dst_img) {
			imagedestroy($dst_img);
		}
		if ($src_img) {
			imagedestroy($src_img);
		}
		//usage example
		//$sheel->attachment_tools->resize_crop_image(208, 208, "videos/thumb.png", "fd/image2.jpg", 85);
	}
	function remove_attachments_from_abandoned_messages()
	{ // runs from cron.daily.php
		$sql = $this->sheel->db->query("
			SELECT attachid, pmb_id
			FROM " . DB_PREFIX . "attachment
			WHERE attachtype = 'pmb'
				AND user_id > 0
				AND pmb_id > 0
		", 0, null, __FILE__, __LINE__);
		if ($this->sheel->db->num_rows($sql) > 0) {
			$i = 0;
			while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
				$sql2 = $this->sheel->db->query("
					SELECT id
					FROM " . DB_PREFIX . "pmb
					WHERE event_id = '" . $res['pmb_id'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->sheel->db->num_rows($sql2) <= 0) { // remove abandonded attachment
					$this->remove_attachment($res['attachid']);
					$i++;
				}
			}
			return 'attachment_tools->remove_attachments_from_abandoned_messages() [' . $i . '], ';
		}
	}
	function migrate_assets_to_uploads_500_600()
	{
		$sourcefolder = DIR_FUNCTIONS . 'assets/images/ax';
		$targetfolder = DIR_ATTACHMENTS . 'ax';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'cache';
		$targetfolder = DIR_ATTACHMENTS . 'cache';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/categoryheros';
		$targetfolder = DIR_ATTACHMENTS . 'categoryheros';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/categoryicons';
		$targetfolder = DIR_ATTACHMENTS . 'categoryicons';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/categorysearch';
		$targetfolder = DIR_ATTACHMENTS . 'categorysearch';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/categorysponsor';
		$targetfolder = DIR_ATTACHMENTS . 'categorysponsor';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/categorythumbs';
		$targetfolder = DIR_ATTACHMENTS . 'categorythumbs';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/content';
		$targetfolder = DIR_ATTACHMENTS . 'content';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/heros';
		$targetfolder = DIR_ATTACHMENTS . 'heros';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/meta';
		$targetfolder = DIR_ATTACHMENTS . 'meta';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		// logos & favicon
		if (file_exists(DIR_FUNCTIONS . 'assets/images/logo.png') and !file_exists(DIR_ATTACHMENTS . 'meta/logo.png')) { // logo.png
			$data = file_get_contents(DIR_FUNCTIONS . 'assets/images/logo.png');
			$path = DIR_ATTACHMENTS . 'meta/logo.png';
			file_put_contents($path, $data);
		}
		if (file_exists(DIR_FUNCTIONS . 'assets/images/meta/favicon.png') and !file_exists(DIR_ATTACHMENTS . 'meta/favicon.png')) { // favicon.png
			$data = file_get_contents(DIR_FUNCTIONS . 'assets/images/meta/favicon.png');
			$path = DIR_ATTACHMENTS . 'meta/favicon.png';
			file_put_contents($path, $data);
		}
		if (file_exists(DIR_FUNCTIONS . 'assets/images/logo-mobile.png') and !file_exists(DIR_ATTACHMENTS . 'meta/logo-mobile.png')) { // logo-mobile.png
			$data = file_get_contents(DIR_FUNCTIONS . 'assets/images/logo-mobile.png');
			$path = DIR_ATTACHMENTS . 'meta/logo-mobile.png';
			file_put_contents($path, $data);
		}

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/nonprofit';
		$targetfolder = DIR_ATTACHMENTS . 'nonprofit';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/plan';
		$targetfolder = DIR_ATTACHMENTS . 'plan';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/product';
		$targetfolder = DIR_ATTACHMENTS . 'product';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			@mkdir($targetfolder . '/brand', 0755);
			@mkdir($targetfolder . '/gtin', 0755);
			@mkdir($targetfolder . '/owner', 0755);
			@mkdir($targetfolder . '/upc', 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/vendor';
		$targetfolder = DIR_ATTACHMENTS . 'vendor';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);

		$sourcefolder = DIR_FUNCTIONS . 'assets/images/watermarks';
		$targetfolder = DIR_ATTACHMENTS . 'watermarks';
		if (!is_dir($targetfolder)) {
			$oldumask = umask(0);
			@mkdir($targetfolder, 0755);
			umask($oldumask);
		}
		$this->recursive_copy_directory($sourcefolder, $targetfolder, 0755);
		unset($sourcefolder, $targetfolder);
		return true;
	}
}
?>