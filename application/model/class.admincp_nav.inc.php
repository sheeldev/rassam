<?php
/**
 * AdminCP left nav builder
 *
 * @package      Sheel\AdminCP\Nav
 * @version      1.0.0.0
 * @author       Sheel
 */
class admincp_nav extends admincp
{
    function print($currenturl = '')
    {
        $recentsearches = $this->recent_searches();
        $customerscount = $this->sheel->customerscount();

        $html = '<div id="NavDrawer" class="nav-drawer" define="{iLPage: new Sheel.Drawer(this)}">
        <nav role="navigation" class="draw-nav draw-nav--is-expanded" define="{iLNav: new Sheel.Navi(this)}" bind-event-mouseenter="iLNav.onMouseEnter(this)" bind-event-mouseleave="iLNav.onMouseLeave(this)" bind-class="{\'draw-nav--show-overflow\': globalSearch.expanded}">
                <div class="draw-nav__panel draw-nav__panel--primary" bind-event-mouseenter="iLNav.onMouseEnter(this)">
                        <header class="draw-nav__logo draw-nav__logo">
                                <a href="' . HTTPS_SERVER_ADMIN . '">
                                    <img src="' . $this->sheel->config['imgcdn'] . 'acp/logo.png" height="45">
                                </a>
                                <a class="btn btn--plain tooltip tooltip-right-align tooltip-bottom tooltip-bottom--light-arrow" title="Front End" href="' . HTTPS_SERVER . '" target="_blank">
                                        <span class="glyphicons glyphicons-new-window draw-icon" aria-hidden="true"></span>
                                        <span class="helper--visually-hidden">User Access</span>
                                        <div class="tooltip-container"> <span class="tooltip-label tooltip--view-website tooltip-label--light">User Access</span> </div>
                                </a>
                        </header>
                        <section id="GlobalSearch" role="search" define="{globalSearch: new Sheel.GlobalSearch(this, \'\')}" context="globalSearch" class="draw-nav__search">
                                <button type="button" class="btn draw-nav__link search" bind-event-click="toggleSearchPane()">
                                <div>
                                        <span class="glyphicons glyphicons-search draw-icon" aria-hidden="true"></span>
                                        <span class="draw-nav__text draw-nav-search__text">{_search}</span>
                                </div>
                                </button>
                                <div class="global-search__pane" id="global-search-pane" role="dialog" aria-hidden="true" tabindex="-1">
                                        <div class="global-search__header draw-grid draw-grid--vertically-centered draw-grid--no-padding">
                                                <div class="draw-grid__cell">
                                                        <div class="draw-input-wrapper global-search__input-wrapper">
                                                                <label class="draw-label helper--visually-hidden" for="global-search-input">Search. Your results will appear below as you type.</label>
                                                                <div class="draw-input--stylized">
                                                                        <span class="draw-input__add-on draw-input__add-on--before">
                                                                                <svg class="draw-icon draw-icon--16"><use xlink:href="#draw-search-16"></use></svg>
                                                                        </span>
                                                                        <input type="search" name="global-search-input" id="global-search-input" placeholder="What are you looking for?" bind-event-keydown="onSearchInputKeydown(event)" aria-controls="GlobalSearchPaneAnnounce" class="draw-input draw-input--search draw-input--invisible">
                                                                        <span class="draw-input__add-on draw-input__add-on--after">
                                                                                <i class="ico ico-16-svg ico-loading-circle global-search__loading animate animate-rotate hide" bind-show="isLoading"></i>
                                                                                <button class="global-search__clear btn btn--plain btn--icon btn--icon--tiny hide" bind-event-click="clearResults(true)" bind-class="{hide: (!searchQuery().length || isLoading) }">
                                                                                        <i class="ico ico-16-svg ico-clear-bluegray"></i>
                                                                                        <span class="helper--visually-hidden">Clear search text</span>
                                                                                </button>
                                                                        </span>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="draw-grid__cell--no-flex">
                                                        <button type="button" class="btn global-search__close btn--plain" bind-event-click="hideSearchPane()">
                                                                <svg icon_class="draw-icon--slate-lighter" aria-labelledby="draw-remove-title" role="img" class="draw-icon draw-icon--16">
                                                                    <title id="draw-remove-title">{_remove}</title>
                                                                    <use xlink:href="#draw-remove"></use>
                                                                </svg>
                                                                <span class="helper--visually-hidden">Close search dialog</span>
                                                        </button>
                                                </div>
                                        </div>
                                        <div id="GlobalSearchPaneResults" class="global-search__results" refresh="global-search-pane-results">
                                                <div class="global-search__wrapper">
                                                        <div class="global-search__body global-search__blank-slate">
                                                                <div class="global-search__blank-slate-message">
                                                                        <h1>Enter your search terms above.</h1>
                                                                        <h2>You can search for anything � settings, customers and more.</h2>
                                                                </div>
                                                                <!-- recent keywords -->
                                                                ' . $recentsearches . '
                                                                 <!-- recent keywords -->
                                                        </div>
                                                </div>
                                        </div>
                                        <div id="GlobalSearchPaneAnnounce" class="helper--visually-hidden" role="status" aria-live="polite" tabindex="-1"></div>
                                </div>
                        </section>
                        <ol class="draw-nav__list draw-nav__list--primary">
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="home" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . '">
                                                <span class="glyphicons glyphicons-home draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_home}</span>
                                        </a>
                                </li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="dashboard" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Dashboard" aria-controls="iLNav_Dashboard" href="' . HTTPS_SERVER_ADMIN . 'dashboard/">
                                                <span class="glyphicons glyphicons-dashboard draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_dashboard}</span>
                                        </a>
                                </li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="customers" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Customers" aria-controls="iLNav_Customers" href="' . HTTPS_SERVER_ADMIN . 'customers/">
                                                <span class="glyphicons glyphicons-vcard draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_customers}</span>
                                                ' . (($customerscount > 0) ? '<span class="draw-nav__badge sheelColor draw-nav__badge--adjacent-chevron" id="iLNav_Customers" title="{_customers}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="customerscount">' . $this->custom_number_format($customerscount, 1) . '</span>
                                                </span>' : '<span class="draw-nav__badge sheelColor draw-nav__badge--adjacent-chevron" id="iLNav_Customers" title="{_customers}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="customerscount">' . $this->custom_number_format($customerscount, 1) . '</span>
                                                </span>') . '
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="users" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . 'users/">
                                                <span class="glyphicons glyphicons-user draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_users}</span>
                                                <span class="draw-nav__badge sheelColor draw-nav__badge--adjacent-chevron" id="iLNav_Users" title="{users}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="usercount">' . $this->custom_number_format($this->sheel->usercount()) . '</span>
                                                </span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="reports" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . 'reports/">
                                                <span class="glyphicons glyphicons-charts draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">Reports</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>

                                <li class="draw-nav__item--spacer"></li>
                                ' . ((isset($this->sheel->config['stores']) AND $this->sheel->config['stores']) ? '<li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="stores" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Stores" aria-controls="iLNav_Stores" href="' . HTTPS_SERVER_ADMIN . 'stores/">
                                                <span class="glyphicons glyphicons-shop draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_stores}</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>' : '') . '
                                ' . ((isset($this->sheel->config['brands']) AND $this->sheel->config['brands']) ? '<li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="brands" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Brands" aria-controls="iLNav_Brands" href="' . HTTPS_SERVER_ADMIN . 'brands/">
                                                <span class="glyphicons glyphicons-tag draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_products} &amp; {_brands}</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>' : '') . '
                                
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="whosonline" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_WhosOnline" aria-controls="iLNav_WhosOnline" href="' . HTTPS_SERVER_ADMIN . 'sessions/">
                                            <span class="glyphicons glyphicons-door draw-icon" aria-hidden="true"></span>
                                            <span class="draw-nav__text">Who\'s Online</span>
                                            <span class="draw-nav__badge green draw-nav__badge--adjacent-chevron" id="iLNav_WhosOnline" title="Visitors online" refresh-always="">
                                                <span class="draw-nav__badge-content">' . number_format($this->sheel->admincp->members_online(false)) . '</span>
                                            </span>
                                            <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                    <use xlink:href="#draw-chevron"></use>
                                            </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item--spacer"></li>
                                
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="settings" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Settings" aria-controls="iLNav_Settings" href="' . HTTPS_SERVER_ADMIN . 'settings/">
                                                <span class="glyphicons glyphicons-settings draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_settings}</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item--spacer"></li>
                               
                                <li class="draw-nav__item--flex-spacer"></li>
                                <li class="draw-nav__item draw-nav__item--group draw-nav__item--account">
                                        <div class="draw-popover__container draw-popover__container--full-width">
                                                <button type="button" class="draw-nav__link" bind-event-click="iLNav.hover()" id="draw-popover-activator--1" aria-expanded="false" aria-haspopup="true" aria-owns="draw-popover--1" aria-controls="draw-popover--1">
                                                        <div class="draw-grid draw-grid--no-padding draw-grid--vertically-centered">
                                                                <div class="draw-grid__cell draw-grid__cell--no-flex">
                                                                        <img class="gravatar gravatar--icon draw-nav__gravatar" src="' . $this->sheel->config['imgcdn'] . 'acp/no-gravatar.png" alt=""> </div>
                                                                <div class="draw-grid__cell type--left">
                                                                        <span class="draw-nav__text">' . SITE_NAME . ' <span class="draw-nav__text--subdued">' . (isset($_SESSION['sheeldata']['user']['username']) ? $_SESSION['sheeldata']['user']['username'] : '') . '</span> </span>
                                                                </div>
                                                                <div class="draw-grid__cell draw-grid__cell--no-flex">
                                                                        <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                                                <use xlink:href="#draw-chevron"></use>
                                                                        </svg>
                                                                </div>
                                                        </div>
                                                </button>
                                                <div data-popover-horizontally-relative-to-closest=".draw-nav__panel--primary" data-popover-preferred-position="top" class="draw-popover draw-popover--half-spacing draw-popover--is-positioned-above" data-popover-css-vertical-margin="15" data-popover-css-horizontal-margin="0" data-popover-css-max-height="300" data-popover-css-max-width="10000" id="draw-popover--1" aria-labelledby="draw-popover-activator--1" aria-expanded="false" style="max-width: none; margin-right: 0px; margin-left: 0px; transform-origin: 82px calc(100% + 5px); left: 33px;">
                                                        <div class="draw-popover__tooltip" style="left: 82px;"></div>
                                                        <div class="draw-popover__content-wrapper">
                                                                <div class="draw-popover__content" style="max-height: 300px;">
                                                                        <div class="draw-popover__pane">
                                                                           
                                                                                <div class="draw-popover__section">
                                                                                        <ul class="unstyled">                                                                                                
                                                                                                <li> <a class="draw-nav__popover-link" href="' . HTTPS_SERVER_ADMIN . 'signin/signout/">{_log_out}</a> </li>
                                                                                        </ul>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </li>
                        </ol>
                </div>
                <div class="draw-nav__panel draw-nav__panel--secondary" bind-event-mouseenter="iLNav.onMouseEnter(this)">
                        <!-- customers subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="customers" id="iLNav_Customers">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Customers</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_customers" bind-event-click="" allow-default="1" href="customers/">{_customers}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_bc" bind-event-click="" allow-default="1" href="customers/bc/">{_bc_customers_list}</a> </li>
                        </ol>
                        <!-- customers subnav -->
                        <!-- users subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="users" id="iLNav_Users">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Users</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="users" bind-event-click="" allow-default="1" href="users/">Users</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="users_bulkmailer" bind-event-click="" allow-default="1" href="users/bulkmailer/">Bulk Mailer</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="users_roles" bind-event-click="" allow-default="1" href="users/roles/">Roles</a> </li>
                        </ol>
                        <!-- users subnav -->
                        <!-- reports subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="reports" id="iLNav_Reports">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Reports</h2>
                                </li>
                        </ol>
                        <!-- reports subnav -->

                        <!-- settings subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="settings" id="iLNav_Settings">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Settings</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_general" bind-event-click="" allow-default="1" href="settings/">General</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_companies" bind-event-click="" allow-default="1" href="settings/companies/">Companies</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_branding" bind-event-click="" allow-default="1" href="settings/branding/">Branding</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_locale" bind-event-click="" allow-default="1" href="settings/locale/">Locale</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_mail" bind-event-click="" allow-default="1" href="settings/mail/">Mail / SMTP</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_registration" bind-event-click="" allow-default="1" href="settings/registration/">Registration</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_security" bind-event-click="" allow-default="1" href="settings/security/">Security</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_optimization" bind-event-click="" allow-default="1" href="settings/optimization/">Optimization</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_session" bind-event-click="" allow-default="1" href="settings/session/">Session</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_emails" bind-event-click="" allow-default="1" href="settings/emails/">Email Templates</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_currency" bind-event-click="" allow-default="1" href="settings/currency/">Currency</a> </li>
                                
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_pages" bind-event-click="" allow-default="1" href="settings/pages/">Pages &amp; Content</a> </li>
                                <li class="draw-nav__item"> <a data-no-turbolink="true" class="draw-nav__link" data-nav-sub-item="settings_heros" bind-event-click="" allow-default="1" href="settings/heros/">Hero Designer</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_memberships" bind-event-click="" allow-default="1" href="settings/memberships/">Memberships</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_motd" bind-event-click="" allow-default="1" href="settings/motd/">Message of the Day</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_announcements" bind-event-click="" allow-default="1" href="settings/announcements/">Announcements</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_attachments" bind-event-click="" allow-default="1" href="settings/photos/">Photos &amp; Attachments</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_languages" bind-event-click="" allow-default="1" href="settings/languages/">Language Manager</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_locations" bind-event-click="" allow-default="1" href="settings/locations/">Locations Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_sizingrules" bind-event-click="" allow-default="1" href="settings/sizingrules/">Sizing Rules</a> </li>
                                
                                <li class="draw-nav__item--spacer"></li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_bc" bind-event-click="" allow-default="1" href="settings/bc/">Business Central</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_api" bind-event-click="" allow-default="1" href="settings/api/">API Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_automation" bind-event-click="" allow-default="1" href="settings/automation/">Automation</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_diagnosis" bind-event-click="" allow-default="1" href="settings/diagnosis/">App Diagnosis</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_serverinfo" bind-event-click="" allow-default="1" href="settings/serverinfo/">App &amp; Server Specs</a> </li>
                        </ol>
                        <!-- settings subnav -->
                </div>
                <div class="draw-nav__panel draw-nav__panel--icon-overlay" bind-event-click="iLNav.setState(\'default\')"></div>
        </nav>
</div>';
        return $html;
    }
    function recent_searches()
    {
        $html = '';
        if (!empty($_COOKIE[COOKIE_PREFIX . 'admin_searches']))
        {
            $html = '<div class="global-search__recent"><h4 class="global-search__list-heading">Recent searches</h4><ul class="global-search__list">';
            $json = urldecode($_COOKIE[COOKIE_PREFIX . 'admin_searches']);
            $json = json_decode($json, true);
            foreach ($json AS $key => $keyword)
            {
                $html .= '<li class="global-search__list-item"> <a href="javascript:;" bind-event-click="setSearch(\'' . o($keyword) . '\')">' . o($keyword) . '</a> </li>';
            }
            $html .= '</ul></div>';
        }
        return $html;
    }
}
?>