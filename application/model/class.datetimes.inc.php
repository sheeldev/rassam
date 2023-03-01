<?php
/**
* Date and Time class to perform the majority of date and timezone functions in sheel
*
* @package      sheel\DateTimes
* @version      1.0.0.0
* @author       sheel
*/
class datetimes
{
	/**
	* Timezone offset placeholder
	*
	* @var integer
	* @access public
	*/
	var $tz_offset;
	/**
	* Days array placeholder
	*
	* @var array
	* @access public
	*/
	var $days = array();

        /**
        * Constructor
        */
	function __construct(){}

	/**
        * Function to fetch the date from now with the days supplied as the argument
        *
        * @param       integer      days
        *
        * @return      string       Return formatted date
        */
	function fetch_date_fromnow($days)
	{
                return date('Y-m-d', (TIMESTAMPNOW + ($days * 86400)));
        }

	/**
        * Function to fetch the date and time from now with the days supplied as the argument
        *
        * @param       integer      days
        *
        * @return      string       Return formatted date
        */
	function fetch_datetime_fromnow($days)
	{
                return date('Y-m-d H:i:s', (TIMESTAMPNOW + ($days * 86400)));
        }
	/**
        * Function to fetch the date and time from specified date increased by seconds
        *
	* @param       string       datetime
        * @param       integer      days
        *
        * @return      string       Return formatted date
        */
	function fetch_datetime_fromnow_seconds($datetime, $seconds)
	{
                return date('Y-m-d H:i:s', ($this->fetch_timestamp_from_datetime($datetime) + $seconds));
        }

	/**
        * Function to fetch the date and time from a specified date along with the days supplied as the argument
        *
        * @param       integer      days
        * @param       string       datetime (YYYY-MM-DD HH:MM:SS)
	* @param       string       output format (default Y-m-d H:i:s)
        *
        * @return      string       Return formatted date
        */
	function fetch_datetime_from($days, $datetime = '', $format = 'Y-m-d H:i:s')
	{
                return date($format, ($this->fetch_timestamp_from_datetime($datetime) + ($days * 86400)));
        }

	/**
        * Function to fetch the number of days between two dates
        *
        * @param       integer      month (of first date)
        * @param       integer      day (of first date)
        * @param       integer      year (of first date)
        * @param       integer      month (of the 2nd date)
        * @param       integer      day (of the 2nd date)
        * @param       integer      year (of the 2nd date)
        *
        * @return      string       Return formatted days
        */
	function fetch_days_between($m1, $d1, $y1, $m2, $d2, $y2)
	{
		return round(abs((mktime(0, 0, 0, $m2, $d2, $y2) - mktime(0, 0, 0, $m1, $d1, $y1))) / 86400);
	}
	/**
        * Function to fetch the number of hours between two dates
        *
        * @param       string      date 1 (YYYY-MM-DD HH:MM:SS)
        * @param       string      date 2 (YYYY-MM-DD HH:MM:SS)
        *
        * @return      string       Return formatted days
        */
	function fetch_hours_between($date1 = '', $date2 = '')
	{
		return round((strtotime($date2) - strtotime($date1)) / (60 * 60));
	}
        /**
        * Function to determine if today's day is a business day (ie: sat & sun will return false).  If no arguments are passed this function will take the date of today
        *
        * @param       string       year (YYYY)
        * @param       string       month (MM)
        * @param       string       day (DD)
        *
        * @return      array        Return array(true or false, day of the week code)
        */
        function is_business_day($y = '', $m = '', $d = '')
        {
		if (!empty($y) AND !empty($m) AND !empty($d))
		{
			$dotw = $this->day_of_week($y, $m, $d);
		}
		else
		{
			$dotw = $this->day_of_week(date('Y'), date('m'), date('d'));
		}
                if ($dotw != '6' AND $dotw != '0')
                {
                        return array(true, $dotw);
                }
                return array(false, $dotw);
        }

        /**
        * Function to fetch the day of the week based on a supplied date argument. 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
        *
        * @param       integer      year
        * @param       integer      month
        * @param       integer      day
        *
        * @return      string       Returns the day of week
        */
	function day_of_week($year, $month, $day)
	{
		if ($month > 2)
		{
			$month -= 2;
		}
		else
		{
			$month += 10;
			$year--;
		}
		$day = (floor((13 * $month - 1) / 5) + $day + ($year % 100) + floor(($year % 100) / 4) + floor(($year / 100) / 4) - 2 * floor($year / 100) + 77);
		return (($day - 7 * floor($day / 7)));
	}

	/**
        * Function to fetch a valid unix timestamp based on a supplied datetime format (YYYY-MM-DD HH:MM:SS)
        *
        * @param       string       datetime string (YYYY-MM-DD HH:MM:SS)
        *
        * @return      string       Return timestamp
        */
	function fetch_timestamp_from_datetime($datetime)
	{
		return strtotime($datetime);
	}

	/**
        * Function to fetch a valid datetime stamp based on a supplied unix timestamp
        *
        * @param       integer      timestamp
        *
        * @return      string       Return datetime
        */
        function fetch_datetime_from_timestamp($timestamp)
        {
                return date("Y-m-d H:i:s", $timestamp);
        }

