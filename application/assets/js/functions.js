var DOMTYPE = '';
if (document.getElementById)
{
	DOMTYPE = 'std';
}
else if (document.layers)
{
	DOMTYPE = 'ns4';
}
else if (document.all)
{
	DOMTYPE = 'ie4';
}
var supportstorage = function()
{
	try {
		return 'localStorage' in window && window['localStorage'] !== null;
 	}
	catch (e) {
		return false;
	}
}
/**
* AJAX Compatibility
*/
function AJAX_Handler(async)
{
	this.async = async ? true : false;
}
AJAX_Handler.prototype.init = function() {
	try {
		this.handler = new XMLHttpRequest();
		return (this.handler.setRequestHeader ? true : false);
	}
	catch(e) {
		try {
			this.handler = eval("new A" + "ctiv" + "eX" + "Ob" + "ject('Micr" + "osoft.XM" + "LHTTP');");
			return true;
		}
		catch(e) {
			return false;
		}
	}
}
AJAX_Handler.prototype.is_compatible = function() {
	if (typeof sheel_disable_ajax != 'undefined' && sheel_disable_ajax == 2) {
		return false; // disable ajax functionality
	}
	if (checkie && !checkie4) {
		return true;
	}
	else if (typeof XMLHttpRequest != 'undefined') {
		try {
			return XMLHttpRequest.prototype.setRequestHeader ? true : false;
		}
		catch(e) {
			try { var tester = new XMLHttpRequest(); return tester.setRequestHeader ? true : false; }
			catch(e) { return false; }
		}
	}
	else { return false; }
}
AJAX_Handler.prototype.not_ready = function() {
	return (this.handler.readyState && (this.handler.readyState < 4));
}
AJAX_Handler.prototype.onreadystatechange = function(event) {
	if (!this.handler) {
		if  (!this.init()) {
			return false;
		}
	}
	if (typeof event == 'function') {
		this.handler.onreadystatechange = event;
	}
	else {
		alert('XML Sender OnReadyState event is not a function');
	}
}
AJAX_Handler.prototype.send = function(url, data) {
	if (!this.handler) {
		if (!this.init()) {
			return false;
		}
	}
	if (!this.not_ready()) {
		this.handler.open('POST', url, this.async);
		this.handler.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		this.handler.send(data + '&s=' + fetch_session_id());
		if (!this.async && this.handler.readyState == 4 && this.handler.status == 200) {
			return true;
		}
	}
	return false;
}
AJAX_Handler.prototype.fetch_data = function(xml_node) {
	if (xml_node && xml_node.firstChild && xml_node.firstChild.nodeValue) {
		return unescape_cdata(xml_node.firstChild.nodeValue);
	}
	else {
		return '';
	}
}
/**
* Set AJAX Compatiblity
*/
var AJAX_Compatible = AJAX_Handler.prototype.is_compatible();

/**
* Fetch Browser Agent
*/
var AGENT = navigator.userAgent.toLowerCase();
var checkopera = (AGENT.indexOf('opera') != -1);
var checksaf = ((AGENT.indexOf('safari') != -1) || (navigator.vendor && navigator.vendor.toLowerCase().indexOf('apple')));
var checkwebtv = (AGENT.indexOf('webtv') != -1);
var checkie = ((AGENT.indexOf('msie') != -1) && (!checkopera) && (!checksaf) && (!checkwebtv));
var checkie4 = ((checkie) && (AGENT.indexOf('msie 4.') != -1));
var checkie7 = ((checkie) && (AGENT.indexOf('msie 7.') != -1));
var checkmoz = ((navigator.product == 'Gecko') && (!checksaf));
var checkkon = (AGENT.indexOf('konqueror') != -1);
var checkns = ((AGENT.indexOf('compatible') == -1) && (AGENT.indexOf('mozilla') != -1) && (!checkopera) && (!checkwebtv) && (!checksaf));
var checkns4 = ((checkns) && (parseInt(navigator.appVersion) == 4));
var checkmac = (navigator.vendor.toLowerCase().indexOf('apple') != -1);
var checkchrome = (AGENT.indexOf('chrome') != -1);
var checkiphone = (AGENT.indexOf('iphone') != -1);
var checkblackberry = (AGENT.indexOf('blackberry') != -1);

/**
* Define slideshow rotation delay on item listing page
*/
var current = 0;
var attw = null;
var drww = null;
var checkobj = null;
var category_popup_timer = null;

/**
* AJAX and Regular Expressions Compatibility
*/
var REGEXP_Compatible = (window.RegExp) ? true : false;
var AJAX_Compatible = false;
var http_request = null;

/**
* Other variables used in this script
*/
var cbchecked = false;
var cbchecked2 = false;

/**
* Full dialog modal defaults
*/
var current_container_panel = null;
var current_container_iframe = null;
var current_container = null;
var current_container_w = null;

