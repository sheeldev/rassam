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
    'addition',
    'vendor' => array(
        'growl',
        'font-awesome',
        'glyphicons',
        'chartist',
        'balloon',
        'growl'
    )
);


if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $sheel->template->meta['jsinclude']['footer'][] = 'admin_genai';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }
    if (isset($sheel->GPC['subcmd']) && $sheel->GPC['subcmd'] == 'delete') {
        $query = "
            DELETE FROM " . DB_PREFIX . "prompts
            WHERE id = ?
        ";

        $stmt = $sheel->db->prepare($query);
        $stmt->bind_param("i", $sheel->GPC['id']);

        $stmt->execute();

        $sheel->template->templateregistry['message'] = '{_successfully_deleted_prompt}';

        die(json_encode([
            'response' => '1',
            'message' => $sheel->template->parse_template_phrases('message')
        ]));
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Prompts - Update</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Prompts - Update';
        $areanav = 'settings_genai';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            $varname = $sheel->GPC['varname'];
            $description = $sheel->GPC['description'];
            $prompt_text = $sheel->GPC['prompt_text'];
            $prompt_context = $sheel->GPC['prompt_context'];
            $response_schema = $sheel->GPC['response_schema'];
            $prompt_parameters = $sheel->GPC['prompt_parameters'];
            $adminonly = $sheel->GPC['adminonly'];
            $type = $sheel->GPC['type'];
            $group = $sheel->GPC['group'];
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "prompts
                SET varname = '" . $varname . "',
                description = '" . $description . "',
                prompt_text = '" . $prompt_text . "',
                prompt_parameters = '" . $prompt_parameters . "',
                prompt_context = '" . $prompt_context . "',
                response_schema = '" . $response_schema . "',
                adminonly = '" . $adminonly . "',
                type = '" . $type . "',
                `group` = '" . $group . "'
                WHERE id = '" . $sheel->GPC['id'] . "'
            ");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'prompt updated', 'A prompt was updated to the portal');
            refresh(HTTPS_SERVER_ADMIN . 'settings/genai/');
        }

        if (isset($sheel->GPC['id']) and $sheel->GPC['id'] != '') {
            $query = "
                SELECT *
                FROM " . DB_PREFIX . "prompts
                WHERE varname = ?
            ";
            $stmt = $sheel->db->prepare($query);
            $stmt->bind_param("s", $sheel->GPC['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $form = [];
                $form['id'] = $row['id'];
                $form['varname'] = htmlspecialchars($row['varname']);
                $form['description'] = htmlspecialchars($row['description']);
                $form['prompt_text'] = htmlspecialchars($row['prompt_text']);
                $form['prompt_parameters'] = htmlspecialchars($row['prompt_parameters']);
                $form['prompt_context'] = htmlspecialchars($row['prompt_context']);
                $form['response_schema'] = htmlspecialchars($row['response_schema']);
                $form['adminonlyno'] = $row['adminonly'] == '0' ? ' checked="checked"' : '';
                $form['adminonlyyes'] = $row['adminonly'] == '1' ? ' checked="checked"' : '';
                $form['groupsizing'] = $row['group'] == 'sizing' ? ' checked="checked"' : '';
                $form['groupglobal'] = $row['group'] == 'global' ? ' checked="checked"' : '';
                $form['typechat'] = $row['type'] == 'chat' ? ' checked="checked"' : '';
                $form['typeimage'] = $row['type'] == 'image' ? ' checked="checked"' : '';
                $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/genai/">Generative AI</a> / </span>' . $form['varname'];
            } else {
                $sheel->template->templateregistry['message'] = '{_prompt_not_found}';
                $sheel->template->assign('message', $sheel->template->parse_template_phrases('message'));
            }
        } else {
            $sheel->template->templateregistry['message'] = '{_please_select_prompt}';
            $sheel->template->assign('message', $sheel->template->parse_template_phrases('message'));
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'save') {
            $varname = $sheel->GPC['varname'];
            $description = $sheel->GPC['description'];
            $prompt_text = $sheel->GPC['prompt_text'];
            $prompt_context = $sheel->GPC['prompt_context'];
            $response_schema = $sheel->GPC['response_schema'];
            $prompt_parameters = $sheel->GPC['prompt_parameters'];
            $adminonly = $sheel->GPC['adminonly'];
            $type = $sheel->GPC['type'];
            $group = $sheel->GPC['group'];
            $sheel->db->query("
                INSERT INTO " . DB_PREFIX . "prompts
                (varname, description, prompt_text, prompt_parameters, prompt_context, response_schema, adminonly, type, `group`)
                VALUES
                ('" . $varname . "',
                '" . $description . "',
                '" . $prompt_text . "',
                '" . $prompt_context . "',
                '" . $response_schema . "',
                '" . $prompt_parameters . "',
                '" . $adminonly . "',
                '" . $type . "',
                '" . $group . "')
            ");
            $sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), 'success' . "\n" . $sheel->array2string($sheel->GPC), 'New prompt added', 'A new prompt was added to the portal');
            refresh(HTTPS_SERVER_ADMIN . 'settings/genai/');
        }
        $form = [];
        $form['varname'] = '';
        $form['description'] = '';
        $form['prompt_text'] = '';
        $form['prompt_parameters'] = '';
        $form['response_schema'] = '';
        $form['prompt_context'] = '';
        $form['adminonly'] = 1;
        $form['group'] = 'global';
        $form['type'] = 'prompt';
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Prompts - Add</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Prompts - Add';
        $areanav = 'settings_genai';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/genai/">Generative AI</a> / </span> {_add}';
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'config') {
        $buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'settings/genai/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_cancel}</button></a></p>';
        $settings = $sheel->admincp->construct_admin_input('genai', HTTPS_SERVER_ADMIN . 'settings/genai/config/', '', $buttons);
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Generative AI Configuration</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Generative AI Configuration';
        $areanav = 'settings_genai';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/genai/">Generative AI</a> / </span> {_configuration}';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $vars['sidenav'] = $sidenav;
        $vars['settings'] = $settings;
        $vars['url'] = $_SERVER['REQUEST_URI'];
        $sheel->template->fetch('main', 'settings_genai_config.html', 1);

        $sheel->template->parse_loop(
            'main',
            array(
                'types_rows' => $types_rows
            )
        );
        $sheel->template->parse_hash(
            'main',
            array(
                'form' => (isset($form) ? $form : array())
            )
        );
        $sheel->template->pprint('main', $vars);
        exit();
    } else {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Generative AI</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Generative AI';
        $areanav = 'settings_genai';
        $currentarea = 'Generative AI';
        $prompts = [];
        $count = 0;
        $where = "type = 'chat'";
        if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'chats') {
            $where = "type = 'chat'";
        } else if (isset($sheel->GPC['view']) and $sheel->GPC['view'] == 'images') {
            $where = "type = 'image'";
        }
        $sql = $sheel->db->query("
                SELECT id, varname, description, prompt_text, prompt_parameters, prompt_context, adminonly, type, `group`
                FROM " . DB_PREFIX . "prompts
                WHERE $where
                ORDER BY id ASC
            ");
        $count = $sheel->db->num_rows($sql);
        if ($sheel->db->num_rows($sql) > 0) {
            while ($row = $sheel->db->fetch_array($sql, DB_ASSOC)) {
                $row['adminonly'] = ($row['adminonly'] == 1 ? '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_checkmark.png" border="0" alt="" />' : '<img src="' . $sheel->config['imgcdn'] . 'v5/ico_dot_red.gif" border="0" alt="" />');
                $prompts[] = $row;
            }
        }
    }
    $form['count'] = $count;
    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $vars['url'] = $_SERVER['REQUEST_URI'];
    $sheel->template->fetch('main', 'settings_genai.html', 1);
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array())
        )
    );
    $sheel->template->parse_loop(
        'main',
        array(
            'prompts' => $prompts
        )
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>