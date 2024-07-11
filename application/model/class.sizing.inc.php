<?php
/**
 * Sizing class to perform the majority of Sizing functions in sheel.
 *
 * @package      sheel\Sizing
 * @version      1.0.0.0
 * @author       sheel
 */
class sizing
{
	protected $sheel;

    function __construct($sheel)
        {
                $this->sheel = $sheel;
        }

	function get_default_uom($measurement = '')
	{
		$uom = '';
		$defaultuoms = json_decode($this->sheel->config['defaultuom'], true);
		foreach ($defaultuoms as $key => $value) {
			if ($key == $measurement) {
				$uom = $value;
			}
		}
		return $uom;
	}
}
?>