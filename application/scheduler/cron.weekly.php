<?php
if (!isset($this->sheel))
{
        die('Warning: This script cannot be loaded directly.');
}
$this->sheel->timer->start();
$cronlog = '';

$cronlog .= $this->sheel->language->clean_cache();
$cronlog .= $this->sheel->styles->clean_cache();
$cronlog .= $this->sheel->xlsx->clean_cache();
if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

$this->sheel->timer->stop();
$this->log_cron_action('cron.weekly.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());

?>
