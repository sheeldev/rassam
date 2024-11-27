<?php
define('LOCATION', 'admin');
if (isset($match['params'])) {
    $sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
    'header' => array(
        'functions',
        'admin',
        'inline',
        'vendor/chartist',
        'vendor/growl'
    ),
    'footer' => array(
    )
);
$sheel->template->meta['cssinclude'] = array(
    'common',
    'vendor' => array(
        'growl',
        'font-awesome',
        'glyphicons',
        'chartist',
        'balloon',
        'growl'
    )
);

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Languages Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Languages';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }


    $areanav = 'settings_languages';
    $currentarea = 'Language Manager';

    if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'add')
    {
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
        {
            $create = true;
            if (empty($sheel->GPC['form']['lng']))
            {
                $error = '{_please_enter_a_language_name}';
                $create = false;
            }
            if (empty($sheel->GPC['form']['baselanguage']))
            {
                $error .= '{_please_select_a_base_language}';
                $create = false;
            }
            if (empty($sheel->GPC['form']['author']))
            {
                $sheel->GPC['form']['author'] = $_SESSION['sheeldata']['user']['username'];
            }
            if (empty($sheel->GPC['form']['alphabet']))
            {
                $error = '{_please_enter_proper_alphabet_from_a_to_z}';
                $create = false;
            }
            $conflicts = $sheel->db->query("
                SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
                FROM " . DB_PREFIX . "language
                WHERE (title LIKE '%" . $sheel->db->escape_string($sheel->GPC['form']['lng']) . "%' OR languagecode LIKE '%" . $sheel->db->escape_string($sheel->GPC['form']['lng']) . "%')
                LIMIT 1
            ");
            if ($sheel->db->num_rows($conflicts) > 0)
            {
                $error = '{_this_language_appears_to_be_similar_to_a_language_already_installed_operation_aborted}';
                $create = false;
            }
            if ($create == true)
            {
                $sheel->language->add_language_schema($sheel->GPC['form']);
                refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
                exit();
            }
        }
        // add new language defaults
        $form['title_pulldown'] = $sheel->construct_pulldown('lng', 'form[lng]', $sheel->language->languages, 'English', 'class="draw-select"');
        $form['base_language_pulldown'] = $sheel->language->print_language_pulldown(false, false, 'form[baselanguage]', '{_choose_base_language}', 'draw-select');
        $form['languageiso'] = 'en';
        $form['locale'] = 'en_US';
        $form['alphabet'] = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $form['textdirection_pulldown'] = $sheel->construct_pulldown('textdirection', 'form[textdirection]', array('ltr' => '{_left_to_right}', 'rtl' => '{_right_to_left}'), 'ltr', 'class="draw-select"');
        $form['characterset_pulldown'] = $sheel->construct_pulldown('charset', 'form[charset]', $sheel->language->charactersets, 'utf-8', 'class="draw-select"');

    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'update')
    {
        $sql = $sheel->db->query("
            SELECT languageid, title, charset, author, locale, textdirection, languageiso, canselect, replacements, alphabet
            FROM " . DB_PREFIX . "language
            WHERE languageid = '" . intval($sheel->GPC['languageid']) . "'
        ");
        if ($sheel->db->num_rows($sql) > 0)
        {
            if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'process')
            {
                $sheel->db->query("
                    UPDATE " . DB_PREFIX . "language
                    SET title = '" . $sheel->db->escape_string($sheel->GPC['form']['lng']) . "',
                    charset = '" . $sheel->db->escape_string($sheel->GPC['form']['charset']) . "',
                    locale = '" . $sheel->db->escape_string($sheel->GPC['form']['locale']) . "',
                    author = '" . $sheel->db->escape_string($sheel->GPC['form']['author']) . "',
                    textdirection = '" . $sheel->db->escape_string($sheel->GPC['form']['textdirection']) . "',
                    languageiso = '" . $sheel->db->escape_string($sheel->GPC['form']['languageiso']) . "',
                    canselect = '" . intval($sheel->GPC['form']['canselect']) . "',
                    replacements = '" . $sheel->db->escape_string($sheel->GPC['form']['replacements']) . "',
                    alphabet = '" . $sheel->db->escape_string(trim($sheel->GPC['form']['alphabet'])) . "'
                    WHERE languageid = '" . intval($sheel->GPC['languageid']) . "'
                    LIMIT 1
                ");
                if (isset($sheel->GPC['form']['defaultlanguage']) AND $sheel->GPC['form']['defaultlanguage'] == '1')
                {
                    $sheel->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '" . intval($sheel->GPC['languageid']) . "'
                        WHERE name = 'globalserverlanguage_defaultlanguage'
                        LIMIT 1
                    ");
                }
                refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
                exit();
            }
            $res = $sheel->db->fetch_array($sql, DB_ASSOC);
            $sheel->GPC['form']['lng'] = $res['title'];
            $sheel->GPC['form']['languageiso'] = $res['languageiso'];
            $sheel->GPC['form']['replacements'] = $res['replacements'];
            $sheel->GPC['form']['alphabet'] = $res['alphabet'];

            $form['languageid'] = intval($sheel->GPC['languageid']);
            $form['title'] = $res['title'];
            $form['id'] = $res['languageid'];
            $form['charset'] = $res['charset'];
            $form['author'] = $res['author'];
            $form['locale'] = $res['locale'];
            $form['alphabet'] = $res['alphabet'];

            $form['title_pulldown'] = $sheel->construct_pulldown('lng', 'form[lng]', $sheel->language->languages, o($form['title']), 'class="draw-select"');
            $form['languageiso'] = $sheel->GPC['form']['languageiso'];
            $form['locale'] = $form['locale'];
            $form['textdirection_pulldown'] = $sheel->construct_pulldown('textdirection', 'form[textdirection]', array('ltr' => '{_left_to_right}', 'rtl' => '{_right_to_left}'), $res['textdirection'], 'class="draw-select"');
            $form['characterset_pulldown'] = $sheel->construct_pulldown('charset', 'form[charset]', $sheel->language->charactersets, $form['charset'], 'class="draw-select"');

            if ($sheel->config['globalserverlanguage_defaultlanguage'] == $res['languageid'])
            {
                $defaultlanguage0 = '';
                $defaultlanguage1 = 'selected="selected"';
            }
            else
            {
                $defaultlanguage0 = 'selected="selected"';
                $defaultlanguage1 = '';
            }
            if ($res['textdirection'] == 'rtl')
            {
                $textdirection0 = '';
                $textdirection1 = 'selected="selected"';
            }
            else
            {
                $textdirection0 = 'selected="selected"';
                $textdirection1 = '';
            }
            if ($res['canselect'])
            {
                $canselect0 = '';
                $canselect1 = 'selected="selected"';
            }
            else
            {
                $canselect0 = 'selected="selected"';
                $canselect1 = '';
            }
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'defaultusers')
    {
        if (isset($sheel->GPC['languageid']) AND $sheel->GPC['languageid'] > 0)
        {
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "users
                SET languageid = '" . intval($sheel->GPC['languageid']) . "'
            ");
            refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
            exit();
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'default')
    {
        if (isset($sheel->GPC['languageid']) AND $sheel->GPC['languageid'] > 0)
        {
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "configuration
                SET value = '" . intval($sheel->GPC['languageid']) . "'
                WHERE name = 'globalserverlanguage_defaultlanguage'
                LIMIT 1
            ");
            refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
            exit();
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'delete')
    {

        $success = true;
        if (isset($sheel->GPC['languageid']) AND $sheel->GPC['languageid'] > 1)
        {
            $success = $sheel->language->remove_language_schema($sheel->GPC['languageid'], '1');
            if ($success)
            {
                refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
                exit();
            }
            else
            {
                $error = '{_there_was_an_error_deleting_the_selected_language_please_select_all_required_form_fields_and_retry_your_action}';
            }
        }
        else
        {
            $error = '{_there_was_an_error_deleting_the_selected_language_you_cannot_remove_language_id_1}';
        }
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'export')
    {
        $sheel->GPC['untranslated'] = (isset($sheel->GPC['untranslated'])) ? intval($sheel->GPC['untranslated']) : 0;
        $sheel->admincp_importexport->export('phrase', 'admincp', $sheel->GPC['languageid'], '', false, $sheel->GPC['untranslated']);
        exit();
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'import')
    {
        if (empty($_FILES['xml_file']['tmp_name']))
        {
            refresh(HTTPS_SERVER_ADMIN . 'settings/languages/');
            exit();
        }
        $xml = file_get_contents($_FILES['xml_file']['tmp_name']);
        $sheel->GPC['noversioncheck'] = isset($sheel->GPC['noversioncheck']) ? intval($sheel->GPC['noversioncheck']) : 0;
        $sheel->GPC['overwrite'] = isset($sheel->GPC['overwrite']) ? intval($sheel->GPC['overwrite']) : 0;
        $sheel->admincp_importexport->import('phrase', 'admincp', $xml, false, $sheel->GPC['noversioncheck'], $sheel->GPC['overwrite']);
        $sheel->language->clean_cache();
        exit();
    }
    else if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'orphan')
    {
        if (isset($sheel->GPC['do']) AND $sheel->GPC['do'] == 'deleteorphanphrases')
        {
            $orphan_phrases_text_area = ((isset($sheel->GPC['orphan_phrases_sql'])) ? $sheel->GPC['orphan_phrases_sql'] : '');
            $arr = explode("\n", $orphan_phrases_text_area);
            if (is_array($arr))
            {
                foreach ($arr AS $key => $value)
                { // verify each line has these words
                    if (!empty($value) AND strrchr($value, 'language_phrases') AND strrchr($value, 'varname =') AND strrchr($value, 'DELETE FROM'))
                    {
                        $sheel->db->query($value, 0, null, __FILE__, __LINE__);
                    }
                }
            }
        }
        $sheel->show['results'] = true;
        $orphan_phrases_text_area = $sheel->admincp_find_orphan->find_phrase(substr(SITE_ROOT, 0, -1));
        $orphan_phrases_table = "{_total_number_of_phrases_found}: <strong>" . $sheel->admincp_find_orphan->totalphrases . "</strong>, {_total_number_of_orphan_phrases_found}: <strong>" . $sheel->admincp_find_orphan->orphanphrases . "</strong>";
        if ($sheel->admincp_find_orphan->orphanphrases > 0)
        {
            $sheel->show['can_delete'] = true;
        }
        else
        {
            $sheel->show['no_results'] = true;
        }
    }
    if (isset($sheel->GPC['subcmd']) AND $sheel->GPC['subcmd'] == 'update')
    {
        $sheel->show['update'] = true;
    }
    $languageresults = $sheel->db->query("
        SELECT languageid, languagecode, title, charset, locale, author, textdirection, languageiso, canselect, installdate, lastimport
        FROM " . DB_PREFIX . "language
    ");
    if ($sheel->db->num_rows($languageresults) > 0)
    {
        while ($res = $sheel->db->fetch_array($languageresults, DB_ASSOC))
        {
            $res['actions'] = '<ul class="segmented"><li><a href="' . (($sheel->config['globalserverlanguage_defaultlanguage'] == $res['languageid']) ? 'javascript:;' : HTTPS_SERVER_ADMIN . 'settings/languages/default/' . $res['languageid'] . '/') . '" class="btn btn-slim btn--icon" title="' . (($sheel->config['globalserverlanguage_defaultlanguage'] == $res['languageid']) ? '{_default_language}' : '{_set_as_default_language}') . '"><span class="ico-16-svg halflings halflings-star draw-icon' . (($sheel->config['globalserverlanguage_defaultlanguage'] == $res['languageid']) ? '--sky-darker' : '') . '" aria-hidden="true"></span></a></li><li><a href="' . HTTPS_SERVER_ADMIN . 'settings/languages/defaultusers/' . $res['languageid'] . '/" class="btn btn-slim btn--icon" title="Set this language default for all users"><span class="ico-16-svg halflings halflings-user draw-icon" aria-hidden="true"></span></a></li><li><a href="' . HTTPS_SERVER_ADMIN . 'settings/languages/delete/' . $res['languageid'] . '/" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a></li></ul>';
            $res['textdirection'] = (($res['textdirection'] == 'ltr') ? '{_left_to_right}' : '{_right_to_left}');
            $res['installdate'] = (($res['installdate'] != '0000-00-00 00:00:00') ? $sheel->common->print_date($res['installdate']) : '{_unknown}');
            $res['lastimport'] = (($res['lastimport'] != '0000-00-00 00:00:00') ? $sheel->common->print_date($res['lastimport']) : '{_never}');
            $res['phrasecount'] = '0';
            $percent = '0.0';
            if ($res['languagecode'] != 'english')
            {
                $sqlcount = $sheel->db->query("
                    SELECT COUNT(phraseid) AS count
                    FROM " . DB_PREFIX . "language_phrases
                    WHERE text_" . substr($res['languagecode'], 0, 3) . " != text_eng
                ");
                $rescount = $sheel->db->fetch_array($sqlcount);
                $percent = round(sprintf('%.2f', ($rescount['count'] / $sheel->admincp->fetch_total_phrases_count() * 100)), 2);
            }
            $res['percent'] = (($res['languagecode'] == 'english') ? '100' : $percent);
            $installedlanguages[] = $res;
        }
    }
    $customphrases = number_format($sheel->admincp->fetch_custom_phrases_count());
    $totalphrases = number_format($sheel->admincp->fetch_total_phrases_count());
    $charsetvariable = $sheel->db->query("SHOW VARIABLES LIKE 'character_set%'");
    if ($sheel->db->num_rows($charsetvariable) > 0)
    {
        while ($resvar = $sheel->db->fetch_array($charsetvariable))
        {
            if ($resvar['Variable_name'] == 'character_set_client' OR $resvar['Variable_name'] == 'character_set_server' OR $resvar['Variable_name'] == 'character_set_database')
            {
                $charset[] = $resvar;
            }
        }
    }
    $global_languagesettings = $sheel->admincp->construct_admin_input('language', $sheel->slpage['language']);
    $language_pulldown = $sheel->language->print_language_pulldown('', '', '', '', 'draw-select');

    

    $sheel->template->fetch('main', 'settings_languages.html', 1);
    $vars = array(
		'sidenav' => $sidenav,
		'areanav' => (isset($areanav) ? $areanav : ''),
		'currentarea' => (isset($currentarea) ? $currentarea : ''),
		'prevnext' => (isset($prevnext) ? $prevnext : ''),
        'language_pulldown' => (isset($language_pulldown) ? $language_pulldown : ''),
        'title_pulldown' => (isset($title_pulldown) ? $title_pulldown : ''),
		'languageiso' => (isset($languageiso) ? $languageiso : ''),
		'locale' => (isset($locale) ? $locale : ''),
		'textdirection_pulldown' => (isset($textdirection_pulldown) ? $textdirection_pulldown : ''),
		'characterset_pulldown' => (isset($characterset_pulldown) ? $characterset_pulldown : ''),
		'q' => (isset($q) ? $q : '')
	);

    $sheel->template->parse_loop('main', array('installedlanguages' => $installedlanguages, 'charset' => $charset));
    $sheel->template->parse_hash('main', array(
		'form' => (isset($form) ? $form : array()),
		'slpage' => $sheel->slpage
	));
    
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>