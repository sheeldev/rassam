<?php
/**
* Template debug helper functions for the footer stats display.
*
* @package      sheel\Template\Debug
* @version      6.0.0.622
* @author       sheel
*/
class template_debug extends template
{
	function print($node = '')
	{
		$html = '';
		if (defined('DEBUG_FOOTER') AND DEBUG_FOOTER)
		{
			if ($node == 'main')
			{
				$dbexplain = $classlist = $functionlist = $sessionlist = $templatelist = $requestlist = $routelist = '';
				$functioncount = $classcount = $sessioncount = $requestcount = 0;
				if (isset($GLOBALS['DEBUG']['FUNCTION']))
				{
					foreach ($GLOBALS['DEBUG']['FUNCTION'] AS $key => $value)
					{
						$functioncount++;
						$functionlist .= '<li class="phpdebugbar-widgets-list-item">
<span class="sql" title="(#' . $key . ') ' . o(trim($value['text'])) . '">' . $this->sheel->shorten(trim($value['text']), 200) . '</span>
<span class="phpdebugbar-widgets-param-count" title="Caller">' . $value['caller'] . '</span>
<span class="phpdebugbar-widgets-param-count" title="Memory peak">' . $value['memorypeak'] . ' / ' . $value['memoryusage'] . ' (usage)</span>
<span class="phpdebugbar-widgets-param-count" title="Duration">' . $value['timer'] . 'ms</span></li>' . "\n";
					}
				}
				if (isset($GLOBALS['DEBUG']['CLASS']))
				{
					foreach ($GLOBALS['DEBUG']['CLASS'] AS $key => $value)
					{
						$classcount++;
						$classlist .= '<li class="phpdebugbar-widgets-list-item">
<span class="sql" title="(#' . $key . ') ' . o(trim($value['text'])) . '">' . $this->sheel->shorten(trim($value['text']), 200) . '</span>
<span class="phpdebugbar-widgets-param-count" title="Caller">' . $value['caller'] . '</span>
<span class="phpdebugbar-widgets-param-count" title="Memory peak">' . $value['memorypeak'] . ' / ' . $value['memoryusage'] . ' (usage)</span>
<span class="phpdebugbar-widgets-param-count" title="Duration">' . $value['timer'] . 'ms</span></li>' . "\n";

					}
				}
				$sessionlist .= '<dt class="phpdebugbar-widgets-key"><span title="">id</span></dt><dd class="phpdebugbar-widgets-value">' . session_id() . '</dd>' . "\n";
				foreach ($_SESSION['sheeldata']['user'] AS $key => $value)
				{
					$sessioncount++;
					if (is_array($value))
					{
						foreach ($value AS $key2 => $value2)
						{
							if (!empty($key2) AND !empty($value2))
							{
								$sessionlist .= '<dt class="phpdebugbar-widgets-key"><span title="' . $key2 . '">' . $key2 . '</span></dt><dd class="phpdebugbar-widgets-value">' . $value2 . '</dd>' . "\n";
							}
						}
					}
					else
					{
						$sessionlist .= '<dt class="phpdebugbar-widgets-key"><span title="' . $key . '">' . $key . '</span></dt><dd class="phpdebugbar-widgets-value">' . $value . '</dd>' . "\n";
					}
				}
				$routelist .= '
				<dt class="phpdebugbar-widgets-key">
					<span title="uri">uri</span>
				</dt>
				<dd class="phpdebugbar-widgets-value">GET ' . $_SERVER['REQUEST_URI'] . '</dd>
				<dt class="phpdebugbar-widgets-key">
					<span title="query string">query string</span>
				</dt>
				<dd class="phpdebugbar-widgets-value">' . $_SERVER['QUERY_STRING'] . '</dd>' . "\n";
				foreach ($this->template_views AS $view)
				{
					$templatelist .= '<li class="phpdebugbar-widgets-list-item">
	<span class="phpdebugbar-widgets-name">' . $view . '</span>
</li>';
				}
				foreach ($this->sheel->GPC AS $key => $value)
				{
					$requestcount++;
					$requestlist .= '<dt class="phpdebugbar-widgets-key"><span title="' . $key . '">' . $key . '</span></dt><dd class="phpdebugbar-widgets-value">' . ((is_array($value)) ? $this->array_recursive($value) : $value) . '</dd>' . "\n";
				}
				$html .= '<div class="phpdebugbar phpdebugbar-minimized">
    <div class="phpdebugbar-header">
        <div class="phpdebugbar-header-left">
		<a id="debug_classes_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-list-alt"></i>
			<span class="phpdebugbar-text">Classes</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . $classcount . '</span>
		</a>
		<a id="debug_functions_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-list-alt"></i>
			<span class="phpdebugbar-text">Functions</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . $functioncount . '</span>
		</a>
		<a id="debug_templates_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-leaf"></i>
			<span class="phpdebugbar-text">Views</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . $this->template_load_count . '</span>
		</a>
		<a id="debug_routes_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-share"></i>
			<span class="phpdebugbar-text">Route</span>
			<span class="phpdebugbar-badge" style="display: inline;">1</span>
		</a>
		' . ((!empty($_SESSION['sheeldata']['user']['isadmin']) AND $_SESSION['sheeldata']['user']['isadmin']) ? '<a id="debug_sql_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-inbox"></i>
			<span class="phpdebugbar-text">Queries</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . $this->sheel->db->query_count . '</span>
		</a>' : '') . '
		<a id="debug_session_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-archive"></i>
			<span class="phpdebugbar-text">Session</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . count($_SESSION['sheeldata']['user']) . '</span>
		</a>
		<a id="debug_requests_tab" href="javascript:" class="phpdebugbar-tab">
			<i class="fa fa-tags"></i>
			<span class="phpdebugbar-text">Request</span>
			<span class="phpdebugbar-badge" style="display: inline;">' . $requestcount . '</span>
		</a>
        </div>
        <div class="phpdebugbar-header-right">
		<a href="javascript:" class="phpdebugbar-close-btn"></a>
		<span class="phpdebugbar-indicator">

			<span class="phpdebugbar-text">PHP ' . PHP_VERSION . '</span>
			<span class="phpdebugbar-tooltip">PHP Version</span>
		</span>
		<span class="phpdebugbar-indicator">
			<span class="phpdebugbar-text">sheel ' . $this->sheel->config['version'] . '.' . $this->sheel->config['build'] . '.' . $this->sheel->config['current_sql_version'] . '</span>
			<span class="phpdebugbar-tooltip">sheel Software Version</span>
		</span>
		<span class="phpdebugbar-indicator">
			<i class="fa fa-clock-o"></i>
			<span class="phpdebugbar-text">' . $this->runtime . 'ms</span>
			<span class="phpdebugbar-tooltip">Request Duration</span>
		</span>
		<span class="phpdebugbar-indicator">
			<i class="fa fa-cogs"></i>
			<span class="phpdebugbar-text">' . $this->sheel->attachment->print_filesize(memory_get_peak_usage(false)) . '</span>
			<span class="phpdebugbar-tooltip">Total Memory Usage</span>
		</span>
		<span class="phpdebugbar-indicator">
			<i class="fa fa-share"></i>
			<span class="phpdebugbar-text">' . $this->sheel->template->templateregistry['currentview'] . '</span>
			<span class="phpdebugbar-tooltip">Main Template View</span>
		</span>
		<span class="phpdebugbar-indicator">
			<i class="fa fa-share"></i>
			<span class="phpdebugbar-text" title="' . o($_SERVER['REQUEST_URI']) . '">' . $_SERVER['REQUEST_METHOD'] . ' ' . $this->sheel->shorten($_SERVER['REQUEST_URI'], 40) . '</span>
			<span class="phpdebugbar-tooltip">Route</span>
		</span>
        </div>
    </div>
    <div id="debugbody" class="phpdebugbar-body" style="height: 300px; display: none;">
	<div id="debug_templates" class="phpdebugbar-panel phpdebugbar-active">
            <div class="phpdebugbar-widgets-templates">
                <div class="phpdebugbar-widgets-status"><span>' . $this->template_load_count . ' template views were rendered</span>
                </div>
                <ul class="phpdebugbar-widgets-list">
			' . $templatelist . '
                </ul>
            </div>
        </div>
        <div id="debug_routes" class="phpdebugbar-panel">
            <dl class="phpdebugbar-widgets-kvlist phpdebugbar-widgets-varlist">
		' . $routelist . '
            </dl>
        </div>
        ' . ((!empty($_SESSION['sheeldata']['user']['isadmin']) AND $_SESSION['sheeldata']['user']['isadmin']) ? '<div id="debug_sql" class="phpdebugbar-panel">
            <div class="phpdebugbar-widgets-templates">
                <div class="phpdebugbar-widgets-status"><span>' . $this->sheel->db->query_count . ' statements were executed</span><span title="Accumulated duration" class="phpdebugbar-widgets-duration">' . $this->sheel->db->ttquery . 'μs</span>
                </div>
                <div class="phpdebugbar-widgets-toolbar"></div>
                <ul class="phpdebugbar-widgets-list">
			' . $this->sheel->db->explain . '
		</ul>
            </div>
        </div>' : '') . '
        <div id="debug_classes" class="phpdebugbar-panel">
            <div class="phpdebugbar-widgets-templates">
                <div class="phpdebugbar-widgets-status"><span>' . $classcount . ' classes were loaded</span><span title="Accumulated duration" class="phpdebugbar-widgets-duration">0μs</span></div>
                <div class="phpdebugbar-widgets-toolbar"></div>
                <ul class="phpdebugbar-widgets-list">
			' . $classlist . '
		</ul>
            </div>
        </div>
	<div id="debug_functions" class="phpdebugbar-panel">
            <div class="phpdebugbar-widgets-templates">
                <div class="phpdebugbar-widgets-status"><span>' . $functioncount . ' functions were loaded</span><span title="Accumulated duration" class="phpdebugbar-widgets-duration">0μs</span>
                </div>
                <div class="phpdebugbar-widgets-toolbar"></div>
                <ul class="phpdebugbar-widgets-list">
			' . $functionlist . '
		</ul>
            </div>
        </div>

        <div id="debug_session" class="phpdebugbar-panel">
            <dl class="phpdebugbar-widgets-kvlist phpdebugbar-widgets-varlist">
		' . $sessionlist . '
            </dl>
        </div>

	<div id="debug_requests" class="phpdebugbar-panel">
            <dl class="phpdebugbar-widgets-kvlist phpdebugbar-widgets-varlist">
		' . $requestlist . '
            </dl>
        </div>

    </div>
</div>';
			}
		}
		return $html;
	}
}
?>
