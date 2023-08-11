<?php
if (!isset($this->sheel))
{
        die('Warning: This script cannot be loaded directly.');
}
$this->sheel->timer->start();
$cronlog = '';

$cronlog .= $this->sheel->automation->clean_hits_logs(); // retains 1 years worth of hits
$cronlog .= $this->sheel->automation->clean_visits_logs();  // retains 1 years worth of visits


if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

$this->sheel->timer->stop();
$this->log_cron_action('cron.yearly.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());

?>