	/**
        * Function to display the timezone pull down menu
        *
        * @param       string       fieldname
        * @param       string       selected value
        * @param       boolean      show current time (default true)
        * @param       boolean      show timezone abbreviation
        * @param       string       pull down menu css class (default draw-select)
        *
        * @return      string       Returns HTML representation of the pull down menu
        */
	function timezone_pulldown($fieldname = 'timezone', $selected = '', $showtime = true, $showabbr = true, $cssextra = 'draw-select', $formfieldname = 'timezone')
	{
		$list = DateTimeZone::listAbbreviations();
		$data = $offset = $added = array();
		foreach ($list AS $abbr => $info)
		{
			foreach ($info AS $zone)
			{
				if (!empty($zone['timezone_id']) AND !in_array($zone['timezone_id'], $added))
				{
					try
					{
						$z = new DateTimeZone($zone['timezone_id']);
						$c = new DateTime(null, $z);
						if ($showtime)
						{
							$zone['time'] = $c->format('h:i A') . ' - ';
						}
						else
						{
							$zone['time'] = '';
						}
						if ($showabbr)
						{
							$zone['abbr'] = ' (' . $c->format('T') . ')';
						}
						else
						{
							$zone['abbr'] = '';
						}
						$zone['offset'] = $z->getOffset($c);
						$data[] = $zone;
						$offset[] = $zone['offset'];
						$added[] = $zone['timezone_id'];
					}
					catch (Exception $e)
					{
						//echo $e->getMessage() . '<br />';
					}
				}
			}
		}
		array_multisort($offset, SORT_ASC, $data);
		$options = array();
		foreach ($data AS $key => $row)
		{
			$options[$row['timezone_id']] = $row['time'] . $this->timezone_offset($row['offset']) . $row['abbr'] . ' - ' . $row['timezone_id'];
		}
		$html = '<select name="' . $formfieldname . '" id="' . $fieldname . '" class="' . $cssextra . '">';
		foreach ($options AS $timezone_id => $output)
		{
			if (!empty($selected) AND $selected == $timezone_id)
			{
				$html .= '<option value="' . $timezone_id . '" selected="selected">' . $output . '</option>';
			}
			else
			{
				$html .= '<option value="' . $timezone_id . '">' . $output . '</option>';
			}
		}
                $html .= '</select>';
                return $html;
	}

	/**
        * Function to display a local time for a user based on a supplied time zone identifier
        *
        * @param       string       timezone id (default America/New_York)
        * @param       string       time format (default h:i A)
        * @param       boolean      show offset (default false)
        * @param       boolean      show timezone abbreviation
        * @param       boolean      format local time (default false)
        * @param       string       offset class color (default litegray)
        *
        * @return      string       Returns HTML representation of the local user time
        */
	function fetch_local_time($timezone_id = 'America/New_York', $timeformat = 'h:i A', $showoffset = false, $showabbrev = false, $format = false, $offsetclass = 'litegray')
	{
		$z = new DateTimeZone($timezone_id);
		$c = new DateTime(null, $z);
		$time = $c->format($timeformat);
		if ($showoffset)
		{
			if ($format)
			{
				$time .= ' <span class="' . $offsetclass . '">' . $this->timezone_offset($z->getOffset($c)) . '</span>';
			}
			else
			{
				$time .= ' ' . $this->timezone_offset($z->getOffset($c));
			}
		}
		if ($showabbrev)
		{
			if ($format)
			{
				$time .= ' <span class="' . $offsetclass . '">' . mb_strtoupper($c->format('T')) . '</span>';
			}
			else
			{
				$time .= ' ' . mb_strtoupper($c->format('T'));
			}
		}
		return $time;
	}

	/**
        * Function to fetch the offset of a date and time based on a supplied offset identifier
        *
        * @param       string       timezone offset (default false)
        *
        * @return      string       Returns timezone offset
        */
	function timezone_offset($offset)
	{
		$hours = $offset / 3600;
		$remainder = $offset % 3600;
		$sign = $hours > 0 ? '+' : '-';
		$hour = (int)abs($hours);
		$minutes = (int) abs($remainder / 60);
		if ($hour == 0 AND $minutes == 0)
		{
			$sign = ' ';
		}
		return 'GMT&nbsp;' . $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
	}
	function get_timezone_offset($remote_tz, $origin_tz = null)
	{
		if ($origin_tz === null)
		{
			if (!is_string($origin_tz = date_default_timezone_get()))
			{
				return false; // A UTC timestamp was returned -- bail out!
			}
		}
		$origin_dtz = new DateTimeZone($origin_tz);
		$remote_dtz = new DateTimeZone($remote_tz);
		$origin_dt = new DateTime("now", $origin_dtz);
		$remote_dt = new DateTime("now", $remote_dtz);
		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
		return $offset;
	}
	function fetch_timezone_offset($servertz = 'America/Toronto', $clienttz = 'America/Los_Angeles')
	{
		$offset = $this->get_timezone_offset($servertz, $clienttz);
		return $offset;
	}
	/**
        * Function to fetch the total days count of the current calendar month.
        *
        * @return      integer       Returns count of days in the current month
        */
	function calendar_days_this_month()
	{
		$days = date("t");
		return $days;
	}
	function calendar_days_for($month = '', $year = '')
	{
		return cal_days_in_month(CAL_GREGORIAN, $month, $year);
	}
	/**
        * Function to fetch the total seconds between two datetime strings.
        *
	* @param       string        date 1 (large)
	* @param       string        date 2 (small)
	*
        * @return      integer       Returns difference in seconds
        */
	function seconds_between($datetime1, $datetime2)
	{
		$diff = strtotime($datetime1) - strtotime($datetime2);
		return $diff;
	}
}
?>
