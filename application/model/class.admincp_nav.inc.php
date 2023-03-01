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
        //$ordercount = $this->sheel->ordercount();
        $companiescount = $this->sheel->companiescount();
        //$categorycount = $this->sheel->categories->count();
        //$installedapplinks = $this->sheel->admincp_products->fetch_installed_apps();
        $ordercount = '0';
        $categorycount = '0';
        $installedapplinks = '0';
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
                                                                        <h2>You can search for anything � products, orders, customers and more.</h2>
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
                                        <a class="draw-nav__link" data-nav-section="companies" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Companies" aria-controls="iLNav_Companies" href="' . HTTPS_SERVER_ADMIN . 'companies/">
                                                <span class="glyphicons glyphicons-bank draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_companies}</span>
                                                ' . (($companiescount > 0) ? '<span class="draw-nav__badge sheelColor draw-nav__badge--adjacent-chevron" id="iLNav_Companies" title="{_companies}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="companiescount">' . $this->custom_number_format($companiescount, 1) . '</span>
                                                </span>' : '<span class="draw-nav__badge sheelColor draw-nav__badge--adjacent-chevron" id="iLNav_Companies" title="{_companies}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="companiescount">' . $this->custom_number_format($companiescount, 1) . '</span>
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
                                s
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="orders" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Orders" aria-controls="iLNav_Orders" href="' . HTTPS_SERVER_ADMIN . 'orders/">
                                                <span class="glyphicons glyphicons-credit-card draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_orders}</span>
                                                ' . (($ordercount > 0) ? '<span class="draw-nav__badge green draw-nav__badge--adjacent-chevron" id="iLNav_Orders" title="{_orders}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="ordercount">' . $this->custom_number_format($ordercount, 1) . '</span>
                                                </span>' : '') . '
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>

                            
                                
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="accounting" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . 'accounting/">
                                                <span class="glyphicons glyphicons-bank draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_accounting}</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
                                <!--<li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="reports" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . 'reports/">
                                                <span class="glyphicons glyphicons-charts draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">Reports</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>-->
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="categories" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER_ADMIN . 'categories/">
                                                <span class="glyphicons glyphicons-tree-structure draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">{_categories}</span>
                                                <span class="draw-nav__badge purple draw-nav__badge--adjacent-chevron" id="iLNav_Categories" title="{_categories}" refresh-always="">
                                                    <span class="draw-nav__badge-content" id="usercount">' . $this->custom_number_format($categorycount, 0) . '</span>
                                                </span>
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
                                        <a class="draw-nav__link" data-nav-section="marketplace" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Marketplace" aria-controls="iLNav_Marketplace" href="' . HTTPS_SERVER_ADMIN . 'marketplace/">
                                                <span class="glyphicons glyphicons-globe draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">Marketplace</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="whosonline" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_WhosOnline" aria-controls="iLNav_WhosOnline" href="' . HTTPS_SERVER_ADMIN . 'sessions/">
                                            <span class="glyphicons glyphicons-door draw-icon" aria-hidden="true"></span>
                                            <span class="draw-nav__text">Who\'s Online</span>
                                            <span class="draw-nav__badge orange draw-nav__badge--adjacent-chevron" id="iLNav_WhosOnline" title="Visitors online" refresh-always="">
                                                <span class="draw-nav__badge-content">' . number_format($this->sheel->admincp->members_online(false)) . '</span>
                                            </span>
                                            <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                    <use xlink:href="#draw-chevron"></use>
                                            </svg>
                                        </a>
                                </li>
                                <li class="draw-nav__item--spacer"></li>
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="apps" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_Apps" aria-controls="iLNav_Apps" href="' . HTTPS_SERVER_ADMIN . 'apps/">
                                                <span class="glyphicons glyphicons-electrical-plug draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">Apps</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
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
                                <li class="draw-nav__item">
                                        <a class="draw-nav__link" data-nav-section="licenseinfo" bind-event-click="" allow-default="1" data-secondary-nav-id="#iLNav_License" aria-controls="iLNav_License" href="' . HTTPS_SERVER_ADMIN . 'settings/license/">
                                                <span class="glyphicons glyphicons-info-sign draw-icon" aria-hidden="true"></span>
                                                <span class="draw-nav__text">License & Info</span>
                                                <svg class="draw-icon draw-icon--16 draw-nav__chevron draw-icon--no-nudge">
                                                        <use xlink:href="#draw-chevron"></use>
                                                </svg>
                                        </a>
                                </li>
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
                        <!-- orders subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="orders" id="iLNav_Orders">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Orders</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="orders_orders" bind-event-click="" allow-default="1" href="orders/">Orders</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="orders_pending" bind-event-click="" allow-default="1" href="orders/checkouts/">Checkouts Pending</a> </li>
                        </ol>
                        <!-- orders subnav -->
                        <!-- companies subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="companies" id="iLNav_Companies">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Companies</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="companies_companies" bind-event-click="" allow-default="1" href="companies/">{_companies}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="companies_item" bind-event-click="" allow-default="1" href="companies/">{_add_item}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="companies_item_upload" bind-event-click="" allow-default="1" href="companies/items/bulk/">{_add_item_via_upload}</a> </li>
                        </ol>
                        <!-- companies subnav -->
                        <!-- products subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="products" id="iLNav_Products">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Products</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="products_brand" bind-event-click="" allow-default="1" href="products/brands/">Brands</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="products_addbrand" bind-event-click="" allow-default="1" href="products/brands/add/">Add Brand</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="products_addproduct" bind-event-click="" allow-default="1" href="products/product/add/">Add Product</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="inventory" bind-event-click="" allow-default="1" href="products/inventory/">Inventory</a> </li>
                        </ol>
                        <!-- products subnav -->
                        <!-- customers subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="customers" id="iLNav_Customers">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Customers</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers" bind-event-click="" allow-default="1" href="customers/">Customers</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_bulkmailer" bind-event-click="" allow-default="1" href="customers/bulkmailer/">Bulk Mailer</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_violation" bind-event-click="" allow-default="1" href="customers/violations/">Violation Reports</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_questions" bind-event-click="" allow-default="1" href="customers/questions/">Profile Questions</a> </li>
                                <!--<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="customers_audit" bind-event-click="" allow-default="1" href="customers/audit/">Audit Log</a> </li>-->
                        </ol>
                        <!-- customers subnav -->
                        <!-- accounting subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="accounting" id="iLNav_Accounting">
                                <li class="draw-nav__item draw-nav__item--header draw-nav__item--view-channel">
                                        <h2 class="draw-heading--callout">{_accounting}</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_overview" bind-event-click="" allow-default="1" href="accounting/">{_overview}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_currency" bind-event-click="" allow-default="1" href="accounting/currency/">{_currency_manager}</a> </li>
                                <!--<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_statements" bind-event-click="" allow-default="1" href="accounting/statements/">{_statements_manager}</a> </li>-->
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_invoices" bind-event-click="" allow-default="1" href="accounting/invoices/">{_billing_manager}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_recurring" bind-event-click="" allow-default="1" href="accounting/recurring/">{_recurring_manager}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_escrow" bind-event-click="" allow-default="1" href="accounting/escrow/">{_escrow_manager}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_deposits" bind-event-click="" allow-default="1" href="accounting/deposits/">{_deposits_manager}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_withdraws" bind-event-click="" allow-default="1" href="accounting/withdraws/">{_withdrawal_manager}</a> </li>
                                <li class="draw-nav__item--spacer"></li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_creditcards" bind-event-click="" allow-default="1" href="accounting/creditcards/">{_credit_cards_manager}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="accounting_bankaccounts" bind-event-click="" allow-default="1" href="accounting/bankaccounts/">{_bank_accounts_manager}</a> </li>
                        </ol>
                        <!-- accounting subnav -->
                        <!-- marketplace subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="marketplace" id="iLNav_Marketplace">
                                <li class="draw-nav__item draw-nav__item--header draw-nav__item--view-channel">
                                        <h2 class="draw-heading--callout">Marketplace</h2>
                                        <a class="btn btn--plain tooltip tooltip-right-align tooltip-bottom tooltip-bottom--light-arrow" target="_blank" title="Front End" href="' . HTTPS_SERVER . '">
                                                <span class="glyphicons glyphicons-new-window draw-icon" aria-hidden="true"></span>
                                                <div class="tooltip-container"> <span class="tooltip-label tooltip--view-website tooltip-label--light">View your marketplace</span> </div>
                                        </a>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_overview" bind-event-click="" allow-default="1" href="marketplace/">Overview</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_pages" bind-event-click="" allow-default="1" href="marketplace/pages/">Pages &amp; Content</a> </li>
                                <!--<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_blocks" bind-event-click="" allow-default="1" href="marketplace/blocks/">HTML Blocks</a> </li>-->
                                <li class="draw-nav__item"> <a data-no-turbolink="true" class="draw-nav__link" data-nav-sub-item="marketplace_heros" bind-event-click="" allow-default="1" href="marketplace/heros/">Hero Designer</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_plans" bind-event-click="" allow-default="1" href="marketplace/plans/">Membership Plans</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_fees" bind-event-click="" allow-default="1" href="marketplace/fees/">Fees &amp; Upsell</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_motd" bind-event-click="" allow-default="1" href="marketplace/motd/">Message of the Day</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_announcements" bind-event-click="" allow-default="1" href="marketplace/announcements/">Annoucements</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_listings" bind-event-click="" allow-default="1" href="marketplace/listings/" data-turbolinks-track="reload">Listings Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_attachments" bind-event-click="" allow-default="1" href="marketplace/attachments/">Attachment Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_bids" bind-event-click="" allow-default="1" href="marketplace/bids/">Bids Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_keywords" bind-event-click="" allow-default="1" href="marketplace/keywords/">Popular Keywords</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_themes" bind-event-click="" allow-default="1" href="marketplace/themes/">Theme Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_emails" bind-event-click="" allow-default="1" href="marketplace/emails/">Email Templates</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_languages" bind-event-click="" allow-default="1" href="marketplace/languages/">Language Manager</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_locations" bind-event-click="" allow-default="1" href="marketplace/locations/">Locations Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_nonprofit" bind-event-click="" allow-default="1" href="marketplace/nonprofit/">Nonprofit Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_feedback" bind-event-click="" allow-default="1" href="marketplace/feedback/">Feedback Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_api" bind-event-click="" allow-default="1" href="marketplace/api/">API Manager</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_automation" bind-event-click="" allow-default="1" href="marketplace/automation/">Automation</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="marketplace_maintenance" bind-event-click="" allow-default="1" href="marketplace/maintenance/">Maintenance Mode</a> </li>
                        </ol>
                        <!-- marketplace subnav -->
                        ' . ((isset($this->sheel->config['stores']) AND $this->sheel->config['stores']) ? '<!-- stores subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="stores" id="iLNav_Stores">
                                <li class="draw-nav__item draw-nav__item--header draw-nav__item--view-channel">
                                        <h2 class="draw-heading--callout">Stores</h2>
                                        <a class="btn btn--plain tooltip tooltip-right-align tooltip-bottom tooltip-bottom--light-arrow" target="_blank" title="View Stores Front End" href="' . HTTPS_SERVER . 'stores/">
                                                <span class="glyphicons glyphicons-new-window draw-icon" aria-hidden="true"></span>
                                                <div class="tooltip-container"> <span class="tooltip-label tooltip--view-website tooltip-label--light">View stores</span> </div>
                                        </a>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="stores_overview" bind-event-click="" allow-default="1" href="stores/">Overview</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="stores_listings" bind-event-click="" allow-default="1" href="stores/listings/">Stores</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="stores_promocodes" bind-event-click="" allow-default="1" href="stores/promocodes/">Promotional Codes</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="stores_fees" bind-event-click="" allow-default="1" href="stores/fees/">Fees &amp; Upsell</a> </li>
                        </ol>
                        <!-- stores subnav -->' : '') . '
                        ' . ((isset($this->sheel->config['brands']) AND $this->sheel->config['brands']) ? '<!-- brands subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="brands" id="iLNav_Brands">
                                <li class="draw-nav__item draw-nav__item--header draw-nav__item--view-channel">
                                        <h2 class="draw-heading--callout">{_brands}</h2>
                                        <a class="btn btn--plain tooltip tooltip-right-align tooltip-bottom tooltip-bottom--light-arrow" target="_blank" title="View Brands Front End" href="' . HTTPS_SERVER . 'b/">
                                                <span class="glyphicons glyphicons-new-window draw-icon" aria-hidden="true"></span>
                                                <div class="tooltip-container"> <span class="tooltip-label tooltip--view-website tooltip-label--light">View brands</span> </div>
                                        </a>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_overview" bind-event-click="" allow-default="1" href="brands/">{_overview}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_owners" bind-event-click="" allow-default="1" href="brands/owners/">{_owners}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_listings" bind-event-click="" allow-default="1" href="brands/listings/">{_brands}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_products" bind-event-click="" allow-default="1" href="brands/products/">{_products}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_barcodeimport" bind-event-click="" allow-default="1" href="brands/products/import/">{_products_impex}</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_barcodeimportapi" bind-event-click="" allow-default="1" href="brands/products/import/api/">UPC Import API</a> </li>
                                <!--<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_dropshipimport" bind-event-click="" allow-default="1" href="brands/products/import/dropship/">Dropship Import</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_dropshipimportapi" bind-event-click="" allow-default="1" href="brands/products/import/dropship/api/">Dropship Import API</a> </li>-->
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="brands_suggest" bind-event-click="" allow-default="1" href="' . HTTPS_SERVER . 'b/suggest/" target="_blank">{_suggest_new_brand}</a> </li>
                        </ol>
                        <!-- brands subnav -->' : '') . '
                        <!-- apps subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="apps" id="iLNav_Settings">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Applications</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link draw-nav__link--is-selected" data-nav-sub-item="apps_overview" bind-event-click="" allow-default="1" href="apps/">{_overview}</a> </li>
                                ' . $installedapplinks . '
                                <li class="draw-nav__item--spacer"></li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="app_store" bind-event-click="" allow-default="1" href="apps/store/">App Store</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="app_payments" bind-event-click="" allow-default="1" href="apps/payments/">App Payments</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="app_upload" bind-event-click="" allow-default="1" href="apps/upload/">App Install</a> </li>
                        </ol>
                        <!-- apps subnav -->
                        <!-- settings subnav -->
                        <ol class=" draw-nav__list draw-nav__list--secondary" data-nav-section="settings" id="iLNav_Settings">
                                <li class="draw-nav__item draw-nav__item--header">
                                        <h2 class="draw-heading--callout">Settings</h2>
                                </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_general" bind-event-click="" allow-default="1" href="settings/">General</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_branding" bind-event-click="" allow-default="1" href="settings/branding/">Branding</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_locale" bind-event-click="" allow-default="1" href="settings/locale/">Locale</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_mail" bind-event-click="" allow-default="1" href="settings/mail/">Mail / SMTP</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_currency" bind-event-click="" allow-default="1" href="settings/currency/">Currency</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_invoice" bind-event-click="" allow-default="1" href="settings/invoice/">Invoice &amp; Transaction</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_payment" bind-event-click="" allow-default="1" href="settings/payment/">Payments &amp; APIs</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_tax" bind-event-click="" allow-default="1" href="settings/tax/">Taxes</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_registration" bind-event-click="" allow-default="1" href="settings/registration/">Registration &amp; SSO</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_escrow" bind-event-click="" allow-default="1" href="settings/escrow/">Escrow</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_listings" bind-event-click="" allow-default="1" href="settings/listings/">Selling &amp; Bulk CSV</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_bidding" bind-event-click="" allow-default="1" href="settings/bidding/">Bidding</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_feedback" bind-event-click="" allow-default="1" href="settings/feedback/">Feedback</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_pmb" bind-event-click="" allow-default="1" href="settings/pmb/">Private Message</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_censor" bind-event-click="" allow-default="1" href="settings/censor/">Censor</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_blacklist" bind-event-click="" allow-default="1" href="settings/blacklist/">Blacklist</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_categories" bind-event-click="" allow-default="1" href="settings/categories/">Categories</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_attachments" bind-event-click="" allow-default="1" href="settings/attachments/">Photos &amp; Attachments</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_seo" bind-event-click="" allow-default="1" href="settings/seo/">SEO</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_search" bind-event-click="" allow-default="1" href="settings/search/">Search</a> </li>
				<li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_shipping" bind-event-click="" allow-default="1" href="settings/shipping/">Shipping &amp; APIs</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_security" bind-event-click="" allow-default="1" href="settings/security/">Security</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_distance" bind-event-click="" allow-default="1" href="settings/distance/">Distance &amp; GeoData</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_session" bind-event-click="" allow-default="1" href="settings/session/">Session</a> </li>
                                <li class="draw-nav__item--spacer"></li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_license" bind-event-click="" allow-default="1" href="settings/license/">License Info</a> </li>
                                <li class="draw-nav__item"> <a class="draw-nav__link" data-nav-sub-item="settings_updates" bind-event-click="" allow-default="1" href="settings/updates/">Automatic Updates</a> </li>
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