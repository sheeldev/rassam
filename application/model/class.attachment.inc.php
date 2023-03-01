<?php
/**
 * Attachment class to perform the majority of uploading and attachment handling operations within sheel.
 *
 * @package      sheel\Attachment
 * @version      1.0.0.0
 * @author       sheel
 */
class attachment
{
        protected $sheel;
        /**
         * Total attachments counter
         * @var integer
         * @access public
         */
        var $totalattachments = null;
        /**
         * Total diskspace counter
         * @var integer
         * @access public
         */
        var $totaldiskspace = null;
        /**
         * Total downloads counter
         * @var integer
         * @access public
         */
        var $totaldownloads = null;
        /**
         * Storage type method
         * @var string
         * @access public
         */
        var $storagetype = null;
        /**
         * Temp filename placeholder
         * @var string
         * @access public
         */
        var $temp_file_name = null;
        /**
         * Real filename placeholder
         * @var string
         * @access public
         */
        var $file_name = null;
        /**
         * Upload folder
         * @var string
         * @access public
         */
        var $upload_dir = null;
        /**
         * Maximum filesize placeholder
         * @var integer
         * @access public
         */
        var $max_file_size = null;
        /**
         * File extensions array placeholder
         * @var array
         * @access public
         */
        var $ext_array = array();
        /**
         * Filetype placeholder
         * @var string
         * @access public
         */
        var $filetype = null;
        /**
         * Original filetype placeholder
         * @var string
         * @access public
         */
        var $filetype_original = null;
        /**
         * Datetime placeholder
         * @var string
         * @access public
         */
        var $date_time = null;
        /**
         * Picture width placeholder
         * @var integer
         * @access public
         */
        var $width = null;
        /**
         * Picture height placeholder
         * @var integer
         * @access public
         */
        var $height = null;
        /**
         * Filesize placeholder
         * @var integer
         * @access public
         */
        var $filesize = null;
        /**
         * Original filename placeholder
         * @var string
         * @access public
         */
        var $file_name_original = null;
        /**
         * Picture was resized placeholder
         * @var boolean
         * @access public
         */
        var $pictureresized = false;
        /**
         * Picture was watermarked placeholder
         * @var boolean
         * @access public
         */
        var $watermarked = false;
        /**
         * Exif placeholder
         * @var boolean
         * @access public
         */
        var $exif = null;
        var $picturehash = null;
        var $uncrypted = null;
        var $remove_errors = array();
        /**
         * File mimetypes placeholder
         * @var array
         * @access public
         */
        var $mimetypes = array(
                'image/gif',
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/psd',
                'image/bmp',
                'image/tiff',
                'image/jp2',
                'image/iff',
                'image/fif',
                'image/florian',
                'image/g3fax',
                'image/xbm',
                'image/ief',
                'image/jutvision',
                'image/naplps',
                'image/vnd.wap.wbmp',
                'image/vnd.microsoft.icon',
                'image/vnd.fpx',
                'image/vnd.net-fpx',
                'image/vnd.djvu',
                'image/vnd.dwg',
                'image/vnd.xiff',
                'image/vnd.rn-realflash',
                'image/vnd.rn-realpix',
                'image/cmu-raster',
                'image/x-icon',
                'image/x-dwg',
                'image/x-cmu-raster',
                'image/x-cmu-raster',
                'image/x-portable-anymap',
                'image/x-portable-bitmap',
                'image/x-portable-graymap',
                'image/x-portable-pixmap',
                'image/x-xwindowdump',
                'image/x-rgb',
                'image/x-xbitmap',
                'image/x-xpixmap',
                'image/x-xwindowdump',
                'image/x-png',
                'image/x-jps',
                'image/x-pict',
                'image/x-pcx',
                'image/x-xbm',
                'image/x-xpixmap',
                'image/x-quicktime',
                'image/x-niff',
                'image/x-tiff',
                'image/webp'
        );
        /**
         * Attachment type placeholder
         *
         * @var string
         * @access public
         */
        public $attachtype = '';
        /**
         * Attachment filehash placeholder
         *
         * @var string
         * @access public
         */
        public $filehash = '';
        /**
         * Attachment user id
         *
         * @var integer
         * @access public
         */
        public $user_id = 0;
        /**
         * Listing id
         *
         * @var integer
         * @access public
         */
        public $project_id = 0;
        /**
         * Category id
         *
         * @var integer
         * @access public
         */
        public $category_id = 0;
        /***
         * Constructor
         */
        function __construct($sheel)
        {
                $this->sheel = $sheel;
        }
        /**
         * Function for printing the innerhtml javascript code in the templates
         *
         * @param       string       attachment div id
         * @param       string       attachment list html contents
         *
         * @return      string       Returns javascript code
         */
        function print_innerhtml_js($attachmentlist = 'attachmentlist', $attachment_list_html = '', $attachmentlist_hide = '')
        {
                $js = '<script>
var ' . $attachmentlist . ' = window.parent.document.getElementById("' . $attachmentlist . '");';
                $js .= (!empty($attachmentlist_hide) ? 'var ' . $attachmentlist_hide . ' = window.parent.document.getElementById(\'' . $attachmentlist_hide . '\');' : '');
                $js .= '' . $attachmentlist . '.innerHTML = \'' . addslashes($attachment_list_html) . '\';';
                $js .= (!empty($attachmentlist_hide) ? $attachmentlist_hide . '.innerHTML = \'\';' : '');
                $js .= '
</script>';

                return $js;
        }
        /**
         * Function for returning the total amount of attachments in the system
         *
         * @return      integer      total amount of attachments
         */
        function totalattachments()
        {
                $sql = $this->sheel->db->query("
                        SELECT COUNT(*) AS totalattachments
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['totalattachments'];
                }
                return '0';
        }
        /**
         * Function for returning the total amount of disk space used by attachments in the system
         *
         * @return      integer      total amount of attachments
         */
        function totaldiskspace()
        {
                $sql = $this->sheel->db->query("
                        SELECT SUM(filesize) AS totaldiskspace
                        FROM " . DB_PREFIX . "attachment
                        WHERE (filesize != '' OR filesize != '0')
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $this->print_filesize($res['totaldiskspace']);
                }
                return $this->print_filesize(0);
        }
        /**
         * Function for returning the total downloads based on attachments in the system
         *
         * @return      integer      total number of downloads
         */
        function totaldownloads()
        {
                $sql = $this->sheel->db->query("
                        SELECT SUM(counter) AS totaldownloads
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                        return $res['totaldownloads'];
                }
                return '0';
        }
        /**
         * Function for returning the method of storage used by the attachment system
         *
         * @param       string       action of function to return
         *
         * @return      mixed
         */
        function storagetype($action = '')
        {
                if ($action == 'type') {
                        return '{_attachments_are_currently_being_stored_in_the_filepath_system}';
                } else if ($action == 'formaction') {
                        return '<input type="radio" name="form[action]" id="action2" value="rebuildpictures" checked="checked" /> {_rebuild_all_pictures_to_adhere}';
                }
        }
        /**
         * Function for validating the filename extention based on the file being uploaded
         *
         * @return      bool         true or false if extension is valid
         */
        function validate_extension()
        {
                $extension = mb_strtolower(mb_strrchr($this->file_name, '.'));
                if (!$this->file_name) {
                        return false;
                }
                if (!$this->ext_array) {
                        return true;
                }
                $extensions = array();
                foreach ($this->ext_array as $value) {
                        $first_char = mb_substr($value, 0, 1);
                        $extensions[] = (($first_char <> '.') ? '.' . mb_strtolower($value) : mb_strtolower($value));
                }
                foreach ($extensions as $accepted) {
                        if ($accepted == $extension) {
                                return true;
                        }
                }
                return false;
        }
        /**
         * Function to return the actual file type of a file being uploaded
         *
         * @return      string       file type
         */
        function get_file_type()
        {
                $file_type = trim($this->filetype);
                $file_type = ($file_type) ? $file_type : '';
                return $file_type;
        }
        /**
         * Function to return the actual file size of a file being uploaded
         *
         * @return      string       file type
         */
        function get_file_size()
        {
                $size = (!empty($this->temp_file_name)) ? filesize($this->temp_file_name) : 0;
                return $size;
        }
        /**
         * Function to return the maximum size permitted for upload (should already be assigned)
         *
         * @return      string       maximum file size
         */
        function get_max_size()
        {
                $kb = 1024;
                $mb = 1024 * $kb;
                $gb = 1024 * $mb;
                $tb = 1024 * $gb;
                if (!empty($this->max_file_size)) {
                        if ($this->max_file_size < $kb) {
                                $this->max_file_size = "{_max_file_size_bytes}";
                        } else if ($this->max_file_size < $mb) {
                                $final = round($this->max_file_size / $kb, 2);
                                $this->max_file_size = "$final";
                        } else if ($this->max_file_size < $gb) {
                                $final = round($this->max_file_size / $mb, 2);
                                $this->max_file_size = "$final";
                        } else if ($this->max_file_size < $tb) {
                                $final = round($this->max_file_size / $gb, 2);
                                $this->max_file_size = "$final";
                        } else {
                                $final = round($this->max_file_size / $tb, 2);
                                $this->max_file_size = "$final";
                        }
                } else {
                        $this->max_file_size = '{_error_no_size_passed}';
                }
                return $this->max_file_size;
        }
        /**
         * Function to return the full upload directory (should already be assigned)
         *
         * @return      string       full folder path
         */
        function get_upload_directory()
        {
                $upload_dir = trim($this->upload_dir);
                if ($upload_dir) {
                        $ud_len = mb_strlen($upload_dir);
                        $last_slash = mb_substr($upload_dir, $ud_len - 1, 1);
                        if ($last_slash <> '/') {
                                $upload_dir = $upload_dir . '/';
                        } else {
                                $upload_dir = $upload_dir;
                        }
                        $handle = @opendir($upload_dir);
                        if ($handle) {
                                $upload_dir = $upload_dir;
                                closedir($handle);
                        } else {
                                $upload_dir = 'ERROR';
                        }
                } else {
                        $upload_dir = 'ERROR';
                }
                return $upload_dir;
        }
        /**
         * Function to handle the attachment type settings for the current upload
         *
         * @param       string       attachment type
         * @param       integer      user id
         * @param       integer      listing id
         * @param       integer      private message event id
         * @param       string       filehash
         * @param       integer      ads id
         *
         * @return      array        Returns array with rebuilt attachment settings
         */
        function handle_attachtype_rebuild_settings($attachtype = '', $userid = 0, $projectid = 0, $eventid = 0, $filehash = '', $ads_id = 0)
        {
                $array = array();
                $maximum_files = $max_width = $max_height = $min_width = $min_height = $max_filesize = $max_size = $extensions = $query = $queryextra = '';
                if ($attachtype == 'profile') {
                        $maximum_files = 1;
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->print_filesize($this->sheel->permissions->check_access($userid, 'uploadlimit'));
                        $max_width = $max_height = $min_width = $min_height = 0;
                        $this->sheel->show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_profileextensions']);
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $queryextra = "AND user_id = '" . intval($userid) . "'";
                } else if ($attachtype == 'itemphoto') {
                        $maximum_files = $this->sheel->config['attachmentlimit_slideshowmaxfiles'];
                        $max_size = $this->print_filesize($this->sheel->permissions->check_access($userid, 'uploadlimit'));
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_width = $max_height = 0;
                        $min_width = $this->sheel->config['minwidth'];
                        $min_height = $this->sheel->config['minheight'];
                        $this->sheel->show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_productphotoextensions']);
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $queryextra = "AND user_id = '" . intval($userid) . "' AND project_id = '" . intval($projectid) . "'";
                } else if ($attachtype == 'pmb') {
                        $maximum_files = $this->sheel->permissions->check_access($userid, 'maxpmbattachments');
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->print_filesize($this->sheel->permissions->check_access($userid, 'uploadlimit'));
                        $max_width = $max_height = $min_width = $min_height = 0;
                        $this->sheel->show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_defaultextensions']);
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $queryextra = "AND user_id = '" . intval($userid) . "' AND project_id = '" . intval($projectid) . "' AND pmb_id = '" . intval($eventid) . "'";

                } else if ($attachtype == 'digital') {
                        $maximum_files = 1;
                        $max_size = $this->print_filesize($this->sheel->permissions->check_access($userid, 'uploadlimit'));
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_width = $max_height = $min_width = $min_height = 0;
                        $this->sheel->show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_digitalfileextensions']);
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $queryextra = "AND user_id = '" . intval($userid) . "' AND project_id = '" . intval($projectid) . "'";
                }

                $query = "SELECT attachid, attachtype, user_id, project_id, pmb_id, category_id, date, filename, filetype, filetype_original, visible, counter, filesize, filehash, ipaddress, tblfolder_ref FROM " . DB_PREFIX . "attachment WHERE attachtype = '" . $this->sheel->db->escape_string($attachtype) . "' $queryextra";
                $array = array(
                        'maximum_files' => $maximum_files,
                        'max_width' => $max_width,
                        'max_height' => $max_height,
                        'min_width' => $min_width,
                        'min_height' => $min_height,
                        'max_filesize' => $max_filesize,
                        'max_size' => $max_size,
                        'extensions' => $extensions,
                        'query' => $query
                );
                return $array;
        }
        /**
         * Function to handle the uploaded file settings parsing based on the attachment type
         *
         * @param        string       attach type
         *
         * @return       string
         */
        function handle_attachtype_upload_settings($attachtype = '', $userid = 0)
        {
                $array = array();
                if ($attachtype == 'profile') {
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $upload_to = DIR_PROFILE_ATTACHMENTS;
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_profileextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $this->sheel->config['attachmentlimit_profileextensions']);
                } else if ($attachtype == 'itemphoto' or $attachtype == 'eventphoto') {
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_productphotoextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $this->sheel->config['attachmentlimit_productphotoextensions']);
                } else if ($attachtype == 'pmb') {
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $upload_to = DIR_PMB_ATTACHMENTS;
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $this->sheel->config['attachmentlimit_defaultextensions']);
                } else if ($attachtype == 'digital') {
                        $max_filesize = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $max_size = $this->sheel->permissions->check_access($userid, 'uploadlimit');
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $this->sheel->config['attachmentlimit_digitalfileextensions']);
                        $extensions = '';
                        foreach ($permittedext as $value) {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $this->sheel->config['attachmentlimit_digitalfileextensions']);
                }

                $array = array(
                        'max_filesize' => $max_filesize,
                        'max_size' => $max_size,
                        'upload_to' => $upload_to,
                        'extensions' => $extensions
                );
                return $array;
        }
        /**
         * Function to create a watermarked stamped image (from a text string or source image).  This function will overwrite the source file if no destination folder/file.jpg is specified.
         *
         * @param        string       attachment type
         * @param        string       full server path to the picture that you are going to watermark
         * @param        string       file extension of real picture being passed to this function
         * @param        string       blank (to process current source file only) or full server path to a new file which will be the source file with watermark text on it
         *
         * @return       string
         */
        function watermark($attachtype = '', $src = '', $srcextension = '', $dsrc = '')
        {
                if ($this->sheel->config['watermark'] == 0) {
                        $this->watermarked = false;
                        return false;
                }
                $wsrc = !empty($this->sheel->config['watermark_image']) ? DIR_ATTACHMENTS . 'watermarks/' . $this->sheel->config['watermark_image'] : '';
                $mode = 'image';
                if (empty($wsrc) and !empty($this->sheel->config['watermark_text'])) {
                        $mode = 'text';
                }
                $text = $this->sheel->config['watermark_text'];
                $font = DIR_FONTS . $this->sheel->config['watermark_textfont'];
                $font_size = $this->sheel->config['watermark_textsize'];
                $quality = $this->sheel->config['watermark_quality'];
                $font_angle = $this->sheel->config['watermark_angle'];
                $markposition = $this->sheel->config['watermark_position'];
                $markpadding = $this->sheel->config['watermark_padding'];
                $opacity = $this->sheel->config['watermark_imageopacity'];
                if ($attachtype == 'profile') {
                        if ($this->sheel->config['watermark_profiles'] == 0) {
                                $this->watermarked = false;
                                return false;
                        }
                } else if ($attachtype == 'itemphoto') {
                        if ($this->sheel->config['watermark_itemphoto'] == 0) {
                                $this->watermarked = false;
                                return false;
                        }
                } else {
                        $this->watermarked = false;
                        return false;
                }
                $r = 1;
                $e = strtolower(substr($srcextension, strrpos($srcextension, '.') + 1, 3));
                if (($e == 'jpg') or ($e == 'peg') or ($e == 'jpe')) {
                        $oldimage = imagecreatefromjpeg($src) or $r = 0;
                        imagealphablending($oldimage, true);
                } else if ($e == 'gif') {
                        $oldimage = imagecreatefromgif($src) or $r = 0;
                } else if ($e == 'bmp') {
                        $oldimage = $this->imagecreatefrombmp($src) or $r = 0;
                } else if ($e == 'png') {
                        $oldimage = imagecreatefrompng($src) or $r = 0;
                } else if (($e == 'tif') or ($e == 'iff') or ($e == 'pdf')) {
                        $oldimage = $this->imagecreatefromtiff($src) or $r = 0;
                } else {
                        $r = 0;
                }
                if ($r) {
                        list($source_image_width, $source_image_height) = @getimagesize($src);
                        if ($mode == 'text') {
                                $newthumb = imagecreatetruecolor($source_image_width, $source_image_height);
                                imagecopyresampled($newthumb, $oldimage, 0, 0, 0, 0, $source_image_width, $source_image_height, $source_image_width, $source_image_height);
                                $font_color = imagecolorallocate($newthumb, 0, 0, 0); // black
                                $box = imagettfbbox($font_size, 0, $font, $text);
                                $textwidth = abs($box[4] - $box[0]);
                                $textheight = abs($box[5] - $box[1]);
                                switch ($markposition) {
                                        case 'TOPLEFT': {
                                                        $xcord = $markpadding;
                                                        $ycord = ($fontsize + $markpadding);
                                                        break;
                                                }
                                        case 'TOPCENTER': {
                                                        $xcord = (($source_image_width - $textwidth) / 2);
                                                        $ycord = ($font_size + $markpadding);
                                                        break;
                                                }
                                        case 'TOPRIGHT': {
                                                        $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                        $ycord = ($fontsize + $markpadding);
                                                        break;
                                                }
                                        case 'MIDLEFT': {
                                                        $xcord = $markpadding;
                                                        $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                        break;
                                                }
                                        case 'MIDCENTER': {
                                                        $xcord = (($source_image_width - $textwidth) / 2);
                                                        $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                        break;
                                                }
                                        case 'MIDRIGHT': {
                                                        $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                        $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                        break;
                                                }
                                        case 'BOTLEFT': {
                                                        $xcord = $markpadding;
                                                        $ycord = ($source_image_height - $textheight) + ($fontsize - $markpadding);
                                                        break;
                                                }
                                        case 'BOTCENTER': {
                                                        $xcord = (($source_image_width - $textwidth) / 2);
                                                        $ycord = ($source_image_height - $textheight) + ($font_size - $markpadding);
                                                        break;
                                                }
                                        case 'BOTRIGHT': {
                                                        $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                        $ycord = ($source_image_height - $textheight) + ($fontsize - $markpadding);
                                                        break;
                                                }
                                }
                                imagettftext($newthumb, $font_size, $font_angle, $xcord, $ycord, $font_color, $font, $text);
                                if (!empty($dsrc)) {
                                        if (($e == 'jpg') or ($e == 'peg') or ($e == 'jpe')) {
                                                imagejpeg($newthumb, $dsrc, $quality);
                                        } else if ($e == 'gif') {
                                                imagegif($newthumb, $dsrc);
                                        } else if ($e == 'bmp') {
                                                imagewbmp($newthumb, $dsrc);
                                        } else if ($e == 'png') {
                                                imagepng($newthumb, $dsrc, (int) $quality / 10);
                                        }
                                } else {
                                        if (($e == 'jpg') or ($e == 'peg') or ($e == 'jpe')) {
                                                imagejpeg($newthumb, $src, $quality);
                                        } else if ($e == 'gif') {
                                                imagegif($newthumb, $src);
                                        } else if ($e == 'bmp') {
                                                imagewbmp($newthumb, $src);
                                        } else if ($e == 'png') {
                                                imagepng($newthumb, $src, (int) $quality / 10); // always best output
                                        }
                                }
                                imagedestroy($newthumb);
                                imagedestroy($oldimage);
                                $this->watermarked = true;
                        } else if ($mode == 'image' and !empty($wsrc)) {
                                $wext = substr($wsrc, -3);
                                list($source_watermark_width, $source_watermark_height) = @getimagesize($wsrc);
                                $original_source_watermark_width = $source_watermark_width; // 275px
                                $original_source_watermark_height = $source_watermark_height; // 78px
                                if ($source_watermark_width > ($source_image_width * 0.3) && false) {
                                        $a = ($source_watermark_width < $source_watermark_height) ? $source_watermark_width / $source_watermark_height : $source_watermark_height / $source_watermark_width;
                                        $source_watermark_width = (int) $source_image_width * 0.3;
                                        $source_watermark_height = (int) $source_watermark_width * $a;
                                }
                                if ($wext == 'gif') {
                                        $newthumb = imagecreatefromgif($wsrc);
                                } else if ($wext == 'png') {
                                        $newthumb = imagecreatefrompng($wsrc);
                                        imagealphablending($newthumb, true);
                                        imagesavealpha($newthumb, true);
                                } else {
                                        $this->watermarked = false;
                                        return false;
                                }
                                $x = $source_image_width - $source_watermark_width;
                                $x = (int) $x - ($x / 100);
                                $y = $source_image_height - $source_watermark_height;
                                $y = (int) $y - ($y / 100);
                                /*
                                 * ALIGN TOP, LEFT      : 0, 0, 0, 0
                                 * $xcord = 0
                                 * $ycord = 0
                                 *
                                 * ALIGN TOP RIGHT      : $source_image_width - $source_watermark_width, 0, 0, 0
                                 * $xcord = ($source_image_width - $source_watermark_width)
                                 * $ycord = 0
                                 *
                                 * ALIGN BOTTOM RIGHT   : $source_image_width - $source_watermark_width, $source_image_height - $source_watermark_height, 0, 0
                                 * $xcord = ($source_image_width - $source_watermark_width)
                                 * $ycord = ($source_image_height - $source_watermark_height)
                                 *
                                 * ALIGN BOTTOM LEFT    : 0, $source_image_height - $source_watermark_height, 0, 0
                                 * $xcord = 0
                                 * $ycord = ($source_image_height - $source_watermark_height)
                                 *
                                 * ALIGN CENTER CENTER  : floor(($source_image_width - $source_watermark_width) / 2), floor(($source_image_height - $source_watermark_height) / 2), 0, 0
                                 * $xcord = floor(($source_image_width - $source_watermark_width) / 2)
                                 * $ycord = floor(($source_image_height - $source_watermark_height) / 2)
                                 */
                                // xcord = x-coordinate of destination point.
                                // ycord = y-coordinate of destination point.
                                imagecopy($oldimage, $newthumb, $x, $y, 0, 0, $original_source_watermark_width, $original_source_watermark_height);
                                if (!empty($dsrc)) {
                                        if (($e == 'jpg') or ($e == 'peg') or ($e == 'jpe')) {
                                                imagejpeg($oldimage, $dsrc, $quality);
                                        } else if ($e == 'gif') {
                                                imagegif($oldimage, $dsrc);
                                        } else if ($e == 'bmp') {
                                                imagewbmp($oldimage, $dsrc);
                                        } else if ($e == 'png') {
                                                imagepng($oldimage, $dsrc, (int) $quality / 10);
                                        }
                                } else {
                                        if (($e == 'jpg') or ($e == 'peg') or ($e == 'jpe')) {
                                                imagejpeg($oldimage, $src, $quality);
                                        } else if ($e == 'gif') {
                                                imagegif($oldimage, $src);
                                        } else if ($e == 'bmp') {
                                                imagewbmp($oldimage, $src);
                                        } else if ($e == 'png') {
                                                imagepng($oldimage, $src, (int) $quality / 10);
                                        }
                                }
                                imagedestroy($newthumb);
                                imagedestroy($oldimage);
                                $this->watermarked = true;
                        } else {
                                $this->watermarked = false;
                        }
                } else {
                        $this->watermarked = false;
                }
        }
        /**
         * Function for validating the uploaded file and returning the upload information via array.
         * This function is also responsible for determining if an uploaded picture is larger (or smaller) than max/min values
         * and will attempt to scale the picture down keeping aspect ratio.
         *
         * @return      array         Returns array format with information about the file upload details (height, width, if failed, if success, etc.)
         */
        function validate_size()
        {
                $newfilename = '';
                $this->pictureresized = $this->watermarked = false;
                $this->exif = '';
                $this->picturehash = '';
                $this->file_name_original = $this->file_name;
                $this->filetype_original = $this->filetype;
                $this->filesize = 0;
                $this->attachtype = $this->uncrypted['attachtype'] = (isset($this->uncrypted['attachtype']) and empty($this->attachtype)) ? $this->uncrypted['attachtype'] : $this->attachtype;
                $this->user_id = (isset($this->uncrypted['user_id']) and empty($this->user_id)) ? $this->uncrypted['user_id'] : $this->user_id;
                $this->filehash = $filehash = (isset($this->uncrypted['filehash']) and !empty($this->uncrypted['filehash'])) ? $this->uncrypted['filehash'] : md5(uniqid(microtime()));
                $attachid = $this->sheel->db->fetch_field(DB_PREFIX . "attachment", "filehash = '" . $this->sheel->db->escape_string($this->filehash) . "' AND user_id = '" . intval($this->user_id) . "'", "attachid");
                $this->filehash = $filehash = ($attachid > 0) ? md5(uniqid(microtime())) : $this->filehash;
                $extension = mb_strtolower(mb_strrchr($this->file_name_original, '.'));
                $valid_filesize = true;
                $failedfilesize = '0';
                if (isset($this->temp_file_name) and is_uploaded_file($this->temp_file_name)) { // something was uploaded
                        $this->filesize = filesize($this->temp_file_name);
                        foreach ($this->ext_array as $value) {
                                $first_char = mb_substr($value, 0, 1);
                                if ($first_char <> '.') {
                                        $extensions[] = '.' . mb_strtolower($value);
                                } else {
                                        $extensions[] = mb_strtolower($value);
                                }
                        }
                        unset($first_char);
                        if (in_array($extension, $extensions)) { // valid extension
                                if ($this->attachtype == 'profile') {
                                        $fullpath = DIR_PROFILE_ATTACHMENTS;
                                } else if ($this->attachtype == 'itemphoto' or $this->attachtype == 'digital' or $this->attachtype == 'eventphoto') {
                                        $fullpath = DIR_AUCTION_ATTACHMENTS;
                                } else if ($this->attachtype == 'pmb') {
                                        $fullpath = DIR_PMB_ATTACHMENTS;
                                } else {
                                }
                                $newfilename = $fullpath . $filehash . '/' . $this->file_name_original;
                                $foldername = $fullpath . $filehash . '/';
                                if (is_dir($foldername)) { // remove folder hash if exists (so we can re-create new content)
                                        $this->recursive_remove_directory($foldername);
                                        $oldumask = umask(0);
                                        mkdir($foldername, 0755); // 0777
                                        umask($oldumask);
                                } else {
                                        $oldumask = umask(0);
                                        mkdir($foldername, 0755); // 0777
                                        umask($oldumask);
                                }
                                if (move_uploaded_file($this->temp_file_name, $newfilename)) {
                                        $this->filetype_original = empty($fileinfo['mime']) ? $this->filetype : $fileinfo['mime'];
                                        if (in_array($this->filetype, $this->mimetypes)) { // uploaded file is an image
                                                if ($fileinfo = getimagesize($newfilename)) {
                                                        $degree = 0;
                                                        if (function_exists('exif_read_data')) {
                                                                $exifdata = @exif_read_data($newfilename, 0, true);
                                                                if (!empty($exifdata)) {
                                                                        $degree = $this->fetch_orientation_degree($exifdata);
                                                                        $this->exif = serialize($exifdata);
                                                                }
                                                                unset($exifdata);
                                                        }
                                                        $this->picturehash = md5_file($newfilename);
                                                        // picture factory
                                                        $tmp = $this->sheel->picture_factory($this->user_id);
                                                        $dimensions = $tmp[$this->attachtype]['dimensions'];
                                                        if (count($dimensions) > 0) {
                                                                foreach ($dimensions as $dimension) {
                                                                        $mwh = explode('x', $dimension);
                                                                        $rfn = $foldername . $dimension . '.jpg'; // 160x160.jpg
                                                                        $this->picture_resizer($newfilename, $mwh[0], $mwh[1], $extension, $fileinfo[0], $fileinfo[1], $rfn, $this->sheel->config['resizequality'], $degree);
                                                                        $this->watermark($this->attachtype, $rfn, $extension, '');
                                                                }
                                                        }
                                                        unset($tmp, $dimensions, $dimension, $mwh, $rfn, $fileinfo);
                                                }
                                        }
                                }
                                $removed = false;
                                $valid_width = $valid_height = $valid_minwidth = $valid_minheight = true; // benefit of doubt
                                if (in_array($this->filetype, $this->mimetypes)) { // uploaded file is an image
                                        if (!$fileinfo = getimagesize($newfilename)) {
                                                $valid_width = $valid_height = $valid_minwidth = $valid_minheight = false;
                                                if (is_dir($foldername)) { // remove folder hash if exists
                                                        $this->recursive_remove_directory($foldername);
                                                        $removed = true;
                                                }
                                        }
                                }
                                if ($this->attachtype == 'itemphoto' and isset($fileinfo[0]) and isset($fileinfo[1])) {
                                        if ($this->sheel->config['minwidth'] > 0) { // min width requirement
                                                if ($fileinfo[0] < $this->sheel->config['minwidth']) {
                                                        $valid_minwidth = false;
                                                }
                                        }
                                        if ($this->sheel->config['minheight'] > 0) { // min height requirement
                                                if ($fileinfo[1] < $this->sheel->config['minheight']) {
                                                        $valid_minheight = false;
                                                }
                                        }
                                }
                                if ($this->filesize > $this->max_file_size) { // ensure the filesize of uploaded image is still lower than our acceptable uploaded file size defined by admin..
                                        $valid_filesize = false;
                                        if (is_dir($foldername) and !$removed) { // remove folder hash if not already done so
                                                $this->recursive_remove_directory($foldername);
                                        }
                                }
                                if ($valid_filesize and $valid_width and $valid_height and $valid_minwidth and $valid_minheight) { // everything is good
                                        return array(
                                                'success' => '1',
                                                'failedextension' => '0',
                                                'failedfilesize' => '0',
                                                'failedwidth' => '0',
                                                'failedheight' => '0',
                                                'failedminwidth' => '0',
                                                'failedminheight' => '0',
                                                'uploadwidth' => ((isset($fileinfo[0])) ? $fileinfo[0] : '0'),
                                                'uploadheight' => ((isset($fileinfo[1])) ? $fileinfo[1] : '0'),
                                                'uploadfilesize' => $this->filesize,
                                                'uploadfiletype' => $this->filetype,
                                                'uploadfilename' => $this->file_name_original,
                                                'uploadfiletype_original' => $this->filetype_original,
                                                'filehash' => $filehash,
                                                'picturehash' => $this->picturehash,
                                                'newfilename' => $newfilename
                                        );
                                } else { // something is wrong
                                        $failedfilesize = $failedwidth = $failedheight = $failedminwidth = $failedminheight = '0';
                                        if ($valid_filesize == false) {
                                                $failedfilesize = '1';
                                        }
                                        if ($valid_width == false) {
                                                $failedwidth = '1';
                                        }
                                        if ($valid_height == false) {
                                                $failedheight = '1';
                                        }
                                        if ($valid_minwidth == false) {
                                                $failedminwidth = '1';
                                        }
                                        if ($valid_minheight == false) {
                                                $failedminheight = '1';
                                        }
                                        return array(
                                                'success' => '0',
                                                'failedfilesize' => $failedfilesize,
                                                'failedextension' => '0',
                                                'failedwidth' => $failedwidth,
                                                'failedheight' => $failedheight,
                                                'failedminwidth' => $failedminwidth,
                                                'failedminheight' => $failedminheight,
                                                'uploadwidth' => ((isset($fileinfo[0])) ? $fileinfo[0] : '0'),
                                                'uploadheight' => ((isset($fileinfo[1])) ? $fileinfo[1] : '0'),
                                                'uploadfilesize' => $this->filesize,
                                                'uploadfiletype' => $this->filetype,
                                                'uploadfilename' => $this->file_name_original,
                                                'uploadfiletype_original' => '',
                                                'filehash' => $filehash,
                                                'picturehash' => $this->picturehash
                                        );
                                }
                        } else { // invalid extension
                                return array(
                                        'success' => '0',
                                        'failedfilesize' => '0',
                                        'failedextension' => '1',
                                        'failedwidth' => '0',
                                        'failedheight' => '0',
                                        'failedminwidth' => '0',
                                        'failedminheight' => '0',
                                        'uploadwidth' => '0',
                                        'uploadheight' => '0',
                                        'uploadfilesize' => $this->filesize,
                                        'uploadfiletype' => $this->filetype,
                                        'uploadfilename' => $this->file_name_original,
                                        'uploadfiletype_original' => $this->filetype_original,
                                        'filehash' => $filehash,
                                        'picturehash' => $this->picturehash
                                );
                        }
                } else { // nothing to do
                        return array(
                                'success' => '0',
                                'failedfilesize' => '1',
                                'failedextension' => '0',
                                'failedwidth' => '0',
                                'failedheight' => '0',
                                'failedminwidth' => '0',
                                'failedminheight' => '0',
                                'uploadwidth' => '0',
                                'uploadheight' => '0',
                                'uploadfilesize' => '0',
                                'uploadfiletype' => $this->filetype,
                                'uploadfilename' => $this->file_name_original,
                                'uploadfiletype_original' => '',
                                'filehash' => $filehash,
                                'picturehash' => $this->picturehash
                        );
                }
        }
        /**
         * Function to save the uploaded file attachment to the file system or database
         *
         * @return      boolean      true or false based on successful attachment upload
         */
        function save_attachment($valid_size = array())
        {
                $upload_dir = $this->get_upload_directory();


                if ($upload_dir == 'ERROR' or $valid_size['success'] == '0' or $this->validate_extension() == false) {
                        return false;
                }
                $this->uncrypted['user_id'] = isset($this->uncrypted['user_id']) ? $this->uncrypted['user_id'] : $this->user_id;
                $this->uncrypted['attachtype'] = isset($this->uncrypted['attachtype']) ? $this->uncrypted['attachtype'] : $this->attachtype;
                $valid_size['uploadfilename'] = trim($valid_size['uploadfilename']);


                $this->uncrypted['project_id'] = isset($this->uncrypted['project_id']) ? $this->uncrypted['project_id'] : $this->project_id;
                $this->uncrypted['pmb_id'] = isset($this->uncrypted['pmb_id']) ? $this->uncrypted['pmb_id'] : 0;
                $this->uncrypted['category_id'] = isset($this->uncrypted['category_id']) ? (int) $this->uncrypted['category_id'] : $this->category_id;
                $this->uncrypted['user_sort'] = isset($this->uncrypted['user_sort']) ? (int) $this->uncrypted['user_sort'] : 0;

                if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['isadmin'] == '1' and defined('LOCATION') and LOCATION == 'admin') { // is admin uploading or managing auction attachments via admincp?


                        $this->sheel->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, user_sort, project_id, pmb_id, category_id, date, folder, filename, filetype, filetype_original, visible, counter, filesize, width, height, filehash, picturehash, ipaddress, exifdata, watermarked)
                                VALUES(
                                NULL,
                                '" . $this->sheel->db->escape_string($this->uncrypted['attachtype']) . "',
                                '" . intval($this->uncrypted['user_id']) . "',
                                '" . intval($this->uncrypted['user_sort']) . "',
                                '" . intval($this->uncrypted['project_id']) . "',
                                '" . intval($this->uncrypted['pmb_id']) . "',
                                '" . intval($this->uncrypted['category_id']) . "',
                                '" . DATETIME24H . "',
                                '" . $this->sheel->db->escape_string(date('Y') . '/' . date('m') . '/' . date('d')) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfilename']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfiletype']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfiletype_original']) . "',
                                '" . intval($this->sheel->config['attachment_moderationdisabled']) . "',
                                '0',
                                '" . intval($valid_size['uploadfilesize']) . "',
                                '" . intval($valid_size['uploadwidth']) . "',
                                '" . intval($valid_size['uploadheight']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['filehash']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['picturehash']) . "',
                                '" . $this->sheel->db->escape_string(IPADDRESS) . "',
                                '" . $this->sheel->db->escape_string($this->exif) . "',
                                '" . intval($this->watermarked) . "')
                        ", 0, null, __FILE__, __LINE__);
                        $newattachid = $this->sheel->db->insert_id();

                } else { // regular user uploading attachment


                        $this->sheel->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, user_sort, project_id, pmb_id, category_id, date, folder, filename, filetype, filetype_original, visible, counter, filesize, width, height, filehash, picturehash, ipaddress, exifdata, watermarked)
                                VALUES(
                                NULL,
                                '" . $this->sheel->db->escape_string($this->uncrypted['attachtype']) . "',
                                '" . intval($this->uncrypted['user_id']) . "',
                                '" . intval($this->uncrypted['user_sort']) . "',
                                '" . intval($this->uncrypted['project_id']) . "',
                                '" . intval($this->uncrypted['pmb_id']) . "',
                                '" . intval($this->uncrypted['category_id']) . "',
                                '" . DATETIME24H . "',
                                '" . $this->sheel->db->escape_string(date('Y') . '/' . date('m') . '/' . date('d')) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfilename']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfiletype']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['uploadfiletype_original']) . "',
                                '" . intval($this->sheel->config['attachment_moderationdisabled']) . "',
                                '0',
                                '" . intval($valid_size['uploadfilesize']) . "',
                                '" . intval($valid_size['uploadwidth']) . "',
                                '" . intval($valid_size['uploadheight']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['filehash']) . "',
                                '" . $this->sheel->db->escape_string($valid_size['picturehash']) . "',
                                '" . $this->sheel->db->escape_string(IPADDRESS) . "',
                                '" . $this->sheel->db->escape_string($this->exif) . "',
                                '" . intval($this->watermarked) . "')
                        ", 0, null, __FILE__, __LINE__);
                        $newattachid = $this->sheel->db->insert_id();
                        if ($this->sheel->config['attachment_moderationdisabled'] == 0 and $this->uncrypted['attachtype'] == 'itemphoto') {
                                $this->sheel->email->mail = SITE_CONTACT;
                                $this->sheel->email->slng = $this->sheel->language->fetch_site_slng();
                                $this->sheel->email->get('attachment_moderation_mail');
                                $this->sheel->email->set(
                                        array(
                                                '{{ownername}}' => 'Admin',
                                                '{{provider}}' => $this->sheel->fetch_user('username', $this->uncrypted['user_id']),
                                                '{{project_title}}' => $this->sheel->auction->fetch_auction('project_title', intval($this->uncrypted['project_id'])),
                                                '{{attachment}}' => HTTP_ATTACHMENTS . 'auctions/' . $valid_size['filehash'] . '/' . $valid_size['uploadfilename'] . ' (' . $this->sheel->print_string_wrap(o($valid_size['uploadfilename'])) . ')',
                                                '{{p_id}}' => $this->uncrypted['project_id'],
                                                '{{url}}' => HTTPS_SERVER,
                                                '{{type}}' => $valid_size['uploadfiletype'],
                                        )
                                );
                                $this->sheel->email->send();
                        }

                }
                switch ($this->uncrypted['attachtype']) {
                        case 'itemphoto': {
                                        $itemphotocount = $this->fetch_listing_photo_count($this->uncrypted['project_id']);
                                        if ($itemphotocount <= 0) {
                                                $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasimage = '0', hasimageslideshow = '0'
                                                WHERE project_id = '" . intval($this->uncrypted['project_id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        } else if ($itemphotocount == 1) {
                                                $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasimage = '1', hasimageslideshow = '0'
                                                WHERE project_id = '" . intval($this->uncrypted['project_id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        } else if ($itemphotocount > 1) {
                                                $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasimage = '1', hasimageslideshow = '1'
                                                WHERE project_id = '" . intval($this->uncrypted['project_id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        }
                                        break;
                                }
                        case 'digital': {
                                        $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET hasdigitalfile = '1'
                                        WHERE project_id = '" . intval($this->uncrypted['project_id']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                        break;
                                }
                }

                $this->reorder_listing_photos($this->uncrypted['project_id']);


                return true;
        }
        function reorder_listing_photos($listingid = 0)
        {
                $sql = $this->sheel->db->query("
                        SELECT attachid
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id = '" . intval($listingid) . "'
                        ORDER BY attachid ASC
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $i = 1;
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $this->sheel->db->query("
                                        UPDATE " . DB_PREFIX . "attachment
                                        SET user_sort = '" . intval($i) . "'
                                        WHERE project_id = '" . intval($listingid) . "'
                                                AND attachid = '" . $res['attachid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $i++;
                        }
                }
        }
        /**
         * Function to count the number of uploaded photos to any given listing id
         *
         * @param       integer      listing id
         *
         * @return      integer      Returns the number (count) of photos found
         */
        function fetch_listing_photo_count($listingid = 0)
        {
                $count = 0;
                $sql = $this->sheel->db->query("
                        SELECT attachtype
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id = '" . intval($listingid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                if ($res['attachtype'] == 'itemphoto') {
                                        $count++;
                                }
                        }
                }
                return $count;
        }
        /**
         * Function to remove a file attachment from the system for a specified user
         *
         * @param       integer      attachment id
         * @param       integer      user id (optional)
         *
         * @return      boolean      Returns true or false if attachment was deleted
         */
        function remove_attachment($attachid = 0, $userid = 0)
        {
                $sqluserid = '';
                if ($userid > 0) {
                        $sqluserid = "AND user_id = '" . intval($userid) . "'";
                }
                $sql = $this->sheel->db->query("
                        SELECT attachtype, filesize, tblfolder_ref, project_id, eventid, filehash
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachid = '" . intval($attachid) . "'
                        $sqluserid
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);


                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "attachment
                                WHERE attachid = '" . intval($attachid) . "'
                                $sqluserid
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "attachment_color
                                WHERE attachid = '" . intval($attachid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        switch ($res['attachtype']) {
                                case 'itemphoto': {
                                                $itemphotocount = $this->fetch_listing_photo_count($res['project_id']);
                                                if ($itemphotocount <= 0) {
                                                        $this->sheel->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '0',
							hasimageslideshow = '0'
                                                        WHERE project_id = '" . intval($res['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                } else if ($itemphotocount == 1) {
                                                        $this->sheel->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '1',
							hasimageslideshow = '0'
                                                        WHERE project_id = '" . intval($res['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                } else if ($itemphotocount > 1) {
                                                        $this->sheel->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '1',
							hasimageslideshow = '1'
                                                        WHERE project_id = '" . intval($res['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                }
                                                break;
                                        }
                                case 'digital': {
                                                $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasdigitalfile = '0'
                                                WHERE project_id = '" . intval($res['project_id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                                break;
                                        }
                                case 'eventphoto': {
                                                $this->sheel->db->query("
                                                UPDATE " . DB_PREFIX . "events
                                                SET attachid = '0'
                                                WHERE eventid = '" . intval($res['eventid']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                                break;
                                        }
                        }


                        // remove physical file from the filesystem
                        $attachpath = $this->sheel->attachment_tools->fetch_attachment_path($res['attachtype']);
                        $foldername = $attachpath . $res['filehash'] . '/';
                        unset($attachpath);
                        if (is_dir($foldername)) { // remove folder hash if exists (so we can re-create new content)
                                $this->recursive_remove_directory($foldername);
                        }

                        return true;
                }
                return false;
        }
        /**
         * Function to create a ImageCreateBMP equiv for GD
         *
         * @param       string       image source location + name (ie: /home/images/image.jpg)
         * @param       boolean
         *
         * @return      boolean      Returns true or false if the bmp to be converted
         */
        function imagecreatebmp2gd($src, $dest = false)
        {
                if (!($src_f = fopen($src, "rb"))) {
                        return false;
                }
                if (!($dest_f = fopen($dest, "wb"))) {
                        return false;
                }
                $header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f, 14));
                $info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));
                extract($info);
                extract($header);
                if ($type != 0x4D42) {
                        return false;
                }
                $palette_size = $offset - 54;
                $ncolor = $palette_size / 4;
                $gd_header = "";
                $gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
                $gd_header .= pack("n2", $width, $height);
                $gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
                if ($palette_size) {
                        $gd_header .= pack("n", $ncolor);
                }
                // no transparency
                $gd_header .= "\xFF\xFF\xFF\xFF";
                fwrite($dest_f, $gd_header);
                if ($palette_size) {
                        $palette = fread($src_f, $palette_size);
                        $gd_palette = "";
                        $j = 0;
                        while ($j < $palette_size) {
                                $b = $palette { $j++};
                                $g = $palette { $j++};
                                $r = $palette { $j++};
                                $a = $palette { $j++};
                                $gd_palette .= "$r$g$b$a";
                        }
                        $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
                        fwrite($dest_f, $gd_palette);
                }
                $scan_line_size = (($bits * $width) + 7) >> 3;
                $scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;
                for ($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
                        fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
                        $scan_line = fread($src_f, $scan_line_size);
                        if ($bits == 24) {
                                $gd_scan_line = "";
                                $j = 0;
                                while ($j < $scan_line_size) {
                                        $b = $scan_line { $j++};
                                        $g = $scan_line { $j++};
                                        $r = $scan_line { $j++};
                                        $gd_scan_line .= "\x00$r$g$b";
                                }
                        } else if ($bits == 8) {
                                $gd_scan_line = $scan_line;
                        } else if ($bits == 4) {
                                $gd_scan_line = "";
                                $j = 0;
                                while ($j < $scan_line_size) {
                                        $byte = ord($scan_line { $j++});
                                        $p1 = chr($byte >> 4);
                                        $p2 = chr($byte & 0x0F);
                                        $gd_scan_line .= "$p1$p2";
                                }
                                $gd_scan_line = substr($gd_scan_line, 0, $width);
                        } else if ($bits == 1) {
                                $gd_scan_line = "";
                                $j = 0;
                                while ($j < $scan_line_size) {
                                        $byte = ord($scan_line { $j++});
                                        $p1 = chr((int) (($byte & 0x80) != 0));
                                        $p2 = chr((int) (($byte & 0x40) != 0));
                                        $p3 = chr((int) (($byte & 0x20) != 0));
                                        $p4 = chr((int) (($byte & 0x10) != 0));
                                        $p5 = chr((int) (($byte & 0x08) != 0));
                                        $p6 = chr((int) (($byte & 0x04) != 0));
                                        $p7 = chr((int) (($byte & 0x02) != 0));
                                        $p8 = chr((int) (($byte & 0x01) != 0));
                                        $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
                                }
                                $gd_scan_line = substr($gd_scan_line, 0, $width);
                        }
                        fwrite($dest_f, $gd_scan_line);
                }
                fclose($src_f);
                fclose($dest_f);
                return true;
        }
        /**
         * Function to to create an image from a .bmp picture
         *
         * @param       string       image source location + name (ie: /home/images/image.jpg)
         *
         * @return      mixed        Returns image resource from imagecreatefromgd() or false if cannot be completed
         */
        function imagecreatefrombmp($filename)
        {
                $tmp_name = tempnam(sys_get_temp_dir(), "GD");
                if ($this->imagecreatebmp2gd($filename, $tmp_name)) {
                        $img = imagecreatefromgd($tmp_name);
                        unlink($tmp_name);
                        return $img;
                }
                return false;
        }
        function thumbnail_box($img, $box_w, $box_h)
        {
                // create the image, of the required size
                $new = imagecreatetruecolor($box_w, $box_h);
                if ($new === false) {
                        // creation failed -- probably not enough memory
                        return null;
                }
                // fill the image with a light grey color
                // (this will be visible in the padding around the image,
                // if the aspect ratios of the image and the thumbnail do not match)
                // replace this with any color you want, or comment it out for black.
                // I used grey for testing =)
                $fill = imagecolorallocate($new, 200, 200, 205);
                imagefill($new, 0, 0, $fill);

                //  compute resize ratio
                $hratio = $box_h / imagesy($img);
                $wratio = $box_w / imagesx($img);
                $ratio = min($hratio, $wratio);

                // if the source is smaller than the thumbnail size,
                // don't resize -- add a margin instead
                // (that is, dont magnify images)
                if ($ratio > 1.0)
                        $ratio = 1.0;

                // compute sizes
                $sy = floor(imagesy($img) * $ratio);
                $sx = floor(imagesx($img) * $ratio);

                // compute margins
                // using these margins centers the image in the thumbnail.
                // if you always want the image to the top left,
                // set both of these to 0
                $m_y = floor(($box_h - $sy) / 2);
                $m_x = floor(($box_w - $sx) / 2);

                // copy the image data, and resample
                // if you want a fast and ugly thumbnail,
                // replace imagecopyresampled with imagecopyresized
                if (
                        !imagecopyresampled(
                                $new,
                                $img,
                                $m_x,
                                $m_y,
                                //dest x, y (margins)
                                0,
                                0,
                                //src x, y (0,0 means top left)
                                $sx,
                                $sy, //dest w, h (resample to this size (computed above)
                                imagesx($img),
                                imagesy($img)
                        ) //src w, h (the full size of the original)
                ) {
                        imagedestroy($new);
                        return null;
                }
                return $new;
        }
        function imagecreatefromtiff($filename = '')
        {
                $tmp_name = sys_get_temp_dir() . '/IM.jpg';
                exec(escapeshellcmd("/usr/bin/convert $filename $tmp_name"), $output);
                if (file_exists($tmp_name)) {
                        $img = imagecreatefromjpeg($tmp_name);
                        unlink($tmp_name);
                        return $img;
                }
                return false;
        }
        function fetch_orientation_degree($exif = array())
        {
                $orientation = 1;
                if (isset($exif['Orientation'])) {
                        $orientation = $exif['Orientation'];
                } else if (isset($exif['COMPUTED']['Orientation'])) {
                        $orientation = $exif['COMPUTED']['Orientation'];
                } else if (isset($exif['IFD0']['Orientation'])) {
                        $orientation = $exif['IFD0']['Orientation'];
                }
                if ($orientation != 1) {
                        $deg = 0;
                        switch ($orientation) {
                                case 3: {
                                                $deg = 180;
                                                break;
                                        }
                                case 6: {
                                                $deg = -90;
                                                break;
                                        }
                                case 8: {
                                                $deg = 270;
                                                break;
                                        }
                        }
                        return $deg;
                }
                return 0;
        }
        /**
         * Function to resize an uploaded picture by keeping it's aspect ratio based on the max width and height defined by the admin within the Attachment Manager of the Admin CP.
         *
         * @param       string      source file
         * @param       integer     max width
         * @param       integer     max height
         * @param       string      file extension of original image
         * @param       integer     width from original image getimagesize()
         * @param       integer     height from original image getimagesize()
         * @param       string      destination source file (default blank)
         * @param       integer     resized image quality (default 100)
         * @param       integer     rotatation degree (default 0, no rotate)
         *
         * @return      boolean     Returns true or false and sets $this->file_name, $this->filetype, $this->width, $this->height, $this->pictureresized
         */
        function picture_resizer($src, $maxwidth, $maxheight, $extension, $picturewidth, $pictureheight, $dsrc = '', $quality = 100, $degree = 0)
        {
                $r = 1;
                $e = strtolower(substr($extension, strrpos($extension, '.') + 1, 3)); // jpg, gif, png, web, etc.
                $m = mime_content_type($src);
                if ((($e == 'jpg' or $e == 'peg' or $e == 'jpe') and $m == 'image/jpeg') or $m == 'image/jpeg') {
                        $oldimage = imagecreatefromjpeg($src) or $r = 0;
                } else if (($e == 'gif' and $m == 'image/gif') or $m == 'image/gif') {
                        $oldimage = imagecreatefromgif($src) or $r = 0;
                } else if (($e == 'bmp' and $m == 'image/bmp') or $m == 'image/bmp') {
                        $oldimage = $this->imagecreatefrombmp($src) or $r = 0;
                } else if (($e == 'png' and $m == 'image/png') or $m == 'image/png') {
                        $oldimage = imagecreatefrompng($src) or $r = 0;
                } else if (($e == 'tif' or $e == 'iff' or $e == 'pdf') or $m == 'image/tiff') {
                        $oldimage = $this->imagecreatefromtiff($src) or $r = 0;
                } else if ($e == 'web' or $m == 'image/webp') {
                        $oldimage = imagecreatefromwebp($src) or $r = 0;
                } else {
                        $r = 0;
                }
                if ($r > 0) {
                        if ($degree != 0) { // picture orientation determined
                                imagesetinterpolation($oldimage, IMG_BELL);
                                $oldimage = imagerotate($oldimage, $degree, 0);
                                $w = $pictureheight;
                                $h = $picturewidth;
                                $picturewidth = $w;
                                $pictureheight = $h;
                                unset($w, $h);
                        }
                        $newthumb = imagecreatetruecolor($maxwidth, $maxheight);
                        $bgcolor = imagecolorallocate($newthumb, 255, 255, 255);
                        imagefill($newthumb, 0, 0, $bgcolor);
                        // compute resize ratios
                        $hratio = $maxheight / $pictureheight;
                        $wratio = $maxwidth / $picturewidth;
                        $ratio = min($hratio, $wratio);
                        if ($ratio > 1.0) { // if source is smaller than thumbnail don't resize -- add a margin instead
                                $ratio = 1.0;
                        }
                        // compute sizes
                        $sy = floor($pictureheight * $ratio);
                        $sx = floor($picturewidth * $ratio);
                        // compute margins to center the image in the thumbnail.
                        $m_y = floor(($maxheight - $sy) / 2);
                        $m_x = floor(($maxwidth - $sx) / 2);
                        // copy the image data, and resample
                        imagecopyresampled($newthumb, $oldimage, $m_x, $m_y, 0, 0, $sx, $sy, $picturewidth, $pictureheight);
                        $newname = substr($this->file_name, 0, -4) . '.jpg';
                        $this->file_name = $newname;
                        $this->filetype = 'image/jpeg';
                        $this->width = $maxwidth;
                        $this->height = $maxheight;
                        if (!empty($dsrc)) {
                                imagejpeg($newthumb, $dsrc, $quality);
                        } else {
                                imagejpeg($newthumb, $src, $quality);
                        }
                        $this->pictureresized = true;
                        imagedestroy($newthumb);
                        imagedestroy($oldimage);
                        return true;
                } else {
                        $this->pictureresized = false;
                }
                return false;
        }
        /**
         * Function to print the file's extension icon
         *
         * @param       string      filename
         *
         * @return      string      Returns HTML formatted img srg tag icon
         */
        function print_file_extension_icon($filename)
        {
                $attachextension = $this->fetch_extension($filename) . '.gif';
                if (file_exists(DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_IMAGES_NAME . '/mime/' . $attachextension)) {
                        $attachextension = $this->fetch_extension($filename) . '.gif';
                } else {
                        $attachextension = 'attach.gif';
                }
                return $this->sheel->config['imgcdn'] . 'mime/' . $attachextension;
        }
        /*
         * Function to print out an attachment gauge based on a supplied user id
         *
         * @param        integer         user id
         *
         * @return       string          Returns HTML formatted bar of attachment usage
         */
        function print_spaceleft_gauge($userid = 0, $sheel = false)
        {
                if ($sheel) {
                        $this->sheel->show['pleaseupgrade'] = false;
                        $limit = $this->sheel->permissions->check_access($userid, 'attachlimit');
                        $total = 0;
                        $sql = $this->sheel->db->query("
			SELECT SUM(filesize) AS attach_usage_total
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $total = $res['attach_usage_total']; //$this->print_filesize($res['attach_usage_total']);
                        }
                        if (!is_numeric($limit)) {
                                $limit = $total;
                                $this->sheel->show['pleaseupgrade'] = true;
                        }
                        if ($limit == 0 and $total == 0) {
                                $percentage_used = 0;
                        } else {
                                $percentage_used = round(($total / $limit) * 100);
                        }
                        $this->sheel->show['pleaseupgrade'] = (($percentage_used >= 100) ? true : false);
                        $percentage_left = (100 - $percentage_used);
                        $html = '<div class="progress progress-margin"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="' . $percentage_used . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percentage_used . '">' . $percentage_used . '%</div></div><p>{_storage}: ' . $this->print_filesize($total) . ' {_of} ' . $this->print_filesize($limit) . '.</p>';
                } else {
                        $this->sheel->show['pleaseupgrade'] = false;
                        $limit = $this->sheel->permissions->check_access($userid, 'attachlimit');
                        $total = 0;
                        $sql = $this->sheel->db->query("
			SELECT SUM(filesize) AS attach_usage_total
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
                        if ($this->sheel->db->num_rows($sql) > 0) {
                                $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
                                $total = $res['attach_usage_total']; //$this->print_filesize($res['attach_usage_total']);
                        }
                        if (!is_numeric($limit)) {
                                $limit = $total;
                                $this->sheel->show['pleaseupgrade'] = true;
                        }
                        if ($limit == 0 and $total == 0) {
                                $percentage_used = 0;
                        } else {
                                $percentage_used = round(($total / $limit) * 100);
                        }
                        $this->sheel->show['pleaseupgrade'] = (($percentage_used >= 100) ? true : false);
                        $percentage_left = (100 - $percentage_used);
                        $html = '<div class="pt-4"><div class="spent-progress" style="width:100%"><div class="smaller right fs-9 pr-4">' . $percentage_used . '% {_used}</div><div class="spent-progress-bar" style="width:' . (($percentage_used > 100) ? 100 : $percentage_used) . '%"></div></div></div><div class="clear"></div><div class="smaller litegray pt-6 lh-14">{_storage}: ' . $this->print_filesize($total) . ' {_of} ' . $this->print_filesize($limit) . '.</div>';
                }

                return $html;
        }
        /**
         * Function to print the attachments filelist for a particular user or listing
         *
         * @param       integer        user id
         * @param       integer        project id
         * @param       integer        pmb event id
         * @param       string         attachment type (default is 'itemphoto')
         * @param       boolean        print <img> tag (default false, show only filename)
         * @param       string         order id (if applicable)
         *
         * @return      string         Returns the file list
         */
        function fetch_inline_attachment_filelist($userid = 0, $projectid = 0, $pmbeventid = 0, $attachtype = 'itemphoto', $printimage = false, $orderidpublic = '')
        {
                $html = '';
                $query = "AND project_id = '" . intval($projectid) . "'";
                if (!empty($orderidpublic)) {
                        $query = "AND orderidpublic = '" . $this->sheel->db->escape_string($orderidpublic) . "'";
                }
                $sql = $this->sheel->db->query("
                        SELECT attachid, visible, filename, filesize, filehash, user_id
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachtype = '" . $this->sheel->db->escape_string($attachtype) . "'
                                $query
                                AND pmb_id = '" . intval($pmbeventid) . "'
                                " . (($userid > 0) ? "AND user_id = '" . intval($userid) . "'" : '') . "
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        $html = (($attachtype == 'digital') ? '<ul class="skill-tags">' : '<ul class="attach-filelist">');
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $moderated = '';
                                if ($attachtype == 'digital') {
                                        if ($res['visible'] == 0) {
                                                $moderated = ' ({_review_in_progress})';
                                        }
                                        $displayable = mb_split(', ', $this->sheel->config['attachmentlimit_productphotoextensions']);
                                        $ext = pathinfo($res['filename'], PATHINFO_EXTENSION);
                                        if (in_array('.' . $ext, $displayable)) { // image
                                                $attachment_link = '<div class="vam dtc"><img src="' . HTTP_ATTACHMENTS . $this->sheel->attachment_tools->fetch_physical_folder_name($attachtype) . '/' . $res['filehash'] . '/62x62.jpg" border="0" alt="file" /></div>';
                                        } else { // non image
                                                $attachment_link = o($res['filename']) . ' <span class="fr plr-6"><a href="javascript:;" onclick="delete_digital_upload(' . $res['attachid'] . ', \'digital_attachmentlist\')"><img src="' . $this->sheel->config['imgcdn'] . 'v5/ico_trash.png" alt="del" width="16" /></a></span>';
                                        }
                                } else {
                                        if ($res['visible'] == 0) {
                                                $moderated = ' ({_review_in_progress})';
                                                $attachment_link = o($res['filename']);
                                        } else {
                                                if ($printimage) {
                                                        $attachment_link = '<img src="' . HTTP_ATTACHMENTS . $this->sheel->attachment_tools->fetch_physical_folder_name($attachtype) . '/' . $res['filehash'] . '/60x60.jpg" border="0" alt="" id="" />';
                                                } else {
                                                        $attachment_link = '<span class="blue"><a href="' . HTTP_ATTACHMENTS . $this->sheel->attachment_tools->fetch_physical_folder_name($attachtype) . '/' . $res['filehash'] . '/' . $res['filename'] . '" target="_blank">' . o($res['filename']) . '</a></span> <span class="smaller litegray">(' . $this->sheel->fetch_user('username', $res['user_id']) . ')</span>';
                                                }
                                        }
                                }
                                $html .= (($attachtype == 'digital') ? '<li class="skill-tag" title="' . o($res['filename']) . ' (' . $this->print_filesize($res['filesize']) . $moderated . '">' . $attachment_link . '</li>' : '<li style="padding:6px 0 6px 0"><div title="' . o($res['filename']) . ' (' . $this->print_filesize($res['filesize']) . ')' . $moderated . '">' . $attachment_link . '</div></li>');
                        }
                        $html .= '</ul>';
                }
                return $html;
        }
        function recursive_remove_directory($directory = '')
        {
                if (empty($directory) or $directory == '' or $directory == '/' or $directory == '//' or $directory == '/root' or $directory == '/root/' or $directory == '/home' or $directory == '/home/') {
                        return false;
                }
                if (!file_exists($directory)) {
                        return true;
                }
                if (!is_dir($directory)) {
                        return unlink($directory);
                }
                foreach (scandir($directory) as $item) {
                        if ($item == '.' or $item == '..') {
                                continue;
                        }
                        if (!$this->recursive_remove_directory($directory . DIRECTORY_SEPARATOR . $item)) {
                                return false;
                        }
                }
                return rmdir($directory);
        }
        /**
         * Copy a file, or recursively copy a folder and its contents
         *
         * @param       string   $source    Source path
         * @param       string   $dest      Destination path
         * @param       string   $permissions New folder creation permissions
         * @return      bool     Returns true on success, false on failure
         */
        function recursive_copy_directory($source = '', $dest = '', $permissions = 0755)
        {
                if (is_file($source)) {
                        return copy($source, $dest);
                }
                if (!is_dir($dest)) {
                        $oldumask = umask(0);
                        mkdir($dest, $permissions);
                        umask($oldumask);
                }
                $dir = dir($source);
                while (false !== $entry = $dir->read()) {
                        if ($entry == '.' || $entry == '..') {
                                continue;
                        }
                        $this->recursive_copy_directory("$source/$entry", "$dest/$entry", $permissions);
                }
                $dir->close();
                return true;
        }
        /**
         * Function to fetch the extension of a filename being passed as the argument
         *
         * @param        string        filename including the file extension
         *
         * @return	string        Returns the file extension (ie: gif) without the period
         */
        function fetch_extension($filename = '')
        {
                $dot = mb_substr(mb_strrchr($filename, '.'), 1);
                return $dot;
        }
        /**
         * Function to print a human-readable filesize based on bytes being sent as an argument
         *
         * @param        integer     size in bytes
         *
         * @return	string      Returns formatted filesize like 1.3KB, 2.5MB, 1.7GB, etc
         */
        function print_filesize($bytes = 0)
        {
                if ($bytes < 0) {
                        $format = '0.1 KB';
                } else if (mb_strlen($bytes) <= 9 and mb_strlen($bytes) >= 7) {
                        $format = number_format($bytes / 1048576, 1) . ' MB';
                } else if (mb_strlen($bytes) >= 10) {
                        $format = number_format($bytes / 1073741824, 1) . ' GB';
                } else {
                        $format = number_format($bytes / 1024, 1) . ' KB';
                }
                return $format;
        }
        function remove_unlinked_item_attachments()
        {
                $sql = $this->sheel->db->query("
                        SELECT attachid, project_id, user_id
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id > 0
                        GROUP BY project_id
                ", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0) {
                        while ($res = $this->sheel->db->fetch_array($sql, DB_ASSOC)) {
                                $sql2 = $this->sheel->db->query("
                                        SELECT id
                                        FROM " . DB_PREFIX . "projects
                                        WHERE project_id = '" . $res['project_id'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($this->sheel->db->num_rows($sql2) <= 0) {
                                        if ($res['user_id'] > 0) {
                                                $this->remove_attachment($res['attachid'], $res['user_id']);
                                        } else {
                                                $this->remove_attachment($res['attachid']);
                                        }
                                }
                        }
                }
                return 'attachment->remove_unlinked_item_attachments(), ';
        }
}
?>