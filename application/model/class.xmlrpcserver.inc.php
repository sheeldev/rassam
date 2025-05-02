<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Single Seller Business 6.0.0 Build 622
|| # -------------------------------------------------------------------- # ||
|| # Customer License # ygrMWJZmaxWINpm
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2019 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | Enterprise Marketplace Software for the Web. # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
/**
* XML RPC server class to perform the majority of xml remote call procedures tasks in iLance.
*
* @package      iLance\XMLRPC
* @version      6.0.0.622
* @author       ILance
*/
class xmlrpcserver
{
	private $server_handler;
	private $external_functions;
	public function __construct()
	{
		$this->server_handler = xmlrpc_server_create();
		$this->external_functions = array();
	}
	public function register_method($external_name, $function, $parameter_names)
	{
		if ($function == null)
		{
			$function = $external_name;
		}
		xmlrpc_server_register_method($this->server_handler, $external_name, array(&$this, 'call_method'));
		$this->external_functions[$external_name] = array('function' => $function, 'parameter_names' => $parameter_names);
	}
	public function call_method($function_name, $parameters_from_request)
	{
		$function = $this->external_functions[$function_name]['function'];
		$parameter_names = $this->external_functions[$function_name]['parameter_names'];
		$parameters = array();
		if (!empty($parameter_names) AND count($parameter_names) > 0)
		{
			foreach ($parameter_names AS $parameter_name)
			{
				$parameters[] = (isset($parameters_from_request[0][$parameter_name]) ? $parameters_from_request[0][$parameter_name] : '');
			}
		}
		return call_user_func_array($function, $parameters);
	}
	public function send_reponse()
	{
		$options = ['output_type' => 'xml', 'verbosity' => 'pretty', 'escaping' => ['markup'], 'version' => 'xmlrpc', 'encoding' => 'utf-8'];
		return xmlrpc_server_call_method($this->server_handler, file_get_contents('php://input'), null, $options);
	}
}
/**
* XML RPC server class for iLance
*
* @package      iLance\XMLRPC\Server
* @version      6.0.0.622
* @author       ILance
*/
class ilance_xmlrpcserver
{
	protected $ilance;
	private $xmlrpcserver;
	var $csrftoken = '';
	public function __construct($ilance, $xmlrpcserver)
	{
		$this->ilance = $ilance;
		$this->xmlrpcserver = $xmlrpcserver;
		$this->xmlrpcserver->register_method('system.officialtime', array(&$this, 'system_getofficialtime'), array('sessid'));
		$this->xmlrpcserver->register_method('system.officialtime.formatted', array(&$this, 'system_getofficialtimeformatted'), array('sessid'));
		$this->xmlrpcserver->register_method('user.get', array(&$this, 'user_get'), array('userid', 'username', 'password', 'apikey', 'csrftoken'));
		
		$this->xmlrpcserver->register_method('user.add.transaction', array(&$this, 'user_addtransaction'), array('userid', 'title', 'debit', 'credit', 'debitpoints', 'creditpoints', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.update', array(&$this, 'item_update'), array('itemid', 'sku', 'price', 'qty', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.add', array(&$this, 'item_add'), array('iteminfo', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.get.messages', array(&$this, 'item_getmessages'), array('itemid', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.get.shipping', array(&$this, 'item_getshipping'), array('itemid', 'sku', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.get.specifics', array(&$this, 'item_getspecifics'), array('itemid', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.get.bidders', array(&$this, 'item_getbidders'), array('itemid', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('item.get.bids', array(&$this, 'item_getbids'), array('itemid', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('category.get', array(&$this, 'category_get'), array('cid', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('feedback.get', array(&$this, 'feedback_get'), array('userid', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('search.items.keyword', array(&$this, 'search_itemsbykeyword'), array('keyword', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('search.items.category', array(&$this, 'search_itemsbycategory'), array('cid', 'keyword', 'limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('orders.get', array(&$this, 'orders_get'), array('limit', 'sort', 'page', 'username', 'password', 'apikey', 'csrftoken'));
		$this->xmlrpcserver->register_method('orders.get.transaction', array(&$this, 'orders_gettransaction'), array('orderid', 'username', 'password', 'apikey', 'csrftoken'));
		// private
		$this->xmlrpcserver->register_method('admin.import.xml', array(&$this, 'import'), array('type'));
		$this->xmlrpcserver->register_method('admin.license.update', array(&$this, 'license_update'), array('licensekey', 'appid', 'site_limit', 'user_limit', 'license_expiry', 'license_type', 'license_tier', 'istrial', 'platform_type', 'billing_endpoint', 'license_suspended', 'license_payment_in_process'));
		$this->xmlrpcserver->register_method('admin.license.suspend', array(&$this, 'license_suspend'), array('licensekey', 'appid', 'license_suspended'));
		$this->xmlrpcserver->register_method('admin.license.billing', array(&$this, 'license_billing'), array('licensekey', 'appid', 'billing_cancelled'));
		$this->xmlrpcserver->register_method('admin.build.md5', array(&$this, 'build_md5_filelist'), array());



		
		$this->xmlrpcserver->register_method('system.connect', array(&$this, 'system_connect'), array('devicetoken')); // <-- retrieves a session id & csrf token for subsequent connections
		$this->xmlrpcserver->register_method('user.signin', array(&$this, 'user_signin'), array('username', 'password', 'apikey', 'csrftoken','rememberuser','devicetoken'));
		$this->xmlrpcserver->register_method('user.signout', array(&$this, 'user_signout'), array('sessid'));
		$this->xmlrpcserver->register_method('user.register', array(&$this, 'user_register'), array('username', 'password', 'email', 'firstname', 'lastname', 'address', 'address2', 'phone', 'country', 'city', 'state', 'zipcode', 'currency', 'acceptsmarketing',  'secretquestion', 'secretanswer', 'dob', 'csrftoken'));
		$this->xmlrpcserver->register_method('session.setlanguage', array(&$this, 'session_setlanguage'), array('sessid','lngid','csrftoken'));
		$this->xmlrpcserver->register_method('session.getlanguage', array(&$this, 'session_getlanguage'), array('sessid','csrftoken'));
		$this->xmlrpcserver->register_method('session.getexpiry', array(&$this, 'session_getexpiry'), array('sessid','csrftoken'));
		$this->xmlrpcserver->register_method('system.getlanguages', array(&$this, 'system_getlanguages'), array('csrftoken'));
		$this->xmlrpcserver->register_method('system.getloginphrase', array(&$this, 'system_getloginphrase'), array('lng', 'phrasegroup', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getappphrase', array(&$this, 'system_getappphrase'), array('lng', 'phrasegroup', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getphrase', array(&$this, 'system_getphrase'), array('var','lng','csrftoken'));
		$this->xmlrpcserver->register_method('system.getheros', array(&$this, 'system_getheros'), array('mode', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.checkusername', array(&$this, 'system_checkusername'), array('username', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.checkemail', array(&$this, 'system_checkemail'), array('email', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.checkconfig', array(&$this, 'system_checkconfig'), array('configoption', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getcurrencies', array(&$this, 'system_getcurrencies'), array('csrftoken'));
		$this->xmlrpcserver->register_method('system.getcountries', array(&$this, 'system_getcountries'), array('csrftoken'));
		$this->xmlrpcserver->register_method('system.getsps', array(&$this, 'system_getsps'), array('countryid', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getcities', array(&$this, 'system_getcities'), array('countryid', 'sp', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getsecretquestions', array(&$this, 'system_getsecretquestions'), array('csrftoken'));
		$this->xmlrpcserver->register_method('system.getcaptcha', array(&$this, 'system_getcaptcha'), array('csrftoken'));
		$this->xmlrpcserver->register_method('system.validatecaptcha', array(&$this, 'system_validatecaptcha'), array('captcha','csrftoken'));
		$this->xmlrpcserver->register_method('user.forgotpassword', array(&$this, 'user_forgotpassword'), array('useremail', 'csrftoken'));
		$this->xmlrpcserver->register_method('user.verifyotp', array(&$this, 'user_verifyotp'), array('useremail', 'usercode', 'group', 'csrftoken'));
		$this->xmlrpcserver->register_method('user.changepassword', array(&$this, 'user_changepassword'), array('useremail', 'usercode', 'codeid', 'newpassword', 'csrftoken'));
		$this->xmlrpcserver->register_method('system.getcontents', array(&$this, 'system_getcontents'), array('lng','csrftoken'));
		$this->xmlrpcserver->register_method('listing.get', array(&$this, 'listing_get'), array('cid', 'storeid', 'keywords', 'csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('listing.staffpicks.get', array(&$this, 'listing_staffpicks_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('listing.featured.get', array(&$this, 'listing_featured_get'), array('csrftoken', 'storeid','limit', 'page'));
		$this->xmlrpcserver->register_method('listing.related.get', array(&$this, 'listing_related_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('listing.watchlist.get', array(&$this, 'listing_watchlist_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('listing.latest.get', array(&$this, 'listing_latest_get'), array('csrftoken', 'storeid','limit', 'page'));
		$this->xmlrpcserver->register_method('listing.endingsoon.get', array(&$this, 'listing_endingsoon_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('listing.latestcategories.get', array(&$this, 'listing_latestcategories_get'), array('csrftoken', 'limit'));
		$this->xmlrpcserver->register_method('categories.get', array(&$this, 'categories_get'), array('csrftoken','parent', 'limit'));
		$this->xmlrpcserver->register_method('categories.get.parent', array(&$this, 'categories_get_parent'), array('csrftoken','cid'));
		$this->xmlrpcserver->register_method('item.get', array(&$this, 'item_get'), array('csrftoken', 'itemid', 'password', 'doended'));
		$this->xmlrpcserver->register_method('user.add.cart', array(&$this, 'user_addcart'), array('itemid', 'quantity', 'sku', 'csrftoken'));
		$this->xmlrpcserver->register_method('user.remove.cart', array(&$this, 'user_removecart'), array('cartid','csrftoken'));
		$this->xmlrpcserver->register_method('user.savelater.cart', array(&$this, 'user_savelatercart'), array('cartid','csrftoken'));
		$this->xmlrpcserver->register_method('user.movesave.cart', array(&$this, 'user_movesavecart'), array('cartid','csrftoken'));
		$this->xmlrpcserver->register_method('user.count.cart', array(&$this, 'user_countcart'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.count.cartitems', array(&$this, 'user_countcartitems'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.get.cart', array(&$this, 'user_getcart'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.get.cart.saved', array(&$this, 'user_getcartsaved'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.review.order', array(&$this, 'user_review_order'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.bid.add', array(&$this, 'user_bid_add'), array('csrftoken', 'bidamount', 'minimumbid', 'itemid', 'currency', 'paddleid'));
		$this->xmlrpcserver->register_method('user.bid.retract', array(&$this, 'user_bid_retract'), array('csrftoken', 'bidid', 'reason'));
		$this->xmlrpcserver->register_method('user.bid.status', array(&$this, 'user_bid_status'), array('csrftoken', 'bidid'));
		$this->xmlrpcserver->register_method('user.follow.seller', array(&$this, 'user_follow_seller'), array('csrftoken', 'sellerid'));
		$this->xmlrpcserver->register_method('user.unfollow.seller', array(&$this, 'user_unfollow_seller'), array('csrftoken', 'sellerid'));
		$this->xmlrpcserver->register_method('listing.stores.get', array(&$this, 'listing_stores_get'), array('csrftoken', 'limit', 'page', 'cid', 'anchor', 'featured', 'trending', 'keywords', 'nopictures', 'picturedim', 'logodim'));
		$this->xmlrpcserver->register_method('store.get.details', array(&$this, 'store_get_details'), array('csrftoken', 'storeid', 'limit'));
		$this->xmlrpcserver->register_method('notification.unsubscribe', array(&$this, 'notification_unsubscribe'), array('csrftoken', 'userid', 'varname'));
		$this->xmlrpcserver->register_method('notification.subscribe', array(&$this, 'notification_subscribe'), array('csrftoken', 'userid', 'varname'));
		$this->xmlrpcserver->register_method('notification.delete', array(&$this, 'notification_delete'), array('csrftoken', 'deviceid', 'notificationid'));
		$this->xmlrpcserver->register_method('notification.flag', array(&$this, 'notification_flag'), array('csrftoken', 'deviceid', 'notificationid'));
		$this->xmlrpcserver->register_method('notification.get', array(&$this, 'notification_get'), array('csrftoken', 'deviceid', 'limit', 'page'));
		$this->xmlrpcserver->register_method('notification.count', array(&$this, 'notification_count'), array('csrftoken', 'deviceid'));
		$this->xmlrpcserver->register_method('notification.get.details', array(&$this, 'notification_get_details'), array('csrftoken', 'deviceid', 'notificationid'));
		$this->xmlrpcserver->register_method('listing.search', array(&$this, 'listing_search'), array('csrftoken', 'searchparams', 'limit', 'page'));
		$this->xmlrpcserver->register_method('brands.get', array(&$this, 'brands_get'), array('csrftoken', 'keyword', 'limit', 'page'));
		$this->xmlrpcserver->register_method('brand.add', array(&$this, 'brand_add'), array('csrftoken', 'brandparams'));
		$this->xmlrpcserver->register_method('brand.get.listing', array(&$this, 'brand_get_listing'), array('csrftoken', 'bsin', 'limit', 'page'));
		$this->xmlrpcserver->register_method('nonprofits.get', array(&$this, 'nonprofits_get'), array('csrftoken', 'keyword', 'limit', 'page'));
		$this->xmlrpcserver->register_method('nonprofit.get.listing', array(&$this, 'nonprofit_get_listing'), array('csrftoken', 'charityid', 'limit', 'page'));
		$this->xmlrpcserver->register_method('auctions.get', array(&$this, 'auctions_get'), array('csrftoken', 'keyword', 'limit', 'page'));
		$this->xmlrpcserver->register_method('user.register.auction', array(&$this, 'user_register_auction'), array('csrftoken', 'auctionid'));
		$this->xmlrpcserver->register_method('user.unregister.auction', array(&$this, 'user_unregister_auction'), array('csrftoken', 'auctionid'));
		$this->xmlrpcserver->register_method('is.added.to.watchlist', array(&$this, 'is_added_to_watchlist'), array('csrftoken', 'type', 'id'));
		$this->xmlrpcserver->register_method('auction.get.listing', array(&$this, 'auction_get_listing'), array('csrftoken', 'auctionid', 'limit', 'page'));
		$this->xmlrpcserver->register_method('auction.get.details', array(&$this, 'auction_get_details'), array('csrftoken', 'auctionid'));
		$this->xmlrpcserver->register_method('mystore.get', array(&$this, 'mystore_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('mystore.check.name', array(&$this, 'mystore_check_name'), array('csrftoken', 'storename'));
		$this->xmlrpcserver->register_method('mystore.add', array(&$this, 'mystore_add'), array('csrftoken', 'mystoreparams'));
		$this->xmlrpcserver->register_method('mystore.update', array(&$this, 'mystore_update'), array('csrftoken', 'mystoreparams'));
		$this->xmlrpcserver->register_method('mystore.add.category', array(&$this, 'mystore_add_category'), array('csrftoken', 'categoryname'));
		$this->xmlrpcserver->register_method('mystore.update.category', array(&$this, 'mystore_update_category'), array('csrftoken', 'cid', 'updatedname'));
		$this->xmlrpcserver->register_method('mystore.delete.category', array(&$this, 'mystore_delete_category'), array('csrftoken', 'cid'));
		$this->xmlrpcserver->register_method('mystore.add.promocode', array(&$this, 'mystore_add_promocode'), array('csrftoken', 'promocodeparams'));
		$this->xmlrpcserver->register_method('mystore.update.promocode', array(&$this, 'mystore_update_promocode'), array('csrftoken', 'promocodeparams'));
		$this->xmlrpcserver->register_method('mystore.delete.promocode', array(&$this, 'mystore_delete_promocode'), array('csrftoken', 'promoid'));
		$this->xmlrpcserver->register_method('mystore.upgrade.get', array(&$this, 'mystore_upgrade_get'), array('csrftoken', 'upgradeparams'));
		$this->xmlrpcserver->register_method('mymessages.get', array(&$this, 'mymessages_get'), array('csrftoken', 'folder', 'period', 'view', 'limit', 'page'));
		$this->xmlrpcserver->register_method('mymessage.delete', array(&$this, 'mymessage_delete'), array('csrftoken', 'eventid', 'system', 'all'));
		$this->xmlrpcserver->register_method('mymessage.archive', array(&$this, 'mymessage_archive'), array('csrftoken', 'eventid'));
		$this->xmlrpcserver->register_method('mymessage.compose', array(&$this, 'mymessage_compose'), array('csrftoken', 'username', 'subject', 'message', 'project_id', 'event_id', 'attachparams'));
		$this->xmlrpcserver->register_method('mymessage.read', array(&$this, 'mymessage_read'), array('csrftoken', 'project_id', 'orderidpublic','event_id'));
		$this->xmlrpcserver->register_method('mymessage.read.vm', array(&$this, 'mymessage_read_vm'), array('csrftoken', 'messageid'));
		$this->xmlrpcserver->register_method('mymessage.count', array(&$this, 'mymessages_count'), array('csrftoken', 'filter'));
		$this->xmlrpcserver->register_method('myauctions.get', array(&$this, 'myauctions_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('myauctions.add', array(&$this, 'myauctions_add'), array('csrftoken', 'myauctionparams'));
		$this->xmlrpcserver->register_method('myauctions.publish', array(&$this, 'myauctions_publish'), array('csrftoken', 'eventid'));
		$this->xmlrpcserver->register_method('myauctions.preadd', array(&$this, 'myauctions_preadd'), array('csrftoken'));
		$this->xmlrpcserver->register_method('myauctions.update', array(&$this, 'myauctions_update'), array('csrftoken', 'myauctionparams'));
		$this->xmlrpcserver->register_method('myauctions.preupdate', array(&$this, 'myauctions_preupdate'), array('csrftoken', 'eventid'));
		$this->xmlrpcserver->register_method('user.profile.get', array(&$this, 'user_profile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.profile.update', array(&$this, 'user_profile_update'), array('csrftoken', 'gender', 'dobyear', 'dobmonth', 'dobday', 'company', 'firstname', 'lastname', 'regnumber', 'vatnumber', 'dnbnumber'));
		$this->xmlrpcserver->register_method('user.currency.update', array(&$this, 'user_currency_update'), array('csrftoken', 'currencyid'));
		$this->xmlrpcserver->register_method('user.currency.get', array(&$this, 'user_currency_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.language.update', array(&$this, 'user_language_update'), array('csrftoken', 'languageid'));
		$this->xmlrpcserver->register_method('user.account.data', array(&$this, 'user_account_data'), array('csrftoken', 'period'));
		$this->xmlrpcserver->register_method('user.password.update', array(&$this, 'user_password_update'), array('csrftoken', 'newpassword'));
		$this->xmlrpcserver->register_method('user.sellingprofile.get', array(&$this, 'user_sellingprofile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.sellingprofile.update', array(&$this, 'user_sellingprofile_update'), array('csrftoken', 'profileintro', 'profilevideourl', 'displayprofile', 'usecompanyname', 'companyname', 'sellingprofilepicture'));
		$this->xmlrpcserver->register_method('user.sellingprofile.attachment.remove', array(&$this, 'user_sellingprofile_attachment_remove'), array('csrftoken', 'attachid'));
		$this->xmlrpcserver->register_method('user.shippingprofile.get', array(&$this, 'user_shippingprofile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.shippingprofile.delete', array(&$this, 'user_shippingprofile_delete'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.shippingprofile.add', array(&$this, 'user_shippingprofile_add'), array('csrftoken', 'shippingparams'));
		$this->xmlrpcserver->register_method('user.shippingprofile.preupdate', array(&$this, 'user_shippingprofile_preupdate'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.shippingprofile.update', array(&$this, 'user_shippingprofile_update'), array('csrftoken', 'shippingparams'));
		$this->xmlrpcserver->register_method('user.shippingprofile.setdefault', array(&$this, 'user_shippingprofile_setdefault'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.taxprofile.get', array(&$this, 'user_taxprofile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.taxprofile.delete', array(&$this, 'user_taxprofile_delete'), array('csrftoken', 'tid'));
		$this->xmlrpcserver->register_method('user.taxprofile.add', array(&$this, 'user_taxprofile_add'), array('csrftoken', 'taxparams'));
		$this->xmlrpcserver->register_method('user.taxprofile.preupdate', array(&$this, 'user_taxprofile_preupdate'), array('csrftoken', 'tid'));
		$this->xmlrpcserver->register_method('user.taxprofile.update', array(&$this, 'user_taxprofile_update'), array('csrftoken', 'taxparams'));
		$this->xmlrpcserver->register_method('user.taxprofile.setdefault', array(&$this, 'user_taxprofile_setdefault'), array('csrftoken', 'tid'));
		$this->xmlrpcserver->register_method('user.sellingpaymentprofile.get', array(&$this, 'user_sellingpaymentprofile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.membership.get', array(&$this, 'user_membership_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.watchlist.items.get', array(&$this, 'user_watchlist_items_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('user.watchlist.sellers.get', array(&$this, 'user_watchlist_sellers_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('user.watchlist.auctions.get', array(&$this, 'user_watchlist_auctions_get'), array('csrftoken', 'limit', 'page'));
		$this->xmlrpcserver->register_method('user.watchlist.delete', array(&$this, 'user_watchlist_delete'), array('csrftoken', 'recordid', 'type', 'all'));
		$this->xmlrpcserver->register_method('user.billingprofile.get', array(&$this, 'user_billingprofile_get'), array('csrftoken'));
		$this->xmlrpcserver->register_method('user.billingprofile.delete', array(&$this, 'user_billingprofile_delete'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.billingprofile.add', array(&$this, 'user_billingprofile_add'), array('csrftoken', 'billingparams'));
		$this->xmlrpcserver->register_method('user.billingprofile.preupdate', array(&$this, 'user_billingprofile_preupdate'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.billingprofile.update', array(&$this, 'user_billingprofile_update'), array('csrftoken', 'billingparams'));
		$this->xmlrpcserver->register_method('user.billingprofile.setdefault', array(&$this, 'user_billingprofile_setdefault'), array('csrftoken', 'spid'));
		$this->xmlrpcserver->register_method('user.rtbf', array(&$this, 'user_rtbf'), array('csrftoken', 'password', 'comments'));
		$this->xmlrpcserver->register_method('user.buyingactivity.product', array(&$this, 'user_buyingactivity_product'), array('csrftoken', 'bidsub', 'keyw2', 'keyw', 'page', 'period', 'orderby', 'displayorder'));
	}
	public function system_getofficialtime($sessid)
	{ // retrieves the official system time
		return array('error' => '0', 'message' => '', 'datetime' => DATETIME24H);
	}
	public function system_getofficialtimeformatted($sessid)
	{ // retrieves the official system time
		return array('error' => '0', 'message' => '', 'datetime' => $this->ilance->common->print_date(DATETIME24H));
	}


	public function user_addtransaction($userid, $title, $debit, $credit, $debitpoints, $creditpoints, $username, $password, $apikey, $csrftoken) // staff
	{ // staff adds new transaction for a user
		if (!$this->authenticate_staff($username, $password, $apikey, $csrftoken, 'user.add.transaction'))
		{
			$this->api_failed('user.add.transaction');
			return array('error' => '1', 'message' => 'Could not authenticate as staff.', 'user' => array());
		}
		// ..
	}
	public function user_get($userid, $username, $password, $apikey, $csrftoken) // staff
	{ // retrieves data pertaining to a marketplace customer
		if (!$this->authenticate_staff($username, $password, $apikey, $csrftoken, 'user.get') OR $userid <= 0)
		{
			$this->api_failed('user.get');
			return array('error' => '1', 'message' => 'Could not authenticate as staff.', 'user' => array());
		}
		$sql = $this->ilance->db->query("
			SELECT u.rewardpoints AS points, u.available_balance, u.username, u.first_name, u.last_name, u.state, u.city, u.zip_code AS zipcode, u.email, u.status, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS country
			FROM " . DB_PREFIX . "users u
			LEFT JOIN " . DB_PREFIX . "locations l ON (u.country = l.locationid)
			WHERE u.user_id = '" . intval($userid) . "'
			LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$user = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$this->api_success('user.get');
			return array('error' => '0', 'message' => '', 'user' => $user);
		}
		$this->api_failed('user.get');
		return array('error' => '1', 'message' => 'Could not find user by ID.', 'user' => array());
	}


	public function item_update($itemid, $sku, $price, $qty, $username, $password, $apikey, $csrftoken)
	{ // updates item data such as title, description, price information, seller information, and so on, for the specified item id.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.update'))
		{
			$this->api_failed('item.update');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'item' => array());
		}
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $itemid != '' AND $price != '' AND $qty != '')
		{
			$sellerid = $this->ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . intval($itemid) . "'", "user_id");
			if ($sellerid > 0 AND $sellerid == $_SESSION['ilancedata']['user']['userid'])
			{ // seller updating own item
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET buynow_qty = '" . intval($qty) . "',
					buynow_price = '" . $this->ilance->db->escape_string($price) . "'
					WHERE sku = '" . $this->ilance->db->escape_string($sku) . "'
						AND project_id = '" . intval($itemid) . "'
					LIMIT 1
				");
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "variants
					SET qty = '" . intval($qty) . "',
					price = '" . $this->ilance->db->escape_string($price) . "'
					WHERE sku = '" . $this->ilance->db->escape_string($sku) . "'
						AND project_id = '" . intval($itemid) . "'
					LIMIT 1
				");
				$sql = $this->ilance->db->query("
					SELECT project_title, buynow_price, buynow_qty, sku
					FROM " . DB_PREFIX . "projects
					WHERE sku = '" . $this->ilance->db->escape_string($sku) . "'
						AND project_id = '" . intval($itemid) . "'
					LIMIT 1
				");
				if ($this->ilance->db->num_rows($sql) > 0)
				{
					$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
					$response = array(
						'itemid' => intval($itemid),
						'title' => $res['project_title'],
						'price' => $res['buynow_price'],
						'qty' => $res['buynow_qty'],
						'sku' => $res['sku']
					);
					$this->api_success('item.update');
					return array('error' => '0', 'message' => '', 'item' => $response);
				}
			}
		}
		$this->api_failed('item.update');
		return array('error' => '1', 'message' => 'Could not update item.', 'item' => array());
	}
	public function item_add($iteminfo = array(), $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves item data such as title, description, price information, seller information, and so on, for the specified item id.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.add'))
		{
			$this->api_failed('item.add');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'status' => '');
		}
		// fetch posted status
		// ..
		$status = '';
		$this->api_success('item.add');
		return array('error' => '0', 'message' => '', 'status' => $status);
	}

	public function item_getmessages($itemid, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves a list of the messages buyers have posted about an item listing.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.get.messages'))
		{
			$this->api_failed('item.get.messages');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'messages' => array());
		}
		$messages[] = array(
			'id' => '',
			'title' => '',
			'subject' => '',
			'body' => '',
			'username' => '',
			'datetime' => '',
			'replies' => ''
		);
		//return 'failed';
		$this->api_success('item.get.messages');
		return array('error' => '0', 'message' => '', 'messages' => $messages);
	}
	public function item_getshipping($itemid, $sku, $limit, $sort, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves item shipping data such as price, shipping service, etc.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.get.shipping'))
		{
			$this->api_failed('item.get.shipping');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'shipping' => array());
		}
		$response[] = array('price' => '', 'service' => '', 'estimated' => '');
		$this->api_success('item.get.shipping');
		return array('error' => '0', 'message' => '', 'shipping' => $response);
	}
	public function item_getspecifics($itemid, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves item shipping data such as price, shipping service, etc.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.get.specifics'))
		{
			$this->api_failed('item.get.specifics');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'specifics' => array());
		}
		$specifics[] = array('Color' => '', 'Size' => '', 'Type' => '');
		$this->api_success('item.get.specifics');
		return array('error' => '0', 'message' => '', 'specifics' => $specifics);
	}
	public function item_getbidders($itemid, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves item shipping data such as price, shipping service, etc.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.get.bidders'))
		{
			$this->api_failed('item.get.bidders');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'bidders' => array());
		}
		$bidders[] = array('username' => '');
		$this->api_success('item.get.bidders');
		return array('error' => '0', 'message' => '', 'bidders' => $bidders);
	}
	public function item_getbids($itemid, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves item shipping data such as price, shipping service, etc.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'item.get.bids'))
		{
			$this->api_failed('item.get.bids');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'bids' => array());
		}
		$bids[] = array('username' => '', 'bidid' => '', 'bidamount' => '', 'bidstatus' => '', 'datetime' => '');
		$this->api_success('item.get.bids');
		return array('error' => '0', 'message' => '', 'bids' => $bids);
	}
	public function category_get($cid, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves category data such as title, description, etc.
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'category.get'))
		{
			$this->api_failed('category.get');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'category' => array());
		}
		$category[] = array('title' => '', 'description' => '', 'cid' => '', 'parentid' => '');
		$this->api_success('category.get');
		return array('error' => '0', 'message' => '', 'category' => $category);
	}
	public function feedback_get($userid, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO retrieves feedback information for a user
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'feedback.get'))
		{
			$this->api_failed('feedback.get');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'feedback' => array());
		}
		$feedback[] = array('title' => '', 'foruser' => '', 'fromuser' => '', 'response' => '', 'datetime' => '');
		$this->api_success('feedback.get');
		return array('error' => '0', 'message' => '', 'feedback' => $feedback);
	}
	public function search_itemsbykeyword($keyword, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO search items by keywords
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'search.items.keyword'))
		{
			$this->api_failed('search.items.keyword');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'search' => array());
		}
		$search[] = array('itemid' => '', 'title' => '', 'description' => '', 'sku' => '', 'qty' => '', 'price' => '', 'type' => '', 'cid' => '', 'category' => '');
		$this->api_success('search.items.keyword');
		return array('error' => '0', 'message' => '', 'search' => $search);
	}
	public function search_itemsbycategory($cid, $keyword, $limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO search items by category and/or keyword
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'search.items.category'))
		{
			$this->api_failed('search.items.category');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'search' => array());
		}
		$search[] = array('itemid' => '', 'title' => '', 'description' => '', 'sku' => '', 'qty' => '', 'price' => '', 'type' => '', 'cid' => '', 'category' => '');
		$this->api_success('search.items.category');
		return array('error' => '0', 'message' => '', 'search' => $search);
	}
	public function orders_get($limit, $sort, $page, $username, $password, $apikey, $csrftoken)
	{ // TODO get all orders for customer
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'orders.get'))
		{
			$this->api_failed('orders.get');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'orders' => array());
		}
		$orders[] = array('orderid' => '', 'customer' => '', 'customeremail' => '', 'itemid' => '', 'title' => '', 'sku' => '', 'qty' => '', 'priceeach' => '', 'shipservice' => '', 'subtotal' => '', 'shiptotal' => '', 'taxtotal' => '', 'ordertotal' => '', 'datetime' => '');
		$this->api_success('orders.get');
		return array('error' => '0', 'message' => '', 'orders' => $orders);
	}
	public function orders_gettransaction($orderid, $username, $password, $apikey, $csrftoken)
	{ // TODO get specific order details for a customer
		if (!$this->authenticate($username, $password, $apikey, $csrftoken, 'orders.get.transaction'))
		{
			$this->api_failed('orders.get.transaction');
			return array('error' => '1', 'message' => 'Could not authenticate.', 'transactions' => array());
		}
		$transactions[] = array('orderid' => '', 'transactionid' => '', 'invoiceid' => '', 'title' => '', 'credit' => '', 'debit' => '', 'datetime' => '');
		$this->api_success('orders.get.transaction');
		return array('error' => '0', 'message' => '', 'transactions' => $transactions);
	}
	public function import($type)
	{
		if ($type == 'phrase')
		{
			$xml = file_get_contents(DIR_FUNCTIONS . 'installer/xml/master-phrases-english.xml');
			$this->ilance->admincp_importexport->import('phrase', 'xmlrpc', $xml, true, 1, 1);
		}
		else if ($type == 'email')
		{
			$xml = file_get_contents(DIR_FUNCTIONS . 'installer/xml/master-emails-english.xml');
			$this->ilance->admincp_importexport->import('email', 'xmlrpc', $xml, true, 1, 1);
		}
		else
		{
			return 'failure';
		}
		return 'success';
	}
	public function license_update($licensekey, $appid, $site_limit, $user_limit, $license_expiry, $license_type, $license_tier, $istrial, $platform_type, $billing_endpoint, $license_suspended, $license_payment_in_process)
	{
		return $this->ilance->admincp->license_update($licensekey, $appid, $site_limit, $user_limit, $license_expiry, $license_type, $license_tier, $istrial, $platform_type, $billing_endpoint, $license_suspended, $license_payment_in_process);
	}
	public function license_suspend($licensekey, $appid, $license_suspended)
	{
		return $this->ilance->admincp->license_suspend($licensekey, $appid, $license_suspended);
	}
	public function license_billing($licensekey, $appid, $billing_cancelled)
	{
		return $this->ilance->admincp->license_billing($licensekey, $appid, $billing_cancelled);
	}
	public function build_md5_filelist()
	{
		$this->ilance->security->build_md5_filelist();
		return 'success';
	}
	private function api_failed($method)
	{
		if (!empty($method))
		{
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET failed = failed + 1
				WHERE name = '" . $this->ilance->db->escape_string($method) . "'
				LIMIT 1
			");
		}
	}
	private function api_success($method)
	{
		if (!empty($method))
		{
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET success = success + 1
				WHERE name = '" . $this->ilance->db->escape_string($method) . "'
				LIMIT 1
			");
		}
	}










	public function authenticate_staff($username, $password, $apikey, $guestcsrftoken, $method)
	{ // verify username, password, api key & csrf token for any request
		if (
			!isset($_SESSION['ilancedata']['user']['userid']) OR
			(isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0) OR
			(isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] <= 0))
		{
			$this->csrftoken = $this->ilance->common->login($username, $password, $apikey, $guestcsrftoken, true); // true denotes force building of $_SESSION
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string($method) . "'
				LIMIT 1
			");
			if ($this->csrftoken != '' AND $this->csrftoken <= 0)
			{
				return false;
			}
			if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] > 0)
			{
				return true;
			}
			return false;
		}
		else
		{
			$this->csrftoken = $_SESSION['ilancedata']['user']['csrf'];
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string($method) . "'
				LIMIT 1
			");
		}
		return true;
	}

	public function getphrase($var, $lng) {
		$slng = substr($lng,0,3);
		$sql = $this->ilance->db->query("
			SELECT text_$slng AS text
			FROM " .DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$phrase = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			return  $phrase['text'];
		}
		return 'Could not find Any Phrase.';
	}

	public function getsessionexpiry($sessid='') {
		$sql = $this->ilance->db->query("
		SELECT expiry
		FROM " . DB_PREFIX . "sessions
		WHERE sesskey = '" . $sessid . "'
		LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$sess = $this->ilance->db->fetch_array($sql, DB_ASSOC);	
			
			return $sess['expiry'];
		}
		return '0';
	}



	public function authorize($username, $password, $apikey) {
		$badpassword = true;
		if (!empty($username))
		{
			$sql = $this->ilance->db->query("
				SELECT u.username, u.password, u.apikey, u.salt
				FROM " . DB_PREFIX . "users AS u
				WHERE (u.username = '" . $this->ilance->db->escape_string($username) . "' OR u.email = '" . $this->ilance->db->escape_string($username) . "' OR u.companyname = '" . $this->ilance->db->escape_string($username) . "')
					AND u.status='active'
					" . ((!empty($apikey)) ? "AND u.apikey = '" . $this->ilance->db->escape_string($apikey) . "' AND u.useapi = '1'" : "") . "
				LIMIT 1
			");
			if ($this->ilance->db->num_rows($sql) > 0)
			{
				$userinfo = $this->ilance->db->fetch_array($sql, DB_ASSOC);
				$md5pass = $md5pass_utf = md5($password);
				$badpassword = false;
				if (
					$userinfo['password'] != iif($password AND !$md5pass, md5(md5($password) . $userinfo['salt']), '') AND
					$userinfo['password'] != md5($md5pass . $userinfo['salt']) AND
					$userinfo['password'] != iif($md5pass_utf, md5($md5pass_utf . $userinfo['salt']), '')
				)
				{
					$badpassword = true;
				}
			}
			else
			{
				return false;
			}
			if ($badpassword)
			{
				return false;
			}
		}	
		return true;
	}

	public function authenticate($username, $password, $apikey, $guestcsrftoken, $method, $rememberuser)
	{ // verify username, password, api key & csrf token for any request
		if (!isset($_SESSION['ilancedata']['user']['userid']) OR (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0) OR (!isset($_SESSION['ilancedata']['user']['csrf'])))
		{
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string($method) . "'
				LIMIT 1
			");
			if ($this->authorize($username, $password, $apikey)) {
				$this->csrftoken = $this->ilance->common->login($username, $password, $apikey, $guestcsrftoken, true, $rememberuser, true); // true denotes force building of $_SESSION
				if ($this->csrftoken != '' AND $this->csrftoken <= 0)
				{
					return false;
				}

				if ($this->csrftoken == '' )
				{
					return false;
				}
				return true;
			}
			else {
				return false;
			}
		}
		else
		{
			if ($this->authorize($username, $password, $apikey)) 
			{
				$this->csrftoken = $_SESSION['ilancedata']['user']['csrf'];
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "api
					SET hits = hits + 1
					WHERE name = '" . $this->ilance->db->escape_string($method) . "'
					LIMIT 1
				");
			}

			else {
				return false;
			}
			
		}
		return true;
	}


	public function system_connect($devicetoken = '0')
	{ // retrieves a session id & csrf token for subsequent connections
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('system.connect') . "'
		LIMIT 1
		");
		if (!isset($_SESSION['ilancedata']['user']['userid']) OR (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0) OR (!isset($_SESSION['ilancedata']['user']['csrf']))) {
			$_SESSION['ilancedata']['user']['ismobile'] = '1';
			if ($devicetoken=='') {
				$_SESSION['ilancedata']['user']['devicetoken'] = '0';
			}
			else {
				$_SESSION['ilancedata']['user']['devicetoken'] = $devicetoken;
			}
			
			return array('error' => '0', 'message' => 'Connected.', 'sessid' => session_id(), 'csrftoken' => $_SESSION['ilancedata']['user']['csrf'], 'expiry' => $this->getsessionexpiry(session_id()), 'prefix' => COOKIE_PREFIX);
		}
		else {
			
			return array('error' => '0', 'message' => 'Connected.', 'sessid' => '0', 'csrftoken' => $_SESSION['ilancedata']['user']['csrf'], 'expiry' => $this->getsessionexpiry(session_id()) , 'prefix' => COOKIE_PREFIX);
		}
		
	}

	public function session_setlanguage($sessid='', $lngid='0', $guestcsrftoken='')
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('session.setlanguage') . "'
		LIMIT 1
		");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('session.setlanguage');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		if ($sessid == session_id() AND $lngid > 0) {
			$_SESSION['ilancedata']['user']['languageid'] = intval($lngid);
			$this->api_success('session.setlanguage');
			return array('error' => '0', 'message' => 'Session Language Successfully Changed To ' . $lngid, 'Session ID' => session_id()) ;
		}

		else {
			$this->api_failed('session.setlanguage');
			if ($sessid != session_id()) {
				return array('error' => '1', 'message' => 'Wrond Session ID ' .$sessid.'.');
			}

			if ($lngid == 0) {
				return array('error' => '1', 'message' => 'Language ID ' .$lngid.' is not valid.');
			}
		}
	}

	public function session_getlanguage($sessid='', $guestcsrftoken='')
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('session.getlanguage') . "'
		LIMIT 1
		");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('session.getlanguage');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT languageid
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $sessid . "'
			LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$language = $this->ilance->db->fetch_array($sql, DB_ASSOC);	
			$this->api_success('session.getlanguage');
			return array('error' => '0', 'message' => 'Success', 'language' => $language['languageid']);
		}
		$this->api_failed('session.getlanguage');
		return array('error' => '1', 'message' => 'Could not find Any Language Associated with session id ' .$sessid.'.');
	}
	public function session_getexpiry($sessid='', $csrftoken='') {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('session.getexpiry') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('session.getexpiry');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$sql = $this->ilance->db->query("
		SELECT expiry
		FROM " . DB_PREFIX . "sessions
		WHERE sesskey = '" . $sessid . "'
		LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$sess = $this->ilance->db->fetch_array($sql, DB_ASSOC);	
			$this->api_success('session.getexpiry');
			return array('error' => '0', 'message' => 'Success', 'sessexpiry' => $sess['expiry']);
		}
		$this->api_failed('session.getexpiry');
		return array('error' => '1', 'message' => 'Could not find Expiry Associated with session id ' .$sessid.'.');
	}
	

	public function system_getlanguages($guestcsrftoken='') 
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getlanguages') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getlanguages');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT languageid, title, languagecode, charset, locale, textdirection, languageiso
			FROM " . DB_PREFIX . "language
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$languages[] = $res;
			}
			$this->api_success('system.getlanguages');
			return array('error' => '0', 'message' => 'Success', 'languages' => $languages);
		}
		$this->api_failed('system.getlanguages');
		return array('error' => '1', 'message' => 'Could not find Any Language.', 'languages' => array());
	}

	public function system_getphrase($var, $lng, $csrftoken) 
	{
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getphrase');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$slng = substr($lng,0,3);
		$sql = $this->ilance->db->query("
			SELECT text_$slng AS text
			FROM " .DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");

		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$phrase = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$this->api_success('system.getphrase');
			return array('error' => '0', 'message' => 'Success', 'phrase' => $phrase);
		}
		$this->api_failed('system.getphrase');
		return array('error' => '1', 'message' => 'Could not find Any Phrase.', 'phrase' => array());
	}

	public function getmessage($var, $lng) 
	{
		$finalmessage='';
		$sqllang = $this->ilance->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language
			where languageid ='".$lng."'
		");
		if ($this->ilance->db->num_rows($sqllang) > 0) {
			$reslang = $this->ilance->db->fetch_array($sqllang, DB_ASSOC);
			$slng = substr($reslang['languagecode'],0,3);
		}
		$sql = $this->ilance->db->query("
			SELECT text_$slng AS text
			FROM " .DB_PREFIX . "language_phrases
			WHERE varname IN ('" . $var . "')
			LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$message = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$finalmessage = $message['text'];
	
		}
		return $finalmessage;
	}

	public function system_getloginphrase($lng, $phrasegroup, $guestcsrftoken) 
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getloginphrase') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getloginphrase');
			return array('error' => '1', 'message' => 'Content Management System is not available with provided security.');
		}
		if (is_numeric($lng)) {
			$sqllang = $this->ilance->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language where languageid ='".$lng."'
			LIMIT 1
			");

			if ($this->ilance->db->num_rows($sqllang) > 0) {
				$langrecord = $this->ilance->db->fetch_array($sqllang, DB_ASSOC);
				$lang= $langrecord['languagecode'];
			}
			$slng = substr($lang,0,3);
		}
		else {
			$slng = substr($lng,0,3);	
		}
		$sql = $this->ilance->db->query("
				SELECT varname, text_$slng AS text
				FROM " .DB_PREFIX . "language_phrases
				WHERE phrasegroup = ('".$phrasegroup."')
			");

		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$res['text'] = str_replace(array("\r\n", "\n", "\r"), '', $res['text']);
				$phrase[$res['varname']] = stripslashes($this->ilance->common->un_htmlspecialchars($res['text']));
			}
			$this->api_success('system.getloginphrase');
			return array('error' => '0', 'message' => 'Success', 'phrase' => $phrase);
		}
		$this->api_failed('system.getloginphrase');
		return array('error' => '1', 'message' => 'Could not find Any Login Phrase.', 'phrase' => array());
	}


	public function system_getappphrase($lng, $phrasegroup, $guestcsrftoken) 
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getappphrase') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getappphrase');
			return array('error' => '1', 'message' => 'Content Management System is not available with provided security.');
		}
		if (is_numeric($lng)) {
			$sqllang = $this->ilance->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language where languageid ='".$lng."'
			LIMIT 1
			");

			if ($this->ilance->db->num_rows($sqllang) > 0) {
				$langrecord = $this->ilance->db->fetch_array($sqllang, DB_ASSOC);
				$lang= $langrecord['languagecode'];
			}
			$slng = substr($lang,0,3);
		}
		else {
			$slng = substr($lng,0,3);	
		}
		$sql = $this->ilance->db->query("
				SELECT varname, text_$slng AS text
				FROM " .DB_PREFIX . "language_phrases
				WHERE phrasegroup = ('".$phrasegroup."')
			");

		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$res['text'] = str_replace(array("\r\n", "\n", "\r"), '', $res['text']);
				$phrase[$res['varname']] = stripslashes($this->ilance->common->un_htmlspecialchars($res['text']));
			}
			$this->api_success('system.getappphrase');
			return array('error' => '0', 'message' => 'Success', 'phrase' => $phrase);
		}
		$this->api_failed('system.getappphrase');
		return array('error' => '1', 'message' => 'Could not find Any App Phrase.', 'phrase' => array());
	}


	public function user_signin($username, $password, $apikey, $csrftoken, $rememberuser, $devicetoken)
	{ 

		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.signin') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.signin');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
		SELECT status
		FROM " . DB_PREFIX . "users 
		WHERE (username = '" . $this->ilance->db->escape_string($username) . "' OR email = '" . $this->ilance->db->escape_string($username) . "' OR companyname = '" . $this->ilance->db->escape_string($username) . "')
			" . ((!empty($apikey)) ? "AND apikey = '" . $this->ilance->db->escape_string($apikey) . "' AND useapi = '1'" : "") . "
		LIMIT 1
		");

		if ($this->ilance->db->num_rows($sql) > 0) {
			$userinfo = $this->ilance->db->fetch_array($sql, DB_ASSOC);

			if ($userinfo['status'] == 'unverified') {
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_you_have_not_activated_your_account_via_email', $_SESSION['ilancedata']['user']['languageid']));
			}

			if ($userinfo['status'] == 'banned') {
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_this_account_has_been_banned', $_SESSION['ilancedata']['user']['languageid']));
			}
		}
		if ($this->authorize($username, $password, $apikey)) {
			$usertoken = $this->ilance->common->login($username, $password, $apikey, $guestcsrftoken, true, $rememberuser, true, $devicetoken);

			if ($usertoken == '' )
			{
				$this->api_failed('user.signin');
				return array('error' => '1', 'message' => $this->getmessage('_app_user_password_incorrect', $_SESSION['ilancedata']['user']['languageid']));
			}
			$this->api_success('user.signin');
			return array('error' => '0', 'message' => 'Authentication successful.', 'sessid' => session_id() , 'csrftoken' => $usertoken);
		}
		else {
			$this->api_failed('user.signin');
			return array('error' => '1', 'message' => $this->getmessage('_app_user_password_incorrect', $_SESSION['ilancedata']['user']['languageid']));
		}

		$this->api_failed('user.signin');
		return array('error' => '1', 'message' => 'Could not authenticate. General Error');
	}

	public function user_signout($sessid)
	{ 
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.signout') . "'
				LIMIT 1
			");
		if ($sessid == session_id())
		{
			$this->ilance->common->logout($sessid);
			$this->api_success('user.signout');
			return array('error' => '0', 'message' => 'Sign-out successful.');
		}
		$this->api_failed('user.signout');
		return array('error' => '1', 'message' => 'Sign-out requires a valid sessid.');
	}

	public function user_register($username, $password, $email, $firstname, $lastname, $address, $address2, $phone, $country, $city, $state, $zipcode, $currency, $acceptsmarketing, $secretquestion, $secretanswer, $dob, $gender, $csrftoken)
	{ 
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.register') . "'
				LIMIT 1
			");
		$ccode='';
		$sql = $this->ilance->db->query("
			SELECT cc
			FROM " . DB_PREFIX . "locations 
			WHERE visible = '1' AND locationid ='".$country."'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);

		if ($this->ilance->db->num_rows($sql) > 0) {
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
				$ccode= $res['cc'];
			}
		unset($sql);
		
		$status = $this->ilance->registration->register($username, $password, $email, $firstname, $lastname, $address, $address2, $phone, $ccode, $city, $state, $zipcode, $currency, 'XML-RPC', $acceptsmarketing, $secretquestion,  $secretanswer, $dob, $gender);
		// 1 = active, 2 = unverified, 3 = moderated or string for error output message
		$message = '';
		if ($status == '1' || $status == '2' || $status == '3')
		{
			
			switch ($status)
                        {
                                case '1':
                                {
								   $message = 'Account Successfully Registered.';
								   break;
                                }
                                case '2':
                                {
									$message = 'Account Successfully Registered. Please Verify Your Email Before Logging In.';
									break;
                                }
                                case '3':
                                {
									$message = 'Account Successfully Registered. Please Hold For Activation By Our Staff.';
									break;
								}
								default:
								{
									$message = 'Error Creating Account.';
									break;
								} 

							
                        }

		}
		else
		{
			$this->api_failed('user.register');
			return array('error' => '1', 'message' => 'Registration Failed due to Unkonwn Status.');
		}
		//$timezone = geoip_time_zone_by_country_and_region('CA', 'QC');
		$this->api_success('user.register');
		return array('error' => (($status == '1'|| $status == '2' || $status == '3') ? '1' : '1'), 'message' => $message, 'status' => $status);
	}

	public function system_getheros($mode='homepage', $csrftoken) 
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getheros') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.getheros');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$heros = $this->ilance->auction_listing_app->fetch_heros($mode);
		$pathfile = substr($this->ilance->config['imguploadscdn'],1,strlen($this->ilance->config['imguploadscdn'])-1);
		$server = HTTPS_SERVER;
		foreach ($heros as $key => $val) {
			$heros[$key]['url']= $server.$pathfile.'heros/'.$heros[$key]['filename'];
		}
		if (count($heros) > 0)
		{
			$this->api_success('system.getheros');
			return array('error' => '0', 'message' => 'Success', 'heros' => $heros);
		}
		$this->api_failed('system.getheros');
		return array('error' => '1', 'message' => 'Could not find Any Hero(s).');
	}

	public function system_checkconfig($configoption, $csrftoken) {
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.checkconfig');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if (!empty($this->ilance->config[$configoption])){
			return array('error' => '0', 'message' => 'Success', 'option' => $this->ilance->config[$configoption]);
		}
		else {
			return array('error' => '0', 'message' => 'Option Variable Not Set', 'option' => $this->ilance->config[$configoption]);
		}

	}
	public function system_checkusername($username='', $guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.checkusername') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.checkusername');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		if ($this->ilance->common->is_username_banned($username))
            {
				$username_blocked_note='';
				if (!empty($this->ilance->common->username_errors[0])){
					$this->ilance->template->templateregistry['phrase']=$this->ilance->common->username_errors[0];
					$username_blocked_note=$this->ilance->template->parse_template_phrases('phrase');
				}
				else {
					$username_blocked_note= $this->getmessage('_app_message_username_invalid_characters', $_SESSION['ilancedata']['user']['languageid']);
					
				}
               	$this->api_failed('system.checkusername');
				return array('error' => '1', 'message' => $username_blocked_note);
            }
            else
            {
                $sqlusercheck = $this->ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "users
										WHERE username = '" . $this->ilance->db->escape_string($username) . "'
										LIMIT 1
                                ");
                if ($this->ilance->db->num_rows($sqlusercheck) > 0)
                {
					$this->api_failed('system.checkusername');
                    return array('error' => '1', 'message' => $this->getmessage('_app_message_username_exist', $_SESSION['ilancedata']['user']['languageid']));
                }
                else
                {
					$this->api_success('system.checkusername');
                    return array('error' => '0', 'message' => $this->getmessage('_app_message_username_available', $_SESSION['ilancedata']['user']['languageid']));
                }
            }
	}

	public function system_checkemail($email='', $guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.checkemail') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.checkemail');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		$sqlusercheck = $this->ilance->db->query("
								SELECT user_id
								FROM " . DB_PREFIX . "users
								WHERE email = '" . $this->ilance->db->escape_string($email) . "'
								LIMIT 1
						");
		if ($this->ilance->db->num_rows($sqlusercheck) > 0)
		{
			$this->api_failed('system.checkemail');
			return array('error' => '1', 'message' =>  $this->getmessage('_app_message_email_exist', $_SESSION['ilancedata']['user']['languageid']));
		}
		else
		{
			$this->api_success('system.checkemail');
			return array('error' => '0', 'message' =>  $this->getmessage('_app_message_email_available', $_SESSION['ilancedata']['user']['languageid']));
		}
            
	}

	public function system_getcurrencies($csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getcurrencies') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getcurrencies');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT currency_id, currency_abbrev AS code, symbol_left, symbol_right, symbol_local, decimal_point, thousands_point, decimal_places, decimal_places_local, rate, currency_name, currency_abbrev, currency_subunit, iscrypto
			FROM " . DB_PREFIX . "currency
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$currencies[] = $res;
			}
			$this->api_success('system.getcurrencies');
			return array('error' => '0', 'message' => 'Success', 'currencies' => $currencies);
		}
		$this->api_failed('system.getcurrencies');
		return array('error' => '1', 'message' => 'Could not find Any Currency.', 'currencies' => array());
	}

	public function system_getcountries($csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getcountries') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getcountries');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT l.locationid, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location, l.regionid, r.region_" . $_SESSION['ilancedata']['user']['slng'] . " AS region, l.cc
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.visible = '1'
			ORDER BY location_" . $_SESSION['ilancedata']['user']['slng'] . "
			
		", 0, null, __FILE__, __LINE__);

		if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$countries[] = $res;
			}
			$this->api_success('system.getcountries');
			unset($sql);
			return array('error' => '0', 'message' => 'Success', 'countries' => $countries);
		}
		$this->api_failed('system.getcountries');
		return array('error' => '1', 'message' => 'Could not find Any Country.');
	}

	public function system_getsps($countryid = '', $csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getsps') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getsps');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT state, sc
			FROM " . DB_PREFIX . "locations_states
			WHERE locationid = '" . intval($countryid) . "'
			    AND visible = '1'
		");

		if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$sp[] = $res;
			}
			$this->api_success('system.getsps');
			unset($sql);
			return array('error' => '0', 'message' => 'Success', 'sp' => $sp);
		}
		$this->api_failed('system.getsps');
		return array('error' => '1', 'message' => 'Could not find Any State/Province.');
	}

	public function system_getcities($countryid = '', $sp='', $csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.c') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getcities');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT id, city
			FROM " . DB_PREFIX . "locations_cities
			WHERE locationid = '" . intval($countryid) . "'
			    AND state = '" . $sp . "' AND visible = '1'
		");

		if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$cities[] = $res;
			}
			$this->api_success('system.getcities');
			unset($sql);
			return array('error' => '0', 'message' => 'Success', 'cities' => $cities);
		}
		$this->api_failed('system.getcities');
		return array('error' => '1', 'message' => 'Could not find Any Cities.');
	}

	public function system_getsecretquestions($csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getsecretquestions') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getsecretquestions');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$secretquestionsorig = $this->ilance->template->meta['secretquestions'];
		$secretquestions  = array();

		foreach ($secretquestionsorig as $key => $value) {
			if ($key!=0) {
				$secretquestions[$key]['id'] = $key;
				$secretquestions[$key]['value'] = $this->getphrase(substr($value, 1, -1), 'english') ;
			}	 
		}
		$this->api_success('system.getsecretquestions');
		return array('error' => '0', 'message' => 'Success', 'secretquestions' => $secretquestions);
	}

	public function system_getcaptcha($guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getcaptcha') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getcaptcha');
			return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
		}
		$this->api_success('system.getcaptcha');
		return array('error' => '0', 'message' => 'Success', 'captchapath' => HTTPS_SERVER . 'attachment/captcha/');
	}

	public function system_validatecaptcha($captcha, $guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.validatecaptcha') . "'
				LIMIT 1
			");
			if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
				$this->api_failed('system.validatecaptcha');
				return array('error' => '1', 'message' => 'Token ' .$guestcsrftoken.' is not valid.');
			}
			if ($captcha==$_SESSION['ilancedata']['user']['captcha']) {
				$this->api_success('system.validatecaptcha');
				return array('error' => '0', 'message' => 'Success.');
			}
			else {
				$this->api_failed('system.validatecaptcha');
				return array('error' => '1', 'message' => 'Failed.');
			}
	}

	public function user_forgotpassword ($useremail,$guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.forgotpassword') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.forgotpassword');
			return array('error' => '1', 'message' => 'Activity is not available with provided security.');
		}
		$sql = $this->ilance->db->query("
					SELECT user_id, email, username, first_name, last_name, phone, status
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $useremail . "'
					LIMIT 1
				");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$user = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$this->ilance->email->mail = $user['email'];
			$this->ilance->email->slng = $this->ilance->language->fetch_user_slng($user['user_id']);
			$this->ilance->email->get('forgot_password');
			$this->ilance->email->set(array(
				'{{username}}' => $user['username'],
				'{{user_id}}' => $user['user_id'],
				'{{first_name}}' => $user['first_name'],
				'{{last_name}}' => $user['last_name'],
				'{{code}}' => $this->ilance->code->get_code($user['user_id'], '2', 5)['code']
			));
			$this->ilance->email->send();
			$this->api_success('user.forgotpassword');
			return array('error' => '0', 'message' => 'Verification Code Successfully Sent', 'expiry' => $this->ilance->code->get_code($user['user_id'], '2')['expiry']);
		}
		else {
			$this->api_failed('user.forgotpassword');
			return array('error' => '1', 'message' => 'User Email Not Found.');
		}
	}

	public function user_verifyotp ($useremail, $usercode, $group='RST',$guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.verifyotp') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.verifyotp');
			return array('error' => '1', 'message' => 'Activity is not available with provided security.');
		}
		$sql = $this->ilance->db->query("
			SELECT user_id, email, username, status
			FROM " . DB_PREFIX . "users
			WHERE email = '" . $useremail . "'
			LIMIT 1
			");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$user = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$sqlgroup = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "code_groups
			WHERE code = '" . $group . "'
			LIMIT 1
			");
	
			if ($this->ilance->db->num_rows($sqlgroup) > 0) {
				$codegroup = $this->ilance->db->fetch_array($sqlgroup, DB_ASSOC);
				$dbcode = $this->ilance->code->get_code($user['user_id'], '2');
				if ($usercode == $dbcode['code']) {
					$this->ilance->code->set_verified($dbcode['id']);
					$this->api_success('user.verifyotp');
					return array('error' => '0', 'message' => 'Code is Valid', 'code' => $dbcode);
				}
				else {
					$this->api_failed('user.verifyotp');
					return array('error' => '1', 'message' => 'Code is Invalid');
				}
			}
			else {
				$this->api_failed('user.verifyotp');
				return array('error' => '1', 'message' => 'No associated OTP Group');
			}
		}
		else {
			$this->api_failed('user.verifyotp');
			return array('error' => '1', 'message' => 'User Email Not Found.');
		}

	}

	public function user_changepassword($useremail, $usercode, $codeid, $newpassword,$guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.changepassword') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.changepassword');
			return array('error' => '1', 'message' => 'Activity is not available with provided security.');
		}
		$sql = $this->ilance->db->query("
			SELECT user_id, email, username, status
			FROM " . DB_PREFIX . "users
			WHERE email = '" . $useremail . "'
			LIMIT 1
			");
			if ($this->ilance->db->num_rows($sql) > 0) {
				$user = $this->ilance->db->fetch_array($sql, DB_ASSOC);
				if ($this->ilance->code->isverified($codeid, $usercode, $user['user_id'])) {
				$usersalt = $this->ilance->construct_password_salt(5);						
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET salt ='". $this->ilance->db->escape_string($usersalt) ."', password = '". md5(md5($newpassword) . $usersalt). "'
					WHERE user_id = '" . intval($user['user_id']) . "'
					LIMIT 1
				");				

				$this->api_success('user.changepassword');
				return array('error' => '0', 'message' => 'Password Successfully Changed');

				}

				else {
				$this->api_failed('user.changepassword');
				return array('error' => '1', 'message' => 'Security issue with provided code.');
				}

			}
			else {
				$this->api_failed('user.changepassword');
				return array('error' => '1', 'message' => 'User Email Not Found.');
			}
	}

	public function system_getcontents($lng='eng',$guestcsrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('system.getcontents') . "'
				LIMIT 1
			");
		if ($guestcsrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('system.getcontents');
			return array('error' => '1', 'message' => 'Content URLs are not available with provided security.');
		}
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) {
			$sql = $this->ilance->db->query("
				SELECT title, seourl
				FROM " . DB_PREFIX . "content
				WHERE visible = '1'
					AND ispublished = '1'
				ORDER BY sort ASC
			");
		}
		else {
			$sql = $this->ilance->db->query("
				SELECT title, seourl
				FROM " . DB_PREFIX . "content
				WHERE visible = '1'
					AND ispublished = '1'
					AND membersonly= '0'
				ORDER BY sort ASC
			");
		}	
        if ($this->ilance->db->num_rows($sql) > 0) {
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC)) {
				$res['seourl']= HTTPS_SERVER . 'content/' . $res['seourl'] . '.html?ismobile=1';
				$contents[] = $res;	
			}
			$this->api_success('system.getcontents');
			return array('error' => '0', 'message' => 'Success', 'contents' => $contents);
		}
		else {
			$this->api_failed('system.getcontents');
			return array('error' => '1', 'message' => 'No Contents Found.');
		}
	}

	public function listing_get($cid, $storeid, $keywords, $csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$itemlist = $this->ilance->auction_listing_app->fetch_items($limit, $page, $storeid ,$cid, $keywords, '300x280');
		if (count($itemlist) > 0)
		{
			$this->api_success('listing.get');
			return array('error' => '0', 'message' => 'Success', 'itemlist' => $itemlist, 'numberofrecords' => count($itemlist));
		}
		$this->api_failed('listing.get');
		return array('error' => '1', 'message' => 'Could not find Any Items.');
	}

	public function listing_staffpicks_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.staffpicks.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.staffpicks.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$staffpicks = $this->ilance->auction_listing_app->fetch_staff_picks($limit, $page , 0, '', '300x280');
		$rows = $this->ilance->auction_listing_app->fetch_count_staff_picks(0, '');
		if (count($staffpicks) > 0)
		{
			$this->api_success('listing.staffpicks.get');
			return array('error' => '0', 'message' => 'Success', 'staffpicks' => $staffpicks , 'numberofrecords' => $rows);
		}
		$this->api_failed('listing.staffpicks.get');
		return array('error' => '1', 'message' => 'Could not find Any StaffPick(s).');
	}

	public function listing_featured_get($csrftoken, $storeid, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.featured.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.featured.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$featured = $this->ilance->auction_listing_app->fetch_featured_auctions('product', $storeid, $limit, $page, 0, '', array(), '300x280');
		$rows = $this->ilance->auction_listing_app->fetch_count_featured_auctions(0, '');
		if (count($featured) > 0)
		{
			$this->api_success('listing.featured.get');
			return array('error' => '0', 'message' => 'Success', 'featured' => $featured, 'numberofrecords' => $rows);
		}
		$this->api_failed('listing.featured.get');
		return array('error' => '1', 'message' => 'Could not find Any featured listings.');
	}

	public function listing_related_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.related.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.related.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$related = $this->ilance->auction_listing_app->fetch_related_auctions($limit, $page, 0, '', '300x280');
		
		if (count($related) > 0)
		{
			$this->api_success('listing.related.get');
			return array('error' => '0', 'message' => 'Success', 'related' => $related, 'numberofrecords' => count($related));
		}
		$this->api_failed('listing.related.get');
		return array('error' => '1', 'message' => 'Could not find Any Related Item(s).');
	}
	
	public function listing_watchlist_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.watchlist.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.watchlist.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$userid = ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
		$watchlist = $this->ilance->auction_listing_app->fetch_items_from_seller_watchlist('product', $limit, $page, $userid, 0, '', '300x280');
		
		if (count($watchlist) > 0)
		{
			$this->api_success('listing.watchlist.get');
			return array('error' => '0', 'message' => 'Success', 'watchlist' => $watchlist, 'numberofrecords' => count($watchlist));
		}
		$this->api_failed('listing.watchlist.get');
		return array('error' => '1', 'message' => 'Could not find Any Watchlist Item(s).');
	}
	public function listing_latest_get($csrftoken, $storeid, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.latest.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.latest.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$latest = $this->ilance->auction_listing_app->fetch_latest_auctions('product', $storeid, $limit, $page, 0, '', '300x280');
		if (count($latest) > 0)
		{
			$this->api_success('listing.latest.get');
			return array('error' => '0', 'message' => 'Success', 'latest' => $latest, 'numberofrecords' => count($latest));
		}
		$this->api_failed('listing.latest.get');
		return array('error' => '1', 'message' => 'Could not find Any Latest(s) listings.');
	}
	public function listing_endingsoon_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.endingsoon.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.endingsoon.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$endingsoon = $this->ilance->auction_listing_app->fetch_ending_soon_auctions('product', $limit, $page, 0, '', '300x280');
		if (count($endingsoon) > 0)
		{
			$this->api_success('listing.endingsoon.get');
			return array('error' => '0', 'message' => 'Success', 'endingsoon' => $endingsoon, 'numberofrecords' => count($endingsoon));
		}
		$this->api_failed('listing.endingsoon.get');
		return array('error' => '1', 'message' => 'Could not find Any ending soon listings.');
	}

	public function listing_latestcategories_get($csrftoken, $limit=9)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.latestcategories.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.latestcategories.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$latestcategories = $this->ilance->auction_listing_app->fetch_latest_categories($limit, 0);
		$rows = $this->ilance->auction_listing_app->fetch_count_latest_categories(0, '');
		if (count($latestcategories) > 0)
		{
			$this->api_success('listing.latestcategories.get');
			return array('error' => '0', 'message' => 'Success', 'latestcategories' => $latestcategories, 'numberofrecords' => $rows);
		}
		$this->api_failed('listing.latestcategories.get');
		return array('error' => '1', 'message' => 'Could not find Any latest categories.');
	}

	public function categories_get($csrftoken, $parent='0', $limit=9)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('categories.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('categories.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$categories=array();
		if ($parent=='0') {
			$categories = $this->ilance->categories_parser_app->get_parent_categories($limit);
		}
		else {
			$categories = $this->ilance->categories_parser_app->get_sub_categories($parent, $limit);
		}
		
		if (count($categories) > 0)
		{
			$this->api_success('categories.get');
			return array('error' => '0', 'message' => 'Success', 'categories' => $categories);
		}
		$this->api_failed('categories.get');
		return array('error' => '1', 'message' =>  'Could not find Any categories.');
	} 
	public function categories_get_parent($csrftoken, $cid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('categories.get.parent') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('categories.get.parent');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$category=array();
		$category = $this->ilance->categories_parser_app->get_parent_details($cid);
		if (count($category) > 0)
		{
			$this->api_success('categories.get.parent');
			return array('error' => '0', 'message' => 'Success', 'categories' => $category);
		}
		
	}
	
	public function item_get($csrftoken, $itemid,$password,$doended) {
		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "api
			SET hits = hits + 1
			WHERE name = '" . $this->ilance->db->escape_string('item.get') . "'
			LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('item.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$item=array();
		$item = $this->ilance->auction_listing_app->get_item_details($itemid, $password, $doended);
		if (count($item) > 0)
		{
			$this->api_success('item.get');
			return array('error' => '0', 'message' => 'Success', 'item' => $item);
		}
		$this->api_failed('item.get');
		return array('error' => '1', 'message' =>  'Could not find Any Item details for:'. $itemid);
	}

	public function user_addcart($itemid, $quantity, $sku, $csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.add.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.add.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart['pid'] = $itemid;
		$cart['qty'] = $quantity;
		$cart['userid'] = ((isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
		$cart['spid'] = ((isset($_SESSION['ilancedata']['user']['shipprofileid']) AND $_SESSION['ilancedata']['user']['shipprofileid'] > 0) ? $_SESSION['ilancedata']['user']['shipprofileid'] : 0);
		$cart['bpid'] = ((isset($_SESSION['ilancedata']['user']['billprofileid']) AND $_SESSION['ilancedata']['user']['billprofileid'] > 0) ? $_SESSION['ilancedata']['user']['billprofileid'] : 0);
		$cart['sellerid'] = $this->ilance->auction->fetch_auction('user_id', $itemid);
		$cart['sku'] = $sku;
		$cart['variants'] = '';
		$cart['output'] = 'api'; 
		$response = $this->ilance->cart_app->add($cart); 
		$tmp = explode('|', $response);
		if (isset($tmp[0]) AND ($tmp[0] == 'success' OR $tmp[0] == 'successexceeded' OR $tmp[0] == 'successsaved'))
		{ 
			$this->api_success('user.add.cart');
			return array('error' => '0', 'message' => 'Success', 'cart' => $cart);
		}
		$this->api_failed('user.add.cart');
		return array('error' => '1', 'message' => 'Error Adding to Cart. ' . $tmp[1]);
	}

	public function user_removecart($cartid, $csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.remove.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.remove.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart['cartid'] = $cartid;
		$cart['userid'] = ((isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
		$cart['output'] = 'api'; 
		$response = $this->ilance->cart_app->remove($cart); 
		$tmp = explode('|', $response);
		if (isset($tmp[0]) AND ($tmp[0] == 'success'))
		{ 
			$this->api_success('user.remove.cart');
			return array('error' => '0', 'message' => $tmp[1]);
		}
		$this->api_failed('user.remove.cart');
		return array('error' => '1', 'message' => $tmp[1]);
	}

	public function user_savelatercart($cartid, $csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.savelater.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.savelater.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart['cartid'] = $cartid;
		$cart['userid'] = ((isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
		$cart['output'] = 'api'; 
		$response = $this->ilance->cart_app->save($cart); 
		$tmp = explode('|', $response);
		if (isset($tmp[0]) AND ($tmp[0] == 'success'))
		{ 
			$this->api_success('user.savelater.cart');
			return array('error' => '0', 'message' => $tmp[1]);
		}
		$this->api_failed('user.savelater.cart');
		return array('error' => '1', 'message' => $tmp[1]);
	}

	public function user_movesavecart($cartid, $csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.movesave.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.movesave.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart['cartid'] = $cartid;
		$cart['userid'] = ((isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
		$cart['output'] = 'api'; 
		$response = $this->ilance->cart_app->move_saved($cart); 
		$tmp = explode('|', $response);
		if (isset($tmp[0]) AND ($tmp[0] == 'success'))
		{ 
			$this->api_success('user.movesave.cart');
			return array('error' => '0', 'message' => $tmp[1]);
		}
		$this->api_failed('user.movesave.cart');
		return array('error' => '1', 'message' => $tmp[1]);
	}


	public function user_countcart($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.count.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.count.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart = $this->ilance->cart_app->fetch($_SESSION['ilancedata']['user']['userid'], array('saveforlater' => 0, 'isdeleted' => 0, 'purchased' => 0, 'wishlist' => 0));
		
		if (count($cart) > 0)
		{
            if (is_bool($cart)) {
				$this->api_failed('user.count.cart');
				return array('error' => '0', 'message' => 'Success', 'cartcount' => 0);
            }
			else {
				$this->api_success('user.count.cart');
				return array('error' => '0', 'message' => 'Success', 'cartcount' => count($cart));
				
			}	
		}
		$this->api_failed('user.count.cart');
		return array('error' => '1', 'message' =>  'Could not find Any Item(s) in Cart for user: '. $_SESSION['ilancedata']['user']['userid']);
	}

	public function user_countcartitems($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.count.cartitems') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.count.cartitems');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart = $this->ilance->cart_app->count($_SESSION['ilancedata']['user']['userid']);
		$this->api_success('user.count.cartitems');
		return array('error' => '0', 'message' => 'Success', 'cartitemscount' => $cart);
	}

	public function user_getcart($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.get.cart') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.get.cart');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart = $this->ilance->cart_app->fetch($_SESSION['ilancedata']['user']['userid'], array('saveforlater' => 0, 'isdeleted' => 0, 'purchased' => 0, 'wishlist' => 0));
		if (count($cart) > 0)
		{
            if (is_bool($cart)) {
				$this->api_failed('user.get.cart');
				return array('error' => '1', 'message' =>  'Could not find Any Item(s) in Cart for user: '. $_SESSION['ilancedata']['user']['userid']);
            }
			else {
				$this->api_success('user.get.cart');
				unset($cart['0']['picture']);
				unset($cart['0']['delete']);
				return array('error' => '0', 'message' => 'Success', 'cart' => $cart);
			}
			
		}
		$this->api_failed('user.get.cart');
		return array('error' => '1', 'message' =>  'Could not find Any Item(s) in Cart for user: '. $_SESSION['ilancedata']['user']['userid']);
	}
	public function user_getcartsaved($csrftoken)
	{ 
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.get.cart.saved') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.get.cart.saved');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$cart = $this->ilance->cart_app->fetch($_SESSION['ilancedata']['user']['userid'], array('saveforlater' => 1, 'isdeleted' => 0, 'purchased' => 0, 'wishlist' => 0));
		if (count($cart) > 0)
		{
			if (is_bool($cart)) {
				$this->api_failed('user.get.cart.saved');
				return array('error' => '1', 'message' =>  'Could not find Any Item(s) in Save for later for user: '. $_SESSION['ilancedata']['user']['userid']);
			}
			else {
				$this->api_success('user.get.cart.saved');
				unset($cart['0']['picture']);
				unset($cart['0']['delete']);
				return array('error' => '0', 'message' => 'Success', 'cart' => $cart);
			}
			
		}
		$this->api_failed('user.get.cart.saved');
		return array('error' => '1', 'message' =>  'Could not find Any Item(s) in Save for later for user: '. $_SESSION['ilancedata']['user']['userid']);
	}

	public function user_review_order($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.review.order') . "'
		LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.review.order');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$orders = $this->ilance->buynow_app->print_order_review($_SESSION);

			$response = $this->ilance->notification_app->get($_SESSION['ilancedata']['user']['userid'], 'test_notification');
			$response1 = '';
			$this->ilance->notification_app->set(array(
				'{{username}}' => $this->ilance->fetch_user('fullname', $_SESSION['ilancedata']['user']['userid']) 
			));
			if ($response) {
				$response1= $this->ilance->notification_app->send();
			}
			
		if (count($orders) > 0)
		{
			$this->api_success('user.review.order');
			return array('error' => '0', 'message' => 'Success', 'orders' => $orders);
		}
		$this->api_failed('user.review.order');
		return array('error' => '1', 'message' =>  'Could not find Any Item(s) to checkout for user: '. $_SESSION['ilancedata']['user']['userid']);
	}

	public function user_follow_seller ($csrftoken, $sellerid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.follow.seller') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.follow.seller');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->watchlist->insert_item(intval($_SESSION['ilancedata']['user']['userid']), intval($sellerid), 'mprovider', '{_added_from_listing_page}', 0, 0, 0, 0))
		{
			$this->api_success('user.follow.seller');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.follow.seller');
		return array('error' => '1', 'message' => 'Unable to follow seller with id: ' . $sellerid . '.');
	}

	public function user_unfollow_seller ($csrftoken, $sellerid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.unfollow.seller') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.unfollow.seller');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->watchlist->delete_item('seller', intval($_SESSION['ilancedata']['user']['userid']), intval($sellerid), 0))
		{
			$this->api_success('user.unfollow.seller');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.unfollow.seller');
		return array('error' => '1', 'message' => 'Unable to unfollow seller with id: ' . $sellerid . '.');
	}
	
	public function listing_stores_get($csrftoken, $limit = 4, $page = 1, $cid = 0, $anchor = 0, $featured = 0, $trending = 0, $keywords = '', $nopictures = 0, $picturedim = '150x150', $logodim = '60x60')
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.stores.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.stores.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$stores = $this->ilance->auction_listing_app->fetch_stores($limit, $page, $cid, $anchor, $featured, $trending, $keywords, $nopictures, $picturedim, $logodim);
		if (count($stores) > 0)
		{
			$this->api_success('listing.stores.get');
			return array('error' => '0', 'message' => 'Success', 'stores' => $stores, 'numberofrecords' => count($stores));
		}
		$this->api_failed('listing.stores.get');
		return array('error' => '1', 'message' => 'Could not find Any Stores.');
	}

	public function store_get_details($csrftoken, $storeid = 0, $limit=4)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('store.get.details') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('store.get.details');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$store = $this->ilance->auction_listing_app->fetch_store_details($storeid, $limit);
		if (count($store) > 0)
		{
			$this->api_success('store.get.details');
			return array('error' => '0', 'message' => 'Success', 'store' => $store);
		}
		$this->api_failed('store.get.details');
		return array('error' => '1', 'message' => 'Could not find Any Store Details.');
	}

	public function notification_unsubscribe($csrftoken, $userid, $varname)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.unsubscribe') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.unsubscribe');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->notification_app->unsubscribe_notification($userid, $varname))
		{
			$this->api_success('notification.unsubscribe');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('notification.unsubscribe');
		return array('error' => '1', 'message' => 'Unable to Unsubscribe from Selected Notification with id: ' . $varname . '.');
	}

	public function notification_subscribe($csrftoken, $userid, $varname)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.subscribe') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.subscribe');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->notification_app->subscribe_notification($userid, $varname))
		{
			$this->api_success('notification.subscribe');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('notification.subscribe');
		return array('error' => '1', 'message' => 'Unable to subscribe from Selected Notification with id: ' . $varname . '.');
	}

	public function notification_delete($csrftoken, $deviceid, $notificationid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.delete') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->notification_app->delete_notification($notificationid))
		{
			$this->api_success('notification.delete');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('notification.delete');
		return array('error' => '1', 'message' => 'Unable to delete Notification with id: ' . $notificationid . '.');
	}

	public function notification_flag($csrftoken, $deviceid, $notificationid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.flag') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.flag');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->notification_app->flag_notification($notificationid))
		{
			$this->api_success('notification.flag');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('notification.flag');
		return array('error' => '1', 'message' => 'Unable to flag Notification with id: ' . $notificationid . '.');
	}

	public function notification_get($csrftoken, $deviceid, $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$notifications = $this->ilance->notification_app->get_notifications(intval($_SESSION['ilancedata']['user']['userid']), $deviceid, $limit, $page);
		if (count($notifications) > 0)
		{
			$this->api_success('notification.get');
			return array('error' => '0', 'message' => 'Success', 'notifications' => $notifications);
		}
		$this->api_failed('notification.get');
		return array('error' => '1', 'message' => 'Could not find Any Notifications.');
	}

	public function notification_count($csrftoken, $deviceid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.count') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.count');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$notifications = $this->ilance->notification_app->get_notifications_count(intval($_SESSION['ilancedata']['user']['userid']), $deviceid);
		if (count($notifications) > 0)
		{
			$this->api_success('notification.count');
			return array('error' => '0', 'message' => 'Success', 'notifications_count' => $notifications);
		}
		$this->api_failed('notification.count');
		return array('error' => '1', 'message' => 'Could not find Any Notifications.');
	}

	public function notification_get_details($csrftoken, $deviceid, $notificationid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('notification.get.details') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('notification.get.details');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$notification = $this->ilance->notification_app->get_notification_details($deviceid, $notificationid);
		if (count($notification) > 0)
		{
			$this->api_success('notification.get.details');
			return array('error' => '0', 'message' => 'Success', 'notification' => $notification);
		}
		$this->api_failed('notification.get.details');
		return array('error' => '1', 'message' => 'Could not find Any Details for notification: ' .$notificationid. '.');
	}


	public function listing_search($csrftoken, $searchparams = array(), $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('listing.search') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('listing.search');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$searcharray = array();
		foreach($searchparams as $array)
		{
			foreach($array as $subarray)
			{
				foreach($subarray as $key => $value)
				{
					$searcharray[$key] = $value;
				} 
			}	   
		}

		$search = $this->ilance->auction_listing_app->listing_search($searcharray, $limit, $page);

		if (count($search) > 0)
		{
			$this->api_success('listing.search');
			return array('error' => '0', 'message' => 'Success', 'search' => $search, 'numberofrecords' => count($search));
		}
		$this->api_failed('listing.search');
		return array('error' => '1', 'message' => 'Seacrh criteria did not return any Item(s).');
	}

	public function brands_get($csrftoken, $keyword, $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('brands.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('brands.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		if ($this->ilance->config['brands'] == 0)
		{
			$this->api_failed('brands.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$condition = '';
		$brands = array();
		if (isset($keyword) AND !empty($keyword))
		{
			$condition = "WHERE BRAND_NM LIKE '%" . $this->ilance->db->escape_string($keyword) . "%'";
			$this->ilance->search->handle_search_keywords($keyword, 'brand', 0, 0, false, '', 0);
		}
		if ($limit == 0) {
			$limit = $this->ilance->config['globalfilters_maxrowsdisplay'];
		}
		$offset = ($page == 0 ? 0 : $page - 1) * $limit;
		$sql = $this->ilance->db->query("
			SELECT BSIN as id, BRAND_NM as name, SLUG as slug, BRAND_LINK as url
			FROM " . DB_PREFIX . "brand
			$condition
			ORDER BY name ASC
			LIMIT $limit OFFSET $offset
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['name'] = o($res['name']);
				if (file_exists(DIR_BRANDS . $res['id'] . '.jpg'))
				{
					$res['img'] = HTTP_BRANDS . $res['id'] . '.jpg';
				}
				else
				{
					$res['img'] = HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/img_nophoto.png';
				}
				
				$res['description'] = '';
				$brands[] = $res;
			}
		}
		if (count($brands) > 0)
		{
			$this->api_success('brands.get');
			return array('error' => '0', 'message' => 'Success', 'brands' => $brands, 'numberofrecords' => count($brands));
		}
		$this->api_failed('brands.get');
		return array('error' => '1', 'message' => 'Could not find any brand(s).');
	}

	public function brand_add($csrftoken, $brandparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('brand.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('brand.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['brands'] == 0)
		{
			$this->api_failed('brands.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$bsin = $this->ilance->referral->create_referral_code(4) . rand(10, 99);
		$brandarray = array();
		foreach($brandparams as $array)
		{
			foreach($array as $key => $value)
			{
				$brandarray[$key] = $value;
			}    
		}
		if (is_array($brandarray)) {
			foreach ($brandarray as $record) {
				$imagedata = base64_decode($record['image']);
				$fileinfo = getimagesizefromstring($imagedata);
				if (isset($fileinfo[0]) AND isset($fileinfo[1]) AND $fileinfo[0] > 0 AND $fileinfo[1] > 0)
					{
						if ($fileinfo[0] != 150 AND $fileinfo[1] != 150)
						{ // check width / height limits
							$this->api_failed('brands.add');
							$this->ilance->template->templateregistry['message_a'] = '{_the_wh_for_picture_should_be_x::150x150}';
							return array('error' => '1', 'message' => $this->ilance->template->parse_template_phrases('message_a'));
						}
						if ($fileinfo[2] != 2)
						{ // check if .jpg
							$this->api_failed('brands.add');
							$this->ilance->template->templateregistry['message_a'] = '{_the_picture_type_must_be_x::.jpg}';
							return array('error' => '1', 'message' => $this->ilance->template->parse_template_phrases('message_a'));
						}
						$targetPath = DIR_BRANDS . $bsin . '.jpg';
						if (file_exists($targetPath))
						{
							unlink($targetPath);
						}
                        if (file_put_contents($targetPath, $imagedata)) {
							$this->ilance->db->query("
								INSERT INTO " . DB_PREFIX . "brand
								(BSIN, BRAND_NM, SLUG, BRAND_TYPE_CD, BRAND_LINK, VISIBLE, MODERATED)
								VALUES (
								'" . $this->ilance->db->escape_string($bsin) . "',
								'" . $this->ilance->db->escape_string($record['name']) . "',
								'" . $this->ilance->db->escape_string($this->ilance->seo->construct_seo_url_name($record['name'], true)) . "',
								'" . intval($this->ilance->$record['type']) . "',
								'" . $this->ilance->db->escape_string($record['url']) . "',
								'0',
								'1'
								)
							");
							$this->api_success('brands.add');
							return array('error' => '0', 'message' => 'Success');
                        }
						else {
							$this->api_failed('brands.add');
							return array('error' => '1', 'message' => 'Could not write file');
						}
					}
			}
		}
		$this->api_failed('brands.add');
		return array('error' => '1', 'message' => 'Could not create suggested brand');
	}

	public function brand_get_listing($csrftoken, $bsin , $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('brand.get.listing') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('brand.get.listing');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['brands'] == 0)
		{
			$this->api_failed('brands.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		if (isset($bsin) AND !empty($bsin)) {
			$searcharray = array();
			$searcharray['bsin'] = $bsin;
			$branditems = $this->ilance->auction_listing_app->listing_search($searcharray, $limit, $page);
		}
		if (count($branditems) > 0)
		{
			$this->api_success('brand.get.listing');
			return array('error' => '0', 'message' => 'Success', 'search' => $branditems, 'numberofrecords' => count($branditems));
		}
		$this->api_failed('brand.get.listing');
		return array('error' => '1', 'message' => 'No Item(s) found for brand ' . $bsin . '.');
	}

	public function nonprofits_get($csrftoken, $keyword, $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('nonprofits.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('nonprofits.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['enablenonprofits'] == 0)
		{
			$this->api_failed('nonprofits.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$condition = '';
		$nonprofits = array();
		if (isset($keyword) AND !empty($keyword))
		{
			$condition = "AND title LIKE '%" . $this->ilance->db->escape_string($keyword) . "%'";
		}
		if ($limit == 0) {
			$limit = $this->ilance->config['globalfilters_maxrowsdisplay'];
		}
		$offset = ($page == 0 ? 0 : $page - 1) * $limit;
		$sql = $this->ilance->db->query("
			SELECT charityid, seourl, title, description, url, donations, earnings, visible, logo
			FROM " . DB_PREFIX . "charities
			WHERE visible = '1'
			$condition
			ORDER BY title ASC
			LIMIT $limit OFFSET $offset
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
				while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$res['description'] = $this->ilance->shorten($res['description'], 150);
					$res['logo'] = HTTPS_SERVER . $this->ilance->config['imguploadscdn'] . 'nonprofit/' . $res['logo']; 
					$res['seourl'] = HTTPS_SERVER . 'nonprofits/' . $res['seourl'] . '/';
					$nonprofits[] = $res;
				}
		}
		if (count($nonprofits) > 0)
		{
			$this->api_success('nonprofits.get');
			return array('error' => '0', 'message' => 'Success', 'nonprofits' => $nonprofits, 'numberofrecords' => count($nonprofits));
		}
		$this->api_failed('nonprofits.get');
		return array('error' => '1', 'message' => 'Could not find any brand(s).');
	}

	public function nonprofit_get_listing($csrftoken, $charityid=0 , $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('nonprofit.get.listing') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('nonprofit.get.listing');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['enablenonprofits'] == 0)
		{
			$this->api_failed('nonprofit.get.listing');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$searcharray = array();
		if ($charityid > 0) {
			$searcharray['donation'] = 1;
			$searcharray['charityid'] = $charityid;
			$nonprofititems = $this->ilance->auction_listing_app->listing_search($searcharray, $limit, $page);
		}
		
		if (count($nonprofititems) > 0)
		{
			$this->api_success('nonprofit.get.listing');
			return array('error' => '0', 'message' => 'Success', 'search' => $nonprofititems, 'numberofrecords' => count($nonprofititems));
		}
		$this->api_failed('nonprofit.get.listing');
		return array('error' => '1', 'message' => 'No Item(s) found for charity ' . $charityid . '.');
	}

	public function auctions_get($csrftoken, $keyword='', $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('auctions.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('auctions.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['auctionevents'] == 0)
		{
			$this->api_failed('auctions.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		
		$auctionevents = $this->ilance->auction_listing_app->fetch_auctions($limit, $page, 10, 0, '', 0, '30x30', '300x280');

		if (count($auctionevents) > 0)
		{
			$this->api_success('auctions.get');
			return array('error' => '0', 'message' => 'Success', 'auctionevents' => $auctionevents, 'numberofrecords' => count($auctionevents));
		}
		$this->api_failed('auctions.get');
		return array('error' => '1', 'message' => 'No Auctions Found.');
	}

	public function auction_get_details ($csrftoken, $auctionid) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('auction.get.details') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('auction.get.details');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['auctionevents'] == 0)
		{
			$this->api_failed('auction.get.details');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		
		$auctionevent = $this->ilance->auction_listing_app->fetch_auction_details($auctionid);

		if (count($auctionevent) > 0)
		{
			$this->api_success('auction.get.details');
			return array('error' => '0', 'message' => 'Success', 'auctionevent' => $auctionevent);
		}
		$this->api_failed('auction.get.details');
		return array('error' => '1', 'message' => 'No Auction Details Found.');
	}

	public function user_register_auction ($csrftoken, $auctionid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.register.auction') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.register.auction');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['auctionevents'] == 0)
		{
			$this->api_failed('auctions.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->watchlist->insert_item(intval($_SESSION['ilancedata']['user']['userid']), intval($auctionid), 'event', 'Auction Event Registration', 0, 0, 1, 1))
		{
			$this->api_success('user.register.auction');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.register.auction');
		return array('error' => '1', 'message' => 'Unable to register for auction with id: ' . $auctionid . '.');
	}

	public function user_unregister_auction ($csrftoken, $auctionid)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.unregister.auction') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.unregister.auction');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['auctionevents'] == 0)
		{
			$this->api_failed('auctions.get');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		if ($this->ilance->watchlist->delete_item('event', intval($_SESSION['ilancedata']['user']['userid']), 0, $auctionid))
		{
			$this->api_success('user.unregister.auction');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.unregister.auction');
		return array('error' => '1', 'message' => 'Unable to unregister for auction with id: ' . $auctionid . '.');
	}

	public function is_added_to_watchlist($csrftoken, $type, $id) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('is.added.to.watchlist') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('is.added.to.watchlist');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$condition = '';
		if ($type == 'seller') {
			$condition = "AND watching_user_id = '" . intval($id) . "'";
		}
		else if ($type == 'item') {
			$condition = "AND watching_project_id = '" . intval($id) . "'";
		}
		else if ($type == 'auction') {
			$condition = "AND watching_eventid = '" . intval($id) . "'";
		}

		$sql = $this->ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				$condition
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$this->api_success('is.added.to.watchlist');
			return array('error' => '0', 'message' => 'True');
		}
		else {
			$this->api_success('is.added.to.watchlist');
			return array('error' => '0', 'message' => 'False');
		}

		$this->api_failed('is.added.to.watchlist');
		return array('error' => '1', 'message' => 'Error getting watchlist result');
	}

	public function auction_get_listing($csrftoken, $auctionid=0 , $limit=0, $page=1)
	{
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('auction.get.listing') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('auction.get.listing');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->config['auctionevents'] == 0)
		{
			$this->api_failed('auction.get.listing');
			$this->ilance->template->templateregistry['message_a'] = '{_were_sorry_this_feature_is_currently_disabled}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$searcharray = array();
		if ($auctionid > 0) {
			$searcharray['eventid'] = $auctionid;
			$auctionitems = $this->ilance->auction_listing_app->listing_search($searcharray, $limit, $page);
		}
		
		if (count($auctionitems) > 0)
		{
			$this->api_success('auction.get.listing');
			return array('error' => '0', 'message' => 'Success', 'auctionitems' => $auctionitems, 'numberofrecords' => count($auctionitems));
		}
		$this->api_failed('auction.get.listing');
		return array('error' => '1', 'message' => 'No Item(s) found for auction ' . $auctionid . '.');
	}

	public function mystore_get ($csrftoken) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('mystore.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.get');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$mystore = $this->ilance->mystore_app->mystore_get($_SESSION['ilancedata']['user']['userid']);
		if (count($mystore) > 0) {
			$this->api_success('mystore.get');
			return array('error' => '0', 'message' => 'Success', 'mystore' => $mystore);		
		}
		else
		{
			$this->api_success('mystore.get');
			return array('error' => '0', 'message' => 'Success', 'mystore' => '0');
		}
		$this->api_failed('mystore.get');
		return array('error' => '1', 'message' => 'No Store Details Found.');
	}

	public function mystore_check_name($csrftoken, $storename) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.check.name') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.check.name');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.check.name');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$sql = $this->ilance->db->query("
				SELECT storeid
				FROM " . DB_PREFIX . "stores
				WHERE storename = '" . $this->ilance->db->escape_string($storename) . "'
			", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->api_failed('mystore.check.name');
			$this->ilance->template->templateregistry['message_a'] = '{_store_name_already_taken}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		
		$this->api_success('mystore.check.name');
		$this->ilance->template->templateregistry['message_a'] = '{_store_name_is_valid}';
		return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
	}

	public function mystore_add($csrftoken, $mystoreparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.add');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		$sql = $this->ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "stores
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->api_failed('mystore.add');
			return array('error' => '1', 'message' => 'User Store already exist.');
		}

		if ($this->ilance->mystore_app->mystore_add($_SESSION['ilancedata']['user']['userid'], $mystoreparams)) {
			$this->api_success('mystore.add');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.add');
		return array('error' => '1', 'message' => 'Could not create your store');
	}

	public function mystore_update($csrftoken, $mystoreparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.update') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.update');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		if ($this->ilance->mystore_app->mystore_update($_SESSION['ilancedata']['user']['userid'], $mystoreparams)) {
			$this->api_success('mystore.update');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('mystore.update');
		return array('error' => '1', 'message' => 'Could not update your store');
	}

	public function mystore_add_category($csrftoken, $categoryname) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.add.category') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.add.category');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.add.category');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_add_category($_SESSION['ilancedata']['user']['userid'], $categoryname)) {
			$this->api_success('mystore.add.category');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.add.category');
		return array('error' => '1', 'message' => 'Could not add new category');
	}

	public function mystore_update_category($csrftoken, $cid, $updatedname) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.update.category') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.update.category');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.update.category');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_update_category($_SESSION['ilancedata']['user']['userid'], $cid, $updatedname)) {
			$this->api_success('mystore.update.category');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.update.category');
		return array('error' => '1', 'message' => 'Could not update category');
	}

	public function mystore_delete_category($csrftoken, $cid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.delete.category') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.delete.category');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.delete.category');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_delete_category($_SESSION['ilancedata']['user']['userid'], $cid)) {
			$this->api_success('mystore.delete.category');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.delete.category');
		return array('error' => '1', 'message' => 'Could not delete category');
	}
	public function mystore_add_promocode($csrftoken, $promocodeparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.add.promocode') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.add.promocode');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.add.promocode');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_add_promocode($_SESSION['ilancedata']['user']['userid'], $promocodeparams)) {
			$this->api_success('mystore.add.promocode');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.add.promocode');
		return array('error' => '1', 'message' => 'Could not add new promocode');
	}

	public function mystore_update_promocode($csrftoken, $promocodeparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.update.promocode') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.update.promocode');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.update.promocode');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_update_promocode($_SESSION['ilancedata']['user']['userid'], $promocodeparams)) {
			$this->api_success('mystore.update.promocode');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.update.promocode');
		return array('error' => '1', 'message' => 'Could not update promocode');
	}

	public function mystore_delete_promocode($csrftoken, $promoid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.delete.promocode') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.delete.promocode');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.delete.promocode');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mystore_app->mystore_delete_promocode($_SESSION['ilancedata']['user']['userid'], $promoid)) {
			$this->api_success('mystore.delete.promocode');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.delete.promocode');
		return array('error' => '1', 'message' => 'Could not delete promocode');
	}

	public function mystore_upgrade_get($csrftoken, $upgradeparams) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mystore.upgrade.get') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mystore.upgrade.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canopenstore') == 'no')
		{
			$this->api_failed('mystore.upgrade.get');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$upgradearray = array();
		foreach($upgradeparams as $array)
		{
			foreach($array as $subarray)
			{
				foreach($subarray as $key => $value)
				{
					$upgradearray[$key] = $value;
				} 
			}	   
		}
		$successful = $this->ilance->stores->promotion_process($_SESSION['ilancedata']['user']['userid'], $upgradearray);
		if ($successful) {
			$this->api_success('mystore.upgrade.get');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mystore.upgrade.get');
		$this->ilance->template->templateregistry['error'] = '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}';
		return array('error' => '1', 'message' => $this->ilance->template->parse_template_phrases('error'));
	}

	public function mymessages_get ($csrftoken, $folder, $period, $view, $limit, $page) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('mymessages.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessages.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$mymessages = $this->ilance->mymessages_app->mymessages_get($_SESSION['ilancedata']['user']['userid'], $folder, $period, $view,  $limit, $page);
		if (count($mymessages) > 0) {
			$this->api_success('mymessages.get');
			return array('error' => '0', 'message' => 'Success', 'mymessages' => $mymessages);		
		}
		else
		{
			$this->api_success('mymessages.get');
			return array('error' => '0', 'message' => 'Success', 'mymessages' => '0');
		}
		$this->api_failed('mymessages.get');
		return array('error' => '1', 'message' => 'No Messages Found.');
	}

	public function mymessage_delete($csrftoken, $eventid, $system, $all) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessage.delete') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessage.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		if ($this->ilance->mymessages_app->mymessage_delete($_SESSION['ilancedata']['user']['userid'], $eventid, $system, $all)) {
			$this->api_success('mymessage.delete');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mymessage.delete');
		return array('error' => '1', 'message' => 'Could not delete message');
	}
	public function mymessage_archive($csrftoken, $eventid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessage.archive') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessage.archive');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		if ($this->ilance->mymessages_app->mymessage_archive($_SESSION['ilancedata']['user']['userid'], $eventid)) {
			$this->api_success('mymessage.archive');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mymessage.archive');
		return array('error' => '1', 'message' => 'Could not archive message');
	}
	public function mymessage_compose($csrftoken, $username, $subject, $message, $project_id, $event_id, $attchparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessage.compose') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessage.compose');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'pmbcompose') == 'no')
		{
			$this->api_failed('mymessages.compose');
			$this->ilance->template->templateregistry['message_a'] = '{_no_access_to_send_pm}'.'. ' .'{_it_appears_your_membership_does_not_permit_pm_composing}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->mymessages_app->mymessage_compose($_SESSION['ilancedata']['user']['userid'], $username, $subject, $message, $project_id, $event_id, $attchparams)) {
			$this->api_success('mymessage.compose');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('mymessage.compose');
		return array('error' => '1', 'message' => 'Could not send message');
	}
	public function mymessage_read($csrftoken,$project_id, $orderidpublic, $event_id) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessage.read') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessage.read');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$mymessage = $this->ilance->mymessages_app->mymessage_read($project_id, $orderidpublic, $event_id, $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['isadmin']);
		if (count($mymessage) > 0) {
			$this->api_success('mymessages.read');
			return array('error' => '0', 'message' => 'Success', 'mymessage' => $mymessage);		
		}
		else
		{
			$this->api_failed('mymessage.read');
			return array('error' => '1', 'message' => 'Could not read message');
		}		
	}

	public function mymessage_read_vm($csrftoken, $messageid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessage.read.vm') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessage.read.vm');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$mymessage = $this->ilance->mymessages_app->mymessage_read_vm($_SESSION['ilancedata']['user']['userid'],$messageid);
		if (count($mymessage) > 0) {
			$this->api_success('mymessages.read.vm');
			return array('error' => '0', 'message' => 'Success', 'mymessage' => $mymessage);		
		}
		else
		{
			$this->api_failed('mymessage.read.vm');
			return array('error' => '1', 'message' => 'Could not read vm message');
		}		
	}

	public function mymessages_count($csrftoken, $filter) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('mymessages.count') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('mymessages.count');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$count = $this->ilance->mymessages_app->mymessages_count($_SESSION['ilancedata']['user']['userid'], $filter);
		if (count($count) > 0) {
			$this->api_success('mymessages.count');
			return array('error' => '0', 'message' => 'Success', 'count' => $count);		
		}
		else
		{
			$this->api_failed('mymessages.count');
			return array('error' => '1', 'message' => 'Could not count messages');
		}		
	}

	public function myauctions_get ($csrftoken, $limit, $page) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('myauctions.get') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$myauctions = $this->ilance->myauctions_app->myauctions_get($_SESSION['ilancedata']['user']['userid'],  $limit, $page);
		if (count($myauctions) > 0) {
			$this->api_success('myauctions.get');
			return array('error' => '0', 'message' => 'Success', 'myauctions' => $myauctions);		
		}
		else
		{
			$this->api_success('myauctions.get');
			return array('error' => '0', 'message' => 'Success', 'myauctions' => '0');
		}
		$this->api_failed('myauctions.get');
		return array('error' => '1', 'message' => 'No Auctions Found.');
	}

	public function myauctions_add($csrftoken, $myauctionparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('myauctions.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'no')
		{
			$this->api_failed('myauctions.add');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		if ($this->ilance->myauctions_app->myauctions_add($_SESSION['ilancedata']['user']['userid'], $myauctionparams)) {
			$this->api_success('myauctions.add');
			return array('error' => '0', 'message' => 'Success');
		}
		
		$this->api_failed('myauctions.add');
		return array('error' => '1', 'message' => 'Could not add live auction event');
	}

	public function myauctions_publish($csrftoken, $eventid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('myauctions.publish') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.publish');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'no')
		{
			$this->api_failed('myauctions.publish');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}

		$tmp = $this->ilance->myauctions_app->myauctions_publish($_SESSION['ilancedata']['user']['userid'], intval($eventid));


		if ($tmp['error']=='0') {
			$this->api_success('myauctions.publish');
			return array('error' => '0', 'message' => $tmp['message']);
		}
		else {
			$this->api_failed('myauctions.publish');
			return array('error' => '1', 'message' => $tmp['message']);
		}
	}

	public function myauctions_preadd($csrftoken) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('myauctions.preadd') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.preadd');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'no')
		{
			$this->api_failed('myauctions.preadd');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$tmp = $this->ilance->myauctions_app->myauctions_preadd($_SESSION['ilancedata']['user']['userid']);
		if (count($tmp) > 0) {
			$this->api_success('myauctions.preadd');
			return array('error' => '0', 'myauctions_preadd' => $tmp);
		}
		$this->api_failed('myauctions.preadd');
		return array('error' => '1', 'message' => 'Error in Auction Pre-Add');
	}

	public function myauctions_update($csrftoken, $myauctionparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('myauctions.update') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'no')
		{
			$this->api_failed('myauctions.update');
			$this->ilance->template->templateregistry['message_a'] = '{_in_order_perform_action_additional_permission}';
			return array('error' => '1', 'message' =>  $this->ilance->template->parse_template_phrases('message_a'));
		}
		$tmp = $this->ilance->myauctions_app->myauctions_update($_SESSION['ilancedata']['user']['userid'], $myauctionparams);
		if ($tmp['error']=='0') {
			$this->api_success('myauctions.update');
			return array('error' => '0', 'message' => $tmp['message']);
		}
		
		$this->api_failed('myauctions.update');
		return array('error' => '1', 'message' => $tmp['message']);
	}

	public function myauctions_preupdate ($csrftoken, $eventid) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('myauctions.preupdate') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('myauctions.preupdate');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$myauction = $this->ilance->myauctions_app->myauctions_preupdate($_SESSION['ilancedata']['user']['userid'],  $eventid);
		if (count($myauction) > 0) {
			$this->api_success('myauctions.preupdate');
			return array('error' => '0', 'message' => 'Success', 'myauctions' => $myauction);		
		}
		else
		{
			$this->api_success('myauctions.preupdate');
			return array('error' => '0', 'message' => 'No Auction Found');
		}
		$this->api_failed('myauctions.preupdate');
		return array('error' => '1', 'message' => 'Error Occured with preupdate.');
	}
	
	public function user_bid_add($csrftoken, $bidamount, $minimumbid, $itemid, $currency, $paddleid)
	{ // add new bid for user
		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "api
			SET hits = hits + 1
			WHERE name = '" . $this->ilance->db->escape_string('user.bid.add') . "'
			LIMIT 1
		");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.bid.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		// add new bid
		$currency = $this->ilance->currency_app->currencies[$_SESSION['ilancedata']['user']['currencyid']]['currency_abbrev'];
		$iscrypto = ((isset($this->ilance->currency_app->currencies[$currency]['iscrypto']) AND $this->ilance->currency_app->currencies[$currency]['iscrypto']) ? true : false);
		$crypto_bidamount = $bidamount = trim($bidamount); // 0.07775305
		if ($iscrypto AND isset($this->ilance->currency_app->currencies[$currency]['rate']))
		{ // 0.07775305 => 25.00
			$bidamount = sprintf("%01.2f", $this->ilance->currency_app->convert_currency($this->ilance->config['globalserverlocale_defaultcurrency'], $crypto_bidamount, $this->ilance->currency_app->currencies[$currency]['currency_id']));
		}
		else
		{ // 25.00 => 0.07775305
			if (isset($this->ilance->currency_app->currencies[$this->ilance->config['globalserverlocale_defaultcryptocurrency']]['rate']))
			{
				$crypto_bidamount = $this->ilance->currency_app->convert_currency($this->ilance->config['globalserverlocale_defaultcryptocurrency'], $bidamount, $this->ilance->currency_app->currencies[$currency]['currency_id']);
			}
		}
		$response = $this->ilance->bid_product->placebid($itemid, $bidamount, $crypto_bidamount, $_SESSION['ilancedata']['user']['userid'], $minimumbid, 0, $paddleid, $this->ilance->currency_app->currencies[$currency]['currency_id']);
		$response = json_decode($response, true);
		if (isset($response['error']) AND $response['error'] == 0)
		{
			$this->api_success('user.bid.add');
			$this->ilance->template->templateregistry['message_a'] = $response['response'];
			return array('error' => '0', 'message' => $this->ilance->template->parse_template_phrases('message_a'), 'bid' => array('status' => 'placed'));
		}
		$this->api_failed('user.bid.add');
		$this->ilance->template->templateregistry['message_a'] = $response['response'];
		return array('error' => '1', 'message' => $this->ilance->template->parse_template_phrases('message_a'));
	}
	public function user_bid_retract($csrftoken, $bidid, $reason)
	{ // perform a bid retraction for a bidder

		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "api
			SET hits = hits + 1
			WHERE name = '" . $this->ilance->db->escape_string('user.bid.retract') . "'
			LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.bid.retract');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		// retract bid
		$userid = $this->ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", "user_id");
		$itemid = $this->ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", "project_id");
		$status = $this->ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", "bidstatus");
		$bidwon = (($status == 'awarded') ? true : false);
		if ($userid > 0 AND $itemid > 0)
		{
			$response = $this->ilance->bid_retract->process($userid, $bidid, $itemid, $reason, $bidwon, true);
			if ($response == true)
			{
				$this->api_success('user.bid.retract');
				return array('error' => '0', 'message' => 'Bid was retracted.', 'bid' => array('status' => 'retracted'));
			}
			else
			{
				$this->api_failed('user.bid.retract');
				return array('error' => '1', 'message' => $response, 'bid' => array('status' => $status));
			}
		}
		else
		{
			$this->api_failed('user.bid.retract');
			return array('error' => '1', 'message' => 'Invalid user or item id.', 'bid' => array());
		}
	}
	public function user_bid_status($csrftoken, $bidid)
	{ // get bid status for a user
		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "api
			SET hits = hits + 1
			WHERE name = '" . $this->ilance->db->escape_string('user.bid.status') . "'
			LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.bid.status');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$this->api_success('user.bid.status');
		$bidstatus = $this->ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", "bidstatus"); // placed, outbid, awarded
		$bidstate = $this->ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", "bidstate"); // '' or 'retracted'
		return array('error' => '0', 'message' => '', 'bid' => array('status' => (($bidstate == 'retracted') ? 'retracted' : $bidstatus)));
	}

	public function user_profile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.profile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.profile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$sql_user = $this->ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql_user) > 0)
		{
			$res_user = $this->ilance->db->fetch_array($sql_user, DB_ASSOC);
			$first_name = o($res_user['first_name']);
			$last_name = o($res_user['last_name']);
			$company = o($res_user['companyname']);
			$yearpulldown = $monthpulldown = $daypulldown = $dob_year = '';
			if ($this->ilance->config['registrationdisplay_dob'])
			{
				$dateofbirth = $_SESSION['ilancedata']['user']['dob'];
				$dobsplit = explode('-', $dateofbirth);
				$dob_year = $dobsplit[0];
				$dobmonth = $dobsplit[1];
				if (strlen($dobmonth) == 1 && intval($dobmonth)<10) {
					$dobmonth = '0'.$dobmonth;
				}
				$dobday = $dobsplit[2];
				if (strlen($dobday) == 1 && intval($dobday)<10) {
					$dobday = '0'.$dobday;
				}
				$dateofbirth = $dob_year.'-'.$dobmonth.'-'.$dobday;
			}
			$cb_gender_undecided = $cb_gender_male = $cb_gender_female = '';
			if ($this->ilance->config['genderactive'])
			{
				if ($res_user['gender'] == 'male' OR $res_user['gender'] == '')
				{
					$cb_gender_undecided = '';
					$cb_gender_male = '1"';
					$cb_gender_female = '';
				}
				else if ($res_user['gender'] == 'female')
				{
					$cb_gender_undecided = '';
					$cb_gender_male = '';
					$cb_gender_female = '1';
				}
			}
			$customquestions = $this->ilance->registration_questions->construct_register_questions(0, 'update', $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['roleid']);
			$regnumber = $this->ilance->fetch_user('regnumber', $_SESSION['ilancedata']['user']['userid']);
			$vatnumber = $this->ilance->fetch_user('vatnumber', $_SESSION['ilancedata']['user']['userid']);
			$dnbnumber = $this->ilance->fetch_user('dnbnumber', $_SESSION['ilancedata']['user']['userid']);
			$vars = array(
				'dob_year' => $dob_year,
				'dobmonth' => $dobmonth,
				'dobday' => $dobday,
				'cb_gender_undecided' => $cb_gender_undecided,
				'cb_gender_male' => $cb_gender_male,
				'cb_gender_female' => $cb_gender_female,
				'dateofbirth' => $dateofbirth,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'regnumber' => $regnumber,
				'vatnumber' => $vatnumber,
				'dnbnumber' => $dnbnumber,
				'company' => $company
			);

			$this->api_success('user.profile.get');
			return array('error' => '0', 'message' => 'Success', 'userprofile' => $vars);
		}
		else {
			$this->api_failed('user.profile.get');
			return array('error' => '1', 'message' => 'No Profile Data Found.');
		}
	}

	public function user_profile_update($csrftoken, $gender, $dobyear, $dobmonth, $dobday, $company, $firstname, $lastname, $regnumber, $vatnumber, $dnbnumber)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.profile.update') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.profile.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$_SESSION['ilancedata']['user']['firstname'] = o($firstname);
		$_SESSION['ilancedata']['user']['lastname'] = o($lastname);
		$_SESSION['ilancedata']['user']['fullname'] = o($firstname) . ' ' . o($lastname);
		$extraquery = $companyregnumber = $companyvatnumber = $dnbnumber = $companyname = '';
		if ($this->ilance->config['registrationdisplay_dob'] AND !empty($dobyear) AND !empty($dobmonth) AND !empty($dobday))
		{ // date of birth
			$year = intval($dobyear);
			$month = intval($dobmonth);
			$day = intval($dobday);
			$_SESSION['ilancedata']['user']['dob'] = $year . '-' . $month . '-' . $day;
			$extraquery .= "dob = '" . $year . "-" . $month . "-" . $day . "',";
		}
		if ($this->ilance->config['genderactive'] AND !empty($gender))
		{ // gender
			$extraquery .= "gender = '" . $this->ilance->db->escape_string($gender) . "',";
		}

		if (!empty($regnumber))
		{
			$companyregnumber = $regnumber;
		}
		if (!empty($vatnumber))
		{
			$companyvatnumber = $vatnumber;
		}
		if (!empty($company))
		{
			$companyname = $company;
		}
		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET first_name = '" . $this->ilance->db->escape_string($firstname) . "',
			$extraquery
			last_name = '" . $this->ilance->db->escape_string($lastname) . "',
			regnumber = '" . $this->ilance->db->escape_string($companyregnumber) . "',
			vatnumber = '" . $this->ilance->db->escape_string($companyvatnumber) . "',
			dnbnumber = '" . $this->ilance->db->escape_string($dnbnumber) . "',
			companyname = '" . $this->ilance->db->escape_string($companyname) . "'
            WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
        ", 0, null, __FILE__, __LINE__);

		$this->api_success('user.profile.update');
		return array('error' => '0', 'message' => 'Success');
	}

	public function user_currency_update($csrftoken, $currencyid)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.currency.update') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.currency.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		
		$sql = $this->ilance->db->query("
			SELECT currency_name, symbol_left, currency_abbrev
			FROM " . DB_PREFIX . "currency
			WHERE currency_id = '" . intval($currencyid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET currencyid = '" . intval($currencyid) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$_SESSION['ilancedata']['user']['currencyid'] = intval($currencyid);
			$_SESSION['ilancedata']['user']['currencyname'] = $res['currency_name'];
			$_SESSION['ilancedata']['user']['currencysymbol'] = $res['symbol_left'];
			$_SESSION['ilancedata']['user']['currency_abbrev'] = $res['currency_abbrev'];
			$this->api_success('user.currency.update');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.currency.update');
		return array('error' => '1', 'message' => 'Selected Currency is not recognized by the system');	
	}

	public function user_currency_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.currency.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.currency.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$vars = array(
			'currencyid' => $_SESSION['ilancedata']['user']['currencyid'],
			'currency_abbrev' => $this->ilance->currency_app->currencies[$_SESSION['ilancedata']['user']['currencyid']]['currency_abbrev']
		);
		$this->api_success('user.currency.get');
		return array('error' => '0', 'message' => 'Success', 'currency' => $vars);
	}

	

	public function user_language_update($csrftoken, $languageid)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.language.update') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.language.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$langdata = $this->ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($languageid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($langdata) > 0)
		{
			$this->ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET languageid = '" . intval($languageid) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
			$langinfo = $this->ilance->db->fetch_array($langdata);
			$_SESSION['ilancedata']['user']['languageid'] = $langinfo['languageid'];
			$_SESSION['ilancedata']['user']['languagecode'] = $langinfo['languagecode'];
			$_SESSION['ilancedata']['user']['slng'] = mb_substr($_SESSION['ilancedata']['user']['languagecode'] ? $_SESSION['ilancedata']['user']['languagecode'] : 'english', 0, 3);
			$this->api_success('user.language.update');
			return array('error' => '0', 'message' => 'Success');
		}
		$this->api_failed('user.language.update');
		return array('error' => '1', 'message' => 'Selected language is not recognized by the system');
	}

	public function user_account_data($csrftoken, $period)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.account.data') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.account.data');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$notices = $breminder = $sreminder = '';
		$period = !empty($period) ? intval($period) : '7';
		$temp = $this->ilance->mycp->fetch_unread_messages($_SESSION['ilancedata']['user']['userid']);
		$messagesactivity = $temp[0];
		$notices .= $temp[1]; 
		unset($temp);
		$invitationactivity = '';
		$temp = $this->ilance->feedback->feedback_activity($_SESSION['ilancedata']['user']['userid']);
		$feedbackactivity = $temp[1];
		$notices .= $temp[2];
		unset($temp);
		$bidtotal = $this->ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
		$bidsleft = max(0, ($bidtotal - $this->ilance->bid->fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
		$datereset = $this->ilance->common->print_date($this->ilance->datetimes->fetch_datetime_from_timestamp($this->ilance->db->fetch_field(DB_PREFIX . "cron", "varname = 'dailyrfp'", 'nextrun')), $this->ilance->config['globalserverlocale_globaltimeformat'], 0, 0);
		$referalactivity = $this->ilance->mycp->referral_activity($_SESSION['ilancedata']['user']['userid']);
		$countdown = $this->ilance->subscription->fetch_seconds_left($_SESSION['ilancedata']['user']['userid']);
		$membershipstatus = (($this->ilance->subscription->has_active_subscription($_SESSION['ilancedata']['user']['userid'])) ? '{_active}' : '{_inactive}');
		$this->ilance->template->templateregistry['membershipstatus'] = $membershipstatus;
		$membershipstatus =  $this->ilance->template->parse_template_phrases('membershipstatus');
		$membershipexpiry = $this->ilance->subscription->subscription_countdown_timeleft($countdown);
		$this->ilance->template->templateregistry['membershipexpiry'] = $membershipexpiry;
		$membershipexpiry =  $this->ilance->template->parse_template_phrases('membershipexpiry');
		$accountdata = $this->ilance->accounting->fetch_user_balance($_SESSION['ilancedata']['user']['userid']);
		$accountbalance = $accountdata['available_balance'];
		$accountbalance = $this->ilance->currency_app->format($accountbalance);
		$pointsavailable = ((isset($accountdata['rewardpoints'])) ? $accountdata['rewardpoints'] : '');
		$pointspending = ((isset($accountdata['rewardpointsdelay'])) ? $accountdata['rewardpointsdelay'] : '');
		$temp = $this->ilance->accounting->fetch_user_balance_owing($_SESSION['ilancedata']['user']['userid']);
		$balanceowing = $this->ilance->currency_app->format($temp['balanceowing']);
		unset($temp);
		$attachgauge = $this->ilance->attachment->print_spaceleft_gauge($_SESSION['ilancedata']['user']['userid']);
		unset($countdown);
		$vars = array(
			'accountname' => $_SESSION['ilancedata']['user']['firstname'] . ' ' . $_SESSION['ilancedata']['user']['lastname'],
			'accountbalance' => $accountbalance,
			'balanceowing' => $balanceowing,
			'membershipstatus' => $membershipstatus,
			'membershipexpiry' => $membershipexpiry,
			//'notices' => $notices,
			//'feedbackactivity' => $feedbackactivity,
			'bidsleft' => $bidsleft,
			//'messagesactivity' => $messagesactivity,
			//'datereset' => $datereset,
			//'invitationactivity' => $invitationactivity,
			//'referalactivity' => $referalactivity,
			//'attachgauge' => $attachgauge,
			'pointsavailable' => $pointsavailable,
			'pointspending' => $pointspending
		);
		$this->api_success('user.account.data');
		return array('error' => '0', 'message' => 'Success', 'accountdata' => $vars);		
	}

	public function user_password_update($csrftoken, $newpassword) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.password.update') . "'
				LIMIT 1
			");
		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.password.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT user_id, email, username, status
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
			");
		if ($this->ilance->db->num_rows($sql) > 0) {
			$usersalt = $this->ilance->construct_password_salt(5);						
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET salt ='". $this->ilance->db->escape_string($usersalt) ."', password = '". md5(md5($newpassword) . $usersalt). "'
				WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
				LIMIT 1
			");				
			$this->api_success('user.password.update');
			return array('error' => '0', 'message' => 'Password Successfully Changed');
		}
		else {
			$this->api_failed('user.password.update');
			return array('error' => '1', 'message' => 'User Id Not Found.');
		}
	}

	public function user_sellingprofile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.sellingprofile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.sellingprofile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$gender = $this->ilance->fetch_user('gender', $_SESSION['ilancedata']['user']['userid'], '', '', false);
		if ($gender == '' OR $gender == 'male')
		{
			$profile_logo =  HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/img_profile_unavailable.jpg';
		}
		else if ($gender == 'female')
		{
			$profile_logo = HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/img_profile_unavailable.jpg';
		}
		$profileattachid = 0;
		$sql_attach = $this->ilance->db->query("
			SELECT attachid, filehash
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND visible = '1'
				AND attachtype = 'profile'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql_attach) > 0)
		{
			$res_attach = $this->ilance->db->fetch_array($sql_attach, DB_ASSOC);
			$profileattachid = $res_attach['attachid'];
			$profile_logo = HTTP_ATTACHMENTS . 'profiles/' . $res_attach['filehash'] . '/160x160.jpg';
		}

		$displayprofile = $this->ilance->fetch_user('displayprofile', $_SESSION['ilancedata']['user']['userid']);
		$profilevideourl = $this->ilance->fetch_user('profilevideourl', $_SESSION['ilancedata']['user']['userid']);
		$profilevideourl = ilance_htmlentities($profilevideourl);
		$profileintro = o($this->ilance->fetch_user('profileintro', $_SESSION['ilancedata']['user']['userid'], '', '', false));
		$companyname = $this->ilance->fetch_user('companyname', $_SESSION['ilancedata']['user']['userid']);
		$usecompanyname = $this->ilance->fetch_user('usecompanyname', $_SESSION['ilancedata']['user']['userid']);
		$companyabout = o($this->ilance->fetch_user('companyabout', $_SESSION['ilancedata']['user']['userid'], '', '', false));
		$companydescription = o($this->ilance->fetch_user('companydescription', $_SESSION['ilancedata']['user']['userid'], '', '', false));
		$vars = array(
			'companyabout' => $companyabout,
			'companydescription' => $companydescription,
			'companyname' => $companyname,
			'profileintro' => $profileintro,
			'profilevideourl' => $profilevideourl,
			'displayprofile' => $displayprofile,
			'profile_logo' => $profile_logo,
			'profileattachid' => $profileattachid
		);

		$this->api_success('user.sellingprofile.get');
		return array('error' => '0', 'message' => 'Success', 'sellingprofile' => $vars);	

	}

	public function user_sellingprofile_update($csrftoken, $profileintro, $profilevideourl, $displayprofile, $usecompanyname, $companyname, $sellingprofilepicture = array())
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.sellingprofile.update') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.sellingprofile.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		if (is_array($sellingprofilepicture)) {
			foreach($sellingprofilepicture as $array) {
				foreach($array as $key => $record) {
					if (isset($record[$key]) AND !empty($record[$key])) {
						$attachdata = base64_decode($record[$key]);
						$fileinfo = getimagesizefromstring($attachdata);
						$exif = '';
						$filehash = md5(uniqid(microtime()));
						$filetype = image_type_to_extension($fileinfo[2]);
						$mimetype = 'image/'. substr($filetype, strpos($filetype, ".") + 1);    
						$filesize = strlen($attachdata);
						$filename = $_SESSION['ilancedata']['user']['firstname'].'_profile_'.$key.$filetype;
						$upload_dir = DIR_PROFILE_ATTACHMENTS;
						$newfilename = $upload_dir . $filehash . '/' . $filename;
						$foldername = $upload_dir . $filehash . '/';
						if (!is_dir($foldername))
						{
								$oldumask = umask(0);
								mkdir($foldername, 0777); // 0777
								umask($oldumask);
						}
						if (file_put_contents($newfilename, $attachdata))
						{
							$picturehash = md5_file($newfilename);
							$this->ilance->attachment->file_name = $filename;
							$extension = mb_strtolower(mb_strrchr($this->ilance->attachment->file_name, '.'));
							$degree = 0;
							if (function_exists('exif_read_data'))
							{
									$exifdata = @exif_read_data($newfilename, 0, true);
									if (!empty($exifdata))
									{
											$degree = $this->ilance->attachment->fetch_orientation_degree($exifdata);
											$exif = serialize($exifdata);
											unset($exifdata);
									}
							}
							if (!empty($fileinfo) AND is_array($fileinfo))
							{
									// picture factory resizer
									$tmp = $this->ilance->picture_factory($userid);
									$dimensions = $tmp['profile']['dimensions'];
									if (count($dimensions) > 0)
									{
											foreach ($dimensions AS $dimension)
											{
													$mwh = explode('x', $dimension);
													$rfn = $foldername . $dimension . '.jpg'; // 160x160.jpg
													$this->ilance->attachment->picture_resizer($newfilename, $mwh[0], $mwh[1], $extension, $fileinfo[0], $fileinfo[1], $rfn, $this->ilance->config['resizequality'], $degree);
													$this->ilance->attachment->watermark('profile', $rfn, $extension, '');
											}
									}
									unset($tmp, $dimensions, $dimension, $mwh, $rfn);
							}
							$folderdate = ''; // date('Y') . '/' . date('m') . '/' . date('d')
							$this->ilance->db->query("
									INSERT INTO " . DB_PREFIX . "attachment
									(attachid, attachtype, user_id, `date`, folder, filename, filetype, filetype_original, visible, counter, filesize, width, height, filehash, picturehash, ipaddress, exifdata)
									VALUES(
									NULL,
									'profile',
									'" . intval($_SESSION['ilancedata']['user']['userid']) . "',
									'" . DATETIME24H . "',
									'" . $this->ilance->db->escape_string($folderdate) . "',
									'" . $this->ilance->db->escape_string($filename) . "',
									'" . $this->ilance->db->escape_string($mimetype) . "',
									'" . $this->ilance->db->escape_string($mimetype) . "',
									'1',
									'0',
									'" . $this->ilance->db->escape_string($filesize) . "',
									'" . intval($fileinfo[0]) . "',
									'" . intval($fileinfo[1]) . "',
									'" . $this->ilance->db->escape_string($filehash) . "',
									'" . $this->ilance->db->escape_string($picturehash) . "',
									'" . $this->ilance->db->escape_string(IPADDRESS) . "',
									'" . $this->ilance->db->escape_string($exif) . "')
							", 0, null, __FILE__, __LINE__);
							
						}
					}

				}
			}
		}

		$profilevideourl = !empty($profilevideourl) ? $profilevideourl : '';
		$displayprofile = !empty($displayprofile) ? 1 : 0;
		$companyname = $companyregnumber = $companyvatnumber = $dnbnumber = '';
		$usecompanyname = 0;
		if (!empty($profileintro))
		{
			$profileintro = $this->ilance->censor->strip_vulgar_words($profileintro);
			if ($this->ilance->config['globalfilters_emailfilterpsp'])
			{
				$profileintro = $this->ilance->censor->strip_email_words($profileintro);
			}
			if ($this->ilance->config['globalfilters_domainfilterpsp'])
			{
				$profileintro = $this->ilance->censor->strip_domain_words($profileintro);
			}
		}
		if (!empty($usecompanyname) AND !empty($companyname))
		{
			$usecompanyname = 1;
		}
		if (!empty($companyname))
		{
			$companyname = $companyname;
		}
		$freelancing = 'individual';
		if ($usecompanyname)
		{
			$freelancing = 'business';
		}
		$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET displayprofile = '" . $displayprofile . "',
			profilevideourl = '" . $this->ilance->db->escape_string($profilevideourl) . "',
			profileintro = '" . $this->ilance->db->escape_string($profileintro) . "',
			companyname = '" . $this->ilance->db->escape_string($companyname) . "',
			usecompanyname = '" . intval($usecompanyname) . "',
			freelancing = '" . $this->ilance->db->escape_string($freelancing) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);

		$this->api_success('user.sellingprofile.update');
		return array('error' => '0', 'message' => 'Success');
	}

	public function user_sellingprofile_attachment_remove($csrftoken, $attachid)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.sellingprofile.attachment.remove') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.sellingprofile.attachment.remove');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		if ($this->ilance->attachment->remove_attachment(intval($attachid), $_SESSION['ilancedata']['user']['userid'])) {
			$this->api_success('user.sellingprofile.attachment.remove');
			return array('error' => '0', 'message' => 'Success');
		}
		else {
			$this->api_failed('user.sellingprofile.attachment.remove');
			return array('error' => '1', 'message' => 'Error Removing Profile Attachment');
		}
	}

	public function user_shippingprofile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$shipprofiles = array();
		$sql = $this->ilance->db->query("
			SELECT id, first_name, last_name, address, address2, phone, city, state, country, zipcode, type, isdefault
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND type = 'shipping'
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['name'] = o($res['first_name']) . ' ' . o($res['last_name']);
				$shipprofiles[] = $res;
			}
		}
		else {
			$this->api_failed('user.shippingprofile.get');
			return array('error' => '1', 'message' => 'No Shipping Profiles Found.');
		}
		$this->api_success('user.shippingprofile.get');		
		return array('error' => '0', 'message' => 'Success', 'shippingprofile' => $shipprofiles);	
	}

	public function user_shippingprofile_delete($csrftoken, $spid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.delete') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $spid . "'
				AND type = 'shipping'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			DELETE FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . intval($spid) . "'
				AND type = 'shipping'
			");
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "carts
				SET spid = '0'
				WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND spid = '" . intval($spid) . "'
			");
			$sql_profiles = $this->ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "shipping_profiles
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND type = 'shipping'
				ORDER BY id DESC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->ilance->db->num_rows($sql_profiles) > 0)
			{
				$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "shipping_profiles
					SET isdefault = '1'
					WHERE id = '" . $resx['id'] . "'
				");
				$_SESSION['ilancedata']['user']['shipprofileid'] = $resx['id'];
			}
			$_SESSION['ilancedata']['user']['countryids'] = $this->ilance->shipping_app->fetch_shipping_profile_countries_array($_SESSION['ilancedata']['user']['userid'], true);
			$_SESSION['ilancedata']['user']['countries'] = $this->ilance->shipping_app->print_shipping_profile_countries($_SESSION['ilancedata']['user']['userid']);
			$this->api_success('user.shippingprofile.delete');
			return array('error' => '0', 'message' => 'Success');
		}
		else {
			$this->api_failed('user.shippingprofile.delete');
			return array('error' => '1', 'message' => 'Shipping Profile with provided id does not exist');
		}	
	}

	public function user_shippingprofile_add($csrftoken, $shippingparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$shippingarray = array();
		foreach($shippingparams as $array)
		{
			foreach($array as $key => $value)
			{
				$shippingarray[$key] = $value;
			}    
		}
		if (is_array($shippingarray)) {
			foreach ($shippingarray as $record) {
				$isdefault = (($this->ilance->shipping_app->profiles_count($_SESSION['ilancedata']['user']['userid'], 'shipping') <= 0) ? '1' : '0');
				if ($record['isdefault'] == '1')
				{
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "shipping_profiles
						SET isdefault = '0'
						WHERE type = 'shipping'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
					$isdefault = '1';
				}
				else {
					$isdefault = '0';
				}
				$this->ilance->db->query("
					INSERT INTO " . DB_PREFIX . "shipping_profiles
					(id, user_id, first_name, last_name, address, address2, phone, city, state, zipcode, country, dateadded, type, status, isdefault, sessionid)
					VALUES(
					NULL,
					'" . $_SESSION['ilancedata']['user']['userid'] . "',
					'" . $this->ilance->db->escape_string($record['first_name']) . "',
					'" . $this->ilance->db->escape_string($record['last_name']) . "',
					'" . $this->ilance->db->escape_string($record['address']) . "',
					'" . $this->ilance->db->escape_string($record['address2']) . "',
					'" . $this->ilance->db->escape_string($record['phone']) . "',
					'" . $this->ilance->db->escape_string($record['city']) . "',
					'" . $this->ilance->db->escape_string($record['state']) . "',
					'" . $this->ilance->db->escape_string($record['zipcode']) . "',
					'" . $this->ilance->db->escape_string($record['country']) . "',
					'" . DATETIME24H . "',
					'shipping',
					'1',
					'" . $isdefault . "',
					'" . $this->ilance->db->escape_string(session_id()) . "')
				", 0, null, __FILE__, __LINE__);
				$ship_profile_id = $this->ilance->db->insert_id();
				$_SESSION['ilancedata']['user']['shipprofileid'] = $ship_profile_id;
				$this->api_success('user.shippingprofile.add');
				return array('error' => '0', 'message' => 'Success');
			}
		}
		$this->api_failed('user.shippingprofile.add');
		return array('error' => '1', 'message' => 'Could not create suggested shipping profile');
	}

	public function user_shippingprofile_preupdate ($csrftoken, $spid) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.preupdate') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.preupdate');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
		SELECT id, first_name, last_name, address, address2, phone, city, state, country, zipcode, type, isdefault
		FROM " . DB_PREFIX . "shipping_profiles
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND id = '" . intval($spid) . "'
			AND type = 'shipping'
		LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$res['countryid'] =  $this->ilance->country_app->countries[$res['country']]['locationid'];
			$this->api_success('user.shippingprofile.preupdate');		
			return array('error' => '0', 'message' => 'Success', 'shippingprofile' => $res);
		}
		else {
			$this->api_failed('user.shippingprofile.preupdate');
			return array('error' => '1', 'message' => 'No Shipping Profile Found.');
		}

		$this->api_failed('user.shippingprofile.preupdate');
		return array('error' => '1', 'message' => 'Error Occured with preupdate.');
	}

	public function user_shippingprofile_update($csrftoken, $shippingparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.update') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$shippingarray = array();
		foreach($shippingparams as $array)
		{
			foreach($array as $key => $value)
			{
				$shippingarray[$key] = $value;
			}    
		}
		if (is_array($shippingarray)) {
			foreach ($shippingarray as $record) {
				$sql = $this->ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "shipping_profiles
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND id = '" . $record['spid'] . "'
						AND type = 'shipping'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->ilance->db->num_rows($sql) > 0) {
					$isdefault = '';
					if ($record['isdefault'] == '1') { // setting as primary shipping location
						$this->ilance->db->query("
							UPDATE " . DB_PREFIX . "shipping_profiles
							SET isdefault = '0'
							WHERE type = 'shipping'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
						$isdefault = "isdefault = '1',";
					}
					else {
						$sql_profiles = $this->ilance->db->query("
							SELECT id
							FROM " . DB_PREFIX . "shipping_profiles
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND id != '" . $record['spid'] . "'
								AND type = 'shipping'
							ORDER BY id ASC
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($this->ilance->db->num_rows($sql_profiles) > 0) { // set customers latest billing profile as default..
							$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
							$this->ilance->db->query("
								UPDATE " . DB_PREFIX . "shipping_profiles
								SET isdefault = '1'
								WHERE id = '" . $resx['id'] . "'
							");
							$isdefault = "isdefault = '0',";
							$_SESSION['ilancedata']['user']['shipprofileid'] = $resx['id'];
						}
					}
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "shipping_profiles
						SET first_name = '" . $this->ilance->db->escape_string($record['first_name']) . "',
						last_name = '" . $this->ilance->db->escape_string($record['last_name']) . "',
						address = '" . $this->ilance->db->escape_string($record['address']) . "',
						address2 = '" . $this->ilance->db->escape_string($record['address2']) . "',
						phone = '" . $this->ilance->db->escape_string($record['phone']) . "',
						city = '" . $this->ilance->db->escape_string($record['city']) . "',
						state = '" . $this->ilance->db->escape_string($record['state']) . "',
						zipcode = '" . $this->ilance->db->escape_string($record['zipcode']) . "',
						country = '" . $this->ilance->db->escape_string($record['country']) . "',
						$isdefault
						type = 'shipping'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND id = '" . intval($record['spid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$this->api_success('user.shippingprofile.update');
					return array('error' => '0', 'message' => 'Success');
				}
				else {
					$this->api_failed('user.shippingprofile.update');
					return array('error' => '1', 'message' => 'Shipping Profile with provided id does not exist');
				}
			}
		}
		$this->api_failed('user.shippingprofile.update');
		return array('error' => '1', 'message' => 'Could not update suggested shipping profile');
	}

	public function user_shippingprofile_setdefault($csrftoken, $spid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.shippingprofile.setdefault') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.shippingprofile.setdefault');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $spid . "'
				AND type = 'shipping'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "shipping_profiles
			SET isdefault = '0'
			WHERE type = 'shipping'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "shipping_profiles
				SET isdefault = '1'
				WHERE id = '" . $spid . "'
			");	
			$this->api_success('user.shippingprofile.setdefault');
			return array('error' => '0', 'message' => 'Success');
		}

		else {
			$this->api_failed('user.shippingprofile.setdefault');
			return array('error' => '1', 'message' => 'Shipping Profile with provided id does not exist');
		}
	}

	public function user_taxprofile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$taxprofile = array();
		$sql = $this->ilance->db->query("
			SELECT id, country, state, label, rate, currencyid, isdefault
			FROM " . DB_PREFIX . "tax_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$taxprofile[] = $res;
			}
		}
		else {
			$this->api_failed('user.taxprofile.get');
			return array('error' => '1', 'message' => 'No Tax Profiles Found.');
		}
		$this->api_success('user.taxprofile.get');		
		return array('error' => '0', 'message' => 'Success', 'taxprofile' => $taxprofile);	
	}

	public function user_taxprofile_delete($csrftoken, $tid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.delete') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "tax_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $tid . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			DELETE FROM " . DB_PREFIX . "tax_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . intval($tid) . "'
			");
			$sql_profiles = $this->ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "tax_profiles
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				ORDER BY id DESC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->ilance->db->num_rows($sql_profiles) > 0)
			{
				$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "tax_profiles
					SET isdefault = '1'
					WHERE id = '" . $resx['id'] . "'
				");
			}
			$this->api_success('user.taxprofile.delete');
			return array('error' => '0', 'message' => 'Success');
		}
		else {
			$this->api_failed('user.taxprofile.delete');
			return array('error' => '1', 'message' => 'Tax Profile with provided id does not exist');
		}	
	}

	public function user_taxprofile_add($csrftoken, $taxparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$taxarray = array();
		foreach($taxparams as $array)
		{
			foreach($array as $key => $value)
			{
				$taxarray[$key] = $value;
			}    
		}
		if (is_array($taxarray)) {
			foreach ($taxarray as $record) {
				if ($record['isdefault'] == '1')
				{
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "tax_profiles
						SET isdefault = '0'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
					$isdefault = '1';
				}
				else {
					$isdefault = '0';
				}
				$this->ilance->db->query("
					INSERT INTO " . DB_PREFIX . "tax_profiles
					(id, user_id, country, state, label, rate, currencyid, isdefault)
					VALUES(
					NULL,
					'" . $_SESSION['ilancedata']['user']['userid'] . "',
					'" . $this->ilance->db->escape_string($record['tax_country']) . "',
					'" . $this->ilance->db->escape_string($record['tax_state']) . "',
					'" . $this->ilance->db->escape_string($record['tax_label']) . "',
					'" . $this->ilance->db->escape_string($record['tax_rate']) . "',
					'" . intval($record['tax_currencyid']) . "',
					'" .$isdefault . "')
				", 0, null, __FILE__, __LINE__);
				$this->api_success('user.taxprofile.add');
				return array('error' => '0', 'message' => 'Success');
			}
		}
		$this->api_failed('user.taxprofile.add');
		return array('error' => '1', 'message' => 'Could not create suggested tax profile');
	}

	public function user_taxprofile_preupdate ($csrftoken, $tid) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.preupdate') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.preupdate');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
		SELECT id, country, state, label, rate, currencyid, isdefault
		FROM " . DB_PREFIX . "tax_profiles
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND id = '" . intval($tid) . "'
		LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$res['countryid'] =  $this->ilance->country_app->countries[$res['country']]['locationid'];
			$this->api_success('user.taxprofile.preupdate');		
			return array('error' => '0', 'message' => 'Success', 'taxprofile' => $res);
		}
		else {
			$this->api_failed('user.taxprofile.preupdate');
			return array('error' => '1', 'message' => 'No Tax Profile Found.');
		}
		$this->api_failed('user.taxprofile.preupdate');
		return array('error' => '1', 'message' => 'Error Occured with preupdate.');
	}

	public function user_taxprofile_update($csrftoken, $taxparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.update') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$taxarray = array();
		foreach($taxparams as $array)
		{
			foreach($array as $key => $value)
			{
				$taxarray[$key] = $value;
			}    
		}
		if (is_array($taxarray)) {
			foreach ($taxarray as $record) {
				$sql = $this->ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "tax_profiles
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND id = '" . $record['tid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->ilance->db->num_rows($sql) > 0) {
					$isdefault = '';
					if ($record['isdefault'] == '1') { 
						$this->ilance->db->query("
							UPDATE " . DB_PREFIX . "tax_profiles
							SET isdefault = '0'
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
						$isdefault = "isdefault = '1'";
					}
					else {
						$sql_profiles = $this->ilance->db->query("
							SELECT id
							FROM " . DB_PREFIX . "tax_profiles
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND id != '" . $record['tid'] . "'
							ORDER BY id ASC
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($this->ilance->db->num_rows($sql_profiles) > 0) { 
							$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
							$this->ilance->db->query("
								UPDATE " . DB_PREFIX . "tax_profiles
								SET isdefault = '1'
								WHERE id = '" . $resx['id'] . "'
							");
						}
						$isdefault = "isdefault = '0'";
					}
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "tax_profiles
						SET country = '" . $this->ilance->db->escape_string($record['tax_country']) . "',
						state = '" . $this->ilance->db->escape_string($record['tax_state']) . "',
						label = '" . $this->ilance->db->escape_string($record['tax_label']) . "',
						rate = '" . $this->ilance->db->escape_string($record['tax_rate']) . "',
						currencyid = '" . $this->ilance->db->escape_string($record['tax_currencyid']) . "',
						$isdefault
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND id = '" . intval($record['tid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$this->api_success('user.taxprofile.update');
					return array('error' => '0', 'message' => 'Success');
				}
				else {
					$this->api_failed('user.taxprofile.update');
					return array('error' => '1', 'message' => 'Tax Profile with provided id does not exist');
				}
			}
		}
		$this->api_failed('user.taxprofile.update');
		return array('error' => '1', 'message' => 'Could not update suggested tax profile');
	}

	public function user_taxprofile_setdefault($csrftoken, $tid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.taxprofile.setdefault') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.taxprofile.setdefault');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "tax_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $tid . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "tax_profiles
			SET isdefault = '0'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "tax_profiles
				SET isdefault = '1'
				WHERE id = '" . $tid . "'
			");	
			$this->api_success('user.taxprofile.setdefault');
			return array('error' => '0', 'message' => 'Success');
		}

		else {
			$this->api_failed('user.taxprofile.setdefault');
			return array('error' => '1', 'message' => 'Tax Profile with provided id does not exist');
		}
	}

	public function user_sellingpaymentprofile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.sellingpaymentprofile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.sellingpaymentprofile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$profilesx = array();
		$sql = $this->ilance->db->query("
			SELECT paymethod, paymethodcc, paymethodoptions, paymethodoptionsemail
			FROM " . DB_PREFIX . "payment_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND mode = 'product'
				AND type = 'seller'
				LIMIT 1
		");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			
			$this->ilance->template->templateregistry['paymethod_text']= '{_i_prefer_not_to_use_secure_escrow_trading_for_this_project_payments_made_outside_marketplace}';
			$res['paymethod_text'] =$this->ilance->template->parse_template_phrases('paymethod_text');
			$res['paymethod'] = unserialize($res['paymethod']);
			$paymethodarray = $paymethodccarray = array();

			foreach($res['paymethod'] as $key => $value) {
				$this->ilance->template->templateregistry['paymethod']= '{' . $value . '}';
				$paymethodarray[] =$this->ilance->template->parse_template_phrases('paymethod');
			}
			
			if(count($paymethodarray) == 0) {
				$res['paymethod'] = '';
			}
			else {
				$res['paymethod'] = $paymethodarray;
			}
			$this->ilance->template->templateregistry['paymethodcc_text']= '{_i_would_like_winning_bidders_or_buyers_to_pay_immediately_using_the_following_credit_cards_payment_gateways}';
			$res['paymethodcc_text'] =$this->ilance->template->parse_template_phrases('paymethodcc_text');

			$res['paymethodcc'] = unserialize($res['paymethodcc']);
			foreach($res['paymethodcc'] as $key => $value) {
				$this->ilance->template->templateregistry['paymethodcc']= '{_' . $key . '}';
				$paymethodccarray[] =$this->ilance->template->parse_template_phrases('paymethodcc');
			}
			if(count($paymethodccarray) == 0) {
				$res['paymethodcc'] = '';
			}
			else {
				$res['paymethodcc'] = $paymethodccarray;
			}
			$this->ilance->template->templateregistry['paymethodoptions_text']= '{_i_would_like_winning_bidders_or_buyers_to_pay_immediately}';
			$res['paymethodoptions_text'] =$this->ilance->template->parse_template_phrases('paymethodcc_text');

			$res['paymethodoptions'] = unserialize($res['paymethodoptions']);
			$payoptionsarray = $temppayoptionsarray = array();

			foreach($res['paymethodoptions'] as $key => $value) {
				$this->ilance->template->templateregistry['paymethodoptions']= '{_' . $key . '}';
				$temppayoptionsarray['code'] =$key;
				$temppayoptionsarray['value'] =$this->ilance->template->parse_template_phrases('paymethodoptions');
				$payoptionsarray[] = $temppayoptionsarray;
			}
			if(count($payoptionsarray) == 0) {
				$res['paymethodoptions'] = '';
			}
			else {
				$res['paymethodoptions'] = $payoptionsarray;
			}
			$res['paymethodoptionsemail'] = unserialize($res['paymethodoptionsemail']);
			$payoptionsemailarray = array();
			foreach($res['paymethodoptionsemail'] as $key => $value) {
				$payoptionsemailarray[$key] =$value;
			}
			if(count($payoptionsemailarray) == 0) {
				$res['paymethodoptionsemail'] = '';
			}
			else {
				$res['paymethodoptionsemail'] = $payoptionsemailarray;
			}
			$this->api_success('user.sellingpaymentprofile.get');		
			return array('error' => '0', 'message' => 'Success', 'sellingpaymentprofile' => $res);	
		}
		else
		{
			$this->api_failed('user.sellingpaymentprofile.get');		
			return array('error' => '1', 'message' => 'No Seller Payment Profile Found.');	
		}
		$this->api_failed('user.sellingpaymentprofile.get');		
		return array('error' => '1', 'message' => 'Something Went Wrong Retrieving Selling Payment Profile');	
	}

	public function user_membership_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.membership.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.membership.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$paidplan = $hasinvoice = false;
		$subscription_rows = $subscription_rows_upgrade = $show = array();
		$show['subscriptioncancelled'] = $this->ilance->subscription_plan->is_subscription_cancelled($_SESSION['ilancedata']['user']['userid']);
		// paid or free plan detection
		$sql = $this->ilance->db->query("
			SELECT u.subscriptionid, u.invoiceid, s.cost
			FROM " . DB_PREFIX . "subscription_user u
			LEFT JOIN " . DB_PREFIX . "subscription s ON (u.subscriptionid = s.subscriptionid)
			WHERE u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND s.type = 'product'
		");
		if ($this->ilance->db->num_rows($sql) > 0) {
            $res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['cost'] > 0)
			{
				$paidplan = true;
			}
			$sql2 = $this->ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "invoices
				WHERE subscriptionid = '" . $res['subscriptionid'] . "'
					AND invoiceid = '" . $res['invoiceid'] . "'
			");
			if ($this->ilance->db->num_rows($sql2) > 0)
			{
				$hasinvoice = true;
			}
		}
        unset($sql, $sql2, $res);

		//echo (($paidplan AND $hasinvoice) ? 'yes' : 'no');
		// current membership
		$show['freeplan'] = false;
		$show['no_subscription_rows'] = true;
		$sql = $this->ilance->db->query("
			SELECT UNIX_TIMESTAMP(u.renewdate) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS countdown, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, s.description_" . $_SESSION['ilancedata']['user']['slng'] . " AS description, s.cost, s.length, s.units, s.subscriptiongroupid, u.cancelled, u.migrateto, u.migratelogic, u.recurring, u.recurring_profileid, u.recurring_other, u.recurring_gateway AS paymentgateway, u.user_id, u.paymethod, u.startdate, u.renewdate, u.active, u.subscriptionid, u.autorenewal, u.autopayment" . (($paidplan AND $hasinvoice) ? ", i.invoiceid, i.transactionid, i.paiddate, i.status AS invoicestatus, i.custommessage" : "") . "
			FROM " . DB_PREFIX . "subscription AS s
			LEFT JOIN " . DB_PREFIX . "subscription_user AS u ON (s.subscriptionid = u.subscriptionid)
			" . (($paidplan AND $hasinvoice) ? "LEFT JOIN " . DB_PREFIX . "invoices AS i ON (u.invoiceid = i.invoiceid)" : "") . "
			WHERE u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND s.type = 'product'
				" . (($paidplan AND $hasinvoice) ? "AND i.invoicetype = 'subscription' AND i.user_id = u.user_id ORDER BY i.invoiceid DESC" : "ORDER BY u.renewdate DESC") . "
				LIMIT 1
                ");
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$show['no_subscription_rows'] = false;
			$show['action'] = false;
			$show['migration'] = false;
			$row = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$row['migration'] = '';
			$row['migrationdate'] = '';
			if ($row['migrateto'] > 0 AND $row['migratelogic'] != 'none' AND $row['migrateto'] != $row['subscriptionid'])
			{
				$show['migration'] = true;
				$migration = $this->ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($row['migrateto']) . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
				$mgid = $this->ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($row['migrateto']) . "'", "subscriptiongroupid");
				$row['migration'] = '<a href="javascript:;" onclick="javascript:jQuery(\'#show_permissions\').jqm({modal: false}).jqmShow();print_permissions(' . $row['migrateto'] . ', ' . $mgid . ')" class="linkarrow">'  . $migration . '</a>';
				$row['migrationdate'] = $this->ilance->subscription->subscription_countdown_timeleft($row['countdown']);
			}
			$row['title'] = stripslashes($row['title']);
			$row['description'] = stripslashes($row['description']);
			$row['startdate'] = $this->ilance->common->print_date($row['startdate'], 'm/d/Y');
			$row['nextbilldate'] = $this->ilance->common->print_date($row['renewdate'], 'F d, Y', 0, 0);
			$row['renewdate'] = $this->ilance->common->print_date($row['renewdate'], 'm/d/Y');
			$row['billingcycle'] = '';
			$row['custommessage'] = ((!empty($row['custommessage'])) ? $row['custommessage'] : '{_none}');
			$this->ilance->template->templateregistry['custommessage']= $row['custommessage'];
			$row['custommessage']=$this->ilance->template->parse_template_phrases('custommessage');
			$row['otherid'] = ((!empty($row['recurring_other'])) ? $row['recurring_other'] : '');
			$row['paymentgateway'] = ((!empty($row['paymentgateway'])) ? $row['paymentgateway'] : 'none');
			if ($row['paymethod'] == 'account')
							{
				$row['paymethod'] = '{_account_balance}';
			}
			else if ($row['paymethod'] == 'bank')
							{
				$row['paymethod'] = '{_bank_slash_wire}';
			}
			else if ($row['paymethod'] == 'check')
							{
				$row['paymethod'] = '{_check_slash_mo}';
			}
			else if ($row['paymethod'] == 'creditcard')
							{
				$row['paymethod'] = '{_credit_card}';
			}
			else if ($row['paymethod'] == 'ipn')
							{
				$row['paymethod'] = '{_' . $row['paymentgateway'] . '}';
			}
			else
							{
				$row['paymethod'] = '{_' . $row['paymethod'] . '}';
			}
			$this->ilance->template->templateregistry['paymethod']= $row['paymethod'];
			$row['paymethod']=$this->ilance->template->parse_template_phrases('paymethod');
			$raw_cost = $row['cost'];
			$row['cost'] = ($raw_cost > 0) ? $this->ilance->currency->print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['cost']) : '{_free}';
			$this->ilance->template->templateregistry['cost']= $row['cost'];
			$row['cost']=$this->ilance->template->parse_template_phrases('cost');
			$show['freeplan'] = ($raw_cost > 0) ? false : true;
			$row['length'] = $row['length'];
			$row['units'] = $this->ilance->subscription->print_unit($row['units']);
			$this->ilance->template->templateregistry['units']= $row['units'];
			$row['units']=$this->ilance->template->parse_template_phrases('units');
			$row['action'] = '-';
			if ($row['recurring'])
			{
				$row['recurring'] = (($row['cancelled']) ? '{_cancelled}' : '{_active}');
				$show['onlyupgrade'] = 1;
				if ($row['active'] == 'yes' AND $row['cancelled'])
				{
					$row['status'] .= ' {_until_x::' . $this->ilance->common->print_date($row['renewdate'], 'F d, Y', 0, 0) . '}';
				}
			}
			else
			{
				$row['recurring'] = '-';
				$show['onlyupgrade'] = 0;
			}
			if ($row['active'] == 'yes')
			{
				$show['renewal_countdown'] = true;
				$row['status'] = '{_active}';
				$row['status'] .= (($row['cancelled']) ? ' {_until_x::' . $this->ilance->common->print_date($row['renewdate'], 'F d, Y', 0, 0) . '}' : '');
				$row['billingcycle'] = $row['startdate'] . ' - ' . $row['renewdate'];
			}
			else
			{
				$show['renewal_countdown'] = false;
				$row['billingcycle'] = $row['nextbilldate'] = $row['length'] = $row['paymethod'] = $row['cost'] = '-';
				$row['units'] = '';
				$row['status'] = '{_inactive}';
				if ($paidplan AND $hasinvoice)
				{
					$row['action'] = '-';
					if ($row['invoicestatus'] != 'cancelled' AND $row['invoicestatus'] != 'paid')
					{
						$show['action'] = true;
						$row['action'] = '<a href="' . HTTPS_SERVER . 'pay/?txn=' . $row['transactionid'] . $returnbit . '">{_pay_now}</a>';
					}
				}
			}
			$this->ilance->template->templateregistry['status']= $row['status'];
			$row['status']=$this->ilance->template->parse_template_phrases('status');
			$row['subscription_role'] = $this->ilance->subscription_role->print_role($_SESSION['ilancedata']['user']['roleid']);
			$row['hasactivemembership'] =$this->ilance->subscription->has_active_subscription($_SESSION['ilancedata']['user']['userid']) ? true : false;
			$row['show'] = $show;
			$this->api_success('user.membership.get');		
			return array('error' => '0', 'message' => 'Success', 'membership' => $row);	
		}
		else {
			$this->api_failed('user.membership.get');		
			return array('error' => '1', 'message' => 'No Membership Found.');	
		}
		$this->api_failed('user.membership.get');		
		return array('error' => '1', 'message' => 'Something Went Wrong Retrieving Membership');	
	}

	public function user_watchlist_items_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.watchlist.items.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.watchlist.items.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$page = ($page == '' || $page == 0) ? '1' : $page;
		$limit = ($limit == '' || $limit == 0) ? $this->ilance->config['globalfilters_maxrowsdisplay'] : $limit;
        $offset = ($page - 1) * $limit;
		$sqlauctionsc = $this->ilance->db->query("
			SELECT watchlistid
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_project_id != '0'
				AND mode = 'product'
		");
		$sqlauctions = $this->ilance->db->query("
			SELECT watchlistid, user_id, watching_project_id, watching_user_id, watching_category_id, comment, state, lowbidnotify, highbidnotify, hourleftnotify, subscribed, dateadded
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_project_id != '0'
				AND mode = 'product'
			ORDER BY watchlistid DESC
			LIMIT $limit OFFSET $offset
		");
		$row_count = 0;
		if ($this->ilance->db->num_rows($sqlauctions) > 0)
		{
			while ($row = $this->ilance->db->fetch_array($sqlauctions, DB_ASSOC))
			{
				$result = $this->ilance->db->query("
					SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $row['watching_project_id'] . "'
					ORDER BY date_end ASC
				");
				if ($this->ilance->db->num_rows($result) > 0)
				{
					$rows = $this->ilance->db->fetch_array($result, DB_ASSOC);
					if ($rows['bids'] == 0)
					{
						$row['bids'] = '0 {_bids_lower}';
					}
					else
					{
						$row['bids'] = $rows['bids'] . ' {_bids_lower}';
					}
					$row['cid'] = $rows['cid'];
					$row['comment'] = str_replace('"', "&#34;", $row['comment']);
					$row['comment'] = str_replace("'", "&#39;", $row['comment']);
					$row['comment'] = str_replace("<", "&#60;", $row['comment']);
					$row['comment'] = str_replace(">", "&#61;", $row['comment']);
					$this->ilance->template->templateregistry['comment']= $row['comment'];
					$row['comment']=$this->ilance->template->parse_template_phrases('comment');
					$picturedata = array('url' => $url, 'mode' => '208x208', 'projectid' => $rows['project_id'], 'start_from_image' => 0, 'attachtype' => '', 'httponly' => false, 'limit' => 1, 'forcenoribbon' => true, 'forceplainimg' => false, 'forceimgsrc' => true);
					$row['logo'] = $this->ilance->auction->print_item_photo($picturedata);
					unset($url, $picturedata);
					$row['title'] = $rows['project_title'];
					$row['description'] = $this->ilance->bbcode->strip_bb_tags($rows['description']);
					$row['description'] = $this->ilance->short_string($row['description'], 100);
					$row['description'] = strip_tags($row['description']);
					$row['status'] = $this->ilance->auction->print_auction_status($rows['status']);
					$this->ilance->template->templateregistry['status']= $row['status'];
					$row['status']=$this->ilance->template->parse_template_phrases('status');
					if ($rows['filtered_auctiontype'] == 'regular')
					{ // is bid placed?
						$sql_bidplaced = $this->ilance->db->query("
							SELECT bid_id
							FROM " . DB_PREFIX . "project_bids
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND project_id = '" . $rows['project_id'] . "'
						");
						$row['bidplaced'] = ($this->ilance->db->num_rows($sql_bidplaced) > 0)
							? '{_you_have_placed_a_bid_on_this_auction}'
							: '{_place_a_bid}';
						// is realtime auction?
						$row['realtime'] = ($rows['project_details'] == 'realtime')
							? '{_realtime_auction}'
							: '';
					}
					$this->ilance->template->templateregistry['bidplaced']= $row['bidplaced'];
					$row['bidplaced']=$this->ilance->template->parse_template_phrases('bidplaced');
					$this->ilance->template->templateregistry['realtime']= $row['realtime'];
					$row['realtime']=$this->ilance->template->parse_template_phrases('realtime');
					$currencyid = $rows['currencyid'];
					$bids = $rows['bids'];
					$this->ilance->template->templateregistry['bids']= $row['bids'];
					$row['bids']=$this->ilance->template->parse_template_phrases('bids');
					$startprice = $rows['startprice'];
					$currentbid = $rows['currentprice'];
					if ($rows['filtered_auctiontype'] == 'regular')
					{
						if ($rows['date_starts'] > DATETIME24H)
						{
							$dif = $rows['starttime'];
							$ndays = floor($dif / 86400);
							$dif -= $ndays * 86400;
							$nhours = floor($dif / 3600);
							$dif -= $nhours * 3600;
							$nminutes = floor($dif / 60);
							$dif -= $nminutes * 60;
							$nseconds = $dif;
							$sign = '+';
							if ($rows['starttime'] < 0)
							{
								$row['starttime'] = - $row['starttime'];
								$sign = '-';
								$row['currentbid'] = '-';
							}
							if ($sign != '-')
							{
								if ($ndays != '0')
								{
									$project_time_left = $ndays . '{_d_shortform}, ';
									$project_time_left .= $nhours . '{_h_shortform}+';
								}
								else if ($nhours != '0')
								{
									$project_time_left = $nhours . '{_h_shortform}, ';
									$project_time_left .= $nminutes . '{_m_shortform}+';
								}
								else
								{
									$project_time_left = $nminutes . '{_m_shortform}, ';
									$project_time_left .= $nseconds . '{_s_shortform}+';
								}
							}
							$row['timetostart'] = $project_time_left;
							$row['timeleft'] = '{_starts}: ' . $row['timetostart'];
						}
						else
						{
							$dif = $rows['mytime'];
							$ndays = floor($dif / 86400);
							$dif -= $ndays * 86400;
							$nhours = floor($dif / 3600);
							$dif -= $nhours * 3600;
							$nminutes = floor($dif / 60);
							$dif -= $nminutes * 60;
							$nseconds = $dif;
							$sign = '+';
							if ($rows['mytime'] < 0)
							{
								$row['mytime'] = - $rows['mytime'];
								$sign = '-';
							}

							if ($sign == '-')
							{
								$project_time_left = '{_ended}';
								$row['currentbid'] = '-';
							}
							else
							{
								if ($ndays != '0')
								{
									$project_time_left = $ndays . '{_d_shortform}, ';
									$project_time_left .= $nhours . '{_h_shortform}+';
								}
								else if ($nhours != '0')
								{
									$project_time_left = $nhours . '{_h_shortform}, ';
									$project_time_left .= $nminutes . '{_m_shortform}+';
								}
								else
								{
									$project_time_left = $nminutes . '{_m_shortform}, ';
									$project_time_left .= $nseconds . '{_s_shortform}+';
								}
							}

							$row['timeleft'] = $project_time_left;
						}
						$this->ilance->template->templateregistry['timeleft']= $row['timeleft'];
						$row['timeleft']=$this->ilance->template->parse_template_phrases('timeleft');
					}
					if ($bids > 0 AND $currentbid > $startprice)
					{
						$row['currentbid'] = $this->ilance->currency->format($currentbid, $currencyid);
					}
					else if ($bids > 0 AND $currentbid == $startprice)
					{
						$row['currentbid'] = $this->ilance->currency->format($currentbid, $currencyid);
					}
					else
					{
						$row['currentbid'] = $this->ilance->currency->format($startprice, $currencyid);
						$currentbid = $startprice;
					}
					$row['currentbid'] = strip_tags($row['currentbid']);
					$sql_user_results = $this->ilance->db->query("
						SELECT username
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $rows['user_id'] . "'
					");
					$res_project_user = $this->ilance->db->fetch_array($sql_user_results, DB_ASSOC);
					$row['sellerx'] = $res_project_user['username'];
					$watchlist_rfp[] = $row;
					$row_count++;
				}
			}

			$this->api_success('user.watchlist.items.get');		
			return array('error' => '0', 'message' => 'Success', 'itemswatchlist' => $watchlist_rfp);	
		}		
		else {
			$this->api_failed('user.watchlist.items.get');		
			return array('error' => '1', 'message' => 'No Items Found On the watchlist.');	
		}
		$this->api_failed('user.watchlist.items.get');		
		return array('error' => '1', 'message' => 'Something Went Wrong Retrieving Watchlist');	
	}


	public function user_watchlist_sellers_get($csrftoken, $limit=4, $page=1)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.watchlist.sellers.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.watchlist.sellers.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$page = ($page == '' || $page == 0) ? '1' : $page;
		$limit = ($limit == '' || $limit == 0) ? $this->ilance->config['globalfilters_maxrowsdisplay'] : $limit;
        $offset = ($page - 1) * $limit;
		$sqlauctionsc = $this->ilance->db->query("
			SELECT watchlistid
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_user_id != '0'
				AND state = 'mprovider'
		");
		$sqlauctions = $this->ilance->db->query("
			SELECT watchlistid, user_id, watching_project_id, watching_user_id, watching_category_id, comment, state, lowbidnotify, highbidnotify, hourleftnotify, subscribed, dateadded
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_user_id != '0'
				AND state = 'mprovider'
			ORDER BY watchlistid DESC
			LIMIT $limit OFFSET $offset
		");
		if ($this->ilance->db->num_rows($sqlauctions) > 0)
		{
			while ($row = $this->ilance->db->fetch_array($sqlauctions, DB_ASSOC))
			{
				$sql_providers = $this->ilance->db->query("
					SELECT user_id, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . $row['watching_user_id'] . "'
				");
				if ($this->ilance->db->num_rows($sql_providers) > 0)
				{
					$row_count = 0;
					while ($row2 = $this->ilance->db->fetch_array($sql_providers, DB_ASSOC))
					{
						$row2['watching_user_id'] = $row['watching_user_id'];
						$row2['title'] = $this->ilance->common->print_username($row['watching_user_id'], 'plain', 0);
						$row2['titleplain'] = $row2['username'];
						$feedback = $this->ilance->feedback->datastore(intval($row['watching_user_id']));
						$row2['feedbackbit'] = $feedback['score'];
						$row2['id'] = $row2['user_id'];
						$row2['online'] = strip_tags($this->ilance->common->print_online_status($row['watching_user_id']));
						$this->ilance->template->templateregistry['online']= $row2['online'];
						$row2['online']=$this->ilance->template->parse_template_phrases('online');
						$sqlattach = $this->ilance->db->query("
							SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, filehash, filename
							FROM " . DB_PREFIX . "attachment
							WHERE user_id = '" . $row2['user_id'] . "'
								AND visible = '1'
								AND attachtype = 'profile'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($this->ilance->db->num_rows($sqlattach) > 0)
						{
							$resattach = $this->ilance->db->fetch_array($sqlattach, DB_ASSOC);
							$row2['logo'] = HTTP_ATTACHMENTS . 'profiles/' . $resattach['filehash'] . '/208x208.jpg';
						}
						else
						{
							$row2['logo'] = HTTPS_SERVER. $this->ilance->config['imgcdn'] . 'v5/img_nophoto.png';
						}
						$row2['comment'] = str_replace('"', "&#34;", $row['comment']);
						$row2['comment'] = str_replace("'", "&#39;", $row2['comment']);
						$row2['comment'] = str_replace("<", "&#60;", $row2['comment']);
						$row2['comment'] = str_replace(">", "&#61;", $row2['comment']);
						$this->ilance->template->templateregistry['comment']= $row2['comment'];
						$row2['comment']=$this->ilance->template->parse_template_phrases('comment');
						$watchlist_mproviders[] = $row2;
						$row_count++;
					}
				}
			}
			$this->api_success('user.watchlist.sellers.get');		
			return array('error' => '0', 'message' => 'Success', 'sellerswatchlist' => $watchlist_mproviders);	
		}		
		else {
			$this->api_failed('user.watchlist.sellers.get');		
			return array('error' => '1', 'message' => 'No Sellers Found On the watchlist.');	
		}
		$this->api_failed('user.watchlist.sellers.get');		
		return array('error' => '1', 'message' => 'Something Went Wrong Retrieving Watchlist');	
	}

	public function user_watchlist_auctions_get($csrftoken, $limit, $page)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.watchlist.auctions.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.watchlist.auctions.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$page = ($page == '' || $page == 0) ? '1' : $page;
		$limit = ($limit == '' || $limit == 0) ? $this->ilance->config['globalfilters_maxrowsdisplay'] : $limit;
        $offset = ($page - 1) * $limit;
		$sqlauctionsc = $this->ilance->db->query("
			SELECT w.watchlistid
			FROM " . DB_PREFIX . "watchlist w
			LEFT JOIN " . DB_PREFIX . "events e ON (w.watching_eventid = e.eventid)
			WHERE w.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND w.watching_eventid != '0'
				AND w.state = 'event'
		");
		$sqlauctions = $this->ilance->db->query("
			SELECT w.watchlistid, w.user_id, w.watching_eventid, w.comment, w.state, w.hourleftnotify, w.subscribed, w.dateadded, e.title, e.attachid, e.lots, e.cid, e.userid
			FROM " . DB_PREFIX . "watchlist w
			LEFT JOIN " . DB_PREFIX . "events e ON (w.watching_eventid = e.eventid)
			WHERE w.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND w.watching_eventid != '0'
				AND w.state = 'event'
			ORDER BY w.watchlistid DESC
			LIMIT $limit OFFSET $offset
		");
		if ($this->ilance->db->num_rows($sqlauctions) > 0)
		{
			while ($row = $this->ilance->db->fetch_array($sqlauctions, DB_ASSOC))
			{
				$row['title'] = $row['title'];
				$row['seller'] = $this->ilance->common->print_username($row['userid'], 'plain', 0);
				$sqlattach = $this->ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, filehash, filename
					FROM " . DB_PREFIX . "attachment
					WHERE attachid = '" . $row['attachid'] . "'
						AND visible = '1'
						AND attachtype = 'eventphoto'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->ilance->db->num_rows($sqlattach) > 0)
				{
					$resattach = $this->ilance->db->fetch_array($sqlattach, DB_ASSOC);
					$row['logo'] = HTTP_ATTACHMENTS . 'auctions/' . $resattach['filehash'] . '/208x208.jpg';
				}
				else
				{
					$row['logo'] = HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/img_nophoto.png';
				}
				$tmp = PAGEURL;
				$tmp = $this->ilance->seo->rewrite_url($tmp, 'note=deleted');
				$row['comment'] = str_replace('"', "&#34;", $row['comment']);
				$row['comment'] = str_replace("'", "&#39;", $row['comment']);
				$row['comment'] = str_replace("<", "&#60;", $row['comment']);
				$row['comment'] = str_replace(">", "&#61;", $row['comment']);
				$this->ilance->template->templateregistry['comment']= $row['comment'];
				$row['comment']=$this->ilance->template->parse_template_phrases('comment');
				$watchlist_auctions[] = $row;
			}
			$this->api_success('user.watchlist.auctions.get');		
			return array('error' => '0', 'message' => 'Success', 'auctionswatchlist' => $watchlist_auctions);	
		}		
		else {
			$this->api_failed('user.watchlist.auctions.get');		
			return array('error' => '1', 'message' => 'No Auctions Found On the watchlist.');	
		}
		$this->api_failed('user.watchlist.auctions.get');		
		return array('error' => '1', 'message' => 'Something Went Wrong Retrieving Watchlist');	
	}

	public function user_watchlist_delete($csrftoken, $recordid=0, $type, $all) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.watchlist.delete') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.watchlist.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		if (!isset($type) OR empty($type))
		{
			$this->api_failed('user.watchlist.delete');
			return array('error' => '1', 'message' => 'Watchlist Type cannot be empty.');
		}
		$field = $watchlisttype = '';

		if ($type=='sellers')
		{
			$watchlisttype = 'mprovider';
			$field = 'watching_user_id';
		}
		else if ($type=='items')
		{
			$watchlisttype = 'auction';
			$field = 'watching_project_id';
		}
		else if ($type=='auctions')
		{
			$watchlisttype = 'event';
			$field = 'watching_eventid';
		}
		else {
			$this->api_failed('user.watchlist.delete');
			return array('error' => '1', 'message' => 'Wrong Type Value.');
		}
		if ($all) {
			$this->ilance->db->query("
				DELETE FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND state = '" . $this->ilance->db->escape_string($watchlisttype) . "'
			", 0, null, __FILE__, __LINE__);
			$this->api_success('user.watchlist.delete');
			return array('error' => '0', 'message' => 'Successfully Deleted All Watchlist entries for ' . $type);
		}
		else {
			$sql = $this->ilance->db->query("
			SELECT watchlistid
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND state = '" . $watchlisttype . "'
					AND $field = '" . intval($recordid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
			if ($this->ilance->db->num_rows($sql) > 0) {
				$this->ilance->db->query("
				DELETE FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND state = '" . $watchlisttype . "'
					AND $field = '" . intval($recordid) . "'
				LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$this->api_success('user.watchlist.delete');
				return array('error' => '0', 'message' => 'Successfully Deleted watchlist.');
			}

			else {
				$this->api_failed('user.watchlist.delete');
				return array('error' => '1', 'message' => 'Could Not Delete Watchlist with id ' . $recordid . '.');
			}
		}
		$this->api_failed('user.watchlist.delete');
		return array('error' => '1', 'message' => 'Error Occured Deleting Watchlist');
	}

	public function user_billingprofile_get($csrftoken)
	{
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.get') . "'
		LIMIT 1
		");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.get');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$shipprofiles = array();
		$sql = $this->ilance->db->query("
			SELECT id, first_name, last_name, address, address2, phone, city, state, country, zipcode, type, isdefault
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND type = 'billing'
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			while ($res = $this->ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['name'] = o($res['first_name']) . ' ' . o($res['last_name']);
				$shipprofiles[] = $res;
			}
		}
		else {
			$this->api_failed('user.billingprofile.get');
			return array('error' => '1', 'message' => 'No Billing Profiles Found.');
		}
		$this->api_success('user.billingprofile.get');		
		return array('error' => '0', 'message' => 'Success', 'billingprofile' => $shipprofiles);	
	}

	public function user_billingprofile_delete($csrftoken, $spid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.delete') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.delete');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $spid . "'
				AND type = 'billing'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			DELETE FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . intval($spid) . "'
				AND type = 'billing'
			");
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "carts
				SET bpid = '0'
				WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND bpid = '" . intval($spid) . "'
			");
			$sql_profiles = $this->ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "shipping_profiles
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND type = 'billing'
				ORDER BY id DESC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->ilance->db->num_rows($sql_profiles) > 0)
			{
				$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
				$this->ilance->db->query("
					UPDATE " . DB_PREFIX . "shipping_profiles
					SET isdefault = '1'
					WHERE id = '" . $resx['id'] . "'
				");
				$_SESSION['ilancedata']['user']['billrofileid'] = $resx['id'];
			}
			$this->api_success('user.billingprofile.delete');
			return array('error' => '0', 'message' => 'Success');
		}
		else {
			$this->api_failed('user.billingprofile.delete');
			return array('error' => '1', 'message' => 'Billing Profile with provided id does not exist');
		}	
	}

	public function user_billingprofile_add($csrftoken, $billingparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.add') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.add');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$billingarray = array();
		foreach($billingparams as $array)
		{
			foreach($array as $key => $value)
			{
				$billingarray[$key] = $value;
			}    
		}
		if (is_array($billingarray)) {
			foreach ($billingarray as $record) {
				$isdefault = (($this->ilance->shipping_app->profiles_count($_SESSION['ilancedata']['user']['userid'], 'billing') <= 0) ? '1' : '0');
				if ($record['isdefault'] == '1')
				{
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "shipping_profiles
						SET isdefault = '0'
						WHERE type = 'billing'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
					$isdefault = '1';
				}
				else {
					$isdefault = '0';
				}
				$this->ilance->db->query("
					INSERT INTO " . DB_PREFIX . "shipping_profiles
					(id, user_id, first_name, last_name, address, address2, phone, city, state, zipcode, country, dateadded, type, status, isdefault, sessionid)
					VALUES(
					NULL,
					'" . $_SESSION['ilancedata']['user']['userid'] . "',
					'" . $this->ilance->db->escape_string($record['first_name']) . "',
					'" . $this->ilance->db->escape_string($record['last_name']) . "',
					'" . $this->ilance->db->escape_string($record['address']) . "',
					'" . $this->ilance->db->escape_string($record['address2']) . "',
					'" . $this->ilance->db->escape_string($record['phone']) . "',
					'" . $this->ilance->db->escape_string($record['city']) . "',
					'" . $this->ilance->db->escape_string($record['state']) . "',
					'" . $this->ilance->db->escape_string($record['zipcode']) . "',
					'" . $this->ilance->db->escape_string($record['country']) . "',
					'" . DATETIME24H . "',
					'billing',
					'1',
					'" . $isdefault . "',
					'" . $this->ilance->db->escape_string(session_id()) . "')
				", 0, null, __FILE__, __LINE__);
				$ship_profile_id = $this->ilance->db->insert_id();
				$_SESSION['ilancedata']['user']['shipprofileid'] = $ship_profile_id;
				$this->api_success('user.billingprofile.add');
				return array('error' => '0', 'message' => 'Success');
			}
		}
		$this->api_failed('user.billingprofile.add');
		return array('error' => '1', 'message' => 'Could not create suggested billing profile');
	}

	public function user_billingprofile_preupdate ($csrftoken, $spid) {
		$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "api
				SET hits = hits + 1
				WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.preupdate') . "'
				LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.preupdate');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$sql = $this->ilance->db->query("
		SELECT id, first_name, last_name, address, address2, phone, city, state, country, zipcode, type, isdefault
		FROM " . DB_PREFIX . "shipping_profiles
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND id = '" . intval($spid) . "'
			AND type = 'billing'
		LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0)
		{
			$res = $this->ilance->db->fetch_array($sql, DB_ASSOC);
			$res['countryid'] =  $this->ilance->country_app->countries[$res['country']]['locationid'];
			$this->api_success('user.billingprofile.preupdate');		
			return array('error' => '0', 'message' => 'Success', 'billingprofile' => $res);
		}
		else {
			$this->api_failed('user.billingprofile.preupdate');
			return array('error' => '1', 'message' => 'No Billing Profile Found.');
		}

		$this->api_failed('user.billingprofile.preupdate');
		return array('error' => '1', 'message' => 'Error Occured with preupdate.');
	}

	public function user_billingprofile_update($csrftoken, $billingparams = array()) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.update') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.update');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$billingarray = array();
		foreach($billingparams as $array)
		{
			foreach($array as $key => $value)
			{
				$billingarray[$key] = $value;
			}    
		}
		if (is_array($billingarray)) {
			foreach ($billingarray as $record) {
				$sql = $this->ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "shipping_profiles
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND id = '" . $record['spid'] . "'
						AND type = 'billing'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->ilance->db->num_rows($sql) > 0) {
					$isdefault = '';
					if ($record['isdefault'] == '1') { // setting as primary billing location
						$this->ilance->db->query("
							UPDATE " . DB_PREFIX . "shipping_profiles
							SET isdefault = '0'
							WHERE type = 'billing'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
						$isdefault = "isdefault = '1',";
					}
					else {
						$sql_profiles = $this->ilance->db->query("
							SELECT id
							FROM " . DB_PREFIX . "shipping_profiles
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND id != '" . $record['spid'] . "'
								AND type = 'billing'
							ORDER BY id ASC
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($this->ilance->db->num_rows($sql_profiles) > 0) { // set customers latest billing profile as default..
							$resx = $this->ilance->db->fetch_array($sql_profiles, DB_ASSOC);
							$this->ilance->db->query("
								UPDATE " . DB_PREFIX . "shipping_profiles
								SET isdefault = '1'
								WHERE id = '" . $resx['id'] . "'
							");
							$isdefault = "isdefault = '0',";
							$_SESSION['ilancedata']['user']['shipprofileid'] = $resx['id'];
						}
					}
					$this->ilance->db->query("
						UPDATE " . DB_PREFIX . "shipping_profiles
						SET first_name = '" . $this->ilance->db->escape_string($record['first_name']) . "',
						last_name = '" . $this->ilance->db->escape_string($record['last_name']) . "',
						address = '" . $this->ilance->db->escape_string($record['address']) . "',
						address2 = '" . $this->ilance->db->escape_string($record['address2']) . "',
						phone = '" . $this->ilance->db->escape_string($record['phone']) . "',
						city = '" . $this->ilance->db->escape_string($record['city']) . "',
						state = '" . $this->ilance->db->escape_string($record['state']) . "',
						zipcode = '" . $this->ilance->db->escape_string($record['zipcode']) . "',
						country = '" . $this->ilance->db->escape_string($record['country']) . "',
						$isdefault
						type = 'billing'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND id = '" . intval($record['spid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$this->api_success('user.billingprofile.update');
					return array('error' => '0', 'message' => 'Success');
				}
				else {
					$this->api_failed('user.billingprofile.update');
					return array('error' => '1', 'message' => 'Billing Profile with provided id does not exist');
				}
			}
		}
		$this->api_failed('user.billingprofile.update');
		return array('error' => '1', 'message' => 'Could not update suggested billing profile');
	}

	public function user_billingprofile_setdefault($csrftoken, $spid) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.billingprofile.setdefault') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.billingprofile.setdefault');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}

		$sql = $this->ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "shipping_profiles
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $spid . "'
				AND type = 'billing'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($sql) > 0) {
			$this->ilance->db->query("
			UPDATE " . DB_PREFIX . "shipping_profiles
			SET isdefault = '0'
			WHERE type = 'billing'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "shipping_profiles
				SET isdefault = '1'
				WHERE id = '" . $spid . "'
			");	
			$this->api_success('user.billingprofile.setdefault');
			return array('error' => '0', 'message' => 'Success');
		}

		else {
			$this->api_failed('user.billingprofile.setdefault');
			return array('error' => '1', 'message' => 'Billing Profile with provided id does not exist');
		}
	}

	public function user_rtbf($csrftoken, $password, $comments) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.rtbf') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.rtbf');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$badpassword = false;
		if ($_SESSION['ilancedata']['user']['password'] != iif($password, md5(md5($password) . $_SESSION['ilancedata']['user']['salt']), '') AND $_SESSION['ilancedata']['user']['password'] != md5(md5($password) . $_SESSION['ilancedata']['user']['salt']))
		{
			$badpassword = true;
		}
		if (!$badpassword)
		{
			$sql = $this->ilance->db->query("
			SELECT requestdeletion
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND requestdeletion = '1'
			LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($this->ilance->db->num_rows($sql) > 0) {
				$this->api_failed('user.rtbf');
				return array('error' => '1', 'message' => 'Request Already Submitted');
			}
			$this->ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET requestdeletion = '1'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);

			$this->ilance->email->mail = SITE_CONTACT;
			$this->ilance->email->slng = $this->ilance->language->fetch_site_slng();
			$this->ilance->email->get('rtbf_deletion_request');
			$this->ilance->email->set(array(
					'{{firstname}}' => $_SESSION['ilancedata']['user']['firstname'],
					'{{lastname}}' => $_SESSION['ilancedata']['user']['lastname'],
					'{{username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{ipaddress}}' => $_SERVER['REMOTE_ADDR'],
					'{{comments}}' => $comments
			));
			$this->ilance->email->send();
			$this->api_success('user.rtbf');
			$this->ilance->template->templateregistry['success']='{_you_requested_staff_delete_account}';
			return array('error' => '0', 'message' => $this->ilance->template->parse_template_phrases('success'));
		}
		else {
			$this->api_failed('user.rtbf');
			return array('error' => '1', 'message' => 'Wrong Password Provided');
		}
	}

	public function user_buyingactivity_product($csrftoken, $bidsub, $keyw2, $keyw, $page, $period, $orderby, $displayorder) {
		$this->ilance->db->query("
		UPDATE " . DB_PREFIX . "api
		SET hits = hits + 1
		WHERE name = '" . $this->ilance->db->escape_string('user.buyingactivity.product') . "'
		LIMIT 1
			");

		if ($csrftoken!=$_SESSION['ilancedata']['user']['csrf']){
			$this->api_failed('user.buyingactivity.product');
			return array('error' => '1', 'message' => 'Token ' .$csrftoken.' is not valid.');
		}
		$page = (empty($page) OR $page <= 0) ? 1 : intval($page);
		$extra = '';
		$extra .= (!empty($bidsub)) ? '&amp;bidsub=' . $bidsub : '';

		$period = (isset($period) ? intval($period) : -1);
		$bidsub = (isset($bidsub) ? $bidsub : '');
		$periodsql = $this->ilance->mycp->fetch_startend_sql(intval($period), 'DATE_SUB', 'p.date_added', '>=');
		$extra .= '&amp;period=' . intval($period);

		$orderby = '&amp;orderby=date_end';
		$orderbysql = 'date_added';
		$orderbyfields = array('project_title', 'date_added', 'date_end', 'bids');
		if (!empty($orderby) AND in_array($orderby, $orderbyfields))
		{
			$orderby = '&amp;orderby=' . $orderby;
			$orderbysql = $orderby;
		}
		$displayorderfields = array('asc', 'desc');
		$displayorder= '&amp;displayorder=asc';
		$currentdisplayorder = $displayorder;
		$displayordersql = 'DESC';
		if (!empty($displayorder) AND $displayorder == 'asc')
		{
			$displayorder = '&amp;displayorder=desc';
			$currentdisplayorder = '&amp;displayorder=asc';
		}
		else if (!empty($displayorder) AND $displayorder == 'desc')
		{
			$displayorder = '&amp;displayorder=asc';
			$currentdisplayorder = '&amp;displayorder=desc';
		}
		if (!empty($displayorder) AND in_array($displayorder, $displayorderfields))
		{
			$displayordersql = mb_strtoupper($displayorder);
		}
		$groupby = "GROUP BY b.project_id";
		$orderby = "ORDER BY $orderbysql $displayordersql";
		//$this->ilance->config['globalfilters_maxrowsdisplay'] = (isset($this->ilance->GPC['pp'])  AND $this->ilance->GPC['pp'] >= 0)  ? intval($this->ilance->GPC['pp']) : $this->ilance->config['globalfilters_maxrowsdisplay'] ;
		$limit = "LIMIT " . (($page - 1) * $this->ilance->config['globalfilters_maxrowsdisplay']) . "," . $this->ilance->config['globalfilters_maxrowsdisplay'];
		$block_header_title = '{_items_im_bidding_on}';
		$period_options = array(
			'-1' => '{_any_date}',
			'1' => '{_last_hour}',
			'6' => '{_last_12_hours}',
			'7' => '{_last_24_hours}',
			'13' => '{_last_7_days}',
			'14' => '{_last_14_days}',
			'15' => '{_last_30_days}',
			'16' => '{_last_60_days}',
			'17' => '{_last_90_days}');
		$bidsub_options = array(
			'active' => '{_active}',
			'awarded' => '{_i_won}',
			'invited' => '{_invited}',
			'expired' => '{_i_lost}',
			'retracted' => '{_retracted}');
		$bids_retracted = $bids_awarded = $bids_invited = $bids_expired = $bids_active = $row_count2 = 0;
		$query = array();
		$counter = ($page - 1) * $this->ilance->config['globalfilters_maxrowsdisplay'];
		$condition = $condition2 = '';
		$show['datetime'] = false;
		$show['canretractbid'] = false;
		$product_bidding_activity = array();
		if (!empty($bidsub) AND $bidsub == 'retracted')
		{
			$block_header_title = '{_retracted_items}';
			$bids_retracted = 1;
			$query['1'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$query['2'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		}
		else if (!empty($bidsub) AND $bidsub == 'awarded')
		{
			$block_header_title = '{_items_ive_won}';
			$bids_awarded = 1;
			$periodsql = $this->ilance->mycp->fetch_startend_sql(intval($this->ilance->GPC['period']), 'DATE_SUB', 'b.date_awarded', '>=');
			$query['1'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$query['2'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		}
		else if (!empty($bidsub) AND $bidsub == 'invited')
		{
			$block_header_title = '{_invited_items}';
			$bids_invited = 1;
			$query['1'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$query['2'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		}
		else if (!empty($bidsub) AND $bidsub == 'expired')
		{
			$block_header_title = '{_items_i_didnt_win}';
			$bids_expired = 1;
			$query['1'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$query['2'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		}
		else if (!empty($bidsub) AND $bidsub == 'active')
		{
			$bids_active = 1;
			$query['1'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql);
			$query['2'] = $this->ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		}
		else {
			$this->api_failed('user.buyingactivity.product');
			return array('error' => '1', 'message' => 'Sub Value ' .$bidsub.' is not valid.');
		}
		$numberrows = $this->ilance->db->query($query['2'], 0, null, __FILE__, __LINE__);
		$number = $this->ilance->db->num_rows($numberrows);
		$numberx = $number;
		$result2 = $this->ilance->db->query($query['1'], 0, null, __FILE__, __LINE__);
		if ($this->ilance->db->num_rows($result2) > 0)
		{
			while ($row2 = $this->ilance->db->fetch_array($result2, DB_ASSOC))
			{
				if (!isset($row2['reason'])) // retracted tab only
				{
					$row2['reason'] = '';
				}
				$row2['paystatus'] = '<span title="{_not_in_use}"><img src="' . HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/ico_buy_disabled.gif" border="0" alt="" /></span>';
				$row2['shipstatus'] = '<span title="{_item_not_shipped}"><img src="' . HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
				$row2['feedback'] = '<span title="{_feedback_not_left}"><img src="' . HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/ico_feedback.gif" border="0" alt="" /></span>';
				$row2['feedbackreceived'] = '<span title="{_feedback_not_received}"><img src="' . HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received_disabled.gif" border="0" alt="" /></span>';
				$row2['escrowtotal'] = '<div><span title="{_not_in_use}"><img src="' . HTTPS_SERVER . $this->ilance->config['imgcdn'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span></div>';
				$row2['merchant'] = $this->ilance->common->print_username($row2['user_id'], 'href', 0, '', '');
				$row2['merchantplain'] = $this->ilance->common->print_username($row2['user_id'], 'plain', 0, '', '');
				$row2['icons'] = $this->ilance->auction->auction_icons($row2);
				$row2['price'] = $this->ilance->currency->print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], (isset($highest) ? $highest : 0), $row2['currencyid']);
				$row2['item_title'] = $this->ilance->print_string_wrap(o(stripslashes($row2['project_title'])), '45');
				$row2['item_titleseo'] = o(str_replace(' ', '+', $row2['project_title']));
				$row2['actions'] = (isset($bids_retracted) AND $bids_retracted OR isset($bids_expired) AND $bids_expired OR isset($bids_invited) AND $bids_invited) ? '<input type="checkbox" id="bidid_' . $row2['bid_id'] . '" name="bidid[]" value="' . $row2['bid_id'] . '" disabled="disabled" />' : '<input type="checkbox" id="bidid_' . $row2['bid_id'] . '" name="bidid[]" value="' . $row2['bid_id'] . '" />';
				$row2['wondate'] = '';
				$row2['awarded'] = '';
				$row2['work'] = '';
				$row2['pmb'] = '';
				$row2['payment'] = '-';
				$row2['bid_id'] = ((isset($bids_invited) AND $bids_invited) ? '-' : $row2['bid_id']);
				$row2['timeleft'] = $this->ilance->auction->auction_timeleft(true, $row2['date_starts'], $row2['mytime'], $row2['starttime']);
				$row2['ends'] = $this->ilance->common->print_date($row2['date_end'], 'l M d Y g:i:s A', 0, 0); // l M d Y h:i:s A | Monday sep 25 2013 at 5:
				$row2['bidretractdate'] = $this->ilance->common->print_date($row2['date_retracted'], 'l M d Y g:i:s A', 0, 0);
				$row2['bidretractreason'] = $row2['reason'];
				$row2['bidplacedate'] = $this->ilance->common->print_date($row2['date_added'], 'M j Y g:i:s A', 0, 0);
				$url = $this->ilance->seo->url(array('type' => 'productauctionplain', 'catid' => 0, 'seourl' => '', 'auctionid' => $row2['project_id'], 'name' => o($row2['project_title']), 'customlink' => '', 'bold' => 0, 'searchquestion' => '', 'questionid' => 0, 'answerid' => 0, 'removevar' => '', 'extrahref' => '', 'cutoffname' => ''));
				$picturedata = array('url' => $url, 'mode' => '150x150', 'projectid' => $row2['project_id'], 'start_from_image' => 0, 'attachtype' => '', 'httponly' => false, 'limit' => 1, 'forcenoribbon' => false, 'forceplainimg' => false, 'forceimgsrc' => true);
				$row2['photo'] = $this->ilance->auction->print_item_photo($picturedata);
				$row2['url'] = $url;
				unset($url, $picturedata);
				$row2['currentbid'] = $this->ilance->currency->format($row2['currentprice'], $row2['currencyid']);
				$row2['highestbidderid'] = $this->ilance->bid->fetch_highest_bidder($row2['project_id']);
				$row2['highestbid'] = $this->ilance->currency->format($row2['currentprice'], $row2['currencyid']);
				$row2['winningbid'] = $this->ilance->currency->format($row2['currentprice'], $row2['currencyid']);
				$row2['contactseller'] = '';
				$show['show_highestbidder'] = (($row2['highestbidderid'] == $_SESSION['ilancedata']['user']['userid']) ? 1 : 0);
				$show['show_bidretracted'] = (($row2['bidstate'] == 'retracted') ? 1 : 0);
				$show['show_blockedbidding'] = 0;
				$show['show_bannedbidding'] = 0;
				$show['show_ended'] = (($row2['mytime'] < 0 OR $row2['close_date'] != '0000-00-00 00:00:00') ? 1 : 0);
				$show['show_endedtowinner'] = $this->ilance->bid->has_winning_bidder($row2['project_id']);
				$show['show_sellerinfavorites'] = $this->ilance->watchlist->is_seller_added_to_watchlist($row2['user_id']);
				$show['show_iteminwatchlist'] = $this->ilance->watchlist->is_listing_added_to_watchlist($row2['project_id']);
				$show['show_endedearlytopurchase'] = (($row2['close_date'] == '0000-00-00 00:00:00') ? 0 : 1);
				$show['show_canproxycategory'] = (($this->ilance->config['productbid_enableproxybid'] == 0 OR $this->ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $row2['cid']) == 0) ? 0 : 1);
				$show['show_reservepricenotmet'] = 0;
				$show['show_reserve'] = $row2['reserve'];
				$row2['close_date'] = $this->ilance->common->print_date($row2['close_date'], 'l M d Y g:i:s A', 0, 0); // l M d Y h:i:s A | Monday sep 25 2013 at 5:30pm
				if ($row2['bidderid'] > 0)
				{
					if ($row2['reserve'])
					{
						if ($row2['reserve_price'] <= $row2['currentprice'])
						{
							// reserve price met
							$show['show_reservepricenotmet'] = 0;
							if ($row2['mytime'] < 0)
							{
								if ($bids_retracted OR $bids_expired)
								{
									$row2['timeleft'] = '{_ended}';
								}
								else
								{
									$row2['awarded'] = '<img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_bid_won.gif" border="0" alt="{_winner}" />';
									$row2['timeleft'] = '{_ended}';
								}
							}
							$row2['pmb'] = ($bids_retracted OR $bids_expired OR $bids_invited)
								? '<div align="center"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_pmb_disabled.gif" border="0" alt="" /></div>'
								: $this->ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
							// feedback left?
							if ($this->ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller', $row2['bid_id']))
							{
								$row2['feedback'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
							}
							else
							{
								$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
									? '<div align="center"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_disabled.gif" border="0" alt="" /></div>'
									: '<div align="center"><span title="{_submit_feedback_for} ' . $this->ilance->fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $this->ilance->ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback.gif" border="0" alt="{_submit_feedback_for} ' . $this->ilance->fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
							}
							// feedback received?
							if ($this->ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], 'buyer', $row2['bid_id']))
							{
								$row2['feedbackreceived'] = '<div><span title="{_feedback_received}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received.gif" border="0" alt="" /></span></div>';
							}
							else
							{
								$row2['feedbackreceived'] = '<div><span title="{_feedback_not_received}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received_disabled.gif" border="0" alt="" /></span></div>';
							}
						}
						else
						{
							// reserve price not met
							$show['show_reservepricenotmet'] = 1;
							if ($row2['mytime'] < 0)
							{
								$row2['timeleft'] = '{_ended}';
								$row2['shipstatus'] = '<span title="{_not_in_use}"><img src="' . $this->ilance->config['imgcdn'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								$row2['feedback'] = '<span title="{_not_in_use}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback.gif" border="0" alt="" /></span>';
								$row2['feedbackreceived'] = '<span title="{_not_in_use}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received_disabled.gif" border="0" alt="" /></span>';
								$row2['work'] = '<span title="{_not_in_use}"><img src="' . $this->ilance->config['imgcdn'] . 'icons/share_litegray.gif" border="0" alt="" /></span>';
								$row2['escrowtotal'] = '<div><span title="{_not_in_use}"><img src="' . $this->ilance->config['imgcdn'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span></div>';
								$row2['pmb'] = '';
							}
						}
					}
					else
					{
						if ($row2['mytime'] < 0)
						{
							if ($bids_retracted OR $bids_expired)
							{
								$row2['timeleft'] = '{_ended}';
							}
							else
							{
								$row2['awarded'] = '<img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_bid_won.gif" border="0" alt="{_winner}" />';
								$row2['timeleft'] = '{_ended}';
							}
						}
						$row2['pmb'] = $this->ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
						// #### feedback experience with buyer
						if ($this->ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller', $row2['bid_id']))
						{
							$row2['feedback'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
						}
						else
						{
							$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
								? '<div align="center"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback.gif" border="0" alt="" /></div>'
								: '<div align="center"><span title="{_submit_feedback_for} ' . $this->ilance->fetch_user('username', $row2['user_id']) . '"><a href="' . $this->ilance->ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback.gif" border="0" alt="{_submit_feedback_for} ' . $this->ilance->fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
						}
						// feedback received?
						if ($this->ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], 'buyer', $row2['bid_id']))
						{
							$row2['feedbackreceived'] = '<div><span title="{_feedback_received}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received.gif" border="0" alt="" /></span></div>';
						}
						else
						{
							$row2['feedbackreceived'] = '<div><span title="{_feedback_not_received}"><img src="' . $this->ilance->config['imgcdn'] . 'v5/ico_feedback_received_disabled.gif" border="0" alt="" /></span></div>';
						}
					}
				}
				$this->ilance->show['datetime'] = false;
				$row2['datetime'] = $this->ilance->common->print_date($row2['date_added'], $this->ilance->config['globalserverlocale_globaltimeformat'], 0, 0);
				$sql_highest = $this->ilance->db->query("
					SELECT bidamount AS highest, user_id, date_added, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $row2['project_id'] . "'
					ORDER BY highest
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($this->ilance->db->num_rows($sql_highest) > 0)
				{
					$res_highest = $this->ilance->db->fetch_array($sql_highest, DB_ASSOC);
					$row2['highest'] = $this->ilance->currency->format($res_highest['highest'], $row2['currencyid']);
					if ($res_highest['user_id'] == $_SESSION['ilancedata']['user']['userid'] AND $row2['status'] == 'expired')
					{
						$this->ilance->show['datetime'] = true;
						if ($row2['buynow'] == '1')
						{
							$row2['datetime'] = $this->ilance->common->print_date($res_highest['date_added'], $this->ilance->config['globalserverlocale_globaltimeformat'], 0, 0);
						}
					}
				}
				else
				{
					$row2['highest'] = '-';
				}
				if (isset($this->ilance->GPC['bidsub']) AND $this->ilance->GPC['bidsub'] == 'awarded')
				{ // viewing items i've won tab
					$row2['contactseller'] = $this->ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
					$row2['winningbid'] = $this->ilance->currency->format($row2['currentprice'], $row2['currencyid']);
					$row2['hoursleft'] = $this->ilance->config['woncartexpiryhours'];
					$show['show_cancheckout'] = false;
					$addedtocartdate = $this->ilance->db->fetch_field(DB_PREFIX . "carts", "userid = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND sellerid = '" . $row2['user_id'] . "' AND itemid = '" . $row2['project_id'] . "' AND auctionorderid = '1' AND isdeleted = '0' AND purchased = '0'", "added");
					if (!empty($addedtocartdate))
					{ // can checkout?
						$elapsed = $this->ilance->datetimes->fetch_hours_between($addedtocartdate, DATETIME24H);
						$row2['hoursleft'] = ($this->ilance->config['woncartexpiryhours'] - $elapsed);
						if ($row2['hoursleft'] <= 0)
						{
							$show['show_cancheckout'] = false;
						}
						else
						{
							$show['show_cancheckout'] = true;
						}
						unset($elapsed, $addedtocartdate);
					}
					$this->ilance->show['datetime'] = true;
					$methodscount = $this->ilance->payment->print_payment_methods($row2['user_id'], false, true);
					if ($methodscount == 1)
					{ // single payment method offered by seller
						$row2['buyerpaymethod'] = $this->ilance->payment->print_payment_method_title($row2['user_id']);
					}
				}
				else
				{ // all other tabs (active, retracted, didn't win)
					$row2['orderlocation'] = $row2['shipping'] = $row2['shipservice'] = '-';
					$shipperid = $row2['buyershipperid'];
					$shippingcosts = $this->ilance->shipping->fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty'], $row2['buyershipcost'], $_SESSION['ilancedata']['user']['userid']);
					$row2['buyershipcost'] = $shippingcosts['total'];
					$shippercount = $this->ilance->shipping->print_shipping_methods($row2['project_id'], '', $row2['qty'], false, true, false, $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng']);
					if ($shippercount == 1)
					{
						$row2['shipservice'] = '<span class="smaller" title="' . $this->ilance->shipping->print_shipping_partner($shipperid) . '">' . $this->ilance->shorten($this->ilance->shipping->print_shipping_partner($shipperid), 28) . '</span>';
					}
					else
					{
						if ($shippercount > 1)
						{
							$row2['shipservice'] = '<span class="smaller" title="' . $this->ilance->shipping->print_shipping_partner($shipperid) . '"><a href="' . HTTP_SERVER . $this->ilance->ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=' . $shipperid . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration:underline">' . $this->ilance->shorten($this->ilance->shipping->print_shipping_partner($shipperid), 28) . '</a></span>';
						}
					}
					$row2['shipping'] = '+' . $this->ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
				}
				$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$row2['total'] = $this->ilance->currency->format(($row2['bidamount'] + $row2['buyershipcost']), $row2['currencyid']);
				$methodscount = $this->ilance->payment->print_payment_methods($row2['user_id'], false, true);
				$row2['startprice'] = $this->ilance->auction->fetch_auction('startprice',$row2['project_id']);
				$row2['started'] = $this->ilance->currency->format($row2['startprice'], $row2['currencyid']);
				$max_your_bid_query = $this->ilance->db->query("SELECT maxamount, user_id FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . $row2['project_id'] . "' AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' LIMIT 1");
				$fetch_your_max_bid = $this->ilance->db->fetch_array($max_your_bid_query, DB_ASSOC);
				$max_bid_query = $this->ilance->db->query("SELECT MAX(bidamount) AS max_bid, user_id FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . $row2['project_id'] . "' AND user_id != '" . $_SESSION['ilancedata']['user']['userid'] . "' LIMIT 1");
				$fetch_max_bid = $this->ilance->db->fetch_array($max_bid_query, DB_ASSOC);
				$show['show_maxproxybid'] = (($fetch_your_max_bid['maxamount'] > 0) ? 1 : 0);
				$row2['youmaxbid'] = $this->ilance->currency->format($fetch_your_max_bid['maxamount'], $row2['currencyid']);
				$row2['bidamount'] = $this->ilance->currency->format($row2['bidamount'], $row2['currencyid']);
				$this->ilance->show['no_product_bidding_activity'] = false;
				$this->ilance->show['bidpulldownmenu'] = true;
				$row2['show'] = $show;
				$product_bidding_activity[] = $row2;
				$row_count2++;
			}
			$vars = array(
				'numbers_of_rows' => $numberx,
				'bidsub_options' => $bidsub_options,
				'period_options' => $period_options,
				'block_header_title' => $block_header_title,
				'product_bidding_activity' => $product_bidding_activity
			);
	
			$this->api_success('user.buyingactivity.product');
			return array('error' => '0', 'message' => 'Success', 'buyingactivity_products' => $vars);
		}

		else {
			$vars = array(
				'numbers_of_rows' => $numberx, 
				'bidsub_options' => $bidsub_options,
				'period_options' => $period_options,
				'block_header_title' => $block_header_title,
				'product_bidding_activity' => 'No Results Found'
			);
			$this->api_success('user.buyingactivity.product');
			return array('error' => '0', 'message' => 'Success', 'buyingactivity_products' => $vars);
		}
		
	}
}
/*
http://hilite.me
$cookiefile = "cookies.txt";
if (!file_exists($cookiefile)) {$fh = fopen($cookiefile, "w");fwrite($fh, "");fclose($fh);}
$params = array(
	'itemid' => 93950958,
	'sku' => '',
	'price' => '25.50',
	'qty' => '22',
	'username' => 'Peter',
	'password' => '123123',
	'apikey' => '27157fbbaedf4661942f66a5b394e8fb',
	'csrftoken' => ''
);
$request = xmlrpc_encode_request('item.update', $params);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://demo.ilance.com/rpc/');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HEADER, 0);
// Cookie awareness
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
$results = curl_exec($ch);
$results = xmlrpc_decode($results);
curl_close($ch);
print_r($results);
*/

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Sun, Jun 16th, 2019
|| ####################################################################
\*======================================================================*/
?>