/**
* Mobile helper functions
*/
function is_portrait()
{
	return window.innerHeight > window.innerWidth;
}
function is_landscape()
{
	return (window.orientation === 90 || window.orientation === -90);
}
function is_touch_device()
{
	return typeof window.ontouchstart !== 'undefined';
}
function is_mobile_device()
{
	var check = false;
	(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
}
/**
* Core Functions
*/
function fetch_session_id()
{
	return (iL['SESSION'] == '' ? '' : iL['SESSION'].substr(2, 32));
}
function fetch_js_object(idname)
{
	try {
		if (document.getElementById) {
			return document.getElementById(idname);
		}
		else if (document.all) {
			return document.all[idname];
		}
		else if (document.layers) {
			return document.layers[idname];
		}
	}
	catch (e) {
		console.log('fetch_js_object(\'' + idname + '\') -  not found');
		return null;
	}
}
function fetch_js_cookie(name)
{
	v3cookiename = name + '=';
	v3cookiesize = document.cookie.length;
	v3cookiestart = 0;
	while (v3cookiestart < v3cookiesize) {
		v3cookievalue = v3cookiestart + v3cookiename.length;
		if (document.cookie.substring(v3cookiestart, v3cookievalue) == v3cookiename) {
			var v3cookievalue2 = document.cookie.indexOf (';', v3cookievalue);
			if (v3cookievalue2 == -1) {
				v3cookievalue2 = v3cookiesize;
			}
			return unescape(document.cookie.substring(v3cookievalue, v3cookievalue2));
		}
		v3cookiestart = document.cookie.indexOf(' ', v3cookiestart) + 1;
		if (v3cookiestart == 0) {
			break;
		}
	}
	return null;
}
function update_js_cookie(name, value, expires, secure, httponly)
{
	if (!expires) {
		expires = new Date();
	}
	document.cookie = name + "=" + escape(value) + "; expires=" + expires.toGMTString() +  "; path=/" + (secure ? '; secure' : '') + (httponly ? '; HttpOnly' : '');
}
function update_js_collapse_cookie(objid, setcookiedata, cookiename)
{
	var cookiedata = fetch_js_cookie(cookiename);
	var cookietemp = new Array();
	if (cookiedata != null) {
		cookiedata = cookiedata.split('|');
		for (i in cookiedata) {
			if (cookiedata[i] != objid && cookiedata[i] != '') {
				cookietemp[cookietemp.length] = cookiedata[i];
			}
		}
	}
	if (setcookiedata) {
		cookietemp[cookietemp.length] = objid;
	}
	cookieexpire = new Date();
	cookieexpire.setTime(cookieexpire.getTime() + (500 * 86400 * 365));
	update_js_cookie(cookiename, cookietemp.join("|"), cookieexpire);
}
function toggle(objid)
{
	if (!REGEXP_Compatible) {
		return false;
	}
	obj = fetch_js_object('collapseobj_' + objid);
	img = fetch_js_object('collapseimg_' + objid);
	if (!obj) {
		if (img) {
			img.style.display = 'none';
		}
		return false;
	}
	// #### our data is collapsed lets show it #############################
	if (obj.style.display == 'none') {
		obj.style.display = '';
		// #### tell cookie to remove this obj from the list so php can show it
		update_js_collapse_cookie(objid, false, iL['COOKIENAME'] + 'collapse');
		if (img) {
			// #### flip the collapsed icon to expanded state
			img_re = new RegExp("_collapsed\\.gif$");
			img.src = img.src.replace(img_re, '.gif');
		}
	}
	// #### our data is expanded lets hide it ##############################
	else {
		obj.style.display = 'none';
		// #### tell cookie to add this obj to the list so php can hide it
		update_js_collapse_cookie(objid, true, iL['COOKIENAME'] + 'collapse');
		if (img) {
			// #### flip the expanded icon collapsed state
			img_re = new RegExp("\\.gif$");
			img.src = img.src.replace(img_re, '_collapsed.gif');
		}
	}
	return false;
}
function agreesubmit(el)
{
	checkobj = el
	if (document.all || document.getElementById) {
		for (i = 0; i < checkobj.form.length; i++) {
			var tempobj = checkobj.form.elements[i];
			if (tempobj.type.toLowerCase() == 'submit' || tempobj.type.toLowerCase() == 'button') {
				tempobj.disabled = !checkobj.checked
			}
		}
	}
}
function defaultagree(formobj)
{ // attach to onsubmit="return defaultagree()"
	var ischecked = formobj.agreecheck.checked;
	if (ischecked == 0) {
  		alert_js(phrase['_please_read_accept_terms_to_submit_form']);
		return false;
	}
	return true;
}
function confirm_js(message)
{
	if (confirm(message)) {
		return true;
	}
	else {
		return false;
	}
}
function alert_js(message)
{
	message.replace(/'/g, "\x27");
	message.replace(/"/g, "\x22");
	jQuery.growl.error({ size: 'large', duration: 5200, title: phrase['_notice'], message: message });
	return true;
}
function notice_js(title, message)
{
	message.replace(/'/g, "\x27");
	message.replace(/"/g, "\x22");
	jQuery.growl.notice({ size: 'large', duration: 5200, title: title, message: message });
	return true;
}
function showImage(imagename, imageurl, errors)
{
	document[imagename].src = imageurl;
	if (!haveerrors && errors) {
		haveerrors = errors;
	}
}
function showImageInline(inputid, errors, showcheckmark)
{
	if (jQuery('#' + inputid).length > 0) {
		if (errors && !showcheckmark) {
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)show_checkmark(?!\S)/g, '');
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)show(?!\S)/g, '');
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)redborder(?!\S)/g, '');
			fetch_js_object(inputid).className += ' show redborder';
		}
		else {
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)show(?!\S)/g, '');
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)redborder(?!\S)/g, '');
		}
		if (showcheckmark) {
			fetch_js_object(inputid).className += ' show_checkmark';
		}
		if (!haveerrors && errors) {
			haveerrors = errors;
		}
	}
}
function showInlineBorder(inputid, errors)
{
	if (jQuery('#' + inputid).length > 0)
	{
		if (errors)
		{
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)redborder(?!\S)/g, '');
			fetch_js_object(inputid).className += ' redborder';
		}
		else
		{
			fetch_js_object(inputid).className = fetch_js_object(inputid).className.replace(/(?:^|\s)redborder(?!\S)/g, '');
		}
	}
	if (!haveerrors && errors)
	{
		haveerrors = errors;
	}
}
function noenter()
{
	return !(window.event && window.event.keyCode == 13);
}
function createWindow(u, n, w, h, r)
{
	args = 'width=' + w + ',height=' + h + ',resizable=yes,scrollbars=yes,status=0';
	remote = window.open(u, n, args);
	if (remote != null)
	{
		if (remote.opener == null)
		{
			remote.opener = self;
		}
	}
	if (r == 1)
	{
		return remote;
	}
}
function Attach(url)
{
	if (!attw || attw.closed)
	{
		browsername=navigator.appName;
		if (browsername.indexOf("Netscape")!=-1)
		{
			attw = createWindow(url, 'attachwin', 480, 450, 1);
		}
		else
		{
			attw = createWindow(url, 'attachwin', 470, 450, 1);
		}
	}
	attw.focus();
}
function toggle_block(target)
{
	if (jQuery('#' + target).length) {
		obj = fetch_js_object(target);
		obj.style.display = (obj.style.display == 'none') ? 'inline-block' : 'none';
	}
}
function toggle_tr(target)
{
	if (jQuery('#' + target).length) {
		obj = fetch_js_object(target);
		obj.style.display = (obj.style.display == 'none') ? 'inline' : 'none';
	}
}
function toggle_hide_tr(target)
{
	if (jQuery('#' + target).length) {
		obj = fetch_js_object(target);
		obj.style.display = 'none';
	}
}
function toggle_show_tr(target)
{
	if (jQuery('#' + target).length) {
		obj = fetch_js_object(target);
		obj.style.display = 'inline';
	}
}
function toggle_more(target, showmoreid, moretext, lesstext, showmoreicon)
{
	obj = fetch_js_object(target);
	if (obj.style.display == 'none')
	{
		obj.style.display = '';
		update_js_collapse_cookie(target, true, iL['COOKIENAME'] + 'collapse');
	}
	else
	{
		obj.style.display = 'none';
		update_js_collapse_cookie(target, false, iL['COOKIENAME'] + 'collapse');
	}
	obj2 = fetch_js_object(showmoreid);
	obj2.style.fontweight = 'bold';
	obj2.innerHTML = (obj.style.display == 'none')
		? moretext
		: lesstext;
	obj3 = fetch_js_object(showmoreicon);
	obj3.src = (obj.style.display == 'none')
		? iL['CDNIMG'] + 'v5/ico_arrow_down.gif'
		: iL['CDNIMG'] + 'v5/ico_arrow_up.gif';
	return false;
}
function toggle_height(target, max, min)
{
	obj = fetch_js_object(target);
	obj.style.height = ((obj.style.height == max + 'px') ? min : max) + 'px';
}
function toggle_bid_row(objid)
{
	if (jQuery('#' + objid).length) {
		//2i7duqgirf = fetch_js_object(objid);
		//2i7duqgirf.style.display = (2i7duqgirf.style.display == 'none') ? '' : 'none';
	}
}
function toggle_hide(target)
{
	if (jQuery('#' + target).length)
	{
		if (jQuery('#' + target).hasClass('hide') == false)
		{
			jQuery('#' + target).addClass('hide');
		}
	}
	else
	{
		console.log("toggle_hide('" + target + "') not found");
	}
}
function toggle_show(target)
{
	if (jQuery('#' + target).length)
	{
		if (jQuery('#' + target).hasClass('hide'))
		{
			jQuery('#' + target).removeClass('hide');
		}
		else
		{
			obj = fetch_js_object(target);
			obj.style.display = '';
		}
	}
	else
	{
		console.log("toggle_show('" + target + "') not found");
	}
}
function toggler(target)
{
	if (jQuery('#' + target).length)
	{
		if (jQuery('#' + target).hasClass('hide'))
		{
			jQuery('#' + target).removeClass('hide');
		}
		else if (jQuery('#' + target).hasClass('hide') == false)
		{
			jQuery('#' + target).addClass('hide');
		}
		else
		{ // backward compatibility for style="display:none"..
			obj = fetch_js_object(target);
			obj.style.display = (obj.style.display == 'none') ? 'inline' : 'none';
		}
	}
}
function trim(field)
{
	value = field;
	while (value.charAt(value.length - 1) == " ")
	{
		value = value.substring(0, value.length-1);
	}
	while (value.substring(0, 1) == " ")
	{
		value = value.substring(1, value.length);
	}
	return value;
}
function popUP(mypage, myname, w, h, scroll, titlebar)
{
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
	win = window.open(mypage, myname, winprops)
	if (parseInt(navigator.appVersion) >= 4)
	{
		win.window.focus();
	}
}
function urlswitch(styleobj, _type)
{
	var themeid = styleobj.options[styleobj.selectedIndex].value;
	if (themeid == '')
	{
		return;
	}
	var url = new String(window.location);
	var fragment = new String('');
	url = url.split('#');
	if (url[1])
	{
		fragment = '#' + url[1];
	}
	url = url[0];
	if (_type == 'dostyle')
	{
		if (url.indexOf('styleid=') != -1)
		{
			re = new RegExp("styleid=\\d+&?");
			url = url.replace(re, '');
		}
	}
	else
	{
	    if (url.indexOf('language=') != -1)
	    {
		    re = new RegExp("language=\\D+&?");
		    url = url.replace(re, '');
	    }
	}
	if (url.indexOf('?') == -1)
	{
		url += '?';
	}
	else
	{
		endchar = url.substr(url.length - 1);
		if (endchar != '&' && endchar != '?')
		{
			url += '&';
		}
	}
	if (_type == 'dostyle')
	{
		window.location = url + 'styleid=' + themeid + fragment;
	}
	else
	{
		window.location = url + 'language=' + themeid + fragment;
	}
}
function fetch_tags(parentobj, tag)
{
	if (parentobj == null)
	{
		return new Array();
	}
	else if (typeof parentobj.getElementsByTagName != 'undefined')
	{
		return parentobj.getElementsByTagName(tag);
	}
	else if (parentobj.all && parentobj.all.tags)
	{
		return parentobj.all.tags(tag);
	}
	else
	{
		return new Array();
	}
}
function construct_phrase()
{
	if (!arguments || arguments.length < 1 || !REGEXP_Compatible)
	{
		return false;
	}
	var args = arguments;
	var str = args[0];
	var re;
	for (var i = 1; i < args.length; i++)
	{
		re = new RegExp("%" + i + "\\$s", 'gi');
		str = str.replace(re, args[i]);
	}
	return str;
}
function unescape_cdata(str)
{
	var r1 = /<\=\!\=\[\=C\=D\=A\=T\=A\=\[/g;
	var r2 = /\]\=\]\=>/g;
	return str.replace(r1, '<![CDATA[').replace(r2, ']]>');
}
function urlencode(text)
{
	text = escape(text.toString()).replace(/\+/g, "%2B");
	var matches = text.match(/(%([0-9A-F]{2}))/gi);
	if (matches)
	{
		for (var matchid = 0; matchid < matches.length; matchid++)
		{
			var code = matches[matchid].substring(1,3);
			if (parseInt(code, 16) >= 128)
			{
				text = text.replace(matches[matchid], '%u00' + code);
			}
		}
	}
	text = text.replace('%25', '%u0025');
	return text;
}
function fetch_watchlist_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		if (phpstatus == 'addeditem')
		{
			if (xmldata.hrefclass === -1)
			{
				fetch_js_object(xmldata.divid).innerHTML = phrase['_you_are_watching_this_item_js'];
			}
			else
			{
				fetch_js_object(xmldata.divid).innerHTML = '<a href="' + iL['BASEURL'] + 'watchlist/" class="' + xmldata.hrefclass + '">' + phrase['_you_are_watching_this_item_js'] + '</a>';
			}
		}
		else if (phpstatus == 'removeditem')
		{
			if (xmldata.hrefclass === -1)
			{
				fetch_js_object(xmldata.divid).innerHTML = phrase['_watch_this_item'];
				if (jQuery('#follow-seller').length)
				{
					jQuery('#follow-seller').attr('onclick', "follow('item', 0, '" + xmldata.userid + "', " + xmldata.itemid + ", '" + xmldata.divid + "', -1, " + xmldata.followcount + ")");
				}
			}
			else
			{
				fetch_js_object(xmldata.divid).innerHTML = '<a href="' + iL['BASEURL'] + 'watchlist/sellers/" class="' + xmldata.hrefclass + '">' + phrase['_watch_this_item'] + '</a>';
			}
		}
		else if (phpstatus == 'addedseller')
		{
			if (xmldata.divid != '')
			{
				if (jQuery('#' + xmldata.divid).length)
				{
					var currentclass = jQuery('#' + xmldata.divid).attr('class');
					var currentphrase = jQuery('#' + xmldata.divid).html();
					jQuery('#' + xmldata.divid).attr('onclick', "unfollow('" + xmldata.type + "', '" + xmldata.sellerid + "', '" + xmldata.userid + "', '', '" + xmldata.divid + "', '', " + ((xmldata.followcount != -1) ? ((xmldata.followcount * 1) + 1) : -1) + ", '" + currentphrase + "')");
					jQuery('#' + xmldata.divid).html(xmldata.unfollowphrase);
					if (xmldata.hrefclass != '')
					{
						jQuery('#' + xmldata.divid).removeClass();
						jQuery('#' + xmldata.divid).addClass(xmldata.hrefclass);
						jQuery('#' + xmldata.divid).attr('onclick', "unfollow('" + xmldata.type + "', '" + xmldata.sellerid + "', '" + xmldata.userid + "', '', '" + xmldata.divid + "', '" + currentclass + "', " + ((xmldata.followcount != -1) ? ((xmldata.followcount * 1) + 1) : -1) + ", '" + currentphrase + "')");
					}
				}
				if (jQuery('#fcount').length)
				{
					jQuery('#fcount').html((xmldata.followcount * 1) + 1);
				}
			}
		}
		else if (phpstatus == 'removedseller')
		{
			if (xmldata.divid != '')
			{
				if (jQuery('#' + xmldata.divid).length)
				{
					var currentclass = jQuery('#' + xmldata.divid).attr('class');
					var currentphrase = jQuery('#' + xmldata.divid).html();
					jQuery('#' + xmldata.divid).attr('onclick', "follow('" + xmldata.type + "', '" + xmldata.sellerid + "', '" + xmldata.userid + "', '', '" + xmldata.divid + "', '', " + ((xmldata.followcount != -1) ? ((xmldata.followcount * 1) - 1) : -1) + ", '" + currentphrase + "')");
					jQuery('#' + xmldata.divid).html(xmldata.followphrase);
					if (xmldata.hrefclass != '')
					{
						jQuery('#' + xmldata.divid).removeClass();
						jQuery('#' + xmldata.divid).addClass(xmldata.hrefclass);
						jQuery('#' + xmldata.divid).attr('onclick', "follow('" + xmldata.type + "', '" + xmldata.sellerid + "', '" + xmldata.userid + "', '', '" + xmldata.divid + "', '" + currentclass + "', " + ((xmldata.followcount != -1) ? ((xmldata.followcount * 1) - 1) : -1) + ", '" + currentphrase + "')");
					}
				}
				if (jQuery('#fcount').length)
				{
					jQuery('#fcount').html((xmldata.followcount*1) - 1);
				}
			}
		}
		else if (phpstatus == 'error')
		{
			if (xmldata.hrefclass === -1)
			{
				fetch_js_object(xmldata.divid).innerHTML = phrase['_please_signin'];
			}
			else
			{
				fetch_js_object(xmldata.divid).innerHTML = '<a href="' + iL['BASEURL'] + 'signin/" class="' + xmldata.hrefclass + '">' + phrase['_please_signin'] + '</a>';
			}
		}
		xmldata.handler.abort();
	}
}
function add_item_to_watchlist(projectid, userid, divid, classid)
{
	if (userid == '' || userid == 0 || projectid == '' || projectid == 0)
	{
		fetch_js_object(divid).innerHTML = '<a href="' + iL['BASEURL'] + 'signin/" class="' + classid + '">' + phrase['_please_signin_to_save_items_to_watchlist'] + '</a>';
		return false;
	}
	xmldata = new AJAX_Handler(true);
	xmldata.projectid = projectid;
	xmldata.userid = userid;
	xmldata.divid = divid;
	xmldata.hrefclass = classid;
	xmldata.onreadystatechange(fetch_watchlist_response);
	xmldata.send(iL['AJAXURL'], 'do=addwatchlist&projectid=' + urlencode(projectid) + '&userid=' + urlencode(userid) + '&token=' + iL['TOKEN']);
}
function follow(type, sellerid, userid, itemid, divid, hrefclass, followcount, unfollowphrase)
{
	if (userid == '' || userid == 0)
	{
		fetch_js_object(divid).innerHTML = phrase['_please_signin'];
		location.href = iL['BASEURL'] + 'signin/?redirect=' + encodeURIComponent(iL['URI']);
		return false;
	}
	else if (userid == sellerid)
	{
		alert_js(phrase['_you_cannot_follow_yourself_js']);
		return false;
	}
	xmldata = new AJAX_Handler(true);
	xmldata.type = type;
	xmldata.sellerid = sellerid;
	xmldata.itemid = itemid;
	xmldata.userid = userid;
	xmldata.divid = divid;
	xmldata.hrefclass = hrefclass; // may be blank
	xmldata.followcount = followcount;
	xmldata.unfollowphrase = unfollowphrase;
	xmldata.onreadystatechange(fetch_watchlist_response);
	xmldata.send(iL['AJAXURL'], 'do=follow&sellerid=' + urlencode(sellerid) + '&userid=' + urlencode(userid) + '&itemid=' + urlencode(itemid) + '&token=' + iL['TOKEN']);
}
function unfollow(type, sellerid, userid, itemid, divid, hrefclass, followcount, followphrase)
{
	if (userid == '' || userid == 0)
	{
		fetch_js_object(divid).innerHTML = phrase['_please_signin'];
		location.href = iL['BASEURL'] + 'signin/?redirect=' + encodeURIComponent(iL['URI']);
		return false;
	}
	xmldata = new AJAX_Handler(true);
	xmldata.type = type;
	xmldata.sellerid = sellerid;
	xmldata.userid = userid;
	xmldata.divid = divid;
	xmldata.hrefclass = hrefclass;
	xmldata.followcount = followcount;
	xmldata.followphrase = followphrase;
	xmldata.onreadystatechange(fetch_watchlist_response);
	xmldata.send(iL['AJAXURL'], 'do=unfollow&sellerid=' + urlencode(sellerid) + '&userid=' + urlencode(userid) + '&itemid=' + urlencode(itemid) + '&token=' + iL['TOKEN']);
}
function print_states(fieldname, countryfieldname, divstateid, shortform, extracss, disablecities, citiesfieldname, citiesdivid)
{
	var ajaxRequest;
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function() {
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divstateid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	fetch_js_object(fieldname).disabled = true;
	var countryname = fetch_js_object(countryfieldname).options[fetch_js_object(countryfieldname).selectedIndex].value;
	var querystring = "&countryname=" + countryname + "&fieldname=" + fieldname + "&shortform=" + shortform + "&extracss=" + extracss + "&disablecities=" + disablecities + "&citiesfieldname=" + citiesfieldname + "&citiesdivid=" + citiesdivid + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=showstates' + querystring, true);
	ajaxRequest.send(null);
}
function print_cities(fieldname, statefieldname, divcityid, extracss)
{
	var ajaxRequest;
	var currentcitiesclass = jQuery('#' + fieldname).attr('class').replace(/input /g, '');
	var currentstateclasswidth = jQuery('#state-wrapper').attr('class').replace(/draw-select__wrapper /g, ''); // draw-select__wrapper w-355 = w-355
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function() {
		fetch_js_object(fieldname).disabled = true;
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
			var ajaxDisplay = fetch_js_object(divcityid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			fetch_js_object(fieldname).disabled = false;
		}
	}
	var statename = fetch_js_object(statefieldname).options[fetch_js_object(statefieldname).selectedIndex].value;
	ajaxRequest.open('GET', iL['AJAXURL'] + "?do=showcities&state=" + statename + "&fieldname=" + fieldname + "&extracss=" + extracss + "&currentcitiesclass=" + currentcitiesclass + "&currentstateclasswidth=" + currentstateclasswidth + "&token=" + iL['TOKEN'], true);
	ajaxRequest.send(null);
}
function get_html_translation_table(table, quote_style)
{
	// http://kevin.vanzonneveld.net
	// +   original by: Philip Peterson
	// +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   bugfixed by: noname
	// %          note: It has been decided that we're not going to add global
	// %          note: dependencies to php.js. Meaning the constants are not
	// %          note: real constants, but strings instead. integers are also supported if someone
	// %          note: chooses to create the constants themselves.
	// %          note: Table from http://www.the-art-of-web.com/html/character-codes/
	// *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
	// *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

	var entities = {}, histogram = {}, decimal = 0, symbol = '';
	var constMappingTable = {}, constMappingQuoteStyle = {};
	var useTable = {}, useQuoteStyle = {};

	useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
	useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');

	// Translate arguments
	constMappingTable[0]      = 'HTML_SPECIALCHARS';
	constMappingTable[1]      = 'HTML_ENTITIES';
	constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
	constMappingQuoteStyle[2] = 'ENT_COMPAT';
	constMappingQuoteStyle[3] = 'ENT_QUOTES';

	// Map numbers to strings for compatibilty with PHP constants
	if (!isNaN(useTable)) {
	    useTable = constMappingTable[useTable];
	}
	if (!isNaN(useQuoteStyle)) {
	    useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
	}

	if (useQuoteStyle != 'ENT_NOQUOTES') {
	    entities['34'] = '&quot;';
	}

	if (useQuoteStyle == 'ENT_QUOTES') {
	    entities['39'] = '&#039;';
	}

	if (useTable == 'HTML_SPECIALCHARS') {
	    // ascii decimals for better compatibility
	    entities['38'] = '&amp;';
	    entities['60'] = '&lt;';
	    entities['62'] = '&gt;';
	} else if (useTable == 'HTML_ENTITIES') {
	    // ascii decimals for better compatibility
	  entities['38']  = '&amp;';
	  entities['60']  = '&lt;';
	  entities['62']  = '&gt;';
	  entities['160'] = '&nbsp;';
	  entities['161'] = '&iexcl;';
	  entities['162'] = '&cent;';
	  entities['163'] = '&pound;';
	  entities['164'] = '&curren;';
	  entities['165'] = '&yen;';
	  entities['166'] = '&brvbar;';
	  entities['167'] = '&sect;';
	  entities['168'] = '&uml;';
	  entities['169'] = '&copy;';
	  entities['170'] = '&ordf;';
	  entities['171'] = '&laquo;';
	  entities['172'] = '&not;';
	  entities['173'] = '&shy;';
	  entities['174'] = '&reg;';
	  entities['175'] = '&macr;';
	  entities['176'] = '&deg;';
	  entities['177'] = '&plusmn;';
	  entities['178'] = '&sup2;';
	  entities['179'] = '&sup3;';
	  entities['180'] = '&acute;';
	  entities['181'] = '&micro;';
	  entities['182'] = '&para;';
	  entities['183'] = '&middot;';
	  entities['184'] = '&cedil;';
	  entities['185'] = '&sup1;';
	  entities['186'] = '&ordm;';
	  entities['187'] = '&raquo;';
	  entities['188'] = '&frac14;';
	  entities['189'] = '&frac12;';
	  entities['190'] = '&frac34;';
	  entities['191'] = '&iquest;';
	  entities['192'] = '&Agrave;';
	  entities['193'] = '&Aacute;';
	  entities['194'] = '&Acirc;';
	  entities['195'] = '&Atilde;';
	  entities['196'] = '&Auml;';
	  entities['197'] = '&Aring;';
	  entities['198'] = '&AElig;';
	  entities['199'] = '&Ccedil;';
	  entities['200'] = '&Egrave;';
	  entities['201'] = '&Eacute;';
	  entities['202'] = '&Ecirc;';
	  entities['203'] = '&Euml;';
	  entities['204'] = '&Igrave;';
	  entities['205'] = '&Iacute;';
	  entities['206'] = '&Icirc;';
	  entities['207'] = '&Iuml;';
	  entities['208'] = '&ETH;';
	  entities['209'] = '&Ntilde;';
	  entities['210'] = '&Ograve;';
	  entities['211'] = '&Oacute;';
	  entities['212'] = '&Ocirc;';
	  entities['213'] = '&Otilde;';
	  entities['214'] = '&Ouml;';
	  entities['215'] = '&times;';
	  entities['216'] = '&Oslash;';
	  entities['217'] = '&Ugrave;';
	  entities['218'] = '&Uacute;';
	  entities['219'] = '&Ucirc;';
	  entities['220'] = '&Uuml;';
	  entities['221'] = '&Yacute;';
	  entities['222'] = '&THORN;';
	  entities['223'] = '&szlig;';
	  entities['224'] = '&agrave;';
	  entities['225'] = '&aacute;';
	  entities['226'] = '&acirc;';
	  entities['227'] = '&atilde;';
	  entities['228'] = '&auml;';
	  entities['229'] = '&aring;';
	  entities['230'] = '&aelig;';
	  entities['231'] = '&ccedil;';
	  entities['232'] = '&egrave;';
	  entities['233'] = '&eacute;';
	  entities['234'] = '&ecirc;';
	  entities['235'] = '&euml;';
	  entities['236'] = '&igrave;';
	  entities['237'] = '&iacute;';
	  entities['238'] = '&icirc;';
	  entities['239'] = '&iuml;';
	  entities['240'] = '&eth;';
	  entities['241'] = '&ntilde;';
	  entities['242'] = '&ograve;';
	  entities['243'] = '&oacute;';
	  entities['244'] = '&ocirc;';
	  entities['245'] = '&otilde;';
	  entities['246'] = '&ouml;';
	  entities['247'] = '&divide;';
	  entities['248'] = '&oslash;';
	  entities['249'] = '&ugrave;';
	  entities['250'] = '&uacute;';
	  entities['251'] = '&ucirc;';
	  entities['252'] = '&uuml;';
	  entities['253'] = '&yacute;';
	  entities['254'] = '&thorn;';
	  entities['255'] = '&yuml;';
	}
	else
	{
		return false;
	}
	// ascii decimals to real symbols
	for (decimal in entities)
	{
		symbol = String.fromCharCode(decimal)
		histogram[symbol] = entities[decimal];
	}
	return histogram;
}
function html_entity_decode(string, quote_style)
{
	var histogram = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();
	if (false === (histogram = get_html_translation_table('HTML_ENTITIES', quote_style))) {
		return false;
	}
	// &amp; must be the last character when decoding!
	delete(histogram['&']);
	histogram['&'] = '&amp;';
	for (symbol in histogram) {
		entity = histogram[symbol];
		tmp_str = tmp_str.split(entity).join(symbol);
	}
	return tmp_str;
}
function htmlspecialchars(string, quote_style)
{
	var histogram = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();
	if (false === (histogram = get_html_translation_table('HTML_SPECIALCHARS', quote_style))) {
		return false;
	}
	for (symbol in histogram) {
		entity = histogram[symbol];
		tmp_str = tmp_str.split(symbol).join(entity);
	}
	return tmp_str;
}
function show_category_popup_link()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = '';
}
function show_category_popup_link1()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = '';
}
function show_category_popup()
{
	var div;
	div = fetch_js_object('category_popup');
	category_popup_timer = setTimeout("do_show_category_popup();", 750);
}
function show_category1_popup()
{
	var div;
	div = fetch_js_object('category_popup1');
	category_popup_timer = setTimeout("do_show_category_popup1();", 750);
}
function hide_category1_popup()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var div;
	div = fetch_js_object('category_popup1');
	category_popup_timer = setTimeout("do_hide_category_popup1();", 750);
}
function do_hide_category_popup1()
{
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = 'none';
}
function do_show_category_popup1()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = '';
}
function hide_category_popup()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var div;
	div = fetch_js_object('category_popup');
	category_popup_timer = setTimeout("do_hide_category_popup();", 550);
}
function do_hide_category_popup()
{
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = 'none';
}
function do_show_category_popup()
{
	if (category_popup_timer != null) {
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = '';
}
function sheel_prompt(str)
{
	try {
		str = str.replace(/<[^>]+>/ig,'');
		return prompt(str, '');
	}
	catch (e) {
		return false;
	}
}
function string_to_number(price)
{
	var thous = " '", decPoint, decComma, decMark, dec, re, matches, pre = '3';
	price = price.replace(/^\s+|\s+$/g, "");
	decPoint = price.lastIndexOf('.');
	decComma = price.lastIndexOf(',');
	if (decPoint > -1 && decComma > -1) {
		if (decPoint > decComma) {
			thous += ',';
		}
		else {
			thous += '.';
			decMark = ',';
		}
	}
	if ((price.indexOf(' ') > 0 || price.indexOf("'") > 0) && decComma) {
		decMark = ',';
	}
	if (price.substring(decPoint + 1).length === 3 && decComma < 1 && price.indexOf('.') < decPoint) {
		thous += '.';
	}
	if (price.substring(decComma + 1).length === 3 && decPoint < 1 && price.indexOf(',') < decComma) {
		thous += ',';
	}
	re = new RegExp("^(?:(\\d{1,3}(?:(?:(?:[" + thous + "]\\d{3})+)?)?|\\d+)?([,.]\\d{1,})?|\\d+)$");
	matches = re.exec(price);
	if (!matches) {
		return false;
	}
	if (!matches[1] && !matches[2]) {
		matches[1] = matches[0];
	}
	dec = matches[2] && matches[2].length === 4 && !decMark && matches[1] !== '0' ? '' : '.';
	return (matches[1] || '').replace(/[,' .]/g, '') + '' + (matches[2] || '').replace(',', dec);
}
function show_working_icon(icondiv)
{
	toggle_show(icondiv);
	fetch_js_object(icondiv).innerHTML = '<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" border="0" width="13" height="13" />';
}
function disable_submit_button(theform, thephrase, showworkingicon, icondiv)
{
	if (document.all || document.getElementById) {
		for (i = 0; i < theform.length; i++) {
			var tempobj = theform.elements[i];
			if (tempobj.type.toLowerCase() == 'submit' || tempobj.type.toLowerCase() == 'reset') {
				tempobj.disabled = true;
			}
		}
		if (showworkingicon == 1) {
			show_working_icon(icondiv);
		}
		setTimeout('alert_js("' + thephrase + '")', 2000);
		return true;
	}
}
function rollovericon(img_name, img_src)
{
	document[img_name].src = img_src;
}
function mysql_datetime_to_js_date(timestamp)
{
	var regex = /^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
	var parts = timestamp.replace(regex, "$1 $2 $3 $4 $5 $6").split(' ');
	return new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
}
function validate_otp_code(f)
{
        haveerrors = 0;
        (f.otp.value.length < 1) ? showImageInline('otp', true, false) : showImageInline('otp', false, false);
        return (!haveerrors);
}
function validate_all_pmb_fields()
{
	if (fetch_js_object('message_id').value == '') {
		alert_js(phrase['_your_description_or_message_should_not_be_empty']);
		return false;
	}
	return true;
}
function refresh_pmb_conversation(crypted)
{
	fetch_js_object('pmbcrypted_modal').value = crypted;
	xmldata = new AJAX_Handler(true);
	xmldata.crypted = crypted;
	toggle_show('pmbconversation');
	fetch_js_object('modal_pmb_working').innerHTML = '<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" alt="working" />';
	xmldata.onreadystatechange(function () {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			fetch_js_object('modal_pmb_working').innerHTML = '';
			if (jQuery('#attachmentlist').length) {
				fetch_js_object('attachmentlist').innerHTML = '';
			}
			fetch_js_object('pmbconversation').innerHTML = '';
			fetch_js_object('lineitemlist').innerHTML = '';
			if (xmldata.handler.responseText != '') {
				var attachinfo;
				attachinfo = xmldata.handler.responseText;
				attachinfo = attachinfo.split("|");
				if (jQuery('#attachmentlist').length) {
					fetch_js_object('attachmentlist').innerHTML = attachinfo[0];
				}
				fetch_js_object('pmbconversation').innerHTML = attachinfo[2];
				setTimeout('refresh_pmb_conversation(xmldata.crypted)', 10000);
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=pminfo&crypted=' + crypted + '&conversation=1&token=' + iL['TOKEN']);
	return true;
}
function validate_all_violation_fields()
{
	jQuery('#wrapper-reason').removeClass('redborder');
	if (jQuery('#abusereason').length) {
		if (fetch_js_object('abusereason').options[fetch_js_object('abusereason').selectedIndex].value == '') {
			jQuery.growl.error({ size: 'large', duration: 5200, title: phrase['_error'], message: phrase['_you_did_not_select_reason_for_report_try_again'] });
			jQuery('#wrapper-reason').addClass('redborder');
			return false;
		}
		else if (fetch_js_object('abusereason').options[fetch_js_object('abusereason').selectedIndex].value == phrase['_other'] && jQuery('#abusemessage').val() == '') {
			jQuery.growl.error({ size: 'large', duration: 5200, title: phrase['_error'], message: phrase['_you_selected_other_but_no_comment_for_report_try_again'] });
			jQuery('#wrapper-reason').removeClass('redborder');
			jQuery('#abusemessage').addClass('redborder');
			return false;
		}
	}
	return true;
}
function submit_violation()
{
	validate = validate_all_violation_fields();
	return validate;
}
function submit_pmb()
{
	validate = validate_all_pmb_fields();
	if (validate == true) {
		xmldatasubmit = new AJAX_Handler(true);
		crypted = fetch_js_object('pmbcrypted_modal').value;
		message = fetch_js_object('message_id').value;
		xmldatasubmit.crypted = crypted;
		xmldatasubmit.message = message;
		fetch_js_object('submitpmbbutton').disabled = true;
		fetch_js_object('modal_pmb_working').innerHTML = '<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" alt="" border="" id="" />';
		xmldatasubmit.onreadystatechange(function() {
			fetch_js_object('message_id').disabled = true;
			if (xmldatasubmit.handler.readyState == 4 && xmldatasubmit.handler.status == 200) {
				fetch_js_object('message_id').disabled = false;
				fetch_js_object('submitpmbbutton').disabled = false;
				fetch_js_object('modal_pmb_working').innerHTML = '';
				fetch_js_object('message_id').value = '';
				if (xmldatasubmit.handler.responseText != '') {
					fetch_js_object('pmnotices').innerHTML = xmldatasubmit.handler.responseText;
					smoothscroll('conversation');
				}
				xmldatasubmit.handler.abort();
			}
		});
		xmldatasubmit.send(iL['AJAXURL'], 'do=submitpm&crypted=' + crypted + '&message=' + urlencode(message) + '&s=' + iL['SESSION'] + '&token=' + iL['TOKEN']);
		return true;
	}
	return false;
}
function upload_modal(attachmentpane, url, height)
{
	fetch_js_object(attachmentpane).innerHTML = '<iframe src="' + url + '" name="pmcontainer" id="pmcontainer" class="w-100pct" style="height:' + height + 'px" scrolling="no" frameborder="0"></iframe>';
}
function remove_pmb_post(id)
{
	xmlrdata = new AJAX_Handler(true);
	xmlrdata.id = id;
	fetch_js_object('pmbid_' + id).disabled = true;
	xmlrdata.onreadystatechange(function () {
		if (xmlrdata.handler.readyState == 4 && xmlrdata.handler.status == 200) {
			fetch_js_object('pmbid_' + xmlrdata.id).disabled = false;
			if (xmlrdata.handler.responseText == '1') {
				toggle_hide('pmbpostblock_' + xmlrdata.id);
			}
			xmlrdata.handler.abort();
		}
	});
	xmlrdata.send(iL['AJAXURL'], 'do=pmremove&id=' + urlencode(id) + '&s=' + iL['SESSION'] + '&token=' + iL['TOKEN']);
	return true;
}
function do_show_actions_popup(pid)
{
	if (actions_popup_timer != null)
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}
	jQuery('#actions_popup_' + pid).removeClass('hide');
}
function show_actions_popup(pid)
{
	actions_popup_timer = setTimeout("do_show_actions_popup('" + pid + "');", 750);
}
function show_actions_popup_links(pid)
{
	if (actions_popup_timer != null)
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}
	jQuery('#actions_popup_' + pid).removeClass('hide');
}
function hide_actions_popup(pid)
{
	if (actions_popup_timer != null)
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}
	actions_popup_timer = setTimeout("do_hide_actions_popup('" + pid + "');", 750);
}
function do_hide_actions_popup(pid)
{
	jQuery('#actions_popup_' + pid).addClass('hide');
}
function close_popup_window()
{
        window.close();
        if (window.opener && !window.opener.closed)
        {
                window.opener.location.reload();
        }
}
function toggle_id(idobjname)
{
        obj = fetch_js_object(idobjname);
        if (obj)
        {
                if (obj.style.display == "none")
                {
                        obj.style.display = "";
                }
                else
                {
                        obj.style.display = "none";
                }
        }
        return false;
}
function show_prompt_payment_buyer(urlbit)
{
	var prompttext = sheel_prompt('<div class="pb-3 bold">' + phrase['_how_exactly_did_you_pay_the_seller_for_this_item'] + '</div><div class="pb-4"> ' + phrase['_be_specific_example_paypal_visa_wire_etc'] + '</div>');
	var newurl = '';
	if (prompttext != null && prompttext != false && prompttext != '')
	{
		newurl = urlbit + "&winnermarkedaspaidmethod=" + prompttext;
		var xyz = '';
		xyz = confirm_js(phrase['_you_are_about_to_inform_the_seller_that_payment_for_this_item_has_been_paid_in_full']);
		if (xyz)
		{
			document.location = newurl;
		}
		else
		{
			return false;
		}
	}
	else
	{
		if (prompttext == null || prompttext == false)
		{
			alert_js(phrase['_please_describe_how_you_paid_the_seller_for_this_item']);
		}
	}
}
function check_uncheck_all(formid)
{
	if (cbchecked == false)
	{
		cbchecked = true
	}
	else
	{
		cbchecked = false
	}
	for (var i = 0; i < fetch_js_object(formid).elements.length; i++)
	{
		if (fetch_js_object(formid).elements[i].disabled == false)
		{
			fetch_js_object(formid).elements[i].checked = cbchecked;
		}
	}
}
function check_uncheck_all_id(checkbox_name, class_name)
{
	if (cbchecked2 == false)
	{
		cbchecked2 = true;
	}
	else
	{
		cbchecked2 = false;
	}
	elements = document.getElementsByName(checkbox_name);
	for (var i = 0; i < elements.length; i++)
	{
		if (elements[i].getAttribute('class') == class_name + '_checkbox')
		{
			if (elements[i].disabled == false)
			{
				elements[i].checked = cbchecked2;
			}
		}
	}
}
function reset_image()
{
	imgtag.src = favoriteicon;
}
function print_recently_viewed_items(type, columns, pageurl)
{
	xmldata = new AJAX_Handler(true);
	type = urlencode(type);
	columns = parseInt(columns);
	xmldata.type = type;
	xmldata.columns = columns;
	if (xmldata.columns == 1)
	{
		xmldata.btnNext = '#c7r';
		xmldata.btnPrev = '#c7l';
		xmldata.visible = 1;
		xmldata.scroll = 1;
		xmldata.divid = 'recentviewedmidloader';
	}
	else if (xmldata.columns == 3)
	{
		xmldata.btnNext = '#c6r';
		xmldata.btnPrev = '#c6l';
		xmldata.visible = 3;
		xmldata.scroll = 3;
		xmldata.divid = 'recentviewedtoploader';
	}
	xmldata.onreadystatechange(function () {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				fetch_js_object(xmldata.divid).innerHTML = xmldata.handler.responseText;
				jQuery('.carousel_recentviewed_' + xmldata.columns + 'col').jCarouselLite({
					btnNext:xmldata.btnNext,
					btnPrev:xmldata.btnPrev,
					easing:'easeOutQuad',
					visible:xmldata.visible, //jQuery(".carousel_recentviewed_' + xmldata.columns + 'col ul li").length,
					scroll:xmldata.scroll,
					speed:100,
					circular: false,
					autoWidth: true,
					responsive: true
				});
				if (xmldata.columns == 3) {
					if (jQuery(".carousel_recentviewed_3col ul li").length < 4) {
						jQuery("#c6r").addClass('disabled');
					}
				}
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=recentlyvieweditems&type=' + type + '&columns=' + columns + '&token=' + iL['TOKEN'] + '&returnurl=' + urlencode(pageurl));
}
function print_favourite_items(limit)
{
	xmldata = new AJAX_Handler(true);
	limit = parseInt(limit);
	xmldata.onreadystatechange(function() {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				var result = JSON.parse(xmldata.handler.responseText);
				fetch_js_object('favouriteauctionevents').innerHTML = result.favouriteauctionevents;
				fetch_js_object('favouriteitems').innerHTML = result.favouriteitems;
				fetch_js_object('favouritesellers').innerHTML = result.favouritesellers;
				fetch_js_object('favouritesearches').innerHTML = result.favouritesearches;
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=favourites&limit=' + limit + '&s=' + iL['SESSION'] + '&token=' + iL['TOKEN']);
}
function print_request_details(rid,dview)
{
	xmldata = new AJAX_Handler(true);
	rid = parseInt(rid);
	xmldata.onreadystatechange(function() {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				var result = JSON.parse(xmldata.handler.responseText);
				fetch_js_object('specificrequest').innerHTML = result.requestdetails;
				
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=requestdetails&rid=' + rid + '&dview=' + dview + '&s=' + iL['SESSION'] + '&token=' + iL['TOKEN']);
}
function print_request_details_div()
{
	xmldata = new AJAX_Handler(true);
	rid = parseInt(rid);
	xmldata.onreadystatechange(function() {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				var result = JSON.parse(xmldata.handler.responseText);
				fetch_js_object('specificrequest').innerHTML = result.emptyrequestdetails;
				
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=requestdetails_orig&s=' + iL['SESSION'] + '&token=' + iL['TOKEN']);
}
function change_modal_width_height(id)
{
	var width = jQuery(window).width();
	var height = jQuery(window).height();
	jQuery('#' + id).css('width', width - 60);
	jQuery('#' + id).css('height', height - 60);
}
function move_from_merge_to(divfrom, divto)
{
        var fromdiv = fetch_js_object(divfrom).innerHTML;
	var todiv = fetch_js_object(divto).innerHTML;
	mergediv = fromdiv + todiv;
	fetch_js_object(divto).innerHTML = mergediv;
	fetch_js_object(divfrom).innerHTML = '';
	window.frames['category_iframe'].location.reload(true); // works only on same domain!
}
function isNumeric(n)
{
	return !isNaN(parseFloat(n)) && isFinite(n);
}
function validate_ipn_email(f)
{
	haveerrors = 0;
	(f.payer_email.value.length < 1) ? showImageInline("payer_email", true, false) : showImageInline("payer_email", false, false);
	if (haveerrors)
	{
		jQuery( "#submitbutton" ).effect( "shake", {times: 2, distance: 15}, 550);
	}
	return (!haveerrors);
}
function validate_card_auth(f)
{
	haveerrors = 0;
	(f.amount1.value.length < 1) ? showImageInline("amount1", true, false) : showImageInline("amount1", false, false);
	(f.amount2.value.length < 1) ? showImageInline("amount2", true, false) : showImageInline("amount2", false, false);
	if (haveerrors)
	{
		jQuery( "#submitbutton" ).effect( "shake", {times: 2, distance: 15}, 550);
	}
	return (!haveerrors);
}
function validate_card(f)
{
	haveerrors = 0;
	var valid = Payment.fns.validateCardNumber(jQuery('.cc-number').val());
	if (!valid)
	{
		jQuery('.cc-number').addClass('invalid');
		haveerrors = 1;
	}
	var valid = Payment.fns.validateCardExpiry(Payment.fns.cardExpiryVal(jQuery('.cc-exp').val()));
	if (!valid)
	{
		jQuery('.cc-exp').addClass('invalid');
		haveerrors = 1;
	}
	var cardType = Payment.fns.cardType(jQuery('.cc-number').val());
	jQuery('.cc-cardtype').val(cardType);
	var valid = Payment.fns.validateCardCVC(jQuery('.cc-cvc').val(), cardType);
	if (!valid)
	{
		jQuery('.cc-cvc').addClass('invalid');
		haveerrors = 1;
	}
	var valid = jQuery('.cc-name').val();
	if (valid == '')
	{
		jQuery('.cc-name').addClass('invalid');
		haveerrors = 1;
	}
	if (jQuery('#bpid').length)
	{
		var valid = jQuery('#bpid').val();
		if (valid == '' || valid == 0 || valid <= 0)
		{
			jQuery('#bpid_wrapper').addClass('redborder');
			haveerrors = 1;
		}
	}
	if (haveerrors)
	{
		if (jQuery('#refreshloading').length)
		{
			jQuery('#refreshloading').addClass('hide');
		}
		if (jQuery('#submitbutton').length)
		{
			fetch_js_object('submitbutton').disabled = false;
		}
		if (jQuery('#cardsubmit').length)
		{
			fetch_js_object('cardsubmit').disabled = false;
		}
	}
	return (!haveerrors);
}
function validate_membership_cancellation(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImageInline("password", true, false) : showImageInline("password", false, false);
        (f.comment.value.length < 1) ? showInlineBorder("comment", true) : showInlineBorder("comment", false);
	if (haveerrors) {
		jQuery("#submit").effect("shake", {times: 2, distance: 9}, 550);
	}
        return (!haveerrors);
}
function validate_membership_upgrade(f, phrase)
{
        var radio_choice = false;
        if (f.subscriptionid.length != undefined) {
                for (counter = 0; counter < f.subscriptionid.length; counter++) {
                        if (f.subscriptionid[counter].checked) {
                                radio_choice = true;
                        }
                }
        }
        else {
                radio_choice = true;
        }
        if (!radio_choice) {
                alert_js(phrase)
                grayscale[0].style.filter = "";
                return (false);
        }
        return (true);
}
function print_permissions(id, gid)
{
	jQuery.ajax({
		type: 'POST',
		url: iL['AJAXURL'],
		data: 'do=viewpermission&id=' + id + '&gid=' + gid,
		success: function(msg) {
			if (msg != '') {
				data = msg.split("|");
				if (data[0] != '' && data[2] != '') {
					fetch_js_object('permissiontitle').innerHTML = data[0];
					fetch_js_object('permissiondescription').innerHTML = data[1];
					fetch_js_object('permissionitems').innerHTML = data[2];
				}
			}
		}
	});
}
function validateprofileverification(f)
{
	haveerrors = 0;
	(f.contactname.value.length < 1) ? showImageInline("contactname", true, false) : showImageInline("contactname", false, false);
	(f.contactnumber.value.length < 1) ? showImageInline("contactnumber", true, false) : showImageInline("contactnumber", false, false);
	(f.contactnotes.value.length < 1) ? showInlineBorder("contactnotes", true) : showInlineBorder("contactnotes", false);
	return (!haveerrors);
}
function close_full_dialog()
{
	jQuery('#' + current_container).animate({'width' : '-=' + current_container_w + 'px'});
	jQuery('body').css('overflow', 'visible');
	if ((checkiphone || checkblackberry)) {
		jQuery('body').css('position','relative');
	}
	jQuery('#side_panel').attr('class', 'hide');
	setTimeout("jQuery('#" + current_container + "').hide()", 390);
	setTimeout("jQuery('#" + current_container_panel + "').hide()", 550);
	setTimeout("jQuery('#" + current_container_iframe + "').attr('src', 'about:blank');", 750);
	setTimeout("jQuery('#" + current_container + "').css('width', '0px');", 850);
}
function open_full_dialog(url, panelid, iframeid, containerid)
{
	w = 830;
	if (jQuery(window).width() < w) {
		w = jQuery(window).width() - 40;
	}
	current_container_panel = panelid;
	current_container_iframe = iframeid;
	current_container = containerid;
	current_container_w = w;
	jQuery('body').css('overflow', 'hidden');
	if ((checkiphone || checkblackberry)) {
		jQuery('body').css('position','fixed');
	}
	jQuery('#' + panelid).show();
	jQuery('#' + panelid).attr('class', 'show');
	jQuery('#' + containerid).show();
	jQuery('#' + containerid).css('width', '0px');
	if (w > 830) {
		w = 830;
	}
	jQuery('#' + containerid).animate({'width' : '+=' + w + 'px'});
	setTimeout("jQuery('#" + iframeid + "').attr('src', '" + url + "');", 750);
}
function validateCB(theName)
{
	var counter = 0;
	var cb = document.getElementsByName(theName)
	for (i = 0; i < cb.length; i++) {
		if ((cb[i].tagName == 'INPUT') && (cb[i].type == 'checkbox')) {
			if (cb[i].checked)
			counter++;
		}
	}
	if (counter == 0) {
		return false;
	}
	return true;
}
function smoothscroll(id)
{
	jQuery('html,body').animate({scrollTop: jQuery("#" + id).offset().top}, 'slow');
}
function validate_payment_profile(formobj, escrow, directpayment, gateway, ia)
{
	var total = '';
	var total2 = '';
	var total3 = '';
	var total4 = '';
	var e = 0;
	if (fetch_js_object('enableescrow2') && fetch_js_object('enableescrow2').checked == true) {
		if (!validateCB('paymethod[]')) {
			jQuery.growl.error({ size: 'large', duration: 5200, title: phrase['_error'], message: phrase['_you_have_selected_that_you_will_do_business_outside_the_marketplace'] });
			jQuery( "#submit" ).effect( "shake", {times: 2, distance: 15}, 550);
			return(false);
		}
		total = '1';
	}
	if (fetch_js_object('enableescrow3') && fetch_js_object('enableescrow3').checked == true)
	{
		formobjx = formobj.elements;
		if (typeof(formobjx) != 'undefined') {
			for (var c = 0, i = formobjx.length - 1; i > -1; --i) {
				if (formobjx[i].name && /^paymethodoptions\[\w+\]$/.test(formobjx[i].name) && formobjx[i].checked) {
					++c;
					if (formobjx[i].name == 'paymethodoptions[platnosci]') {
						e++;
					}
				}
			}
			if (c < 1) {
				//alert_js(phrase['_you_have_selected_that_you_will_offer_buyers_a_direct_method_of_payment_ipn']);
				jQuery.growl.error({ size: 'large', duration: 5200, title: phrase['_error'], message: phrase['_you_have_selected_that_you_will_offer_buyers_a_direct_method_of_payment_ipn'] });
				jQuery( "#submit" ).effect( "shake", {times: 2, distance: 15}, 550);
				return(false);
			}
		}
		total2 = '1';
	}
	if (escrow && fetch_js_object('enableescrow1').checked == true)
	{
		total3 = '1';
	}
	if (directpayment && fetch_js_object('enableescrow4').checked == true)
	{ // gateway == 'paypal_pro' && ia == 1
		formobjy = formobj.elements;
		if (typeof(formobjy) != 'undefined') {
			for (var c = 0, i = formobjy.length - 1; i > -1; --i) {
				if (formobjy[i].name && /^paymethodcc\[\w+\]$/.test(formobjy[i].name) && formobjy[i].checked) {
					++c;
				}
			}
			if (c < 1) {
				jQuery.growl.error({ size: 'large', duration: 5200, title: 'Oops..', message: phrase['_you_have_selected_that_you_will_offer_buyers_a_direct_method_of_payment'] });
				jQuery( "#submit" ).effect( "shake", {times: 2, distance: 15}, 550);
				return(false);
			}
		}
		total4 = '1';
	}
	if (total == '' && total2 == '' && total3 == '' && total4 == '')
	{
		jQuery.growl.error({ size: 'large', duration: 5200, title: 'Oops..', message: phrase['_in_order_to_sell_your_items_sucessfully_buyers_will_need_to_know_how'] });
		jQuery( "#submit" ).effect( "shake", {times: 2, distance: 15}, 550);
		return(false);
	}
        return(true);
}
function fetch_div_height(id)
{
	var height = fetch_js_object(id).offsetHeight;
	return height;
}
function fetch_div_width(id)
{
	var width = fetch_js_object(id).offsetWidth;
	return width;
}
function addslashes(str)
{
	return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}
function sidenav_open(id)
{
	jQuery('.' + id).addClass('open');
	jQuery('.' + id).addClass('opentb');
	jQuery('.' + id).addClass('w0plr0');
	jQuery('body').css("overflow", "hidden");
	setTimeout("jQuery('." + id + "').removeClass('w0plr0')", 420);
	setTimeout("jQuery('." + id + "').addClass('open')", 800);
	setTimeout("jQuery('." + id + "').removeClass('opentb')", 400);
	return false;
}
function sidenav_close(id)
{
	jQuery('.' + id).addClass('w0plr0');
	jQuery('.' + id).addClass('opentb');
	jQuery('.' + id).removeClass('open')
	jQuery('body').css("overflow", "");
	setTimeout("jQuery('." + id + "').removeClass('w0plr0')", 800);
	setTimeout("jQuery('." + id + "').removeClass('opentb')", 800);
	return false;
}
function sidenav_listener(id)
{
	if (jQuery('.closep').length)
	{
		jQuery('.closep').click(function () {
			return sidenav_close(id);
		});
	}
	if (jQuery('.optionsp').length)
	{
		jQuery('.optionsp').click(function () {
			return sidenav_open(id);
		});
	}
}
function calculate_insertionfees(cid, defaultcurrencyid, newlisting)
{
	if (newlisting == '0')
	{
		return false;
	}
	if (jQuery('#cid').length)
	{
		if (fetch_js_object('cid').value != '')
		{
			cid = fetch_js_object('cid').value;
		}
	}
	var price = 0;
	startprice = 0;
	if (jQuery('#startprice').length)
	{
		startprice = fetch_js_object('startprice').value;
	}
	reserve_price = 0;
	if (jQuery('#fee9').length)
	{
		reserve_price = fetch_js_object('fee9').value;
	}
	buynow_price = '';
	if (jQuery('#fee7').length)
	{
		buynow_price = fetch_js_object('fee7').value;
	}
	buynow_price_fixed = '';
	if (jQuery('#fee8').length)
	{
		buynow_price_fixed = fetch_js_object('fee8').value;
	}
	classified_price = 0;
	if (jQuery('#fee10').length)
	{
		classified_price = fetch_js_object('fee10').value;
	}
	if (buynow_price == '' && buynow_price_fixed != '')
	{
		price = buynow_price_fixed;
	}
	else if (buynow_price != '' && buynow_price_fixed == '')
	{
		price = buynow_price;
	}
	else if (buynow_price != '' && buynow_price_fixed != '')
	{
		if (parseFloat(buynow_price) > parseFloat(buynow_price_fixed))
		{
			price = buynow_price;
		}
		else
		{
			price = buynow_price_fixed;
		}
	}
	if (jQuery('#currencyoptions').length)
	{
		currencyid = fetch_js_object('currencyoptions').options[fetch_js_object('currencyoptions').selectedIndex].value;
	}
	else
	{
		currencyid = defaultcurrencyid;
	}
	$.ajax({
		type: 'GET',
		url: iL['AJAXURL'],
		data: 'do=calculateinsertionfees&cid=' + urlencode(cid) + '&startprice=' + urlencode(startprice) + '&reserve_price=' + urlencode(reserve_price) + '&buynow_price=' + urlencode(price) + '&classified_price=' + urlencode(classified_price) + '&currencyid=' + urlencode(currencyid),
		success: function(msg) {
			if (msg != '') {
				data = msg.split("|");
				if (data[0] != '' || data[1] != '') {
					jQuery('#fee5').attr('fee', data[1]);
					livefeecalculator();
				}
			}
		}
	});
}
function selling_format(selected)
{
	if (selected == 'regular')
	{ // auction + potential fixed price
		fetch_js_object('filtered_auctiontype').value='regular';
		toggle_show('showeventtype');
		update_shipping_next_cost();
		jQuery('#btnauction').addClass('sel');
		jQuery('#btnfixed').removeClass('sel');
		jQuery('#btnclassifieds').removeClass('sel');
		toggle_show('formatauction');
		toggle_hide('formatfixedprice');
		toggle_hide('formatclassifieds');
		toggle_hide('pickupinfo');
		toggle_show('shippinginfo');
		jQuery('#ship_method option[value="flatrate"]').removeAttr('disabled');
		jQuery('#ship_method option[value="calculated"]').removeAttr('disabled');
		jQuery('#ship_method option[value="digital"]').removeAttr('disabled');
		jQuery('#public_title').removeClass('hide');
		jQuery('#public_title_fixed').addClass('hide');
		jQuery('#public_help').removeClass('hide');
		jQuery('#public_help_fixed').addClass('hide');
		jQuery('#realtime_title').removeClass('hide');
		jQuery('#realtime_title_fixed').addClass('hide');
		jQuery('#realtime_help').removeClass('hide');
		jQuery('#realtime_help_fixed').addClass('hide');
		jQuery('#durationverbose').removeClass('hide');
		jQuery('#durationverbose_fixed').addClass('hide');
		jQuery('#shipping-promo-selection').removeClass('hide');
		// disable GTC within duration pull down if applicable
		if (jQuery('#fee6').length)
		{ // duration pull down exists
			jQuery('#fee6').find("option[value*='GTC']").prop("disabled", true);
		}
	}
	else if (selected == 'fixed')
	{ // fixed price item
		fetch_js_object('filtered_auctiontype').value='fixed';
		toggle_show('showeventtype');
		update_shipping_next_cost();
		jQuery('#btnauction').removeClass('sel');
		jQuery('#btnfixed').addClass('sel');
		jQuery('#btnclassifieds').removeClass('sel');
		toggle_hide('formatauction');
		toggle_show('formatfixedprice');
		toggle_hide('formatclassifieds');
		toggle_hide('pickupinfo');
		toggle_show('shippinginfo');
		jQuery('#ship_method option[value="flatrate"]').removeAttr('disabled');
		jQuery('#ship_method option[value="calculated"]').removeAttr('disabled');
		jQuery('#ship_method option[value="digital"]').removeAttr('disabled');
		jQuery('#public_title').addClass('hide');
		jQuery('#public_title_fixed').removeClass('hide');
		jQuery('#public_help').addClass('hide');
		jQuery('#public_help_fixed').removeClass('hide');
		jQuery('#realtime_title').addClass('hide');
		jQuery('#realtime_title_fixed').removeClass('hide');
		jQuery('#realtime_help').addClass('hide');
		jQuery('#realtime_help_fixed').removeClass('hide');
		jQuery('#durationverbose').addClass('hide');
		jQuery('#durationverbose_fixed').removeClass('hide');
		jQuery('#shipping-promo-selection').removeClass('hide');
		// re-enable GTC within duration pull down if applicable
		if (jQuery('#fee6').length)
		{ // duration pull down exists
			jQuery('#fee6').find("option[value*='GTC']").prop("disabled", false);
		}
	}
	else if (selected == 'classified')
	{ // classified ad
		fetch_js_object('filtered_auctiontype').value='classified';
		toggle_hide('showeventtype');
		jQuery('#btnauction').removeClass('sel');
		jQuery('#btnfixed').removeClass('sel');
		jQuery('#btnclassifieds').addClass('sel');
		toggle_hide('formatauction');
		toggle_hide('formatfixedprice');
		toggle_show('formatclassifieds');
		toggle_hide('shippinginfo');
		toggle_show('pickupinfo');
		jQuery('#ship_method option[value="flatrate"]').attr('disabled','disabled');
		jQuery('#ship_method option[value="calculated"]').attr('disabled','disabled');
		jQuery('#ship_method option[value="localpickup"]').attr('selected','selected');
		jQuery('#ship_method option[value="digital"]').attr('disabled','disabled');
		toggle_hide('showshipping');
		toggle_hide('handlingfeerow');
		toggle_hide('digitalfile');
		jQuery('#durationverbose').addClass('hide');
		jQuery('#durationverbose_fixed').addClass('hide');
		jQuery('#shipping-promo-selection').addClass('hide');
		// re-enable GTC within duration pull down if applicable
		if (jQuery('#fee6').length)
		{ // duration pull down exists
			jQuery('#fee6').find("option[value*='GTC']").prop("disabled", true);
		}
	}
	livefeecalculator();
}
function duration_onchange(id)
{
	if (jQuery('#' + id).val() == 'GTC') { // hide option to relist for x days
		if (jQuery('#fee3').is(':checked')) {
			jQuery('#fee3').removeAttr('checked');
			alert_js(phrase['_we_removed_auto_relist_from_cart_gtc_enabled']);
		}
		jQuery('#enhancements_tr_autorelist').hide();
	}
	else { // show option to relist for x days
		jQuery('#enhancements_tr_autorelist').show();
	}
}
function livefeecalculator()
{
	var sum = 0;
	var fee = 0;
	var service, elem, type;
	var verbose = '';
	for (i = 0; i < 20; i++) // run up to 20 fees max
	{
		service = 'fee' + i;
		if (jQuery('#' + service).is(':disabled')) {}
		else {
			elem = document.getElementById(service);
			if (jQuery('#' + service).filter('select').length > 0) {
				type = 'select';
			}
			else {
				type = jQuery('#' + service).attr('type'); // text, checkbox, radio, hidden
			}
			if (type == 'checkbox') {
				if (jQuery('#' + service).attr('feetitle').length > 0 && parseFloat(jQuery('#' + service).attr('fee')) > 0 && parseFloat(jQuery('#' + service).attr('invoiced')) === 0) {
					if (elem.checked == true) {
						fee = parseFloat(jQuery('#' + service).attr('fee'));
						sum += fee;
						verbose += '<div class="pb-6"><span class="' + ((iL['LTR'] == '1') ? 'right' : 'left') + ' mlr-6">' + iL['CURRENCYSYMBOL'] + jQuery('#' + service).attr('fee') + iL['CURRENCYSYMBOLRIGHT'] + '</span>' + jQuery('#' + service).attr('feetitle') + '</div>';
						if (service === 'fee0')
						{
							verbose += '<input type="hidden" name="fees[bold]" value="' + jQuery('#' + service).attr('fee') + '">';
						}
						else if (service === 'fee1')
						{
							verbose += '<input type="hidden" name="fees[highlite]" value="' + jQuery('#' + service).attr('fee') + '">';
						}
						else if (service === 'fee2')
						{
							verbose += '<input type="hidden" name="fees[featured]" value="' + jQuery('#' + service).attr('fee') + '">';
						}
						else if (service === 'fee4')
						{
							verbose += '<input type="hidden" name="fees[featured_searchresults]" value="' + jQuery('#' + service).attr('fee') + '">';
						}
					}
				}
			}
			else if (type == 'text' || type == 'hidden') {
				if (type == 'text') { // text field
					if (jQuery('#' + service).attr('feetitle').length > 0 && parseFloat(jQuery('#' + service).attr('fee')) > 0 && parseFloat(jQuery('#' + service).attr('invoiced')) === 0) {
						if (elem.value != '') {
							fee = parseFloat(jQuery('#' + service).attr('fee'));
							sum += fee;
							verbose += '<div class="pb-6"><span class="' + ((iL['LTR'] == '1') ? 'right' : 'left') + ' mlr-6">' + iL['CURRENCYSYMBOL'] + jQuery('#' + service).attr('fee') + iL['CURRENCYSYMBOLRIGHT'] + '</span>' + jQuery('#' + service).attr('feetitle') + '</div>';
						}
					}
				}
				else { // hidden input
					if (jQuery('#' + service).attr('feetitle').length > 0 && parseFloat(jQuery('#' + service).attr('fee')) > 0 && parseFloat(jQuery('#' + service).attr('invoiced')) === 0) {
						fee = parseFloat(jQuery('#' + service).attr('fee'));
						sum += fee;
						verbose += '<div class="pb-6"><span class="' + ((iL['LTR'] == '1') ? 'right' : 'left') + ' mlr-6">' + iL['CURRENCYSYMBOL'] + jQuery('#' + service).attr('fee') + iL['CURRENCYSYMBOLRIGHT'] + '</span>' + jQuery('#' + service).attr('feetitle') + '</div>';
					}
				}
			}
			else if (type == 'select') {
				if (jQuery('#' + service + ' option:selected').attr('feetitle').length > 0 && parseFloat(jQuery('#' + service + ' option:selected').attr('fee')) > 0 && parseFloat(jQuery('#' + service + ' option:selected').attr('invoiced')) === 0) {
					fee = parseFloat(jQuery('#' + service + ' option:selected').attr('fee'));
					sum += fee;
					verbose += '<div class="pb-6"><span class="' + ((iL['LTR'] == '1') ? 'right' : 'left') + ' mlr-6">' + iL['CURRENCYSYMBOL'] + jQuery('#' + service + ' option:selected').attr('fee') + iL['CURRENCYSYMBOLRIGHT'] + '</span>' + jQuery('#' + service + ' option:selected').attr('feetitle') + '</div>';
					if (service === 'fee6')
					{ // add duration hidden field if we're selling in bulk
						verbose += '<input type="hidden" name="fees[duration]" value="' + jQuery('#' + service + ' option:selected').attr('fee') + '">';
					}
				}
			}
		}
	}
	var n = sum.toFixed(2);
	if (jQuery('#total').length > 0)
	{
		fetch_js_object('total').value = n;
	}
	if (jQuery('#feetotal').length > 0)
	{
		fetch_js_object('feetotal').innerHTML = n;
	}
	if (jQuery('#otherfees').length > 0)
	{
		fetch_js_object('otherfees').innerHTML = verbose;
	}
}
function acp_cb_running_total(item)
{
	if (item.checked)
	{
           	runningtotal += parseFloat(item.value);
        }
	else
	{
           	runningtotal -= parseFloat(item.value);
        }
	n = runningtotal.toFixed(2);
        jQuery('.acp-input#refundamount').val(n);
}
function show_cat_search_results()
{
	if (fetch_js_object('ckw').value.length > 2) {
		var varname = fetch_js_object('ckw').value;
	}
	else {
		var varname = 'x';
	}
	jQuery.ajax({
		type: 'GET',
		url: iL['AJAXURL'],
		data: 'do=search_category_keyword&var=' + urlencode(varname),
		success: function(msg) {
			if (msg != '') {
				toggle_show('cresults');
			}
			else {
				toggle_hide('cresults');
			}
			fetch_js_object('cresults').innerHTML = msg;
		}
	});
}
function show_brand_search_results()
{
	if (fetch_js_object('ckw').value.length > 2) {
		var varname = fetch_js_object('ckw').value;
	}
	else {
		var varname = 'x';
	}
	jQuery.ajax({
		type: 'GET',
		url: iL['AJAXURL'],
		data: 'do=search_brand_keyword&var=' + urlencode(varname),
		success: function(msg) {
			if (msg != '') {
				toggle_show('cresults');
			}
			else {
				toggle_hide('cresults');
			}
			fetch_js_object('cresults').innerHTML = msg;
		}
	});
}
function abbrNum(number, decPlaces)
{
	// 2 decimal places => 100, 3 => 1000, etc
	decPlaces = Math.pow(10,decPlaces);
	// Enumerate number abbreviations
	var abbrev = [ "k", "m", "b", "t" ];
	// Go through the array backwards, so we do the largest first
	for (var i=abbrev.length-1; i>=0; i--) {
		// Convert array index to "1000", "1000000", etc
		var size = Math.pow(10,(i+1)*3);
		// If the number is bigger or equal do the abbreviation
		if (size <= number) {
			// Here, we multiply by decPlaces, round, and then divide by decPlaces.
			// This gives us nice rounding to a particular decimal place.
			number = Math.round(number*decPlaces/size)/decPlaces;
			// Handle special case where we round up to the next abbreviation
			if ((number == 1000) && (i < abbrev.length - 1)) {
				number = 1;
				i++;
			}
			// Add the letter for the abbreviation
			number += abbrev[i];
			break;
		}
	}
	return number;
}
function customImage(imagename, imageurl, errors)
{
        document[imagename].src = imageurl;
        if (!haveerrors && errors) {
                haveerrors = errors;
                alert_js(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
        }
}
function change_pickup_location(orderid)
{
	if (jQuery('#localpickuplocation').length) {
		jQuery('#localpickuplocation').toggleClass('open');
		if (jQuery('#localpickuplocation').hasClass('open')) {
			jQuery('#pickuplocationorderid').html(orderid);
			jQuery('#locationorderid').val(orderid);
			jQuery('#pickuplocationaddress').val('');
		}
		else {
			jQuery('#pickuplocationorderid').html('');
			jQuery('#locationorderid').val('');
			jQuery('#pickuplocationaddress').val('');
		}
	}
}
function validate_all_pickuplocation_fields()
{
	if (fetch_js_object('pickuplocationaddress').value == '') {
		alert_js(phrase['_please_enter_a_pickup_location_address']);
		return false;
	}
	return true;
}
function submit_pickup_location()
{
	validate = validate_all_pickuplocation_fields();
	if (validate == true) {
		xmldatasubmit = new AJAX_Handler(true);
		address = fetch_js_object('pickuplocationaddress').value;
		orderid = fetch_js_object('locationorderid').value;
		month = fetch_js_object('plmonth').value;
		day = fetch_js_object('plday').value;
		time = fetch_js_object('pltime').value;
		xmldatasubmit.address = address;
		xmldatasubmit.orderid = orderid;
		xmldatasubmit.month = month;
		xmldatasubmit.day = day;
		xmldatasubmit.time = time;
		fetch_js_object('submitpickuplocation').disabled = true;
		fetch_js_object('plmonth').disabled = true;
		fetch_js_object('plday').disabled = true;
		fetch_js_object('pltime').disabled = true;
		xmldatasubmit.onreadystatechange(function() {
			fetch_js_object('pickuplocationaddress').disabled = true;
			if (xmldatasubmit.handler.readyState == 4 && xmldatasubmit.handler.status == 200) {
				fetch_js_object('pickuplocationaddress').disabled = false;
				fetch_js_object('submitpickuplocation').disabled = false;
				fetch_js_object('pickuplocationaddress').value = '';
				fetch_js_object('plmonth').disabled = false;
				fetch_js_object('plday').disabled = false;
				fetch_js_object('pltime').disabled = false;
				if (xmldatasubmit.handler.responseText != '') {
					fetch_js_object('itempickuplocationtext_' + xmldatasubmit.orderid).innerHTML = xmldatasubmit.handler.responseText;
					jQuery('#localpickuplocation').removeClass('open');
				}
				xmldatasubmit.handler.abort();
			}
		});
		xmldatasubmit.send(iL['AJAXURL'], 'do=submitpl&message=' + urlencode(address) + '&orderid=' + urlencode(orderid) + '&month=' + urlencode(month) + '&day=' + urlencode(day) + '&time=' + urlencode(time) + '&token=' + iL['TOKEN']);
		return true;
	}
	return false;
}
function mark_shipment_fulfilled(orderid, shippertitle)
{
	if (jQuery('#markfulfillment').length) {
		jQuery('#markfulfillment').toggleClass('open');
		if (jQuery('#markfulfillment').hasClass('open')) {
			jQuery('#markfulfillmentorderid').html(orderid);
			jQuery('#buyerchoseshipper').html(shippertitle);
			jQuery('#fulfillmentorderid').val(orderid);
			jQuery('#trackingnumber').val('');
		}
		else {
			jQuery('#markfulfillmentorderid').html('');
			jQuery('#buyerchoseshipper').html('');
			jQuery('#fulfillmentorderid').val('');
			jQuery('#trackingnumber').val('');
		}
	}
}
function submit_shipment_fulfillment()
{
	xmldatasubmit = new AJAX_Handler(true);
	trackingnumber = fetch_js_object('trackingnumber').value;
	orderid = fetch_js_object('fulfillmentorderid').value;
	xmldatasubmit.trackingnumber = trackingnumber;
	xmldatasubmit.orderid = orderid;
	xmldatasubmit.shippertitle = jQuery('#buyerchoseshipper').html();
	fetch_js_object('submitshippingfulfillment').disabled = true;
	xmldatasubmit.onreadystatechange(function() {
		fetch_js_object('trackingnumber').disabled = true;
		if (xmldatasubmit.handler.readyState == 4 && xmldatasubmit.handler.status == 200) {
			fetch_js_object('trackingnumber').disabled = false;
			fetch_js_object('submitshippingfulfillment').disabled = false;
			fetch_js_object('trackingnumber').value = '';
			if (xmldatasubmit.handler.responseText != '') {
				response = xmldatasubmit.handler.responseText.split('|');
				if (response[0] == 'success') {
					jQuery('#shipping-message-' + xmldatasubmit.orderid).html(response[1]);
					//jQuery('#tracking-message-' + xmldatasubmit.orderid).html(response[2]);
					if (jQuery('#track-shipment-' + xmldatasubmit.orderid).hasClass('hide')) {
						jQuery('#shipping-status-' + xmldatasubmit.orderid).addClass('minh-100--ni');
						jQuery('#mark-fulfilled-' + xmldatasubmit.orderid).addClass('hide');
						jQuery('#track-shipment-' + xmldatasubmit.orderid).removeClass('hide');
						jQuery('#update-tracking-' + xmldatasubmit.orderid).removeClass('hide');
						if (xmldatasubmit.trackingnumber != '') {
							jQuery('#track-shipment-url-' + xmldatasubmit.orderid).prop('href', jQuery('#track-shipment-url-' + xmldatasubmit.orderid).attr('href') + xmldatasubmit.trackingnumber);
						}
						else {
							jQuery('#track-shipment-url-' + xmldatasubmit.orderid).addClass('disabled');
							jQuery('#track-shipment-url-' + xmldatasubmit.orderid).prop('href', 'javascript:;');
						}
						notice_js(phrase['_success'], response[1]);
					}
					else
					{ // updating tracking number
						if (response[3] != '') {
							jQuery('#track-shipment-url-' + xmldatasubmit.orderid).removeClass('disabled');
							jQuery('#track-shipment-url-' + xmldatasubmit.orderid).prop('href', response[3]);
							notice_js(phrase['_success'], phrase['_ship_tracking_number_updated_order'] + xmldatasubmit.orderid);
						}
					}
					jQuery('#buyerchoseshipper').html('');
					jQuery('#markfulfillment').removeClass('open');
				}
				else {
					alert_js(response[1]);
				}
			}
			xmldatasubmit.handler.abort();
		}
	});
	xmldatasubmit.send(iL['AJAXURL'], 'do=submitsf&message=' + urlencode(trackingnumber) + '&orderid=' + urlencode(orderid) + '&service=' + urlencode(xmldatasubmit.shippertitle) + '&token=' + iL['TOKEN']);
	return true;
}
function add_to_cart(pid, sku, qty, returnurl, title)
{
	xhr = new AJAX_Handler(true);
	xhr.pid = pid;
	xhr.title = title;
	xhr.sku = sku;
	xhr.qty = qty;
	xhr.returnurl = returnurl;
	xhr.onreadystatechange(function() {
		if (jQuery('#actionbutton_' + xhr.pid).length) {
			jQuery('#actionbutton_' + xhr.pid).html('<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif">');
		}
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200) {
			if (xhr.handler.responseText != '') {
				response = xhr.handler.responseText.split("|");
				if (jQuery('#cart-count').length && jQuery('#cart-count').length) {
					jQuery('#cart-count').html(response[1]);
					if (jQuery('#cart-checkout-button').length && jQuery('#cart-checkout-button').length) {
						if ((response[1]*1) <= 0)
						{
							jQuery('#cart-checkout-button').addClass('hide');
						}
						else
						{
							jQuery('#cart-checkout-button').removeClass('hide');
						}
					}
					print_shopping_cart(10, 'shopping-cart');
				}
				if (response[0] == 'success') {
					if (jQuery('#actionbutton_' + xhr.pid).length) {
						jQuery('#actionbutton_' + xhr.pid).html(phrase['_added'] + '!');
						setTimeout("jQuery('#actionbutton_" + xhr.pid + "').html(phrase['_add_to_cart'])", 1500);
					}
					jQuery.growl.notice({ title: phrase['_success'], message: xhr.title + ' (#' + xhr.pid + ') ' + phrase['_added_to_shopping_cart_lc'] + '.' });
				}
				else if (response[0] == 'successexceeded') {
					if (jQuery('#actionbutton_' + xhr.pid).length) {
						jQuery('#actionbutton_' + xhr.pid).html(phrase['_added'] + '!');
						setTimeout("jQuery('#actionbutton_" + xhr.pid + "').html(phrase['_add_to_cart'])", 1500);
					}
					jQuery.growl.warning({ title: phrase['_quantity_exceeded'], message: phrase['_quantity_exceeded_for'] + ' ' + xhr.title + ' (#' + xhr.pid + ').' });
				}
				else if (response[0] == 'successsaved') {
					if (jQuery('#actionbutton_' + xhr.pid).length) {
						jQuery('#actionbutton_' + xhr.pid).html(phrase['_saved_for_later'] + '!');
						setTimeout("jQuery('#actionbutton_" + xhr.pid + "').html(phrase['_add_to_cart'])", 1500);
					}
					jQuery.growl.warning({ title: phrase['_success'], message: xhr.title + ' (#' + xhr.pid + ') ' + phrase['_added_to_cart_saved_for_later_lc'] + '.' });
				}
				else {
					if (jQuery('#actionbutton_' + xhr.pid).length) {
						jQuery('#actionbutton_' + xhr.pid).html(phrase['_cannot_add_to_cart']);
						setTimeout("jQuery('#actionbutton_" + xhr.pid + "').html(phrase['_add_to_cart'])", 2000);
					}
					jQuery.growl.error({ title: phrase['_failed'], message: xhr.title + ' (#' + xhr.pid + ') ' + phrase['_could_not_be_added_to_cart_lc'] + '.' });
				}
			}
			xhr.handler.abort();
		}
	});
	xhr.send(iL['AJAXURL'], 'do=atc&pid=' + urlencode(pid) + '&sku=' + urlencode(sku) + '&qty=' + urlencode(qty) + '&returnurl=' + urlencode(returnurl) + '&token=' + iL['TOKEN']);
	return true;
}
function delete_cart(cartid)
{
	xhrd = new AJAX_Handler(true);
	xhrd.cartid = cartid;
	xhrd.onreadystatechange(function() {
		if (jQuery('#cartid-' + xhrd.cartid).length) {
			jQuery('#cartid-' + xhrd.cartid).addClass('fade-40');
		}
		if (xhrd.handler.readyState == 4 && xhrd.handler.status == 200) {
			if (xhrd.handler.responseText != '') {
				response = xhrd.handler.responseText.split("|");
				if (jQuery('#cart-count').length && jQuery('#cart-count').length) {
					jQuery('#cart-count').html(response[1]);
					jQuery('#cart-count-verbose').html(response[1]);
					if (jQuery('#cart-checkout-button').length && jQuery('#cart-checkout-button').length) {
						if ((response[1]*1) <= 0)
						{
							jQuery('#cart-checkout-button').addClass('hide');
						}
						else
						{
							jQuery('#cart-checkout-button').removeClass('hide');
						}
					}
					print_shopping_cart(10, 'shopping-cart');
				}
				if (response[0] == 'success') {
					if (jQuery('#cartid-' + xhrd.pid).length) {
						setTimeout("jQuery('#cartid-" + xhrd.pid + "').remove()", 1500);
					}
				}
				else {
					jQuery('#cartid-' + xhrd.cartid).removeClass('fade-40');
				}
			}
			xhrd.handler.abort();
		}
	});
	xhrd.send(iL['AJAXURL'], 'do=dfc&cartid=' + cartid + '&token=' + iL['TOKEN']);
	return true;
}
function print_shopping_cart(limit, divid) {
	xmlscdata = new AJAX_Handler(true);
	limit = parseInt(limit);
	divid = urlencode(divid);
	xmlscdata.limit = limit;
	xmlscdata.divid = divid;
	xmlscdata.onreadystatechange(function() {
		if (xmlscdata.handler.readyState == 4 && xmlscdata.handler.status == 200) {
			if (xmlscdata.handler.responseText != '') {
				fetch_js_object(xmlscdata.divid).innerHTML = xmlscdata.handler.responseText;
			}
			xmlscdata.handler.abort();
		}
	});
	xmlscdata.send(iL['AJAXURL'], 'do=shoppingcart&limit=' + limit + '&token=' + iL['TOKEN']);
}
function in_iframe()
{
	try {
		return window.self !== window.top;
	}
	catch (e) {
		return true;
	}
}
function print_search_result_moreinfo(itemid)
{
	xmldata = new AJAX_Handler(true);
	itemid = parseInt(itemid);
	xmldata.itemid = itemid;
	xmldata.onreadystatechange(function() {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				iteminfo = xmldata.handler.responseText;
				iteminfo = iteminfo.split("|");
				fetch_js_object('ibigphoto_' + xmldata.itemid).innerHTML = iteminfo[0];
				if (iteminfo[3] != '') {
					toggle_show('ispecifics_' + xmldata.itemid);
					fetch_js_object('ispecifics_' + xmldata.itemid).innerHTML = iteminfo[3];
				}
				if (iteminfo[5] == '1') {
					toggle_show('ibidlabel_' + xmldata.itemid);
					toggle_show('ibidvalue_' + xmldata.itemid);
				}
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=searchresult&itemid=' + itemid + '&token=' + iL['TOKEN']);
}
function acpjs_function(func)
{
	this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}
function acpjs_confirm(action, title, message, xid, runfunction, arg1, arg2, arg3, arg4, arg5) {
	if (runfunction == 'undefined' || runfunction == '' || runfunction == 'NaN') {
		return false;
	}
	var newtitle = title;
	jQuery('#modal_confirm #modal_confirm_header').html(newtitle);
	jQuery('#modal_confirm #modal_confirm_body').html(message);
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('onclick', 'event.preventDefault();acpjs_confirm_submit(\'' + action + '\', \'' + xid + '\', \'' + runfunction + '\', \'' + arg1 + '\', \'' + arg2 + '\', \'' + arg3 + '\', \'' + arg4 + '\', \'' + arg5 + '\');');
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('data-no-turbolink', 'true');
	show_modal('confirm', action, '');
}
function acpjs_confirm_submit(action, xid, runfunction, arg1, arg2, arg3, arg4, arg5) {
	jQuery('#modal_container #modal_confirm_onclick_action').removeClass('btn-primary');
	jQuery('#modal_container #modal_confirm_onclick_action').addClass('disabled');
	jQuery('#modal_container #modal_spinner').html('Working <img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" style="display:inline-block;padding-left:4px">');
	acpjs_function(runfunction, arg1, arg2, arg3, arg4, arg5);
}
function acp_confirm(action, title, message, xid, refresh, docmd, endpoint) {
	if (endpoint == 'undefined' || endpoint == '' || endpoint == 'NaN') {
		endpoint = location.href;
	}
	var newtitle = title;
	jQuery('#modal_confirm #modal_confirm_header').html(newtitle);
	jQuery('#modal_confirm #modal_confirm_body').html(message);
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('data-no-turbolink', 'true');
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('onclick', 'event.preventDefault();acp_confirm_submit(\'' + action + '\', \'' + xid + '\', \'' + refresh + '\', \'' + docmd + '\', \'' + endpoint + '\');');
	show_modal('confirm', action, '');
}
function acp_confirm_submit(action, xid, refresh, docmd, endpoint) {
	var keyvalues = '';
	var errorfields = 0;
	jQuery("#modal_container #modal_confirm_body input[class^='draw-input acp-required acp-input'], #modal_container #modal_confirm_body select[class^='draw-select acp-required acp-input'], #modal_container #modal_confirm_body textarea[class^='draw-textarea acp-required acp-input'], #modal_container #modal_confirm_body input[class^='cb-input acp-input'], #modal_container #modal_confirm_body input[class^='acp-hidden-input']").each(function(index, item) {
		if (jQuery(item).attr('type') == 'checkbox' && jQuery(item).is(':checked')) {
			keyvalues += '&' + jQuery(item).attr('name') + '=' + jQuery(item).val();
		}
		else if (jQuery(item).attr('name') != '' && jQuery(item).attr('type') != 'checkbox') {
			if (jQuery(item).val() == '' && jQuery(item).attr('type') != 'hidden')
			{
				jQuery(item).addClass('error');
				errorfields++;
			}
			else
			{
				keyvalues += '&' + jQuery(item).attr('name') + '=' + jQuery(item).val();
			}
		}
	});
	if (errorfields > 0)
	{
		return false;
	}
	jQuery('#modal_container #modal_confirm_onclick_action').removeClass('btn-primary');
	jQuery('#modal_container #modal_confirm_onclick_action').addClass('disabled');
	jQuery('#modal_container #modal_spinner').html('Working <img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" style="display:inline-block;padding-left:4px">');
	var datas = {
		'subcmd': action,
		'do': docmd,
		'xid': xid,
		'kv': keyvalues,
		'token': iL['TOKEN']
	};
	jQuery.ajax({
		url: endpoint,
		type: 'POST',
		data: datas,
		dataType: 'json',
		success: function(data, status, xhr) {
			close_modal();
			if (data.response == '0') {
				jQuery.growl.error({ title: phrase['_error'], message: data.message, duration: 5000, size: 'large', fixed: true });
			}
			else if (data.response == '1') {
				jQuery.growl.notice({ title: phrase['_successful'], message: data.message, duration: 4000, size: 'large' });
				if (refresh == '1')
				{
					setTimeout(location.reload.bind(location), 4000);
				}
			}
		},
		error: function(xhr, status, error) {
		        close_modal();
			jQuery.growl.error({ title: phrase['_error'], message: error, duration: 5000, size: 'large', fixed: true });
		}
	});
}
function bulk_confirm(action, title, message, checkboxid, selectedcountid, refresh, isdelete, endpoint)
{
	if (endpoint == 'undefined' || endpoint == '' || endpoint == 'NaN') {
		endpoint = location.href;
	}
	var newtitle = title;
	newtitle = newtitle.replace("[x]", jQuery('#'+ selectedcountid).html());
	jQuery('#modal_confirm #modal_confirm_header').html(newtitle);
	jQuery('#modal_confirm #modal_confirm_body').html(message);
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('data-no-turbolink', 'true');
	jQuery('#modal_confirm #modal_confirm_onclick_action').attr('onclick', 'event.preventDefault();bulk_confirm_submit(\'' + action + '\', \'' + checkboxid + '\', \'' + refresh + '\', \'' + isdelete + '\', \'' + endpoint + '\')');
	show_modal('confirm', action, '');
}
function bulk_confirm_submit(action, checkboxid, refresh, isdelete, endpoint)
{
	var keyvalues = '';
	var errorfields = 0;
	jQuery("#modal_container #modal_confirm_body input[class^='draw-input acp-required acp-input'], #modal_container #modal_confirm_body select[class^='draw-select acp-required acp-input'], #modal_container #modal_confirm_body textarea[class^='draw-textarea acp-required acp-input'], #modal_container #modal_confirm_body input[class^='cb-input acp-input'], #modal_container #modal_confirm_body input[class^='acp-hidden-input']").each(function(index, item) {
		console.log(jQuery(item).attr('type'));
		if (jQuery(item).attr('type') == 'checkbox' && jQuery(item).is(':checked'))
		{
			keyvalues += '&' + jQuery(item).attr('name') + '=' + jQuery(item).val();
		}
		else if (jQuery(item).attr('name') != '' && jQuery(item).attr('type') != 'checkbox') {
			if (jQuery(item).val() == '' && jQuery(item).attr('type') != 'hidden') {
				jQuery(item).addClass('error');
				errorfields++;
			}
			else {
				keyvalues += '&' + jQuery(item).attr('name') + '=' + jQuery(item).val();
			}
		}
	});
	if (errorfields > 0) {
		return false;
	}
	//
	jQuery('#modal_container #modal_confirm_onclick_action').removeClass('btn-primary');
	jQuery('#modal_container #modal_confirm_onclick_action').addClass('disabled');
	jQuery('#modal_container #modal_spinner').html('Working <img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" style="display:inline-block;padding-left:4px">');
	var datas = {
		'subcmd': action,
		'checkboxid': checkboxid,
		'kv': keyvalues,
		'token': iL['TOKEN']
	};
	jQuery.ajax({
		url: endpoint,
		type: 'POST',
		data: datas,
		dataType: 'json',
		success: function(data, status, xhr) {
			close_modal();
			if (data.response == '0') {
				jQuery.growl.error({ title: phrase['_error'], message: data.message, duration: 5000, size: 'large', fixed: true });
			}
			else if (data.response == '1') {
				jQuery.growl.notice({ title: phrase['_successful'], message: data.message, duration: 5000, size: 'large' });
			}
			else if (data.response == '2') {
				var dorefresh = true;
				var ids = data.ids.split('~');
				var successids = data.successids.split('~');
				var failedids = data.failedids.split('~');
				var failureresponse = data.errors.split('|');
				var successresponse = data.success.split('|');
				for (i in successresponse) {
					if (successresponse[i] != '') {
						jQuery.growl.notice({ title: phrase['_successful'], message: successresponse[i], duration: 5000, size: 'large' });
					}
				}
				for (i in failureresponse) {
					if (failureresponse[i] != '') {
						dorefresh = false;
						jQuery.growl.error({ title: phrase['_error'], message: failureresponse[i], duration: 5000, size: 'large', fixed: true });
					}
				}
				for (i in successids) { // untick successful checkboxes
					if (successids[i] != '') {
						if (jQuery('#tr_selected_' + successids[i]).length && jQuery('#' + checkboxid + '_' + successids[i]).length) {
							inlineCB.uncheck(jQuery('#' + checkboxid + '_' + successids[i] + ':checkbox').get(0));
							if (isdelete == '1') {
								jQuery('#tr_selected_' + successids[i]).addClass('draw-nav__link--is-disabled');
								jQuery('#tr_selected_' + successids[i]).hide(500);
							}
						}
						if (jQuery('#refreshpagelink').length) {
							jQuery('#refreshpagelink').removeClass('hide');

						}
					}
				}
				if (refresh == '1' && dorefresh) {
					setTimeout(location.reload.bind(location), 3000);
				}
			}
		},
		error: function(xhr, status, error) {
		        close_modal();
			jQuery.growl.error({ title: phrase['_error'], message: error, duration: 5000, size: 'large', fixed: true });
		}
	});
}
function show_modal(action, formsubcmd, modalhtml)
{
	if (modalhtml != '') {
		open_modal(modalhtml);
	}
	if (action == 'confirm') {
		var html = jQuery('#modal_confirm').html();
		open_modal(html)
	}
	else if (action == 'prompt') {
		var html = jQuery('#modal_prompt').html();
		open_modal(html)
	}
}
function open_modal(modal)
{
	jQuery('#modal_container').html(modal);
	jQuery('#modal_container').css('display','inline-block');
	jQuery('#modal_backdrop').css('display','inline-block');
	jQuery('#UIModalBackdrop').css('display','inline-block');
}
function close_modal()
{
	jQuery('#modal_confirm #modal_confirm_header').html('');
	jQuery('#modal_confirm #modal_confirm_body').html('');
	jQuery('#modal_container').css('display','none');
	jQuery('#modal_backdrop').css('display','none');
	jQuery('#UIModalBackdrop').css('display','none');
}
function sheel_widget(widget, options)
{
	//console.log(widget + ' :: [options: ' + JSON.stringify(options) + ']'); // <-- useful in development mode
	jQuery.ajax({
		type: 'POST',
		url: iL['AJAXURL'],
		data: 'do=jswidget&widget=' + widget + '&options=' + JSON.stringify(options) + '&token=' + iL['TOKEN'],
		success: function(output) {
			if (output != '') {
				var result = JSON && JSON.parse(output) || $.parseJSON(output);
				if (result.html != '' && options.container != undefined)
				{
					jQuery('#' + options.container).html(result.html);
				}
				if (result.js != '' && result.eval == 1)
				{
					eval(result.js); // no <script> tags
				}
			}
		},
		error: function(xhr, status, error) {
			console.log(error);
		}
	});
}
function in_array(needle, haystack)
{
	var count = haystack.length;
	for (var i=0; i<count; i++) {
		if (haystack[i] === needle) {
			return true;
		}
	}
	return false;
}
function delete_item_question(qid, sellerid)
{
	jQuery.ajax({
		type: 'POST',
		url: iL['AJAXURL'],
		data: 'do=deleteitemquestion&qid=' + qid + '&sellerid=' + sellerid + '&token=' + iL['TOKEN'],
		success: function(output) {
			if (output != '') {
				if (output == 1)
				{
					jQuery('#qa_' + qid).remove();
					jQuery('#qas_' + qid).remove();
					notice_js(phrase['_success'], phrase['_the_question_and_answers_was_deleted']);
				}
				else
				{
					alert_js(phrase['_the_question_could_not_be_deleted']);
				}
			}
		},
		error: function(xhr, status, error) {
			console.log(error);
		}
	});
}
function answer_vote(mode, messageid)
{
	jQuery.ajax({
		type: 'POST',
		url: iL['AJAXURL'],
		data: 'do=voteitemanswer&mode=' + mode + '&id=' + messageid + '&token=' + iL['TOKEN'],
		success: function(output) {
			if (output != '') {
				if (output == 1)
				{
					jQuery('#didthishelp_' + messageid).addClass('hide');
					notice_js(phrase['_success'], phrase['_your_vote_was_recorded_for_this_answer']);
				}
				else
				{
					alert_js(phrase['_your_vote_could_not_be_recorded']);
				}
			}
		},
		error: function(xhr, status, error) {
			console.log(error);
		}
	});
}
function validate_payment_dispute(f)
{
        haveerrors = 0;
        (f.comment.value.length < 1) ? showImageInline('comment', true, false) : showImageInline('comment', false, false);
	if (haveerrors)
	{
		alert_js(phrase['_you_did_not_select_reason_for_dispute_try_again']);
	}
        return (!haveerrors);
}
function emailcheck(str)
{
	if (str == '')
	{
		return true;
	}
        var at = "@"
        var dot = "."
        var lat = str.value.indexOf(at)
        var lstr = str.value.length
        var ldot = str.value.indexOf(dot)
        if (str.value.indexOf(at) == -1 || str.value.indexOf(at) == 0 || str.value.indexOf(at) == lstr)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false;
        }
        if (str.value.indexOf(dot) == -1 || str.value.indexOf(dot) == 0 || str.value.indexOf(dot) == lstr)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false
        }
        if (str.value.indexOf(at,(lat+1)) != -1)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false;
        }
        if (str.value.substring(lat-1,lat) == dot || str.value.substring(lat+1, lat+2) == dot)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false;
        }
        if (str.value.indexOf(dot,(lat+2)) == -1)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false;
        }
        if (str.value.indexOf(" ") != -1)
        {
                alert_js(phrase['_invalid_email']);
                str.value = '';
                return false;
        }
        return true
}
function auction_event(mode, userid, sellerid, eventid, divid)
{
	if (userid == '' || userid == 0)
	{
		location.href = iL['BASEURL'] + 'signin/?redirect=' + encodeURIComponent(iL['URI']);
		return false;
	}
	else if (userid == sellerid)
	{
		alert_js('You cannot register to your own auction event.  Thank you.');
		return false;
	}
	xmldata = new AJAX_Handler(true);
	xmldata.mode = mode;
	xmldata.sellerid = sellerid;
	xmldata.userid = userid;
	xmldata.eventid = eventid;
	xmldata.divid = divid;
	xmldata.onreadystatechange(function () {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200) {
			if (xmldata.handler.responseText != '') {
				if (xmldata.handler.responseText == '1')
				{
					if (jQuery('#' + divid).length)
					{
						if (xmldata.mode == 'register')
						{
							jQuery('#' + xmldata.divid).text(phrase['_unregister_from_event']);
							jQuery('#' + xmldata.divid).attr('onclick', 'auction_event(\'unregister\', ' + xmldata.userid + ', ' + xmldata.sellerid + ', ' + xmldata.eventid + ', \'' + xmldata.divid + '\')');
							notice_js(phrase['_registered'], 'You have successfully registered to this auction event.  We will send you email 1 hour before the event begins.  Good luck!');
						}
						else
						{
							jQuery('#' + xmldata.divid).text(phrase['_register_for_event']);
							jQuery('#' + xmldata.divid).attr('onclick', 'auction_event(\'register\', ' + xmldata.userid + ', ' + xmldata.sellerid + ', ' + xmldata.eventid + ', \'' + xmldata.divid + '\')');
							notice_js(phrase['_unregistered'], 'You have successfully unregistered from this auction event.  You will not receive email when this event starts.');
						}
					}
				}
				else
				{
					alert_js('Could not perform that action at this time. Please try again later.');
				}
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=avr&mode=' + urlencode(mode) + '&userid=' + urlencode(userid) + '&eventid=' + urlencode(eventid) + '&token=' + iL['TOKEN']);
}
