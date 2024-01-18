function showOrderDetails(orderno) {
    var element = document.querySelector("#order_details");
    if (!element.classList.contains("open")) {
        fetch_js_object("toggleOrder").innerHTML = '<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" width="13" height="13" alt="{_loading}" />';

        var querystring = "&orderno=" + orderno + "&token=" + iL['TOKEN'];
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
        ajaxRequest.onreadystatechange = function () {
            if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
                var ajaxDisplay = fetch_js_object("orders_status");
                ajaxDisplay.innerHTML = ajaxRequest.responseText;
            }
        }
        ajaxRequest.open('GET', iL['AJAXURL'] + '?do=getorderdetails' + querystring, true);
        ajaxRequest.send(null);
        fetch_js_object("toggleOrder").innerHTML = '<a href="javascript:;" onclick="showOrderDetails(\'' + orderno + '\')"><i class="fa fa-ellipsis-h text-black-50"></i></a>';
    }
    jQuery('#order_details').toggleClass('open');
}

function display_info_message(type, message) {
	if (type=='critical') {
        jQuery.growl.error({title: phrase['_error'], message: message});
	}
	else {
		jQuery.growl.warning({title: phrase['_notice'], message: message});
	}
	
}