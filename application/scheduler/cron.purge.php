<?php
if (!isset($this->sheel)) {
        die('Warning: This script cannot be loaded directly.');
}
$cronlog = '';

$this->sheel->timer->start();


$eventPurgeDays = $this->sheel->config['eventpurgedays'];

$sqlevents = $this->sheel->db->query("
        DELETE
        FROM " . DB_PREFIX . "events
        WHERE createdtime < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $eventPurgeDays . " DAY)) 
        ", 0, null, __FILE__, __LINE__);
$deletedRecords = $this->sheel->db->affected_rows();
$cronlog .= 'purge->events older than ' . $eventPurgeDays . ' days, ' . $deletedRecords . ' records deleted; ';

if (!empty($cronlog)) {
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.purge.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>