<?php
/**
* Timer class to debug how long a function takes within sheel
*
* @package      sheel\Timer
* @author       sheel
*/
class timer
{
        var $stime;
        var $etime;
        
        function __construct()
        {
                $this->stime = 0.0;
        }
        function get_microtime()
        {
                $tmp = explode(" ", microtime());
                $rtime = (double)$tmp[0] + (double)$tmp[1];
                return $rtime;
        }
        function start()
        {
                $this->stime = $this->get_microtime();
        }
        function stop()
        {
                $this->etime = $this->get_microtime();
        }
        function get($decimal = 4)
        {
                return round(($this->etime - $this->stime), $decimal);
        }
}
?>