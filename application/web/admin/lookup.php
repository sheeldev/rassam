<?php
define('LOCATION', 'admin');
if (isset($match['params']))
{
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['areatitle'] = 'Admin Panel Search';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - Admin Panel Search';
if (!empty($_SESSION['sheeldata']['user']['userid']) AND $_SESSION['sheeldata']['user']['userid'] > 0 AND $_SESSION['sheeldata']['user']['isadmin'] == '1')
{
	$count = 0;
	$results = '';
	$slng = ((isset($_SESSION['sheeldata']['user']['slng'])) ? $_SESSION['sheeldata']['user']['slng'] : 'eng');
	$found_settings = false;
	$found_customers = false;




	$var = ((isset($sheel->GPC['q'])) ? $sheel->db->escape_string($sheel->GPC['q']) : '');
	$output = array();
    $sheel->GPC['models'] = ((!isset($sheel->GPC['models'])) ? 'all' : $sheel->GPC['models']);
	$output['settings'] = '';
	$output['customers'] = '';
	if (!empty($var))
	{ // configuration settings
        
		$sql = $sheel->db->query("
			SELECT c.name, c.configgroup
			FROM " . DB_PREFIX . "configuration c
			LEFT JOIN " . DB_PREFIX . "language_phrases l ON (c.name = substr(l.varname, 2, (length(l.varname)-6)))
			WHERE l.phrasegroup = 'admincp_configuration'
				AND l.text_$slng LIKE '%$var%'
			GROUP BY c.name
		");
		if ($sheel->db->num_rows($sql) > 0)
		{
            $i=0;
            $varname = array();
			$configgroup =  $result = '';
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				if (!isset($varname[$res['configgroup']]))
				{
					$varname[$res['configgroup']] = "'" . $res['name'] . "'";
				}
				else
				{
					$varname[$res['configgroup']] .= empty($varname[$res['configgroup']]) ? "'" . $res['name'] . "'" : ", '" . $res['name'] . "'";
				}
			}
			if (is_array($varname))
			{
				foreach ($varname AS $key => $value)
				{
					if (!empty($value))
					{
						$count++;
						$result .= '<div class="global-search__result draw-grid"><div class="draw-grid__cell"><div class="global-search__result-body">' . $sheel->admincp->construct_admin_input($key, HTTPS_SERVER_ADMIN . 'settings/?note=success', $value, '', 'configuration_groups', 'configuration', true) . '</div></div></div>';
					}
				}
				$found_settings = true;
				$sheel->template->templateregistry['result'] = $result;
				$sheel->template->parse_template_collapsables('result');
				$result = $sheel->template->parse_template_phrases('result');
				$results = $result;
				$results .= '<div id="pagination-links"></div>';
				$output['settings'] = $results;
				$output['count']['settings'] = $count;
				unset($results);
			}
		}
	}
	if (!empty($var) AND strlen($var) > 2)
	{ // customers
		$count = 0;
		$sql = $sheel->db->query("
			SELECT customer_id, customer_ref, customername, customername2, customerabout, logo, date_added, customerdescription, available_balance, total_balance, status
			FROM " . DB_PREFIX . "customers
			WHERE (customername LIKE '%$var%' OR customer_ref LIKE '%$var%' OR customername2 LIKE '%$var%' OR customerabout LIKE '%$var%')
			LIMIT 50
		");
		if ($sheel->db->num_rows($sql) > 0)
		{
			$found_customers = true;
			$results = '';
			while ($res = $sheel->db->fetch_array($sql, DB_ASSOC))
			{
				$count++;
				
				$results .= '<div class="global-search__result draw-grid">
				<!-- picture -->
				<div class="draw-grid__cell draw-grid__cell--no-flex global-search__result__image-container">
				    <a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $res['customer_id'] . '/">
					<img title="' . o($res['customer_ref']) . '" class="global-search__result__image" src="' . ((!empty($res['logo'])) ? HTTPS_SERVER . 'application/uploads/attachments/customers/' . $res['logo']  : $sheel->config['imgcdn'] . 'v5/img_nophoto.png') . '" alt="thumb" />
				    </a>
				</div>
				<!-- picture -->
				<div class="draw-grid__cell">
				    <div class="global-search__result__heading">
					<a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $res['customer_id'] . '/"><strong>' . $res['customername'] . '</strong></a>
				    </div>
				    <div class="global-search__result-body">
					<p>Customer Reference: <strong>' . $res['customer_ref'] . '</strong></p>
					<p>Added since: ' . $sheel->common->print_date($res['date_added'], 'F j, Y', 0, 0) . '</p>
					<p class="global-search__first-match">Customer About: ' . $res['customerabout'] . '</p>
					<br />
					<div class="global-search__result-body">
					    <h4 class="global-search__list-heading">Information</h4>
					    <ul class="global-search__list">
						<li class="global-search__list-item"><a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $res['customer_id'] . '/">Status <span class="type--subdued">' . $res['status'] . '</span></a> </li>
						<li class="global-search__list-item"><a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $res['customer_id'] . '/">Description <span class="type--subdued">' . $res['customerdescription']. '</span></a> </li>
						<li class="global-search__list-item"><a href="' . HTTPS_SERVER_ADMIN . 'customers/update/' . $res['customer_id'] . '/">Account Balance <span class="type--subdued">' . $sheel->currency->format($res['available_balance']) . '</span></a> </li>
						</ul>
					</div>
				    </div>
				</div>
				</div>
				<div id="pagination-links"></div>';
			}
			$output['customers'] = $results;
			$output['count']['customers'] = $count;
			unset($results);
		}
	}

	$output['all'] = $output['settings'] . $output['customers'] ;
	$output['count']['all'] = ($output['count']['settings'] +$output['count']['customers']);

?>

<?php
	if (empty($output['all']) AND empty($sheel->GPC['q']))
	{
		$recentsearches = $sheel->admincp_nav->recent_searches();
?>
<div id="GlobalSearchPaneResults" class="global-search__results draw-grid draw-grid--no-padding" refresh="global-search-pane-results">
	<div class="global-search__wrapper">
		<div class="global-search__body global-search__blank-slate">
			<div class="global-search__blank-slate-message">
				<h1>Enter your search terms above.</h1>
				<h2>You can search for anything – settings, customers and more.</h2>
			</div>
			<!-- recent keywords -->
			<?php echo $recentsearches; ?>
			<!-- recent keywords -->
		</div>
	</div>
</div>
<?php
	}
	else
	{
?>
<div id="GlobalSearchPaneResults" class="global-search__results draw-grid draw-grid--no-padding" refresh="global-search-pane-results">
    <div class="draw-grid__cell">

        <!-- what we found -->
	<div>
            <ul class="draw-tab__list" role="tablist">
                <li role="presentation""><a class="draw-tab<?php echo ((!isset($sheel->GPC['models']) OR (isset($sheel->GPC['models']) AND $sheel->GPC['models'] == 'all')) ? ' draw-tab--is-active' : ''); ?>" tabindex="0" aria-controls="NextTabPanel1" aria-selected="true" href="javascript:;" bind-event-click="setModel('all')">All</a></li>
                <li role="presentation" class="<?php echo ((empty($sheel->GPC['q']) OR !$found_customers) ? 'hide' : ''); ?>"><a class="draw-tab<?php echo ((isset($sheel->GPC['models']) AND $sheel->GPC['models'] == 'customers') ? ' draw-tab--is-active' : ''); ?>" tabindex="-1" aria-controls="NextTabPanel2" aria-selected="false" href="javascript:;" bind-event-click="setModel('customers')">Customers</a></li>
                <li role="presentation" class="<?php echo ((empty($sheel->GPC['q']) OR !$found_settings) ? 'hide' : ''); ?>"><a class="draw-tab<?php echo ((isset($sheel->GPC['models']) AND $sheel->GPC['models'] == 'settings') ? ' draw-tab--is-active' : ''); ?>" tabindex="-1" aria-controls="NextTabPanel5" aria-selected="false" href="javascript:;" bind-event-click="setModel('settings')">Settings</a></li>
		
		<li class="draw-tab__list__disclosure-item dropdown-container">
                        <span class="draw-tab draw-tab--disclosure" tabindex="-1" data-dropdown="~ .dropdown" aria-selected="true">
                                <i class="ico ico-16-svg ico-chevron-down-blue"></i>
                        </span>
                        <div class="dropdown">
                                <ul class="draw-tab__list--vertical" role="tablist"></ul>
                        </div>
                </li>
            </ul>
        </div>

        <!-- what we found -->
        <div id="global-search-results" class="global-search__wrapper" data-total-results="1">

            <div class="global-search__body">

		    <?php echo $output[$sheel->GPC['models']]; ?>


                <div class="draw-card__section">
                    <div id="table_heading" refresh="global-search-results">
                        <p class="global-search__results__count type--subdued"><?php echo ((empty($sheel->GPC['q'])) ? 'Let\'s find what you\'re looking for.  Enter keywords to begin.' : 'Viewing ' . $output['count'][$sheel->GPC['models']] . (($output['count'][$sheel->GPC['models']] == 1) ? ' result' : ' results') . '.'); ?></p>
                    </div>
                </div>
            </div>

            <div class="global-search__footer">
                <div class="global-search__tip<?php echo ((empty($sheel->GPC['q'])) ? ' hide' : ''); ?>">
                    <div class="draw-grid draw-grid--vertically-centered draw-grid--no-outside-padding">
                        <div class="draw-grid__cell--no-flex">
                            <i class="ico draw-icon--20 draw-icon--info-blue"></i>
                        </div>
                        <div class="draw-grid__cell">
			    <?php echo ((empty($sheel->GPC['q'])) ? '' : '<p>Didn\'t find what you\'re looking for? </p>'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<?php
}
}
?>
