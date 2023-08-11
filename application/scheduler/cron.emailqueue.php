<?php
if (!isset($this->sheel))
{
        die('Warning: This script cannot be loaded directly.');
}
$this->sheel->timer->start();
$cronlog = '';
$cronlog .= $this->sheel->email->dispatch_in_queue();
if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}
$this->sheel->timer->stop();
$this->log_cron_action('cron.emailqueue.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>
