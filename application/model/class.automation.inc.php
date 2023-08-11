<?php
class automation
{
    protected $sheel;

    function __construct($sheel)
    {
        $this->sheel = $sheel;
    }

    function log_cron_action($description, $nextitem, $time = 0)
    {
        if ($nextitem['loglevel']) {
            $this->sheel->db->query("
				INSERT INTO " . DB_PREFIX . "cronlog
				(varname, dateline, description, time)
				VALUES(
				'" . $this->sheel->db->escape_string($nextitem['varname']) . "',
				" . TIMESTAMPNOW . ",
				'" . $this->sheel->db->escape_string($description) . "',
				'" . $this->sheel->db->escape_string($time) . "')
			", 0, null, __FILE__, __LINE__);
        }
    }

    function fetch_cron_next_run($data, $hour = -2, $minute = -2)
    {
        if ($hour == -2) {
            $hour = intval(date('H', TIMESTAMPNOW));
        }
        if ($minute == -2) {
            $minute = intval(date('i', TIMESTAMPNOW));
        }
        $data['minute'] = unserialize($data['minute']);
        if ($data['hour'] == -1 and $data['minute'][0] == -1) {
            $newdata['hour'] = $hour;
            $newdata['minute'] = $minute + 1;
        } else if ($data['hour'] == -1 and $data['minute'][0] != -1) {
            $newdata['hour'] = $hour;
            $nextminute = $this->fetch_cron_next_minute($data['minute'], $minute);
            if ($nextminute === false) {
                ++$newdata['hour'];
                $nextminute = $data['minute'][0];
            }
            $newdata['minute'] = $nextminute;
        } else if ($data['hour'] != -1 and $data['minute'][0] == -1) {
            if ($data['hour'] < $hour) {
                $newdata['hour'] = -1;
                $newdata['minute'] = -1;
            } else if ($data['hour'] == $hour) {
                $newdata['hour'] = $data['hour'];
                $newdata['minute'] = $minute + 1;
            } else {
                $newdata['hour'] = $data['hour'];
                $newdata['minute'] = 0;
            }
        } else if ($data['hour'] != -1 and $data['minute'][0] != -1) {
            $nextminute = $this->fetch_cron_next_minute($data['minute'], $minute);
            if ($data['hour'] < $hour or ($data['hour'] == $hour and $nextminute === false)) {
                $newdata['hour'] = -1;
                $newdata['minute'] = -1;
            } else {
                $newdata['hour'] = $data['hour'];
                $newdata['minute'] = $nextminute;
            }
        }
        return $newdata;
    }

    function fetch_cron_next_minute($minutedata, $minute)
    {
        foreach ($minutedata as $nextminute) {
            if ($nextminute > $minute) {
                return $nextminute;
            }
        }
        return false;
    }

    function construct_cron_item($cronid, $data = '')
    {
        if (!is_array($data)) {
            $data = $this->sheel->db->query_fetch("
				SELECT cronid, nextrun, resetafter, month, weekday, day, hour, minute, filename, loglevel, active, varname, product
				FROM " . DB_PREFIX . "cron
				WHERE cronid = '" . intval($cronid) . "'
			");
        }

        $minutenow = intval(date('i', TIMESTAMPNOW)); // 17
        $hournow = intval(date('H', TIMESTAMPNOW)); // 02
        $daynow = intval(date('d', TIMESTAMPNOW)); // 28
        $monthnow = intval(date('m', TIMESTAMPNOW)); // 05
        $yearnow = intval(date('Y', TIMESTAMPNOW)); // 2019
        $weekdaynow = intval(date('w', TIMESTAMPNOW)); // 2
        $daysinmonthnow = intval(date('t', TIMESTAMPNOW)); // 31

        // figure out date and time of 1st and 2nd next times to run
        if ($data['month'] == -1) { // every month
            if ($data['weekday'] == -1) { // every day of the week
                if ($data['day'] == -1) { // every day of the month
                    $firstday = $daynow; // 28
                    $secondday = $daynow + 1; // 28 + 1 = 29 (adds whole day)
                } else { // specific day of the month
                    $firstday = $data['day']; // 1
                    $secondday = $data['day'] + $daysinmonthnow; // 1 + 31 = 32 (adds whole month)
                }
            } else { // specific day of the week
                $firstday = $daynow + ($data['weekday'] - $weekdaynow); // 28 + (1 - 2) = 27
                $secondday = $firstday + 7; // 27 + 7 = 34
            }
        } else { // specific month (Jan, Mar, Dec, etc.)
            if ($data['weekday'] == -1) { // every day of the week
                if ($data['day'] == -1) { // every day of the month
                    $firstday = $daynow; // 1
                    $secondday = $this->sheel->datetimes->fetch_days_between($monthnow, $daynow, $yearnow, $data['month'], ($daynow + 1), (($monthnow > $data['month']) ? ($yearnow + 1) : $yearnow)); // 246 or 1
                } else { // specific day of the month
                    $firstday = $data['day']; // 1
                    $secondday = $daynow + $this->sheel->datetimes->fetch_days_between($monthnow, $daynow, $yearnow, $data['month'], $data['day'], (($monthnow > $data['month']) ? ($yearnow + 1) : $yearnow)); // 28 + 218 = 246
                }
            } else { // specific day of the week (need 248)
                $firstday = $this->sheel->datetimes->fetch_days_between($monthnow, $daynow, $yearnow, $data['month'], $daynow + ($data['weekday'] - $weekdaynow), (($monthnow > $data['month']) ? ($yearnow + 1) : $yearnow)); // 248
                $secondday = $this->sheel->datetimes->fetch_days_between($monthnow, $daynow, $yearnow, $data['month'], ($daynow + ($data['weekday'] - $weekdaynow) + 7), (($monthnow > $data['month']) ? ($yearnow + 1) : $yearnow)); // 255
            }
        }

        if ($firstday < $daynow) // 28 < 246
        { // first day has past, use second day..
            $firstday = $secondday; // 246
        }

        if ($firstday == $daynow) // 32 == 28
        {
            $todaytime = $this->fetch_cron_next_run($data);
            if ($todaytime['hour'] == -1 and $todaytime['minute'] == -1) {
                $data['day'] = $secondday;
                $newtime = $this->fetch_cron_next_run($data, 0, -1);
                $data['hour'] = $newtime['hour'];
                $data['minute'] = $newtime['minute'];
            } else {
                $data['day'] = $firstday;
                $data['hour'] = $todaytime['hour'];
                $data['minute'] = $todaytime['minute'];
            }
        } else {
            $data['day'] = $firstday; // 32
            $newtime = $this->fetch_cron_next_run($data, 0, -1);
            $data['hour'] = $newtime['hour'];
            $data['minute'] = $newtime['minute'];
        }

        $nextrun = mktime($data['hour'], $data['minute'], 0, $monthnow, $data['day'], $yearnow);

        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "cron
			SET nextrun = '" . intval($nextrun) . "'
			WHERE cronid = '" . intval($cronid) . "'
				AND nextrun = '" . $data['nextrun'] . "'
		");
        $norun = ($this->sheel->db->affected_rows() > 0);
        $this->build_cron_next_runtime($nextrun);
        return iif($norun, $nextrun, 0);
    }
    function build_cron_next_runtime($nextrun = '')
    {
        if (!$nextcron = $this->sheel->db->query_fetch("SELECT MIN(nextrun) AS nextrun FROM " . DB_PREFIX . "cron AS cron")) {
            $nextcron['nextrun'] = TIMESTAMPNOW + 60 * 60;
        }
        return $nextrun;
    }
    function reset_locked_tasks()
    {
        $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "cron
                        SET active = '1', resetafter = '0'
                        WHERE resetafter <= " . TIMESTAMPNOW . "
                                AND active = '-1'
                                AND resetafter > 0
                ", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->affected_rows() > 0) {
            $nextitem['loglevel'] = '1';
            $nextitem['varname'] = 'reset';
            $this->log_cron_action('Successfully reset ' . $this->sheel->db->affected_rows() . ' run-away tasks.', $nextitem, 0);
        }
    }
    function automatic_update_in_progress()
    {
        $response = $this->sheel->db->fetch_field(DB_PREFIX . "configuration", "name = 'automaticupdateinprogress'", "value");
        if ($response) {
            return true;
        }
        return false;
    }

    function execute_task($cronid = null)
    {
        $this->reset_locked_tasks();
        
        if (!$this->automatic_update_in_progress()) {
            if ($cronid = intval($cronid)) {
                $nextitem = $this->sheel->db->query_fetch("
        				SELECT cronid, nextrun, resetafter, month, weekday, day, hour, minute, filename, loglevel, active, varname, product
        				FROM " . DB_PREFIX . "cron
        				WHERE cronid = '" . intval($cronid) . "'
        			");
            } else {
                $nextitems = $this->sheel->db->query("
        				SELECT cronid, nextrun, resetafter, month, weekday, day, hour, minute, filename, loglevel, active, varname, product
        				FROM " . DB_PREFIX . "cron
        				WHERE nextrun <= " . TIMESTAMPNOW . "
                        AND active = '1'
        				ORDER BY nextrun
        			", 0, null, __FILE__, __LINE__);
            }
            
            if (isset($nextitem)) { // set new date for nextrun for this task
                if ($nextrun = $this->construct_cron_item($nextitem['cronid'], $nextitem)) {
                    if (!empty($nextitem['filename']) and file_exists(DIR_CRON . $nextitem['filename'])) {
                        $this->lock_task($nextitem['cronid'], $nextitem['varname']);
                        include_once(DIR_CRON . $nextitem['filename']);
                        $this->unlock_task($nextitem['cronid']);
                    }
                }
            } else if (isset($nextitems)) {
                while ($nextitem = $this->sheel->db->fetch_array($nextitems, DB_ASSOC)) { // for each task, set new date for nextrun
                    if ($nextrun = $this->construct_cron_item($nextitem['cronid'], $nextitem)) {
                        if (!empty($nextitem['filename']) and file_exists(DIR_CRON . $nextitem['filename'])) { // execute task!
                            $this->lock_task($nextitem['cronid'], $nextitem['varname']);
                            include_once(DIR_CRON . $nextitem['filename']);
                            $this->unlock_task($nextitem['cronid']);
                        }
                    }
                }
            } else {
                $this->build_cron_next_runtime();
            }
        }
    }

    function lock_task($cronid, $varname = '')
    {
        $resetafter = 0;
        switch ($varname) {
            case '1minute':
            case '15minute':
            case '30minute':
            case '45minute':
            case '1hour':
            case 'daily':
            case 'weekly':
            case 'monthly':
            case 'emailqueue':
            case 'currency':{
                    $resetafter = 1 * 60 * 60; // 12 hour max execution time! 
                }
            default: {
                    $resetafter = 1 * 60 * 60; // 1 hour max execution time!
                }
        }
        if ($resetafter <= 0) {
            $resetafter = 1 * 60 * 60; // 1 hour max execution time!
        }
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "cron
			SET active = '-1', resetafter = nextrun + $resetafter
			WHERE cronid = '" . intval($cronid) . "'
		", 0, null, __FILE__, __LINE__);
    }

    function unlock_task($cronid)
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "cron
			SET active = '1', resetafter = '0'
			WHERE cronid = '" . intval($cronid) . "'
		", 0, null, __FILE__, __LINE__);
    }

    function unlock_all_tasks()
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "cron
			SET active = '1', resetafter = '0'
		", 0, null, __FILE__, __LINE__);
    }
    function clean_logs()
    {
        $this->sheel->db->query("
                        TRUNCATE TABLE " . DB_PREFIX . "cronlog
                ", 0, null, __FILE__, __LINE__);
        return 'automation->clean_logs(), ';
    }
    function clean_hits_logs()
    {
        if (date('n') == 1 and date('j') == 1) { // only runs on 1st of jan
            $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "hits
                                WHERE datetime < DATE_SUB(NOW(), INTERVAL 1 YEAR)
                        ", 0, null, __FILE__, __LINE__);
        }
        return 'automation->clean_hits_logs(), ';
    }
    function clean_visits_logs()
    {
        if (date('n') == 1 and date('j') == 1) { // only runs on 1st of jan
            $this->sheel->db->query("
                                DELETE FROM " . DB_PREFIX . "visits
                                WHERE `date` < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                        ", 0, null, __FILE__, __LINE__);
        }
        return 'automation->clean_visits_logs(), ';
    }
    function backup($tables = '*')
    {
        return false;
        if ($tables == '*') { // get all of the tables
            $tables = array();
            $result = $this->sheel->db->query('SHOW TABLES');
            while ($row = $this->sheel->db->fetch_row($result)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }
        $return = '';
        foreach ($tables as $table) { // cycle through
            $result = $this->sheel->db->query('SELECT * FROM ' . $table);
            $num_fields = $this->sheel->db->num_fields($result);
            $return .= 'DROP TABLE ' . $table . ';';
            $row2 = @$this->sheel->db->fetch_row(@$this->sheel->db->query('SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . $row2[1] . ";\n\n";
            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $this->sheel->db->fetch_row($result)) {
                    $return .= 'INSERT INTO ' . $table . ' VALUES(';
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }
            $return .= "\n\n\n";
        }
        // save sql file
        $time = time();
        $filename = DIR_TMP . 'backup/db-' . $time . '.sql';
        $handle = fopen($filename, 'w+');
        fwrite($handle, $return);
        fclose($handle);
        // zip sql file
        $z = new ZipArchive();
        $z->open(DIR_TMP . "backup/db-$time.zip", ZIPARCHIVE::CREATE);
        $z->addFile($filename);
        $z->close();
        unlink($filename);
        $this->sheel->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET `value` = '" . DATETIME24H . "'
                        WHERE `name` = 'last_automatic_backup'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        // send email?
        // ..
        return "automation->backup() [" . DIR_TMP . "backup/db-$time.zip], ";
    }
}
?>