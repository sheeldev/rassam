<?php
define('LOCATION', 'javascript');
require_once(dirname(__FILE__, 3) . '/config.php');
$expires = 60 * 60 * 24 * 7;
$html = '';
$computed_scripts = [];

header('Pragma: public');
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . date('D, d M Y H:i:s', time() + $expires) . ' GMT');
header('Content-type: application/x-javascript');
$js = array();
if (isset($_REQUEST['dojs']) AND !empty($_REQUEST['dojs']))
{
    $js = explode(' ', $_REQUEST['dojs']);
    
    if (isset($js) AND is_array($js) AND count($js) > 0)
    {
        foreach ($js AS $jsfile)
        {
            if (!empty($jsfile))
            {
                if (stripos(strtolower($jsfile), 'vendor/') !== false)
                { // load vendor related library                 
                    $tmp = explode('/', $jsfile);
                    if ($sheel->config['globalfilters_jsminify'] AND file_exists(DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.min.js'))
                    {
                        $html .= file_get_contents(DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.min.js') . LINEBREAK;
                        $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.min.js';
                    }
                    else if (file_exists(DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.js'))
                    {
                        $html .= file_get_contents(DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.js') . LINEBREAK;
                        $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . '/' . $tmp[1] . '.js';
                    }
                }
                else
                { // load library
                    switch ($jsfile)
                    {
                        case 'functions':
                            {
                                $file = DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js');
                                if (file_exists($file . 'x'))
                                {
                                    $html .= file_get_contents($file . 'x') . LINEBREAK;
                                    $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js' . 'x');
                                }
                                else
                                {
                                    $html .= file_get_contents($file) . LINEBREAK;
                                    $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js');
                                }
                                break;
                            }
                        default:
                            {
                                $file = DIR_FUNCTIONS . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/functions_' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js');
                                if (file_exists($file . 'x'))
                                {
                                    $html .= file_get_contents($file . 'x') . LINEBREAK;
                                    $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/functions_' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js' . 'x');
                                }
                                else
                                {
                                    $html .= file_get_contents($file) . LINEBREAK;
                                    $computed_scripts[] = '/' . DIR_APPLICATION_NAME . '/' . DIR_ASSETS_NAME . '/' . DIR_JS_NAME . '/functions_' . $jsfile . (($sheel->config['globalfilters_jsminify']) ? '.min.js' : '.js');
                                }
                                break;
                            }
                    }
                }
            }
        }
    }
}


//if (!empty($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] > 0 AND $_SESSION['sheeldata']['user']['isadmin'] == '1')
    //{
    //	echo 'test: ' . $_SESSION['sheeldata']['user']['isadmin'];
    //}
        
    // Uncomment to enable JS Cache
    //if (!empty($html))
        //{
    //	echo $html;
    //	die();
    //}
    
    // Comment to enable JS Cache
    if(!empty($computed_scripts))
    {
        $js_code = '';
        foreach ($computed_scripts as $js)
        {
            $js_code .= '<script src="' .$js. '"></script>' . LINEBREAK;
        }
        
        echo $js_code;
        die();
    }
    ?>
