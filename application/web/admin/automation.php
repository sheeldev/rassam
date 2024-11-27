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

$sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Automation Manager</div>';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Automation Manager';

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
    $areanav = 'settings_api';
    $currentarea = 'Automation Manager';
    if (($sidenav = $sheel->cache->fetch("sidenav_settings")) === false) {
        $sidenav = $sheel->admincp_nav->print('settings');
        $sheel->cache->store("sidenav_settings", $sidenav);
    }

    $areanav = 'settings_automation';
    $currentarea = 'Automation Manager';
    if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'add') {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Automation Manager - Add Task</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Automation Manager - Add Task';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process') {
            $sheel->GPC['form']['varname'] = str_replace(' ', '_', $sheel->GPC['form']['varname']);
            $sheel->GPC['form']['product'] = isset($sheel->GPC['form']['product']) ? $sheel->GPC['form']['product'] : 'sheel';
            $newminute = array(0 => 0);
            if (isset($sheel->GPC['form']['minute'])) {
                foreach ($sheel->GPC['form']['minute'] as $key => $value) {
                    if ($value != '-1') {
                        $newminute[$key] = $value;
                    }
                }
            }
            $sheel->GPC['form']['minute'] = serialize($newminute);
            $sheel->db->query("
                INSERT INTO " . DB_PREFIX . "cron
                (cronid, nextrun, month, weekday, day, hour, minute, filename, loglevel, active, varname, product)
                VALUES
                (NULL,
                '" . TIMESTAMPNOW . "',
                '" . intval($sheel->GPC['form']['month']) . "',
                '" . intval($sheel->GPC['form']['weekday']) . "',
                '" . intval($sheel->GPC['form']['day']) . "',
                '" . intval($sheel->GPC['form']['hour']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['minute']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['filename']) . "',
                '" . intval($sheel->GPC['form']['loglevel']) . "',
                '" . intval($sheel->GPC['form']['status']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['varname']) . "',
                '" . $sheel->db->escape_string($sheel->GPC['form']['product']) . "')
            ");
            refresh(HTTPS_SERVER_ADMIN . 'settings/automation/');
        }
        $form['varname'] = '';
        $form['minute1'] = '<select name="form[minute][0]" tabindex="1" class="draw-select">';
        $form['minute1'] .= '<option value="-1" selected="selected">* (every minute)</option>';
        for ($m = 0; $m <= 59; $m++) {
            $form['minute1'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $form['minute1'] .= '</select>';
        $form['minute2'] = '<select name="form[minute][1]" tabindex="1" class="draw-select">';
        $form['minute2'] .= '<option value="-1">-</option>';
        for ($m = 0; $m <= 59; $m++) {
            $form['minute2'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $form['minute2'] .= '</select>';
        $form['minute3'] = '<select name="form[minute][2]" tabindex="1" class="draw-select">';
        $form['minute3'] .= '<option value="-1">-</option>';
        for ($m = 0; $m <= 59; $m++) {
            $form['minute3'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $form['minute3'] .= '</select>';
        $form['minute4'] = '<select name="form[minute][3]" tabindex="1" class="draw-select">';
        $form['minute4'] .= '<option value="-1">-</option>';
        for ($m = 0; $m <= 59; $m++) {
            $form['minute4'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        $form['minute4'] .= '</select>';
        $form['hours'] = '<select name="form[hour]" id="sel_hour" tabindex="1" class="draw-select">';
        $form['hours'] .= '<option value="-1">* (every hour)</option>';
        for ($h = 0; $h <= 23; $h++) {
            $form['hours'] .= '<option value="' . $h . '">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':</option>';
        }
        $form['hours'] .= '</select>';
        // day of the week
        $form['dow'] = '<select name="form[weekday]" id="sel_weekday" tabindex="1" class="draw-select">';
        $form['dow'] .= '<option value="-1">* (every day)</option>';
        $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        for ($dow = 0; $dow <= 6; $dow++) {
            $day = $days[$dow];
            $weekday = '{_' . $day . '}';
            $form['dow'] .= '<option value="' . $dow . '">' . $weekday . '</option>';
        }
        $form['dow'] .= '</select>';
        // day of the month
        $form['dom'] = '<select name="form[day]" id="sel_day" tabindex="1" class="draw-select">';
        $form['dom'] .= '<option value="-1">* (every day)</option>';
        for ($dom = 1; $dom <= 31; $dom++) {
            $form['dom'] .= '<option value="' . $dom . '">' . $sheel->admincp->ordinal($dom) . '</option>';
        }
        $form['dom'] .= '</select>';
        $form['moy'] = '<select name="form[month]" id="sel_moy" tabindex="1" class="draw-select">';
        $form['moy'] .= '<option value="-1">* (every month)</option>';
        $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
        for ($dom = 1; $dom <= 12; $dom++) {
            $month = $months[$dom - 1];
            $monthname = '{_' . $month . '}';
            $form['moy'] .= '<option value="' . $dom . '">' . $monthname . '</option>';
        }
        $form['moy'] .= '</select>';
        $form['savelog_1'] = 'checked="checked"';
        $form['savelog_0'] = '';
        $form['status_1'] = 'checked="checked"';
        $form['status_0'] = '';
        $form['filename'] = '';
        $form['products_pulldown'] = $sheel->admincp->products_pulldown('sheel', 'form[product]');
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'run' and isset($sheel->GPC['cronid'])) {
        $sheel->db->query("
            UPDATE " . DB_PREFIX . "cron
            SET nextrun = '" . TIMESTAMPNOW . "',
            active = '1'
            WHERE cronid = '" . intval($sheel->GPC['cronid']) . "'
            LIMIT 1
        ");
        refresh(HTTPS_SERVER_ADMIN . 'settings/automation/?note=running');
        exit();
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'update') {
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Automation Manager - Edit Task</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | Automation Manager - Edit Task';
        if (isset($sheel->GPC['do']) and $sheel->GPC['do'] == 'process' and isset($sheel->GPC['cronid'])) {

            $sheel->GPC['form']['varname'] = str_replace(' ', '_', $sheel->GPC['form']['varname']);
            $newminute = array(0 => -1);
            if (isset($sheel->GPC['form']['minute'])) {
                foreach ($sheel->GPC['form']['minute'] as $key => $value) {
                    if ($value != '-1') {
                        $newminute[$key] = $value;
                    }
                }
            }
            $sheel->GPC['form']['minute'] = serialize($newminute);
            $sheel->db->query("
                UPDATE " . DB_PREFIX . "cron
                SET month = '" . intval($sheel->GPC['form']['month']) . "',
                weekday = '" . intval($sheel->GPC['form']['weekday']) . "',
                day = '" . intval($sheel->GPC['form']['day']) . "',
                hour = '" . intval($sheel->GPC['form']['hour']) . "',
                minute = '" . $sheel->db->escape_string($sheel->GPC['form']['minute']) . "',
                filename = '" . $sheel->db->escape_string($sheel->GPC['form']['filename']) . "',
                loglevel = '" . intval($sheel->GPC['form']['loglevel']) . "',
                active = '" . intval($sheel->GPC['form']['status']) . "',
                varname = '" . $sheel->db->escape_string($sheel->GPC['form']['varname']) . "',
                product = '" . $sheel->db->escape_string($sheel->GPC['form']['product']) . "',
                nextrun = '" . TIMESTAMPNOW . "'
                WHERE cronid = '" . intval($sheel->GPC['cronid']) . "'
                LIMIT 1
            ");
            refresh(HTTPS_SERVER_ADMIN . 'settings/automation/');
        }
        $sql = $sheel->db->query("
            SELECT *
            FROM " . DB_PREFIX . "cron
            WHERE cronid = '" . intval($sheel->GPC['cronid']) . "'
            LIMIT 1
        ");
        if ($sheel->db->num_rows($sql) > 0) {
            while ($res = $sheel->db->fetch_array($sql)) {
                $form['varname'] = $res['varname'];
                $form['cronid'] = $res['cronid'];
                $minutes = stripslashes($res['minute']);
                $minutes = unserialize($minutes);
                $form['minute1'] = '<select name="form[minute][0]" tabindex="1" class="draw-select">';
                if (!isset($minutes[1])) {
                    $form['minute1'] .= '<option value="-1" selected="selected">* (every minute)</option>';
                } else {
                    $form['minute1'] .= '<option value="-1">* (every minute)</option>';
                }
                for ($m = 0; $m <= 59; $m++) {
                    if (isset($minutes[0]) and $minutes[0] == $m) {
                        $form['minute1'] .= '<option value="' . $m . '" selected="selected">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    } else {
                        $form['minute1'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    }
                }
                $form['minute1'] .= '</select>';
                $form['minute2'] = '<select name="form[minute][1]" tabindex="1" class="draw-select">';
                if (!isset($minutes[1])) {
                    $form['minute2'] .= '<option value="-1" selected="selected">-</option>';
                } else {
                    $form['minute2'] .= '<option value="-1">-</option>';
                }
                for ($m = 0; $m <= 59; $m++) {
                    if (isset($minutes[1]) and $minutes[1] == $m) {
                        $form['minute2'] .= '<option value="' . $m . '" selected="selected">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    } else {
                        $form['minute2'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    }
                }
                $form['minute2'] .= '</select>';
                $form['minute3'] = '<select name="form[minute][2]" tabindex="1" class="draw-select">';
                if (!isset($minutes[2])) {
                    $form['minute3'] .= '<option value="-1" selected="selected">-</option>';
                } else {
                    $form['minute3'] .= '<option value="-1">-</option>';
                }
                for ($m = 0; $m <= 59; $m++) {
                    if (isset($minutes[2]) and $minutes[2] == $m) {
                        $form['minute3'] .= '<option value="' . $m . '" selected="selected">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    } else {
                        $form['minute3'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    }
                }
                $form['minute3'] .= '</select>';
                $form['minute4'] = '<select name="form[minute][3]" tabindex="1" class="draw-select">';
                if (!isset($minutes[3])) {
                    $form['minute4'] .= '<option value="-1" selected="selected">-</option>';
                } else {
                    $form['minute4'] .= '<option value="-1">-</option>';
                }
                for ($m = 0; $m <= 59; $m++) {
                    if (isset($minutes[3]) and $minutes[3] == $m) {
                        $form['minute4'] .= '<option value="' . $m . '" selected="selected">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    } else {
                        $form['minute4'] .= '<option value="' . $m . '">:' . str_pad($m, 2, '0', STR_PAD_LEFT) . '</option>';
                    }
                }
                $form['minute4'] .= '</select>';
                $form['hours'] = '<select name="form[hour]" id="sel_hour" tabindex="1" class="draw-select">';
                if ($res['hour'] == '-1') {
                    $form['hours'] .= '<option value="-1" selected="selected">* (every hour)</option>';
                } else {
                    $form['hours'] .= '<option value="-1">* (every hour)</option>';
                }
                for ($h = 0; $h <= 23; $h++) {
                    if (isset($res['hour']) and $res['hour'] == $h) {
                        $form['hours'] .= '<option value="' . $h . '" selected="selected">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':</option>';
                    } else {
                        $form['hours'] .= '<option value="' . $h . '">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':</option>';
                    }
                }
                $form['hours'] .= '</select>';
                $form['dow'] = '<select name="form[weekday]" id="sel_weekday" tabindex="1" class="draw-select">';
                if ($res['weekday'] == '-1') {
                    $form['dow'] .= '<option value="-1" selected="selected">* (every day)</option>';
                } else {
                    $form['dow'] .= '<option value="-1">* (every day)</option>';
                }
                $days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                for ($dow = 0; $dow <= 6; $dow++) {
                    $day = $days[$dow];
                    $weekday = '{_' . $day . '}';
                    if (isset($res['weekday']) and $res['weekday'] == $dow) {
                        $form['dow'] .= '<option value="' . $dow . '" selected="selected">' . $weekday . '</option>';
                    } else {
                        $form['dow'] .= '<option value="' . $dow . '">' . $weekday . '</option>';
                    }
                }
                $form['dow'] .= '</select>';

                $form['dom'] = '<select name="form[day]" id="sel_day" tabindex="1" class="draw-select">';
                if ($res['day'] == '-1') {
                    $form['dom'] .= '<option value="-1" selected="selected">* (every day)</option>';
                } else {
                    $form['dom'] .= '<option value="-1">* (every day)</option>';
                }
                for ($dom = 1; $dom <= 31; $dom++) {
                    if (isset($res['day']) and $res['day'] == $dom) {
                        $form['dom'] .= '<option value="' . $dom . '" selected="selected">' . $sheel->admincp->ordinal($dom) . '</option>';
                    } else {
                        $form['dom'] .= '<option value="' . $dom . '">' . $sheel->admincp->ordinal($dom) . '</option>';
                    }
                }
                $form['dom'] .= '</select>';

                $form['moy'] = '<select name="form[month]" id="sel_moy" tabindex="1" class="draw-select">';
                if ($res['month'] == '-1') {
                    $form['moy'] .= '<option value="-1" selected="selected">* (every month)</option>';
                } else {
                    $form['moy'] .= '<option value="-1">* (every month)</option>';
                }
                $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
                for ($dom = 1; $dom <= 12; $dom++) {
                    $month = $months[$dom - 1];
                    $monthname = '{_' . $month . '}';
                    if (isset($res['month']) and $res['month'] == $dom) {
                        $form['moy'] .= '<option value="' . $dom . '" selected="selected">' . $monthname . '</option>';
                    } else {
                        $form['moy'] .= '<option value="' . $dom . '">' . $monthname . '</option>';
                    }
                }
                $form['moy'] .= '</select>';

                $form['savelog_1'] = '';
                $form['savelog_0'] = 'checked="checked"';
                $form['status_1'] = '';
                $form['status_0'] = 'checked="checked"';
                if ($res['loglevel'] == 1) {
                    $form['savelog_1'] = 'checked="checked"';
                    $form['savelog_0'] = '';
                }
                if ($res['active'] == 1) {
                    $form['status_1'] = 'checked="checked"';
                    $form['status_0'] = '';
                }
                $form['filename'] = $res['filename'];
                $form['products_pulldown'] = $sheel->admincp->products_pulldown($res['product'], 'form[product]');
            }
        }
    } else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'delete') {
        if (isset($sheel->GPC['xid']) and !empty($sheel->GPC['xid'])) {
            $sheel->db->query("
                DELETE FROM " . DB_PREFIX . "cron
                WHERE cronid = '" . intval($sheel->GPC['xid']) . "'
                LIMIT 1
            ");
            die(json_encode(array('response' => 1, 'message' => 'Successfully deleted automated task ID #' . $sheel->GPC['xid'])));
        } else {
            $sheel->template->templateregistry['message'] = 'No automated task was selected.  Please try again.';
            die(json_encode(array('response' => '0', 'message' => $sheel->template->parse_template_phrases('message'))));
        }
    }
    else if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'configurations') {
        $buttons = '<p><a href="' . HTTPS_SERVER_ADMIN . 'settings/automation/"><button name="button" type="button" data-accordion-toggler-for="" class="btn" id="" aria-expanded="false" aria-controls="">{_cancel}</button></a></p>';
        $settings = $sheel->admincp->construct_admin_input('automation', HTTPS_SERVER_ADMIN . 'settings/automation/confirgurations/', '', $buttons);
        $sheel->template->meta['areatitle'] = 'Admin CP | <div class="type--subdued">Automation Configurations</div>';
        $sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin CP | - Automations Configurations';
        $areanav = 'settings_automation';
        $currentarea = '<span class="breadcrumb"><a href="' . HTTPS_SERVER_ADMIN . 'settings/automation/">Automation</a> / </span> {_configurations}';
        $vars['areanav'] = $areanav;
        $vars['currentarea'] = $currentarea;
        $vars['sidenav'] = $sidenav;
        $vars['settings'] = $settings;
        $vars['url'] = $_SERVER['REQUEST_URI'];
        $sheel->template->fetch('main', 'settings_automationconfigurations.html', 1);

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
    }
    $tasks = array();
    $sql = $sheel->db->query("
        SELECT cron.*, AVG(cronlog.time) AS average
        FROM " . DB_PREFIX . "cron cron
        LEFT JOIN " . DB_PREFIX . "cronlog cronlog ON (cron.varname = cronlog.varname)
        GROUP BY cron.filename
        ORDER BY nextrun ASC
    ");
    if ($sheel->db->num_rows($sql) > 0) {
        while ($res = $sheel->db->fetch_array($sql, DB_ASSOC)) {
            $nextrun = $sheel->datetimes->fetch_datetime_from_timestamp($res['nextrun']);
            $timerule = $sheel->admincp->fetch_cron_schedule($res);
            $res['minute'] = (($timerule['minute'] != '*') ? str_pad($timerule['minute'], 2, '0', STR_PAD_LEFT) : $timerule['minute']);
            $res['hour'] = (($timerule['hour'] != '*') ? str_pad($timerule['hour'], 2, '0', STR_PAD_LEFT) : $timerule['hour']);
            $res['day'] = (($timerule['day'] != '*') ? $sheel->admincp->ordinal($timerule['day']) : $timerule['day']);
            $res['month'] = $timerule['month'];
            $res['day_of_week'] = $timerule['weekday'];
            $res['job'] = $res['filename'];
            $res['average'] = number_format($res['average'], 1) . '{_s_shortform}';
            $res['nextrun'] = $sheel->common->print_date($nextrun, 'D M j Y g:i A', 0, 0);
            if ($res['product'] == 'sheel' or empty($res['product'])) {
                $res['product'] = '<img src="' . $sheel->config['imgcdn'] . 'acp/sheel_acp.png" border="0" width="16" title="sheel" alt="sheel" />';
            } else {
                $res['product'] = '{_' . $res['product'] . '}';
            }

            $run = ($res['active'] == '1')
                ? '<li><a href="' . HTTPS_SERVER_ADMIN . 'settings/automation/run/task/' . $res['cronid'] . '/" class="btn btn-slim btn--icon" title="Run task"><span class="ico-16-svg halflings halflings-flash draw-icon" aria-hidden="true"></span></a></li>'
                : '<li><a href="javascript:;" class="btn btn-slim btn--icon" title="Task running"><span class="ico-16-svg halflings halflings-flash draw-icon--sky-darker" aria-hidden="true"></span></a></li>';
            $res['action'] = '<ul class="segmented">' . $run . '<li><a href="' . HTTPS_SERVER_ADMIN . 'settings/automation/update/task/' . $res['cronid'] . '/" class="btn btn-slim btn--icon" title="{_update}"><span class="ico-16-svg halflings halflings-edit draw-icon" aria-hidden="true"></span></a></li><li><a href="javascript:;" data-bind-event-click="acp_confirm(\'delete\', \'Delete this automated task?\', \'Are you sure you want to delete this automated task? Please be sure you know what you are doing before performing this action.  No physical files will be deleted, only the entry to run the task will be removed until you add it again.\', \'' . $res['cronid'] . '\', 1, \'task\', \'\')" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a><!--<a href="' . HTTPS_SERVER_ADMIN . 'settings/automation/delete/task/' . $res['cronid'] . '/" class="btn btn-slim btn--icon" title="{_delete}"><span class="ico-16-svg halflings halflings-trash draw-icon" aria-hidden="true"></span></a>--></li></ul>';

            $tasks[] = $res;
        }
    }


    $vars['areanav'] = $areanav;
    $vars['currentarea'] = $currentarea;
    $vars['sidenav'] = $sidenav;
    $sheel->template->fetch('main', 'settings_automation.html', 1);
    $sheel->template->parse_loop('main', array('tasks' => $tasks));
    $sheel->template->parse_hash(
        'main',
        array(
            'form' => (isset($form) ? $form : array())
        )
    );
    $sheel->template->pprint('main', $vars);
    exit();
} else {
    refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode(SCRIPT_URI));
    exit();
}
?>