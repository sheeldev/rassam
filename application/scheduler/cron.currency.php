<?php
if (!isset($this->sheel))
{
        die('Warning: This script cannot be loaded directly.');
}
$this->sheel->timer->start();

$cronlog = $this->sheel->currency->fetch_live_rates();

if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}

$this->sheel->timer->stop();
$this->log_cron_action('cron.currency.php: ' . $cronlog, $nextitem, $this->sheel->timer->get());
?>
