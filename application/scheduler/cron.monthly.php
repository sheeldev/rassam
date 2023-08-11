<?php
if (!isset($this->sheel))
{
        die('Warning: This script cannot be loaded directly.');
}
$this->sheel->timer->start();
$cronlog = '';

$cronlog .= $this->sheel->smtp->clean_logs();
$cronlog .= $this->sheel->ldap->clean_logs();
$cronlog .= $this->sheel->clean_api_requests();
$cronlog .= $this->sheel->automation->clean_logs();


if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

$this->sheel->timer->stop();
$this->log_cron_action('cron.monthly.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());


?>
