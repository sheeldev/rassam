<?php
/**
 * Security class to perform the majority of security handling functions within sheel
 *
 * @package      sheel\Security
 * @version      1.0.0.0
 * @author       sheel
 */
class security
{
        function __construct()
        {
        }
        /**
         * Function to read, build and create a new php script based on md5 file hash of all files for the sheel product.  Ultimately we'll use this to allow the user
         * to check their original files for any changes since last download
         */
        function build_md5_filelist()
        {
                $filelist = $this->read_my_dir(mb_substr(DIR_SERVER_ROOT, 0, -1));
                $files_a = explode("\n", $filelist);
                foreach ($files_a as $key => $filename) {
                        $filename = trim($filename);
                        if ($filename == "") {
                                unset($files_a[$key]);
                        } else {
                                $files_a[$key] = (mb_substr($filename, 0, 1) == "/" ? $filename : "/" . $filename);
                        }
                }
                sort($files_a);
                $files_a = array_unique($files_a);
                foreach ($files_a as $key => $filename) {
                        $directory = str_replace('\\', '/', dirname($filename));
                        $directory = str_replace(SITE_ROOT, '/', $directory);
                        $directory = str_replace(substr(SITE_ROOT, 0, -1), '/', $directory);
                        $productfilename = $filename;
                        $filehash = @md5_file($productfilename);
                        $file = basename($filename);
                        $md5_files[$directory][] = "'$file' => '$filehash'";
                }
                ksort($md5_files);
                if ($fp = fopen(DIR_TMP . 'checkup/filelist.php', 'wt')) {
                        // Write a header
                        fwrite($fp, "<?php\n");
                        fwrite($fp, "// #### Built on " . vdate('M, d, Y') . " at " . vdate('H:i:s') . "\n");
                        fwrite($fp, "// #### Version " . VERSION . "." . SVNVERSION . "\n\n");
                        fwrite($fp, '$sheel_md5 = array(' . "\n");
                        foreach ($md5_files as $dir => $data) {
                                fwrite($fp, "\t'$dir' => array(" . "\n");
                                sort($data);
                                foreach ($data as $key => $fileinfo) {
                                        fwrite($fp, "\t\t$fileinfo,\n");
                                }
                                fwrite($fp, "\t),\n");
                        }
                        fwrite($fp, ");\n");
                        fwrite($fp, "\n");
                        fwrite($fp, "?>");
                        fclose($fp);
                }
        }
        /**
         * Function to read and scan through an entire directory specified.
         *
         * @param        string          directory
         *
         * @return       string          Returns directory contents
         */
        function read_my_dir($dir)
        {
                $tfile = '';
                $tdir = '';
                $i = 0;
                $j = 0;
                $md5_files = array();
                $html = '';
                $myfiles[][] = array();
                if (is_dir($dir)) {
                        if ($dh = opendir($dir)) {
                                while (($file = readdir($dh)) !== false) {
                                        if (!is_dir($dir . '/' . $file) && ($file != "config.php") && ($file != "config.php.new") && ($file != "sitemap.xml") && ($file != ".notinstalled") && ($file != "certificate.cer") && ($file != "error_log") && ($file != "logo.png")) {
                                                $tfile[$i] = $file;
                                                $i++;
                                                $html .= $dir . '/' . $file . "\n";
                                        } else {
                                                if (($file != ".") && ($file != "..") && ($file != "pdf") && ($file != ".svn") && ($file != ".git") && ($file != "ckeditor") && ($file != "froala") && ($file != "cache") && ($file != "uploads") && ($file != "categoryheros") && ($file != "categoryicons") && ($file != "ads") && ($file != "categorysearch") && ($file != "categorythumbs") && ($file != "flags") && ($file != "heros") && ($file != "codemirror") && ($file != "lollipop")) {
                                                        $tdir[$j] = $file;
                                                        $html .= $this->read_my_dir($dir . '/' . $file);
                                                        $j++;
                                                }
                                        }
                                }
                                closedir($dh);
                        }
                }
                return $html;
        }
        function generate_sri_checksum($input = '')
        {
                $hash = hash('sha256', $input, true);
                $hash_base64 = base64_encode($hash);
                return "sha256-$hash_base64";
        }
}
?>